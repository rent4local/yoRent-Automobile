<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered" role="document" id="guest-user-popup">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Guest_User', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div id="sign-in">
                <div class="login-wrapper">
                    <div class="form-side">
                        <!-- <div class="section-head  section--head--center">
                            <div class="section__heading">
                                <h2><?php echo Labels::getLabel('LBL_Guest_User', $siteLangId); ?></h2>
                            </div>
                        </div> -->
                        <?php
                        $frm->setFormTagAttribute('class', 'form form-checkout-login');
                        $frm->setFormTagAttribute('name', 'frmGuestLogin');
                        $frm->setFormTagAttribute('id', 'frmGuestLogin');
                        $frm->setValidatorJsObjectName('guestLoginFormObj');

                        $frm->setFormTagAttribute('onsubmit', 'return guestUserLogin(this, guestLoginFormObj);');
                        $frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-12 col-xs-';
                        $frm->developerTags['fld_default_col'] = 12;

                        /* $fldSpace = $frm->getField('space');
            $fldSpace->value ='<a href="#" class="forgot">&nbsp;</a>'; */

                        $fldSubmit = $frm->getField('btn_submit');
                        $fldSubmit->addFieldTagAttribute("class", "btn btn-brand btn-block");
                        $fldSubmit->developerTags['noCaptionTag'] = true;
                        echo $frm->getFormHtml(); ?>
                        <div class="row justify-content-center">
                            <div class="col-auto text-center">
                                <a class="link" href="<?php echo UrlHelper::generateUrl('GuestUser', 'loginForm', array(applicationConstants::YES)); ?>"><?php echo sprintf(Labels::getLabel('LBL_Not_Register_Yet?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId)); ?></a>
                            </div>
                            <div class="col-auto text-center">
                                <a class="link" href="javascript:void(0)" onclick="openSignInForm(true)"><?php echo sprintf(Labels::getLabel('LBL_Existing_User?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId)); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>