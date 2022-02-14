$(document).ready(function () {
    searchListing(document.frmCustomProdReqSrch);
});
$(document).on('change', '.option-js', function () {
    /* $(document).delegate('.option-js','change',function(){ */
    var option_id = $(this).val();
    var preq_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
    var lang_id = $('.language-js').val();
    productImages(preq_id, option_id, lang_id);
});
$(document).on('change', '.language-js', function () {
    /* $(document).delegate('.language-js','change',function(){ */
    var lang_id = $(this).val();
    var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
    var option_id = $('.option-js').val();
    productImages(product_id, option_id, lang_id);
});
(function () {
    var currentPage = 1;
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCustomProdReqSrch;
        searchListing(frm, page);
    };

    reloadList = function () {
        searchListing(document.frmCustomProdReqSrchPaging, currentPage);
    };

    searchListing = function (form, page) {
        if (!page) {
            page = currentPage;
        }
        currentPage = page;
        var dv = $('#listing');
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        data = data + '&page=' + currentPage;
        fcom.ajax(fcom.makeUrl('CustomProducts', 'search'), data, function (t) {
            dv.html(t);
        });
    };

    goToCustomCatalogProductSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCustomProdReqSrchPaging;
        $(frm.page).val(page);
        searchListing(frm, page);
    };

    clearSearch = function () {
        document.frmCustomProdReqSrch.reset();
        searchListing(document.frmCustomProdReqSrch);
    };

    addProductForm = function (preqId) {
        $.facebox(function () {
            productForm(preqId);
        });
    }

    productForm = function (preqId) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('CustomProducts', 'productInitialSetupFrm', [preqId]), '', function (res) {
            /* fcom.updateFaceboxContent(t, 'faceboxWidth product-setup-width'); */
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").addClass('active');
            $("#tabs_001").html(res);
        });
    };

    setupProduct = function (frm) {
        var getFrm = $('#tabs_001 form')[0];
        var validator = $(getFrm).validation({errordisplay: 3});
        validator.validate();
        if (!validator.isValid())
            return;

        var data = fcom.frmData(getFrm);
        fcom.ajax(fcom.makeUrl('CustomProducts', 'setup'), data, function (t) {
            var res = $.parseJSON(t);
            if (res.preqId > 0 && res.status == 1) {
                if (res.isCustomFields) {
                    $('.tabs_005').removeClass('disabled');
                } else {
                    $('.tabs_005').addClass('disabled');
                }
            
                fcom.displaySuccessMessage(res.msg);
                customCatalogSpecifications(res.preqId);
                return;
            }
        });
        return;
    };

    translateData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='product_name[" + defaultLang + "]']").val();
        var oEdit = eval(oUtil.arrEditor[0]);
        var prodDesc = oEdit.getTextBody();

        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
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


    sellerProductForm = function (preqId) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'sellerProductForm', [preqId]), '', function (t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
    };

    setupSellerProduct = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupSellerProduct'), data, function (t) {
            reloadList();
            /* if (t.lang_id > 0) {
             productLangForm(t.preq_id, t.lang_id);
             return ;
             } */
            customCatalogSpecifications(t.preq_id);
            /* $(document).trigger('close.facebox'); */
            return;
        });
        return;
    };


    /*............CUSTOM CATALOG SPECIFICATION [............*/

    customCatalogSpecifications = function (preq_id) {
        var buttonClick = 0;
        fcom.ajax(fcom.makeUrl('CustomProducts', 'productAttributeAndSpecifications', [preq_id]), '', function (res) {
            /* fcom.updateFaceboxContent(t, 'faceboxWidth'); */
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").addClass('active');
            $("#tabs_002").html(res);
        });
    };

    prodSpecificationSection = function (langId, $key = - 1) {
        var preqId = $("input[name='preq_id']").val();
        var data = "langId=" + langId + "&key=" + $key;

        fcom.ajax(fcom.makeUrl('CustomProducts', 'catalogProdSpecForm', [preqId]), data, function (res) {
            $(".specifications-form-" + langId).html(res);
        });
    }

    prodSpecificationsByLangId = function (langId) {
        var preqId = $("input[name='preq_id']").val();
        var data = 'preq_id=' + preqId + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('CustomProducts', 'catalogSpecificationsByLangId'), data, function (res) {
            $(".specifications-list-" + langId).html(res);
        });
    }

    saveSpecification = function () {
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

        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setUpCustomCatalogSpecifications'), data, function (t) {
            prodSpecificationsByLangId(langId);
            prodSpecificationSection(langId);
        });
    }

    deleteProdSpec = function ($key, langId, showMedia = 0) {
        var agree = confirm("Do you want to delete record?");
        if (!agree) {
            return false;
        }
        var preqId = $("input[name='preq_id']").val();
        var data = "langId=" + langId + "&key=" + $key;
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'deleteCustomCatalogSpecification', [preqId]), data, function (t) {
            if (showMedia > 0) {
                prodSpecificationMediaSection(langId);
                prodSpecificationsMediaByLangId(langId)
            } else {
                prodSpecificationsByLangId(langId);
            }
        });
    }

    displayOtherLangProdSpec = function (obj, langId, defaultLangId = 0) {
        if ($('.collapse-js-' + langId).hasClass('show')) {
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
    }

    getCustomCatalogSpecificationForm = function (preqId, prodSpecId = 0) {
        buttonClick++;
        var SpecDiv = "#addSpecFields";
        fcom.ajax(fcom.makeUrl('CustomProducts', 'getSpecificationForm', [preqId, prodSpecId, buttonClick]), '', function (t) {
            $(SpecDiv).append(t);
        });
    };

    setupCustomCatalogSpecification = function (frm, preq_id, prodSpecId = 0) {
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupSpecification', [preq_id, prodSpecId]), data, function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.lang_id > 0) {
                productLangForm(t.preqId, t.lang_id);
                return;
            } else {
                customEanUpcForm(t.preqId);
            }
            fcom.scrollToTop(dv);
            return;
        });
    };

    removeSpecDiv = function (currentDiv) {
        $('#specification' + currentDiv).remove();
        buttonClick--;
    };

    /* ] */

    setUpCatalogProductAttributes = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setUpCatalogProductAttributes'), data, function (t) {
            productOptionsAndTag(t.preqId);
        });
    };

    productOptionsAndTag = function (preqId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customCatalogOptionsAndTag', [preqId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").addClass('active');
            $("#tabs_003").html(res);
        });
    }

    updateProductOption = function (preq_id, option_id, e) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'updateCustomCatalogOption'), 'preq_id=' + preq_id + '&option_id=' + option_id, function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                upcListing(preq_id);
                $.mbsmessage(ans.msg, true, 'alert--success');
            } else {
                var tagifyId = e.detail.tag.__tagifyId;
                $('[__tagifyid=' + tagifyId + ']').remove();
                $.systemMessage(ans.msg, 'alert--danger');
            }
        });
    }

    removeProductOption = function (preq_id, option_id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'removeCustomCatalogOption'), 'preq_id=' + preq_id + '&option_id=' + option_id, function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                upcListing(preq_id);
                $.mbsmessage(ans.msg, true, 'alert--success');
            }
        });
    };

    removeProductTag = function (preq_id, tag_id) {
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'removeCustomCatalogTag'), 'preq_id=' + preq_id + '&tag_id=' + tag_id, function (t) {
        });
    };



    /* customEanUpcForm = function (preq_id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customEanUpcForm', [preq_id]), '', function (res) {
            
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").addClass('active');
            $("#tabs_004").html(res);
        });
    }; */

    upcListing = function (preq_id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customEanUpcForm', [preq_id]), '', function (t) {
            $("#upc-listing").html(t);
        });
    };

    updateUpc = function (preqId, optionValueId) {
        var code = $("input[name='code" + optionValueId + "']").val();
        var data = {'code': code, 'optionValueId': optionValueId};
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupEanUpcCode', [preqId]), data, function (t) {
        });
    };


    validateEanUpcCode = function (upccode) {
        var data = {code: upccode};
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'validateUpcCode'), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            return;
        });
    };

    /* setupEanUpcCode = function (preq_id, frm) {
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupEanUpcCode', [preq_id]), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.preq_id > 0) {
                
                customCatalogCustomFields(t.preq_id);
                return;
            }
            return;
        });
    }; */

    productShipping = function (preqId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('CustomProducts', 'CustomCatalogShippingFrm', [preqId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").addClass('active');
            $("#tabs_004").html(res);
            /* addShippingTab(preqId);*/
        });
    }

    setUpProductShipping = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setUpCustomCatalogShipping'), data, function (t) {
            if (t.isUseCustomFields > 0) {
                productCatCustomFields(t.preqId);
            } else {
                customCatalogProductImages(t.preqId);
            }

        });
    }


    productLangForm = function (preq_id, lang_id, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        /* $.facebox(function() { */
        fcom.ajax(fcom.makeUrl('CustomProducts', 'langForm', [preq_id, lang_id, autoFillLangData]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").addClass('active');
            $("#tabs_003").html(t);
            /* fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(lang_id); */
            var frm = $('#facebox form')[0];
            var validator = $(frm).validation({errordisplay: 3});
            $(frm).submit(function (e) {
                e.preventDefault();

                validator.validate();
                if (!validator.isValid())
                    return;
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'langSetup'), data, function (t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.lang_id > 0) {
                        productLangForm(t.preq_id, t.lang_id);
                        return;
                    }
                    if (t.productOptions != null && t.productOptions != '') {
                        customEanUpcForm(t.preq_id);
                        return;
                    }
                    updateStatusForm(t.preq_id);
                    return;
                });
            });
        });
        /* }); */
    };


    productImagesForm = function (preq_id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'imagesForm', [preq_id]), '', function (t) {
            productImages(preq_id);
            $.facebox(t, 'faceboxWidth');
        });
    };

    productImages = function (preq_id, option_id, lang_id) {
        if (typeof option_id == 'undefined') {
            option_id = 0;
        }
        if (typeof lang_id == 'undefined') {
            lang_id = 0;
        }

        fcom.ajax(fcom.makeUrl('CustomProducts', 'images', [preq_id, option_id, lang_id]), '', function (t) {
            $('#imageupload_div').html(t);
            fcom.resetFaceboxHeight();
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
                    aspectRatio: minWidth / minHeight,
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
        var preq_id = document.imageFrm.preq_id.value;
        var option_id = document.imageFrm.option_id.value;
        var lang_id = document.imageFrm.lang_id.value;
        formData.append('preq_id', preq_id);
        formData.append('option_id', option_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl('CustomProducts', 'setupCustomCatalogProductImages'),
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
                $.mbsmessage(ans.msg, true, 'alert--success');
                productImages($('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option').val(), $('.language').val());
                $('#prod_image').val('');
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }


    submitImageUploadForm = function ( ) {
        var data = new FormData(  );
        $inputs = $('#frmCustomCatalogProductImage input[type=text],#frmCustomCatalogProductImage select,#frmCustomCatalogProductImage input[type=hidden]');
        $inputs.each(function () {
            data.append(this.name, $(this).val());
        });
        var preq_id = $('#frmCustomCatalogProductImage input[name="preq_id"]').val();
        $.each($('#prod_image')[0].files, function (i, file) {
            $('#imageupload_div').html(fcom.getLoader());
            data.append('prod_image', file);
            $.ajax({
                url: fcom.makeUrl('CustomProducts', 'uploadProductImages'),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (t) {
                    try {
                        var ans = $.parseJSON(t);
                        productImages($('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val());
                        if (ans.status == 1) {
                            fcom.displaySuccessMessage(ans.msg);
                        } else {
                            fcom.displayErrorMessage(ans.msg);
                        }
                    } catch (exc) {
                        productImages($('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val());
                        fcom.displayErrorMessage(t);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };

    deleteImage = function (preq_id, image_id, isSizeChart = 0) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }

        fcom.ajax(fcom.makeUrl('CustomProducts', 'deleteImage', [preq_id, image_id, isSizeChart]), '', function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 0) {
                fcom.displayErrorMessage(ans.msg);
                return;
            } else {
                fcom.displaySuccessMessage(ans.msg);
            }
            productImages(preq_id, $('.option-js').val(), $('.language-js').val());
        });
    }

    updateStatusForm = function (id) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('CustomProducts', 'updateStatusForm', [id]), '', function (res) {
            /* $.facebox(t, 'faceboxWidth'); */
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_007").show();
            $("a[rel='tabs_007']").addClass('active');
            $("#tabs_007").html(res);
        });
    };

    updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'updateStatus'), data, function (t) {
            window.location.href = fcom.makeUrl('CustomProducts');
        });
        return;
    };

    showHideCommentBox = function (val) {
        if (val == 2) {
            $('#div_comments_box').removeClass('hide');
        } else {
            $('#div_comments_box').addClass('hide');
        }
    };

    productCatCustomFields = function (id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customFieldsForm', [id]), '', function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_005").show();
            $("a[rel='tabs_005']").addClass('active');
            $("#tabs_005").html(res);
            /* $.facebox(t, 'faceboxWidth'); */
        });
    };

    setupCustomFields = function (frm) {
        if (!$(frm).validate()) {
            return
        };
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupCustomFields'), data, function (t) {
            var preq_id = $("input[name='preq_id']").val();
            customCatalogProductImages(preq_id);
        });
    };

    customCatalogProductImages = function (preqId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customCatalogProductImages', [preqId]), (data), function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav  > li > a").removeClass('active');
            $("#tabs_006").show();
            $("a[rel='tabs_006']").addClass('active');
            $("#tabs_006").html(t);
            productImages(preqId);
            var langId = $('input[name="langId"]').val();
            prodSpecificationMediaSection(langId);
            prodSpecificationsMediaByLangId(langId);
        });
    };

    popupSizeChart = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Products', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
                var file = inputBtn.files[0];
                var minWidth = document.imageFrm.min_width.value;
                var minHeight = document.imageFrm.min_height.value;
                var options = {
                    aspectRatio: 1 / 1,
                    data: {
                        width: minWidth,
                        height: minHeight,
                    },
                    minCropBoxWidth: minWidth,
                    minCropBoxHeight: minHeight,
                    toggleDragModeOnDblclick: false,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadSizeChart', inputBtn);
            });
        }
    };

    uploadSizeChart = function (formData) {
        var preq_id = document.imageFrm.preq_id.value;
        var option_id = document.imageFrm.option_id.value;
        var lang_id = document.imageFrm.lang_id.value;
        formData.append('preq_id', preq_id);
        formData.append('option_id', option_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl('CustomProducts', 'uploadSizeChart'),
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
                    productImagesForm(preq_id);
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }


    /* [ product specification media */
        prodSpecificationMediaSection = function (langId, key = - 1) {
            var preq_id = $("input[name='preq_id']").val();
            var data = "langId=" + langId + "&key=" + key;
            fcom.ajax(fcom.makeUrl('CustomProducts', 'catalogProdSpecMediaForm', [preq_id]), data, function (res) {
                $(".specifications-form-" + langId).html(res);
            });
        }
        prodSpecificationsMediaByLangId = function (langId) {
            var productId = $("input[name='preq_id']").val();
            var data = 'preq_id=' + productId + '&langId=' + langId;
            fcom.ajax(fcom.makeUrl('CustomProducts', 'catalogSpecificationsMediaByLangId'), data, function (res) {
                $(".specifications-list-" + langId).html(res);
            });
        }
        displayOtherLangProdSpecMedia = function (obj, langId) {
            if ($('.collapse-js-' + langId).hasClass('show')) {
                return false;
            }
            prodSpecificationMediaSection(langId);
            prodSpecificationsMediaByLangId(langId);
        }
    
        saveSpecificationWithFile = function (langId) {
            var langId = $("input[name='langId").val();
            var prodspec_name = $("input[name='prodspec_name[" + langId + "]").val();
            var prodspecId = $("input[name='prodSpecId").val();
            var fileUploaded = $("input[name='fileUploaded").val();
            if (prodspec_name == '') {
                $(".erlist_specification_" + langId).show();
                return false;
            }
    
            if (parseInt(prodspecId) < 0 && 1 > fileUploaded) {
                $(".erlist_specification_media" + langId).show();
                return false;
            }
    
            $(".erlist_specification_" + langId).hide();
            var frm = $('form.attr-spec-frm--js')[0];
            var data = fcom.frmData(frm);
    
            fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setUpCustomCatalogSpecifications'), data, function (t) {
                prodSpecificationsMediaByLangId(langId);
                prodSpecificationMediaSection(langId);
            });
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
                    fcom.ajax(fcom.makeUrl('CustomProducts', 'imgCropper'), '', function (t) {
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
            var preqId = $("input[name='preq_id']").val();
            var prodspec_group = $("input[name='prodspec_group']").val();
            var key = $("input[name='key']").val();
            var prod_spec_file_index = $("input[name='prod_spec_file_index']").val();
            if (langId > 0) {
                formData.append('langId', langId);
            }
            formData.append('prodspec_group', prodspec_group);
            formData.append('key', key);
            formData.append('preq_id', preqId);
            formData.append('is_image', is_image);
            formData.append('prod_spec_file_index', prod_spec_file_index);
            langId = formData.get('langId');
    
            $.ajax({
                url: fcom.makeUrl('CustomProducts', 'uploadCatalogProductSpecificationMediaData'),
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
                success: function (t) {
                    var ans = jQuery.parseJSON(t);
                    if (ans.status == 1) {
                        $('input[name="fileUploaded"]').val(1);
                        $(".errorlist").hide();
                        if (ans.uploadedFileData != '' && ans.uploadedFileData != undefined) {
                            $('#filePreviewDiv_' + langId).html(ans.uploadedFileData);
                        }
                        $.mbsmessage(ans.msg, true, 'alert--success');
                    } else {
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                    }
                    $(document).trigger('close.facebox');
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
        /* ] */

})();

