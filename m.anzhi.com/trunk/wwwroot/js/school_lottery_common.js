/**
 * Created by anbei on 14-10-20.
 */
function conHideShow(obj,hgt,cls){
    $(obj).each(function(){
        var _this=$(this),
            moreCon=$(this).next(".more-con"),
            moreSpan=moreCon.children("span"),
            clickFn=function(){
                if(moreSpan.hasClass(cls))
                {
                    _this.css('height',hgt+"px");
                    moreSpan.removeClass(cls);
					moreSpan.html("更多");
                }
                else{
                    _this.css('height','auto');
                    moreSpan.addClass(cls);
					moreSpan.html("收起");
                }
            }
        if (_this.height() <= hgt) {
            moreCon.hide();
            _this.css("cursor","default");
        }else{
            _this.css("height",hgt+"px");
            _this.bind('click',clickFn);
            moreCon.bind('click',clickFn);
        }
    });
}
$(function(){

});
