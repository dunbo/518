function totag(tagid)
{
	var tagurl = [
		'/index.php/Sj/EbookRecommendVerify/UnverifiedList',
		'/index.php/Sj/EbookRecommendVerify/AvailableList',
		'/index.php/Sj/EbookRecommendVerify/UnavailableList',
		'/index.php/Sj/EbookRecommendVerify/VerifyHistory',
	];
	//alert(tagid);
	window.location.href = tagurl[tagid];
	//alert(tagid);
}

function pass(vid)
{
	return confirm("确定要通过么？");
	if (confirm("确定要通过么？"))
	{
		window.location = '/index.php/Sj/EbookRecommendVerify/UnverifiedList/action/pass/id/' + vid;
	}
}

function refuse(vid)
{
	$("#bgdiv").css("display","block");
	$("#bgdiv").css({width:$(document).width()+"px",height:$(document).height()+"px"});
	
	$("#refuse_div").css({display:"block",position:"absolute",zIndex:"2"});
	$("#refuse_div").css("top",($(window).height()-$("#refuse_div")[0].offsetHeight)/2+$(document).scrollTop()+"px");
	$("#refuse_div").css("left",($(window).width()-$("#refuse_div")[0].offsetWidth)/2+$(document).scrollLeft()+"px");
	$("#refuseid").val(vid);
}

function popclose(id) {
	$("#refuse_div").css({display:"none"});
	$("#bgdiv").css({display:"none"});
}

function change_type(r_type) {
	if (r_type != 3) {
		$("#sub_page")[0].selectedIndex = 0;
		$("#sub_page").attr('disabled', 'disabled');
	}
	else {
		$("#sub_page").removeAttr('disabled');
	}
}
	
function checkval()
{
	if($('#search_key').val() == '请输入包名或软件名称'){
		$('#search_key').val('');
	}
}