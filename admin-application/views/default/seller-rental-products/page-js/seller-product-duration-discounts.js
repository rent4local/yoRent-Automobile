$(document).ready(function () {
    searchDurationDiscounts(document.frmSearch);
    sellerProductDurationDiscountForm();
});

(function () {
    var dv = '#listing';
    searchDurationDiscounts = function (frm) {
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        var dv = $('#listing');
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'searchDurationDiscounts'), data, function (res) {
            $("#listing").html(res);
        });
    };
    clearSearch = function (selProd_id) {
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerRentalProducts', 'searchDurationDiscounts');
        } else {
            document.frmSearch.reset();
            searchDurationDiscounts(document.frmSearch);
        }
    };
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchSpecialPricePaging;
        $(frm.page).val(page);
        searchDurationDiscounts(frm);
    }

    reloadList = function () {
        var frm = document.frmSearch;
        searchDurationDiscounts(frm);
    }

    sellerProductDurationDiscountForm = function (selprod_id = 0, durdiscount_id = 0) {
        if (typeof durdiscount_id == undefined || durdiscount_id == null) {
            durdiscount_id = 0;
        }

        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'sellerProductDurationDiscountForm', [selprod_id, durdiscount_id]), '', function (t) {
            $('#discount-form--js').html(t);
            select2Init();
        });
    };
    setUpSellerProductDurationDiscount = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'setUpSellerProductDurationDiscount'), data, function (t) {
            sellerProductDurationDiscountForm();
            searchDurationDiscounts();
        });
    };
    deleteSellerProductDurationDiscount = function (produr_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'deleteSellerProductDurationDiscount'), 'produr_id=' + produr_id, function (t) {
            searchDurationDiscounts();
        });
    }

    select2Init = function () {
        $("select[name='product_name']").select2({
            closeOnSelect: true,
            dir: langLbl.layoutDirection,
            allowClear: true,
            placeholder: $("select[name='product_name']").attr('placeholder'),
            ajax: {
                url: fcom.makeUrl('sellerProducts', 'autoCompleteProducts'),
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
            $("#" + parentForm + " input[name='produr_selprod_id']").val(item.id);
            /* $("#" + parentForm + " input[name='produr_duration_type']").val(item.duration_type); */
            $("#" + parentForm + " .duration_label--js").html(item.duration_label);
            
        }).on('select2:unselecting', function (e)
        {
            var parentForm = $(this).closest('form').attr('id');
            $("#" + parentForm + " input[name='produr_selprod_id']").val('');
            $("#" + parentForm + " .duration_label--js").html(" ");
        });
    };

})();
