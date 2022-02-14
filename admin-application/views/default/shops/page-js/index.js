$(document).ready(function () {
    searchShops(document.frmShopSearch);
});
$(document).on('change', '.logo-language-js', function () {
    /* $(document).delegate('.logo-language-js','change',function(){ */
    var lang_id = $(this).val();
    var shop_id = document.frmShopLogo.shop_id.value;
    shopImages(shop_id, 'logo', 0, lang_id);
});
$(document).on('change', '.banner-language-js', function () {
    var lang_id = $(this).val();
    var shop_id = document.frmShopBanner.shop_id.value;
    var slide_screen = $(".prefDimensions-js").val();
    shopImages(shop_id, 'banner', slide_screen, lang_id);
});
$(document).on('change', '.prefDimensions-js', function () {
    var slide_screen = $(this).val();
    var shop_id = document.frmShopBanner.shop_id.value;
    var lang_id = $(".banner-language-js").val();
    shopImages(shop_id, 'banner', slide_screen, lang_id);
});
/*$(document).on('change','.bg-language-js',function(){
	var lang_id = $(this).val();
	var shop_id = document.frmShopBg.shop_id.value;
	shopImages(shop_id,'bg',lang_id);
});*/
$(document).on('change', '.collection-language-js', function () {
    var lang_id = $(this).val();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    var shop_id = document.frmCollectionMedia.shop_id.value;
    shopCollectionImages(shop_id, scollection_id, lang_id);
});
(function () {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dvt = '#shopFormChildBlock';

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmShopSearchPaging;
        $(frm.page).val(page);
        searchShops(frm);
    }

    reloadList = function () {
        var frm = document.frmShopSearchPaging;
        searchShops(frm);
    }

    reloadCollectionList = function () {
        shopCollections($("input[name='collection_shopId']").val());
    }

    addShopForm = function (id) {
        $.facebox(function () {
            shopForm(id);
        });
    };
    shopForm = function (id) {
        fcom.displayProcessing();
        var frm = document.frmShopSearchPaging;
        fcom.ajax(fcom.makeUrl('Shops', 'form', [id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupShop = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Shops', 'setup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                /* $.mbsmessage(t.msg,'','alert--success'); */
                addShopLangForm(t.shopId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addShopLangForm = function (shopId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Shops', 'langForm', [shopId, langId, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });

    };

    setupShopLang = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Shops', 'langSetup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                addShopLangForm(t.shopId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    searchShops = function (form) {
        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/

        $("#shopListing").html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Shops', 'search'), data, function (res) {

            $("#shopListing").html(res);
        });
    };

    clearShopSearch = function () {
        document.frmShopSearch.reset();
        searchShops(document.frmShopSearch);
    };

    getCountryStates = function (countryId, stateId, dv) {
        fcom.ajax(fcom.makeUrl('Shops', 'getStates', [countryId, stateId]), '', function (res) {
            $(dv).empty();
            $(dv).append(res);
        });
    };

    getStatesByCountryCode = function (countryCode, stateCode, dv, idCol = 'state_id') {
        fcom.ajax(fcom.makeUrl('Shops', 'getStatesByCountryCode', [countryCode, stateCode, idCol]), '', function (res) {
            $(dv).empty();
            $(dv).append(res);
        });
    };

    shopMediaForm = function (shopId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('shops', 'media', [shopId]), '', function (t) {
            shopImages(shopId, 'logo', 1);
            shopImages(shopId, 'banner', 1);
            shopImages(shopId, 'bg', 1);
            fcom.updateFaceboxContent(t);
        });
    };

    shopImages = function (shopId, imageType, slide_screen, lang_id) {
        fcom.ajax(fcom.makeUrl('shops', 'images', [shopId, imageType, lang_id, slide_screen]), '', function (t) {
            if (imageType == 'logo') {
                $('#logo-image-listing').html(t);
            } else if (imageType == 'banner') {
                $('#banner-image-listing').html(t);
            } else {
                $('#bg-image-listing').html(t);
            }
            fcom.resetFaceboxHeight();
        });
    };

    shopTemplates = function (shopId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('shops', 'shopTemplate', [shopId]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setTemplate = function (shopId, ltemplateId) {
        fcom.updateWithAjax(fcom.makeUrl('shops', 'setTemplate', [shopId, ltemplateId]), '', function (t) {
            shopTemplates(shopId);
            return;
        });
    };

	/* shopCollectionProducts= function(shopId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('shops', 'shopCollection', [shopId]), '', function(t) {
			fcom.updateFaceboxContent(t);
			getShopCollectionGeneralForm(shopId);
		});
	}; */

    deleteShopCollection = function (shop_id, scollection_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('shops', 'deleteShopCollection', [shop_id, scollection_id]), '', function (res) {
            searchShopCollections(shop_id);
        });
    };
    deleteSelected = function () {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmCollectionsListing").attr("action", fcom.makeUrl('Shops', 'deleteSelectedCollections')).submit();
    };

    shopCollections = function (shopId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('shops', 'shopCollections', [shopId]), '', function (t) {
            fcom.updateFaceboxContent(t);
            searchShopCollections(shopId);
        });
    };

    shopAgreement = function (shopId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('shops', 'shopAgreement', [shopId]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    deleteShopAgreement = function(afileId, shopId){
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return;
        }
        if (afileId < 1 || shopId < 1) {
            return false;
        }
        var data = new FormData();
        data.append('id', afileId);
        data.append('shopId', shopId);
        $.ajax({
            url: fcom.makeUrl('Shops', 'deleteShopAgreement'),
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (t) {
                $.mbsmessage.close();
                $.systemMessage.close();
                var ans = $.parseJSON(t);
                if (ans.status == 1) {
                    fcom.displaySuccessMessage(ans.msg);
                    setTimeout(function(){
                        shopAgreement(shopId);
                    }, 1000);      
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error Occurred.");
            }
        });
        $.systemMessage.close();
    }

    setupShopAgreement = function () {
        var data = new FormData();
        var shopId = $("#frmShopAgreement input[name='shop_id']").val();
        data.append('shop_agreemnet', $("input[name='shop_agreemnet']")[0].files[0]);
        data.append('shop_id', shopId);
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        $.ajax({
            url: fcom.makeUrl('Shops', 'setupShopAgreement'),
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (t) {
                $.mbsmessage.close();
                $.systemMessage.close();
                var ans = $.parseJSON(t);
                $(document).trigger('close.mbsmessage');
                if (ans.status == 1) {
                    $("input[name='shop_agreemnet']").val('');
                    fcom.displaySuccessMessage(ans.msg);
                    setTimeout(function(){
                        shopAgreement(shopId);
                    }, 1000);   
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert("Error Occurred.");
            }
        });
        $.systemMessage.close();
    };

    searchShopCollections = function (shopId) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('shops', 'searchShopCollections', [shopId]), '', function (t) {
            $(dvt).html(t);
        });
    };

    getShopCollectionGeneralForm = function (shop_id, scollection_id) {
        fcom.ajax(fcom.makeUrl('shops', 'shopCollectionGeneralForm', [shop_id, scollection_id]), '', function (t) {
            $(dvt).html(t);
        });
    };

    setupShopCollection = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shops', 'setupShopCollection'), data, function (t) {
            if (t.langId > 0) {
                editShopCollectionLangForm(t.shop_id, t.collection_id, t.langId);
                return;
            }

        });
    }

    setupShopCollectionlangForm = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shops', 'setupShopCollectionLang'), data, function (t) {
            if (t.langId > 0) {
                editShopCollectionLangForm(t.shop_id, t.scollection_id, t.langId);
                return;
            }
        });

    }

    editShopCollectionLangForm = function (shop_id, scollection_id, langId, autoFillLangData = 0) {
        if (typeof (scollection_id) == "undefined" || scollection_id < 0) {
            return false;
        }
        if (typeof (langId) == "undefined" || langId < 0) {
            return false;
        }
        if (typeof (shop_id) == "undefined" || shop_id < 0) {

            return false;
        }
        fcom.ajax(fcom.makeUrl('shops', 'shopCollectionLangForm', [shop_id, scollection_id, langId, autoFillLangData]), '', function (t) {
            $(dvt).html(t);
        });
    };

    sellerCollectionProducts = function (scollection_id, shop_id) {
        fcom.ajax(fcom.makeUrl('shops', 'sellerCollectionProductLinkFrm', [scollection_id, shop_id]), '', function (t) {
            $(dvt).html(t);
            bindAutoComplete();
        });
    };

    setUpSellerCollectionProductLinks = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shops', 'setUpSellerCollectionProductLinks'), data, function (t) {
        });
    };

    collectionMediaForm = function (shop_id, scollection_id) {
        fcom.ajax(fcom.makeUrl('shops', 'shopCollectionMediaForm', [shop_id, scollection_id]), '', function (t) {
            $(dvt).html(t);
            shopCollectionImages(shop_id, scollection_id);
        });
    };

    shopCollectionImages = function (shop_id, scollection_id, lang_id) {
        fcom.ajax(fcom.makeUrl('shops', 'shopCollectionImages', [shop_id, scollection_id, lang_id]), '', function (t) {
            $('#imageListing').html(t);
        });
    };

    removeCollectionImage = function (shop_id, scollection_id, langId) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('shops', 'removeCollectionImage', [shop_id, scollection_id, langId]), '', function (t) {
            shopCollectionImages(shop_id, scollection_id, langId);
        });
    };

    deleteImage = function (fileId, shopId, imageType, langId, slide_screen) {
        var agree = confirm(langLbl.confirmDeleteImage);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Shops', 'removeShopImage', [fileId, shopId, imageType, langId, slide_screen]), '', function (t) {
            shopImages(shopId, imageType, slide_screen, langId);
        });
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
        var shopId = parseInt(obj.value);
        if (shopId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'shopId=' + shopId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Shops', 'changeStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
				/* setTimeout(function(){
					reloadList();
				}, 1000); */
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

    toggleCollectionStatus = function (e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var shopCollectionId = parseInt(obj.value);
        if (shopCollectionId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'scollection_id=' + shopCollectionId;
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Shops', 'changeCollectionStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
				/* setTimeout(function(){
					reloadList();
				}, 1000); */
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
        $.systemMessage.close();
    };

    toggleBulkCollectionStatues = function (status) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return false;
        }
        $("#frmCollectionsListing input[name='collection_status']").val(status);
        $("#frmCollectionsListing").submit();
    };

    bannerPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var minWidth = document.frmShopBanner.banner_min_width.value;
                var minHeight = document.frmShopBanner.banner_min_height.value;
                var options = {
                    aspectRatio: aspectRatio,
                    data: {
                        width: minWidth,
                        height: minHeight,
                    },
                    minCropBoxWidth: minWidth,
                    minCropBoxHeight: minHeight,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadShopImages', inputBtn);
            });
        }
    };

    logoPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var minWidth = document.frmShopLogo.logo_min_width.value;
                var minHeight = document.frmShopLogo.logo_min_height.value;
                if (minWidth == minHeight) {
                    var aspectRatio = 1 / 1
                } else {
                    var aspectRatio = 16 / 9;
                }
                var options = {
                    aspectRatio: aspectRatio,
                    data: {
                        width: minWidth,
                        height: minHeight,
                    },
                    minCropBoxWidth: minWidth,
                    minCropBoxHeight: minHeight,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadShopImages', inputBtn);
            });
        }
    };

    uploadShopImages = function (formData) {
        var frmName = formData.get("frmName");
        if ('frmShopLogo' == frmName) {
            var shopId = document.frmShopLogo.shop_id.value;
            var langId = document.frmShopLogo.lang_id.value;
            var fileType = document.frmShopLogo.file_type.value;
            var imageType = 'logo';
            var ratio_type = $('input[name="ratio_type"]:checked').val();
        } else {
            var shopId = document.frmShopBanner.shop_id.value;
            var langId = document.frmShopBanner.lang_id.value;
            var slideScreen = document.frmShopBanner.slide_screen.value;
            var fileType = document.frmShopBanner.file_type.value;
            var imageType = 'banner';
            var ratio_type = 0;
        }

        formData.append('slide_screen', slideScreen);
        formData.append('lang_id', langId);
        formData.append('file_type', fileType);
        formData.append('ratio_type', ratio_type);
        $.ajax({
            url: fcom.makeUrl('Shops', 'uploadShopImages', [shopId, langId]),
            type: 'post',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            complete: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            success: function (ans) {
                $('.text-danger').remove();
                $('#input-field' + fileType).html(ans.msg);
                if (ans.status == true) {
                    $('#input-field' + fileType).removeClass('text-danger');
                    $('#input-field' + fileType).addClass('text-success');
                    $('#form-upload').remove();
              
                    shopMediaForm(shopId);
                    fcom.displaySuccessMessage(ans.msg);
                   
                } else {
                    $('#input-field' + fileType).removeClass('text-success');
                    $('#input-field' + fileType).addClass('text-danger');
                    fcom.displayErrorMessage(ans.msg);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    collectionPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var options = {
                    aspectRatio: 16 / 9,
                    data: {
                        width: collectionMediaWidth,
                        height: collectionMediaHeight,
                    },
                    minCropBoxWidth: collectionMediaWidth,
                    minCropBoxHeight: collectionMediaHeight,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadCollectionImage', inputBtn);
            });
        }
    };

    uploadCollectionImage = function (formData) {
        var node = this;
        $('#form-upload').remove();

        var shop_id = document.frmCollectionMedia.shop_id.value;
        var scollection_id = document.frmCollectionMedia.scollection_id.value;
        var lang_id = document.frmCollectionMedia.lang_id.value;

        formData.append('scollection_id', scollection_id);
        formData.append('lang_id', lang_id);
        /* $val = $(node).val(); */
        $.ajax({
            url: fcom.makeUrl('Shops', 'uploadCollectionImage'),
            type: 'post',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $(node).val('Loading');
            },
            complete: function () {
                /* $(node).val($val); */
            },
            success: function (ans) {
                $.mbsmessage.close();
                $.systemMessage.close();
                var dv = '#mediaResponse';
                $('.text-danger').remove();
                if (ans.status == true) {
                    $.systemMessage(ans.msg, 'alert--success');
                    $(dv).removeClass('text-danger');
                    $(dv).addClass('text-success');
                    collectionMediaForm(shop_id, scollection_id);

                } else {
                    $.systemMessage(ans.msg, 'alert--danger');
                    $(dv).removeClass('text-success');
                    $(dv).addClass('text-danger');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    getUniqueSlugUrl = function (obj, str, recordId) {
        if (str == '') {
            return;
        }
        var data = {url_keyword: str, recordId: recordId}
        fcom.ajax(fcom.makeUrl('Shops', 'isShopRewriteUrlUnique'), data, function (t) {
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

function bindAutoComplete() {
    var shopId = document.frmLinks1.shop_id.value;    
    $("select[name='scp_selprod_id']").select2({
        closeOnSelect: true,
        dir: layoutDirection,
        allowClear: true,
    
        ajax: {
            url: fcom.makeUrl('Shops', 'autoCompleteProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                return {
                    keyword: params.term, 
                    page: params.page,
                    shopId: shopId
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
            return  (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        },
        templateSelection: function (result)
        {
            return  (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        }
    }).on('select2:selecting', function (e)
    {   
        var item = e.params.args.data;      
        $('#selprod-products' + item.id).remove();
        $('#selprod-products ul').append('<li id="selprod-products' + item.id + '"><i class=" icon ion-close-round"></i> ' + item.name + '[' + item.product_identifier + ']' + '<input type="hidden" name="product_ids[]" value="' + item.id + '" /></li>');
        
        setTimeout(function () {
           $('select[name=\'scp_selprod_id\']').val('').trigger('change.select2');
        }, 200);
    });
    
}
