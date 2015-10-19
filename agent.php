<?php
// 首先访问一下设置cookie
if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'test.com') !== false){
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');   
	setcookie("test", 'test', time()+1800, "/");
	exit('1');
}
// 验证
if(empty($_COOKIE['test'])){
	exit('500');
}


$url = isset($_GET['url']) ? urldecode($_GET['url']) : '';

if(!empty($url)){
	$urlarr = parse_url($url);
	$domain = $urlarr['host'];
	$ct = file_get_contents($url);
	$ct = str_replace($domain, '', $ct);
	// 相对绝对路径要处理
	$js = '<script>(function(){if(typeof jQuery != "function" ) return;$("a").click(function(){location.href="?url=http://"+'.$domain.'+encodeURIComponent($(this).attr("src"))});})();</script>';

}
