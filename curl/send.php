<?php
$cookie = $_SERVER['DOCUMENT_ROOT'].'/cookie.txt';
touch($cookie);
$url1 = 'http://t.dianping.com/register';
$url2 = 'http://www.dianping.com/ajax/json/account/reg/mobile/send?m=13012345678&flow=t&callback=DP._JSONPRequest._7';
$url3 = 'http://hls.dianping.com/hippo.gif?__hlt=www.dianping.com&__ppp=1020&__had={"module":"5_mobreg_getverify","action":"click","":"","reqid":"0a010711-14843992e9f-28aa5b","serverguid":"0a010711-14843992e9f-203292"}&force=|time|&__hsr=1920x1200&__hsc=24bit&__hlh=http://t.dianping.com/register&__mv=||102|0';
$ua = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1';

//1409883789051
$url3 = str_replace('|time|', time().'000', $url3);

$ch = curl_init($url1);
$host = array("Host:t.dianping.com");
curl_setopt($ch, CURLOPT_HTTPHEADER, $host);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_REFERER, 'http://t.dianping.com/jiaxing');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
curl_close($ch);

$ch = curl_init($url3);
$host = array("Host:hls.dianping.com");
curl_setopt($ch, CURLOPT_HTTPHEADER, $host);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_REFERER, 'http://t.dianping.com/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
curl_close($ch);

$ch1 = curl_init($url2);
$host = array("Host:www.dianping.com");
curl_setopt($ch1, CURLOPT_HTTPHEADER, $host);
curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch1, CURLOPT_COOKIEFILE, $cookie);
// curl_setopt($ch1, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch1, CURLOPT_USERAGENT, $ua);
curl_setopt($ch1, CURLOPT_REFERER, 'http://t.dianping.com/register');
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 0);
curl_exec($ch1);
curl_close($ch1);