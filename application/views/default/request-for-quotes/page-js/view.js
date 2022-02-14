(function () {
    setupQuotedOffer = function (frm) {
        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'setupOffer'), data, function (ans) {
            var ans = JSON.parse(ans);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                window.location.reload();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

    setupQuotedDocument = function () {
        var data = new FormData();
        data.append('counter_offer_document', $("input[name='counter_offer_document']")[0].files[0]);
        data.append('rfq_id', $("input[name='counter_offer_rfq_id']").val());
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        $.ajax({
            url: fcom.makeUrl('RequestForQuotes', 'uploadDocument'),
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (t) {
                var ans = $.parseJSON(t);
                if (ans.status == 1) {
                    $("input[name='counter_offer_document']").val('');
                    getUploadedDocuments($("input[name='counter_offer_rfq_id']").val(), ans.msg);

                } else {
                    $(document).trigger('close.mbsmessage');
                    $.systemMessage(ans.msg, 'alert--danger');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error Occurred.");
            }
        });
    };

    getUploadedDocuments = function (rfqId, msg) {
        let data = 'rfq_id=' + rfqId;
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'getUploadedDocuments'), data, function (res) {
            $('#uploaded-documents-js').html(res);
            if (msg != '' && msg != undefined) {
                $(document).trigger('close.mbsmessage');
                $.systemMessage(msg, 'alert--success');
            }
        });
    }

    removeRfqDocument = function (rfqId, afileId) {
        let data = 'rfq_id=' + rfqId + '&afile_id=' + afileId
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'removeDocument'), data, function (ans) {
            var ans = JSON.parse(ans);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                $('#document-js-' + afileId).remove();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    }


    offersListing = function ($rfqId) {
        var data = 'rfq_id=' + $rfqId;
        $('#listing').html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('CounterOffer', 'listingForSeller'), data, function (res) {
            $('#listing').html(res);
        });
    };


    /* changeStatus = function (rfqId, status, offerId) {
        var data = 'rfq_id=' + rfqId + '&status=' + status + "&counter_offer_id=" + offerId;
        fcom.updateWithAjax(fcom.makeUrl('CounterOffer', 'changeStatus'), data, function (res) {
            window.location.reload();
        });
    } */

    counterOfferForm = function (rfqId) {
        var data = 'rfq_id=' + rfqId;
        fcom.ajax(fcom.makeUrl('CounterOffer', 'formForSeller'), data, function (res) {
            $('#counter-offer-form-js').html(res);
            $('#counter-offer-form-section-js').show();
            var position = $("#counter-offer-form-section-js").offset().top + 100;
            $("html,body").animate({scrollTop: position}, "slow");
            
        });
    }

    changeStatus = function (rfqId, status) {
        var data = 'rfq_id=' + rfqId + '&status=' + status;
        fcom.updateWithAjax(fcom.makeUrl('CounterOffer', 'updateStatusBySeller'), data, function (ans) {
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                window.location.reload();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    }

    setupCounterOffer = function (frm) {

        if (!$(frm).validate()) {
            return false;
        }

        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('CounterOffer', 'setupSellerCounterOffer'), data, function (ans) {
            var ans = JSON.parse(ans);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                window.location.reload();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

})();