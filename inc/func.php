<?php 
function strToBin($str){
	$arr = preg_split('/(?<!^)(?!$)/u', $str);
	// $arr = preg_split('//u', $str,-1, PREG_SPLIT_NO_EMPTY);

	//2.unpack字符
	foreach($arr as &$v){
		$temp = unpack('H*', $v); 
		$v = base_convert($temp[1], 16, 2);
		unset($temp);
	}

	return join(' ',$arr);
}

/**
 * 返回4天前
 * @param  int 10 $timestamp 
 * @return string            
 */
function getDateDesc($timestamp){
	$t = time();
	$cha = $t - $timestamp;
	if($cha < 5*60) return '刚刚';
	if($cha < 3600) return floor($cha/60) . '分钟前';
	if($cha < 24*3600) return floor($cha/3600) . '小时前';
	if($cha < 30*86400) return floor($cha/86400) . '天前';

	return date('Y-m-d', $timestamp);
}
// 前后台公共函数，不试用模板文件

function ex($arr){
	exit(json_encode($arr));
}

function getval($k){
	return isset($_POST[$k]) ? $_POST[$k] : (isset($_GET[$k]) ? $_GET[$k] : '');
}

// 问答数统计表 增加/减少问答数
// 会带来个问题 如果删除的问题不是当天的 那么首页的总数会不准 再传个$day参数也不好，问题回复可能不在一天
// todo
function qanum($type, $num = 1){
	global $db;
	$day = date('Ymd');
	if($type == 'add'){
		if(!$db->exists('qanum', "day='{$day}'")){
			$db->insert('qanum', array('day' => $day));
		}else{
			$db->update('qanum', array('num' => "{$num}++"), "day='{$day}'");
		}
	}else{
		if(!$db->exists('qanum', "day='{$day}'")){
			$db->insert('qanum', array('day' => $day, 'num' => 0));
		}else{
			$db->update('qanum', array('num' => "{$num}--"), "day='{$day}'");
		}
	}
}

/**
 * 数据库question表的status字段对应的描述及颜色class
 * @param  int $v all：返回全部 用于下拉时选择
 * @return array    
 */
function getStatus($v = ''){
	$a = array(
		'-1' => array('cls' => 'grey', 'desc' => '不显示'),
		'0'  => array('cls' => 'yellow', 'desc' => '待审核'),
		'1'  => array('cls' => 'green', 'desc' => '已显示')
	);

	if(!$v) return $a;

	return isset($a[$v]) ? $a[$v] : array('cls' => 'red', 'desc' => '异常');
}

/**
 * 获取所有产品小类，用于问题的分类tag
 * @return array id=>desc
 */
function getTag(){
	// 不用保险产品的小类了
	// $tag = $db->getOneField('field_config', 'field_name="field_pro_stype"', 'data');
	// $tag = unserialize($tag);
	// $tag = $tag['settings']['allowed_values'];

	return getStypeArr('', 'id');
}

/**
 * 从template.php中复制的,多了个$db..数据库操作方法不一样，就不引用了，
 * 后台添加的模板为asklist的文章 正文 转换成 访问链接=>typeid
 * @param  string $str 正文
 * @param  string $key 默认以url为返回数组键
 * @return array array('url' => array('typeid'=>1,'tit'=>title))
 */
function getStypeArr($str = '', $key = 'url'){
	if(empty($str)){
		global $db;
		$str = $db->getOneAssoc('url_alias', "alias='asklist'", 'source');
		$str = $str['source'];
		list($node, $nodeid) = explode('/', $str);
		$str = $db->getOneAssoc('field_revision_body', "entity_id={$nodeid}", 'body_value');
		$str = $str['body_value'];
	}
	$str = explode("\r\n", $str);
	// 拼接成url=>array(typeid, tit)
	$ret = array();
	foreach ($str as $s) {
		list($typeid, $typetit, $typeurl) = explode('|', $s);
		if($key == 'url'){
			$ret[$typeurl] = array('typeid' => $typeid, 'tit' => $typetit);
		}else if($key == 'tit'){
			$ret[$typetit] = array('typeid' => $typeid, 'url' => $typeurl);
		}else{
			$ret[$typeid] = $typetit;
		}
	}

	return $ret;
}

/**
 * 也是从template.php中复制的,改了CMBURL..
 * 处理question表的tag字段
 * @param  string $str 少儿险,意外险
 * @return html string 
 *         <li><a href='http://cmb.com/ask/shaoerxian'>少儿险</a></li>
 *         <li><a href='http://cmb.com/ask/yiwaixian'>意外险</a></li>
 */
function getTagHtml($str = ''){
	if(empty($str)) return '';

	static $types;
	if(empty($types)){
		$types = getStypeArr('', 'tit');
	}

	$str = explode(',', $str);
	$ret = '';
	foreach ($str as $s) {
		if(!isset($types[$s])) continue;
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $types[$s]['url'];
		$ret .= "<li><a href='{$url}' title='{$s}' target='_blank'>{$s}</a></li>";
	}

	return $ret;
}

/**
 * 还是从template.php中复制的,改了CMBURL..
 * 获取保险问问详细页url
 * @param  int $id 
 * @param  bool $fullpath
 * @return string     
 */
function getAskDetailUrl($id, $fullpath = true){
	$s = "ask/{$id}.html";
	if($fullpath)
		$s = "http://{$_SERVER['HTTP_HOST']}/{$s}";
	return $s;
}
