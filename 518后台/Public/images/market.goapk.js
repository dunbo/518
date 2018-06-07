function addFavourite(url){
	// alert(scope.$current_softid);
	$.post(url, {action: "add_favourite", softid: scope.$current_softid}, function(xml) {
       // format and output result
        var reg = /<code>(.*?)<\/code>/;
        var code = reg.exec(xml)[1];
       //alert($("code", xml).text() );
        if(code == "0") {
           $("#favourite_app").html("<center class=\"gray\">已收藏</center>");
       }
    });
	//doGet(url,null);
}

// 0-10
function showRate(star)
{
	var id_prefix = "#post_star_";
	var url_star_none = "/images/star_0.png";
	var url_star_half = "/images/star_1.png";
	var url_star_full = "/images/star_2.png";

	for(var i = 1; i <= 5; i++)
	{
		var id_name = id_prefix + String(i*2);
		if(star > 1)
		{
			$(id_name).attr({src: url_star_full});
		}
		else if(star == 1)
		{
			$(id_name).attr({src: url_star_half});
		}
		else
		{
			$(id_name).attr({src: url_star_none});
		}
		star -= 2;
	}
}

function setRate(star)
{
	scope.$starring = star;
}

function getRate()
{
	return scope.$starring || 0;
}

$(document).ready(function() {
    scope.$starring =  0;
	$("#post_star_2").click(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		var s = x < -147 ? 1 : 2;
		showRate(s);
		setRate(s);
		$("#submit_comment").attr({style: "display: inline"});
	});
	$("#post_star_2").mousemove(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		showRate(x < -147 ? 1 : 2);
	});
	$("#post_star_4").click(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		var s = x < -147 ? 3 : 4;
		showRate(s);
		setRate(s);
		$("#submit_comment").attr({style: "display: inline"});
	});
	$("#post_star_4").mousemove(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		showRate(x < -147 ? 3 : 4);
	});

	$("#post_star_6").click(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		var s = x < -147 ? 5 : 6;
		showRate(s);
		setRate(s);
		$("#submit_comment").attr({style: "display: inline"});
	});
	$("#post_star_6").mousemove(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		showRate(x < -147 ? 5 : 6);
	});
	$("#post_star_8").click(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		var s = x < -147 ? 7 : 8;
		showRate(s);
		setRate(s);
		$("#submit_comment").attr({style: "display: inline"});
	});
	$("#post_star_8").mousemove(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		showRate(x < -147 ? 7 : 8);
	});
	$("#post_star_10").click(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		var s = x < -147 ? 9 : 10;
		showRate(s);
		setRate(s);
		$("#submit_comment").attr({style: "display: inline"});
	});
	$("#post_star_10").mousemove(function(e) {
		var x = e.originalEvent.x-$(this).offset().left||e.originalEvent.layerX-$(this).offset().left||0;
		showRate(x < -147 ? 9 : 10);
	});
	$("#post_star").mouseleave(function() {
		showRate(getRate());
	});
	$("#post_button").click(function() {
		if($("#post_text").attr("value").length == 0)
		{
			alert("评论是空的：）");
			return;
		}

		$.post("/ajax.php", {action: "add_comment", softid: scope.$current_softid, comment_star: getRate(), comment_text: $("#post_text").attr("value")}, function(xml) {
			var reg = /<code>(.*?)<\/code>/;
			var code = reg.exec(xml)[1];
			if(code == "0")
			{
				//alert(xml);
				var reg = /<data>(.*?)<\/data>/;
				var res = reg.exec(xml);
				$("#post_comment").attr({style: "display: none"});
				$("#comments_table tbody").prepend(res[1]);
			} else {
				alert("评论失败");
            }
		});
	});
    if(window.externalCall) {
        $('#download_app a img').attr("src", "/images/install.png");
        $('#download_app a').bind('click',function(){
                var name = $("#app_name").text();
                var href = $(this).attr('href');
                if(href.indexOf("http") != 0) href="http://"+window.location.host+href;
                var paras = [{name:name,downloadUrl : href}];
                window.externalCall('appslist','do_downloadInstallApps',JSON.stringify(paras));
        });
        $('#download_market a').attr("href", "/wdj_download.php");
        $('#download_market a').bind('click',function(){
                href="http://att.goapk.com/market/GoMarket1.3.5_37_wdj.apk";
                var paras = [{name:"安智市场",downloadUrl : href}];
                window.externalCall('appslist','do_downloadInstallApps',JSON.stringify(paras));
        });
    }  
    //$('.zero_top').text(JSON.stringify(parent));
    //$('.zero_top').text(parent.getApplication());
    //$('.zero_top').text("hi, wdj");
 });

function doSearch()
{
	if(document.getElementById("search_key").value.length < 1)
	{
		alert("请输出搜索关键词!");
		return;
	}
	document.forms.search.submit();
}

