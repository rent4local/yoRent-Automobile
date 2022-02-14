$(document).ready(function(){	
    if (REPORT_TYPE == 3) {
        mostWishListAddedProducts();
    } else {
        productPerformanceSrch();
    }
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listingDiv';

	goToMostWishListAddedProdSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmMostWishListAddedProdSrchPaging;
		$( frm.page ).val( page );
		mostWishListAddedProducts(page);
	}

	goToTopPerformingProductsSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchProdPerformancePaging;
		$(frm.page).val(page);
		productPerformanceSrch(frm);
	}

	productPerformanceSrch = function(frm) {
		if (typeof frm == undefined || frm == null) {
			frm = document.frmProdPerformanceSrch;
		}
        $(dv).html(fcom.getLoader());
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsPerformance'), data, function(t) {
			$(dv).html(t);
		});
	};

	/* badPerformingProducts = function(frm){
		$(dv).html(fcom.getLoader());
        if(typeof frm == undefined || frm == null){
			frm = document.frmProdPerformanceSrch;
		}
        
		var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('Reports', 'searchProductsPerformance'), data, function(t) {
			$('#performanceReportExport').attr('onClick', "exportProdPerformanceReport(0)");
			$(dv).html(t);
		});
	}; */

	mostWishListAddedProducts = function(page){
        if(typeof page==undefined || page == null){
			page = 1;
		}
		var data = '&page='+page;
		fcom.ajax(fcom.makeUrl('Reports', 'searchMostWishListAddedProducts'), data, function(t) {
			$('#performanceReportExport').attr('onClick', 'exportMostFavProdReport()');
			$(dv).html(t);
		});
	};

	exportMostFavProdReport = function(){
		document.frmMostWishListAddedProdSrchPaging.action = fcom.makeUrl('Reports','exportMostWishListAddedProducts');
		document.frmMostWishListAddedProdSrchPaging.submit();
	};

	exportProdPerformanceReport = function(reportType = 1) { 
        document.frmProdPerformanceSrch.action = fcom.makeUrl('Reports','searchProductsPerformance', ["export"] );
		document.frmProdPerformanceSrch.submit();
	}

    clearSearch = function() {
		document.frmProdPerformanceSrch.reset();
		productPerformanceSrch(document.frmProdPerformanceSrch);
	};
    
})();
