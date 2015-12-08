<?php
/**
 * php分页类，支持动态静态url
 * @author cw
 * @bug 648003174~.
 */
class Page {
	public $url;
	private $maxpageno;
	private $pageno;
	private $suffix = ''; //静态文件后缀
	private $staticSuf = array('.html','.htm');

	/**
	 * 
	 * @param int $total 总数
	 * @param int $pagesize 每页数
	 * @param string $url
	 * @param int $maxpageno 最多页码数 ,包括省略不包括首页上页等,>=7
	 */
	function __construct($total, $pagesize, $url, $maxpageno = 7) {
		$this->pageno = ceil($total / $pagesize);
		$this->url = $url;
		$this->maxpageno = $maxpageno < 7 ? 7 : $maxpageno;
	}
	
	function pagelist($curpage = 1, $curclass = 'in') {
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
		
		$page = $this->getpage($index_url, ' class="index"', '首页') . $this->getpage($pre_url, ' class="pre"', '上一页');
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
		$page .= $this->getpage($next_url, ' class="pre"', '下一页') . $this->getpage($end_url, ' class="index"', '末页');
		
		return $page;
	}
	
	private function getpage($url, $class, $i) {
		return "<a href='{$url}'{$class}>{$i}</a>";
		// return "<li{$class}><a href='{$url}'>{$i}</a></li>";
	}
}
