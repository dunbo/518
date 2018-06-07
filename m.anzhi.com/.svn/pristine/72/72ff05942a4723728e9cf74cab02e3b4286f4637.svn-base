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
                    _this.css('height',hgt+"em");
                    moreSpan.removeClass(cls);
                }
                else{
                    _this.css('height','auto');
                    moreSpan.addClass(cls);
                }
            }
        if (_this.height() <= hgt) {
            moreCon.hide();
            _this.css("cursor","default");
        }else{
            _this.css("height",hgt+"em");
            _this.bind('click',clickFn);
            moreCon.bind('click',clickFn);
        }
    });
}
$(function(){

});
