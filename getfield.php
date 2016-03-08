<?php 

$a = isset($_GET['a']) ? $_GET['a'] : 'findc';
$wd = isset($_GET['wd']) ? $_GET['wd'] : 'zc.qq.com';

if (empty($wd)) exit('empty wd.');

header('content-type:text/html;charset=utf-8');
echo '<pre>';

include 'zhuanpan/public/inc/Mysql.class.php';
$dbcfg['dbhost'] = '127.0.0.1';
$dbcfg['dbuser'] = 'root';
$dbcfg['dbpwd']  = '123456';
$dbcfg['dbname'] = 'cmb_insurance';
$dbcfg['dbport'] = '';
$dbcfg['pre'] = '';

$db = new Mysql($dbcfg);

set_time_limit(0);

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
	
	default:
		break;
}


