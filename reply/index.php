<?php 
// 后台回复
require '../inc/conn.php';
require '../inc/func.php';
require '../inc/Page.class.php';
require 'check.php';


// 数据
$pageno = empty($_GET['page']) ? 1 : intval($_GET['page']);
$pagesize = empty($_GET['pagesize']) ? 10 : intval($_GET['pagesize']);
$pagestart = ($pageno-1) * $pagesize;
$limit = "limit {$pagestart},{$pagesize}";

$order = isset($_GET['order']) ? $_GET['order'] : 'desc';
if(!in_array($order, array('desc','asc'))) exit('Access denied(code:3)');

$where = array();

$where[] = '1=1';

$where = implode(' AND ', $where);
$where .= " order by addtime {$order} {$limit}";

$questions = $db->getAssoc('question', $where);
// echo $db->lastsql;

$total = $db->getRowNum('question');

// 分页 id: session id
$url = "?id={$id}&page=";
$page = new Page($total, $pagesize, $url);

// 问题行
$list = '<tr {trbg}>
			<td class="q_tit" width="20%">{tit}</td>
			<td class="q_cont">{cont}</td>
			<td class="q_tag" width="15%">{tag}</td>
			<td width="12%" class="{cls}">{name}</td>
			<td width="10%" title="点击修改"><span class="status {cls_status}">{status}</span></td>
			<td width="10%">{addtime}</td>
			<td data-id="{id}" class="operate" width="20%">
				<a href="javascript:void(0)" class="reply">添加回复</a> | 
				<a href="javascript:void(0)" class="view"><span class="viewhide">查看回复</span>(<span class="replynum">{replynum}</span>)</a> | 
				<a href="javascript:void(0)" class="modify">修改提问</a> | 
				<a href="javascript:void(0)" class="del">删除</a>
				<input type="hidden" class="alias" value="{alias}" />
				<input type="hidden" class="viewnum" value="{view}">
				<input type="hidden" class="keywords" value="{keywords}">
				<input type="hidden" class="description" value="{description}">
			</td>
		</tr>';

// tag 
$tag = getTag();

$status_all = getStatus();


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>提问管理</title>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/index.css">
	<script type="text/javascript">
	var id = '<?php echo $id ?>';// get过来的session id 
	var tag = <?php echo json_encode($tag) ?>;
	var status_all = <?php echo json_encode($status_all) ?>;

	</script>
</head>
<body>
	<div id="header">
		<h1>提问管理<span><a href="javascript:void(0)">提问列表</a></span></h1>
	</div>
	<div id="main" class="clearfix">
		<div id="search_bar">
			<input type="button" title="添加提问" class="btn_add btn" value="添加提问" id="add_question">
		</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th width="20%"><p>问题</p></th>
				<th><p>描述</p></th>
				<th width="15%"><p>tag</p></th>
				<th width="12%"><p>用户名/手机号</p></th>
				<th width="10%"><p>状态</p></th>
				<th width="10%"><p>添加时间</p></th>
				<th width="20%"><p>操作</p></th>
			</tr>
		</table>
		<div id="list">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<?php 
				if(!empty($questions)):
					foreach ($questions as $i => $q):
						$cls = 'green';
						if($q['uid'] === '0'){
							$name = '系统添加';
							$cls = 'grey';
						}elseif(!empty($q['uname'])){
							$name = $q['uname'];
						}else{
							$name = $q['mobile'];
						}

						if(empty($q['alias'])){
							$q['alias'] = getAskDetailUrl($q['id'], false);
						}

						$status = $status_all[$q['status']];

						$trbg = $i % 2 == 0 ? '' : 'class="even"';

						echo strtr($list, array(
							'{tit}' => $q['tit'],
							'{trbg}' => $trbg,
							'{keywords}' => $q['keywords'],
							'{description}' => $q['description'],
							'{cls}' => $cls,
							'{status}' => $status['desc'],
							'{cls_status}' => $status['cls'],
							'{cont}' => $q['cont'],
							'{replynum}' => $q['replynum'],
							'{tag}' => $q['tag'],
							'{name}' => $name,
							'{addtime}' => date('Y-m-d H:i', $q['addtime']),
							'{id}' => $q['id'],
							'{alias}' => $q['alias'],
							'{view}' => $q['view']
						));
					endforeach;
				else: ?>
				<tr class="nodata">
					<td colspan="7">暂无数据~</td>
				</tr>
				<?php endif; ?>
			</table>
		</div>
		<div id="pagelist">
			<?php echo $page->pagelist($pageno); ?>
		</div>
	</div>
	
	<script type="text/javascript">
		// 弹出层等模板
	var	template = {
			// 添加回复弹出层
			reply : '<div style="padding:20px;">\
	        			<form>\
	        				<textarea placeholder="回复内容.." name="cont" style="width: 548px; height: 156px;"></textarea>\
	        				<input type="hidden" name="qid" value="{id}" />\
	        				<input type="hidden" name="a" value="reply" />\
	        				<input class="btn_reply_sub btn" type="button" value="提交" />\
        				</form>\
    				</div>',
			// 添加问题弹出层
			add : '<div style="padding:20px;">\
						<form class="question_form">\
	        				<table width="100%" border="0" cellspacing="0" cellpadding="0">\
								<tr>\
									<td width="10%" align="right">标题:</td>\
									<td class="t2em"><input type="text" class="ipt" placeholder="问题标题" name="tit" /></td>\
								</tr>\
								<tr>\
									<td align="right">keywords:</td>\
									<td class="t2em"><input type="text" placeholder="meta keywords" name="keywords" class="ipt" /></td>\
								</tr>\
								<tr style="height:auto;">\
									<td align="right">description:</td>\
									<td class="t2em"><textarea placeholder="meta description" name="description" style="width: 95%; height: 60px;"></textarea></td>\
								</tr>\
								<tr>\
									<td align="right">tag:</td>\
									<td class="t2em">{tag}</td>\
								</tr>\
								<tr>\
									<td align="right">url:</td>\
									<td class="t2em"><input type="text" placeholder="访问链接,会自动生成ask/id.html" name="alias" class="ipt" /></td>\
								</tr>\
								<tr>\
									<td align="right">浏览量:</td>\
									<td class="t2em"><input type="text" placeholder="浏览量,用于热门问题排序依据,会自动递增" name="view" class="ipt" /></td>\
								</tr>\
								<tr>\
									<td align="right">描述:</td>\
									<td class="t2em"><textarea placeholder="问题描述,如果为空列表页会显示回复." name="cont" style="width: 95%; height: 156px;"></textarea></td>\
								</tr>\
								<tr>\
									<td class="t2em" colspan="2"><input class="btn_add_sub btn" type="button" value="提交" /></td>\
								</tr>\
	        				</table>\
							<input type="hidden" name="a" value="add" />\
        				</form>\
					</div>',
			// 修改回复弹出层
			modify_reply : '<div style="padding:20px;">\
	        			<form>\
	        				<textarea name="cont" style="width: 548px; height: 156px;">{val}</textarea>\
	        				<input type="hidden" name="id" value="{id}" />\
	        				<input type="hidden" name="a" value="modify_reply" />\
	        				<input class="btn_rp_modify_sub btn" type="button" value="提交" />\
        				</form>\
    				</div>',
    		// 修改问题弹出层
			modify_question : '<div style="padding:20px;">\
	        			<form class="question_form">\
	        				<table width="100%" border="0" cellspacing="0" cellpadding="0">\
								<tr>\
									<td width="10%" align="right">标题:</td>\
									<td class="t2em"><input type="text" class="ipt" placeholder="问题标题" value="{tit}" name="tit" /></td>\
								</tr>\
								<tr>\
									<td align="right">keywords:</td>\
									<td class="t2em"><input type="text" placeholder="meta keywords" name="keywords" value="{keywords}" class="ipt" /></td>\
								</tr>\
								<tr style="height:auto;">\
									<td align="right">description:</td>\
									<td class="t2em"><textarea placeholder="meta description.." name="description" style="width: 95%; height: 60px;">{description}</textarea></td>\
								</tr>\
								<tr>\
									<td align="right">tag:</td>\
									<td class="t2em">{tag}</td>\
								</tr>\
								<tr>\
									<td align="right">url:</td>\
									<td class="t2em"><input type="text" placeholder="访问链接" name="alias" class="ipt" value="{alias}" /></td>\
								</tr>\
								<tr>\
									<td align="right">浏览量:</td>\
									<td class="t2em"><input type="text" placeholder="浏览量,用于热门问题排序依据,会自动递增" name="view" class="ipt" value="{view}" /></td>\
								</tr>\
								<tr>\
									<td align="right">描述:</td>\
									<td class="t2em"><textarea placeholder="问题描述,如果为空列表页会显示回复." name="cont" style="width: 95%; height: 156px;">{cont}</textarea></td>\
								</tr>\
								<tr>\
									<td class="t2em" colspan="2"><input class="btn_modify_sub btn" type="button" value="提交" /></td>\
								</tr>\
	        				</table>\
							<input type="hidden" name="id" value="{id}" />\
	        				<input type="hidden" name="a" value="modify_question" />\
        				</form>\
    				</div>',		
			// 添加问题增加新行模板
			list : '<?php echo strtr($list, array("\r\n"=>'', "\r"=>'',"\n"=>'')) ?>',
			// 查看回复新行模板
			viewreply:'\
			<tr class="replybox">\
					<td colspan="7">\
						<table width="90%" border="0" cellspacing="0" cellpadding="0">\
							<tr>\
								<td width="6%">用户</td>\
								<td>内容</td>\
								<td width="12%">回复时间</td>\
								<!-- <td>修改时间</td> -->\
								<td width="6%">操作</td>\
							</tr>\
						</table>\
					</td>\
				</tr>',
			// 查看回复 循环的行模板
			reply_info:'<tr>\
							<td>{uname}</td>\
							<td class="cont">{cont}</td>\
							<td>{addtime}</td>\
							<!-- <td class="uptime">{uptime}</td> -->\
							<td data-id={id}>\
								<a href="javascript:void(0)" class="rp_modify">修改</a>\
								<!-- <a href="javascript:void(0)" title="将显示在" class="rp_rec">设为推荐 </a> -->\
								<a href="javascript:void(0)" class="rp_del">删除</a>\
							</td>\
						</tr>',
			nodata:'<tr class="nodata"><td colspan="7">暂无数据~</td></tr>'

	}
	</script>
	<script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="/js/layer2.0/layer.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
</body>
</html>
