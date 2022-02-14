$(document).ready(function() {
    searchState(document.frmStateSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmStateSearchPaging;
        $(frm.page).val(page);
        searchState(frm);
    }

    reloadList = function() {
        var frm = document.frmStateSearchPaging;
        searchState(frm);
    };

    searchState = function(form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/

        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('States', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };

    addStateForm = function(id) {
        $.facebox(function() {
            stateForm(id);
        });
    }

    stateForm = function(id) {
        fcom.displayProcessing();

       
        fcom.ajax(fcom.makeUrl('States', 'form', [id]), '', function(t) {
 
            fcom.updateFaceboxContent(t);
        });
      
    };
    editStateFormNew = function(stateId) {
        $.facebox(function() {
            editStateForm(stateId);
        });
    };


    editStateForm = function(stateId) {
        fcom.displayProcessing();
       
        fcom.ajax(fcom.makeUrl('States', 'form', [stateId]), '', function(t) {
            
            fcom.updateFaceboxContent(t);
        });
      
    };

    setupState = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('States', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editStateLangForm(t.stateId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editStateLangForm = function(stateId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
      
        fcom.ajax(fcom.makeUrl('States', 'langForm', [stateId, langId, autoFillLangData]), '', function(t) {
      
            fcom.updateFaceboxContent(t);
        });
     
    };

    setupLangState = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('States', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editStateLangForm(t.stateId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var stateId = parseInt(obj.value);
        if (stateId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
           
            return false;
        }
        data = 'stateId=' + stateId;
        fcom.ajax(fcom.makeUrl('States', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchState(document.frmSearch);
    };

})();
