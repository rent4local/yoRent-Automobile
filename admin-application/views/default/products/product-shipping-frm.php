<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'web_form mt-5');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProductShipping(this); return(false);');
$productFrm->developerTags['colClassPrefix'] = 'col-md-';
$productFrm->developerTags['fld_default_col'] = 12;

$spProfileFld = $productFrm->getField('shipping_profile');
$spProfileFld->developerTags['col'] = 6;

if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
    $spPackageFld = $productFrm->getField('product_ship_package');
    $spPackageFld->developerTags['col'] = 6;
}

$weightUnitFld = $productFrm->getField('product_weight_unit');
$weightUnitFld->developerTags['col'] = 6;

$weightFld = $productFrm->getField('product_weight');
$weightFld->developerTags['col'] = 6;

if (!$shippedByUserId) {
    $fulfillmentType = $productFrm->getField('product_fulfillment_type');
    $fulfillmentType->developerTags['col'] = 6;
}

$codFld = $productFrm->getField('product_cod_enabled');
if (!empty($codFld)){
    $codFld->developerTags['col'] = 12;
}


$btnbackFld = $productFrm->getField('btn_back');
$btnbackFld->developerTags['col'] = 6;

$btnSubmitFld = $productFrm->getField('btn_submit');
$btnSubmitFld->developerTags['col'] = 6;
$btnSubmitFld->addWrapperAttribute('class', 'text-right');
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormHtml(); ?>
    </div>
</div>