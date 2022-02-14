<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) {
    if (FatApp::getConfig('CONF_NEWSLETTER_SYSTEM') == applicationConstants::NEWS_LETTER_SYSTEM_MAILCHIMP) {
        $frm->setFormTagAttribute('onSubmit', 'setUpNewsLetter(this); return false;');
        $emailFld = $frm->getField('email');
        $emailFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Enter_Your_Email_Address', $siteLangId));
        $emailFld->setFieldTagAttribute('class', "subscribe");
        $submitBtn = $frm->getField('btnSubmit');
        $submitBtn->setFieldTagAttribute('class', "link-more"); ?>
        <?php echo $frm->getFormTag(); ?>
        <div class="newsletter-form-wrapper">
            <div class="newsletter-form">
                <?php echo $frm->getFieldHtml('email'); ?>
            </div>
            <div id="customErrorJs"></div>
        </div>
        </form>
        <?php echo $frm->getExternalJS(); ?>
    <?php } elseif (FatApp::getConfig('CONF_NEWSLETTER_SYSTEM') == applicationConstants::NEWS_LETTER_SYSTEM_AWEBER) { ?>
        <span class='d-none aweber-js'><?php echo FatApp::getConfig('CONF_AWEBER_SIGNUP_CODE'); ?></span>
        <div class="d-flex justify-content-center">
            <a href="javascript:void(0)" class="btn btn-brand" onclick="awebersignup();">
                <?php echo Labels::getLabel('LBL_NEWSLETTER_SIGNUP_AWEBER', $siteLangId); ?>
            </a>
        </div>
    <?php }
} else { ?>
    <div class="gap"></div>
<?php } ?>
<script type="text/javascript">
    (function() {
        setUpNewsLetter = function(frm) {
            if (!$(frm).validate()) return;
            events.newsLetterSubscription();
            var data = fcom.frmData(frm);
            fcom.updateWithAjax(fcom.makeUrl('MyApp', 'setUpNewsLetter'), data, function(t) {
                if (t.status) {
                    frm.reset();
                }
            });
        };
    })();
</script>