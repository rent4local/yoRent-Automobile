<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$langFld = $customProductLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "productLangForm(" . $preqId . ", this.value);");
?>
<div class="tabs_panel_wrap">
    <?php
    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
    $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
    if (!empty($translatorSubscriptionKey) && $product_lang_id != $siteDefaultLangId) {
    ?>
        <div class="row justify-content-end">
            <div class="col-auto mb-4">
                <input class="btn btn-brand" type="button" value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" onClick="productLangForm(<?php echo $preqId; ?>, <?php echo $product_lang_id; ?>, 1)">
            </div>
        </div>
    <?php } ?>
    <div class="tabs_panel">
        <?php
        //$customProductLangFrm->setFormTagAttribute('onsubmit','setUpSellerProduct(this); return(false);');
        $customProductLangFrm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
        $customProductLangFrm->developerTags['colClassPrefix'] = 'col-md-';
        $customProductLangFrm->developerTags['fld_default_col'] = 12;
        echo $customProductLangFrm->getFormHtml();
        ?>
    </div>
</div>