<?php
include "Qrcode.class.php";
$qr = new Qrcode();
//$qr->jpg('你好');   //输出jpg格式二维码图片
$qr->png('http://test.com');     //输出png格式二维码图片
