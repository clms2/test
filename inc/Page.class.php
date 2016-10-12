<?php
/**
 * php分页类，支持动态静态url
 * @author cw
 * @bug 648003174~.
 */
class Page {
	public $url;
	public $tpl = '<li><a{class} href="{url}">{pageno}</a></li>';// 分页html模板
	private $maxpageno = 10;// 显示几页
	private $pageno;
	private $suffix = ''; //静态文件后缀
	private $staticSuf = array('.html','.htm');
	private $has_index;// 有首页 
	private $has_last;// 有末页
	// 上一页 下一页文字描述
	private $desc = array(
		'prev' => '‹',// 上一页
		'next' => '›',// 下一页
		'index' => '首页',
		'last' => '末页'
	);

	/**
	 * 
	 * @param int $total 总数
	 * @param int $pagesize 每页数
	 * @param string $url
	 * @param int $maxpageno 最多页码数 ,包括省略不包括首页上页等,>=7
	 */
	function __construct($param) {
		if(empty($param['desc'])) $param['desc'] = array();
		if(empty($param['pagesize'])) $param['pagesize'] = 12;
		if(isset($param['maxpageno'])){
			if($param['maxpageno'] < 7)
				$param['maxpageno'] = 7;
			$this->maxpageno = $param['maxpageno'];
		}

		$this->pageno = ceil($param['total'] / $param['pagesize']);
		$this->url = $param['url'];
		$this->has_index = isset($param['has_index']) ? $param['has_index'] : false;
		$this->has_last  = isset($param['has_last']) ? $param['has_last'] : false;

		$this->desc = array_merge($this->desc, $param['desc']);
	}
	
	function pagelist($curpage = 1, $curclass = 'active') {
		// 如果是静态地址
		if (in_array(strrchr($this->url, '.'), $this->staticSuf) && $pos = strrpos($this->url, '.')) {
			$this->suffix = substr($this->url, $pos);
			$this->url = substr($this->url, 0, $pos);
		}
		//首页上页下页末页
		$index_url = $pre_url = $next_url = $end_url = 'javascript:void(0)';
		if ($curpage > 1) {
			$index_url = "{$this->url}1{$this->suffix}";
			$pre_url = $this->url . ($curpage - 1) . $this->suffix;
		}
		if ($curpage < $this->pageno) {
			$end_url = "{$this->url}{$this->pageno}{$this->suffix}";
			$next_url = $this->url . ($curpage + 1) . $this->suffix;
		}
		
		//省略
		if ($this->pageno > $this->maxpageno) {
			$half = floor(($this->maxpageno - 4) / 2);
			$half_start = $curpage - $half + 1;
			if ($this->maxpageno % 2 !== 0) --$half_start;
			$half_end = $curpage + $half;
		}
		if (($this->pageno - $curpage) < ($this->maxpageno - 3)) {
			$half_start = $this->pageno - $this->maxpageno + 3;
			unset($half_end);
		}
		if ($curpage <= ($this->maxpageno - 3)) {
			$half_end = $this->maxpageno - 2;
			unset($half_start);
		}
		$page = '';
		if($this->has_index){
			$page .= $this->getpage($index_url, ' class="index"', $this->desc['index']);
		}
		$page .= $this->getpage($pre_url, ' class="pre"', $this->desc['prev']);
		
		for($i = 1; $i <= $this->pageno; $i++) {
			if (isset($half_start) && $i < $half_start && $i > 1) {
				if ($i == 2) $page .= $this->getpage('javascript:void(0)', ' class="pageinfo"', '...');
				continue;
			}
			if (isset($half_end) && $i > $half_end && $i < $this->pageno) {
				if ($i == ($half_end + 1)) $page .= $this->getpage('javascript:void(0)', ' class="pageinfo"', '...');
				continue;
			}
			
			if ($i == $curpage) {
				$in = " class='{$curclass}'";
				$url = 'javascript:void(0)';
			} else {
				$in = '';
				$url = $this->url . $i . $this->suffix;
			}
			$page .= $this->getpage($url, $in, $i);
		}
		$page .= $this->getpage($next_url, ' class="pre"', $this->desc['next']);
		if($this->has_last){
			$page .= $this->getpage($end_url, ' class="index"', $this->desc['last']);
		}
		
		return $page;
	}

	public function setTpl($tpl){
		$this->tpl = $tpl;
	}
	
	private function getpage($url, $class, $i) {
		return strtr($this->tpl, array(
			'{class}'  => $class,
			'{url}'    => $url,
			'{pageno}' => $i
		));
	}
}
