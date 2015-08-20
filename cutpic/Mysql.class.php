<?php
class Mysql {
	public $link_id;
	public $debug = 0;
	public $lastsql; //最后次查询的sql语句
	public $pre;
	// static $instance;
	

	function __construct($cfg) {
		if(!isset($cfg['linkid'])){
			$this->link_id = mysqli_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']) or die('connect to database failed.');
			mysqli_select_db($this->link_id, $cfg['dbname']);
			mysqli_query($this->link_id, "set names '{$cfg['dbcharset']}'");
		}else{
			$this->link_id = $cfg['linkid'];
		}
		$this->pre = $cfg['databasePrefix'];
	}
	
	/*function getInstance($cfg = array()){
		if(!self::$instance instanceof self){
			self::$instance = new self($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['databasePrefix'], $cfg['dbcharset']);
		}
		return self::$instance;
	}*/
	
	/**
	 * 执行一条sql语句
	 *
	 * @param string $sql        	
	 * @return resource/false
	 */
	function query($sql) {
		if ($this->debug) echo $sql . '<br>';
		$this->lastsql = $sql;
		if (!($res = mysqli_query($this->link_id, $sql))) return false;
		return $res;
	}
	
	/**
	 * 返回数组
	 * @param  string  $sql      
	 * @param  boolean $onefield 是否返回第一个字段
	 * @return array            结果数组
	 */
	function getArr($sql, $onefield = false) {
		$ret = array ();
		$func = $onefield ? 'mysqli_fetch_row' : 'mysqli_fetch_assoc';
		if ($res = mysqli_query($this->link_id, $sql)) {
			while ( ($row = $func($res)) !== false ) {
				$ret[] = $onefield ? $row[0] : $row;
			}
		}
		return $ret;
	}
	
	/**
	 * 返回受影响记录数,insert、delete、update
	 *
	 * @param string $sql        	
	 * @return int
	 */
	function affectedRow($sql) {
		if (!$this->query($sql)) return 0;
		return mysqli_affected_rows($this->link_id);
	}
	
	/**
     * 获取一个字段值
     * @param string $table 表名
     * @param string $field 字段名
     * @param string $where
     * @return string
     */
	function getField($table, $field, $where = '') {
		$where = $where ? "where {$where}" : '';
		$sql = "select `{$field}` from `{$this->pre}{$table}` {$where}";
		if (!($res = $this->query($sql))) return '';
		$row = mysqli_fetch_row($res);
		return empty($row[0]) ? '' : $row[0];
	}
	
	/**
     * 获取表的记录总数
     * @param string $table
     * @param string $where
     * @return int
     */
	function getRowNum($table, $condition = '') {
		$condition = strtolower($condition);
		if(strpos($condition, 'group by') !== false){
			preg_match('#group by\s+(.*)[\b]?$#is', $condition,$field);
			$field = $field[1];
			$sql = "SELECT COUNT(*) FROM  (
				SELECT COUNT(*) FROM `{$this->pre}{$table}` GROUP BY {$field} 
				)a";
		}else{
			$sql = "select count(*) from `{$this->pre}{$table}` {$condition}";
		}
		if (!($res = $this->query($sql))) return 0;
		$row = mysqli_fetch_row($res);
		return isset($row[0]) ? $row[0] : 0;
	}
	
	/**
	 * 获取关联数组形式的结果集,
	 *
	 * @param string $table
	 * @param string $condition 完整语句 where id in() 
	 * @param string $field 需要的字段，默认全部
	 * @param string $limit 默认返回:array(0=>array([$k]=>[$v])),如果为true返回:array([$k]=>[$v])
	 * @return array
	 */
	function getAssoc($table, $condition = '', $field = '', $limit = '') {
		$field = empty($field) ? '*' : $this->safe_field($field);
		
		$sql = "select {$field} from {$this->pre}{$table} {$condition}";
		$arr = $this->getAssocBySql($sql);
		return $limit ? (isset($arr[0]) ? $arr[0] : array()) : $arr;
	}

	function getAssocBySql($sql){
		$arr = array ();
		if (!($res = $this->query($sql))) return $arr;
		while ( ($rs = mysqli_fetch_assoc($res)) !== null ) {
			$arr[] = $rs;
		}
		return $arr;
	}

	/**
     * 获取一列组成1维数组
     * @param string $table
     * @param string $colName
     * @param string $where
     * @return array/'' 
     */
	function getCols($table, $colName, $where = '') {
		empty($where) or $where = "where {$where}";
		$sql = "select {$colName} from `{$this->pre}{$table}` {$where}";
		if (!($res = $this->query($sql))) return '';
		while ( ($row = mysqli_fetch_row($res)) !== null ) {
			$arr[] = $row[0];
		}
		return !empty($arr) ? $arr : array();
	}
	
	/**
     * 获取1条关联数组
     * @param string $table
     * @param string $where
     * @param string $field 需要的字段，默认全部
     * @return array/false
     */
	function getOneAssoc($table, $where = '', $field = '') {
		$where = empty($where) ? '' : "where {$where}";
		return $this->getAssoc($table, "{$where} limit 1", $field, 1);
	}
	
	/**
	 * 更新数据
	 * @param  stirng $table 
	 * @param  array $assoc 键值关联数组(字段=>值), 值可传++/--，如'1++',实现字段加1
	 * @param  string $where 
	 * @return int        受影响记录数
	 */
	function update($table, $assoc, $where = '') {
		$set = array ();
		foreach ( $assoc as $k => $v ) {
			if (is_string($v)) {
				//使用:array('field'=>'v++'),实现:set `field`=`field`+v
				if (strpos($v, '++') > 0) {
					$v = "`{$k}`+" . strtr($v, '++', '  ');
				} elseif (strpos($v, '--') > 0) {
					$v = "`{$k}`-" . strtr($v, '--', '  ');
				} else {
					$v = "'" . mysqli_real_escape_string($this->link_id, $v) . "'";
				}
			}
			$set[] = "`{$k}`=" . $v;
		}
		$set = implode(',', $set);
		$where = !empty($where) ? "where {$where}" : '';
		$sql = "update `{$this->pre}{$table}` set {$set} {$where}";
		return $this->affectedRow($sql);
	}
	
	/**
	 * 插入一条数据
	 * @param  string $table
	 * @param  array $assoc 键值数组(字段=>值)
	 * @return int        受影响记录数
	 */
	function insert($table, $assoc) {
		$keys = array_keys($assoc);
		$values = array_values($assoc);
		foreach ( $keys as $k => $v ) {
			$keys[$k] = "`{$v}`";
		}
		foreach ( $values as $k => $v ) {
			if (is_string($v)) $values[$k] = "'" . mysqli_real_escape_string($this->link_id, $v) . "'";
			else $values[$k] = $v;
		}
		$keys = implode(',', $keys);
		$values = implode(',', $values);
		$sql = "insert into `{$this->pre}{$table}`({$keys}) values({$values})";
		return $this->query($sql) ? $this->last_id() : 0;
	}
	
	/**
	 * 删除记录
	 * @param  string $table 
	 * @param  string $where 
	 * @return int
	 */
	function delete($table, $where) {
		$sql = "delete from `{$this->pre}{$table}` where {$where}";
		return $this->affectedRow($sql);
	}
	
	/**
     * 给字段加``
     * @param string $field
     * @return string
     */
	function safe_field($field) {
		if (!strpos($field, ',')) return "`{$field}`";
		
		$temp_arr = explode(',', $field);
		foreach ( $temp_arr as $k => $v ) {
			if(strpos($v, '.') !== false){
				$f = explode('.', $v);
				if($tf = strchr($f[1], ' ')){
					$tt = str_replace($tf, '', $f[1]);
					$f[1] = "`{$tt}` {$tf}";
				}else{
					$f[1] = "`{$f[1]}`";
				}
				$temp_arr[$k] = "{$f[0]}.{$f[1]}";
			}else{
				$temp_arr[$k] = "`{$v}`";
			}
		}
		return implode(',', $temp_arr);
	}

	function exists($table, $condition = ''){
		$condition = !empty($condition) ? "where {$condition}" : '';
		return $this->getRowNum($table, $condition) != 0;
	}
	
	function error() {
		return addslashes(mysqli_error($this->link_id));
	}
	
	function debug() {
		$this->debug = 1;
	}
	
	function last_id() {
		return mysqli_insert_id($this->link_id);
	}
}

?>