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

    addEditRuleForm = function (id) {
        $.facebox(function () {
            fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl(controllerName, 'form', [id]), '', function (t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };

    setupRule = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'setup'), data, function (t) {
            window.setTimeout(function() { location.reload() }, 200);
            $(document).trigger('close.facebox');
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
})();
