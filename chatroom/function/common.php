<?php
function getval($v) {
	$val = isset ( $_POST [$v] ) ? $_POST [$v] : (isset ( $_GET [$v] ) ? $_GET [$v] : '');
	return get_magic_quotes_gpc () ? $val : addslashes ( $val );
}

function is_assoc($arr) {
	return array_keys($arr) !== range(0, count($arr) - 1);
}

?>