<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="shipping-summary" class="step">
    <div class="step_section">
        <div class="step_body">
            <ul class="review-block">
                <li>
                    <div class="review-block__label"> <?php
                    if ($hasPhysicalProd) {
                        echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                    }
                    ?> </div>

                    <div class="review-block__content">
                        <div class="delivery-address">
                            <p><?php echo $addresses['addr_name'] . ', ' . $addresses['addr_address1']; ?>
                                <?php
                            if (strlen($addresses['addr_address2']) > 0) {
                                echo ", " . $addresses['addr_address2'];
                                ?>
                                <?php } ?>
                            </p>
                            <p><?php echo $addresses['addr_city'] . ", " . $addresses['state_name'] . ", " . $addresses['country_name'] . ", " . $addresses['addr_zip']; ?>
                            </p>

                            <?php if (strlen($addresses['addr_phone']) > 0) { ?>
                            <p class="phone-txt"><i
                                    class="fas fa-mobile-alt"></i><?php echo $addresses['addr_phone']; ?>
                            </p>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="review-block__link">
                        <a  href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?>" onClick="showAddressList()" class="btn btn-brand btn-sm mr-2">
                            <svg class="svg  mr-0">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit"></use>
                            </svg>
                        </a>
                        <a onclick="showAddressFormDiv(0);" title="<?php echo Labels::getLabel('LBL_Add_New_Address', $siteLangId); ?>" name="addNewAddress" href="javascript:void(0)" class="btn btn-brand btn-sm">
                            <svg class="svg mr-0">
                                <use xlink:href="/images/retina/sprite.svg#add" href="/images/retina/sprite.svg#add"></use>
                            </svg>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- REMOVED SIGNATURE CODE FROM HERE --->
    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title">
                <?php
                    $cartObj = new Cart();
                    if ($cartObj->hasPhysicalProduct()) {
                        echo Labels::getLabel('LBL_Shipping_Summary', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_REVIEW_CHECKOUT', $siteLangId);
                    }
                    ?>
            </h5>
        </div>
        <div class="step_body">
            <?php
            ksort($shippingRates);
            if (!empty($shippingRates)) { 
               foreach ($shippingRates as $shippedBy => $shippedByItemArr) {
                    $shopLevelTotalAmount = (isset($shippedByItemArr['shopTotalAmount'])) ? $shippedByItemArr['shopTotalAmount'] : 0;
                
                    ksort($shippedByItemArr);
                    foreach ($shippedByItemArr as $shipLevel => $items) {
                        switch ($shipLevel) {
                            case Shipping::LEVEL_ORDER:
                            case Shipping::LEVEL_SHOP:
                                if (isset($items['products']) && !empty($items['products'])) {
                                    $productData = $items['products'];
                                    $shippingCharges = $items['rates'];
                                    $productInfo = current($productData);
                                    require('shipping-summary-group.php');
                                }
                                break;
                            case Shipping::LEVEL_PRODUCT:
                                if (isset($items['products']) && !empty($items['products'])) {
                                    foreach ($items['products'] as $selProdid => $product) {
                                        $isFreeShipEnable = $product['shop_is_free_ship_active'];
                                        $isFreeShipAmount = $product['shop_free_shipping_amount'];
                                        require('shipping-summary-product.php');
                                    }
                                }
                                if (isset($items['digital_products']) && !empty($items['digital_products'])) {
                                    foreach ($items['digital_products'] as $selProdid => $product) {
                                        require('shipping-summary-product.php');
                                    }
                                }
                                break;
                        }
                    }
                }
            } else {
                echo Labels::getLabel('LBL_Products_is_not_eligible_for_shipping', $siteLangId);
            }
            ?>
        </div>
        <div class="step_foot">
            <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="showAddressList();">
                <?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>
            </a>
            <?php if (!empty($shippingRates)) { ?>
            <?php if ($hasPhysicalProd) { ?>
            <a class="btn btn-brand btn-wide " onClick="setUpShippingMethod();" href="javascript:void(0)">
                <?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?>
            </a>
            <?php } else { ?>
            <a class="btn btn-brand btn-wide " onClick="loadPaymentSummary();" href="javascript:void(0)">
                <?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?>
            </a>
            <?php } ?>
            <?php } ?>

        </div>

    </div>