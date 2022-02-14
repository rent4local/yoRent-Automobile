$(document).ready(function () {
    searchAddonProducts(document.frmSearchProductLisiting);
});
(function () {
    var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
    var runningAjaxReq = false;
    var dv = '#addon-products-listing-js';

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    searchAddonProducts = function (frm) {
        checkRunningAjax();
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = fcom.frmData(frm);
        /*]*/
        $(dv).html(fcom.getLoader());
        
        fcom.ajax(fcom.makeUrl('AddonProducts', 'productListing'), data, function (res) {
            runningAjaxReq = false;
            $(dv).html(res);
        });
    };

    clearSearch = function () {
        document.frmSearchProductLisiting.reset();
        searchAddonProducts(document.frmSearchProductLisiting);
    };

    goToAddonProductSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchProductLisiting;
        $(frm.page).val(page);
        searchAddonProducts(frm);
    }

})();