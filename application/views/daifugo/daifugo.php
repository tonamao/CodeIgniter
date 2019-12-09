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
      <?php for($i = 1; $i < count($all_hands); $i++) {?>
		    <div class="cpu <?php echo $i ?>">CPU<?php echo $i ?>
				<div class="hand">
					<?php foreach ($all_hands[$i] as $key => $idPath) :?>
						<?php foreach ($idPath as $id => $path) :?>
							<?php $card_prop = array(
												'src' => $back,
												'id' => $id,
												'width' => '40',
												'height' => 'auto'
										);
							echo img($card_prop); ?>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</div>
			</div>
      <?php }?>
	</div>
	<div class="game-area">
		<div class="cards" id="game-cards">
			<?php $cnt = 0;?>
	      	<?php foreach ($game_area_cards as $key => $cardIds) :?>
	      		<?php 
	      			$index = $cnt + 1;
	      			$l = 0;
	      			$t = 0;
	      			if ($index % 2 == 0) {//2
	      				$l = 50;
	      				$t = 24;
	      				if ($index % 4 == 0) {//4
	      					$l = 43;
	      					$t = 30;
	      				}
	      			} else {//1
	      				$l = 45;
	      				$t = "auto";
	      				if (($index + 1) % 4 == 0) {//3
	      					$l = 48;
	      					$t = 36;
	      				}
	      			}
	      		?>
	      		<?php $leftCnt = 0; ?>
	      		<?php foreach ($cardIds as $key => $pathArray) :?>
	      			<?php
	      				$length = count($pathArray);
	      				$firstLeft = $l - (($length - 1) / 2) + $leftCnt; 
	      			?>
	      			<?php foreach ($pathArray as $cardId => $path) :?>
						<div class="card img" id="location-<?php echo $index;?>"
							style="z-index:<?php echo $index;?>;
									left:<?php echo $firstLeft.'%'; ?>;
									top:<?php echo $t == 'auto' ? $t : $t.'%';  ?>;">
							<?php
							$card_prop = array(
											'src' => $path,
											'id' => 'img-location-'."$index",
											'width' => '90',
											'height' => 'auto'
										);
							echo img($card_prop); ?>
						</div>
	      			<?php endforeach; ?>
	      			<?php $leftCnt += 1; ?>
	      		<?php endforeach; ?>
	      		<?php $cnt++;?>
	      	<?php endforeach; ?>
	    </div>
	</div>
	<div class="user-area">
		<?php echo form_open('daifugo/put'); ?>
            <input type="submit" name="put" id="put" class="btn-square-so-pop" value="出す">
            <input type="hidden" name="hidden-put" id="hidden-put">
        </form>
		<?php echo form_open('daifugo/pass'); ?>
            <input type="submit" name="pass" id="pass" class="btn-square-so-pop" value="パス">
            <input type="hidden" name="hidden-pass" id="hidden-pass">
        </form>
            <!-- <form class="btn" action="#" method="post">
                <a href="#" id="put" class="btn-square-so-pop">出す</a>
                <a href="#" id="pass" class="btn-square-so-pop">パス</a>
            </form> -->
	<div class="hand" id="user-hand">
			<?php $i = 0;
			foreach ($all_hands[0] as $key => $idPath): ?>
				<?php foreach ($idPath as $id => $path) :?>
					<?php $card_prop = array(
										'src' => $path,
										'id' => 'img'.$id,
										'class' => 'img'.$i++,
										'width' => '90',
										'height' => 'auto'
								);
					echo img($card_prop); ?>
				<?php endforeach; ?>
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
	<div>テスト</div>
	<div>
		<div>CPU1 <?php echo count($all_hands[1])?>枚</div>
			<?php foreach ($all_hands[1] as $key => $idPath): ?>
				<?php foreach ($idPath as $id => $path): ?>
					<?php echo $path; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<div>CPU2 <?php echo count($all_hands[2])?>枚</div>
			<?php foreach ($all_hands[2] as $key => $idPath): ?>
				<?php foreach ($idPath as $id => $path): ?>
					<?php echo $path; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<div>CPU3 <?php echo count($all_hands[3])?>枚</div>
			<?php foreach ($all_hands[3] as $key => $idPath): ?>
				<?php foreach ($idPath as $id => $path): ?>
					<?php echo $path; ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
	</div>
	<div>テスト</div>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/daifugo.js"></script>
</body>
</html>
