<li class=" <?php echo md5($product['key']); ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
    <div class="cell cell_product">
        <div class="product-profile">
            <div class="product-profile__thumbnail">
                <?php if(isset($isShipPickable) && $isShipPickable == false) { ?>
                <div class="not-pick-ship">
                    <?php echo $label; ?>              
                </div>
                <?php } ?>
                <a href="<?php echo $productUrl; ?>">
                    <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $productTitle; ?>" title="<?php echo $productTitle; ?>">
                </a>
            </div>
            <div class="product-profile__data">
                <div class="title">
                    <a class="" href="<?php echo $productUrl; ?>">
                        <?php echo $productTitle; ?>
                    </a>
                </div>
                <div class="options">
                    <p class="">
                        <?php
                        if (isset($product['options']) && count($product['options'])) {
                            foreach ($product['options'] as $key => $option) {
                                echo (0 < $key) ? ' | ' : "";
                                echo $option['option_name'] . ':';
                                ?>
                                <span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
                                <?php
                            }
                        }
                        ?>
                    </p>
                </div>
                <div class="dates">
                    <?php if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                        <i class="icn">
                            <svg class="svg" width="12px" height="12px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#calendar">
                            </use>
                            </svg>
                        </i>

                        <span class="lable">
                            <?php echo date('M d, Y ', strtotime($product['rentalStartDate'])); ?>/
                            <?php echo date('M d, Y ', strtotime($product['rentalEndDate'])); ?>
                        </span>
                    <?php } ?>
                </div>

            </div>
        </div>
        <?php
        $enableQuantityUpdate = false;
        if (($product['productFor'] == applicationConstants::PRODUCT_FOR_SALE && $product['selprod_stock'] > $product['quantity']) || ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && $product['sprodata_rental_stock'] > $product['quantity']) && $cartType != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $enableQuantityUpdate = true;
        }
        ?>
    </div>
    <div class="cell cell_qty">
        <div class="product-quantity">
            <div class="quantity quantity-2 quantity--js" data-rentalstock="<?php echo $product['sprodata_rental_stock']; ?>" data-stock="<?php echo $product['selprod_stock']; ?>" data-minsaleqty="<?php echo $product['selprod_min_order_qty']; ?>" data-minrentqty="<?php echo $product['sprodata_minimum_rental_quantity']; ?>">
                <span class="decrease  decrease-js">
                    <i class="icn">
                        <svg class="svg" width="16px" height="16px">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus"></use>
                        </svg>
                    </i>
                </span>
                <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                    <input name="qty_<?php echo md5($product['key']); ?>" data-key="<?php echo md5($product['key']); ?>" class="qty-input cartQtyTextBox productQty-js" value="<?php echo $product['quantity']; ?>" type="text" <?php echo (!$enableQuantityUpdate) ? "disabled" : " "; ?> />
                </div>
                <span class="increase <?php echo ($enableQuantityUpdate) ? " increase-js " : "not-allowed "; ?>">
                    <i class="icn">
                        <svg class="svg" width="16px" height="16px">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus"></use>
                        </svg>
                    </i>
                </span>
            </div>
        </div>
    </div>
    <div class="cell cell_price">
        <div class="product-price">
            <?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?></div>
    </div>
    <div class="cell cell_action">
        <ul class="actions">
            <?php
            $productAmount = ($product['theprice'] * $product['quantity']);

            if ($product['sellerProdType'] != SellerProduct::PRODUCT_TYPE_ADDON && $cartType != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) { /* @TODO */
                $showAddToFavorite = true;
                if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                    $showAddToFavorite = false;
                }
                if ($showAddToFavorite) {
                    $jsFunc = 0 < $product['ufp_id'] ? 'removeFromFavorite(' . $product['selprod_id'] . ')' : 'markAsFavorite(' . $product['selprod_id'] . ')';
                    ?>
                    <li> <a href="javascript:void(0)" class="heart-wrapper--js heart-wrapper <?php echo ($product['ufp_id']) ? 'is-active' : ''; ?>" onclick="<?php echo $jsFunc; ?>" data-id="<?php echo $product['selprod_id']; ?>" title="<?php echo ($product['ufp_id']) ? Labels::getLabel('LBL_Remove_product_from_favourite_list', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_favourite_list', $siteLangId); ?>">
                            <i class="icn icn-fav-heart">
                                <svg class="svg icon-unchecked--js" <?php if ($product['ufp_id']) { ?> style="display:none" <?php } ?>>
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#fav-heart">
                                </use>
                                </svg>
                                <svg class="svg icon-checked--js" <?php if (!$product['ufp_id']) { ?> style="display:none" <?php } ?>>
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#fav-heart-fill">
                                </use>
                                </svg>
                            </i>
                        </a> </li>
                    <?php
                }
                ?>
                <li>
                    <a href="javascript:void(0)" class="" onClick="moveToSaveForLater('<?php echo md5($product['key']); ?>',<?php echo $product['selprod_id']; ?>, <?php echo $fullfillmentType; ?>);" title="<?php echo Labels::getLabel('LBL_Move_to_Save_For_Later', $siteLangId); ?>"> <i class="icn">
                            <svg class="svg" width="24px" height="24px" title="">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#saveforlater">
                            </use>
                            </svg></i> </a>
                </li>
            <?php } ?>
            <li>
                <a href="javascript:void(0)" onclick="cart.remove('<?php echo md5($product['key']); ?>', 'cart')">
                    <i class="icn">
                        <svg class="svg" width="24px" height="24px" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove"></use>
                        </svg>
                    </i>
                </a>
            </li>
        </ul>
    </div>
    <?php
    if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
        $duration = CommonHelper::getDifferenceBetweenDates($product['rentalStartDate'], $product['rentalEndDate'], $product['selprod_user_id'], $product['sprodata_duration_type']);

        $label = Labels::getLabel('LBL_Details', $siteLangId);
        if (isset($product['attachedAddonsList']) && count($product['attachedAddonsList']) > 0) {
            $label = Labels::getLabel('LBL_Addons_and_Details', $siteLangId) . '<span class="count"> ' . count($checkedAddonsArr) . '</span>';
        }
        ?>
        <div class="addons">
            <button class="addons_trigger collapsed" type="button" data-toggle="collapse" data-target="#collapseExample_<?php echo md5($product['key']); ?>" aria-expanded="false" aria-controls="collapseExample"> <span class="txt"><?php echo $label; ?></span> <i class="icn"></i>
            </button>

            <div class="collapse" id="collapseExample_<?php echo md5($product['key']); ?>">
                <?php
                $addonTotalAmount = 0;
                if (isset($product['attachedAddonsList']) && !empty($product['attachedAddonsList'])) {
                    ?>
                    <ul class="addons-list scroll scroll-x">
                        <?php
                        $uncheckedAddons = [];
                        foreach ($product['attachedAddonsList'] as $addonKey => $addVal) {
                            $productKey = '';
                            $checkedAddon = false;
                            if (in_array($addVal['selprod_id'], $checkedAddonsArr)) {
                                $productKey = Cart::CART_KEY_PREFIX_PRODUCT . $addVal['selprod_id'] . $product['rentalStartDate'] . $product['rentalEndDate'];
                                $productKey = md5(base64_encode(json_encode($productKey)));
                                $checkedAddon = true;
                                $addonTotalAmount += ($addVal['selprod_price'] * $product['quantity']);
                            } else {
                                $uncheckedAddons[$addonKey] = $addVal;
                                continue;
                            }
                            ?>
                            <li>
                                <label class="addons-list_label" for="">
                                    <div class="checkbox">
                                        <input type="checkbox" data-cartkey="<?php echo $productKey; ?>" data-selprodid="<?php echo $addVal['selprod_id']; ?>" data-mainproductkey="<?php echo md5($product['key']); ?>" name="check_addons" onClick="addAddonToCart(this);" id="check_addons_<?php echo $addVal['selprod_id']; ?>" <?php echo ($checkedAddon) ? 'checked' : ''; ?> />
                                    </div>
                                    <div>
                                        <div class="title">
                                            <a href="javascript:void(0);" title="<?php echo html_entity_decode($addVal['selprod_title']); ?>">
                                                <?php echo html_entity_decode($addVal['selprod_title']); ?>
                                            </a>
                                        </div>
                                        <div class="price">
                                            <strong><?php echo CommonHelper::displayMoneyFormat($addVal['selprod_price']); ?></strong>
                                            / <?php echo Labels::getLabel('LBL_Per_Qty', $siteLangId); ?>
                                        </div>
                                    </div>
                                </label>
                            </li>
                        <?php } ?>
                        <?php
                        if (!empty($uncheckedAddons)) {
                            foreach ($uncheckedAddons as $addonKey => $addVal) {
                                ?>
                                <li>
                                    <label class="addons-list_label" for="">
                                        <div class="checkbox">
                                            <input type="checkbox" data-cartkey="<?php echo $productKey; ?>" data-selprodid="<?php echo $addVal['selprod_id']; ?>" data-mainproductkey="<?php echo md5($product['key']); ?>" name="check_addons" onClick="addAddonToCart(this);" id="check_addons_<?php echo $addVal['selprod_id']; ?>" />
                                        </div>
                                        <div>
                                            <div class="title">
                                                <a href="javascript:void(0);" title="<?php echo html_entity_decode($addVal['selprod_title']); ?>">
                                                    <?php echo html_entity_decode($addVal['selprod_title']); ?>
                                                </a>
                                            </div>
                                            <div class="price">
                                                <strong><?php echo CommonHelper::displayMoneyFormat($addVal['selprod_price']); ?></strong>
                                                / <?php echo Labels::getLabel('LBL_Per_Qty', $siteLangId); ?>
                                            </div>
                                        </div>
                                    </label>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                <?php } ?>
                <ul class="list-addons-specification">
                    <li data-toggle="tooltip" data-placement="top" title="<?php echo Labels::getLabel('LBL_Duration', $siteLangId); ?>">
                        <i class="icn">
                            <svg class="svg" width="16px" height="16px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#calendar">
                            </use>
                            </svg>
                        </i> <span class="lable">
                            <?php echo CommonHelper::displayProductRentalDuration($duration, $product['sprodata_duration_type'], $siteLangId); ?>
                        </span>
                    </li>
                    <li data-toggle="tooltip" data-placement="top" title="<?php echo Labels::getLabel('LBL_Price_', $siteLangId); ?>"> <i class="icn">
                            <svg class="svg" width="16px" height="16px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#price">
                            </use>
                            </svg>
                        </i> <span class="lable">
                            <?php echo CommonHelper::displayMoneyFormat($productAmount + $addonTotalAmount, $siteLangId); ?>
                        </span>
                    </li>
                    <li data-toggle="tooltip" data-placement="top" title="<?php echo Labels::getLabel('LBL_Security', $siteLangId); ?>"> <i class="icn">
                            <svg class="svg" width="16px" height="16px">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#seurity-amount">
                            </use>
                            </svg>
                        </i> <span class="lable">
                            <?php echo CommonHelper::displayMoneyFormat(($product['sprodata_rental_security'] * $product['quantity']), $siteLangId); ?>
                        </span> </li>
                </ul>
            </div>
        </div>
    <?php } ?>
</li>