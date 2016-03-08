<?php 

$a = 'findc';
$wd = 'zc.qq.com';

header('content-type:text/html;charset=utf-8');
include 'inc/Mysql.class.php';
$dbcfg['dbhost'] = '127.0.0.1';
$dbcfg['dbuser'] = 'root';
$dbcfg['dbpwd']  = '123456';
$dbcfg['dbname'] = 'test';
$dbcfg['dbport'] = '';
$dbcfg['pre'] = '';

$db = new Mysql($dbcfg);

set_time_limit(0);

$a = isset($a) ? $a : (isset($_GET['a']) ? $_GET['a'] : 'field');
$wd = isset($wd) ? $wd : (isset($_GET['wd']) ? $_GET['wd'] : '');

if (empty($wd)) exit('empty wd.');

$tables = $db->getArr("show tables from {$dbcfg['dbname']}", true);

switch ($a) {
	case 'field':
		foreach ($tables as $table) {
			$fields = $db->getArr("show fields from {$table}", true);
			if(in_array($wd, $fields)){
				echo 'table:', $table;
				exit;
			}
		}
	break;
	case 'findc':
		foreach ($tables as $table) {
			$fields = $db->getArr("show fields from {$table}", true);
			foreach ($fields as $field) {
				if($db->exists($table, "`{$field}` like '%{$wd}%'")){
					echo 'table:',$table,'|field:',$field,'<br>';
					// exit;
				}
				// echo $db->lastsql;exit;
			}
		}
	break;
	
	default:
		break;
}


