<?php
/**
 * 在需过滤的文件中引入即可，防止sql注入、xss
 */
function filter(&$arr) {
	array_walk_recursive($arr, 'walk', get_magic_quotes_gpc());
}
function walk(&$v, $k, $quote) {
	$v = preg_replace(array (
		'#<script#is',
		//'#<img#is',
		'#<link#is',
		'#javascript#is',
		'#select#is',
		'#update#is',
		'#insert#is', 
		'#replace#is', 
		'#where#is', 
		'#drop#is',
		'#create#is',
		'#alter#is',
		'#and#is',
		'#or#is',
		'#union#is',
		'#delete#is',
		'#\$#s'), '', $v);
	!$quote && $v = addslashes($v);
}
function filter_one($str){
	walk($str, '', get_magic_quotes_gpc());
	return $str;
}

filter($_GET);
filter($_POST);
//$_REQUEST = $_GET + $_POST;