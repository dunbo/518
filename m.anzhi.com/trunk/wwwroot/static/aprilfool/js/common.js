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
})
