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
      <?php for($i = 0; $i < 3; $i++) {
      	$num = ($i + 1);?>
		    <div class="cpu <?php echo $num ?>">CPU<?php echo $num ?>
				<div class="hand">
					<?php foreach ($backs as $back): ?>
						<?php 
						$card_prop = array(
											'src' => $back,
											'width' => '40',
											'height' => 'auto'
									);
						echo img($card_prop); ?>
					<?php endforeach; ?>
				</div>
			</div>
      <?php }?>
	</div>
	<div class="game-area">
		<div class="cards">
	      	<?php for($i = 0; $i < 2; $i++) {?>
				<div class="card img" style="z-index:<?php echo $cnt = $i + 1;?>; left:<?php echo $left = 42 + 4*$cnt; ?>%; top:<?php echo $top = 18 + 8*$cnt ?>%;">
					<?php 
					$cnt = $i + 1;
					$position = 100/(54/4 + 10)*$cnt;
						$card_prop = array(
										'src' => 'assets/img/cards/spade_1.png',
										'width' => '90',
										'height' => 'auto'
									);
					echo img($card_prop); ?>
				</div>
	      	<?php }?>
	    </div>
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
									'width' => '90',
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
</html>
