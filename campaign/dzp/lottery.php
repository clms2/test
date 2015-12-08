<?php
require 'public/inc/conn.php';// 配置文件和数据库连接
header("Content-Type: text/html; charset=utf-8");
!isset($_SESSION) && session_start();

$rs   = array();
$time = time();
$day  = date('md', $time);

// 活动已结束/未开始
if(!ISDEV && ($temp = $time < $ltcfg['start'] || $time > $ltcfg['end'])){
	$rs['result']   = 4;
	$rs['hasstart'] = $temp ? 0 : 1;
	ex($rs);
}

// 没有登陆
if(empty($_SESSION['uid'])){
	$rs['result'] = 2;
	ex($rs);
}
$uid = $_SESSION['uid'];

// 用户信息/当天抽奖次数信息
$user = $db->getOneAssoc('user', "id={$uid}");
$dayinfo = $db->getOneAssoc('info_user', "uid={$uid} and `day`='{$day}'");

// 每天次数+额外次数
$totalcount = $ltcfg['daytime'] + $user['extra_time'];
// 次数不够
if(!empty($dayinfo) && $totalcount <= $dayinfo['current']){
	$rs['result'] = 3;
	ex($rs);
}

// 已抽奖次数+1
if(empty($dayinfo)){
	$db->insert('info_user', array('day'=>$day, 'uid'=>$uid));
}else{
	// 如果每天抽奖次数已达到限制次数，那么消耗额外次数
	if($dayinfo['current'] == $ltcfg['daytime']){
		$db->update('user', array('extra_time'=>'1--'), "id={$uid}");
	}else{
		$db->update('info_user', array('current'=>'1++'), "`day`='{$day}' and uid={$uid}");
	}
}
// 剩余次数
$current = empty($dayinfo['current']) ? 0 : $dayinfo['current'];
$leftcount = $totalcount - $current - 1;

// 开始抽奖
$row = array(
	'uid'     => $uid,
	'uname'   => $user['uname'],
	'addtime' => $time,
	'mobile'  => $user['mobile'],
	'day'     => $day,
	'ip'      => $_SERVER['REMOTE_ADDR']
);

foreach ($prize_arr as $key => $val) { 
	$arr[$key] = $val['v']; 
}
$rid = getRand($arr); //根据概率获取奖项id
$res = $prize_arr[$rid]; //中奖项 
//中奖
if($res['jx'] != 5){
	$reward_id = $res['jx'];

	$db->lock('reward');
	$reward = $db->getOneAssoc('reward', "id={$reward_id}");
	// 读不到奖品表记录
	if(empty($reward)){
		$db->unlock();
		ex(array('result'=>110));
	}
	$max = $reward['max'];// 该奖品最大送出数

	// 该奖品超过预计数量
	if($reward['current'] >= $max){
		// 那就谢谢惠顾了
		$res = $prize_arr[6];
	}else{
		// 中奖
		$db->update('reward', array('current'=>'1++'), "id={$reward_id}");
		$row['reward_id'] = $reward_id;
		$row['reward']    = $res['prize'];
	}
	$db->unlock();
}
if(!$db->insert('info', $row)){
	ex(array('result'=>120));
}

$result['angle'] = mt_rand($res['min'], $res['max']); //随机生成一个角度 
$result['prize'] = $res['prize'];
$result['jx'] = $res['jx'];
$result['cs'] = $leftcount;
$result['result'] = 1;
		 
ex($result); 

function ex($ar){
	exit(json_encode($ar));
}

function getRand($proArr) { 
	$result = ''; 
	//概率数组的总概率精度 
	$proSum = array_sum($proArr); 
	//概率数组循环 
	foreach ($proArr as $key => $proCur) { 
		$randNum = mt_rand(1, $proSum); 
		if ($randNum <= $proCur) { 
			$result = $key; 
			break; 
		} else { 
			$proSum -= $proCur; 
		}
	} 
	unset ($proArr); 
	return $result; 
} 
