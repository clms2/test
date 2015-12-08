<?php 
date_default_timezone_set('Asia/Shanghai');
$host = $_SERVER['HTTP_HOST'];

if(stripos($host, 'cignacmb') !== false && stripos($host, 'test.cignacmb') === false){
	define('ISDEV', false);
}else{
	define('ISDEV', true);
}

// 登陆地址
$loginurl = array(
	'test'   => 'https://10.140.5.77/login.json',
	'online' => 'https://member.cignacmb.com/login.json'
);

// 抽奖配置
$ltcfg = array(
	'daytime' => 3,//每天抽奖次数
	'start'   => strtotime('2015-09-01 00:00:00'), //开始日期
	'end'     => strtotime('2015-09-30 23:59:59'),//结束日期
);
// 获奖几率和角度定义,v:中奖权重，jx:前台js判断是否中奖/奖品reward表id,min/max:转盘角度
$prize_arr = array( 
	'0' => array('min'=>2,  'max'=>50, 'v'=>10,'jx'=>2,'prize'=>'Bongxx 健康手环'), 
	'1' => array('min'=>56, 'max'=>100,'v'=>10,'jx'=>4,'prize'=>'30元话费'), 
	'2' => array('min'=>106,'max'=>150,'v'=>20,'jx'=>3,'prize'=>'50元京东购物卡'), 
	'3' => array('min'=>156,'max'=>200,'v'=>29974,'jx'=>5,'prize'=>'谢谢参与'), 
	'4' => array('min'=>206,'max'=>250,'v'=>10,'jx'=>4,'prize'=>'30元话费'), 
	'5' => array('min'=>256,'max'=>300,'v'=>2,'jx'=>1,'prize'=>'Apple Watch'), 
	'6' => array('min'=>310,'max'=>352,'v'=>29974,'jx'=>5,'prize'=>'谢谢参与')
);

// 数据库配置
$dbcfg = array();
// localhost
if($host == 'test.com'){
	$dbcfg['dbhost'] = '127.0.0.1';
	$dbcfg['dbuser'] = 'root';
	$dbcfg['dbpwd']  = '123456';
	$dbcfg['dbname'] = 'test';
}else{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/sites/default/settings.php';
	$temparr = $databases['activity']['default'];
	$dbcfg['dbhost'] = $temparr['host'];
	$dbcfg['dbuser'] = $temparr['username'];
	$dbcfg['dbpwd']  = $temparr['password'];
	$dbcfg['dbname'] = $temparr['database'];
	$dbcfg['dbport'] = $temparr['port'];
}
$dbcfg['pre'] = 'tb_zhuanpan_';
