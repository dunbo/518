
function uacheck(){
	var reg = /nokia|sony|ericsson|mot|samsung|sgh|lg|philips|panasonic|alcatel|lenovo|cldc|midp|wap|mobile|android|bada/i;
	var ua = window.navigator.userAgent;
	var result =  reg.exec(ua);
	if(result == null) return true;
	return false;
}
function getCookie(cookie_name){
	var allcookies = document.cookie;
	var cookie_pos = allcookies.indexOf(cookie_name);
	if (cookie_pos != -1) {
		cookie_pos += cookie_name.length + 1;
		var cookie_end = allcookies.indexOf(";", cookie_pos);
		if (cookie_end == -1){
			cookie_end = allcookies.length;
		}
		var value = unescape(allcookies.substring(cookie_pos, cookie_end));
	}	
	return value;
}
function getArgs1() {
	var args = {};
	var query = location.search.substring(1);
	var pairs = query.split("&");
	for(var i = 0; i < pairs.length; i++) {
		var pos = pairs[i].indexOf('=');
		if (pos == -1) continue;
		var argname = pairs[i].substring(0,pos);
		var value = pairs[i].substring(pos+1);
		value = decodeURIComponent(value);
		args[argname] = value;
	}
	return args;
}

var proid_arr1 = getArgs1();
var cs = proid_arr1.cs;
var xhr;

if (cs) {
	document.cookie = "cs=1; path=/"; 
} else {
	var cs_cook = getCookie("cs");
	if (cs_cook == null) {
		var cook = getCookie("wapcheck");
		if(!uacheck()){
			//1：浏览器兼容问题解决
			if(window.XMLHttpRequest){
			    xhr = new XMLHttpRequest();
			}else{
			    xhr = new ActiveXObject('Microsoft.XMLHTTP');
			}

			//2:接收服务器返回数据
			xhr.onreadystatechange=function(){
			    if(xhr.readyState==4&&xhr.status==200){
			    	if(xhr.responseText) {
			    		//跳转到适配的m站
						window.location.replace(xhr.responseText);
					} else {
						if(cook == null){
							//没有cookie跳转到拦截页面
							window.location.href="/wapcheck2.html?backurl="+window.location.href;
						}else{
							if(cook == "m"){
								//有cookie跳转到m站首页
								window.location.href="http://m.anzhi.com/";
							}
						}
					}
			    }
			}

			//3：配置 Ajax请求地址
			xhr.open('GET','/wapcheck2.php?url='+window.location.href,true);

			//4：发送请求
			xhr.send();

		}
	}
}
