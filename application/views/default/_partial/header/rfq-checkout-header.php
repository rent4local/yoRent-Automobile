<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
    $this->includeTemplate('restore-system/top-header.php');
    $this->includeTemplate('restore-system/page-content.php');
} ?>
<div class="checkout">
    <header class="header-checkout" data-header="" >
        <div class="container header-checkout_inner">
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
            <a class="logo-checkout-main" href="<?php echo UrlHelper::generateUrl(); ?>"><img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?> data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?> src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>"></a>
            
            <?php if ($controllerName == 'rfqcheckout') { ?>
            <div class="checkout-progress">
                <div class="progress-track checkout-flow-js"></div>
                <div id="step1" class="progress-step checkoutNav-js billing-js"><?php echo Labels::getLabel('LBL_Billing', $siteLangId); ?>
                </div>
                <div id="step3"  class="progress-step checkoutNav-js verification-js"><?php echo Labels::getLabel('LBL_Verify_cart', $siteLangId); ?></div>
                <div id="step4" class="progress-step checkoutNav-js payment-js"><?php echo Labels::getLabel('LBL_Payment', $siteLangId); ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </header>