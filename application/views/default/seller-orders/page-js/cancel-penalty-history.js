$(document).ready(function() {
	searchHistory(document.frmOrderSrch);
});

(function() {
	searchHistory = function(frm) {
		var data = fcom.frmData(frm);
		$("#chargesListing").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('SellerOrders', 'cancelPenaltySearchListing'), data, function(res) {
			$("#chargesListing").html(res);
		});
	};
	
	goToChargesSearchPage = function(page) {
		if (typeof page==undefined || page == null) {
			page =1;
		}
		var frm = document.frmPenaltySrchPaging;
		$(frm.page).val(page);
		searchHistory(frm);
	};
    
    clearSearch = function() {
        document.frmChargesSearch.reset();
        searchHistory(document.frmChargesSearch);
    }
    
})();