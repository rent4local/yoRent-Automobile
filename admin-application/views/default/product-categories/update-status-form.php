<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);

?>
<section class="section" >
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Change_Product_Categories_Request_Status', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="border-box border-box--space">
                <?php echo $frm->getFormTag();?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label"><?php echo $frm->getField('prodcat_status')->getCaption(); ?></label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('prodcat_status'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php echo $frm->getFieldHtml('prodcat_id'); ?>
                    <div class="col-md-8  ">
                        <?php echo $frm->getFieldHtml('btn_submit'); ?>
                    </div>
                </div>
                </form>
        </div>
    </div>
</section>
<?php echo $frm->getExternalJS(); ?>