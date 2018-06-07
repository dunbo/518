//滚动条
(function(a) {
    function b(b, c) {
        function w(a) {
            if (! (g.ratio >= 1)) {
                o.now = Math.min(i[c.axis] - j[c.axis], Math.max(0, o.start + ((k ? a.pageX: a.pageY) - p.start)));
                n = o.now * h.ratio;
                g.obj.css(l, -n);
                j.obj.css(l, o.now)
            }
            document.body.setCapture();
            return false
        }
        function v(b) {
            a(document).unbind("mousemove", w);
            a(document).unbind("mouseup", v);
            j.obj.unbind("mouseup", v);
            document.ontouchmove = j.obj[0].ontouchend = document.ontouchend = null;
            document.body.releaseCapture();
            return false;
        }
        function u(b) {
            if (! (g.ratio >= 1)) {
                var b = b || window.event;
                var d = -1;
                if(b.wheelDelta){//IE/Opera/Chrome 
					d=b.wheelDelta/120; 
				}else if(b.detail){//Firefox 
					d=-b.detail/3; 
				} 
                n -= d * c.wheel;
                n = Math.min(g[c.axis] - f[c.axis], Math.max(0, n));
                if(!isFinite(n))
               {
                  alert("-b.detail / 3" +(-b.detail) +mmm+"出错了"+d+"b.wheelDelta"+b.wheelDelta+" b.wheelDelta / 120"+ b.wheelDelta / 120 );
                }
                j.obj.css(l, n / h.ratio);
                g.obj.css(l, -n);
                b = a.event.fix(b);
                b.preventDefault()
                
            }
        }
        function t(b) {
            p.start = k ? b.pageX: b.pageY;
            var c = parseInt(j.obj.css(l));
            o.start = c == "auto" ? 0 : c;
            a(document).bind("mousemove", w);
           
            document.ontouchmove = function(b) {
               a(document).unbind("mousemove");
               w(b.touches[0]);
            };
            a(document).bind("mouseup", v);
            j.obj.bind("mouseup", v);
            j.obj[0].ontouchend = function(b) {
                a(document).unbind("mouseup");
                j.obj.unbind("mouseup");
               v(b.touches[0])
            };                                                                                                               
            return false
        }
        function s() {
            j.obj.bind("mousedown", t);
            j.obj[0].ontouchstart = function(a) {
                a.preventDefault();
                j.obj.unbind("mousedown");
                t(a.touches[0]);
                return false
            };
            i.obj.bind("mouseup", v);
            if (c.scroll && this.addEventListener) {
                e[0].addEventListener("DOMMouseScroll", u, false);
                e[0].addEventListener("mousewheel", u, false)
            } else if (c.scroll) {
                e[0].onmousewheel = u
            }
        }
        function r() {
            j.obj.css(l, n / h.ratio);
            g.obj.css(l, -n);
            p["start"] = j.obj.offset()[l];
            var a = m.toLowerCase();
         //   h.obj.css(a, i[c.axis]);
           h.obj.css(a, "100%");
            i.obj.css(a, i[c.axis]);
            j.obj.css(a, j[c.axis])
        }
        function q() {
            d.update();
            s();
            return d
        }
        var d = this;
        var e = b;
        var f = {
            obj: a(".viewport", b)
        };
        var g = {
            obj: a(".overview", b)
        };
        var h = {
            obj: a(".scrollbar", b)
        };
        var i = {
            obj: a(".track", h.obj)
        };
        var j = {
            obj: a(".thumb", h.obj)
        };
        var k = c.axis == "x",
        l = k ? "left": "top",
        m = k ? "Width": "Height";
        var n, o = {
            start: 0,
            now: 0
        },
        p = {};
        this.update = function(a) {
            f[c.axis] = f.obj[0]["offset" + m];
            g[c.axis] = g.obj[0]["scroll" + m];
            g.ratio = f[c.axis] / g[c.axis];
            h.obj.toggleClass("disable", g.ratio >= 1);
            i[c.axis] = c.size == "auto" ? f[c.axis] : c.size;
            j[c.axis] = Math.min(i[c.axis], Math.max(0, c.sizethumb == "auto" ? i[c.axis] * g.ratio: c.sizethumb));
            h.ratio = c.sizethumb == "auto" ? g[c.axis] / i[c.axis] : (g[c.axis] - f[c.axis]) / (i[c.axis] - j[c.axis]);
            n = a == "relative" && g.ratio <= 1 ? Math.min(g[c.axis] - f[c.axis], Math.max(0, n)) : 0;
            n = a == "bottom" && g.ratio <= 1 ? g[c.axis] - f[c.axis] : isNaN(parseInt(a)) ? n: parseInt(a);
            r()
        };
        return q()
    }
    a.tiny = a.tiny || {};
    a.tiny.scrollbar = {
        options: {
            axis: "y",
            wheel: 40,
            scroll: true,
            size: "auto",
            sizethumb: "auto"
        }
    };
    a.fn.tinyscrollbar = function(c) {
        var c = a.extend({},
        a.tiny.scrollbar.options, c);
        this.each(function() {
            a(this).data("tsb", new b(a(this), c))
        });
        return this
    };
    a.fn.tinyscrollbar_update = function(b) {
        return a(this).data("tsb").update(b)
    };
})(jQuery)

var apps = {
	Config: {
		container:'.changewidth_box',
		con: '.recommend',
		subject:".ztindex"
		//conbj:'#changewidth_box1'
	},
	resize:function(){
		var wind = $(apps.Config.container).width();
		//var container=$(app.Config.conbj);
			apps.setconwidth(apps.Config.con);
			//alert(wind);
			$(apps.Config.con).find("li").each(function(index,item){
			  if(wind>538&&wind<734){
					var space=Math.floor(wind/5);
					$(item).width(space-3);
				}
			   if(wind==540){
				    $(item).width(104);
			  }
			  if(wind==736){
				  $(item).width(120);
			 }
		 });
		 var widtnsu=$(apps.Config.subject).width();
		 $(apps.Config.subject).find("li").each(function(index,item){
			 if(widtnsu>540&&widtnsu<736){
					var space=Math.floor(widtnsu/3);
					$(item).width(space-1);
				}
			if(widtnsu==540){
				$(item).width(180);
			}
			if(widtnsu==736)
			{
				$(item).width(184);
			}
		});
	},
	setconwidth:function(con){
		var wind = $(apps.Config.container).width();
       if(wind==540){
		 	$(con).width(531);
		}
		else if(wind==736){
			$(con).width(726);
		}else{//每个item的宽度
			  var space=Math.floor(wind/5);
			 //alert(space);
			  $(con).width(space*5-9);//整个页面的宽度
			  
		}
	},
	init:function(options){

		apps.Config=$.extend(apps.Config,options);
		apps.resize();
		$(".flashimg").width($(".images").width());
		$(window).resize(function(){
			apps.Config=$.extend(apps.Config,options);
		apps.resize();
	    $(".flashimg").width($(".images").width());
		});
   },
   initie6:function(options){
	   apps.Config=$.extend(apps.Config,options);
	   $(apps.Config.con).find("li").each(function(index,item){
			 $(item).width(105);
		 });
		 $('.ztindex ul').find('li').each(function(index,item){
			$(item).width(180);
		 });
   }
  
    }
function ckie6(){
	if(window.ActiveXObject){
		var browser=navigator.appName 
        var b_version=navigator.appVersion 
        var version=b_version.split(";"); 
        var trim_Version=version[1].replace(/[ ]/g,""); 
			if(browser=="Microsoft Internet Explorer" && trim_Version=="MSIE6.0") 
        {  
			return true;
        }else{
			return false;
		}
	}
}
function chk(num){ //判断是否是奇数
  if(num%2){
	  return true;
  }
  else{
	  return false
 }
} 
$(document).ready(function(){
	$('#scrollbar1').tinyscrollbar();
	$(window).resize(function(){
		$('#scrollbar1').tinyscrollbar();
	});	
	//设置安装，更新按钮的状态
	if(ckie6()){
  			var opt1={"container":".changewidth_box","con":".recommend"};
       		apps.initie6(opt1);
			$('#scrollbar1').tinyscrollbar();
		
	}else{
		    var opt1={"container":".changewidth_box","con":".recommend"};
       		apps.init(opt1);
	}
  
 });
