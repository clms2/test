$(function(){
	var _rand = function(min,max){
		return Math.round(Math.random()*(max-min))+min;
	}, c = function(v){if(typeof console != 'undefined') console.log(v)};
	var noreward_len = $(".noreward").length-1,reward_len = $(".reward").length-1;
	var	show = function(type){
		// 显示中奖/未中奖div
		var o = $("."+type).eq(_rand(0, eval(type+'_len')));
		o.show();
		return o;
	}, t, hide = function(o, search_num){
		// 隐藏中奖/未中奖div, 并绑定2秒后消失或点击消失
		t = setTimeout(function(){
			o.hide();
			if(typeof search_num != 'undefined' && search_num == 3){
				showtotal();
				return;
			}
			$(".fix").hide();
		}, 2000);
		o.unbind('click').click(function() {
			clearTimeout(t);
			$(this).hide();
			if(search_num == 3){
				showtotal();
				return;
			}
			$(".fix").hide();
		});
	}, filltext = function(obj, total){
		// 填充金额
		var total = total || total_baoxian;
		obj.find(".baoxiane").text(total);
	},showtotal = function(total){
		// 显示总保险额
		var total = total || total_baoxian;
		clearTimeout(t);
		$("#total,.fix").show();
		filltext($("#total"), total);
	}, finshed = false;

	// 抽奖
	var main_clickable = true,
		total_baoxian = 28;
	$("#main li").click(function(){
		// 达到3次就不请求了
		if(finshed){
			showtotal();
			return;
		}
		// 如果已经点过了也不请求
		if($(this).attr('data-clicked')) return;
		$(this).attr('data-clicked', 1);
		// 防重复点
		if(!main_clickable) return;
		main_clickable = false;
		$.post('ajax.php?a=find', {yu:$(this).attr('class')}, function(d){
			main_clickable = true;
			if(!d) return;
			var d = eval("("+d+")"), o;
			if(typeof d.search_num != 'undefined' && d.search_num == 3) finshed = true;
			$(".fix").show();
			switch(d.code){
				// 未中奖
				case 0:
					o = show('noreward');
					hide(o, d.search_num);
					return;
				break;
				// 中奖
				case 1:
					var total = d['baoxian'];
					o = show('reward');
					filltext(o, total);
					total_baoxian += parseInt(total);
					hide(o, d.search_num);
				break;
				// 达到3次
				case 2:
					showtotal(d.baoxian);
					return;
				break;
				case 110:
					alert('活动已结束');
			}
		})
	});
	//再来一次
	var again_clickable = true;
	$("#again").click(function(){
		if(!again_clickable) return;
		again_clickable = false;
		$.post("ajax.php?a=again", function(){
			again_clickable = true;
			$("#main li").removeAttr('data-clicked');
			finshed = false;
			total_baoxian = 28;
			$(".fix,#total").hide();
		});
	});
	//满足
	var mz_clickable = true;
	$(".manzu").click(function(){
		if(!mz_clickable) return;
		mz_clickable = false;
		$.post('ajax.php?a=satisfy', function(d){
			mz_clickable = true;
			if(!d) return;
			var d = eval("("+d+")");
			// 未绑定
			if(d.code == 1 && typeof d.href != 'undefined'){
				location.href = d.href;
				return;
			}
			if(d.code == -2){
				alert('本不该出现此提示~');
				return;
			}
			if(d.code == 0){
				alert('系统错误~');
				return;
			}
			$(".zongshu").hide();
			$(".faqicangbao").show();
			filltext($(".faqicangbao"));
		})
	});
	
})
