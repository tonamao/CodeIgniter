<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>大富豪</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <?php echo link_tag('/assets/css/rule.css'); ?>
    <link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

</head>
<body>
    <br>
    <br>
    <br>
    <div><a href="<?php echo site_url('daifugo/start') ;?>" class="btn-square">START</a></div>
    <div class="container">
        <div class="row">
            <?php if (isset($rule_info)) { ?>
                <?php foreach ($rule_info as $key => $rule) :?>
                    <div class="card col-lg-4;">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $rule['title'];?></h6>
                            <?php echo $rule['description'];?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
</body>
</html>
