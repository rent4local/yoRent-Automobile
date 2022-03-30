$("document").ready(function () {
    $(".cancel").on('click', function () {
        $(this).closest('.addon--js').toggleClass('cancelled--js ');
        $(this).toggleClass('remove-add-on');
    });

    /* bannerAdds(); */
    reviews(document.frmReviewSearch);
});

function getSortedReviews(elm) {
    if ($(elm).length) {
        var sortBy = $(elm).data('sort');
        if (sortBy) {
            document.frmReviewSearch.orderBy.value = $(elm).data('sort');
            $(elm).parent().siblings().removeClass('is-active');
            $(elm).parent().addClass('is-active');
        }
    }
    reviews(document.frmReviewSearch);
}

function reviewAbuse(reviewId) {
    if (reviewId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Reviews', 'reviewAbuse', [reviewId]), '', function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    }
}

function setupReviewAbuse(frm) {
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.updateWithAjax(fcom.makeUrl('Reviews', 'setupReviewAbuse'), data, function (t) {
        $(document).trigger('close.facebox');
    });
    return false;
}

(function () {
    var setProdWeightage = false;
    var timeSpendOnProd = false;
    bannerAdds = function () {
        fcom.ajax(fcom.makeUrl('Banner', 'products'), '', function (res) {
            $("#productBanners").html(res);
        });
    };

    setProductWeightage = function (code) {
        var data = 'selprod_code=' + code;
        if (setProdWeightage == true && timeSpendOnProd == true) {
            return;
        }
        if (setProdWeightage == true) {
            timeSpendOnProd = true;
            data += '&timeSpend=true';
        }
        setProdWeightage = true;
        fcom.ajax(fcom.makeUrl('Products', 'logWeightage'), data, function (res) {
        });
    };

    /* reviews section[ */
    var dv = '#itemRatings .listing__all';
    var currPage = 1;

    reviews = function (frm, append) {
        if (typeof append == undefined || append == null) {
            append = 0;
        }

        var data = fcom.frmData(frm);
        if (append == 1) {
            $(dv).prepend(fcom.getLoader());
        } else {
            $(dv).html(fcom.getLoader());
        }

        fcom.updateWithAjax(fcom.makeUrl('Reviews', 'searchForProduct'), data, function (ans) {
            $.mbsmessage.close();
            if (ans.totalRecords) {
                $('#reviews-pagination-strip--js').show();
            }
            if (append == 1) {
                $(dv).find('.loader-yk').remove();
                $(dv).find('form[name="frmSearchReviewsPaging"]').remove();
                $(dv).append(ans.html);
                $('#reviewEndIndex').html((Number($('#reviewEndIndex').html()) + ans.recordsToDisplay));
            } else {
                $(dv).html(ans.html);
                $('#reviewStartIndex').html(ans.startRecord);
                $('#reviewEndIndex').html(ans.recordsToDisplay);
            }
            $('#reviewsTotal').html(ans.totalRecords);
            $("#loadMoreReviewsBtnDiv").html(ans.loadMoreBtnHtml);
        }, '', false);
    };

    goToLoadMoreReviews = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        currPage = page;
        var frm = document.frmSearchReviewsPaging;
        $(frm.page).val(page);
        reviews(frm, 1);
    };

    /*] */

    markReviewHelpful = function (reviewId, isHelpful) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        isHelpful = (isHelpful) ? isHelpful : 0;
        var data = 'reviewId=' + reviewId + '&isHelpful=' + isHelpful;
        fcom.updateWithAjax(fcom.makeUrl('Reviews', 'markHelpful'), data, function (ans) {
            $.mbsmessage.close();
            reviews(document.frmReviewSearch);
            /* if(isHelpful == 1){
             
             } else {
             
             } */
        });
    }

    shareSocialReferEarn = function (selprod_id, socialMediaName) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        var data = 'selprod_id=' + selprod_id + '&socialMediaName=' + socialMediaName;

        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Account', 'shareSocialReferEarn'), data, function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
        return false;
    }

    rateAndReviewProduct = function (product_id, sellerId = 0) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        /* var data = 'product_id=' + product_id; */
        window.location = fcom.makeUrl('Reviews', 'write', [product_id, sellerId]);
    }

    checkUserLoggedIn = function () {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        } else
            return true;
    }

})();
jQuery(document).ready(function ($) {
    $('a[rel*=facebox]').facebox()
});
/* for sticky things*/
if ($(window).width() > 1050) {
    function sticky_relocate() {
        var window_top = $(window).scrollTop();
        var div_top = $('.fixed__panel').offset().top - 110;
        var sticky_left = $('#fixed__panel');
        if ((window_top + sticky_left.height()) >= ($('.unique-heading').offset().top - 40)) {
            var to_reduce = ((window_top + sticky_left.height()) - ($('.unique-heading').offset().top - 40));
            var set_stick_top = -40 - to_reduce;
            sticky_left.css('top', set_stick_top + 'px');
        } else {
            sticky_left.css('top', '110px');
            if (window_top > div_top) {
                $('#fixed__panel').addClass('stick');
            } else {
                $('#fixed__panel').removeClass('stick');
            }
        }
    }
}

$('.gallery').modaal({
    type: 'image'
});

$(document).on('click', '.atom-section-js', function(e) {
    if ($(this).find('.atom-radio-drawer_head_left').hasClass('disabled')) {
        e.preventDefault();
        return;
    }
    var productType = $(this).find('input[name="radio_for_rent_sale_section"]').val();
    $('input[name="product_for"]').val(parseInt(productType));
    
	$('.atom-radio-drawer').removeClass('selected');
	$(this).parent().addClass('selected');
    
    $('.discount-slider-js').slick('refresh');
    $('.discount-slider-js').resize();
    $('.volume-discount-slider').slick('refresh');
    $('.volume-discount-slider').resize();
});