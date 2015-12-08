<?php 
require("hm.php");
$_hmt = new _HMT("f70be1b09b7a5549adbdb47b4335b259");
$_hmtPixel = $_hmt->trackPageView();


require 'inc/config.php';

// 30天免验证
session_name('CMBCC201510');
!isset($_SESSION) && session_start();
// session_unset();
// session_destroy();
// exit();

setcookie(session_name(), session_id(), time()+30*86400, '/');

if(DEBUG){
    $_SESSION['openid'] = 'testopenid';
    $_SESSION['uid'] = 3;
    $_SESSION['uname'] = '哈哈哈';
    $_SESSION['is_friend'] = 1;// 代表用好友计划
    !isset($_SESSION['viewd']) && $_SESSION['viewd'] = 0;
    // $_SESSION['viewd'] = 0;
    $_SESSION['isbind'] = 1;
    $_SESSION['hash'] = '562f210fc0890';
    !isset($_SESSION['gottreasure']) && $_SESSION['gottreasure'] = 0;
    $_SESSION['gottreasure'] = 0;
}

require 'inc/check.php';
require 'inc/conn.php';
require 'inc/func.php';
require 'inc/CMB.class.php';

// 该openid是否关注，每次都去请求~不存数据库，可能他中途取消关注什么的
if(!DEBUG){
    $bindinfo = CMB::getBindInfo($_SESSION['openid']);
    // print_r($bindinfo);exit;
    $_SESSION['isbind'] = $bindinfo['isBind'] == 2 ? true : false;
    // 未关注,在点击开始寻宝的时候跳转
    if($bindinfo['isBind'] === '0'){
        $_SESSION['need_guanzhu'] = 1;
    }else{
        unset($_SESSION['need_guanzhu']);
    }
}

// 没绑定就可以继续寻宝,已绑定且已领取就根据是否发起过藏宝进行跳转
if($_SESSION['isbind'] && !empty($_SESSION['gottreasure'])){
    $user = $db->getOneAssoc('user', "id={$_SESSION['uid']}", 'startnum');
    $url = $user['startnum'] ? 'lishi4.php' : 'lishi2.php';
    $url = !empty($_GET['hash']) ? "{$url}?hash={$_GET['hash']}" : $url;
    header("location:{$url}");
    exit();
}

// 通过分享进来的, 还没有进入过guide页面的进去guide一下
if(!isset($_GET['viewed'])){
    // 确保有效的藏宝计划,并记录好友设置的藏宝点
    if(!empty($_SESSION['hash'])){
        // 不能是他自己的藏宝计划
        $row = $db->getOneAssoc('user_set', "hash='{$_SESSION['hash']}' and uid != {$_SESSION['uid']}", 'place,uid');
        if(empty($row)){
            unset($_SESSION['hash'], $_SESSION['follow_id']);
        }else{
            $_SESSION['follow_id'] = $row['uid'];
            $_SESSION['place'] = $_SESSION['lastplace'] = json_decode($row['place'], 1);
            $_SESSION['is_friend'] = 1;
        }
    }
    $url = empty($_SESSION['hash']) ? 'guide.php' : 'guide-friend.php';
    header("location:{$url}");
    exit();
}

init(1);

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta name="viewport" content="height=device-height,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=yes" /> 
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no"/>
<title>70万神秘海底宝藏</title>
<link rel="stylesheet" href="css/css.css"/>
<link rel="stylesheet" href="css/animation.css"/>
<?php if(isset($need_guanzhu)): ?>
<script type="text/javascript">
    var url_guanzhu = '<?php echo $need_guanzhu ?>';
</script>
<?php endif; ?>
</head>
<body>
<div class="main" id="main">


<ul>
	<img src="images/qipao1.png" class="qipao1">
    <img src="images/qipao2.png" class="qipao2">
    <img src="images/qipao3.png" class="qipao3">
    <img src="images/fish1.png" class="fish1">
    <img src="images/fish2.png" class="fish2">
    <img src="images/fish3.png" class="fish3">
    <img src="images/guang.png" class="guangmang">
    <img src="images/eyes.png" class="eyes">
	<li class="shayu"><img src="images/shayu.png"></li>
    <li class="yu"><img src="images/yu.png"></li>
    <li class="meirenyu"><img src="images/meirenyu.png"></li>
    <li class="zhangyu"><img src="images/zhangyu.png"></li>
    <li class="ankang"><img src="images/ankang.png"></li>
    <li class="haima"><img src="images/haima.png"></li>
    <li class="pangxie"><img src="images/pangxie.png"></li>
    <li class="wugui"><img src="images/wugui.png"></li>
    <li class="haixing"><img src="images/haixing.png"></li>
    <li class="bangke"><img src="images/bangke.png"></li>
</ul>
</div>
<div class="fix" style="display:block"></div>
<!--玩法介绍-->
<div class="pop" style="display:block">
  <p>图中有<span class="yellow">3种</span>海洋生物藏有保障，<br>您可以点击3处，找出的保障就是你的哦</p>
  <p>左右滑动屏幕可以查询完整藏保图</p>
  <p style="text-align:left">完成寻保后还可发起你自己的藏保计划分享给好友来找，更有机会获得3000元大奖呢，马上开始吧！</p>
  <img src="images/pop.png" style="width:73%; margin-bottom:10px">
  <div class="pop-close"><img src="images/pop-close.png"></div>
</div>
<!--玩法介绍-->

<!--规则介绍-->
<?php include 'rule.html'; ?>
<!--规则介绍-->


<!--中奖1-->
<div class="tanchu zhongjiang1 reward">
	<img src="images/tanchu.png">
	<p>
    	<span class="font-18">牛掰啊，找到<span class="baoxiane">28</span>万保障</span><br>
        <img src="images/zhongjiang1.png" class="zhongjiang">
    </p>
</div>
<!--中奖1-->

<!--中奖2-->
<div class="tanchu zhongjiang2 reward">
	<img src="images/tanchu.png">
	<p>
    	<span class="font-18">开外挂了吧,找到<span class="baoxiane">28</span>万保障</span><br>
        <img src="images/zhongjiang2.png" class="zhongjiang">
    </p>
</div>
<!--中奖2-->

<!--中奖3-->
<div class="tanchu zhongjiang3 reward">
	<img src="images/tanchu.png">
	<p>
       <span class="font-18">搜查达人,找到<span class="baoxiane">28</span>万保障</span><br>
        <img src="images/zhongjiang3.png" class="zhongjiang">
    </p>
</div>
<!--中奖3-->

<!--未中奖1-->
<div class="tanchu weizhongjiang1 noreward">
	<img src="images/tanchu.png">
	<p>
    	<span class="font-18">呃，换个手指点吧</span><br>
        <img src="images/weizhongjiang1.png" class="zhongjiang">
    </p>
</div>
<!--未中奖1-->

<!--未中奖2-->
<div class="tanchu weizhongjiang2 noreward">
	<img src="images/tanchu.png">
	<p>
    	<span class="font-18">ORZ，这里没有哦。</span><br>
        <img src="images/weizhongjiang2.png" class="zhongjiang">
    </p>
</div>
<!--未中奖2-->

<!--未中奖3-->
<div class="tanchu weizhongjiang3 noreward">
	<img src="images/tanchu.png">
	<p>
    	<span class="font-18">吉时未到,保障不出啊~</span><br>
        <img src="images/weizhongjiang3.png" class="zhongjiang">
    </p>
</div>
<!--未中奖3-->

<!--中奖总数-->
<div class="zongshu" id="total">
	<img src="images/zongshu.png">
    <p>
        <span class="font-24">获得<span class="baoxiane">28</span>万<br>海陆空意外保障</span><br>
    </p>
    <div class="zongshu-close"></div>
    <div class="again" id="again"></div>
    <div class="manzu"></div>
</div>
<!--中奖总数-->

<!--发起藏保-->
<div class="faqicangbao">
	<img src="images/faqicangbao.png">
    
    <p>
    <span class="faqi-title">您已成功领取<span class="baoxiane">28</span>万</span><br>
    <span class="font-27">海陆空意外保障</span><br>
	<span class="qixian">保障期限：2015年12月1日至2015年12月31日</span><br>
    <span class="faqi-text">试试看好友和你的默契度，看他几次能找出的你的藏保点？发起我的藏保计划，还有机会参与<span class="red">3000元</span>购物金抽奖哦。</span>
    </p>
    <div class="zongshu-close"></div>
    <div class="faqi"></div>
</div>
<!--发起藏保-->

<!--设置藏保-->
<?php include 'setplace.html' ?>
<!--设置藏保-->

<!--分享-->
<?php include 'share.html' ?>
<!--分享-->

<div style="display:none">
    <img style="display:none" src="<?php echo $_hmtPixel; ?>" width="0" height="0" />
</div>
<script src="js/jquery-1.11.1.min.js"></script>
<script src="/js/jweixin-1.0.0.js"></script>
<script src="js/women.js?v=110"></script>
<script src="js/index.js?v=110"></script>

</body>
</html>
