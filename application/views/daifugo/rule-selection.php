<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>大富豪</title>
	<?php echo link_tag('/assets/css/rule.css'); ?>
	<link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

</head>
<body>
	<br>
	<br>
	<br>
	<div><a href="<?php echo site_url('daifugo/start') ;?>" class="btn-square">START</a></div>
	<div>
		<!-- <?php foreach ($gameInfo as $key => $game) :?>
			<div class="game">
				<div class="icon">
					<?php
					$icon_prop = array(
								'src' => $game['img_path'],
								'width' => '100',
								'height' => 'auto'
							);
					echo img($icon_prop);?>	
				</div>
				<div class="info">
					<p class="name">
						<a href="<?php echo site_url('daifugo/rule') ;?>" class="btn-square"><?php echo $game['display_name']?></a>
					</p>
					<p class="current-class"><?php echo $game['description']?></p>
				</div>
			</div>
		<?php endforeach; ?> -->
	</div>
</body>
</html>