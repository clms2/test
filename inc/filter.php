<?php
function filter(&$arr) {
	array_walk_recursive($arr, 'walk', get_magic_quotes_gpc());
}
function walk(&$v, $k, $quote) {
	$v = preg_replace(array (
		'#<script#is',
		'#<img#is',
		'#<link#is',
		'#javascript#is',
		'#select#is',
		'#update#is',
		'#insert#is', 
		'#replace#is',
		'#truncate#is',
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
