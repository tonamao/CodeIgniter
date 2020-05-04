<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ajaxのサンプル</title>
    <script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
<script>
$(function(){
    $('button').on('click', function(){

        //クリックされたボタンのid属性を取得
        var button_id = $(this).attr('id');

        //Ajax	
        $.ajax({
            type: 'POST', // HTTPメソッド（CodeIgniterだとgetは捨てられる）
            url: 'http://localhost/test/ajax/test',//リクエストの送り先URL（適宜変える）
            data: {'number': button_id}, //サーバに送るデータ。JSのオブジェクトで書ける
            dataType: 'json',//サーバからくるデータの形式を指定

            //リクエストが成功したらこの関数を実行！！
            success: function(data){
                // alert('フレームワーク：' + data.framework + ', 言語：' + data.lang);
                document.getElementById("test").innerHTML = 'フレームワーク：' + data.framework + ', 言語：' + data.lang;
            }
            // error: function(err){
            //     alert('失敗！　：' + err);
            // }
        });
    });
});
</script>
</head>
<body>
    <button id="0">ボタン 1番</button>
    <button id="1">ボタン 2番</button>
	<div id="test"></div>
</body>
</html>
