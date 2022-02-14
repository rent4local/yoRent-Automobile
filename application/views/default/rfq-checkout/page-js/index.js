var pageContent = '.checkout-content-js';
var paymentDiv = '#payment';
var financialSummary = '.summary-listing-js';
var shippingSummaryDiv = '#payment';
var verificationSection = '#verification-section';

function checkLogin() {
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }
    return true;
}

function moveErrorAfterCustomUpload() {
    if (0 < $(".errorlist").length) {
    $(".errorlist").each(function (i, obj) {
      if ($(obj).siblings().attr("type") == "file") {
        var id = "err_" + $(obj).siblings().attr("name");
        $(obj)
          .detach()
          .insertAfter("." + id);
      }
    });
  }
}



$("document").ready(function () {
    $(document).on("keydown", "#cc_number", function () {
        var obj = $(this);
        var cc = obj.val();
        obj.attr('class', 'p-cards');
        if (cc != '') {
            var card_type = getCardType(cc).toLowerCase();
            obj.addClass('p-cards ' + card_type);
        }
    });
    setCheckoutFlow("VERIFICATION");
    $(document).on("submit", "form", function () {
        moveErrorAfterCustomUpload();
    });
});


(function () {
    loadFinancialSummary = function () {
        $(financialSummary).html(fcom.getLoader());
        var data = 'order_id=' + $orderId;
        fcom.updateWithAjax(fcom.makeUrl('RfqCheckout', 'getFinancialSummary'), data, function (ans) {
            $(financialSummary).html(ans.data);
            $('#netAmountSummary').html(ans.netAmount);
        }, [], false);
    };

    loadVerificationSection = function (showLoader = 1) {
        if (showLoader == 1) {
            $(pageContent).html(fcom.getLoader());
        }
        var data = 'order_id=' + $orderId;
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'verificationForm'), data, function (ans) {
            $(pageContent).html(ans);
            setCheckoutFlow('VERIFICATION');
        });
    };

    removeUploadedFile = function (fileId, recordId) {
        $.mbsmessage(langLbl.requestProcessing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'removeUploadedFile', [fileId, recordId]), '', function (ans) {
            $.mbsmessage.close();
            ans = $.parseJSON(ans);
            if (ans.status == 1) {
                loadVerificationSection(0);
            }
        });
    }

    submitVerificationFlds = function (frm) {
        if (verficationFlsEnable == 1) {
            if (!$(frm).validate()) {
                return false;
            }
        }

        /* [ SAVE SIGNATRURE IMAGE FIRST */
        if (signatureEnable == 1) {
            if ($('input[name="accept_term"]').prop('checked') == false) {
                $.mbsmessage(langLbl.termCheckCaption, true, 'alert--danger');
                return false;
            }
            if (signatureAdded == 0) {
                $.mbsmessage(langLbl.requestProcessing, false, 'alert--process');
                if (!saveImage()) {
                    return;
                }
            }

            if (verficationFlsEnable == 0) {
                loadFinancialSummary();
                loadPaymentSummary();
                setCheckoutFlow('PAYMENT');
            }
        } else {
            $.mbsmessage(langLbl.requestProcessing, false, 'alert--process');
        }
        /* ] */

        if (verficationFlsEnable == 1) {
            var frmData = new FormData(document.getElementById("frmSubmitVerificationFlds"));
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: fcom.makeUrl('RfqCheckout', 'setupVerificationFlds'),
                data: frmData,
                processData: false,
                contentType: false,
                cache: false,
                success: function (t) {
                    t = $.parseJSON(t);
                    if (t.status == 1) {
                        loadFinancialSummary();
                        loadPaymentSummary();
                        setCheckoutFlow('PAYMENT');
                    } else {
                        loadVerificationSection();
                        $.mbsmessage(t.msg, true, 'alert--danger');
                    }
                    return;
                },
                error: function (e) {
                    alert('error');
                }
            });
        }
    };


    addSign = function () {
        if (!checkLogin()) {
            return false;
        }
        signatureAdded = 0;
        var data = 'controllerName=' + 'RfqCheckout' + '&record_id=' + ORDER_NUMERIC_ID;
        fcom.ajax(fcom.makeUrl('Signature', 'view'), data, function (ans) {
            $("#e_sign").html(ans);
        });
    };


    viewOrder = function () {
        if (!checkLogin()) {
            return false;
        }
        resetPaymentSummary();
        loadShippingSummary();
        loadCartReviewDiv();
    };

    loadPaymentSummary = function () {
        if (!checkLogin()) {
            return false;
        }
        $(pageContent).html(fcom.getLoader());
        var data = 'order_id=' + $orderId;
        $.mbsmessage(langLbl.requestProcessing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'PaymentSummary'), data, function (ans) {
            $.mbsmessage.close();
            $(pageContent).html(ans);
            $(paymentDiv).addClass('is-current');
        });
    };

    walletSelection = function (el) {
        if (!checkLogin()) {
            return false;
        }
        var wallet = ($(el).is(":checked")) ? 1 : 0;
        var data = 'payFromWallet=' + wallet + '&order_id=' + $orderId;
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'walletSelection'), data, function (ans) {
            loadPaymentSummary();
        });
    };



    sendPayment = function (frm, dv = '') {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action');
        var submitBtn = $('input[type=submit]', frm);
        var btnText = submitBtn.val();
        submitBtn.attr('disabled', 'disabled');
        submitBtn.val(submitBtn.data('processing-text'));
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        fcom.ajax(action, data, function (t) {
            submitBtn.val(btnText);
            try {
                var json = $.parseJSON(t);
                if (typeof json.status != 'undefined' && 1 > json.status) {
                    submitBtn.removeAttr('disabled');
                    $.mbsmessage(json.msg, true, 'alert--danger');
                    return false;
                }
                if (typeof json.html != 'undefined') {
                    $(dv).append(json.html);
                }
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (e) {
                $(dv).append(t);
            }
        });
    };

    /* Phone/Email Verification for COD */
    validateOtp = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        var method = $(frm).data('method');
        var orderId = $(frm).find('input[name="order_id"]').val();
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'validateOtp'), data, function (t) {
            t = $.parseJSON(t);
            if (1 == t.status) {
                if ('undefined' != typeof method) {
                    $(frm).attr('action', fcom.makeUrl(method + 'Pay', 'charge', [orderId]));
                }
                $.mbsmessage(t.msg, false, 'alert--success');
                $('.successOtp-js').removeClass('d-none');
                $('.otpBlock-js').addClass('d-none');
                confirmOrder(frm);
            } else {
                $.mbsmessage(t.msg, false, 'alert--danger');
                invalidOtpField();
            }
        });
        return false;
    };

    resendOtp = function (frm = '') {
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl('RfqCheckout', 'resendOtp'), '', function (t) {
            t = $.parseJSON(t);
            if (typeof t.status != 'undefined' && 1 > t.status) {
                $.mbsmessage(t.msg, false, 'alert--danger');
                return false
            }
            $(".otpVal-js").val('');
            if ('' != frm) {
                $(frm).attr('onsubmit', 'validateOtp(this); return(false);');
                $('input[name="btn_submit"]', frm).val(langLbl.proceed);
                $(".otpVal-js").removeAttr('disabled');
            }
            $.mbsmessage(t.msg, false, 'alert--success');
            startOtpInterval('', "showElements");
            $(".resendOtpDiv-js").addClass('d-none');
        });
        return false;
    };
    /* Phone/Email Verification for COD */
    goToBack = function () {
        if ($(".payment-js").hasClass('is-active')) {
            loadPaymentSummary();
        } else {
            window.location.href = fcom.makeUrl('Cart');
        }
    }


    setCheckoutFlow = function (type) {
        var obj = $('.checkout-progress');
        obj.find('div').removeClass('is-complete');
        obj.find('div').removeClass('is-active');
        obj.find('div').removeClass('pending');
        switch (type) {
            case 'BILLING':
                obj.find('.billing-js').addClass('is-active');
                obj.find('.shipping-js').addClass('pending');
                obj.find('.verification-js').addClass('pending');
                obj.find('.payment-js').addClass('pending');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'SHIPPING':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-active');
                obj.find('.verification-js').addClass('pending');
                obj.find('.payment-js').addClass('pending');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'VERIFICATION':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-complete');
                obj.find('.verification-js').addClass('is-active');
                obj.find('.payment-js').addClass('pending');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'PAYMENT':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-complete');
                obj.find('.verification-js').addClass('is-complete');
                obj.find('.payment-js').addClass('is-active');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'COMPLETED':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-complete');
                obj.find('.verification-js').addClass('is-complete');
                obj.find('.payment-js').addClass('is-complete');
                obj.find('.order-complete-js').addClass('pending');
                break;
            default:
                obj.find('li').addClass('pending');
        }
    }

})();
