<?php 
require 'inc/check.php';
require 'inc/config.php';
require 'inc/func.php';

$a = isset($_GET['a']) ? $_GET['a'] : '';
if(empty($a)) exit();

$ret = array();

// 未处于活动时间
if(!$isdev && (time() > $ac_duration['end'])){
	$ret['code'] = 110;
	ex($ret);
}
if(empty($_SESSION['uid'])){
	$ret['code'] = 120;
	ex($ret);
}

switch ($a) {
	// 寻宝
	case 'find':
		init();
		$yu = getval('yu');
		if(!in_array($yu, $treasure_map)) exit();

		// 寻宝次数
		empty($_SESSION['searchnum']) && $_SESSION['searchnum'] = 1;
		// 点的藏宝点次数
		!isset($_SESSION['treasure_num']) && $_SESSION['treasure_num'] = 0;
		// 找到的藏宝点个数
		!isset($_SESSION['foundnum']) && $_SESSION['foundnum'] = 0;
		// 记录总保险额
		!isset($_SESSION['totalbaoxian']) && $_SESSION['totalbaoxian'] = 0;

		// 达到3次显示结果
		if($_SESSION['treasure_num'] == 3){
			$ret['code'] = 2;
			$ret['baoxian'] = $_SESSION['totalbaoxian'];
			ex($ret);
		}
		$ret['search_num'] = ++$_SESSION['treasure_num'];

		// 藏宝点
		$place = array_keys($_SESSION['place']);

		// 第一个点必中, 如果是分享进来就不需要必中
		if($_SESSION['treasure_num'] == 1 && !isset($_SESSION['hash'])){
			++$_SESSION['foundnum'];
			$baoe = array_pop($_SESSION['place']);
			$_SESSION['totalbaoxian'] += $baoe;
			$ret['baoxian'] = $baoe;
			$ret['code'] = 1;
			ex($ret);
		}

		// 抽中
		if(in_array($yu, $place)){
			++$_SESSION['foundnum'];
			$baoe = $_SESSION['place'][$yu];
			unset($_SESSION['place'][$yu]);
			$_SESSION['totalbaoxian'] += $baoe;
			$ret['baoxian'] = $baoe;
			$ret['code'] = 1;
			ex($ret);
		}

		$ret['code'] = 0;
		ex($ret);
	break;
	// 不满足
	case 'again':
		++$_SESSION['searchnum'];
		if(isset($_SESSION['is_friend'])){
			init(2);
		}else{
			init(1);
		}
	break;
	// 满足
	case 'satisfy':
		if($_SESSION['treasure_num'] != 3) exit();

		// 是否绑定
		require 'inc/conn.php';
		if(!$_SESSION['isbind']){
			$ret['href'] = BINDURL;
		}else{
			$_SESSION['gottreasure'] = 1;
			$db->update('user', array('isbind'=>'1'), "id={$_SESSION['uid']}");
		}
		
		$baoe   = $_SESSION['totalbaoxian'] + 28;

		// 需记录未绑定时点击满足的保额和绑定后最终的保额
		$where = isset($_SESSION['follow_id']) ? " and follow_id={$_SESSION['follow_id']}" : '';
		if($db->exists('info', "uid={$_SESSION['uid']}{$where}")){
			if($_SESSION['isbind']){
				$data = array(
					'baoe' => $baoe,
					'gettime' => time()
				);
				if(!empty($_SESSION['hash'])){
					$data['hash'] = $_SESSION['hash'];
				}
				$db->update('info', $data, "uid={$_SESSION['uid']}{$where}");
			}
			unset($_SESSION['follow_id']);
		}else{
			$data = array(
				'total'     => $baoe,
				'searchnum' => $_SESSION['searchnum'],
				'foundnum'  => $_SESSION['foundnum'],
				'uid'       => $_SESSION['uid'],
				'uname'     => $_SESSION['uname'],
				'addtime'   => time(),
				'moqi'      => getRate($_SESSION['searchnum'], $_SESSION['foundnum'])
			);
			if($_SESSION['isbind']){
				$data['baoe'] = $baoe;
				$data['gettime'] = time();
				if(!empty($_SESSION['hash'])){
					$data['hash'] = $_SESSION['hash'];
				}
			}
			if(isset($_SESSION['follow_id'])){
				$data['follow_id'] = $_SESSION['follow_id'];
				// 同一个人多次玩一个好友的藏宝不增加参与人数
				if($db->getRowNum('info', "where follow_id={$data['follow_id']} and uid={$_SESSION['uid']}") == 0){
					// 参与人数加1
					$db->update('user', array('friendsnum'=>'1++'), "id={$_SESSION['follow_id']}");
				}
				unset($_SESSION['follow_id']);
			}
			if(!$db->insert('info', $data)){
				$ret['code'] = 0;
				ex($ret);
			}
		}
		$ret['code'] = 1;
		ex($ret);
	break;
	// 用户藏宝点设置
	case 'setplace':
		$place = getval('place');
		if(!is_array($place)) exit();
		// 藏宝点有效性验证
		foreach (array_keys($place) as $p) {
			if(!in_array($p, $treasure_map)) exit();
		}
		// 总和42万
		if(array_sum(array_values($place)) != 42) exit();

		require 'inc/conn.php';
		$hash = uniqid();
		// 保存用户藏宝点
		$data = array(
			'uid'   => $_SESSION['uid'],
			'place' => json_encode($place),
			'hash'  => $hash
		);
		$number = $db->getOneField('user_set', "uid={$_SESSION['uid']} order by id desc", 'setnumber');
		if(is_numeric($number)){
			$data['setnumber'] = ++$number;
		}

		if(!$db->insert('user_set', $data)){
			$ret['code'] = 0;
			ex($ret);
		}
		$db->update('user', array('startnum'=>'1++'), "id={$_SESSION['uid']}");
		$_SESSION['need_show_share'] = 1;// 用于跳转页面后显示分享层
		$ret['hash'] = $hash;
		$ret['code'] = 1;
		ex($ret);
	break;
}
