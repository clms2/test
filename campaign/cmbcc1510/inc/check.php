<?php 
session_name('CMBCC201510');
!isset($_SESSION) && session_start();

// 记住推荐人
if(isset($_GET['hash']) && strlen($_GET['hash']) == 13){
	$_SESSION['hash'] = addslashes($_GET['hash']);
}
// 未登陆过
if(!isset($_SESSION['openid'])){
	// 授权
	header('location:auth.php');
	exit();
}
