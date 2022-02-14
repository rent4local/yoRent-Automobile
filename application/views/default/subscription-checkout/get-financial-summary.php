<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
$cartAdjustableAmount = isset($cartSummary['cartAdjustableAmount']) ? $cartSummary['cartAdjustableAmount'] : 0;
$discountTotal = isset($cartSummary['cartDiscounts']) && isset($cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0;
$amount = CommonHelper::displayMoneyFormat($cartTotal - $cartAdjustableAmount - $discountTotal, true, false, true, false, true);
?>
<h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?></h5>
<div class="box box--white box--radius order-summary">
    <?php if ($spackage_type != SellerPackages::FREE_TYPE) { ?>
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
                <?php $arr =  ['{AMOUNT}' => CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total'])];
                        echo CommonHelper::replaceStringData(Labels::getLabel("LBL_YOU_SAVED_ADDITIONAL_{AMOUNT}", $siteLangId), $arr); ?>
            </p>
        </div>
        <button class="close-layer" onClick="removePromoCode()"> </button>

    </div>
    <?php } else { ?>
    <div class="coupons">
        <button class="btn btn-outline-brand btn-block btn-coupons" onclick="getPromoCode()">
            <i class="icn">
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#coupon">
                    </use>
                </svg>
            </i>
            <span><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></button></span>

    </div>
    <?php } ?>
    <?php } ?>
    <div class="order-summary__sections">
        <div class="cart-summary">
            <ul>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span>
                    <span
                        class="value"><?php echo CommonHelper::displayMoneyFormat($cartTotal, true, false, true, false, true); ?></span>
                </li>
                <?php if ($cartAdjustableAmount > 0) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Adjusted_Amount', $siteLangId); ?></span>
                    <span
                        class="value"><?php echo CommonHelper::displayMoneyFormat($cartAdjustableAmount, true, false, true, false, true); ?></span>
                </li>
                <?php } ?>
                <?php if ($discountTotal > 0) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
                    <span class="value">
                        <?php echo CommonHelper::displayMoneyFormat($discountTotal, true, false, true, false, true); ?></span>
                </li>
                <?php } ?>
                <li class="hightlighted">
                    <span class="label"><?php echo Labels::getLabel('LBL_You_Pay', $siteLangId); ?></span>
                    <span class="value">
                        <?php echo $amount; ?></span>
                </li>
            </ul>

        </div>

    </div>
</div>