$(document).ready(function(){
	searchRentalsReport(document.frmRentalsReportSearch);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = '#listing';

	goToSearchPage = function(page) {	
		if(typeof page == undefined || page == null){
			page =1;
		}
		var frm = document.frmRentalsReportSearchPaging;		
		$(frm.page).val(page);
		searchRentalsReport(frm);
	};
	redirectBack=function(redirecrt){

	var url=	SITE_ROOT_URL +''+redirecrt;
	window.location=url;
	}
	reloadList = function() {
		var frm = document.frmRentalsReportSearchPaging;
		searchRentalsReport(frm);
	};
	
	searchRentalsReport = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		
		$(dv).html(fcom.getLoader());
		
		fcom.ajax(fcom.makeUrl('RentalsReport','search'),data,function(res){
			$(dv).html(res);
		});
	};
	
	exportReport = function(dateFormat){
		document.frmRentalsReportSearch.action = fcom.makeUrl('RentalsReport','export');
		document.frmRentalsReportSearch.submit();		
	}
	
	clearSearch = function(){
		document.frmRentalsReportSearch.reset();
		searchRentalsReport(document.frmRentalsReportSearch);
	};
})();	