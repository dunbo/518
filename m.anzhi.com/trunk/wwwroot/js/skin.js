var proid_arr = getArgs();
var skin = proid_arr.skinpkg;
addCss(skin);
function getArgs() {
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

if(!skin){
	skin = '';
}

function addCss(skin) {
	var link = document.createElement('link');
	link.type = 'text/css';
	link.rel = 'stylesheet';
	if(skin){
		link.href = '/css/'+skin+'.css';
	}else{
		link.href = '/css/cn.goapk.market.skin.default.css';
	}
	document.getElementsByTagName("head")[0].appendChild(link);
}

