<?php
class msg {
	private static $instance = null;
	private $max = 5;
	public static $arr = array ();
	private function msg($uname, $time, $msg) {
		$count = array_push ( self::$arr, array (
				'uname' => $uname,
				'time' => $time,
				'msg' => $msg 
		) );
		/* if ($count > $this->max) {
			array_shift ( $arr );
		} */
	}
	static function getInstance($uname,$time,$msg) {
		if (self::$instance == null) {
			self::$instance = new msg ( $uname, $time, $msg );
		}
		return self::$instance;
	}
}

?>