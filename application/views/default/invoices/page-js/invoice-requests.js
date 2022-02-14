$(document).ready(function(){
   searchRequests(); 
});

(function () {
    var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
    var runningAjaxReq = false;
    var dv = '#listing';

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    searchRequests = function (frm) {
        checkRunningAjax();
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = fcom.frmData(frm);
        /*]*/
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Invoices', 'searchRequests'), data, function (res) {
            runningAjaxReq = false;
            $(dv).html(res);
        });
    };

    goToRequestSearchPage = function (page) {
        if (typeof page == undefined || page == null || page < 1) {
            page = 1;
        }
        var frm = document.frmRequestSrchPaging;
        $(frm.page).val(page);
        searchRequests(frm);
    }

    clearSearch = function () {
        document.frmSearchRequests.reset();
        searchCatalogProducts(document.frmSearchRequests);
    };

})();
