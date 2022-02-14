<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?> </h5>


<div class="order-summary_list scroll scroll-y">
    <!-- List group -->
    <ul class="list-cart list-cart-checkout">
        <?php
        $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
        if ($product['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
            $productUrl = "javascript:void(0);";
            $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($product['selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $productTitle = (isset($product['op_product_name'])) ? $product['op_product_name'] : $product['selprod_title'];
        } else {
            $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['selprod_product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $productTitle = ($product['selprod_title']) ? $product['selprod_title'] : $product['op_product_name'];
        }
        ?>
        <li>
            <div class="cell cell_product">
                <div class="product-profile">
                    <div class="product-profile__thumbnail">
                        <a href="<?php echo $productUrl; ?>">
                            <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $productTitle; ?>" title="<?php echo $productTitle; ?>" />
                            <span class="product-qty"><?php echo $product['op_qty']; ?></span>
                        </a>
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
                            /* $duration = CommonHelper::getDifferenceBetweenDates($product['opd_rental_start_date'], $product['opd_rental_end_date'], $product['selprod_user_id'], $product['opd_rental_type']); */
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cell cell_price">
                <div class="product-price">
                    <?php echo CommonHelper::displayMoneyFormat($product['opd_rental_price']); ?>
                </div>
            </div>
        </li>
    </ul>
</div>

<div class="cart-summary">
    <ul>
        <?php if (isset($cartSummary['cartTotal']) && $cartSummary['cartTotal'] > 0) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Subtotal', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
            </li>
        <?php } ?>
        <?php if (isset($cartSummary['total_security']) && $cartSummary['total_security'] > 0) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Rental_Security', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['total_security']); ?></span>
            </li>
        <?php } ?>
        <?php if (isset($cartSummary['cartTaxTotal']) && $cartSummary['cartTaxTotal'] > 0) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTaxTotal']); ?></span>
            </li>
        <?php } ?>
        <?php if ($cartSummary['originalShipping']) { ?>
            <li>
                <span class="label"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></span>
                <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['shippingTotal']); ?></span>
            </li>
        <?php } ?>
        <li class="hightlighted">
            <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
            <span class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']); ?></span>
        </li>
    </ul>
</div>