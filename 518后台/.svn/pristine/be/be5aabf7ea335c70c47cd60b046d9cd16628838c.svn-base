function ModifyUserInfo(){
	
	var ui = document.getElementById("UserInfo");
	var ifr = document.getElementById("mm");
	ifr.src="/address/UserInfoModify.php";
	ui.style.left = (window.document.body.clientWidth - parseInt(ui.style.width))/2;
	ui.style.top = (window.document.body.clientHeight - parseInt(ui.style.height))/2;
	var userin = document.getElementById("in");
	userin.style.left = -5;
	userin.style.top = -5;
	ui.style.display = "block";
}

function CloseDiv(){
	var ifr = document.getElementById("mm");
	ifr.src="";
	document.getElementById('UserInfo').style.display='none';
}

function CloseDiv(dv){
	if(document.getElementById("simg").src="/images/point_up.gif");
		document.getElementById("simg").src = "/images/point.gif";
	tips = false;
	document.getElementById(dv).style.display = 'none';	
}
function AppendMyMail(inp){
	CloseAllDiv();
	var pt = document.getElementById(inp);
	if(window.panelControl==null){
		var pnControl = document.createElement("DIV");
		pnControl.id = "panelControl";
		pnControl.style.border = "1px solid #000";
		pnControl.style.width = pt.style.width;
		pnControl.style.height = "100px";
		pnControl.style.background = '#FFFFFF';
		pnControl.style.display = "block";

		pnControl.style.position = "absolute";
		var x,y;
		x = pt.offsetLeft;
		y = pt.offsetTop;
		el = pt;
		while(el = el.offsetParent){
			x += el.offsetLeft;
			y += el.offsetTop;
		}
		pnControl.style.left = x;
		pnControl.style.top = y+23;
		pnControl.innerHTML = "<DIV STYLE='z-index:auto;width:"+pt.style.width+";height:22px;background:#D9E8F7; text-align:right;padding-top:4px'><img src=\"/images/close.gif\" style=\"cursor:pointer\" onclick=\"CloseDiv('panelControl');\">&nbsp;</DIV><iframe id=sss scrolling=no height=100% width=100% name=bb  border=0 marginWidth=0 marginHeight=0 frameBorder=0 class=rc scrolling=yes src=\"/address/MyLinkMan.php?p="+inp+"\"></iframe>";
		document.body.appendChild(pnControl);
	}else{
		var pnControl =  document.getElementById("panelControl");
		var x,y;
		x = pt.offsetLeft;
		y = pt.offsetTop;
		el = pt;
		while(el = el.offsetParent){
			x += el.offsetLeft;
			y += el.offsetTop;
		}
		pnControl.style.left = x;
		pnControl.style.top = y+23;
		pnControl.innerHTML = "<DIV STYLE='z-index:auto;width:"+pt.style.width+";height:22px;background:#D9E8F7; text-align:right;padding-top:4px'><img src=\"/images/close.gif\" style=\"cursor:pointer\" onclick=\"CloseDiv('panelControl');\">&nbsp;</DIV><iframe id=sss scrolling=no height=100% width=100% name=bb  border=0 marginWidth=0 marginHeight=0 frameBorder=0 class=rc scrolling=yes src=\"/address/MyLinkMan.php?p="+inp+"\"></iframe>";
	}
	pnControl.style.display = "block";
	return false;
}

function CloseAllDiv(){
	//document.getElementById('UserInfo').style.display='none';
	if(window.panelControl!=null){
		document.getElementById("panelControl").style.display = 'none';
	}	
}

function SearchLink(img, inp){
		if(tips == false){
				tips = true;
				img.src = "/images/point_up.gif";
				var searchlink = "/report/my.php";
				var pt = document.getElementById(inp);
				var mylink = "/report/my.php";
				if(window.searchControl==null){
					var pnControl = document.createElement("DIV");
					pnControl.id = "searchControl";
					pnControl.style.border = "";
					pnControl.style.width = pt.style.width;
					pnControl.style.height = "200px";
					pnControl.style.background = '#FFFFFF';
					pnControl.style.display = "block";
			
					pnControl.style.position = "absolute";
					var x,y;
					x = pt.offsetLeft;
					y = pt.offsetTop;
					el = pt;
					while(el = el.offsetParent){
						x += el.offsetLeft;
						y += el.offsetTop;
					}
					pnControl.style.left = x;
					pnControl.style.top = y+23;
					pnControl.innerHTML = "<DIV STYLE='border-left:1px solid #999; border-top:1px solid #999;border-right:1px solid #999;z-index:auto;height:25px;background:#D9E8F7; text-align:right;padding-top:4px'><img src=\"/images/close.gif\" style=\"cursor:pointer\" onclick=\"CloseDiv('searchControl');\">&nbsp;</DIV><div style='background:#FFFFFF;height:100%;border-left:1px solid #999; border-bottom:1px solid #999;border-right:1px solid #999'><iframe height=100% id=sss width=99% name=bb  border=0 marginWidth=0 marginHeight=0 frameBorder=0 class=rc src=\""+mylink+"?p="+inp+"\"></iframe></div>";
					document.body.appendChild(pnControl);
				}else{
					var pnControl =  document.getElementById("searchControl");
					var x,y;
					x = pt.offsetLeft;
					y = pt.offsetTop;
					el = pt;
					while(el = el.offsetParent){
						x += el.offsetLeft;
						y += el.offsetTop;
					}
					pnControl.style.left = x;
					pnControl.style.top = y+21;
					pnControl.innerHTML = "<DIV STYLE='border-left:1px solid #999; border-top:1px solid #999;border-right:1px solid #999;z-index:auto;height:25px;background:#D9E8F7; text-align:right;padding-top:4px'><img src=\"/images/close.gif\" style=\"cursor:pointer\" onclick=\"CloseDiv('searchControl');\">&nbsp;</DIV><div style='background:#FFFFFF;height:100%;border-left:1px solid #999; border-bottom:1px solid #999;border-right:1px solid #999'><iframe height=100% id=sss width=99% name=bb  border=0 marginWidth=0 marginHeight=0 frameBorder=0 class=rc src=\""+mylink+"?p="+inp+"\"></iframe></div>";
				}
				pnControl.style.display = "block";
				return false;
				
		}	else{
			tips = false;
			img.src = "/images/point.gif";
			document.getElementById("searchControl").style.display = "none";
		}
	}