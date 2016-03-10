<?php 
/*
1.查找某个字段在数据库的哪个表中
2.查找数据库某内容在哪个表哪个字段
3.后台操作反应到数据库的记录变化
*/

$sesspath = './session';
$sid = 'qeo1nga5uu4i0p3r943a30r2u5';
$primaryIds = array('id', 'entity_id');
$sidfile = rtrim($sesspath, '/') . "/sess_{$sid}";

!is_dir($sesspath) && mkdir($sesspath);
!file_exists($sidfile) && touch($sidfile);

session_save_path($sesspath);
session_id($sid);
session_start();

$a = isset($_GET['a']) ? $_GET['a'] : 'beforeChange';
$wd = isset($_GET['wd']) ? $_GET['wd'] : 'zc.qq.com';

if (empty($wd)) exit('empty wd.');

header('content-type:text/html;charset=utf-8');
echo '<pre>';

include 'zhuanpan/public/inc/Mysql.class.php';
$dbhost = '127.0.0.1';
$dbuser = 'root';
$dbpwd = '123456';
$dbname = 'cmb_insurance';

$db = new Mysql(array(
	'dbhost' => $dbhost,
	'dbuser' => $dbuser,
	'dbpwd'  => $dbpwd,
	'dbname' => $dbname,
	'dbport' => '',
	'pre'    => ''
));

set_time_limit(0);

$tables = $db->getArr("show tables from {$dbname}", true);

switch ($a) {
	// 查找字段在哪个表
	case 'findf':
		foreach ($tables as $table) {
			$fields = $db->getArr("show fields from {$table}", true);
			if(in_array($wd, $fields)){
				echo 'table:', $table,'<br>';
			}
		}
	break;
	// 查找指定内容在哪个表哪个字段
	case 'findc':
		foreach ($tables as $table) {
			$fields = $db->getArr("show fields from {$table}", true);
			foreach ($fields as $field) {
				$row = $db->getAssoc($table, "`{$field}` like '%{$wd}%'");
				if(!empty($row)){
					echo 'table:',$table,'|field:',$field,'<br>';
					print_r($row);
					echo '<hr>';
					// exit;
				}
				// echo $db->lastsql;exit;
			}
		}
	break;
	// 后台操作反应到数据库的记录变化，反应后台insert操作
	case 'beforeChange':
		!isset($_SESSION[$dbname]) && $_SESSION[$dbname] = array();
		foreach ($tables as $table) {
			$record = array();
			$primaryid = '';

			$record['rownum'] = $db->getRowNum($table);
			$fields = $db->getArr("show fields from {$table}", true);
			// 默认以primaryIds中的键作为自增主键并以此作为Change依据
			// todo .. 需要改啊
			foreach ($primaryIds as $id) {
				if(in_array($id, $fields)){
					$primaryid = $id;
					break;
				}
			}
			if($primaryid){
				// 最后一条记录的id记下来
				$lastid = $db->getOneField($table, $primaryid, "1=1 order by {$primaryid} desc limit 1");
				$record['lastid'] = $lastid;
				echo $db->lastsql,'<br>',$lastid;
				exit;
			}

			$_SESSION[$dbname][$table] = $record;
		}
	break;
	// 后台操作完后访问，显示数据库变化
	case 'showChange':


	break;
	
	default:
		break;
}


