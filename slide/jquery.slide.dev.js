/**
 * 切换和无缝滚插件
 * @author qq:648003174
 */
$.fn.extend({
	/**
	 * 上左切换
	 * 
	 * @param object ul $(this) 需滚动元素的直接上级ul, ul>li
	 * @param number auto 自动
	 * @param number interval 自动切换时间
	 * @param number speed 切换速度
	 * @param number stepLen 切换数量
	 * @param number showNum 展示数量
	 * @param enum   pos left/top 运动方向
	 * @param object prev 上一个
	 * @param object next 下一个
	 * @param object control 控制的ul,$("ul"),可传递多个控制器,直接父级
	 * @param enum   ctltrigger click/hover 控制触发事件
	 * @param string easing 默认swing
	 * @param function beforeSlide 切换之前执行的函数,第一个参数为方向(prev/next)，第二个参数为移动个数，第三个参数为ul
	 * @param function afterSlide 切换之后执行的函数,参数为ul
	 * @param function click li点击事件
	 */
	slide:function(param){
		var config = $.extend({
				auto:1,
				interval:3000,
				speed:600,
				stepLen:1,
				showNum:3,
				pos:'left',
				prev:null,
				next:null,
				control:null,
				ctltrigger:'click',
				easing:'swing',
				afterSlide:null,
				beforeSlide:null,
				click:null
			}, param),ul = $(this), li = ul.children(), num = li.length-1, wh, w = li.outerWidth(1), h = li.outerHeight(1),stop=0,ulcss = {},t,cur,wrap_div = ul.parent('.wrap_div'),max,shift,move,aobj = {};

		if(wrap_div.length == 0){
			var warp_css;
			switch(config['pos']){
				case 'left':
					max = config['showNum']*w;
					warp_css = 'width:'+max +'px;height:'+h+'px';
					ulcss['width'] = max+num*w+'px';
					ulcss['top'] = 0;
					li.css('float','left');
					wh = w;
				break;
				case 'top':
					max = config['showNum']*h;
					warp_css = 'height:'+max+'px;width:'+w+'px';
					ulcss['height'] = max+num*h+'px';
					ulcss['left'] = 0;
					wh = h;
				break;
				default:
					alert('unsupport position:'+config['pos']);
					return;
			}
			ul.wrap('<div class="wrap_div" style="position:relative;overflow:hidden;'+warp_css+'"></div>')
			ulcss['position'] = 'absolute';
			ulcss['overflow'] = 'hidden';
			ul.css(ulcss)
		}
		if(config['control'] != null){
			li.each(function(){
				$(this).attr('index',$(this).index());
			});
		}

		switch(config['pos']){
			case 'top':
			case 'left':
				shift = function(p, len){
					if(typeof p == 'undefined') var p = 'next';
					if(typeof len == 'undefined') var len = config['stepLen'];
					cur = wh*len;
					ul.stop(true, true);
					aobj[config['pos']] = -cur + 'px';
					if(config['control'] != null){
						var i = p == 'next' ? len : (num-len+1);
						i = ul.children().eq(i).attr('index');
						config['control'].each(function(){
							$(this).children().eq(i).addClass('in').siblings().removeClass('in');
						});
					}
					if(config['beforeSlide'] != null) config['beforeSlide'].call(null, p, len, ul);
					if(p == 'next'){
						ul.append(ul.children().filter(':lt('+len+')').clone(true,true));
						ul.animate(aobj, config['speed'], config['easing'], function(){
							ul.children().filter(':lt('+len+')').remove();
							aobj[config['pos']] = 0;
							ul.css(aobj);
							if(config['afterSlide'] != null) config['afterSlide'].call(null, $(this));
						})
					}else if(p == 'prev'){
						ul.prepend(ul.children().filter(':gt('+(num-len)+')').clone(true,true)).css(aobj);
						aobj[config['pos']] = 0;
						ul.animate(aobj, config['speed'], config['easing'], function(){
							ul.children().filter(':gt('+num+')').remove();
							if(config['afterSlide'] != null) config['afterSlide'].call(null, $(this));
						})
					}
				}
				move = function(){
					if(!config['auto']) return;
					t = setInterval(shift, config['interval']);
				};
				move();
			break;
			default:
				return;
		}
		var obj = ul;
		if(config['next'] != null){
			obj = obj.add(config['next']);
			config['next'].click(function() {
				clearInterval(t);
				shift('next',1);
				move();
			});
		}
		if(config['prev'] != null){
			obj = obj.add(config['prev']);
			config['prev'].click(function() {
				clearInterval(t);
				shift('prev',1);
				move();
			});
		}
		obj.hover(function() {
			clearInterval(t);
		}, function() {
			clearInterval(t);
			move();
		});
		if(config['click'] != null){
			ul.children().click(function() {
				config['click'].call(null, $(this));
			});
		}
		if(config['control'] != null){
			if(config['ctltrigger'] == 'hover') config['ctltrigger'] = 'mouseover';
			config['control'].each(function(){
				var chd = $(this).children();
				chd.bind(config['ctltrigger'],function(){
					clearInterval(t);
					var tari = $(this).index(),curi = ul.children().filter(':first').attr('index');
					if(tari == curi) {
						move();
						return;
					}
					var p = tari < curi ? 'prev' : 'next';
					shift(p, Math.abs(tari-curi));
					$(this).addClass('in').siblings().removeClass('in');
				});
				chd.mouseout(function() {
					clearInterval(t);
					move();
				});
			});
			
		}
	},
	/**
	 * 上下左无缝滚动
	 * 
	 * @param object ul $(this) 需滚动li的直接父级
	 * @param number config['showNum']
	 * @param enum config['pos'] left/top/bottom
	 * @param number config['speed']
	 * @param int step 点击prev next加速移动的li数量  pos为top或bottom不支持prev next step 暂时不折腾了 要用到的时候再说~
	 * @param jquery object prev 加速往前按钮
	 * @param jquery object next 加速往后按钮
	 * @param function click li点击事件
	 */
	nslide:function(param){
		var config = $.extend({
			speed:9000,
			speedup:700,
			pos:'left',
			showNum:2,
			step:1,
			click:null,
			prev:null,
			next:null
		},param);
		var ul = $(this), li = ul.children(), len = li.length;
		var wh, max, cur, init, licss = {}, ulcss = {}, wrap_css, move, uwh,w = li.outerWidth(1),h=li.outerHeight(1),hasBind=ul.parent('.nslide_wrap').length == 1,t,aobj={},left_time,fn;

		if(!hasBind){
			switch(config['pos']){
				case 'left':
				// case 'right':
					wh = w;
					uwh = 'width';
					licss['float'] = 'left';
					wrap_css = 'width:'+config['showNum']*wh + 'px;height:'+h+'px;';
				break;
				case 'top':
				case 'bottom':
					wh = h;
					uwh = 'height';
					wrap_css = 'height:'+wh*config['showNum']+'px;width:'+w+'px';
				break;
				default:
					alert('unsupport position:'+config['pos']);
					return;
			}
			max = wh * len;
			ulcss['position'] = 'relative';
			ulcss[uwh] = max+wh*config['showNum']+'px';
			li.css(licss);
			ul.css(ulcss);
			ul.wrap('<div class="nslide_wrap" style="position:relative;overflow:hidden;'+wrap_css+'"></div>');
		}else{
			wh = (config['pos'] == 'left' || config['pos'] == 'right') ? w : h;
			max = (len - config['showNum']) * wh;
		}


		// /
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
		// *
		function accMul(arg1,arg2){ 
			var m=0,s1=arg1.toString(),s2=arg2.toString(); 
			try{m+=s1.split(".")[1].length}catch(e){} 
			try{m+=s2.split(".")[1].length}catch(e){} 
			return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
		}
		Number.prototype.mul = function (arg){
			return accMul(arg, this); 
		}
		var time_per_px = config['speed'].div(max);//移动1px需要的时间
		if(config['prev'] || config['next']){
			// var time_step = time_per_px.mul(wh.mul(config['step']));//点击next需要加速的时间
			var speedup_distance = wh*config['step'];//加速移动的距离
				
		}

		switch(config['pos']){
			case 'bottom':
			case 'right':
				if(!hasBind) ul.prepend(ul.children().filter(':gt('+(len-config['showNum']-1)+')').clone());
				init = (config['showNum']+1)*wh;
				cur = ul.css(config['pos']) != '0px' ? ul.css(config['pos']).replace('px','') : init;
				if(cur == 'auto') cur = 0;
				move = function(){
					t = setInterval(function(){
						eval('ul.css("'+config['pos']+'","'+cur+'px'+'")');
						if(--cur < 0) cur = init;
					}, config['speed']);
				}
			break;
			case 'top':
			case 'left':
				if(!hasBind) ul.append(ul.children().filter(':lt('+config['showNum']+')').clone());
				
				move = function(p){
					//获取初始位置
					init = ul.css(config['pos']);
					init = init == 'auto' ? 0 : Math.abs(parseInt(init));
					//计算从初始位置移动一个周期结束剩下所需的时间,保持匀速
					left_distance = max-init;//到一周期结束剩下的距离
					if(init != 0){
						left_time = Math.round(left_distance.mul(time_per_px));//剩余时间
					}else{
						left_time = config['speed'];
					}
					if(typeof p != 'undefined'){
						if(p == 'next'){
							//如果无缝滚的时候点击next时正好当前是最后一个的一半，那么先要把最后一个的一半滚完，再从头开始滚另一半
							//正常就不用
							if(left_distance >= speedup_distance){
								ul.animate({left:'-='+speedup_distance+'px'},config['speedup'],'linear',function(){move()});
							}else{
								//计算移动第一半的时候
								var time_left = Math.round(left_distance.div(speedup_distance).mul(config['speedup']));
								ul.animate({left:'-='+left_distance+'px'},time_left,'linear',function(){
									$(this).css(config['pos'],0);
									//第二半
									ul.animate({left:'-='+(speedup_distance-left_distance)+'px'},config['speedup']-time_left,'linear',function(){move()});
								});
							}
						}else{
							//判断初始位置是否够一个步长的滚动距离
							if(init >= speedup_distance){
								ul.animate({left:'+='+speedup_distance+'px'},config['speedup'],'linear',function(){move()});
							}else{
								//计算移动第一半的时候
								var time_left = Math.round(config['speedup'].div(speedup_distance).mul(init));
								ul.animate({left:'+='+init+'px'},time_left,'linear',function(){
									$(this).css(config['pos'],-max);
									//第二半
									ul.animate({left:'+='+(speedup_distance-init)+'px'},config['speedup']-time_left,'linear',function(){move()});
								});
							}
						}
						return;
					}
					aobj[config['pos']] = '-=' + left_distance + 'px';
					ul.animate(aobj, left_time, 'linear', function(){
						$(this).css(config['pos'], 0);
						aobj[config['pos']] = -max;
					});
					fn = function(){
						ul.stop(true,true).animate(aobj, config['speed'], 'linear', function(){
							$(this).css(config['pos'], 0);
						});
						t = setTimeout(fn, config['speed']);
					};
					t = setTimeout(fn, left_time);
				}
			break;
		}
		if(config['pos'] != 'top' && config['pos'] != 'bottom'){
			if(config['prev'] != null){
				config['prev'].click(function(){
					clearInterval(t);
					ul.stop(true);
					move('prev');
				})
			}
			if(config['next'] != null){
				config['next'].click(function(){
					clearInterval(t);
					ul.stop(true);
					move('next');
				})
			}
		}

		ul.children().hover(function(){
			clearInterval(t);
			ul.stop(true);
		},function(){
			clearInterval(t);
			move();
		})
		if(config['click'] != null){
			ul.children().click(function() {
				config['click'].call(null, $(this));
			});
		}
		move();
	},
	/**
	 * 分割图片切换效果
	 * @param  {[type]} param [description]
	 * @return {[type]}       [description]
	 */
	sliceChange:function(param){
		var config = $.extend({
			sliceNum:15,
			speed:40,
			interval:4000
		}, param), li = $(this), len = li.length - 1, w = li.width(), h = li.height(), slicew = Math.round(w/config.sliceNum), sliceh = Math.round(h/config.sliceNum), last = i = 0, tempdiv = '';

		li.css({width:w,height:h});

		//添加div
		while(++i <= config.sliceNum){
			tempdiv += '<div></div>';
		}
		tempdiv = $(tempdiv).appendTo(li.parent());

		//效果类
		var effect = function(src, toshow, last_li){
			if(typeof src != 'undefined'){
				this.toshow = toshow;
				this.last_li = last_li;
				tempdiv.css({'background-image':'url('+src+')','background-repeat':'no-repeat'});
			}

			// 风琴效果
			this.eft0 = function(isFromLeft){
				var _this = this, i = -1, templeft, temph;
				//初始化图片背景位置
				while(++i < config.sliceNum){
					// 从左往右/从右往左
					templeft = typeof isFromLeft != 'undefined' ? i*slicew : (config.sliceNum-i-1)*slicew;
					temph = (config.sliceNum-i)*sliceh;
					tempdiv.eq(i).css({width:slicew,height:temph,left:templeft,top:0,'background-position':"-"+templeft+"px 0",opacity:0});
				}
				i = 0;
				var t1 = setInterval(function(){
					tempdiv.eq(i).animate({height:h,opacity:1});
					// 动画结束
					if(++i > (config.sliceNum-1)) {
						clearInterval(t1);
						tempdiv.eq(--i).css('opacity',1);
					}
				}, config.speed);
				//检测最后一个div是否已经animate完毕....
				var d = new Date(), t2 = setInterval(function(){
					if(tempdiv.eq(config.sliceNum-1).height() == h || ((new Date() - d) > config.interval)){
						clearInterval(t2);
						_this.last_li.removeClass('in');
						_this.toshow.addClass('in')
					}
				}, 400);
			}
			this.eft1 = function(){
				this.eft0(true);
			}
			// 窗户效果
			this.eft2 = function(isFromRight){
				var _this = this, i = -1, bgleft,left, tempw, opacity;
				//初始化图片背景位置
				while(++i < config.sliceNum){
					bgleft = left = typeof isFromRight != 'undefined' ? (config.sliceNum-i-1)*slicew : i*slicew;
					//需要运动的div
					if(i%2 == 0){
						tempw = 0;
						opacity = 1;
						if(typeof isFromRight != 'undefined') left += slicew;//从右往左的话animate width不行，得改变left，所以初始化的时候是和不运动的div重叠的
					}else{
						tempw = slicew;
						opacity = 0;
					}

					tempdiv.eq(i).css({width:tempw,height:h,left:left,top:0,'background-position':"-"+bgleft+"px 0",opacity:opacity});
				}
				i = -1;
				var t1 = setInterval(function(){
					//非运动的div
					if(++i%2 != 0) {
						tempdiv.eq(i).animate({'opacity': 1})
						return;
					}
					//结束动画
					if(i > (config.sliceNum-1)) {
						clearInterval(t1);
						return;
					}
					//从左往右
					if(typeof isFromRight == 'undefined') tempdiv.eq(i).animate({width:slicew},function(){$(this).css('opacity', 1);});
					else tempdiv.eq(i).animate({left:'-='+slicew+'px',width:slicew},function(){$(this).css('opacity', 1);});
				}, config.speed);

				//检测最后一个div是否已经animate完毕....
				var d = new Date(), t2 = setInterval(function(){
					if(tempdiv.eq(config.sliceNum-1).width() == slicew || ((new Date() - d) > config.interval)){
						clearInterval(t2);
						_this.last_li.removeClass('in');
						_this.toshow.addClass('in')
					}
				}, 400);
			}
			this.eft3 = function(){
				this.eft2(true);
			}
			this.eft4 = function(){
				tempdiv.hide();
				this.last_li.fadeOut(800,function(){
					$(this).removeClass('in').css('display','');
				})
				this.toshow.fadeIn(900,function(){
					$(this).addClass('in').css('display','');
					tempdiv.show();
				})
			}
		}, getFuncNum = function(obj){
			var count = 0, k;
			for(k in obj) if(typeof obj[k] == 'function') count++;
				console.log(obj[k]);
			return count;
		}, rand = function(a, b){
			return Math.round(Math.random()*(b-a) + a);
		};
		var max = getFuncNum(new effect()) - 1;

		var change = function(){
			var last_li = li.eq(last);
			if(++last > len) last = 0;
			var toshow = li.eq(last);

			var src = toshow.find('img').attr('src');

			eval('new effect(src,toshow,last_li).eft'+rand(0,max)+'()');
			//eval('new effect(src,toshow,last_li).eft'+rand(0,max)+'()');
			// return;
			setTimeout(change, config.interval)
		}
		change();
	}
})