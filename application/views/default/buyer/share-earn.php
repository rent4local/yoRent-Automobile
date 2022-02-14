<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$sharingFrm->addFormTagAttribute('class', 'form form--inline form--fly');
$sharingFrm->addFormTagAttribute('onsubmit', 'sendMailShareEarn(this);return false;');
$sharingFrm->developerTags['colClassPrefix'] = 'col-xs-12 col-md-';
$sharingFrm->developerTags['fld_default_col'] = 12;
$submitFld = $sharingFrm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
$submitFld->developerTags['col'] = 2;

$email = $sharingFrm->getField('email');
$email->setFieldTagAttribute('class', 'emailAddressJs');
$email->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_EMAIL_ADDRESS', $siteLangId));

$this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Share_and_Earn', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-body">
                    <div class="invite-box">
                        <div class="share-earn">
                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/share-earn.png" alt="">
                            <h2>
                                <?php echo Labels::getLabel('LBL_INVITE_YOUR_FRIENDS', $siteLangId); ?>
                            </h2>
                            <p>
                                <?php echo Labels::getLabel('LBL_INVITE_YOUR_FRIENDS_TO_JOIN_YORENT_AND_EARN_ONCE_THEY_SIGNUP.', $siteLangId); ?>
                            </p>
                        </div>
                        <div class="invite-by-email">
                            <?php echo $sharingFrm->getFormTag(); ?>
                            <div class="form-group">
                                <?php echo $sharingFrm->getFieldHTML('email'); ?> 
                                <button type="submit" disabled="disabled" class="btn-fly submitBtnJs">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#submitfly">
                                        </use>
                                    </svg>
                                </button>
                            </div>
                            </form>
                            <?php echo $sharingFrm->getExternalJS(); ?>
                        </div>
                        <ul class="social-invites ">
                            <li>
                                <a href="javascript:void(0);" title="<?php echo Labels::getLabel('MSG_COPY_TO_CLIPBOARD', $siteLangId); ?>" onclick="copy($(this))" data-url="<?php echo $referralTrackingUrl; ?>" data-val="" data-code="" class="btn">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#icon_link">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_FACEBOOK', $siteLangId); ?>" class="share-network-facebook st-custom-button" data-network="facebook" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-facebook" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-facebook">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_LINKEDIN', $siteLangId); ?>" class="share-network-linkedin st-custom-button" data-network="linkedin" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-linkedin" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-linkedin">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_REDDIT', $siteLangId); ?>" class="share-network-reddit st-custom-button" data-network="reddit" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-reddit" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-reddit">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_SKYPE', $siteLangId); ?>" class="share-network-skype st-custom-button" data-network="skype" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-skype" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-skype">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_TELEGRAM', $siteLangId); ?>" class="share-network-telegram st-custom-button" data-network="telegram" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-telegram" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-telegram">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_TWITTER', $siteLangId); ?>" class="share-network-twitter st-custom-button" data-network="twitter" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-twitter" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-twitter">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)"  title="<?php echo Labels::getLabel('MSG_SHARE_ON_WHATSAPP', $siteLangId); ?>" class="share-network-whatsapp st-custom-button" data-network="whatsapp" data-url="<?php echo $referralTrackingUrl; ?>">
                                    <span class="icon">
                                        <i class="svg--icon">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-whatsapp" href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/share.svg#share-whatsapp">
                                                </use>
                                            </svg>
                                        </i>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src =
            "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo FatApp::getConfig("CONF_FACEBOOK_APP_ID", FatUtility::VAR_STRING, ''); ?>";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function facebook_redirect(response_token) {
        FB.ui({
            method: 'share_open_graph',
            action_type: 'og.likes',
            action_properties: JSON.stringify({
                object: {
                    'og:url': "<?php echo $referralTrackingUrl ?>",
                    'og:title': "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId")) ?>",
                    'og:description': "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId")) ?>",
                    'og:image': "<?php echo UrlHelper::generateFullUrl('image', 'socialFeed', array($siteLangId, ''), CONF_WEBROOT_FRONTEND) ?>",
                }
            })
        }, function(response) {
            if (response !== null && typeof response.post_id !== 'undefined') {
                $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
                /* $("#fb_ajax").html(langLbl.thanksForSharing); */
            }
        });
    }

    function twitter_shared(name) {
        $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
        /* $("#twitter_ajax").html(langLbl.thanksForSharing); */
    }
</script>
<script type="text/javascript">
    var newwindow;
    var intId;

    function twitter_login() {
        var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
            screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
            outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
            outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
            width = 800,
            height = 600,
            left = parseInt(screenX + ((outerWidth - width) / 2), 10),
            top = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
            features = ('width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
        newwindow = window.open('<?php echo $twitterUrl; ?>', 'Login_by_twitter', features);
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }
</script>

<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>