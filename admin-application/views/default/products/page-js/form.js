(function () {

    goToProduct = function () {
        window.location.href = fcom.makeUrl('Products');
    }

    goToProductCategory = function () {
        window.location.href = fcom.makeUrl('productCategories');
    }

    displayProdInitialTab = function () {
        $(".tabs_panel").hide();
        $(".tabs_nav  > li > a").removeClass('active');
        $("#tabs_001").show();
        $("a[rel='tabs_001']").addClass('active');
    }

    productInitialSetUpFrm = function (productId, prodCatId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Products', 'productInitialSetUpFrm', [productId, prodCatId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").addClass('active');
            $("#tabs_001").html(res);
            fcom.resetEditorWidth();
            var editors = oUtil.arrEditor;
            for (x in editors) {
                var oEdit1 = eval(editors[x]);
                var layout = langLbl['language' + (parseInt(x) + parseInt(1))];
                $('#idContent' + editors[x]).contents().find("body").css('direction', layout);
                $('#idArea' + oEdit1.oName + ' td[dir="ltr"]').attr('dir', layout);
            }
        });
    };

    setUpProduct = function (frm) {

        var getFrm = $('#tabs_001 form')[0];
        var validator = $(getFrm).validation({errordisplay: 3});
        validator.validate();
        if (!validator.isValid())
            return;
        
        var data = fcom.frmData(getFrm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProduct'), data, function (t) {
            if (t.isCustomFields) {
                $('.tabs_006').removeClass('disabled');
                } else {
                 $('.tabs_006').addClass('disabled');
            }
            productAttributeAndSpecificationsFrm(t.productId);
        });
    };

    productAttributeAndSpecificationsFrm = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Products', 'productAttributeAndSpecificationsFrm', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").addClass('active');
            $("#tabs_002").html(res);
        });
    }

    setUpProductAttributes = function (frm) {
        if (!$(frm).validate()) {
            return false;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProductAttributes'), data, function (t) {
            productOptionsAndTag(t.productId);
        });
    };

    prodSpecificationSection = function (langId, prodSpecId = 0) {
        var productId = $("input[name='product_id']").val();
        var data = "langId=" + langId + "&prodSpecId=" + prodSpecId;
        fcom.ajax(fcom.makeUrl('Products', 'prodSpecificationFrm', [productId]), data, function (res) {
            $(".specifications-form-" + langId).html(res);
        });
    }

    prodSpecificationsByLangId = function (langId) {
        var productId = $("input[name='product_id']").val();
        var data = 'product_id=' + productId + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('Products', 'prodSpecificationsByLangId'), data, function (res) {
            $(".specifications-list-" + langId).html(res);
        });
    }

    saveSpecification = function (langId, prodSpecId) {
        /*var productId = $("input[name='product_id']").val();
         var prodspec_name = $("input[name='prodspec_name[" + langId + "]']").val();
         var prodspec_value = $("input[name='prodspec_value[" + langId + "]']").val();
         var prodspec_group = $("input[name='prodspec_group[" + langId + "]']").val();
         if (prodspec_name.trim() == '' || prodspec_value.trim() == '') {
         $(".erlist_specification_" + langId).show();
         return false;
         }
         $(".erlist_specification").hide();
         var data = 'product_id=' + productId + '&langId=' + langId + '&prodSpecId=' + prodSpecId + '&prodspec_name=' + prodspec_name + '&prodspec_value=' + prodspec_value + '&prodspec_group=' + prodspec_group;
         fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProductSpecifications'), data, function (t) {
         prodSpecificationsByLangId(langId);
         prodSpecificationSection(langId);
         }); */

        var langId = $("input[name='langId").val();
        var prodspec_name = $("input[name='prodspec_name[" + langId + "]").val();
        var prodspec_value = $("input[name='prodspec_value[" + langId + "]").val();
        if (prodspec_name == '' || prodspec_value == '') {
            $(".erlist_specification_" + langId).show();
            return false;
        }

        $(".erlist_specification_" + langId).hide();
        var frm = $('form.attr-spec-frm--js')[0];
        var data = fcom.frmData(frm);

        fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProductSpecifications'), data, function (t) {
            prodSpecificationsByLangId(langId);
            prodSpecificationSection(langId);
        });

    }

    deleteProdSpec = function (prodSpecId, langId, showMediaTab = 0) {
        var agree = confirm("Do you want to delete record?");
        if (!agree) {
            return false;
        }
        var data = 'prodSpecId=' + prodSpecId;
        fcom.updateWithAjax(fcom.makeUrl('Products', 'deleteProdSpec'), data, function (t) {
            /* prodSpecificationsByLangId(langId); */
            if (showMediaTab == 1) {
                prodSpecificationsMediaByLangId(langId);
            } else {
                prodSpecificationsByLangId(langId);
            }
        });
    }

    displayOtherLangProdSpec = function (obj, langId, defaultLangId) {
        if ($(obj).hasClass('active')) {
            return false;
        }
        if ($('input[name="autocomplete_lang_data"]').prop('checked') == false) {
            return;
        }

        var prodspec_name = $('input[name="prodspec_name[' + defaultLangId + ']"]').val();
        var prodspec_value = $('input[name="prodspec_value[' + defaultLangId + ']"]').val();
        if (prodspec_name == "" && prodspec_value == "") {
            return;
        }

        var data = 'prodspec_name=' + prodspec_name + '&prodspec_value=' + prodspec_value + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('Products', 'getSpecificationTranslatedData'), data, function (t) {
            var res = $.parseJSON(t);
            if (res.status == 1) {
                $('input[name="prodspec_name[' + langId + ']"]').val(res.prodspec_name);
                $('input[name="prodspec_value[' + langId + ']"]').val(res.prodspec_value);
            }
        });

        /* prodSpecificationSection(langId);
         prodSpecificationsByLangId(langId); */
    }

    productOptionsAndTag = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Products', 'productOptionsAndTag', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").addClass('active');
            $("#tabs_003").html(res);
        });
    }

    upcListing = function (product_id) {
        fcom.ajax(fcom.makeUrl('products', 'upcListing', [product_id]), '', function (t) {
            $("#upc-listing").html(t);
        });
    };

    updateUpc = function (productId, optionValueId) {
        var code = $("input[name='code" + optionValueId + "']").val();
        var data = {'code': code, 'optionValueId': optionValueId};
        fcom.updateWithAjax(fcom.makeUrl('products', 'updateUpc', [productId]), data, function (t) {
        });
    };

    productShipping = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Products', 'productShippingFrm', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").addClass('active');
            $("#tabs_004").html(res);

        });
    }

    addShippingTab = function (productId) {
        var ShipDiv = "#tab_shipping";
        fcom.ajax(fcom.makeUrl('products', 'getShippingTab'), 'product_id=' + productId, function (t) {
            $(ShipDiv).html(t);
        });
    };

    setUpProductShipping = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProductShipping'), data, function (t) {
          
            if (t.isUseCustomFields > 0) {
                productCatCustomFields(t.productId);
            } else {
                productMedia(t.productId);
            }
        });
    }

    shippingautocomplete = function (shipping_row) {
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'countries_autocomplete'),
                    data: {keyword: request['term'], fIsAjax: 1, includeEverywhere: true},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][country_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'shippingCompanyAutocomplete'),
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'shippingMethodDurationAutocomplete'),
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'] + '[' + item['duraion'] + ']',
                                value: item['name'] + '[' + item['duraion'] + ']',
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });
    };

    productMedia = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Products', 'imagesForm', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_005").show();
            $("a[rel='tabs_005']").addClass('active');
            $("#tabs_005").html(res);
            productImages(productId);
        });
    }

    productImages = function (product_id, option_id = 0, lang_id = 0) {
        fcom.ajax(fcom.makeUrl('Products', 'images', [product_id, option_id, lang_id]), '', function (t) {
            $('#imageupload_div').html(t);
        });
    };

    popupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Products', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
                var file = inputBtn.files[0];
                var minWidth = document.imageFrm.min_width.value;
                var minHeight = document.imageFrm.min_height.value;
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
                return cropImage(file, options, 'uploadImages', inputBtn);
            });
        }
    };

    uploadImages = function (formData) {
        var product_id = document.imageFrm.product_id.value;
        var option_id = document.imageFrm.option_id.value;
        var lang_id = document.imageFrm.lang_id.value;
        formData.append('product_id', product_id);
        formData.append('option_id', option_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl('Products', 'uploadProductImages'),
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
                if (ans.status == 1) {
                    fcom.displaySuccessMessage(ans.msg);
                    productImages(product_id, option_id, lang_id);
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    /*submitImageUploadForm = function ( ){
     var data = new FormData(  );
     $inputs = $('#imageFrm input[type=text],#imageFrm select,#imageFrm input[type=hidden]');
     $inputs.each(function() { data.append( this.name,$(this).val());});
     var product_id = $('#imageFrm input[name="product_id"]').val();
     $.each( $('#prod_image')[0].files, function(i, file) {
     $('#imageupload_div').html(fcom.getLoader());
     data.append('prod_image', file);
     $.ajax({
     url : fcom.makeUrl('Products', 'uploadProductImages'),
     type: "POST",
     data : data,
     processData: false,
     contentType: false,
     success: function(t){
     try{
     var ans = $.parseJSON(t);
     productImages( $('#imageFrm input[name=product_id]').val(), $('.option-js').val(), $('.language-js').val() );
     if( ans.status == 1 ){
     $.systemMessage(ans.msg, 'alert--success');
     }else {
     $.systemMessage(ans.msg, 'alert--danger');
     }
     }
     catch(exc){
     productImages( $('#imageFrm input[name=product_id]').val(), $('.option-js').val(), $('.language-js').val() );
     $.systemMessage(t, 'alert--danger');
     }
     },
     error: function(jqXHR, textStatus, errorThrown){
     alert("Error Occured.");
     }
     });
     });
     };*/

    deleteImage = function (product_id, image_id, isSizeChart = 0) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('Products', 'deleteImage', [product_id, image_id, isSizeChart]), '', function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 0) {
                fcom.displayErrorMessage(ans.msg);
                return;
            } else {
                fcom.displaySuccessMessage(ans.msg);
            }
            productImages(product_id, $('.option-js').val(), $('.language-js').val());
        });
    };

    translateData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='product_name[" + defaultLang + "]']").val();
        var oEdit = eval(oUtil.arrEditor[0]);
        var prodDesc = oEdit.getTextBody();

        var alreadyOpen = $('#collapse_' + toLangId).hasClass('active');
        if (autoTranslate == 0 || prodName == "" || alreadyOpen == true) {
            return false;
        }
        var data = "product_name=" + prodName + '&product_description=' + prodDesc + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('Products', 'translatedProductData'), data, function (t) {
            if (t.status == 1) {
                $("input[name='product_name[" + toLangId + "]']").val(t.productName);
                var oEdit1 = eval(oUtil.arrEditor[toLangId - 1]);
                oEdit1.putHTML(t.productDesc);
                var layout = langLbl['language' + toLangId];
                $('#idContent' + oUtil.arrEditor[toLangId - 1]).contents().find("body").css('direction', layout);
                $('#idArea' + oUtil.arrEditor[toLangId - 1] + ' td[dir="ltr"]').attr('dir', layout);
            }
        });
    }

    prodSpecificationMediaSection = function (langId, prodSpecId = 0) {
        var productId = $('input[name=product_id]').val();
        var data = "langId=" + langId + "&prodSpecId=" + prodSpecId;
        fcom.ajax(fcom.makeUrl('Products', 'prodSpecificationMediaFrm', [productId]), data, function (res) {
            $(".specifications-form-" + langId).html(res);
        });
        prodSpecificationsMediaByLangId(langId);
    }

    prodSpecificationsMediaByLangId = function (langId) {
        var productId = $("input[name='product_id']").val();
        var data = 'product_id=' + productId + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('Products', 'prodSpecificationsMediaByLangId'), data, function (res) {
            $(".specifications-list-" + langId).html(res);
        });
    }

    saveSpecificationWithFile = function () {
        var langId = $("input[name='langId").val();
        var prodspec_name = $("input[name='prodspec_name[" + langId + "]").val();
        var prodspecId = $("input[name='prodSpecId").val();
        var fileUploaded = $("input[name='fileUploaded").val();
        if (prodspec_name == '') {
            $(".erlist_specification_" + langId).show();
            return false;
        }

        if (parseInt(prodspecId) <= 1 && 1 > fileUploaded) {
            $(".erlist_specification_media" + langId).show();
            return false;
        }

        $(".erlist_specification_" + langId).hide();
        $(".erlist_specification_media" + langId).hide();
        var frm = $('form.attr-spec-frm--js')[0];
        var data = fcom.frmData(frm);

        fcom.updateWithAjax(fcom.makeUrl('Products', 'setUpProductSpecifications'), data, function (t) {
            prodSpecificationsMediaByLangId(langId);
            prodSpecificationMediaSection(langId);
        });
    }

    displayOtherLangProdSpecMedia = function (obj, langId) {
        if ($(obj).hasClass('active')) {
            return false;
        }
        prodSpecificationMediaSection(langId);
        
    }

    popupSpecificationFile = function (langId) {
        var frm = "#spectifction_media_frm";
        var formData = new FormData($(frm)[0]);
        formData.delete('langId');
        var $i = $(frm + '  #prodspec_files_' + langId);
        var inputBtn = $i[0];
        if (inputBtn.files && inputBtn.files[0]) {
            var file = inputBtn.files[0];
            var fileName = file["name"];
            var ext = fileName.split('.').pop().toLowerCase();
            var imageTypes = ['gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff'];
            if ($.inArray(ext, imageTypes) != -1) {
                fcom.ajax(fcom.makeUrl('Products', 'imgCropper'), '', function (t) {
                    $.facebox(t, 'faceboxWidth');
                    var minWidth = 800;
                    var minHeight = 800;
                    var options = {
                        aspectRatio: 1,
                        minCropBoxWidth: minWidth,
                        minCropBoxHeight: minHeight,
                        toggleDragModeOnDblclick: false,
                    };
                    $(inputBtn).val('');
                    return cropImage(file, options, 'uploadSpecificationFile', inputBtn, langId);
                });
            } else {
                uploadSpecificationFile(formData, 0, langId);
            }
        }
    };

    uploadSpecificationFile = function (formData, is_image = 1, langId = 0) {

        var productId = $("input[name='product_id']").val();
        var prodspec_group = $("input[name='prodspec_group']").val();
        var prodspec_id = $("input[name='prodSpecId']").val();
        if (langId > 0) {
            formData.append('langId', langId);
        }
        formData.append('prodspec_group', prodspec_group);
        formData.append('prodspec_id', prodspec_id);
        formData.append('prodspec_product_id', productId);
        formData.append('is_image', is_image);
        langId = formData.get('langId');

        $.ajax({
            url: fcom.makeUrl('Products', 'uploadProductSpecificationMediaData'),
            type: 'post',
           
            data: formData,
            mimeType: "multipart/form-data",
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loader-js').html(fcom.getLoader());
                fcom.displayProcessing();
            },
            complete: function () {
                $('#loader-js').html(fcom.getLoader());               
            },
            success: function (ans) {
                var ans = jQuery.parseJSON(ans);
                if (ans.status == 1) {
                    $('input[name="fileUploaded"]').val(1);
                    $(".errorlist").hide();
                    if (ans.uploadedFileData != '' && ans.uploadedFileData != undefined) {
                        $('#filePreviewDiv_' + langId).html(ans.uploadedFileData);
                    }
                    fcom.displaySuccessMessage(ans.msg);
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
})();

$(document).on('change', '.option-js', function () {
    var option_id = $(this).val();
    var product_id = $('#imageFrm input[name=product_id]').val();
    var lang_id = $('.language-js').val();
    productImages(product_id, option_id, lang_id);
});
$(document).on('change', '.language-js', function () {
    var lang_id = $(this).val();
    var product_id = $('#imageFrm input[name=product_id]').val();
    var option_id = $('.option-js').val();
    productImages(product_id, option_id, lang_id);
});

$(document).on('click', '.tabs_001', function () {
    var productId = $("input[name='product_id']").val();
    productInitialSetUpFrm(productId);
});

$(document).on('click', '.tabs_002', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productAttributeAndSpecificationsFrm(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_003', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productOptionsAndTag(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_004', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productShipping(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_005', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productMedia(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_006', function (e) {
    if ($(this).hasClass('disabled')) {
        e.preventDefault();
        e.stopPropagation();
    } else {
        var productId = $("input[name='product_id']").val();
        if (productId > 0) {
            productCatCustomFields(productId);
        } else {
            displayProdInitialTab();
        }
    }
});


productCatCustomFields = function (productId) {
    var data = 'productId=' + productId;
    fcom.ajax(fcom.makeUrl('Products', 'prodCatCustomFieldsForm'), data, function (res) {
        $(".tabs_panel").html('');
        $(".tabs_panel").hide();
        $(".tabs_nav  > li > a").removeClass('active');
        $("#tabs_006").show();
        $("a[rel='tabs_006']").addClass('active');
        $("#tabs_006").html(res);
    });
}

setupAttrData = function (frm) {
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.updateWithAjax(fcom.makeUrl('Products', 'setupCustomFieldsData'), data, function (t) {
        var productId = $("input[name='product_id']").val();
        /* productCatCustomFields(productId); */
        productMedia(productId);
    });
};

/* [ UPLOAD SIZE CHART IMAGE */
popupSizeChartImage = function (inputBtn) {
    if (inputBtn.files && inputBtn.files[0]) {
        fcom.ajax(fcom.makeUrl('Products', 'imgCropper'), '', function (t) {
            $.facebox(t, 'faceboxWidth');
            var file = inputBtn.files[0];
            var minWidth = document.imageFrm.min_width.value;
            var minHeight = document.imageFrm.min_height.value;
            if (minWidth == minHeight) {
                var aspectRatio = 1 / 1
            } else {
                var aspectRatio = aspectRatio;
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
            };
            $(inputBtn).val('');
            return cropImage(file, options, 'uploadSizeChartImages', inputBtn);
        });
    }
};

uploadSizeChartImages = function (formData) {
    var product_id = document.imageFrm.product_id.value;
    /* var option_id = document.imageFrm.option_id.value; */
    var lang_id = document.imageFrm.lang_id.value;
    formData.append('product_id', product_id);
    /* formData.append('option_id', option_id); */
    formData.append('lang_id', lang_id);
    $.ajax({
        url: fcom.makeUrl('Products', 'uploadSizeChartImages'),
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
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                productImages(product_id, 0, lang_id);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
            $(document).trigger('close.facebox');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}
/* ] */
