$(document).ready(function () {
    $(".div_tracking_number").hide();
    $(".div_refund_security").hide();
    $("select[name='op_status_id']").change(function () {
        $(".div_tracking_number").hide();
        $(".div_refund_security").hide();
        if ($("select[name='op_status_id']").val() == SHIPPING_STATUS) {
            $(".div_tracking_number").show();
        }
        if ($("select[name='op_status_id']").val() == RENTAL_RETURN_STATUS) {
            $(".div_refund_security").show();
        }
        
    
        /* var data = 'val=' + $(this).val();
        fcom.ajax(fcom.makeUrl('Seller', 'checkIsShippingMode'), data, function (t) {
            var response = $.parseJSON(t);
            if (response["shipping"]) {
                $(".div_tracking_number").show();
            } else {
                $(".div_tracking_number").hide();
            }

            if (response["refundSecurity"]) {
                $(".div_refund_security").show();
            } else {
                $(".div_refund_security").hide();
            }
        }); */
    });
    

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

});

(function () {
    updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        /*var data = fcom.frmData(frm);*/
        $.mbsmessage(langLbl.processing, true, 'alert--process alert');
        $('input[name="btn_submit"]').attr('disabled', 'disabled');
        $.ajax({
            url: fcom.makeUrl('sellerOrders', 'changeOrderStatus'),
            type: 'post',
            dataType: 'json',
            data: new FormData($(frm)[0]),
            cache: false,
            contentType: false,
            processData: false,
            success: function (ans) {
                if (ans.status == true) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    setTimeout("pageRedirect(" + ans.op_id + ")", 1000);
                } else {
                    $('input[name="btn_submit"]').removeAttr('disabled');
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });

        /*fcom.updateWithAjax(fcom.makeUrl('sellerOrders', 'changeOrderStatus'), data, function (t) {
         setTimeout("pageRedirect(" + t.op_id + ")", 1000);
         });*/
    };
    
    /* Shipping Services */
    generateLabel = function (opId) {
        fcom.updateWithAjax(fcom.makeUrl('ShippingServices', 'generateLabel', [opId]), '', function (t) {
            window.location.reload();
        });
    }
    /* Shipping Services */
    
})();

function pageRedirect(op_id) {
    window.location.replace(fcom.makeUrl('SellerOrders', 'viewOrder', [op_id]));
}