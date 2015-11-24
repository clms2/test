<?php 
date_default_timezone_set('PRC');
header('content-type:text/html;charset="utf-8"');
define('COOKIEFILE', $_SERVER['DOCUMENT_ROOT'].'/cookie.txt');
define('LOGDIR',$_SERVER['DOCUMENT_ROOT'].'/log' );
!file_exists(COOKIEFILE) && touch(COOKIEFILE);
!is_dir(LOGDIR) && mkdir(LOGDIR, 0777, 1);

$debug = 1;// 记录日志
$nosend = true;// 不发送请求,仅测试
$login = 'http://vmprncs02:8020/User/Login';
$offurl = 'http://vmprncs02:8020/Home/OffDutyCheck';
$data = array(
	'PassWord' => 'Aa654321',
	'SavePwd' => 'true',
	'UserName' => 'd1chix'
);

$time_range = array(
	// 时分秒截止打卡时间,8:30~58:0~59
	'morning' => array(8, array(25,54), array(0,59), 'endtime'=>'9:00:00'),
	// 时分秒开始打卡时间,18:2~12:0~59
	'evening' => array(18, array(2, 12), array(0,59), 'starttime'=>'18:01:00')
);

// --end config

ignore_user_abort(1);
set_time_limit(0);

$session = array();
while(1){
	$t = time();
	if(empty($session) || date('d') != $session['day']){
		addlog('create random param');
		$session['day'] = date('d');
		unset($session['cleared']);

		$range_morning = $time_range['morning'];
		$i = mt_rand($range_morning[1][0], $range_morning[1][1]);// 分
		$s = mt_rand($range_morning[2][0], $range_morning[2][1]);// 秒
		$morning = array(strtotime("{$range_morning[0]}:{$i}:{$s}"), strtotime($range_morning['endtime']));

		$range_evening = $time_range['evening'];
		$i = mt_rand($range_evening[1][0], $range_evening[1][1]);
		$s = mt_rand($range_evening[2][0], $range_evening[2][1]);
		$evening = array(strtotime($range_evening['starttime']), strtotime("{$range_evening[0]}:{$i}:{$s}"));

		if($debug){
			addlog('上班开始打卡时间：'.date('Y-m-d H:i:s', $morning[0]));
			addlog('上班结束打卡时间：'.date('Y-m-d H:i:s', $morning[1]));
			addlog('下班开始打卡时间：'.date('Y-m-d H:i:s', $evening[0]));
			addlog('下班结束打卡时间：'.date('Y-m-d H:i:s', $evening[1]));
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

	// 上班
	if(!isset($session['morning']) && $t > $morning[0] && $t < $morning[1]){
		$session['morning'] = 1;
		if($debug){
			addlog('mo0:'. date('Y-m-d H:i:s', $morning[0]));
			addlog('mo1:'. date('Y-m-d H:i:s', $morning[1]));
			addlog('send request loginurl for morning<br>');
		}
		if(!$nosend) curl_send($login, $data);
	}

	// 下班
	if(!isset($session['evening']) && $t > $evening[0] && $t < $evening[1]){
		$session['evening'] = 1;
		if($debug){
			addlog('ev0:'.date('Y-m-d H:i:s', $evening[0]));
			addlog('ev1:'.date('Y-m-d H:i:s', $evening[1]));
			addlog('send request offurl for evening');
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

	sleep(mt_rand(60, 5*60));
}

function addlog($ct){
	$t = date('Y-m-d H:i:s');
	$f = 'log_' . date('Ymd') . '.txt';
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
