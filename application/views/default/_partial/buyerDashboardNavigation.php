<?php
$controller = strtolower($controller);
$action = strtolower($action);
?>
<sidebar class="sidebar no-print dashboard-sidebar--js">
    <div class="logo-wrapper">
        <?php
        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
            $logoUrl = UrlHelper::generateUrl('home', 'index');
        } else {
            $logoUrl = UrlHelper::generateUrl();
        }
        ?>
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
        

        $ratio = '';
        if (isset($fileData['afile_aspect_ratio']) && $fileData['afile_aspect_ratio'] > 0 && isset($aspectRatioArr[$fileData['afile_aspect_ratio']])) {
            $ratio = $aspectRatioArr[$fileData['afile_aspect_ratio']];
        }
        ?>
        <div class="logo-dashboard">
            <a href="<?php echo $logoUrl; ?>">
                <img data-ratio= "<?php echo $ratio; ?>" src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>">
            </a>
        </div>

        <?php
        $isOpened = '';
        if (isset($_COOKIE['openSidebar']) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && isset($_COOKIE['screenWidth']) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
            $isOpened = 'is-opened';
        }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar  scroll scroll-y" id="scrollElement-js" >
        <nav class="dashboard-menu">
            <ul>
                <!-- [ ======  RENTAL TABS START HERE  =====--->
                <!-- RENTAL ORDERS -->
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Rental_Orders", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'rentalorders')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'rentalOrders'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-rental"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'rentalordercancellationrequests') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Cancellation_Requests", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'rentalOrderCancellationRequests'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-cancel-request"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Cancellation_Requests", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'rentalorderreturnrequests')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'rentalOrderReturnRequests'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-return-request"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
				<li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'latechargeshistory')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Late_Charges_History", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'lateChargesHistory'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#late-charges-management"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Late_Charges_History", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="divider"></li>
                <!-- RFQ -->
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_REQUEST_FOR_QUOTES", $siteLangId); ?></span></div>
                </li>
                
                <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'quotedrequests') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_In-progress", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'quotedRequests'); ?>" >
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-in-progress"></use>
                                </svg>    
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_In-progress", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <!-- <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'reQuotedRequests') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Re-quote_on_RFQ", $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'reQuotedRequests'); ?>" >
                            <i class="icn shop">
                                <svg  class="svg" id="my_requote_requests" height="512pt" viewBox="0 0 512 512" width="512pt" xmlns="http://www.w3.org/2000/svg">
                                <path d="m143.9375 382.8125c18.640625-14.515625 30.664062-37.148438 30.664062-62.546875 0-43.707031-35.5625-79.265625-79.269531-79.265625s-79.265625 35.558594-79.265625 79.265625c0 25.398437 12.023438 48.03125 30.660156 62.546875-26.304687 15.648438-46.726562 45.203125-46.726562 82.054688v32.132812c0 8.285156 6.714844 15 15 15h160.667969c8.28125 0 15-6.714844 15-15v-32.132812c0-36.84375-20.417969-66.402344-46.730469-82.054688zm-97.871094-62.546875c0-27.164063 22.101563-49.265625 49.269532-49.265625 27.164062 0 49.265624 22.101562 49.265624 49.265625 0 27.167969-22.101562 49.269531-49.265624 49.269531-27.167969 0-49.269532-22.101562-49.269532-49.269531zm114.601563 161.734375h-130.667969v-17.132812c0-36.085938 29.195312-65.332032 65.332031-65.332032 36.085938 0 65.332031 29.195313 65.332031 65.332032v17.132812zm0 0"/>
                                <path d="m448.800781 0h-192.800781c-34.90625 0-63.199219 28.242188-63.199219 63.199219v289.199219c0 12.269531 14.070313 19.445312 24 12l60.265625-45.199219h171.734375c34.90625 0 63.199219-28.242188 63.199219-63.199219v-192.800781c0-34.90625-28.242188-63.199219-63.199219-63.199219zm33.199219 256c0 18.351562-14.839844 33.199219-33.199219 33.199219h-176.734375c-3.246094 0-6.402344 1.054687-9 3l-40.265625 30.199219v-259.199219c0-18.351563 14.839844-33.199219 33.199219-33.199219h192.800781c18.351563 0 33.199219 14.839844 33.199219 33.199219zm0 0"/>
                                <path d="m432.734375 80.332031h-160.667969c-8.285156 0-15 6.71875-15 15 0 8.285157 6.714844 15 15 15h160.667969c8.28125 0 15-6.714843 15-15 0-8.28125-6.714844-15-15-15zm0 0"/>
                                <path d="m432.734375 144.601562h-160.667969c-8.285156 0-15 6.714844-15 15 0 8.28125 6.714844 15 15 15h160.667969c8.28125 0 15-6.71875 15-15 0-8.285156-6.714844-15-15-15zm0 0"/>
                                <path d="m352.398438 208.867188h-80.332032c-8.285156 0-15 6.714843-15 15 0 8.285156 6.714844 15 15 15h80.332032c8.285156 0 15-6.714844 15-15 0-8.285157-6.714844-15-15-15zm0 0"/>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Re-quote_on_RFQ", $siteLangId); ?></span>
                        </a>
                    </div>
                </li> -->
                <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'acceptedbuyeroffers') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Accepted', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'AcceptedBuyerOffers'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-accepted"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Accepted', $siteLangId); ?></span></a>
                    </div>
                </li>

                <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'rejectedbuyeroffers') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Rejected', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'rejectedBuyerOffers'); ?>">
                            <i class="icn shop">
                            <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-rejected"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Rejected', $siteLangId); ?></span></a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'requestforquotes' && ($action == 'orders' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('RequestForQuotes', 'Orders'); ?>">
                            <i class="icn shop">
                            <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-order"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="divider"></li>


                <!---- SALE ORDERS --->
                <?php if(FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Sale_Orders", $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'orders' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'Orders'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-sales"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Orders", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'ordercancellationrequests') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_Cancellation_Requests", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'orderCancellationRequests'); ?>" >
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-cancel-request"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Cancellation_Requests", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'buyer' && ($action == 'orderreturnrequests' || $action == 'vieworderreturnrequest')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'orderReturnRequests'); ?>" >
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-return-request"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <li class="divider"></li>
                <?php }
                if (User::canViewBuyerTab()) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Offers_&_Rewards", $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'offers') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_My_Offers", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'offers'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-offers"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Offers", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'rewardpoints') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_Reward_Points", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'rewardPoints'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#reward-points"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Reward_Points", $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                    <?php if (FatApp::getConfig('CONF_ENABLE_REFERRER_MODULE', FatUtility::VAR_INT, 1)) { ?>
                        <li class="menu__item <?php echo ($controller == 'buyer' && $action == 'shareearn') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel("LBL_Share_and_Earn", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'shareEarn'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#share-earn"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Share_and_Earn", $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>
                <li class="menu__item">
                    <div class="menu__item__inner"><span class="menu-head"><?php echo Labels::getLabel("LBL_General", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && ($action == 'messages' || strtolower($action) == 'viewmessages')) ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'Messages'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#messages"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>
                                <?php if ($todayUnreadMessageCount > 0) { ?>
                                    <span class="msg-count"><?php echo ($todayUnreadMessageCount < 9) ? $todayUnreadMessageCount : '9+'; ?></span>
                                <?php } ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_My_Credits", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-credits"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <?php
                $kingPin = FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_SPLIT_PAYMENT_METHOD, FatUtility::VAR_INT, 0);
                if (0 < $kingPin) {
                    ?>
                    <li class="menu__item <?php echo ($controller == 'account' && $action == 'cards') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel("LBL_MY_CARDS", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'cards'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-credits"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_MY_CARDS', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'wishlist') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <?php
                        $label = Labels::getLabel("LBL_FAVORITES", $siteLangId);
                        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
                        $favVar = 0;
                        if (0 < $favVar) {
                            $label = Labels::getLabel("LBL_WISHLIST", $siteLangId);
                        }
                        ?>
                        <a title="<?php echo $label; ?>" href="<?php echo UrlHelper::generateUrl('Account', 'wishlist'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#favorites"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo $label; ?></span>
                        </a>
                    </div>
                </li>
                <!-- <li class="menu__item <?php echo ($controller == 'savedproductssearch' && $action == 'listing') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Saved_Searches", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SavedProductsSearch', 'listing'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#dash-saved-searches"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Saved_Searches', $siteLangId); ?></span>
                        </a>
                    </div>
                </li> -->
                <li class="divider"></li>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Profile", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Account_Settings", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#account-settings"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Account_Settings", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'myaddresses') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_Manage_Addresses", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'myAddresses'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#manage-address"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Manage_Addresses", $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel("LBL_UPDATE_CREDENTIALS", $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'changeEmailPassword'); ?>">
                            <i class="icn shop">
                                <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#update-credentials"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId); ?></span>
                        </a>
                    </div>
                </li>
                <?php $this->includeTemplate('_partial/dashboardLanguageArea.php'); ?>
            </ul>
        </nav>
    </div>
</sidebar>

<script>
    $(document).ready(function() {
        var offset = $('#main-area').outerHeight();
        if ($(".dashboard-sidebar--js .is-active").offset() != undefined && $(".dashboard-sidebar--js .is-active").offset() != null && $(".dashboard-sidebar--js .is-active").offset()  != "") {
            $('.custom-scrollbar').animate({
                scrollTop: $(".dashboard-sidebar--js .is-active").offset().top - offset
            }, 100);
        }
    });   
</script>