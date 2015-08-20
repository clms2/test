<?php
include_once 'Jdapi.class.php';
date_default_timezone_set('PRC');
define('APP_KEY', 'A39ABDB22FC499F62BAFB00D79C703D8');
define('APP_SECRET', 'df9add6fbd424cc383e1a1508d956b4b');
define('RED_URL', 'http://jufanqian.com/jd/auth.php?act=token');
define('STATE', '');
define('CODE_URL', 'http://auth.360buy.com/oauth/authorize?'); // 请求授权码地址,正式地址：去掉sandbox
/* if (!isset($_SESSION['token_ok'])) {
    include_once 'auth.php';
    exit();
} */

echo '<pre>';
$token = '46fe85c9-08e9-4271-93df-825b7df64392';

$jdapi = new Jdapi(APP_KEY, APP_SECRET,$token);
$param = &$jdapi->get_param();
/* $param['method'] = 'jingdong.union.promotiongood.query';
$param['keywords'] = '裤子';
$param['pageIndex'] = 1;
$param['pageSize'] = 1;
print_r($jdapi->getData());
exit; */

//1033984690
$url = 'http://www.360buy.com/product/1033984690.html';
$url = 28111927;
var_dump($url); 
echo '<br>';
$param['method'] = 'jingdong.union.goods.code.get';
$param['good_id'] = ($url);
print_r($jdapi->getData());

