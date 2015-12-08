$(function(){
	$.ajaxSetup({
		url: 'ajax.php?id='+id,
		type: 'post',
		dataType: 'json',
		beforeSend: function(){
			layer.load(1, {
			    shade: [0.1,'#fff']
			});
		},
		complete: function(){
			layer.closeAll('loading');
		}
	});
	function _post(data, func){
		$.ajax({
			data:data,
			success:func
		});
	}
	/**
	 * 返回2015-11:03 11:20
	 * @param  {object} obj new Date()的对象
	 * @return {string}     
	 */
	function getDate(obj){
		var day = obj.getDate().toString();
		if(day.length == 1) day = '0' + day;

		return obj.getFullYear() + '-' + obj.getMonth() + '-' + day + ' ' + obj.getHours() + ':' + obj.getMinutes();
	}
	/**
	 * php strtr 2个参数版
	 * @param  {string} str   
	 * @param  {object} assoc 
	 * @return {string}       
	 */
	function strtr(str, assoc){
		for(var k in assoc){
			str = str.replace(new RegExp(k, 'g'), assoc[k]);
		}

		return str;
	}
	/**
	 * 替换查看回复模板数据为相应的值
	 * @param  {array} arr 必须键：{cont:'',id:''},可选：{addtime:'',uptime:''} unix时间戳
	 * @return {string}    
	 */
	function replaceReply(arr){
		var temp = '',
			dateobj = new Date();
		for(var k in arr){
			var assoc = {};
			for(var k1 in arr[k]){
				if(k1 == 'uname' && arr[k][k1] == 'system'){
					arr[k][k1] = '系统添加';
				}
				if(k1 == 'addtime' || k1 =='uptime'){
					if(arr[k][k1] == '0'){
						arr[k][k1] = '';
					}else{
						dateobj.setTime(arr[k][k1]+'000');
						arr[k][k1] = getDate(dateobj);
					}
				}

				assoc['{'+k1+'}'] = arr[k][k1];
			}
			if(typeof assoc['{uptime}'] == 'undefined')
				assoc['{uptime}'] = '';

			temp += strtr(template.reply_info, assoc);
		}

		return temp;
	}

	/**
	 * 载入某问题的回复并替换dom结构
	 * @param  {int} qid      
	 * @param  {bool} hidetips 是否显示提示
	 * @return {}          
	 */
	function loadreply(qid, hidetips){
		if(!qid) return ;
		_post({a:'loadreply',qid:qid}, function(d){
			if(d.code != '1'){
				if(!hidetips)
					layer.alert(d.msg || '加载回复失败');
				return;
			}
			if(d.total == 0){
				if(!hidetips)
					layer.msg('还没有回复哦~', {time:1500,icon:5});
				return;
			}
			cache.replybtn.html('隐藏回复');
			// 确保再次点查看不会有重复
			if(cache.replytr.next('.replybox').length == 1){
				cache.replytr.next('.replybox').remove();
			}
			cache.replytr.after(template.viewreply);

			$(replaceReply(d.data)).insertAfter(cache.replytr.next('.replybox').find('tr:first'))
		});
	}

	var index = {},// layer.open返回的index数组
		cache = {},// 保存一些点击按钮所在行什么的，用于关闭等
		reg_ask = /ask\/(.*\-)?\d+\.html/;// 添加和修改提问时确保与rewrite规则一致

	// 显示添加回复层
	$("#list").delegate('.reply', 'click', function() {
		var _id = $(this).parent().attr('data-id'),
			_this = $(this);
		if(!_id){
			alert('系统错误 #list .reply');
			return;
		}
		// 记录这行 用于保存回复的时候找到它 并在它后面添加dom元素
		cache.replytr = $(this).closest('tr');
		// 记录查看回复按钮 用于添加成功时改变文字描述
		cache.replybtn = $(this).siblings('.view').children('.viewhide');
		// 记录该索引 用于关闭层
		index.reply = layer.open({
	        type: 1,
	        area: ['600px', '360px'],
	        shadeClose: true, //点击遮罩关闭
	        content: template.reply.replace('{id}',_id),
	        success: function(obj){
	        	obj.find('textarea').focus();
	        	// 如果有回复，且是第一次点击的，那么同时加载回复内容
	        	if(parseInt(_this.siblings('.view').children('.replynum').text()) != 0 && cache.replytr.next('.replybox').length == 0)
	        		loadreply(_id, 1);
	        }
	    });
	});
	// 显示添加问题层
	$("#add_question").click(function() {
		var temptag = '';
		for(var k in tag){
			temptag += '<input type="checkbox" name="tag[]" value="'+k+'" />'+tag[k];
		}
		index.add = layer.open({
	        type: 1,
	        area: ['600px', '600px'],
	        shadeClose: true,
	        content: template.add.replace('{tag}', temptag),
	        success: function(obj){
	        	obj.find('input:first').focus();
	        }
	    });
	});
	// 点击查看回复载入该问题的回复
	$("#list").delegate('.view', 'click', function() {
		var _id = $(this).parent().attr('data-id');
		if(!_id){
			layer.alert('系统错误 #list .view');
			return;
		}
		if(parseInt($(this).find('.replynum').text()) == 0){
			layer.msg('还没有回复哦~', {time:1500,icon:5});
			return;
		}

		cache.replybtn = $(this).children('.viewhide');// 用于改变文字
		cache.replytr = $(this).closest('tr');
		var nexttr = cache.replytr.next('.replybox');
		// 点击显示隐藏回复内容 todo ..show太生硬 加些效果什么的
		// 隐藏回复
		if(nexttr.length == 1){
			if(nexttr.is(':visible')){
				nexttr.hide();
				cache.replybtn.html('查看回复');
			}else{
				nexttr.show();
				cache.replybtn.html('隐藏回复');
			}
		}else{
			loadreply(_id);
		}
	});

	// 添加回复--submit
	$("body").delegate('.btn_reply_sub', 'click', function() {
		var form = $(this).closest('form');
		if(form.find("textarea[name=cont]").val() == ''){
			layer.alert('请输入内容');
			return;
		}
		_post(form.serialize(), function(d){
			if(d.code != '1'){
				layer.alert(d.msg || '操作失败');
				return;
			}
			var arr = {};
			arr[0] = {};
			arr[0]['addtime'] = d.addtime;
			arr[0]['cont'] = form.find("textarea").val();
			arr[0]['id'] = d.id;
			arr[0]['uname'] = 'system';

			cache.replybtn.html(cache.replytr.next('.replybox').is(':visible') ? '隐藏回复' : '查看回复');
			cache.replytr.find('.status').removeClass().addClass('status green').html('已显示');

			// 确保添加回复所在行的下一行有回复层
			if(cache.replytr.next('.replybox').length == 0){
				cache.replytr.after(template.viewreply);
			}
			// 替换内容
			$(replaceReply(arr)).insertAfter(cache.replytr.next('.replybox').find('tr:first'));

			// 改变数量
			var onum = cache.replybtn.siblings('.replynum');
			onum.html(parseInt(onum.text())+1);

			layer.close(index.reply);
		});
	});
	// 添加问题--submit
	$("body").delegate('.btn_add_sub', 'click', function() {
		var form = $(this).closest('form'),
			selected_tag = form.find(':checkbox:checked').map(function(){return tag[$(this).val()]}).get().join(','),
			alias = form.find("input[name=alias]").val();
		if(form.find("input[name=tit]").val() == ''){
			layer.alert('标题不能为空');
			return;
		}
		if(alias != '' && !reg_ask.test(alias)){
			layer.alert('url需类似ask/5.html或ask/baoxian-5.html哦~');
			return;
		}
		_post(form.serialize(), function(d){
			if(d.code != '1'){
				layer.alert(d.msg || '操作失败');
				return;
			}
			
			var first_tr = $("#list tr:first"),
				cls = first_tr.hasClass('even') ? '' : ' class="even"',
				temp = strtr(template.list, {
					'{tit}' : form.find("input[name=tit]").val(),
					'{cont}' : form.find("textarea[name=cont]").val(),
					'{addtime}' : d.addtime,
					'{tag}' : selected_tag,
					'{cls_status}' : 'green',
					'{cls}' : 'grey',
					'{status}' : '已显示',
					'{name}' : '系统添加',
					'{trbg}' : cls,
					'{id}' : d.id,
					'{replynum}' : 0,
					'{view}' : form.find("input[name=view]").val(),
					'{alias}' : d.alias
				});

			$(temp).insertBefore(first_tr);

			// 去除没数据那行
			if($("#list .nodata").length == 1){
				$("#list .nodata").remove();
			}

			$("#list tr").toggleClass('even');
			// todo.. 数量超过pagesize 删除最后一行

			layer.close(index.add);
		});
	});
	
	// 点击状态添加下拉列表供用户选择
	$("#list").delegate('.status', 'click', function() {
		var opts = '<select class="status_sel">',
			old = $.trim($(this).text()),
			selected = '';

		for(var k in status_all){
			selected = status_all[k]['desc'] == old ? 'selected' : '';
			// 用于判断是否和之前的值一样
			if(selected != ''){
				cache.old_status = k;
			}
			opts += '<option '+selected+' value="'+k+'">'+status_all[k]['desc']+'</option>';
		}
		opts += '</select>';

		$(this).parent().html(opts);
	});
	// 提交状态  绑定<select>的change可能会好些 不修改就不用请求 但是这样还要绑定一个body的click事件 可能用户误点了 但是又不想看到下拉框出来 于是点击其他地方想隐藏它 还是绑定option的click好些..但是chrome不支持click...还是change吧
	$("#list").delegate('.status_sel', 'change', function() {
		var id = $(this).closest('td').siblings('.operate').attr('data-id'),
			new_status = $(this).val(),
			_this = $(this);
		if(!id){
			layer.alert('系统错误 #list .status_sel option');
			return;
		}
		if(new_status == cache.old_status){
			_this.closest('td').html('<span class="status '+status_all[new_status]['cls']+'">'+status_all[new_status]['desc']+'</span>');
			return;
		}
		
		_post({a:'set_status',id:id,status:new_status}, function(d){
			if(d.code != '1'){
				layer.alert(d.msg || '系统错误 #list .status_sel option');
				return;
			}
			cache.old_status = new_status;
			_this.closest('td').html('<span class="status '+status_all[new_status]['cls']+'">'+status_all[new_status]['desc']+'</span>');
		});
	});
	$("body").click(function(e) {
		var el = e.target;
		if(el.tagName.toLowerCase() != 'span' && $(el).closest('.status_sel').length == 0 && $(".status_sel").length != 0){
			var new_status = $('.status_sel').val();
			$('.status_sel').closest('td').html('<span class="status '+status_all[new_status]['cls']+'">'+status_all[new_status]['desc']+'</span>');
		}
	});


	// 删除问题
	$("#list").delegate('.del', 'click', function() {
		var _id = $(this).parent().attr('data-id'),
			_tr = $(this).closest('tr');
		layer.confirm('确定要删除吗？将同时删除回复', function(index){
			if(!_id){
				alert('系统错误 #list .del');
				return;
			}
			_post({a:'del',qid:_id}, function(d){
				if(d.code != '1'){
					layer.alert(d.msg || '操作失败');
					return;
				}
				layer.close(index);
				// 先删除后面那个 再删除当前的
				_tr.next('.replybox').remove().end().remove();

				// 该页问题都删了 todo.. 判断总数才显示没有数据
				if($("#list tr").length == 0){
					$("#list table").html(template.nodata);
					// todo.. #pagelist~
					return;
				}

				$("#list tr").toggleClass('even');
			});
		})
	});

	// 删除回复
	$("#list").delegate('.rp_del', 'click', function() {
		var _id = $(this).parent().attr('data-id'),
			_tr = $(this).closest('tr');
		layer.confirm('确定要删除吗？', function(index){
			if(!_id){
				alert('系统错误 #list .rp_del');
				return;
			}
			_post({a:'del_reply',id:_id}, function(d){
				if(d.code != '1'){
					layer.alert(d.msg || '操作失败');
					return;
				}
				layer.close(index);
				// 如果是最后一条回复就整个删除了~
				if(_tr.siblings('tr').length == 1){
					_tr.closest('.replybox').remove();
					return;
				}
				_tr.remove();

			});
		})
	});

	// 修改回复层
	$("#list").delegate('.rp_modify', 'click', function() {
		var id = $(this).parent().attr('data-id'),
			val = $(this).parent().siblings('.cont').html();
		if(!id){
			alert('系统错误 #list .rp_modify');
			return;
		}
		// 记录点的是哪个回复行 用于保存成功后修改页面值
		cache.replytr = $(this).closest('tr');
		// 记录原始内容 用于判断有没修改
		cache.reply_old_cont = val;

		index.modify_reply = layer.open({
	        type: 1,
	        area: ['600px', '360px'],
	        shadeClose: true, //点击遮罩关闭
	        content: template.modify_reply.replace('{id}',id).replace('{val}',val),
	        success: function(obj){
	        	obj.find('textarea').focus();
	        }
	    });
	});
	// 修改回复 --submit
	$("body").delegate('.btn_rp_modify_sub', 'click', function() {
		var form = $(this).closest('form'),
			textarea = form.find("textarea"),
			val = textarea.val();
		if($.trim(val) == ''){
			layer.alert('请输入内容');
			return;
		}
		if(val == cache.reply_old_cont){
			layer.alert('和之前的一样哦~', function(i){
				textarea.focus();
				layer.close(i);
			});
			return;
		}
		_post(form.serialize(), function(d){
			if(d.code != '1'){
				layer.alert(d.msg || '操作失败');
				return;
			}
			cache.replytr.find('.cont').html(val).end().find('.uptime').html(d.uptime);

			layer.close(index.modify_reply);
		});
	});

	// 修改提问层
	$("#list").delegate('.modify', 'click', function() {
		var tr = $(this).closest('tr'),
			id = $(this).parent().attr('data-id'),
			tit = tr.children('.q_tit').html(),
			val = tr.children('.q_cont').html(),
			keywd = tr.find('.keywords').val(),
			description = tr.find('.description').val();
		if(!id){
			alert('系统错误 #list .modify');
			return;
		}

		// 记录点的是哪个回复行 用于保存成功后修改页面值
		cache.questiontr = tr;
		// 记录原始内容 用于判断有没修改
		// cache.question_old_tit = tit;
		// cache.question_old_cont = val;

		var div = template.modify_question,
			cur_tag = $.trim(tr.children('.q_tag').html()),
			temptag = '',
			checked = '',
			alias = '',
			view = '';
		// 记录原始tag 用于判断有没修改
		// cache.question_old_tag = cur_tag;

		cur_tag = cur_tag.split(',');
		// 替换tag选中
		for(var k in tag){
			checked = $.inArray(tag[k], cur_tag) != -1 ? 'checked' : '';
			temptag += '<input type="checkbox" '+checked+' name="tag[]" value="'+k+'" />'+tag[k];
		}
		// 替换url alias
		alias = $(this).siblings('.alias').val();
		// cache.question_old_alias = alias;
		// 替换浏览量
		view = $(this).siblings('.viewnum').val();
		// cache.question_old_view = view;

		index.modify_question = layer.open({
	        type: 1,
	        area: ['600px', '600px'],
	        shadeClose: true, //点击遮罩关闭
	        content: strtr(div, {
				'{id}':id,
				'{cont}':val,
				'{tit}':tit,
				'{tag}':temptag,
				'{alias}':alias,
				'{view}' : view,
				'{keywords}' : keywd,
				'{description}' : description
	        }),
	        success: function(obj){
	        	obj.find('textarea').focus();
	        }
	    });
	});
	// 修改提问 --submit
	$("body").delegate('.btn_modify_sub', 'click', function() {
		var form = $(this).closest('form'),
			tit = form.find('input[name=tit]').val(),
			view = form.find('input[name=view]').val(),
			val = form.find("textarea").val(),
			keywords = form.find('input[name=keywords]').val(),
			description = form.find('textarea[name=description]').val(),
			alias = form.find('input[name=alias]').val(),
			new_tag = form.find(':checkbox:checked').map(function(){return tag[$(this).val()]}).get().join(',');
		if($.trim(tit) == ''){
			layer.alert('请输入标题');
			return;
		}
		// 比较tag title desc alias是否一样 会越来越多..todo 简化这地方的代码
		// if(val == cache.question_old_cont && tit == cache.question_old_tit && new_tag == cache.question_old_tag && alias == cache.question_old_alias && view == cache.question_old_view){
		// 	layer.alert('和之前的一样哦~');
		// 	return;
		// }
		// 确保url符合rewrite规则
		if(!reg_ask.test(alias)){
			layer.alert('url需类似ask/5.html或ask/baoxian-5.html哦~');
			return;
		}
		if(view != '' && !/^\d+$/.test(view)){
			layer.alert('请输入正确的浏览量~仅允许数字');
			return;
		}

		_post(form.serialize(), function(d){
			if(d.code != '1'){
				layer.alert(d.msg || '操作失败');
				return;
			}
			// 修改提问所在行 并标为已显示 todo.. 优化下
			cache.questiontr.find('.description').val(description).end().find('.keywords').val(keywords).end().find('.viewnum').val(view).end().find('.q_cont').html(val).end().find('.q_tag').html(new_tag).end().find('.q_tit').html(tit).end().find('.status').removeClass().addClass('status green').html('已显示');

			layer.close(index.modify_question);
		});
	});

})
