(function () {
    generateInvoice = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Invoices', 'generateInvoice'), data, function (ans) {
            window.location = fcom.makeUrl(ans.controllerName, 'viewOrder', [ans.orderId]);
        });
    };
})();