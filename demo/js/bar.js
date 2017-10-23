var theme_list_open = !1;
$(document).ready(function() {
    function a() {
        var a = $("#switcher").height() + 11;
        //$("#iframe").css("height", $(window).height() - 70)
    }
    IS_IPAD = null != navigator.userAgent.match(/iPad/i), $(window).resize(function() {
        a()
    }).resize(), $("#theme_select").click(function() {
        return 1 == theme_list_open ? ($(".center ul li ul").hide(), theme_list_open = !1) : ($(".center ul li ul").show(), theme_list_open = !0), !1
    }), $("#theme_list ul li a").click(function() {
        var a = $(this).attr("rel").split(",");
        return $("li.purchase a").attr("href", a[1]), $("li.remove_frame a").attr("href", a[0]), $("#iframe").attr("src", a[0]), $("#theme_list a#theme_select").text($(this).text()), $(".center ul li ul").hide(), theme_list_open = !1, !1
    }), $("#header-bar").hide(), clicked = "desktop";
    var b = {
        desktop: "100%",
        tabletlandscape: 1040,
        tabletportrait: 788,
        mobilelandscape: 500,
        mobileportrait: 340,
        placebo: 0
    };
    jQuery(".responsive a").on("click", function() {
        var a = jQuery(this);
        for (device in b) console.log(device), console.log(b[device]), a.hasClass(device) && (clicked = device, jQuery("#iframe").width(b[device]), clicked == device && (jQuery(".responsive a").removeClass("active"), a.addClass("active")));
        return !1
    }), IS_IPAD && $("#iframe").css("padding-bottom", "60px")
});