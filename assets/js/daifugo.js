(function() {
	var userHand = document.getElementById("user-hand");
	var userHandNum = userHand.childElementCount;

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
	for (var i = 0; i < userHandNum; i++) {
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
		let targetCards = getSelectedCards();

		let postData = {
			userId: getUserId(),
			cards: getSelectedCards()
		}
		alert('data: ' + targetCards);
		document.getElementById("hidden-put").value = targetCards;
	}

	//TODO : 
	function pass() {
		document.getElementById("hidden-pass").value = "pass";
	}

	//TODO :
	function test() {
		let postData = {
			userId: getUserId(),
			cards: getSelectedCards()
		}

		alert(`cards: ${postData.cards}`);

		//Ajax
		$.ajax({
			type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
			url: 'http://localhost/daifugo/test', //リクエストの送り先URL（適宜変える）
			data: postData, //サーバに送るデータ。JSのオブジェクトで書ける
			dataType: 'json', //サーバからくるデータの形式を指定

			success: function(data) {

				// 手札から出したカードを消す
				const handsElement = document.getElementById("user-hand");
				deleteCarads(data.cards_used_in_current_turn, handsElement);


				// 選択したカードをajax-testに表示する
				// const areaCardElement = document.getElementById("ajax-test");
				const gameAreaCardElement = document.getElementsByClassName("game-area")[0];
				const usedCardElements = generateGameAreaCardElement(data.cards_used_in_current_turn, gameAreaCardElement);
				usedCardElements.forEach(element => gameAreaCardElement.insertAdjacentHTML('beforeend', element));

				//TODO : CPU３体分の動き
				//CPUが出すカードはサーバから受け取る
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
	 * @type {String} userId
	 */
	function getUserId() {
		return 'user0';
	}

	/**
	 * 選択しているカードを返す
	 * @type {String} selected cards
	 */
	function getSelectedCards() {
		let targetCards = [];
		for (let i = 0; i < userHandNum; i++) {
			let target = document.getElementsByClassName("img" + i)[0];
			if (target != null) {
				let tgtStyle = target.style.fontWeight;
				if (tgtStyle == "bold") {
					targetCards.push(target);
				}
			}
		}
		let selectCardIdStr = "";
		for (let i = 0; i < targetCards.length; i++) {
			if ((targetCards.length - 1) == i) {
				selectCardIdStr += targetCards[i].id;
			} else {
				selectCardIdStr += targetCards[i].id + ",";
			}
		}
		return selectCardIdStr;
	}

	/**
	 * delete putted cards' img element
	 * @type {Array{Object}} cards arrat
	 * @type {Array{String}} divElement array
	 */
	function deleteCarads(cards, targetElement) {
		const handsElements = Array.from(targetElement.children);
		let deleteCardIds = [];
		cards.forEach(c => {
			handsElements.filter(e => c.id == e.id).forEach(deleteE =>
				deleteE.parentNode.removeChild(deleteE));
		});
	}

	/**
	 * generate div element for game-area-card with putdata index, card id, img path.
	 * @type {Array{Object}} cards arrat
	 * @type {Array{String}} divElement array
	 * @return gameAreaCardElement array
	 */
	function generateGameAreaCardElement(cards, targetElement) {
		const cardIndexes = Object.keys(cards);
		const leftValues = [44, 50, 48, 43];
		const topValues = ['auto', 24, 36, 30];
		const location = getLocation(targetElement);
		let zIndex = getZIndex(targetElement);

		let cnt = 0;
		const gameAreaCardElements = [];
		for (const card of cards) {
			const cardIndex = parseInt(cardIndexes[cnt++]);
			const gameAreaElement = `<div class="card img" id="location-${location}" ` +
				`style="z-index:${zIndex++}; left:${leftValues[location-1]+cardIndex}%; top:${topValues[location-1]}%;">` +
				`<img src="http://localhost/${card.cardImg}" id="${card.id}" ` +
				`width="90" height="auto" alt=""></div>`;

			console.log(`cnt: ${cnt} /card index: ${cardIndex} /id: ${card.id} /imgPath: ${card.cardImg} n` +
				`/location: ${location} /left: ${leftValues[cardIndex]} /top: ${topValues[cardIndex]}`);

			gameAreaCardElements.push(gameAreaElement);
		}
		return gameAreaCardElements;
	}

	/**
	 * get z-index value from current game-area card z-index
	 * @param  {HTMLDivElement} targetElement element of target area 
	 * @return {int} location
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
	 * @param  {HTMLDivElement} targetElement element of target area 
	 * @return {int} location
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