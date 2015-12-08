<?php 
require_once 'config.php';
require 'Mysql.class.php';

if(!$isdev && (/*time() < $ac_duration['start'] ||*/ time() > $ac_duration['end'])){
	header('location:end.html');exit;
	header('content-type:text/html;charset="utf-8"');
	echo '感谢您的关注，此次活动已结束，敬请期待抽奖结果。<br>十个工作日内我们会制作中奖结果的jpg，届时再更新中奖结果。';
	exit();
}

$db = new Mysql($dbcfg);
