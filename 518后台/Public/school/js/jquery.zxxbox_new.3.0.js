/*! 
* zxxbox.js 
* © 2010-2011 by zhangxinxu http://www.zhangxinxu.com/
* v1.0 2010-03-20
* v1.1 2010-04-03 #添加拖拽
* v1.2 2010-07-12 #修改浏览器高宽以及页面滚动高度获取
* v2.0 2010-08-01 #重写js，增加可读性，维护性
*                 #添加问答框确认的回调方法
*                 #修复浏览器大小变化时黑背景高度不变化的bug，且弹框一直居中显示
* v3.0 2010-09-05 #修改自定义提示的调用方法
				  #增加提示方法的回调函数接口
				  #增加弹窗打开和关闭的回调方法接口
				  #增加Ajax功能
				  #增加外框CSS3外阴影效果，美化UI
				  #弹框关闭以渐隐动画显示
* v3.1 2010-11-25 #更准确使用$.noop方法
* v3.2 2010-11-26 #修改些属性，使向下兼容1.3版本
* v3.3 2010-11-30 #阻止弹框重复关闭执行
* v3.4 2011-01-21 #修复错别字符，修复IE下弹框高度超过一屏拖拽会隐藏的bug
* v3.5 2011-03-03 #添加新的api参数protect
* v3.6 2011-04-06
* v3.7 2012-06-05 #修复ajax加载页面或html片段含JavaScript脚本时重复执行的bug
* v3.8 2012-06-12 #修复title记忆shut参数在ajax 加载时不起作用的问题
* v4.0 2012-06-13 #框架CSS实现大调整
*/
(function($) {
	//给页面装载CSS样式
	var LG = 'linear-gradient(top, #fafafa, #eee)', CSS = '<style type="text/css">' +
		'#zxxBlank{position:fixed;z-index:2000;left:0;top:0;width:100%;height:10;background:black;}' +
		'.wrap_out{padding:5px;background:#eee;box-shadow:0 0 6px rgba(0,0,0,.5);position:absolute;z-index:2000;left:-9999px;}' +
		'.wrap_in{background:#fafafa;border:1px solid #ccc;}' +
		'.wrap_bar{border-bottom:1px solid #ddd;background:#f0f0f0;background:-moz-'+ LG +';background:-o-'+ LG +';background:-webkit-'+ LG +';background:'+ LG +';}' +
		'.wrap_title{line-height:24px;padding-left:10px;margin:0;font-weight:normal;font-size:1em;}' +
		'.wrap_close{position:relative;}' +
		'.wrap_close a{width:20px;height:20px;text-align:center;margin-top:-22px;color:#34538b;font:bold 1em/20px Tahoma;text-decoration:none;cursor:pointer;position:absolute;right:6px;}' +
		'.wrap_close a:hover{text-decoration:none;color:#f30;}' +
		'.wrap_body{background:white;}' +
		'.wrap_remind{width:16em;padding:30px 40px;}' +
		'.wrap_remind p{margin:10px 0 0;}' +
		'.submit_btn, .cancel_btn{display:inline-block;padding:3px 12px 1.99px;line-height:16px;border:1px solid;cursor:pointer;overflow:visible;}' +
		'.submit_btn{background:#486aaa;border-color:#a0b3d6 #34538b #34538b #a0b3d6;color:#f3f3f3;}' +
		'.submit_btn:hover{text-decoration:none;color:#fff;}' +
		'.cancel_btn{background:#eee;border-color:#f0f0f0 #bbb #bbb #f0f0f0;color:#333;}' +
	'</style>';
	$("head").append(CSS);	  
	
	var WRAP = '<div id="zxxBlank" onselectstart="return false;"></div>' + 
		'<div class="wrap_out" id="wrapOut">' +
			'<div class="wrap_in" id="wrapIn">' +
				'<div id="wrapBar" class="wrap_bar" onselectstart="return false;">' +
					'<h4 id="wrapTitle" class="wrap_title"></h4>' +
					'<div class="wrap_close"><a href="javasctipt:" id="wrapClose" title="关闭"></a></div>' +	
				'</div>' +
				'<div class="wrap_body" id="wrapBody"></div>' +
			'</div>' +
		'</div>';
	
	$.fn.zxxbox = function(options) {	
		options = options || {};
		var s = $.extend({}, zxxboxDefault, options);
		return this.each(function() {		
			var node = this.nodeName.toLowerCase();
			if (node === "a" && s.ajaxTagA) {
				$(this).click(function() {
					var href = $.trim($(this).attr("href"));
					if (href && href.indexOf("javascript:") != 0) {
						if (href.indexOf('#') === 0) {
							$.zxxbox($(href), options);
						} else {
							//加载图片
							$.zxxbox.loading();
							var myImg = new Image(), element;
							myImg.onload = function() {
								var w = myImg.width, h = myImg.height;
								if (w > 0) {
									if (options.show_rotate) {
										var element = $('<div><img id ="img" src="'+ href +'" width="'+ w +'" height="'+ h +'" /><br/><input type="button" value="<-Rotate左" name="RotateR" id="RotateR" onclick="$(\'#img\').rotateLeft(90);"><input type="button" value="右Rotate->" name="RotateL" id="RotateL" onclick="$(\'#img\').rotateRight(90);"></div>');	
									} else {
										var element = $('<img id ="img" src="'+ href +'" width="'+ w +'" height="'+ h +'" />');	
									}
									options.protect = false;
									$.zxxbox(element, options);
								}
							};
							myImg.onerror = function() {
								//显示加载图片失败
								$.zxxbox.ajax(href, {}, options);
							};
							myImg.src = href;
						}
					}	
					return false;
				});
			} else {
				$.zxxbox($(this), options);	
			}
		});				
	};
	
	$.zxxbox = function(elements, options) {
		if (!elements) {
			return;	
		}

		var s = $.extend({}, zxxboxDefault, options || {});

		//弹框的显示
		var eleOut = $("#wrapOut"), eleBlank = $("#zxxBlank");
					
		if (eleOut.size()) {
			eleOut.show();
			eleBlank[s.bg? "show": "hide"]();
		} else {
			$("body").append(WRAP);	
		}
		
		if (typeof(elements) === "object") {
			elements.show();
		} else {
			elements = $(elements);
		}
		//一些元素对象
		$.o = {
			s: s,
			ele: elements,
			bg: eleBlank.size()? eleBlank: $("#zxxBlank"), 
			out: eleOut.size()? eleOut: $("#wrapOut"), 
			tit: $("#wrapTitle"),
			bar: $("#wrapBar"), 
			clo: $("#wrapClose"),
			bd: $("#wrapBody")
		};
		
		// 标题以及关闭内容
		$.o.tit.html(s.title);
		$.o.clo.html(s.shut);
		
		//装载元素
		$.o.bd.empty().append(elements);

		if ($.isFunction(s.onshow)) {
			s.onshow();
		}
		//尺寸
		$.zxxbox.setSize();
		//定位
		$.zxxbox.setPosition();

		if (s.fix) {
			$.zxxbox.setFixed();
		}
		if (s.drag) {
			$.zxxbox.drag();	
		} else {
			$(window).resize(function() {
				$.zxxbox.setPosition();					  
			});	
		}
		if (!s.bar) {
			$.zxxbox.barHide();	
		} else {
			$.zxxbox.barShow();	
		}
		if (!s.bg) {
			$.zxxbox.bgHide();
		} else {
			$.zxxbox.bgShow();
		}
		if (!s.btnclose) {
			$.zxxbox.closeBtnHide();	
		} else {
			$.o.clo.click(function() {
				$.zxxbox.hide();	
				return false;
			});
		}
		if (s.bgclose) {
			$.zxxbox.bgClickable();	
		}
		if (s.delay > 0) {
			setTimeout($.zxxbox.hide, s.delay);	
		}
	};
	$.extend($.zxxbox, {
		setSize: function() {
			if (!$.o.bd.size() || !$.o.ele.size() || !$.o.bd.size()) {
				return;	
			}
			//主体内容的尺寸
			$.o.out.css({
				"width": $.o.s.width,
				"height:": $.o.s.height
			});
						
			return $.o.out;
		},
		setPosition: function(flag) {
			flag = flag || false;
			if (!$.o.bg.size() || !$.o.ele.size() || !$.o.out.size()) {
				return;	
			}
			var w = $(window).width(), h = $(window).height(), st = $(window).scrollTop(), ph = $("body").height();
			if (ph < h) {
				ph = h;	
			}
			$.o.bg.width(w).height(ph).css("opacity", $.o.s.opacity);
			//主体内容的位置
			//获取当前主体元素的尺寸
			var xh = $.o.out.outerHeight(), xw = $.o.out.outerWidth();
			var t = st + (h - xh)/2, l = (w - xw)/2;
			
			if ($.o.s.fix && window.XMLHttpRequest) {
				t = (h - xh)/2;
			}
			if (flag === true) {
				$.o.out.animate({
					top: t,
					left: l
				});
			} else {
				$.o.out.css({
					top: t,
					left: l,
					zIndex: $.o.s.index
				});
			}
			return $.o.out;
		},
		//定位
		setFixed: function() {
			if (!$.o.out || !$.o.out.size()) {
				return;	
			}
			if (window.XMLHttpRequest) {
				$.zxxbox.setPosition().css({
					position: "fixed"			
				});
			} else {
				$(window).scroll(function() {
					$.zxxbox.setPosition();						  
				});
			}
			return $.o.out;
		},
		//背景可点击
		bgClickable: function() {
			if ($.o.bg && $.o.bg.size()) {
				$.o.bg.click(function() {
					$.zxxbox.hide();
				});
			}
		},
		//背景隐藏
		bgHide: function() {
			if ($.o.bg && $.o.bg.size()) {
				$.o.bg.hide();
			}
		},
		//背景层显示
		bgShow: function() {
			if ($.o.bg && $.o.bg.size()) {
				$.o.bg.show();
			} else {
				$('<div id="zxxBlank"></div>').prependTo("body");	
			}
		},
		//标题栏隐藏
		barHide: function() {
			if ($.o.bar && $.o.bar.size()) {
				$.o.bar.hide();
			}
		},
		//标题栏显示
		barShow: function() {
			if ($.o.bar && $.o.bar.size()) {
				$.o.bar.show();
			}
		},
		//关闭按钮隐藏
		closeBtnHide: function() {
			if ($.o.clo && $.o.clo.size()) {
				$.o.clo.hide();
			}
		},
		//弹框隐藏
		hide: function() {
			if ($.o.ele && $.o.out.size() && $.o.out.css("display") !== "none") {
				$.o.out.fadeOut("fast", function() {
					if ($.o.s.protect && (!$.o.ele.hasClass("wrap_remind") || $.o.ele.attr("id"))) {
						$.o.ele.hide().appendTo($("body"));
					}
					$(this).remove();
					if ($.isFunction($.o.s.onclose)) {
						$.o.s.onclose();
					}
				});
				if ($.o.bg.size()) {
					$.o.bg.fadeOut("fast", function() {
						$(this).remove();								
					});
				}
			}
			return false;
		},
		//拖拽
		drag: function() {
			if (!$.o.out.size() || !$.o.bar.size()) {
				$(document).unbind("mouseover").unbind("mouseup");
				return;
			}
			var bar = $.o.bar, out = $.o.out;
			var drag = false;
			var currentX = 0, currentY = 0, posX = out.css("left"), posY = out.css("top");
			bar.mousedown(function(e) {
				drag = true;
				currentX = e.pageX;
				currentY = e.pageY;							 
			}).css("cursor", "move");	
			$(document).mousemove(function(e) {
				if (drag) {
					var nowX = e.pageX, nowY = e.pageY;
					var disX = nowX - currentX, disY = nowY - currentY;
					out.css("left", parseInt(posX) + disX).css("top", parseInt(posY) + disY);
				}					   
			});
			$(document).mouseup(function() {
				drag = false;
				posX = out.css("left");
				posY = out.css("top");
			});
		},
		//预载
		loading: function() {
			var element = $('<div class="wrap_remind">加载中...</div>');
			$.zxxbox(element, { bar: false });
		},
		//ask询问方法
		ask: function(message, sureCall, cancelCall, options) {
			var element = $('<div class="wrap_remind">'+message+'<p><button id="zxxSureBtn" class="submit_btn">确认</button>&nbsp;&nbsp;<button id="zxxCancelBtn" class="cancel_btn">取消</button></p></div>');
			$.zxxbox(element, options);
			//回调方法
			$("#zxxSureBtn").click(function() {
				if ($.isFunction(sureCall)) {
					sureCall.call(this);
				}						
			});
			$("#zxxCancelBtn").click(function() {
				if (cancelCall && $.isFunction(cancelCall)) {
					cancelCall.call(this);
				}
				$.zxxbox.hide();						  
			});	
		},
		//remind提醒方法
		remind: function(message, callback, options) {
			var element = $('<div class="wrap_remind">'+message+'<p><button id="zxxSubmitBtn" class="submit_btn">确认</button</p></div>');
			$.zxxbox(element, options);
			$("#zxxSubmitBtn").click(function() {
				//回调方法
				if (callback && $.isFunction(callback)) {
					callback.call(this);	
				}
				$.zxxbox.hide();							  
			});
				
		},
		//uri Ajax方法
		ajax: function(uri, params, options) {
			if (uri) {
				$.zxxbox.loading();
				options = options || {};
				options.protect = false;
				$.ajax({
					url: uri,
					data: params || {},
					success: function(html, other) {
						$.zxxbox(html, options);
					},
					error: function() {
						$.zxxbox.remind("加载出了点问题。");	
					}
				});	
			}
		}
	});
	var zxxboxDefault = {
		title: "&nbsp;",
		shut: "×",
		index: 2000,
		opacity: 0.5,
		
		width: "auto",
		height: "auto",
		
		bar: false, //是否显示标题栏
		bg: true, //是否显示半透明背景
		btnclose:false, //是否显示关闭按钮
		
		fix: false, //是否弹出框固定在页面上
		bgclose: true, //是否点击半透明背景隐藏弹出框
		drag: true, //是否可拖拽
		
		ajaxTagA: true, //是否a标签默认Ajax操作
		
		protect: "auto", //保护装载的内容
		
		onshow: $.noop, //弹窗显示后触发事件
		onclose: $.noop, //弹窗关闭后触发事件
		
		delay: 0, //弹窗打开后关闭的时间, 0和负值不触发
		
		show_rotate: false //是否显示旋转按钮
	};
})(jQuery);

jQuery.fn.rotate = function(angle,whence) {
	var p = this.get(0);

	// we store the angle inside the image tag for persistence
	if (!whence) {
		p.angle = ((p.angle==undefined?0:p.angle) + angle) % 360;
	} else {
		p.angle = angle;
	}

	if (p.angle >= 0) {
		var rotation = Math.PI * p.angle / 180;
	} else {
		var rotation = Math.PI * (360+p.angle) / 180;
	}
	var costheta = Math.cos(rotation);
	var sintheta = Math.sin(rotation);

	if (document.all && !window.opera) {
		var canvas = document.createElement('img');

		canvas.src = p.src;
		canvas.height = p.height;
		canvas.width = p.width;

		canvas.style.filter = "progid:DXImageTransform.Microsoft.Matrix(M11="+costheta+",M12="+(-sintheta)+",M21="+sintheta+",M22="+costheta+",SizingMethod='auto expand')";
	} else {
		var canvas = document.createElement('canvas');
		if (!p.oImage) {
			canvas.oImage = new Image();
			canvas.oImage.src = p.src;
		} else {
			canvas.oImage = p.oImage;
		}

		canvas.style.width = canvas.width = Math.abs(costheta*canvas.oImage.width) + Math.abs(sintheta*canvas.oImage.height);
		canvas.style.height = canvas.height = Math.abs(costheta*canvas.oImage.height) + Math.abs(sintheta*canvas.oImage.width);

		var context = canvas.getContext('2d');
		context.save();
		if (rotation <= Math.PI/2) {
			context.translate(sintheta*canvas.oImage.height,0);
		} else if (rotation <= Math.PI) {
			context.translate(canvas.width,-costheta*canvas.oImage.height);
		} else if (rotation <= 1.5*Math.PI) {
			context.translate(-costheta*canvas.oImage.width,canvas.height);
		} else {
			context.translate(0,-sintheta*canvas.oImage.width);
		}
		context.rotate(rotation);
		context.drawImage(canvas.oImage, 0, 0, canvas.oImage.width, canvas.oImage.height);
		context.restore();
	}
	canvas.id = p.id;
	canvas.angle = p.angle;
	p.parentNode.replaceChild(canvas, p);
}

jQuery.fn.rotateRight = function(angle) {
	this.rotate(angle==undefined?90:angle);
}

jQuery.fn.rotateLeft = function(angle) {
	this.rotate(angle==undefined?-90:-angle);
}
