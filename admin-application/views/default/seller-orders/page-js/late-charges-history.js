$(document).ready(function() {
	searchHistory(document.frmOrderSrch);
});

(function() {
	searchHistory = function(frm) {
		var data = fcom.frmData(frm);
		$("#chargesListing").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('SellerOrders', 'lateChargesSearchListing'), data, function(res) {
			$("#chargesListing").html(res);
		});
	};
	
	goToSearchPage = function(page) {
		if (typeof page==undefined || page == null) {
			page =1;
		}
		var frm = document.frmChargesSrchPaging;
		$(frm.page).val(page);
		searchHistory(frm);
	};
    
    clearSearch = function() {
        document.frmChargesSearch.reset();
        searchHistory(document.frmChargesSearch);
    }
    
})();