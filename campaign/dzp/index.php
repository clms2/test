<?php 
session_start();
$rec = isset($_GET['rec']) ? $_GET['rec'] : '';
// 分享人ID，未登陆就判断为是分享链接进来的
if(!empty($rec) && !isset($_SESSION['uid']) && preg_match('#^\d+$#', $rec)){
	$_SESSION['rec'] = $rec;
}
// wx.php里已经记录了openid
// 微信进来带openid,确保不是分享链接,有一种可能是他点的别人的链接但把rec=去了,所以login.php里又查了次数据库
/*$openid = isset($_GET['openid']) ? $_GET['openid'] : '';
if(!empty($openid) && empty($rec) && strlen($openid) == 28){
	$_SESSION['openid'] = $openid;
}*/
// echo !empty($_SESSION['openid']) ? $_SESSION['openid'] : '';
// 假如他登陆成功，但是返回了下次进来点的链接是原始链接没有带rec=的那么就带上他的id
if(!empty($_SESSION['uid']) && stripos($_SERVER['QUERY_STRING'], 'rec=') === false){
	header("location:index.php?rec={$_SESSION['uid']}");
	exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Cache-Control" content="no-transform " />
<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link href="public/css/index.css" type="text/css" rel="stylesheet">
<title>幸运大转盘 好礼伴你行</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<script type="text/javascript" src="public/js/jquery.js"></script>
<script type="text/javascript" src="public/js/jQueryRotate.2.2.js"></script>
<script type="text/javascript" src="public/js/jquery.easing.min.js"></script>
<script type="text/javascript" src="public/js/kxbdMarquee.js"></script>
<script src="/js/jweixin-1.0.0.js"></script>
<script>
	var title_arr = ['原地转个圈就有机会拿Apple Watch？','我送话费你说要！话费！要！要！！要！！！','每天来转转，让好运在你指尖绽放','大家都说这次的礼品：哎哟不错哦~很容易到手哦~'],
		desc_arr = ['转个圈容易，中个奖不易，快来助我一臂之力。','人生不如意，十之八九都是因为没！中！奖！不过依我的颜值来看，这是迟早的事儿~','很久没扶老奶奶过马路，人品下滑了…快来助朕一臂之力，军功章里有你的一半！','我这么用力却没中，好不甘心…快扶我起来再试试'],
		imgs_arr = ['share.jpg','share2.jpg'];
	var _rand = function(min,max){
		return Math.round(Math.random()*(max-min))+min;
	};
   //微信分享数据
	var wxData = {   
		title:title_arr[_rand(0, title_arr.length-1)],
		desc: desc_arr[_rand(0, desc_arr.length-1)],
		link: window.location.href,
		imgUrl:'http://'+ window.location.host + '/campaign/201509_DZP/public/img/index/'+imgs_arr[_rand(0, imgs_arr.length-1)]
	};
	var appId='wx9de7849179fa4533';
	if (location.host.indexOf(".cigna") > 0&&location.host.indexOf("est")<=0) {  appId='wx8d188615510c9093'; } 
	//调用分享接口
	$.ajax({ 
		type: "post",
		url: "/include_form/weixinConfig.php", 
		cache:false,
		async:false,
		dataType: "html",
		data:{appId:appId,share:'all'},
		success: function(obj){
			eval(obj);
		}
	});
</script>

</head>
<body>
<section class="main prvo">
<img src="public/img/index/bg.jpg" width="100%">
<div class="zp_box ptas">
<div class="prvo zp-wp">
<img src="public/img/index/zpbg.png" width="100%">
<div class="ptas zbg"><img src="public/img/index/zz.png" width="100%"></div>
<div class="startbtn ptas"><img src="public/img/index/sbb.png" width="100%"></div>
</div>
</div>
<div class="hdr ptas fz12">活动时间：2015年9月1日-9月30日</div>
<div class="butbox ptas">
<div class="prvo">
<a href="javascript:;" class="dh_but"><img src="public/img/index/bt1.png" width="100%"></a>
<a href="javascript:;" class="zj_but"><img src="public/img/index/bt2.png" width="100%"></a>
<a href="javascript:;" class="my_but"><img src="public/img/index/bt3.png" width="100%"></a>
</div>
</div>
</section>


<!-- 弹窗未登录-->
<div id="no_denglu" class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt">您还未登陆呢~</div>
<div class="ms_txt ptas fz12">赶紧登陆吧！<br>大奖就在这静静的等着您！</div>
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas login_but"><img src="public/img/index/bkbut.png" width="100%"></div>
</div>
</div>


<!-- 弹窗中奖-->
<div id="zj_smg" class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt">恭喜您！</div>
<div class="ms_txt ptas fztt ms_hj_txt">您真是上帝的宠儿，恭喜<br>获得<span>XXX</span>！</div>
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_hj_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<div class="ms_zc ptas fz10 clred ">小诺正在为您准备礼品，<br>活动结束后7个工作日内将打包送到您家</div>
</div>
</div>

<!-- 弹窗未中奖-->
<div id="nzj_msg" class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt">您离大奖只差1毫米！</div>
<div class="ms_txt ptas fz12 ms_nzj_txt">
<font class="fz15">好遗憾啊！</font><br><span>难道是转动方向不对？换个<br>Pose再试试看！</span>
</div>
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_hj_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<div class="ms_zc ptas fz10 clred ms_nzj_zc">您今天还剩<span>3</span>次抽奖机会</div>
</div>
</div>

<!-- 弹窗我的奖品-->
<div id="no_jp"  class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt ms_jp_tt">我的奖品~</div>
<div class="ms_txt ptas fz12 ms_jp_txt">此页面理应不存在，因为您还未斩获<br>任何奖品…建议您关闭此页面，让转<br>盘先飞起来吧~~</div>
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_jp_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<!-- <div class="ms_zc ptas fz10 ms_nzj_zc">还不是会员？<a href="" class="reg" target="_blank">立即注册</a></div> -->
</div>
</div>

<!-- 弹窗中奖名单-->
<div id="myjp_msg" class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt ms_jp_tt">我的奖品</div>
<!-- -->
<div class="ms_list ptas">
<div class="fz10 prvo ms_list_tt"><span>奖品</span><span>中奖时间</span></div>
<div class="prvo gd_box my_gd_box" id="myreward" style="overflow:auto">
<ul>
<li class="fz10"><font>还没有中奖哦</font></li>
</ul>
</div>
</div>
<!-- -->
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_jp_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<div class="ms_zc ptas fz10 ms_nzj_zc" style="display:none">还不是会员？<a href="" target="_blank" class="reg">立即注册</a></div>
</div>
</div>

<!-- 弹窗中奖名单-->
<div id="zjmd_msg"   class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt ms_jp_tt">中奖名单</div>
<!-- -->
<div class="ms_list ptas">
<div class="fz10 prvo ms_list_tt"><span>手机号码</span><span>中奖礼品</span></div>
<div class="prvo gd_box wj_gd_box" id="lucklist">
<ul>
<li class="fz10"><font>131****2375 </font><font>京东购物卡</font></li>
<li class="fz10"><font>137****3351 </font><font>30元话费</font></li>
<li class="fz10"><font>180****5711 </font><font>30元话费</font></li>
<li class="fz10"><font>133****6871 </font><font>京东购物卡</font></li>
<li class="fz10"><font>135****7759 </font><font>Bongxx 健康手环</font></li>
<li class="fz10"><font>151****5831 </font><font>30元话费</font></li>
<li class="fz10"><font>130****0073 </font><font>30元话费</font></li>
<li class="fz10"><font>130****6656 </font><font>京东购物卡</font></li>
<li class="fz10"><font>130****0073 </font><font>30元话费</font></li>
<li class="fz10"><font>130****0073 </font><font>30元话费</font></li>
</ul>
</div>
</div>
<!-- -->
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_jp_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<div class="ms_zc ptas fz10 ms_nzj_zc">还不是会员？<a href="" target="_blank" class="reg">立即注册</a></div>
</div>
</div>

<!-- 弹窗活动说明-->
<div id="hdsm_msg" class="msg">
<div class="prvo">
<img src="public/img/index/tc.png" width="100%">
<div class="ms_tt ptas fztt ms_jp_tt">活动说明</div>
<!-- -->
<div class="ms_list ptas">
<img src="public/img/index/sm.jpg" width="100%">
</div>
<!-- -->
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
<div class="jbut ptas ms_jp_jbut ui_exit"><img src="public/img/index/bkbut.png" width="100%"></div>
<div class="ms_zc ptas fz10 ms_nzj_zc">还不是会员？<a href="" class="reg" target="_blank">立即注册</a></div>
</div>
</div>

<!-- 登陆-->
<div id="login_msg" class="msg">
<div class="prvo">
<img src="public/img/index/bg2.png" width="100%">
<!-- -->
<div class="ms_list_wp ptas">
<div class="lg_ms_tt prov fz12">您还没有登录，请先登录!</div>
<div class="lg_nav prov"><ul><li id="mmlgin" class="fz12 hover lh52">密码登录</li><li id="sjlgin" class="fz12 hover lh52">手机动态码登陆</li></ul></div>
<div class="lgin_wp_box lg_box0">
<form id="myform2" method="post" action="login.php">
<input type="hidden" name="type" value="password">
<div class="lgin_list mat7"><input placeholder="请输入您的账号" id="uname1" type="text" name="userName" class="username fz12 lh52"></div>
<div class="lgin_list mat7"><input placeholder="请输入您的密码" id="pwd" type="password" name="password" class="pwd fz12 lh52"></div>
<div class="lgin_list2 mat7"><input type="checkbox" data-for="uname1" class="remember" ><span class="fz10">记住用户名</span></div>
<div class="lgin_but"><input type="submit" value="" class="mm_sb sb-but"></div>
</form>
</div>

<div class="lgin_wp_box lg_box1" style="display:none;">
<form id="myform1" method="post" action="login.php">
<input type="hidden" name="type" value="mobile">
<div class="lgin_list mat7"><input id="mobile" placeholder="请输入您的手机号码" type="text" name="userName" maxlength="11" class="phone fz12 lh52"></div>

<div class="lgin_list mat7"><div class="yam_inp"><input placeholder="请输入验证码" type="text" id="imgCodeVal" name="imgCodeVal" autocomplete="off" maxlength="4" class="yzm fz12 lh52"></div><div class="lgin_yzm2 fz12 lh52"><img id="imgCode" onclick="javascript:this.src = this.src.replace('&?0\.\d+','')+'&'+Math.random()" class="lh52" title="点击更换验证码" alt="验证码"></div></div>

<div class="lgin_list mat7"><div class="yam_inp p_yzm_inp"><div class="p_suo lh52"><img class="ps_img" src="public/img/index/suo.jpg" width="100%"></div><input class="p_yzm fz12 lh52" placeholder="请输入验证码" type="text" id="code" name="validateCode" autocomplete="off" maxlength="4" id="codeval" class="yzm fz12"></div><div class="lgin_yzm fz12 lh55" id="getCode">获取验证码</div></div>

<div class="lgin_list2 mat7"><input type="checkbox" data-for="mobile" class="remember" ><span class="fz10">记住用户名</span></div>
<div class="lgin_but"><input type="submit" value="" id="login1" class="sj_sb sb-but"></div>
</form>
</div>

<div class="lgin_list3 fz10"><span><a href="" class="forgotpwd" target="_blank">忘记密码</a></span><font>还不是会员？<a href="" class="reg" target="_blank">立即注册</a></font></div>
</div>
<!-- -->
<div class="smg_exit ptas ui_exit"><img src="public/img/index/exit.png" width="100%"></div>
</div>
</div>


<div id="fx_msg">

<div class="fx_wp fz15 prov">
<p class="fx_p1">今日的抽奖机会已用完，点击右上角</p><p>【发送给朋友】【分享到朋友圈】，</p><p>邀请朋友注册可获得额外抽奖机会！</p>
<div class="fximg poas"><img src="public/img/index/fx.png" width="100%"></div>
</div>
</div>


<div id="Black_bg"></div>
<script type="text/javascript" src="public/js/dc_common.js"></script>
<script type="text/javascript" src="public/js/index.js"></script>
<!-- Gridsum tracking code begin. -->
<script type='text/javascript'>
    (function () {
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.async = true;
        s.src = (location.protocol == 'https:' ? 'https://ssl.' : 'http://static.') + 'gridsumdissector.com/js/Clients/GWD-002548-45F840/gs.js';
        var    firstScript = document.getElementsByTagName('script')[0];
        firstScript.parentNode.insertBefore(s, firstScript);
    })();
</script>
<!--Gridsum tracking code end. -->


</body>
</html>
