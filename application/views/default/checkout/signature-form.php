<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered" role="document" id="signature-form">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"> <?php echo Labels::getLabel('LBL_Upload_Your_Signature', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div id="signature-modaal">
                <?php 
                    $signFrm->setFormTagAttribute('id', 'frmSignImage');
                    $signFrm->setFormTagAttribute('class', 'form');
                    $signFrm->setFormTagAttribute('onSubmit', 'uploadSignFile(this); return false;');
                    $signFrm->developerTags['colClassPrefix'] = 'col-md-';
                    $signFrm->developerTags['fld_default_col'] = 6;
                    $fld = $signFrm->getField('btn_submit');
                    $fld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
                    echo $signFrm->getFormHtml(); 
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>


