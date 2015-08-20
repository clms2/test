function c(v){console.log(v)}

function changeTwoDecimal(x){  
	var f_x = parseFloat(x);  
	if (isNaN(f_x)) return 0;
	return Math.round(x*100)/100;
}
function accDiv(arg1,arg2){
	var t1=0,t2=0,r1,r2;
	try{t1=arg1.toString().split(".")[1].length}catch(e){} 
	try{t2=arg2.toString().split(".")[1].length}catch(e){}
	with(Math){
	r1=Number(arg1.toString().replace(".",""))
	r2=Number(arg2.toString().replace(".",""))
	return (r1/r2)*pow(10,t2-t1);
	}
}
Number.prototype.div = function (arg){ 
	return accDiv(this, arg); 
}

function showCoords(obj){
	$("#x").val(obj.x);
	$("#y").val(obj.y);
	$("#w").val(obj.w);
	$("#h").val(obj.h);
	return;
	if(parseInt(obj.w) > 0){
		//计算预览区域图片缩放的比例，通过计算显示区域的宽度(与高度)与剪裁的宽度(与高度)之比得到
		var w = $("#preview_box").width(),
			h = $("#preview_box").height(),
			src_w = $("#cutpic").width(),
			src_h = $("#cutpic").height();
		var rx = w.div(obj.w);
		var ry = h.div(obj.h);
		return;
		//通过比例值控制图片的样式与显示
		$("#crop_preview").css({
			width:Math.round(rx * src_w) + "px",	//预览图片宽度为计算比例值与原图片宽度的乘积
			height:Math.round(rx * src_w) + "px",	//预览图片高度为计算比例值与原图片高度的乘积
			marginLeft:"-" + Math.round(rx * obj.x) + "px",
			marginTop:"-" + Math.round(ry * obj.y) + "px"
		});
		
	}
}

//调整裁图区域css
function justpos(upimgw, src_w, src_h){
	$("#preview_box").css({
		left: parseFloat(upimgw)+20,
		width:src_w,
		height:src_h
	});
	//var totalw = upimgw+src_w+20;
	var totalw = src_w+20;
	$("#uppic").css({
		left: (document.body.clientWidth-totalw)/2,
		top: 50
	});
}

function _close(id){
	$("#cover,"+id).hide();
}

// rgb转16进制
function r2h(rgb){
	var rgb = rgb.replace(/rgb|\(|\)|\s/ig,'').split(','),
		hex = ((rgb[0] << 16) | (rgb[1] << 8) | rgb[2]).toString(16);

	if(hex.length < 6){
		var pad_len = 6 - hex.length,
			zero = '';
		while(pad_len-- > 0){
			zero += '0';
		}
		hex = zero + hex;
	}

	return hex;
}

function tab(ctrl, item, evt){
	var evt = evt || 'click';
	ctrl.bind(evt, function(){
		$(this).addClass('in').siblings().removeClass('in');
		item.eq($(this).index()).addClass('in').siblings().removeClass('in');
		showbg();
	})
}

function resize(o, top){
	var cH = cH || $(window).height(),
		scrolltop = document.documentElement.scrollTop || window.pageYOffset || document.body.scrollTop,
		coverH = window.document.body.offsetHeight ||  window.document.documentElement.offsetHeight,
		oH = o.height(),
		objtop = typeof top != 'undefined' ? top : (cH-oH)/2+scrolltop;
	if(coverH == 0) coverH = cH;
	if(coverH < $(document).height()) coverH = $(document).height();
	$("#cover").show().height(coverH);
	o.show().css('top',objtop);

	return {
		'scrolltop':scrolltop,
		'coverH':coverH
	}
}

/**
 * 替换所有o下面的img中带有data-src属性的图片
 * @param  object o jq对象
 */
function loadimg(o){
	o.find("img[data-src!='']").each(function(){
		$(this).attr('src', $(this).attr('data-src'));
	});
}

/**
 * 排序 交换位置 _this需包含<div class="rank">30</div>和class为id的元素
 * @param  up/down op    往上/往下
 * @param  jqobj _this 点击对象
 * @return string 
 */
function dealrank(op, _this){
	var _sibling = op == 'up' ? _this.prev() : _this.next();
	if(_sibling.length == 0 || _sibling.hasClass('hide')){
		c('no sibling')
		return false;
	}
	var	sib_rank  = _sibling.find('.rank').text(),
		sib_id = _sibling.find('.id').text(),
		this_id = _this.find('.id').text(),
		this_rank = _this.find('.rank').text(),
		idx = $("#module_user li").index(_this),
		params;
		
	if(!sib_id || !this_id){
		c('no id')
		return false;
	}

	params = this_id+'='+sib_rank+'&'+sib_id+'='+this_rank+'&operate='+op;

	_sibling.find('.rank').text(this_rank);
	_this.find('.rank').text(sib_rank);

	if(op == 'up') _sibling.before(_this.clone(true, true));
	else _sibling.after(_this.clone(true, true))
	_this.remove();

	var temp = $("#"+this_id).clone(true, true);
	$("#"+this_id).remove();
	if(op == 'up') $("#"+sib_id).before(temp);
	else $("#"+sib_id).after(temp);

	return params;
}
