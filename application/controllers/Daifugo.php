<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {
    public static $GAME_NAME = 'DFG';

    public function __construct()
    {
        parent::__construct();
        $this->output->enable_profiler(false);
        $this->load->helper('url_helper');
        $this->load->helper('html');
        $this->load->model('gameMatching');
        $this->load->model('cardManager');
        $this->load->model('ruleManager');
        $this->load->model('gameManager');
        $this->load->model('gameAreaManager');
        $this->load->model('msGame');
        $this->load->helper('form');
        $this->load->library('form_validation');

    }

    public function rule()
    {
        //マッチングを状態を登録する
        $this->gameMatching->insertGameMatching(Daifugo::$GAME_NAME);

        $data['game_info'] = $this->msGame->findAllGame();
        //TODO: ルール情報取得
        $data['rule_info'] = [];

        $this->load->view('daifugo/rules', $data);
    }

    /**
     * Insert daifugo_matching.
     * Hand out cards.
     * Check player's class & exchange cards.
     * Insert daifugo_hand.
     * Display first player hands.
     */
    public function start()
    {
        //get all player's hands
        $data['all_hands']       = $this->cardManager->getFirstHandsLists($this->gameMatching->getNumOfPlayer());
        $data['back']            = $this->cardManager->getCardBack();
        $data['game_area_cards'] = [];

        //TODO: check player's class & exchange cards -> update hand DB

        $this->load->view('daifugo/daifugo', $data);
    }

    /**
     * update player hands & game status, return ajax response
     */
    public function put()
    {
        $this->_execTurn(false);
    }

    /**
     * update only game status, return ajax data
     */
    public function pass()
    {
        $this->_execTurn(true);
    }

    private function __execTurn($pass_flg)
    {
        $user_id      = $this->input->post('userId');
        $user_turn_info = [
            $user_id => [
                'used_cards' => $this->input->post('cards'),
                'pass_flg'   => $pass_flg,
            ],
        ];

        $turn_status_info['user_id'] = $user_id;

        // select card
        $turn_status_info['all_player_selecting_cards'] = $this->gameManager->selectCards($user_id, $user_turn_info);

        // save turn status
        //$this->gameManager->insertTurnStatus($game_id, $game_turn_id, $all_players_hands, $all_player_selecting_cards, $end_players, $turn_owner, $pass_num);
        //$this->gameManager->insertTurnStatus($turn_status_infoƒrules);

        // return response
        $area_cards = [];
        foreach ($turn_status_info['all_player_selecting_cards'] as $player_id => $cards) {
            $cardArray = [];
            foreach ($cards as $card) {
                $cardArray[$player_id][] = $card->getId();
            }
            log_message('debug', __METHOD__.':'.__LINE__.':cardArray: ' . print_r($cardArray, true));
            $area_cards[] = $cardArray;
        }
        $turn_status_info['area_cards'] = $area_cards;
        $data = $this->_createResponseData($turn_status_info);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _execTurn($passFlg)
    {
        $userId      = $this->input->post('userId');
        $userPutInfo = [
            $userId => [
                'used_cards' => $this->input->post('cards'),
                'pass_flg'   => $passFlg,
            ],
        ];
        $allPlayersSelectingCards = $userPutInfo + $this->cardManager->selectCpuHands($userId, $userPutInfo);
        $allPlayerUsedCards = [];
        foreach ($allPlayersSelectingCards as $playerId => $cardArray) {
            $result = $this->_execPlayerTurn($userId, $playerId, $cardArray['used_cards'], boolval($cardArray['pass_flg']));
            $allPlayerUsedCards += [$playerId => $result];
        }

        //$dataをJSONにして返す
        $data = $this->_createPutResponse($userId, $allPlayerUsedCards);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _createResponseData($turn_status_info)
    {
        log_message('debug', '['.__LINE__.'] create response');
        $playerNum = $this->gameMatching->getNumOfPlayer();
        $data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $turn_status_info['user_id']);
        $data['back'] = $this->cardManager->getCardBack();
        $data['game_area_cards'] = [];
        //$data['game_area_cards'] = $turn_status_info['area_cards'];
        $data['cards_used_in_current_turn'] = isset($turn_status_info['all_player_selecting_cards'][$turn_status_info['user_id']]) ? array_splice($turn_status_info['all_player_selecting_cards'], array_search($turn_status_info['user_id'], $turn_status_info['all_player_selecting_cards']), 1) : [];
        $data['cards_cpu_used_in_current_turn'] = $turn_status_info['all_player_selecting_cards'];
        log_message('debug', 'Response [cards_used_in_current_turn]: ' . print_r($data['cards_used_in_current_turn'], true));
        log_message('debug', 'Response [cards_cpu_used_in_current_turn]: ' . print_r($data['cards_cpu_used_in_current_turn'], true));
        return $data;
    }

    private function _execPlayerTurn($userId, $playerId, $usedCards=[], $passFlg)
    {
        // update user hand
        if (!empty($usedCards)) {
            $playerHands = $this->cardManager->updateCardToUsed($userId, $usedCards);
        }

        $playerEndFlg = false;
        if (!$passFlg) {
            $playerEndFlg = $this->cardManager->getPlayerEndFlg($playerId);
        }
        // update game status for user
        $latestGameStatusId = $this->_updateGameStatus($playerId, $playerEndFlg, $passFlg);

        // update game area cards for user
        if (!empty($usedCards)) {
            $this->gameAreaManager->insertGameAreaStatus($userId, $passFlg, $latestGameStatusId, $usedCards);
        }

        return isset($playerHands) ? $playerHands : [];
    }

    private function _updateGameStatus($playerId, $playerEndFlg, $passFlg)
    {
        //insert game status
        $this->gameManager->insertGameStatus($playerId, $passFlg);
        //update user status
        $latestGameStatusId = $this->gameManager->getLatestGameStatus();
        if ($playerEndFlg) {
            $this->gameManager->insertUserStatus($latestGameStatusId, $playerId, $playerEndFlg);
        }
        return $latestGameStatusId;
    }

    /**
     * create data of user & cpu hands
     *
     * @param int userId
     * @param array userCards
     * @param array cpuCards
     */
     //FIXME: all_handsは_execPlayerTurn内のupdateCardToUsed()で取得できるはずなので、そこでまとめてやる
    private function _createPutResponse($userId, $allPlayerUsedCards)
    {
        log_message('debug', '['.__LINE__.'] create response');
        $playerNum = $this->gameMatching->getNumOfPlayer();
        $data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
        $data['back'] = $this->cardManager->getCardBack();
        $data['cards_used_in_current_turn'] = isset($allPlayerUsedCards[$userId]) ? array_splice($allPlayerUsedCards, array_search($userId, $allPlayerUsedCards), 1) : [];
        $data['cards_cpu_used_in_current_turn'] = $allPlayerUsedCards;
        $data['game_area_cards'] = $this->cardManager->getUsedCards();
        log_message('debug', 'Response [game_area_cards]: ' . print_r($data['game_area_cards'], true));
        log_message('debug', 'Response [cards_used_in_current_turn]: ' . print_r($data['cards_used_in_current_turn'], true));
        log_message('debug', 'Response [cards_cpu_used_in_current_turn]: ' . print_r($data['cards_cpu_used_in_current_turn'], true));
        return $data;
    }

    ////////////////////////////////////////
    ///  test code                       ///
    ////////////////////////////////////////
    /**
     * test code for delete DB
     */
    public function delete()
    {
        $result = $this->cardManager->deleteAll();
        echo $result == true ? 'Deletion completed!!' : 'Failed to delete DB...';
    }
}
