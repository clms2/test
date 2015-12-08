<?php 
require_once 'public/inc/conn.php';
$act = !empty($_GET['act']) ? $_GET['act'] : '';

switch ($act) {
	case 'getLuckList':
		$records = $db->getAssoc('info', "reward_id != 0 order by id desc limit 10", "IF(mobile='', IF(CHAR_LENGTH(uname)<3, CONCAT(SUBSTRING(uname,1,1),'*'), CONCAT(SUBSTRING(uname,1,1), LPAD('',CHAR_LENGTH(uname)-2,'*'),SUBSTRING(uname,-1))), CONCAT(SUBSTRING(mobile,1,3),'****',SUBSTRING(mobile,-4))) AS mobile,reward");
		echo json_encode($records);
		exit;
	break;
	case 'getMyReward':
		!isset($_SESSION) && session_start();
		$uid = empty($_SESSION['uid']) ? '' : $_SESSION['uid'];
		if(!$uid) exit('0');
		$records = $db->getAssoc('info', "uid={$uid} and reward_id != 0 order by id desc", 'reward,DATE_FORMAT(FROM_UNIXTIME(addtime),"%Y/%m/%d") as date');
		echo json_encode($records);
		exit;
	break;
	
}
