$(document).ready(function () {
    $(".div_refund_security").hide();
    $("select[name='op_status_id']").change(function () {
        var data = 'val=' + $(this).val();
        fcom.ajax(fcom.makeUrl('SellerOrders', 'checkIsShippingMode'), data, function (t) {
            var response = $.parseJSON(t);
            if (response["shipping"]) {
                $('.manualShipping-js').attr('data-fatreq', '{"required":false}');
            }
            /* [ RENTAL UPDATES */
            if (response["refundSecurity"]) {
                $(".div_refund_security").show();
            } else {
                $(".div_refund_security").hide();
            }
            /* ] */
            });
    });

    $(document).on('click', 'ul.linksvertical li a.redirect--js', function (event) {
        event.stopPropagation();
    });

    /* [ RENTAL UPDATES */
    var refund_security_type = $("select[name='refund_security_type']").val();
    if (refund_security_type != undefined && (refund_security_type == 1 || refund_security_type == 3)) {
        if (refund_security_type == 1) {
            $("input[name='refund_security_amount']").attr('readonly', 'readonly').val($("select[name='refund_security_type']").attr('completeamount'));
        } else {
            $("input[name='refund_security_amount']").attr('readonly', 'readonly').val(0);
        }
        $("input[name='refund_security_amount']").addClass('disabled-input');
    } else {
        $("input[name='refund_security_amount']").removeClass('disabled-input');
    }

    $("select[name='refund_security_type']").change(function () {
        if ($(this).val() == 1) {
            $("input[name='refund_security_amount']").attr('readonly', 'readonly').val($(this).attr('completeamount'));
            $("input[name='refund_security_amount']").addClass('disabled-input');
        } else if ($(this).val() == 2) {
            $("input[name='refund_security_amount']").removeAttr('readonly', 'readonly');
            $("input[name='refund_security_amount']").removeClass('disabled-input');
        } else if ($(this).val() == 3) {
            $("input[name='refund_security_amount']").attr('readonly', 'readonly').val(0);
            $("input[name='refund_security_amount']").addClass('disabled-input');
        }
    });
    /* ] */

});
function pageRedirect(op_id) {
    window.location.replace(fcom.makeUrl('SellerOrders', 'view', [op_id]));
}
(function () {
    updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        var op_id = $(frm.op_id).val();
        var data = fcom.frmData(frm);
        var orderStatusId = $(frm.op_status_id).val();

        if (0 < $(".shippingUser-js").length && '' == $(".shippingUser-js").val()) {
            $.systemMessage(langLbl.shippingUser, 'alert--danger', false);
            return;
        }

        var manualShipping = 0;
        if (0 < $("input.manualShipping-js").length) {
            manualShipping = $("input.manualShipping-js:checked").val();
        }

        if (0 < canShipByPlugin && 1 != manualShipping && orderShippedStatus == orderStatusId) {
            proceedToShipment(op_id);
        } else {
            $('input[name="btn_submit"]').attr('disabled', 'disabled');
            $('input[name="btn_submit"]').addClass('disabled-btn');
            fcom.updateWithAjax(fcom.makeUrl('SellerOrders', 'changeOrderStatus'), data, function (t) {
                if (t.status == true) {
                    setTimeout("pageRedirect(" + op_id + ")", 1000);
                } else {
                    $('input[name="btn_submit"]').removeAttr('disabled');
                    $('input[name="btn_submit"]').removeClass('disabled-btn');
                }
            });
        }
    };

    updateShippingCompany = function (frm) {
        var data = fcom.frmData(frm);
        var op_id = $(frm.op_id).val();
        if (!$(frm).validate())
            return;
        fcom.updateWithAjax(fcom.makeUrl('SellerOrders', 'updateShippingCompany'), data, function (t) {
            setTimeout("pageRedirect(" + op_id + ")", 1000);
        });
    };

    /* ShipStation */
    generateLabel = function (opId) {
        fcom.updateWithAjax(fcom.makeUrl('ShippingServices', 'generateLabel', [opId]), '', function (t) {
            window.location.reload();
        });
    }

    proceedToShipment = function (opId) {
        $.systemMessage(langLbl.processing, 'alert--process', false);
        if ('' == $(".shippingUser-js").val()) {
            $.systemMessage(langLbl.shippingUser, 'alert--danger', false);
            return;
        }
        fcom.ajax(fcom.makeUrl('ShippingServices', 'proceedToShipment', [opId]), '', function (t) {
            $.systemMessage.close();
            t = $.parseJSON(t);
            $.systemMessage(t.msg, 'alert--success', false);
            if (1 > t.status) {
                $.systemMessage(t.msg, 'alert--danger', false);
                return;
            }

            var form = "form.markAsShipped-js";
            if (0 < $(form).length) {
                $(form + " .status-js").val(orderShippedStatus).change();
                $(form + " .notifyCustomer-js").val(1);
                $(form + " input[name='tracking_number']").val(t.tracking_number);
                canShipByPlugin = 0;
                setTimeout(function () {
                    $(form).submit();
                }, 200);
            } else {
                window.location.reload();
            }
        });
    }
    /* ShipStation */
    
    showBreakdownPopup = function(el) {
        var targetId = $(el).data('target');
        $.facebox($(targetId).html(), 'faceboxWidth fbminwidth');
    }
    
    
})();