$(function(){
	var _height = window.innerHeight;
	var _width = window.innerWidth;
	var w_width =_height*1.7518;
	$(".main ul,.main,.all-bg").width(w_width)
	$(".main,.lishi,.all-bg,.guide,.guide-friend,body").height(_height)
	$(".pop").height(_height)
	$(".pop-form").height(_width*0.9*1.51).css("margin-top",0-(_width*0.9*0.75))
	//$(".pop-form,.fix").show();
	$(".tanchu").css("margin-top",0-(_width*0.9*0.653/2))
	$(".zongshu").css("margin-top",0-(_width*0.9*1.2/2))
	$(".faqicangbao").css("margin-top",0-(_width*0.9*1.22/2))
	$(".shezhicangbao").css("margin-top",0-(_width*0.9*1.2/2))
	$(".shezhicangbao ul").width(_width*0.9)
	$(".jijiehao").css("margin-left",0-(_height*0.4*1.14*0.5))
	$(".guide-text").css("margin-left",0-(_height*0.2*2.34*0.5))
	$(".paopao").css("margin-left",0-(_height*0.15*2.55*0.5))
	$(".guide-bottom").css({"margin-left":0-(_height*0.35*1.1*0.5),"width":_height*0.35*1.1})
	
	$(".lishi1").width($(".lishi1").height()*0.92)
	$(".lishi1").css("margin-left",0-$(".lishi1").height()*0.92*0.5)
	
	$(".lishi2").width($(".lishi2").height()*0.83)
	$(".lishi2").css("margin-left",0-$(".lishi2").height()*0.83*0.5)
	
	$(".lishi3").width($(".lishi3").height()*0.88)
	$(".lishi3").css("margin-left",0-$(".lishi3").height()*0.88*0.5)
	
	$(".lishi4").width($(".lishi4").height()*0.83)
	$(".lishi4").css("margin-left",0-$(".lishi4").height()*0.83*0.5)
	
	if($('.list-box').width()<260){
		$('.lishi4,.lishi3').css({'width':_width*0.9,'margin-left':0-_width*0.45})
		$('.lishi4 img:first,.lishi3 img:first').css('width','100%')
		
		}
	
	if(_height < 600 && _height>550){
		$(".lishi4 p").css("line-height",1.1)
		$(".lishi4 .faqi-title").css("font-size","18px")
		$(".lishi4 .font-27").css("font-size","20px")
		$(".lishi4 .font-18").css("font-size","14px")
		$(".guide-friend .jijiehao").css({"height":(_height*0.35),"margin-left":0-(_height*0.35*1.14*0.5)})
		$(".guide-friend .guide-text").css({"height":(_height*0.13),"margin-left":0-(_height*0.13*2.34*0.5),"top":"34%","left":"55%"})
		$(".guide-friend .userid").css({"top":"38%","left":"51%"})
		$(".guide-friend .kaishixunbao").css({"top":"48%"})
		$(".guide-friend .list-box").css({"width":"105%"})
		$(".guide-friend .guide-bottom").css({"height":(_height*0.42),"margin-left":0-(_height*0.42*1.1*0.45),"bottom":"0","top":"auto"})
		//$(".guide-friend .list th,.guide-friend .list td").css({"height":"13px","line-height":"13px"})		
		}else if(_height<550){
		$(".lishi4 p").css({"line-height":1.1,"top":"2%"})
		$(".lishi4 .faqi-title").css("font-size","16px")
		$(".lishi4 .font-27").css("font-size","18px")
		$(".lishi4 .font-18").css("font-size","12px")
		$(".list th,.list td").css({"height":"13px","line-height":"13px"})
		$(".guide-friend .jijiehao").css({"height":(_height*0.35),"margin-left":0-(_height*0.35*1.14*0.5)})
		$(".guide-friend .guide-text").css({"height":(_height*0.13),"margin-left":0-(_height*0.13*2.34*0.5),"top":"34%","left":"55%"})
		$(".guide-friend .userid").css({"top":"38%","left":"51%"})
		$(".guide-friend .kaishixunbao").css({"top":"48%"})
		$(".guide-friend .list-box").css({"width":"105%"})
		$(".guide-friend .guide-bottom").css({"height":(_height*0.43),"margin-left":0-(_height*0.43*1.1*0.45),"bottom":"0","top":"auto"})
		$(".guide-bottom .list th,.guide-bottom .list td").css({"height":"13px","line-height":"13px"})
		$(".guide-bottom .list td img").css({"width":$(".guide-bottom .list td img").height(),"height":"100%"})
		$(".list div").css({"height":"50px"})
		};
	
	
	
	//引导页跳转
	$(".kaishixunbao").click(function(){
		if(typeof need_guanzhu != 'undefined'){
			location.href = need_guanzhu;
			return;
		}
		window.location.href = "index.php?viewed=1"
	})

	$(".eyes").click(function(){
		$(".yu").click()	
	})
	
	//关闭总数or发起藏保or设置藏保
	$(".zongshu-close").click(function(){
		$(".zongshu,.faqicangbao,.shezhicangbao,.fix").hide();
		// if($(this).closest('.faqicangbao').length != 0)
			// location.href = 'index.php';
	})
	
	//历史页面关闭设置藏保
	$(".shezhicangbao .zongshu-close").click(function(){
		$(".lishi1,.lishi2").show();	
	})
	
	//发起藏保
	$(".faqi,.faqi_2").click(function(){
		$(".faqicangbao,.lishi1,.lishi2").hide();
		$(".shezhicangbao,.fix").show();	
	})
	
	//关闭分享窗
	$(".fenxiang").click(function(){
		$(this).hide();
		$(".fix,.fix-2,.shezhicangbao").hide();
		$(".lishi1,.lishi2").show()
		
	})
	
	//关闭中奖窗
	$(".tanchu").click(function(){
		$(this).hide();
		$(".zongshu").show();
	})
	
	//规则介绍
	$(".shuoming").click(function(){
		$(".lishi1,.lishi2,.lishi3,.lishi4").hide();
		$(".fix,.pop-form").show();	
	})
	
	//关闭规则介绍
	$(".pop-form-btn").click(function () {
		$(".fix,.pop-form").hide();
		$(".lishi1,.lishi2,.lishi3,.lishi4").show()
	})

	//关闭游戏玩法
	$(".pop-close").click(function () {
		$(".pop,.fix").hide();
	})


	//发起藏保选中效果
	var index = 0,
		 _max = 42;//最大数
	//发起藏保选中效果
	$(".shezhicangbao li").click(function(){
	if($(".checked").length<3){
		if($(this).hasClass('checked')){
			$(this).next('.num').children('input').val(0);
		}
		$(this).toggleClass("checked")
		if($(this).hasClass("checked")){
				$(this).next(".num").addClass("check");
				if($(this).next(".num").children("input").val() == 0&& index < 3){
					if(index == 0 || index == 1){
						$(this).next(".num").children("input").val(10).attr("value",10)
					}
					
					};
				index++;
				}else{
				$(this).next(".num").removeClass("check").removeAttr("id").children("input").val(0).attr("value",0);
				index--;
				}
		}else if($(".checked").length==3){
			if($(this).hasClass("checked")){
				$(this).removeClass("checked")
				$(this).next(".num").removeClass("check").removeAttr("id").children("input").val(0).attr("value",0);
				index--;
			}
		}
		var ototal = 0;
		$(this).siblings('.check').each(function(){
			ototal += parseInt($(this).children('input').val());
		});
		var _check3=_max-ototal;
		if(index == 2){
			if($("#check1 input").val() == _max ){$(this).next().children("input").val(0)}
		}
		if(index == 3){
			$(this).next().children("input").val(_check3)
			//alert($(this).next().children("input").val())
		}
	})
	//发起藏保
	if(typeof need_show_share != 'undefined'){
		$(".fenxiang,.fix-2").show();
	}
	$(".shezhi").click(function(){
		var obj = $(".shezhicangbao li.checked");
		if(obj.length < 3){
			alert('还需选择'+(3-obj.length)+'个藏宝点哦~');
			return;
		}
		var place = {}, temp_total = 0, tempv;
		obj.map(function() {
			tempv = parseInt($(this).next().children('input').val())
			place[$(this).attr('data-val')] = tempv;
			temp_total += tempv;
		});
		if(temp_total != _max){
			alert('总和应为'+_max+'万');
			return;
		}
		$.post('ajax.php?a=setplace', {place:place}, function(d){
			if(!d) return;
			var d = eval("("+d+")");
			if(d.code != 1){
				alert('系统错误~');
				return;
			}
			var hf = location.href;
			hf = hf.replace(/(.*?\.php).*/,'$1');
			hf = hf +'?hash='+d.hash;
			location.href = hf;
		});
	});
	//保额计算

	$(".numberbox").keyup(function(){
		var _num = _max - $(this).parent().siblings(".check").children("input:eq(0)").val() - $(this).parent().siblings(".check").children("input:eq(1)").val()
		var _num1 = _max - $(this).parent().siblings(".check").children("input:eq(0)").val()
		var _num2 = _max - $(this).parent().siblings(".check").children("input:eq(1)").val()
		if(!_num1){
			if($(this).val() > _max){
			$(this).val(_max)
			}
		}else if(!_num2&&_num1){
			if($(this).val() > _num1){
			$(this).val(_num1)
			}
		}else{
			if($(this).val() > _num){
			$(this).val(_num)
			}
		}
	});
	$(".jia").click(function(){
		var _num = 0;
		for(var i=0;i < $(".checked").length;i++){
			_num+=parseInt(($("div.check input").eq(i).val()))
		}
		if($(".numberbox").val() == ""){
			$(".numberbox").val(0)
		}
		
		if(_num < _max){
		$(this).prev(".numberbox").val(parseInt($(this).prev(".numberbox").val())+1).attr("value",parseInt($(this).prev(".numberbox").val()))
		};
		$(this).siblings('.numberbox').keyup();
	});
	$(".jian").click(function(){
		if($(this).next(".numberbox").val() > 1){
			$(this).next(".numberbox").val(parseInt($(this).next(".numberbox").val())-1).attr("value",parseInt($(this).next("#numberbox").val()))
		}
		$(this).siblings('.numberbox').keyup();
	});

	// 分享
	var title_arr = ['朋友这么久了，70万而已，找到就送你了，快来海底寻宝吧~','我在海底藏了70万，进来找找吧，看看咱们有多默契：）','朋友圈发了这么久，你总不点赞，这次扔70万，不信没人气！','豪车炫富什么的弱爆了，70万找到就送你了，人人有份。','神秘海底宝藏探寻，搜查达人们快来试试吧！','都是70万，我是一视同仁的，至于你找到多少，真看人品了！'],
	desc_arr = [' '],
	imgs_arr = ['share1.jpg','share2.jpg','share3.jpg','share4.jpg','share5.jpg'];
	var _rand = function(min,max){
		return Math.round(Math.random()*(max-min))+min;
	};
	//微信分享数据
	var wxData = {   
		title:title_arr[_rand(0, title_arr.length-1)],
		desc: desc_arr[_rand(0, desc_arr.length-1)],
		link: window.location.href,
		imgUrl:'http://'+ window.location.host + '/campaign/mc/cmbcc/201510/images/'+imgs_arr[_rand(0, imgs_arr.length-1)]
	};
	//调用分享接口
	$.ajax({ 
		type: "post",
		url: "/include_form/weixin.config.php", 
		cache:false,
		async:false,
		dataType: "html",
		success: function(obj){
			eval(obj);
		}
	});
	
})

function IsNum(e) {
	var k = window.event ? e.keyCode : e.which;
	if (((k >= 48) && (k <= 57)) || k == 8 || k == 0) {
	} else {
		if (window.event) {
			window.event.returnValue = false;
		}
		else {
			e.preventDefault(); //for firefox 
		}
	}
}
