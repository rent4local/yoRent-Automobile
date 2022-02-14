$(document).ready(function() {
    console.log("Hello...");
    var showChar = 100;
    var ellipsestext = "...";
    var moretext = "more";
    var lesstext = "less";
    $('.more').each(function() {
        var content = $(this).html();

        if (content.length > showChar) {

            var c = content.substr(0, showChar);
            var h = content.substr(showChar - 1, content.length - showChar);

            var html = c + '<span class="moreellipses">' + ellipsestext + '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

            $(this).html(html);
        }

    });

    $(".morelink").click(function() {
        if ($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
});
(function() {
    offersListing = function($rfqId) {
        var data = 'rfq_id=' + $rfqId;
        $('#listing').html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('CounterOffer', 'listingForBuyer'), data, function(res) {
            $('#listing').html(res);
        });
    };

    getUploadedDocuments = function(rfqId) {
        let data = 'rfq_id=' + rfqId;
        fcom.ajax(fcom.makeUrl('RequestForQuotes', 'getUploadedDocuments'), data, function(res) {
            $('#uploaded-documents-js').html(res);
        });
    }

    counterOfferForm = function(rfqId) {
        var data = 'rfq_id=' + rfqId;
        fcom.ajax(fcom.makeUrl('CounterOffer', 'form'), data, function(res) {
            $('#counter-offer-form-js').html(res);
            $('#counter-offer-form-section-js').show();
            var position = $("#counter-offer-form-section-js").offset().top - 100;
            $("html,body").animate({ scrollTop: position }, "slow");
        });
    }

    changeStatus = function(rfqId, status) {
        var data = 'rfq_id=' + rfqId + '&status=' + status;
        fcom.updateWithAjax(fcom.makeUrl('CounterOffer', 'updateStatusByBuyer'), data, function(ans) {
            // var ans = JSON.parse(ans);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                window.location.reload();
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    }

    setupCounterOffer = function(frm) {

        if (!$(frm).validate()) {
            return false;
        }

        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('CounterOffer', 'setupBuyerCounterOffer'), data, function(ans) {
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