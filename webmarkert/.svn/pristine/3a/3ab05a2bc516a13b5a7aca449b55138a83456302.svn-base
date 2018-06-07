
!function() {
    "use anbei";
    //t:li宽；e：li高；a：li(margin-right);n:可视区域宽度；i:最少显示几个
    $.fn.screenshots = function(t, e, a, n, i, o) {
        function s(t) {
            if (C) {
                var e = t || window.event, a = e.pageX || e.clientX;
                e.preventDefault ? e.preventDefault() : window.event.returnValue = !1;
                var n = a - E + Q;
                0 > n ? (D = 0, m.css("left", "0px"), f.css("left", "0px")) : n >= z ? (D = -1 * B, m.css("left", z + "px"), f.css("left", -1 * B + "px")) : (D = parseInt(-1 * n / X / q, 10) * q, m.css("left", n + "px"), f.css("left", -1 * n / X + "px"))
            }
        }
        function r() {
            if (C) {
                var t = $(document);
                t.off("mousemove", s), t.off("mouseup", r), C = !1
            }
        }
        function l(t, e, a) {
            P && void 0 !== t && void 0 !== e ? 0 === t && (e > 50 || -50 > e) ? c(-1 * a) : d(t, e) : c(a), M || (M = !0, p(o))
        }
        function c(t) {
            if (!k && !I) {
                var e = parseInt(f.css("left"), 10);
                0 > t ? e - q >= -1 * B && (k = !0, I = !0, D -= q, -1 * B > D && (D = -1 * B), f.animate({left: D}, 100, function() {
                    k = !1
                }), m.animate({left: -1 * D * X}, 100, function() {
                    I = !1
                })) : 0 > e && (k = !0, I = !0, D += q, D > 0 && (D = 0), f.animate({left: D}, 100, function() {
                    k = !1
                }), m.animate({left: -1 * D * X}, 100, function() {
                    I = !1
                }))
            }
        }
        function d(t) {
            var e = parseInt(f.css("left"), 10), a = e + t;
            a > 0 ? (D = 0, f.css("left", "0px"), m.css("left", "0px")) : -1 * B >= a ? (D = -1 * B, f.css("left", -1 * B), m.css("left", z + "px")) : (D = parseInt(a / q, 10) * q, f.css("left", a + "px"), m.css("left", -1 * a * X + "px"))
        }
        function p(t) {
            t && Mobres.linkTrack({type: "browse",f: "screenshoot_" + t})
        }
        var u = this, f = u.find("ul"), h = $('<div class="screenshots-scroll"><div></div></div>'), m = h.find("div");
        if (f.size()) {
            var v = t, g = e, b = a, w = n, C = !1, y = !1, x = !1, _ = !1, k = !1, I = !1, M = !1, P = navigator.platform.match(/mac/gi) ? !0 : !1, T = u.find("img").size(), q = b + v, j = q * T, D = 0, O = w / (j - b), z = w * (1 - O), B = parseInt(j * (1 - O) / q, 10) * q, X = z / B, E = 0, Q = 0, W = null;
            f.css({width: j + "px",left: "0px"}), f.find("li").css({width: v + "px",height: g + "px","margin-right": b}), m.css({width: 100 * O + "%",left: "0px"}), u.css({width: w,display: "block"}).addClass("screenshots-container"), i >= T || (h.appendTo(u), m.on("mousedown", function(t) {
                var e = t || window.event, a = $(document);
                Q = parseInt(m.css("left"), 10), E = e.pageX || e.clientX, a.on("mousemove", s), a.on("mouseup", r), C = !0, M || (M = !0, p(o))
            }), $(document).on("scroll", function() {
                x || (window.clearTimeout(W), _ = !0, W = setTimeout(function() {
                    _ = !1, y && setTimeout(function() {
                        _ || (x = !0)
                    }, 100)
                }, 200))
            }), "onmousewheel" in document ? $(document).on("mousewheel", function(t) {
                if (x) {
                    var e = t || window.event;
                    e.preventDefault ? e.preventDefault() : window.event.returnValue = !1, l(t.originalEvent.wheelDeltaX, t.originalEvent.wheelDeltaY, t.originalEvent.wheelDelta)
                }
            }) : $(document).on("DOMMouseScroll", function(t) {
                if (x) {
                    var e = t || window.event;
                    e.preventDefault && e.preventDefault(), c(-1 * t.originalEvent.detail), M || (M = !0, p(o))
                }
            }), f.on("mouseover", function() {
                y = !0
            }), f.on("mousemove", function() {
                _ || (x = !0)
            }), f.on("mouseleave", function() {
                y = !1, x = !1
            }))
        }
    }
}();
;

