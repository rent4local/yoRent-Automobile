$(document).ready(function () {
    searchProducts(document.frmSearch);

    $('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function (request, response) {
            $.ajax({
                url: fcom.makeUrl('Users', 'autoCompleteJson'),
                data: {keyword: request['term'], fIsAjax: 1},
                dataType: 'json',
                type: 'post',
                success: function (json) {
                    response($.map(json, function (item) {
                        return {label: item['name'] + '(' + item['username'] + ')', value: item['username'], id: item['id']};
                    }));
                },
            });
        },
        select: function (event, ui) {
            $("input[name='user_id']").val(ui.item.id);
        }
    });
});
(function () {
    var currentProdId = 0;
    var currentPage = 1;
    var dv = '#listing';
    searchProducts = function (frm) {

        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing');
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProducts', [0, SELPROD_TYPE]), data, function (res) {
            $("#listing").html(res);
        });
    };
    addSellerProductForm = function (product_id, selprod_id) {
        /* if( !product_id && selprod_id == 0 ){
         return;
         } */
        $.facebox(function () {
            sellerProductForm(product_id, selprod_id);
        });
    };

    sellerProductForm = function (product_id, selprod_id) {
        /* if( !product_id && selprod_id == 0 ){
         return;
         } */
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductForm', [product_id, selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setUpSellerProduct = function (frm) {
        if (!$(frm).validate())
            return;

        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProduct'), data, function (t) {
            if (t.selprod_id > 0) {
                $(frm.splprice_selprod_id).val(t.selprod_id);
            }
            reloadList();
            $(document).trigger('close.facebox');
            /* if(t.langId > 0){
             sellerProductLangForm(t.selprod_id,t.langId);
             } */
        });
    };

    sellerProductLangForm = function (selprod_id, lang_id, autoFillLangData = 0) {
        fcom.resetEditorInstance();
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductLangForm', [selprod_id, lang_id, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(lang_id);
        });
    };

    sellerProductDelete = function (id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'sellerProductDelete'), data, function (res) {
            reloadList();
        });
    };
    
    sellerProductDeleteSale = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'sellerProductDeleteSale'), data, function(res) {
			reloadList();
		});
	};
    
    

    setUpSellerProductLang = function (frm) {
        if (!$(frm).validate())
            return;

        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductLang'), data, function (t) {
            if (t.selprod_id > 0) {
                $(frm.splprice_selprod_id).val(t.selprod_id);
            }
            if (t.langId > 0) {
                sellerProductLangForm(t.selprod_id, t.langId);
            }
        });
    };
    addSellerProductSpecialPrices = function (selprod_id) {
        $.facebox(function () {
            sellerProductSpecialPrices(selprod_id);
        });
    };


    sellerProductSpecialPrices = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductSpecialPrices', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });

    };

    sellerProductSpecialPriceForm = function (selprod_id, splprice_id) {
        if (typeof splprice_id == undefined || splprice_id == null) {
            splprice_id = 0;
        }
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductSpecialPriceForm', [selprod_id, splprice_id]), '', function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    setUpSellerProductSpecialPrice = function (frm) {
        if (!$(frm).validate())
            return;

        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductSpecialPrice'), data, function (t) {
            sellerProductSpecialPrices($(frm.splprice_selprod_id).val());
           
        });
        return false;
    };

    deleteSellerProductSpecialPrice = function (splprice_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splprice_id, function (t) {
          
            $(document).trigger('close.facebox');
        });
    };

    sellerProductVolumeDiscounts = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductVolumeDiscounts', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    sellerProductVolumeDiscountForm = function (selprod_id, voldiscount_id) {
        if (typeof voldiscount_id == undefined || voldiscount_id == null) {
            voldiscount_id = 0;
        }
        $.facebox(function () {
            fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductVolumeDiscountForm', [selprod_id, voldiscount_id]), '', function (t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };

    setUpSellerProductVolumeDiscount = function (frm) {
        if (!$(frm).validate())
            return;

        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductVolumeDiscount'), data, function (t) {
            sellerProductVolumeDiscounts($(frm.voldiscount_selprod_id).val());

        });
        return false;
    };

    deleteSellerProductVolumeDiscount = function (voldiscount_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductVolumeDiscount'), 'voldiscount_id=' + voldiscount_id, function (t) {
            sellerProductVolumeDiscounts(t.selprod_id);
            $(document).trigger('close.facebox');
        });
    }

    cancelForm = function (frm) {
        searchProducts(document.frmSearch);
        $(document).trigger('close.facebox');
    };

    productSeo = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeo', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
            getProductSeoGeneralForm(selprod_id);
        });
    };

    getProductSeoGeneralForm = function (selprod_id) {
        fcom.displayProcessing();

        fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeoGeneralForm'), 'selprod_id=' + selprod_id, function (t) {
            fcom.updateFaceboxContent(t);
        });

    }

    setupProductMetaTag = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdMeta'), data, function (t) {

            if (t.langId > 0) {
                editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
                return;
            }

        });
    }

    setupProductLangMetaTag = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdMetaLang'), data, function (t) {
            if (t.langId > 0) {
                editProductMetaTagLangForm(t.metaId, t.langId, t.metaType);
                return;
            }
        });

    }

    editProductMetaTagLangForm = function (metaId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'productSeoLangForm', [metaId, langId, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    sellerProductLinkFrm = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'sellerProductLinkFrm', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    }

    setUpSellerProductLinks = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupSellerProductLinks'), data, function (t) {
        });
    }

    linkPoliciesForm = function (product_id, selprod_id, ppoint_type) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'linkPoliciesForm', [product_id, selprod_id, ppoint_type]), '', function (t) {
            fcom.updateFaceboxContent(t);
            searchPoliciesToLink();


        });

    };

    searchPoliciesToLink = function (form) {
        var form = (form) ? form : document.frmLinkWarrantyPolicies;
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }

        fcom.ajax(fcom.makeUrl('SellerProducts', 'searchPoliciesToLink'), data, function (res) {
            $('#listPolicies').html(res);
            fcom.resetFaceboxHeight();
        });
    };

    addPolicyPoint = function (selprod_id, ppoint_id) {
        var data = 'selprod_id=' + selprod_id + '&ppoint_id=' + ppoint_id;

        fcom.ajax(fcom.makeUrl('SellerProducts', 'addPolicyPoint'), data, function (res) {
            searchPoliciesToLink();
        });
    };

    removePolicyPoint = function (selprod_id, ppoint_id) {
        var data = 'selprod_id=' + selprod_id + '&ppoint_id=' + ppoint_id;
        fcom.ajax(fcom.makeUrl('SellerProducts', 'removePolicyPoint'), data, function (res) {
            searchPoliciesToLink();
        });
    };

    goToNextPolicyToLinkPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPolicyToLinkSearchPaging;
        $(frm.page).val(page);
        searchPoliciesToLink(frm);
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        searchProducts(frm);
    }

    reloadList = function () {
        var frm = document.frmSearch;
        searchProducts(frm);
    }


    productAttributeGroupForm = function ( ) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('SellerProducts', 'productAttributeGroupForm'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    }

    setupProduct = function (frm) {
        if (!$(frm).validate())
            return;
        var addingNew = ($(frm.product_id).val() == 0);
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProduct'), data, function (t) {
            reloadList();
            if (addingNew) {
                productLangForm(t.product_id, t.lang_id);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    setupProductLang = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setUpSellerProductLang'), data, function (t) {
            reloadList();
            if (t.lang_id > 0) {
                productLangForm(t.product_id, t.lang_id);
                return;
            }
            $(document).trigger('close.facebox');
            return;
        });
        return;
    };

    clearSearch = function () {
        document.frmSearch.reset();
        document.frmSearch.user_id.value = '';
        searchProducts(document.frmSearch);
    };

    toggleStatus = function (e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var selprodId = parseInt(obj.value);
        if (selprodId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        var status = 0; 
        if ($(obj).prop('checked') == true) {
            status = 1;
        } 
        
        data = 'selprodId=' + selprodId + '&productType='+SELPROD_TYPE + '&status='+status;
        fcom.ajax(fcom.makeUrl('SellerProducts', 'changeStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    deleteSelected = function () {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmSelProdListing").attr("action", fcom.makeUrl('SellerProducts', 'deleteSelected')).submit();
    };
    addSpecialPrice = function () {
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
            $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
            return false;
        }
        $("#frmSelProdListing").attr({'action': fcom.makeUrl('SellerProducts', 'specialPrice'), 'target': "_blank"}).removeAttr('onsubmit').submit();
        searchProducts(document.frmSearch);
        showActionsBtns();
    };

    addVolumeDiscount = function () {
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
            $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
            return false;
        }
        $("#frmSelProdListing").attr({'action': fcom.makeUrl('SellerProducts', 'volumeDiscount'), 'target': "_blank"}).removeAttr('onsubmit').submit();
        searchProducts(document.frmSearch);
        showActionsBtns();
    };

    /* [ Seller Products Rental Options */
    addSellerProductSaleForm = function (productId, selprod_id, userId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerProducts', 'productSaleDetailsForm', [productId, selprod_id, userId]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };
    setupProductSaleDetails = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupProdSaleDetails'), data, function (t) {
            $(document).trigger('close.facebox');
        });
    }

    sellerProductDurationDiscounts = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'sellerProductDurationDiscounts', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    sellerProductDurationDiscountForm = function (selprod_id, durdiscount_id) {
        if (typeof durdiscount_id == undefined || durdiscount_id == null) {
            durdiscount_id = 0;
        }
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'sellerProductDurationDiscountForm', [selprod_id, durdiscount_id]), '', function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    sellerProductDurationDiscountForm = function (selprod_id, durdiscount_id) {
        if (typeof durdiscount_id == undefined || durdiscount_id == null) {
            durdiscount_id = 0;
        }
        $.facebox(function () {
            fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'sellerProductDurationDiscountForm', [selprod_id, durdiscount_id]), '', function (t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };
    setUpSellerProductDurationDiscount = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'setUpSellerProductDurationDiscount'), data, function (t) {
            sellerProductDurationDiscounts($(frm.produr_selprod_id).val());
        });
        return false;
    };

    deleteSellerProductDurationDiscount = function (produr_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'deleteSellerProductDurationDiscount'), 'produr_id=' + produr_id, function (t) {
            sellerProductDurationDiscounts(t.selprod_id);
            $(document).trigger('close.facebox');
        });
    }

    productRentalUnavailableDates = function (selprod_id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'productRentalUnavailableDates', [selprod_id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };
    productRentalUnavailableDatesForm = function (selprod_id, pu_id) {
        if (typeof pu_id == undefined || pu_id == null) {
            pu_id = 0;
        }
        $.facebox(function () {
            fcom.displayProcessing();
            fcom.ajax(fcom.makeUrl('SellerRentalProducts', 'productRentalUnavailableDatesForm', [selprod_id, pu_id]), '', function (t) {
                fcom.updateFaceboxContent(t);
            });
        });
    };

    setUpRentalUnavailableDates = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'setUpRentalUnavailableDates'), data, function (t) {
            productRentalUnavailableDates($(frm.pu_selprod_id).val());
        });
        return false;
    };

    deleteRentalUnavailableDates = function (pu_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'deleteRentalUnavailableDates'), 'pu_id=' + pu_id, function (t) {
            productRentalUnavailableDates(t.selprod_id);
            $(document).trigger('close.facebox');
        });
    };

    translateRentalData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var sprodata_rental_terms = $("textarea[name='sprodata_rental_terms[" + defaultLang + "]']").val();
        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || sprodata_rental_terms == "" || alreadyOpen == true) {
            return false;
        }
        var data = "sprodata_rental_terms=" + sprodata_rental_terms + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('SellerRentalProducts', 'translatedProductRenatlData'), data, function (t) {
            if (t.status == 1) {
                $("textarea[name='sprodata_rental_terms[" + toLangId + "]']").val(t.sprodata_rental_terms);
            }
        });
    };

    /* [ Seller Products Rental Options ] */

    updatePriceFields = function (durationType = 0) {
        durationType = parseInt(durationType);
        switch (durationType) {
            case 2 : /* DAYS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').removeAttr('disabled');
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');
                break;
            case 3 : /* WEEKS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-daily--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').val(0);
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');

                break;
            case 4 : /* MONTHS */
                $('.price-hourly--js input').attr('disabled', 'disabled');
                $('.price-daily--js input').attr('disabled', 'disabled');
                $('.price-weekly--js input').attr('disabled', 'disabled');
                $('.price-hourly--js input').val(0);
                $('.price-daily--js input').val(0);
                $('.price-weekly--js input').val(0);
                $('.price-monthly--js input').removeAttr('disabled');
                break;
            default : /* HOURS */
                $('.price-hourly--js input').removeAttr('disabled');
                $('.price-daily--js input').removeAttr('disabled');
                $('.price-weekly--js input').removeAttr('disabled');
                $('.price-monthly--js input').removeAttr('disabled');
                break;
        }
    };


    getUniqueSlugUrl = function(obj,str,recordId){
        if(str == ''){
            return;
        }
        var data = {url_keyword:str,recordId:recordId}
        fcom.ajax(fcom.makeUrl('SellerProducts', 'isProductRewriteUrlUnique'), data, function(t) { 
            var ans = $.parseJSON(t);
            $(obj).next().html(ans.msg);
            if(ans.status == 0){
                $(obj).next().addClass('text-danger');
            }else{
                $(obj).next().removeClass('text-danger');
            }
        });
    };


})();
