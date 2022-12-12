<?php if ('' != FatApp::getConfig("CONF_FACEBOOK_PIXEL_ID", FatUtility::VAR_STRING, '')) { ?>
    <img alt="Facebook Pixel" height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $pixelId; ?>&ev=PageView&noscript=1" />
    <?php
}
if (strtolower($controllerName) != 'guestuser' || (strtolower($controllerName) == 'guestuser' && strtolower($action) == 'registrationsuccess') ) {
    if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0) && !$isUserDashboard) { ?>
        <section class="section" style="background-color:rgba(var(--brand-color-alpha),0.2)">
            <div class="container">
                <div class="section__heading">
                    <h2><?php echo Labels::getLabel('LBL_Want_Yo!Rent_updates_sent_straight_to_your_inbox?', $siteLangId); ?>
                    </h2>
                    <h5><?php echo Labels::getLabel('LBL_Sign_up_to_be_the_first_to_hear_about_big_news.', $siteLangId); ?></h5>
                </div>
                <?php $this->includeTemplate('_partial/footerNewsLetterForm.php'); ?>
            </div>
        </section>
    <?php } ?>
    <?php if (!$isUserDashboard) { ?>
        <footer class="section footer pb-0">
            <div class="container">
                <div class="up-footer">
                        <?php
                        $logoUrl = UrlHelper::generateUrl();
                        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
                            $logoUrl = UrlHelper::generateUrl('home', 'index');
                        }

                        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
                        $sizeType = 'CUSTOM';
                        $logoClass="logo-custom";
                        if ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_RECTANGULAR) {
                            $sizeType = '16X9';
                            $logoClass="";
                        } elseif($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_SQUARE) {
                            $sizeType = '1X1';
                            $logoClass="";
                        }
                        
                        $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
                        $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId, $sizeType), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        ?>

                        <div class="footer-logo <?php echo $logoClass; ?>">
                            <a href="<?php echo $logoUrl; ?>">
                                <img src="<?php echo $siteLogo; ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
                            </a>
                        </div>
                        <div class="footer-top-right">
                            <?php $this->includeTemplate('_partial/footerUserLangArea.php'); ?>
                            <?php $this->includeTemplate('_partial/footerSocialMedia.php'); ?>
                        </div>
                </div>
                <div class="down-footer">
                    <div class="row mt-5">
                        <?php Navigation::footerNavigation(); ?>
                        <div class="col-md-3">
                            <div class="toggle-group">
                                <h5 class="toggle__trigger toggle__trigger-js">
                                    <?php echo Labels::getLabel('LBL_CONTACT_US', $siteLangId); ?></h5>
                                <div class="toggle__target toggle__target-js">
                                    <ul class="nav-vertical">
                                        <li><?php echo FatApp::getConfig('CONF_SITE_PHONE_CODE', FatUtility::VAR_STRING, '') . ' ' . FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, '') ?></li>
                                        <li><?php echo FatApp::getConfig('CONF_SITE_OWNER_EMAIL', FatUtility::VAR_STRING, '') ?>
                                        </li>
                                        <li><?php echo nl2br(FatApp::getConfig('CONF_ADDRESS_' . $siteLangId, FatUtility::VAR_STRING, '')); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-brand-alpha">
                <div class="container">
                    <div class="bottom-footer">
                        <div class="copyright">
                            <?php
                            $productName = (CommonHelper::demoUrl()) ? "Yo!Rent" : FatApp::getConfig('CONF_WEBSITE_NAME_'. $siteLangId, FatUtility::VAR_STRING, "");
                            $defaultLangId = FatApp::getConfig("CONF_DEFAULT_SITE_LANG", FatUtility::VAR_INT, 1);
                            
                            $ownerName = FatApp::getConfig('CONF_SITE_OWNER_'. $defaultLangId, FatUtility::VAR_STRING, "") != '' ? FatApp::getConfig('CONF_SITE_OWNER_'. $defaultLangId, FatUtility::VAR_STRING, "") : 'FATbit Technologies';

                            $productName = (trim($productName) == '') ? FatApp::getConfig('CONF_WEBSITE_NAME_'. $defaultLangId, FatUtility::VAR_STRING, "") : $productName;
                            
                            $siteUrl = (CommonHelper::demoUrl()) ? "https://yo-rent.com" : UrlHelper::generateFullUrl();
                            $replacements = array(
                                '{YEAR}' => '&copy; ' . date("Y"),
                                '{PRODUCT}' => '<strong style="font-weight: bolder !important;"><a target="_blank" href="'. $siteUrl .'" rel="noopener">'. $productName .'</a></strong>',
                                '{OWNER}' => '<strong style="font-weight: bolder !important;"><a target="_blank" href="'. $siteUrl .'" rel="noopener">' . $ownerName . '</a><strong>',
                            );
                            echo CommonHelper::replaceStringData(Labels::getLabel('LBL_COPYRIGHT_TEXT', $siteLangId), $replacements);
                            ?>
                        </div>
                        
                        <?php Navigation::footerNavigation('',Navigations::NAVTYPE_FOOTER_BOTTOM);?>
                    </div>
                </div>
            </div>
            <?php if (CommonHelper::demoUrl()) { ?>
            <div class="footer__disclaimer py-4">
                <div class="container"><?php $this->includeTemplate('_partial/disclaimer.php'); ?></div>
            </div>    
            <?php } ?>
        </footer>
    <?php } ?>
<?php } ?>
<?php if (FatApp::getConfig('CONF_ENABLE_COOKIES', FatUtility::VAR_INT, 1) && !CommonHelper::getUserCookiesEnabled()) { ?>
    <div class="cc-window cc-banner cc-type-info cc-theme-block cc-bottom cookie-alert no-print">
        <?php if (FatApp::getConfig('CONF_COOKIES_TEXT_' . $siteLangId, FatUtility::VAR_STRING, '')) { ?>
            <div class="box-cookies">
                <span id="cookieconsent:desc" class="cc-message">
                    <?php echo FatUtility::decodeHtmlEntities(mb_substr(FatApp::getConfig('CONF_COOKIES_TEXT_' . $siteLangId, FatUtility::VAR_STRING, ''), 0, 500)); ?>
                    <a href="<?php echo UrlHelper::generateUrl('cms', 'view', array(FatApp::getConfig('CONF_COOKIES_BUTTON_LINK', FatUtility::VAR_INT))); ?>"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a></span>
                <span class="cc-close cc-cookie-accept-js"><?php echo Labels::getLabel('LBL_Accept_Cookies', $siteLangId); ?></span>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<?php if (!isset($_SESSION['geo_location'])) { ?>
    <script src='https://maps.google.com/maps/api/js?key=<?php echo FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, ''); ?>&libraries=places'>
    </script>
    <script>
        googleAddressAutocomplete();
    </script>
<?php }

if (FatApp::getConfig('CONF_ENABLE_LIVECHAT', FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig('CONF_LIVE_CHAT_CODE', FatUtility::VAR_STRING, '');
}

if (FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '');
}
?>

<div class="no-print">
	<?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
        $this->includeTemplate('restore-system/page-content.php');
    }
    if (FatApp::getConfig('CONF_PWA_SERVICE_WORKER', FatUtility::VAR_INT, 1)) { ?>
        <script>
            $(document).ready(function() {
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', function() {
                        navigator.serviceWorker.register(
                            '<?php echo CONF_WEBROOT_URL; ?>sw.js?t=<?php echo filemtime(CONF_INSTALLATION_PATH . 'public/sw.js'); ?>&f'
                        ).then(function(registration) {});
                    });
                }
            });
        </script>
    <?php } ?>
</div>
</div>
</body>
<?php if (CommonHelper::demoUrl()) { 
   $this->includeTemplate('_partial/requestDemoPopup.php');
} ?>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
</html>