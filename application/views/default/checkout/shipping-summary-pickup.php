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
                    $addrPhone = $addresses['addr_dial_code'] . ' ' . $addresses['addr_phone'];
                    ?>
                    <p class="phone-txt"><i class="fas fa-mobile-alt"></i> <?php echo $addrPhone; ?></p>
                <?php } ?>
            </div>
            <div class="review-block__link" role="cell">
                <a  href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?>" onClick="showAddressList()" class="btn btn-brand btn-sm mr-2">
                    <svg class="svg  mr-0">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#edit"></use>
                    </svg>
                </a>
                <a onclick="showAddressFormDiv(0);" title="<?php echo Labels::getLabel('LBL_Add_New_Address', $siteLangId); ?>"  name="addNewAddress" href="javascript:void(0)" class="btn btn-brand btn-sm">
                    <svg class="svg mr-0">
                        <use xlink:href="/images/retina/sprite.svg#add" href="/images/retina/sprite.svg#add"></use>
                    </svg>
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

        <?php
        ksort($shippingRates);
        $levelNo = -1;

        foreach ($shippingRates as $pickUpBy => $levelItems) {
            ?>
            <ul class="list-cart list-shippings" data-list="PICKUP SUMMARY-BUY">
                <?php
                if (isset($levelItems['products']) && count($levelItems['products']) > 0 && $pickUpBy == 0) {
                    $productData = current($levelItems['products']);
                    ?>
                    <li class="shipping-select shipping-select-column">
                        <div class="shop-name">
                            <h6>
                                <i class="icn">
                                    <svg class="svg" width="16px" height="16px">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                                    </use>
                                    </svg>
                                </i>
                                <?php echo ($pickUpBy == Shipping::LEVEL_SHOP) ? $productData['shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?>
                            </h6>

                            <!-- [ PICKUP SELECT HTML GOES HERE -->
                            <?php
                            if ($levelNo != $pickUpBy) {
                                $seletedSlotId = '';
                                $seletedSlotDate = '';
                                $seletedAddrId = '';
                                if (!empty($levelItems['pickup_address'])) {
                                    $address = $levelItems['pickup_address'];
                                    $seletedSlotId = $address['time_slot_id'];
                                    $seletedSlotDate = $address['time_slot_date'];
                                    $seletedAddrId = $address['addr_id'];
                                }

                                $recordId = ($pickUpBy == 0) ? 0 : $product['shop_id'];
                                ?>
                                <div class="mr-3 shipping-edit shipping-method js-slot-addr-<?php echo $pickUpBy; ?>" data-addr-id="<?php echo $seletedAddrId; ?>">
                                    <input type="hidden" name="slot_id[<?php echo $pickUpBy; ?>]" class="js-slot-id" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotId; ?>">
                                    <input type="hidden" name="slot_date[<?php echo $pickUpBy; ?>]" class="js-slot-date" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotDate; ?>">

                                    <?php
                                    if (count($levelItems['pickup_options']) > 0) {
                                        $lblText = (!empty($levelItems['pickup_address'])) ? Labels::getLabel('LBL_Edit', $siteLangId) : Labels::getLabel('LBL_SELECT_PICKUP', $siteLangId);
                                        ?>
                                        <a class="link link-text pickupAddressBtn-<?php echo $pickUpBy; ?>-js" href="javascript:void(0)" onclick="getSellProductsPickupAddresses(<?php echo $pickUpBy; ?>, <?php echo $recordId; ?>)"><?php echo $lblText; ?></a>
                                    <?php } ?>
                                </div>
                            <?php } ?>    
                            <!-- ] -->
                        </div>
                    </li>
                    <?php
                }
                if (isset($levelItems['products'])) {
                    $levelItemCount = 1;
                    foreach ($levelItems['products'] as $product) {
                        $productUrl = !$isAppUser ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : 'javascript:void(0)';
                        $shopUrl = !$isAppUser ? UrlHelper::generateUrl('Shops', 'View', array($product['shop_id'])) : 'javascript:void(0)';
                        $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');

                        if ($levelNo != $pickUpBy) {
                            if (count($levelItems['products']) > 0 && $pickUpBy != 0) {
                                ?>
                                <li class="shipping-select">
                                    <div class="shop-name">
                                        <h6>
                                            <i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                                                </use>
                                                </svg>
                                            </i> <?php echo $product['shop_name']; ?>
                                        </h6>

                                        <!-- [ PICKUP SELECT HTML GOES HERE -->
                                        <?php
                                        if ($levelNo != $pickUpBy) {
                                            $seletedSlotId = '';
                                            $seletedSlotDate = '';
                                            $seletedAddrId = '';
                                            if (!empty($levelItems['pickup_address'])) {
                                                $address = $levelItems['pickup_address'];
                                                $seletedSlotId = $address['time_slot_id'];
                                                $seletedSlotDate = $address['time_slot_date'];
                                                $seletedAddrId = $address['addr_id'];
                                            }

                                            $recordId = ($pickUpBy == 0) ? 0 : $product['shop_id'];
                                            ?>
                                            <div class="mr-3 shipping-edit shipping-method js-slot-addr-<?php echo $pickUpBy; ?>" data-addr-id="<?php echo $seletedAddrId; ?>">
                                                <input type="hidden" name="slot_id[<?php echo $pickUpBy; ?>]" class="js-slot-id" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotId; ?>">
                                                <input type="hidden" name="slot_date[<?php echo $pickUpBy; ?>]" class="js-slot-date" data-level="<?php echo $pickUpBy; ?>" value="<?php echo $seletedSlotDate; ?>">

                                                <?php
                                                if (count($levelItems['pickup_options']) > 0) {
                                                    $lblText = (!empty($levelItems['pickup_address'])) ? Labels::getLabel('LBL_Edit', $siteLangId) : Labels::getLabel('LBL_SELECT_PICKUP', $siteLangId);
                                                    ?>
                                                    <a class="link link-text pickupAddressBtn-<?php echo $pickUpBy; ?>-js" href="javascript:void(0)" onclick="getSellProductsPickupAddresses(<?php echo $pickUpBy; ?>, <?php echo $recordId; ?>)"><?php echo $lblText; ?></a>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>    
                                        <!-- ] -->
                                    </div>
                                </li>
                                <?php
                            }
                        }
                        ?>
                        <li class="">
                            <div class="product-profile">
                                <div class="product-profile__thumbnail">
                                    <a href="<?php echo $productUrl; ?>">
                                        <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                                    </a>
                                </div>
                                <div class="product-profile__data">
                                    <div class="title">
                                        <a class="" href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $product['product_name']; ?></a>
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
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php if ($levelItemCount == count($levelItems['products'])) { ?>
                            <li>
                                <div class="picked-address shop-address js-slot-addr_<?php echo $pickUpBy; ?>" style="<?php echo (empty($levelItems['pickup_address'])) ? "display:none;" : ""; ?>">
                                    <i class="icn">
                                        <svg class="svg" width="20px" height="20px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#manage-shop">
                                        </use>
                                        </svg>
                                    </i> 
                                    <span class="js-address-detail-<?php echo $pickUpBy; ?>">
                                        <?php if (!empty($levelItems['pickup_address'])) { ?>
                                            <p><?php
                                                $address = $levelItems['pickup_address'];

                                                $fromTime = date('H:i', strtotime($address["time_slot_from"]));
                                                $toTime = date('H:i', strtotime($address["time_slot_to"]));
                                                echo $address['addr_name'] . ', ' . $address['addr_address1'];
                                                if (strlen($address['addr_address2']) > 0) {
                                                    echo ", " . $address['addr_address2'];
                                                }

                                                echo $address['addr_city'] . ", " . $address['state_name'];
                                                echo $address['country_name'] . ", " . $address['addr_zip'];
                                                ?>
                                                <i class="fas fa-mobile-alt"></i> <?php echo $address['addr_dial_code'] . ' ' . $address['addr_phone']; ?>
                                                <br /><span class="time-txt"><i class="fas fa-calendar-day"></i><?php echo FatDate::format($address["time_slot_date"]) . ' ' . $fromTime . ' - ' . $toTime; ?>
                                                </span>
                                            </p>
                                        <?php } ?>
                                    </span>
                                </div>
                            </li>
                        <?php } ?>
                        <?php
                        $levelNo = $pickUpBy;
                        $levelItemCount++;
                        ?>
                        <?php if (isset($levelItems['products']) && count($levelItems['products']) == 1) { ?>
                        </ul> <?php } ?>
                    <?php
                }
            }

            if (isset($levelItems['products']) && count($levelItems['products']) > 1) {
                ?>
                </ul>
                <?php
            }
        }
        ?>
    </div>
    <div class="step_foot">
        <div class="checkout-actions">
            <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="showAddressList();"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
            <?php if ($hasPhysicalProd) { ?>
                <a class="btn btn-brand btn-wide " onClick="setUpPickUpForSell();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } else { ?>
                <a class="btn btn-brand btn-wide " onClick="loadPaymentSummary();" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
            <?php } ?>
        </div>
    </div>
</div>
<style>
    .fa-mobile-alt {margin : 0 5px;}
</style>