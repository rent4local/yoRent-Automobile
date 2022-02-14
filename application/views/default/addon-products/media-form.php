<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaForm->setFormTagAttribute('class', 'form');
$mediaForm->developerTags['colClassPrefix'] = 'col-md-';
$mediaForm->developerTags['fld_default_col'] = 4;

$fld = $mediaForm->getField('addonprod_image');
$fld->addFieldTagAttribute('class', 'btn btn--sm');
$fld->addFieldTagAttribute('onChange', 'popupImage(this)');  ?>
<div class="col-md-12">
    <?php 
    /* [ MEDIA INSTRUCTIONS START HERE */
    $tpl = new FatTemplate('', '');
    $tpl->set('siteLangId', $siteLangId);
    echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
    /* ] */    
    ?>
</div>
<?php echo $mediaForm->getFormHtml(); ?>
<script type="text/javascript">
    (function () {
        popupImage = function (inputBtn) {
            if (inputBtn.files && inputBtn.files[0]) {
                fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                    /* $.facebox(t, 'faceboxWidth'); */
                    $('#exampleModal').html(t);
        			$('#exampleModal').modal('show');
                    var file = inputBtn.files[0];
                    //var minWidth = $('#frmCustomCatalogProductImage input[name="min_width"]').val();
                    //var minHeight = $('#frmCustomCatalogProductImage input[name="minHeight"]').val();
                    let minWidth = 500;
                    let minHeight = 500;
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
                    return cropImage(file, options, 'uploadImage', inputBtn);
                });
            }
        };
    })();


    uploadImage = function (formData) {
        let addon_prod_id = $('input[name="addonprod_id"]').val();
        let lang_id = $('select[name="lang_id"]').val();

        formData.append('addonprod_id', addon_prod_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl('AddonProducts', 'setupImage'),
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
                /* $(document).trigger('close.facebox'); */
                $("#exampleModal .close").click();
                mediaListing();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    };
</script>