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
$img_fld->addFieldTagAttribute('accept', 'image/*');

$img_fld = $imagesFrm->getField('prod_size_chart');
if (!empty($img_fld)) {
    $img_fld->addFieldTagAttribute('onChange', 'popupSizeChartImage(this)');
}
?>
<div class="tabs_data">
    <div class="tabs_body">
        <div class="col-md-12">
        <?php   
        /* [ MEDIA INSTRUCTIONS START HERE */
        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $siteLangId);
        echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
        /* ] */    
        ?>
        </div>
    
        <?php echo $imagesFrm->getFormHtml(); ?>
        <div id="imageupload_div"></div>
    </div>
    <!-- [ PRODUCT SPECIFICATION MEDIA SECTION -->
    <?php if ($displaySpec == 1) { ?>
        <input type="hidden" name="langId" value="<?php echo $siteDefaultLangId; ?>" />
        <div class="specifications-form-<?php echo $siteDefaultLangId; ?>"></div>
        <div class="specifications-list-<?php echo $siteDefaultLangId; ?>"></div>
    <?php } ?>	 
    <!-- ] -->

    <div class="row tabs_footer mt-3">
        <div class="col-6">
            <div class="field-set">
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="<?php echo ($productType == Product::PRODUCT_TYPE_PHYSICAL) ? 'productShipping(' . $preqId . ')' : 'productOptionsAndTag(' . $preqId . ')'; ?>" class="btn btn-outline-brand" type="button" name="btn_back" value="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 text-right">
            <div class="btn-group">
                <div class="col-auto js-approval-btn"></div>
                <div class="field-set">
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input onclick="goToCatalogRequest();" type="button" class="btn btn-brand" name="btn_Finish" value="<?php echo Labels::getLabel('LBL_Finish', $siteLangId); ?>">
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</div>
