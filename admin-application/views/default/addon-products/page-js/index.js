$(document).ready(function () {
    searchProducts(document.frmSearchAddonProduct);
    $("body").on('click', function () {
        $('.search-card-pro--js').hide();
    });

    $(".search-card-pro--js, #filterText").on('click', function (e) {
        e.stopPropagation();
        $('.search-card-pro--js').show();
    });
});

(function () {
    var dv = '#addon-products-listing-js';
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        searchProducts(frm);
    }

    reloadList = function () {
        var frm = document.frmProductSearchPaging;
        searchProducts(frm);
    };

    searchProducts = function (form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('AddonProducts', 'search'), data, function (res) {
            $(dv).html(res);
        });
    }

    clearSearch = function () {
        document.frmSearchAddonProduct.reset();
        searchProducts(document.frmSearchAddonProduct);
    };

})(); 