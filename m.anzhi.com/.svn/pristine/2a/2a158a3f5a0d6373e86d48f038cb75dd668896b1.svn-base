/**
 * Created by anzhi on 15-1-16.
 */
function btnAnimate(obj){
    obj.bind("touchstart",function(e){
        e.preventDefault();
        $(this).addClass("cur");
    })
    obj.bind("touchend",function(e){
        e.preventDefault();
        $(this).removeClass("cur");
    })
}
$(function(){
    $("a.btnAni").each(function(){
        btnAnimate($(this));
    });
    $(".formitem-btn").each(function(){
        btnAnimate($(this));
    });
	var bodyHeight=document.body.scrollHeight||document.documentElement.scrollHeight;
    var vHeight=document.body.clientHeight||document.documentElement.clientHeight;
    if(bodyHeight<=vHeight){
        $("section:first").css("height","100%");
    }else{
        $("section:first").css("height","auto");
    }
})
