<div class="form-side-inner">
    <?php
    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
    $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId, true);
    
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
    <a class="form-item_logo" href="<?php echo UrlHelper::generateFullFileUrl(); ?>">
        <img class="<?php echo $logoClass;?>" src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
    </a>

    <div class="card-sign">
        <div class="card-sign_head">
            <div class="section-head">
                <div class="section__heading otp-heading">
                    <h2 class="title">
                        <?php echo Labels::getLabel('LBL_Sign_Up', $siteLangId);?>

                    </h2>

                    <?php if (isset($registerdata['signUpWithPhone']) && true === $smsPluginStatus) {
                            if (0 == $registerdata['signUpWithPhone']) { ?>
                    <a class="link otp-link" href="javaScript:void(0)"
                        onClick="signUpWithPhone()"><?php echo Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $siteLangId); ?></a>
                    <?php } else { ?>
                    <a class="link otp-link" href="javaScript:void(0)"
                        onClick="signUpWithEmail()"><?php echo Labels::getLabel('LBL_USE_EMAIL_INSTEAD', $siteLangId); ?></a>
                    <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="card-sign_body">
            <?php $this->includeTemplate('guest-user/registerationFormTemplate.php', $registerdata, false); ?></div>
        <div class="card-sign_foot"></div>
    </div>


</div>