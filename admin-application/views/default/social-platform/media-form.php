<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$fld1 = $frm->getField('image');
$fld1->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld1->addFieldTagAttribute('onChange', 'popupImage(this)');

$tpl = new FatTemplate('', '');
$tpl->set('adminLangId', $adminLangId);
$instructionHtml = $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);

$htmlAfterField = '<span class="uploadimage--info">'. Labels::getLabel('LBL_This_will_be_displayed_in_30x30_on_your_store.', $adminLangId).'<br/>'. Labels::getLabel('LBL_SVG_images_are_not_supported_in_emails.', $adminLangId) .'</span><div>'. $instructionHtml .'</div>';
if (isset($img) && !empty($img)) {
    $htmlAfterField .= '<ul class="grids--onethird"> <li><div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image', 'SocialPlatform', array($splatform_id,'THUMB'), CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeImg('.$splatform_id.')" class="remove--img"><i class="ion-close-round"></i></a></div></li></ul>';
}
$fld1->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Image_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Image_Setup',$adminLangId);?></h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="addForm(<?php echo $splatform_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <li class="<?php echo (0 == $splatform_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $splatform_id) ? "onclick='addLangForm(" . $splatform_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0);"
                        <?php if ($splatform_id>0) {?>
                            onclick="mediaForm(<?php echo $splatform_id ?>);"
                        <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $frm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
