<?php
date_default_timezone_set('Asia/Shanghai');
header('content-type:text/html;charset=utf-8');
if (!isset($_GET['cookie'])) {
    setcookie('testcookie', '123', time() + 60);
    header('location:?cookie=1');
}
if (!isset($_COOKIE['testcookie'])) {
    echo '您的浏览器需开启cookie功能才可正常使用。';
    exit();
} else {
    header('location:index.html');
}

?>