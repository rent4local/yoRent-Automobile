$(document).ready(function () {
    searchQuotedRequests(document.frmSearchQuotesRequests);
});
(function () {
    var runningAjaxReq = false;
    var dv = '#listing';

    searchQuotedRequests = function (frm) {
        var data = fcom.frmData(frm);
        $(dv).html(fcom.getLoader());
        data = data+'&requote=1'
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'searchBuyerQuotes'), data, function (res) {
            $(dv).html(res);
        });
    };

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

})();