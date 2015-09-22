<?php 
require 'MysqlObj.class.php';

$dbcfg['dbhost'] = '127.0.0.1';
$dbcfg['dbuser'] = 'root';
$dbcfg['dbpwd']  = '123456';
$dbcfg['dbname'] = 'test';
$dbcfg['pre'] = '';

$db = new Mysql($dbcfg);

$where = array(
	'id'=> array('between', array(1,3)),
	'max'=> 15
);

header('content-type:text/html;charset="utf-8"');

$db->setSql("select * from tb_zhuanpan_reward")
	->where($where)
	->order(array('max'=>'desc','id'=>'asc'))
	->getAssoc();

$db->select('tb_zhuanpan_reward')
	->where($where)
	->order(array('max'=>'desc','id'=>'asc'))
	->getAssoc();
