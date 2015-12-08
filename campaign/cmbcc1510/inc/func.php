<?php 
/**
 * 初始化数据
 * @param  [type]  $baoxian      保额
 * @param  [type]  $treasure_map 藏宝点
 * @param  int $resettype     重置类型，1:全部重置,2:不满足重置
 * @return [type]                [description]
 */
function init($resettype = 0){
	// 产生随机保额和藏宝点,如果是推荐进来的就用好友的藏宝点和藏宝额
	if($resettype == 1 && empty($_SESSION['is_friend'])){
	    shuffle($GLOBALS['treasure_map']);
	    $yu = array_slice($GLOBALS['treasure_map'], 0, 3);
	    $num = array();
	    // 产生3个保额值，总和为42
	    $num[0] = mt_rand(1, 40);
	    $num[1] = mt_rand(1, 42-$num[0]-1);
	    $num[2] = 42- $num[0] - $num[1];
	    // lastplace:点不满足的时候用上一次的保额功能
	    $_SESSION['place'] = $_SESSION['lastplace'] = array_combine($yu, $num);
	}
	if($resettype == 1){
		$_SESSION['treasure_num'] = 0;
		$_SESSION['totalbaoxian'] = 0;
		$_SESSION['searchnum'] = 0;
		$_SESSION['foundnum'] = 0;
		if(empty($_SESSION['place']) && !empty($_SESSION['lastplace'])){
			$_SESSION['place'] = $_SESSION['lastplace'];
		}
	}
	if($resettype == 2){
		$_SESSION['treasure_num'] = 0;
		$_SESSION['totalbaoxian'] = 0;
		$_SESSION['place'] = $_SESSION['lastplace'];
	}
}

function ex($ar){
	exit(json_encode($ar));
}

function getval($k){
	return isset($_POST[$k]) ? $_POST[$k] : '';
}

/**
 * 默契度:领取保障时发现的藏保点数/3/寻宝次数
 * @param  int $searchnum 寻宝次数
 * @param  int $foundnum  发现藏宝点个数
 * @return int            rate
 */
function getRate($searchnum, $foundnum){
	return round($foundnum/3/$searchnum*100);
}
