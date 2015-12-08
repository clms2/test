<?php 
require 'inc/check.php';
require 'inc/conn.php';
require 'inc/func.php';
require 'inc/CMB.class.php';

// 假如不是从index跳过来的就再判断下是否关注  header location过来的没有referer？？。。
// $refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if(!DEBUG){
    if(empty($refer) || stripos($refer, 'index.php') === false){
        $bindinfo = CMB::getBindInfo($_SESSION['openid']);
        $_SESSION['isbind'] = $bindinfo['isBind'] == 2 ? true : false;
        // 未关注,在点击开始寻宝的时候跳转
        if($bindinfo['isBind'] === '0'){
            $_SESSION['need_guanzhu'] = 1;
        }else{
            unset($_SESSION['need_guanzhu']);
        }
    }
}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" /> 
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no"/>
<title>70万神秘海底宝藏</title>
<link rel="stylesheet" href="css/css.css"/>
<link rel="stylesheet" href="css/animation.css"/>
<?php if (isset($_SESSION['need_guanzhu'])): ?>
<script type="text/javascript">
    var need_guanzhu = '<?php  echo GUANZHU?>';
</script>
<?php endif; ?>
</head>
<body>
<div class="guide">
	<img src="images/jijiehao.png" class="jijiehao">
    <img src="images/guide-text.png" class=" guide-text">
    <img src="images/paopao.png" class="paopao">
	<div class="shuoming"></div>
    <div class="kaishixunbao"><img src="images/pop-form-btn.png" style="width:100%"></div>
</div>
<div class="fix"></div>

<!--规则介绍-->
<?php include 'rule.html'; ?>
<!--规则介绍-->

<!--设置藏保-->
<?php include 'setplace.html'; ?>
<!--设置藏保-->

<!--分享-->
<div class="fenxiang"><img src="images/fenxiang.png"></div>
<!--分享-->

<script src="js/jquery-1.11.1.min.js"></script>
<script src="/js/jweixin-1.0.0.js"></script>
<script src="js/women.js?v=110"></script> 

</body>
</html>
