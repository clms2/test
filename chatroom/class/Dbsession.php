<?php
/**
 * session save handle:db
 * @author Administrator
 *
 */
include_once 'mysql.php';
$dbhost = '127.0.0.1';
$dbuser = 'root';
$dbpwd = '123456';
$dbname = 'test';
$tbname = 'session';
$gctime = 600; //session.gc_maxlifetime


ob_start();
class Dbsession {
    static $db;
    static $max_time;
    static $table;
    static function run($db = null) {
        self::$db = !isset($db) ? new mysql($GLOBALS['dbhost'], $GLOBALS['dbuser'], 
                $GLOBALS['dbpwd'], $GLOBALS['dbname']) : $db;
        self::$table = $GLOBALS['tbname'];
        self::$max_time = $GLOBALS['gctime'];
        self::$db->query('create table if not exists ' . self::$table . '(
				sid char(32) primary key,sdata varchar(200) default "",lastmodify int(10) not null,
				remark varchar(100) null
		)engine=myisam default charset=utf8');
        session_set_save_handler(array (
            __CLASS__, 'open' 
        ), array (
            __CLASS__, 'close' 
        ), array (
            __CLASS__, 'read' 
        ), array (
            __CLASS__, 'write' 
        ), array (
            __CLASS__, 'destroy' 
        ), array (
            __CLASS__, 'gc' 
        ));
    }
    
    static function open($path, $sname) {
        return true;
    }
    
    static function close() {
        return true;
    }
    
    static function read($sid) {
        $num = self::$db->rowNum("select `sdata` from `" . self::$table . "` where `sid`='{$sid}'");
        $data = '';
        if ($num <= 0) {
            self::$db->insert(self::$table, array (
                'sid' => $sid, 'lastmodify' => time() 
            ));
        } else {
            $data = self::$db->getField('sdata', self::$table, "`sid`='{$sid}'");
        }
        //self::gc();
        return $data;
    }
    
    static function write($sid, $data) {
        self::$db->update(self::$table, array (
            'sdata' => $data, 'lastmodify' => time() 
        ), "`sid`='{$sid}'");
        return true;
    }
    
    static function destroy($sid) {
        self::$db->delete(self::$table, "`sid`='{$sid}'");
        return true;
    }
    
    static function gc() {
        self::$db->delete(self::$table, '`lastmodify`+' . self::$max_time . '<' . time());
        return true;
    }
}
Dbsession::run();
session_start();
