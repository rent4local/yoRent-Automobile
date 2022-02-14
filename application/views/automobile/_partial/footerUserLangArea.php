<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="footer-drop dropdown dropdown--arrow" id="HDR-SETTING">
    <a href="javascript:void(0)" class="footer-drop-trigger dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <span><?php if ($languages[$siteLangId]['language_country_code']) {
                                            echo $languages[$siteLangId]['language_country_code']
                                        ?>
            <?php } else { ?>
                <i class="icn icn-lang">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#lang"></use>
                    </svg>
                </i>
            <?php } ?> | <?php echo $currencies[$siteCurrencyId]; ?></span>
    </a>
    <div class="footer-drop-target dropdown-menu dropdown-menu-fit dropdown-menu-anim dropdown-menu-right" aria-labelledby="dropdownMenuButton" x-placement="bottom-end">
        <?php ?>
        <div class="setting-group">
            <h6> <?php echo Labels::getLabel('LBL_Language', $siteLangId); ?></h6>
            <ul class="list--vertical list--vertical-tabs">
                <?php foreach ($languages as $langId => $language) { ?>
                    <li class="<?php echo ($siteLangId == $langId) ? ' is-active' : ''; ?>">
                        <a class="" href="javascript:void(0);" onClick="setSiteDefaultLang(<?php echo $langId; ?>)">
                            <?php if ($language['language_country_code']) { ?>
                                <i class="icn icn-lang">
                                    <i class="icn icn-lang">
                                        <img class="icon--img" alt="<?php echo Labels::getLabel('LBL_Language_Flag', $siteLangId); ?>" src="<?php echo CONF_WEBROOT_URL; ?>images/flags/<?php echo FatApp::getConfig('CONF_COUNTRY_FLAG_TYPE', FatUtility::VAR_STRING, 'round'); ?>/<?php echo $language['language_country_code']; ?>.svg">
                                    </i>
                                </i>
                            <?php } ?>
                            <?php echo ' ' . $language['language_name']; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="setting-group">
            <h6> <?php echo Labels::getLabel('LBL_Currency', $siteLangId); ?> </h6>
            <ul class="list--vertical">
                <?php foreach ($currencies as $currencyId => $currency) { ?>
                    <li class="<?php echo ($siteCurrencyId == $currencyId) ? ' is-active' : ''; ?>">
                        <a class="" href="javascript:void(0);" onClick="setSiteDefaultCurrency(<?php echo $currencyId; ?>)"> <?php echo $currency; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>