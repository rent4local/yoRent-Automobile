$(document).ready(function () {
    searchRuleList(document.frmRuleListSearch);
    $('body').on('change', 'select[name="taxruleloc_type"]', function () {
        var dv = '#taxruleloc_to_state_id';
        if ($(this).val() == -1) {
            $(dv).selectpicker('val', -1);
            $(dv).attr('disabled', true);
            $(dv + " option[value='-1']").show();
        } else {
            $(dv).removeAttr('disabled');
            $(dv).selectpicker('val', "");
            $(dv + " option[value='-1']").hide();
        }
        $(dv).selectpicker('refresh');
    });
    
    var allSelectedValues = [];
    $(document).on('change', '#taxruleloc_from_state_id.selectpicker', function (e) {
        var newArray = [];
    
        var selectId = $(this).attr('id'); 
        var dv = '#'+ selectId;
        var selectedValues = $(this).val();
        
        if (selectedValues.length > allSelectedValues.length) {
            var array1 = selectedValues;
            var array2 = allSelectedValues;
        } else {
            var array1 = allSelectedValues;
            var array2 = selectedValues;
        }
        
        jQuery.grep(array1, function(el) {
            if (jQuery.inArray(el, array2) == -1) newArray.push(el);
        });
        
        if (newArray.length > 0) {
            var lastIndex = 0;
            if (1 > allSelectedValues.length) {
                lastIndex = newArray.length - 1;
            }
        
            var currentVal = newArray[lastIndex];
            if (currentVal == "-1") {
               selectedValues = ["-1"];
            } else if(jQuery.inArray("-1", selectedValues) !== -1 ) {
                selectedValues = jQuery.grep(selectedValues, function(value) {
                    return value != "-1";
                });
            } 
            $(dv).selectpicker('val', selectedValues);
            $(dv).selectpicker('refresh');
        }
        
        allSelectedValues = selectedValues;
    });
});

(function () {
    var dv = '#taxListing';
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmRuleSearchPaging;
        $(frm.page).val(page);
        searchRuleList(frm);
    };

    reloadList = function () {
        searchRuleList(document.frmRuleListSearch);
    };

    searchRuleList = function (form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        $('#taxBackBtn').addClass('d-none');
        fcom.ajax(fcom.makeUrl('Tax', 'ruleListSearch'), data, function (res) {
            $(dv).html(res);
        });
    };

    ruleForm = function (taxcatId, id = 0) {
        $(dv).html(fcom.getLoader());
        $('#taxBackBtn').removeClass('d-none');
        fcom.ajax(fcom.makeUrl('Tax', 'ruleForm', [taxcatId, id]), '', function (t) {
            $(dv).html(t);
        });

    };

    setupTaxRule = function (frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('tax', 'setupTaxRule'), data, function (t) {
            if (t.status == 1) {
                searchRuleList(document.frmRuleListSearch);
            }
        });
    };
    deleteRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Tax', 'deleteRule'), data, function (res) {
            reloadList();
        });
    };

    clearSearch = function () {
        document.frmRuleListSearch.reset();
        reloadList();
    };

})();

function checkStatesDefault(countryId, stateIds, dv) {

    fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, 0]), '', function (res) {
        $(dv).empty();
        var firstChild = '<option value = "-1" >All</option>';
        $(dv).append(firstChild);
        $(dv).append(res);
        $(dv).find("option[value='-1']:eq(1)").remove();
        $(dv).find("option[value='']").remove();
        $(dv).selectpicker('val', stateIds);
        if (dv == '#taxruleloc_to_state_id') {
            if (stateIds.indexOf("-1") > -1) {
                $(dv).attr('disabled', true);
            }
        }

        $(dv).selectpicker('refresh');
    });
}



function getCombinedTaxes(self, taxStrId) {
    var taxruleId = $(self).closest('form').find('input[name="taxrule_id"]').val();
    if (taxStrId == 0) {
        $('.combined-tax-details--js').html('')
        return;
    }
    fcom.ajax(fcom.makeUrl('Tax', 'getCombinedTaxes', [taxStrId, taxruleId]), '', function (t) {
        $('.combined-tax-details--js').html(t);
        $('.combinetaxvalue--js').on("keyup", function () {
            if ('' == $(this).val()) {
                $(this).val(0);
            }
        });
    });
}

getCountryStates = function (countryId, stateId, dv) {
    fcom.ajax(fcom.makeUrl('Tax', 'getStates', [countryId, stateId]), '', function (res) {
        $(dv).empty();
        $(dv).append(res);
        $(dv).find("option:first").text('All');
        $(dv).find("option:first").val("-1");
        $(dv).val(-1);
        $(dv).selectpicker('refresh');
    });
};

getCountryStatesTaxInTaxForm = function (self, countryId, stateId) {

    var dv = '#taxruleloc_to_state_id';
    $(dv).empty();
    var firstChild = '<option value = "-1" >All</option>';
    $(dv).append(firstChild);
    if (countryId == -1) {
        $(dv).attr('disabled', true);
        $(self).closest('form').find(' select[name="taxruleloc_type"]').val(-1).attr('disabled', true);

        $(dv).val(-1);
        $(dv).selectpicker('refresh');
        return;
    }
    fcom.displayProcessing();
    fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, stateId]), '', function (res) {
        var locationFld = $(self).closest('form').find(' select[name="taxruleloc_type"]');
        $(dv).removeAttr('disabled');
        $(locationFld).removeAttr('disabled');
        $(dv).append(res);
        $(dv).find("option[value='-1']:eq(1)").remove();
        $(dv).find("option[value='']").remove();
        $(dv).selectpicker('refresh');
        if ('' == countryId) {
            $(locationFld).val($(locationFld + " option:first").val()).attr('disabled', 'disabled');
        } else if ('' == $(locationFld).val()) {
            $(locationFld).val($(locationFld + " option:eq(1)").val());
            $(locationFld).trigger('change');
        } else if (-1 == $(locationFld).val()) {
            $(locationFld).trigger('change');
        }

    });
    $.systemMessage.close();
};
