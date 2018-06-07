var var_QUERY = window.location.href.split("?");
var var_GET = new Array();

if(var_QUERY.length > 1){
    var var_buffer = var_QUERY[1].split("&");
    var var_loop = var_buffer.length;
    for(var i=0; i<var_loop; i++){
		var temp = var_buffer[i].split("=");
		var_GET[temp[0]] = temp[1];
	}
}

function _GET(key, def){
	if(typeof(var_GET[key])=="undefined")
		return def;
    return var_GET[key];
}

function getViewHeight(){
	return document.documentElement.clientHeight || document.body.clientHeight;
}

function getViewWidth(){
	return document.documentElement.clientWidth || document.body.clientWidth;
}

function getScrollHeight(){
	return document.documentElement.scrollHeight || document.body.scrollHeight;
}

function scrollY(){
	return document.documentElement.scrollTop || document.body.scrollTop;
}