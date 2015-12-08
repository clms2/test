<?php
require '../inc/conn.php';
require '../inc/func.php';
require 'check.php';

$ret = array('code' => 1);
$a = isset($_POST['a']) ? $_POST['a'] : '';
$time = time();
$day = date('Ymd', $time);

// 保险问问详细页匹配正则，添加和修改时的url匹配
$reg_ask_detail = '#ask/(.*\-)?\d+\.html#';

switch ($a) {
	// 添加回复
	case 'reply':
		$qid = intval(getval('qid'));
		$cont = getval('cont');

		if(!$qid || !$cont){
			$ret['code'] = 0;
			ex($ret);
		}
		$data = array(
			'qid' => $qid,
			'cont' => $cont,
			'addtime' => $time,
			'uname' => 'system'
		);
		if(!$db->insert('reply', $data)){
			$ret['code'] = -1;
			$ret['msg'] = '系统错误';
			ex($ret);
		}
		$ret['addtime'] = $time;
		$ret['id'] = $db->last_id();

		$question = $db->getOneAssoc('question', "id={$qid}", 'solved,status');

		// 问答数++
		qanum('add');
		// 回复数++
		$db->update('question', array('replynum'=>'1++'), "id={$qid}");

		// 标为已解决
		if(!$question['solved'])
			$db->update('question', array('solved'=>'1'), "id={$qid}");

		// 标为已显示
		if($question['status'] != 1)
			$db->update('question', array('status'=>1), "id={$qid}");

	break;
	// 添加提问
	case 'add':
		extract($_POST);
		if(empty($tag)) $tag = array();
		if(empty($tit)){
			$ret['code'] = -1;
			ex($ret);
		}
		if(!empty($alias)){
			if(!preg_match($reg_ask_detail, $alias)) exit('0');
			if($db->exists('question', "alias='{$alias}'")){
				$ret['code'] = -3;
				$ret['msg'] = '该url已存在';
				ex($ret);
			}
		}

		// 存中文tag到question表便于前台展示
		$tagall = getTag();
		$tag_desc = array();
		$insertsql = "insert into {$db->pre}question_tag(qid,typeid) values ";
		foreach ($tag as $typeid) {
			if(isset($tagall[$typeid])){
				$tag_desc[] = $tagall[$typeid];
				$insertsql .= "({qid}, {$typeid}),";
			}
		}
		$tag_desc = implode(',', $tag_desc);
		$insertsql = rtrim($insertsql, ',');

		$data = array(
			'tit'         => $tit,
			'uid'         => 0,
			'cont'        => $cont,
			'tag'         => $tag_desc,
			'addtime'     => $time,
			'keywords'    => $keywords,
			'description' => $description,
			'status'      => 1,
			'alias'       => $alias
		);
		if(!empty($view) && preg_match('#^\d+$#', $view)){
			$data['view'] = $view;
		}

		if(!$db->insert('question', $data)){
			$ret['code'] = -2;
			$ret['msg'] = $db->error() . "<br>" . $db->lastsql;
			ex($ret);
		}
		$ret['addtime'] = date('Y-m-d H:i', $time);
		$ret['id'] = $db->last_id();
		// 插入tagid 到question_tag表
		if(!empty($tag)){
			$insertsql = strtr($insertsql, array('{qid}' => $ret['id']));
			$db->query($insertsql);
		}
		// alias默认值
		if(empty($alias)){
			$ret['alias'] = getAskDetailUrl($ret['id'], false);
			$db->update('question', array('alias'=>$ret['alias']), "id={$ret['id']}");
		}else{
			$ret['alias'] = $alias;
		}

		// 问答数++
		qanum('add');

	break;
	// 修改提问
	case 'modify_question':
		extract($_POST);
		$qid = intval(getval('id'));
		if(empty($tag)) $tag = array();
		if(!$qid || !$tit) exit('0');
		if(!preg_match($reg_ask_detail, $alias)){
			exit('0');
		}
		if($db->exists('question', "id !={$qid} and alias='{$alias}'")){
			$ret['code'] = -3;
			$ret['msg'] = '该url已存在';
			ex($ret);
		}

		// $db->debug();

		// 存中文tag到question表便于前台展示
		$tagall = getTag();
		$tag_desc = array();
		$insertsql = "insert into {$db->pre}question_tag(qid,typeid) values ";
		foreach ($tag as $typeid) {
			if(isset($tagall[$typeid])){
				$tag_desc[] = $tagall[$typeid];
				$insertsql .= "({$qid}, {$typeid}),";
			}
		}
		$tag_desc = implode(',', $tag_desc);
		$insertsql = rtrim($insertsql, ',');

		$data = array(
			'cont'        => $cont, 
			'tag'         => $tag_desc, 
			'tit'         => $tit, 
			'uptime'      => $time, 
			'alias'       => $alias,
			'keywords'    => $keywords,
			'description' => $description
		);
		if(!empty($view) && preg_match('#^\d+$#', $view)){
			$data['view'] = $view;
		}
		
		// todo .. 过滤用户输入
		if(!$db->update('question', $data, "id={$qid}")){
			$ret['code'] = -1;
			$ret['msg'] = $db->error() . "<br>" . $db->lastsql;
			ex($ret);
		}
		// 更新question_tag表，用于访问ask/tag分类，查询优化
		$db->delete('question_tag', "qid = {$qid}");
		$db->query($insertsql);

		$ret['uptime'] = date('Y-m-d H:i', $time);

		// 标为已显示
		$db->update('question', array('status'=>1), "id={$qid}");
	break;
	// 删除提问
	case 'del':
		$id = intval(getval('qid'));
		if(!$id) exit('0');

		$row = $db->delete('question', "id={$id}");
		$ct = $db->delete('reply', "qid={$id}");
		$db->delete('question_tag', "qid={$id}");
		if(!$row){
			$ret['code'] = -1;
			ex($ret);
		}

		// 问答数--
		qanum('jian', 1 + $ct);
	break;
	// 读取某问题的回复
	case 'loadreply':
		$id = intval(getval('qid'));
		if(!$id) exit('0');

		$row = $db->getAssoc('reply', "qid={$id} order by addtime desc", 'id,uname,cont,addtime,uptime');

		$ret['data'] = $row;
		$ret['total'] = count($row);
		ex($ret);
	break;
	// 删除回复
	case 'del_reply':
		$id = intval(getval('id'));
		if(!$id) exit('0');
		$qid = $db->getOneField('reply', "id={$id}", 'qid');
		if(!$db->delete('reply', "id={$id}")){
			$ret['code'] = -1;
			$ret['msg'] = $db->error() . "<br>" . $db->lastsql;
			ex($ret);
		}

		// 问答数--
		qanum('jian');
		// 回复数--
		$db->update('question', array('replynum'=>'1--'), "id={$qid}");
	break;
	// 修改回复
	case 'modify_reply':
		$id = intval(getval('id'));
		$cont = getval('cont');
		if(!$id || !$cont) exit('0');
		// todo .. 过滤用户输入
		if(!$db->update('reply', array('cont'=>$cont, 'uptime'=>$time), "id={$id}")){
			$ret['code'] = -1;
			$ret['msg'] = $db->error() . "<br>" . $db->lastsql;
			ex($ret);
		}
		$ret['uptime'] = date('Y-m-d H:i', $time);
	break;

	// 设置状态
	case 'set_status':
		$id = intval(getval('id'));
		$status = getval('status');

		$status_all = getStatus();
		$status_all = array_keys($status_all);
		if(!$id || !in_array($status, $status_all)) exit('0');

		$db->update('question', array('status'=>$status/*, 'uptime'=>$time*/), "id={$id}");
	break;
	
	default:
		exit;
}
ex($ret);
