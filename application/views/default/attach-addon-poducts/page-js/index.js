$(document).ready(function () {
    searchProducts(document.frmProfileSearch);
    $("body").on('click', function () {
        $('.search-card-pro--js').hide();
    });

    $(".search-card-pro--js, #filterText").on('click', function (e) {
        e.stopPropagation();
        $('.search-card-pro--js').show();
    });
});

(function () {
    var runningAjaxReq = false;
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
        fcom.ajax(fcom.makeUrl('AttachAddonPoducts', 'search'), data, function (res) {
            $(dv).html(res);
        });
    }

    clearSearch = function () {
        document.frmSearchAddonProduct.reset();
        searchProducts(document.frmSearchAddonProduct);
    };

    saveAddonWithProducts = function (frm) {
        var addonProdId = parseInt($("input[name='addon_product_id']").val());
        var selectedProducts = [];
        var treeView = $("#treeview").data("yomultiselectTreeView");
        getCheckedNodes(treeView.dataSource.view(), selectedProducts);

        if (addonProdId <= 0 || selectedProducts.length < 1) {
            $.mbsmessage(langLbl.pleaseSelectAddonAndProducts, true, 'alert--danger');
            return false;
        }

        var data = "addon_product_id=" + addonProdId + "&products_data=" + selectedProducts;
        fcom.updateWithAjax(fcom.makeUrl('AttachAddonPoducts', 'updateAddonWithProducts'), data, function (t) {
            var treeView = $("#treeview").data("yomultiselectTreeView");
            treeView.dataSource.data([]);
            $("#filterText").val('');
            $(".selectAll").css("visibility", "hidden");
            if (RELOAD_AFTER_SAVE == 1) {
                clearForm();
            } else {
                reloadList();
            }
        });
    };

    deleteAttachedProduct = function (addonProdId, sellerProdId) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'addon_product_id=' + addonProdId + "&seller_product_id=" + sellerProdId;
        fcom.updateWithAjax(fcom.makeUrl('AttachAddonPoducts', 'deleteAttachedProduct'), data, function (res) {
            reloadList();
        });
    }

})(); 