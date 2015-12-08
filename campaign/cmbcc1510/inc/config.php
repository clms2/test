<?php 
date_default_timezone_set('Asia/Shanghai');
$host = $_SERVER['HTTP_HOST'];

// 是否本地调试
define('DEBUG', 0);

if(stripos($host, 'cignacmb') !== false && stripos($host, 'test.cignacmb') === false){
	$isdev = false;
}else{
	// define('ISDEV', true);
	$isdev = true;
}
if($isdev){
	//验证是否绑定
	define('CHECKBIND', 'https://pointbonustest.dev.cmbchina.com/IMSPActivities/up/isBind');
	$redirect = 'http://apitest.joying.com/auth/j/u/108-s.html?c=';
	$auth_url = 'http://test.cignacmb.com/campaign/mc/cmbcc/201510/auth.php?a=getcode';
	$redirect = urlencode($redirect);
	$auth_url = urlencode($auth_url);
	$appid = 'wxd9137161bc8f9ca9';
}else{
	define('CHECKBIND', 'https://pointbonus.cmbchina.com/IMSPActivities/up/isBind');
	$redirect = 'http://api.joying.com/auth/j/u/108-s.html?c=';
	$auth_url = 'http://m.cignacmb.com/campaign/mc/cmbcc/201510/auth.php?a=getcode';
	$redirect = urlencode($redirect);
	$auth_url = urlencode($auth_url);
	$appid = 'wx619991cc795028f5';
}
// 获取openid、昵称接口
define('AUTHURL', "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$redirect}{$auth_url}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect");
// 绑定页地址
define('BINDURL', 'http://xyk.cmbchina.com/LatteSubsite/wx/20150727/20151022b.html');
// 关注页地址
define('GUANZHU', 'http://xyk.cmbchina.com/LatteSubsite/wx/20150727/20151022a.html');

// 藏宝点配置
$treasure_map = array(
	'0' => 'shayu',
	'1' => 'yu',
	'2' => 'meirenyu',
	'3' => 'zhangyu',
	'4' => 'ankang',
	'5' => 'haima',
	'6' => 'pangxie',
	'7' => 'wugui',
	'8' => 'haixing',
	'9' => 'bangke'
);

// 活动时间
$ac_duration = array(
	'start' => strtotime('2015-10-26 00:00:00'),
	'end'   => strtotime('2015-11-24 23:59:59')
);

// 保险额可能情况,array(航意,陆意,水意)
/*$baoxian = array(
	0 => array(
		array(15,3,3),
		array(10,2,2),
		array(5,1,1)
	),
	1 => array(
		array(5,1,1),
		array(5,1,1),
		array(20,4,4)
	),
	2 => array(
		array(10,2,2),
		array(10,2,2),
		array(10,2,2)
	),
);*/

// 数据库配置
$dbcfg = array();
// localhost
if($_SERVER['REMOTE_ADDR'] == '127.0.0.1'){
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
$dbcfg['pre'] = 'tb_treasure_';
