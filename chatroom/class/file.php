<?php
class file {
    private $name; // 文件名
    private $dir; // 文件夹目录
    

    private $root; // 根目录
    private $fp; // 文件资源
    private $fullpath; // 完整路径
    

    /**
	 *
	 * @param string $path 相对根目录路径
	 */
    function __construct($path) {
        $this->name = basename($path);
        $dir = substr($path, 0, strpos($path, $this->name));
        $this->dir = $dir == '' ? $this->name : $dir;
        $this->root = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        $this->fullpath = $this->root . $path;
    }
    /**
	 * 若content为数组则生成return array()形式的文件
	 * @param string/array $content
	 * @return boolean
	 */
    function mk_file($content = '') {
        if ($this->name == $this->dir) {
            $this->fp = 0;
        } else {
            !is_dir($this->dir) && mkdir($this->root . $this->dir, 0777, true);
            $this->fp = fopen($this->fullpath, 'w') or false;
            if ($content && $this->fp) {
                if (is_array($content)) {
                    $content = "<?php return " . var_export($content, 1) . " ?>";
                }
                if (!is_writable($this->fullpath)) {
                    $this->close();
                    die('写入文件失败：' . $this->fullpath);
                }
                fwrite($this->fp, $content);
            }
        }
        return $this->fp ? true : false;
    }
    
    /**
	 *
	 * @param 是否为返回数组形式的php文件 $returnarr        	
	 * @return string
	 */
    function read($returnarr = false) {
        if (is_readable($this->fullpath) && file_exists($this->fullpath)) {
            if ($returnarr) {
                return include $this->fullpath;
            } else {
                return file_get_contents($this->fullpath);
            }
        } else {
            die('文件不存在或不可读：' . $this->fullpath);
        }
    }
    
    /**
	 * 删除指定模式的所有文件
	 *
	 * @param string $patten        	
	 */
    function glob_del($patten = '*.*') {
        if ($this->dir && is_dir($this->dir)) array_map('unlink', glob($this->dir . $patten));
    }
    
    function deldir($dir) {
        if (is_dir($dir)) {
            array_map(create_function('$file', 'is_file($file) ? unlink($file) : deldir($file);'), glob($dir . '/*'));
            rmdir($dir);
        }
    }
    
    function globdir($dir) {
        static $file_arr = array ();
        if (is_dir($dir)) {
            array_walk(glob($dir . '/*'), 'walk_func', &$file_arr);
        }
        return $file_arr;
    }
    
    function walk_func($file, $k, $file_arr) {
        is_file($file) ? $file_arr[] = $file : globdir($file);
    }
    
    function __destruct() {
        $this->close();
    }
    
    private function close() {
        is_resource($this->fp) ? fclose($this->fp) : '';
    }

}

?>