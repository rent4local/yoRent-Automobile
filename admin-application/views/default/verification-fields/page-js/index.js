$(document).ready(function() {
    searchVerificationFls();
});
(function() {
    var dv = '#vFldsListing';
    searchVerificationFls = function() {
        var data = '';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('VerificationFields', 'search'), '', function(res) {
            $(dv).html(res);
        });
    };

    addFieldForm = function(id){
        $.facebox(function() {
            fieldForm(id);
        });
    };

    reloadList = function() {
		searchVerificationFls();
	}

    fieldForm = function(id){
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('VerificationFields', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupVerificationFlds = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('VerificationFields', 'setup'), data, function(t) {
            reloadList();
            $(document).trigger('close.facebox');
        });
    };

    
    translateData = function(item){
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var defaultLang = $(item).attr('defaultLang');
        var vFldsName = $("input[name='vflds_name["+defaultLang+"]']").val();
        var toLangId = $(item).attr('language');
        var alreadyOpen = $('#collapse_'+toLangId).hasClass('active');
        if(autoTranslate == 0 || vFldsName == "" || alreadyOpen == true){
            return false;
        }
        var data = "vFldsName="+vFldsName+"&toLangId="+toLangId ;
        fcom.updateWithAjax(fcom.makeUrl('VerificationFields', 'translatedData'), data, function(t) {
            if(t.status == 1){
                $("input[name='vflds_name["+toLangId+"]']").val(t.vFldsName);
            }
        });
    }
/* 
    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var vfldsId = parseInt(obj.id);
        if (vfldsId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'vfldsId=' + vfldsId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('VerificationFields', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    }; */

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var vfldsId = parseInt(obj.value);

        if (vfldsId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }

        var checked = 0;
        if ($("#switch" + vfldsId).is(":checked")) {
             checked = 1;
        }

        data = 'vfldsId=' + vfldsId + '&status=' + checked;
        fcom.ajax(fcom.makeUrl('VerificationFields', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

})();

