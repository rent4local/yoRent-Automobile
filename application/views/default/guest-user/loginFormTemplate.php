<?php
$showSignUpLink = isset($showSignUpLink) ? $showSignUpLink : true;
$onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';

$loginFrm->setFormTagAttribute('class', 'form');
$loginFrm->setFormTagAttribute('name', 'formLoginPage');
$loginFrm->setFormTagAttribute('id', 'formLoginPage');
$loginFrm->setValidatorJsObjectName('loginFormObj');

$loginFrm->setFormTagAttribute('onsubmit', 'return ' . $onSubmitFunctionName . '(this, loginFormObj);');
$loginFrm->developerTags['fld_default_col'] = 12;
$loginFrm->developerTags['colClassPrefix'] = 'col-md-';
$remembermeField = $loginFrm->getField('remember_me');
$remembermeField->setWrapperAttribute("class", "rememberme-text");
$remembermeField->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$remembermeField->developerTags['col'] = 6;
$remembermeField->developerTags['cbHtmlAfterCheckbox'] = '';
$fldforgot = $loginFrm->getField('forgot');
$fldforgot->value = '<a href="' . UrlHelper::generateUrl('GuestUser', 'forgotPasswordForm') . '"
    class="">' . Labels::getLabel('LBL_Forgot_Password?', $siteLangId) . '</a>';
$fldforgot->developerTags['col'] = 6;
$fldSubmit = $loginFrm->getField('btn_submit');
$fldSubmit->addFieldTagAttribute("class", "btn btn-brand btn-block");
?>

<div class="modal-dialog modal-dialog-centered" role="document" id="sign-in">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Login', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="login-wrapper">
                <?php echo $loginFrm->getFormTag(); ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $loginFrm->getFieldHtml('username'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $loginFrm->getFieldHtml('password'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6 col-6">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $loginFrm->getFieldHtml('remember_me'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-6">
                        <div class="field-set">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $loginFrm->getFieldHtml('btn_submit'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>

                <?php echo $loginFrm->getExternalJS(); ?>
                <div class="text-center">
                    <?php if (isset($smsPluginStatus) && true === $smsPluginStatus) { ?>
                        <a class="link-brand" href="javaScript:void(0)" data-form="formLoginPage" onClick="signInWithPhone(this, true)">
                            <?php echo Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $siteLangId); ?>
                        </a>
                    <?php } ?>
                </div>
                <?php if (!empty($socialLoginApis) && 0 < count($socialLoginApis)) { ?>
                    <div class="other-option">
                        <div class="or-divider">
                            <span class="or">
                                <?php echo Labels::getLabel('LBL_Or', $siteLangId); ?>
                            </span>
                        </div>
                        <ul class="buttons-list">
                            <?php foreach ($socialLoginApis as $plugin) { ?>
                                <li class="buttons-list-item">
                                    <a class="buttons-list-link btn--<?php echo $plugin['plugin_code']; ?>" href="<?php echo UrlHelper::generateUrl($plugin['plugin_code']); ?>">
                                        <span class="buttons-list-wrap">
                                            <span class="buttons-list-icon">
                                                <img class="svg" width="30" height="30" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/social-icons/<?php echo $plugin['plugin_code']; ?>.svg">
                                            </span>
                                            <?php echo $plugin['plugin_name']; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>

                    </div>
                <?php } ?>

                <?php if ($showSignUpLink) { ?>
                    <ul class="other-links">
                        <li><?php echo $loginFrm->getFieldHtml('forgot'); ?></li>
                        <li>
                            <a href="<?php echo UrlHelper::generateUrl('GuestUser', 'loginForm', array(applicationConstants::YES)); ?>">
                                <?php echo sprintf(Labels::getLabel('LBL_Not_Registered_Yet?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId)); ?>
                            </a>
                        </li>
                        <?php if (isset($includeGuestLogin) && 'true' == $includeGuestLogin) { ?>
                            <li>
                                <a href="javascript:void(0)" onclick="guestUserFrm()"><?php echo sprintf(Labels::getLabel('LBL_Guest_Checkout?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId)); ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

            </div>

        </div>

    </div>
</div>