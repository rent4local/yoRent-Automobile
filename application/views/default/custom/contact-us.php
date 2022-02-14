<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$contactFrm->setFormTagAttribute('class', 'form form--normal');
$contactFrm->setFormTagAttribute('action', UrlHelper::generateUrl('Custom', 'contactSubmit'));
$contactFrm->developerTags['colClassPrefix'] = 'col-md-';
$contactFrm->developerTags['fld_default_col'] = 6;
$fld = $contactFrm->getField('phone');
$fld->developerTags['col'] = 12;
$fld = $contactFrm->getField('message');
$fld->developerTags['col'] = 12;

$fld = $contactFrm->getField('htmlNote');
if (null != $fld) {
    $fld->developerTags['col'] = 12;
}

$fld = $contactFrm->getField('btn_submit');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
$fld->developerTags['col'] = 12;
?>
<script>
    events.contactUs();
</script>
<div id="body" class="body">
    <div class="bg-brand-light py-4">
        <div class="container">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8">
                    <div class="section-head section--white--head justify-content-center mb-0">
                        <div class="section__heading text-center">
                            <h1><?php echo Labels::getLabel('LBL_Get_in_Touch', $siteLangId); ?>
                            </h1>
                            <p><?php echo Labels::getLabel('LBL_Get_in_Touch_Txt', $siteLangId); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-auto col-sm-auto"></div>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="bg-gray rounded p-4">
                                <?php echo $contactFrm->getFormHtml(); ?>
                            </div>

                        </div>
                        <div class="col-md-5">
                            <div class="border rounded p-4 h-100">
                                <div class="cms">
                                    <h6><strong><?php echo Labels::getLabel('LBL_General_Inquiry', $siteLangId); ?></strong>
                                    </h6>
                                    <p class=""><?php echo FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ''); ?>
                                        <br><?php echo Labels::getLabel('LBL_24_a_day_7_days_week', $siteLangId); ?>
                                    </p>

                                    <div class="divider"></div>

                                    <h6><strong><?php echo Labels::getLabel('LBL_Fax', $siteLangId); ?></strong> </h6>
                                    <p class=""><?php echo FatApp::getConfig('CONF_SITE_FAX', FatUtility::VAR_STRING, ''); ?>
                                        <br><?php echo Labels::getLabel('LBL_24_a_day_7_days_week', $siteLangId); ?>
                                    </p>

                                    <div class="divider"></div>

                                    <h6><strong><?php echo Labels::getLabel('LBL_Address', $siteLangId); ?></strong> </h6>
                                    <p class=""><?php echo nl2br(FatApp::getConfig('CONF_ADDRESS_' . $siteLangId, FatUtility::VAR_STRING, '')); ?>
                                    </p>
                                </div>

                                <?php $this->includeTemplate('_partial/footerSocialMedia.php'); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="g-map">
        <?php if (FatApp::getConfig('CONF_MAP_IFRAME_CODE', FatUtility::VAR_STRING, '') != '') {
            echo FatApp::getConfig('CONF_MAP_IFRAME_CODE', FatUtility::VAR_STRING);
        } ?>
    </section>
</div>
<?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey)) { ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>