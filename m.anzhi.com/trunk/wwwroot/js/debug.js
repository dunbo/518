function alert_new(txt)
{
	var shield = document.createElement("DIV");
	var rank = Math.floor(Math.random()*10000);	
	shield.id = "shield"+rank;
	shield.style.position = "absolute";
	shield.style.left = "0px";
	shield.style.top = "0px";
	shield.style.width = "100%";
	shield.style.height = document.body.scrollHeight+"px";
	shield.style.background = "rgba(0,0,0,0.5)";
	shield.style.textAlign = "center";
	shield.style.zIndex = "10000";
	shield.style.filter = "alpha(opacity=0)";
	var alertFram = document.createElement("DIV");
	alertFram.id="alertFram"+rank;
	alertFram.style.position = "absolute";
	alertFram.style.left = "50%";
	alertFram.style.top = "50%";
	alertFram.style.marginLeft = "-165px";
	alertFram.style.marginTop = "-155px";
	alertFram.style.width = "330px";
	//alertFram.style.height = "150px";
	alertFram.style.background = "#ccc";
	alertFram.style.textAlign = "center";
	alertFram.style.lineHeight = "150px";
	alertFram.style.zIndex = "10001";
	strHtml = "<ul style=\"list-style:none;margin:0px;padding:absolute;width:100%\">\n";
	strHtml += " <li style=\"background:#DD828D;text-align:left;padding-left:20px;font-size:14px;font-weight:bold;height:25px;line-height:25px;border:1px solid #F9CADE;\">zhuang</li>\n";
	strHtml += " <li style=\"background:#fff;text-align:center;font-size:12px;line-height:25px;border-left:1px solid #F9CADE;border-right:1px solid #F9CADE; word-break:break-all; word-wrap:break-word;\">"+txt+"</li>\n";
	
	strHtml += " <li style=\"background:#FDEEF4;text-align:center;font-weight:bold;height:35px;line-height:35px; border:1px solid #F9CADE;\"><input type=\"button\" value=\"确 定\" onclick=\"doOk("+rank+")\" /></li>\n";
	strHtml += "</ul>\n";
	alertFram.innerHTML = strHtml;
	document.body.appendChild(alertFram);
	document.body.appendChild(shield);
	var c = 0;
	this.doAlpha = function(){
		if (c++ > 20){clearInterval(ad);return 0;}
		shield.style.filter = "alpha(opacity="+c+");";
	}
	var ad = setInterval("doAlpha()",5);
	this.doOk = function(id){
		var obj = document.getElementById("alertFram"+id);
		var obj_shield = document.getElementById("shield"+id);
		obj.style.display = "none";
		obj_shield.style.display = "none";
		obj.remove();
		obj_shield.remove();
	}
	alertFram.focus();
	document.body.onselectstart = function(){return false;};
	return false;
}
