<?php 
/**
 * 获取openid
 */
require 'inc/config.php';
session_name('CMBCC201510');
session_start();
// 30天免验证
setcookie(session_name(), session_id(), time()+30*86400, '/');
// 已经取得了openid
if(isset($_SESSION['openid'])){
	header("location:index.php");
	exit();
}
// 获取授权
if(!isset($_GET['a'])){
	header('location:'.AUTHURL);
	exit();
}
$tuid     = isset($_GET['tuid']) ? $_GET['tuid'] : '';
$datetime = isset($_GET['datetime']) ? $_GET['datetime'] : '';
$nickname = isset($_GET['nn']) ? urldecode($_GET['nn']) : '***';
if(empty($tuid) || empty($datetime)){
	// 防止无限重定向..
	!isset($_SESSION['errornum']) && $_SESSION['errornum'] = 0;
	if(++$_SESSION['errornum'] == 3) exit('system error, please try later.(code:-1)');
	header('location:'.AUTHURL);
	exit();
}
// 授权成功
require 'inc/CMB.class.php';
require 'inc/conn.php';
$openid = CMB::getOpenId($tuid, $datetime);
$_SESSION['openid'] = $openid;
$_SESSION['uname'] = $nickname;
$user = $db->getOneAssoc('user', "openid='{$openid}'", 'id,isbind,uname');
if(empty($user)){
	$row = array(
		'openid' => $openid,
		'uname' => $nickname,
		'loginip' => $_SERVER['REMOTE_ADDR'],
		'addtime' => time()
	);
	if(!$db->insert('user', $row)){
		exit('system error, please try later.(code:-2)');
	}
	$_SESSION['uid'] = $db->last_id();
	$_SESSION['gottreasure'] = false;// 标识是否已领保障
}else{
	// 更新昵称
	if($nickname != '***' && $user['uname'] != $nickname)
		$db->update('user', array('uname'=>$nickname), "openid='{$openid}'");
	$_SESSION['uid'] = $user['id'];
	$_SESSION['gottreasure'] = $user['isbind'] ? true : false;
}
header("location:index.php");
exit();
