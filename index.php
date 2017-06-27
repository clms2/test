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
    <!-- 按钮end -->
</form>
<br />
<!-- 结果框 -->
<iframe name="i" src="" id='i' class="t"></iframe>
<div class="t" id='rs'></div>
<script type="text/javascript">
    var c = function(s){ console.log(s)};
	$("#code").focus();
	
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
        code = $("#code"),
        codeInfo = {'width' : code.width(), 'height' : code.height(), 'lineH' : parseFloat(code.css('line-height')), 'fontsize' : code.css('font-size'), 'offset': code.offset()};
    // 阻止firefox ctrl+r刷新页面
    $(document).keypress(function (e) {
        var k = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
        if (e.ctrlKey) {
            if (k == 114) {
                e.preventDefault();
            }
        }
    });

    /**
     * 获取光标距离输入框左侧和顶部的距离，用于动态定位代码提示的div
     * @param  {object} domObj dom 对象
     * @param  {int} pos    光标位置
     * @return {object}     {top: 33, left:5}
     */
    function getCursorAbsolutePos(domObj, pos){
        var _top = 0,
            _left = 0;

        // 选中当前光标到起点的文本
        selectText(domObj, 0, pos);
        // 获得选中文本
        txt = getSelectText(domObj);
        // 获取选中文本宽高
        var sizeInfo = textSize(codeInfo.fontsize, txt),
            rowNum = 0,
            thisRow = '';
        // 统计光标到起点的字符串行数，默认一个\n一行，但如果该行width超过输入框width，那么就以多行计算
        for(var j = 0, arr = txt.split('\n'),len = arr.length; j < len; j++){
            rowNum += Math.ceil(textSize(codeInfo.fontsize, arr[j]).width/codeInfo.width) || 1;
            // 保存下最后一行 也就是光标所在行
            if(j == len - 1){
                thisRow = arr[j];
            }
        }
        // 那么距离顶部的距离就是:
        _top = rowNum * codeInfo.lineH;
        // 距左侧距离就是：
        _left = textSize(codeInfo.fontsize, thisRow).width;

        return {top: _top + codeInfo.offset.top, left: _left + codeInfo.offset.left};
    }

    /**
     * 模糊匹配，需要数组phpfunc
     * @param  {string} wd 输入的字
     * @return {array}   
     */
    function search_txt(wd){
        var ret = [], count = phpfunc_remind_count || 10;
        if(typeof phpfunc == 'undefined') return ret;
        var reg = '^'+wd.split('').join('.*')+'.*',
            func,
            i = 0;
        // todo.. 可优化：
        // 不用遍历全部 似乎 按首字母分类 在特定首字母类别下进行检索效率会高
        for(func of phpfunc){
            if(!!func.match(new RegExp(reg))){
              ret.push(func);
              if(++i > count) break;
            }
        }

        return ret;
    }

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
                $(this).insert("    ");
                e.preventDefault();
            }
        }).keyup(function(e){
            // 实现输入</自动补完标签功能，以及类似编辑器的函数提示功能
            // todo.. 加个延迟时间，防止输入过快程序会未捕获到输入的文本
            var pos,// 光标位置
                divpos, // 函数提示div的位置
                curwd,// 本次输入内容
                txt,// 选中文本
                temp,// 临时变量
                list,// 存模糊匹配结果数组
                cursorSetted = false// 是否已setCursor过
            // 获得当前光标位置
            pos = getCursorPos(this);
            if(pos == lastpos || pos === 0){
                // c('pos == lastpos || pos === 0 return')
                return;
            }
            lastpos = pos;

            // 方向键 左右不处理
            var k = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
            if(k == 37 || k == 39){
                // c('keycode == 37 || 39 return')
                return;
            }

            // 获得光标位置往前一位的字母，即刚输入的字母
            // todo.. 可优化在keyup的时候赋值给lastwd
            selectText(this, pos-1, pos);
            curwd = getSelectText(this);
            // 代码提示功能：
            // 通过判断出输入的是否为函数字母开头进行提示。
            do{
                if(env == 'html') break;
                // 匹配到非字母输入 也就是非函数输入，可能为空格、引号、$等
                if(/[^a-zA-Z]/.test(curwd)){
                    // 如果还没开始忽略 那么就是第一次匹配
                    if(!ignore_start) {
                        // c('ignore_start:true')
                        ignore_start = true;
                        // 开始忽略需要做:
                        func_start = false;
                        ignore_end = false;
                        ignore_real_end = false;
                    }else{
                        // 如果已经开始忽略 那么该匹配就是第二次匹配到非函数输入了
                        // c('ignore_end:true')
                        ignore_end = true;
                        // 忽略结束需要做:
                        // // 问题?.,,
                        ignore_start = false;
                    }
                    // 如果已经开始匹配函数 那么就是匹配结束
                    if(func_start){
                        // c('func_end:true')
                        func_end = true;
                        // 函数结束需要做：
                        ignore_start = false;
                        ignore_end = true;
                        ignore_real_end = true;
                        func_start = false;
                    }
                    // 非函数输入都是清空words
                    words = '';
                }else if(ignore_end){
                    ignore_real_end = true;
                }
                if(ignore_real_end && !func_start){
                    // 开始拼接函数标志
                    // c('func_start:true')
                    func_start = true;
                    ignore_start = false;
                }
                // 函数提示功能 匹配开始
                if(func_start && !func_end){
                    // c('concat..')
                    words += curwd;
                }
                if(words == '') break;
                list = search_txt(words);
                temp = list.length;
                if(temp == 0) break;
                divpos = getCursorAbsolutePos(this, pos);
                // func_list.css({left: divpos.left, top: divpos.top, display: 'block'}).children('li').hide().filter(':lt('+temp+')').show().each(function(i){
                //     $(this).text(list[i]);
                // });
            }while(false);
            // c('curwd:'+curwd+'|words:'+words+'|ignore_start:'+ignore_start+'|ignore_end:'+ignore_end+'|func_start:'+func_start+'|func_end:'+func_end);
            
            // 自动补完标签功能
            do{
                if(env == 'php') break;
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
        $("#rs").html("<img src='?qrcode=1&code="+encodeURIComponent($("#code").val())+"'>");
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
        $.post('index.php', {
            code: $('#code').val()
        }, function(data){
            $('#rs').html('<pre>' + data + '</pre>');
        });
    }
    
    function setcookie(name,obj_val){
    	$.cookie(name,$.toJSON(obj_val),{path:'/',expires:<?php echo $expire_day?>});
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
        $("#code").val(tempstr);
        $("#form1").submit();
    }
    
    // 清空/初始化html
    function init(){
        var code = $("#code"),
            html = code.val();
        if (html == '') {
            env = 'html';
            code.val(str);
            setCursor(code[0], str.indexOf('/body') - 12);
            // code.html('<pre>'+str+'</pre>');
        }
        else {
            env = 'php';
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
