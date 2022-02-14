<?php
$productUrl = !$isAppUser ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : 'javascript:void(0)';
$shopUrl = !$isAppUser ? UrlHelper::generateUrl('Shops', 'View', array($product['shop_id'])) : 'javascript:void(0)';
$imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
?>
<ul class="list-cart list-shippings" data-list="SHIPPING SUMMARY">
    <li class="shipping-select  shipping-select-row">
        <div class="shop-name">
            <h6>
                <i class="icn">
                    <svg class="svg" width="20px" height="20px">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                        </use>
                    </svg>
                </i> <?php echo $product['shop_name']; ?>
            </h6>
        </div>
        <div class="shipping-method">
            <?php
            $isAvailableForShipping = true;
            if ($cart_type != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            
            $rates = isset($shippedByItemArr[$shipLevel]['rates']) ? $shippedByItemArr[$shipLevel]['rates'] : [];
            $sellerProductId = $product['selprod_id'];
            if ($cart_type == applicationConstants::PRODUCT_FOR_RENT) {
                $sellerProductId = $selProdid;
            }

            $priceListCount = !empty($rates) && isset($rates[$sellerProductId]) ? count($rates[$sellerProductId]) : 0;
            if ($priceListCount > 0) {
                $name = current($rates[$sellerProductId])['code'];
                echo '<select class="form-control custom-select" name="shipping_services[' . $name . ']">';
                foreach ($rates[$sellerProductId] as $key => $shippingcharge) {
                    $selected = '';
                    if (!empty($orderShippingData)) {
                        foreach ($orderShippingData as $shipdata) {
                            if ($shipdata['opshipping_code'] == $name && ($key == $shipdata['opshipping_carrier_code'] . "|" . $shipdata['opshipping_label'] || $key == $shipdata['opshipping_rate_id'])) {
                                $selected = 'selected=selected';
                                break;
                            }
                        }
                    }
                    $shipAmount = $shippingcharge['cost'];
                    if ($isFreeShipEnable && $shopLevelTotalAmount >= $isFreeShipAmount) {
                        $shipAmount = 0;
                    }
                    
                    echo '<option ' . $selected . ' value="' . $key . '">' . $shippingcharge['title'] .   ' ( '. $shippingcharge['shiprate_min_duration'] .' Day(s) -  ' . CommonHelper::displayMoneyFormat($shipAmount) . ' ) </option>';
                    
                }
                echo '</select>';
            } elseif ($product['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                echo Labels::getLabel('MSG_Product_is_not_available_for_shipping', $siteLangId);
                $isAvailableForShipping = false;
            }
            } else {
                echo Labels::getLabel('LBL_Shipping_not_applicable_on_extended_orders', $siteLangId);
            }
            ?>
        </div>
    </li>
    <li>
        <div class="product-profile">
            <div class="product-profile__thumbnail">
                <a href="<?php echo $productUrl; ?>">
                    <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>"
                        alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                </a>
            </div>
            <div class="product-profile__data">
                <div class="title">
                    <a class="" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a>
                </div>
                <?php if (isset($product['options']) && count($product['options'])) {
                    $optionStr = '';
                    foreach ($product['options'] as $option) {
                        $optionStr .= $option['optionvalue_name'] . '|';
                    }
                    echo '<div class="options"><p class="">'. rtrim($optionStr, '|'). '</p></div>';
                } ?>
            </div>
        </div>
        <?php if (!$isAvailableForShipping) { ?>
        <div class="cell cell_action">
            <ul class="actions">
                <li>
                    <a href="javascript:void(0);" onclick="cart.remove('<?php echo md5($product['key']); ?>', 'checkout')" title="<?php echo Labels::getLabel('Lbl_Remove_From_Cart', $siteLangId);?>">
                        <svg class="svg" width="24px" height="24px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                            </use>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
        <?php } ?>
    </li>
</ul>