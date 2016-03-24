<?php 
header('content-type:text/html;charset="utf-8"');
echo '<pre>';

function randKey($num = 24){
	$key = 'qwertyuioplkjhgfdsazxcvbnmQWERTYUIOPLKJHGFDSAZXCVBNM1234567890';
	$i = 0;
	$ret = '';
	$len = strlen($key) - 1;
	while(++$i <= $num){
		$index = mt_rand(0, $len);
		$ret .= $key{$index};
	}
	return $ret;
}

function buildA(){
	// 100 elements, 24byte key and 10k data per entr
	$aHash = array();
	for($i = 0;$i<100; $i++){
		$aHash[randKey()] = randKey(10*1024);
	}
	return $aHash;
}

$a = buildA();

$t = new TimeCost();

$t->setName('foreach');
$t->ts();
foreach($a as $val);
$t->te();

$t->setName('listach');
$t->ts();
reset($a);
while(list(,$val) = each($a));
$t->te();


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
