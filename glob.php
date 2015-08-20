<?php
/**
 * 函数功能：
 * globdir(添加5.4支持)：可以遍历指定目录下的所有文件、文件夹、特定后缀的文件
 * deldir: 删除目录及目录下的所有文件和文件夹,有兴趣的可以把它扩展为能删除特定后缀的
 * 
 * 函数调用：
 * globdir($dir)**********************返回所有文件
 * globdir($dir, '*.php')*************返回php文件
 * globdir($dir, '*.{php,html}')******返回php和html文件
 * globdir($dir, '*', GLOB_ONLYDIR)***返回目录
 * deldir($dir)
 */
ini_set('memory_limit', '128m');

echo '<pre>';
$dir = '.';
print_r(globdir($dir));
//print_r(globdir($dir, '*.php', GLOB_BRACE, true));
//print_r(globdir($dir, '*.{php,html}', GLOB_BRACE, true));
//print_r(globdir($dir, '*', GLOB_ONLYDIR, true));


//deldir('F:\AppServ\www\test\a');


//php 5.4
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
/**
 * 遍历目录 <=5.3
 * @param string $dir 绝对/相对路径
 * @param string $filter 默认*返回所有文件及文件夹，*.php仅返回php文件，如果$patten为GLOB_BRACE可实现多文件筛选，如*.{php,html}，返回php和html文件
 * @param const $patten 默认GLOB_BRACE，可选:GLOB_ONLYDIR，更多参数请参考手册
 * @param string/bool $nocache 防止本次调用的结果缓存上次的结果，如果一个脚本仅调用一次本函数，则不用管，否则得设个值
 * @return array
 */
 /*
function globdir($dir, $filter = '*', $patten = GLOB_BRACE, $nocache = null) {
	static $file_arr = array ();
	isset($nocache) && $file_arr = array ();
	if (!is_dir($dir)) return;
	if ($patten == GLOB_ONLYDIR) {
		$code = 'if (is_dir($file)) {$file_arr[] = $file;globdir($file, "*", GLOB_ONLYDIR);}';
	} else {
		$code = 'is_file($file) ? $file_arr[] = $file : globdir($file,"' . $filter . '",' . $patten . ');';
	}
	array_walk(glob("{$dir}/{$filter}", $patten), create_function('$file, $k, $file_arr', $code), &$file_arr);
	if ($filter != '*') {
		array_walk(glob("{$dir}/*", GLOB_ONLYDIR), create_function('$dir,$k,$param', 'list($filter, $patten) = explode("|", $param);globdir($dir, $filter, $patten);'), "{$filter}|{$patten}");
	}
	return $file_arr;
}
*/

/**
 * 删除目录及目录下的所有文件
 * @param string $dir
 */
function deldir($dir) {
	if (is_dir($dir)) {
		array_map(create_function('$file', 'is_file($file) ? unlink($file) : deldir($file);'), glob("{$dir}/*"));
		rmdir($dir);
	}
}