$(document).ready(function () {
    searchProductQuotes(document.frmSearchQuotesRequests);
});
(function () {
    var runningAjaxReq = false;
    var dv = '#listing';

    searchProductQuotes = function (frm) {
        var data = fcom.frmData(frm);
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'searchProdQuotes'), data, function (res) {
            $(dv).html(res);
        });
    };

    reloadList = function () {
        searchProductQuotes(document.frmSearchQuotesRequests);
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchProductQuotesPaging;
        $(frm.page).val(page);
        searchProductQuotes(frm);
    };

    clearSearch = function () {
        document.frmSearchQuotesRequests.reset();
        searchProductQuotes(document.frmSearchQuotesRequests);
    };

})();
