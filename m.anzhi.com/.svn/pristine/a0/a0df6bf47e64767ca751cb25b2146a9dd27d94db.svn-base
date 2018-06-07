define(function(){

	function std() {

		//模版变量替换方法
		this.setTpl = function(str,val) {
			for(var k in val) {
				var reg = new RegExp('\\$\\{'+ k +'\\}','g');
				str = str.replace(reg,val[k]);
			}
			return str
		}

		//格式化输出时间(使用方法同PHP date函数)
		this.date = function(str,unixTime) {
			var o = {
				'Y' : 'getFullYear',
				'm' : 'getMonth',
				'd' : 'getDate',
				'h' : 'getHours',
				'i' : 'getMinutes',
				's' : 'getSeconds'
			};
			var d = new Date();
			if(unixTime) {
				d.setTime(unixTime * 1000);
			}
			var nStr = "";
			
			for(var i=0;i < str.length;++i) {
				var c = str.substr(i,1);
				if(o[c]) {
					
					var s = "";
					//奇葩的月份
					if(c == 'm') {
						s = (d[o[c]]() + 1);
					} else {
						s = d[o[c]]();
					}
					s = String(s);
					
					if(s.length < 2) {
						s = ('0' + s);
					}
					nStr += s;
				} else {
					nStr += c;
				}
			}
			return nStr;
		}
		
		//格式化输出。(目前只支持%s)
		this.sprintf = function(str,val) {
			for (var i = 1; i < arguments.length; i++) {
				str = str.replace("%s",arguments[i]);
			}
			return str;
		}; 

		//获取地址栏中锚点后的参数
		this.getParam = function(key,def) {
			if(typeof(def) == "undefined") {
				def = null;
			}
			var index = window.location.href.indexOf("#");
			if(index == -1) {
				return def;
			}
			var str = window.location.href.substr(index + 1);
			var arr =  str.split("&");
			var param = {};
			for(var i = 0;i < arr.length;++i) {
				//var tmp = arr[i].indexOf("=");
				if(arr[i].indexOf("=") == -1) {
					param[arr[i]] = "";
					continue;
				}
				var tmp = arr[i].split("=");
				if(tmp.length < 2) {
					param[arr[i]] = "";
					continue;
				}
				param[tmp[0]] = tmp[1];

			}
			if(typeof(param[key]) == "undefined") {
				return def;
			}
			return param[key];
		};

		this.goLogin = function(){
			window.location.href = "http://dev.i.anzhi.com/mweb/account/login?serviceId=029&serviceVersion=5410&serviceType=0&redirecturi="+encodeURIComponent(window.location.href);
			return false;
		};
		


	}

	return new std();

});