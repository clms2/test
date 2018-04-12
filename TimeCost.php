<?php 
/**
 * 测试运行时间
 * $tc = new TimeCost;
 * $tc->setName('use transaction');
 * $tc->ts();
 * //code..
 * $tc->te();
 *
 * $tc->setName('no transaction');
 * $tc->ts();
 * //code..
 * $tc->te();
 * 
 */

class TimeCost{
	public $stime;
	public $etime;
	public $smemory = 0;
	public $ememory;
	public $name;

	function __construct(){
	}

	function setName($name){
		$this->name = $name;
	}

	function ms(){
		$this->smemory = memory_get_usage();
	}

	// todo .. 不准确
	function me(){
		$this->ememory = memory_get_usage();
		$this->output('memory');
	}

	function ts(){
		$this->stime = microtime(true);
	}

	function te(){
		$this->etime = microtime(true);
		$this->output('time');
	}

	function output($type = 'all'){
		$name = !empty($this->name) ? $this->name :  'Code';
		if(!empty($this->etime) && $type == 'all' || $type == 'time'){
			$cost = number_format($this->etime - $this->stime, 8);
			echo $name, ' time costs:', $cost,'<hr>';
			$this->stime = null;
			$this->etime = null;
		}
		// 不准确
		if(!empty($this->ememory) && $type == 'all' || $type == 'memory'){
			$cost = $this->ememory - $this->smemory;
			// echo 'sme:',$this->smemory,'<br>','eme:',$this->ememory,'<br>';
			echo $name, ' memory costs:', $cost,'<hr>';
			$this->smemory = null;
			$this->ememory = null;
		}

	}
}
