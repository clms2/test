<?php
/**
 * mysqli class
 */
class Mysql {
	public $link_id;
	public $debug = 0;
	public $lastsql; //最后次查询的sql语句
	protected $dbname;
	protected $pre;
	private $res = false;
	private $sql = '';
	private $opmap = array(
		'eq'  => '=',
		'neq' => '!=',
		'gt'  => '>',
		'egt' => '>=',
		'lt'  => '<',
		'elt' => '<='	
	);
	// static $instance;
	
	function __construct($cfg) {
		if(empty($cfg['charset'])) $cfg['charset'] = 'utf8';
		if(empty($cfg['pre'])) $cfg['pre'] = '';
		if(empty($cfg['dbport'])) $cfg['dbport'] = '3306';

		$this->link_id = mysqli_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpwd'], $cfg['dbname'], $cfg['dbport']) or die('connect to database failed.');
		mysqli_query($this->link_id, "set names '{$cfg['charset']}'");
		$this->dbname  = $cfg['dbname'];
		$this->pre     = $cfg['pre'];
		$this->resetSql();
	}

	private function resetSql(){
		$this->sql = 'select {field} from {table}';
	}

	/*function getInstance($cfg = array()){
		if(!self::$instance instanceof self){
			self::$instance = new self($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpwd'], $cfg['pre'], $cfg['charset']);
		}
		return self::$instance;
	}*/

	function setSql($sql){
		$this->sql = $sql;
		return $this;
	}

	function select($table){
		$this->sql = str_replace('{table}', $table, $this->sql);
		return $this;
	}
	function field($field){
		$this->sql = str_replace('{field}', $field, $this->sql);
		return $this;
	}

	function where($where = ''){
		if(empty($where)){
			return $this;
		}
		if(is_string($where)){
			$this->sql .= " WHERE $where";
			return $this;
		}

		$temp = '1=1';
		foreach ($where as $field => $value) {
			if(is_array($value)){
				$op = strtolower($value[0]);
				$data = $value[1];
			}else{
				$op = 'eq';
				$data = $value;
			}
			
			if(isset($this->opmap[$op])){
				$op = $this->opmap[$op];
				$data = $this->escape($data);
				$temp .= " AND `{$field}` {$op} {$data}";
				continue;
			}
			switch ($op) {
				case 'in':
				case 'not in':
					if(is_array($data)){
						$data = implode(',', $data);
					}
					$data = "({$data})";
				break;
				case 'between':
				case 'not between':
					if(!is_array($data)){
						$data = explode(',', $data);
					}
					$data = "{$data[0]} AND {$data[1]}";
				break;
				case 'exp':
					$op = '';
				break;
				default:
					$data = $this->escape($data);
			}
			$temp .= " AND `{$field}` {$op} {$data}";

		}
		$this->sql .= " WHERE $temp";

		return $this;
	}

	function escape($value, $quote = true){
		$value = mysqli_real_escape_string($this->link_id, $value);
		return $quote ? "'{$value}'" : $value;
	}

	function order($order){
		$temp = $order;
		if(is_array($order)){
			$temp = array();
			foreach ($order as $field => $value) {
				$temp[] = "`{$field}` {$value}";
			}
			$temp = implode(',', $temp);
			$temp = "ORDER BY {$temp}";
		}
		$this->sql .= " $temp";

		return $this;
	}

	function limit($offset, $num = null){
		$temp = !isset($num) ? $offset : "{$offset}, {$num}";
		$this->sql .= " LIMIT {$temp}";

		return $this;
	}

	/**
	 * 执行一条sql语句
	 *
	 * @param string $sql        	
	 * @return resource/false
	 */
	function query() {
		if ($this->debug) echo $this->sql . '<br>';
		$this->lastsql = $this->sql;
		$this->res = mysqli_query($this->link_id, $this->sql);
		$this->resetSql();
		return $this;
	}

	function getAssoc(){
		if(strpos($this->sql, '{field}') !== false){
			$this->sql = str_replace('{field}', '*', $this->sql);
		}

		$arr = array();
		if(!$this->query()) return $arr;

		while ( ($rs = mysqli_fetch_assoc($this->res)) !== null ) {
			$arr[] = $rs;
		}
		echo '<pre>';
		print_r($arr);
		echo '<br>';
		return $arr;
	}

	/**
   	 * 加锁
   	 * @param  string $table 表名
   	 * @param  string $type  WRITE READ
   	 * WRITE
   	 * 除了当前用户被允许读取和修改被锁表外，其他用户的所有访问被完全阻止。一个 WRITE 写锁被执行仅当所有其他锁取消时。
   	 * READ
   	 * 所有的用户只能读取被锁表，不能对表进行修改（包括执行 LOCK 的用户），当表不存在 WRITE 写锁时 READ 读锁被执行。
   	 * @return [type]        [description]
   	 */
	function lock($table, $type = 'WRITE' ) {
	    $this->query( "LOCK TABLE `{$this->pre}{$table}` {$type}" );
	}
    
    /**
     * 解锁
     */
    function unlock() {
		$this->query( "UNLOCK TABLES" );
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
	function getOneField($table, $field, $where = '') {
		$where = $where ? "where {$where}" : '';
		$sql   = "select `{$field}` from `{$this->pre}{$table}` {$where}";
		if (!($res = $this->query($sql))) return '';
		$row = mysqli_fetch_row($res);
		return $row[0];
	}
	
	/**
     * 获取表的记录总数
     * @param string $table
     * @param string $where
     * @return int
     */
	function getRowNum($table, $condition = '') {
		$sql = "select count(*) from {$table} {$condition}";
		if (!($res = $this->query($sql))) return 0;
		$row = mysqli_fetch_row($res);
		return isset($row[0]) ? $row[0] : 0;
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
		$sql = "select {$colName} from {$this->pre}{$table} {$where}";
		if (!($res = $this->query($sql))) return '';
		while ( ($row = mysqli_fetch_row($res)) !== null ) {
			$arr[] = $row[0];
		}
		return !empty($arr) ? $arr : '';
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
		$set   = implode(',', $set);
		$where = !empty($where) ? "where {$where}" : '';
		$sql   = "update `{$this->pre}{$table}` set {$set} {$where}";
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
		return $this->affectedRow($sql);
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
	 * 是否存在
	 * @param  string $table 
	 * @param  string $where 
	 * @return bool
	 */
	function exists($table, $where){
		$where = "where {$where}";
		return $this->getRowNum($this->pre . $table, $where) > 0 ? true : false;
	}
	
	/**
     * 给字段加`` 带as就不处理
     * @param string $field
     * @return string
     */
	function safe_field($field) {
		if(stripos($field, 'as') !== false) return $field;
		if (!strpos($field, ',')) return "`{$field}`";
		$temp_arr = explode(',', $field);
		foreach ( $temp_arr as $k => $v ) {
			$temp_arr[$k] = "`{$v}`";
		}
		return implode(',', $temp_arr);
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
