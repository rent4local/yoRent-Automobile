$(document).ready(function(){
	searchDeletedBrands(document.frmDeletedBrandSearch);	
});

(function() {
	var currentPage = 1;
		
	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page = 1;
		}		
		var frm = document.frmBrandSearchPaging;		
		$(frm.page).val(page);
		searchDeletedBrands(frm);
	};
		
	searchDeletedBrands = function(form,page){ 
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#brandsListing").html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('deletedBrands','search'),data,function(res){ 
			$("#brandsListing").html(res);
		});
	};
		
	reloadBrandList = function() {
		searchDeletedBrands(document.frmBrandSearchPaging, currentPage);
	};
	
	clearSearch = function(){ 
		document.frmDeletedBrandSearch.reset();
		searchDeletedBrands( document.frmDeletedBrandSearch );
	};	

	brandsListing = function(){
		document.location.href = fcom.makeUrl('Brands');
	};	

	restoreBrand = function(brandId){
		if(!confirm(langLbl.confirmRestore)){return;}
		var data = 'brand_id='+brandId;
		fcom.updateWithAjax(fcom.makeUrl('DeletedBrands', 'restore'), data, function(t) {						
			reloadBrandList();			
		});
	};		
})();