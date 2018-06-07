var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser'});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
	HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}

var CSSLOADED = [];
var JSLOADED = [];
var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':200,'menu':300,'dialog':400,'prompt':500};
JSMENU['float'] = '';
var CURRENTSTYPE = null;
var discuz_uid = isUndefined(discuz_uid) ? 0 : discuz_uid;
var creditnotice = isUndefined(creditnotice) ? '' : creditnotice;
var cookiedomain = isUndefined(cookiedomain) ? '' : cookiedomain;
var cookiepath = isUndefined(cookiepath) ? '' : cookiepath;
var EXTRAFUNC = [], EXTRASTR = '';
EXTRAFUNC['showmenu'] = [];

var DISCUZCODE = [];
DISCUZCODE['num'] = '-1';
DISCUZCODE['html'] = [];

var USERABOUT_BOX = true;
var USERCARDST = null;
var CLIPBOARDSWFDATA = '';
var NOTICETITLE = [];

if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineGetter__( "innerText", function(){
		var anyString = "";
		var childS = this.childNodes;
		for(var i=0; i <childS.length; i++) {
			if(childS[i].nodeType==1) {
				anyString += childS[i].tagName=="BR" ? '\n' : childS[i].innerText;
			} else if(childS[i].nodeType==3) {
				anyString += childS[i].nodeValue;
			}
		}
		return anyString;
	});
	HTMLElement.prototype.__defineSetter__( "innerText", function(sText){
		this.textContent=sText;
	});
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
			var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

function GetId(id) {
	return !id ? null : document.getElementById(id);
}

function C(classname, ele, tag) {
	var returns = [];
	ele = ele || document;
	tag = tag || '*';
	if(ele.getElementsByClassName) {
		var eles = ele.getElementsByClassName(classname);
		if(tag != '*') {
			for (var i = 0, L = eles.length; i < L; i++) {
				if (eles[i].tagName.toLowerCase() == tag.toLowerCase()) {
						returns.push(eles[i]);
				}
			}
		} else {
			returns = eles;
		}
	}else {
		eles = ele.getElementsByTagName(tag);
		var pattern = new RegExp("(^|\\s)"+classname+"(\\s|$)");
		for (i = 0, L = eles.length; i < L; i++) {
				if (pattern.test(eles[i].className)) {
						returns.push(eles[i]);
				}
		}
	}
	return returns;
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function _detachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.removeEventListener) {
		obj.removeEventListener(evt, func, false);
	} else if(eventobj.detachEvent) {
		obj.detachEvent('on' + evt, func);
	}
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function trim(str) {
	return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function mb_strlen(str) {
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = !dot ? '...' : dot;
	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}

function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function htmlspecialchars(str) {
	return preg_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], str);
}

function display(id) {
	var obj = GetId(id);
	if(obj.style.visibility) {
		obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
	} else {
		obj.style.display = obj.style.display == '' ? 'none' : '';
	}
}

function checkall(form, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != checkall && !e.disabled && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
			if(e.checked) {
				count++;
			}
		}
	}
	return count;
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	var expires = new Date();
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	expires.setTime(expires.getTime() + seconds * 1000);
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

function Ajax(recvType, waitId) {

	var aj = new Object();

	aj.loading = '请稍候...';
	aj.recvType = recvType ? recvType : 'XML';
	aj.waitId = waitId ? GetId(waitId) : null;

	aj.resultHandle = null;
	aj.sendString = '';
	aj.targetUrl = '';

	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};

	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};

	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : GetId(waitid);
	};

	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};

	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + aj.loading + '</span>';
		}
	};

	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
					aj.resultHandle('<a href="' + aj.targetUrl + '" target="_blank" style="color:red">内部错误，无法显示此内容</a>' , aj);
				} else {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
				}
			}
		}
	};

	aj.get = function(targetUrl, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		if(window.XMLHttpRequest) {
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);
		} else {
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();
		}
	};
	aj.post = function(targetUrl, sendString, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	return aj;
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /^\w+\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	if(!url.match(/^https?:\/\//)) url = SITEURL + url;
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

var safescripts = {}, evalscripts = [];
function safescript(id, call, seconds, times, timeoutcall, endcall, index) {
	seconds = seconds || 1000;
	times = times || 0;
	var checked = true;
	try {
		if(typeof call == 'function') {
			call();
		} else {
			eval(call);
		}
	} catch(e) {
		checked = false;
	}
	if(!checked) {
		if(!safescripts[id] || !index) {
			safescripts[id] = safescripts[id] || [];
			safescripts[id].push({
				'times':0,
				'si':setInterval(function () {
					safescript(id, call, seconds, times, timeoutcall, endcall, safescripts[id].length);
				}, seconds)
			});
		} else {
			index = (index || 1) - 1;
			safescripts[id][index]['times']++;
			if(safescripts[id][index]['times'] >= times) {
				clearInterval(safescripts[id][index]['si']);
				if(typeof timeoutcall == 'function') {
					timeoutcall();
				} else {
					eval(timeoutcall);
				}
			}
		}
	} else {
		try {
			index = (index || 1) - 1;
			if(safescripts[id][index]['si']) {
				clearInterval(safescripts[id][index]['si']);
			}
			if(typeof endcall == 'function') {
				endcall();
			} else {
				eval(endcall);
			}
		} catch(e) {}
	}
}

function F(func, args, script) {
	var run = function () {
		var argc = args.length, s = '';
		for(i = 0;i < argc;i++) {
			s += ',args[' + i + ']';
		}
		eval('var check = typeof ' + func + ' == \'function\'');
		if(check) {
			eval(func + '(' + s.substr(1) + ')');
		} else {
			setTimeout(function () { checkrun(); }, 50);
		}
	};
	var checkrun = function () {
		if(JSLOADED[src]) {
			run();
		} else {
			setTimeout(function () { checkrun(); }, 50);
		}
	};
	script = script || 'common_extra';
	src = JSPATH + script + '.js?' + VERHASH;
	if(!JSLOADED[src]) {
		appendscript(src);
	}
	checkrun();
}

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && GetId(id)) {
		GetId(id).parentNode.removeChild(GetId(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

function ajaxupdateevents(obj, tagName) {
	tagName = tagName ? tagName : 'A';
	var objs = obj.getElementsByTagName(tagName);
	for(k in objs) {
		var o = objs[k];
		ajaxupdateevent(o);
	}
}

function ajaxupdateevent(o) {
	if(typeof o == 'object' && o.getAttribute) {
		if(o.getAttribute('ajaxtarget')) {
			if(!o.id) o.id = Math.random();
			var ajaxevent = o.getAttribute('ajaxevent') ? o.getAttribute('ajaxevent') : 'click';
			var ajaxurl = o.getAttribute('ajaxurl') ? o.getAttribute('ajaxurl') : o.href;
			_attachEvent(o, ajaxevent, newfunction('ajaxget', ajaxurl, o.getAttribute('ajaxtarget'), o.getAttribute('ajaxwaitid'), o.getAttribute('ajaxloading'), o.getAttribute('ajaxdisplay')));
			if(o.getAttribute('ajaxfunc')) {
				o.getAttribute('ajaxfunc').match(/(\w+)\((.+?)\)/);
				_attachEvent(o, ajaxevent, newfunction(RegExp.$1, RegExp.$2));
			}
		}
	}
}

function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = typeof waitid == 'undefined' || waitid === null ? showid : waitid;
	var x = new Ajax();
	x.setLoading(loading);
	x.setWaitId(waitid);
	x.display = typeof display == 'undefined' || display == null ? '' : display;
	x.showId = GetId(showid);

	if(url.substr(strlen(url) - 1) == '#') {
		url = url.substr(0, strlen(url) - 1);
		x.autogoto = 1;
	}

	var url = url + '&inajax=1&ajaxtarget=' + showid;
	x.get(url, function(s, x) {
		var evaled = false;
		if(s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			if(x.showId) {
				x.showId.style.display = x.display;
				ajaxinnerhtml(x.showId, s);
				ajaxupdateevents(x.showId);
				if(x.autogoto) scroll(0, x.showId.offsetTop);
			}
		}

		ajaxerror = null;
		if(recall && typeof recall == 'function') {
			recall();
		} else if(recall) {
			eval(recall);
		}
		if(!evaled) evalscript(s);
	});
}

function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = GetId(ajaxframeid);
	var curform = GetId(formid);
	var formtarget = curform.target;

	var handleResult = function() {
		var s = '';
		var evaled = false;

		showloading('none');
		try {
			s = GetId(ajaxframeid).contentWindow.document.XMLDocument.text;
		} catch(e) {
			try {
				s = GetId(ajaxframeid).contentWindow.document.documentElement.firstChild.wholeText;
			} catch(e) {
				try {
					s = GetId(ajaxframeid).contentWindow.document.documentElement.firstChild.nodeValue;
				} catch(e) {
					s = '内部错误，无法显示此内容';
				}
			}
		}
		if(s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			if(showidclass != 'onerror') {
				GetId(showid).className = showidclass;
			} else {
				showError(s);
				ajaxerror = true;
			}
		}
		if(submitbtn) {
			submitbtn.disabled = false;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml(GetId(showid), s);
		}
		ajaxerror = null;
		if(GetId(formid)) GetId(formid).target = formtarget;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		if(!BROWSER.firefox) {
			GetId('append_parent').removeChild(ajaxframe.parentNode);
		} else {
			setTimeout(
				function(){
					GetId('append_parent').removeChild(ajaxframe.parentNode);
				},
				100
			);
		}
	};
	if(!ajaxframe) {
		var div = document.createElement('div');
		div.style.display = 'none';
		div.innerHTML = '<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1"></iframe>';
		GetId('append_parent').appendChild(div);
		ajaxframe = GetId(ajaxframeid);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	showloading();
	curform.target = ajaxframeid;
	var action = curform.getAttribute('action');
	action = hostconvert(action);
	curform.action = action.replace(/\&inajax\=1/g, '')+'&inajax=1';
	curform.submit();
	if(submitbtn) {
		submitbtn.disabled = true;
	}
	doane();
	return false;
}

function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall, idclass, contentclass) {
	if(!ctrlObj.getAttribute('mid')) {
		var ctrlid = ctrlObj.id;
		if(!ctrlid) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	} else {
		var ctrlid = ctrlObj.getAttribute('mid');
		if(!ctrlObj.id) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	}
	var menuid = ctrlid + '_menu';
	var menu = GetId(menuid);
	if(isUndefined(timeout)) timeout = 3000;
	if(isUndefined(cache)) cache = 1;
	if(isUndefined(pos)) pos = '43';
	if(isUndefined(duration)) duration = timeout > 0 ? 0 : 3;
	if(isUndefined(idclass)) idclass = 'p_pop';
	if(isUndefined(contentclass)) contentclass = 'p_opt';
	var func = function() {
		showMenu({'ctrlid':ctrlObj.id,'menuid':menuid,'duration':duration,'timeout':timeout,'pos':pos,'cache':cache,'layer':2});
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
	};

	if(menu) {
		if(menu.style.display == '') {
			hideMenu(menuid);
		} else {
			func();
		}
	} else {
		menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = idclass;
		menu.innerHTML = '<div class="' + contentclass + '" id="' + menuid + '_content"></div>';
		GetId('append_parent').appendChild(menu);
		var url = (!isUndefined(ctrlObj.attributes['shref']) ? ctrlObj.attributes['shref'].value : (!isUndefined(ctrlObj.href) ? ctrlObj.href : ctrlObj.attributes['href'].value));
		url += (url.indexOf('?') != -1 ? '&' :'?') + 'ajaxmenu=1';
		ajaxget(url, menuid + '_content', 'ajaxwaitid', '', '', func);
	}
	doane();
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function showPreview(val, id) {
	var showObj = GetId(id);
	if(showObj) {
		showObj.innerHTML = val.replace(/\n/ig, "<bupdateseccoder />");
	}
}

function showloading(display, waiting) {
	var display = display ? display : 'block';
	var waiting = waiting ? waiting : '请稍候...';
	GetId('ajaxwaitid').innerHTML = waiting;
	GetId('ajaxwaitid').style.display = display;
}

function ajaxinnerhtml(showid, s) {
	if(showid.tagName != 'TBODY') {
		showid.innerHTML = s;
	} else {
		while(showid.firstChild) {
			showid.firstChild.parentNode.removeChild(showid.firstChild);
		}
		var div1 = document.createElement('DIV');
		div1.id = showid.id+'_div';
		div1.innerHTML = '<table><tbody id="'+showid.id+'_tbody">'+s+'</tbody></table>';
		GetId('append_parent').appendChild(div1);
		var trs = div1.getElementsByTagName('TR');
		var l = trs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(trs[0]);
		}
		var inputs = div1.getElementsByTagName('INPUT');
		var l = inputs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(inputs[0]);
		}
		div1.parentNode.removeChild(div1);
	}
}

function doane(event, preventDefault, stopPropagation) {
	var preventDefault = isUndefined(preventDefault) ? 1 : preventDefault;
	var stopPropagation = isUndefined(stopPropagation) ? 1 : stopPropagation;
	e = event ? event : window.event;
	if(!e) {
		e = getEvent();
	}
	if(!e) {
		return null;
	}
	if(preventDefault) {
		if(e.preventDefault) {
			e.preventDefault();
		} else {
			e.returnValue = false;
		}
	}
	if(stopPropagation) {
		if(e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	return e;
}

function loadcss(cssname) {
	if(!CSSLOADED[cssname]) {
		if(!GetId('css_' + cssname)) {
			css = document.createElement('link');
			css.id = 'css_' + cssname,
			css.type = 'text/css';
			css.rel = 'stylesheet';
			css.href = 'data/cache/style_' + STYLEID + '_' + cssname + '.css?' + VERHASH;
			var headNode = document.getElementsByTagName("head")[0];
			headNode.appendChild(css);
		} else {
			GetId('css_' + cssname).href = 'data/cache/style_' + STYLEID + '_' + cssname + '.css?' + VERHASH;
		}
		CSSLOADED[cssname] = 1;
	}
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = GetId(ctrlid);
	var menuObj = GetId(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 600 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && GetId(drag) ? GetId(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	var ctrlclass = isUndefined(v['ctrlclass']) ? '' : v['ctrlclass'];
	var winhandlekey = isUndefined(v['win']) ? '' : v['win'];
	zindex = cover ? zindex + 500 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	for(i in EXTRAFUNC['showmenu']) {
		try {
			eval(EXTRAFUNC['showmenu'][i] + '()');
		} catch(e) {}
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if(ctrlObj) {
		if(!ctrlObj.getAttribute('initialized')) {
			ctrlObj.setAttribute('initialized', true);
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				}
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	if(!menuObj.getAttribute('initialized')) {
		menuObj.setAttribute('initialized', true);
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			return doane(e, 0, 1);
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
			coverObj.style.opacity = 0.5;
			coverObj.onclick = function () { hideMenu(); };
			GetId('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}

	if(cover) GetId(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O > 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 20;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 40);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	menuObj.style.display = '';
	if(ctrlObj && ctrlclass) {
		ctrlObj.className += ' ' + ctrlclass;
		menuObj.setAttribute('ctrlid', ctrlid);
		menuObj.setAttribute('ctrlclass', ctrlclass);
	}
	if(pos != '*') {
		setMenuPosition(showid, menuid, pos);
	}
	if(BROWSER.ie && BROWSER.ie < 7 && winhandlekey && GetId('fwin_' + winhandlekey)) {
		GetId(menuid).style.left = (parseInt(GetId(menuid).style.left) - parseInt(GetId('fwin_' + winhandlekey).style.left)) + 'px';
		GetId(menuid).style.top = (parseInt(GetId(menuid).style.top) - parseInt(GetId('fwin_' + winhandlekey).style.top)) + 'px';
	}
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
	var hasshow = function(ele) {
		while(ele.parentNode && ((typeof(ele['currentStyle']) === 'undefined') ? window.getComputedStyle(ele,null) : ele['currentStyle'])['display'] !== 'none') {
			ele = ele.parentNode;
		}
		if(ele === document) {
			return true;
		} else {
			return false;
		}
	};
	if(!menuObj.getAttribute('disautofocus')) {
		try{
			var focused = false;
			var tags = ['input', 'select', 'textarea', 'button', 'a'];
			for(var i = 0; i < tags.length; i++) {
				var _all = menuObj.getElementsByTagName(tags[i]);
				if(_all.length) {
					for(j = 0; j < _all.length; j++) {
						if((!_all[j]['type'] || _all[j]['type'] != 'hidden') && hasshow(_all[j])) {
							_all[j].className += ' hidefocus';
							_all[j].focus();
							focused = true;
							var cobj = _all[j];
							_attachEvent(_all[j], 'blur', function (){cobj.className = trim(cobj.className.replace(' hidefocus', ''));});
							break;
						}
					}
				}
				if(focused) {
					break;
				}
			}
		} catch (e) {
		}
	}
}
var delayShowST = null;
function delayShow(ctrlObj, call, time) {
	if(typeof ctrlObj == 'object') {
		var ctrlid = ctrlObj.id;
		call = call || function () { showMenu(ctrlid); };
	}
	var time = isUndefined(time) ? 500 : time;
	delayShowST = setTimeout(function () {
		if(typeof call == 'function') {
			call();
		} else {
			eval(call);
		}
	}, time);
	if(!ctrlObj.delayinit) {
		_attachEvent(ctrlObj, 'mouseout', function() {clearTimeout(delayShowST);});
		ctrlObj.delayinit = 1;
	}
}

var dragMenuDisabled = false;
function dragMenu(menuObj, e, op) {
	e = e ? e : window.event;
	if(op == 1) {
		if(dragMenuDisabled || in_array(e.target ? e.target.tagName : e.srcElement.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
			return;
		}
		JSMENU['drag'] = [e.clientX, e.clientY];
		JSMENU['drag'][2] = parseInt(menuObj.style.left);
		JSMENU['drag'][3] = parseInt(menuObj.style.top);
		document.onmousemove = function(e) {try{dragMenu(menuObj, e, 2);}catch(err){}};
		document.onmouseup = function(e) {try{dragMenu(menuObj, e, 3);}catch(err){}};
		doane(e);
	}else if(op == 2 && JSMENU['drag'][0]) {
		var menudragnow = [e.clientX, e.clientY];
		menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
		menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
		menuObj.removeAttribute('top_');menuObj.removeAttribute('left_');
		doane(e);
	}else if(op == 3) {
		JSMENU['drag'] = [];
		document.onmousemove = null;
		document.onmouseup = null;
	}
}
function setMenuPosition(showid, menuid, pos) {
	var showObj = GetId(showid);
	var menuObj = menuid ? GetId(menuid) : GetId(showid + '_menu');
	if(isUndefined(pos) || !pos) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var important = pos.indexOf('!') != -1 ? 1 : 0;
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0, ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0, bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = fetchOffset(showObj);
		sx = sxy['left'];
		sy = sxy['top'];
		sw = showObj.offsetWidth;
		sh = showObj.offsetHeight;
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:
			bpl = sx;
			bpt = sy;
			break;
		case 2:
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			break;
		case 1:
			ml = bpl - mw;
			mt = bpt - mh;
			break;
		case 2:
			ml = bpl;
			mt = bpt - mh;
			break;
		case 3:
			ml = bpl;
			mt = bpt;
			break;
		case 4:
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(!important) {
		if(in_array(direction, [1, 4]) && ml < 0) {
			ml = bpl;
			if(in_array(basePoint, [1, 4])) ml += sw;
		} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
			ml = bpl - mw;
			if(in_array(basePoint, [2, 3])) {
				ml -= sw;
			} else if(basePoint == 4) {
				ml += sw;
			}
		}
		if(in_array(direction, [1, 2]) && mt < 0) {
			mt = bpt;
			if(in_array(basePoint, [1, 2])) mt += sh;
		} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
			mt = bpt - mh;
			if(in_array(basePoint, [3, 4])) mt -= sh;
		}
	}
	if(pos.substr(0, 3) == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	if(ml) menuObj.style.left = ml + 'px';
	if(mt) menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = GetId(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		var ctrlObj = '', ctrlclass = '';
		if((ctrlObj = GetId(menuObj.getAttribute('ctrlid'))) && (ctrlclass = menuObj.getAttribute('ctrlclass'))) {
			var reg = new RegExp(' ' + ctrlclass);
			ctrlObj.className = ctrlObj.className.replace(reg, '');
		}
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(menuObj.cache) {
				if(menuObj.style.visibility != 'hidden') {
					menuObj.style.display = 'none';
					if(menuObj.cover) GetId(attr + '_cover').style.display = 'none';
				}
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) GetId(attr + '_cover').parentNode.removeChild(GetId(attr + '_cover'));
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 20;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 40);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
}

function getCurrentStyle(obj, cssproperty, csspropertyNS) {
	if(obj.style[cssproperty]){
		return obj.style[cssproperty];
	}
	if (obj.currentStyle) {
		return obj.currentStyle[cssproperty];
	} else if (document.defaultView.getComputedStyle(obj, null)) {
		var currentStyle = document.defaultView.getComputedStyle(obj, null);
		var value = currentStyle.getPropertyValue(csspropertyNS);
		if(!value){
			value = currentStyle[cssproperty];
		}
		return value;
	} else if (window.getComputedStyle) {
		var currentStyle = window.getComputedStyle(obj, "");
		return currentStyle.getPropertyValue(csspropertyNS);
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			position = getCurrentStyle(obj, 'position', 'position');
			if(position == 'relative') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}

function showTip(ctrlobj) {
	F('_showTip', arguments);
}

function showPrompt(ctrlid, evt, msg, timeout) {
	F('_showPrompt', arguments);
}

function showCreditPrompt() {
	F('_showCreditPrompt', []);
}

var showDialogST = null;
function showDialog(msg, mode, t, func, cover, funccancel, leftmsg, confirmtxt, canceltxt, closetime, locationtime) {
	clearTimeout(showDialogST);
	cover = isUndefined(cover) ? (mode == 'info' ? 0 : 1) : cover;
	leftmsg = isUndefined(leftmsg) ? '' : leftmsg;
	mode = in_array(mode, ['confirm', 'notice', 'info', 'right']) ? mode : 'alert';
	var menuid = 'fwin_dialog';
	var menuObj = GetId(menuid);
	var showconfirm = 1;
	confirmtxtdefault = '确定';
	closetime = isUndefined(closetime) ? '' : closetime;
	closefunc = function () {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if(closetime) {
		leftmsg = closetime + ' 秒后窗口关闭';
		showDialogST = setTimeout(closefunc, closetime * 1000);
		showconfirm = 0;
	}
	locationtime = isUndefined(locationtime) ? '' : locationtime;
	if(locationtime) {
		leftmsg = locationtime + ' 秒后页面跳转';
		showDialogST = setTimeout(closefunc, locationtime * 1000);
		showconfirm = 0;
	}
	confirmtxt = confirmtxt ? confirmtxt : confirmtxtdefault;
	canceltxt = canceltxt ? canceltxt : '取消';

	if(menuObj) hideMenu('fwin_dialog', 'dialog');
	menuObj = document.createElement('div');
	menuObj.style.display = 'none';
	menuObj.className = 'fwinmask';
	menuObj.id = menuid;
	GetId('append_parent').appendChild(menuObj);
	var hidedom = '';
	if(!BROWSER.ie) {
		hidedom = '<style type="text/css">object{visibility:hidden;}</style>';
	}
	var s = hidedom + '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l">&nbsp;&nbsp;</td><td class="m_c"><h3 class="flb"><em>';
	s += t ? t : '提示信息';
	s += '</em><span><a href="javascript:;" id="fwin_dialog_close" class="flbc" onclick="hideMenu(\'' + menuid + '\', \'dialog\')" title="关闭">关闭</a></span></h3>';
	if(mode == 'info') {
		s += msg ? msg : '';
	} else {
		s += '<div class="c altw"><div class="' + (mode == 'alert' ? 'alert_error' : (mode == 'right' ? 'alert_right' : 'alert_info')) + '"><p>' + msg + '</p></div></div>';
		s += '<p class="o pns">' + (leftmsg ? '<span class="z xg1">' + leftmsg + '</span>' : '') + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="pn pnc"><strong>'+confirmtxt+'</strong></button>' : '');
		s += mode == 'confirm' ? '<button id="fwin_dialog_cancel" value="true" class="pn" onclick="hideMenu(\'' + menuid + '\', \'dialog\')"><strong>'+canceltxt+'</strong></button>' : '';
		s += '</p>';
	}
	s += '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
	menuObj.innerHTML = s;
	if(GetId('fwin_dialog_submit')) GetId('fwin_dialog_submit').onclick = function() {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if(GetId('fwin_dialog_cancel')) {
		GetId('fwin_dialog_cancel').onclick = function() {
			if(typeof funccancel == 'function') funccancel();
			else eval(funccancel);
			hideMenu(menuid, 'dialog');
		};
		GetId('fwin_dialog_close').onclick = GetId('fwin_dialog_cancel').onclick;
	}
	showMenu({'mtype':'dialog','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'cache':0,'cover':cover});
	try {
		if(GetId('fwin_dialog_submit')) GetId('fwin_dialog_submit').focus();
	} catch(e) {}
}

function showWindow(k, url, mode, cache, menuv) {
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = GetId(menuid);
	var drag = null;
	var loadingst = null;
	var hidedom = '';

	if(disallowfloat && disallowfloat.indexOf(k) != -1) {
		if(BROWSER.ie) url += (url.indexOf('?') != -1 ?  '&' : '?') + 'referer=' + escape(location.href);
		location.href = url;
		doane();
		return;
	}

	var fetchContent = function() {
		if(mode == 'get') {
			menuObj.url = url;
			url += (url.search(/\?/) > 0 ? '&' : '?') + 'infloat=yes&handlekey=' + k;
			url += cache == -1 ? '&t='+(+ new Date()) : '';
			if(BROWSER.ie &&  url.indexOf('referer=') < 0) {
				url = url + '&referer=' + encodeURIComponent(location);
			}
			ajaxget(url, 'fwin_content_' + k, null, '', '', function() {initMenu();show();});
		} else if(mode == 'post') {
			menuObj.act = GetId(url).action;
			ajaxpost(url, 'fwin_content_' + k, '', '', '', function() {initMenu();show();});
		}
		if(parseInt(BROWSER.ie) != 6) {
			loadingst = setTimeout(function() {showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> 请稍候...')}, 500);
		}
	};
	var initMenu = function() {
		clearTimeout(loadingst);
		var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', k);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				drag = objs[i].id;
				fctrlidinit = true;
			}
		}
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		v = {'mtype':'win','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['win'],'drag':typeof drag == null ? '' : drag,'cache':cache};
		for(k in menuv) {
			v[k] = menuv[k];
		}
		showMenu(v);
	};

	if(!menuObj) {
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = 'fwinmask';
		menuObj.style.display = 'none';
		GetId('append_parent').appendChild(menuObj);
		evt = ' style="cursor:move" onmousedown="dragMenu(GetId(\'' + menuid + '\'), event, 1)" ondblclick="hideWindow(\'' + k + '\')"';
		if(!BROWSER.ie) {
			hidedom = '<style type="text/css">object{visibility:hidden;}</style>';
		}
		menuObj.innerHTML = hidedom + '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"' + evt + '></td><td class="t_r"></td></tr><tr><td class="m_l"' + evt + ')">&nbsp;&nbsp;</td><td class="m_c" id="fwin_content_' + k + '">'
			+ '</td><td class="m_r"' + evt + '"></td></tr><tr><td class="b_l"></td><td class="b_c"' + evt + '></td><td class="b_r"></td></tr></table>';
		if(mode == 'html') {
			GetId('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		} else {
			fetchContent();
		}
	} else if((mode == 'get' && (url != menuObj.url || cache != 1)) || (mode == 'post' && GetId(url).action != menuObj.act)) {
		fetchContent();
	} else {
		show();
	}
	doane();
}

function showError(msg) {
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	msg = msg.replace(p, '');
	if(msg !== '') {
		showDialog(msg, 'alert', '错误信息', null, true, null, '', '', '', 3);
	}
}

function hideWindow(k, all, clear) {
	all = isUndefined(all) ? 1 : all;
	clear = isUndefined(clear) ? 1 : clear;
	hideMenu('fwin_' + k, 'win');
	if(clear && GetId('fwin_' + k)) {
		GetId('append_parent').removeChild(GetId('fwin_' + k));
	}
	if(all) {
		hideMenu();
	}
}

function AC_FL_RunContent() {
	var str = '';
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if(BROWSER.ie && !BROWSER.opera) {
		str += '<object ';
		for (var i in ret.objAttrs) {
			str += i + '="' + ret.objAttrs[i] + '" ';
		}
		str += '>';
		for (var i in ret.params) {
			str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
		}
		str += '</object>';
	} else {
		str += '<embed ';
		for (var i in ret.embedAttrs) {
			str += i + '="' + ret.embedAttrs[i] + '" ';
		}
		str += '></embed>';
	}
	return str;
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}

function simulateSelect(selectId, widthvalue) {
	var selectObj = GetId(selectId);
	if(!selectObj) return;
	if(BROWSER.other) {
		if(selectObj.getAttribute('change')) {
			selectObj.onchange = function () {eval(selectObj.getAttribute('change'));}
		}
		return;
	}
	var widthvalue = widthvalue ? widthvalue : 70;
	var defaultopt = selectObj.options[0] ? selectObj.options[0].innerHTML : '';
	var defaultv = '';
	var menuObj = document.createElement('div');
	var ul = document.createElement('ul');
	var handleKeyDown = function(e) {
		e = BROWSER.ie ? event : e;
		if(e.keyCode == 40 || e.keyCode == 38) doane(e);
	};
	var selectwidth = (selectObj.getAttribute('width', i) ? selectObj.getAttribute('width', i) : widthvalue) + 'px';
	var tabindex = selectObj.getAttribute('tabindex', i) ? selectObj.getAttribute('tabindex', i) : 1;

	for(var i = 0; i < selectObj.options.length; i++) {
		var li = document.createElement('li');
		li.innerHTML = selectObj.options[i].innerHTML;
		li.k_id = i;
		li.k_value = selectObj.options[i].value;
		if(selectObj.options[i].selected) {
			defaultopt = selectObj.options[i].innerHTML;
			defaultv = selectObj.options[i].value;
			li.className = 'current';
			selectObj.setAttribute('selecti', i);
		}
		li.onclick = function() {
			if(GetId(selectId + '_ctrl').innerHTML != this.innerHTML) {
				var lis = menuObj.getElementsByTagName('li');
				lis[GetId(selectId).getAttribute('selecti')].className = '';
				this.className = 'current';
				GetId(selectId + '_ctrl').innerHTML = this.innerHTML;
				GetId(selectId).setAttribute('selecti', this.k_id);
				GetId(selectId).options.length = 0;
				GetId(selectId).options[0] = new Option('', this.k_value);
				eval(selectObj.getAttribute('change'));
			}
			hideMenu(menuObj.id);
			return false;
		};
		ul.appendChild(li);
	}

	selectObj.options.length = 0;
	selectObj.options[0]= new Option('', defaultv);
	selectObj.style.display = 'none';
	selectObj.outerHTML += '<a href="javascript:;" id="' + selectId + '_ctrl" style="width:' + selectwidth + '" tabindex="' + tabindex + '">' + defaultopt + '</a>';

	menuObj.id = selectId + '_ctrl_menu';
	menuObj.className = 'sltm';
	menuObj.style.display = 'none';
	menuObj.style.width = selectwidth;
	menuObj.appendChild(ul);
	GetId('append_parent').appendChild(menuObj);

	GetId(selectId + '_ctrl').onclick = function(e) {
		GetId(selectId + '_ctrl_menu').style.width = selectwidth;
		showMenu({'ctrlid':(selectId == 'loginfield' ? 'account' : selectId + '_ctrl'),'menuid':selectId + '_ctrl_menu','evt':'click','pos':'43'});
		doane(e);
	};
	GetId(selectId + '_ctrl').onfocus = menuObj.onfocus = function() {
		_attachEvent(document.body, 'keydown', handleKeyDown);
	};
	GetId(selectId + '_ctrl').onblur = menuObj.onblur = function() {
		_detachEvent(document.body, 'keydown', handleKeyDown);
	};
	GetId(selectId + '_ctrl').onkeyup = function(e) {
		e = e ? e : window.event;
		value = e.keyCode;
		if(value == 40 || value == 38) {
			if(menuObj.style.display == 'none') {
				GetId(selectId + '_ctrl').onclick();
			} else {
				lis = menuObj.getElementsByTagName('li');
				selecti = selectObj.getAttribute('selecti');
				lis[selecti].className = '';
				if(value == 40) {
					selecti = parseInt(selecti) + 1;
				} else if(value == 38) {
					selecti = parseInt(selecti) - 1;
				}
				if(selecti < 0) {
					selecti = lis.length - 1
				} else if(selecti > lis.length - 1) {
					selecti = 0;
				}
				lis[selecti].className = 'current';
				selectObj.setAttribute('selecti', selecti);
				lis[selecti].parentNode.scrollTop = lis[selecti].offsetTop;
			}
		} else if(value == 13) {
			var lis = menuObj.getElementsByTagName('li');
			lis[selectObj.getAttribute('selecti')].onclick();
		} else if(value == 27) {
			hideMenu(menuObj.id);
		}
	};
}

function switchTab(prefix, current, total, activeclass) {
	F('_switchTab', arguments);
}

function imageRotate(imgid, direct) {
	F('_imageRotate', arguments);
}

function thumbImg(obj, method) {
	if(!obj) {
		return;
	}
	obj.onload = null;
	file = obj.src;
	zw = obj.offsetWidth;
	zh = obj.offsetHeight;
	if(zw < 2) {
		if(!obj.id) {
			obj.id = 'img_' + Math.random();
		}
		setTimeout("thumbImg(GetId('" + obj.id + "'), " + method + ")", 100);
		return;
	}
	zr = zw / zh;
	method = !method ? 0 : 1;
	if(method) {
		fixw = obj.getAttribute('_width');
		fixh = obj.getAttribute('_height');
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
		}
		if(zh > fixh) {
			zh = fixh;
			zw = zh * zr;
		}
	} else {
		fixw = typeof imagemaxwidth == 'undefined' ? '600' : imagemaxwidth;
		if(zw > fixw) {
			zw = fixw;
			zh = zw / zr;
			obj.style.cursor = 'pointer';
			if(!obj.onclick) {
				obj.onclick = function() {
					zoom(obj, obj.src);
				};
			}
		}
	}
	obj.width = zw;
	obj.height = zh;
}

var zoomstatus = 1;
function zoom(obj, zimg, nocover, pn, showexif) {
	F('_zoom', arguments);
}

function showselect(obj, inpid, t, rettype) {
	F('_showselect', arguments);
}

function showColorBox(ctrlid, layer, k, bgcolor) {
	F('_showColorBox', arguments);
}

function ctrlEnter(event, btnId, onlyEnter) {
	if(isUndefined(onlyEnter)) onlyEnter = 0;
	if((event.ctrlKey || onlyEnter) && event.keyCode == 13) {
		GetId(btnId).click();
		return false;
	}
	return true;
}

function parseurl(str, mode, parsecode) {
	if(isUndefined(parsecode)) parsecode = true;
	if(parsecode) str= str.replace(/\s*\[code\]([\s\S]+?)\[\/code\]\s*/ig, function($1, $2) {return codetag($2);});
	str = str.replace(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig, mode == 'html' ? '$1<a href="$2" target="_blank">$2</a>' : '$1[url]$2[/url]');
	str = str.replace(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig, mode == 'html' ? '$1<a href="mailto:$2">$2</a>' : '$1[email]$2[/email]');
	if(parsecode) {
		for(var i = 0; i <= DISCUZCODE['num']; i++) {
			str = str.replace("[\tDISCUZ_CODE_" + i + "\t]", DISCUZCODE['html'][i]);
		}
	}
	return str;
}

function codetag(text) {
	DISCUZCODE['num']++;
	if(typeof wysiwyg != 'undefined' && wysiwyg) text = text.replace(/<br[^\>]*>/ig, '\n');
	DISCUZCODE['html'][DISCUZCODE['num']] = '[code]' + text + '[/code]';
	return '[\tDISCUZ_CODE_' + DISCUZCODE['num'] + '\t]';
}

function saveUserdata(name, data) {
	try {
		if(window.localStorage){
			localStorage.setItem('Discuz_' + name, data);
		} else if(window.sessionStorage){
			sessionStorage.setItem('Discuz_' + name, data);
		}
	} catch(e) {
		if(BROWSER.ie){
			if(data.length < 54889) {
				with(document.documentElement) {
					setAttribute("value", data);
					save('Discuz_' + name);
				}
			}
		}
	}
	setcookie('clearUserdata', '', -1);
}

function loadUserdata(name) {
	if(window.localStorage){
		return localStorage.getItem('Discuz_' + name);
	} else if(window.sessionStorage){
		return sessionStorage.getItem('Discuz_' + name);
	} else if(BROWSER.ie){
		with(document.documentElement) {
			load('Discuz_' + name);
			return getAttribute("value");
		}
	}
}

function initTab(frameId, type) {
	F('_initTab', arguments);
}

function openDiy(){
	window.location.href = ((window.location.href + '').replace(/[\?\&]diy=yes/g, '').split('#')[0] + ( window.location.search && window.location.search.indexOf('?diy=yes') < 0 ? '&diy=yes' : '?diy=yes'));
}

function hasClass(elem, className) {
	return elem.className && (" " + elem.className + " ").indexOf(" " + className + " ") != -1;
}

function runslideshow() {
	F('_runslideshow', []);
}

function toggle_collapse(objname, noimg, complex, lang) {
	F('_toggle_collapse', arguments);
}

function updatestring(str1, str2, clear) {
	str2 = '_' + str2 + '_';
	return clear ? str1.replace(str2, '') : (str1.indexOf(str2) == -1 ? str1 + str2 : str1);
}

function getClipboardData() {
	window.document.clipboardswf.SetVariable('str', CLIPBOARDSWFDATA);
}

function setCopy(text, msg) {
	F('_setCopy', arguments);
}

function copycode(obj) {
	F('_copycode', arguments);
}

function showdistrict(container, elems, totallevel, changelevel, containertype) {
	F('_showdistrict', arguments);
}

function setDoodle(fid, oid, url, tid, from) {
	F('_setDoodle', arguments);
}


function initSearchmenu(searchform, cloudSearchUrl) {
	var defaultUrl = 'search.php?searchsubmit=yes';
	if(typeof cloudSearchUrl == "undefined" || cloudSearchUrl == null || cloudSearchUrl == '') {
		cloudSearchUrl = defaultUrl;
	}

	var searchtxt = GetId(searchform + '_txt');
	if(!searchtxt) {
		searchtxt = GetId(searchform);
	}
	var tclass = searchtxt.className;
	searchtxt.className = tclass + ' xg1';
	if (!!("placeholder" in document.createElement("input"))) {
		if(searchtxt.value == '请输入搜索内容') {
			searchtxt.value = '';
		}
		searchtxt.placeholder = '请输入搜索内容';
	} else {
		searchtxt.onfocus = function () {
			if(searchtxt.value == '请输入搜索内容') {
				searchtxt.value = '';
				searchtxt.className = tclass;
			}
		};
		searchtxt.onblur = function () {
			if(searchtxt.value == '' ) {
				searchtxt.value = '请输入搜索内容';
				searchtxt.className = tclass + ' xg1';
			}
		};
	}
	if(!GetId(searchform + '_type_menu')) return false;
	var o = GetId(searchform + '_type');
	var a = GetId(searchform + '_type_menu').getElementsByTagName('a');
	var formobj = searchtxt.form;
	for(var i=0; i<a.length; i++){
		if(a[i].className == 'curtype'){
			o.innerHTML = a[i].innerHTML;
			GetId(searchform + '_mod').value = a[i].rel;
			formobj.method = 'post';
			if((a[i].rel == 'forum' || a[i].rel == 'curforum') && defaultUrl != cloudSearchUrl) {
				formobj.action = cloudSearchUrl;
				formobj.method = 'get';
				if(GetId('srchFId')) {
					GetId('srchFId').value = a[i].rel == 'forum' ? 0 : a[i].getAttribute('fid');
				}
			} else {
				formobj.action = defaultUrl;
			}
		}
		a[i].onclick = function(){
			o.innerHTML = this.innerHTML;
			GetId(searchform + '_mod').value = this.rel;
			formobj.method = 'post';
			if((this.rel == 'forum' || this.rel == 'curforum') && defaultUrl != cloudSearchUrl) {
				formobj.action = cloudSearchUrl;
				formobj.method = 'get';
				if(GetId('srchFId')) {
					GetId('srchFId').value = this.rel == 'forum' ? 0 : this.getAttribute('fid');
				}
			} else {
				formobj.action = defaultUrl;
			}
		};
	}
}

function searchFocus(obj) {
	if(obj.value == '请输入搜索内容') {
		obj.value = '';
	}
	if(GetId('cloudsearchquery') != null) {
		GetId('cloudsearchquery').value = obj.value;
	}
}

function extstyle(css) {
	F('_extstyle', arguments);
}

function widthauto(obj) {
	F('_widthauto', arguments);
}

var secST = new Array();
function updatesecqaa(idhash) {
	F('_updatesecqaa', arguments);
}

function updateseccode(idhash, play) {
	F('_updateseccode', arguments);
}

function checksec(type, idhash, showmsg, recall) {
	F('_checksec', arguments);
}

function createPalette(colorid, id, func) {
	F('_createPalette', arguments);
}

function showForummenu(fid) {
	F('_showForummenu', arguments);
}

function cardInit() {
	var cardShow = function (obj) {
		if(BROWSER.ie && BROWSER.ie < 7 && obj.href.indexOf('username') != -1) {
			return;
		}
		pos = obj.getAttribute('c') == '1' ? '43' : obj.getAttribute('c');
		USERCARDST = setTimeout(function() {ajaxmenu(obj, 500, 1, 2, pos, null, 'p_pop card');}, 250);
	};
	var cardids = {};
	var a = document.body.getElementsByTagName('a');
	for(var i = 0;i < a.length;i++){
		if(a[i].getAttribute('c')) {
			var href = a[i].getAttribute('href', 1);
			if(typeof cardids[href] == 'undefined') {
				cardids[href] = Math.round(Math.random()*10000);
			}
			a[i].setAttribute('mid', 'card_' + cardids[href]);
			a[i].onmouseover = function() {cardShow(this)};
			a[i].onmouseout = function() {clearTimeout(USERCARDST);};
		}
	}
}

function navShow(id) {
	var mnobj = GetId('snav_mn_' + id);
	if(!mnobj) {
		return;
	}
	var uls = GetId('mu').getElementsByTagName('ul');
	for(i = 0;i < uls.length;i++) {
		if(uls[i].className != 'cl current') {
			uls[i].style.display = 'none';
		}
	}
	if(mnobj.className != 'cl current') {
		showMenu({'ctrlid':'mn_' + id,'menuid':'snav_mn_' + id,'pos':'*'});
		mnobj.className = 'cl floatmu';
		mnobj.style.width = (GetId('nv').clientWidth) + 'px';
		mnobj.style.display = '';
	}
}

function strLenCalc(obj, checklen, maxlen) {
	var v = obj.value, charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = strlen(v);
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen -= charset == 'utf-8' ? 2 : 1;
		}
	}
	if(curlen >= len) {
		GetId(checklen).innerHTML = curlen - len;
	} else {
		obj.value = mb_cutstr(v, maxlen, 0);
	}
}

function patchNotice() {
	if(GetId('patch_notice')) {
		ajaxget('misc.php?mod=patch&action=patchnotice', 'patch_notice', '');
	}
}

function pluginNotice() {
	if(GetId('plugin_notice')) {
		ajaxget('misc.php?mod=patch&action=pluginnotice', 'plugin_notice', '');
	}
}

function noticeTitle() {
	NOTICETITLE = {'State':0, 'oldTitle':document.title, flashNumber:0, sleep:15};
	if(!getcookie('noticeTitle')) {
		window.setInterval('noticeTitleFlash();', 500);
	} else {
		window.setTimeout('noticeTitleFlash();', 500);
	}
	setcookie('noticeTitle', 1, 600);
}

function noticeTitleFlash() {
	if(NOTICETITLE.flashNumber < 5 || NOTICETITLE.flashNumber > 4 && !NOTICETITLE['State']) {
		document.title = (NOTICETITLE['State'] ? '【　　　】' : '【新提醒】') + NOTICETITLE['oldTitle'];
		NOTICETITLE['State'] = !NOTICETITLE['State'];
	}
	NOTICETITLE.flashNumber = NOTICETITLE.flashNumber < NOTICETITLE.sleep ? ++NOTICETITLE.flashNumber : 0;
}

function relatedlinks(rlinkmsgid) {
	F('_relatedlinks', arguments);
}

function con_handle_response(response) {
	return response;
}

function showTopLink() {
	var ft = GetId('ft');
	if(ft){
		var scrolltop = GetId('scrolltop');
		var viewPortHeight = parseInt(document.documentElement.clientHeight);
		var scrollHeight = parseInt(document.body.getBoundingClientRect().top);
		var basew = parseInt(ft.clientWidth);
		var sw = scrolltop.clientWidth;
		if (basew < 1000) {
			var left = parseInt(fetchOffset(ft)['left']);
			left = left < sw ? left * 2 - sw : left;
			scrolltop.style.left = ( basew + left ) + 'px';
		} else {
			scrolltop.style.left = 'auto';
			scrolltop.style.right = 0;
		}

		if (BROWSER.ie && BROWSER.ie < 7) {
			scrolltop.style.top = viewPortHeight - scrollHeight - 150 + 'px';
		}
		if (scrollHeight < -100) {
			scrolltop.style.visibility = 'visible';
		} else {
			scrolltop.style.visibility = 'hidden';
		}
	}
}

function showCreditmenu() {
	F('_showCreditmenu', []);
}

function showUpgradeinfo() {
	showMenu({'ctrlid':'g_upmine', 'pos':'21'});
}

function addFavorite(url, title) {
	try {
		window.external.addFavorite(url, title);
	} catch (e){
		try {
			window.sidebar.addPanel(title, url, '');
        	} catch (e) {
			showDialog("请按 Ctrl+D 键添加到收藏夹", 'notice');
		}
	}
}

function setHomepage(sURL) {
	if(BROWSER.ie){
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(sURL);
	} else {
		showDialog("非 IE 浏览器请手动将本站设为首页", 'notice');
		doane();
	}
}

function smilies_show(id, smcols, seditorkey) {
	F('_smilies_show', arguments, 'smilies');
}

function showfocus(ftype, autoshow) {
	var id = parseInt(GetId('focuscur').innerHTML);
	if(ftype == 'prev') {
		id = (id-1) < 1 ? focusnum : (id-1);
		if(!autoshow) {
			window.clearInterval(focusautoshow);
		}
	} else if(ftype == 'next') {
		id = (id+1) > focusnum ? 1 : (id+1);
		if(!autoshow) {
			window.clearInterval(focusautoshow);
		}
	}
	GetId('focuscur').innerHTML = id;
	GetId('focus_con').innerHTML = GetId('focus_'+(id-1)).innerHTML;
}

function rateStarHover(target,level) {
	if(level ==  0) {
		GetId(target).style.width = '';
	} else {
		GetId(target).style.width = level * 16 + 'px';
	}
}
function rateStarSet(target,level,input) {
	GetId(input).value = level;
	GetId(target).className = 'star star' + level;
}

function img_onmouseoverfunc(obj) {
	if(typeof showsetcover == 'function') {
		showsetcover(obj);
	}
	return;
}

function toggleBlind(dom) {
	if(dom) {
		if(loadUserdata('is_blindman')) {
			saveUserdata('is_blindman', '');
			dom.title = '开启辅助访问';
		} else {
			saveUserdata('is_blindman', '1');
			dom.title = '关闭辅助访问';
		}
	}
}

function checkBlind() {
	var dom = GetId('switchblind');
	if(dom) {
		if(loadUserdata('is_blindman')) {
			dom.title = '关闭辅助访问';
		} else {
			dom.title = '开启辅助访问';
		}
	}
}

if(typeof IN_ADMINCP == 'undefined') {
	if(creditnotice != '' && getcookie('creditnotice')) {
		_attachEvent(window, 'load', showCreditPrompt, document);
	}
	if(typeof showusercard != 'undefined' && showusercard == 1) {
		_attachEvent(window, 'load', cardInit, document);
	}
}

if(BROWSER.ie) {
	document.documentElement.addBehavior("#default#userdata");
}

/* common_extra.js */

function _relatedlinks(rlinkmsgid) {
	if(!GetId(rlinkmsgid) || GetId(rlinkmsgid).innerHTML.match(/<script[^\>]*?>/i)) {
		return;
	}
	var alink = new Array(), ignore = new Array();
	var i = 0;
	var msg = GetId(rlinkmsgid).innerHTML;
	msg = msg.replace(/(<ignore_js_op\>[\s|\S]*?<\/ignore_js_op\>)/ig, function($1) {
		ignore[i] = $1;
		i++;
		return '#ignore_js_op '+(i - 1)+'#';
	});
	var alink_i = 0;
	msg = msg.replace(/(<a.*?<\/a\>)/ig, function($1) {
		alink[alink_i] = $1;
		alink_i++;
		return '#alink '+(alink_i - 1)+'#';
	});
	var relatedid = new Array();
	msg = msg.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3) {
		for(var j = 0; j < relatedlink.length; j++) {
			if(relatedlink[j] && !relatedid[j]) {
				if(relatedlink[j]['surl'] != '') {
					var ra = '<a href="'+relatedlink[j]['surl']+'" target="_blank" class="relatedlink">'+relatedlink[j]['sname']+'</a>';
						alink[alink_i] = ra;
						ra = '#alink '+alink_i+'#';
						alink_i++;
				} else {
					var ra = '<strong><font color="#FF0000">'+relatedlink[j]['sname']+'</font></strong>';
				}
				var $rtmp = $3;
				$3 = $3.replace(relatedlink[j]['sname'], ra);
				if($3 != $rtmp) {
					relatedid[j] = 1;
				}
			}
		}
		return $2 + $3;
    	});

	for(var k in alink) {
		msg = msg.replace('#alink '+k+'#', alink[k]);
	}

	for(var l in ignore) {
		msg = msg.replace('#ignore_js_op '+l+'#', ignore[l]);
	}
	GetId(rlinkmsgid).innerHTML = msg;
}

function _updatesecqaa(idhash) {
	if(GetId('secqaa_' + idhash)) {
		GetId('secqaaverify_' + idhash).value = '';
		if(secST['qaa_' + idhash]) {
			clearTimeout(secST['qaa_' + idhash]);
		}
		GetId('checksecqaaverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
		ajaxget('misc.php?mod=secqaa&action=update&idhash=' + idhash, 'secqaa_' + idhash, null, '', '', function() {
			secST['qaa_' + idhash] = setTimeout(function() {GetId('secqaa_' + idhash).innerHTML = '<span class="xi2 cur1" onclick="updatesecqaa(\''+idhash+'\')">刷新验证问答</span>';}, 180000);
		});
	}
}

function _updateseccode(idhash, play) {
	if(isUndefined(play)) {
		if(GetId('seccode_' + idhash)) {
			GetId('seccodeverify_' + idhash).value = '';
			if(secST['code_' + idhash]) {
				clearTimeout(secST['code_' + idhash]);
			}
			GetId('checkseccodeverify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/none.gif" width="16" height="16" class="vm" />';
			ajaxget('misc.php?mod=seccode&action=update&idhash=' + idhash, 'seccode_' + idhash, null, '', '', function() {
				secST['code_' + idhash] = setTimeout(function() {GetId('seccode_' + idhash).innerHTML = '<span class="xi2 cur1" onclick="updateseccode(\''+idhash+'\')">刷新验证码</span>';}, 180000);
			});
		}
	} else {
		eval('window.document.seccodeplayer_' + idhash + '.SetVariable("isPlay", "1")');
	}
}

function _checksec(type, idhash, showmsg, recall) {
	var showmsg = !showmsg ? 0 : showmsg;
	var secverify = GetId('sec' + type + 'verify_' + idhash).value;
	if(!secverify) {
		return;
	}
	var x = new Ajax('XML', 'checksec' + type + 'verify_' + idhash);
	x.loading = '';
	GetId('checksec' + type + 'verify_' + idhash).innerHTML = '<img src="'+ IMGDIR + '/loading.gif" width="16" height="16" class="vm" />';
	x.get('misc.php?mod=sec' + type + '&action=check&inajax=1&&idhash=' + idhash + '&secverify=' + (BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(secverify) : secverify), function(s){
		var obj = GetId('checksec' + type + 'verify_' + idhash);
		obj.style.display = '';
		if(s.substr(0, 7) == 'succeed') {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_right.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				recall(1);
			}
		} else {
			obj.innerHTML = '<img src="'+ IMGDIR + '/check_error.gif" width="16" height="16" class="vm" />';
			if(showmsg) {
				if(type == 'code') {
					showError('验证码错误，请重新填写');
				} else if(type == 'qaa') {
					showError('验证问答错误，请重新填写');
				}
				recall(0);
			}
		}
	});
}

function _setDoodle(fid, oid, url, tid, from) {
	if(tid == null) {
		hideWindow(fid);
	} else {
		GetId(tid).style.display = '';
		GetId(fid).style.display = 'none';
	}
	var doodleText = '[img]'+url+'[/img]';
	if(GetId(oid) != null) {
		if(from == "editor") {
			insertImage(url);
		} else if(from == "fastpost") {
			seditor_insertunit('fastpost', doodleText);
		} else if(from == "forumeditor") {
			if(wysiwyg) {
				insertText('<img src="' + url + '" border="0" alt="" />', false);
			} else {
				insertText(doodleText, strlen(doodleText), 0);
			}
		} else {
			insertContent(oid, doodleText);
		}
	}
}

function _showdistrict(container, elems, totallevel, changelevel, containertype) {
	var getdid = function(elem) {
		var op = elem.options[elem.selectedIndex];
		return op['did'] || op.getAttribute('did') || '0';
	};
	var pid = changelevel >= 1 && elems[0] && GetId(elems[0]) ? getdid(GetId(elems[0])) : 0;
	var cid = changelevel >= 2 && elems[1] && GetId(elems[1]) ? getdid(GetId(elems[1])) : 0;
	var did = changelevel >= 3 && elems[2] && GetId(elems[2]) ? getdid(GetId(elems[2])) : 0;
	var coid = changelevel >= 4 && elems[3] && GetId(elems[3]) ? getdid(GetId(elems[3])) : 0;
	var url = "home.php?mod=misc&ac=ajax&op=district&container="+container+"&containertype="+containertype
		+"&province="+elems[0]+"&city="+elems[1]+"&district="+elems[2]+"&community="+elems[3]
		+"&pid="+pid + "&cid="+cid+"&did="+did+"&coid="+coid+'&level='+totallevel+'&handlekey='+container+'&inajax=1'+(!changelevel ? '&showdefault=1' : '');
	ajaxget(url, container, '');
}

function _copycode(obj) {
	if(!obj) return false;
	if(window.getSelection) {
		var sel = window.getSelection();
		if (sel.setBaseAndExtent) {
			sel.setBaseAndExtent(obj, 0, obj, 1);
		} else {
			var rng = document.createRange();
			rng.selectNodeContents(obj);
			sel.addRange(rng);
		}
	} else {
		var rng = document.body.createTextRange();
		rng.moveToElementText(obj);
		rng.select();
	}
	setCopy(BROWSER.ie ? obj.innerText.replace(/\r\n\r\n/g, '\r\n') : obj.textContent, '代码已复制到剪贴板');
}

function _setCopy(text, msg){
	if(BROWSER.ie) {
		var r = clipboardData.setData('Text', text);
		if(r) {
			if(msg) {
				showPrompt(null, null, '<span>' + msg + '</span>', 1500);
			}
		} else {
			showDialog('<div class="c"><div style="width: 200px; text-align: center;">复制失败，请选择“允许访问”</div></div>', 'alert');
		}
	} else {
		var msg = '<div class="c"><div style="width: 200px; text-align: center; text-decoration:underline;">点此复制到剪贴板</div>' +
		AC_FL_RunContent('id', 'clipboardswf', 'name', 'clipboardswf', 'devicefont', 'false', 'width', '200', 'height', '40', 'src', STATICURL + 'image/common/clipboard.swf', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent', 'style' , 'margin-top:-20px') + '</div>';
		showDialog(msg, 'info');
		text = text.replace(/[\xA0]/g, ' ');
		CLIPBOARDSWFDATA = text;
	}
}

function _showselect(obj, inpid, t, rettype) {
	var showselect_row = function (inpid, s, v, notime, rettype) {
		if(v >= 0) {
			if(!rettype) {
				var notime = !notime ? 0 : 1;
				var t = today.getTime();
				t += 86400000 * v;
				var d = new Date();
				d.setTime(t);
				var month = d.getMonth() + 1;
				month = month < 10 ? '0' + month : month;
				var day = d.getDate();
				day = day < 10 ? '0' + day : day;
				var hour = d.getHours();
				hour = hour < 10 ? '0' + hour : hour;
				var minute = d.getMinutes();
				minute = minute < 10 ? '0' + minute : minute;
				return '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').value = \'' + d.getFullYear() + '-' + month + '-' + day + (!notime ? ' ' + hour + ':' + minute: '') + '\'">' + s + '</a>';
			} else {
				return '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').value = \'' + v + '\'">' + s + '</a>';
			}
		} else if(v == -1) {
			return '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').focus()">' + s + '</a>';
		} else if(v == -2) {
			return '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').onclick()">' + s + '</a>';
		}
	};

	if(!obj.id) {
		var t = !t ? 0 : t;
		var rettype = !rettype ? 0 : rettype;
		obj.id = 'calendarexp_' + Math.random();
		div = document.createElement('div');
		div.id = obj.id + '_menu';
		div.style.display = 'none';
		div.className = 'p_pop';
		GetId('append_parent').appendChild(div);
		s = '';
		if(!t) {
			s += showselect_row(inpid, '一天', 1, 0, rettype);
			s += showselect_row(inpid, '一周', 7, 0, rettype);
			s += showselect_row(inpid, '一个月', 30, 0, rettype);
			s += showselect_row(inpid, '三个月', 90, 0, rettype);
			s += showselect_row(inpid, '自定义', -2);
		} else {
			if(GetId(t)) {
				var lis = GetId(t).getElementsByTagName('LI');
				for(i = 0;i < lis.length;i++) {
					s += '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').value = this.innerHTML;GetId(\''+obj.id+'_menu\').style.display=\'none\'">' + lis[i].innerHTML + '</a>';
				}
				s += showselect_row(inpid, '自定义', -1);
			} else {
				s += '<a href="javascript:;" onclick="GetId(\'' + inpid + '\').value = \'0\'">永久</a>';
				s += showselect_row(inpid, '7 天', 7, 1, rettype);
				s += showselect_row(inpid, '14 天', 14, 1, rettype);
				s += showselect_row(inpid, '一个月', 30, 1, rettype);
				s += showselect_row(inpid, '三个月', 90, 1, rettype);
				s += showselect_row(inpid, '半年', 182, 1, rettype);
				s += showselect_row(inpid, '一年', 365, 1, rettype);
				s += showselect_row(inpid, '自定义', -1);
			}
		}
		GetId(div.id).innerHTML = s;
	}
	showMenu({'ctrlid':obj.id,'evt':'click'});
	if(BROWSER.ie && BROWSER.ie < 7) {
		doane(event);
	}
}

function _zoom(obj, zimg, nocover, pn, showexif) {
	zimg = !zimg ? obj.src : zimg;
	showexif = !parseInt(showexif) ? 0 : showexif;
	if(!zoomstatus) {
		window.open(zimg, '', '');
		return;
	}
	if(!obj.id) obj.id = 'img_' + Math.random();
	var faid = !obj.getAttribute('aid') ? 0 : obj.getAttribute('aid');
	var menuid = 'imgzoom';
	var menu = GetId(menuid);
	var zoomid = menuid + '_zoom';
	var imgtitle = !nocover && obj.title ? '<div class="imgzoom_title">' + obj.title + '</div>' +
		(showexif ? '<div id="' + zoomid + '_exif" class="imgzoom_exif" onmouseover="this.className=\'imgzoom_exif imgzoom_exif_hover\'" onmouseout="this.className=\'imgzoom_exif\'"></div>' : '')
		: '';
	var cover = !nocover ? 1 : 0;
	var pn = !pn ? 0 : 1;
	var maxh = (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight) - 70;
	var loadCheck = function (obj) {
		if(obj.complete) {
			var imgw = loading.width;
			var imgh = loading.height;
			var r = imgw / imgh;
			var w = document.body.clientWidth * 0.95;
			w = imgw > w ? w : imgw;
			var h = w / r;
			if(w < 100 & h < 100) {
				GetId(menuid + '_waiting').style.display = 'none';
				hideMenu();
				return;
			}
			if(h > maxh) {
				h = maxh;
				w = h * r;
			}
			if(GetId(menuid)) {
				GetId(menuid).removeAttribute('top_');GetId(menuid).removeAttribute('left_');
				clearTimeout(GetId(menuid).getAttribute('timer'));
			}
			showimage(zimg, w, h, imgw, imgh);
			if(showexif && faid) {
				var x = new Ajax();
				x.get('forum.php?mod=ajax&action=exif&aid=' + faid + '&inajax=1', function(s, x) {
					if(s) {
						GetId(zoomid + '_exif').style.display = '';
						GetId(zoomid + '_exif').innerHTML = s;
					} else {
						GetId(zoomid + '_exif').style.display = 'none';
					}
				});
			}
		} else {
			setTimeout(function () { loadCheck(loading); }, 100);
		}
	};
	var showloading = function (zimg, pn) {
		if(!pn) {
			if(!GetId(menuid + '_waiting')) {
				waiting = document.createElement('img');
				waiting.id = menuid + '_waiting';
				waiting.src = '/Public/img/imageloading.gif';
				waiting.style.opacity = '0.8';
				waiting.style.filter = 'alpha(opacity=80)';
				waiting.style.position = 'absolute';
				waiting.style.zIndex = '100000';
				GetId('append_parent').appendChild(waiting);
			}
		}
		GetId(menuid + '_waiting').style.display = '';
		GetId(menuid + '_waiting').style.left = (document.body.clientWidth - 42) / 2 + 'px';
		GetId(menuid + '_waiting').style.top = ((document.documentElement.clientHeight - 42) / 2 + Math.max(document.documentElement.scrollTop, document.body.scrollTop)) + 'px';
		loading = new Image();
		setTimeout(function () { loadCheck(loading); }, 100);
		if(!pn) {
			GetId(menuid + '_zoomlayer').style.display = 'none';
		}
		loading.src = zimg;
	};
	var adjustpn = function(h) {
		h = h < 90 ? 90 : h;
		if(GetId('zimg_prev')) {
			GetId('zimg_prev').style.height= parseInt(h) + 'px';
		}
		if(GetId('zimg_next')) {
			GetId('zimg_next').style.height= parseInt(h) + 'px';
		}
	};
	var showimage = function (zimg, w, h, imgw, imgh) {
		GetId(menuid + '_waiting').style.display = 'none';
		GetId(menuid + '_zoomlayer').style.display = '';
		GetId(menuid + '_img').style.width = 'auto';
		GetId(menuid + '_img').style.height = 'auto';
		GetId(menuid).style.width = (w < 300 ? 320 : w + 20) + 'px';
		mheight = h + 63;
		menu.style.height = mheight + 'px';
		GetId(menuid + '_zoomlayer').style.height = (mheight < 120 ? 120 : mheight) + 'px';
		GetId(menuid + '_img').innerHTML = '<img id="' + zoomid + '" src="' + zimg + '" width="' + w + '" height="' + h + '" w="' + imgw + '" h="' + imgh + '" />' + imgtitle;
		if(GetId(menuid + '_imglink')) {
			GetId(menuid + '_imglink').href = zimg;
		}
		setMenuPosition('', menuid, '00');
		adjustpn(h);
	};
	var adjustTimer = 0;
	var adjustTimerCount = 0;
	var wheelDelta = 0;
	var clientX = 0;
	var clientY = 0;
	var adjust = function(e, a) {
		if(BROWSER.ie && BROWSER.ie<7) {
		} else {
			if(adjustTimerCount) {
				adjustTimer = (function(){
					return setTimeout(function () {
						adjustTimerCount++;
						adjust(e);
					}, 20);
					})();
					GetId(menuid).setAttribute('timer', adjustTimer);
				if(adjustTimerCount > 17) {
					clearTimeout(adjustTimer);
					adjustTimerCount = 0;
					doane();
				}
			} else if(!a) {
				adjustTimerCount = 1;
				if(adjustTimer) {
					clearTimeout(adjustTimer);
					adjust(e, a);
				} else {
					adjust(e, a);
				}
				doane();
			}
		}
		var ele = GetId(zoomid);
		if(!ele) {
			return;
		}
		var imgw = ele.getAttribute('w');
		var imgh = ele.getAttribute('h');

		if(!a) {
			e = e || window.event;
			try {
				if(e.altKey || e.shiftKey || e.ctrlKey) return;
			} catch (e) {
				e = {'wheelDelta':wheelDelta, 'clientX':clientX, 'clientY':clientY};
			}
			var step = 0;
			if(e.wheelDelta <= 0 || e.detail > 0) {
				if(ele.width - 1 <= 200 || ele.height - 1 <= 200) {
					clearTimeout(adjustTimer);
					adjustTimerCount = 0;
					doane(e);return;
				}
				step = parseInt(imgw/ele.width)-4;
			} else {
				if(ele.width + 1 >= imgw*40) {
					clearTimeout(adjustTimer);
					adjustTimerCount = 0;
					doane(e);return;
				}
				step = 4-parseInt(imgw/ele.width) || 2;
			}
			if(BROWSER.ie && BROWSER.ie<7) { step *= 5;}
			wheelDelta = e.wheelDelta;
			clientX = e.clientX;
			clientY = e.clientY;
			var ratio = 0;
			if(imgw > imgh) {
				ratio = step/ele.height;
				ele.height += step;
				ele.width = imgw*(ele.height/imgh);
			} else if(imgw < imgh) {
				ratio = step/ele.width;
				ele.width += step;
				ele.height = imgh*(ele.width/imgw);
			}
			if(BROWSER.ie && BROWSER.ie<7) {
				setMenuPosition('', menuid, '00');
			} else {
				var menutop = parseFloat(menu.getAttribute('top_') || menu.style.top);
				var menuleft = parseFloat(menu.getAttribute('left_') || menu.style.left);
				var imgY = clientY - menutop - 39;
				var imgX = clientX - menuleft - 10;
				var newTop = (menutop - imgY*ratio) + 'px';
				var newLeft = (menuleft - imgX*ratio) + 'px';
				menu.style.top = newTop;
				menu.style.left = newLeft;
				menu.setAttribute('top_', newTop);
				menu.setAttribute('left_', newLeft);
			}
		} else {
			ele.width = imgw;
			ele.height = imgh;
		}
		menu.style.width = (parseInt(ele.width < 300 ? 300 : parseInt(ele.width)) + 20) + 'px';
		var mheight = (parseInt(ele.height) + 50);
		menu.style.height = mheight + 'px';
		GetId(menuid + '_zoomlayer').style.height = (mheight < 120 ? 120 : mheight) + 'px';
		adjustpn(ele.height);
		doane(e);
	};
	if(!menu && !pn) {
		menu = document.createElement('div');
		menu.id = menuid;
		if(cover) {
			menu.innerHTML = '<div class="zoominner" id="' + menuid + '_zoomlayer" style="display:none"><p><span class="y"><a id="' + menuid + '_imglink" class="imglink" target="_blank" title="在新窗口打开">在新窗口打开</a><a id="' + menuid + '_adjust" href="javascipt:;" class="imgadjust" title="实际大小">实际大小</a>' +
				'<a href="javascript:;" onclick="hideMenu()" class="imgclose" title="关闭">关闭</a></span>鼠标滚轮缩放图片</p>' +
				'<div class="zimg_p" id="' + menuid + '_picpage"></div><div class="hm" id="' + menuid + '_img"></div></div>';
		} else {
			menu.innerHTML = '<div class="popupmenu_popup" id="' + menuid + '_zoomlayer" style="width:auto"><span class="right y"><a href="javascript:;" onclick="hideMenu()" class="flbc" style="width:20px;margin:0 0 2px 0">关闭</a></span>鼠标滚轮缩放图片<div class="zimg_p" id="' + menuid + '_picpage"></div><div class="hm" id="' + menuid + '_img"></div></div>';
		}
		if(BROWSER.ie || BROWSER.chrome){
			menu.onmousewheel = adjust;
		} else {
			menu.addEventListener('DOMMouseScroll', adjust, false);
		}
		GetId('append_parent').appendChild(menu);
		if(GetId(menuid + '_adjust')) {
			GetId(menuid + '_adjust').onclick = function(e) {adjust(e, 1)};
		}
	}
	showloading(zimg, pn);
	picpage = '';
	GetId(menuid + '_picpage').innerHTML = '';
	if(typeof zoomgroup == 'object' && zoomgroup[obj.id] && typeof aimgcount == 'object' && aimgcount[zoomgroup[obj.id]]) {
		authorimgs = aimgcount[zoomgroup[obj.id]];
		var aid = obj.id.substr(5), authorlength = authorimgs.length, authorcurrent = '';
		if(authorlength > 1) {
			for(i = 0; i < authorlength;i++) {
				if(aid == authorimgs[i]) {
					authorcurrent = i;
				}
			}
			if(authorcurrent !== '') {
				paid = authorcurrent > 0 ? authorimgs[authorcurrent - 1] : authorimgs[authorlength - 1];
				picpage += ' <div id="zimg_prev" onmouseover="dragMenuDisabled=true;this.style.backgroundPosition=\'0 50px\'" onmouseout="dragMenuDisabled=false;this.style.backgroundPosition=\'0 -100px\';" onclick="_zoom_page(\'' + paid + '\', ' + (showexif ? 1 : 0) + ')" class="zimg_prev"><strong>上一张</strong></div> ';
				paid = authorcurrent < authorlength - 1 ? authorimgs[authorcurrent + 1] : authorimgs[0];
				picpage += ' <div id="zimg_next" onmouseover="dragMenuDisabled=true;this.style.backgroundPosition=\'100% 50px\'" onmouseout="dragMenuDisabled=false;this.style.backgroundPosition=\'100% -100px\';" onclick="_zoom_page(\'' + paid + '\', ' + (showexif ? 1 : 0) + ')" class="zimg_next"><strong>下一张</strong></div> ';
			}
			if(picpage) {
				GetId(menuid + '_picpage').innerHTML = picpage;
			}
		}
	}
	showMenu({'ctrlid':obj.id,'menuid':menuid,'duration':3,'pos':'00','cover':0,'drag':menuid,'maxh':''});
}

function _zoom_page(paid, showexif) {
	var imagesrc = GetId('aimg_' + paid).getAttribute('zoomfile') ? GetId('aimg_' + paid).getAttribute('zoomfile') : GetId('aimg_' + paid).getAttribute('file');
	zoom(GetId('aimg_' + paid), imagesrc, 0, 1, showexif ? 1 : 0);
}

function _switchTab(prefix, current, total, activeclass) {
	activeclass = !activeclass ? 'a' : activeclass;
	for(var i = 1; i <= total;i++) {
		var classname = ' '+GetId(prefix + '_' + i).className+' ';
		GetId(prefix + '_' + i).className = classname.replace(' '+activeclass+' ','').substr(1);
		GetId(prefix + '_c_' + i).style.display = 'none';
	}
	GetId(prefix + '_' + current).className = GetId(prefix + '_' + current).className + ' '+activeclass;
	GetId(prefix + '_c_' + current).style.display = '';
}

function _initTab(frameId, type) {
	if (typeof document['diyform'] == 'object' || GetId(frameId).className.indexOf('tab') < 0) return false;
	type = type || 'click';
	var tabs = GetId(frameId+'_title').childNodes;
	var arrTab = [];
	for(var i in tabs) {
		if (tabs[i]['nodeType'] == 1 && tabs[i]['className'].indexOf('move-span') > -1) {
			arrTab.push(tabs[i]);
		}
	}
	var counter = 0;
	var tab = document.createElement('ul');
	tab.className = 'tb cl';
	var len = arrTab.length;
	for(var i = 0;i < len; i++) {
		var tabId = arrTab[i].id;
		if (hasClass(arrTab[i],'frame') || hasClass(arrTab[i],'tab')) {
			var arrColumn = [];
			for (var j in arrTab[i].childNodes) {
				if (typeof arrTab[i].childNodes[j] == 'object' && !hasClass(arrTab[i].childNodes[j],'title')) arrColumn.push(arrTab[i].childNodes[j]);
			}
			var frameContent = document.createElement('div');
			frameContent.id = tabId+'_content';
			frameContent.className = hasClass(arrTab[i],'frame') ? 'content cl '+arrTab[i].className.substr(arrTab[i].className.lastIndexOf(' ')+1) : 'content cl';
			var colLen = arrColumn.length;
			for (var k = 0; k < colLen; k++) {
				frameContent.appendChild(arrColumn[k]);
			}
		} else {
			var frameContent = GetId(tabId+'_content');
			frameContent = frameContent || document.createElement('div');
		}
		frameContent.style.display = counter ? 'none' : '';
		GetId(frameId+'_content').appendChild(frameContent);

		var li = document.createElement('li');
		li.id = tabId;
		li.className = counter ? '' : 'a';
		var reg = new RegExp('style=\"(.*?)\"', 'gi');
		var matchs = '', style = '', imgs = '';
		while((matchs = reg.exec(arrTab[i].innerHTML))) {
			if(matchs[1].substr(matchs[1].length,1) != ';') {
				matchs[1] += ';';
			}
			style += matchs[1];
		}
		style = style ? ' style="'+style+'"' : '';
		reg = new RegExp('(<img.*?>)', 'gi');
		while((matchs = reg.exec(arrTab[i].innerHTML))) {
			imgs += matchs[1];
		}
		li.innerHTML = arrTab[i]['innerText'] ? arrTab[i]['innerText'] : arrTab[i]['textContent'];
		var a = arrTab[i].getElementsByTagName('a');
		var href = a && a[0] ? a[0].href : 'javascript:;';
		var onclick = type == 'click' ? ' onclick="return false;"' : '';
		li.innerHTML = '<a href="' + href + '"' + onclick + ' onfocus="this.blur();" ' + style + '>' + imgs + li.innerHTML + '</a>';
		_attachEvent(li, type, switchTabUl);
		tab.appendChild(li);
		GetId(frameId+'_title').removeChild(arrTab[i]);
		counter++;
	}
	GetId(frameId+'_title').appendChild(tab);
}

function switchTabUl (e) {
	e = e || window.event;
	var aim = e.target || e.srcElement;
	var tabId = aim.id;
	var parent = aim.parentNode;
	while(parent['nodeName'] != 'UL' && parent['nodeName'] != 'BODY') {
		tabId = parent.id;
		parent = parent.parentNode;
	}
	if(parent['nodeName'] == 'BODY') return false;
	var tabs = parent.childNodes;
	var len2 = tabs.length;
	for(var j = 0; j < len2; j++) {
		tabs[j].className = (tabs[j].id == tabId) ? 'a' : '';
		var content = GetId(tabs[j].id+'_content');
		if (content) content.style.display = tabs[j].id == tabId ? '' : 'none';
	}
}

function slideshow(el) {
	var obj = this;
	if(!el.id) el.id = Math.random();
	if(typeof slideshow.entities == 'undefined') {
		slideshow.entities = {};
	}
	this.id = el.id;
	if(slideshow.entities[this.id]) return false;
	slideshow.entities[this.id] = this;

	this.slideshows = [];
	this.slidebar = [];
	this.slideother = [];
	this.slidebarup = '';
	this.slidebardown = '';
	this.slidenum = 0;
	this.slidestep = 0;

	this.container = el;
	this.imgs = [];
	this.imgLoad = [];
	this.imgLoaded = 0;
	this.imgWidth = 0;
	this.imgHeight = 0;

	this.getMEvent = function(ele, value) {
		value = !value ? 'mouseover' : value;
		var mevent = !ele ? '' : ele.getAttribute('mevent');
		mevent = (mevent == 'click' || mevent == 'mouseover') ? mevent : value;
		return mevent;
	};
	this.slideshows = C('slideshow', el);
	this.slideshows = this.slideshows.length>0 ? this.slideshows[0].childNodes : null;
	this.slidebar = C('slidebar', el);
	this.slidebar = this.slidebar.length>0 ? this.slidebar[0] : null;
	this.barmevent = this.getMEvent(this.slidebar);
	this.slideother = C('slideother', el);
	this.slidebarup = C('slidebarup', el);
	this.slidebarup = this.slidebarup.length>0 ? this.slidebarup[0] : null;
	this.barupmevent = this.getMEvent(this.slidebarup, 'click');
	this.slidebardown = C('slidebardown', el);
	this.slidebardown = this.slidebardown.length>0 ? this.slidebardown[0] : null;
	this.bardownmevent = this.getMEvent(this.slidebardown, 'click');
	this.slidenum = parseInt(this.container.getAttribute('slidenum'));
	this.slidestep = parseInt(this.container.getAttribute('slidestep'));
	this.timestep = parseInt(this.container.getAttribute('timestep'));
	this.timestep = !this.timestep ? 2500 : this.timestep;

	this.index = this.length = 0;
	this.slideshows = !this.slideshows ? filterTextNode(el.childNodes) : filterTextNode(this.slideshows);

	this.length = this.slideshows.length;

	for(i=0; i<this.length; i++) {
		this.slideshows[i].style.display = "none";
		_attachEvent(this.slideshows[i], 'mouseover', function(){obj.stop();});
		_attachEvent(this.slideshows[i], 'mouseout', function(){obj.goon();});
	}

	for(i=0, L=this.slideother.length; i<L; i++) {
		for(var j=0;j<this.slideother[i].childNodes.length;j++) {
			if(this.slideother[i].childNodes[j].nodeType == 1) {
				this.slideother[i].childNodes[j].style.display = "none";
			}
		}
	}

	if(!this.slidebar) {
		if(!this.slidenum && !this.slidestep) {
			this.container.parentNode.style.position = 'relative';
			this.slidebar = document.createElement('div');
			this.slidebar.className = 'slidebar';
			this.slidebar.style.position = 'absolute';
			this.slidebar.style.top = '5px';
			this.slidebar.style.left = '4px';
			this.slidebar.style.display = 'none';
			var html = '<ul>';
			for(var i=0; i<this.length; i++) {
				html += '<li on'+this.barmevent+'="slideshow.entities[' + this.id + '].xactive(' + i + '); return false;">' + (i + 1).toString() + '</li>';
			}
			html += '</ul>';
			this.slidebar.innerHTML = html;
			this.container.parentNode.appendChild(this.slidebar);
			this.controls = this.slidebar.getElementsByTagName('li');
		}
	} else {
		this.controls = filterTextNode(this.slidebar.childNodes);
		for(i=0; i<this.controls.length; i++) {
			if(this.slidebarup == this.controls[i] || this.slidebardown == this.controls[i]) continue;
			_attachEvent(this.controls[i], this.barmevent, function(){slidexactive()});
			_attachEvent(this.controls[i], 'mouseout', function(){obj.goon();});
		}
	}
	if(this.slidebarup) {
		_attachEvent(this.slidebarup, this.barupmevent, function(){slidexactive('up')});
	}
	if(this.slidebardown) {
		_attachEvent(this.slidebardown, this.bardownmevent, function(){slidexactive('down')});
	}
	this.activeByStep = function(index) {
		var showindex = 0,i = 0;
		if(index == 'down') {
			showindex = this.index + 1;
			if(showindex > this.length) {
				this.runRoll();
			} else {
				for (i = 0; i < this.slidestep; i++) {
					if(showindex >= this.length) showindex = 0;
					this.index = this.index - this.slidenum + 1;
					if(this.index < 0) this.index = this.length + this.index;
					this.active(showindex);
					showindex++;
				}
			}
		} else if (index == 'up') {
			var tempindex = this.index;
			showindex = this.index - this.slidenum;
			if(showindex < 0) return false;
			for (i = 0; i < this.slidestep; i++) {
				if(showindex < 0) showindex = this.length - Math.abs(showindex);
				this.active(showindex);
				this.index = tempindex = tempindex - 1;
				if(this.index <0) this.index = this.length - 1;
				showindex--;
			}
		}
		return false;
	};
	this.active = function(index) {
		this.slideshows[this.index].style.display = "none";
		this.slideshows[index].style.display = "block";
		if(this.controls && this.controls.length > 0) {
			this.controls[this.index].className = '';
			this.controls[index].className = 'on';
		}
		for(var i=0,L=this.slideother.length; i<L; i++) {
			this.slideother[i].childNodes[this.index].style.display = "none";
			this.slideother[i].childNodes[index].style.display = "block";
		}
		this.index = index;
	};
	this.xactive = function(index) {
		if(!this.slidenum && !this.slidestep) {
			this.stop();
			if(index == 'down') index = this.index == this.length-1 ? 0 : this.index+1;
			if(index == 'up') index = this.index == 0 ? this.length-1 : this.index-1;
			this.active(index);
		} else {
			this.activeByStep(index);
		}
	};
	this.goon = function() {
		this.stop();
		var curobj = this;
		this.timer = setTimeout(function () {
			curobj.run();
		}, this.timestep);
	};
	this.stop = function() {
		clearTimeout(this.timer);
	};
	this.run = function() {
		var index = this.index + 1 < this.length ? this.index + 1 : 0;
		if(!this.slidenum && !this.slidestep) {
			this.active(index);
		} else {
			this.activeByStep('down');
		}
		var ss = this;
		this.timer = setTimeout(function(){
			ss.run();
		}, this.timestep);
	};

	this.runRoll = function() {
		for(var i = 0; i < this.slidenum; i++) {
			if(this.slideshows[i] && typeof this.slideshows[i].style != 'undefined') this.slideshows[i].style.display = 'block';
			for(var j=0,L=this.slideother.length; j<L; j++) {
				this.slideother[j].childNodes[i].style.display = 'block';
			}
		}
		this.index = this.slidenum - 1;
	};
	var imgs = this.slideshows.length ? this.slideshows[0].parentNode.getElementsByTagName('img') : [];
	for(i=0, L=imgs.length; i<L; i++) {
		this.imgs.push(imgs[i]);
		this.imgLoad.push(new Image());
		this.imgLoad[i].onerror = function (){obj.imgLoaded ++;};
		this.imgLoad[i].src = this.imgs[i].src;
	}

	this.getSize = function () {
		if(this.imgs.length == 0) return false;
		var img = this.imgs[0];
		this.imgWidth = img.width ? parseInt(img.width) : 0;
		this.imgHeight = img.height ? parseInt(img.height) : 0;
		var ele = img.parentNode;
		while ((!this.imgWidth || !this.imgHeight) && !hasClass(ele,'slideshow') && ele != document.body) {
			this.imgWidth = ele.style.width ? parseInt(ele.style.width) : 0;
			this.imgHeight = ele.style.height ? parseInt(ele.style.height) : 0;
			ele = ele.parentNode;
		}
		return true;
	};

	this.getSize();

	this.checkLoad = function () {
		var obj = this;
		this.container.style.display = 'block';
		for(i = 0;i < this.imgs.length;i++) {
			if(this.imgLoad[i].complete && !this.imgLoad[i].status) {
				this.imgLoaded++;
				this.imgLoad[i].status = 1;
			}
		}
		var percentEle = GetId(this.id+'_percent');
		if(this.imgLoaded < this.imgs.length) {
			if (!percentEle) {
				var dom = document.createElement('div');
				dom.id = this.id+"_percent";
				dom.style.width = this.imgWidth ? this.imgWidth+'px' : '150px';
				dom.style.height = this.imgHeight ? this.imgHeight+'px' : '150px';
				dom.style.lineHeight = this.imgHeight ? this.imgHeight+'px' : '150px';
				dom.style.backgroundColor = '#ccc';
				dom.style.textAlign = 'center';
				dom.style.top = '0';
				dom.style.left = '0';
				dom.style.marginLeft = 'auto';
				dom.style.marginRight = 'auto';
				this.slideshows[0].parentNode.appendChild(dom);
				percentEle = dom;
			}
			el.parentNode.style.position = 'relative';
			percentEle.innerHTML = (parseInt(this.imgLoaded / this.imgs.length * 100)) + '%';
			setTimeout(function () {obj.checkLoad();}, 100);
		} else {
			if (percentEle) percentEle.parentNode.removeChild(percentEle);
			if(this.slidebar) this.slidebar.style.display = '';
			this.index = this.length - 1 < 0 ? 0 : this.length - 1;
			if(this.slideshows.length > 0) {
				if(!this.slidenum || !this.slidestep) {
					this.run();
				} else {
					this.runRoll();
				}
			}
		}
	};
	this.checkLoad();
}

function slidexactive(step) {
	var e = getEvent();
	var aim = e.target || e.srcElement;
	var parent = aim.parentNode;
	var xactivei = null, slideboxid = null,currentslideele = null;
	currentslideele = hasClass(aim, 'slidebarup') || hasClass(aim, 'slidebardown') || hasClass(parent, 'slidebar') ? aim : null;
	while(parent && parent != document.body) {
		if(!currentslideele && hasClass(parent, 'slidebar')) {
			currentslideele = parent;
		}
		if(!currentslideele && (hasClass(parent, 'slidebarup') || hasClass(parent, 'slidebardown'))) {
			currentslideele = parent;
		}
		if(hasClass(parent, 'slidebox')) {
			slideboxid = parent.id;
			break;
		}
		parent = parent.parentNode;
	}
	var slidebar = C('slidebar', parent);
	var children = slidebar.length == 0 ? [] : filterTextNode(slidebar[0].childNodes);
	if(currentslideele && (hasClass(currentslideele, 'slidebarup') || hasClass(currentslideele, 'slidebardown'))) {
		xactivei = step;
	} else {
		for(var j=0,i=0,L=children.length;i<L;i++){
			if(currentslideele && children[i] == currentslideele) {
				xactivei = j;
				break;
			}
			if(!hasClass(children[i], 'slidebarup') && !hasClass(children[i], 'slidebardown')) j++;
		}
	}
	if(slideboxid != null && xactivei != null) slideshow.entities[slideboxid].xactive(xactivei);
}

function filterTextNode(list) {
	var newlist = [];
	for(var i=0; i<list.length; i++) {
		if (list[i].nodeType == 1) {
			newlist.push(list[i]);
		}
	}
	return newlist;
}

function _runslideshow() {
	var slideshows = C('slidebox');
	for(var i=0,L=slideshows.length; i<L; i++) {
		new slideshow(slideshows[i]);
	}
}

function _showTip(ctrlobj) {
	if(!ctrlobj.id) {
		ctrlobj.id = 'tip_' + Math.random();
	}
	menuid = ctrlobj.id + '_menu';
	if(!GetId(menuid)) {
		var div = document.createElement('div');
		div.id = ctrlobj.id + '_menu';
		div.className = 'tip tip_4';
		div.style.display = 'none';
		div.innerHTML = '<div class="tip_horn"></div><div class="tip_c">' + ctrlobj.getAttribute('tip') + '</div>';
		GetId('append_parent').appendChild(div);
	}
	GetId(ctrlobj.id).onmouseout = function () { hideMenu('', 'prompt'); };
	showMenu({'mtype':'prompt','ctrlid':ctrlobj.id,'pos':'12!','duration':2,'zindex':JSMENU['zIndex']['prompt']});
}

function _showPrompt(ctrlid, evt, msg, timeout) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	var duration = timeout ? 0 : 3;
	if(GetId(menuid)) {
		GetId(menuid).parentNode.removeChild(GetId(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = ctrlid ? 'tip tip_js' : 'ntcwin';
	div.style.display = 'none';
	GetId('append_parent').appendChild(div);
	if(ctrlid) {
		msg = '<div id="' + ctrlid + '_prompt"><div class="tip_horn"></div><div class="tip_c">' + msg + '</div>';
	} else {
		msg = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner">' + msg +
			'</td><td class="pc_r">&nbsp;</td></tr></table>';
	}
	div.innerHTML = msg;
	if(ctrlid) {
		if(!timeout) {
			evt = 'click';
		}
		if(GetId(ctrlid)) {
			if(GetId(ctrlid).evt !== false) {
				var prompting = function() {
					showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210'});
				};
				if(evt == 'click') {
					GetId(ctrlid).onclick = prompting;
				} else {
					GetId(ctrlid).onmouseover = prompting;
				}
			}
			showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210','duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
			GetId(ctrlid).unselectable = false;
		}
	} else {
		showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
		GetId(menuid).style.top = (parseInt(GetId(menuid).style.top) - 100) + 'px';
	}
}
function _showCreditPrompt() {
	var notice = getcookie('creditnotice').split('D');
	var basev = getcookie('creditbase').split('D');
	var creditrule = decodeURI(getcookie('creditrule', 1)).replace(String.fromCharCode(9), ' ');
	if(!discuz_uid || notice.length < 2 || notice[9] != discuz_uid) {
		setcookie('creditnotice', '');
		setcookie('creditrule', '');
		return;
	}
	var creditnames = creditnotice.split(',');
	var creditinfo = [];
	var e;
	for(var i = 0; i < creditnames.length; i++) {
		e = creditnames[i].split('|');
		creditinfo[e[0]] = [e[1], e[2]];
	}
	creditShow(creditinfo, notice, basev, 0, 1, creditrule);
}

function creditShow(creditinfo, notice, basev, bk, first, creditrule) {
	var s = '', check = 0;
	for(i = 1; i <= 8; i++) {
		v = parseInt(Math.abs(parseInt(notice[i])) / 5) + 1;
		if(notice[i] !== '0' && creditinfo[i]) {
			s += '<span>' + creditinfo[i][0] + (notice[i] != 0 ? (notice[i] > 0 ? '<em>+' : '<em class="desc">') + notice[i] + '</em>' : '') + creditinfo[i][1] + '</span>';
		}
		if(notice[i] > 0) {
			notice[i] = parseInt(notice[i]) - v;
			basev[i] = parseInt(basev[i]) + v;
		} else if(notice[i] < 0) {
			notice[i] = parseInt(notice[i]) + v;
			basev[i] = parseInt(basev[i]) - v;
		}
		if(GetId('hcredit_' + i)) {
			GetId('hcredit_' + i).innerHTML = basev[i];
		}
	}
	for(i = 1; i <= 8; i++) {
		if(notice[i] != 0) {
			check = 1;
		}
	}
	if(!s || first) {
		setcookie('creditnotice', '');
		setcookie('creditbase', '');
		setcookie('creditrule', '');
		if(!s) {
			return;
		}
	}
	if(!GetId('creditpromptdiv')) {
		showPrompt(null, null, '<div id="creditpromptdiv">' + (creditrule ? '<i>' + creditrule + '</i> ' : '') + s + '</div>', 0);
	} else {
		GetId('creditpromptdiv').innerHTML = s;
	}
	setTimeout(function () {hideMenu(1, 'prompt');GetId('append_parent').removeChild(GetId('ntcwin'));}, 1500);
}

function _showColorBox(ctrlid, layer, k, bgcolor) {
	var tag1 = !bgcolor ? 'color' : 'backcolor', tag2 = !bgcolor ? 'forecolor' : 'backcolor';
	if(!GetId(ctrlid + '_menu')) {
		var menu = document.createElement('div');
		menu.id = ctrlid + '_menu';
		menu.className = 'p_pop colorbox';
		menu.unselectable = true;
		menu.style.display = 'none';
		var coloroptions = ['Black', 'Sienna', 'DarkOliveGreen', 'DarkGreen', 'DarkSlateBlue', 'Navy', 'Indigo', 'DarkSlateGray', 'DarkRed', 'DarkOrange', 'Olive', 'Green', 'Teal', 'Blue', 'SlateGray', 'DimGray', 'Red', 'SandyBrown', 'YellowGreen', 'SeaGreen', 'MediumTurquoise', 'RoyalBlue', 'Purple', 'Gray', 'Magenta', 'Orange', 'Yellow', 'Lime', 'Cyan', 'DeepSkyBlue', 'DarkOrchid', 'Silver', 'Pink', 'Wheat', 'LemonChiffon', 'PaleGreen', 'PaleTurquoise', 'LightBlue', 'Plum', 'White'];
		var colortexts = ['黑色', '赭色', '暗橄榄绿色', '暗绿色', '暗灰蓝色', '海军色', '靛青色', '墨绿色', '暗红色', '暗桔黄色', '橄榄色', '绿色', '水鸭色', '蓝色', '灰石色', '暗灰色', '红色', '沙褐色', '黄绿色', '海绿色', '间绿宝石', '皇家蓝', '紫色', '灰色', '红紫色', '橙色', '黄色', '酸橙色', '青色', '深天蓝色', '暗紫色', '银色', '粉色', '浅黄色', '柠檬绸色', '苍绿色', '苍宝石绿', '亮蓝色', '洋李色', '白色'];
		var str = '';
		for(var i = 0; i < 40; i++) {
			str += '<input type="button" style="background-color: ' + coloroptions[i] + '"' + (typeof setEditorTip == 'function' ? ' onmouseover="setEditorTip(\'' + colortexts[i] + '\')" onmouseout="setEditorTip(\'\')"' : '') + ' onclick="'
			+ (typeof wysiwyg == 'undefined' ? 'seditor_insertunit(\'' + k + '\', \'[' + tag1 + '=' + coloroptions[i] + ']\', \'[/' + tag1 + ']\')' : (ctrlid == editorid + '_tbl_param_4' ? 'GetId(\'' + ctrlid + '\').value=\'' + coloroptions[i] + '\';hideMenu(2)' : 'discuzcode(\'' + tag2 + '\', \'' + coloroptions[i] + '\')'))
			+ '" title="' + colortexts[i] + '" />' + (i < 39 && (i + 1) % 8 == 0 ? '<br />' : '');
		}
		menu.innerHTML = str;
		GetId('append_parent').appendChild(menu);
	}
	showMenu({'ctrlid':ctrlid,'evt':'click','layer':layer});
}

function _toggle_collapse(objname, noimg, complex, lang) {
	var obj = GetId(objname);
	if(obj) {
		obj.style.display = obj.style.display == '' ? 'none' : '';
		var collapsed = getcookie('collapse');
		collapsed = updatestring(collapsed, objname, !obj.style.display);
		setcookie('collapse', collapsed, (collapsed ? 2592000 : -2592000));
	}
	if(!noimg) {
		var img = GetId(objname + '_img');
		if(img.tagName != 'IMG') {
			if(img.className.indexOf('_yes') == -1) {
				img.className = img.className.replace(/_no/, '_yes');
				if(lang) {
					img.innerHTML = lang[0];
				}
			} else {
				img.className = img.className.replace(/_yes/, '_no');
				if(lang) {
					img.innerHTML = lang[1];
				}
			}
		} else {
			img.src = img.src.indexOf('_yes.gif') == -1 ? img.src.replace(/_no\.gif/, '_yes\.gif') : img.src.replace(/_yes\.gif/, '_no\.gif');
		}
		img.blur();
	}
	if(complex) {
		var objc = GetId(objname + '_c');
		if(objc) {
			objc.className = objc.className == 'umh' ? 'umh umn' : 'umh';
		}
	}

}

function _extstyle(css) {
	if(!GetId('css_extstyle')) {
		loadcss('extstyle');
	}
	GetId('css_extstyle').href = css ? css + '/style.css' : STATICURL + 'image/common/extstyle_none.css';
	currentextstyle = css;
	setcookie('extstyle', css, 86400 * 30);
	if(GetId('css_widthauto') && !GetId('css_widthauto').disabled) {
		CSSLOADED['widthauto'] = 0;
		loadcss('widthauto');
	}
}

function _widthauto(obj) {
	if(GetId('css_widthauto')) {
		CSSLOADED['widthauto'] = 1;
	}
	if(!CSSLOADED['widthauto'] || GetId('css_widthauto').disabled) {
		if(!CSSLOADED['widthauto']) {
			loadcss('widthauto');
		} else {
			GetId('css_widthauto').disabled = false;
		}
		HTMLNODE.className += ' widthauto';
		setcookie('widthauto', 1, 86400 * 30);
		obj.innerHTML = '切换到窄版';
	} else {
		GetId('css_widthauto').disabled = true;
		HTMLNODE.className = HTMLNODE.className.replace(' widthauto', '');
		setcookie('widthauto', -1, 86400 * 30);
		obj.innerHTML = '切换到宽版';
	}
	hideMenu();
}

function _showCreditmenu() {
	if(!GetId('extcreditmenu_menu')) {
		menu = document.createElement('div');
		menu.id = 'extcreditmenu_menu';
		menu.style.display = 'none';
		menu.className = 'p_pop';
		menu.innerHTML = '<div class="p_opt"><img src="'+ IMGDIR + '/loading.gif" width="16" height="16" class="vm" /> 请稍候...</div>';
		GetId('append_parent').appendChild(menu);
		ajaxget(GetId('extcreditmenu').href, 'extcreditmenu_menu', 'ajaxwaitid');
	}
	showMenu({'ctrlid':'extcreditmenu','ctrlclass':'a','duration':1});
}

function _showForummenu(fid) {
	if(!GetId('fjump_menu')) {
		fid = !fid ? 0 : fid;
		menu = document.createElement('div');
		menu.id = 'fjump_menu';
		menu.style.display = 'none';
		menu.className = 'p_pop';
		menu.innerHTML = '<div class="p_opt"><img src="'+ IMGDIR + '/loading.gif" width="16" height="16" class="vm" /> 请稍候...</div>';
		GetId('append_parent').appendChild(menu);
		ajaxget('forum.php?mod=ajax&action=forumjump&jfid=' + fid, 'fjump_menu', 'ajaxwaitid');
	}
	showMenu({'ctrlid':'fjump','ctrlclass':'a','duration':2});
}

function _imageRotate(imgid, direct) {
	var image = GetId(imgid);
	if(!image.getAttribute('deg')) {
		var deg = 0;
		image.setAttribute('ow', image.width);
		image.setAttribute('oh', image.height);
		if(BROWSER.ie) {
			image.setAttribute('om', parseInt(image.currentStyle.marginBottom));
		}
	} else {
		var deg = parseInt(image.getAttribute('deg'));
	}
	var ow = image.getAttribute('ow');
	var oh = image.getAttribute('oh');
	deg = direct == 1 ? deg - 90 : deg + 90;
	if(deg > 270) {
		deg = 0;
	} else if(deg < 0) {
		deg = 270;
	}
	image.setAttribute('deg', deg);
	if(BROWSER.ie) {
		if(!isNaN(image.getAttribute('om'))) {
			image.style.marginBottom = (image.getAttribute('om') + (BROWSER.ie < 8 ? 0 : (deg == 90 || deg == 270 ? Math.abs(ow - oh) : 0))) + 'px';
		}
		image.style.filter = 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (deg / 90) + ')';
	} else {
	        switch(deg) {
			case 90:var cow = oh, coh = ow, cx = 0, cy = -oh;break;
			case 180:var cow = ow, coh = oh, cx = -ow, cy = -oh;break;
			case 270:var cow = oh, coh = ow, cx = -ow, cy = 0;break;
	        }
		var canvas = GetId(image.getAttribute('canvasid'));
		if(!canvas) {
			var i = document.createElement("canvas");
			i.id = 'canva_' + Math.random();
			image.setAttribute('canvasid', i.id);
			image.parentNode.insertBefore(i, image);
			canvas = GetId(i.id);
		}
		if(deg) {
			var canvasContext = canvas.getContext('2d');
			canvas.setAttribute('width', cow);
			canvas.setAttribute('height', coh);
			canvasContext.rotate(deg * Math.PI / 180);
			canvasContext.drawImage(image, cx, cy, ow, oh);
			image.style.display = 'none';
			canvas.style.display = '';
		} else {
			image.style.display = '';
			canvas.style.display = 'none';
		}
	}
}

function _createPalette(colorid, id, func) {
	var iframe = "<iframe name=\"c"+colorid+"_frame\" src=\"\" frameborder=\"0\" width=\"210\" height=\"148\" scrolling=\"no\"></iframe>";
	if (!GetId("c"+colorid+"_menu")) {
		var dom = document.createElement('span');
		dom.id = "c"+colorid+"_menu";
		dom.style.display = 'none';
		dom.innerHTML = iframe;
		GetId('append_parent').appendChild(dom);
	}
	func = !func ? '' : '|' + func;
	window.frames["c"+colorid+"_frame"].location.href = SITEURL+STATICURL+"image/admincp/getcolor.htm?c"+colorid+"|"+id+func;
	showMenu({'ctrlid':'c'+colorid});
	var iframeid = "c"+colorid+"_menu";
	_attachEvent(window, 'scroll', function(){hideMenu(iframeid);});
}

/* forum_viewthread.js */

var replyreload = '', attachimgST = new Array(), zoomgroup = new Array(), zoomgroupinit = new Array();

function attachimggroup(pid) {
	if(!zoomgroupinit[pid]) {
		for(i = 0;i < aimgcount[pid].length;i++) {
			zoomgroup['aimg_' + aimgcount[pid][i]] = pid;
		}
		zoomgroupinit[pid] = true;
	}
}

function attachimgshow(pid, onlyinpost) {
	onlyinpost = !onlyinpost ? false : onlyinpost;
	aimgs = aimgcount[pid];
	aimgcomplete = 0;
	loadingcount = 0;
	for(i = 0;i < aimgs.length;i++) {
		obj = GetId('aimg_' + aimgs[i]);
		if(!obj) {
			aimgcomplete++;
			continue;
		}
		if(onlyinpost && obj.getAttribute('inpost') || !onlyinpost) {
			if(!obj.status) {
				obj.status = 1;
				if(obj.getAttribute('file')) obj.src = obj.getAttribute('file');
				loadingcount++;
			} else if(obj.status == 1) {
				if(obj.complete) {
					obj.status = 2;
				} else {
					loadingcount++;
				}
			} else if(obj.status == 2) {
				aimgcomplete++;
				if(obj.getAttribute('thumbImg')) {
					thumbImg(obj);
				}
			}
			if(loadingcount >= 10) {
				break;
			}
		}
	}
	if(aimgcomplete < aimgs.length) {
		setTimeout(function () {
			attachimgshow(pid, onlyinpost);
		}, 100);
	}
}

function attachimglstshow(pid, islazy, fid, showexif) {
	var aimgs = aimgcount[pid];
	var s = '';
	if(fid) {
		s = ' onmouseover="showMenu({\'ctrlid\':this.id, \'pos\': \'12!\'});"';
	}
	if(typeof aimgcount == 'object' && GetId('imagelistthumb_' + pid)) {
		for(pid in aimgcount) {
			var imagelist = '';
			for(i = 0;i < aimgcount[pid].length;i++) {
				if(!GetId('aimg_' + aimgcount[pid][i]) || GetId('aimg_' + aimgcount[pid][i]).getAttribute('inpost') || parseInt(aimgcount[pid][i]) != aimgcount[pid][i]) {
					continue;
				}
				if(fid) {
					imagelist += '<div id="pattimg_' + aimgcount[pid][i] + '_menu" class="tip tip_4" style="display: none;"><div class="tip_horn"></div><div class="tip_c"><a href="forum.php?mod=ajax&action=setthreadcover&aid=' + aimgcount[pid][i] + '&fid=' + fid + '" class="xi2" onclick="showWindow(\'setcover' + aimgcount[pid][i] + '\', this.href)">设为封面</a></div></div>';
				}
				imagelist += '<div class="pattimg">' +
					'<a id="pattimg_' + aimgcount[pid][i] + '" class="pattimg_zoom" href="javascript:;"' + s + ' onclick="zoom(GetId(\'aimg_' + aimgcount[pid][i] + '\'), attachimggetsrc(\'aimg_' + aimgcount[pid][i] + '\'), 0, 0, ' + (parseInt(showexif) ? 1 : 0) + ')" title="点击放大">点击放大</a>' +
					'<img ' + (islazy ? 'file' : 'src') + '="forum.php?mod=image&aid=' + aimgcount[pid][i] + '&size=100x100&key=' + imagelistkey + '&atid=' + tid + '" width="100" height="100" /></div>';
			}
			if(GetId('imagelistthumb_' + pid)) {
				GetId('imagelistthumb_' + pid).innerHTML = imagelist;
			}
		}
	}
}

function attachimggetsrc(img) {
	return GetId(img).getAttribute('zoomfile') ? GetId(img).getAttribute('zoomfile') : GetId(img).getAttribute('file');
}

function attachimglst(pid, op, islazy) {
	if(!op) {
		GetId('imagelist_' + pid).style.display = 'none';
		GetId('imagelistthumb_' + pid).style.display = '';
	} else {
		GetId('imagelistthumb_' + pid).style.display = 'none';
		GetId('imagelist_' + pid).style.display = '';
		if(islazy) {
			o = new lazyload();
			o.showImage();
		} else {
			attachimgshow(pid);
		}
	}
	doane();
}

function attachimginfo(obj, infoobj, show, event) {
	objinfo = fetchOffset(obj);
	if(show) {
		GetId(infoobj).style.left = objinfo['left'] + 'px';
		GetId(infoobj).style.top = obj.offsetHeight < 40 ? (objinfo['top'] + obj.offsetHeight) + 'px' : objinfo['top'] + 'px';
		GetId(infoobj).style.display = '';
	} else {
		if(BROWSER.ie) {
			GetId(infoobj).style.display = 'none';
			return;
		} else {
			var mousex = document.body.scrollLeft + event.clientX;
			var mousey = document.documentElement.scrollTop + event.clientY;
			if(mousex < objinfo['left'] || mousex > objinfo['left'] + objinfo['width'] || mousey < objinfo['top'] || mousey > objinfo['top'] + objinfo['height']) {
				GetId(infoobj).style.display = 'none';
			}
		}
	}
}

function signature(obj) {
	if(obj.style.maxHeightIE != '') {
		var height = (obj.scrollHeight > parseInt(obj.style.maxHeightIE)) ? obj.style.maxHeightIE : obj.scrollHeight + 'px';
		if(obj.innerHTML.indexOf('<IMG ') == -1) {
			obj.style.maxHeightIE = '';
		}
		return height;
	}
}

function tagshow(event) {
	var obj = BROWSER.ie ? event.srcElement : event.target;
	ajaxmenu(obj, 0, 1, 2);
}

function parsetag(pid) {
	if(!GetId('postmessage_'+pid) || GetId('postmessage_'+pid).innerHTML.match(/<script[^\>]*?>/i)) {
		return;
	}
	var havetag = false;
	var tagfindarray = new Array();
	var str = GetId('postmessage_'+pid).innerHTML.replace(/(^|>)([^<]+)(?=<|$)/ig, function($1, $2, $3, $4) {
		for(i in tagarray) {
			if(tagarray[i] && $3.indexOf(tagarray[i]) != -1) {
				havetag = true;
				$3 = $3.replace(tagarray[i], '<h_ ' + i + '>');
				tmp = $3.replace(/&[a-z]*?<h_ \d+>[a-z]*?;/ig, '');
				if(tmp != $3) {
					$3 = tmp;
				} else {
					tagfindarray[i] = tagarray[i];
					tagarray[i] = '';
				}
			}
		}
		return $2 + $3;
		});
		if(havetag) {
		GetId('postmessage_'+pid).innerHTML = str.replace(/<h_ (\d+)>/ig, function($1, $2) {
			return '<span href=\"forum.php?mod=tag&name=' + tagencarray[$2] + '\" onclick=\"tagshow(event)\" class=\"t_tag\">' + tagfindarray[$2] + '</span>';
	    	});
	}
}

function setanswer(pid, from){
	if(confirm('您确认要把该回复选为“最佳答案”吗？')){
		if(BROWSER.ie) {
			doane(event);
		}
		GetId('modactions').action='forum.php?mod=misc&action=bestanswer&tid=' + tid + '&pid=' + pid + '&from=' + from + '&bestanswersubmit=yes';
		GetId('modactions').submit();
	}
}

var authort;
function showauthor(ctrlObj, menuid) {
	authort = setTimeout(function () {
		showMenu({'menuid':menuid});
		if(GetId(menuid + '_ma').innerHTML == '') GetId(menuid + '_ma').innerHTML = ctrlObj.innerHTML;
	}, 500);
	if(!ctrlObj.onmouseout) {
		ctrlObj.onmouseout = function() {
			clearTimeout(authort);
		}
	}
}

function fastpostappendreply() {
	if(GetId('fastpostrefresh') != null) {
		setcookie('fastpostrefresh', GetId('fastpostrefresh').checked ? 1 : 0, 2592000);
		if(GetId('fastpostrefresh').checked) {
			location.href = 'forum.php?mod=redirect&tid='+tid+'&goto=lastpost&random=' + Math.random() + '#lastpost';
			return;
		}
	}
	newpos = fetchOffset(GetId('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	GetId('post_new').style.display = '';
	GetId('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	GetId('postlistreply').appendChild(div);
	GetId('fastpostsubmit').disabled = false;
	if(GetId('fastpostmessage')) {
		GetId('fastpostmessage').value = '';
	} else {
		editdoc.body.innerHTML = BROWSER.firefox ? '<br />' : '';
	}
	if(GetId('secanswer3')) {
		GetId('checksecanswer3').innerHTML = '<img src="' + STATICURL + 'image/common/none.gif" width="17" height="17">';
		GetId('secanswer3').value = '';
		secclick3['secanswer3'] = 0;
	}
	if(GetId('seccodeverify3')) {
		GetId('checkseccodeverify3').innerHTML = '<img src="' + STATICURL + 'image/common/none.gif" width="17" height="17">';
		GetId('seccodeverify3').value = '';
		secclick3['seccodeverify3'] = 0;
	}
	showCreditPrompt();
}

function succeedhandle_fastpost(locationhref, message, param) {
	var pid = param['pid'];
	var tid = param['tid'];
	var from = param['from'];
	if(pid) {
		ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + pid + '&from=' + from, 'post_new', 'ajaxwaitid', '', null, 'fastpostappendreply()');
		if(replyreload) {
			var reloadpids = replyreload.split(',');
			for(i = 1;i < reloadpids.length;i++) {
				ajaxget('forum.php?mod=viewthread&tid=' + tid + '&viewpid=' + reloadpids[i] + '&from=' + from, 'post_' + reloadpids[i]);
			}
		}
		GetId('fastpostreturn').className = '';
	} else {
		if(!message) {
			message = '本版回帖需要审核，您的帖子将在通过审核后显示';
		}
		GetId('post_new').style.display = GetId('fastpostmessage').value = GetId('fastpostreturn').className = '';
		GetId('fastpostreturn').innerHTML = message;
	}
	if(param['sechash']) {
		updatesecqaa(param['sechash']);
		updateseccode(param['sechash']);
	}
	if(GetId('attach_tblheader')) {
		GetId('attach_tblheader').style.display = 'none';
	}
	if(GetId('attachlist')) {
		GetId('attachlist').innerHTML = '';
	}
}

function errorhandle_fastpost() {
	GetId('fastpostsubmit').disabled = false;
}

function succeedhandle_comment(locationhref, message, param) {
	ajaxget('forum.php?mod=misc&action=commentmore&tid=' + param['tid'] + '&pid=' + param['pid'], 'comment_' + param['pid']);
	hideWindow('comment');
	showCreditPrompt();
}

function succeedhandle_postappend(locationhref, message, param) {
	ajaxget('forum.php?mod=viewthread&tid=' + param['tid'] + '&viewpid=' + param['pid'], 'post_' + param['pid']);
	hideWindow('postappend');
}

function recommendupdate(n) {
	if(getcookie('recommend')) {
		var objv = n > 0 ? GetId('recommendv_add') : GetId('recommendv_subtract');
		objv.innerHTML = parseInt(objv.innerHTML) + 1;
		setTimeout(function () {
			GetId('recommentc').innerHTML = parseInt(GetId('recommentc').innerHTML) + n;
			GetId('recommentv').style.display = 'none';
		}, 1000);
		setcookie('recommend', '');
	}
}

function favoriteupdate() {
	var obj = GetId('favoritenumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}
function relayupdate() {
	var obj = GetId('relaynumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function shareupdate() {
	var obj = GetId('sharenumber');
	obj.innerHTML = parseInt(obj.innerHTML) + 1;
}

function switchrecommendv() {
	display('recommendv');
	display('recommendav');
}

function appendreply() {
	newpos = fetchOffset(GetId('post_new'));
	document.documentElement.scrollTop = newpos['top'];
	GetId('post_new').style.display = '';
	GetId('post_new').id = '';
	div = document.createElement('div');
	div.id = 'post_new';
	div.style.display = 'none';
	div.className = '';
	GetId('postlistreply').appendChild(div);
	if(GetId('postform')) {
		GetId('postform').replysubmit.disabled = false;
	}
	showCreditPrompt();
}

function poll_checkbox(obj) {
	if(obj.checked) {
		p++;
		for (var i = 0; i < GetId('poll').elements.length; i++) {
			var e = GetId('poll').elements[i];
			if(p == max_obj) {
				if(e.name.match('pollanswers') && !e.checked) {
					e.disabled = true;
				}
			}
		}
	} else {
		p--;
		for (var i = 0; i < GetId('poll').elements.length; i++) {
			var e = GetId('poll').elements[i];
			if(e.name.match('pollanswers') && e.disabled) {
				e.disabled = false;
			}
		}
	}
	GetId('pollsubmit').disabled = p <= max_obj && p > 0 ? false : true;
}

function itemdisable(i) {
	if(GetId('itemt_' + i).className == 'z') {
		GetId('itemt_' + i).className = 'z xg1';
		GetId('itemc_' + i).value = '';
		itemset(i);
	} else {
		GetId('itemt_' + i).className = 'z';
		GetId('itemc_' + i).value = GetId('itemc_' + i).value > 0 ? GetId('itemc_' + i).value : 0;
	}
}
function itemop(i, v) {
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	GetId('item_' + i).style.backgroundPosition = '10px ' + h;
}
function itemclk(i, v) {
	GetId('itemc_' + i).value = v;
	GetId('itemt_' + i).className = 'z';
}
function itemset(i) {
	var v = GetId('itemc_' + i).value;
	var h = v > 0 ? '-' + (v * 16) + 'px' : '0';
	GetId('item_' + i).style.backgroundPosition = '10px ' + h;
}

function checkmgcmn(id) {
	if(GetId('mgc_' + id) && !GetId('mgc_' + id + '_menu').getElementsByTagName('li').length) {
		GetId('mgc_' + id).innerHTML = '';
		GetId('mgc_' + id).style.display = 'none';
	}
}

function toggleRatelogCollapse(tarId, ctrlObj) {
	if(GetId(tarId).className == 'rate') {
		GetId(tarId).className = 'rate rate_collapse';
		setcookie('ratecollapse', 1, 2592000);
		ctrlObj.innerHTML = '展开';
	} else {
		GetId(tarId).className = 'rate';
		setcookie('ratecollapse', 0, -2592000);
		ctrlObj.innerHTML = '收起';
	}
}

function copyThreadUrl(obj) {
	setCopy(GetId('thread_subject').innerHTML.replace(/&amp;/g, '&') + '\n' + obj.href + '\n', '帖子地址已经复制到剪贴板');
	return false;
}

function replyNotice() {
	var newurl = 'forum.php?mod=misc&action=replynotice&tid=' + tid + '&op=';
	var replynotice = GetId('replynotice');
	var status = replynotice.getAttribute("status");
	if(status == 1) {
		replynotice.href = newurl + 'receive';
		replynotice.innerHTML = '接收回复通知';
		replynotice.setAttribute("status", 0);
	} else {
		replynotice.href = newurl + 'ignore';
		replynotice.innerHTML = '取消回复通知';
		replynotice.setAttribute("status", 1);
	}
}

var connect_share_loaded = 0;
function connect_share(connect_share_url, connect_uin) {
	if(parseInt(discuz_uid) <= 0) {
		return true;
	} else {
		if(connect_uin) {
			setTimeout(function () {
				if(!connect_share_loaded) {
					showDialog('分享服务连接失败，请稍后再试。', 'notice');
					GetId('append_parent').removeChild(GetId('connect_load_js'));
				}
			}, 5000);
			connect_load(connect_share_url);
		} else {
			showDialog(GetId('connect_share_unbind').innerHTML, 'info', '请先绑定QQ账号');
		}
		return false;
	}
}

function connect_load(src) {
	var e = document.createElement('script');
	e.type = "text/javascript";
	e.id = 'connect_load_js';
	e.src = src + '&_r=' + Math.random();
	e.async = true;
	GetId('append_parent').appendChild(e);
}

function connect_show_dialog(title, html, type) {
	var type = type ? type : 'info';
	showDialog(html, type, title, null, 0);
}

function connect_get_thread() {
	connect_thread_info.subject = GetId('connect_thread_title').value;
	if (GetId('postmessage_' + connect_thread_info.post_id)) {
		connect_thread_info.html_content = preg_replace(["'"], ['%27'], encodeURIComponent(preg_replace(['本帖最后由 .*? 于 .*? 编辑','&nbsp;','<em onclick="copycode\\(\\$\\(\'code0\'\\)\\);">复制代码</em>'], ['',' ', ''], GetId('postmessage_' + connect_thread_info.post_id).innerHTML)));
	}
	return connect_thread_info;
}

function lazyload(className) {
	var obj = this;
	lazyload.className = className;
	this.getOffset = function (el, isLeft) {
		var  retValue  = 0 ;
		while  (el != null ) {
			retValue  +=  el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return  retValue;
	};
	this.initImages = function (ele) {
		lazyload.imgs = [];
		var eles = lazyload.className ? C(lazyload.className, ele) : [document.body];
		for (var i = 0; i < eles.length; i++) {
			var imgs = eles[i].getElementsByTagName('IMG');
			for(var j = 0; j < imgs.length; j++) {
				if(imgs[j].getAttribute('file') && !imgs[j].getAttribute('lazyloaded')) {
					if(this.getOffset(imgs[j]) > document.documentElement.clientHeight) {
						lazyload.imgs.push(imgs[j]);
					} else {
						imgs[j].setAttribute('src', imgs[j].getAttribute('file'));
						imgs[j].setAttribute('lazyloaded', 'true');
					}
				}
			}
		}
	};
	this.showImage = function() {
		this.initImages();
		if(!lazyload.imgs.length) return false;
		var imgs = [];
		var scrollTop = Math.max(document.documentElement.scrollTop , document.body.scrollTop);
		for (var i=0; i<lazyload.imgs.length; i++) {
			var img = lazyload.imgs[i];
			var offsetTop = this.getOffset(img);
			if (!img.getAttribute('lazyloaded') && offsetTop > document.documentElement.clientHeight && (offsetTop  - scrollTop < document.documentElement.clientHeight)) {
				var dom = document.createElement('div');
				var width = img.getAttribute('width') ? img.getAttribute('width') : 100;
				var height = img.getAttribute('height') ? img.getAttribute('height') : 100;
				dom.innerHTML = '<div style="width: '+width+'px; height: '+height+'px;background: url('+IMGDIR + '/loading.gif) no-repeat center center;"></div>';
				img.parentNode.insertBefore(dom.childNodes[0], img);
				img.onload = function () {if(!this.getAttribute('_load')) {this.setAttribute('_load', 1);this.style.width = this.style.height = '';this.parentNode.removeChild(this.previousSibling);}};
				img.style.width = img.style.height = '1px';
				img.setAttribute('src', img.getAttribute('file') ? img.getAttribute('file') : img.getAttribute('src'));
				img.setAttribute('lazyloaded', true);
			} else {
				imgs.push(img);
			}
		}
		lazyload.imgs = imgs;
		return true;
	};
	this.showImage();
	_attachEvent(window, 'scroll', function(){obj.showImage();});
}
function update_collection(){
	sum = 1;
    GetId('collectionnumber').innerText = parseInt(GetId('collectionnumber').innerText)+sum;
}
