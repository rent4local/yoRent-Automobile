<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
if (0 < $withPhone) {
    $frm->setFormTagAttribute('onsubmit', 'getOtpForm(this); return(false);');
}
?>
<div id="body" class="body forgotPwForm">

    <div class="full-page-from">
        <div class="full-page-from-block">
            <div class="full-page-from-block_head">
                <?php
                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
                $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId, true);
                
                $sizeType = 'CUSTOM';
                $extraClass = 'logo-custom';

                if ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_RECTANGULAR) {
                    $sizeType = '16X9';
                    $extraClass = '';
                } elseif($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_SQUARE) {
                    $sizeType = '1X1';
                    $extraClass = '';
                }
                
                $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
                $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId, $sizeType), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                
                ?>
                <a class="form-item_logo" href="<?php echo UrlHelper::generateFullFileUrl(); ?>">
                    <img class="<?php echo $extraClass;?>" src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
                </a>
            </div>

            <div class="card" id="otpFom">
                <div class="card-header">
                    <h1><?php echo Labels::getLabel('LBL_Forgot_Password', $siteLangId); ?></h1>
                    <p>
                        <?php if (1 > $withPhone) {
                            echo Labels::getLabel('LBL_Forgot_Password_Msg', $siteLangId);
                        } else {
                            echo Labels::getLabel('LBL_RECOVER_PASSWORD_FORM_MSG', $siteLangId);
                        } ?>                        
                    </p>
                </div>
                <div class="card-body">
                    <?php
                    $frm->setFormTagAttribute('class', 'form form--normal');
                    $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                    $frm->developerTags['fld_default_col'] = 12;

                    $frm->setFormTagAttribute('id', 'frmPwdForgot');
                    $frm->setFormTagAttribute('autocomplete', 'off');
                    $frm->setValidatorJsObjectName('forgotValObj');
                    $frm->setFormTagAttribute('action', UrlHelper::generateUrl('GuestUser', 'forgotPassword'));
                    $btnFld = $frm->getField('btn_submit');
                    $btnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
                    if (1 > $withPhone) {
                        $frmFld = $frm->getField('user_email_username');
                        $frmFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Username_or_email', $siteLangId));
                    } else {
                        $frmFld = $frm->getField('user_phone');
                    }
                    $frmFld->developerTags['noCaptionTag'] = true;

                    $frmFld = $frm->getField('btn_submit');
                    $frmFld->developerTags['noCaptionTag'] = true;
                    echo $frm->getFormHtml(); ?>
                    <?php if (isset($smsPluginStatus) && true === $smsPluginStatus) {
                            if (isset($withPhone) && 1 > $withPhone) { ?>
                                <a href="javaScript:void(0)" onClick="forgotPwdForm(<?php echo applicationConstants::YES; ?>)" class="link">
                                    <?php echo Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $siteLangId); ?>
                                </a>
                            <?php } else { ?>
                                <a href="javaScript:void(0)" onClick="forgotPwdForm(<?php echo applicationConstants::NO; ?>)" class="link">
                                    <?php echo Labels::getLabel('LBL_USE_EMAIL_INSTEAD', $siteLangId); ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                </div>
                <div class="card-footer">
                    <ul class="other-links">
                    <li >
                    <?php echo Labels::getLabel('LBL_Back_to', $siteLangId); ?>
                    <a href="<?php echo UrlHelper::generateUrl('GuestUser', 'loginForm'); ?>" class="link">
                            <?php echo Labels::getLabel('LBL_login', $siteLangId); ?>
                        </a>
                    </li>                           
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>


<?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey)) { ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>