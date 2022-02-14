<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);

?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Change_Brands_Request_Status', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="border-box border-box--space">

            <?php echo $frm->getFormTag(); ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo $frm->getField('brand_status')->getCaption(); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('brand_status'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-md-8">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo $frm->getField('brand_comments')->getCaption(); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('brand_comments'); ?>
                            </div>
                        </div>
                    </div>
                </div> -->

                <?php 
                echo $frm->getFieldHtml('brand_comments');
                echo $frm->getFieldHtml('brand_id');
                echo $frm->getFieldHtml('brand_identifier');
                echo $frm->getFieldHtml('urlrewrite_custom');
                ?>
                <div class="col-md-8  ">
                    <?php echo $frm->getFieldHtml('btn_submit'); ?>
                </div>
            </div>
            </form>

        </div>
    </div>
</section>
<?php echo $frm->getExternalJS(); ?>