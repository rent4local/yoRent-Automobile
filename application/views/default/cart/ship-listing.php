<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<input type="hidden" name="product_for" value="<?php echo $cartType; ?>">
<div class="cart-blocks">
    <?php
    $fullfillmentType = Shipping::FULFILMENT_SHIP;
    $productsCount = count($products);
    if ($productsCount) {
        uasort($products, function ($a, $b) {
            return $b['fulfillment_type'] - $a['fulfillment_type'];
        });
        ?>
        <ul class="list-cart list-cart-page <?php echo (count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]) != $productsCount) ? '' : 'list-cart-page'; ?>">
                <?php if (count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]) != $productsCount) { ?>
                <li class="minus-space">
                    <div class="delivery-info">
                        <span> <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                            </use>
                            </svg><?php echo Labels::getLabel('MSG_SOME_ITEMS_NOT_AVAILABLE_FOR_SHIPPING', $siteLangId); ?>
                            
                            <a href="javascript:void(0);" onClick="showFullfillmentPopup(<?php echo Shipping::FULFILMENT_SHIP;?>)"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                            
                            <?php if (count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]) == $productsCount) { ?>
                                <a href="javascript:void(0);"
                                   onClick="listCartProducts(<?php echo Shipping::FULFILMENT_PICKUP; ?>);"
                                   class=""><?php echo Labels::getLabel('LBL_Pickup_Entire_Order', $siteLangId); ?></a>
                               <?php } ?>
                        </span>
                        <ul class="list-actions">
                            <li>
                                <a href="javascript:void(0);" onClick="removePickupOnlyProducts();">
                                    <svg class="svg" width="24px" height="24px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#cross"></use>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php
                foreach ($products as $key => $product) {
                    if ($product['fulfillment_type'] != Shipping::FULFILMENT_PICKUP) {
                        continue;
                    }
                    $isShipPickable = false;
                    $label = Labels::getLabel('LBL_Not_Shipable', $siteLangId);
                    
                    $checkedAddonsArr = [];
                    if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && isset($product['addonsData']) && !empty($product['addonsData'])) {
                        foreach ($product['addonsData'] as $val) {
                            $checkedAddonsArr[] = $val['selprod_id'];
                        }
                    }

                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                    $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));

                    if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $productUrl = "javascript:void(0);";
                        $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($product['selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $productTitle = (isset($product['product_name'])) ? $product['product_name'] : $product['selprod_title'];
                    } else {
                        $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $productTitle = ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
                    }
                    include(CONF_DEFAULT_THEME_PATH . 'cart/product-list.php');
                }
                ?>
            </ul>
            <ul class="list-cart list-cart-page">
            <?php } ?>
            <?php
            foreach ($products as $product) {
                if ($product['fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
                    continue;
                }
                $isShipPickable = true;
                $label = '';
                

                $checkedAddonsArr = [];
                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && isset($product['addonsData']) && !empty($product['addonsData'])) {
                    foreach ($product['addonsData'] as $val) {
                        $checkedAddonsArr[] = $val['selprod_id'];
                    }
                }

                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));

                if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $productUrl = "javascript:void(0);";
                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($product['selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $productTitle = (isset($product['product_name'])) ? $product['product_name'] : $product['selprod_title'];
                } else {
                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $productTitle = ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
                }
                include(CONF_DEFAULT_THEME_PATH . 'cart/product-list.php');
            }
            ?>
        </ul>
    <?php } ?>
    <?php if (0 < count($saveForLaterProducts)) { /* @TODO */ ?>
        <h5 class="cart-title"><?php echo Labels::getLabel('LBL_Save_For_later', $siteLangId); ?>
            (<?php echo count($saveForLaterProducts); ?>)</h5>
        <ul class="list-cart list-cart-page">
            <?php
            foreach ($saveForLaterProducts as $product) {
                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $productTitle = ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name'];
                include(CONF_DEFAULT_THEME_PATH . 'cart/product-list-saved-for-later.php');
            }
            ?>
        </ul>
    <?php } ?>
</div>