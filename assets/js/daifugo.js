(function() {
	var userHand = document.getElementById("user-hand");
	var imgNum = userHand.childElementCount;

	//user-btn event
	document.getElementById("put").addEventListener('click', put, false);
	document.getElementById("pass").addEventListener('click', pass, false);

	function put() {
		var puttable = checkCards();
		if (puttable) {
			var selectedCards = getSelectedCards();
			putSelectedCards(selectedCards);
			deleteHands(selectedCards);
		}
	}

	function pass() {
		console.log("PASS");
	}

	function checkCards() {
		//TODO: 選択してるカードが出せるカードかチェック
		return true;
	}

	function getSelectedCards() {
		var targetCards = [];
		for (var i = 0 ; i < imgNum; i++) {
			var target = document.getElementById("img" + i);
			if (target != null) {
				var tgtStyle = target.style.fontWeight;
				if (tgtStyle == "bold") {
					targetCards.push(target);
				}	
			}
		}
		console.log(targetCards);
		return targetCards;
	}

	function putSelectedCards(selectedCards) {
		//TODO:複数枚の配置が決まってない
		for (var card of selectedCards) {
			
			console.log(card);
		}
	}

	function deleteHands(selectedCards) {
		//TODO:複数枚の配置が決まってない
		var hands = userHand.children;
		for (var card of selectedCards) {
			for (var c of hands) {
				if(c.style.fontWeight == "bold" && c.style.fontWeight == card.style.fontWeight) {
					c.remove();
				}
			}
		}
	}

	//user-hands event
	for (var i = 0; i < imgNum; i++) {
		var selectFlg = [];
		selectFlg[i] = false;

		(function(n) {
			document.getElementById("img" + n).addEventListener('click', function() {
				if (!selectFlg[n]) {
					//1枚しか選べないテストコード
					var hands = userHand.children;
					for (var j = 0; j < imgNum; j++) {
						for (var c of hands) {
							if (c.id == ("img" + j)) {
								if (document.getElementById("img" + j).style.fontWeight == "bold"){
									document.getElementById("img" + j).style.fontWeight = "";
									document.getElementById("img" + j).style.border = "";
								}
							}
						}
					}
					document.getElementById("img" + n).style.fontWeight = "bold";
					document.getElementById("img" + n).style.border = "solid 3px #754F44";
					selectFlg[n] = true;
				}else{
					document.getElementById("img" + n).style.fontWeight = "";
					document.getElementById("img" + n).style.border = "";
					selectFlg[n] = false;	
				}
			}, false);
		})(i);
	}


})();
