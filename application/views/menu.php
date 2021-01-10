<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>大富豪</title>
    <?= link_tag('/assets/css/menu.css'); ?>
    <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">
</head>

<body>
    <h1>メニュー</h1>
    <div>
        <?php foreach ($game_info as $game) : ?>
            <div class="game">
                <div class="icon">
                    <?php
                    $icon_prop = [
                        'src' => $game['img_path'],
                        'width' => '100',
                        'height' => 'auto'
                    ];
                    echo img($icon_prop); ?>
                </div>
                <div class="info">
                    <p class="name">
                        <a href="<?= site_url('daifugo/rule'); ?>" class="btn-square"><?= $game['name'] ?></a>
                    </p>
                    <p class="current-class"><?php echo $game['description'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
