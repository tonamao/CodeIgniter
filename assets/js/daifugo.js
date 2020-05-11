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
				if (!selectFlg[n]) {//1クリック:枠で囲む
					document.getElementsByClassName("img" + n)[0].style.fontWeight = "bold";
					document.getElementsByClassName("img" + n)[0].style.border = "solid 3px #754F44";
					selectFlg[n] = true;
				} else {//2クリック:枠を外す
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

		let putData = {
			userId	: getUserId(),
			cards	: getSelectedCards()
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
		let putData = {
			userId	: getUserId(),
			cards	: getSelectedCards()
		}

		alert(`cards: ${putData.cards}`);

		//Ajax
		$.ajax({
			type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
			url: 'http://localhost/daifugo/test',//リクエストの送り先URL（適宜変える）
			data: putData, //サーバに送るデータ。JSのオブジェクトで書ける
			dataType: 'json',//サーバからくるデータの形式を指定

			//リクエストが成功したらこの関数を実行！！
			success: function(data){

				// 選択したカードをajax-testに表示する
				let areaCardElement = document.getElementById("ajax-test");

				// --------getSelectingCards()で取得しようとした場合--------
				let areaCards = data.game_area_cards;
				for (const cardIdPathArray of areaCards) {
					let index = Object.keys(cardIdPathArray);
					console.log(`array index: ${index}`);
					for (const value of cardIdPathArray) {
						let cardId = Object.keys(value);
						let imgPath = Object.values(value);
						console.log(`cardId: ${cardId}`);
						console.log(`imgPath: ${imgPath}`);
						let insertImg = `<img src='http://localhost/${imgPath}'
							 id='img${cardId}'
							 class='img${index}' width='90' height='auto' alt>`;
						areaCardElement.insertAdjacentHTML('beforeend', insertImg);

						//TODO: divを生成する関数を作る
						// let gameAreaCardElement = generateGameAreaCardElement(index, cardId, imgPath);

					}
				}

				// // --------getUsedCards()で取得した場合--------
				// let areaCrads = data.game_area_cards;
				// for (const cardArrayPerTurn of areaCrads) { // ターンごとに出したカード
				// 	let turnNo = Object.keys(cardArrayPerTurn);
				// 	let cardsPerTurn = Object.values(cardArrayPerTurn);
				// 	console.log(`turnNo : ${turnNo}`);
				// 	for (const cardArray of cardArrayPerTurn) { // １ターンで出したカード
				// 		let cardId = Object.keys(cardArray);
				// 		let cardPath = Object.values(cardArray);
				// 		console.log(`cardId : ${cardId}`);
				// 		console.log(`cardPath : ${cardPath}`);

				// 		let insertImg = `<img src='http://localhost/${cardPath}'
				// 			 id='img${cardId}'
				// 			 class='img${turnNo}' width='90' height='auto' alt>`;
				// 		areaCardElement.insertAdjacentHTML('afterend', insertImg);
				// 	}
				// }

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
	 * 選択しているカードを返す
	 * @type {String} selected cards
	 */
	function getSelectedCards() {
		let targetCards = [];
		for (let i = 0 ; i < userHandNum; i++) {
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
				selectCardIdStr += targetCards[i].id.slice(3);
			}else{
				selectCardIdStr += targetCards[i].id.slice(3) + ",";
			}
		}
		return selectCardIdStr;
	}

	/**
	 * @type {String} userId
	 */
	function getUserId() {
		return 'user0';
	}

	/**
	 * generate div element for game-area-card with putdata index, card id, img path.
	 * @type {String} divElement
	 */
	function generateGameAreaCardElement(index, cardId, imgPath) {

		let gameAreaElement =  `<div class="card img" id="location-${index}" style="z-index:${index}; 
								left:<?php echo $firstLeft.'%'; ?>; top:<?php echo $t == 'auto' ? $t : $t.'%';  ?>;">
							<?php
							$card_prop = array(
											'src' => $path,
											'id' => 'img-location-'."$index",
											'width' => '90',
											'height' => 'auto'
										);
							echo img($card_prop); ?>
						</div>`;
	}
})();
