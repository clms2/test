<?php
/**
 * 获取授权码及access token
 */
define('APP_KEY', '6B89F9318D5353C5067B33A5E591A147');
define('APP_SECRET', 'e7407d0bd867429b8397e488565246c8');
define('RED_URL', 'http://jufanqian.com/jd/auth.php?act=token');
define('STATE', '');
define('CODE_URL', 'http://auth.360buy.com/oauth/authorize?'); // 请求授权码地址,正式地址：去掉sandbox
define('TOKEN_URL', 'http://auth.360buy.com/oauth/token'); // 请求token地址

// 获取token
if (isset($_GET['act']) && $_GET['act'] == 'token') {
    $arr[] = 'client_id=' . APP_KEY;
    $arr[] = 'scope=read';
    $arr[] = 'code=' . $_GET['code'];
    $arr[] = 'grant_type=authorization_code';
    $arr[] = 'client_secret=' . APP_SECRET;
    $arr[] = 'redirect_uri=' . RED_URL;
    if (!function_exists('curl_send')) {
        function curl_send($url, $data = array()) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            if (!empty($data)) {
                $data = is_array($data) ? http_build_query($data) : $data;
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $return = curl_exec($ch);
            curl_close($ch);
            return $return;
        }
    }
    $res = curl_send(TOKEN_URL, implode('&', $arr));
    $res = iconv('GBK', 'utf-8//IGNORE//TRANSLIT', $res);
    $res = json_decode($res, true);
    if (isset($res['access_token'])) {
        !isset($_SESSION) && session_start();
        $_SESSION['token_ok'] = 1;
        echo $_SESSION['access_token'] = $res['access_token']; // 最好存到数据库中
        $_SESSION['refresh_token'] = $res['refresh_token'];
        header('location:index.php');
    } else {
        echo '授权失败,错误信息：<br />';
        echo '<pre>';
        print_r($res);
        exit();
    }
} // 获取授权码
else {
    $arr['client_id'] = APP_KEY;
    $arr['scope'] = 'read';
    $arr['response_type'] = 'code';
    $arr['redirect_uri'] = RED_URL;
    $arr['state'] = STATE;
    
    $url = CODE_URL . http_build_query($arr);
    echo "<a href='{$url}'>授权登陆</a>";
}
