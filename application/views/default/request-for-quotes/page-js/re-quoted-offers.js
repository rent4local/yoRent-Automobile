
$(document).ready(function () {
    searchQuotedRequests(document.frmSearchQuotesRequests);
});
(function () {
    var runningAjaxReq = false;
    var dv = '#listing';

    searchQuotedRequests = function (frm) {
        var data = fcom.frmData(frm);
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'searchReQuotedRequests'), data, function (res) {
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
        var frm = document.frmSearchReQuotesPaging;
        $(frm.page).val(page);
        searchQuotedRequests(frm);
    };

    clearSearch = function () {
        document.frmSearchQuotesRequests.reset();
        searchQuotedRequests(document.frmSearchQuotesRequests);
    };
    
    changeStatus = function (rfqId, status) {
        var data = 'rfq_id=' + rfqId + '&status=' + status;
        fcom.updateWithAjax(fcom.makeUrl('CounterOffer', 'updateStatusBySeller'), data, function (ans) {
            // var ans = JSON.parse(ans);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                window.location.reload();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    }

})();
