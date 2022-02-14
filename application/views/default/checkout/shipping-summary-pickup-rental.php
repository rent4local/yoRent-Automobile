<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="shipping-summary" class="step">
    <ul class="list-group review-block">
        <li class="">
            <div class="review-block__label">
                <?php echo Labels::getLabel('LBL_Billing_to:', $siteLangId); ?>
            </div>
            <div class="review-block__content" role="cell">
                <p><?php echo $addresses['addr_name'] . ', ' . $addresses['addr_address1']; ?>
                    <?php
                    if (strlen($addresses['addr_address2']) > 0) {
                        echo ", " . $addresses['addr_address2'];
                        ?>
                    <?php } ?>
                </p>
                <p><?php echo $addresses['addr_city'] . ", " . $addresses['state_name'] . ", " . $addresses['country_name'] . ", " . $addresses['addr_zip']; ?>
                </p>
                <?php
                if (strlen($addresses['addr_phone']) > 0) {
                    $addrPhone = $addresses['addr_phone'];
                    ?>
                    <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $addrPhone; ?></p>
                <?php } ?>
            </div>
            <div class="review-block__link" role="cell">
                <a class="link" href="javascript:void(0);" onClick="showAddressList()">
                    <span><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></span>
                </a>
            </div>
        </li>
    </ul>

    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title">
                <?php
                $cartObj = new Cart();
                if ($cartObj->hasPhysicalProduct()) {
                    echo Labels::getLabel('LBL_Pickup_Summary', $siteLangId);
                } else {
                    echo Labels::getLabel('LBL_REVIEW_CHECKOUT', $siteLangId);
                }
                ?>
            </h5>
        </div>
        <div class="step_body">
            <ul class="list-cart list-shippings">
                <?php
                $shopId = 0;
                $srNo = 1;
                foreach ($products as $cartKey => $product) {
                    if (isset($product['sellerProdType']) && $product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                        continue;
                    }
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                    //$shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));
                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $selProdId = $product['selprod_id'];
                    if ($shopId != $product['shop_id']) {
                        $shopId = $product['shop_id'];
                        ?>
                        <li class="shipping-select">
                            <div class="shop-name">
                                <h6 class="h6">
                                    <i class="icn">
                                        <svg class="svg" width="16px" height="16px">
                                        <use
                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                                        </use>
                                        </svg>
                                    </i> <?php echo $product['shop_name']; ?>
                                </h6>
                            </div>
                        </li>
                    <?php } ?>
                    <li class="list--js_<?php echo $srNo; ?>">
                        <div class="product-profile">
                            <div class="product-profile__thumbnail">
                                <a href="<?php echo $productUrl; ?>">
                                    <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>"
                                         alt="<?php echo $product['product_name']; ?>"
                                         title="<?php echo $product['product_name']; ?>">
                                </a>

                            </div>
                            <div class="product-profile__data">
                                <div class="title">
                                    <a class=""
                                       href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a>
                                </div>
                                <div class="options">
                                    <p class="">
                                        <?php
                                        if (isset($product['options']) && count($product['options'])) {
                                            $optionStr = '';
                                            foreach ($product['options'] as $option) {
                                                $optionStr .= $option['optionvalue_name'] . '|';
                                            }
                                            echo rtrim($optionStr, '|');
                                        }
                                        ?>
                                    </p>
                                    <?php
                                    if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                                        $duration = CommonHelper::getDifferenceBetweenDates($product['rentalStartDate'], $product['rentalEndDate'], $product['selprod_user_id'], $product['sprodata_duration_type']);
                                        ?>

                                        <div class="item__specification">
                                            <?php echo Labels::getLabel("LBL_From_:", $siteLangId) . ' ' . date('M d, Y', strtotime($product['rentalStartDate'])); ?>
                                        </div>
                                        <div class="item__specification">
                                            <?php echo Labels::getLabel("LBL_To_:", $siteLangId) . ' ' . date('M d, Y', strtotime($product['rentalEndDate'])); ?>
                                        </div>

                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="<?php echo $cartKey; ?>" class="pickup-address-js" id="product-js-<?php echo $srNo; ?>" value="<?php echo (!empty($shippingRates[$cartKey])) ? $shippingRates[$cartKey]['addr_id'] : ""; ?>" />

                        <?php if (empty($shippingRates[$cartKey])) { ?>
                            <div class="shipping-edit pickupAddJs">
                                <a class="link link-text" href="javascript:void(0)" onclick="displayPickupAddress('<?php echo $selProdId; ?>', '<?php echo $shopId; ?>', '<?php echo $srNo; ?>')"><?php echo Labels::getLabel('LBL_SELECT_PICKUP', $siteLangId); ?>
                                </a>
                            </div>
                        <?php } ?>

                        <div class="shipping-edit">
                            <a class="link link-text" <?php if (empty($shippingRates[$cartKey])) { ?>style="display:none"
                               <?php } ?> id="pickup-address-edit-js-<?php echo $srNo; ?>" href="javascript:void(0);"
                               onclick="displayPickupAddress('<?php echo $selProdId; ?>', '<?php echo $shopId; ?>', '<?php echo $srNo; ?>')">
                                   <?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?>
                            </a>
                        </div>
                    </li>
                    <li><div class="picked-address" style="<?php echo (empty($shippingRates[$cartKey])) ? 'display: none;' : ""; ?>">
                            <i class="icn">
                                <svg class="svg" width="20px" height="20px">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                                </use>
                                </svg>
                            </i> 
                            <span class="js-address-detail-<?php echo $srNo; ?>">
                                <?php if (!empty($shippingRates[$cartKey])) { ?>
                                    <p><?php
                                        $address = $shippingRates[$cartKey];
                                        echo $address['addr_name'] . ', ' . $address['addr_address1'];
                                        if (strlen($address['addr_address2']) > 0) {
                                            echo ", " . $address['addr_address2'];
                                        }

                                        echo $address['addr_city'] . ", " . $address['state_name'];
                                        echo $address['country_name'] . ", " . $address['addr_zip'];
                                        ?>
                                        <i class="fas fa-mobile-alt"></i><?php echo $address['addr_phone']; ?>
                                    </p>
                                <?php } ?>
                            </span>
                        </div></li>
                    <?php
                    $srNo++;
                }
                ?>
            </ul>
        </div>
        <div class="step_foot">
            <div class="checkout-actions">
                <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)"
                   onclick="showAddressList();"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
                <a class="btn btn-brand btn-wide " onClick="setUpPickup();"
                   href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            </div>
        </div>
    </div>

</div>
<style>
    .phone-txt {display : block;}
</style>