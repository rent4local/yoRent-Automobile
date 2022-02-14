<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$user_is_buyer = 0;
if (UserAuthentication::isUserLogged()) {
    $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
}
$rentalSecutityTotal = 0;
if ($user_is_buyer > 0 || (!UserAuthentication::isUserLogged())) { ?>
<a href="javascript:void(0);" title="<?php echo Labels::getLabel('LBL_Cart', $siteLangId);?>" data-trigger-cart="side-cart" class="cart item-circle">
    <i class="icn icn-cart">
        <svg class="svg">
            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite-header.svg#bag">
            </use>
        </svg>
    </i>
    <?php if($totalCartItems > 0 ) { ?> 
    <span
        class="cartQuantity "><?php echo (Cart::CART_MAX_DISPLAY_QTY < $totalCartItems) ? Cart::CART_MAX_DISPLAY_QTY . '+' : $totalCartItems; ?></span>
    <span class="hide-sm"><?php echo Labels::getLabel("LBL_Item(s)", $siteLangId); ?></span>
    <?php } ?>
</a>
<div class="side-cart" id="side-cart" data-close-on-click-outside-cart="side-cart">
    <div class="side-cart_head">
        <a href="javascript:void(0)" class="" data-target-close-cart="side-cart">
            <i class="icn icn-close">
                <svg class="svg" width="10px">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#arrow-left">
                    </use>
                </svg>
            </i>
        </a>
        <h6><strong><?php echo Labels::getLabel('LBL_ITEMS', $siteLangId); ?>(<?php echo $totalCartItems; ?>)</strong></h6>
    </div>
    <?php if ($totalCartItems > 0) { ?>
    <div class="side-cart_body scroll scroll-y">
        <div class="short-detail">
            <ul class="list-cart">
                <?php if (count($products)) {
                            foreach ($products as $product) {
                                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                                    $rentalSecutityTotal += $product['sprodata_rental_security'] * $product['quantity'];
                                }
                                $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                                $shopUrl = UrlHelper::generateUrl('Shops', 'View', array($product['shop_id']));

                                $productName = isset($product['product_name']) ? $product['product_name'] : $product['selprod_title'];
                                if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($product['selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                    $productUrl = "javascript:void(0);";
                                } else {
                                    $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "EXTRA-SMALL", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                }
                                ?>

                <li class="<?php
                                echo (!$product['in_stock']) ? 'disabled' : '';
                                echo 'physical_product_tab-js';
                                ?>">
                    <div class="cell cell_product">
                        <div class="product-profile">
                            <div class="product-profile__thumbnail">
                                <a href="<?php echo $productUrl; ?>">
                                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo $productName; ?>"
                                        title="<?php echo $productName; ?>">
                                </a>
                                <span class="product-qty"><?php echo $product['quantity']; ?></span>

                            </div>
                            <div class="product-profile__data">
                                <div class="title">
                                    <a title="<?php echo $productName; ?>"
                                        href="<?php echo $productUrl; ?>"><?php echo ($product['selprod_title']) ? $product['selprod_title'] : $productName; ?></a>
                                </div>


                                <div class="options">
                                    <?php if (isset($product['options']) && count($product['options'])) {
                                                        $count = 0;
                                                        foreach ($product['options'] as $option) {
                                                            echo ($count > 0) ? ' | ' : '';
                                                            echo $option['option_name'] . ':';
                                                            echo $option['optionvalue_name'];
                                                            $count++;
                                                        }
                                                    } ?>
                                    <?php if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                                    <p>
                                        <span><?php echo date('M d, Y', strtotime($product['rentalStartDate'])) . ' / ';
                                                        echo date('M d, Y', strtotime($product['rentalEndDate'])); ?></span>
                                    </p>
                                    <?php } ?>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="cell cell_price">
                        <?php if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                        <div class="product_price">
                            <span class="item__price">
                                <?php echo CommonHelper::displayMoneyFormat(($product['theprice'] * $product['quantity'])); ?>
                            </span>
                        </div>
                        <?php } else { ?>
                        <div class="product_price">
                            <span class="item__price">
                                <?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?>
                            </span>
                            <?php if ($product['special_price_found']) { ?>
                            <span
                                class="text--normal text--normal-secondary text-nowrap"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="cell cell_action">
                        <a href="javascript:void(0)" class=""
                            onclick="cart.remove('<?php echo md5($product['key']); ?>')"
                            title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                            <svg class="svg" width="24px" height="24px"
                                title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                </use>
                            </svg>
                        </a>
                    </div>
                    <?php if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                    <div class="addons-products">
                        <ul>
                            <li>
                                <span
                                    class="lbl"><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></span>
                                <span
                                    class="price"><?php echo CommonHelper::displayMoneyFormat(($product['sprodata_rental_security'] * $product['quantity'])); ?></span>
                            </li>

                            <?php if (isset($product['addonsData']) && !empty($product['addonsData'])) { ?>
                            <?php foreach ($product['addonsData'] as $addKey => $addVal) { ?>
                            <li>
                                <a href="javascript:void(0)" class="cross"
                                    onclick="cart.remove('<?php echo md5($addVal['key']); ?>')"
                                    title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                                    <i class="fas fa-times"></i>
                                </a>

                                <span class="lbl"><?php echo $addVal['selprod_title']; ?></span>
                                <span class="price">
                                    <?php echo CommonHelper::displayMoneyFormat($addVal['theprice'] * $addVal['quantity']); ?>
                                </span>
                            </li>
                            <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>

                </li>

                <?php
                            }
                        } else {
                            echo Labels::getLabel('LBL_Your_cart_is_empty', $siteLangId);
                        }
                        ?>
            </ul>
        </div>
    </div>
    <div class="side-cart_foot">
        <div class="cart-summary">
            <ul class="">
                <li>
                    <?php if ($cartSummary['cartType'] == applicationConstants::PRODUCT_FOR_SALE) { ?>
                    <span class="label"><?php echo Labels::getLabel('LBL_Sale_Amount', $siteLangId); ?></span>
                    <?php } else { ?>
                    <span class="label"><?php echo Labels::getLabel('LBL_Rental_Amount', $siteLangId); ?></span>
                    <?php } ?>
                    <span
                        class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
                </li>
                <?php if ($rentalSecutityTotal > 0) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Rental_Security', $siteLangId); ?></span>
                    <span class="value"><?php echo CommonHelper::displayMoneyFormat($rentalSecutityTotal); ?></span>
                </li>
                <?php } ?>

                <?php if ($cartSummary['addonTotalAmount'] > 0) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Addons_Total_Amount', $siteLangId); ?></span>
                    <span
                        class="value"><?php echo CommonHelper::displayMoneyFormat($cartSummary['addonTotalAmount']); ?></span>
                </li>
                <?php } ?>
                <?php if (0 < $cartSummary['cartVolumeDiscount']) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span>
                    <span
                        class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
                </li>
                <?php } ?>
                <?php if (0 < $cartSummary['cartDurationDiscount']) { ?>
                <li>
                    <span class="label"><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?></span>
                    <span
                        class="value"> - <?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDurationDiscount']); ?></span>
                </li>
                <?php } ?>

                <?php
                        if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                            $netChargeAmt = $cartSummary['cartTotal'] + $cartSummary['addonTotalAmount'] + $rentalSecutityTotal - ((0 < $cartSummary['cartDurationDiscount']) ? $cartSummary['cartDurationDiscount'] : 0);
                        } else {
                            $netChargeAmt = $cartSummary['cartTotal'] + $cartSummary['addonTotalAmount'] + $rentalSecutityTotal - ((0 < $cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0);
                        }
                        ?>

                <li class="hightlighted">
                    <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
                    <span class="value"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></span>
                </li>
            </ul>
        </div>
        <div class="buttons-group">
            <a href="javascript:void(0);" onclick="cart.clear();" class="btn btn-outline-brand"><?php echo Labels::getLabel('LBL_CLEAR_CART', $siteLangId); ?></a>
            <a class="btn btn-brand" href="<?php echo UrlHelper::generateUrl('cart'); ?>"><?php echo Labels::getLabel('LBL_Go_To_Cart', $siteLangId); ?></a>

        </div>
    </div>
    <?php } else { ?>
    <div class="block--empty m-auto text-center">
        <img class="block__img" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/empty_cart.svg"
            alt="<?php echo Labels::getLabel('LBL_No_Record_Found', $siteLangId); ?>">
        <h4><?php echo Labels::getLabel('LBL_Your_Shopping_Bag_is_Empty', $siteLangId); ?></h4>
    </div>
    <?php } ?>
</div>
<?php } ?>

<script>
$(document).ready(function() {
    $('body').find('*[data-trigger-cart]').click(function() {
        var targetElmId = $(this).data('trigger-cart');
        var elmToggleClass = targetElmId + '--on';
        if ($('body').hasClass(elmToggleClass)) {
            $('body').removeClass(elmToggleClass);
        } else {
            $('body').addClass(elmToggleClass);
        }
    });

    $('body').find('*[data-target-close-cart]').click(function() {
        var targetElmId = $(this).data('target-close-cart');
        $('body').toggleClass(targetElmId + '--on');
    });

    $('body').mouseup(function(event) {
        if ($(event.target).data('triggerCart') != '' && typeof $(event.target).data('triggerCart') !==
            typeof undefined) {
            event.preventDefault();
            return;
        }

        $('body').find('*[data-close-on-click-outside-cart]').each(function(idx, elm) {
            var slctr = $(elm);
            if (!slctr.is(event.target) && !$.contains(slctr[0], event.target)) {
                $('body').removeClass(slctr.data('close-on-click-outside-cart') + '--on');
            }
        });

        $('body').find('*[data-target-close-cart]').click(function() {
            var targetElmId = $(this).data('target-close-cart');
            $('body').toggleClass(targetElmId + '--on');
        });

        $('body').mouseup(function(event) {
            if ($(event.target).data('triggerCart') != '' && typeof $(event.target).data(
                    'triggerCart') !== typeof undefined) {
                event.preventDefault();
                return;
            }

            $('body').find('*[data-close-on-click-outside-cart]').each(function(idx, elm) {
                var slctr = $(elm);
                if (!slctr.is(event.target) && !$.contains(slctr[0], event.target)) {
                    $('body').removeClass(slctr.data('close-on-click-outside-cart') +
                        '--on');
                }
            });
        });
    });
});
</script>