var cfg = {
	isdev:true,
	form:$("#myform1,#myform2"),
	testcodeurl:'http://test.cignacmb.com/member/m/validate-code.xhtml?source=OW',// 页面验证码
	onlinecodeurl:'https://member.cignacmb.com/validate-code.xhtml?source=OW',
	testregurl:'http://test.cignacmb.com/member/register.xhtml?source=OW',// 注册
	onlineregurl:'https://member.cignacmb.com/register.xhtml?source=OW',
	test_forgot_url:'http://test.cignacmb.com/member/retrieve/pwd.xhtml?source=OW',// 忘记密码
	online_forgot_url:'https://member.cignacmb.com/retrieve/pwd.xhtml?source=OW',
	testsmsurl:'http://test.cignacmb.com/member/sendsms.json?source=OW',// 发短信
	onlinesmsurl:'https://member.cignacmb.com/sendsms.json?source=OW',
},host = window.location.host;

// 正式环境
if(host.indexOf('cignacmb') != -1 && host.indexOf('test.cignacmb') == -1){
	cfg.isdev = false;
}
// cfg.isdev = false;
if(cfg.isdev){
	cfg.regurl    = cfg.testregurl;
	cfg.forgoturl = cfg.test_forgot_url;
	cfg.smsurl    = cfg.testsmsurl;
	cfg.codeurl   = cfg.testcodeurl;
}else{
	cfg.regurl    = cfg.onlineregurl;
	cfg.forgoturl = cfg.online_forgot_url;
	cfg.smsurl    = cfg.onlinesmsurl;
	cfg.codeurl   = cfg.onlinecodeurl;
}

// cfg.isdev = false;


/*-------function-------*/

function c(v){
	if(typeof console != 'undefined') console.log(v);
}


function getCookie(name){
	var value = '',
		search = name + '=';
	if (document.cookie.length > 0) {
		offset = document.cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = document.cookie.indexOf(';', offset);
			if (end == -1) {
				end = document.cookie.length;
			}
			value = unescape(document.cookie.substring(offset, end))
		}
	}
	return value;
}

function setCookie(name, value, days) {
	var date = new Date();
	var d = days || 1;
	date.setDate(date.getDate() + d);
	document.cookie = name + "=" + escape(value) + "; expires=" + date.toGMTString();
}
