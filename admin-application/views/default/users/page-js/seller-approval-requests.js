$(document).ready(function(){
	searchListing(document.frmSupplierReqSrch);	
});
(function() {
	var currentPage = 1;
	
	goToSearchPage = function(page) {	
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		var frm = document.frmSupplierReqSrch;				
		searchListing(frm,page);
	}
	
	searchListing = function(form,page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;		
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#supplierApprovallist").html(fcom.getLoader());
		data = data+'&page='+currentPage;
		fcom.ajax(fcom.makeUrl('Users','sellerApprovalRequestSearch'),data,function(t){
			$("#supplierApprovallist").html(t);
		});
	};
	
	reloadList = function() {
		searchListing(document.frmSearchPaging, currentPage);
	};
	
	viewSellerRequest = function(requestId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Users','viewSellerRequest',[requestId]),'',function(t){
				fcom.updateFaceboxContent(t);
			});	
		});		
	};
	
	updateSellerRequestForm = function (requestId){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Users','updateSellerRequestForm',[requestId]),'',function(t){
				fcom.updateFaceboxContent(t);
			});	
		});	
	};
	
	/* updateSellerRequest = function (frm){
		if (!$(frm).validate()) { return; }
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Users', 'updateSellerRequest'), data, function(t) {			
			reloadList();			
			$(document).trigger('close.facebox');
		});
	}; */
	
	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');

		}else{
			$('#div_comments_box').addClass('hide');
			
		}		
	};

	changeStatus = function(requestId,status) {

        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        if (requestId < 1 || status < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'requestId=' + requestId + '&status=' + status;
        fcom.ajax(fcom.makeUrl('Users', 'updateSellerRequest'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                setTimeout(function() {
                    reloadList();
                }, 1000);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };
	
	clearSupplierReqSrch = function(){
		document.frmSupplierReqSrch.reset();
		searchListing(document.frmSupplierReqSrch);
	};
	
})();