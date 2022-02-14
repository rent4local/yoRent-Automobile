<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<ul>
<?php
$showDefalultLi = true;
if ($languages && count($languages) > 1) {
    $showDefalultLi = false;
?>
    <li>
        <div class="dropdown dropdown--lang">
            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
                <span><?php echo $languages[$siteLangId]['language_name']; ?></span>
            </a>
            <span class="slash">|</span>
            <div class="dropdown-menu dropdown-menu-fit dropdown-menu-anim">
                <div class="scroll-y">
                    <ul class="nav nav-block">
                        <li class="nav__item">
                            <h6 class="dropdown-header expand-heading">
                                <?php echo Labels::getLabel('LBL_Select_Language', $siteLangId); ?></h6>
                        </li>
                        <?php foreach ($languages as $langId => $language) { ?>
                        <li class="nav__item is-active">
                            <a class="dropdown-item nav__link" href="javascript:void(0);"
                                onClick="setSiteDefaultLang(<?php echo $langId; ?>);">
                                <img class="icon--img" alt="Language Flag"
                                    src="<?php echo CONF_WEBROOT_URL; ?>images/flags/<?php echo FatApp::getConfig('CONF_COUNTRY_FLAG_TYPE', FatUtility::VAR_STRING, 'round'); ?>/<?php echo $language['language_country_code'] . '.svg'; ?>" />
                                <?php echo $language['language_name']; ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </li>
    <?php } ?>
    <?php if ($currencies && count($currencies) > 1) {
    $showDefalultLi = false; ?>
    <li>
        <div class="dropdown dropdown--lang">
            <a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
                <?php echo (CommonHelper::getCurrencySymbolRight()) ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft(); ?>
                <span class="hide-sm"><?php echo $currencies[$siteCurrencyId]; ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-fit dropdown-menu-anim">
                <div class="scroll-y">
                    <ul class="nav nav-block">
                        <li class="nav__item">
                            <h6 class="dropdown-header expand-heading">
                                <?php echo Labels::getLabel('LBL_Select_Currency', $siteLangId); ?></h6>
                        </li>
                        <?php foreach ($currencies as $currencyId => $currency) { ?>
                        <li class="nav__item is-active">
                            <a class="dropdown-item nav__link" href="javascript:void(0);"
                                onClick="setSiteDefaultCurrency(<?php echo $currencyId; ?>);">
                                <?php echo $currency; ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </li>
    <?php } ?>
</ul>