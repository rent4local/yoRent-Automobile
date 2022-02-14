var dv = '#ordersListing';

$(document).ready(function () {
    search(document.frmSearch);
});

search = function (frm) {
    var data = fcom.frmData(frm);
    $(dv).html(fcom.getLoader());
    fcom.ajax(fcom.makeUrl('ProductReturns', 'searchProductReturns'), data, function (res) {
        $(dv).html(res);
    });
};

goToProductReturns = function (page) {
    if (typeof page == undefined || page == null) {
        page = 1;
    }
    var frm = document.frmProductReturns;
    $(frm.page).val(page);
    search(frm);
}

clearSearch = function () {
    document.frmSearch.reset();
    search(document.frmSearch);
}

sendEmailForOverdueProducts = function (orderID, oprID) {
    var agree = confirm(langLbl.confirmSendMail);
    if (!agree) {
        return false;
    }

    var data = {
        order_id: orderID,
        op_id: oprID
    };

    fcom.updateWithAjax(fcom.makeUrl('ProductReturns', 'overdueProductNotification'), data, function (t) {
        window.location.reload();
    });
}