<?php 
require 'public/inc/conn.php';
session_start();
header('content-type:text/html;charset=utf-8');

$type = getvar('type');// 登陆类型
$rec = empty($_SESSION['rec']) ? '' : $_SESSION['rec'];
$openid = empty($_SESSION['openid']) ? '' : $_SESSION['openid'];
// 假如openid是分享链接里的,那么确保新增用户时不会把别人的openid写入到该用户里
if(!empty($openid) && $db->exists('user', "openid='{$openid}'")){
	$openid = '';
}

$msg = '';
$data = array('type'=>$type, 'source'=>'OW');// source区分官网
$url = ISDEV ? $loginurl['test'] : $loginurl['online']; // form提交地址
// $url = $loginurl['online'];
$isajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? true : false;
$result = array();
switch ($type) {
	// 手机号登陆
	case 'mobile':
	do{
		$imgcode = getvar('imgCodeVal');
		$code = getvar('validateCode');
		$mobile = getvar('userName');
		if(empty($imgcode) || empty($code) || !preg_match('#^\d{4}$#', $imgcode) || !preg_match('#^\d{4}$#', $code) || !preg_match('#^\d{11}$#', $mobile)){
			$msg = '请输入正确的手机号或验证码';
			$result['code'] = -2;
			$result['msg']  = $msg;
			break;
		}
		$data['imgValidateCode'] = $imgcode;
		$data['validateCode'] = $code;
		$data['userName'] = $mobile;
		$ret = curl($url, $data);
		if(!$ret || $ret == 'err'){
			$msg = '系统错误';
			$result['code'] = -1;
			$result['msg']  = $msg;
			break;
		}
		$ret = json_decode($ret, 1);
		// 登陆失败
		if($ret['code'] != '0'){
			$msg = $ret['message'];
			$result['code'] = 0;
			$result['msg']  = $msg;
			break;
		}
		// 登陆成功 检测是否已存在该用户
		$user = $db->getOneAssoc('user', "mobile='{$mobile}'");
		// 新用户
		if(empty($user)){
			$user = array(
				'openid'  => $openid,
				'uname'   => '',
				'mobile'  => $mobile,
				'loginip' => $_SERVER['REMOTE_ADDR'],
				'addtime' => time()
			);
			// 有推荐人
			if(!empty($rec)){
				$user['follow_id'] = $rec;
				// 增加1次抽奖次数
				$db->update('user', array('extra_time'=>'1++'), "id={$rec}");
			}
			if(!$db->insert('user', $user)){
				$msg = '数据入库失败';
				$result['code'] = 1;
				$result['msg']  = $msg;
				break;
			}
			$_SESSION['uid'] = $db->last_id();
			$result['code']  = 2;
			$result['rec']   = $_SESSION['uid'];
			break;
		}

		$db->update('user', array('loginip'=>$_SERVER['REMOTE_ADDR']), "mobile='{$mobile}'");
		$_SESSION['uid'] = $user['id'];
		$result['code']  = 2;
		$result['rec']   = $_SESSION['uid'];

	}while(0);

	break;
	// 账号密码登陆
	case 'password':
	do{
		$uname = getvar('userName');
		$pwd = getvar('password');

		$user = $db->getOneAssoc('user', "uname = '{$uname}'");
		// 本地库已存在
		if(!empty($user)){
			if($user['pwd'] != getpwd($pwd)){
				$msg = '密码错误';
				$result['code'] = 0;
				$result['msg']  = $msg;
				break;
			}
			$_SESSION['uid'] = $user['id'];
			$result['code']  = 2;
			$result['rec']   = $_SESSION['uid'];
			break;
		}
		// 否则发送请求到会员中心判断是否存在
		$data['userName'] = $uname;
		$data['password'] = $pwd;
		$ret = curl($url, $data);
		if(!$ret || $ret == 'err'){
			$msg = '系统错误';
			$result['code'] = -1;
			$result['msg']  = $msg;
			break;
		}
		$ret = json_decode($ret, 1);
		// 失败
		if($ret['code'] != '0'){
			$msg = $ret['message'];
			$result['code'] = 1;
			$result['msg']  = $msg;
			break;
		}

		// 成功 插入user表
		$user = array(
			'openid'  => $openid,
			'uname'   => $uname,
			'pwd'     => getpwd($pwd),
			'loginip' => $_SERVER['REMOTE_ADDR'],
			'addtime' => time()
		);
		// 有推荐人
		if(!empty($rec)){
			$user['follow_id'] = $rec;
			// 增加1次抽奖次数
			$db->update('user', array('extra_time'=>'1++'), "id={$rec}");
		}
		if(!$db->insert('user', $user)){
			$msg = '数据入库失败';
			$result['code'] = 3;
			$result['msg']  = $msg;
			break;
		}
		$_SESSION['uid'] = $db->last_id();
		$result['code']  = 2;
		$result['rec']   = $_SESSION['uid'];
	}while(0);

	break;
	default:
		exit('denied');
}

// ajax响应
if($isajax){
	echo json_encode($result);
	exit;
}
// form响应
if(!empty($msg)){
	echo "<script>alert('{$msg}');location.href='index.php'</script>";
	exit;
}
// 登陆成功，带上推荐人id
echo "<script>location.href='index.php?rec={$_SESSION['uid']}'</script>";


// 用户密码加密方式
function getpwd($origin){
	return md5(str_rot13($origin).'_qwjelw#');
}

function getvar($param){
	return !empty($_REQUEST[$param]) ? $_REQUEST[$param] : '';
}

function curl($url, $data, $ispost = false){
	$rs = '';
	$data = is_array($data) ? http_build_query($data) : $data;
	if(function_exists('curl_init')){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, $ispost ? 1 : 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$rs = curl_exec($ch);
		// echo '<pre>';
		// var_dump(curl_error($ch));
		// print_r(curl_getinfo($ch));
		curl_close($ch);

	}elseif(extension_loaded('openssl')){
		$ctx = array(
			'http'=>array(
				'method'=> $ispost ? 'POST' : 'GET',
				'header'=>"User-Agent:{$_SERVER['HTTP_USER_AGENT']}"
			));
		$rs = file_get_contents($url.'?'.$data, false, stream_context_create($ctx));
	}else{
		return 'err';
	}

	return $rs;
}
