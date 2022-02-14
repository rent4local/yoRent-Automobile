$(document).on('click', '.anchor--js', function (event) {
    $(".cg--js ul li").removeClass('iss--active');
    $(this).parent().addClass('iss--active');
    $id = $(this).attr('data-role');
    /* var target_offset = $("." + $id).offset();
    var target_top = target_offset.top - 60;
    $('html, body').animate({scrollTop: target_top}, 1000); */
    
    var offset = $('#header').outerHeight() + 30;
    $('html, body').animate({
        scrollTop: $("." + $id).offset().top - offset
    }, 100);
    
    
});

$(document).ready(function () {
    $(".cg--js ul li:first").addClass('iss--active');
    /* searchCategories(document.frmSearchCategories); */
});

(function () {
    searchCategories = function (frm) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing-categories');
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Category', 'search'), data, function (res) {
            $(dv).html(res);
        });
    };

    goToCategorySearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchCategories;
        $(frm.page).val(page);
        searchCategories(frm);
    };
})();