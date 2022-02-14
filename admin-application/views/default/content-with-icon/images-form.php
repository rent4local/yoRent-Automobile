<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$imagesFrm->setFormTagAttribute('class', 'web_form mt-5');
$imagesFrm->setFormTagAttribute('id', 'imageFrm');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 6;

$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$imgFld = $imagesFrm->getField('block_image');
$imgFld->addFieldTagAttribute('onChange', 'popupImage(this)');
$imgFld->htmlBeforeField = '<span class="filename"></span>';
$imgFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_100_x_100', $adminLangId) . '</small>';
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Content_Block_with_icon_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0)"
                               onclick="addBlockForm(<?php echo $blockId ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <li class="<?php echo (0 == $blockId) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $blockId) ? "onclick='addBlockLangForm(" . $blockId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li class=" active">
                            <a class="active" href="javascript:void(0);" <?php echo (0 < $blockId) ? "onclick='blockMedia(" . $blockId . ");'" : ""; ?> >
                                <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <div id="mediaForm-js">
                                <?php echo $imagesFrm->getFormHtml(); ?>
                            </div>
                            <div id="cropperBox-js"></div>
                            <div id="imageupload_div" class="padd15"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>