$(document).ready(function () {
    /* alert(singleFeaturedProduct); */
    /* home page main slider */

    $(".js-group-body").hide();
    $(".js-group-body:first").show();
    $(".js-group-head").click(function () {
        if ($(this).parents('.js-group').hasClass('is-active')) {

            /*$(this).siblings('.js-group-body').slideUp();
            $('.js-group').removeClass('is-active'); */
        } else {
            $('.js-group').removeClass('is-active');
            $(this).parents('.js-group').addClass('is-active');
            $('.js-group-body').slideUp();
            $(this).siblings('.js-group-body').slideDown();
        }
    });


    $('.navchild').hover(function () {
        reinitSlick();
    });
    reinitSlick = function () {
        $('.js-hero-slider').slick("slickPause");
    }

    $('.js-collection-corner').slick(getSlickSliderSettings(5, 1, langLbl.layoutDirection));
    if (langLbl.layoutDirection == 'rtl') {
        var slickOptions = {
            slidesToShow: 1,
            arrows: false,
            dots: true,
            rtl: true,
            autoplay: true,
            centerMode: true,
            centerPadding: '0%',
        }
        if ($('.js-hero-slider .hero-item').length > 1) {
            $('.js-hero-slider').slick(slickOptions);
        }

        $('.featured-item-js').slick({

            centerMode: true,
            centerPadding: '26%',
            slidesToShow: 1,
            rtl: true,
            responsive: [

                {

                    breakpoint: 768,
                    settings: {

                        arrows: false,
                        centerMode: true,
                        centerPadding: '5%',
                        slidesToShow: 3

                    }

                },
                {

                    breakpoint: 500,
                    settings: {

                        arrows: false,
                        centerMode: true,
                        centerPadding: '0%',
                        slidesToShow: 1

                    }

                }

            ]

        });
        $('.fashion-corner-js').slick({
            dots: false,
            arrows: false,
            autoplay: true,
            pauseOnHover: true,
            slidesToShow: 6,
            rtl: true,
            responsive: [{
                    breakpoint: 1025,
                    settings: {
                        arrows: false,
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                    }
                }
            ]
        });
    } else {
        var slickOptions = {
            slidesToShow: 1,
            arrows: false,
            dots: true,
            autoplay: true,
            centerMode: true,
            centerPadding: '0%',
        }

        if ($('.js-hero-slider .hero-item').length > 1) {
            $('.js-hero-slider').slick(slickOptions);
        }

        $('.featured-item-js').slick({
            centerMode: true,
            centerPadding: '26%',
            slidesToShow: 1,
            responsive: [{
                    breakpoint: 768,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '5%',
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        arrows: false,
                        centerMode: true,
                        centerPadding: '0%',
                        slidesToShow: 1
                    }
                }
            ]
        });
        $('.fashion-corner-js').slick({
            dots: false,
            arrows: false,
            autoplay: true,
            pauseOnHover: true,
            slidesToShow: 6,
            responsive: [{
                    breakpoint: 1025,
                    settings: {
                        arrows: false,
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        arrows: false,
                        slidesToShow: 1,
                    }
                }
            ]
        });
    }

    /*Tabs*/
    $(".tabs-content-home--js").hide();
    $(".faqTabs--flat-js").each(function (index, element) {
        var sectionId = $(element).parents('section').attr('id');
        $('#' + sectionId + ' .faqTabs--flat-js li:first').addClass("is-active active").show();
    });
    $(".tabs-content-home--js").each(function (index, element) {
        var sectionId = $(element).parents('section').attr('id');
        $('#' + sectionId + ' .tabs-content-home--js:first').addClass("is-active active").show();
    });

    $(".faqTabs--flat-js li").click(function (e) {
		e.preventDefault();
        var sectionId = $(this).parents('section').attr('id');
        $("#" + sectionId + " .faqTabs--flat-js li").removeClass("is-active active");
        $(this).addClass("is-active active");
        $("#" + sectionId + " .tabs-content-home--js").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
		$(activeTab +' .js-carousel_new:not(.slick-initialized)').slick(getNewSlickSliderSettings(activeTab + ' .js-carousel_new'));
        return false;
    });
});
resendOtp = function (userId, getOtpOnly = 0) {
    $.mbsmessage(langLbl.processing, false, 'alert--process');
    fcom.ajax(fcom.makeUrl('GuestUser', 'resendOtp', [userId, getOtpOnly]), '', function (t) {
        t = $.parseJSON(t);
        if (1 > t.status) {
            $.mbsmessage(t.msg, false, 'alert--danger');
            return false;
        }
        $.mbsmessage(t.msg, true, 'alert--success');
        startOtpInterval();
    });
    return false;
};
validateOtp = function (frm) {
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('GuestUser', 'validateOtp'), data, function (t) {
        t = $.parseJSON(t);
        if (1 == t.status) {
            window.location.href = t.redirectUrl;
        } else {
            invalidOtpField();
        }
    });
    return false;
};