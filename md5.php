<?php 
// 生成整站md5 到md5.txt

// 需生成目录
$dir = '.';
$md5_file = 'md5.txt';
// 不生成MD5的文件/文件夹
$exclude = array(
	basename(__FILE__),
	$md5_file,
	'./boot/',
	'campaign/'
);

ini_set('memory_limit', '130m');
$files = globdir($dir);

$m = memory_get_usage();
header('content-type:text/html;charset="utf-8"');
echo '<pre>';
// $ret = array();
// print_r($files);
$fp = fopen($md5_file, 'w');
foreach ($files as &$file) {
	$dir = dirname($file);
	$filename = basename($file);
	$pass = false;
	// 在exclude中的不处理
	foreach ($exclude as $exc) {
		// 不是目录
		if(substr($exc, -1) != '/'){
			// 文件名相同跳过
			if($exc == $filename){
				$pass = true;
				break;
			}
			continue;
		}
		// 在目录下
		if(strpos($dir, $exc) !== false){
			$pass = true;
			break;
		}
	}
	if(!$pass){
		fwrite($fp, md5_file($file). '=>'. $file . "\r\n");
		// $ret[$file] = md5_file($file);
	}
	
}
fclose($fp);
echo memory_get_usage()-$m;


function globdir($dir, $filter = '*', $patten = GLOB_BRACE, $nocache = null) {
	static $file_arr = array ();
	isset($nocache) && $file_arr = array ();
	if (!is_dir($dir)) return;
	$a = glob("{$dir}/{$filter}", $patten);
	array_walk($a, function ($file) use(&$file_arr, $patten, $filter) {
		if ($patten == GLOB_ONLYDIR) {
			$file_arr[] = $file;
			globdir($file, '*', GLOB_ONLYDIR);
		} else {
			is_file($file) ? $file_arr[] = $file : globdir($file, $filter, $patten);
		}
	});
	if ($filter != '*') {
		$b = glob("{$dir}/*", GLOB_ONLYDIR);
		array_walk($b, function ($dir) use($filter, $patten) {
			globdir($dir, $filter, $patten);
		});
	}
	return $file_arr;
}