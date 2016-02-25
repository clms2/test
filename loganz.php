<?php 
/**
 * iis log analyze
 */
include 'class/Page.class.php';

$logfile = './cmblog/u_ex160130.log';
$sesspath = './session';
$sid = 'geo1nga5uu4i0p3r943a30r2u4';
$allow_ext = array('jpg', 'gif', 'css', 'js', 'ico', 'php?q=financing/');
$allow_method = array('GET', 'POST');


$sidfile = rtrim($sesspath, '/') . "/sess_{$sid}";
define('SEEKNAME', strchr(basename($logfile), '.', true));
!is_dir($sesspath) && mkdir($sesspath);
!file_exists($sidfile) && touch($sidfile);

session_save_path($sesspath);
session_id($sid);
session_start();

/**
 * get total line number of file
 * @param  string $file file path
 * @return int       	666
 */
function getLineNo($file){
	$fp = fopen($file, 'r');
	if(!$fp) return 0;
	$i = 0;
	while(stream_get_line($fp, 1024, "\n")) ++$i;
	fclose($fp);

	return $i;
}

/**
 * 根据offset设置文件当前指针位置，返回offset之前的字符串总长度
 * set file pointer by line no,return total string bytes before this line
 * @param resource $fp     
 * @param int $offset line number
 * @return int  total string bytes before line no
 */
function setOffset($fp, $offset){
	// no cache then calculate
	if(!isset($_SESSION[SEEKNAME][$offset])){
		$i = 0;
		$linesize = 0;
		while (++$i < $offset) {
			$linesize += strlen(fgets($fp));
		}
		$_SESSION[SEEKNAME][$offset] = $linesize;
	}else{
		$linesize = $_SESSION[SEEKNAME][$offset];
		fseek($fp, $linesize);
	}

	return $linesize;
}

/**
 * url suffix
 * @param  string $file http://xx.com/images/2.jpg
 * @return string       jpg
 */
function getext($file){
	$file = basename($file);
	return ltrim(strrchr($file, '.'), '.');
}



/**
 * init
 * array(offset => linesize)
 */
if(!isset($_SESSION[SEEKNAME])) $_SESSION[SEEKNAME] = array();
if(!isset($_SESSION[SEEKNAME]['total'])) $_SESSION[SEEKNAME]['total'] = getLineNo($logfile);


$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pagesize = isset($_GET['pagesize']) ? intval($_GET['pagesize']) : 30;
$offset = ($page - 1) * $pagesize;


// start analyzing log file from line number:$offset to $pagesize+$offset
$fp = fopen($logfile, 'r');
$linesize = setOffset($fp, $offset);

$i = 0;
$newsize = 0;
$ret = array();
$http_code = array();
while (!feof($fp) && ++$i <= $pagesize) {
	$line = fgets($fp);
	if(empty($line)){
		echo 'empty line no:',$i+$offset,'<hr>';
		continue;
	}
	$newsize += strlen($line);

	if(substr($line, 0, 1) === '#'){
		--$i;
		continue;
	}

	$line   = explode(' ', $line);
	$time   = $line[0] . ' ' . $line[1];
	$method = $line[3];
	$url    = $line[4];
	$port   = $line[6];
	$ip     = $line[8];
	$ua     = $line[9];
	$code   = $line[10];

	if($line[5] != '-') $url .= "?{$line[5]}";

	$row = compact('time', 'method', 'url', 'port', 'ip', 'ua', 'code');

	$ext = getext($url);
	$status = 'green';
	// need warning requests
	if(!in_array($method, $allow_method) || $ua == '-' || !in_array($ext, $allow_ext)){
		$status = 'red';
	}
	// unique http-code, use for filter select options
	if(!in_array($code, $http_code)) $http_code[] = $code;

	$row['status'] = $status;
	$ret[] = $row;
}
fclose($fp);
// next page offset=>linesize
$_SESSION[SEEKNAME][$offset+$pagesize] = $newsize + $linesize;


$pager = new Page($_SESSION[SEEKNAME]['total'], $pagesize, "?pagesize={$pagesize}&page=");

// echo '<pre>';
// print_r($_SESSION);

// exit;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<script src="jq.js"></script>
	<style type="text/css">
		*{margin: 0;padding: 0}
		body{font: small "Lucida Grande",Verdana,sans-serif;}
		a{text-decoration: none;outline: none;}

		.red{background-color: #bbb !important;}
		.green{background-color: #ecf0f1 !important;}
		.grey{background-color: #959595 !important;}
		.yellow{background-color: #d9ab0b !important;}

		#toolbar{margin-bottom: 15px;padding-bottom: 10px;padding-left: 20px;margin-top: 35px;border-bottom: 1px solid #ccc;}
		#toolbar>*{display: inline-block;margin:0 10px;font-size: 14px;}

		table{width: 100%;margin: 20px auto;border-collapse: collapse;}
		table tr{height: 40px;}
		table tr.white{height:1px;}
		table th,table td{font-size: 12px;}
		table th{color: #d1d1d1;text-align: left;padding-left: 10px;}
		table td{padding-left: 10px;color: #000;}
		table a,table .operate{color: #777;}
		table a:hover{border-color: #777;}
		table th p{border-right: 1px solid #f4f4f4;}

		#bar{position: fixed;background-color: #fff;top: 85px;margin: 0}
		/*#content thead{visibility: hidden;}*/

		#pagelist{margin-top: 15px;}
		#pagelist a{display: inline-block;padding: 2px 4px;border:1px solid #ccc;margin-right: 5px;}
		#pagelist a.in{border-color: #fff;}
	</style>
</head>
<body>
	<div id="toolbar">
		<a href="javascript:void(0)" data-flag="hide" id="filter_normal">hide normal</a>|
		http-code : 
		<select class="jsfilter" data-type="code">
			<option value="">all</option>
			<?php sort($http_code);array_walk($http_code, function($code){ ?>
			<option value="<?php echo $code ?>"><?php echo $code ?></option>
			<?php }); ?>
			
		</select>|
		method : 
		<select class="jsfilter" data-type="method">
			<option value="">all</option>
			<option value="GET">GET</option>
			<option value="POST">POST</option>
			<option value="HEAD">HEAD</option>
		</select>
		pagesize : <input type="text" style="width:3em;" value="<?php echo $pagesize ?>" id="set_pagesize" />|
		ip : <input type="text" placeholder="only full ip work" style="width:8em;" class="jsfilter" data-type="ip">
		url not contains : <input type="text" placeholder="part url work" style="width:8em;" id="filter_url" data-type="url">
	</div>
	<!-- <table id="bar" width="100%" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th width="4%"><p>No.</p></th>
				<th width="13%"><p>time</p></th>
				<th width="5%"><p>method</p></th>
				<th><p>url</p></th>
				<th width="4%"><p>code</p></th>
				<th width="8%"><p>ip</p></th>
				<th width="8%"><p>ua</p></th>
			</tr>
		</thead>
	</table> -->
	<table id="content" width="100%" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th width="4%"><p>No.</p></th>
				<th width="13%"><p>time</p></th>
				<th width="5%"><p>method</p></th>
				<th width="4%"><p>code</p></th>
				<th width="8%"><p>ip</p></th>
				<th><p>url</p></th>
				<th width="8%"><p>ua</p></th>
			</tr>
		</thead>
		<tbody id="list">
			<?php $i = 0;foreach ($ret as $arr) {?>
			<tr data-method="<?php echo $arr['method'] ?>" data-ip="<?php echo $arr['ip'] ?>" data-code="<?php echo $arr['code'] ?>" class="<?php echo $arr['status'] ?>">
				<td><p><?php echo ++$i; ?></p></td>
				<td><p><?php echo $arr['time'] ?></p></td>
				<td><p><?php echo $arr['method'] ?></p></td>
				<td><p><?php echo $arr['code'] ?></p></td>
				<td><p><?php echo $arr['ip'] ?></p></td>
				<td><p class="url"><?php echo $arr['url'] ?></p></td>
				<td><p title="<?php echo $arr['ua'] ?>"><?php echo $arr['ua'] == '-' ? '-' : 'user agent';?></p></td>
			</tr>
			<tr class="white">
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<div id="pagelist">
		<?php echo $pager->pagelist($page) ?>
	</div>

	<script type="text/javascript">
		function c($v){console.log($v)}

		/**
		 * get selected or not empty input filter value to build selector
		 * @return {string} [data-type=value][..]..
		 */
		function generateFilter(){
			var ret = '';
			$(".jsfilter").each(function() {
				var type = $(this).attr('data-type'),
					v = $(this).val();
				if(v != ''){
					ret += "[data-"+type+"='"+v+"']";
				}
			});

			return ret;
		}

		/*var obar = $("#bar"),
			bar_top = obar.position().top;
		window.onscroll = function(){
			var sctop = $("html,body").scrollTop(),
				_top = bar_top - sctop;
			if(_top < 0){
				obar.css({'opacity' : .8, 'top': 0});
				return;
			}
			obar.css({'opacity' :1, 'top': _top});
		}*/

		$("#filter_normal").click(function() {
			if($(this).attr('data-flag') == 'hide'){
				$("#list .green").hide();
				$(this).html('show normal');
				$(this).attr('data-flag', 'show');
			}else{
				$("#list .green").show();
				$(this).html('hide normal');
				$(this).attr('data-flag', 'hide');
			}
		});

		$("#filter_url").change(function() {
			var url = $(this).val(),
				listtr = $("#list tr"),
				filter = generateFilter();

			if(filter != ''){
				listtr = listtr.filter(filter);
			}
			if(url == ''){
				listtr.show();
				return;
			}

			listtr.find(".url:contains('"+url+"')").closest('tr').hide();
		});

		$(".jsfilter").change(function() {
			var filter = generateFilter(),
				listtr = $("#list tr");

			if(filter == ''){
				listtr.show();
				return;
			}
			listtr.hide().filter(filter).show().next().show();
		});

		$("#set_pagesize").keyup(function(e) {
			var keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode,
				pagesize = $(this).val();
			if(pagesize == '') return;

			pagesize = parseInt(pagesize.replace(/[^\d]/g, ''));
			$(this).val(pagesize);
			if(pagesize > 0 && keycode == 13){
				var href = location.href,
					ct = href.indexOf('?') != -1 ? '&' : '?';

				href = href.replace(/&?pagesize=\d+/, '');
				location.href = href + ct +'pagesize='+pagesize;
			}
		});
	</script>
</body>
</html>
