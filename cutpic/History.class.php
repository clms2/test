<?php
class History{
	/**
	 * 最多历史记录数
	 */
	public static $max_index = 9;

	// public $save_type;

	private function getIndex(){
		if(!isset($_SESSION['history']['info'])) return 0;
		$ct = count($_SESSION['history']['info']) - 1;
		if($ct <= 0) $ct = 0;
		
		return  $ct;
	}

	static function put($v){
		$_SESSION['history']['info'][] = $v;

		$index = self::getIndex();
		if($index == self::$max_index){
			array_shift($_SESSION['history']['info']);
		}
		
		$_SESSION['history']['current'] = $index;
	}

	static function get($act){
		$max_index = self::getIndex();
		if(!isset($_SESSION['history']['current'])) $_SESSION['history']['current'] = $max_index;
		$current = $_SESSION['history']['current'];

		if($act == 'next'){
			if(++$current > $max_index) return 0;
			$has_next = $current == $max_index ? 0 : 1;
			$_SESSION['history']['current'] = $current;
			
			return array('info'=>$_SESSION['history']['info'][$current], 'has_next'=>$has_next);
		}
		if($act == 'prev'){
			if(--$current < 0) return 0;
			$has_prev = $current == 0 ? 0 : 1;
			$_SESSION['history']['current'] = $current;

			return array('info'=>$_SESSION['history']['info'][$current], 'has_prev'=>$has_prev);
		}
	}

	// 数组中是否存在指定键名的操作,用于判断并记录该类操作的初始值,只搜索每个history的第一维.需要在put的时候存入用于区分的key
	static function exists($v, $key = 'hash'){
		foreach (self::getAll() as $history) {
			if(array_key_exists($key, $history) && $history[$key] == $v){
				return true;
			}
		}

		return false;
	}

	static function getAll(){
		if (!empty($_SESSION['history']['info'])) {
			return $_SESSION['history']['info'];
		}

		return array();
	}

	static function isend(){
		if (!isset($_SESSION['history']['current'])) {
			return true;
		}

		return $_SESSION['history']['current'] == self::getIndex();
	}

	static function isfirst(){
		if (!isset($_SESSION['history']['current'])) {
			return true;
		}

		return $_SESSION['history']['current'] == 0;
	}
}