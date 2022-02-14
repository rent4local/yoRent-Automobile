<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupPlugin(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo $identifier ?> <?php echo Labels::getLabel('LBL_PLUGIN_SETUP', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="tabs_nav_container responsive flat">
            <ul class="tabs_nav">
                <li>
                    <a class="active" href="javascript:void(0)" onclick="editPluginForm(<?php echo $type ?>, <?php echo $pluginId ?>);">
                        <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                    </a>
                </li>
                <li class="<?php echo (0 == $pluginId) ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0);" <?php echo (0 < $pluginId) ? "onclick='editPluginLangForm(" . $pluginId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                        <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                    </a>
                </li>          
            </ul>
            <div class="tabs_panel_wrap">
                <div class="tabs_panel">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
