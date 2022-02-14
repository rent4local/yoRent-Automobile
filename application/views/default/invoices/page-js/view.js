(function () {
    shareInvoiceWithBuyer = function (orderId) {
        fcom.ajax(fcom.makeUrl('Invoices', 'sendInvoice', [orderId]), '', function (ans) {
            window.location.href = fcom.makeUrl('Invoices', 'view', [orderId]);
        });
    };

})();