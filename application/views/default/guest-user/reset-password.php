<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">

    <div class="full-page-from">    
        <div class="full-page-from-block">
            <div class="full-page-from-block_head">
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
                <a class="form-item_logo" href="<?php echo UrlHelper::generateFullFileUrl(); ?>">
                    <img src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <h1><?php echo Labels::getLabel('LBL_Reset_Password', $siteLangId); ?></h1>
                    <p>
                        <?php echo Labels::getLabel("LBL_Don't_worry,happens_to_best_of_us.", $siteLangId);?>                      
                    </p>

                </div>
                <div class="card-body">
                    <?php
                    $frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
                    $frm->setFormTagAttribute('class', 'form');
                    $frm->setValidatorJsObjectName('resetValObj');
                    $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                    $frm->developerTags['fld_default_col'] = 12;
                    $frm->setFormTagAttribute('action', '');
                    $btnFld = $frm->getField('user_name');
                    $btnFld->developerTags['noCaptionTag'] = true;
                    $btnFld = $frm->getField('new_pwd');
                    $btnFld->developerTags['noCaptionTag'] = true;
                    $btnFld = $frm->getField('confirm_pwd');
                    $btnFld->developerTags['noCaptionTag'] = true;
                    $btnFld = $frm->getField('btn_submit');
                    $btnFld->developerTags['noCaptionTag'] = true;
                    $btnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
                    if(empty($user_password)){
                        $btnFld->value = Labels::getLabel('LBL_SET_PASSWORD', $siteLangId);   
                    }                    
                    $frm->setFormTagAttribute('onSubmit', 'resetpwd(this, resetValObj); return(false);');
                    $passFld = $frm->getField('new_pwd');
                    $passFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_NEW_PASSWORD', $siteLangId));
                    $confirmFld = $frm->getField('confirm_pwd');
                    $confirmFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_CONFIRM_NEW_PASSWORD', $siteLangId));
                    $fld = $frm->getField('user_name');
                    $fld->setFieldTagAttribute('disabled', 'disabled');
                    $fld->value = $credential_username;                   
                    echo $frm->getFormHtml();
                    ?>
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
                    <!-- <section class="section">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-6 <?php echo (empty($pageData)) ? '' : '';?>">
                                    <div class="section-head">
                                        <div class="section__heading mb-3">
                                            <h3><?php echo empty($user_password) ? Labels::getLabel('LBL_SET_PASSWORD', $siteLangId): Labels::getLabel('LBL_Reset_Password', $siteLangId);?></h3>
                                            <p><?php echo empty($user_password) ? Labels::getLabel('LBL_SET_PASSWORD_MSG', $siteLangId) : Labels::getLabel('LBL_Reset_Password_Msg', $siteLangId); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                    $frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
                                    $frm->setFormTagAttribute('class', 'form');
                                    $frm->setValidatorJsObjectName('resetValObj');
                                    $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                                    $frm->developerTags['fld_default_col'] = 12;
                                    $frm->setFormTagAttribute('action', '');
                                    $btnFld = $frm->getField('btn_submit');
                                    $btnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
                                    if(empty($user_password)){
                                        $btnFld->value = Labels::getLabel('LBL_SET_PASSWORD', $siteLangId);   
                                    }                    
                                    $frm->setFormTagAttribute('onSubmit', 'resetpwd(this, resetValObj); return(false);');
                                    $passFld = $frm->getField('new_pwd');
                                    $passFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_NEW_PASSWORD', $siteLangId));
                                    $confirmFld = $frm->getField('confirm_pwd');
                                    $confirmFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_CONFIRM_NEW_PASSWORD', $siteLangId));
                                    $fld = $frm->getField('user_name');
                                    $fld->setFieldTagAttribute('disabled', 'disabled');
                                    $fld->value = $credential_username;                   
                                    echo $frm->getFormHtml();
                                    ?>
                                </div>
                                <?php if (!empty($pageData)) {
                                            $this->includeTemplate('_partial/GuestUserRightPanel.php', $pageData, false);
                                        } ?>
                            </div>
                        </div>
                    </section> -->
            </div>
        </div>
    
    <?php if (!empty($pageData)) {
       // $this->includeTemplate('_partial/GuestUserRightPanel.php', $pageData, false);
    } ?>
    </div>
</div>