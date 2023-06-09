<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$shopLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$shopLangFrm->setFormTagAttribute('onsubmit', 'setupShopLang(this); return(false);');

$shopLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopLangFrm->developerTags['fld_default_col'] = 12;

$paymentPolicyfld = $shopLangFrm->getField('shop_payment_policy');
$paymentPolicyfld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Shop_payment_terms_comments', $adminLangId) . '</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_delivery_policy');
$paymentPolicyfld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Shop_delivery_policy_comments', $adminLangId) . '</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_refund_policy');
$paymentPolicyfld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Shop_refund_policy_comments', $adminLangId) . '</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_additional_info');
$paymentPolicyfld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Shop_additional_info_comments', $adminLangId) . '</small>';

$paymentPolicyfld = $shopLangFrm->getField('shop_seller_info');
$paymentPolicyfld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Shop_seller_info_comments', $adminLangId) . '</small>';

$langFld = $shopLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addShopLangForm(" . $shop_id . ", this.value);");

?>
<section class="section">
    <div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Shop_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="col-sm-12">

            <div class="row">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0);" onclick="shopForm(<?php echo $shop_id ?>);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (0 == $shop_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <?php /* <li><a href="javascript:void(0);"
                        <?php if ($shop_id > 0) { ?>
                            onclick="shopTemplates(<?php echo $shop_id ?>);"
                        <?php }?>><?php echo Labels::getLabel('LBL_Templates', $adminLangId); ?></a></li> */ ?>
                        <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) {?>
                                onclick="shopMediaForm(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) {?>
                                onclick="shopCollections(<?php echo $shop_id ?>);"
                            <?php } ?>><?php echo Labels::getLabel('LBL_Collections', $adminLangId); ?></a></li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopAgreement(<?php echo $shop_id ?>);"
                            <?php } ?>><?php echo Labels::getLabel('LBL_Shop_Agreement', $adminLangId); ?></a></li>    
                    </ul>
                    <div class="tabs_panel_wrap">
                        <?php 
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $shop_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="addShopLangForm(<?php echo $shop_id; ?>, <?php echo $shop_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
                        <div class="tabs_panel">
                            <?php echo $shopLangFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
