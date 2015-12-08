<?php 
/**
 * 查询某个用户是否中奖接口
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST') exit('Access denied.');

require 'public/inc/conn.php';

$ret = array();
$token = 'e89d6af148e45d3d6cd';

$user = getvar('user');
$sign = getvar('sign');

if(empty($user) || empty($sign)){
	$ret['code'] = -1;
	ex($ret);
}
if($sign != md5($token.$user)){
	$ret['code'] = -2;
	ex($ret);
}


$info = $db->getAssoc('info', "(uname='{$user}' or mobile='{$user}') and reward_id !=0 order by id desc", 'reward,addtime');
if(empty($info)){
	$ret['code'] = -3;
	ex($ret);
}

$ret['code'] = 1;
$ret['data'] = $info;
ex($ret);



function ex($ar){
	exit(json_encode($ar));
}

function getvar($k){
	return isset($_POST[$k]) ? $_POST[$k] : '';
}

