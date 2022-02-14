<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$blockLangFrm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
$blockLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$blockLangFrm->developerTags['fld_default_col'] = 12;
$langFld = $blockLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addBlockLangForm(" . $blockId . ", this.value);");
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Content_Block_with_icon_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0);"
                               onclick="addBlockForm(<?php echo $blockId ?>);">
                                   <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (0 == $blockId) ? 'fat-inactive' : 'active'; ?>">
                            <a class="active" href="javascript:void(0);" <?php echo (0 < $blockId) ? "onclick='addBlockLangForm(" . $blockId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (0 == $blockId) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $blockId) ? "onclick='blockMedia(" . $blockId . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $langId != $siteDefaultLangId) {
                            ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                           type="button" 
                                           value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                           onClick="addBlockLangForm(<?php echo $blockId; ?>, <?php echo $langId; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="tabs_panel">
                            <?php
                            echo $blockLangFrm->getFormTag();
                            echo $blockLangFrm->getFormHtml(false);
                            echo '</form>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>