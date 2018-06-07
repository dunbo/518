!function(e) {
    e.fn.apphover = function() {
        return this.on("mouseenter", "li", function() {
            var t = e(this);
            t.addClass("active"), t.find(".star").hide(), t.find(".down span").animate({top: "0"}, 150, function() {
                e(this).show()
            })
        }).on("mouseleave", "li", function() {
                var t = e(this);
                t.removeClass("active"),t.find(".star").show(), t.find(".down span").css("top", "10px")
            }).on("mousemove", "li", function(t) {
        var n = e(this), i = n.find(".pop_soft"), a = t.pageX, o = t.pageY, s = o - 60, r = a + 20;
        n.hasClass("last") && (r = a - 218), 440 > o && (s = Math.max(s, 180)), i.css({top: s,left: r})
    })

    }
}(window.jQuery);

$(function() {
	$(".recommend").apphover();
});
