(function() {

	const USER_HAND = "user-hand";
	const USER_HAND_ELM = document.getElementById(USER_HAND);
	const USER_HAND_NUM = USER_HAND_ELM.childElementCount;
	const GAME_AREA_CARD_ELM = document.getElementsByClassName("game-area")[0];
	const CPU_MOVE_INTERVAL_MS = 1000;
    const BASE_URL = window.location.origin;

	/**
	 * ボタンクリックイベント
	 */
	document.getElementById("put").addEventListener('click', put, false);
	document.getElementById("pass").addEventListener('click', pass, false);
	document.getElementById("test-btn").addEventListener('click', test, false);

	/**
	 * ユーザの手札に対するイベント
	 * 1クリックで選択したカードを枠で囲む、2クリックで枠を外す
	 */
	var selectFlg = [];
	for (var i = 0; i < USER_HAND_NUM; i++) {
		selectFlg[i] = false;

		(function(n) {
			document.getElementsByClassName("img" + n)[0].addEventListener('click', function() {
				if (!selectFlg[n]) { //1クリック:枠で囲む
					document.getElementsByClassName("img" + n)[0].style.fontWeight = "bold";
					document.getElementsByClassName("img" + n)[0].style.border = "solid 3px #754F44";
					selectFlg[n] = true;
				} else { //2クリック:枠を外す
					document.getElementsByClassName("img" + n)[0].style.fontWeight = "";
					document.getElementsByClassName("img" + n)[0].style.border = "";
					selectFlg[n] = false;
				}
			}, false);
		})(i);
	}

	/**
	 * formのhidden-putに選択したカードのidを渡す(id, id, ...)
	 */
	function put() {
		let postData = {
			userId: getUserId(),
			cards: getSelectedCards()
		}

		if (!postData.cards) {
			return;
		}

		//Ajax
		$.ajax({
			type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
			url: BASE_URL + '/daifugo/put', //リクエストの送り先URL（適宜変える）
			data: postData, //サーバに送るデータ。JSのオブジェクトで書ける
			dataType: 'json', //サーバからくるデータの形式を指定

			success: function(data) {
				userPlayingMove(data.cards_used_in_current_turn);
				cpuPlayingMove(data.cards_cpu_used_in_current_turn);
				//turnId 的なのもサーバから受け取る
				//TODO : 最後にターンIDで画面遷移？
				//window.location.href = 'http://localhost/test/client/btns';
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest); // XMLHttpRequestオブジェクト
				console.log(textStatus); // status は、リクエスト結果を表す文字列
				console.log(errorThrown); // errorThrown は、例外オブジェクト
			}
		});
	}

	//TODO : 
	function pass() {
		const postData = {
			userId: getUserId(),
			cards: null
		}

		//Ajax
		$.ajax({
			type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
			url: BASE_URL + '/daifugo/pass', //リクエストの送り先URL（適宜変える）
			data: postData, //サーバに送るデータ。JSのオブジェクトで書ける
			dataType: 'json', //サーバからくるデータの形式を指定

			success: function(data) {
				// passの場合は何もしない
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest); // XMLHttpRequestオブジェクト
				console.log(textStatus); // status は、リクエスト結果を表す文字列
				console.log(errorThrown); // errorThrown は、例外オブジェクト
			}
		});
	}

	/**
	 * put用ajaxテストコード
	 */
	function test() {
		let postData = {
			userId: getUserId(),
			cards: getSelectedCards()
		}

		if (!postData.cards) {
			return;
		}

		//Ajax
		$.ajax({
			type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
			url: BASE_URL + '/daifugo/test', //リクエストの送り先URL（適宜変える）
			data: postData, //サーバに送るデータ。JSのオブジェクトで書ける
			dataType: 'json', //サーバからくるデータの形式を指定

			success: function(data) {
				userPlayingMove(data.cards_used_in_current_turn);
				cpuPlayingMove(data.cards_cpu_used_in_current_turn);
				//turnId 的なのもサーバから受け取る
				//TODO : 最後にターンIDで画面遷移？
				//window.location.href = 'http://localhost/test/client/btns';
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest); // XMLHttpRequestオブジェクト
				console.log(textStatus); // status は、リクエスト結果を表す文字列
				console.log(errorThrown); // errorThrown は、例外オブジェクト
			}
		});
	}

	/**
	 * TODO: ユーザごとにIDを返す
	 * @type String userId
	 */
	function getUserId() {
		return 'user0';
	}

	/**
	 * 選択しているカードを返す
	 * @type String selected cards
	 */
	function getSelectedCards() {
		const tartgetArray = Array.from(document.getElementById('user-hand').children);
		return tartgetArray.filter(t => t.style.fontWeight == "bold").map(t => t.id);
	}

	/**
	 * move of user playing
	 * @param  {[type]} data [description]
	 */
	function userPlayingMove(userSelectedCards) {

		if (!userSelectedCards) {
			return;
		}
		playingMove(userSelectedCards, USER_HAND_ELM);
	}

	/**
	 * move of cpu playing
	 * @param  {[type]} data [description]
	 */
	function cpuPlayingMove(cpuSelectedCards) {
		let cnt = 0;
		Object.keys(cpuSelectedCards).forEach(userId => {

			const cpuPlaying = function() {
				console.log(`===Interval : ${cnt++} ===`)

				// CPU手札から出したカードを消す
				const convertedUserId = userId.substr(0, 3) + " " + userId.substr(3, 4);
				const cpuHandsElement = document.getElementsByClassName(convertedUserId)[0].children[0];

				playingMove(cpuSelectedCards[userId], cpuHandsElement);
			}

			setTimeout(cpuPlaying, CPU_MOVE_INTERVAL_MS * ++cnt); //CPU_MOVE_INTERVAL msごとにCPU操作
		});
	}

	/**
	 * playing move
	 * delete selected cards in hands, display selected card in game area. 
	 * @param  json selectedCards 
	 * @param  HTMLCollection targetHandElm
	 */
	function playingMove(selectedCards, targetHandElm) {

		// 手札から出したカードを消す
		// const handsElement = document.getElementById(targetHandElm);
		deleteCarads(selectedCards, targetHandElm);

		// 選択したカードをgame-areaに表示する
		const usedCardElements = generateGameAreaCardElm(selectedCards, GAME_AREA_CARD_ELM);
		usedCardElements.forEach(element => GAME_AREA_CARD_ELM.insertAdjacentHTML('beforeend', element));
	}


	/**
	 * delete putted cards' img element
	 * @type Array{Object} cards
	 * @type Array{String} parentElmOfDltTarget
	 */
	function deleteCarads(cards, parentElmOfDltTarget) {
		const handsElements = Array.from(parentElmOfDltTarget.children);
		let deleteCardIds = [];
		cards.forEach(c => {
			handsElements.filter(e => c.id == e.id).forEach(deleteE => {
				console.log(`deleteCards() c.id : ${c.id} / deleteE.id : ${deleteE.id}`);
				deleteE.parentNode.removeChild(deleteE)
			});
		});
	}

	/**
	 * generate div element for game-area-card with putdata index, card id, img path.
	 * @type Array{Object} cards arrat
	 * @type Array{String} divElement array
	 * @return GAME_AREA_CARD_ELM array
	 */
	function generateGameAreaCardElm(cards, targetElement) {
		const cardIndexes = Object.keys(cards);
		const leftValues = [44, 50, 48, 43];
		const topValues = ['auto', 24, 36, 30];
		const location = getLocation(targetElement);
		let zIndex = getZIndex(targetElement);

		let cnt = 0;
		const cardsElms = [];
		for (const card of cards) {
			const cardIndex = parseInt(cardIndexes[cnt++]);
			const gameAreaElement = `<div class="card img" id="location-${location}" ` +
				`style="z-index:${zIndex++}; left:${leftValues[location-1]+cardIndex}%; top:${topValues[location-1]}%;">` +
				`<img src="${BASE_URL}/${card.cardImg}" id="${card.id}" ` +
				`width="90" height="auto" alt=""></div>`;

			console.log(`cnt: ${cnt} /card index: ${cardIndex} /id: ${card.id} /imgPath: ${card.cardImg} n` +
				`/location: ${location} /left: ${leftValues[cardIndex]} /top: ${topValues[cardIndex]}`);

			cardsElms.push(gameAreaElement);
		}
		return cardsElms;
	}

	/**
	 * get z-index value from current game-area card z-index
	 * @param  HTMLDivElement targetElement element of target area 
	 * @return int location
	 */
	function getZIndex(targetElement) {
		const lastElement = targetElement.lastElementChild;
		const lastZIndex = parseInt(lastElement.style.zIndex);
		console.log(`z-index: ${lastElement.style.zIndex}`);
		console.log(`z-index: ${lastZIndex}`);
		let zIndex = 0;
		if (lastZIndex) {
			zIndex = lastZIndex + 1;
		}
		return zIndex;
	}

	/**
	 * get card location from current game-area card location
	 * @param  HTMLDivElement targetElement element of target area 
	 * @return int location
	 */
	function getLocation(targetElement) {
		const lastElement = targetElement.lastElementChild;
		let location = 1;
		if (lastElement) {
			let lastLocation = parseInt(lastElement.id.replace(/[^0-9]/g, ''));
			if (lastLocation < 4) {
				location = lastLocation + 1;
			}
		}
		return location;
	}
})();
