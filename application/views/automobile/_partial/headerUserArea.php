<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;
if (!$isUserLogged) {
    if (UserAuthentication::isGuestUserLogged()) { ?>
    <div class="header__account">
    <div class="dropdown">
        <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_Account', $siteLangId); ?>" class="dropdown-toggle no-after" data-toggle="dropdown"><span
                class="icn icn-txt"><?php echo Labels::getLabel('LBL_Hi,', $siteLangId) . ' ' . User::getAttributesById(UserAuthentication::getLoggedUserId(), "user_name"); ?></span></a>
        <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim"
            aria-labelledby="dropdownMenuButton">
            <ul class="nav nav-block">
                <?php $userName = User::getAttributesById(UserAuthentication::getLoggedUserId(), "user_name"); ?>
                <li class="nav__item">
                    <a class="dropdown-item nav__link"
                        href="<?php echo UrlHelper::generateUrl('account', 'profileInfo'); ?>">
                        <?php echo Labels::getLabel('LBL_Hi,', $siteLangId) . ' ' . $userName; ?>
                    </a>
                </li>
                <li class="nav__item logout"><a class="dropdown-item nav__link"
                        data-org-url="<?php echo UrlHelper::generateUrl('GuestUser', 'logout', array(), '', null, false, $getOrgUrl); ?>"
                        href="<?php echo UrlHelper::generateUrl('GuestUser', 'logout'); ?>"><?php echo Labels::getLabel('LBL_Logout', $siteLangId); ?>
                    </a></li>
            </ul>
        </div>
    </div>
    </div>
    <?php } else { ?>
    <div class="user__login">
        <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_Login', $siteLangId); ?>" class="item-circle sign-in sign-in-popup-js header__user">
            <?php echo Labels::getLabel('LBL_Login', $siteLangId); ?>
            <i class="icn icn-user">
                <svg class="svg">
                    <use
                        xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#user-login">
                    </use>
                </svg>
            </i>
        </a>
    </div>
    <?php }
    } else {
                    $userActiveTab = false;
                    if (User::canViewSupplierTab() && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'S')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Seller');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Seller', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewBuyerTab()  && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'B')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Buyer');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Buyer', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewAdvertiserTab() && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'Ad')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Advertiser');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Advertiser', '', array(), '', null, false, $getOrgUrl);
                    } elseif (User::canViewAffiliateTab()  && (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab']) && $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] == 'AFFILIATE')) {
                        $userActiveTab = true;
                        $dashboardUrl = UrlHelper::generateUrl('Affiliate');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Affiliate', '', array(), '', null, false, $getOrgUrl);
                    }
                    if (!$userActiveTab) {
                        $dashboardUrl = UrlHelper::generateUrl('Account');
                        $dashboardOrgUrl = UrlHelper::generateUrl('Account', '', array(), '', null, false, $getOrgUrl);
                    } ?>

    <div class="dropdown">
        <?php if (isset($isUserDashboard) && ($isUserDashboard)) { ?>
        <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_Account', $siteLangId); ?>" class="dropdown-toggle no-after" data-display="static" data-toggle="dropdown">
            <img class="my-account__avatar" src="<?php echo $profilePicUrl; ?>" alt="">
        </a>
        <?php } else { ?>
        <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_Account', $siteLangId); ?>" class="dropdown-toggle no-after" data-display="static" data-toggle="dropdown">
            <img class="my-account__avatar" src="<?php echo $profilePicUrl; ?>" alt="">
        </a>
        <?php } ?>
        <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim"
            aria-labelledby="dropdownMenuButton">
            <ul class="nav nav-block">
                <?php $userName = User::getAttributesById(UserAuthentication::getLoggedUserId(), "user_name"); ?>
                <li class="nav__item">
                    <a class="dropdown-item nav__link"
                        href="<?php echo UrlHelper::generateUrl('account', 'profileInfo'); ?>">
                        <?php echo Labels::getLabel('LBL_Hi,', $siteLangId) . ' ' . $userName; ?>
                    </a>
                </li>
                <li
                    class="nav__item <?php if (isset($isUserDashboard) && ($isUserDashboard)) { ?> d-block d-md-none <?php } ?>">
                    <a class="dropdown-item nav__link" data-org-url="<?php echo $dashboardOrgUrl; ?>"
                        href="<?php echo $dashboardUrl; ?>"><?php echo Labels::getLabel("LBL_Dashboard", $siteLangId); ?></a>
                </li>
                <li class="nav__item logout"><a class="dropdown-item nav__link"
                        data-org-url="<?php echo UrlHelper::generateUrl('GuestUser', 'logout', array(), '', null, false, $getOrgUrl); ?>"
                        href="<?php echo UrlHelper::generateUrl('GuestUser', 'logout'); ?>"><?php echo Labels::getLabel('LBL_Logout', $siteLangId); ?>
                    </a></li>
            </ul>
        </div>
    </div>
<?php } ?>