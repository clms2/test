<?php 
require 'Mysql.class.php';

$dbcfg = array();
require_once $_SERVER['DOCUMENT_ROOT'] . '/sites/default/settings.php';
$temparr = $databases['default']['default'];
$dbcfg['dbhost'] = $temparr['host'];
$dbcfg['dbuser'] = $temparr['username'];
$dbcfg['dbpwd']  = $temparr['password'];
$dbcfg['dbname'] = $temparr['database'];
$dbcfg['dbport'] = $temparr['port'];
$dbcfg['pre']    = '';

$db = new Mysql($dbcfg);

define('DEBUG', 1);
