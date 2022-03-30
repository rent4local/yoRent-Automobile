$(document).ready(function() {

    searchUsers(document.frmUserSearch);

    $(document).on('click', function() {
        $('.autoSuggest').empty();
    });

    $('input[name=\'keyword\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteJson'),
                data: {
                    keyword: request['term'],
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'] + '(' + item['username'] + ')',
                            value: item['username'],
                            id: item['user_id']
                        };
                    }));
                },
            });
        },
        'select': function(event, ui) {
            $("input[name='user_id']").val(ui.item.id);
        }
    });

    $('input[name=\'keyword\']').keyup(function() {
        $('input[name=\'user_id\']').val('');
    });

    /* redirect user to login page */
    $(document).on('click', 'ul.linksvertical li a.redirect--js', function(event) {
        event.stopPropagation();
    });


    $('input[name=\'shop_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteShop'),
                data: {
                    shopname: request['term'],
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['name'],
                            id: item['id']
                        };
                    }));
                },
            });
        },
        'select': function(event, ui) {
            $("input[name='shop_id']").val(ui.item.id);
        }
    });

    $('input[name=\'shop_name\']').keyup(function() {
        $('input[name=\'shop_id\']').val('');
    });


});

(function() {
    var currentPage = 1;
    var transactionUserId = 0;
    var rewardUserId = 0;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmUserSearchPaging;
        $(frm.page).val(page);
        searchUsers(frm);
    };

    searchUsers = function(form, page) {
        if (!page) {
            page = currentPage;
        }
        currentPage = page;
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/

        $("#userListing").html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Users', 'search'), data, function(res) {
            $("#userListing").html(res);
        });
    };

    reloadUserList = function() {
        searchUsers(document.frmUserSearchPaging, currentPage);
    };

    fillSuggetion = function(v) {
        $('#keyword').val(v);
        $('.autoSuggest').hide();
    };

    addUserForm = function(id) {
        var frm = document.frmUserSearchPaging;
        $.facebox(function() {
            userForm(id);
        });
    };

    userForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'form', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addBankInfoForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'bankInfoForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });

    };


    setupBankInfo = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupBankInfo'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    userAddresses = function(id) {
        $.facebox(function() {
            addUserAddress(id);
        });
    };

    addUserAddress = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addresses', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addAddress = function(userId, id) {
        $.facebox(function() {
            addOneAddress(userId, id)
        });
    };

    addOneAddress = function(userId, id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addressForm', [userId, id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    setupAddress = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupAddress'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    deleteAddress = function(userId, id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        var data = 'user_id=' + userId + '&id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Users', 'deleteAddress'), data, function(t) {
            if (t.userId > 0) {
                addUserAddress(t.userId);
            }
        });
    };

    deleteUser = function(userId) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        var data = 'user_id=' + userId;
        fcom.updateWithAjax(fcom.makeUrl('Users', 'deleteAccount'), data, function(t) {
            reloadUserList();
        });
    };

    transactions = function(userId) {
        transactionUserId = userId;
        $.facebox(function() {
            addTransaction(userId);
        });
    };


    addTransaction = function(userId) {
        fcom.ajax(fcom.makeUrl('Users', 'transaction', [userId]), '', function(t) {
           fcom.updateFaceboxContent(t);
        });
    };

    goToTransactionPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmTransactionSearchPaging;
        $(frm.page).val(page);
        data = fcom.frmData(frm);
    };

    updateTransaction = function(data) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'transaction', [transactionUserId]), data, function(t) {
            fcom.updateFaceboxContent(t);
        });
    };


    addUserTransaction = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addUserTransaction', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupUserTransaction = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupUserTransaction'), data, function(t) {
            if (t.userId > 0) {
                addTransaction(t.userId);
            }
        });
    };

    addUserLangForm = function(userId, langId) {
        $.facebox(function() {
            addLangForm(userId, langId);
        });
    };

    addLangForm = function(userId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'langForm', [userId, langId, autoFillLangData]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    rewards = function(userId) {
        rewardUserId = userId;
        $.facebox(function() {
            addReward(userId);
        });
    };

    addReward = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'rewards', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    goToRewardPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmRewardSearchPaging;
        $(frm.page).val(page);
        data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'rewards', [rewardUserId]), data, function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    addUserRewardPoints = function(userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'addUserRewardPoints', [userId]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupUserRewardPoints = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setupUserRewardPoints'), data, function(t) {
            if (t.userId > 0) {
                addReward(t.userId);
            }
        });
    };

    changePasswordForm = function(id) {
        var frm = document.frmUserSearchPaging;
        $.facebox(function() {
            changeUserPassword(id);
        });
    };


    changeUserPassword = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'changePasswordForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    updatePassword = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'updatePassword'), data, function(t) {
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    setupUsers = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Users', 'setup'), data, function(t) {
            if (t.userId > 0) {
                addBankInfoForm(t.userId);
                return false;
            }
            $(document).trigger('close.facebox');
            reloadUserList();
        });
    };

    addNewUsers = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'addNewUser'), data, function(t) {
            reloadUserList();
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    verifyUser = function(id, v) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('users', 'verify'), {
            userId: id,
            v: v
        }, function(t) {
            reloadUserList();
        });
        $.systemMessage.close();
    };

    toggleStatus = function(obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        var userId = parseInt(obj.id);
        if (userId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'userId=' + userId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('users', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

    clearUserSearch = function() {
        document.frmUserSearch.reset();
        document.frmUserSearch.user_id.value = '';
        searchUsers(document.frmUserSearch);
    };

    getCountryStates = function(countryId, stateId, dv) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'getStates', [countryId, stateId]), '', function(res) {
            $(dv).empty();
            $(dv).append(res);
        });
        $.systemMessage.close();
    };

    sendMailForm = function(id) {
        $.facebox(function() {
            sendMailToUser(id);
        });
    };

    sendMailToUser = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Users', 'sendMailForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    sendMail = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.displayProcessing();
        fcom.updateWithAjax(fcom.makeUrl('Users', 'sendMail'), data, function(t) {
            $(document).trigger('close.facebox');
        });
        $.systemMessage.close();
    };

    deletedUser = function() {
        document.location.href = fcom.makeUrl('deletedUsers');
    };

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmUsersListing").attr("action",fcom.makeUrl('Users','deleteSelected')).submit();
    };
    
    markSellerAsBuyer = function (userId) {
        if (!confirm(langLbl.confirmSellerAsBuyer)) {
            return;
        }
        var userId = parseInt(userId);
        if (1 > userId) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Users', 'markSellerAsBuyer'), {userId: userId}, function (t) {
            reloadUserList();
        });
    };

    addUserForm = function(id) {
        var frm = document.frmUserSearchPaging;
        $.facebox(function() {
            userForm(id);
        });
    };
    
    

})();


function stylePhoneNumberFld(element = "input[name='user_phone']", destroy = false) {

    var inputList = document.querySelectorAll(element);
    var country = ('' == langLbl.defaultCountryCode || undefined == langLbl.defaultCountryCode ) ? 'in' : langLbl.defaultCountryCode;
    inputList.forEach(function (input) {
        if (true == destroy) {
            $(input).removeAttr('style');
            var clone = input.cloneNode(true);
            $('.iti').replaceWith(clone);
        } else {
            var iti = window.intlTelInput(input, {
                separateDialCode: true,
                initialCountry: country,
                /* utilsScript: "/intlTelInput/intlTelInput-utils.js" */
            });
            $('<input>').attr({
                type: 'hidden',
                name: 'user_dial_code',
                value: "+" + iti.getSelectedCountryData().dialCode
            }).insertAfter(input);

            $('<input>').attr({
                type: 'hidden',
                name: 'user_country_iso',
                value: iti.getSelectedCountryData().iso2
            }).insertAfter(input);

            input.addEventListener('countrychange', function (e) {
                if (typeof iti.getSelectedCountryData().dialCode !== 'undefined') {
                    input.closest('form').user_dial_code.value = "+" + iti.getSelectedCountryData().dialCode;
                    input.closest('form').user_country_iso.value = iti.getSelectedCountryData().iso2;
                }
            });
        }
    });
    }
