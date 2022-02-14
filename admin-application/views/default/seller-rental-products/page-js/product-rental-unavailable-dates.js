$(document).ready(function () {
    searchUnavailbleDates(document.frmSearch);
    productRentalUnavailableDatesForm();
});

(function () {
    searchUnavailbleDates = function (frm) {
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        var dv = $('#listing');
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'searchUnavailbleDates'), data, function (res) {
            $("#listing").html(res);
        });
    };

    clearSearch = function (selProd_id) {
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerRentalProducts', 'searchUnavailbleDates');
        } else {
            document.frmSearch.reset();
            searchUnavailbleDates(document.frmSearch);
        }
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchSpecialPricePaging;
        $(frm.page).val(page);
        searchUnavailbleDates(frm);
    }

    reloadList = function () {
        var frm = document.frmSearch;
        searchUnavailbleDates(frm);
    }

    productRentalUnavailableDatesForm = function (selprod_id = 0, pu_id = 0) {
        if (typeof pu_id == undefined || pu_id == null) {
            pu_id = 0;
        }
        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'productRentalUnavailableDatesForm', [selprod_id, pu_id]), '', function (t) {
            $('#unavailabledates-form--js').html(t);
            select2Init();
        });

    };

    setUpRentalUnavailableDates = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'setUpRentalUnavailableDates'), data, function (t) {
            productRentalUnavailableDatesForm();
            searchUnavailbleDates();
        });
    };

    deleteRentalUnavailableDates = function (selprodId, pu_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }

        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'deleteRentalUnavailableDates'), 'selprodId=' + selprodId + '&pu_id=' + pu_id, function (t) {
            searchUnavailbleDates();
        });
    };

    sellerProductDurationDiscountForm = function (selprod_id = 0, durdiscount_id = 0) {
        if (typeof durdiscount_id == undefined || durdiscount_id == null) {
            durdiscount_id = 0;
        }

        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'sellerProductDurationDiscountForm', [selprod_id, durdiscount_id]), '', function (t) {
            $('#product-rental-unavailable-dates').html(t);
            select2Init();
        });
    };

    setUpSellerProductDurationDiscount = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'setUpSellerProductDurationDiscount'), data, function (t) {
            sellerProductDurationDiscountForm();
            searchUnavailbleDates();
        });
    };

    deleteSellerProductDurationDiscount = function (produr_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'deleteSellerProductDurationDiscount'), 'produr_id=' + produr_id, function (t) {
            searchUnavailbleDates();
        });
    };

    select2Init = function () {
        $("select[name='product_name']").select2({
            closeOnSelect: true,
            dir: langLbl.layoutDirection,
            allowClear: true,
            placeholder: $("select[name='product_name']").attr('placeholder'),
            ajax: {
                url: fcom.makeUrl('SellerProducts', 'autoCompleteProducts'),
                dataType: 'json',
                delay: 250,
                method: 'post',
                data: function (params) {
                    return {
                        keyword: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.products,
                        pagination: {
                            more: params.page < data.pageCount
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            templateResult: function (result)
            {
                return result.name;
            },
            templateSelection: function (result)
            {
                return result.name || result.text;
            }
        }).on('select2:selecting', function (e)
        {
            var parentForm = $(this).closest('form').attr('id');
            var item = e.params.args.data;
            $("#" + parentForm + " input[name='pu_selprod_id']").val(item.id);
        }).on('select2:unselecting', function (e)
        {
            var parentForm = $(this).closest('form').attr('id');
            $("#" + parentForm + " input[name='pu_selprod_id']").val('');
        });
    };
})();
