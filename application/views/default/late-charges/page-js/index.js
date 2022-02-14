$(document).ready(function() {
    searchProfile(document.frmProfileSearch);
});

(function() {
	var controllerName = 'lateCharges';
	var runningAjaxReq = false;
    var dv = '#profile-listing--js';
    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProfileSearchPaging;
        $(frm.page).val(page);
        searchProfile(frm);
    }

    reloadList = function() {
        var frm = document.frmProfileSearchPaging;
        searchProfile(frm);
    };
	
	searchProfile = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'search'), data, function(res) {
            $(dv).html(res);
        });
	}
	
	clearSearch = function() {
        document.frmSearch.reset();
        searchProfile(document.frmSearch);
    };

    deleteRecord = function(shippingProfileId){
        data = 'id='+shippingProfileId;
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'deleteRecord'), data, function() {   
            reloadList();         
        });
    };
	
})(); 