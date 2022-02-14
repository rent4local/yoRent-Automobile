$(document).ready(function () {
    searchListing(document.frmCustomProdReqSrch);
});
$(document).on('change', '.option-js', function () {
    /* $(document).delegate('.option-js','change',function(){ */
    var option_id = $(this).val();
    var preq_id = $('#imageFrm input[name=preq_id]').val();
    var lang_id = $('.language-js').val();
    productImages(preq_id, option_id, lang_id);
});
$(document).on('change', '.language-js', function () {
    /* $(document).delegate('.language-js','change',function(){ */
    var lang_id = $(this).val();
    var preq_id = $('#imageFrm input[name=preq_id]').val();
    var option_id = $('.option-js').val();
    productImages(preq_id, option_id, lang_id);
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
        fcom.ajax(fcom.makeUrl('CustomProducts', 'form', [preqId]), '', function (t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth product-setup-width');
        });
    };

    setupProduct = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setup'), data, function (t) {
            reloadList();
            if (t.preq_id > 0) {
               /*  sellerProductForm(t.preq_id); */
                customCatalogSpecifications(t.preq_id);
                return;
            }
            $(document).trigger('close.facebox');
            return;
        });
        return;
    };

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
        fcom.ajax(fcom.makeUrl('CustomProducts', 'specificationForm', [preq_id]), '', function (t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
        });
    };

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


    customEanUpcForm = function (preq_id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customEanUpcForm', [preq_id]), '', function (t) {
            fcom.updateFaceboxContent(t, 'faceboxWidth');
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

    setupEanUpcCode = function (preq_id, frm) {
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupEanUpcCode', [preq_id]), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.preq_id > 0) {
                updateStatusForm(t.preq_id);
                return;
            }
            return;
        });
    };

    productLangForm = function (preq_id, lang_id, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        /* $.facebox(function() { */
        fcom.ajax(fcom.makeUrl('CustomProducts', 'langForm', [preq_id, lang_id, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(lang_id);
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
                $('#cropperBox-js').html(t);
                $("#mediaForm-js").css("display", "none");
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
            url: fcom.makeUrl('CustomProducts', 'uploadProductImages'),
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

    submitImageUploadForm = function ( ) {
        var data = new FormData(  );
        $inputs = $('#imageFrm input[type=text],#imageFrm select,#imageFrm input[type=hidden]');
        $inputs.each(function () {
            data.append(this.name, $(this).val());
        });
        var preq_id = $('#imageFrm input[name="preq_id"]').val();
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
                        productImages($('#imageFrm input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val());
                        if (ans.status == 1) {
                            fcom.displaySuccessMessage(ans.msg);
                        } else {
                            fcom.displayErrorMessage(ans.msg);
                        }
                    } catch (exc) {
                        productImages($('#imageFrm input[name=preq_id]').val(), $('.option-js').val(), $('.language-js').val());
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
        fcom.ajax(fcom.makeUrl('CustomProducts', 'updateStatusForm', [id]), '', function (t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

    updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'updateStatus'), data, function (t) {
            reloadList();
            $(document).trigger('close.facebox');
            return;
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

    updateStatusForm = function (id) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('CustomProducts', 'updateStatusForm', [id]), '', function (t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

    updateStatus = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'updateStatus'), data, function (t) {
            reloadList();
            $(document).trigger('close.facebox');
            return;
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

    customCatalogCustomFields = function (id) {
        fcom.ajax(fcom.makeUrl('CustomProducts', 'customFieldsForm', [id]), '', function (t) {
            $.facebox(t, 'faceboxWidth');
        });
    };

    setupCustomFields = function (frm) {
        if (!$(frm).validate()) {
            return
        }
        ;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('CustomProducts', 'setupCustomFields'), data, function (t) {
            var preqId = $("input[name='preq_id']").val();
            customCatalogCustomFields(preqId);
        });
    };

    popupSizeChart = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Products', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $("#mediaForm-js").css("display", "none");
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
})();
