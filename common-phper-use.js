// 后台开发者使用到的公共js

function dd(){
    if(typeof console != 'object') return;
    for(var i = 0;i < arguments.length;i++){
        console.log(arguments[i]);
    }
}

function support_localstorage(){
    try { 
      return 'localStorage' in window && window['localStorage'] !== null; 
    } catch (e) { 
      return false; 
    } 
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
    var d = days || 365;
    date.setDate(date.getDate() + d);
    document.cookie = name + "=" + escape(value) + "; expires=" + date.toGMTString();
}

// 支持h5就用h5存
function client_put(name, value, days){
    if(support_localstorage()){
        localStorage.setItem(name, value);
    }else{
        setCookie(name, value, days);
    }
}

function client_get(name){
    if(support_localstorage()){
        return localStorage.getItem(name);
    }else{
        return getCookie(name);
    }
}

//加法函数，用来得到精确的加法结果 
//说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。 
//调用：accAdd(arg1,arg2) 
//返回值：arg1加上arg2的精确结果
function accAdd(arg1, arg2) {
    var r1, r2, m;
    try {
        r1 = arg1.toString().split(".")[1].length
    } catch (e) {
        r1 = 0
    };
    try {
        r2 = arg2.toString().split(".")[1].length
    } catch (e) {
        r2 = 0
    };
    m = Math.pow(10, Math.max(r1, r2))
    return (arg1 * m + arg2 * m) / m;
}

//减法函数，用来得到精确的减法结果 
//说明：javascript的减法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的减法结果。 
//调用：accSubtr(arg1,arg2) 
//返回值：arg1减去arg2的精确结果 
function accSubtr(arg1, arg2) {
    var r1, r2, m, n;
    try {
        r1 = arg1.toString().split(".")[1].length
    } catch (e) {
        r1 = 0
    }
    try {
        r2 = arg2.toString().split(".")[1].length
    } catch (e) {
        r2 = 0
    }
    m = Math.pow(10, Math.max(r1, r2));
    //动态控制精度长度
    n = (r1 >= r2) ? r1 : r2;
    return ((arg1 * m - arg2 * m) / m).toFixed(n);
}


//乘法函数，用来得到精确的乘法结果 
//说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。 
//调用：accMul(arg1,arg2) 
//返回值：arg1乘以arg2的精确结果 
function accMul(arg1, arg2) {
    var m = 0,
        s1 = arg1.toString(),
        s2 = arg2.toString();
    try {
        m += s1.split(".")[1].length
    } catch (e) {}
    try {
        m += s2.split(".")[1].length
    } catch (e) {}
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m)
}

//除法函数，用来得到精确的除法结果 
//说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。 
//调用：accDiv(arg1,arg2) 
//返回值：arg1除以arg2的精确结果 
function accDiv(arg1, arg2) {
    var t1 = 0,
        t2 = 0,
        r1, r2;
    try {
        t1 = arg1.toString().split(".")[1].length
    } catch (e) {}
    try {
        t2 = arg2.toString().split(".")[1].length
    } catch (e) {}
    with(Math) {
        r1 = Number(arg1.toString().replace(".", ""))
        r2 = Number(arg2.toString().replace(".", ""))
        return (r1 / r2) * pow(10, t2 - t1);
    }
}

/**
 * php strtr 2个参数版
 * @param  {string} str   
 * @param  {object} assoc 
 * @return {string}       
 */
function strtr(str, assoc) {
    for (var k in assoc) {
        str = str.replace(new RegExp(k, 'g'), assoc[k]);
    }

    return str;
}

$.extend({
    /**
     * 消息弹窗
     * 使用方式：
     * 1. $.pop('提示')
     * 2. $.pop({msg: '提示', btn_num: 2})
     * 3. $.pop({msg: '提示', btn_num: 2, callback_confirm: function(){ //点击确认回调}}})
     * 
     * @param {object/string}   param  可传参数数组或者直接传提示信息字符串
     * @param {string}   param.msg 提示内容
     * @param {int}      param.btn_num 按钮数量 默认1只显示确认按钮  为2显示确认跟取消按钮 
     * @param {int}      param.hold_time 默认0:不消失 1500:1.5秒后消失
     * @param {string}   param.tpl_msg 弹出层的消息模板
     * @param {string}   param.tpl_mask 弹出层的遮罩模板
     * @param {string}   param.txt_confirm 确认按钮文字描述
     * @param {string}   param.txt_cancel 取消按钮文字描述
     * @param {function} param.callback_confirm 点击确认后执行的回调
     * @param {function} param.callback_cancel 点击取消后执行的回调
     * 
     * @param {string} param.box_id 和 @param {string} param.mask_id 
     * 如果要使用dom中已有的模板 那么模板可参考默认模板 需包含data-btn-num, data-callback, data-msg-box属性
     * data-btn-num: 根据需显示的按钮数量参数(param.btn_num)显示对应的按钮
     * data-callback: 目前包含confirm和cancel两个值，点击后执行对应参数配置的回调(param.callback_confirm和param.callback_cancel)
     * data-msg-box: param.msg值显示在哪个标签内
     * 
     */
    /*
    // 可使用需改的样式    
    #box{
        width:5.6rem;
        background:#fff;
        padding:0.4rem;
        position:fixed;
        left:0.4rem;
        top:3rem;
        z-index:4;
        display:none;
    }
    #box.active{
        display:block;
    }
    #box p{
        font-size:0.3rem;
        line-height:0.4rem;
        text-align:center;
        margin-bottom:0.3rem;
    }
    #box .control a{
        font-size:0.3rem;
        letter-spacing:1px;	
        width:2.3rem;
        line-height:0.7rem;
        color:#48b7f2;
        background:#fff;
        border:0.02rem solid #48b7f2;	
    }
    #box .control a.ok{
        color:#fff;
        background:#48b7f2;
        margin-left:0.2rem;
    }
    */
    pop: function(param){
        if(typeof param == 'string'){
            var param = {msg: param};
        }
        var cfg = $.extend({
            msg: '',
            btn_num: 1,
            hold_time: 0,
            tpl_msg: '<div id="box" class="radius">\
                        <p data-msg-box="1"></p>\
                        <div class="control clearfix">\
                            <a data-btn-num="1" data-callback="confirm" href="javascript:;" class="ok radius" style="margin: 0 auto;display: block">确认</a>\
                            <a data-btn-num="2" data-callback="cancel" href="javascript:;" class="cancel fl radius">取消</a>\
                            <a data-btn-num="2" data-callback="confirm" href="javascript:;" class="ok fr radius">确认</a>\
                        </div>\
                    </div>',
            tpl_mask: '<div id="mask"></div>',
            txt_confirm: '确认',
            txt_cancel: '取消',
            callback_confirm: null,
            callback_cancel: null,
            box_id: '#box',
            mask_id: '#mask',
        }, param);

        var obj_box = $(cfg.box_id),
            obj_mask = $(cfg.mask_id);

        // dom中没有则附加进去
        if(obj_box.length == 0){
            obj_box = $(cfg.tpl_msg).appendTo($("body"));
        }
        if(obj_mask.length == 0){
            obj_mask = $(cfg.tpl_mask).appendTo($("body"));
        }
        // 显示对应的按钮数
        var temp_show = obj_box.find('[data-btn-num=' + cfg.btn_num + ']');
        temp_show.parent().children().not(temp_show).hide();
        // 显示对应的按钮文字描述
        obj_box.find('[data-callback="confirm"]').text(cfg.txt_confirm).end().find('[data-callback="cancel"]').text(cfg.txt_cancel)

        function hide_tip(){
            // 直接remove了 可能一个页面会有多个弹框 多个回调
            obj_box.add(obj_mask).remove();
        }

        // 显示弹窗
        obj_box.find('[data-msg-box]').html(cfg.msg).end().add(obj_mask).show();

        obj_box.find('[data-callback=confirm][data-btn-num=' + cfg.btn_num + ']').click(function() {
            if(typeof cfg.callback_confirm == 'function'){
                cfg.callback_confirm.call(null);
            }
            
            hide_tip();
        });

        obj_box.find('[data-callback=cancel][data-btn-num=' + cfg.btn_num + ']').click(function() {
            if(typeof cfg.callback_cancel == 'function'){
                cfg.callback_cancel.call(null);
            }
            hide_tip();
        });

        if(cfg.hold_time > 0){
            setTimeout(hide_tip, cfg.hold_time);
        }
    }
})

$.fn.extend({
    /**
     * tab切换
     * @param  {object} $(this) 需触发tab切换的集合
     * @param  {object} param 
     * @param  {object} param.tab_con 切换tab后对应的内容集合
     * @param  {string} param.tab_cls 切换tab后给当前tab添加的样式
     * @param  {event}  param.callback 切换后执行的回调函数 参数1:当前tab,参数2:当前内容,参数3:当前索引
     * @param  {string} param.trigger 默认点击切换
     */
    tab: function(param){
        var cfg = $.extend({
            tab_con: null,
            tab_cls: 'active',
            callback: null,
            trigger: 'click'
        }, param), tab_ctl = $(this);

        tab_ctl.on(cfg.trigger, function(){
            var index = $(this).index();

            $(this).addClass(cfg.tab_cls).siblings().removeClass(cfg.tab_cls);

            var tab_con = null;// 用于回调
            if(cfg.tab_con != null){
                cfg.tab_con.hide().eq(index).show();
                tab_con = cfg.tab_con.eq(index);
            }

            if(typeof cfg.callback == 'function'){
                cfg.callback.call(null, $(this), tab_con, index);
            }
        });
    },
    
});

