var selected_products = [];
$(document).ready(function () {
    
    searchAttachedFldsProducts();
    $('#verification-flds').delegate('.remove_vfld', 'click', function () {
        $(this).parents('li').remove();
    });

    $('#products_flds').delegate('.remove_pfld', 'click', function () {
        $(this).parents('li').remove();
    });

    $("select[name='product_name']").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='product_name']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('Seller', 'autoCompleteCatalogProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                var parentForm = $("select[name='products_flds']").closest('form').attr('id');
                var selectedFields = [];
                $('input[name="products_flds[]"]').each(function () {
                    selectedFields.push($(this).val());
                });

                return {
                    keyword: params.term, 
                    page: params.page,
                    fIsAjax: 1,
                    selProdId: $("#" + parentForm + " input[name='vflds_id']").val(),
                    selectedFields: selectedFields,
                };
            },
            beforeSend:
                function (xhr, opts) {
                    var parentForm = $("select[name='products_flds']").closest('form').attr('id');
                    var selprod_id = $("#" + parentForm + " input[name='vflds_id']").val();
                    if (1 > selprod_id) {
                        xhr.abort();
                    }
                    $('input[name="products_flds[]"]').each(function () {
                        selected_products.push($(this).val());
                    });
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
            return result.name;
        }
    }).on('select2:selecting', function (e)
    {
            var parentForm = $(this).closest('form').attr('id');
            var item = e.params.args.data;
            $('#select2-ver-products-js-container').remove();
            $('input[name=\'products_flds\']').val('');
            $('#productFields' + item.id).remove();
            $('#products_flds').append('<li id="productFields' + item.id + '"><span> ' + item.name  + '<i class="remove_pfld remove_param fas fa-times"></i><input type="hidden" name="products_flds[]" value="' + item.id + '" /></span></li>');
            setTimeout(function () {
                $("select[name='products_flds']").val('').trigger('change');
            }, 200);
    
    });

    $("select[name='verification_fields']").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='verification_fields']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('AttachVerificationFields', 'verificationFieldsAutoComplete'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                var parentForm = $("select[name='verification_fields']").closest('form').attr('id');
                var selectedFields = [];
                $('input[name="verification_fields[]"]').each(function () {
                    selectedFields.push($(this).val());
                });
                
                return {
                    keyword: params.term, 
                    page: params.page,
                    fIsAjax: 1,
                    selProdId: $("#" + parentForm + " input[name='vflds_id']").val(),
                    selectedFields: selectedFields,
                };
            },
            beforeSend:
                    function (xhr, opts) {
                        var parentForm = $("select[name='verification_fields']").closest('form').attr('id');
                        var selprod_id = $("#" + parentForm + " input[name='vflds_id']").val();
                        if (1 > selprod_id) {
                            xhr.abort();
                        }
                        $('input[name="verification_fields[]"]').each(function () {
                            selected_products.push($(this).val());
                        });
                    },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.verificationFlds,
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
            return result.name;
        }
    }).on('select2:selecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        var item = e.params.args.data;
        $('input[name=\'verification_fields\']').val('');
        $('#verificationFields' + item.id).remove();
        $('#verification-flds').append('<li id="verificationFields' + item.id + '"><span> ' + item.name  + '<i class="remove_vfld remove_param fas fa-times"></i><input type="hidden" name="verification_fields[]" value="' + item.id + '" /></span></li>');
        setTimeout(function () {
            $("select[name='verification_fields']").val('').trigger('change');
        }, 200);

    });
});
$(document).on('mouseover', "ul.list-tags li span i", function () {
    $(this).parents('li').addClass("hover");
});
$(document).on('mouseout', "ul.list-tags li span i", function () {
    $(this).parents('li').removeClass("hover");
});

(function () {
    var dv = '#listing';
    searchAttachedFldsProducts = function (frm) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing');
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('AttachVerificationFields', 'searchAttachedFldProducts'), data, function (res) {
            $("#listing").html(res);
        });
    };

    searchVerificationFlds = function(frm){
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader());
		fcom.ajax(fcom.makeUrl('AttachVerificationFields','verificationFldSearchListing'), data, function(res){
			$(dv).html(res);
		}); 
	};
    clearSearch = function (product_id) {
        if (0 < product_id) {
            location.href = fcom.makeUrl('AttachVerificationFields', 'index');
        } else {
            document.frmSearch.reset();
            searchAttachedFldsProducts(document.frmSearch);
        }
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmAttachedVerificationFldsPaging;
        $(frm.page).val(page);
        searchAttachedFldsProducts(frm);
    }

    reloadList = function () {
        var frm = document.frmAttachVerificationFldFrm;
        searchAttachedFldsProducts(frm);
    }

    deleteVerificationField = function (productId, vfldsId) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('AttachVerificationFields', 'deleteProductsVerificationFlds', [productId, vfldsId]), '', function (t) {
            searchAttachedFldsProducts(document.frmAttachVerificationFldFrm);
        });
    }

    showElement = function (currObj, value) {
        var sibling = currObj.siblings('div');
        if ('' != value) {
            sibling.text(value);
        }
        sibling.fadeIn();
        currObj.addClass('hidden');
    };

    setUpVerificationFlds = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('AttachVerificationFields', 'attachVerificationField'), data, function (t) {
            document.frmAttachVerificationFldFrm.reset();
            /*$('#ver-products-js').val(null).trigger('change');
            $("input[name='product_id']").val('');*/
            $('#products_flds').empty();
            $('#verification-flds').empty();
            $(frm).find("select[name='product_name']").trigger('change.select2');
            searchAttachedFldsProducts(document.frmAttachVerificationFldFrm);
        });
    };
})();

$(document).on('click', ".js-product-edit", function () {
    var productId = $(this).attr('row-id');
    var prodHtml = $(this).children('.js-prod-name').html();
    var prodName = prodHtml.split('<br>');
    fcom.ajax(fcom.makeUrl('AttachVerificationFields', 'getAttachedFieldsList', [productId]), '', function (t) {
        var ans = $.parseJSON(t);
        $("input[name='product_id']").val(productId);
        $("input[name='product_name']").val(prodName[0]);

        /* $('#ver-products-js').select2('data', {id: productId, name: prodName[0]}); */
        /*var newOption = new Option(prodName[0], productId, true, true);
        // Append it to the select
        $('#ver-products-js').append(newOption).trigger('change');
        $('#products_flds').append("<li id=productFields" + ans.verificationFlds[key]['vflds_id'] + "><span>" + ans.verificationFlds[key]['name'] +"<i class=\"verification-flds remove_vfld remove_param fas fa-times\"></i><input type=\"hidden\" name=\"verification_fields[]\" value=" + ans.verificationFlds[key]['vflds_id'] + " /></span></li>");*/
        $('#products_flds').empty();
        $('#products_flds').append('<li id="productFields' + productId + '"><span> ' + prodName[0]  + '<i class="remove_pfld remove_param fas fa-times"></i><input type="hidden" name="products_flds[]" value="' + productId + '" /></span></li>');
        $('#verification-flds').empty();
        for (var key in ans.verificationFlds) {
            $('#verification-flds').append("<li id=verificationFields" + ans.verificationFlds[key]['vflds_id'] + "><span>" + ans.verificationFlds[key]['name'] +"<i class=\"verification-flds remove_vfld remove_param fas fa-times\"></i><input type=\"hidden\" name=\"verification_fields[]\" value=" + ans.verificationFlds[key]['vflds_id'] + " /></span></li>");
        }
    });
});