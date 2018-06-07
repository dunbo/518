
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

var cook = getCookie("wapcheck");
if(cook == null){
	if(!uacheck()){
		window.location.href="/wapcheck2.html?backurl="+window.location.href;
	}
}else{
	if(cook == "m"){
		window.location.href="http://m.anzhi.com/";
	}
}