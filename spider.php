<?php 
// 爬站点链接
// building 

date_default_timezone_set('PRC');
header('content-type:text/html;charset="utf-8"');
define('COOKIEFILE', $_SERVER['DOCUMENT_ROOT'].'/cookie_sp.txt');
define('LOGDIR', $_SERVER['DOCUMENT_ROOT'].'/log');
define('CACHEDIR', $_SERVER['DOCUMENT_ROOT'].'/cache');
// 仅获取指定站点
define('ONESITE', true);
!file_exists(COOKIEFILE) && touch(COOKIEFILE);
!is_dir(LOGDIR) && mkdir(LOGDIR, 0777, 1);
!is_dir(CACHEDIR) && mkdir(CACHEDIR, 0777, 1);

set_time_limit(0);
ignore_user_abort(1);

echo '<pre>';
$url = 'http://cmb.com/';
$scheme = 'http://';

global $host,$referer,$domain,$dealed_urls,$other_site_urls;

$host = getHost($url);
$referer = '';
$domain = getDomain($host);
$dealed_urls = array($url);
$other_site_urls = array();

// echo $host,'<br>',$domain,'<br>';exit;

getSiteUrl($url);
print_r($dealed_urls);

function getSiteUrl($pageurl){
	global $dealed_urls,$other_site_urls,$host;
	$ct = getContent($pageurl);
	$urls = getPageUrl($ct);
	
	while(list(, $u) = each($urls)){
		// 相对路径 补完url
		if(substr($u, 0, 4) !== 'http'){
			global $scheme;
			$u = ltrim($u, '/');
			$u = "{$scheme}{$host}/{$u}";
		}

		// 跳过已处理url
		if(in_array($u, $dealed_urls)) continue;

		// 跳过非本站url
		if(ONESITE){
			if(in_array($u, $other_site_urls)) continue;
			// 判断是否在本站
			global $domain;
			$temp_host = getHost($u);
			if(strpos($temp_host, $domain) === false){
				$other_site_urls[] = $u;
				continue;
			}
		}
		// if(strpos($u, 'com:8002') !== false){
		// 	exit($pageurl);
		// }

		$dealed_urls[] = $u;
		if(count($dealed_urls) < 100) getSiteUrl($u);
	}
}

/**
 * 根据url从文件缓存中获取html内容，不存在则http请求
 * 缓存文件名：cache/domain.com/www.domain.com.txt
 * @param  string $url http://www.xx.com
 * @param  boolean $nocache 是否不用缓存
 * @return string      html content
 */
function getContent($url, $nocache = false){
	global $domain;
	$cachedir = CACHEDIR . "/{$domain}";
	!is_dir($cachedir) && mkdir($cachedir, 0777, true);

	$cachefile = $cachedir. '/' . md5($url). '.txt';
	if(!$nocache && file_exists($cachefile)) return file_get_contents($cachefile);

	$ct = curl_send($url);
	file_put_contents($cachefile, "{$url}\r\n".$ct);

	return $ct;
}

/**
 * 获取host
 * @param  string $url http://www.xx.com
 * @return string      www.xx.com
 */
function getHost($url){
	$url = strtr($url, array('http://' => '', 'https://' => ''));

	return strchr($url, '/', true);
}

/**
 * 根据host获取域名
 * @param  string $host www.xx.com
 * @return string       xx.com
 */
function getDomain($host){
	if(substr_count($host, '.') < 2) return $host;
	return ltrim(strchr($host, '.'), '.');
}

/**
 * 匹配正文内容超链接
 * @param  string $content html content
 * @return array          links
 */
function getPageUrl($content){
	preg_match_all('#<a.*?href=[\"\']((?:http|/|[a-z][0-9]).*?)[\"\']#is', $content, $match);

	return !empty($match[1]) ? $match[1] : array();
}


function curl_send($url, $data = null) {
	global $host,$referer;
	$header = array(
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    	'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
    	'Cache-Control: max-age=0',
    	'Connection: keep-alive',
    	'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.0'
	);
	if(!empty($host)){
		$header[] = "Host: {$host}";
	}
	if(!empty($referer)){
		$header[] = "Referer: {$referer}";
	}

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIEFILE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIEFILE);
    if (isset($data)) {
        $data = is_array($data) ? http_build_query($data) : $data;
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    $return = curl_exec($ch);
    curl_close($ch);

    return $return;
}
