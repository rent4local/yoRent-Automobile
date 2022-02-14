<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$cartTotal = isset($scartSummary['cartTotal']) ? $scartSummary['cartTotal'] : 0;
$cartAdjustableAmount = isset($scartSummary['cartAdjustableAmount']) ? $scartSummary['cartAdjustableAmount'] : 0;
$discountTotal = isset($scartSummary['cartDiscounts']) && isset($scartSummary['cartDiscounts']['coupon_discount_total']) ? $scartSummary['cartDiscounts']['coupon_discount_total'] : 0;
?>
<?php if (count($subscriptions)) { ?>
<div id="shipping-summary" class="step">
    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title"><?php echo Labels::getLabel('LBL_REVIEW_CHECKOUT', $siteLangId); ?></h5>
        </div>
        <div class="step_body">
        <?php 
        foreach ($subscriptions as $subscription) { 
            $spackageName = isset($subscription['spackage_name']) ? $subscription['spackage_name'] : '';
            $spackagePrice = isset($subscription[SellerPackagePlans::DB_TBL_PREFIX . 'price']) ? $subscription[SellerPackagePlans::DB_TBL_PREFIX . 'price'] : '';
            $interval = isset($subscription[SellerPackagePlans::DB_TBL_PREFIX . 'trial_interval']) ? $subscription[SellerPackagePlans::DB_TBL_PREFIX . 'trial_interval'] : 0;
            ?> 
        
            <ul class="list-cart list-shippings" data-list="SHIPPING SUMMARY">
                <li>
                    <div class="cell cell_product">
                        <div class="product-profile">
                            <div class="product-profile__data">
                                <div class="title">
                                    <?php echo $spackageName;?>  
                                </div>
                                <div class="options">
                                    <p><?php echo SellerPackagePlans::getPlanPeriod($subscription, $spackagePrice); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="cell cell_price">
                        <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($spackagePrice); ?></div>
                    </div>
                    <div class="cell cell_action">
                        <ul class="actions">
                            <li>
                                <a href="javascript:void(0)" onclick="subscription.remove('<?php echo md5($subscription['key']); ?>')" class="icons-wrapper">
                                <i class="icn">
                                    <svg class="svg"><use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#bin"></use></svg>
                                </i>
                            </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        <?php  } ?>
        </div>
        <div class="step_foot">
        <?php
            $amount = CommonHelper::displayMoneyFormat($cartTotal - $cartAdjustableAmount - $discountTotal, true, false, true, false, true);
            if ($amount > 0) {
                $paymentText = Labels::getLabel('LBL_Proceed_To_Pay', $siteLangId);
            } else {
                $paymentText = Labels::getLabel('LBL_Proceed_To_Confirm', $siteLangId);
            } ?>
        
            <div class="checkout-actions">
                <a href="javascript:void(0)" class="btn btn-brand btn-wide confirmReview"><?php echo $paymentText; ?></a>
            </div>
        </div>
    </div>
</div>    
<?php } ?>