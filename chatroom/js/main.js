var uname;

$(function(){
	uname = setuname(getuname(false));
	$("#mainc").click(function(){
		$("#mainc").scrollTop($("#mainc")[0].scrollHeight - $("#mainc").height());
		$(this).blur();
	});
	$("#sendMsg").click(send);
	$("#cleanMsg").click(function(){
		$("#mainc").val('');
		$("#msgc").focus();
	});
	
	$("#msgc").keypress(function(){
		var event = arguments[0]||window.event;
	    var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
		if(keyCode == 13){
			send();
			return false;
		}
	});
	
	$("#modify_name").click(function(){
		uname = setuname(getuname(true,true));
	});
	
	window.onbeforeunload=logout;
	getonlinelist();
	setInterval("showmsg()",1000);
})

function getuname(force,ignorecookie){
	var ignorecookie = ignorecookie || false;
	if(force){
		var name = window.prompt('输入您的昵称');
		return name != null ? name : getuname(false,ignorecookie);
	}
	if(!$.cookie('uname') || ignorecookie){
		if(!confirm('使用随机昵称?')) return getuname(true,ignorecookie);
		return '';
	}
	return $.cookie('uname');
}

function showmsg(){
	$.ajax({
		url:'deal.php?act=getmsg',
		type:'get',
		dataType:'json',
		async:false,
		timeout:800,
		ifmodified:true,
		success:function(data){
			if(data){
				for (key in data){
					var arr=data[key],len=arr.length,i=0;
					switch(key){
					case 'offlinelist':
						for(;i<len;i++){
							$(".member ul li").each(function(){
								if($(this).text()==arr[i])
									$(this).remove();
							});
						}
						break;
					case 'newuser':
						getonlinelist();
						break;
					default:
						for(;i<len;i++){
							if(arr[i]['uname']!=uname){
								$("#mainc").val($("#mainc").val()+"\r\n"+arr[i]['uname']+' ('+key+')'+"\r\n"+arr[i]['msg']);
								$("#mainc").click();
							}
						}
					}
				}
			}
		}
	});
}

function send() {
	var mainc = $("#mainc");
	var newmsg = $('#msgc').val();
	if(newmsg.replace(/\s|\n/,'')=='') {
		$('#msgc').focus();
		return false;
	}
	var time = addNew(newmsg);
	if (time == 0) {
		alert('发送失败');
		return false;
	}
	mainc.val(mainc.val() + "\r\n" + uname + ' (' + time + ')'+"\r\n" + newmsg);
	mainc.click();
	$('#msgc').val('').focus();
}

function addNew(msg) {
	var time = 0;
	$.ajax({
		url : 'deal.php?act=newmsg',
		type : 'post',
		async : false,
		data : {
			uname:uname,
			msg : msg
		},
		success : function(d) {
			time = d;
		}
	});
	return time;
}

function setCursor(id, position) {
    var txtFocus = document.getElementById(id);
    if ($.browser.msie) {
        var range = txtFocus.createTextRange();
        range.move("character", position);
        range.select();
    } else {
       txtFocus.setSelectionRange(position, position);
       txtFocus.focus();
   }
}

function setuname(uname) {
	var rs;
	$.ajax({
		url : 'deal.php',
		data:{act:'setuname',uname:uname},
		type : 'get',
		dataType : 'json',
		async : false,
		success : function(data) {
			rs = data;
		}
	});
	$.cookie('uname',rs);//,{path:'/',expire:new Date().getTime()+30*86400}
	return rs;
}

function logout(){
	$.cookie('uname',null);
	$.ajax({url:'deal.php?act=logout',async:false});
	$.cookie('PHPSESSID',null);
}

function getonlinelist(){
	$.get('deal.php?act=getlist',function(data){
		var d=eval("("+data+")");
		if(d.length>0){
			var li='',i;
			for(i in d){
				//li+='<li id=user'+i+'>'+d[i]+'</li>';
				li+='<li>'+d[i]+'</li>';
			}
			$(".member ul").html(li);
		}
	});
}
