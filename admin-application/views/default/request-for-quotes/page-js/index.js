(function() {
    goToSearchPage = function(page) {
        if(typeof page==undefined || page == null){
                page = 1;
        }
        var frm = document.frmRfqSearchPaging;
        $(frm.page).val(page);
        searchRFQ(frm);
    }

    searchRFQ = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $('#rfq_listing').html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'search'), data, function(res) {
            $('#rfq_listing').html(res);
        });
    };

    clearSearch = function () {
        document.frmSearchQuotesRequests.reset();
        searchRFQ(document.frmSearchQuotesRequests);
    };
})();

searchRFQ();