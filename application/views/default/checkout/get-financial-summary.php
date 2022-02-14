<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?> - <?php echo count($products); ?>
    <?php echo Labels::getLabel('LBL_item(s)', $siteLangId); ?></h5>
<?php /* ?> <div class="section__action js-editCart" style="display:block;"><a href="javascript:void(0);"
  onClick="editCart()"
  class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Edit_Cart', $siteLangId);?></a> </div>
  <?php */ ?>
<?php /*  if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
  <div class="applied-coupon">
  <span><?php echo Labels::getLabel("LBL_Coupon", $siteLangId); ?>
  "<strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong>"
  <?php echo Labels::getLabel("LBL_Applied", $siteLangId); ?></span> <a href="javascript:void(0)"
  onClick="removePromoCode()"
  class="btn btn-brand btn-sm"><?php echo Labels::getLabel("LBL_Remove", $siteLangId); ?></a>
  </div>
  <?php } else { ?>
  <div class="coupon"> <a class="coupon-input btn btn-brand btn-block" href="javascript:void(0)"
  onclick="getPromoCode()"><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></a> </div>

  <?php } */ ?>

<div class="order-summary_list scroll scroll-y">
    <!-- List group -->
    <ul class="list-cart list-cart-checkout">
        <?php
        $totalSecurity = 0;
        foreach ($products as $product) {
            $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
            if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                $productUrl = "javascript:void(0);";
                $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($product['selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $productTitle = (isset($product['product_name'])) ? $product['product_name'] : $product['selprod_title'];
            } else {
                $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $productTitle = ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
            }

            $addonProductList = [];
            if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && isset($product['addonsData']) && !empty($product['addonsData'])) {
                $addonProductList = $product['addonsData'];
            }
            ?>
            <li>
                <div class="cell cell_product">
                    <div class="product-profile">
                        <div class="product-profile__thumbnail">
                            <a href="<?php echo $productUrl; ?>">
                                <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>"
                                     alt="<?php echo $productTitle; ?>" title="<?php echo $productTitle; ?>">
                            </a>
                            <span class="product-qty"><?php echo $product['quantity']; ?></span>
                        </div>
                        <div class="product-profile__data">
                            <div class="title">
                                <a class="" href="<?php echo $productUrl; ?>" title="<?php echo $product['product_name'] ?>">
                                    <?php echo $product['selprod_title'] ?>
                                </a>
                            </div>
                            <div class="options">
                                <p class="">
                                    <?php
                                    if (isset($product['options']) && count($product['options'])) {
                                        $optionStr = '';
                                        foreach ($product['options'] as $key => $option) {
                                            $optionStr .= $option['optionvalue_name'] . '|';
                                        }
                                        echo rtrim($optionStr, '|');
                                    }
                                    ?>
                                </p>
                                <?php
                                $securityAmount = 0;
                                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                                    $securityAmount = $product['sprodata_rental_security'] * $product['quantity'];
                                    $totalSecurity += $securityAmount * $product['quantity'];
                                    $duration = CommonHelper::getDifferenceBetweenDates($product['rentalStartDate'], $product['rentalEndDate'], $product['selprod_user_id'], $product['sprodata_duration_type']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cell cell_price">
                    <div class="product-price">
                        <?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?>
                    </div>
                </div>
                <?php
                //@todo
                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && 0) {
                    ?>
                    <ul class="list-specification">
                        <li>
                            <?php echo Labels::getLabel("LBL_From:", $siteLangId) . ' ' . date('M d, Y h:i A', strtotime($product['rentalStartDate'])); ?>
                        </li>
                        <li>
                            <?php echo Labels::getLabel("LBL_To_:", $siteLangId) . ' ' . date('M d, Y h:i A', strtotime($product['rentalEndDate'])); ?>
                        </li>
                        <li>
                            <?php echo Labels::getLabel("LBL_Duration:", $siteLangId) . ' ' . CommonHelper::displayProductRentalDuration($duration, $product['sprodata_duration_type'], $siteLangId); ?>
                        </li>
                        <li>
                            <?php echo Labels::getLabel("LBL_Rental_Price:", $siteLangId) . ' ' . CommonHelper::displayMoneyFormat($product['sprodata_rental_price'], $siteLangId); ?>
                        </li>
                        <li>
                            <?php echo Labels::getLabel("LBL_Security_Amount:", $siteLangId) . ' ' . CommonHelper::displayMoneyFormat($product['sprodata_rental_security'], $siteLangId); ?>
                        </li>
                    </ul>
                <?php } ?>
                <?php if (!empty($addonProductList)) { ?>
                    <div class="addons-products">
                        <ul>
                            <?php foreach ($addonProductList as $addonKey => $addonProduct) { ?>
                                <li>
                                    <a class="cross" href="javascript:void(0);" onClick="cart.remove('<?php echo md5($addonKey); ?>', 'checkout');"><i class="fas fa-times"></i></a>
                                    <span class="lbl"><?php echo html_entity_decode($addonProduct['selprod_title']); ?></span>
                                    <span class="price"><?php echo CommonHelper::displayMoneyFormat($addonProduct['theprice'] * $addonProduct['quantity']); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
</div>
<!-- Total -->
<div class="cart-summary">
    <ul>
        <li>
            <?php if ($cartSummary['cartType'] == applicationConstants::PRODUCT_FOR_SALE) { ?>
                <span class="label"><?php echo Labels::getLabel('LBL_Sale_Amount', $siteLangId); ?></span> 
            <?php } else { ?>
                <span class="label"><?php echo Labels::getLabel('LBL_Rental_Amount', $siteLangId); ?></span> 
            <?php } ?>
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
        </li>
        <?php if ($cartSummary['rentalSecurityTotal'] > 0) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Rental_Security', $siteLangId); ?></span>
                <span
                    class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['rentalSecurityTotal']); ?></span>
            </li>
        <?php } ?>
        <?php if ($cartSummary['addonTotalAmount'] > 0) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Addons_Total_Amount', $siteLangId); ?></span> <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['addonTotalAmount']); ?></span>
            </li> 
        <?php } ?>
        
        <?php if ($cartSummary['cartVolumeDiscount']) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Loyalty/Volume_Discount', $siteLangId); ?></span>
                <span
                    class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
            </li>
        <?php } ?>
        <?php if ($cartSummary['cartDurationDiscount']) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Duration', $siteLangId); ?></span> <span class="value">
                    -
                    <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDurationDiscount']); ?></span>
            </li>
        <?php } ?>
        <?php if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span> <span
                    class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
            </li>
        <?php } ?>
        <?php
        if (/* 0 < $shippingAddress && */isset($cartSummary['taxOptions'])) {
            foreach ($cartSummary['taxOptions'] as $taxName => $taxVal) {
                ?>
                <li>
                    <span class="label"><?php echo $taxVal['title']; ?></span>
                    <span class="value"><?php echo CommonHelper::displayMoneyFormat($taxVal['value']); ?></span>
                </li>
                <?php
            }
        }
        ?>
        <?php if (!FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
                <span
                    class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
            </li>
        <?php } ?>
        <?php if ($cartSummary['originalShipping']) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['shippingTotal']); ?></span>
            </li>
        <?php } ?>
        <?php
        if (!empty($cartSummary['cartRewardPoints'])) {
            $appliedRewardPointsDiscount = CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']);
            ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Reward_point_discount', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($appliedRewardPointsDiscount); ?></span>
            </li>
        <?php } ?>
        <?php if (array_key_exists('roundingOff', $cartSummary) && $cartSummary['roundingOff'] != 0) { ?>
            <li>
                <span
                    class="label"><?php echo (0 < $cartSummary['roundingOff']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['roundingOff']); ?></span>
            </li>
        <?php } ?>
        <?php
        /* if (0 == $shippingAddress) $orderNetAmt = $orderNetAmt - $cartSummary['cartTaxTotal'];  */
        ?>

        <?php
        if (isset($cartSummary['pendingLateCharges']) && $cartSummary['pendingLateCharges'] > 0) {
            ?>
            <li class="late-charges">
                <span class="label"><?php echo Labels::getLabel('LBL_Pending_Charges', $siteLangId); ?></span>
                <span
                    class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['pendingLateCharges']); ?></span>
            </li>
            <?php
        }
        $orderNetAmt = $cartSummary['orderNetAmount'];
        ?>
        <li class="hightlighted">
            <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($orderNetAmt); ?></span>
        </li>

    </ul>
</div>
