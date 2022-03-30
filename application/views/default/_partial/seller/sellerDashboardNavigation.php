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
                <img data-ratio="<?php echo $ratio; ?>" src="<?php echo $siteLogo; ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId) ?>">
            </a>
        </div>

        <?php
        $isOpened = '';
        if (array_key_exists('openSidebar', $_COOKIE) && !empty(FatUtility::int($_COOKIE['openSidebar'])) && array_key_exists('screenWidth', $_COOKIE) && applicationConstants::MOBILE_SCREEN_WIDTH < FatUtility::int($_COOKIE['screenWidth'])) {
            $isOpened = 'is-opened';
        }
        ?>
        <div class="js-hamburger hamburger-toggle <?php echo $isOpened; ?>"><span class="bar-top"></span><span class="bar-mid"></span><span class="bar-bot"></span></div>
    </div>
    <div class="sidebar__content custom-scrollbar scroll scroll-y" id="scrollElement-js">
        <nav class="dashboard-menu">
            <ul>
                <?php
                if (
                    $userPrivilege->canViewShop(UserAuthentication::getLoggedUserId(), true)
                ) {
                ?>
                    <!-- SHOP  -->
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_SHOP', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewShop(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'shop') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'shop'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#manage-shop"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Manage_Shop', $siteLangId); ?></span></a></div>
                        </li>
                        <li class="divider"></li>
                    <?php } ?>
                <?php } ?>
                <!-- RENTAL PRODUCTS & PROMOTIONS -->

                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_RENTAL_PRODUCTS_&_PROMOTIONS', $siteLangId); ?></span></div>
                </li>

                <?php if ($userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'sellerinventories' && ($action == 'customcatalogproductform' || $action == 'customproductform' || $action == 'catalog' || $action == 'products' || $action == 'customcatalogproducts')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Inventory', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerInventories', 'products'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#inventory"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Inventory', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>

                    <?php if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) > 0 && $userPrivilege->canViewAddons(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'addonproducts') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Addons', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('addonProducts'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#add-ons"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Addons', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                <?php }
                } ?>
                <li class="menu__item <?php echo ($controller == 'sellerinventories' && $action == 'productrentalunavailabledates') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel('LBL_Unavailable_Dates', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerInventories', 'productRentalUnavailableDates'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#unavailable-dates"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Unavailable_Dates', $siteLangId); ?></span>
                        </a>
                    </div>
                </li>

                <?php if ($userPrivilege->canViewSpecialPrice(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && $action == 'rentalspecialprice') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'rentalSpecialPrice'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#special-price"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>
                <li class="menu__item <?php echo ($controller == 'sellerinventories' && $action == 'sellerproductdurationdiscounts') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner">
                        <a title="<?php echo Labels::getLabel('LBL_Duration_Discounts', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerInventories', 'sellerProductDurationDiscounts'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#duration-discount"></use>
                                </svg>
                            </i>
                            <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Duration_Discounts', $siteLangId); ?></span>
                        </a>
                    </div>
                </li>

                <?php if ($userPrivilege->canViewAddons(UserAuthentication::getLoggedUserId(), true) && FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 1) { ?>
                    <li class="menu__item <?php echo ($controller == 'attachaddonpoducts') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Link_Rental_Addons', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('attachAddonPoducts'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#rental-add-ons"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Link_Rental_Addons', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>

                <?php if (FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1) && User::canAddCustomProduct() && $userPrivilege->canViewVerificationFields(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'attachverificationfields' && $action == 'index') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Link_Verification_Fields', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('AttachVerificationFields', 'index'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#verification-fields"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Link_Verification_Fields', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <li class="divider"></li>

                <!-- SALE PRODUCTS & PROMOTIONS -->
                <?php if (ALLOW_SALE) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_SALE_PRODUCTS_&_PROMOTIONS', $siteLangId); ?></span></div>
                    </li>

                    <?php if ($userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'sellerinventories' && ($action == 'sales')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Inventory', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerInventories', 'sales'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#inventory"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Inventory', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="menu__item <?php echo ($controller == 'seller' && $action == 'specialprice') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'specialPrice'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#special-price"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Special_Price', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>

                    <?php if (ALLOW_SALE && $userPrivilege->canViewVolumeDiscount(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'sellerinventories' && $action == 'volumediscount') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerInventories', 'volumeDiscount'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#volume-discount"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <!-- MORE PRODUCT OPTIONS -->

                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_MORE_OPTIONS', $siteLangId); ?></span></div>
                </li>

                <?php if (ALLOW_SALE && $userPrivilege->canViewBuyTogetherProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'sellerinventories' && $action == 'upsellproducts') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Buy_Together_Products', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerInventories', 'upsellProducts'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#buy-together-products"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Buy_Together_Products', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <?php if ($userPrivilege->canViewRelatedProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'sellerinventories' && $action == 'relatedproducts') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Related_Products', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerInventories', 'RelatedProducts'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#related-products"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Related_Products', $siteLangId); ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>


                <?php if (User::canAddCustomProduct() && $userPrivilege->canViewProductTags(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && $action == 'producttags') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Tags', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'productTags'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#product-tags"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Tags', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <?php
                $canAddCustomProd = FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0);
                if (0 < $canAddCustomProd && $userPrivilege->canViewProductOptions(UserAuthentication::getLoggedUserId(), true)) {
                ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && $action == 'options') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Options', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'options'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#options"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Options', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <?php if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0) && $userPrivilege->canViewTaxCategory(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'taxcategories' || $action == 'taxrules')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'taxCategories'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#tax-categories"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <?php if ($userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId(), true) && $isRequsetCount) { ?>
                    <li class="menu__item <?php echo ($controller == 'sellerrequests' && $action == 'index') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('SellerRequests'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#requests"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Requests', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <?php
                $obj = new Plugin();
                $pluginData = $obj->getDefaultPluginData(Plugin::TYPE_ADVERTISEMENT_FEED, null, $siteLangId);
                if (false !== $pluginData && !empty($pluginData) && 0 < $pluginData['plugin_active'] && $userPrivilege->canViewAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) {
                ?>
                    <li class="menu__item <?php echo ($controller == strtolower($pluginData['plugin_code'])) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo $pluginData['plugin_name']; ?>" href="<?php echo UrlHelper::generateUrl($pluginData['plugin_code']); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#dash-promotions"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo $pluginData['plugin_name']; ?></span>
                            </a>
                        </div>
                    </li>
                <?php } ?>

                <li class="divider"></li>



                <?php
                if (
                    $userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true) ||
                    (FatApp::getConfig('CONF_ENABLE_RENTAL_PRODUCT_LATE_CHARGES_MODULE', FatUtility::VAR_INT, 0) && $userPrivilege->canViewLateChargesManagement(UserAuthentication::getLoggedUserId(), true)) ||

                    FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)
                ) {
                ?>
                    <!-- RENTAL ORDERS -->
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_RENTAL_ORDERS', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'sellerorders' && ($action == 'rentals' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerOrders', 'rentals'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-rental"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                        <?php if ($userPrivilege->canViewLateChargesManagement(UserAuthentication::getLoggedUserId(), true)) { ?>
                            <li class="menu__item <?php echo ($controller == 'sellerorders' && (strtolower($action) == 'latechargeshistory')) ? 'is-active' : ''; ?>">
                                <div class="menu__item__inner">
                                    <a title="<?php echo Labels::getLabel('LBL_Late_Charges_History', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerOrders', 'lateChargesHistory'); ?>">
                                        <i class="icn shop"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#late-charges-management"></use>
                                            </svg>
                                        </i>
                                        <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Late_Charges_History', $siteLangId); ?></span>
                                    </a>
                                </div>
                            </li>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'rentalordercancellationrequests') ? 'is-active' : '' ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Cancellation_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'rentalOrderCancellationRequests'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-cancel-request"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Cancellation_Requests", $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                        
                        <li class="menu__item <?php echo ($controller == 'sellerorders' && (strtolower($action) == 'cancelpenaltyhistory')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Late_Charges_History', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('sellerOrders', 'cancelPenaltyHistory'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-cancel-request"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Cancellation_Penalty_History', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'rentalorderreturnrequests')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Return_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'rentalOrderReturnRequests'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-return-request"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                        <li class="menu__item <?php echo ($controller == 'ordercancelrules') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Cancellation_Penalty_Rules', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('orderCancelRules'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#cancellation-penalty-rules"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Cancellation_Penalty_Rules", $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>

                    <?php } ?>
                    <?php
                    $isModuleActiveOnShop = Shop::getAttributesByUserId(UserAuthentication::getLoggedUserId(), 'shop_is_enable_late_charges');
                    if (FatApp::getConfig('CONF_ENABLE_RENTAL_PRODUCT_LATE_CHARGES_MODULE', FatUtility::VAR_INT, 0) && $userPrivilege->canViewLateChargesManagement(UserAuthentication::getLoggedUserId(), true) && $isModuleActiveOnShop) { ?>
                        <li class="menu__item <?php echo ($controller == 'latecharges') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Late_Charges_Management', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('lateCharges'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#late-charges-management"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel("LBL_Late_Charges_Management", $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>

                    <li class="menu__item <?php echo ($controller == 'productreturns' && ($action == 'upcomingproductreturns' || $action == 'overdueproductreturns')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner">
                            <a title="<?php echo Labels::getLabel('LBL_Upcoming/Overdue_Returns', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('ProductReturns', 'upcomingProductReturns'); ?>">
                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#upcoming-overdue-returns"></use>
                                    </svg>
                                </i>
                                <span class="menu-item__title">
                                    <?php echo Labels::getLabel('LBL_Upcoming/Overdue_Returns', $siteLangId); ?>
                                </span>
                            </a>
                        </div>
                    </li>
                    <li class="divider"></li>
                <?php } ?>
                <?php
                if (($userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true))) {
                ?>
                    <!-- RFQ -->
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_REQUEST_FOR_QUOTES', $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'productquotes') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_In-progress', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'productQuotes'); ?>">

                                <i class="icn shop">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-in-progress"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_In-progress', $siteLangId); ?></span></a>
                        </div>
                    </li>

                    <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'acceptedoffers') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Accepted', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'AcceptedOffers'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-accepted"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Accepted', $siteLangId); ?></span></a>
                        </div>
                    </li>

                    <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'rejectedoffers') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Rejected', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'rejectedOffers'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-rejected"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Rejected', $siteLangId); ?></span></a>
                        </div>
                    </li>

                    <!-- <li class="menu__item <?php echo ($controller == 'requestforquotes' && $action == 'requotedoffers') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Re-quotes_on_RFQ', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'reQuotedOffers'); ?>">
                                <i class="icn shop">
                                    <svg class="svg" id="my_requote_requests" height="512pt" viewBox="0 0 512 512" width="512pt" xmlns="http://www.w3.org/2000/svg">
                                        <path d="m143.9375 382.8125c18.640625-14.515625 30.664062-37.148438 30.664062-62.546875 0-43.707031-35.5625-79.265625-79.269531-79.265625s-79.265625 35.558594-79.265625 79.265625c0 25.398437 12.023438 48.03125 30.660156 62.546875-26.304687 15.648438-46.726562 45.203125-46.726562 82.054688v32.132812c0 8.285156 6.714844 15 15 15h160.667969c8.28125 0 15-6.714844 15-15v-32.132812c0-36.84375-20.417969-66.402344-46.730469-82.054688zm-97.871094-62.546875c0-27.164063 22.101563-49.265625 49.269532-49.265625 27.164062 0 49.265624 22.101562 49.265624 49.265625 0 27.167969-22.101562 49.269531-49.265624 49.269531-27.167969 0-49.269532-22.101562-49.269532-49.269531zm114.601563 161.734375h-130.667969v-17.132812c0-36.085938 29.195312-65.332032 65.332031-65.332032 36.085938 0 65.332031 29.195313 65.332031 65.332032v17.132812zm0 0" />
                                        <path d="m448.800781 0h-192.800781c-34.90625 0-63.199219 28.242188-63.199219 63.199219v289.199219c0 12.269531 14.070313 19.445312 24 12l60.265625-45.199219h171.734375c34.90625 0 63.199219-28.242188 63.199219-63.199219v-192.800781c0-34.90625-28.242188-63.199219-63.199219-63.199219zm33.199219 256c0 18.351562-14.839844 33.199219-33.199219 33.199219h-176.734375c-3.246094 0-6.402344 1.054687-9 3l-40.265625 30.199219v-259.199219c0-18.351563 14.839844-33.199219 33.199219-33.199219h192.800781c18.351563 0 33.199219 14.839844 33.199219 33.199219zm0 0" />
                                        <path d="m432.734375 80.332031h-160.667969c-8.285156 0-15 6.71875-15 15 0 8.285157 6.714844 15 15 15h160.667969c8.28125 0 15-6.714843 15-15 0-8.28125-6.714844-15-15-15zm0 0" />
                                        <path d="m432.734375 144.601562h-160.667969c-8.285156 0-15 6.714844-15 15 0 8.28125 6.714844 15 15 15h160.667969c8.28125 0 15-6.71875 15-15 0-8.285156-6.714844-15-15-15zm0 0" />
                                        <path d="m352.398438 208.867188h-80.332032c-8.285156 0-15 6.714843-15 15 0 8.285156 6.714844 15 15 15h80.332032c8.285156 0 15-6.714844 15-15 0-8.285157-6.714844-15-15-15zm0 0" />
                                    </svg>
                                </i>
                                <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Re-quotes_on_RFQ', $siteLangId); ?></span></a>
                        </div>
                    </li> -->

                    <?php /* if ($userPrivilege->canViewInvoices(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'invoices' && $action == 'invoicerequests') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Invoices_Requestes', $siteLangId); ?>" href="<?php echo CommonHelper::generateUrl('Invoices', 'invoiceRequests'); ?>">
                                <i class="icn shop">
                                    <svg class="svg" id="my_requote_requests" height="512pt" viewBox="0 0 512 512" width="512pt" xmlns="http://www.w3.org/2000/svg">
                                        <path d="m143.9375 382.8125c18.640625-14.515625 30.664062-37.148438 30.664062-62.546875 0-43.707031-35.5625-79.265625-79.269531-79.265625s-79.265625 35.558594-79.265625 79.265625c0 25.398437 12.023438 48.03125 30.660156 62.546875-26.304687 15.648438-46.726562 45.203125-46.726562 82.054688v32.132812c0 8.285156 6.714844 15 15 15h160.667969c8.28125 0 15-6.714844 15-15v-32.132812c0-36.84375-20.417969-66.402344-46.730469-82.054688zm-97.871094-62.546875c0-27.164063 22.101563-49.265625 49.269532-49.265625 27.164062 0 49.265624 22.101562 49.265624 49.265625 0 27.167969-22.101562 49.269531-49.265624 49.269531-27.167969 0-49.269532-22.101562-49.269532-49.269531zm114.601563 161.734375h-130.667969v-17.132812c0-36.085938 29.195312-65.332032 65.332031-65.332032 36.085938 0 65.332031 29.195313 65.332031 65.332032v17.132812zm0 0" />
                                        <path d="m448.800781 0h-192.800781c-34.90625 0-63.199219 28.242188-63.199219 63.199219v289.199219c0 12.269531 14.070313 19.445312 24 12l60.265625-45.199219h171.734375c34.90625 0 63.199219-28.242188 63.199219-63.199219v-192.800781c0-34.90625-28.242188-63.199219-63.199219-63.199219zm33.199219 256c0 18.351562-14.839844 33.199219-33.199219 33.199219h-176.734375c-3.246094 0-6.402344 1.054687-9 3l-40.265625 30.199219v-259.199219c0-18.351563 14.839844-33.199219 33.199219-33.199219h192.800781c18.351563 0 33.199219 14.839844 33.199219 33.199219zm0 0" />
                                        <path d="m432.734375 80.332031h-160.667969c-8.285156 0-15 6.71875-15 15 0 8.285157 6.714844 15 15 15h160.667969c8.28125 0 15-6.714843 15-15 0-8.28125-6.714844-15-15-15zm0 0" />
                                        <path d="m432.734375 144.601562h-160.667969c-8.285156 0-15 6.714844-15 15 0 8.28125 6.714844 15 15 15h160.667969c8.28125 0 15-6.71875 15-15 0-8.285156-6.714844-15-15-15zm0 0" />
                                        <path d="m352.398438 208.867188h-80.332032c-8.285156 0-15 6.714843-15 15 0 8.285156 6.714844 15 15 15h80.332032c8.285156 0 15-6.714844 15-15 0-8.285157-6.714844-15-15-15zm0 0" />
                                    </svg>
                                </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Invoices_Requestes', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } */ ?>

                    <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'requestforquotes' && ($action == 'rfqorder' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_RFQ_Orders', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('RequestForQuotes', 'rfqOrder'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#RFQ-order"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>

                <?php } ?>

                <li class="divider"></li>
                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Sale_Orders', $siteLangId); ?></span></div>
                </li>
                <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'sales' || $action == 'vieworder')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'Sales'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-sales"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && $action == 'ordercancellationrequests') ? 'is-active' : '' ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Order_Cancellation_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'orderCancellationRequests'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-cancel-request"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Order_Cancellation_Requests", $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'orderreturnrequests')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Return_Requests', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'orderReturnRequests'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#order-return-request"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Return_Requests", $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>

                <li class="divider"></li>

                <?php
                if ( /* FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0) && */
                    ($userPrivilege->canViewShippingProfiles(UserAuthentication::getLoggedUserId(), true) ||
                        $userPrivilege->canViewShippingPackages(UserAuthentication::getLoggedUserId(), true) ||
                        $userPrivilege->canViewLinkPickupSection(UserAuthentication::getLoggedUserId(), true))
                ) {
                ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_SHIPPING_&_FULLFILMENT', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewShippingProfiles(UserAuthentication::getLoggedUserId(), true) /* && !FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) */ ) { ?>
                        <li class="menu__item <?php echo ($controller == 'shippingprofile') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Shipping_Profiles', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('shippingProfile'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#shipping-profile"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Shipping_Profiles', $siteLangId); ?></span></a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewShippingPackages(UserAuthentication::getLoggedUserId(), true) && FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) { ?>
                        <li class="menu__item <?php echo ($controller == 'shippingpackages') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('shippingPackages'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#shipping-package"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?></span></a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php
                    $fulFillmentType = Shop::getAttributesByUserId($userParentId, 'shop_fulfillment_type', false);
                    if ($userPrivilege->canViewLinkPickupSection(UserAuthentication::getLoggedUserId(), true) && $fulFillmentType != Shipping::FULFILMENT_SHIP) { ?>
                        <li class="menu__item <?php echo ($controller == 'linkpickupaddress' && $action == 'index') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Link_Products_with_Pickup_Address', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('LinkPickupAddress', 'index'); ?>">
                                    <i class="icn shop">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#link-products-pickup-add"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Link_Products_with_Pickup_Address', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <?php
                if (
                    $userPrivilege->canViewMetaTags(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId(), true)
                ) {
                ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_SEO', $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewMetaTags(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'productseo') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Meta_Tags', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'productSeo'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#meta-tags"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Meta_Tags', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewUrlRewriting(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'seller' && $action == 'producturlrewriting') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_URL_Rewriting', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'productUrlRewriting'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#URL-rewriting"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_URL_Rewriting', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <?php
                if (
                    $userPrivilege->canViewSalesReport(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewPerformanceReport(UserAuthentication::getLoggedUserId(), true) ||
                    $userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId(), true)
                ) {
                ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Reports", $siteLangId); ?></span></div>
                    </li>
                    <?php if ($userPrivilege->canViewSalesReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'rentalreport') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Rental_Report', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'rentalReport'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#rental-report"></use>
                                        </svg>
                                    </i>
                                    <span class="menu-item__title"><?php echo Labels::getLabel('LBL_Rental_Report', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'salesreport') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner">
                                <a title="<?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'SalesReport'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#sales-report"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Sales_Report', $siteLangId); ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewPerformanceReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsperformance') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_PRODUCTS_PERFORMANCE_REPORT', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'ProductsPerformance'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#product-performance-report"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_PRODUCTS_PERFORMANCE_REPORT', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventory') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'productsInventory'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#products-inventory"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory', $siteLangId); ?></span></a></div>
                        </li>
                        <li class="menu__item <?php echo ($controller == 'reports' && $action == 'productsinventorystockstatus') ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Reports', 'productsInventoryStockStatus'); ?>">
                                    <i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#product-inventory-stock"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Products_Inventory_Stock_Status', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                    <li class="divider"></li>
                <?php } ?>

                <li class="menu__item">
                    <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel("LBL_Profile", $siteLangId); ?></span></div>
                </li>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'profileinfo') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Account', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'ProfileInfo'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-account"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Account", $siteLangId); ?></span></a></div>
                </li>
                <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'users' || $action == 'userpermissions')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Sub_Users', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'Users'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#sub-users"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Sub_Users", $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userPrivilege->canViewMessages(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item <?php echo ($controller == 'account' && ($action == 'messages' || strtolower($action) == 'viewmessages')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Messages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'Messages'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#messages"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_Messages", $siteLangId); ?>
                                    <?php if ($todayUnreadMessageCount > 0) { ?>
                                        <span class="msg-count"><?php echo ($todayUnreadMessageCount < 9) ? $todayUnreadMessageCount : '9+'; ?></span>
                                    <?php } ?></span></a></div>
                    </li>
                <?php } ?>
                <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                    <li class="menu__item <?php echo ($controller == 'account' && $action == 'credits') ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-credits"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_My_Credits', $siteLangId); ?></span></a></div>
                    </li>
                <?php } ?>
                <li class="menu__item <?php echo ($controller == 'account' && $action == 'changeemailpassword') ? 'is-active' : ''; ?>">
                    <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Account', 'changeEmailPassword'); ?>">
                            <i class="icn shop"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#update-credentials"></use>
                                </svg>
                            </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId); ?></span></a></div>
                </li>


                <?php if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE') && $userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Subscription', $siteLangId); ?></span></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'subscriptions' || $action == 'viewsubscriptionorder')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_My_Subscriptions', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Seller', 'subscriptions'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#my-subscriptions"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel("LBL_My_Subscriptions", $siteLangId); ?></span></a></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'packages')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'Packages'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#subscription-packages"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Packages', $siteLangId); ?></span></a></div>
                    </li>
                    <li class="menu__item <?php echo ($controller == 'seller' && ($action == 'selleroffers')) ? 'is-active' : ''; ?>">
                        <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('seller', 'SellerOffers'); ?>">
                                <i class="icn shop"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#subscription-offers"></use>
                                    </svg>
                                </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Subscription_Offers', $siteLangId); ?></span></a></div>
                    </li>
                    <li class="divider"></li>
                <?php } ?>


                <?php if ($userPrivilege->canViewImportExport(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <li class="divider"></li>
                    <li class="menu__item">
                        <div class="menu__item__inner"> <span class="menu-head"><?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?></span></div>
                    </li>
                    <?php if (FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0)) { ?>
                        <li class="menu__item <?php echo ($controller == 'importexport' && ($action == 'index')) ? 'is-active' : ''; ?>">
                            <div class="menu__item__inner"><a title="<?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('ImportExport', 'index'); ?>"><i class="icn shop"><svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sidebar.svg#import-export"></use>
                                        </svg>
                                    </i><span class="menu-item__title"><?php echo Labels::getLabel('LBL_Import_Export', $siteLangId); ?></span></a></div>
                        </li>
                    <?php } ?>
                <?php } ?>
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