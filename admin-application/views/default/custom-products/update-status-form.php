<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
echo $frm->getFormTag();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="field-set">
            <div class="caption-wraper">
                <label class="field_label"><?php echo $frm->getField('preq_status')->getCaption(); ?></label>
            </div>
            <div class="field-wraper">
                <div class="field_cover">
                    <?php echo $frm->getFieldHtml('preq_status'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="field-set">
            <div class="caption-wraper">
                <label class="field_label"><?php echo $frm->getField('preq_comment')->getCaption(); ?></label>
            </div>
            <div class="field-wraper">
                <div class="field_cover">
                    <?php echo $frm->getFieldHtml('preq_comment'); ?>
                </div>
            </div>
        </div>
    </div>

<?php echo $frm->getFieldHtml('preq_id'); ?>
    <div class="col-md-8  ">
        <?php echo $frm->getFieldHtml('btn_submit'); ?>
    </div>
</div>
</form>
<?php echo $frm->getExternalJS(); ?>