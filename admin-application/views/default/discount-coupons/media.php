<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$couponMediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$couponMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$couponMediaFrm->developerTags['fld_default_col'] = 12;
$fld = $couponMediaFrm->getField('coupon_image');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'popupImage(this)');
$langFld = $couponMediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_This_will_be_displayed_in_%s_on_your_store', $adminLangId), '60*60').'</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="image-listing"></div>';
$fld->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Coupon_Media_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Coupon_Media_Setup',$adminLangId);?></h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="addCouponForm(<?php echo $coupon_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
						<li class="<?php echo ($coupon_id == 0) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo ($coupon_id) ? "onclick='addCouponLangForm(" . $coupon_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0);"
                        <?php if ($coupon_id > 0) {?>
                            onclick="couponMediaForm(<?php echo $coupon_id ?>);"
                        <?php } ?> >
                        <?php echo Labels::getLabel('LBL_Media', $adminLangId);?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                        <?php 
                        /* [ MEDIA INSTRUCTIONS START HERE */
                        $tpl = new FatTemplate('', '');
                        $tpl->set('adminLangId', $adminLangId);
                        echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                        /* ] */    
                        
                        echo $couponMediaFrm->getFormHtml();?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
