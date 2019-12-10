(function() {
	var userHand = document.getElementById("user-hand");
	var userHandNum = userHand.childElementCount;

	//ボタンクリックイベント
	document.getElementById("put").addEventListener('click', put, false);
	document.getElementById("pass").addEventListener('click', pass, false);

	/**
	 * ユーザの手札に対するイベント
	 * 1クリックでカードを囲む、2クリックで枠解除
	 */
	var selectFlg = [];
	for (var i = 0; i < userHandNum; i++) {
		selectFlg[i] = false;

		(function(n) {
			document.getElementsByClassName("img" + n)[0].addEventListener('click', function() {
				if (!selectFlg[n]) {
					document.getElementsByClassName("img" + n)[0].style.fontWeight = "bold";
					document.getElementsByClassName("img" + n)[0].style.border = "solid 3px #754F44";
					selectFlg[n] = true;
				} else {
					document.getElementsByClassName("img" + n)[0].style.fontWeight = "";
					document.getElementsByClassName("img" + n)[0].style.border = "";
					selectFlg[n] = false;	
				}
			}, false);
		})(i);
	}


	/**
	 * 1.クリックしたカードの枠を付ける
	 * 2.hidden-putに選択したカードのidを渡す(id, id, ...)
	 */
	function put() {
		var selectedCardElements = getSelectedCards();
		var selectCardIdStr = "";
		for (var i = 0; i < selectedCardElements.length; i++) {
			if ((selectedCardElements.length - 1) == i) {
				selectCardIdStr += selectedCardElements[i].id.slice(3);
			}else{
				selectCardIdStr += selectedCardElements[i].id.slice(3) + ",";	
			}
		}
		document.getElementById("hidden-put").value = selectCardIdStr;
	}

	//TODO : 
	function pass() {
		document.getElementById("hidden-pass").value = "pass";
	}

	/**
	 * 選択しているカードを返す
	 * @return array [selected cards]
	 */
	function getSelectedCards() {
		var targetCards = [];
		for (var i = 0 ; i < userHandNum; i++) {
			var target = document.getElementsByClassName("img" + i)[0];
			if (target != null) {
				var tgtStyle = target.style.fontWeight;
				if (tgtStyle == "bold") {
					targetCards.push(target);
				}	
			}
		}
		return targetCards;
	}
})();
