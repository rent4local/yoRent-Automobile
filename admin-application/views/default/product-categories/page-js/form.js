(function () {
    categoryForm = function (prodCatId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('productCategories', 'form', [prodCatId]), data, function (res) {
            $(dv).html(res);
            if (prodCatId > 0) {
                categoryImages(prodCatId, 'icon', 1);
                categoryImages(prodCatId, 'banner', 1);
            }
        });
    }

    setupCategory = function () {
        var frm = $('#frmProdCategory');
        var validator = $(frm).validation({errordisplay: 3});
        if (validator.validate() == false) {
            return false;
        }
        if (!$(frm).validate()) {
            return false;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'setup'), data, function (t) {
            if (t.status == 1) {
                fcom.displaySuccessMessage(t.msg);
                if (t.isCustoFieldTab) {
                    $("input[name='prodcat_id']").val(t.categoryId);
                    window.history.pushState('', '', fcom.makeUrl('ProductCategories', 'form', [t.categoryId]));
                    categoryCustomFieldsForm(t.categoryId);
                } else {
                    window.location.href = fcom.makeUrl('ProductCategories', 'form', [t.categoryId]);
                }
                /* window.location.href = fcom.makeUrl('ProductCategories', 'form/' + t.categoryId); */
            }
        });
    };

    discardForm = function () {
        /* searchProductCategories();
         getTotalBlock(); */
        window.location.href = fcom.makeUrl('ProductCategories');
    }

    categoryImages = function (prodCatId, imageType, slide_screen, lang_id) {
        fcom.ajax(fcom.makeUrl('ProductCategories', 'images', [prodCatId, imageType, lang_id, slide_screen]), '', function (t) {
            if (imageType == 'icon') {
                $('#icon-image-listing').html(t);
                var prodCatId = $("[name='prodcat_id']").val();
                if (prodCatId == 0) {
                    var iconImageId = $("#icon-image-listing li").attr('id');
                    var selectedLangId = $(".icon-language-js").val();
                    $("[name='cat_icon_image_id[" + selectedLangId + "]']").val(iconImageId);
                }
            } else if (imageType == 'banner') {
                $('#banner-image-listing').html(t);
                var bannerImageId = $("#banner-image-listing li").attr('id');
                var selectedLangId = $(".banner-language-js").val();
                var sectionClass = ".screen-type-banner--js";
                if (imageType == "icon") {
                    var sectionClass = ".screen-type-icon--js";
                }
                var screen = $(sectionClass + " .prefDimensions-js").val();
                $("[name='cat_banner_image_id[" + selectedLangId + "_" + screen + "]']").val(bannerImageId);
            }
        });
    };

    deleteImage = function (fileId, prodcatId, imageType, langId, slide_screen) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('productCategories', 'removeImage', [fileId, prodcatId, imageType, langId, slide_screen]), '', function (t) {
           
            if (imageType == 'icon') {
                $("#icon-image-listing").html('');
                $("[name='cat_icon_image_id[" + langId + "]']").val('');
            } else if (imageType == 'banner') {
                $("#banner-image-listing").html('');
                $("[name='cat_banner_image_id[" + langId + "_" + slide_screen + "]']").val('');
            }
        });
    };

    translateData = function (item) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var defaultLang = $(item).attr('defaultLang');
        var catName = $("input[name='prodcat_name[" + defaultLang + "]']").val();
        var toLangId = $(item).attr('language');
        var alreadyOpen = $('#collapse_' + toLangId).hasClass('active');
        if (autoTranslate == 0 || catName == "" || alreadyOpen == true) {
            return false;
        }
        var data = "catName=" + catName + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'translatedCategoryData'), data, function (t) {
            if (t.status == 1) {
                $("input[name='prodcat_name[" + toLangId + "]']").val(t.prodCatName);
            }
        });
    }

    bannerPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('ProductCategories', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
                var file = inputBtn.files[0];
                var minWidth = document.frmProdCategory.banner_min_width.value;
                var minHeight = document.frmProdCategory.banner_min_height.value;
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
                return cropImage(file, options, 'uploadCatImages', inputBtn);
            });
        }
    };

    iconPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
                var file = inputBtn.files[0];
                var minWidth = document.frmProdCategory.logo_min_width.value;
                var minHeight = document.frmProdCategory.logo_min_height.value;
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
                return cropImage(file, options, 'uploadCatImages', inputBtn);
            });
        }
    };

    uploadCatImages = function (formData) {
        var frmName = formData.get("frmName");
        var slideScreen = 0;
        var prodcatId = $("[name='prodcat_id']").val();
        if (frmName == 'frmCategoryIcon') {
            var afileId = $("#icon-image-listing li").attr('id');
            var langId = $("[name='icon_lang_id']").val();
            var fileType = $("[name='icon_file_type']").val();
            var imageType = 'icon';
        } else {
            var afileId = $("#banner-image-listing li").attr('id');
            var langId = $("[name='banner_lang_id']").val();
            var fileType = $("[name='banner_file_type']").val();
            slideScreen = $("[name='slide_screen']").val();
            var imageType = 'banner';
        }
        formData.append('prodcat_id', prodcatId);
        formData.append('slide_screen', slideScreen);
        formData.append('lang_id', langId);
        formData.append('file_type', fileType);
        formData.append('afile_id', afileId);
        $.ajax({
            url: fcom.makeUrl('ProductCategories', 'setUpCatImages'),
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
                    categoryImages(prodcatId, imageType, slideScreen, langId);
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


$(document).on('change', '.icon-language-js', function () {
    var lang_id = $(this).val();
    var prodcat_id = $("input[name='prodcat_id']").val();
    var imageId = $("[name='cat_icon_image_id[" + lang_id + "]']").val();
    if (prodcat_id == 0) {
        if (imageId > 0) {
            categoryImages(prodcat_id, 'icon', 0, lang_id);
        } else {
            $("#icon-image-listing").html('');
        }
    } else {
        categoryImages(prodcat_id, 'icon', 0, lang_id);
    }

});

$(document).on('change', '.banner-language-js', function () {
    var lang_id = $(this).val();
    var prodcat_id = $("input[name='prodcat_id']").val();
    var slide_screen = $("input[name='slide_screen']").val();
    var imageId = $("[name='cat_banner_image_id[" + lang_id + "_" + slide_screen + "]']").val();
    if (prodcat_id == 0) {
        if (imageId > 0) {
            categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
        } else {
            $("#banner-image-listing").html('');
        }
    } else {
        categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
    }

});

$(document).on('change', '.prefDimensions-js', function () {
    var type = $(this).parent().data('type');
    var slide_screen = $(this).val();
    var prodcat_id = $("input[name='prodcat_id']").val();
    var lang_id = $(".banner-language-js").val();
    var imageId = $("[name='cat_banner_image_id[" + lang_id + "_" + slide_screen + "]']").val();
    if (prodcat_id == 0) {
        if (imageId > 0) {
            categoryImages(prodcat_id, type, slide_screen, lang_id);
        } else {
            if (type == 'icon') {
                $("#icon-image-listing").html('');
            } else {
                $("#banner-image-listing").html('');
            }
        }
    } else {
        categoryImages(prodcat_id, type, slide_screen, lang_id);
    }
});

$(document).on('click', '.tabs_001', function () {
    var prodCatid = $("input[name='prodcat_id']").val();
    catInitialSetUpFrm(prodCatid);
});

$(document).on('click', '.tabs_002', function () {
    var prodCatid = $("input[name='prodcat_id']").val();
    if (prodCatid > 0) {
        categoryCustomFieldsForm(prodCatid);
    } else {
        catInitialSetUpFrm();
    }
});

catInitialSetUpFrm = function () {
    $(".tabs_panel").hide();
    $(".tabs_nav  > li > a").removeClass('active');
    $("#tabs_001").show();
    $("a[rel='tabs_001']").addClass('active');
}

categoryCustomFieldsForm = function (prodCatid) {
    var data = 'prodCategoryId=' + prodCatid;
    fcom.ajax(fcom.makeUrl('Attributes', 'form', []), data, function (res) {
        $(".tabs_panel").hide();
        $(".tabs_nav  > li > a").removeClass('active');
        $("#tabs_002").show();
        $("a[rel='tabs_002']").addClass('active');
        $("#custom-fields-form-js").html(res);

        var langId = $("input[name='lang_id']").val();
        prodCatAttributesByLangId(langId);

    });
};

setupAttr = function (frm) {
    if (!$(frm).validate())
        return;
    var data = fcom.frmData(frm);
    fcom.updateWithAjax(fcom.makeUrl('Attributes', 'setup'), data, function (t) {
        var catId = $("input[name='prodcat_id']").val();
        categoryCustomFieldsForm(catId);
    });
};

prodCatAttributesByLangId = function (langId = 0) {
    var catId = $("input[name='prodcat_id']").val();
    var data = 'catId=' + catId + '&langId=' + langId;
    fcom.ajax(fcom.makeUrl('ProductCategories', 'getAttributes'), data, function (res) {
        $("#custom-fields-listing-js").html(res);
    });
};

editAttr = function (attrId) {
    var catId = $("input[name='prodcat_id']").val();
    var data = 'prodCategoryId=' + catId + '&attrId=' + attrId;
    fcom.ajax(fcom.makeUrl('Attributes', 'form', []), data, function (res) {
        $('#attr_form').html(res);
        var offset = $('#header').outerHeight() + 100;
        $('html, body').animate({
            scrollTop: $(".custom-fields-form-js").offset().top - offset
        }, 100);
    });
};

deleteAttr = function (attrId) {
    var catId = $("input[name='prodcat_id']").val();
    var data = 'attrId=' + attrId + '&status=0';
    fcom.updateWithAjax(fcom.makeUrl('Attributes', 'changeStatus'), data, function (res) {
        categoryCustomFieldsForm(catId);
    });
};


$(document).on('change', '#attr-type-js', function () {
    var selOption = $('#attr-type-js').val();
    if (selOption == 3 || selOption == 5) {
        $('.attr-options-js').show();
        $('.display-in-filter-field-js').show();
    } else {
        $('.attr-options-js').hide();
        $('.display-in-filter-field-js').hide();
    }
});