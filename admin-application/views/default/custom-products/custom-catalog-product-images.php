<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$imagesFrm->setFormTagAttribute('id', 'frmCustomCatalogProductImage');
$imagesFrm->setFormTagAttribute('class', 'form');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 6;

$optionFld = $imagesFrm->getField('option_id');
$optionFld->addFieldTagAttribute('class', 'option-js');

$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$img_fld = $imagesFrm->getField('prod_image');
$img_fld->addFieldTagAttribute('class', 'btn  btn-sm');
$img_fld->addFieldTagAttribute('onChange', 'popupImage(this)');

$img_fld = $imagesFrm->getField('prod_size_chart');
if (!empty($img_fld)) {
    $img_fld->addFieldTagAttribute('onChange', 'popupSizeChartImage(this)');
}
?>
<div class="tabs_data">
    <div class="tabs_body">
        <div class="row">
            <div class="col-md-12">
                <?php 
                /* [ MEDIA INSTRUCTIONS START HERE */
                $tpl = new FatTemplate('', '');
                $tpl->set('adminLangId', $adminLangId);
                echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                /* ] */    
                ?>
            </div>
            <div class="col-sm-6">
                <div class="specifications-form p-4 border rounded">
                    <?php echo $imagesFrm->getFormHtml(); ?>
                    <div id="imageupload_div"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <!-- [ PRODUCT SPECIFICATION MEDIA SECTION -->
                <?php if ($displaySpec == 1) { ?>
                <div class="specifications-form p-4 border rounded">
                    <input type="hidden" name="langId" value="<?php echo $siteDefaultLangId; ?>" />
                    <div class="specifications-form-<?php echo $siteDefaultLangId; ?>"></div>
                    <div class="specifications-list-<?php echo $siteDefaultLangId; ?>"></div>
                </div>    
                <?php } ?>	 
                <!-- ] -->
            </div>
        </div>
    </div>
    

    <div class="row tabs_footer">
        <div class="col-6">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="<?php echo ($productType == Product::PRODUCT_TYPE_PHYSICAL) ? 'productShipping(' . $preqId . ')' : 'productOptionsAndTag(' . $preqId . ')'; ?>" class="btn btn-outline-brand" type="button" name="btn_back" value="<?php echo Labels::getLabel('LBL_Back', $adminLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 text-right">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input  type="button" onclick="updateStatusForm(<?php echo $preqId;?>)" class="btn btn-brand" name="btn_Finish" value="<?php echo Labels::getLabel('LBL_Finish', $adminLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
