$(document).ready(function () {
    var profileId = $('input[name="lcp_id"]').val();
    searchProductsSection(profileId);
    searchServicesSection(profileId);
});
(function () {
    var prodListing = '#product-listing--js';
	var controllerName = 'lateCharges';
    setupProfile = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        var profileId = $('input[name="lcp_id"]').val();
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'setup'), data, function (t) {
            if (t.status == 1) {
                if (profileId <= 0) {
                    window.location.replace(fcom.makeUrl(controllerName, 'form', [t.profileId]));
                }
            }
        });
    };
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductSearchPaging_1;
        $(frm.page).val(page);
        var profileId = $('input[name="lcp_id"]').val();
        searchProducts(profileId, frm);
    };
    
    reloadListProduct = function () {
        var frm = document.frmProductSearchPaging_1;
        var profileId = $('input[name="lcp_id"]').val();
        searchProducts(profileId, frm);
    };

    searchProducts = function (profileId, form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }

        $(prodListing).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'searchProducts', [profileId]), data, function (res) {
            $(prodListing).html(res);
        });
       
    };

    searchProductsSection = function (profileId) {
        var dv = '#product-section--js';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'productSection', [profileId]), '', function (res) {
            $(dv).html(res);
            searchProducts(profileId);
        });
    };

    setupProfileProduct = function (frm, type) {
        if (!$(frm).validate()) return;
        if ($('input[name="lcp_id"]').val() <= 0) {
             $.mbsmessage(langLbl.saveProfileFirst, true, 'alert--danger');
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'setupProduct'), data, function (t) {
            var profileId = $('input[name="lcp_id"]').val();
            if (type == 2) {
                $('form[name="frmProfileProducts_2"]')[0].reset();
                $('#service_name--js').select2("val", "");
                searchServices(profileId);
            } else {
                $('form[name="frmProfileProducts_1"]')[0].reset();
                $('#product_name--js').select2("val", "");
                searchProducts(profileId);
            }
        });
    };

    removeProductFromProfile = function (productId, type) {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'removeProduct', [productId, type]), '', function (t) {
            var profileId = $('input[name="lcp_id"]').val();
            if (type == 2) {
                searchServices(profileId);
            } else {
                searchProducts(profileId);
            }
            /* $(document).trigger('close.facebox'); */
            $("#exampleModal .close").click();
        });
    }
    
    
    /* [ SERVICES UPDATES START ] */
    var serviceListing = '#services-listing--js';
    reloadListServices = function () {
        var frm = document.frmProductSearchPaging_2;
        var profileId = $('input[name="lcp_id"]').val();
        searchServices(profileId, frm);
    };
    
    goToServiceSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductSearchPaging_2;
        $(frm.page).val(page);
        var profileId = $('input[name="lcp_id"]').val();
        searchServices(profileId, frm);
    };

    searchServices = function (profileId, form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }

        $(serviceListing).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'searchServices', [profileId]), data, function (res) {
            $(serviceListing).html(res);
        });
       
    };

    searchServicesSection = function (profileId) {
        $('#services-section--js').html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'productSection', [profileId, 2]), '', function (res) {
            $('#services-section--js').html(res);
            searchServices(profileId);
        });
    };
    /* ] */
    
    
})();