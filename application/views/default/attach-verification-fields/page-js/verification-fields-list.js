$(document).ready(function(){
	searchVerificationFlds(document.frmSearch);
});
(function() {
	var dv = '#verificationListing';
	
	searchVerificationFlds = function(frm){
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('AttachVerificationFields','verificationFldSearchListing'), data, function(res){
			$(dv).html(res);
		}); 
	};
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchPaging;		
		$(frm.page).val(page);
		searchVerificationFlds(frm);
	}
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchVerificationFlds(document.frmSearch);
	};
})();