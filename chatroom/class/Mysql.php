<?php
class Mysql {
    private $link_id;
    public $debug = 0;
    public $lastsql; //最后次查询的sql语句
    

    function Mysql($dbhost, $dbuser, $dbpwd, $dbname, $link_id = null, $charset = 'utf8') {
        if (isset($link_id)) {
            $this->link_id = $link_id;
        } else {
            $this->link_id = mysql_connect($dbhost, $dbuser, $dbpwd) or die(mysql_error());
            mysql_select_db($dbname, $this->link_id);
            mysql_query("set names '{$charset}'", $this->link_id);
        }
    }
    
    /**
	 * 执行一条sql语句
	 *
	 * @param string $sql        	
	 * @return resource/false
	 */
    function query($sql) {
        if ($this->debug) echo $sql . '<br>';
        $this->lastsql = $sql;
        if (!($res = mysql_query($sql, $this->link_id))) return false;
        return $res;
    }
    
    /**
	 * 返回受影响记录数,insert、delete、update
	 *
	 * @param string $sql        	
	 * @return int
	 */
    function affectedRow($sql) {
        if (!$this->query($sql)) return 0;
        return mysql_affected_rows($this->link_id);
    }
    
    /**
     * 获取一个字段值
     * @param string $table 表名
     * @param string $where
     * @param string $field 字段名
     * @return string
     */
    function getOneField($table, $where, $field) {
        $sql = "select `{$field}` from `{$table}` where {$where}";
        if (!($res = $this->query($sql))) return '';
        $row = mysql_fetch_row($res);
        return $row[0];
    }
    
    /**
     * 获取表的记录总数
     * @param string $table
     * @param string $condition
     * @return int
     */
    function getRowNum($table, $condition = '') {
        $sql = "select count(*) from {$table} {$condition}";
        if (!($res = $this->query($sql))) return 0;
        $row = mysql_fetch_row($res);
        return isset($row[0]) ? $row[0] : 0;
    }
    
    /**
	 * 获取关联数组形式的结果集,
	 *
	 * @param string $table
	 * @param string $condition 条件,需带完整陈述，如where id=1
	 * @param string $field 需要的字段，默认全部
	 * @param string $limit 默认返回:array(0=>array([$k]=>[$v])),如果为true返回:array([$k]=>[$v])
	 * @return array
	 */
    function getAssoc($table, $condition = '', $field = '', $limit = '') {
        $field = !$field ? '*' : $this->safe_field($field);
        $sql = "select {$field} from {$table} {$condition}";
        if (!($res = $this->query($sql))) return false;
        while ( ($rs = mysql_fetch_assoc($res)) !== false ) {
            $arr[] = $rs;
        }
        return $limit ? isset($arr[0]) ? $arr[0] : '' : $arr;
    }
    
    /**
     * 获取一列组成1维数组
     * @param string $table
     * @param string $condition
     * @param string $colName
     * @return string|Ambigous <string, unknown>
     */
    function getCols($table, $condition = '', $colName) {
        $sql = "select {$colName} from {$table} {$condition}";
        if (!($res = $this->query($sql))) return '';
        while ( ($row = mysql_fetch_row($res)) !== false ) {
            $arr[] = $row[0];
        }
        return !empty($arr) ? $arr : '';
    }
    
    /**
     * 获取1条关联数组
     * @param string $table
     * @param string $where 条件,如id=1
     * @param string $field 需要的字段，默认全部
     * @return array/false
     */
    function getOneAssoc($table, $where, $field = '') {
        return $this->getAssoc($table, "where {$where} limit 1", $field, 1);
    }
    
    /**
	 * 更新数据
	 *
	 * @param 表名 $table        	
	 * @param 键值关联数组 $assoc        	
	 * @param string $where        	
	 * @return int
	 */
    function update($table, $assoc, $where) {
        $set = array ();
        foreach ( $assoc as $k => $v ) {
            if (is_string($v)) {
                //使用:array('field'=>'v++'),实现:set `field`=`field`+v
                if (strpos($v, '++') > 0) {
                    $v = "`{$k}`+" . strtr($v, '++', '  ');
                } elseif (strpos($v, '--') > 0) {
                    $v = "`{$k}`-" . strtr($v, '--', '  ');
                } else {
                    $v = "'" . mysql_real_escape_string($v, $this->link_id) . "'";
                }
            }
            $set[] = "`{$k}`=" . $v;
        }
        $set = implode(',', $set);
        $where = $where ? "where {$where}" : '';
        $sql = "update `{$table}` set {$set} {$where}";
        return $this->affectedRow($sql);
    }
    
    /**
     * 用case when更新多条记录
     * @param unknown_type $table
     * @param unknown_type $assoc
     * @param array $case_arr array($when=>array($v,$v2),$value=>array($v,$v2));
     * @param unknown_type $where
     */
    function multi_update($table, $assoc, $case_arr, $where) {
        /*  $q = "update {$BIAOTOU }user set `{$reward_type}`=`{$reward_type}`+ case id";
        foreach ($ids as $id){
            $q .= " when {$id} then {$count[$i]} ";
            $i++;
        }
        $ids = implode(',', $ids);
        $q.= "end where id in ({$ids})"; */
    }
    
    /**
	 * 插入一条数据
	 *
	 * @param 表 $table        	
	 * @param 键值数组 $assoc        	
	 * @return int
	 */
    function insert($table, $assoc) {
        $keys = array_keys($assoc);
        $values = array_values($assoc);
        foreach ( $keys as $k => $v ) {
            $keys[$k] = "`{$v}`";
        }
        foreach ( $values as $k => $v ) {
            if (is_string($v)) $values[$k] = "'" . mysql_real_escape_string($v, $this->link_id) . "'";
            else $values[$k] = $v;
        }
        $keys = implode(',', $keys);
        $values = implode(',', $values);
        $sql = "insert into `{$table}`({$keys}) values({$values})";
        return $this->affectedRow($sql);
    }
    
    /**
	 * 删除记录
	 *
	 * @param 表 $table        	
	 * @param 条件 $where        	
	 * @return int
	 */
    function delete($table, $where) {
        $sql = "delete from `{$table}` where {$where}";
        return $this->affectedRow($sql);
    }
    
    /**
     * 给字段加``
     * @param string $field
     * @return string
     */
    private function safe_field($field) {
        if (!strpos($field, ',')) return "`{$field}`";
        $temp_arr = explode(',', $field);
        foreach ( $temp_arr as $k => $v ) {
            $temp_arr[$k] = "`{$v}`";
        }
        return implode(',', $temp_arr);
    }
    
    function error() {
        return addslashes(mysql_error($this->link_id));
    }
    
    function debug() {
        $this->debug = 1;
    }
    
    function last_id() {
        return mysql_insert_id($this->link_id);
    }
    
    /*    private function get_sql($table,$where,$field){
        
        return "select {$field} from {$table} where {$where}";
    } */
}

?>