$(document).ready(function () {
    searchRules(document.frmRulesSearch);
});

(function () {
    var dv = '#listing';
    var controllerName = 'OrderCancelRules';
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmRulesSearchPaging;
        $(frm.page).val(page);
        searchRules(frm);
    }

    reloadList = function () {
        searchRules();
    };

    searchRules = function (form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'search'), data, function (res) {
            $(dv).html(res);
        });
    };

    addEditRuleForm = function (id, defaultIsActive = true) {
        if (!defaultIsActive) {
            $.systemMessage("Default Value is Not Active", 'alert--danger');
            return false;
        }
        fcom.ajax(fcom.makeUrl(controllerName, 'form', [id]), '', function (t) {
            $('#ruleForm--js').html(t);
        });
    };
    
    viewAdminRules = function() {
        fcom.ajax(fcom.makeUrl(controllerName,'viewAdminRules'), '', function(t){
            $('#exampleModal').html(t);
            $('#exampleModal').modal('show');
        });
	}

    setupRule = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'setup'), data, function (t) {
            addEditRuleForm(0, true);
            window.setTimeout(function() { location.reload() }, 200);
        });
    };

    deleteRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'ruleId=' + id;
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'deleteRecord'), data, function (res) {
            window.setTimeout(function() { location.reload() }, 200);
        });
    };
    deleteSelected = function () {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmCancelRuleListing").submit();
    };

    clearSearch = function () {
        document.frmSearch.reset();
        searchRules(document.frmSearch);
    };

    changeCancelRuleStatus = function (status) {
        if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}

        data ='status='+status;
        fcom.updateWithAjax(fcom.makeUrl(controllerName,'changeAllRulesStatus'), data, function(res) {
			window.setTimeout(function() { location.reload() }, 200);
		});

    };

    toggleCancleRuleStatus = function(e, obj, ocruleId){
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var ocruleId = parseInt(obj.value);
		if( ocruleId < 1 ){
			return false;
		}
        var status = 0;
        if ($(obj).prop('checked') == true) {
            status = 1;
        }
        data ='ocruleId='+ocruleId + '&status='+status;
		fcom.updateWithAjax(fcom.makeUrl(controllerName,'changeCancleRuleStatus'), data, function(res) {
			window.setTimeout(function() { location.reload() }, 200);
		});
	};

})();
