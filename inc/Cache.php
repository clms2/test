<?php 
/**
* 缓存类
*/
class Cache{
	public $dir; //缓存存放文件夹
	
	function __construct($param = array()){
		$this->dir = isset($param['cache_dir']) ? $param['cache_dir'] : './cache';

		$this->dir = rtrim($this->dir, '/') . '/';
		!is_dir($this->dir) && mkdir($this->dir, 0777, true);
	}

	/**
	 * 写入缓存文件
	 * @param string $cacheid 缓存key 文件名 不能有重复
	 * @param string/array $content 内容
	 * @param int $expire  过期时间 指定的过期时间 如time()+86400:1天过期 
	 */
	function set($cacheid, $content, $expire = null){
		$filepath = $this->dir . $cacheid;
		$content = json_encode($content);
		// 在文件开头写上过期时间
		if(isset($expire)){
			$content = $expire . '|exp|' . $content;
		}

		file_put_contents($filepath, $content);
	}


	/**
	 * 读取缓存
	 * @param  string $cacheid 缓存key 文件名 不能用重复
	 * @return array/false     如果缓存已过期/文件不存在则返回false，需重新取数据，否则返回缓存数组
	 */
	function get($cacheid){
		$filepath = $this->dir . $cacheid;
		if(!file_exists($filepath)) return false;
		$content = file_get_contents($filepath);
		$tag = substr($content, 10, 5);
		// 有设置过期时间
		if($tag == '|exp|'){
			$expire = substr($content, 0, 10);
			if(time() > $expire) return false;
			$content = substr($content, 15);
		}

		return json_decode($content, 1);
	}
}
