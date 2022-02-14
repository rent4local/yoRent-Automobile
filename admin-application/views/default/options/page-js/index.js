$(document).ready(function() {
    searchOptions(document.frmOptionSearch);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmOptionsSearchPaging;
        $(frm.page).val(page);
        searchOptions(frm);
    };

    reloadList = function() {
        var frm = document.frmOptionsSearchPaging;
        searchOptions(frm);
    };
    addOptionFormNew = function(optionId) {
        $.facebox(function() {
            optionForm(optionId);
        });

    };
    optionForm = function(optionId) {
        fcom.displayProcessing();
        $.facebox(function() {
			fcom.ajax(fcom.makeUrl('Options', 'form', [optionId]), '', function(t) {
				try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){
					$.facebox(t,'faceboxWidth');
					addOptionForm(optionId);
					optionValueListing(optionId);
				}
				fcom.resetFaceboxHeight();
			});
		});
    };

    addOptionForm = function(optionId, autoFillLangData = 0) {
        var dv = $('#loadForm');
        $.systemMessage(langLbl.processing, 'alert--process');
        fcom.ajax(fcom.makeUrl('Options', 'addForm', [optionId, autoFillLangData]), '', function(t) {
            dv.html(t);
            fcom.resetFaceboxHeight();
            $.systemMessage.close();
        });
    };

    optionValueListing = function(optionId) {
        if (optionId == 0) {
            $('#showHideContainer').addClass('hide');
            return;
        }
        if($("#optionValueListing").length == 0) {
            var dv =$('#optionValuesListing');
        }else{
            var dv =$('#optionValueListing');
        }
        dv.html(fcom.getLoader());
        var data = 'option_id=' + optionId;
        fcom.ajax(fcom.makeUrl('OptionValues', 'search'), data, function(res) {
            dv.html(res);
            fcom.resetFaceboxHeight();
        });
    };

    optionValueForm = function(optionId, id) {
        fcom.displayProcessing();
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId, id]), '', function(t) {
            dv.html(t);
            optionValueListing(optionId);
            jscolor.install();
            fcom.resetFaceboxHeight();
            $.systemMessage.close();
        });
    };

    setUpOptionValues = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function(t) {
            if (t.optionId > 0) {
                optionValueForm(t.optionId, 0);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteOptionValue = function(optionId, id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id + '&option_id=' + optionId;
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'deleteRecord'), data, function(res) {
            optionValueListing(optionId);
            optionValueForm(optionId, 0);
        });
    }

    optionValueSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchOptionValuePaging;
        $(frm.page).val(page);
        searchOptionValueListing(frm);
    };

    searchOptionValueListing = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#optionValueListing").html('Loading....');
        fcom.ajax(fcom.makeUrl('OptionValues', 'search'), data, function(res) {
            $("#optionValueListing").html(res);
        });
    };

    showHideValues = function(obj) {

        var type = obj.value;
        var data = 'optionType=' + type;
        fcom.ajax(fcom.makeUrl('Options', 'canSetValue'), data, function(t) {
            var res = $.parseJSON(t);
            if (res.hideBox == true) {
                $('#showHideContainer').addClass('hide');
                return;
            }
            $('#showHideContainer').removeClass('hide');
        });
    };

    submitOptionForm = function(frm, fn) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Options', 'setup'), data, function(t) {
            reloadList();
            if (t.optionId > 0) {
                optionValueForm(t.optionId); return;
                /* optionForm(t.optionId);
                return; */
            }
            fcom.resetFaceboxHeight();
            $(document).trigger('close.facebox');
        });
    };

    searchOptions = function(form) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $("#optionListing").html('Loading....');

        fcom.ajax(fcom.makeUrl('Options', 'search'), data, function(res) {
            $("#optionListing").html(res);
        });
    };

    deleteOptionRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Options', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
		if(!confirm(langLbl.confirmDelete)){
			return false;
		}
		$("#frmOptionsListing").submit();
	};

    clearOptionSearch = function() {
        document.frmOptionSearch.reset();
        searchOptions(document.frmOptionSearch);
    };
})();
