<?php
error_reporting(E_ALL | E_STRICT);
// form提交或ajax post提交代码
$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
if(!empty($_POST)){    
    header('X-XSS-Protection: 0');    
    $code = get_magic_quotes_gpc() ? stripslashes($code) : $code;
    if (isset($_POST['hidden']) && $_POST['hidden'] == 1) {
        echo $code;
    } else {
        date_default_timezone_set('PRC');
		echo eval($code);
    }
    exit();
}
// 二维码
if(isset($_REQUEST['qrcode'])){
	if(!file_exists('qrcode/Qrcode.class.php')) exit('0');
	require 'qrcode/Qrcode.class.php';
	$qr = new Qrcode();
	$qr->png(urldecode($code));
	exit();
}

header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
//$func = get_extension_funcs('standard');
//sort($func);
$func = array ('' => '无', 'urldecode' => 'url解码', 'md5' => 'md5加密', 'date', 'strlen', 'json_encode', 'json_decode', 'base64_encode', 'base64_decode'); //键名是php函数,键值是描述,未指定键名则两者相同.
$type = array ('echo', 'var_dump', 'print_r');

$default = array ('func' => 'urldecode', 'type' => 'print_r');
$expire_day = 365;
$expire = time() + $expire_day * 86400;
if (!isset($_COOKIE['often_func'])) {
    setcookie('often_func', json_encode($func), $expire, '/');
    setcookie('often_func_default', $default['func'], $expire, '/');
    print_r($func);
} else {
    $func = json_decode($_COOKIE['often_func'], 1);
    if(!empty($_COOKIE['often_func_default'])){
        $temp = trim($_COOKIE['often_func_default'], '"');
    }else{
        $temp = current($func);
    }
    $default['func'] = $temp;
}

$option = $resulttype = '';
foreach ( $func as $val => $desc ) {
    if (is_numeric($val)) $val = $desc;
    $selected = $val == $default['func'] ? ' selected' : '';
    $option .= "<option value='{$val}'{$selected}>{$desc}</option>";
}

foreach ( $type as $val ) {
    $chked = $val == $default['type'] ? ' checked' : '';
    $resulttype .= "<input{$chked} type='radio' name='resulttype[]' class='resulttype' value='{$val}' />{$val}";
}

$phpfunc = get_defined_functions();
$phpfunc = $phpfunc['internal'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>

<!-- code mirror -->
<link rel="stylesheet" href="codemirror/lib/codemirror.css">
<link rel="stylesheet" href="codemirror/theme/xq-light.css">
<style type="text/css">
     .CodeMirror {border: 1px solid #888; font-size:13px}
</style>
<script src="codemirror/lib/codemirror.js" type="text/javascript"></script>
<script src="codemirror/mode/javascript/javascript.js"></script>
<script src="codemirror/addon/selection/active-line.js"></script>
<script src="codemirror/addon/edit/matchbrackets.js"></script>

<script src="jq.js" type="text/javascript"></script>
<!-- 复制 -->
<script type="text/javascript" src="ZeroClipboard.js"></script>
<script type="text/javascript">ZeroClipboard.setMoviePath( "ZeroClipboard.swf" );</script>
<script type="text/javascript">
//cookie
jQuery.cookie = function(name, value, options){
    if (typeof value != 'undefined') { // name and value given, set cookie 
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            }
            else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString();
        }
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    }
    else {
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

</script>
<style>
*{margin: 0;padding: 0;list-style: none}
.t {text-align: left;font-size: 13px;margin-left: 10px;width: 1000px;height: 220px;margin-left: 150px;}
.shadow {box-shadow: 1px 0 2px #0f0;}
#code {margin-top: 30px;}
#rs {line-height: 16px;border: 1px solid #888;}
#i {display: none;border: 1px solid #888;}
.input {margin-left: 150px;margin-top: 10px;}
.input input {margin-top: 5px;outline: none;}

#func_list{display: none;position: absolute;box-shadow: 1px 1px 3px #ededed; border: 1px solid #ccc;}
#func_list li{display: none;padding: 0 10px;font:14px/25px 'Microsoft Yahei';height: 25px;}
#func_list li:hover{background: #eee;cursor: pointer;}
</style>
</head>
<body>
<!-- 代码提示功能 -->
<ul id="func_list">
    <li>qqq</li>
    <li>www</li>
    <li>eee</li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>
<form action="" method="post" name="form1" id="form1" target="i">
    <div style="width:1000px;margin-left: 150px;">
    	<!-- 代码框 -->
    	<textarea class="t" name="code" id='code'></textarea>
    </div>
	<br />
	<!-- 按钮 -->
	<div class="input">
		<input type="button" id="init" value="HTML初始化/清空(ctrl+r)" /> <input type="submit" value="运行(ctrl+enter)" />
        <input type="button" id="qrcode" value="二维码(ctrl+e)">
		<!-- 如果设置name属性,用ctrl+enter提交,php获取不到name,而点击提交则可以,不知为啥 -->
		<input type="button" id="quick" value="ctrl+q=>" /> <select id="func">
        <?php echo $option?>
    </select>
    <?php echo $resulttype?>
        <!-- 添加常用函数 -->
		<div class="often">
			<input type="text" id="often_func" placeholder="常用函数" autocomplete="off" /> <input type="text" id="desc" placeholder="描述(可选)" /> <input type="button" id="dftfunc" value="默认" /> <input type="button" id="addfunc" value="添加" /> <input type="button" id="delfunc" value="删除" />
            <input type="button" id="copy" value="复制结果" />
		</div>
	</div>
    <!-- 按钮end -->
</form>
<br />
<!-- 结果框 -->
<iframe name="i" src="" id='i' class="t"></iframe>
<div class="t" id='rs'></div>

<script type="text/javascript">
var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
    lineNumbers: true,
    styleActiveLine: true,
    matchBrackets: true,
    theme: 'xq-light'
});
editor.focus();
</script>

<script type="text/javascript">
    var c = function(s){ console.log(s)};
	
    var str = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' +
    "<html>" +
    "<head>" +
    "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>" +
    "<title>Insert title here</title>" +
    "<script src='jq.js' type='text/javascript'><\/script>" +
	"<script>function c(){for(var i = 0;i < arguments.length;i++) console.log(arguments[i])}<\/script>"+
    "\n<style>"+
    "\n*{margin:0;padding:0;}"+
    "\n</style>"+
    "\n<\/head>" +
    "\n<body>" +
    "\n\n" +
    "<script>" +
    "\n\n" +
    "<\/script>" +
    "\n<\/body>" +
    "\n<\/html>";
    
    var env = 'php',// 当前编辑器处理什么代码
        lastfunc = '',
        lastpos,
        lastwd = '',// 用于自动补完标签
        words = '',// 用于代码提示
        ignore_start = false,// 开始忽略标志
        ignore_end = false,// 结束忽略标志
        ignore_real_end = false,// 真正结束
        func_start = false,// 开始拼接函数标志
        func_end = false,// 结束拼接函数标志
        phpfunc = <?php echo json_encode($phpfunc);?>,// php 内置函数用于函数提示功能
        phpfunc_remind_count = $("#func_list li").length,// 下拉显示函数个数
        func_list = $("#func_list"),// 函数提示div
        code = $(".CodeMirror textarea"),
        codeInfo = {'width' : code.width(), 'height' : code.height(), 'lineH' : parseFloat(code.css('line-height')), 'fontsize' : code.css('font-size'), 'offset': code.offset()};

    $(document).ready(function(){
        $("#init").click(function(){
            init();
        });
        $("#quick").click(function(){
            quick_func(editor.getValue());
        });
        $(document).on('keypress', '.CodeMirror textarea', function(e) {
            var k = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if (e.ctrlKey) {
                //enter
                if (k == 13) {
                    console.clear();
                    autosubmit();
                    e.preventDefault();
                }
                // e
                if(k == 101){
                    qrcode();
                    e.preventDefault();
                }
                //r
                if (k == 114) {
                    init();
                    e.preventDefault();
                }
                //q
                if (k == 113) {
                    $("#quick").click();
                }
            }
        });
        $("#often_func,#desc").keypress(function(e){
        	var k = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        	if(k == 13){
        	    $("#addfunc").click();
        	    $("#often_func").focus();
        	    e.preventDefault();
        	}
        });
        
        var often_func = eval("("+$.cookie('often_func')+")");
        $("#addfunc").click(function(){
            var func = $("#often_func").val(),desc = $("#desc").val();
            if(func == '') return;
            addfunc(often_func, func, desc);
            setcookie('often_func',often_func);
            change_select(func);
        });
        $("#delfunc").click(function(){
        	var func = $("#often_func").val();
        	if(func == '') func = $("#func option:selected").val();
        	often_func = delObjVal(func,often_func);
        	setcookie('often_func',often_func);
        	change_select($.cookie('often_func_default'));
        });
        $("#dftfunc").click(function(){
            var func = $("#often_func").val();
            var selectedfunc = $("#func option:selected").val();
            if(func == ''){
                setcookie('often_func_default',selectedfunc);
                $("#often_func").val(selectedfunc);
            }
            else{
            	setcookie('often_func_default',func);
                if(!inObject(func,often_func)) $("#addfunc").click();
                $("#often_func").val('');
            }
        });

        var clip = new ZeroClipboard.Client();
        clip.setHandCursor(true);   
        clip.addEventListener('mouseOver', function(client){
            var s = $("#i").is(':visible') ? $("#i").contents().find('body').html() : $("#rs pre").html();
            clip.setText(s);
        });     
        clip.addEventListener('complete', function(client, text){
            c(text+"已复制");
        });
        clip.glue('copy');

        // copyToClipboard('asd');
        
        $("#func,.resulttype").change(function(){
            editor.focus();
            if(editor.getValue() != '') $("#quick").click();
        });
        $('#form1').submit(function(){
            if (editor.getValue().indexOf('<html>') < 0) {
                getRs();
                return false;
            }
            if($("#hidden").length==0){
    			$("#code").after('<input type="hidden" name="hidden" id="hidden" value="1" />');
    		}
            show_result_div('html');
        })
    
        $("#code,#rs,#i,input[type=text]").hover(function(){
            $(this).addClass('shadow');
        },function(){
            $(this).removeClass('shadow');
        });

        $("#qrcode").click(function() {
            qrcode();
        });
    })

    function qrcode(){
        show_result_div('php');
        $("#rs").html("<img src='?qrcode=1&code="+encodeURIComponent(editor.getValue())+"'>");
    }
    
    function autosubmit(){
        if (editor.getValue().indexOf('<html>') > 0) {
            $("#form1").submit();
        }
        else {
            getRs();
        }
        return;
    }

    function getRs(){
        show_result_div('php');
        $.post('index.php', {
            code: editor.getValue()
        }, function(data){
            $('#rs').html('<pre>' + data + '</pre>');
        });
    }
    
    function setcookie(name,obj_val){
    	$.cookie(name,JSON.stringify(obj_val),{path:'/',expires:<?php echo $expire_day?>});
    }
    
    function quick_func(code){
    	var reg = /echo |print_r|var_dump/i;
    	if(reg.test(code)){
    		code = code.replace(reg,'',code).replace(/;$/,'');
    	    code = code.replace(eval('/\\(?'+lastfunc+'\\("(.*)"\\)\\)?/'),'$1');
    	}
        var func = $("#func option:selected").val();
        lastfunc = func;
        var type = $(".resulttype:checked").val(), left = '', right = '', left2 = '',right2 = '';
        if (type == 'echo') {
            type += ' ';
        }
        else {
            left = '(';
            right = ')';
        }
        if(code.indexOf('"') == -1){
            left2 = '("';
            right2 = '")';
        }else{
            left2 = "('";
            right2 = "')";
        }
        var tempstr;
        switch(func){
            // date格式化
            case 'date':
                tempstr = type + left + func + "('Y-m-d H:i:s',"+ code + right + ');';
            break;
            default:
                tempstr = type + left + func + left2 + code + right2 + right + ';';
        }
        editor.setValue(tempstr);
        $("#form1").submit();
    }
    
    // 清空/初始化html
    function init(){
        var html = editor.getValue();
        
        if (html == '') {
            env = 'html';
            editor.setValue(str);
        }
        else {
            env = 'php';
            editor.setValue('');
        }
        return;
    }

    //f:func,d:description
    function addfunc(o,f,d){
    	var hasOwn = Object.prototype.hasOwnProperty, length = 0;
		if ( d === undefined || d == '') {
			while( hasOwn.call( o, d = length++ ) );
		};
		o[ d ] = f;
    }
    
    function delObjVal(val,o){
        //delete o.k;不好使
        var o1 = new Object(),v;
        for(var k in o){
            v = /^\d+$/.test(k) ? o[k] : k;
            if(v != val) o1[k] = o[k];
        }
        return o1;
    }
    
    function inObject(search,obj){
        var k,v,result = false;
        for(k in obj){
            v = /^\d+$/.test(k) ? obj[k] : k;
            if(v == search){
            	result = true;
                break;
            }
        }
        return result;
    }
    function getObjCount(o){
        var count = 0,i;
        for(i in o) {count++;console.log(o[i])}
        return count;
    }
    
    function change_select(value){
        var o = eval("("+$.cookie('often_func')+")"),option = '',k;
        for(k in o){
            func = /^\d+$/.test(k) ? o[k] : k;
            val = func == '' ? '无' : func;
            selected = value == func ? ' selected' : '';
            option += '<option value="'+func+'"'+selected+'>'+val+'</option>';
        }
        $("#func").html(option);
        $("#often_func,#desc").val('');
    }
    
    function show_result_div(type){
        if(type == 'php'){
        	$("#rs").show();
            $("#i").hide();
        }else{
        	$("#rs").hide();
            $("#i").show();
        }
    }
</script>
</body>
</html>