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
        $this->load->model('masterDataManager');
        $this->load->helper('form');
        $this->load->library('form_validation');

    }

    public function rule()
    {
        //マッチングを状態を登録する
        $this->gameMatching->insertGameMatching(Daifugo::$GAME_NAME);

        //TODO: ルール情報取得
        $data['gameInfo'] = $this->masterDataManager->getGameInfo();

        //TODO: ルール情報表示

        $this->load->view('daifugo/rule-selection', $data);
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
        $data['game_area_cards'] = array();

        //TODO: check player's class & exchange cards -> update hand DB

        $this->load->view('daifugo/daifugo', $data);
    }

    /**
     * put process
     * update player hands & game status, return ajax response
     */
    public function put()
    {
        ////////////////////////////////
        // Start To Update USER cards //
        ////////////////////////////////
        $userId          = $this->input->post('userId');
        $userCardsPosted = $this->input->post('cards');
        $passFlg         = false;

        // TODO: check rule
        $isMatchingRules = $this->ruleManager->checkRules($this->ruleManager->getRules(), $userCardsPosted);
        $userEndFlg      = false;
        $userCardsObj    = null;
        if ($isMatchingRules) {
            // update user hand
            $userCardsObj = $this->cardManager->updateCardToUsed($userId, $userCardsPosted);// FIXME: gameId事前に取っておいて引数に入れる
        }

        // update game status for user
        $userEndFlg         = $this->cardManager->getPlayerEndFlg($userId);
        $latestGameStatusId = $this->_updateGameStatus($userId, $userEndFlg, $passFlg);
        // update game area cards for user
        $this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $userCardsPosted);

        ///////////////////////////////
        // Start To Update CPU cards //
        ///////////////////////////////
        $cpuCards = $this->_useCpuHands($userId, $latestGameStatusId, $passFlg);

        /////////////////////
        // create response //
        /////////////////////
        $data = $this->_createPutResponse($userId, $userCardsObj, $this->cardManager->convertCpuCards($cpuCards));

        //$dataをJSONにして返す
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /**
     * pass process
     * update only game status, return ajax data
     */
    public function pass()
    {
        // get ajax data
        $userId  = $this->input->post('userId');
        $passFlg = true;

        //update game status
        $this->gameManager->insertGameStatus($userId, $passFlg);
        //update user(playing game) status
        $latestGameStatusId = $this->gameManager->getLatestGameStatus();

        // create response
        $playerNum               = $this->gameMatching->getNumOfPlayer();
        $data['all_hands']       = $this->cardManager->getLatestHand($playerNum, $userId);
        $data['back']            = $this->cardManager->getCardBack();
        $data['game_area_cards'] = $this->cardManager->getUsedCards();

        ///////////////////////////////
        // Start To Update CPU cards //
        ///////////////////////////////
        $cpuCards = $this->_useCpuHands($userId, $latestGameStatusId, $passFlg);

        //$dataをJSONにして返す
        $data = $this->_createPutResponse($userId, null, $this->cardManager->convertCpuCards($cpuCards));
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _updateGameStatus($playerId, $userEndFlg, $passFlg)
    {
        //insert game status
        $this->gameManager->insertGameStatus($playerId, $passFlg);
        //update user status
        $latestGameStatusId = $this->gameManager->getLatestGameStatus();
        if ($userEndFlg) {
            $this->gameManager->insertUserStatus($latestGameStatusId, $playerId, $userEndFlg);
        }
        return $latestGameStatusId;
    }

    private function _useCpuHands($userId, $latestGameStatusId, $passFlg)
    {
        $cpuNum     = 3; //TODO: 動的に取得する
        $cpuCardNum = 2; //TODO: 動的に取得する
        $cpuCards   = $this->cardManager->useCpuHands($this->gameMatching->getGameIdByUserId($userId), $cpuNum, $cpuCardNum); //TODO: 前の人が出したカードを見てカードを選ぶようにする
        foreach ($cpuCards as $cpuId => $cardArray) {
            // update CPU hand
            $cpuEndFlg = $this->cardManager->getPlayerEndFlg($cpuId);
            // update game status for user
            $passFlg = count($cardArray) == 0 ? true : false;
            $latestGameStatusId = $this->_updateGameStatus($cpuId, $cpuEndFlg, $passFlg);
            // update game area cards for user
            $this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $cardArray);
        }
        return $cpuCards;
    }

    private function _createPutResponse($userId, $userCards, $cpuCards)
    {
        log_message('debug', '['.__LINE__.'] create response');
        $playerNum = $this->gameMatching->getNumOfPlayer();
        $data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
        $data['back'] = $this->cardManager->getCardBack();
        $data['cards_used_in_current_turn'] = $userCards;
        $data['cards_cpu_used_in_current_turn'] = $cpuCards;
        $data['game_area_cards'] = $this->cardManager->getUsedCards();
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
