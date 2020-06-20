
var t, e = function() {
	t = new Swiper("#specile", {
		scrollbar: "#specile .swiper-scrollbar",
		direction: "vertical",
		slidesPerView: "auto",
		freeMode: !0,
		observer: !0,
		observeParents: !0,
		onTransitionEnd: function() {
			// t.translate < 0 ? $("#article .btn_toTop").addClass("show") : $("#article .btn_toTop").removeClass("show"),
			$("#specile .btn_toTop").click(function(){t.slideTo(0)});
		}
	})
}

e();

new Swiper("#latestList", {
	speed: 500,
	pagination : '#latestList .swiper-pagination',
	paginationType : 'fraction',
	nextButton: '#latestList .swiper-button-next',
	prevButton: '#latestList .swiper-button-prev',
	paginationClickable: !0
})

new Swiper("#activy .swiper-container", {
	pagination: "#activy .swiper-pagination",
	prevButton:'#activy .swiper-button-prev',
	nextButton:'#activy .swiper-button-next',
	paginationClickable: !0
})
new Swiper("#gamepoint .swiper-container", {
	nextButton:"#gamepoint .swiper-button-next",
	prevButton:'#gamepoint .swiper-button-prev',
	pagination: "#gamepoint .swiper-pagination",
	paginationClickable: !0
});
function switchBg() {
	function e() {
		p.removeClass("active").eq(i).addClass("active"), s.removeClass("active").eq(i).addClass("active")
	}
	var t, n, a = $(".profTitle a"),
		s = $(".prof_bd .prof_list"),
		p = $(".rwpic img"),
		v = $(".leftBtn"),
		x = $(".rightBtn"),
		i = 0;
	var aBtn=$("#Slide");
	v.bind("click", function() {
		t = i--, i < 0 && (i = a.length-1), e()
	})
	x.bind("click", function() {
		t = i++, i >= a.length && (i = 0), e()
	});
	var left = a.position().left;

	s.bind("swipeLeft", function() {
		t = i--, i < 0 && (i = a.length-1), e()
	})
	s.bind("swipeRight", function() {
		t = i++, i >= a.length && (i = 0), e()
	})

}

//switchBg();
