<?php 
// 与drupal后台session保持一致
// 验证合法用户
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');

$lifetime = ini_get('session.gc_maxlifetime');

$id = isset($_GET['id']) ? $_GET['id'] : '';
if(empty($id)) exit('Access denied');
$ckid = isset($_COOKIE[$id]) ? $_COOKIE[$id] : '';
if(empty($ckid)) exit('页面已过期，请重新登录(code:1)');
// xx分钟未操作 自动过期
$time = $db->getOneField('sessions', "sid='{$ckid}'", 'timestamp');
if(empty($time) || ($time + $lifetime) < time()){ 
	exit('页面已过期，请重新登录(code:2)');
}else{
	// 就更新时间
	$time = time();
	$db->update('sessions', array('timestamp'=>$time), "sid='{$ckid}'");
	$domain = filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP) ? $_SERVER['SERVER_NAME'] : ".{$_SERVER['SERVER_NAME']}";
	setcookie($id, $ckid, $time + $lifetime, '/', $domain);
}
