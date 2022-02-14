$(document).ready(function(){
	searchOrders(document.frmOrderSrch);
});
(function() {
	var dv = '#ordersListing';
	
	searchOrders = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('RequestForQuotes','rfqOrderSearchListing'), data, function(res){
			$(dv).html(res);
		}); 
	};
	
	goToOrderSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmOrderSrchPaging;		
		$(frm.page).val(page);
		searchOrders(frm);
	}
	
	clearSearch = function(){
		document.frmOrderSrch.reset();
		searchOrders(document.frmOrderSrch);
    };
    
    /* Shipping Services */
    generateLabel = function (opId) {
        fcom.updateWithAjax(fcom.makeUrl('RequestForQuotes', 'generateLabel', [opId]), '', function (t) {
            window.location.reload();
        });
    }
    /* Shipping Services */
})();