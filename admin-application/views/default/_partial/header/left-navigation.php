<!--left panel start here-->
<span class="leftoverlay"></span>
<aside class="leftside">
    <div class="sidebar_inner">
        <div class="profilewrap">
            <div class="profilecover">
                <figure class="profilepic"><img id="leftmenuimgtag" src="<?php echo UrlHelper::generateUrl('image', 'profileImage', array(AdminAuthentication::getLoggedAdminId(), "THUMB", true)); ?>" alt=""></figure>
                <span class="profileinfo"><?php echo Labels::getLabel('LBL_Welcome', $adminLangId); ?> <?php echo $adminName; ?></span>
            </div>
            <div class="profilelinkswrap">
                <ul class="leftlinks">
                    <li class=""><a href="<?php echo UrlHelper::generateUrl('profile'); ?>"><?php echo Labels::getLabel('LBL_View_Profile', $adminLangId); ?></a></li>
                    <li class=""><a href="<?php echo UrlHelper::generateUrl('profile', 'changePassword'); ?>"><?php echo Labels::getLabel('LBL_Change_Password', $adminLangId); ?></a></li>
                    <li class=""><a href="<?php echo UrlHelper::generateUrl('profile', 'logout'); ?>"><?php echo Labels::getLabel('LBL_Logout', $adminLangId); ?></a></li>
                </ul>
            </div>
        </div>
        <ul class="leftmenu">
            <!--Dashboard-->
            <?php if ($objPrivilege->canViewAdminDashboard(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li><a href="<?php echo UrlHelper::generateUrl(); ?>"><?php echo Labels::getLabel('LBL_Dashboard', $adminLangId); ?></a></li>
            <?php } ?>

            <?php if ($objPrivilege->canViewShops(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li>
                    <a href="<?php echo UrlHelper::generateUrl('Shops'); ?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId); ?></a>
                </li>
            <?php } ?>

            <!--Products -->
            <?php
            if (
                    $objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAttributes(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerProducts(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Catalog', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewBrands(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Brands'); ?>"><?php echo Labels::getLabel('LBL_Brands', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewOptions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Options'); ?>"><?php echo Labels::getLabel('LBL_Options', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ProductCategories'); ?>"><?php echo Labels::getLabel('LBL_Categories', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('products'); ?>"><?php echo Labels::getLabel('LBL_Products', $adminLangId); ?></a></li>
                            <?php if ($objPrivilege->canViewSellerProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                <li><a href="<?php echo UrlHelper::generateUrl('SellerProducts', 'thresholdProducts'); ?>"><?php echo Labels::getLabel('LBL_Threshold_Products', $adminLangId); ?> <?php if ($threshSelProdCount) { ?><span class='badge'>(<?php echo $threshSelProdCount; ?>)</span><?php } ?></a></li>
                            <?php } ?>
                        <?php } ?>

                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Rental_Product_Options', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>

                                    <li><a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'index'); ?>"><?php echo Labels::getLabel('LBL_Seller_Inventory', $adminLangId); ?></a></li>

                                    <li>
                                        <a href="<?php echo UrlHelper::generateUrl('AddonProducts', 'listing'); ?>"><?php echo Labels::getLabel('LBL_Rental_Addons', $adminLangId); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php echo UrlHelper::generateUrl('AddonProducts'); ?>"><?php echo Labels::getLabel('LBL_Linked_Rental_Addons_To_Products', $adminLangId); ?></a>
                                    </li>

                                    <li><a href="<?php echo UrlHelper::generateUrl('SellerRentalProducts', 'productRentalUnavailableDates'); ?>"><?php echo Labels::getLabel('LBL_Product_Unavailable_Dates', $adminLangId); ?></a>
                                    </li>

                                    <?php if (FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1) && $objPrivilege->canViewDocumentVerification(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                        <li>
                                            <a href="<?php echo UrlHelper::generateUrl('VerificationFields'); ?>"><?php echo Labels::getLabel('LBL_Document_Verification_Fields', $adminLangId); ?></a>
                                        </li>
                                    <?php } ?>

                                <?php } ?>

                            </ul>
                        </li>

                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sale_Product_Options', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <li><a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'sales'); ?>"><?php echo Labels::getLabel('LBL_Seller_Inventory', $adminLangId); ?></a></li>
                            </ul>
                        </li>

                        <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'relatedProducts'); ?>"><?php echo Labels::getLabel('LBL_Related_Products', $adminLangId); ?></a>
                            </li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'upsellProducts'); ?>"><?php echo Labels::getLabel('LBL_Buy_Together_Products', $adminLangId); ?></a>
                            </li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('productReviews'); ?>"><?php echo Labels::getLabel('LBL_Product_Reviews', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Tags'); ?>"><?php echo Labels::getLabel('LBL_Tags', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewBrandRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Brands', 'BrandRequests'); ?>"><?php echo Labels::getLabel('LBL_Brand_Requests', $adminLangId); ?><?php if ($brandReqCount) { ?><span class='badge'>(<?php echo $brandReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewProductCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ProductCategories', 'requests'); ?>"><?php echo Labels::getLabel('LBL_Categories_Requests', $adminLangId); ?><?php if ($categoryReqCount) { ?><span class='badge'>(<?php echo $categoryReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('CustomProducts'); ?>"><?php echo Labels::getLabel('LBL_Custom_Product_Catalog_Requests', $adminLangId); ?> <?php if ($custProdReqCount) { ?><span class='badge'>(<?php echo $custProdReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php
            if (
                    $objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewRecomendedWeightages(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPromotions(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild">
                    <a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Promotions', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Rental_Promotions', $adminLangId); ?></a>
                                <ul style="display: none;">
                                    <li> 
                                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'rentalSpecialPrice'); ?>"><?php echo Labels::getLabel('LBL_Special_Price', $adminLangId); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php echo UrlHelper::generateUrl('SellerRentalProducts', 'sellerProductDurationDiscounts'); ?>"><?php echo Labels::getLabel('LBL_Duration_Discounts', $adminLangId); ?></a>
                                    </li>

                                </ul>
                            </li>

                            <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sale_Promotions', $adminLangId); ?></a>
                                <ul style="display: none;">
                                    <li>
                                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'specialPrice'); ?>"><?php echo Labels::getLabel('LBL_Special_Price', $adminLangId); ?></a>
                                    </li>
                                    <li>
                                        <a href="<?php echo UrlHelper::generateUrl('sellerProducts', 'volumeDiscount'); ?>"><?php echo Labels::getLabel('LBL_Volume_Discount', $adminLangId); ?></a>
                                    </li>

                                </ul>
                            </li>
                        <?php } ?>


                        <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('DiscountCoupons'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewPromotions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('promotions'); ?>"><?php echo Labels::getLabel('LBL_PPC_Promotions_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewRewardsOnPurchase(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('RewardsOnPurchase'); ?>"><?php echo Labels::getLabel('LBL_Rewards_on_every_purchase', $adminLangId); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php
            if (
                    $objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAbandonedCart(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewProductReviews(AdminAuthentication::getLoggedAdminId(), true) ||
                    ($objPrivilege->canViewRentalOrderCancelRules(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0))
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Orders', $adminLangId); ?></a>
                    <ul>
                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Rental_Orders', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <?php if ($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('orders', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Orders', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('SellerOrders', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Seller_Orders', $adminLangId); ?> <?php if (!empty($sellerRentalOrderCount)) { ?><span class='badge'>(<?php echo $sellerRentalOrderCount; ?>)</span><?php } ?></a></li>

                                    <li><a href="<?php echo UrlHelper::generateUrl('SellerOrders', 'lateChargesHistory'); ?>"><?php echo Labels::getLabel('LBL_Late_Charges_History', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('OrderCancellationRequests', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Cancellation_Requests', $adminLangId); ?> <?php if ($rentalOrderCancelReqCount) { ?><span class='badge'>(<?php echo $rentalOrderCancelReqCount; ?>)</span><?php } ?></a></li>

                                    <li><a href="<?php echo UrlHelper::generateUrl('SellerOrders', 'cancelPenaltyHistory'); ?>"><?php echo Labels::getLabel('LBL_Cancel_Penalty_History', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('OrderReturnRequests', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Return/Refund_Requests', $adminLangId); ?> <?php if ($rentalOrderRetReqCount) { ?><span class='badge'>(<?php echo $rentalOrderRetReqCount; ?>)</span><?php } ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewRentalOrderCancelRules(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('orderCancelRules'); ?>"><?php echo Labels::getLabel('LBL_Order_Cancel_Rules', $adminLangId); ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>

                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sale_Orders', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <?php if ($objPrivilege->canViewOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('orders'); ?>"><?php echo Labels::getLabel('LBL_Orders', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewSellerOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('SellerOrders'); ?>"><?php echo Labels::getLabel('LBL_Seller_Orders', $adminLangId); ?> <?php if (!empty($sellerOrderCount)) { ?><span class='badge'>(<?php echo $sellerOrderCount; ?>)</span><?php } ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewOrderCancellationRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('OrderCancellationRequests'); ?>"><?php echo Labels::getLabel('LBL_Cancellation_Requests', $adminLangId); ?> <?php if ($orderCancelReqCount) { ?><span class='badge'>(<?php echo $orderCancelReqCount; ?>)</span><?php } ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewOrderReturnRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('OrderReturnRequests'); ?>"><?php echo Labels::getLabel('LBL_Return/Refund_Requests', $adminLangId); ?> <?php if ($orderRetReqCount) { ?><span class='badge'>(<?php echo $orderRetReqCount; ?>)</span><?php } ?></a></li>
                                <?php } ?>

                            </ul>
                        </li>

                        <?php if ($objPrivilege->canViewSubscriptionOrders(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('SubscriptionOrders'); ?>"><?php echo Labels::getLabel('LBL_Subscription_Orders', $adminLangId); ?> </a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewWithdrawRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('WithdrawalRequests'); ?>"><?php echo Labels::getLabel('LBL_Withdrawl_Requests', $adminLangId); ?> <?php if ($drReqCount) { ?><span class='badge'>(<?php echo $drReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>

            <?php if ($objPrivilege->canViewRfqManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_RFQ', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewRfqManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo CommonHelper::generateUrl('RequestForQuotes'); ?>"><?php echo Labels::getLabel('LBL_RFQ_Management', $adminLangId); ?> </a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>

            <?php
            if (
                    $objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerCatalogRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCustomCatalogProductRequests(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewMessages(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Users', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewUsers(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Users'); ?>"><?php echo Labels::getLabel('LBL_Users', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true) || $objPrivilege->canViewAdminUsers(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('AdminUsers') ?>"><?php echo Labels::getLabel('LBL_Admin_Sub_Users', $adminLangId); ?></a>
                            </li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewMessages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Messages'); ?>"><?php echo Labels::getLabel('LBL_Messages', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewSellerApprovalForm(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Users', 'sellerForm'); ?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Form', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewSellerApprovalRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Users', 'sellerApprovalRequests'); ?>"><?php echo Labels::getLabel('LBL_Seller_Approval_Requests', $adminLangId); ?> <?php if ($supReqCount) { ?><span class='badge'>(<?php echo $supReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewUserRequests(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('userGdprRequests'); ?>"><?php echo Labels::getLabel('LBL_Users_GDPR_Requests', $adminLangId); ?> <?php if ($gdprReqCount) { ?><span class='badge'>(<?php echo $gdprReqCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <!--Mobile Application-->
            <?php
            if (
                    $objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewRentalsReport(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Reports', $adminLangId); ?></a>
                    <ul>
                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Rental_Reports', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <?php if ($objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('UsersReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Buyers/Sellers', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewRentalsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo CommonHelper::generateUrl('RentalsReport'); ?>"><?php echo Labels::getLabel('LBL_Sales', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('ProductsReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Seller_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CatalogReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Catalog', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('ShopsReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CommissionReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Commission', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('TopProductsReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Top_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('BadProductsReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Most_Refunded_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CategoriesReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Categories_Report', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('TaxReport', 'rental'); ?>"><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>


                        <li class="child"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sale_Reports', $adminLangId); ?></a>
                            <ul style="display: none;">
                                <?php if ($objPrivilege->canViewUsersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('UsersReport'); ?>"><?php echo Labels::getLabel('LBL_Buyers/Sellers', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewSalesReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('SalesReport'); ?>"><?php echo Labels::getLabel('LBL_Sales', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewProductsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('ProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Seller_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewCatalogReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CatalogReport'); ?>"><?php echo Labels::getLabel('LBL_Catalog', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewShopsReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('ShopsReport'); ?>"><?php echo Labels::getLabel('LBL_Shops', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewCommissionReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CommissionReport'); ?>"><?php echo Labels::getLabel('LBL_Commission', $adminLangId); ?></a></li>
                                <?php } ?>

                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('TopProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Top_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('BadProductsReport'); ?>"><?php echo Labels::getLabel('LBL_Most_Refunded_Products', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewPerformanceReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('CategoriesReport'); ?>"><?php echo Labels::getLabel('LBL_Categories_Report', $adminLangId); ?></a></li>
                                <?php } ?>
                                <?php if ($objPrivilege->canViewTaxReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                                    <li><a href="<?php echo UrlHelper::generateUrl('TaxReport'); ?>"><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php if ($objPrivilege->canViewAdvertisersReport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('AdvertisersReport'); ?>"><?php echo Labels::getLabel('LBL_Advertisers', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('DiscountCouponsReport'); ?>"><?php echo Labels::getLabel('LBL_Discount_Coupons', $adminLangId); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>

            <?php } ?>

            <!--CMS-->
            <?php
            if (
                    $objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewZones(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPolicyPoints(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerDiscountCoupons(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSmsTemplate(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewAbusiveWords(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewContentWithIconSection(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Cms', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewNavigationManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Navigations'); ?>"><?php echo Labels::getLabel('LBL_Navigation_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewSlides(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('slides'); ?>"><?php echo Labels::getLabel('LBL_Home_Page_Slides_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewCollections(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Collections'); ?>"><?php echo Labels::getLabel('LBL_Collection_Management', $adminLangId); ?> </a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewBanners(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Banners'); ?>"><?php echo Labels::getLabel('LBL_Banners', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewLanguageLabels(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Labels'); ?>"><?php echo Labels::getLabel('LBL_Language_Labels', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewEmailTemplates(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('EmailTemplates'); ?>"><?php echo Labels::getLabel('LBL_Email_Templates_Management', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewSmsTemplate(AdminAuthentication::getLoggedAdminId(), true) && SmsArchive::canSendSms()) { ?>
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('SmsTemplates'); ?>">
                                    <?php echo Labels::getLabel('LBL_SMS_TEMPLATE_MANAGEMENT', $adminLangId); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewContentPages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ContentPages'); ?>"><?php echo Labels::getLabel('LBL_Content_Pages', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewContentBlocks(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ContentBlock'); ?>"><?php echo Labels::getLabel('LBL_Content_Blocks', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewImportInstructions(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ContentBlock', 'importInstructions'); ?>"><?php echo Labels::getLabel('LBL_Import_Instructions', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewFaqCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('FaqCategories'); ?>"><?php echo Labels::getLabel('LBL_FAQs', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewZones(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Zones'); ?>"><?php echo Labels::getLabel('LBL_Zone(Regions)_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewCountries(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Countries'); ?>"><?php echo Labels::getLabel('LBL_Countries_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewStates(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('States'); ?>"><?php echo Labels::getLabel('LBL_States_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewEmptyCartItems(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('emptyCartItems'); ?>"><?php echo Labels::getLabel('LBL_Empty_Cart_Items_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewSocialPlatforms(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('SocialPlatform'); ?>"><?php echo Labels::getLabel('LBL_Social_Platforms_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewShopReportReasons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ShopReportReasons'); ?>"><?php echo Labels::getLabel('LBL_Shop_Report_Reasons_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewOrderStatus(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('OrderStatus'); ?>"><?php echo Labels::getLabel('LBL_Order_Status_Management', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewOrderCancelReasons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('OrderCancelReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Cancel_Reasons_Management', $adminLangId); ?> </a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewOrderReturnReasons(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('OrderReturnReasons'); ?>"><?php echo Labels::getLabel('LBL_Order_Return_Reasons_Management', $adminLangId); ?> </a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewAbusiveWords(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('AbusiveWords'); ?>"><?php echo Labels::getLabel('LBL_Abusive_Keyword', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewTestimonial(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Testimonials'); ?>"><?php echo Labels::getLabel('LBL_Testimonials_Management', $adminLangId); ?> </a></li>
                        <?php } ?>

                    </ul>
                </li>
            <?php } ?>

            <?php
            if (
                    $objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Blog', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewBlogPostCategories(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('BlogPostCategories'); ?>"><?php echo Labels::getLabel('LBL_Blog_Post_Categories', $adminLangId); ?></a></li>
                            <?php
                        }
                        if ($objPrivilege->canViewBlogPosts(AdminAuthentication::getLoggedAdminId(), true)) {
                            ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('BlogPosts'); ?>"><?php echo Labels::getLabel('LBL_Blog_Posts', $adminLangId); ?></a></li>
                            <?php
                        }
                        if ($objPrivilege->canViewBlogContributions(AdminAuthentication::getLoggedAdminId(), true)) {
                            ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('BlogContributions'); ?>"><?php echo Labels::getLabel('LBL_Blog_Contributions', $adminLangId); ?> <?php if ($blogContrCount) { ?><span class='badge'>(<?php echo $blogContrCount; ?>)</span><?php } ?></a></li>
                            <?php
                        }
                        if ($objPrivilege->canViewBlogComments(AdminAuthentication::getLoggedAdminId(), true)) {
                            ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('BlogComments'); ?>"><?php echo Labels::getLabel('LBL_Blog_Comments', $adminLangId); ?> <?php if ($blogCommentsCount) { ?><span class='badge'>(<?php echo $blogCommentsCount; ?>)</span><?php } ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php
            if (
                    $objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_SEO', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewMetaTags(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('MetaTags'); ?>"><?php echo Labels::getLabel('LBL_Meta_Tags_Management', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewUrlRewrite(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('UrlRewriting'); ?>"><?php echo Labels::getLabel('LBL_Url_Rewriting', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewImageAttributes(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ImageAttributes'); ?>"><?php echo Labels::getLabel('LBL_Image_Attributes', $adminLangId); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php
            if ($objPrivilege->canViewShippingPackages(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewShippingManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPickupAddresses(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewTrackingRelationCode()
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></a>
                    <ul>
                        <?php if ($objPrivilege->canViewShippingPackages(AdminAuthentication::getLoggedAdminId(), true) && FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('shippingPackages'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Packages', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewShippingManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('shippingProfile'); ?>"><?php echo Labels::getLabel('LBL_Shipping_Profile', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewTrackingRelationCode()) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('TrackingCodeRelation'); ?>"><?php echo Labels::getLabel('LBL_Tracking_Code_Relation', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewPickupAddresses(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('PickupAddresses'); ?>"><?php echo Labels::getLabel('LBL_Pickup_Addresses', $adminLangId); ?></a></li>
                        <?php } ?>

                        <?php if ($objPrivilege->canViewShippedProducts(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ShippedProducts'); ?>"><?php echo Labels::getLabel('LBL_Shipped_Products', $adminLangId); ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($objPrivilege->canViewTax(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sales_tax', $adminLangId); ?></a>
                    <ul>
                        <li><a href="<?php echo UrlHelper::generateUrl('TaxStructure'); ?>"><?php echo Labels::getLabel('LBL_Tax_Structure', $adminLangId); ?></a></li>
                        <li><a href="<?php echo UrlHelper::generateUrl('Tax'); ?>"><?php echo Labels::getLabel('LBL_Tax_Management', $adminLangId); ?></a></li>
                    </ul>
                </li>
            <?php } ?>
            <!--System Settings-->
            <?php
            if (
                    $objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPlugins(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewPaymentMethods(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true) ||
                    $objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true)
            ) {
                ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_System_Settings', $adminLangId); ?></a>
                    <ul>

                        <?php if ($objPrivilege->canViewGeneralSettings(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('configurations'); ?>"><?php echo Labels::getLabel('LBL_General_Settings', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewPlugins(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Plugins'); ?>"><?php echo Labels::getLabel('LBL_PLUGINS', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewThemeColor(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('ThemeColor'); ?>"><?php echo Labels::getLabel('LBL_Theme_Settings', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewCurrencyManagement(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('CurrencyManagement'); ?>"><?php echo Labels::getLabel('LBL_Currency_Management', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewCommissionSettings(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('Commission'); ?>"><?php echo Labels::getLabel('LBL_Commission_Settings', $adminLangId); ?></a></li>
                        <?php } ?>
                        <?php if ($objPrivilege->canViewSellerPackages(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                            <li><a href="<?php echo UrlHelper::generateUrl('SellerPackages'); ?>"><?php echo Labels::getLabel('LBL_Seller_Packages_Management', $adminLangId); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php if ($objPrivilege->canViewImportExport(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li><a href="<?php echo UrlHelper::generateUrl('ImportExport'); ?>"><?php echo Labels::getLabel('LBL_Import_Export', $adminLangId); ?></a>
                </li>
            <?php } ?>
            <?php if ($objPrivilege->canViewSitemap(AdminAuthentication::getLoggedAdminId(), true)) { ?>
                <li class="haschild"><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Sitemap', $adminLangId); ?></a>
                    <ul>
                        <li><a href="<?php echo UrlHelper::generateUrl('sitemap', 'generate'); ?>"><?php echo Labels::getLabel('LBL_Update_Sitemap', $adminLangId); ?></a></li>
                        <li><a href="<?php echo UrlHelper::generateFullUrl('custom', 'sitemap', array(), CONF_WEBROOT_FRONT_URL); ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_HTML', $adminLangId); ?></a></li>
                        <li><a href="<?php echo UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . 'sitemap.xml'; ?>" target="_blank"><?php echo Labels::getLabel('LBL_View_XML', $adminLangId); ?></a></li>
                    </ul>
                </li>
            <?php } ?>

            <?php if (CommonHelper::demoUrl()) { ?>
                <li>
                    <div class="m-4 text-center">
                        <a class="themebtn btn-brand outline block" href="https://www.yo-rent.com/suggest-feature.html" target="_blank">
                            <?php echo Labels::getLabel('LBL_SUGGEST_A_FEATURE', $adminLangId); ?>
                        </a>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</aside>
<!--left panel end here-->