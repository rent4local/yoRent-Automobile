<?php 
$file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PWA_APP_ICON,0);
$uploadedTime = AttachedFile::setTimeParam($file_row['afile_updated_at']);
?>
<link rel="shortcut icon"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId)), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId)), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="57x57"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '57-57')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="60x60"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '60-60')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="72x72"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '72-72')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="76x76"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '76-76')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="114x114"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '114-114')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="120x120"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '120-120')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="144x144"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '144-144')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="152x152"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '152-152')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="apple-touch-icon" sizes="180x180"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'appleTouchIcon', array($siteLangId, '180-180')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="icon" type="image/png" sizes="192x192"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '192-192')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="icon" type="image/png" sizes="32x32"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '32-32')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="icon" type="image/png" sizes="96x96"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '96-96')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
<link rel="icon" type="image/png" sizes="16x16"
    href="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'favicon', array($siteLangId, '16-16')), CONF_IMG_CACHE_TIME, '.png').$uploadedTime; ?>">
    <?php if (FatApp::getConfig('CONF_ENABLE_PWA')) { ?>
<link rel="manifest" href="<?php echo UrlHelper::generateUrl('Home', 'pwaManifest'); ?>">
<?php } ?>
<?php
if ($canonicalUrl == '') {
    $canonicalUrl = UrlHelper::generateFullUrl($controllerName, FatApp::getAction(), !empty(FatApp::getParameters()) ? FatApp::getParameters() : array());
}
?>
<link rel="canonical" href="<?php echo $canonicalUrl; ?>" />
<?php 
$googleFontFamilyUrl = Theme::DEFAULT_FONT_FAMILY_URL;
$googleFontFamily = Theme::DEFAULT_FONT_FAMILY_FOR_FRONTEND;

if ($themeDetail['theme_font_family'] != '') {
	$googleFontFamily = $themeDetail['theme_font_family'];
}

if ($themeDetail['theme_font_family_url'] != '') {
	$googleFontFamilyUrl = $themeDetail['theme_font_family_url'];
}

?>
<link href="<?php echo $googleFontFamilyUrl; ?>" rel="stylesheet">
<style>
<?php if ( !empty($googleFontFamily)) {
    ?>body {
        font-family: "<?php echo $googleFontFamily; ?>" !important;
    }

    <?php
}

$brandColorWithoutRGB=str_replace("rgb(", "", $themeDetail['theme_color']);
$brandColorWithoutRGB=str_replace(")", "", $brandColorWithoutRGB);

?> :root {
    --brand-color: <?php echo $themeDetail['theme_color'];
    ?>;
    --brand-color-alpha: <?php echo $brandColorWithoutRGB;
    ?>;
    --brand-color-inverse: <?php echo $themeDetail['theme_color_inverse'];
    ?>;
    --secondary-color: <?php echo $themeDetail['theme_secondary_color'];
    ?> !important;
    --secondary-color-inverse: <?php echo $themeDetail['theme_secondary_color_inverse'];
    ?> !important;
}
</style>
<script type="text/javascript">
<?php
    $CONF_ENABLE_GEO_LOCATION = (trim(FatApp::getConfig("CONF_GOOGLEMAP_API_KEY", FatUtility::VAR_STRING, '')) != '') ? 1 : 0;
    $isUserDashboard = ($isUserDashboard) ? 1 : 0;
    echo $str = 'var langLbl = ' . FatUtility::convertToJson($jsVariables, JSON_UNESCAPED_UNICODE) . ';
    var CONF_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 0) . ';
    var CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = ' . FatApp::getConfig("CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES", FatUtility::VAR_INT, 3) . ';
    var CONF_ENABLE_GEO_LOCATION = ' . $CONF_ENABLE_GEO_LOCATION . ';
    var CONF_MAINTENANCE = ' . FatApp::getConfig("CONF_MAINTENANCE", FatUtility::VAR_INT, 0) . ';
    var extendEditorJs = ' . $extendEditorJs . ';
    var themeActive = ' . $themeActive . ';
    var currencySymbolLeft = "' . $currencySymbolLeft . '";
    var currencySymbolRight = "' . $currencySymbolRight . '";
    var isUserDashboard = "' . $isUserDashboard . '";
    var className = "' . FatApp::getController() . '";
    var actionName = "' . FatApp::getAction() . '";
    if( CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES <= 0  ){
        CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES = 3;
    }';
    if (FatApp::getConfig("CONF_ENABLE_ENGAGESPOT_PUSH_NOTIFICATION", FatUtility::VAR_STRING, '')) {
        echo FatApp::getConfig("CONF_ENGAGESPOT_PUSH_NOTIFICATION_CODE", FatUtility::VAR_STRING, '');
        if (UserAuthentication::getLoggedUserId(true) > 0) { ?>
Engagespot.init()
Engagespot.identifyUser('YT_<?php echo UserAuthentication::getLoggedUserId(); ?>');
<?php }
    }

    if (Message::getMessageCount() || Message::getErrorCount() || Message::getDialogCount() || Message::getInfoCount()) { ?>
    (function() {
        if (CONF_AUTO_CLOSE_SYSTEM_MESSAGES == 1) {
            var time = CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES * 1000;
            setTimeout(function() {
                $.systemMessage.close();
            }, time);
        }
    })();
<?php }

    $pixelId = FatApp::getConfig("CONF_FACEBOOK_PIXEL_ID", FatUtility::VAR_STRING, '');
    if ('' != $pixelId) { ?>
    ! function(f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function() {
            n.callMethod ?
                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
    }(window, document, 'script',
        'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo $pixelId; ?>');
fbq('track', 'PageView');
var fbPixel = true;
<?php } ?>
</script>
<?php if ('' !=  $pixelId) {  ?>
<noscript>
    <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?php echo $pixelId; ?>&ev=PageView&noscript=1" />
</noscript>
<?php }

if (FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_GOOGLE_TAG_MANAGER_HEAD_SCRIPT", FatUtility::VAR_STRING, '');
}
if (FatApp::getConfig("CONF_HOTJAR_HEAD_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_HOTJAR_HEAD_SCRIPT", FatUtility::VAR_STRING, '');
}
if (FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '');
}
if (isset($layoutTemplate) && $layoutTemplate != '') { ?>
<link rel="stylesheet"
    href="<?php echo UrlHelper::generateUrl('ThemeColor', $layoutTemplate, array($layoutRecordId)); ?>">
<?php }