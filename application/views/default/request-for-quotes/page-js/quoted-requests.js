$(document).ready(function () {
    searchQuotedRequests(document.frmSearchQuotesRequests);
});
(function () {
    var runningAjaxReq = false;
    var dv = '#listing';


    reloadList = function () {
        searchQuotedRequests(document.frmSearchQuotesRequests);
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchQuotesRequestsPaging;
        $(frm.page).val(page);
        searchQuotedRequests(frm);
    };

    clearSearch = function () {
        document.frmSearchQuotesRequests.reset();
        searchQuotedRequests(document.frmSearchQuotesRequests);
    };

    searchQuotedRequests = function (frm) {
        var data = fcom.frmData(frm);
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'searchBuyerQuotes'), data, function (res) {
            $(dv).html(res);
        });
    };

})();
