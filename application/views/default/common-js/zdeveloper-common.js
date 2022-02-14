var IS_PRODUCT_LISTING = false;
$(document).ready(function () {
    /* $("input:text:visible:first").focus(); */
    
    setTimeout(function () {
        $('body').addClass('loaded');
        if (0 < $('#scrollElement-js').length && document.getElementById('scrollElement-js').SimpleBar != undefined) {
            let scrollElement = document.getElementById('scrollElement-js').SimpleBar.getScrollElement();
            if ($('.menu__item.is-active').position() != null && $('.menu__item.is-active').position() != undefined) {
                scrollElement.scrollTop = ($('.menu__item.is-active').position().top - (($(window).height() / 2) - 100));
            }
        }
    }, 1000);

    $(document).on("click", ".selectItem--js", function () {
        if ($(this).prop("checked") == false) {
            $(".selectAll-js").prop("checked", false);
            $(this).closest("tr").removeClass('selected-row');
        } else {
            $(this).closest("tr").addClass('selected-row');
        }

        if ($(".selectItem--js").length == $(".selectItem--js:checked").length) {
            $(".selectAll-js").prop("checked", true);
        }
        showFormActionsBtns();
    });

    if (0 < $('.js-widget-scroll').length) {
        slickWidgetScroll();
    }

    /*$(document).on('change', 'input.phone-js', function(e) {
     $(this).keydown()
     });
     $(document).on('keydown', 'input.phone-js', function(e) {
     var key = e.which || e.charCode || e.keyCode || 0;
     $phone = $(this);
     
     // Don't let them remove the starting '('
     if ($phone.val().length === 1 && (key === 8 || key === 46)) {
     $phone.val('(');
     return false;
     }
     // Reset if they highlight and type over first char.
     else if ($phone.val().charAt(0) !== '(') {
     $phone.val('(');
     }
     
     // Auto-format- do not expose the mask as the user begins to type
     if (key !== 8 && key !== 9) {
     if ($phone.val().length === 4) {
     $phone.val($phone.val() + ')');
     }
     if ($phone.val().length === 5) {
     $phone.val($phone.val() + ' ');
     }
     if ($phone.val().length === 9) {
     $phone.val($phone.val() + '-');
     }
     }
     
     // Allow numeric (and tab, backspace, delete, hyphen, space) keys only
     return (key == 8 ||
     key == 9 ||
     key == 46 ||
     key == 189 ||
     key == 32 ||
     (key >= 48 && key <= 57) ||
     (key >= 96 && key <= 105));
     });
     $(document).on('blur', 'input.phone-js', function() {
     $phone = $(this);
     if ($phone.val() === '(') {
     $phone.val('');
     }
     });*/

    $(document).on('click', '.accordianheader', function () {
        $(this).next('.accordianbody').slideToggle();
        $(this).parent().parent().siblings().children().children().next().slideUp();
        return false;
    });

    if ('rtl' == langLbl.layoutDirection && 0 < $("[data-simplebar]").length && 1 > $("[data-simplebar-direction='rtl']").length) {
        $("[data-simplebar]").attr('data-simplebar-direction', 'rtl');
    }
});


$(document).on('keyup', 'input.otpVal-js', function (e) {
    if ('' != $(this).val()) {
        $(this).removeClass('is-invalid');
    }

    var element = '';

    /* 
     # e.which = 8(Backspace)
     */
    if (8 != e.which && '' != $(this).val()) {
        element = ($(this).parents('.otpCol-js').nextAll())[0];
    } else {
        element = ($(this).parents('.otpCol-js').prevAll())[0];
    }
    element = $(element).find("input.otpVal-js");
    if ('undefined' != typeof element) {
        element.focus();
    }
});

unlinkSlick = function () {
    $('.js-widget-scroll').slick('unslick');
}

slickWidgetScroll = function () {
    var slides = ($('.widget-stats').length > 2) ? 3 : 2;
    $('.js-widget-scroll').slick(getSlickSliderSettings(slides, 1, langLbl.layoutDirection, false, {
        1199: 3,
        1023: 2,
        767: 1,
        480: 1
    }));
}

invalidOtpField = function () {
    $("input.otpVal-js").val('').addClass('is-invalid').attr('onkeyup', 'checkEmpty($(this))');
}

checkEmpty = function (element) {
    if ('' == element.val()) {
        element.addClass('is-invalid');
    }
}

var otpIntervalObj;
startOtpInterval = function (parent = '', callback = '', params = []) {
    if ('undefined' != typeof otpIntervalObj) {
        clearInterval(otpIntervalObj);
    }

    var parent = '' != parent ? parent + ' ' : '';
    var element = $(parent + ".intervalTimer-js");
    var counter = langLbl.otpInterval;
    element.parent().parent().show();
    element.text(counter);
    $(parent + '.resendOtp-js').addClass('d-none');
    otpIntervalObj = setInterval(function () {
        counter--;
        if (counter === 0) {
            clearInterval(otpIntervalObj);
            $(parent + '.resendOtp-js').removeClass('d-none');
            element.parent().parent().hide();
            if ('' != callback && eval("typeof " + callback) == 'function') {
                window[callback](params);
            }
        }
        element.text(counter);
    }, 1000);
}



loginPopupOtp = function (userId, getOtpOnly = 0) {
    $.mbsmessage(langLbl.processing, false, 'alert--process');
    var isLoginPopup = 0;
    if (0 < $('#exampleModal #formLoginPage').length) {
        isLoginPopup = 1;
    }

    fcom.ajax(fcom.makeUrl('GuestUser', 'resendOtp', [userId, getOtpOnly, isLoginPopup]), '', function (t) {
        t = $.parseJSON(t);
        if (1 > t.status) {
            $.mbsmessage(t.msg, false, 'alert--danger');
            return false;
        }
        $.mbsmessage.close();
        var parent = '';
        if (isLoginPopup == 1) {
            /* fcom.updateFaceboxContent(t.html, 'faceboxWidth loginpopup');  */
            $('#exampleModal .modal-content').html(t.html);
            $('#exampleModal').modal('show');
            var parent = '#exampleModal';
        } else {
            $('#sign-in').html(t.html);
        }
        startOtpInterval(parent);
    });
    return false;
};

function setCurrDateFordatePicker() {
    $('.start_date_js').datepicker('option', {
        minDate: new Date()
    });
    $('.end_date_js').datepicker('option', {
        minDate: new Date()
    });
}

function showFormActionsBtns() {
    if (typeof $(".selectItem--js:checked").val() === 'undefined') {
        $(".formActionBtn-js").addClass('formActions-css');
    } else {
        $(".formActionBtn-js").removeClass('formActions-css');
    }

    var validateActionButtons = setInterval(function () {
        if (1 > $(".selectItem--js:checked").length) {
            $(".formActionBtn-js").addClass('formActions-css');
            clearInterval(validateActionButtons);
        }

        if ($(".formActionBtn-js").hasClass('formActions-css')) {
            clearInterval(validateActionButtons);
        }
    }, 1000);
}

function selectAll(obj) {
    $(".selectItem--js").each(function () {
        if (obj.prop("checked") == false) {
            $(this).prop("checked", false).closest('tr').removeClass('selected-row');
        } else {
            $(this).prop("checked", true).closest('tr').addClass('selected-row');
        }
    });
    showFormActionsBtns();
}

function formAction(frm, callback) {
    if (typeof $(".selectItem--js:checked").val() === 'undefined') {
        $.mbsmessage(langLbl.atleastOneRecord, true, 'alert--danger');
        return false;
    }

    $.mbsmessage(langLbl.processing, true, 'alert--process alert');
    data = fcom.frmData(frm);

    fcom.updateWithAjax(frm.action, data, function (resp) {
        callback();
    });
}

function initialize() {
    geocoder = new google.maps.Geocoder();
}

function getCountryStates(countryId, stateId, dv) {
    $(dv).empty();
    fcom.ajax(fcom.makeUrl('GuestUser', 'getStates', [countryId, stateId]), '', function (res) {
        $(dv).append(res);
    });
}
;

function getStatesByCountryCode(countryCode, stateCode, dv, idCol = 'state_id') {
    $(dv).empty();
    fcom.ajax(fcom.makeUrl('GuestUser', 'getStatesByCountryCode', [countryCode, stateCode, idCol]), '', function (res) {
        $(dv).append(res).change();
    });
}
;
function getNewSlickSliderSettings(divIdentifier) {
    var _this = $(divIdentifier)
    if (_this.data("slides") == undefined || _this.data("slides") == '') {
        return;
    }
    
    
    var _slidesToShow = _this.data("slides").toString().split(',');
    var optionsArr = {
            slidesToShow: parseInt(_slidesToShow.length > 0 ? _slidesToShow[0] : "3"),
            slidesToScroll: 1,
            centerMode: _this.data("mode"),
            arrows: _this.data("arrows"),
            vertical: _this.data("vertical"),
            dots: _this.data("slickdots"),
            infinite: _this.data("infinite"),   
            variableWidth: (_this.data("variablewidth") != undefined) ? _this.data("variablewidth") : false,
            autoplay: false,
            pauseOnHover: false,
            swipe: (_this.data("swipe") != undefined) ? _this.data("swipe") : false,
            swipeToSlide: (_this.data("swipetoslide") != undefined) ? _this.data("swipetoslide") : false,
            centerPadding: 0,
            adaptiveHeight: true,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 1 ? _slidesToShow[1] : "2")),
                        vertical: false
                    }
                },
                {
                    breakpoint: 992,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 2 ? _slidesToShow[2] : "1")),
                        vertical: false
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 3 ? _slidesToShow[3] : "1")),
                        vertical: false
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: parseInt(parseInt(_slidesToShow.length > 4 ? _slidesToShow[4] : "1")),
                        vertical: false
                    }
                }
            ]
        };
        if (_this.data("arrows") == true) {
            if (_this.data("customarrow") != undefined && _this.data("customarrow") != '') {
               /*  optionsArr['appendArrows'] = '.'+ _this.data('arrowcontainer'); */
                optionsArr['prevArrow'] = $('.'+ _this.data('arrowcontainer') + ' .arrow-prev');
                optionsArr['nextArrow'] = $('.'+ _this.data('arrowcontainer') + ' .arrow-next');
            }
        }
    
    _this.slick(optionsArr);
}


function recentlyViewedProducts(selprodId) {
    if (typeof selprodId == 'undefined') {
        selprodId = 0;
    }

    $("#recentlyViewedProductsDiv").html(fcom.getLoader());

    fcom.ajax(fcom.makeUrl('Products', 'recentlyViewedProducts', [selprodId]), '', function (ans) {
        if(typeof ans !== 'undefined' && ans != '') {
            $("#recentlyViewedProductsDiv").html(ans);
            $('.js-carousel:not(.slick-initialized)').slick(getNewSlickSliderSettings('#recentlyViewedProductsDiv .js-carousel'));
        }else{
            $("#recentlyViewedProductsDiv").hide();
        }

    });
}

function resendVerificationLink(user) {
    if (user == '') {
        return false;
    }
    $(document).trigger('close.systemMessage');
    $.mbsmessage(langLbl.processing, false, 'alert--process alert');
    fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'resendVerification', [user]), '', function (ans) {
        $.mbsmessage(ans.msg, true, 'alert--success');
    });
}

function getCardType(number) {
    /* visa */
    var re = new RegExp("^4");
    if (number.match(re) != null)
        return "Visa";

    /* Mastercard */
    re = new RegExp("^5[1-5]");
    if (number.match(re) != null)
        return "Mastercard";

    /* AMEX */
    re = new RegExp("^3[47]");
    if (number.match(re) != null)
        return "AMEX";

    /* Discover */
    re = new RegExp("^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)");
    if (number.match(re) != null)
        return "Discover";

    /* Diners */
    re = new RegExp("^36");
    if (number.match(re) != null)
        return "Diners";

    /* Diners - Carte Blanche */
    re = new RegExp("^30[0-5]");
    if (number.match(re) != null)
        return "Diners - Carte Blanche";

    /* JCB */
    re = new RegExp("^35(2[89]|[3-8][0-9])");
    if (number.match(re) != null)
        return "JCB";

    /* Visa Electron */
    re = new RegExp("^(4026|417500|4508|4844|491(3|7))");
    if (number.match(re) != null)
        return "Visa Electron";

    return "";
}

viewWishList = function (selprod_id, dv, event, excludeWishList = 0) {
    event.stopPropagation();
    /*var dv = "#listDisplayDiv_" + selprod_id; */

    if ($(dv).next().hasClass("is-item-active")) {
        $(dv).next().toggleClass('open-menu');
        $(dv).parent().toggleClass('list-is-active');
        return;
    }
    $('.collection-toggle').next().removeClass("is-item-active");
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }

    /* $.facebox(function () { */
        fcom.ajax(fcom.makeUrl('Account', 'viewWishList', [selprod_id, excludeWishList]), '', function (ans) {
            /* fcom.updateFaceboxContent(ans, 'faceboxWidth collection-ui-popup small-fb-width'); */
            $('#exampleModal').html(ans);
            $('#exampleModal').modal('show');
            /*$(dv).next().html(ans);*/
            $("input[name=uwlist_title]").bind('focus', function (e) {
                e.stopPropagation();
            });

            activeFavList = selprod_id;

        });

    /* }); */

    return false;
}

toggleShopFavorite = function (shop_id) {
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }
    var data = 'shop_id=' + shop_id;
    fcom.updateWithAjax(fcom.makeUrl('Account', 'toggleShopFavorite'), data, function (ans) {
        if (ans.status) {
            if (ans.action == 'A') {
                $("#shop_" + shop_id).addClass("is-active");
                $("#shop_" + shop_id).prop('title', 'Unfavorite Shop');

                $("#shop_" + shop_id +' .icon-unchecked--js').hide();
                $("#shop_" + shop_id +' .icon-checked--js').show();

            } else if (ans.action == 'R') {
                $("#shop_" + shop_id).removeClass("is-active");
                $("#shop_" + shop_id).prop('title', 'Favorite Shop');

                $("#shop_" + shop_id +' .icon-unchecked--js').show();
                $("#shop_" + shop_id +' .icon-checked--js').hide();
            }
        }
    });

}

setupWishList = function (frm, event) {
    if (!$(frm).validate())
        return false;
    var data = fcom.frmData(frm);
    var selprod_id = $(frm).find('input[name="selprod_id"]').val();
    fcom.updateWithAjax(fcom.makeUrl('Account', 'setupWishList'), data, function (ans) {

        if (ans.status) {
            fcom.ajax(fcom.makeUrl('Account', 'viewWishList', [selprod_id]), '', function (ans) {
                /* $(".collection-ui-popup").html(ans); */
                $("#exampleModal").html(ans);
                $("input[name=uwlist_title]").bind('focus', function (e) {
                    e.stopPropagation();
                });
            });
            if (ans.productIsInAnyList) {
                $("[data-id=" + selprod_id + "]").addClass("is-active");
            } else {
                $("[data-id=" + selprod_id + "]").removeClass("is-active");
            }
        }
    });
}

addRemoveWishListProduct = function (selprod_id, wish_list_id, event) {
    event.stopPropagation();
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }
    wish_list_id = (typeof (wish_list_id) != "undefined") ? parseInt(wish_list_id) : 0;
    /* var dv = ".collection-ui-popup"; */
    var dv = "#exampleModal";
    var action = 'addRemoveWishListProduct';
    var alternateData = '';
    if (0 >= selprod_id) {
        var oldWishListId = $("input[name='uwlist_id']").val();
        if (typeof oldWishListId !== 'undefined' && wish_list_id != oldWishListId) {
            action = 'updateRemoveWishListProduct';
            alternateData = $('#wishlistForm').serialize();
        }
    }

    fcom.updateWithAjax(fcom.makeUrl('Account', action, [selprod_id, wish_list_id]), alternateData, function (ans) {
        if (ans.status == 1) {
            $("#exampleModal .close").click();
            $(dv + " .is-active").removeClass("is-active");
            if (ans.productIsInAnyList) {
                $("[data-id=" + selprod_id + "]").addClass("is-active");
            } else {
                $("[data-id=" + selprod_id + "]").removeClass("is-active");
            }
            if (ans.action == 'A') {
                events.addToWishList();
                $(dv).find(".wishListCheckBox_" + ans.wish_list_id).addClass('is-active');
            } else if (ans.action == 'R') {
                $(dv).find(".wishListCheckBox_" + ans.wish_list_id).removeClass('is-active');
            }

            if ('updateRemoveWishListProduct' == action) {
                viewWishListItems(oldWishListId);
            }
            /* updates for product details page */
                if ($('.heart-wrapper--js').hasClass('is-active')) {
                    $('.heart-wrapper--js .icon-unchecked--js').hide();
                    $('.heart-wrapper--js .icon-checked--js').show();
                } else {
                    $('.heart-wrapper--js .icon-unchecked--js').show();
                    $('.heart-wrapper--js .icon-checked--js').hide();
                }
            /* ] */
        }
    });
};

removeFromCart = function (key) {
    var data = 'key=' + key;
    fcom.updateWithAjax(fcom.makeUrl('Cart', 'remove'), data, function (ans) {
        if (ans.status) {
            if (ans.total == 0) {
                $('.emtyCartBtn-js').hide();
            }
            listCartProducts();
            $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
        }
        $.mbsmessage.close();
        $.systemMessage(langLbl.MovedSuccessfully, 'alert--success');
    });
};

function submitSiteSearch(frm, page) {
    events.search();
    var keyword = $.trim($(frm).find('input[name="keyword"]').val());
    keyword = keyword.replace('&', '++');

    if (3 > keyword.length || '' === keyword) {
        $.mbsmessage(langLbl.searchString, true, 'alert--danger');
        return;
    }

    var qryParam = ($(frm).serialize_without_blank());
    var urlString = '';
    if (qryParam.indexOf("keyword") > -1) {
        var protomatch = /^(https?|ftp):\/\//;
        urlString = urlString + setQueryParamSeperator(urlString) + 'keyword-' + encodeURIComponent(keyword.replace(protomatch, '').replace(/\//g, '-')) + '&pagesize-' + page;
    }

    if (qryParam.indexOf("category") > -1 && $(frm).find('input[name="category"]').val() > 0) {
        urlString = urlString + setQueryParamSeperator(urlString) + 'category-' + $(frm).find('input[name="category"]').val();
    }

    if ($(frm).find('input[name="rentalstart"]').val() != '' && $(frm).find('input[name="rentalend"]').val() != '') {
        urlString += '&rentalstart-' + $(frm).find('input[name="rentalstart"]').val();
        urlString += '&rentalend-' + $(frm).find('input[name="rentalend"]').val();
    }

    if (themeActive == true) {
        url = fcom.makeUrl('Products', 'search', []) + urlString + '&theme-preview';
        document.location.href = url;
        return;
    }
    url = fcom.makeUrl('Products', 'search', []) + urlString;
    document.location.href = url;
}

function getSlickGallerySettings(imagesForNav, layoutDirection, slidesToShow = 4, slidesToScroll = 1) {
    slidesToShow = (typeof slidesToShow != "undefined") ? parseInt(slidesToShow) : 4;
    slidesToScroll = (typeof slidesToScroll != "undefined") ? parseInt(slidesToScroll) : 1;
    layoutDirection = (typeof layoutDirection != "undefined") ? layoutDirection : 'ltr';
    if (imagesForNav) {
        var sliderSettings = {
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            asNavFor: '.slider-for',
            dots: false,
            centerMode: false,
            focusOnSelect: true,
            autoplay: true,
            arrows: true,
            vertical: true,
            verticalSwiping: true,
            responsive: [{
                breakpoint: 1499,
                settings: {
                    slidesToShow: 3,

                }
            },
            {
                breakpoint: 1199,
                settings: {
                    slidesToShow: 4,
                    vertical: false,
                    verticalSwiping: false
                }
            },

            {
                breakpoint: 767,
                settings: {
                    slidesToShow: 2,
                    vertical: false,
                    verticalSwiping: false
                }
            }
            ]
        };
        if ($(window).width() < 1025 && layoutDirection == 'rtl') {
            sliderSettings['rtl'] = true;
        }

    } else {
        var sliderSettings = {
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            autoplay: true,
        };

        if (layoutDirection == 'rtl') {
            sliderSettings['rtl'] = true;
        }
    }
    return sliderSettings;
}

var screenResolutionForSlider = {
    1199: 4,
    1023: 3,
    767: 2,
    480: 2,
    375: 1
};

function getSlickSliderSettings(slidesToShow, slidesToScroll, layoutDirection, autoInfinitePlay, slidesToShowForDiffResolution, adaptiveHeight) {
    slidesToShow = (typeof slidesToShow != "undefined") ? parseInt(slidesToShow) : 4;
    slidesToScroll = (typeof slidesToScroll != "undefined") ? parseInt(slidesToScroll) : 1;
    layoutDirection = (typeof layoutDirection != "undefined") ? layoutDirection : 'ltr';
    autoInfinitePlay = (typeof autoInfinitePlay != "undefined") ? autoInfinitePlay : true;
    adaptiveHeight = (typeof adaptiveHeight != "undefined") ? adaptiveHeight : true;
    if (typeof slidesToShowForDiffResolution != "undefined") {
        slidesToShowForDiffResolution = $.extend(screenResolutionForSlider, slidesToShowForDiffResolution);
    } else {
        slidesToShowForDiffResolution = screenResolutionForSlider;
    }

    var sliderSettings = {
        dots: false,
        slidesToShow: slidesToShow,
        slidesToScroll: slidesToScroll,
        infinite: autoInfinitePlay,
        autoplay: autoInfinitePlay,
        adaptiveHeight: adaptiveHeight,
        arrows: true,
        responsive: [{
            breakpoint: 1199,
            settings: {
                slidesToShow: slidesToShowForDiffResolution[1199],
            }
        },
        {
            breakpoint: 1023,
            settings: {
                slidesToShow: slidesToShowForDiffResolution[1023],
            }
        },
        {
            breakpoint: 767,
            settings: {
                slidesToShow: slidesToShowForDiffResolution[767],
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: slidesToShowForDiffResolution[480],
                arrows: false,
                dots: true
            }
        },
        {
            breakpoint: 375,
            settings: {
                slidesToShow: slidesToShowForDiffResolution[375],
                arrows: false,
                dots: true
            }
        }
        ]
    };

    if (layoutDirection == 'rtl') {
        sliderSettings['rtl'] = true;
    }
    return sliderSettings;
}


function codeLatLng(lat, lng, callback) {
    initialize();
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({
        'latLng': latlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {

            if (results[1]) {
                var lat = results[0]['geometry']['location'].lat();
                var lng = results[0]['geometry']['location'].lng();

                for (var i = 0; i < results[0].address_components.length; i++) {
                    if (results[0].address_components[i].types[0] == "country") {
                        var country = results[0].address_components[i].long_name;
                    }
                    if (results[0].address_components[i].types[0] == "country") {
                        var country_code = results[0].address_components[i].short_name;
                    }
                    if (results[0].address_components[i].types[0] == "administrative_area_level_1") {
                        var state_code = results[0].address_components[i].short_name;
                        var state = results[0].address_components[i].long_name;
                    }

                    if (results[0].address_components[i].types[0] == "administrative_area_level_2") {
                        var city = results[0].address_components[i].long_name;
                    }

                    if (results[0].address_components[i].types[0] == "postal_code") {
                        var postal_code = results[0].address_components[i].long_name;
                    }
                }
                var data = { country: country, country_code: country_code, state: state, state_code: state_code, city: city, lat: lat, lng: lng, postal_code: postal_code };

                callback(data);
            } else {
                Console.log("Geocoder No results found");
            }
        } else {
            Console.log("Geocoder failed due to: " + status);
        }
    });
}

function defaultSetUpLogin(frm, v) {
    v.validate();
    if (!v.isValid()) {

        return false;
    }
    fcom.ajax(fcom.makeUrl('GuestUser', 'login'), fcom.frmData(frm), function (t) {
        var ans = JSON.parse(t);
        /* alert(t); */
        if (ans.notVerified == 1) {
            var autoClose = false;
        } else {
            var autoClose = true;
        }
        if (ans.status == 1) {
            $.mbsmessage(ans.msg, autoClose, 'alert--success');
            location.href = decodeURIComponent(ans.redirectUrl);
            return;
        }
        $.mbsmessage(ans.msg, autoClose, 'alert--danger');
    });
    return false;
}

(function ($) {
    var screenHeight = $(window).height() - 100;
    window.onresize = function (event) {
        var screenHeight = $(window).height() - 100;
    };

    $.extend(fcom, {
        getLoader: function () {
            return '<div class="loader-yk"><div class="loader-yk-inner"></div></div>';
        },

        scrollToTop: function (obj) {
            if (typeof obj == undefined || obj == null) {
                $('html, body').animate({
                    scrollTop: $('html, body').offset().top - 100
                }, 'slow');
            } else {
                $('html, body').animate({
                    scrollTop: $(obj).offset().top - 100
                }, 'slow');
            }
        },
        resetEditorInstance: function () {
            if (extendEditorJs == true) {
                var editors = oUtil.arrEditor;
                for (x in editors) {
                    eval('delete window.' + editors[x]);
                }
                oUtil.arrEditor = [];
            }
        },

        resetEditorWidth: function (width = "100%") {
            if (typeof oUtil != 'undefined') {
                (oUtil.arrEditor).forEach(function (input) {
                    var oEdit1 = eval(input);
                    $("#idArea" + oEdit1.oName).attr("width", width);
                });
            }
        },

        setEditorLayout: function (lang_id) {
            if (extendEditorJs == true) {
                var editors = oUtil.arrEditor;
                layout = langLbl['language' + lang_id];
                for (x in editors) {
                    $('#idContent' + editors[x]).contents().find("body").css('direction', layout);
                }
            }
        },

        resetFaceboxHeight: function () {
            /* $('html').css('overflow','hidden'); */
            facebocxHeight = screenHeight;
            var fbContentHeight = parseInt($('#facebox .content').height()) + parseInt(150);
            setTimeout(function () {
                $('#facebox .content').css('max-height', (parseInt(facebocxHeight) - parseInt(facebocxHeight) / 4) + 'px');
            }, 700);
            $('#facebox .content').css('overflow-y', 'auto');
            if (fbContentHeight > screenHeight - parseInt(100)) {
                $('#facebox .content').css('display', 'block');
            } else {
                $('#facebox .content').css('max-height', '');
            }
        },
        updateFaceboxContent: function (t, cls) {
            if (typeof cls == 'undefined' || cls == 'undefined') {
                cls = '';
            }
            $.facebox(t, cls);
            $.systemMessage.close();
            fcom.resetFaceboxHeight();
        },
    });

    $(document).bind('reveal.facebox', function () {
        fcom.resetFaceboxHeight();
    });

    $(window).on("orientationchange", function () {
        fcom.resetFaceboxHeight();
    });

    $(document).bind('loading.facebox', function () {
        $('#facebox .content').addClass('fbminwidth');
    });

    $(document).bind('afterClose.facebox', function () {
        $('html').css('overflow', '');
    });

    /* $(document).bind('afterClose.facebox', fcom.resetEditorInstance); */
    $(document).bind('beforeReveal.facebox', function () {
        $('#facebox .content').addClass('fbminwidth');
        $('html').css('overflow', '')
    });

    $(document).bind('reveal.facebox', function () {
        $('#facebox .content').addClass('fbminwidth');
        $('#facebox .content').addClass('custom-modal');
    });

    $.systemMessage = function (data, cls, autoClose) {
        if ("" == data || typeof data == 'undefined') {
            return;
        }

        if (typeof autoClose == 'undefined' || autoClose == 'undefined') {
            autoClose = true;
        }

        initialize();
        $.systemMessage.loading();
        $.systemMessage.fillSysMessage(data, cls, autoClose);
    };

    $.extend($.systemMessage, {
        settings: {
            closeimage: siteConstants.webroot + 'images/facebox/close.gif',
        },
        loading: function () {
            $('.system_message').show();
        },
        fillSysMessage: function (data, cls, autoClose) {
            if (cls) {
                $('.system_message').removeClass('alert--process');
                $('.system_message').removeClass('alert--danger');
                $('.system_message').removeClass('alert--success');
                $('.system_message').removeClass('alert--info');
                $('.system_message').addClass(cls);
            }
            $('.system_message .content').html(data);
            $('.system_message').fadeIn();

            if (true == autoClose && CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1) {
                var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
                setTimeout(function () {
                    $.systemMessage.close();
                }, time);
            }

            /* $('.system_message').css({top:10}); */
        },
        close: function () {
            $(document).trigger('close.systemMessage');
        },
    });

    $(document).bind('close.systemMessage', function () {
        $('.system_message').fadeOut();
    });

    function initialize() {
        $('.system_message .close').click($.systemMessage.close);
    }
    /* [ */
    $.fn.serialize_without_blank = function () {
        var $form = this,
            result,
            $disabled = $([]);

        $form.find(':input').each(function () {
            var $this = $(this);
            if ($.trim($this.val()) === '' && !$this.is(':disabled')) {
                $disabled.add($this);
                $this.attr('disabled', true);
            }
        });

        result = $form.serialize();
        $disabled.removeAttr('disabled');
        return result;
    };
    /* ] */

})(jQuery);


$(function () {

    var typingTimer;
    var doneTypingInterval = 800;
    var $input = $('#header_search_keyword');

    $input.focus(function (e) {
        searchProductTagsAuto($input.val());
    });

    $input.keyup(function (e) {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });

    $input.keydown(function (e) {
        clearTimeout(typingTimer);
    });

    doneTyping = function (e) {
        searchProductTagsAuto($input.val());
    };

    let $formfloating = $('.form-floating');
    $formfloating.on('keyup', 'input, textarea', function (event) {
        if ($(this).val().length > 0) {
            $(this).addClass('filled')
        } else {
            $(this).removeClass('filled')
        }
    });

    $(document).on('click', '.recentSearch-js', function () {
        $input.val($(this).parent('li').attr('data-keyword'));
        searchProductTagsAuto($(this).parent('li').attr('data-keyword'));
    });

    $(document).on('click', '.clearSearch-js', function () {
        var obj = $(this).hasClass('clear-all') ? 'all' : '';
        clearSearchKeyword(obj);
    });

});

$(document).mouseup(function (e) {
    var container = $('#search-suggestions-js');
    var inputFld = $('#header_search_keyword');
    if ((!container.is(e.target) && container.has(e.target).length === 0) && (!inputFld.is(e.target) && inputFld.has(e.target).length === 0)) {
        $('#search-suggestions-js').html('');
    }
});

$(document).ready(function () {
    var searchSuggestionsJs = $('#search-suggestions-js');
    removeAutoSuggest = function () {
        $('#header_search_keyword').val('');
        searchSuggestionsJs.html('');
    };
    searchTags = function (obj) {
        var frmSiteSearch = document.frmSiteSearch;
        $(frmSiteSearch.keyword).val($(obj).data('txt'));
        $(frmSiteSearch).trigger("submit");
    };
    searchProductTagsAuto = function (keyword) {
        if (parseInt($(window).width()) < 768) {
            return;
        }
        var data = 'keyword=' + keyword;
        fcom.updateWithAjax(fcom.makeUrl('Products', 'searchProductTagsAutocomplete'), data, function (t) {
            if (t.html.length > 0) {
                if (!searchSuggestionsJs.find('div').hasClass('search-suggestions')) {
                    searchSuggestionsJs.html('<a href="javascript:void(0)" onClick="removeAutoSuggest()" class="close-layer"></a><div class="search-suggestions" id="tagsSuggetionList"></div>');
                }
                $('#tagsSuggetionList').html(t.html);
            } else {
                searchSuggestionsJs.html('<a href="javascript:void(0)" onClick="removeAutoSuggest()" class="close-layer"></a>');
            }

        }, '', false);
    };

    clearSearchKeyword = function (obj) {
        var data = '';
        var keyword = $(obj).attr('data-keyword');
        if (typeof keyword != 'undefined') {
            data = 'keyword=' + keyword;
        }
        fcom.ajax(fcom.makeUrl('Products', 'clearSearchKeywords'), data, function (t) {
            if ('all' == obj) {
                $('#search-suggestions-js').html("");
            } else {
                $(obj).closest('li').remove();
                if (0 < $('#search-suggestions-js').length && 1 > $('.recentSearch-js').length) {
                    $('#search-suggestions-js').html("");
                }
            }
        });
    };

    /* var $elem = $('#header_search_keyword').autocomplete({
     'classes': {
     "ui-autocomplete": "custom-ui-autocomplete"
     },
     'source': function(request, response) {
     $.ajax({
     url: fcom.makeUrl('Products', 'searchProductTagsAutocomplete'),
     data: { keyword: encodeURIComponent(request['term']), fIsAjax: 1 },
     dataType: 'json',
     type: 'post',
     success: function(json) {
     response($.map(json, function(item) {
     return { label: item['label'], value: item['value'] };
     }));
     },
     });
     },
     select: function(event, ui) {
     $(document.frmSiteSearch.keyword).val(ui.item.value);
     submitSiteSearch(document.frmSiteSearch);
     }
     }),
     elemAutocomplete = $elem.data("ui-autocomplete") || $elem.data("autocomplete");
     if (elemAutocomplete) {
     elemAutocomplete._renderItem = function(ul, item) {
     var newText = String(item.value).replace(
     new RegExp(this.term, "gi"),
     "<strong>$&</strong>");
     
     return $("<li></li>")
     .data("item.autocomplete", item)
     .append("<div>" + newText + "</div>")
     .appendTo(ul);
     };
     } */

    if ($('.system_message').find('.div_error').length > 0 || $('.system_message').find('.div_msg').length > 0 || $('.system_message').find('.div_info').length > 0 || $('.system_message').find('.div_msg_dialog').length > 0) {
        $('.system_message').show();
    }
    $('.close').click(function () {
        $('.system_message').hide();
    });
    addCatalogPopup = function () {
        /* $.facebox(function () { */
            fcom.ajax(fcom.makeUrl('Seller', 'addCatalogPopup'), '', function (t) {
                /* fcom.updateFaceboxContent(t, 'faceboxWidth loginpopup'); */
                $('#exampleModal').html(t);
                $('#exampleModal').modal('show');

            });
        /* }); */
    }

    markAsFavorite = function (selProdId) {
        $('[data-toggle="tooltip"]').tooltip("hide");
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.mbsmessage.close();
        fcom.updateWithAjax(fcom.makeUrl('Account', 'markAsFavorite', [selProdId]), '', function (ans) {
            if (ans.status) {
                $("[data-id=" + selProdId + "]").addClass("is-active");
                $("[data-id=" + selProdId + "]").attr("onclick", "removeFromFavorite(" + selProdId + ")");
                $("[data-id=" + selProdId + "] span").attr('title', langLbl.RemoveProductFromFavourite);
                $("[data-id=" + selProdId + "].heart-wrapper--js .icon-unchecked--js").hide();
                $("[data-id=" + selProdId + "].heart-wrapper--js .icon-checked--js").show();
            }
        });
    };

    removeFromFavorite = function (selProdId, callbackFunction = false) {
        $('[data-toggle="tooltip"]').tooltip("hide");
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.mbsmessage.close();
        fcom.updateWithAjax(fcom.makeUrl('Account', 'removeFromFavorite', [selProdId]), '', function (ans) {
            if (ans.status) {
                $("[data-id=" + selProdId + "]").removeClass("is-active");
                $("[data-id=" + selProdId + "]").attr("onclick", "markAsFavorite(" + selProdId + ")");
                $("[data-id=" + selProdId + "] span").attr('title', langLbl.AddProductToFavourite);
                $("[data-id=" + selProdId + "].heart-wrapper--js .icon-unchecked--js").show();
                $("[data-id=" + selProdId + "].heart-wrapper--js .icon-checked--js").hide();
            }
        });
        if (callbackFunction !== false) {
            window[callbackFunction]();
        }
    };

    guestUserFrm = function () {
        fcom.ajax(fcom.makeUrl('GuestUser', 'form'), '', function (t) {
            /* fcom.updateFaceboxContent(t, 'faceboxWidth loginpopup'); */
            $('#exampleModal').html(t);
            $('#exampleModal').modal('show');
        });
    };

    openSignInForm = function (includeGuestLogin) {
        if (typeof includeGuestLogin == 'undefined') {
            includeGuestLogin = false;
        }
        data = 'includeGuestLogin=' + includeGuestLogin;
        fcom.ajax(fcom.makeUrl('GuestUser', 'LogInFormPopUp'), data, function (t) {
            try {
                var ans = JSON.parse(t);
                if (ans.status == 1) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    if ('undefined' != typeof ans.redirectUrl) {
                        location.href = ans.redirectUrl;
                    }
                    return;
                }
                $.mbsmessage(ans.msg, true, 'alert--danger');
            } catch (err) {
				/*fcom.updateFaceboxContent(t, 'faceboxWidth loginpopup');*/
                $('#exampleModal').html(t);
                $('#exampleModal').modal('show');
            }
        });
    };

    guestUserLogin = function (frm, v) {
        v.validate();
        if (!v.isValid())
            return;
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl('GuestUser', 'guestLogin'), fcom.frmData(frm), function (t) {
            var ans = JSON.parse(t);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                location.href = ans.redirectUrl;
                return;
            }
            $.mbsmessage(ans.msg, true, 'alert--danger');
        });
        return false;
    };

    autofillLangData = function (autoFillBtn, frm) {
        var actionUrl = autoFillBtn.data('action');

        var defaultLangField = $('input.defaultLang', frm);
        if (1 > defaultLangField.length) {
            $.systemMessage(langLbl.unknownPrimaryLanguageField, 'alert--danger');
            return false;
        }
        var proceed = true;
        var stringToTranslate = '';
        defaultLangField.each(function (index) {
            if ('' != $(this).val()) {
                if (0 < index) {
                    stringToTranslate += "&";
                }
                stringToTranslate += $(this).attr('name') + "=" + $(this).val();
            } else {
                $(this).focus();
                $.systemMessage(langLbl.primaryLanguageField, 'alert--danger');
                proceed = false;
                return false;
            }
        });

        if (true == proceed) {
            $.mbsmessage(langLbl.processing, true, 'alert--process alert');
            fcom.ajax(actionUrl, stringToTranslate, function (t) {
                var res = $.parseJSON(t);
                $.each(res, function (langId, values) {
                    $.each(values, function (selector, value) {
                        $("input.langField_" + langId + "[name='" + selector + "']").val(value);
                    });
                });
                $(document).trigger('close.mbsmessage');
            });
        }
    }

    signInWithPhone = function (obj, flag) {
        var form = $(obj).data('form');
        var formElement = ('undefined' != typeof form) ? 'form[name="' + form + '"]' : 'form';
        var inputElement = $(formElement + " input[name='username']");
        var altPlaceHolder = inputElement.attr('data-alt-placeholder');
        var placeHolder = inputElement.attr('placeholder')
        inputElement.val("").attr({ 'placeholder': altPlaceHolder, 'data-alt-placeholder': placeHolder });
        var objLbl = 0 < flag ? langLbl.withUsernameOrEmail : langLbl.withPhoneNumber;
        $(obj).attr('onclick', 'signInWithPhone(this, ' + (!flag) + ')').text(objLbl)
        stylePhoneNumberFld(formElement + " input[name='username']", (!flag));
        
        setTimeout(function () {
            $('#sign-in form').find('input:text:visible:first').focus();
        }, 500);
        
    };

    redirectfunc = function (url, orderStatus) {
        var input = '<input type="hidden" name="status" value="' + orderStatus + '">';
        $('<form action="' + url + '" method="POST">' + input + '</form>').appendTo($(document.body)).submit();
    };

    $(".sign-in-popup-js").click(function () {
        openSignInForm();
    });

    $(".cc-cookie-accept-js").click(function () {
        fcom.ajax(fcom.makeUrl('Custom', 'updateUserCookies'), '', function (t) {
            $(".cookie-alert").hide('slow');
            $(".cookie-alert").remove();
        });
    });


    $(document).on("click", '.increase-js', function () {
        var type = $('input[name="fulfillment_type"]:checked').val();
        $(this).siblings('.not-allowed').removeClass('not-allowed');
        var rval = $(this).parent().parent('div').find('input').val();
        if (isNaN(rval)) {
            $(this).parent().parent('div').find('input').val(1);
            return false;
        }
        var key = $(this).parent().parent('div').find('input').attr('data-key');
        var page = $(this).parent().parent('div').find('input').attr('data-page');
        val = parseInt(rval) + 1;
        var productType = parseInt($('input[name="product_for"]').val());
        
        var stockKey = 'rentalstock';
        if (productType == 1) {
            var stockKey = 'stock';
        }
        
        if (val > $(this).parent().data(stockKey)) {
            val = $(this).parent().data(stockKey);
            $(this).addClass('not-allowed');
        }
        if ($(this).hasClass('not-allowed') && rval >= $(this).parent().data(stockKey)) {
            return false;
        }
        $(this).parent().parent('div').find('input').val(val);
        if (page == 'product-view') {
            $('.productQty-js').trigger('change');
            return false;
        }
        
        cart.update(key, page, type);
    });

    $(document).on("keyup", '.productQty-js', function () {
        var productType = parseInt($('input[name="product_for"]').val());
        var stockKey = 'rentalstock';
        if (productType == 1) {
            var stockKey = 'stock';
        }
    
        if ($(this).val() > $(this).parent().data(stockKey)) {
            val = $(this).parent().data(stockKey);
            var message = langLbl.quantityAdjusted.replace(/{qty}/g, val);
            $.mbsmessage(message, '', 'alert--success');
            $(this).parent().parent('div').find('.increase-js').addClass('not-allowed');
            $(this).parent().parent('div').find('.decrease-js').removeClass('not-allowed');
        } else if ($(this).val() <= 0) {
            val = 1;
            $(this).parent().parent('div').find('.decrease-js').addClass('not-allowed');
            $(this).parent().parent('div').find('.increase-js').removeClass('not-allowed');
        } else {
            val = $(this).val();
            if ($(this).parent().parent('div').find('.decrease-js').hasClass('not-allowed')) {
                $(this).parent().parent('div').find('.decrease-js').removeClass('not-allowed');
            }

            if ($(this).parent().parent('div').find('.increase-js').hasClass('not-allowed')) {
                $(this).parent().parent('div').find('.increase-js').removeClass('not-allowed');
            }
        }
        $(this).val(val);
        var key = $(this).attr('data-key');
        var page = $(this).attr('data-page');
        if (page == 'product-view') {
            return false;
        }
    });

    $(document).on("blur", '.productQty-js', function () {
        var key = $(this).attr('data-key');
        var page = $(this).attr('data-page');
        if (page == 'product-view') {
            return false;
        }
        var fulfillmentType = $("input[name='fulfillment_type']:checked").val();
        cart.update(key, page, fulfillmentType);
    });

    $(document).on("click", '.decrease-js', function () {
        var type = $('input[name="fulfillment_type"]:checked').val();
        if ($(this).hasClass('not-allowed')) {
            return false;
        }
        $(this).siblings('.not-allowed').removeClass('not-allowed');
        var rval = $(this).parent().parent('div').find('input').val();
        if (isNaN(rval)) {
            $(this).parent().parent('div').find('input').val(1);
            return false;
        }
        var key = $(this).parent().parent('div').find('input').attr('data-key');
        var page = $(this).parent().parent('div').find('input').attr('data-page');
        
        var productType = parseInt($('input[name="product_for"]').val());
        var stockKey = 'minrentqty';
        if (productType == 1) {
            var stockKey = 'minsaleqty';
        }
        
        
        var minQty = $(this).parents().find('div.quantity--js').attr('data-'+ stockKey);
        var minVal = (minQty > 1) ? minQty : 1;
        val = parseInt(rval) - 1;
        if (val <= minVal) {
            val = minVal;
            $(this).addClass('not-allowed');
        }
        if ($(this).hasClass('not-allowed') && rval <= minVal) {
            return false;
        }
        $(this).parent().parent('div').find('input').val(val);
        if (page == 'product-view') {
            $('.productQty-js').trigger('change');
            return false;
        }
        
        cart.update(key, page, type);
    });

    $(document).on("click", '.setactive-js li', function () {
        $(this).closest('.setactive-js').find('li').removeClass('is-active');
        $(this).addClass('is-active');
    });

    $(document).on("keydown", 'input[name=user_username]', function (e) {
        if (e.which === 32) {
            return false;
        }
        this.value = this.value.replace(/\s/g, "");
    });

    $(document).on("change", 'input[name=user_username]', function (e) {
        this.value = this.value.replace(/\s/g, "");
    });

    $(document).on("submit", "form", function () {
        moveErrorAfterIti()
    });

    $(document).on("keyup", "form .iti input[data-intl-tel-input-id]", function () {
        moveErrorAfterIti();
    });

    updatePriceFields = function (durationType = 0) {
        durationType = parseInt(durationType);
        switch (durationType) {
            case 2: /* DAYS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').removeAttr('disabled');
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');
                break;
            case 3: /* WEEKS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-daily--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').val(0);
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');

                break;
            case 4: /* MONTHS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-daily--js input').attr('disabled', 'disabled');
                $('.price-weekly--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').val(0);
                $('.price-weekly--js input').val(0);
                $('.price-monthly--js input').removeAttr('disabled');
                break;
            default: /* HOURS */
                $('.price-hourly--js input').removeAttr('disabled');
                $('.price-daily--js input').removeAttr('disabled');
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');
                break;
        }
    };


});

function moveErrorAfterIti() {
    if (0 < $(".iti .errorlist").length) {
        $(".iti .errorlist").detach().insertAfter('.iti');
    }
}

function isUserLogged() {
    var isUserLogged = 0;
    $.ajax({
        url: fcom.makeUrl('GuestUser', 'checkAjaxUserLoggedIn'),
        async: false,
        dataType: 'json',
    }).done(function (ans) {
        isUserLogged = parseInt(ans.isUserLogged);
    });
    return isUserLogged;
}

/* function checkisThemePreview(){
 var isThemePreview = 0;
 $.ajax({
 url: fcom.makeUrl('MyApp','checkisThemePreview'),
 async: false,
 dataType: 'json',
 }).done(function(ans) {
 isThemePreview = parseInt( ans.isThemePreview );
 });
 alert(isThemePreview);
 return isThemePreview;
 } */

function loginPopUpBox(includeGuestLogin) {
    /* fcom.ajax(fcom.makeUrl('GuestUser','LogInFormPopUp'), '', function(ans){
     $(".login-account a").click();
     }); */
    openSignInForm(includeGuestLogin);
}

function setSiteDefaultLang(langId) {
    var url = window.location.pathname;
    var srchString = window.location.search;
    var data = 'pathname=' + url;
    fcom.ajax(fcom.makeUrl('Home', 'setLanguage', [langId]), data, function (res) {
        var ans = $.parseJSON(res);
        if (ans.status == 1) {
            window.location.href = ans.redirectUrl + srchString;
        }
    });
}

function setSiteDefaultCurrency(currencyId) {
    var currUrl = window.location.href;
    fcom.ajax(fcom.makeUrl('Home', 'setCurrency', [currencyId]), '', function (res) {
        document.location.reload();
    });
}

function quickDetail(selprod_id, wishlistId = 0, fullfillment = 0) {
    var data = '';
    if (wishlistId > 0) {
        data = 'wishlistId=' + wishlistId + '&fullfillment=' + fullfillment;
    }
    $.mbsmessage(langLbl.processing, true, 'alert--process alert');
    fcom.ajax(fcom.makeUrl('Products', 'productQuickDetail', [selprod_id]), data, function (t) {
        $.mbsmessage.close();
        $('#exampleModal').html(t);
        $('#exampleModal').modal('show');
    });   
}

function stylePhoneNumberFld(element = "input[name='user_phone']", destroy = false) {
    var inputList = document.querySelectorAll(element);
    var country = '' == langLbl.defaultCountryCode ? 'in' : langLbl.defaultCountryCode;
    inputList.forEach(function (input) {
        if (true == destroy) {
            $(input).removeAttr('style');
            var clone = input.cloneNode(true);
            $('.iti').replaceWith(clone);
        } else {
            var iti = window.intlTelInput(input, {
                separateDialCode: true,
                initialCountry: country,
                /* utilsScript: "/intlTelInput/intlTelInput-utils.js" */
            });
            $('<input>').attr({
                type: 'hidden',
                name: 'user_dial_code',
                value: "+" + iti.getSelectedCountryData().dialCode
            }).insertAfter(input);

            $('<input>').attr({
                type: 'hidden',
                name: 'user_country_iso',
                value: iti.getSelectedCountryData().iso2
            }).insertAfter(input);

            input.addEventListener('countrychange', function (e) {
                if (typeof iti.getSelectedCountryData().dialCode !== 'undefined') {
                    input.closest('form').user_dial_code.value = "+" + iti.getSelectedCountryData().dialCode;
                    input.closest('form').user_country_iso.value = iti.getSelectedCountryData().iso2;
                }
            });
        }
    });
}

function getCountryIso2CodeFromDialCode(dialCode) {
    var countriesData = window.intlTelInputGlobals.getCountryData();
    var countryData = countriesData.filter(function (country) {
        return country.dialCode == dialCode
    });
    return countryData[0].iso2;
}

function isJson(str) {
    try {
        return JSON.parse(str);
    } catch (e) {
        return false;
    }
}


/* read more functionality [ */
$(document).on('click', '.readMore', function () {
    /* $(document).delegate('.readMore' ,'click' , function(){ */
    var $this = $(this);
    var $moreText = $this.siblings('.moreText');
    var $lessText = $this.siblings('.lessText');

    if ($this.hasClass('expanded')) {
        $moreText.hide();
        $lessText.fadeIn();
        $this.text($linkMoreText);
    } else {
        $lessText.hide();
        $moreText.fadeIn();
        $this.text($linkLessText);
    }
    $this.toggleClass('expanded');
});
/* ] */

$(document).on('click', '.readMoreLess--js', function () {

    if ($(this).hasClass('readmore--js') == true) {
        $(this).text($linkLessText);
        $(this).removeClass('readmore--js');
        $(this).addClass('readless--js');
        $(this).parents('.faq-dec--js').find('.less-desc--js').hide();
        $(this).parents('.faq-dec--js').find('.more-desc--js').show();
    } else {
        $(this).text($linkMoreText);
        $(this).addClass('readmore--js');
        $(this).removeClass('readless--js');
        $(this).parents('.faq-dec--js').find('.less-desc--js').show();
        $(this).parents('.faq-dec--js').find('.more-desc--js').hide();
    }
});



/* Request a demo button [ */
$(document).on('click', '#btn-demo', function () {
    /* $(document).delegate('#btn-demo' ,'click' , function(){ */
    /* $.facebox(function () { */
        fcom.ajax(fcom.makeUrl('Custom', 'requestDemo'), '', function (t) {
            /* fcom.updateFaceboxContent(t, 'faceboxWidth requestdemo'); */
            $('#exampleModal').html(t);
            $('#exampleModal').modal('show');
        });
    /* }); */
});
/* ] */

/* Autocomplete
(function ($) {
 $.fn.autocomplete = function (option) {
 return this.each(function () {
 this.timer = null;
 this.items = new Array();
 
 $.extend(this, option);
 
 $(this).attr('autocomplete', 'off');
 
 // Focus
 $(this).on('focus', function () {
 this.request();
 });
 
 // Blur
 $(this).on('blur', function () {
 
 setTimeout(function (object) {
 object.hide();
 }, 200, this);
 });
 
 // Keydown
 $(this).on('keydown', function (event) {
 switch (event.keyCode) {
 case 27: // escape
 case 9: // tab
 this.hide();
 break;
 default:
 this.request();
 break;
 }
 });
 
 // Click
 this.click = function (event) {
 event.preventDefault();
 value = $(event.target).parent().attr('data-value');
 if (value && this.items[value]) {
 $(this).siblings('ul.dropdown-menu').hide();
 this.select(this.items[value]);
 }
 }
 
 // Show
 this.show = function () {
 var pos = $(this).position();
 
 $(this).siblings('ul.dropdown-menu').css({
 top: pos.top + $(this).outerHeight(),
 left: pos.left
 });
 
 $(this).siblings('ul.dropdown-menu').show();
 }
 
 // Hide
 this.hide = function () {
 $(this).siblings('ul.dropdown-menu').hide();
 }
 
 // Request
 this.request = function () {
 clearTimeout(this.timer);
 this.timer = setTimeout(function (object) {
 
 var txt_box_width = $(object).outerWidth();
 $(object).siblings('ul.dropdown-menu').width(txt_box_width + 'px');
 
 if ($(object).attr('name') == 'keyword') {
 // i.e header search form will enable autocomplete, if minimum characters are 3
 if ($(object).val().length < 3) {
 return;
 }
 }
 
 object.source($(object).val(), $.proxy(object.response, object));
 }, 200, this);
 }
 
 // Response
 this.response = function (json) {
 html = '';
 
 if (json.length) {
 for (i = 0; i < json.length; i++) {
 this.items[json[i]['value']] = json[i];
 }
 
 for (i = 0; i < json.length; i++) {
 if (!json[i]['category']) {
 html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
 }
 }
 
 // Get all the ones with a categories
 var category = new Array();
 
 for (i = 0; i < json.length; i++) {
 if (json[i]['category']) {
 if (!category[json[i]['category']]) {
 category[json[i]['category']] = new Array();
 category[json[i]['category']]['name'] = json[i]['category'];
 category[json[i]['category']]['item'] = new Array();
 }
 
 category[json[i]['category']]['item'].push(json[i]);
 }
 }
 
 for (i in category) {
 html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';
 
 for (j = 0; j < category[i]['item'].length; j++) {
 html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
 }
 }
 }
 
 if (html) {
 this.show();
 } else {
 this.hide();
 }
 
 $(this).siblings('ul.dropdown-menu').html(html);
 }
 
 $(this).after('<ul class="dropdown-menu box--scroller"></ul>');
 $(this).siblings('ul.dropdown-menu').on('click', 'a', $.proxy(this.click, this));
 });
 }
 })(window.jQuery);*/

/* comparison */

addProductToCart = function (selprod_id) {
    var data = '';
    if (selprod_id > 0) {
        data = 'selprod_id=' + selprod_id;
    }

    fcom.updateWithAjax(fcom.makeUrl('Cart', 'add'), data, function (ans) {
        if (ans['redirect']) {
            location = ans['redirect'];
            return false;
        }
        if (9 < ans.total) {
            ans.total = '9+';
        }

        $('span.cartQuantity').html(ans.total);
        $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));

    });
    return false;
}
/* === */


$("document").ready(function () {
    $(document).on('click', '.continue-rental-cart--js', function() {
        if ($(this).hasClass('skipServicesJs')) {
            $(".rental-cart-tbl-js").find("input[type='checkbox']").each(function (e, index) {
                $(this).prop('checked', false);
            });
        }
        
        $('input[name="btnAddToCart"]').trigger('click');
        $("#aditional-services-js .close").click();
    }); 
    
    $(document).on('click', '.add-to-cart--js', function (event) {
        events.addToCart();
        $btn = $(this);
        var orderdetail = parseInt($(this).data('orderdetail'));
        if (orderdetail == 1) {
            if (confirm(langLbl.confirmExtendOrder)) {
                cart.clearForExtendOrder()
            } else {
                return;
            }
        }
        
        var product_for = $(this).data('producttype');
        var wishlistId = parseInt($(this).data('wishlistid'));
        var fullfillmentType = parseInt($(this).data('fullfillment'));
        $('input[name="product_for"]').val(product_for);
        $('input[name="product_for"]').trigger('change');
        /* var product_for = $('input[name="product_for"]').val(); */
        event.preventDefault();
        var data = fcom.frmData(document.frmBuyProduct);
        var frm = document.frmBuyProduct;
        if (!$(frm).validate())
            return;

        var quantity = $('.qty-wrapper--js input[name="quantity"]').val();
        var data = fcom.frmData(document.frmBuyProduct);
        var selprodId = $(this).siblings('input[name="selprod_id"]').val();
        var minOrderQty = 1;
        if (product_for == 2 && ($('.service-link--js').length > 0) && $('.service-link--js').data('displaybox') == 1) {
            /* $('.service-link--js').trigger('click'); */
            showAddonModal(1);
            return;
        }
        
        if (typeof mainSelprodId != 'undefined' && mainSelprodId == selprodId) {
            if (product_for == 1) { /* PRODUCT FOR SALE */
                minOrderQty = $('.qty-wrapper--js').data('minsaleqty');
                $(".bought-together").find("input[type='checkbox']").each(function (e) {
                    /*if (($(this).val() > 0) && (!$(this).closest(".addon--js").hasClass("cancelled--js"))) {
                     data = data + '&' + $(this).attr('lang') + "=" + $(this).val();
                     }*/
                    if ($(this).prop('checked') == true) {
                        data = data + '&' + $(this).data('attrname') + "=" + $(this).data('saleqty');
                    }
                });
            }

            if (product_for == 2) { /* PRODUCT FOR RENT */
                minOrderQty = $('.qty-wrapper--js').data('minrentqty');
                $(".rental-cart-tbl-js").find("input[type='checkbox']").each(function (e) {
                    if ($(this).prop('checked') == true) {
                        data = data + '&' + $(this).data('attrname') + "=" + $(this).data('rentalqty');
                    }
                });
            }
        }
        if (quantity < minOrderQty) {
            $.mbsmessage(langLbl.minOrderQtyLbl + ' ' + minOrderQty, true, 'alert--danger');
            return false;
        }

        fcom.updateWithAjax(fcom.makeUrl('cart', 'add'), data, function (ans) {
            if (ans['redirect']) {
                location = ans['redirect'];
                return false;
            }
            $('.service-link--js').data('displaybox', 1);
            if (product_for == 1) {
                $('.for-rent--js .atom-radio-drawer_head_left').addClass('disabled');
                $('.for-sale--js .sale-rent-only').hide();
                $('.for-rent--js .sale-rent-only').show();
            } else if(orderdetail != 1 && product_for == 2) {
                $('.rental_datescalendar--js').data('dateRangePicker').clear();
                $('input[name="rental_start_date"]').val('');
                $('input[name="rental_end_date"]').val('');
                $('.rental-cart-tbl-js input[type="checkbox"]').prop('checked', false);
                $('.for-sale--js .atom-radio-drawer_head_left').addClass('disabled');
                $('.for-sale--js .sale-rent-only').show();
                $('.for-rent--js .sale-rent-only').hide();
                $('input[name="btnAddToCart"]').val(langLbl.rentNow);
            }
            
            if ($btn.hasClass("btnBuyNow") == true) {
                setTimeout(function () {
                    window.location = fcom.makeUrl('Checkout');
                }, 300);
                return false;
            }
            if ($btn.hasClass("quickView") == true) {
                $("#exampleModal .close").click();
                $('body').addClass('side-cart--on');
            }

            /* if ($btn.hasClass("rentProduct") == true) {
                alert('asdsad');
                $("#exampleModal .close").click();
                $('body').addClass('side-cart--on');
            } */

            if (9 < ans.total) {
                ans.total = '9+';
            }
            $('span.cartQuantity').html(ans.total);
            $('#cartSummary').load(fcom.makeUrl('cart', 'getCartSummary'));
            if (wishlistId > 0) {
                addRemoveWishListProduct(selprodId, wishlistId, event);
            }
            if (fullfillmentType > 0) {
                setTimeout(function () {
                    if (1 > $("#cartList").length) {
                        location.reload();
                    } else {
                        var fullfillmentType = $('input[name="fulfillment_type"]:checked').val();
                        listCartProducts(fullfillmentType);
                    }
                }, 500);
            }
            
            if (orderdetail == 1) {
                setTimeout(function () {
                    window.location = fcom.makeUrl('Checkout');
                }, 300);
                return false;
            } else {
                $('body').addClass('side-cart--on');
            }
            
        });
        return false;
    });
    
    showAddonModal = function(showInputs = 0) {
        if (!showInputs) {
            $('.rental-cart-tbl-js input[type="checkbox"]').hide();
            $('.continue-rental-cart--js').hide();
        } else {
            $('.rental-cart-tbl-js input[type="checkbox"]').show();
            $('.continue-rental-cart--js').hide();    
            $('.continue-rental-cart--js.skipServicesJs').show();
            $('.service-link--js').data('displaybox', 0);
        }
        
        
        $('.close-detail--js').trigger('click');
        $('#aditional-services-js').modal('show');
    }
    
    $('.rental-cart-tbl-js input[type="checkbox"]').on('change', function() {
        var addonCount = 0;
        $(".rental-cart-tbl-js input[type=checkbox]:checked").each(function(){
            addonCount++;
        }); 
        
        if (addonCount > 0) {
            $('.continue-rental-cart--js').show();    
        } else {
            $('.continue-rental-cart--js').hide(); 
            $('.continue-rental-cart--js.skipServicesJs').show();
        }
    });
    
});

$(document).ready(function () {
    if ($(window).width() < 1025) {
        $('html').removeClass('sticky-demo-header');
        $("div.demo-header").hide();
    }
});

/* Scroll Hint */
$(document).ready(function () {
    new ScrollHint('.js-scrollable', {
        i18n: {
            scrollable: langLbl.scrollable
        }
    });
});
$(document).ajaxComplete(function () {
    new ScrollHint('.js-scrollable:not(.scroll-hint)', {
        i18n: {
            scrollable: langLbl.scrollable
        }
    });

    if (0 < $('div.block--empty').length && 0 < $('div.scroll-hint-icon-wrap').length) {
        $('div.block--empty').siblings('.js-scrollable.scroll-hint').children('div.scroll-hint-icon-wrap').remove();
    }

    if (0 < $("#facebox").length) {
        if ($("#facebox").is(":visible")) {
            $('html').addClass('pop-on');
        } else {
            $('html').removeClass('pop-on');
        }
        $("#facebox .close.close--white").on("click", function () {
            $("html").removeClass('pop-on');
        });
    }

    $('body').click(function () {
        if ($('html').hasClass('pop-on')) {
            $('html').removeClass('pop-on');
        }
    });
});

$(document).ready(function () {
    /*
     STARTS triggers & toggles[
     
     data-trigger => value = target element id to be opened
     data-target-close => value = target element id to be closed
     data-close-on-click-outside => value
     
     */

    $('body').find('*[data-trigger]').click(function () {
        var targetElmId = $(this).data('trigger');
        var elmToggleClass = targetElmId + '--on';
        if ($('body').hasClass(elmToggleClass)) {
            $('body').removeClass(elmToggleClass);
        } else {
            $('body').addClass(elmToggleClass);
        }
        setTimeout(function () {
            $('.'+ targetElmId).find('input:text:visible:first').focus();
        }, 500);
    });

    $('body').find('*[data-target-close]').click(function () {
        var targetElmId = $(this).data('target-close');
        $('body').toggleClass(targetElmId + '--on');
    });

    $('body').mouseup(function (event) {
        if ($(event.target).data('trigger') != '' && typeof $(event.target).data('trigger') !== typeof undefined) {
            event.preventDefault();
            return;
        }
        if ($(event.target).parents().hasClass('date-picker-wrapper')) {
            return;
        }
        

        $('body').find('*[data-close-on-click-outside]').each(function (idx, elm) {
            var slctr = $(elm);
            if (!slctr.is(event.target) && !$.contains(slctr[0], event.target)) {
                $('body').removeClass(slctr.data('close-on-click-outside') + '--on');
            }
        });
    });

    /*
     ] ENDS triggers & toggles
     
     */
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });
});

$(document).on("change", "input[type='file']", fileSizeValidation);

function fileSizeValidation() {
    const fsize = this.files[0].size;
    if (fsize > langLbl.allowedFileSize) {
        var msg = langLbl.fileSizeExceeded;
        var msg = msg.replace("{size-limit}", bytesToSize(langLbl.allowedFileSize));
        $.mbsmessage(msg, true, 'alert--danger');
        $(this).val("");
        return false;
    }
}

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0)
        return '0 Byte';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

$('.form-floating').find('input, textarea, select').each(function () {
    if ($(this).val() != "") {
        $(this).addClass('filled')
    } else {
        $(this).removeClass('filled')
    }
});


$('.dropdown-menu').on('click', function (e) {
    e.stopPropagation();
});

function awebersignup() {
    var content = $('.aweber-js').html();
    /* fcom.updateFaceboxContent(content, 'faceboxWidth loginpopup aweberform-js'); */
    $('#exampleModal').html(`<div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-body">`  + content + `</div></div></div>`);
    $('#exampleModal').modal('show');
    var weberformload = setInterval(function () {
        if (0 < $(".aweberform-js form").length) {
            var myForm = $(".aweberform-js form")[0];
            myForm.onsubmit = function () {
                var popwidth = 500,
                    popheight = 700,
                    popleft = ($(window).width() / 2) - (popwidth / 2),
                    poptop = ($(window).height() / 2) - (popheight / 2),
                    popup = window.open("", "popup", "width=" + popwidth + ", height=" + popheight + ", top=" + poptop + ", left=" + popleft);
                this.target = 'popup';
                /* $(document).trigger('close.facebox'); */
                $("#exampleModal .close").click();
            };
            clearInterval(weberformload);
        }
    }, 1000);
}

$(document).on('click', 'input[name="sprodata_is_for_sell"]', function () {
    if ($(this).prop('checked') == true) {
        $('.saleFldJs').removeClass('hideSaleFlds');
        $('.tabsForSaleJs').removeClass('productSaleTabs');
        $('.tabsForSaleJs').addClass('fat-inactive');
        $('.tabsForSaleJs a').attr('onclick', '');
    } else {
        $('.tabsForSaleJs').addClass('productSaleTabs');
        $('.saleFldJs').addClass('hideSaleFlds');
    }
})

$(document).on('click', '.product-type-tabs--js', function () {
    $('.product-type-tabs').removeClass('active');
    $(this).addClass('active');
    var productfor = $(this).data('productfor');
    $('input[name="product_for"]').val(productfor);
    $('input[name="product_for"]').trigger('change');

    if (parseInt(productfor) == 2) {
        $('.rental-fields--js').show();
        $('.sale-products--js').addClass('hide-sell-section ');
    } else {
        $('.rental-fields--js').hide();
        $('.sale-products--js').removeClass('hide-sell-section ');
    }
})

function getRentalDetails() {
    var rental_start_date = $('#frmBuyProduct input[name="rental_start_date"]').val();
    var rental_end_date = $('#frmBuyProduct input[name="rental_end_date"]').val();
    var selprod_id = $('#frmBuyProduct input[name="selprod_id"]').val();
    var quantity = $('#frmBuyProduct input[name="quantity"]').val();
    var extendOrderId = $('#frmBuyProduct input[name="extend_order"]').val();
    var fulfillmentType = $('#frmBuyProduct input[name="fulfillmentType"]').val();
    var data = 'selprod_id=' + selprod_id + '&quantity=' + quantity + '&rental_start_date=' + rental_start_date + '&rental_end_date=' + rental_end_date + '&extendOrderId=' + extendOrderId + '&fulfillmentType='+fulfillmentType;

    if (rental_start_date != '' && rental_start_date != undefined && rental_end_date != '' && rental_end_date != undefined && quantity != '' && quantity > 0) {
        fcom.ajax(fcom.makeUrl('Products', 'getRentalDetails'), data, function (t) {
            t = $.parseJSON(t);
            if (t.status == 0) {
                $.mbsmessage(t.msg, true, 'alert--danger');
                $('.rental-price--js').html('NA');
                $('.rental-security--js').html('NA');
                $('.rental-stock--js').html(t.availableQuantity);
                $('.total-amount--js').html('NA');
                $('.duration-list--js').removeClass('active');
            } else {
                $('.rental-price--js').html(t.rentalPrice);
                $('.rental-security--js').html(t.rentalSecurity);
                $('.rental-stock--js').html(t.availableQuantity);
                /*$('.total-amount--js').html(t.totalPayableAmount);*/
				var rentbtnHtml = t.totalPayableAmount + ' '+ langLbl.rentNow;
                $('input[name="btnAddToCart"]').val(rentbtnHtml);
                $('.duration-list--js').removeClass('active');
                $('#dur_'+ t.durationDiscount).addClass('active');
            }
        });
    }
}

$(document).on('change', '#frmBuyProduct input[name="quantity"]', function () {
    getRentalDetails();
})

$(document).ready(function () {
    if ($('input[name="rentaldates"]').length > 0) {
        $('input[name="rentaldates"]').each(function(){
            var section = $(this).data('section');
            
            $(this).dateRangePicker({
                autoClose: true,
                startDate: new Date(),
                showShortcuts: false,
                customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
                customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
                stickyMonths: true,
                inline: true,
                container: $(this).parents('.'+ section),
            }).bind('datepicker-change',function(event, obj) {
                var selectedDates = obj.value;
                var datesArr = selectedDates.split(" to ");
                $('input[name="rentalstart"]').val(datesArr[0]);
                $('input[name="rentalend"]').val(datesArr[1]);
            });
        });
    }
});

function RequestForQuote(selProdId, rfqId) {
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }

    var parent_id = 0;
    if (rfqId != '' && rfqId != undefined) {
        parent_id = rfqId;
    }
    var data = 'parent_id=' + parent_id;

    if (typeof selProdId != 'undefined') {
        $(".rental-cart-tbl-js").find("input[type='checkbox']").each(function (e) {
            if ($(this).prop('checked') == true) {
                data = data + '&' + $(this).data('attrname') + "=" + $(this).data('rentalqty');
            }
        });
    }

    fcom.ajax(fcom.makeUrl('RequestForQuotes', 'form', [selProdId]), data, function (t) {
        /*fcom.updateFaceboxContent(t, 'faceboxWidth productQuickView');*/
		$('#exampleModal').html(t);
        $('#exampleModal').modal('show');
    });
}

function setupRequestForQuote(frm) {

    /* if ($("input[name='rfq_documents']").length > 0 && $("input[name='group_id']").val() < 1) {
    //     $('#document-fld-js .document-error').remove();
    //     errorHtml = '<ul class="document-error"><li><a href="javascript:void(0);">Document is mandatory</a></li></ul>';
    //     $('#document-fld-js input[name="rfq_documents"]').after(errorHtml);
    //     return false;
    // }*/

    var type = $( "#rfq_fulfilment_type option:selected" ).val();
    if(type == FULFILMENT_TYPE_PICK) {
        var pickAdd = $("input[name='rfq_pickup_address_id']:checked").val();
        if(pickAdd == undefined){
            $.mbsmessage("Select Pickup Address", true, 'alert--danger');
            return false;
        }

        var shipAdd = $("input[name='rfq_ship_address_id']:checked").val();
        if(shipAdd == undefined){
            $.mbsmessage("Select Billing Address", true, 'alert--danger');
            return false;
        }
    }else{
        var shipAdd = $("input[name='rfq_ship_address_id']:checked").val();
        if(shipAdd == undefined){
            $.mbsmessage("Select Shipping Address", true, 'alert--danger');
            return false;
        }
    }
    
    if (!$(frm).validate()) {
        return false;
    }

    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('RequestForQuotes', 'setup'), data, function (ans) {
        var ans = JSON.parse(ans);
        if (ans.status == 1) {
            $.mbsmessage(ans.msg, true, 'alert--success');
            /* $(document).trigger('close.facebox'); */
            $('#exampleModal').modal('hide');
            $("#exampleModal .close").click();
        } else {
            console.log("not");
            $.mbsmessage(ans.msg, true, 'alert--danger');
        }
    });
}

function toggleRequestType(value){
    if (parseInt(value) == 1){
        $(".field--calender-daterange--js").addClass('disabled-input');
    } else {
        $(".field--calender-daterange--js").removeClass('disabled-input');
    }
}

function toggleFulfilmentStatues(value){
    value = parseInt(value);
    if(value == parseInt(FULFILMENT_TYPE_PICK)){
        $("#pickup").show();
        $("#billing").show();
        $("#shipping").hide();
    }else{
        $("#pickup").hide();
        $("#billing").hide();
        $("#shipping").show();
    }
}

loadNotifications = function () {
    fcom.ajax(fcom.makeUrl('account', 'loadNotifications'), '', function (t) {
        $('#notificationList-header').html(t);
    });
}

markNotificationRead = function (notificationId) {
    var data = 'notification_id=' + notificationId;
    fcom.ajax(fcom.makeUrl('account', 'markNotificationRead', [notificationId]), '', function (t) {
        var res = JSON.parse(t);
        if (res.status == 1) {
            if (res.action != '') {
                window.location.href = res.action;
            }
        } else {
            $.mbsmessage(res.msg, true, 'alert--danger');
            window.location.reload();
        }
    });
}

/* product comparison js */

function compare_products(obj,selprodId) {
    if($(obj).prop("checked") == true) {
        addToCompareList(selprodId, 0);
    } else {
        removeFromCompareList(selprodId, 0);
    }
}

function addToCompareList(selprodId, refreshPage, isUrlCompare) {
    var data = 'selProdId=' + selprodId + '&isUrlCompare=' + isUrlCompare;

    fcom.ajax(fcom.makeUrl('CompareProducts', 'add'), data, function (res) {
        var res = JSON.parse(res);
        if (res.status == true) {
            $.mbsmessage(res.msg, true, 'alert--success');

            if(isUrlCompare == 1) {
                location.href = ' ' + res.shareurl + ' ';
                return;
            }

            if (refreshPage == 1) {
                window.location.reload();
            } else {
                $("#compare_product_list_js").html(res.productListing);
                if (res.comparedProductsCount == 1) {
                   $('body').addClass('is-compare-visible'); 
                }
                
                if ($(".compare-count-js").length > 0) {
                    $(".compare-count-js").text(res.comparedProductsCount);
                }
                
                var cat_id = $('.compare_product_js_'+selprodId).data("catid")
                $(".compProductsJs").each(function(i) {
                    if(!$(this).hasClass( "comp_product_cat_"+cat_id)) {
                        var cat = $(this).data("catid");
                        $(".comp_product_cat_"+cat).hide();
                        $(".comp_product_cat_"+cat).closest('label').hide();
                    }

                    $('.compare_product_js_'+selprodId).prop('checked', true);
                    $('.compare_product_js_'+selprodId).closest('.product-tile-1 , .product-tile-3 , .product-tile-2').addClass('compared');
                    
                });

            }
        } else {
            $('.compare_product_js_' + selprodId).prop("checked", false);
            $.mbsmessage(res.msg, true, 'alert--danger');
        }
    });
}

function removeFromCompareList(selprodId, refreshPage, isUrlCompare) {
    var data = 'selProdId=' + selprodId + '&isUrlCompare=' + isUrlCompare;
    fcom.ajax(fcom.makeUrl('CompareProducts', 'removeProduct'), data, function (res) {
        var res = JSON.parse(res);
        if (res.status == true) {
            $.mbsmessage(res.msg, true, 'alert--success');
            if(isUrlCompare == 1) {
                location.href = ' ' + res.shareurl + ' ';
                return;
            }
            if (refreshPage == 1) {
                window.location.reload();
            } else {
                $("#compare_product_list_js").html(res.productListing);
                if (res.comparedProductsCount > 0) {
                    $(".compare-count-js").text(res.comparedProductsCount);
                }else{
                    $(".compProductsJs").show();
                    $(".compProductsJs").closest('label').show();
                }
                $('.compare_product_js_'+selprodId).prop('checked', false);
                $('.compare_product_js_'+selprodId).closest('.product-tile-1 , .product-tile-3 , .product-tile-2 ').removeClass('compared');
            }
        } else {
            $.mbsmessage(res.msg, true, 'alert--danger');
        }
    });
}

function clearCompareList() {
    fcom.ajax(fcom.makeUrl('CompareProducts', 'clearList'), '', function (res) {
        var res = JSON.parse(res);
        if (res.status == true) {
            $.mbsmessage(res.msg, true, 'alert--success');
            window.location.reload();
        } else {
            $.mbsmessage(res.msg, true, 'alert--danger');
        }
    });
}

function changeOption(newProdId, oldProdId) {
    if (newProdId != oldProdId) {
        if (confirm('Are you sure you want to change option')) {
            var data = 'oldProdId=' + oldProdId + '&newProdId=' + newProdId;
            fcom.ajax(fcom.makeUrl('CompareProducts', 'updateProduct'), data, function (res) {
                var res = JSON.parse(res);
                if (res.status == true) {
                    $.mbsmessage(res.msg, true, 'alert--success');
                    window.location.reload();
                } else {
                    $.mbsmessage(res.msg, true, 'alert--danger');
                }
            });
        }
    }
}

function rfqMeaasge(rfq_id){
    console.log(rfq_id);
    fcom.ajax(fcom.makeUrl('RequestForQuotes', 'rfqMessage/'+rfq_id), '', function (ans) {
        var ans = JSON.parse(ans);
        if (ans.status == 1) {
            location.href = ans.redirectUrl;
            // $.mbsmessage(ans.msg, true, 'alert--success');
            // window.location.reload();
        } else {
            $.mbsmessage(ans.msg, true, 'alert--danger');
        }
    });
}

/* FOR STICKY COMPARE BAR */
$(document).on('click', '.compare-toggle-js', function () {
    /* $(this).toggleClass("is-ctive"); */
    $('body').toggleClass("is-compare-visible");
});

/* end */

markOrderReadyForReturn = function (opId) {
    fcom.updateWithAjax(fcom.makeUrl('buyer', 'markOrderReadyForReturn', [opId]), '', function (ans) {
        window.location.reload();
    });
}

sendOrderMessage = function (op_id,controller) {
    if(controller == '' || controller == 'undefined' ) {
        return false;
    }
    fcom.ajax(fcom.makeUrl(controller, 'sendOrderMessage', [op_id]), '', function (t) {
        console.log(t);
        if(isJson(t)) {
            var ans = $.parseJSON(t);
            location.href = decodeURIComponent(ans.redirectUrl);
            
        }else{
            $('#exampleModal').html(t);
            $('#exampleModal').modal('show');
        }
        return false;
    });

};

setupOrderMessage = function (frm,controller) {
    fcom.addTrailingSlash();
    if (!$(frm).validate())
        return;
    $.mbsmessage(langLbl.processing, false, 'alert--process alert');
    $.ajax({
        url: fcom.makeUrl(controller, 'setupSendOrderMessage'),
        type: 'post',
        dataType: 'json',
        data: new FormData($(frm)[0]),
        cache: false,
        contentType: false,
        processData: false,

        success: function (ans) {
            if (ans.status == true) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                setTimeout(function () {
                    document.location.reload();
                }, 2000);
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

getFullfillmentData = function(fullfillType, productType, sellerProductId) {
    var data = {'fullfillmentType' : fullfillType, 'productType' : productType, 'sellerProductId' : sellerProductId};
    fcom.ajax(fcom.makeUrl('products', 'getFullfillmentData'), data, function (t) {
        $('#exampleModal').html(t);
        $('#exampleModal').modal('show');
    });
}

searchShipLocations = function(frm) {
    var data = fcom.frmData(frm);
    fcom.ajax(fcom.makeUrl('products', 'getShippingLocations'), data, function (t) {
        $('#search-result--js').html(t);
    });
}

$(document).ready(function() {
    $('.productQty-js').on('change', function() {
        var qty = $(this).val();
        var qtyArr = [0];
        $('.duration-list--js').removeClass('active');
        $('.duration-list--js').each(function() {
            var minQty = $(this).data('qty');
            if (qty >= minQty) {
                qtyArr.push(minQty);
            }
        });
        var validQty = Math.max(...qtyArr);
        $('#volumne_'+ validQty).addClass('active');
    });
    
    $('input[name="radio_for_rent_sale_section"]').on('change', function() {
        var currentQty = $('.productQty-js').val();
        if ($(this).val() == 1) {
            var minQty = $('.qty-wrapper--js').data('minsaleqty');
        } else {
            var minQty = $('.qty-wrapper--js').data('minrentqty');
        }
        if (minQty >= currentQty) {
            $('.decrease-js').addClass('not-allowed');
        } else {
            $('.decrease-js').removeClass('not-allowed');
        }
        /* $('.productQty-js').trigger('change'); */
    });
    
});

function copyContent(el) {
    $('.clipboard_btn').attr('title', langLbl.copyToClipboard);
    $('.clipboard_btn').attr('data-original-title', langLbl.copyToClipboard);
    var element = $(el).siblings('.clipboard_url');
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
    $(el).attr('title', langLbl.copied);
    $(el).attr('data-original-title', langLbl.copied);
    $(el).tooltip('show')
}

submitDemoRequest = function(frm, q = "v3") {  
    if (!$(frm).validate())
        return false;
        
    var data = fcom.frmData(frm);
    $.mbsmessage(langLbl.processing, false, 'alert--process');
	$('input[name="submitForm"]').attr('disabled', 'disabled');
    $.ajax({
        type: 'POST',
        url: 'https://www.yo-rent.com/send-demo-request.html',
        data: data,
        success: function(res) {
            res = $.parseJSON(res);
            $.mbsmessage(res.message, false, 'alert--success');
            document.cookie = 'yorent_request_submitted=1';
            setTimeout(() => {
                window.location.href = fcom.makeUrl('custom', 'thankYou')+ '?q='+ q;
            }, 1000);
        }
    });
}
