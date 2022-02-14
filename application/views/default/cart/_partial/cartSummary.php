<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
<div class="coupons-applied">
    <div class="">
        <h6>
            <i class="icn">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tick">
                    </use>
                </svg>
            </i>
            <?php echo $cartSummary['cartDiscounts']['coupon_code']; ?>
        </h6>
        <p>
            <?php 
                $arr = ['{AMOUNT}' => CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total'])];
                echo CommonHelper::replaceStringData(Labels::getLabel("LBL_YOU_SAVED_ADDITIONAL_{AMOUNT}", $siteLangId), $arr);
            ?>
        </p>
    </div>
    <button class="close-layer" onClick="removePromoCode()"></button>
</div>
<?php } else { ?>
<div class="coupons">
    <button class="btn btn-outline-brand btn-block btn-coupons btn-coupon--js disabled-input" onclick="getPromoCode()">
        <i class="icn">
            <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#coupon">
                </use>
            </svg>
        </i>
        <span><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></span>
    </button>
</div>
<?php } ?>
<div class="cart-summary">
    <ul class="">
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
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['rentalSecurityTotal']); ?></span>
        </li>
        <?php } ?>

        <?php if ($cartSummary['addonTotalAmount'] > 0) { ?>
        <li>
            <span class="label"><?php echo Labels::getLabel('LBL_Addons_Total_Amount', $siteLangId); ?></span> 
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['addonTotalAmount']); ?></span>
        </li>
        <?php } ?>

        <?php if ($cartSummary['cartVolumeDiscount']) { ?>
        <li class=" ">
            <span class="label"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span>
            <span class="value txt-success"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
        </li>
        <?php } ?>
        <?php if ($cartSummary['cartDurationDiscount']) { ?>
        <li class=" ">
            <span class="label"><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?></span>
            <span class="value txt-success"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDurationDiscount']); ?></span>
        </li>
        <?php } ?>
        <?php if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
        <li class=" ">
            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
            <span class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
        </li>
        <?php } ?>

        <?php if (!FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
        <li class=" ">
            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
            <span class="value txt-success"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
        </li>
        <?php } ?>
        <?php
        $netChargeAmt = $cartSummary['cartTotal'] + $cartSummary['addonTotalAmount'] + $cartSummary['rentalSecurityTotal'] - ((0 < $cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0) - ((0 < $cartSummary['cartDurationDiscount']) ? $cartSummary['cartDurationDiscount'] : 0);
        $netChargeAmt = $netChargeAmt - ((isset($cartSummary['cartDiscounts']['coupon_discount_total']) && 0 < $cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0);
        ?>
        <li class=" hightlighted">
            <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></span>
        </li>
    </ul>
    <input type="hidden" name="pickup_product_count" value="<?php echo $cartSummary['pickUpProductsCount'];?>" />
    <input type="hidden" name="ship_product_count" value="<?php echo $cartSummary['shipProductsCount'];?>" />
</div>
<?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
<p class="included"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $cartSummary['orderNetAmount']); ?> </p>
<?php } ?>

<?php /* if($fulfilmentType == Shipping::FULFILMENT_SHIP && $cartItemCount > $cartSummary['shipProductsCount']) { ?>
    <p class="included pt-0 pb-0 text-danger"><?php echo Labels::getLabel('LBL_Ship_item_not_available_msg', $siteLangId); ?></p>
<?php } elseif($fulfilmentType == Shipping::FULFILMENT_PICKUP && $cartItemCount > $cartSummary['pickUpProductsCount']) { ?>
<p class="included pt-0 pb-0 text-danger"><?php echo Labels::getLabel('LBL_Pickup_item_not_available_msg', $siteLangId); ?></p>
<?php } */ ?>

