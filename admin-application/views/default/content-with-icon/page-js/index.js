$(document).ready(function () {
    searchBlocks(document.frmBlockSearch);
});

(function () {
    var controllerName = 'ContentWithIcon';
    reloadList = function () {
        var frm = document.frmBlockSearch;
        searchBlocks(frm);
    }

    searchBlocks = function (form) {
        var dv = '#blockListing';
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl(controllerName, 'search'), data, function (res) {
            $(dv).html(res);
        });
    };

    addBlockFormNew = function (id) {
        $.facebox(function () {
            addBlockForm(id);
        });
    };
    addBlockForm = function (id = 0) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl(controllerName, 'form', [id]), '', function (t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setupBlock = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'setup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                addBlockLangForm(t.blockId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addBlockLangForm = function (epageId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();

        fcom.ajax(fcom.makeUrl(controllerName, 'langForm', [epageId, langId, autoFillLangData]), '', function (t) {
            fcom.updateFaceboxContent(t);
            fcom.resetFaceboxHeight();
            fcom.setEditorLayout(langId);
            var frm = $('#facebox form')[0];
            var validator = $(frm).validation({errordisplay: 3});

            $(frm).submit(function (e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl(controllerName, 'langSetup'), data, function (t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.langId > 0) {
                        addBlockLangForm(t.blockId, t.langId);
                        return;
                    }
                    $(document).trigger('close.facebox');
                });

            });

        });

    };

    resetToDefaultContent = function () {
        var agree = confirm(langLbl.confirmReplaceCurrentToDefault);
        if (!agree) {
            return false;
        }
        oUtil.obj.insertHTML($("#editor_default_content").html());
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
        var blockId = parseInt(obj.value);
        if (blockId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        var status = 0;
        if ($(obj).prop('checked') == true) {
            var status = 1;
        }

        data = 'cbs_id=' + blockId + '&cbs_active=' + status;
        fcom.ajax(fcom.makeUrl(controllerName, 'changeStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                $(obj).toggleClass("active");
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    deleteImage = function (blockId, fileId, langId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'deleteIcon', [blockId, fileId, langId]), '', function (t) {
            blockMedia(blockId, langId);
        });
    };

    deleteBlock = function (blockId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl(controllerName, 'deleteBlock', [blockId]), '', function (t) {
            reloadList();
        });
    };


    blockMedia = function (blockId) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl(controllerName, 'imagesForm', [blockId]), '', function (t) {
            fcom.updateFaceboxContent(t);
            blockImages(blockId);
        });
    };

    blockImages = function (blockId, langId = 0) {
        fcom.ajax(fcom.makeUrl(controllerName, 'images', [blockId, langId]), '', function (t) {
            $('#imageupload_div').html(t);
        });
    };
    popupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl(controllerName, 'imgCropper'), '', function (t) {
                $("#mediaForm-js").css("display", "none");
                $('#cropperBox-js').html(t);
                var file = inputBtn.files[0];
                var minWidth = 100;
                var minHeight = 100;
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
        var blockId = document.imageFrm.cbs_id.value;
        var lang_id = document.imageFrm.lang_id.value;

        formData.append('cbs_id', blockId);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl(controllerName, 'uploadBlockImages'),
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
                    blockImages(blockId, lang_id);
                } else {
                    fcom.displayErrorMessage(ans.msg);
                }
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    };
})();