$(document).ready(function(){ 
	_resize();
	$(window).resize(function(){
		_resize();
	})

	var running = false;
	var gd=0,gd2=0,uishowid='';
	// 抽奖
    $(".startbtn").click(function(){ 
    	if(running) return false;
        running = true;
		$.ajax({ 
			type: 'POST', 
			url: 'lottery.php', 
			dataType: 'json', 
			async:false,
			cache: false, 
			error: function(){ 
				alert('出错了！'); 
				running = false;
				return false; 
			}, 
			success:function(json){ 
				var result = json.result;
				if(result==1){
					$(".startbtn").css("cursor","default"); 
					var a = json.angle; //角度 
					var p = json.prize; //奖项 
					var jx = json.jx; //奖项等级
					$(".zbg").rotate({ 
						duration:3000, //转动时间 
						angle: 0, 
						animateTo:1800+a, //转动角度 
						easing: $.easing.easeOutSine, 
						callback: function(){ 
							//alert('恭喜你，中得'+p);
							$(".startbtn").css("cursor","pointer");
							//如果是实物奖弹出填写框
							if(jx<5){
								//中奖了
							   $('.ms_hj_txt span').html(json.prize);
							   UiShow('zj_smg');
							}else if(jx==5){
							    //未中奖
								$('.ms_nzj_zc span').html(json.cs);
								 UiShow('nzj_msg');
							}
							running = false;
						} 
					}); 
				}else if(result==2){
					//未登录
					UiShow('no_denglu');
					running = false;
					return false;
				}else if(result==3){
					//抽奖次数已用完,分享弹窗
					$("#fx_msg").show();
					$('#Black_bg').show();
					running = false;
					return false;
				}else if(result == 4){
					alert(json.hasstart == 0 ? '活动未开始' : '活动已结束');
					running = false;
					return false;
				}else{
					alert('网络错误！');
					running = false;
					return false;
				}
			} 
		});  
    });
	// http://test.cignacmb.com/member/sendsms.xhtml?jsoncallback=?
	// https://member.cignacmb.com/sendsms.xhtml?jsoncallback=?
	var _t,
		code_clickable = true,
		max = 60,
		countDown = function(){
			_t = setTimeout(function(){
				code_clickable = false;
				if(max-- <= 0){
					clearCountDown();
					return;
				}
				$("#getCode").html(max+'s');
				countDown();
			}, 1000);
		},
		clearCountDown = function(){
			clearTimeout(_t);
			code_clickable = true;
			$("#getCode").css({'background-color':'#df3121','cursor':'pointer'}).html('获取验证码');;
			max = 60;
		};
	// 获取验证码
	$("#getCode").click(function() {
		if(!code_clickable) return false;
		if(!checkMobile()) return false;
		var verifycode = $("#imgCodeVal").val();
		if(verifycode == '' || !/^\d{4}$/.test(verifycode)){
			alert('请输入正确的验证码');
			return false;
		}

		$("#getCode").css({'background-color':'#555', 'cursor':'default'});
		countDown();

		$.ajax({
			url:cfg.smsurl,
			dataType:'jsonp',
			data:{'imgValidateCode':verifycode, 'mobile':$("#mobile").val(), 'type':2},
			jsonp:'jsoncallback',
			jsonpCallback:'callback',
			error:function(){alert('出错啦');code_clickable = true;},
			success:function(d){
				code_clickable = true;
				var tip = '';
				if(d.code != '0'){
					switch(d.code){
						case '3':
							tip = '您发送的短信在两小时内超过限制，请稍后重试。';
							break;
						case '5':
							tip = '请输入正确的图片验证码。';
							clearCountDown();
							break;
						default:
							tip = d.message;
					}
					alert(tip);
					$("#imgCode").click();
					$("#imgCodeVal").val('');
					return;
				}
			}
		});
	});
	
	$(".lg_nav li").click(function(){ 
		var index=$(".lg_nav li").index(this);
		$('.lgin_wp_box').hide();
		$('.lg_box'+index).show();
		$(".lg_nav li[class*='hover']").removeClass("hover");
		$(this).addClass("fz12 hover");
    });
    $(".lg_nav li:eq(1)").click();

    // ajax登陆
    var formclickable = true;
    cfg.form.submit(function() {
    	var rem = $(this).find('.remember'),
    		id = rem.attr('data-for'),
    		type = $(this).find("input[name=type]").val();
    	if(type == 'mobile' && !checkMobile(1)) return false;
    	if(type == 'password' && !checkForm()) return false;

    	if(!formclickable) return false;
		formclickable = false;
    	$.post('login.php', $(this).serialize(), function(d){
			formclickable = true;
			var d = eval("("+d+")");
			if(d.code != '2'){
				alert(d.msg);
				return;
			}
			if(typeof d.rec == 'undefined'){
				alert('网络错误！');
				return;
			}
			alert('登陆成功');
			var hf = location.href;
			// 1.php  1.php?openid=xx  1.php?rec=22  1.php?oid=xx&rec=22
			// 替换成他的id
			if(hf.indexOf('?') == -1){
				location.href = hf + '?rec='+d.rec;
			}else{
				location.href = hf.replace(/&?rec=\d+/,'')+'&rec='+d.rec;
			}
		});

		// 记住用户名..
    	if(rem.is(":checked")){
    		setCookie(id, $("#"+id).val(), 30);
    	}

		return false;
    });

 
 	$(".dh_but").live("click",function(){
		UiShow('hdsm_msg');
	});
	$(".login_but").live("click",function(){
		UiHide();
		UiShow('login_msg');
	});
	var hasget = false;
	$(".zj_but").live("click",function(){
		if(!hasget){
			// 获取中奖名单
			$.ajax({
				url:'data.php?act=getLuckList',
				async:false,
				success:function(d){
					hasget = true;
					var d = eval("("+d+")"), k, temp = '', len = d.length, append = 10-len-1;
					if(len == 0) return;
					for(k in d){
						temp += '<li class="fz10"><font>'+d[k]['mobile']+' </font><font>'+d[k]['reward']+'</font></li>';
					}
					// 10个中奖
					if(append <= -1){
						$("#lucklist ul").html(temp);
						return;
					}
					$("#lucklist li:gt("+append+")").remove();
					$("#lucklist li:last").after(temp);
				}
			});
		}
		
		UiShow('zjmd_msg');
	});
	$(".my_but").live("click",function(){
		$.get('data.php?act=getMyReward', function(d){
			var d = eval("("+d+")"), k, temp = '';
			if(d === 0){
				UiShow('no_denglu');
				return ;
			}
			if(d.length != 0){
				for(k in d){
					temp += '<li class="fz10"><font>'+d[k]['reward']+'</font><font>'+d[k]['date']+'</font></li>';
				}
				$("#myreward ul").html(temp);
				UiShow('myjp_msg');
			}else{
				UiShow('no_jp');
			}
		});
	});
 
	$(".ui_exit").live("click",function(){
		UiHide();
	});
	
	$("#fx_msg").live("click",function(){
		$("#fx_msg").hide();
		$('#Black_bg').hide();
	});

	init();

	function checkForm(){
		if($("#uname1").val() == ''){
			alert('请填写账号');
			return false;
		}
		if($("#pwd").val() == ''){
			alert('请填写密码');
			return false;
		}
		return true;
	}
	function checkMobile(isCheckForm){
		var v = $("#mobile").val();
		if(!/^(1[3578][0-9]|14[57])\d{8}$/.test(v)){
			alert('请填写正确的手机号');
			return false;
		}
		// 表单提交验证
		if(typeof isCheckForm != 'undefined' && !/^\d{4}$/.test($("#code").val())){
			alert('请填写正确的验证码');
			return false;
		}
		return true;
	}
	/*中奖滚动*/
	function UiShow(id){
		uishowid = id;
		var wh=$("#"+id).width();
		var hg=Math.floor(wh/1.19);
		$('#Black_bg').show();
		$('#'+id).css({top:'50%',left:'50%',marginLeft:'-'+Math.floor(wh/2)+'px',marginTop:'-'+Math.floor(hg/2)+'px'});
		$('#'+id).show();
		if(id=='zjmd_msg' && gd==0){
			gd++;
			$('.wj_gd_box').kxbdMarquee({
				direction:'up',
				controlBtn:{left:'#goL',right:'#goR'},
				eventA:'mouseenter',
				eventB:'mouseleave',
				isEqual:false
			});
		}
		/*if(id=='myjp_msg' && gd2==0){
			gd2++;
			$('.my_gd_box').kxbdMarquee({
					direction:'up',
					controlBtn:{left:'#goL',right:'#goR'},
					eventA:'mouseenter',
					eventB:'mouseleave',
					isEqual:false
			});
		}*/
	}
	function UiHide(id){
		$('#Black_bg').hide();
		$('.msg').hide();
	}
	function init(){
		// 填充记住的用户名
		$(".remember").each(function(){
			var id = $(this).attr('data-for'),
				ck = getCookie(id);
			if(ck){
				$(this).attr('checked', true);
				$("#"+id).val(ck);
			}
		});
		// 初始化忘记密码等的跳转地址
		var hf = encodeURIComponent(location.href);
		$(".forgotpwd").attr('href', cfg.forgoturl+ '&callback=' + hf);
		$(".reg").attr('href', cfg.regurl+ '&callback=' + hf);
		$("#imgCode").attr('src', cfg.codeurl);
	}

	function _resize(){
		var wh=$(window).width();
		var dwh=$('.zbg').width();
		$(".hdr").css('font-size',Math.floor((12/320)*wh));
		$(".sb-but").css('height',Math.floor((32/320)*wh));
		$('.main').height(Math.round($('.main').width()*1.608));
		$('.zp-wp').height($('.zp-wp').width());
		$('.zbg').height($('.zbg').width());
		var mt=Math.round((dwh/2)+(wh/640*9));
		var lt=Math.round((dwh/2)+(wh/640*0.5));
		$('.zbg').css({top:'50%',left:'50%',marginLeft:'-'+lt+'px',marginTop:'-'+mt+'px'});
		$(".lh52").css({'height':Math.floor((26/320)*wh),'lineHeight':Math.floor((26/320)*wh)+'px'});
		$(".lh55").css({'height':Math.floor((27/320)*wh),'lineHeight':Math.floor((27/320)*wh)+'px'});
		$('.gd_box').height(Math.floor((77/320)*wh));
		
		$(".fz12").css('font-size',Math.floor((12/320)*wh));
		$(".fz10").css('font-size',Math.floor((10/320)*wh));
		$(".fztt").css('font-size',Math.floor((16/320)*wh));
		$(".fz15").css('font-size',Math.floor((15/320)*wh));
		if(uishowid){
			var uwh=$("#"+uishowid).width();
			var uhg=$("#"+uishowid).height();
			$('#'+uishowid).css({top:'50%',left:'50%',marginLeft:'-'+Math.floor(uwh/2)+'px',marginTop:'-'+Math.floor(uhg/2)+'px'});
		}
	}
	
}); 

