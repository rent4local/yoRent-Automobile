<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$showLogInLink = isset($showLogInLink) ? $showLogInLink : true;
$onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : false;

if (isset($signUpWithPhone) && 0 < $signUpWithPhone) {
    $onSubmitFunctionName = 'return registerWithPhone';
}

$registerFrm->setFormTagAttribute('action', UrlHelper::generateUrl('GuestUser', 'register'));

if ($onSubmitFunctionName) {
    $registerFrm->setValidatorJsObjectName('SignUpValObj');
    $registerFrm->setFormTagAttribute('onsubmit', $onSubmitFunctionName . '(this, SignUpValObj); return(false);');
}
?>
<?php
$registerFrm->setFormTagAttribute('class', 'form');
$fldSubmit = $registerFrm->getField('btn_submit');
$fldSubmit->addFieldTagAttribute('class', 'btn btn-brand btn-block');
$registerFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$registerFrm->developerTags['fld_default_col'] = 12;

echo $registerFrm->getFormTag();
?>
<div class="row">
    <div class="col-md-6">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('user_name'); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('user_username'); ?></div>
            </div>
        </div>
    </div>
</div>
<?php if (isset($signUpWithPhone) && 0 < $signUpWithPhone) { ?>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('user_phone'); ?></div>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('user_email'); ?></div>
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="row">
    <div class="col-md-6">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('user_password'); ?></div>
                <span
                    class="form-text text-muted"><?php echo sprintf(Labels::getLabel('LBL_Example_password', $siteLangId), 'User@123') ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover"><?php echo $registerFrm->getFieldHtml('password1'); ?></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover">
                    <label class="checkbox">
                        <?php
                        $fld = $registerFrm->getFieldHTML('agree');
                        $fld = str_replace("<label >", "", $fld);
                        $fld = str_replace("</label>", "", $fld);
                        echo $fld;
                    ?>

                        <?php echo sprintf(
                        Labels::getLabel('LBL_I_agree_to_the_terms_conditions', $siteLangId),
                        "<a class='link' target='_blank' href='$termsAndConditionsLinkHref'>" . Labels::getLabel('LBL_TERMS_AND_CONDITIONS', $siteLangId) . '</a>'
                    ); ?>
                    </label>
                    <?php if ($registerFrm->getField('user_newsletter_signup')) { ?>
                    <span class="gap-custom"></span>
                    <label class="checkbox">
                        <?php
                        $fld = $registerFrm->getFieldHTML('user_newsletter_signup');
                        $fld = str_replace("<label >", "", $fld);
                        $fld = str_replace("</label>", "", $fld);
                        echo $fld;
                        ?>

                    </label>
                    <?php }
                    if ((!isset($signUpWithPhone) || 1 > $signUpWithPhone) && $registerFrm->getField('isCheckOutPage')) {
                        echo $registerFrm->getFieldHTML('isCheckOutPage');
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="field-wraper">
                <div class="field_cover">
                    <?php echo $registerFrm->getFieldHTML('user_id') , $registerFrm->getFieldHTML('btn_submit'); ?>
                    <?php echo (isset($signUpWithPhone) && 0 < $signUpWithPhone) ? $registerFrm->getFieldHTML('signUpWithPhone') : ''; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<?php echo $registerFrm->getExternalJs(); ?>