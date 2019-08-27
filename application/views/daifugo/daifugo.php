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
		<div class="hand">
			<?php foreach ($cards as $card): ?>
				<?php echo img($card); ?>
			<?php endforeach; ?>


				<?php for ($i = 0; $i < 54/4; $i++) {
					$cnt = $i + 1;
					$position = 100/(54/4 + 3)*$cnt;
						$handimg_prop = array(
							'src' => 'assets/img/cards/club_01.png',
							'width' => '70',
							'height' => 'auto',
							'class' => "img $cnt",
							'style' => "z-index:$cnt",
							'style' => "left:$position%"
						);
					echo img($handimg_prop);
				} ?>
		</div>
		<div class="status">
			<div class="icon">
				<?php
				$icon_prop = array(
							'src' => 'assets/img/testicon.png',
							'width' => '50',
							'height' => 'auto',
							'class' => "img $cnt",
							'style' => "z-index:$cnt",
							'style' => "left:$position%"
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
</html>