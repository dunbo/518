var zhufengEffect = {
	//当前时间*变化量/持续时间+初始值
	zfLinear: function(t,b,c,d){ return c*t/d + b; },
	Quad: {//二次方的缓动（t^2）；
		easeIn: function(t,b,c,d){
			return c*(t/=d)*t + b;
		},
		easeOut: function(t,b,c,d){
			return -c *(t/=d)*(t-2) + b;
		},
		easeInOut: function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t + b;
			return -c/2 * ((--t)*(t-2) - 1) + b;
		}
	},
	Cubic: {//三次方的缓动（t^3）
		easeIn: function(t,b,c,d){
			return c*(t/=d)*t*t + b;
		},
		easeOut: function(t,b,c,d){
			return c*((t=t/d-1)*t*t + 1) + b;
		},
		easeInOut: function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t*t + b;
			return c/2*((t-=2)*t*t + 2) + b;
		}
	},
	Quart: {//四次方的缓动（t^4）；
		easeIn: function(t,b,c,d){
			return c*(t/=d)*t*t*t + b;
		},
		easeOut: function(t,b,c,d){
			return -c * ((t=t/d-1)*t*t*t - 1) + b;
		},
		easeInOut: function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
			return -c/2 * ((t-=2)*t*t*t - 2) + b;
		}
	},
	Quint: {//5次方的缓动（t^5）；
		easeIn: function(t,b,c,d){
			return c*(t/=d)*t*t*t*t + b;
		},
		easeOut: function(t,b,c,d){
			return c*((t=t/d-1)*t*t*t*t + 1) + b;
		},
		easeInOut: function(t,b,c,d){
			if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
			return c/2*((t-=2)*t*t*t*t + 2) + b;
		}
	},
	Sine: {//正弦曲线的缓动（sin(t)）
		easeIn: function(t,b,c,d){
			return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
		},
		easeOut: function(t,b,c,d){
			return c * Math.sin(t/d * (Math.PI/2)) + b;
		},
		easeInOut: function(t,b,c,d){
			return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
		}
	},
	Expo: {//指数曲线的缓动（2^t）；
		easeIn: function(t,b,c,d){
			return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
		},
		easeOut: function(t,b,c,d){
			return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
		},
		easeInOut: function(t,b,c,d){
			if (t==0) return b;
			if (t==d) return b+c;
			if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
			return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
		}
	},
	Circ: {//圆形曲线的缓动（sqrt(1-t^2)）；
		easeIn: function(t,b,c,d){
			return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
		},
		easeOut: function(t,b,c,d){
			return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
		},
		easeInOut: function(t,b,c,d){
			if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
			return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
		}
	},
	Elastic: {//指数衰减的正弦曲线缓动；
		easeIn: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
		},
		easeOut: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			return (a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b);
		},
		easeInOut: function(t,b,c,d,a,p){
			if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
			if (!a || a < Math.abs(c)) { a=c; var s=p/4; }
			else var s = p/(2*Math.PI) * Math.asin (c/a);
			if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
			return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
		}
	},
	Back: {//超过范围的三次方缓动（(s+1)*t^3 - s*t^2）；
		easeIn: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158;
			return c*(t/=d)*t*((s+1)*t - s) + b;
		},
		easeOut: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158;
			return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
		},
		easeInOut: function(t,b,c,d,s){
			if (s == undefined) s = 1.70158; 
			if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
			return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
		}
	},
	zfBounce: {//指数衰减的反弹缓动。
		easeIn: function(t,b,c,d){
			return c - zhufengEffect.zfBounce.easeOut(d-t, 0, c, d) + b;
		},
		easeOut: function(t,b,c,d){
			if ((t/=d) < (1/2.75)) {
				return c*(7.5625*t*t) + b;
			} else if (t < (2/2.75)) {
				return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
			} else if (t < (2.5/2.75)) {
				return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
			} else {
				return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
			}
		},
		easeInOut: function(t,b,c,d){
			if (t < d/2) return zhufengEffect.zfBounce.easeIn(t*2, 0, c, d) * .5 + b;
			else return zhufengEffect.zfBounce.easeOut(t*2-d, 0, c, d) * .5 + c*.5 + b;
		}
	}
};
function css(ele,attr){
	if(window.getComputedStyle){
		return parseFloat(getComputedStyle(ele,null)[attr]);			
	}else{
		var value=ele.currentStyle[attr];
		if(attr=="opacity"){			
			if(typeof value=='undefined'){
				ele.style.opacity=1;
				ele.style.filter="alpha(opacity=100)";
			}else{
				ele.style.filter="alpha(opacity="+value*100+")"
				
			}
		}
		return parseFloat(ele.currentStyle[attr]);	
	}	
}

function setCss(ele,attr,value){	
	switch(attr){
		case "opacity":
			ele.style.opacity=value;
			ele.style.filter="alpha(opacity="+value*100+")";
		break;
		case "width":
		case "height":
		case "top":
		case "left":
		case "paddingLeft":
		case "padding":
		case "paddingTop":
			ele.style[attr]=value+'px';
			break;
		case "float":
			ele.style.cssFloat=value;//标准浏览器专用
			ele.style.floatStyle=value;//IE专用
			break;
		default:
			ele.style[attr]=value;			
} 	
}

function linear(t,b,c,d){
					return c*t/d+b;
					
				}
				
//1是直线 linear，2减速：expo,3弹性Elastic,4:back,5:bounce反弹
function animat(ele,obj,duration,effect,fn){
/*	if(typeof effect=="function"){		
		var fnEffect=zhufengEffect.Expo.easeOut;	
		fn=effect;
	}else if(effect==2||effect==undefined||effect.toString().toLowerCase()=="expo"){
		var fnEffect=zhufengEffect.Expo.easeOut;
	}else if(effect==1||effect.toString().toLowerCase()=="linear"){
		var fnEffect=zhufengEffect.zfLinear;	
	}else if(effect==3||effect.toString().toLowerCase()=="elastic"){
		var fnEffect=zhufengEffect.Elastic.easeOut;	
	}else if(effect==4||effect.toString().toLowerCase()=="back"){
		var fnEffect=zhufengEffect.Back.easeOut
	}else if(effect==5||effect.toString().toLowerCase()=="bounce"){
		var fnEffect=zhufengEffect.zfBounce.easeOut;	
	}*/
	
	if(typeof effect=="function"){
		var fnEffect=zhufengEffect.Expo.easeOut;
		fn=effect;
	}else if(typeof effect=="number"){
		switch(effect){
		case 1:
			var fnEffect=zhufengEffect.zfLinear;
			break;	
		case 2:
			var fnEffect=zhufengEffect.Expo.easeOut;
			break;
		case 3:
			var fnEffect=zhufengEffect.Elastic.easeOut;
			break;
		case 4:
			var fnEffect=zhufengEffect.Back.easeOut;
			break;
		case 5:
			var fnEffect=zhufengEffect.zfBounce.easeOut;
			break;
			
		}		
		
	}else if(typeof effect=="undefined"){
		var fnEffect=zhufengEffect.Expo.easeOut;
		
	}else if(effect instanceof Array){
		//['Back','easeInOut']
		//zhufengEffect['Back']['easeInOut']
		//zhufengEffect.Back.easeInOut;
		
		if(effect.length==2)
			var fnEffect=zhufengEffect[effect[0]][effect[1]]
	}
	
	var oBegin={};
	var oChange={};

	var count=0;//标识变量，标识各个方向是不是应该运动
	for(var attr in obj){
		var begin=css(ele,attr);
		var change=obj[attr]-begin;
		if(change==0) continue;//如果目标值和起始值相则
		oChange[attr]=change;
		oBegin[attr]=begin;		
		count++;
	}
	
	if(!count) return ;
	
	var times=0;
	var interval=13;
	window.clearTimeout(ele.timer);
	_move();
	
	function _move(){		
		times+=interval;		
		if(times<duration){			
			for(var attr in oBegin){
				var val=fnEffect(times,oBegin[attr],oChange[attr],duration);//oBegin[attr]+oChange[attr]*times/duration;
				
				setCss(ele,attr,val);	
			}			
			ele.timer=window.setTimeout(arguments.callee,interval);
		}else{//到达终点			
			for(var attr in oBegin){
				setCss(ele,attr,obj[attr]);	
			}
			ele.timer=null;	//ele.timer是个标识变量，用来标识当前的元素是不是正在处于运动状态
			if(typeof fn=="function"){
				fn.call(ele);	
			}
		}		
	}	
}