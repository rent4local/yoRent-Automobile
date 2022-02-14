<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($includeEditor) && $includeEditor == true) {
	$extendEditorJs	= 'true';
} else {
	$extendEditorJs	= 'false';
}
if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
	$themeActive = 'true';
} else {
	$themeActive = 'false';
}
$commonHeadData = array(
	'siteLangId' => $siteLangId,
	'siteLangCode' => $siteLangCode,
	'controllerName' => $controllerName,
	'action' => $action,
	'jsVariables' => $jsVariables,
	'extendEditorJs' => $extendEditorJs,
	'themeDetail' => $themeDetail,
	'themeActive' => $themeActive,
	'currencySymbolLeft' => $currencySymbolLeft,
	'currencySymbolRight' => $currencySymbolRight,
	'isUserDashboard' => $isUserDashboard,
	'canonicalUrl' => isset($canonicalUrl) ? $canonicalUrl : '',
);
if (isset($layoutTemplate) && $layoutTemplate != '') {
	$commonHeadData['layoutTemplate'] = $layoutTemplate;
	$commonHeadData['layoutRecordId'] = $layoutRecordId;
}
if (isset($socialShareContent) && $socialShareContent != '') {
	$commonHeadData['socialShareContent'] = $socialShareContent;
}
if (isset($includeEditor) && $includeEditor == true) {
	$commonHeadData['includeEditor'] = $includeEditor;
}
$this->includeTemplate('_partial/header/commonHeadTop.php', $commonHeadData, false);
/* This is not included in common head, because, commonhead file not able to access the $this->Controller and $this->action[ */
echo $this->writeMetaTags();
/* ] */
$this->includeTemplate('_partial/header/commonHeadMiddle.php', $commonHeadData, false);
/* This is not included in common head, because, if we are adding any css/js from any controller then that file is not included[ */
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);
/* ] */

$this->includeTemplate('_partial/header/commonHeadBottom.php', $commonHeadData, false);
?>
<div class="wrapper">
    <div id="header" class="header-advertiser">
        <?php if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
			$this->includeTemplate('restore-system/top-header.php');
		} ?>
        <?php /* <div class="top-bar">
			<div class="container">
				<div class="row">
					<div class="col-lg-4 col-xs-6 d-none d-xl-block d-lg-block hide--mobile">
						<div class="slogan"><?php // Labels::getLabel('LBL_Multi-vendor_Ecommerce_Marketplace_Solution', $siteLangId); ?>
    </div>
</div>
<div class="col-lg-8 col-xs-12">
    <div class="short-links">
        <ul>
            <?php $this->includeTemplate('_partial/headerTopNavigation.php'); ?>
            <?php $this->includeTemplate('_partial/headerLanguageArea.php'); ?>
        </ul>
    </div>
</div>
</div>
</div>
</div> */ ?>
<div class="top-head">
    <div class="container">
        <div class="logo-bar">
            <div class="logo logo-supplier">
                <?php
                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
                $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId, true);
                $sizeType = 'CUSTOM';
                if ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_RECTANGULAR) {
                    $sizeType = '16X9';
                } elseif($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_SQUARE) {
                    $sizeType = '1X1';
                }
                
                $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
                $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId, $sizeType), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                ?>
                <a href="<?php echo UrlHelper::generateUrl(); ?>">
                    <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" />
                </a>
            </div>

            <div class="short-links">
                <ul>
                    <li>
                        <a href="javascript:void(0)" class="header__user sign-in sign-in-popup-js header__user">
                            <i class="icn icn-signin">
                                <svg class="svg">
                                    <use
                                        xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-header.svg#sign_in">
                                    </use>
                                </svg>
                            </i>
                            <span
                                class="hide-sm"><?php echo Labels::getLabel('Lbl_Login_/_Sign_Up', $siteLangId); ?></span>
                        </a>
                    </li>
                    <?php /* $this->includeTemplate('_partial/headerTopNavigation.php'); */ ?>
                    <?php /* $this->includeTemplate('_partial/headerLanguageArea.php'); */ ?>
                </ul>
            </div>
            <?php /* <div class="yk-login--wrapper" id="yk-login--wrapper"
                        data-close-on-click-outside="yk-login--wrapper">

                        <?php $this->includeTemplate('_partial/seller/sellerHeaderLoginForm.php', $loginData, false); ?>
        </div> */ ?>
    </div>
</div>
</div>
</div>