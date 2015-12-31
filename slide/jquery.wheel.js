// 兼容火狐和谷歌的鼠标滚轮插件，简单版，未进行生产应用~
$.fn.extend({
	wheel: function(param) {
		var cfg = $.extend({
			speed: 1000,
			easing: 'swing',
			prev: null, // 向上滚按钮
			next: null // 向下滚按钮
		}, param);

		var animating = false,
			eachheight = $(this).eq(0).outerHeight(true),
			wheel = function(event, isup) {
				if (animating) return event.preventDefault();
				animating = true;

				// 是否是向上滚，谷歌向上滚wheelDelta为正，火狐向上滚detail为负
				var isup = isup || (event.wheelDelta ? event.wheelDelta > 0 : event.detail < 0);

				// 当前距顶部距离
				var sctop = $("body,html").scrollTop();
				// 滚动后距离
				if (isup) sctop -= eachheight;
				else sctop += eachheight;
				$("body,html").animate({"scrollTop": sctop}, cfg.speed, cfg.easing, function() {
					animating = false;
				});
				return event.preventDefault();
			};

		// chrome
		document.body.onmousewheel = function(event) {
			wheel(event);
		}
		// firefox
		document.body.addEventListener("DOMMouseScroll", function(event) {
			wheel(event);
		});
		// 点击向上滚
		if(cfg.prev !== null){
			cfg.prev.click(function(event) {
				wheel(event, true);
			});
		}
		// 点击向下滚
		if(cfg.next !== null){
			cfg.next.click(function(event) {
				wheel(event, false);
			});
		}
	}
});
// section为每个滚动的div块, 点击div.jt向下滚
$(".section").wheel({next : $(".jt")});
