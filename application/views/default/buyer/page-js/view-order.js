$(document).ready(function () {
    $(document).on('click', 'ul.linksvertical li a.redirect--js', function (event) {
        event.stopPropagation();
    });
});
(function () {
    updatePayment = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Buyer', 'updatePayment'), data, function (t) {
            setTimeout(function () {
                location.reload(true);
            }, 2000);
        });
    };

    extendRentalOrderForm = function (opId, saveCommentData = 0, frm = '') {
        var formData = '';
        if (frm != '') {
            var formData = $(frm).serialize();
        }
        
        /* $.facebox(function () { */
            fcom.ajax(fcom.makeUrl('Buyer', 'extendOrderForm', [opId, saveCommentData]), formData, function (res) {
                /* $.facebox(res, 'medium-fb-width'); */
                $('#exampleModal').html(res);
                $('#exampleModal').modal('show');
            });
        /* }); */
    }

    updateOrderStatus = function (frm) {
        fcom.addTrailingSlash();
        if (!$(frm).validate())
            return;

        var selectedOrderStatus = $('select[name="op_status_id"]').val();
        if (selectedOrderStatus == RENTAL_RETURN_STATUS_ID) {
            var opId = $("input[name='op_id']").val();
            var orderQty = $("input[name='op_qty']").val();
            var returnQty = $("input[name='return_qty']").val();
            if (returnQty < orderQty) {
                extendRentalOrderForm(opId, 1, frm);
                return;
            }
        }
        if ($('select[name="op_return_fullfillment_type"]').val() == FULLFILLMENT_SHIP) {
            $('.dropfld input').val(""); 
        }
        
        $.mbsmessage(langLbl.processing, true, 'alert--process alert');
        $('input[name="btn_submit"]').attr('disabled', 'disabled');
        $.ajax({
            url: fcom.makeUrl('Buyer', 'updateOrderStatus'),
            type: 'post',
            dataType: 'json',
            data: new FormData($(frm)[0]),
            cache: false,
            contentType: false,
            processData: false,

            success: function (ans) {
                if (ans.status == true) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    setTimeout(function () {
                        location.reload(true);
                    }, 2000);
                } else {
                    $('input[name="btn_submit"]').removeAttr('disabled');
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
    
    uploadCommentFormFile = function () {
        var frm = "form[name='frmOrderComments']";
        var formData = new FormData($(frm)[0]);
        var $i = $(frm + '  .commentFileJs');
        var inputBtn = $i[0];
        if (inputBtn.files && inputBtn.files[0]) {
            $.mbsmessage(langLbl.processing, true, 'alert--process alert');
            $.ajax({
                url: fcom.makeUrl('Buyer', 'uploadCommentFileTemp'),
                type: 'post',
                data: formData,
                mimeType: "multipart/form-data",
                cache: false,
                contentType: false,
                processData: false,
                success: function (t) {
                    var fileName = inputBtn.files[0].name;
                    $('.commentFileJs').val('');
                    var ans = $.parseJSON(t);
                    if (ans.status == true) {
                        $.mbsmessage(ans.msg, true, 'alert--success');
                        /* var fileHtml = '<span class="fileJs--'+ ans.fileId +'">'+ fileName +'<a href="javascript:void(0);" onClick="removeTempFile('+ ans.fileId +')"><i class="fa fa-trash"></i></a></span>'; */
                        var fileHtml = '<li class="uploaded fileJs--'+ ans.fileId +'"><div class="file"><div class="file_name">'+ fileName +'</div><div class="progress"><div class="progress-bar" style="width:100%"> </div></div><a class="trash" href="javascript:void(0)" onClick="removeTempFile('+ ans.fileId +')"><i class="icn"><svg class="svg" width="18px" height="18px"><use xlink:href="/images/retina/sprite.svg#remove"></use></svg></i></a></div></li>';
                        $('.uploadedFilesJs').append(fileHtml);
                    } else {
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    };
    
    removeTempFile = function (fileId = 0) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Buyer', 'removeTempFile', [fileId]), '', function (t) {
            $('.uploadedFilesJs .fileJs--'+ fileId).remove();
        });
    }
    
})();