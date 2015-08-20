$.fn.extend({
	/**
	 * 下拉框筛选,支持模糊搜索 wd=>word
	 * @param $(this)  		   jquery object  触发下拉框的输入框的元素
	 * @param config['drop']   jquery object 下拉框里面的元素值$("ul li"),li需带有value属性,用于传递给隐藏域
	 * @param config['hide']   jquery object 隐藏域input[type=hidden],用于提交选择的值
	 * @param config['parent'] string	     下拉框的父级(从config['drop']往上搜索),用于隐藏显示,默认ul
	 * @param active		   string		 选中的className
	 * @bug qq:648003174
	 */
	droplist:function(param){
		var config = $.extend({
			drop:null,
			hide:null,
			parent:'ul',
			active:'in'
		}, param);
		var unicode = function(str){return escape(str).replace(/%/g,"\\").toLowerCase();},
			//搜索pinyin数组
			search_py = function(v, arr){for(var k in arr){if(arr[k].indexOf(v) != -1){return k}} return v},
			get_py = function(s){if(unicode(s) == s) return s;return s.split('').map(function(v){return search_py(v, pinyin)}).join('')},
			//搜索data数组===>核心
			search_data = function(v, arr){v='/^'+v.split('').join('.*')+'.*/';for(var k in arr){if(eval(v+".test(arr[k][1])")){return parseInt(k)}}return 0};

		if(typeof pinyin == 'undefined'){
			alert('未载入拼音数组!');
			return;
		}
		var data = {};//键是index 值=>array(提交的值,汉字对应的拼音)
		var input = $(this), text, index, keycode, index, lis, dropbox = config['drop'].closest(config['parent']);
		lis = config['drop'];
		function c(s){console.log(s)}
		config['drop'].map(function(i,v){ text = v.innerHTML;data[i] = [$(this).attr('value'),get_py(text)]});
		dropbox.show();//防止获取不到高度
		var h = lis.outerHeight();
		dropbox.hide();

		var sel = function(idx){
			config['hide'].val(data[idx][0]);
			input.val(data[idx][1]);
			dropbox.hide();
		}
		var _in = function(idx){
			lis.removeClass(config['active']).eq(idx).addClass(config['active']);
		};

		input.keyup(function(e) {
			text = get_py($(this).val());
			if(text == '') {index = undefined;dropbox.show();return;}
			//可以改一种搜索方式，如果没找到该a,那么就显示a后面的b
			index = search_data(text, data);
			dropbox.scrollTop(h*index);

			if(index != undefined){
				keycode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
				//上下 回车
				switch(keycode){
					case 38:
						index--;
					break;
					case 40:
						index++;
					break;
					case 13:
						sel(index);
						return;
					break;
				}
				_in(index);
			}
		}).click(function(){
			dropbox.show();
			if(index != undefined) _in(index);
		});
		//鼠标点选
		lis.click(function(){
			index = lis.index(this);
			sel(index);
		});

	}
})