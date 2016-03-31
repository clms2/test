<?php
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
//$func = get_extension_funcs('standard');
//sort($func);
$func = array ('' => '无', 'urldecode' => 'url解码', 'md5' => 'md5加密', 'strlen', 'json_encode', 'json_decode', 'base64_encode', 'base64_decode'); //键名是php函数,键值是描述,未指定键名则两者相同.
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
    $default['func'] = trim($_COOKIE['often_func_default'], '"');
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
<script src="jq.js" type="text/javascript"></script>
<!-- 复制 -->
<script type="text/javascript" src="ZeroClipboard.js"></script>
<script type="text/javascript">ZeroClipboard.setMoviePath( "ZeroClipboard.swf" );</script>
<script type="text/javascript">
//在光标处插入文字
(function($){
    $.fn.extend({
        "insert": function(str){
            //默认参数
            var dthis = $(this)[0]; //将jQuery对象转换为DOM元素
            //IE下
            if (document.selection) {
                $(dthis).focus(); //输入元素textara获取焦点
                var fus = document.selection.createRange();//获取光标位置
                fus.text = str; //在光标位置插入值
                $(dthis).focus(); ///输入元素textara获取焦点
            }
            //火狐下标准	
            else 
                if (dthis.selectionStart || dthis.selectionStart == '0') {
                    var start = dthis.selectionStart;
                    var end = dthis.selectionEnd;
                    var top = dthis.scrollTop;
                    //以下这句，应该是在焦点之前，和焦点之后的位置，中间插入我们传入的值
                    dthis.value = dthis.value.substring(0, start) + str + dthis.value.substring(end, dthis.value.length);
                    //设置光标位置
                    dthis.setSelectionRange((dthis.value.substring(0, start) + str).length, (dthis.value.substring(0, start) + str).length);
                }
                //在输入元素textara没有定位光标的情况
                else {
                    this.value += str;
                    this.focus();
                };
            return $(this);
        },
    })
})(jQuery)

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
//json
hasOwn = Object.prototype.hasOwnProperty;
$.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function(o){
    if (o === null) {
        return 'null';
    }
    
    var pairs, k, name, val, type = $.type(o);
    
    if (type === 'undefined') {
        return undefined;
    }
    
    // Also covers instantiated Number and Boolean objects,
    // which are typeof 'object' but thanks to $.type, we
    // catch them here. I don't know whether it is right
    // or wrong that instantiated primitives are not
    // exported to JSON as an {"object":..}.
    // We choose this path because that's what the browsers did.
    if (type === 'number' || type === 'boolean') {
        return String(o);
    }
    if (type === 'string') {
        return $.quoteString(o);
    }
    if (typeof o.toJSON === 'function') {
        return $.toJSON(o.toJSON());
    }
    if (type === 'date') {
        var month = o.getUTCMonth() + 1, day = o.getUTCDate(), year = o.getUTCFullYear(), hours = o.getUTCHours(), minutes = o.getUTCMinutes(), seconds = o.getUTCSeconds(), milli = o.getUTCMilliseconds();
        
        if (month < 10) {
            month = '0' + month;
        }
        if (day < 10) {
            day = '0' + day;
        }
        if (hours < 10) {
            hours = '0' + hours;
        }
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        if (seconds < 10) {
            seconds = '0' + seconds;
        }
        if (milli < 100) {
            milli = '0' + milli;
        }
        if (milli < 10) {
            milli = '0' + milli;
        }
        return '"' + year + '-' + month + '-' + day + 'T' +
        hours +
        ':' +
        minutes +
        ':' +
        seconds +
        '.' +
        milli +
        'Z"';
    }
    
    pairs = [];
    
    if ($.isArray(o)) {
        for (k = 0; k < o.length; k++) {
            pairs.push($.toJSON(o[k]) || 'null');
        }
        return '[' + pairs.join(',') + ']';
    }
    
    // Any other object (plain object, RegExp, ..)
    // Need to do typeof instead of $.type, because we also
    // want to catch non-plain objects.
    if (typeof o === 'object') {
        for (k in o) {
            // Only include own properties,
            // Filter out inherited prototypes
            if (hasOwn.call(o, k)) {
                // Keys must be numerical or string. Skip others
                type = typeof k;
                if (type === 'number') {
                    name = '"' + k + '"';
                }
                else 
                    if (type === 'string') {
                        name = $.quoteString(k);
                    }
                    else {
                        continue;
                    }
                type = typeof o[k];
                
                // Invalid values like these return undefined
                // from toJSON, however those object members
                // shouldn't be included in the JSON string at all.
                if (type !== 'function' && type !== 'undefined') {
                    val = $.toJSON(o[k]);
                    pairs.push(name + ':' + val);
                }
            }
        }
        return '{' + pairs.join(',') + '}';
    }
};


// 字符串占网页宽高
function textSize(fontSize, text) {
    var span = document.createElement("span");
    var result = {};
    result.width = span.offsetWidth;
    result.height = span.offsetWidth; 
    span.style.visibility = "hidden";
    if(fontSize.indexOf('px') === -1) fontSize += 'px';
    span.style.fontSize = fontSize; 
    document.body.appendChild(span);
    if (typeof span.textContent != "undefined")
        span.textContent = text;
    else span.innerText = text;
    result.width = span.offsetWidth - result.width;
    result.height = span.offsetHeight - result.height;
    span.parentNode.removeChild(span);
    return result;
}

 /**
 * 获取光标在输入框中的位置
 * @param inpObj 框Id/document.getElementById对象
 * @return int
 */
function getCursorPos(inpObj){
    var inpObj = typeof inpObj == 'object' ? inpObj : document.getElementById(inpObj);
    if(navigator.userAgent.indexOf("MSIE") > -1) { // IE
        var range = document.selection.createRange();
        range.text = '';
        range.setEndPoint('StartToStart',inpObj.createTextRange());
        return range.text.length;
    } else {
        return inpObj.selectionEnd;
    }
}

/**
 * php substr_count的js版
 * @param  {string} str    
 * @param  {string} search 
 * @param  {int} total  不需要传该参数
 * @return {int}        
 */
function substr_count(str, search, total){
    var total = total || 0,
        index = str.indexOf(search);

    if(index === -1) return total;

    return substr_count(str.substr(++index), search, ++total);
}

/**
 * 选择文本
 * @textbox : 要操作的文本对象
 * @startIndex : 要选择文本中第一个字符的索引
 * @stopIndex : 要选择文本最后一个字符之后的索引
 */
function selectText(textbox, startIndex, stopIndex){
    if(typeof textbox == 'string') textbox = document.getElementById(textbox);
    if(textbox.setSelectionRange){
        textbox.setSelectionRange(startIndex,stopIndex);
    }else if(textbox.createTextRange){
        var range=textbox.createTextRange();
        range.collapse(true);
        range.moveStart('character',startIndex);
        range.moveEnd('character',stopIndex-startIndex);
        range.select();
    }
    textbox.focus();
}


/**
 * 获取输入框选中的文本
 * @param  {string/object} editor #id 或者document.getElementById对象
 * @return {string}
 */
function getSelectText(editor) {
    if (!editor) return; 
    if(typeof editor == 'string') editor = document.getElementById(editor);
    editor.focus();
    if (editor.document && editor.document.selection)
        return editor.document.selection.createRange().text; 
    else if ("selectionStart" in editor)
        return editor.value.substring(editor.selectionStart, editor.selectionEnd); 
}


</script>
<style>
.t {
	text-align: left;
	font-size: 13px;
	margin-left: 10px;
	width: 1000px;
	height: 220px;
	margin-left: 150px;
}

.shadow {
	box-shadow: 1px 0 2px #0f0;
}

#code {
	margin-top: 30px;
}

#rs {
	line-height: 16px;
	border: 1px solid #888;
}

#i {
	display: none;
	border: 1px solid #888;
}

.input {
	margin-left: 150px;
	margin-top: 10px;
}

.input input {
	margin-top: 5px;
	outline: none;
}
</style>
</head>
<body>
<form action="d.php" method="post" name="form1" id="form1" target="i">
	<!-- 代码框 -->
	<textarea class="t" name="code" id='code'></textarea>
    <!-- <div contentEditable="true" class="t" name="code" id='code'></div> -->
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
</form>
<br />
<!-- 结果框 -->
<iframe name="i" src="" id='i' class="t"></iframe>
<div class="t" id='rs'></div>
<script type="text/javascript">
    var c = function(s){console.log(s)};
	$("#code").focus();
	
    var str = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' +
    "<html>" +
    "<head>" +
    "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>" +
    "<title>Insert title here</title>" +
    "<script src='jq.js' type='text/javascript'><\/script>" +
	"<script>function c(s){console.log(s)}<\/script>"+
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
    
    var lastfunc = '',
        lastpos,
        lastwd = '',
        code = $("#code"),
        codeInfo = {'width' : code.width(), 'height' : code.height(), 'lineH' : code.css('line-height'), 'fontsize' : code.css('font-size')};
    // 组织firefox ctrl+r刷新页面
    $(document).keypress(function (e) {
        var k = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        if (e.ctrlKey) {
            if (k == 114) {
                e.preventDefault();
            }
        }
    })
    $(document).ready(function(){
        $("#init").click(function(){
            init();
        });
        $("#quick").click(function(){
            quick_func($("#code").val());
        });
        // 快捷键
        code.keypress(function(e){
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
            //tab 加\t
            if (k == 9) {
                $(this).insert("\t");
                e.preventDefault();
            }
        }).keyup(function(){
            // todo.. 实现输入</自动补完标签功能，以及类似编辑器的函数提示功能
            var pos,// 光标位置
                curwd,// 本次输入内容
                txt,// 选中文本
                temp,// 临时变量
                cursorSetted = false;// 是否已setCursor过
            // 获得当前光标位置
            pos = getCursorPos(this);
            if(pos == lastpos || pos === 0) return;
            lastpos = pos;

            // 自动补完标签功能
            // do while:不要太多缩进啦

            // 获得光标位置往前一位的字母，即刚输入的字母
            // todo.. 可优化在keyup的时候赋值给lastwd
            selectText(this, pos-1, pos);
            curwd = getSelectText(this);
            do{
                // 第一次输入跳过
                if(lastwd == '') break;
                // 判断这次输入的和上次输入的拼接起来的字符串是否为</
                temp = lastwd + curwd;
                if(temp !== '</') break;

                // 获得当前光标到起点的所有文本
                selectText(this, 0, pos);
                txt = getSelectText(this);

                // 从后往前检索起始标签位置，不要刚输入的<
                temp = txt.lastIndexOf('<', txt.length - 3);
                if(temp == -1) break;

                // 截取后面的文本以获取标签
                temp = txt.substr(temp);
                var index = temp.indexOf(' ');
                if(index == -1) index = temp.indexOf('>');
                // 跳过未找到结尾>的
                if(index == -1) break;

                // 这个就是tag啦
                var tag = temp.substring(1, index);
                // optional todo ..可以再判断下这个tag是否合法tag
                // 补完标签
                tag = tag + '>';
                setCursor(this, pos);
                $(this).insert(tag);
                setCursor(this, pos + tag.length);
                cursorSetted = true;
            }while(false);
            // 记住这次输入的字母
            lastwd = curwd;

            // 取消选择
            if(!cursorSetted){
                setCursor(this, pos);
            }

            return;

            // 获取光标距离输入框左侧和顶部的距离，用于动态定位代码提示的div

            // 选中当前光标到起点的文本
            selectText(this, 0, pos);
            // 获得选中文本
            var txt = getSelectText(this);
            // 获取选中文本宽高
            var size = textSize(codeInfo.fontsize, txt);
            var rowNum = substr_count(txt, '\n');
            c(rowNum)

            setCursor(this, pos);
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
        console.log(often_func);
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
            $("#code").focus();
            if($("#code").val() != '') $("#quick").click();
        });
        $('#form1').submit(function(){
            if ($("#code").val().indexOf('<html>') < 0) {
                getRs();
                return false;
            }
            if($("#hidden").length==0){
    			$("#code").after('<input type="hidden" name="hidden" id="hidden" value="1" />');
    		}
            show_result_div('html');
        })
        $("#code").focus();
    
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
        $("#rs").html("<img src='d.php?qrcode=1&code="+encodeURIComponent($("#code").val())+"'>");
    }
    
    function autosubmit(){
        if ($("#code").val().indexOf('<html>') > 0) {
            $("#form1").submit();
        }
        else {
            getRs();
        }
        return;
    }

    function getRs(){
        show_result_div('php');
        $.post('d.php', {
            code: $('#code').val()
        }, function(data){
            $('#rs').html('<pre>' + data + '</pre>');
        });
    }
    
    function setcookie(name,obj_val){
    	$.cookie(name,$.toJSON(obj_val),{path:'/',expires:<?=$expire_day?>});
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
        $("#code").val(type + left + func + left2 + code + right2 + right + ';');
        $("#form1").submit();
    }
    
    // 清空/初始化html
    function init(){
        var code = $("#code"),
            html = code.val();
        if (html == '') {
            code.val(str);
            setCursor(code[0], str.indexOf('/body') - 12);
            // code.html('<pre>'+str+'</pre>');
        }
        else {
            code.val('').focus();
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
    
    // 定位光标位置
    function setCursor(obj, position){
        if ($.browser.msie) {
            var range = obj.createTextRange();
            range.move("character", position);
            range.select();
        }
        else {
        	obj.setSelectionRange(position, position);
        	obj.focus();
        }
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
