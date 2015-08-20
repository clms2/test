<?php
// 初始化用户模块数据
function initdata($tempid, $uid){
	global $db,$pre;
	//$db->debug = 1;
	$mids = $db->getField('temp', 'moduleid', "id={$tempid}");
	$mids_arr = explode(',', $mids);
	if(!empty($mids)){
		$muserid = $db->getCols('module_user', 'id',"uid={$uid} and tempid={$tempid}");
		if(!empty($muserid)){
			$muserid = implode(',', $muserid);
			$db->delete('module_user_extra', "muserid in ({$muserid})");
			$db->delete('module_user', "id in ({$muserid})");
		}

		$sql = "insert into {$pre}module_user(uid,tempid,moduleid) values";
		foreach ($mids_arr as $moduleid) {
			$sql .= "({$uid},{$tempid},{$moduleid}),";
		}
		$sql = rtrim($sql,',');
		$db->query($sql);
	}
	
	$sql = "update {$pre}module_user set rank = id where tempid={$tempid} and uid={$uid}";
	$db->query($sql);
}

// 根据id=>rank的数组生成更新排序的sql
function getRankSql($arr){
	global $pre;

	$ids = '';
	$sql = "UPDATE {$pre}module_user SET rank = 
			CASE id ";
	foreach ($arr as $id => $rank) {
		$ids .= "{$id},";
		$sql .= "WHEN {$id} THEN {$rank} ";
	}
	$ids = rtrim($ids, ',');
	$sql .= "END WHERE id IN($ids)";

	return $sql;
}

function add_module($mid, $uid, $tempid){
	global $db;

	$ret = array();
	do{
		$mid = intval($mid);
		$module = $db->getOneAssoc('module', "id={$mid}", 'id,module,module_param,title');
		if(empty($module)){
			$ret['code'] = 1;
			break;
		}

		$default = json_decode($module['module_param'], 1);
		if (!is_array($default)){
			$ret['code'] = 2;
			break;
		}
		
		$assoc = array();
		$assoc['uid'] = $uid;
		$assoc['tempid'] = $tempid;
		$assoc['moduleid'] = $mid;
		$muserid = $db->insert('module_user', $assoc);
		if(!$muserid){
			$ret['code'] = 3;
			break;
		}
		$db->update('module_user', array('rank'=>$muserid), "id={$muserid}");

		$op = array();
		$op['type'] = 'add_module';
		$op['info'] = array('mid'=>$muserid, 'moduleid'=>$mid, 'id'=>"muser{$muserid}");
		
		$ret['op'] = $op;

		$default['{muserid}'] = "id='{$muserid}'";
		$default['{moduleid}'] = $mid;
		$ret['code'] = 0;
		$ret['rank'] = $muserid;
		$ret['html'] = str_replace(array_keys($default), array_values($default), htmlspecialchars_decode($module['module']));
		$ret['title'] = $module['title'];

	}while(0);

	return $ret;
}

/**
 * 截图
 * @param  string $outdir 保存的文件夹
 * @param  string $url    需截图地址
 * @return string         生成图片路径;0:失败;1:未登陆
 */
function screenshoot($outdir, $url){
	if(!isset($_SESSION['uid'])) return 1;

	$uid = $_SESSION['uid'];
	$folder = $outdir . '/' . date('Ymd');
	!is_dir($folder) && mkdir($folder, 0777 ,1);

	$url .= '&isshoot=1&uid=' . $_SESSION['uid'];
	$out = $folder."/{$uid}_".date('His') . ".jpg";
	$cmd = '1.bat "'.$url.'" "'.$out.'"';
	`$cmd`;

	if (!file_exists($out)) return 0;
	if(isset($_SESSION['temp_user_id'])){
		global $db;
		//删除原图
		$info = $db->getOneAssoc('temp_user', "id={$_SESSION['temp_user_id']}", 'pic');
		if(!empty($info['pic']) && file_exists($info['pic'])){
			unlink($info['pic']);
		}
		$db->update('temp_user', array('pic'=>$out), "id={$_SESSION['temp_user_id']}");
	}

	return $out;
}

/**
 * 模板数据统计
 * @param  enum $field  view/update/edit
 * @param  int $tempid 模板id
 * @param  int $designid 设计师id
 * @return null
 */
function _count($field, $tempid, $designid){
	global $db;

	$where = "tempid={$tempid} and uid=";
	$where .= isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

	// 除了更新宝贝的次数每次都记录外，编辑和查看次数1小时只算1次
	/*if($field != 'update'){
		if(!isset($_SESSION['lastcount'][$field.$tempid])){
			$_SESSION['lastcount'][$field.$tempid] = time();
		}else{
			if((time() - $_SESSION['lastcount'][$field.$tempid]) < 3600) return;
		}
	}*/

	if(!$db->exists('count', $where)){
		$assoc = array();
		if(isset($_SESSION['uid'])) 
			$assoc['uid']  = $_SESSION['uid'];
		$assoc['tempid']   = $tempid;
		$assoc['designid'] = $designid;
		$assoc[$field]     = 1;

		$db->insert('count', $assoc);
	}else{
		$db->update('count', array($field=>'1++'), $where);
	}
}

function _count_type($typeid){
	global $db;

	$where = "typeid={$typeid} and uid=";
	$where .= isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

	if(!$db->exists('count_type', $where)){
		$assoc = array();
		if(isset($_SESSION['uid'])) 
			$assoc['uid']  = $_SESSION['uid'];
		$assoc['typeid']   = $typeid;
		$assoc['view']     = 1;

		$db->insert('count_type', $assoc);
	}else{
		$db->update('count_type', array('view'=>'1++'), $where);
	}
}

function _count_style($styleid){
	global $db;

	$where = "styleid={$styleid} and uid=";
	$where .= isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

	if(!$db->exists('count_style', $where)){
		$assoc = array();
		if(isset($_SESSION['uid'])) 
			$assoc['uid']  = $_SESSION['uid'];
		$assoc['styleid']   = $styleid;
		$assoc['view']     = 1;

		$db->insert('count_style', $assoc);
	}else{
		$db->update('count_style', array('view'=>'1++'), $where);
	}
}

// x.jpg => x_s.jpg
function get_spic($pic){
	$ext = strrchr($pic, '.');
	return rtrim($pic, $ext) . '_s' . $ext;
}

//获取模板使用次数
function get_use_count($tempid){
	global $db;
	$sql = "SELECT COUNT(*) c FROM `{$db->pre}count` WHERE tempid={$tempid} AND uid!=0 AND edit!=0";
	$use_count = $db->getAssocBySql($sql);
	if(isset($use_count[0])){
		$use_count = $use_count[0];
		$use_count = !empty($use_count['c']) ? $use_count['c'] : 0;
	}else{
		$use_count = 0;
	}

	return $use_count;
}