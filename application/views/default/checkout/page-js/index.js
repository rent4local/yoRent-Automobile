var pageContent = ".checkout-content-js";

var loginDiv = "#login-register";
var addressDiv = "#address";
var addressFormDiv = "#addressFormDiv";
var addressDivFooter = "#addressDivFooter";
var addressWrapper = "#addressWrapper";
var addressWrapperContainer = ".address-wrapper";
var alreadyLoginDiv = "#alreadyLoginDiv";
var shippingSummaryDiv = "#shipping-summary";
var cartReviewDiv = "#cart-review";
var paymentDiv = "#payment";
var financialSummary = ".summary-listing-js";
var verificationSection = "#verification-section";

function checkLogin() {
    if (isUserLogged() == 0) {
        loginPopUpBox();
        return false;
    }
    return true;
}

function showLoginDiv() {
    $(".step").removeClass("is-current");
    $(loginDiv).find(".step__body").show();
    $(loginDiv).find(".step__body").html(fcom.getLoader());
    fcom.ajax(fcom.makeUrl("Checkout", "login"), "", function (ans) {
        $(loginDiv).find(".step__body").html(ans);
        $(loginDiv).addClass("is-current");
    });
}

function editCart() {
    if (!checkLogin()) {
        return false;
    }
    $(".js-editCart").toggle();
}

function showAddressFormDiv(address_type) {
    if (typeof address_type == "undefined") {
        address_type = 0;
    }
    editAddress(0, address_type);
    if ($(".payment-js").hasClass("is-active") == false) {
        setCheckoutFlow("BILLING");
    }
}
function showAddressList() {
    if (!checkLogin()) {
        return false;
    }
    loadAddressDiv();
    setCheckoutFlow("BILLING");
}
function resetAddress(address_type) {
    loadAddressDiv(address_type);
}
function showShippingSummaryDiv() {
    return loadShippingSummaryDiv();
}
function showCartReviewDiv() {
    return loadCartReviewDiv();
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
        obj.attr("class", "p-cards");
        if (cc != "") {
            var card_type = getCardType(cc).toLowerCase();
            obj.addClass("p-cards " + card_type);
        }
    });

    $(document).on("submit", "form", function () {
        moveErrorAfterCustomUpload();
    });
});

(function () {
    setUpLogin = function (frm, v) {
        v.validate();
        if (!v.isValid())
            return;
        fcom.ajax(
                fcom.makeUrl("GuestUser", "login"),
                fcom.frmData(frm),
                function (t) {
                    var ans = JSON.parse(t);
                    if (ans.notVerified == 1) {
                        var autoClose = false;
                    } else {
                        var autoClose = true;
                    }
                    if (ans.status == 1) {
                        $.mbsmessage(ans.msg, autoClose, "alert--success");
                        location.href = ans.redirectUrl;
                        return;
                    }
                    $.mbsmessage(ans.msg, autoClose, "alert--danger");
                }
        );
        return false;
    };

    loadloginDiv = function () {
        fcom.ajax(fcom.makeUrl("Checkout", "loadLoginDiv"), "", function (ans) {
            $(loginDiv).html(ans);
        });
    };

    loadFinancialSummary = function () {
        $(financialSummary).html(fcom.getLoader());
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "getFinancialSummary"),
                "",
                function (ans) {
                    $(financialSummary).html(ans.data);
                    $("#netAmountSummary").html(ans.netAmount);
                },
                [],
                false
                );
    };

    setUpRegisteration = function (frm, v) {
        v.validate();
        if (!v.isValid())
            return;
        fcom.updateWithAjax(
                fcom.makeUrl("GuestUser", "register"),
                fcom.frmData(frm),
                function (t) {
                    if (t.status == 1) {
                        if (t.needLogin) {
                            window.location.href = t.redirectUrl;
                            return;
                        } else {
                            loadAddressDiv();
                        }
                    }
                }
        );
    };

    removeAddress = function (id, address_type) {
        if (!checkLogin()) {
            return false;
        }
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        if (typeof address_type == "undefined") {
            address_type = 0;
        }
        data = "id=" + id;
        fcom.updateWithAjax(
                fcom.makeUrl("Addresses", "deleteRecord"),
                data,
                function (res) {
                    loadAddressDiv(address_type);
                }
        );
    };

    editAddress = function (address_id, address_type) {
        if (!checkLogin()) {
            return false;
        }
        if (typeof address_id == "undefined") {
            address_id = 0;
        }
        if (typeof address_type == "undefined") {
            address_type = 0;
        }
        var data = "address_id=" + address_id + "&address_type=" + address_type;
        fcom.ajax(fcom.makeUrl("Checkout", "editAddress"), data, function (ans) {
            $(pageContent).html(ans);
            if ($(".payment-js").hasClass("is-active") == false) {
                setCheckoutFlow("BILLING");
            }
        });
    };

    setUpAddress = function (frm, address_type) {
        if (!checkLogin()) {
            return false;
        }
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(
                fcom.makeUrl("Addresses", "setUpAddress"),
                data,
                function (t) {
                    if (t.status == 1) {
                        if ($("#hasAddress").length > 0) {
                            $("#hasAddress").val(1);
                        }
                        if ($(frm.addr_id).val() == 0 || address_type == 1) {
                            loadAddressDiv(address_type);
                            setTimeout(function () {
                                setDefaultAddress(t.addr_id);
                            }, 1000);
                        } else {
                            setUpAddressSelection(t.addr_id);
                        }
                    }
                }
        );
    };

    setDefaultAddress = function (id) {
        $("input[name='shipping_address_id']").each(function () {
            $(this).removeAttr("checked");
        });
        $(".address-" + id + " input[name=shipping_address_id]").attr(
                "checked",
                "checked"
                );
    };

    setUpAddressSelection = function (addr_id) {
        if (!checkLogin()) {
            return false;
        }

        if (typeof addr_id == "undefined") {
            var shipping_address_id = $(
                    'input[name="shipping_address_id"]:checked'
                    ).val();
        } else {
            var shipping_address_id = addr_id;
        }
        var isShippingSameAsBilling = 1;
        var data =
                "shipping_address_id=" +
                shipping_address_id +
                "&billing_address_id=" +
                shipping_address_id +
                "&isShippingSameAsBilling=" +
                isShippingSameAsBilling;
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "setUpAddressSelection"),
                data,
                function (t) {
                    if (t.status == 1) {
                        if (t.loadAddressDiv) {
                            loadAddressDiv();
                        } else if ($(".payment-js").hasClass("is-active")) {
                            loadPaymentSummary();
                            loadFinancialSummary();
                        } else {
                            if (t.hasPhysicalProduct) {
                                $(shippingSummaryDiv).show();
                            } else {
                                $(shippingSummaryDiv).hide();
                                loadShippingAddress();
                            }
                            loadShippingSummaryDiv();
                            loadFinancialSummary();
                        }
                    }
                }
        );
    };

    setUpShippingApi = function (frm) {
        if (!checkLogin()) {
            return false;
        }
        var data = fcom.frmData(frm);
        $(shippingSummaryDiv).html(fcom.getLoader());
        fcom.ajax(
                fcom.makeUrl("Checkout", "setUpShippingApi"),
                data,
                function (ans) {
                    $(shippingSummaryDiv).html(ans);
                    $(".sduration_id-Js").trigger("change");
                }
        );
    };

    getProductShippingComment = function (el, selprod_id) {
        var sduration_id = $(el).find(":selected").val();
        $(".shipping_comment_" + selprod_id).hide();
        $("#shipping_comment_" + selprod_id + "_" + sduration_id).show();
    };

    getProductShippingGroupComment = function (el, prodgroup_id) {
        var sduration_id = $(el).find(":selected").val();
        $(".shipping_group_comment_" + prodgroup_id).hide();
        $("#shipping_group_comment_" + prodgroup_id + "_" + sduration_id).show();
    };

    setUpShippingMethod = function () {
        var data = $("#shipping-summary select").serialize();
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "setUpShippingMethod"),
                data,
                function (t) {
                    if (t.status == 1) {
                        if (t.section == "verification" && CART_TYPE == CART_TYPE_RENTAL) {
                            loadVerificationSection();
                            loadFinancialSummary();
                        } else {
                            loadFinancialSummary();
                            loadPaymentSummary();
                            setCheckoutFlow("PAYMENT");
                        }
                    }
                }
        );
    };

    addSign = function () {
        if (!checkLogin()) {
            return false;
        }
        signatureAdded = 0;
        fcom.ajax(fcom.makeUrl("Signature", "view"), "", function (ans) {
            $("#e_sign").html(ans);
        });
    };

    loadAddressDiv = function (address_type) {
        if (!checkLogin()) {
            return false;
        }
        $(pageContent).html(fcom.getLoader());
        if (typeof address_type == "undefined") {
            address_type = 0;
        }
        var data = "address_type=" + address_type;
        fcom.ajax(fcom.makeUrl("Checkout", "addresses"), data, function (ans) {
            $(pageContent).html(ans);
        });
    };

    loadShippingAddress = function () {
        fcom.ajax(
                fcom.makeUrl("Checkout", "loadBillingShippingAddress"),
                "",
                function (t) {
                    $(addressDiv).html(t);
                }
        );
    };

    resetShippingSummary = function () {
        resetCartReview();
        fcom.ajax(
                fcom.makeUrl("Checkout", "resetShippingSummary"),
                "",
                function (ans) {
                    $(shippingSummaryDiv).html(ans);
                }
        );
    };

    removeShippingSummary = function () {
        resetCartReview();
        fcom.ajax(
                fcom.makeUrl("Checkout", "removeShippingSummary"),
                "",
                function (ans) {}
        );
    };

    resetCartReview = function () {
        fcom.ajax(fcom.makeUrl("Checkout", "resetCartReview"), "", function (ans) {
            $(cartReviewDiv).html(ans);
        });
    };

    loadShippingSummary = function () {
        $(shippingSummaryDiv).show();
        $(shippingSummaryDiv).html(fcom.getLoader());

        fcom.ajax(
                fcom.makeUrl("Checkout", "loadShippingSummary"),
                "",
                function (ans) {
                    $(shippingSummaryDiv).html(ans);
                }
        );
    };

    changeShipping = function () {
        if (!checkLogin()) {
            return false;
        }
        loadShippingSummaryDiv();
        resetCartReview();
        resetPaymentSummary();
    };

    loadShippingSummaryDiv = function () {
        $(pageContent).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl("Checkout", "shippingSummary"), "", function (ans) {
            $(pageContent).html(ans);
            $(".sduration_id-Js").trigger("change");
            setCheckoutFlow("SHIPPING");
        });
    };

    loadVerificationDiv = function (showLoader = 1) {
        if (showLoader == 1) {
            $(pageContent).html(fcom.getLoader());
        }
        fcom.ajax(fcom.makeUrl("Checkout", "verificationForm"), "", function (ans) {
            $(pageContent).html(ans);
            setCheckoutFlow("VERIFICATION");
        });
    };

    loadVerificationSection = function (showLoader = 1) {
        if (showLoader == 1) {
            $(shippingSummaryDiv).html(fcom.getLoader());
        }

        fcom.ajax(fcom.makeUrl("Checkout", "verificationForm"), "", function (ans) {
            $(shippingSummaryDiv).html(ans);
            setCheckoutFlow("VERIFICATION");
        });
    };

    removeUploadedFile = function (obj, fileId) {
        $.mbsmessage(langLbl.requestProcessing, false, "alert--process");
        fcom.ajax(
                fcom.makeUrl("Checkout", "removeUploadedFile", [fileId]),
                "",
                function (ans) {
                    $.mbsmessage.close();
                    ans = $.parseJSON(ans);
                    if (ans.status == 1) {
                        loadVerificationSection(0);
                    }
                }
        );
    };

    viewOrder = function () {
        if (!checkLogin()) {
            return false;
        }
        resetPaymentSummary();
        loadShippingSummary();
        loadCartReviewDiv();
    };

    resetPaymentSummary = function () {
        $(paymentDiv).removeClass("is-current");
        fcom.ajax(
                fcom.makeUrl("Checkout", "resetPaymentSummary"),
                "",
                function (ans) {
                    $(paymentDiv).html(ans);
                }
        );
    };

    loadCartReviewDiv = function () {
        $(pageContent).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl("Checkout", "reviewCart"), "", function (ans) {
            $(pageContent).html(ans);
        });
    };

    loadCartReview = function () {
        fcom.ajax(fcom.makeUrl("Checkout", "loadCartReview"), "", function (ans) {
            $(cartReviewDiv).html(ans);
        });
    };

    loadPaymentSummary = function (showLoader = 1) {
        if (showLoader == 1) {
            if (!checkLogin()) {
                return false;
            }
            $(pageContent).html(fcom.getLoader());
            $.mbsmessage(langLbl.requestProcessing, false, "alert--process");
        }

        fcom.ajax(fcom.makeUrl("Checkout", "PaymentSummary"), "", function (ans) {
            $.mbsmessage.close();
            $(pageContent).html(ans);
            $(paymentDiv).addClass("is-current");
        });
    };

    walletSelection = function (el) {
        if (!checkLogin()) {
            return false;
        }
        $.mbsmessage(langLbl.processing, false, "alert--process alert");
        var wallet = $(el).is(":checked") ? 1 : 0;
        var data = "payFromWallet=" + wallet + "&orderId=" + ORDER_ID;
        fcom.ajax(fcom.makeUrl("Checkout", "walletSelection"), data, function (t) {
            loadPaymentSummary(0);
            /*ans = $.parseJSON(t);
             if (ans.isLoadPaymentTab == 1) {
             loadPaymentSummary(0);
             var tabsId = '#payment_methods_tab';
             $(tabsId + " li:first a").addClass('active');
             if ($(tabsId + ' li a.active').length > 0) {
             loadTab($(tabsId + ' li a.active'));
             }
             $(tabsId + ' a').click(function () {
             if ($(this).hasClass('active')) {
             return false;
             }
             $(tabsId + ' li a.active').removeClass('active');
             $(this).addClass('active');
             loadTab($(this));
             return false;
             });
             } else {
             loadPaymentSummary(0);
             }*/
        });
    };

    getPromoCode = function () {
        checkLogin();

        /* $.facebox(function () { */
        fcom.ajax(fcom.makeUrl("Checkout", "getCouponForm"), "", function (t) {
            /* $.facebox(t, 'faceboxWidth medium-fb-width'); */
            $("#exampleModal").html(t);
            $("#exampleModal").modal("show");
            $("input[name='coupon_code']").focus();
        });
        /* }); */
    };

    applyPromoCode = function (frm) {
        checkLogin();
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);

        fcom.updateWithAjax(
                fcom.makeUrl("Cart", "applyPromoCode"),
                data,
                function (res) {
                    /* $("#facebox .close").trigger('click'); */
                    $("#exampleModal .close").click();
                    $.systemMessage.close();
                    loadFinancialSummary();
                    if ($(paymentDiv).hasClass("is-current")) {
                        loadPaymentSummary();
                    }
                }
        );
    };

    triggerApplyCoupon = function (coupon_code) {
        document.frmPromoCoupons.coupon_code.value = coupon_code;
        applyPromoCode(document.frmPromoCoupons);
        return false;
    };

    removePromoCode = function () {
        fcom.updateWithAjax(
                fcom.makeUrl("Cart", "removePromoCode"),
                "",
                function (res) {
                    loadFinancialSummary();
                    if ($(paymentDiv).hasClass("is-current")) {
                        loadPaymentSummary();
                    }
                }
        );
    };

    useRewardPoints = function (frm) {
        checkLogin();
        $.systemMessage.close();
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "useRewardPoints"),
                data,
                function (res) {
                    loadFinancialSummary();
                    loadPaymentSummary();
                }
        );
    };

    removeRewardPoints = function () {
        checkLogin();
        $.systemMessage.close();
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "removeRewardPoints"),
                "",
                function (res) {
                    loadFinancialSummary();
                    loadPaymentSummary();
                }
        );
    };

    resetCheckoutDiv = function () {
        removeShippingSummary();
        resetPaymentSummary();
        loadShippingSummaryDiv();
    };

    setCheckoutFlow = function (type) {
        var obj = $(".checkout-progress");
        obj.find("div").removeClass("is-complete");
        obj.find("div").removeClass("is-active");
        obj.find("div").removeClass("pending");
        switch (type) {
            case "BILLING":
                obj.find(".billing-js").addClass("is-active");
                obj.find(".shipping-js").addClass("pending");
                obj.find(".verification-js").addClass("pending");
                obj.find(".payment-js").addClass("pending");
                obj.find(".order-complete-js").addClass("pending");
                break;
            case "SHIPPING":
                obj.find(".billing-js").addClass("is-complete");
                obj.find(".shipping-js").addClass("is-active");
                obj.find(".verification-js").addClass("pending");
                obj.find(".payment-js").addClass("pending");
                obj.find(".order-complete-js").addClass("pending");
                break;
            case "VERIFICATION":
                obj.find(".billing-js").addClass("is-complete");
                obj.find(".shipping-js").addClass("is-complete");
                obj.find(".verification-js").addClass("is-active");
                obj.find(".payment-js").addClass("pending");
                obj.find(".order-complete-js").addClass("pending");
                break;
            case "PAYMENT":
                obj.find(".billing-js").addClass("is-complete");
                obj.find(".shipping-js").addClass("is-complete");
                obj.find(".verification-js").addClass("is-complete");
                obj.find(".payment-js").addClass("is-active");
                obj.find(".order-complete-js").addClass("pending");
                break;
            case "COMPLETED":
                obj.find(".billing-js").addClass("is-complete");
                obj.find(".shipping-js").addClass("is-complete");
                obj.find(".verification-js").addClass("is-complete");
                obj.find(".payment-js").addClass("is-complete");
                obj.find(".order-complete-js").addClass("pending");
                break;
            default:
                obj.find("li").addClass("pending");
        }
    };

    submitVerificationFlds = function (frm) {
        if (verficationFlsEnable == 1) {
            if (!$(frm).validate()) {
                return false;
            }
        }

        /* [ SAVE SIGNATRURE IMAGE FIRST */
        if (signatureEnable == 1) {
            $(".error-warning--js").html(" ");
            if ($('input[name="accept_term"]').prop("checked") == false) {
                $(".error-warning--js").html(langLbl.termCheckCaption);
                return false;
            }

            $.mbsmessage(langLbl.requestProcessing, false, "alert--process");
            if (signatureAdded == 0) {
                if (1 > $("#signature").length) {
                    $.mbsmessage.close();
                    $.mbsmessage(langLbl.signatureRequires, false, "alert--danger");
                    return;
                }

                if (!saveImage()) {
                    return;
                }
            }

            if (verficationFlsEnable == 0) {
                loadPaymentSummary();
                setCheckoutFlow("PAYMENT");
            }
        } else {
            $.mbsmessage(langLbl.requestProcessing, false, "alert--process");
        }
        /* ] */

        if (verficationFlsEnable == 1) {
            /* if (!$(frm).validate()){
             return false;
             } */
            var frmData = new FormData(
                    document.getElementById("frmSubmitVerificationFlds")
                    );
            $.ajax({
                type: "POST",
                enctype: "multipart/form-data",
                url: fcom.makeUrl("Checkout", "setupVerificationFlds"),
                data: frmData,
                processData: false,
                contentType: false,
                cache: false,

                success: function (t) {
                    t = $.parseJSON(t);
                    if (t.status == 1) {
                        /* loadFinancialSummary(); */
                        loadPaymentSummary();
                        setCheckoutFlow("PAYMENT");
                    } else {
                        loadVerificationSection();
                        $.mbsmessage(t.msg, true, "alert--danger");
                    }
                    return;
                },
                error: function (e) {
                    alert("error");
                },
            });
        }
    };

    sendPayment = function (frm, dv = "") {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        var action = $(frm).attr("action");
        var submitBtn = $("input[type=submit]", frm);
        var btnText = submitBtn.val();
        submitBtn.attr("disabled", "disabled");
        submitBtn.val(submitBtn.data("processing-text"));
        $.mbsmessage(langLbl.processing, false, "alert--process alert");
        fcom.ajax(action, data, function (t) {
            submitBtn.val(btnText);
            try {
                var json = $.parseJSON(t);
                if (typeof json.status != "undefined" && 1 > json.status) {
                    submitBtn.removeAttr("disabled");
                    $.mbsmessage(json.msg, true, "alert--danger");
                    return false;
                }
                if (typeof json.html != "undefined") {
                    $(dv).append(json.html);
                }
                if (json["redirect"]) {
                    $(location).attr("href", json["redirect"]);
                }
            } catch (e) {
                $(dv).append(t);
            }
        });
    };

    displayPickupAddress = function (productId, shopId, srNo) {
        var selectedAddr = $("#product-js-" + srNo).val();
        data =
                "productId=" +
                productId +
                "&shopId=" +
                shopId +
                "&srNo=" +
                srNo +
                "&selectedAddr=" +
                selectedAddr;
        fcom.ajax(
                fcom.makeUrl("Addresses", "getPickupAddresses"),
                data,
                function (rsp) {
                    $("#exampleModal").html(rsp);
                    $("#exampleModal").modal("show");
                }
        );
    };

    getSellProductsPickupAddresses = function (pickUpBy, recordId) {
        var addrId = $(".js-slot-addr-" + pickUpBy).attr("data-addr-id");
        var slotId = $("input[name='slot_id[" + pickUpBy + "]']").val();
        var slotDate = $("input[name='slot_date[" + pickUpBy + "]']").val();
        var data =
                "pickUpBy=" +
                pickUpBy +
                "&recordId=" +
                recordId +
                "&addrId=" +
                addrId +
                "&slotId=" +
                slotId +
                "&slotDate=" +
                slotDate;
        fcom.ajax(
                fcom.makeUrl("Addresses", "getSellProductsPickupAddresses"),
                data,
                function (rsp) {
                    $("#exampleModal").html(rsp);
                    $("#exampleModal").modal("show");
                }
        );
    };

    setUpPickup = function () {
        /*@todo
         * if (!checkLogin()) {
         return false;
         }*/

        var pickupAddresses = $(".pickup-address-js").serialize();
        var data = pickupAddresses;
        fcom.updateWithAjax(fcom.makeUrl("Checkout", "setUpPickUp"), data, function (t) {
            if (t.section == "verification" && CART_TYPE == CART_TYPE_RENTAL) {
                loadVerificationSection();
                loadFinancialSummary();
            } else {
                loadFinancialSummary();
                loadPaymentSummary();
                setCheckoutFlow("PAYMENT");
            }
        }
        )
    };

    setUpPickUpForSell = function () {
        /*@todo
         * if (!checkLogin()) {
         return false;
         }*/

        var slotIds = $(".js-slot-id").serialize();
        var slotDates = $(".js-slot-date").serialize();
        var data = slotIds + "&" + slotDates;
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "setUpPickUpForSell"),
                data,
                function (t) {
                    loadPaymentSummary();
                    setCheckoutFlow("PAYMENT");
                }
        );
    };

    setPickupAddress = function (srNo, isNew = 0) {
        var selectedPickUpAddr = $('input[name="pickup_address"]:checked').val();
        $("#product-js-" + srNo).val(selectedPickUpAddr);
        var addressHtml = $(".js-addr-" + selectedPickUpAddr).html();
        addressHtml = addressHtml.replace(/<[\/]{0,1}(p)[^><]*>/ig, "  ");
        $(".js-address-detail-" + srNo).parents('.picked-address').show();
        $(".js-address-detail-" + srNo).html(addressHtml);
        $('.list--js_' + srNo + ' .pickupAddJs').hide();
        $("#pickup-address-edit-js-" + srNo).show();
        $("#exampleModal .close").click();
    };

    billingAddress = function (ele) {
        if ($(ele).prop("checked") == false) {
            loadAddressDiv(1);
        }
    };

    setUpBillingAddressSelection = function (elm) {
        if (!checkLogin()) {
            return false;
        }

        var billing_address_id = $(
                'input[name="shipping_address_id"]:checked'
                ).val();
        var isShippingSameAsBilling = 0;
        var data =
                "billing_address_id=" +
                billing_address_id +
                "&isShippingSameAsBilling=" +
                isShippingSameAsBilling;
        fcom.updateWithAjax(
                fcom.makeUrl("Checkout", "setUpBillingAddressSelection"),
                data,
                function (t) {
                    if (t.status == 1) {
                        loadFinancialSummary();
                        loadPaymentSummary();
                        setCheckoutFlow("PAYMENT");
                    }
                }
        );
    };

    /* Phone/Email Verification for COD */
    validateOtp = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        var method = $(frm).data("method");
        var orderId = $(frm).find('input[name="order_id"]').val();
        fcom.ajax(fcom.makeUrl("Checkout", "validateOtp"), data, function (t) {
            t = $.parseJSON(t);
            if (1 == t.status) {
                if ("undefined" != typeof method) {
                    $(frm).attr(
                            "action",
                            fcom.makeUrl(method + "Pay", "charge", [orderId])
                            );
                }
                $.mbsmessage(t.msg, false, "alert--success");
                $(".successOtp-js").removeClass("d-none");
                $(".otpBlock-js").addClass("d-none");
                confirmOrder(frm);
            } else {
                $.mbsmessage(t.msg, false, "alert--danger");
                invalidOtpField();
            }
        });
        return false;
    };

    resendOtp = function (frm = "") {
        $.mbsmessage(langLbl.processing, false, "alert--process");
        fcom.ajax(fcom.makeUrl("Checkout", "resendOtp"), "", function (t) {
            t = $.parseJSON(t);
            if (typeof t.status != "undefined" && 1 > t.status) {
                $.mbsmessage(t.msg, false, "alert--danger");
                return false;
            }
            $(".otpVal-js").val("");
            if ("" != frm) {
                $(frm).attr("onsubmit", "validateOtp(this); return(false);");
                $('input[name="btn_submit"]', frm).val(langLbl.proceed);
                $(".otpVal-js").removeAttr("disabled");
            }
            $.mbsmessage(t.msg, false, "alert--success");
            startOtpInterval("", "showElements");
            $(".resendOtpDiv-js").addClass("d-none");
        });
        return false;
    };
    /* Phone/Email Verification for COD */

    orderPickUpData = function (order_id) {
        var data = "order_id=" + order_id;
        fcom.ajax(
                fcom.makeUrl("Checkout", "orderPickUpData"),
                data,
                function (rsp) {
                    /* $.facebox(rsp, 'faceboxWidth medium-fb-width'); */
                    $("#exampleModal").html(rsp);
                    $("#exampleModal").modal("show");
                }
        );
    };

    goToBack = function () {
        if ($(".payment-js").hasClass("is-active")) {
            loadPaymentSummary();
        } else {
            window.location.href = fcom.makeUrl("Cart");
        }
    };

    orderShippingData = function (order_id) {
        var data = "order_id=" + order_id;
        fcom.ajax(
                fcom.makeUrl("Checkout", "orderShippingData"),
                data,
                function (rsp) {
                    /* $.facebox(rsp, 'faceboxWidth medium-fb-width'); */
                    $("#exampleModal").html(rsp);
                    $("#exampleModal").modal("show");
                }
        );
    };

    signatureForm = function() {
        fcom.ajax(fcom.makeUrl("Checkout", "signatureForm"), '', function (respone) {
            $("#exampleModal").html(respone);
            $("#exampleModal").modal("show");
        });
    }
    
    uploadSignFile = function (frm) {
        if (!$(frm).validate()) {
            return;
        }
        
        var frmData = new FormData(document.getElementById("frmSignImage"));
        $.mbsmessage(langLbl.processing, false, "alert--process");
        $.ajax({
            type: "POST",
            enctype: "multipart/form-data",
            url: fcom.makeUrl("Checkout", "uploadSignFile"),
            data: frmData,
            processData: false,
            contentType: false,
            cache: false,
            success: function (t) {
                t = $.parseJSON(t);
                if (t.status == 1) {
                    $("#e_sign").html(t.sectionHtml);
                    signatureAdded = 1;
                    $("#exampleModal .close").click();
                    $.mbsmessage(t.msg, false, "alert--success");
                } else {
                    $.mbsmessage(t.msg, false, "alert--danger");
                }
                return; 
            },
            error: function (e) {
                alert("error");
            },
        });
    }

})();
