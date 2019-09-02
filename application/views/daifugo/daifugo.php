<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>大富豪</title>
	<?php echo link_tag('/assets/css/daifugo.css'); ?>
	<link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

</head>
<body>
	<div class="cpu-area">
		<div style="text-align:center">CPU</div>
	</div>
	<div class="game-area">
		<div class=""></div>
	</div>
	<div class="user-area">
                <div class="btn">
                    <a href="#" class="btn-square-so-pop">出す</a>
                    <a href="#" class="btn-square-so-pop">パス</a>
                </div>
		<div class="hand">
			<?php foreach ($cards as $card): ?>
				<?php 
				$card_prop = array(
									'src' => $card,
									'width' => '80',
									'height' => 'auto'
							);
				echo img($card_prop); ?>
			<?php endforeach; ?>
		</div>
		<div class="status">
			<div class="icon">
				<?php
				$icon_prop = array(
							'src' => 'assets/img/testicon.png',
							'width' => '50',
							'height' => 'auto'
						);
				echo img($icon_prop);?>	
			</div>
			<div class="info">
					<div class="name">ユーザーネーム</div>
					<div class="current-class">平民</div>
			</div>
		</div>
	</div>
</body>
</html>や
