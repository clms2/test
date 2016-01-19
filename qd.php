<?php 
date_default_timezone_set('PRC');
header('content-type:text/html;charset="utf-8"');
define('COOKIEFILE', $_SERVER['DOCUMENT_ROOT'].'/cookie.txt');
define('LOGDIR',$_SERVER['DOCUMENT_ROOT'].'/log' );
!file_exists(COOKIEFILE) && touch(COOKIEFILE);
!is_dir(LOGDIR) && mkdir(LOGDIR, 0777, 1);

$debug = 1;// 记录日志
$nosend = false;// true:不发送请求,仅测试
$login = 'http://vmprncs02:8020/User/Login';
$offurl = 'http://vmprncs02:8020/Home/OffDutyCheck';
$data = array(
	'PassWord' => 'Aa654321',
	'SavePwd' => 'true',
	'UserName' => 'd1chix'
);
$sleepday = array(0, 6);// 周末
// $sleepdate = array('2016-01-01');

$time_range = array(
	// 8:45~53:0~59
	'morning' => array(8, array(45,53), array(0,59), 'endtime'=>'9:00:00'),
	// evening off
	'evening' => array('starttime'=>'18:01:00', 'endtime' => '18:30:00')
);

// --end config

ignore_user_abort(1);
set_time_limit(0);

addlog('start ..');
addlog('sleepday:' . implode(',', $sleepday));
register_shutdown_function(function(){
	addlog('shutdown ..');
});

$session = array();
while(1){
	$t = time();
	$w = date('w');
	if(in_array($w, $sleepday)){
		addlog("date(w):{$w} sleep one day start ..");
		sleep(86400+10*60);// 加10分钟 确保不会睡3晚
		addlog('sleep one day end.');
		continue;
	}
	if(empty($session) || date('d') != $session['day']){
		addlog('create random param');
		$session['day'] = date('d');
		unset($session['cleared']);

		$range_morning = $time_range['morning'];
		$i = mt_rand($range_morning[1][0], $range_morning[1][1]);// 分
		$s = mt_rand($range_morning[2][0], $range_morning[2][1]);// 秒
		$morning = array(strtotime("{$range_morning[0]}:{$i}:{$s}"), strtotime($range_morning['endtime']));

		$range_evening = $time_range['evening'];
		$evening = array(strtotime($range_evening['starttime']), strtotime($range_evening['endtime']));

		if($debug){
			addlog('morning check in start time：'.date('Y-m-d H:i:s', $morning[0]));
			addlog('morning check in end time：'.date('Y-m-d H:i:s', $morning[1]));
			addlog('evening check out start time：'.date('Y-m-d H:i:s', $evening[0]));
			addlog('evening check out end time：'.date('Y-m-d H:i:s', $evening[1]));
		}
	}

	// 是否需要重新获取cookie，(cookie为空||cookie失效)&&在正常时间内(要是凌晨1点请求就炸了~)
	$ck = file_get_contents(COOKIEFILE);
	$need_req = false;
	if($t > $morning[0] && $t < $evening[1]){
		if(!empty($ck)){
			preg_match('#\d{10}#sU', $ck, $m);
			if(!empty($m[0]) && ($t-10) > $m[0]){
				$need_req = true;
			}
		}else{
			$need_req = true;
		}
	}
	if($need_req){
		if($debug) addlog('send request loginurl for cookie ..');
		if(!$nosend) curl_send($login, $data);
	}

	// check in
	if(!isset($session['morning']) && $t > $morning[0] && $t < $morning[1]){
		$session['morning'] = 1;
		if($debug){
			addlog('mo0:'. date('Y-m-d H:i:s', $morning[0]));
			addlog('mo1:'. date('Y-m-d H:i:s', $morning[1]));
			addlog('send request loginurl for morning ..');
		}
		if(!$nosend) curl_send($login, $data);
	}

	// off
	if(!isset($session['evening']) && $t > $evening[0] && $t < $evening[1]){
		$session['evening'] = 1;
		if($debug){
			addlog('ev0:'.date('Y-m-d H:i:s', $evening[0]));
			addlog('ev1:'.date('Y-m-d H:i:s', $evening[1]));
			addlog('send request offurl for evening ..');
		}
		if(!$nosend) curl_send($offurl, array('q'=>1));
	}

	// 一天已经过去啦
	if(!isset($session['cleared']) && $t > $evening[1]){
		$session['cleared'] = 1;
		if($debug) addlog('unset flag');
		unset($session['morning']);
		unset($session['evening']);
		// todo.. sleep(一整晚);
	}

	$sleeptime = 5*60;
	// 快到off的时候(17:54-17:59 18:00-18:04)改为sleep 60
	// todo .. 平时sleep时间长些
	$h = date('H', $t);
	if($h == '17' || $h == '18'){
		$i = intval(date('i', $t));
		if(($i >= 54 && $i <= 59) || ($i >= 0 && $i <= 4)){
			$sleeptime = 60;
		}
	}

	sleep(mt_rand(60, $sleeptime));
}

function addlog($ct){
	$t = date('Y-m-d H:i:s');
	$f = 'log_' . date('Ymd') . '.txt';
	if($GLOBALS['nosend']) $f = 'test_send.log';// 测试日志
	file_put_contents(LOGDIR . "/{$f}", "{$t}  {$ct}\r\n", FILE_APPEND);
}


function curl_send($url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    	'Accept-Encoding: gzip, deflate',
    	'Connection: keep-alive',
    	'Host: vmprncs02:8020',
    	'Referer: http://vmprncs02:8020/User/Login',
    	'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0'
	));
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEFILE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEFILE);
    if (isset($data)) {
        $data = is_array($data) ? http_build_query($data) : $data;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}
