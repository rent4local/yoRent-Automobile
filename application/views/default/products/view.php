<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmBuyProduct->setFormTagAttribute('class', 'form form--horizontal');
$buyQuantity = $frmBuyProduct->getField('quantity');
$buyQuantity->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
$buyQuantity->addFieldTagAttribute('data-page', 'product-view');

if ($fulfillmentType == Shipping::FULFILMENT_ALL || $fulfillmentType == Shipping::FULFILMENT_PICKUP) {
    $timeSlotHours = FatApp::getConfig("CONF_TIME_SLOT_ADDITION", FatUtility::VAR_INT, 2);
    $rentalAvailableDate = date('Y-m-d H:i:s', strtotime('+ ' . $timeSlotHours . ' hours', strtotime(date('Y-m-d H:i:s'))));
} else {
    $rentalAvailableDate = date('Y-m-d H:i:s', strtotime('+ ' . $minShipDuration . ' days', strtotime(date('Y-m-d'))));
}
$rentalAvailableDate = date("Y-m-d H:0:0", ceil(strtotime($rentalAvailableDate) / (60 * 30)) * (60 * 30));
$addTimeToDate = 0;

if (strtotime($product['sprodata_rental_available_from']) > strtotime($rentalAvailableDate)) {
    $rentalAvailableDate = $product['sprodata_rental_available_from'];
}

if (!empty($extendedOrderData)) {
    $rentalAvailableDate = $extendedOrderData['opd_rental_end_date'];
    $addTimeToDate = 60 * 60 * 1000;
}

$availableForSale = false;
if (strtotime($product['selprod_available_from']) <= strtotime(date('Y-m-d h:i:s')) && $product['selprod_active'] == applicationConstants::ACTIVE) {
    $availableForSale = true;
}

$availableForRent = false;
if (strtotime($product['sprodata_rental_available_from']) <= strtotime(date('Y-m-d h:i:s')) && $product['sprodata_rental_active'] == applicationConstants::ACTIVE) {
    $availableForRent = true;
}
?>

<!-- Detail Section Start -->
<section class="section product-details--js">
    <div class="container">
        <div class="row">
            <!-- [ PRODUCT IMAGES SECTION START -->
            <div class="col-xl-7 col-md-12">
                <div class="detail_view_wrapper">
                    <div class="detail_gallery-view">
                        <?php if ($productImagesArr) { ?>
                        <?php
                            foreach ($productImagesArr as $afile_id => $image) {
                                $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                                $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'DETAIL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
                            ?>
                        <div class="post_view">
                            <div class="post_media">
                                <a href="<?php echo $originalImgUrl ?>">
                                    <img src="<?php echo $mainImgUrl; ?>">
                                </a>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'DETAIL', 0)), CONF_IMG_CACHE_TIME, '.jpg');
                            $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'ORIGINAL', 0)), CONF_IMG_CACHE_TIME, '.jpg');
                            ?>
                        <div class="post_view">
                            <div class="post_media">
                                <a href="<?php echo $originalImgUrl ?>">
                                    <img src="<?php echo $mainImgUrl; ?>">
                                </a>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <!-- ] --->
            <div class="col-xl-5 col-md-12">
                <?php
                $extendOrderId = 0;
                if ($frmBuyProduct->getField('extend_order')) {
                    $extOrderFld = $frmBuyProduct->getField('extend_order');
                    $extendOrderId = $extOrderFld->value;
                }

                $rentalStartDateFld = $frmBuyProduct->getField('rental_start_date');
                $rentalEndDateFld = $frmBuyProduct->getField('rental_end_date');

                $rentalStartDateFld->addFieldTagAttribute('class', 'rental_start_datetime');
                $rentalEndDateFld->addFieldTagAttribute('class', 'rental_end_datetime');

                $qtyField = $frmBuyProduct->getField('quantity');
                if ($extendOrderId > 0) {
                    $rentalStartDateFld->addFieldTagAttribute('disabled', 'true');
                    $qtyField->addFieldTagAttribute('disabled', 'true');
                }
                echo $frmBuyProduct->getFormTag();
                ?>
                <div class="product-detail">
                    <div class="product-detail_head">
                        <div class="product-title">
                            <h2><?php echo $product['selprod_title']; ?></h2>
                            <div class="row justify-content-between">
                                <div class="col">
                                    <?php if (!empty($product['brand_name'])) { ?>
                                    <p>
                                        <span class="txt-gray-light">
                                            <?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:
                                        </span>
                                        <?php echo $product['brand_name']; ?>
                                    </p>
                                    <?php } ?>
                                </div>
                                <div class="col">
                                    <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0  && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) { ?>

                                    <label class="add-compare" for="">

                                        <input
                                            class="checkbox-input compare_product_js_<?php echo $product['selprod_id']; ?> comp_product_cat_<?php echo $product['prodcat_id']; ?> compProductsJs"
                                            onclick="compare_products(this,<?php echo $product['selprod_id']; ?>)"
                                            data-catid=<?php echo $product['prodcat_id']; ?> title="Compare Product"
                                            name="compare" value="1" type="checkbox"
                                            <?php if ($prodInCompList == 1) {
                                                                                                                                                                                                                                                                                                                                                                                                        echo 'checked="checked"';
                                                                                                                                                                                                                                                                                                                                                                                                    } ?>>
                                        <svg class="svg add">
                                            <use
                                                xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#add-compare">
                                            </use>
                                        </svg>
                                        <svg class="svg tick">
                                            <use
                                                xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#added-compare">
                                            </use>
                                        </svg>
                                        <?php echo Labels::getLabel('LBL_Compare', $siteLangId); ?>
                                    </label>

                                    <?php } ?>
                                </div>
                            </div>

                        </div>


                        <div class="action-icons">
                            <?php
                            $isWishList = isset($isWishList) ? $isWishList : 0;
                            if (!isset($showAddToFavorite)) {
                                $showAddToFavorite = true;
                                if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                                    $showAddToFavorite = false;
                                }
                            }
                            $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
                            $favVar = 0;
                            if ($favVar == applicationConstants::NO) {
                                $jsFunc = 0 < $product['ufp_id'] ? 'removeFromFavorite(' . $product['selprod_id'] . ')' : 'markAsFavorite(' . $product['selprod_id'] . ')';
                            ?>
                            <div class="heart-wrapper <?php echo ($product['ufp_id']) ? 'is-active' : ''; ?>"
                                onclick="<?php echo $jsFunc; ?>" data-id="<?php echo $product['selprod_id']; ?>">
                                <a href="javascript:void(0)"
                                    title="<?php echo ($product['ufp_id']) ? Labels::getLabel('LBL_Remove_product_from_favourite_list', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_favourite_list', $siteLangId); ?>">
                                    <i class="icn icn-fav-heart">
                                        <svg class="svg">
                                            <use
                                                xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#fav-heart">
                                            </use>
                                        </svg>
                                    </i>
                                </a>
                            </div>
                            <?php } else { ?>
                            <div class="heart-wrapper wishListLink-Js <?php echo ($product['is_in_any_wishlist']) ? 'is-active' : ''; ?>"
                                data-id="<?php echo $product['selprod_id']; ?>">
                                <a href="javascript:void(0)"
                                    onClick="viewWishList(<?php echo $product['selprod_id']; ?>, this, event);"
                                    title="<?php echo ($product['is_in_any_wishlist']) ? Labels::getLabel('LBL_Remove_product_from_your_wishlist', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_your_wishlist', $siteLangId); ?>">
                                    <i class="icn icn-fav-heart">
                                        <svg class="svg">
                                            <use
                                                xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#fav-heart">
                                            </use>
                                        </svg>
                                    </i>
                                </a>
                            </div>
                            <?php
                            }
                            ?>

                            <!-- [ Social Share Section -->
                            <!--<div class="dropdown"> -->

                            <!-- Button trigger modal -->

                            <a href="javascript:void(0);" class="no-after share-icon" data-toggle="modal"
                                data-target="#shareModal">
                                <i class="icn icn-share">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share">
                                        </use>
                                    </svg>
                                </i>
                            </a>

                            <!-- Modal -->
                            <div class="modal fade" id="shareModal" tabindex="-1" role="dialog"
                                aria-labelledby="shareModal" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLongTitle">
                                                <?php echo Labels::getLabel('LBL_Share', $siteLangId);?></h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="social-sharing">
                                                <li class="social-facebook">
                                                    <a class="st-custom-button" data-network="facebook"
                                                        data-url="<?php echo UrlHelper::generateFullUrl('Products', 'view', array($product['selprod_id'])); ?>/">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb">
                                                                </use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-twitter">
                                                    <a class="st-custom-button" data-network="twitter">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw">
                                                                </use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-pintrest">
                                                    <a class="st-custom-button" data-network="pinterest">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt">
                                                                </use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                                <li class="social-email">
                                                    <a class="st-custom-button" data-network="email">
                                                        <i class="icn"><svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope">
                                                                </use>
                                                            </svg></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- ] -->
                        </div>
                        <!-- ] -->
                    </div>
                    <?php
                    if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                        $rating = round($product['prod_rating'], 1);
                    ?>
                    <?php if (round($product['totReviews']) > 0) { ?>
                    <div class="product-rating">
                        <ul>
                            <?php for ($ii = 0; $ii < $rating; $ii++) { ?>
                            <li>

                            </li>
                            <?php } ?>
                        </ul>
                        <p>(<?php echo round($product['totReviews'], 1); ?>
                            <?php echo Labels::getLabel('LBL_Customer_Reviews', $siteLangId); ?>)</p>
                    </div>
                    <?php } ?>
                    <?php } ?>
                    <div class="product-detail__pricing">
                        <ul>
                            <?php if ($availableForRent) { ?>
                            <li>
                                <span
                                    class="lable"><?php echo Labels::getLabel('LBL_Starting_From', $siteLangId); ?></span>
                                <span class="price price-rent">
                                    <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?> <span
                                        class="slash">/<?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?></span>
                                </span>
                            </li>
                            <li>
                                <span
                                    class="lable"><?php echo Labels::getLabel('LBL_Original_Price', $siteLangId); ?></span>
                                <span
                                    class="price"><?php echo CommonHelper::displayMoneyFormat($product['selprod_cost']); ?></span>
                            </li>
                            <li>
                                <span class="lable"><?php echo Labels::getLabel('LBL_Security', $siteLangId); ?></span>
                                <span
                                    class="price"><?php echo CommonHelper::displayMoneyFormat($product['sprodata_rental_security']); ?>
                                    <span
                                        class="slash"><?php echo Labels::getLabel('LBL_(Refundable)', $siteLangId); ?></span>
                                    <i class="fa-info-circle" data-toggle="tooltip" data-placement="top"
                                        title="<?php echo Labels::getLabel('LBL_Security_Amount_is_refundable_after_product_return', $siteLangId); ?>"></i>
                                </span>
                            </li>
                            <?php } ?>
                            <?php if ($availableForSale) { ?>
                            <li>
                                <span class="lable">
                                    <?php echo Labels::getLabel('LBL_Selling_Price', $siteLangId); ?>
                                    <span
                                        class="price"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?></span>
                                </span>
                            </li>
                            <?php } ?>
                            <li>
                                <span
                                    class="lable"><?php echo Labels::getLabel('LBL_Minimum_Days_For_Shipping', $siteLangId); ?>
                                    <span class="price"><?php echo $minShipDuration; ?></span>
                                </span>
                            </li>

                            <?php if (!empty($product['size_chart'])) { ?>
                            <li>
                                <?php
                                    $chartFileRow = $product['size_chart'];
                                    $sizeChartUrl = CommonHelper::generateUrl('image', 'productSizeChart', array($chartFileRow['afile_record_id'], "ORIGINAL", $chartFileRow['afile_id']));
                                    ?>
                                <a href="<?php echo $sizeChartUrl; ?>"
                                    class="sizechart-image--js btn btn-outline-bottom"
                                    title=""><?php echo Labels::getLabel('LBL_VIEW_SIZE_CHART', $siteLangId); ?></a>
                            </li>
                            <?php } ?>
                            <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0) && count($orderCancelPenaltyRules) > 0) { ?>
                            <li>
                                <a href="#penaltyRules" class="btn btn-outline-bottom"
                                    rel="facebox"><?php echo Labels::getLabel('LBL_check_order_cancellation_policy', $siteLangId); ?></a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>

                    <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                    <div id="penaltyRules" style="display:none;">
                        <?php
                            if (!empty($orderCancelPenaltyRules)) {
                                echo '<table class="policy-list" border="1">
                                <tr>
                                <th>' . Labels::getLabel('LBL_Cancellation_Duration(Hours)', $siteLangId) . '</th>
                                <th>' .  Labels::getLabel('LBL_Refundable_Amount', $siteLangId)  . '</th>
                                </tr>';
                                foreach ($orderCancelPenaltyRules as $rule) {
                            ?>
                        <tr>
                            <td>
                                <?php echo $rule['ocrule_duration'] . ' ' . Labels::getLabel('LBL_Hours', $siteLangId); ?>

                            </td>
                            <td><?php echo  $rule['ocrule_refund_amount'] . ' %';  ?></td>
                            <?php /* echo sprintf(Labels::getLabel('LBL_if_Order_cancel_before_%s_hours_then_%s_amount_will_be_refunded', $siteLangId), $rule['ocrule_duration'], $rule['ocrule_refund_amount'] . '%'); */ ?>
                        </tr>
                        <?php
                                }
                                echo '</table>';
                            }
                            ?>
                    </div>
                    <?php } ?>

                    <div class="product-detail_options">
                        <div class="row">
                            <?php if (!empty($optionsFinalArr) && FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) { ?>
                            <div class="col-md-6">
                                <div class="caption-wraper">
                                    <label
                                        class="field_label"><?php echo Labels::getLabel('LBL_Select_the_Variations', $siteLangId); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <div class="dropdown dropdown-options">
                                            <button class="btn btn-outline-gray dropdown-toggle" type="button"
                                                data-toggle="dropdown" data-display="static" aria-haspopup="true"
                                                aria-expanded="false">
                                                <span> <?php echo $optionsFinalArr[$product['selprod_id']]; ?> </span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-anim">
                                                <ul class="nav nav-block">
                                                    <?php foreach ($optionsFinalArr as $optId => $optRow) { ?>
                                                    <li
                                                        class="nav__item  <?php echo ($optId == $product['selprod_id']) ? "is-active" : ""; ?>">
                                                        <a title="<?php echo $optRow; ?>"
                                                            class="dropdown-item nav__link "
                                                            href="<?php echo UrlHelper::generateUrl('Products', 'view', [$optId]); ?>">
                                                            <?php echo $optRow; ?>
                                                        </a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } elseif (!empty($optionRows) && !FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) { ?>
                            <div class="col-md-6">
                                <?php
                                    $selectedOptionsArr = $product['selectedOptionValues'];
                                    $count = 0;
                                    foreach ($optionRows as $key => $option) {
                                        $selectedOptionValue = $option['values'][$selectedOptionsArr[$key]]['optionvalue_name'];
                                        $selectedOptionColor = $option['values'][$selectedOptionsArr[$key]]['optionvalue_color_code'];
                                    ?>

                                <div class="h6 d-flex justify-content-between"><?php echo $option['option_name']; ?>

                                    <?php if (!empty($product['size_chart'])) { ?>

                                    <?php
                                                $chartFileRow = $product['size_chart'];
                                                $sizeChartUrl = CommonHelper::generateUrl('image', 'productSizeChart', array($chartFileRow['afile_record_id'], "ORIGINAL", $chartFileRow['afile_id']));
                                                ?>
                                    <a href="<?php echo $sizeChartUrl; ?>" class="size-chart sizechart-image--js"
                                        title="">
                                        <i class="icn">
                                            <svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#size-chart"
                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#size-chart">
                                                </use>
                                            </svg>

                                        </i>
                                        <?php echo Labels::getLabel('LBL_SIZE_CHART', $siteLangId); ?></a>

                                    <?php } ?>
                                </div>
                                <div class="dropdown dropdown-options">
                                    <button class="btn btn-outline-gray dropdown-toggle" type="button"
                                        data-toggle="dropdown" data-display="static" aria-haspopup="true"
                                        aria-expanded="false">
                                        <span>
                                            <?php if ($option['option_is_color']) { ?>
                                            <span class="colors"
                                                style="background-color:#<?php echo $selectedOptionColor; ?>;"></span>
                                            <?php } ?>
                                            <?php echo $selectedOptionValue; ?>
                                        </span>
                                    </button>
                                    <?php if ($option['values']) { ?>
                                    <div class="dropdown-menu dropdown-menu-anim">
                                        <ul class="nav nav-block">
                                            <?php
                                                        foreach ($option['values'] as $opVal) {
                                                            $isAvailable = true;
                                                            if (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
                                                                $optionUrl = UrlHelper::generateUrl('Products', 'view', array($product['selprod_id']));
                                                            } else {
                                                                $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
                                                                $optionUrlArr = explode("::", $optionUrl);
                                                                if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                                                                    $optionUrl = $optionUrlArr[0];
                                                                    $isAvailable = false;
                                                                }
                                                            }
                                                        ?>
                                            <li class="nav__item <?php
                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' is-active' : ' ';
                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                    echo (!$isAvailable) ? 'not--available' : '';
                                                                                    ?>">
                                                <?php if ($option['option_is_color'] && $opVal['optionvalue_color_code'] != '') { ?>
                                                <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                    selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>"
                                                    title="<?php
                                                                                                                                                                                                                echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                                                                                                                                                                ?>"
                                                    class="dropdown-item nav__link <?php
                                                                                                                                                                                                                                                    echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                                                                                                                                                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                                                                                                                                                                                    ?>"
                                                    href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                    <span class="colors"
                                                        style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name']; ?></a>
                                                <?php } else { ?>
                                                <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>"
                                                    selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>"
                                                    title="<?php
                                                                                                                                                                                                                echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                                                                                                                                                                ?>"
                                                    class="dropdown-item nav__link <?php
                                                                                                                                                                                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' ';
                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                                                                                                                                                                                    ?>"
                                                    href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                    <?php echo $opVal['optionvalue_name']; ?> </a>
                                                <?php } ?>
                                            </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                    <?php } ?>
                                </div>

                                <?php
                                        $count++;
                                    }
                                    ?>
                            </div>
                            <?php } ?>
                            <!-- ] -->
                            <div class="col-md-6">
                                <div class="qty-wrapper">
                                    <div class="h6"><?php echo $frmBuyProduct->getField('quantity')->getCaption(); ?>
                                    </div>
                                    <div class="quantity quantity-theme"
                                        data-stock="<?php echo $product['selprod_stock']; ?>">
                                        <span class="decrease decrease-js not-allowed"><i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                    <use
                                                        xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus">
                                                    </use>
                                                </svg>
                                            </i></span>
                                        <div class="qty-input-wrapper qty-wrapper--js"
                                            data-stock="<?php echo $product['selprod_stock']; ?>"
                                            data-minsaleqty="<?php echo $product['selprod_min_order_qty']; ?>"
                                            data-minrentqty="<?php echo $product['sprodata_minimum_rental_quantity']; ?>">
                                            <?php
                                            $qtyField = $frmBuyProduct->getField('quantity');
                                            $minQty = min($product['selprod_min_order_qty'], $product['sprodata_minimum_rental_quantity']);

                                            $qtyField->value = ($minQty == 0) ? 1 : $minQty;
                                            echo $frmBuyProduct->getFieldHtml('quantity');
                                            ?>
                                        </div>
                                        <span class="increase increase-js"><i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                    <use
                                                        xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus">
                                                    </use>
                                                </svg>
                                            </i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($availableForRent) { ?>
                <div class="card_collapsed">
                    <input class="switch_label" name="radio_for_rent" type="checkbox" checked>
                    <div class="card_expanded">
                        <div class="show_default">
                            <div class="default-detail">
                                <h2><span
                                        class="brand-color"><?php echo CommonHelper::displayMoneyFormat($product['rent_price'] * $product['sprodata_minimum_rental_duration']); ?></span>
                                    <?php echo Labels::getLabel('LBL_One_Time_Rental', $siteLangId); ?></h2>
                                <p class="black-60"><?php
                                                        $minimum_duration = $product['sprodata_minimum_rental_duration'] . ' ' . $rentalTypeArr[$product['sprodata_duration_type']];
                                                        echo sprintf(Labels::getLabel('LBL_Rent_for_%s', $siteLangId), $minimum_duration);
                                                        ?>
                                </p>
                            </div>
                        </div>
                        <lable class="switch_checkbox">
                            <div class="checkbox--handle">
                                <i class="switch_handle"></i>
                            </div>
                        </lable>
                        <div class="show_after">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="caption-wraper">
                                        <label
                                            class="field_label"><?php echo $frmBuyProduct->getField('rental_start_date')->getCaption(); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frmBuyProduct->getFieldHtml('rental_start_date'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="caption-wraper">
                                        <label
                                            class="field_label"><?php echo $frmBuyProduct->getField('rental_end_date')->getCaption(); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frmBuyProduct->getFieldHtml('rental_end_date'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php /*
                                  <ul class="price-list">
                                  <?php echo '<li>' . Labels::getLabel('LBL_Rental_Price', $siteLangId) . '  ' . CommonHelper::displayMoneyFormat($product['rent_price']) . '</li>'; ?>
                            </ul>
                            */ ?>


                            <?php if ($product['sprodata_rental_stock'] > 0) { ?>
                            <div class="bg-gray p-4 rounded mt-2 mb-3">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-xl-12 col-lg-12 col-md-12">
                                        <h6 class="m-0">
                                            <?php echo Labels::getLabel('LBL_Enter_Start_Date_And_End_Date_to_Calculate_Rental_Price', $siteLangId); ?>
                                        </h6>
                                        <p class="mt-2">
                                            <small><?php echo Labels::getLabel('LBL_Rental_Price', $siteLangId); ?>:</small>
                                            <small class="rental-price--js">
                                                <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?>
                                            </small> +
                                            <small><?php echo Labels::getLabel('LBL_Rental_Security', $siteLangId); ?>:</small>
                                            <small class="text-uppercase rental-security--js">
                                                <?php echo ($extendOrderId < 1) ? CommonHelper::displayMoneyFormat($product['sprodata_rental_security']) : Labels::getLabel('LBL_NA', $siteLangId); ?>
                                            </small>
                                        </p>
                                        <h6 class="mt-2">
                                            <?php echo Labels::getLabel('LBL_Total_Payment', $siteLangId); ?> :
                                            <span class="total-amount--js">
                                                <?php
                                                        /* $rentalSecurityAmount = $product['sprodata_rental_security'];
                                                          if ($extendOrderId > 1) {
                                                          $rentalSecurityAmount = 0;
                                                          }
                                                          $total = $product['rent_price'] + $rentalSecurityAmount;
                                                          echo CommonHelper::displayMoneyFormat($total); */
                                                        echo Labels::getLabel('LBL_Select_Dates_To_Calculate_Amount', $siteLangId);
                                                        ?>
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                            <?php if (!empty($addonProducts)) { ?>
                            <div class="aditional_services">
                                <h3><?php echo Labels::getLabel('LBL_Buy_Aditional_Rental_Addon', $siteLangId) ?></h3>
                                <div class="referproduct rental-cart-tbl-js">
                                    <?php foreach ($addonProducts as $rentalAddon) { ?>
                                    <div class="referproduct__card">
                                        <div class="referproduct__card--media">
                                            <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'addonProduct', array($rentalAddon['selprod_id'], 'THUMB', 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                                alt="<?php echo $rentalAddon['selprod_title']; ?>">
                                        </div>
                                        <div class="referproduct__card--body">
                                            <h4><?php echo $rentalAddon['selprod_title']; ?></h4>
                                            <p class="price">
                                                <?php echo CommonHelper::displayMoneyFormat($rentalAddon['selprod_price']); ?>
                                            </p>
                                            <a href="javascript:void(0);" onClick="viewServiceDetails()"
                                                class="btn btn-outline-bottom"><?php echo Labels::getLabel('LBL_View_Details', $siteLangId); ?></a>
                                        </div>
                                        <div class="select-product checkbox">
                                            <input type="checkbox" name="check_addons" id="check_addons"
                                                data-attrname="rental_addons[<?php echo $rentalAddon['selprod_id'] ?>]"
                                                data-rentalqty="1">
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="btn-layout">
                                <span class="policy-link">
                                    <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                                    <a href="#penaltyRules"
                                        rel="facebox"><?php echo Labels::getLabel('LBL_check_order_cancellation_policy', $siteLangId); ?></a>
                                    <?php } ?>
                                </span>
                                <?php
                                    echo $frmBuyProduct->getFieldHtml('selprod_id');
                                    echo $frmBuyProduct->getFieldHtml('product_for');
                                    echo $frmBuyProduct->getFieldHtml('extend_order');

                                    echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                                    ?>
                            </div>

                        </div>
                    </div>
                </div>
                <?php } ?>

                <?php /* <div class="card_collapsed membership">
                  <input class="switch_label" name="radio_for_membership" type="checkbox">
                  <div class="card_expanded">
                  <div class="show_default">
                  <div class="default-detail">
                  <h2><span class="brand-color">$15.00</span>  Membership</h2>
                  <p class="black-60">4 items / Per 30 Days</p>
                  </div>
                  </div>
                  <lable class="switch_checkbox">
                  <div class="checkbox--handle">
                  <i class="switch_handle"></i>
                  </div>
                  </lable>
                  <div class="show_after">
                  <ul class="thumb-list has-more">
                  <li>
                  <a href="#"><img src="/images/review-thumbs/review-thumb.png"></a>
                  </li>
                  <li>
                  <a href="#"><img src="/images/review-thumbs/review-thumb-2.png"></a>
                  </li>
                  <li>
                  <a href="#"><img src="/images/review-thumbs/review-thumb-3.png"></a>
                  </li>
                  <li>
                  <a href="#"><img src="/images/review-thumbs/review-thumb-4.png"></a>
                  </li>
                  <li>
                  <a href="#"><img src="/images/review-thumbs/review-thumb.png"></a>
                  </li>
                  </ul>
                  <div class="aditional_services">
                  <ul>
                  <li><span class="checkbox"><input type="checkbox" checked=""></span>Rent 4 styles at a time</li>
                  <li><span class="checkbox"><input type="checkbox" checked=""></span>No return dates, you choose how often to swap</li>
                  <li><span class="checkbox"><input type="checkbox" checked=""></span>Buy items you love anytime</li>
                  </ul>
                  <div class="btn-layout">
                  <button class="btn btn-brand " tabindex="0">Explore Membership</button>
                  </div>
                  </div>
                  </div>
                  </div>
                  </div>
                 */ ?>

                <?php if (!empty($upsellProducts)) { ?>
                <div class="bought-together">
                    <h2 class="block-title">
                        <?php echo Labels::getLabel('LBL_Buy_Together_Products', $siteLangId); ?></h2>
                    <div class="referproduct">
                        <?php foreach ($upsellProducts as $upsellProd) { ?>
                        <div class="referproduct__card">
                            <div class="referproduct__card--media">
                                <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($upsellProd['product_id'], 'THUMB', $upsellProd['selprod_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                    alt="<?php echo $upsellProd['product_identifier']; ?>"
                                    title="<?php echo $upsellProd['product_identifier']; ?>">
                            </div>
                            <div class="referproduct__card--body">
                                <span><?php echo $upsellProd['prodcat_name']; ?></span>
                                <h4><?php echo $upsellProd['selprod_title']; ?></h4>
                                <p class="price">
                                    <?php echo CommonHelper::displayMoneyFormat($upsellProd['theprice']); ?></p>
                            </div>
                            <div class="select-product checkbox">
                                <input type="checkbox" name="check_addons" id="check_addons"
                                    data-attrname="addons[<?php echo $upsellProd['selprod_id']; ?>]"
                                    data-saleqty="<?php echo $upsellProd['selprod_min_order_qty']; ?>" />
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <div class="add-cart">
                    <?php if ($availableForRent && $availableForSale) { ?>
                    <p class="text-center">
                        <?php echo Labels::getLabel('LBL_Or_you_can_purchase_this_item_also', $siteLangId); ?></p>
                    <?php } ?>
                    <?php if ($availableForSale) { ?>
                    <div class="btn-layout">
                        <?php
                            echo $frmBuyProduct->getFieldHtml('selprod_id');
                            echo $frmBuyProduct->getFieldHtml('btnAddToCartSale');
                            ?>
                    </div>
                    <?php } ?>

                    <?php
                    if (FatApp::getConfig('CONF_ENABLE_RFQ_MODULE_WITH_PRODUCTS', FatUtility::VAR_INT, 0) == 1 && $product['selprod_enable_rfq'] == 1) { ?>
                    <div class="buy-group">
                        <a class="btn-layout" href="javascript:void(0);"
                            onclick="RequestForQuote('<?php echo $product['selprod_id']; ?>')"
                            class="btn btn--primary text-uppercase">
                            <?php echo Labels::getLabel('LBL_Request_for_quote', $siteLangId); ?>
                        </a>
                    </div>
                    <?php } ?>

                </div>
                </form>
                <?php echo $frmBuyProduct->getExternalJs(); ?>
                <div class="sold-by">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-xl-6 col-lg-6 col-md-5">
                            <h5 class="block-title">
                                <?php echo Labels::getLabel('LBL_Seller', $siteLangId); ?></h5>
                            <h6 class="m-0">
                                <a
                                    href="<?php echo UrlHelper::generateUrl('shops', 'View', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name']; ?></a>
                                <div class="products__rating -display-inline m-0">
                                    <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                                    - <i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                            </use>
                                        </svg>
                                    </i>
                                    <span class="rate"><?php
                                                            echo round($shop_rating, 1), '', '', '';
                                                            if ($shopTotalReviews) {
                                                            ?>
                                        <?php } ?> </span>
                                    <?php } ?>
                                </div>
                            </h6>
                            <?php /* if ($shop_rating>0) { ?>
                            <div class="products__rating"> <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                        </use>
                                    </svg></i> <span
                                    class="rate"><?php echo round($shop_rating, 1); ?><span></span></span>
                            </div><br>
                            <?php } */ ?>
                        </div>
                        <div class="col-auto">
                            <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller())) && (UserAuthentication::getLoggedUserId() != $shop['shop_user_id']))) { ?>
                            <a href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'], $product['selprod_id'])); ?>"
                                class="btn btn-outline-bottom"><?php echo Labels::getLabel('LBL_Ask_Question', $siteLangId); ?></a>
                            <?php } ?>
                            <?php if (count($product['moreSellersArr']) > 0) { ?>
                            <a href="<?php echo UrlHelper::generateUrl('products', 'sellers', array($product['selprod_id'])); ?>"
                                class="btn btn-outline-bottom"><?php echo Labels::getLabel('LBL_Compare_price_with_other_Sellers', $siteLangId); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($volumeDiscountRows)) { ?>
                <div class="price-seller">
                    <div class="block-title">
                        <?php echo Labels::getLabel('LBL_Wholesale_Price_(Piece)', $siteLangId); ?>:
                    </div>
                    <div class="js-carousel discount-slider" data-slides="3,2,1,1,1" data-infinite="false"
                        data-arrows="true" data-slickdots="flase"
                        dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
                        <?php
                            foreach ($volumeDiscountRows as $volumeDiscountRow) {
                                $volumeDiscount = $product['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
                                $price = ($product['theprice'] - $volumeDiscount);
                            ?>
                        <div class="item">
                            <div class="qty__value">
                                <?php echo ($volumeDiscountRow['voldiscount_min_qty']); ?>
                                <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                                (<?php echo $volumeDiscountRow['voldiscount_percentage'] . '%'; ?>) <span
                                    class="item__price"><?php echo CommonHelper::displayMoneyFormat($price); ?>
                                    /
                                    <?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></span></div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php if (!empty($durationDiscountRows)) { ?>
                <div class="price-seller">
                    <div class="block-title">
                        <?php echo Labels::getLabel('LBL_Duration_Discounts_for_rent', $siteLangId); ?>:
                    </div>
                    <div class="js-carousel discount-slider" data-slides="3,2,1,1,1" data-infinite="false"
                        data-arrows="true" data-slickdots="flase"
                        dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
                        <?php
                            foreach ($durationDiscountRows as $durationDiscountRow) {
                                //$durationDiscountRow = $product['theprice'] * ($durationDiscountRow['voldiscount_percentage'] / 100);
                                //$price = ($product['theprice'] - $volumeDiscount);
                            ?>
                        <div class="item">
                            <div class="qty__value">
                                <?php echo ($durationDiscountRow['produr_rental_duration']) . ' ' . $rentalTypeArr[$durationDiscountRow['produr_duration_type']]; ?>
                                <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                                <span
                                    class="item__price">(<?php echo $durationDiscountRow['produr_discount_percent'] . '%'; ?>)
                                </span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>


                <?php
                $attributeCount = 0;
                if (count($productSpecifications) > 0 || (!empty($attributes) && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES)) { ?>
                <div class="specific-information hidespecificationtitle">
                    <h2><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></h2>
                    <table class="information_wrapper" width="100%">
                        <?php
                            $i = 0;
                            foreach ($productSpecifications as $key => $specification) {
                                echo ($i == 0) ? "<tr> " : "";
                                if (trim($specification['prodspec_value']) == '' && $specification['prodspec_is_file'] == 0) {
                                    continue;
                                }
                                $attributeCount++;
                            ?>
                        <td>
                            <span><?php echo $specification['prodspec_name'] . ":"; ?></span>
                            <p>
                                <?php echo html_entity_decode($specification['prodspec_value'], ENT_QUOTES, 'utf-8'); ?>
                                <?php
                                        if ($specification['prodspec_is_file'] == 1) {
                                            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $product['product_id'], $specification['prodspec_id'], $siteLangId);
                                            if (!empty($fileData)) {
                                                $fileArr = explode('.', $fileData['afile_name']);
                                                $fileTypeIndex = count($fileArr) - 1;
                                                $fileType = strtolower($fileArr[$fileTypeIndex]);
                                                $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');

                                                $attachmentUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id']), CONF_WEBROOT_FRONT_URL);

                                                if (in_array($fileType, $imageTypes)) {
                                                    $imageUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id'], 50, 50), CONF_WEBROOT_FRONT_URL);

                                                    $fileHtml = "<a href='" . $attachmentUrl . "' class='spcification-image--js'><img src='" . $imageUrl . "' class='img-thumbnail' style='max-width:150px;' /></a>";
                                                } else {
                                                    $fileHtml = "<a href='" . $attachmentUrl . "' title='" . $fileData['afile_name'] . "' download><i class='fa fa-download' aria-hidden='true'></i></a>";
                                                }
                                                echo $fileHtml;
                                            }
                                        }
                                        ?>
                            </p>
                        </td>
                        <?php
                                echo ($i == 1) ? "</tr> " : "";
                                $i = ($i >= 1) ? 0 : ++$i;
                                ?>
                        <?php } ?>

                        <?php
                            if (!empty($attributes) && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
                                foreach ($attributes as $key => $attribute) {
                                    if ($attribute['attr_group_name'] != '') {
                            ?>
                        <tr class="attr--group">
                            <td colspan="2"><?php echo $attribute['attr_group_name']; ?></td>
                        </tr>
                        <?php
                                    }
                                    $i = 0;
                                    foreach ($attribute['attributes'] as $attr) {
                                        echo ($i == 0) ? "<tr> " : "";
                                        if (!isset($productCustomFields[$key][$attr['attr_fld_name']]) || $productCustomFields[$key][$attr['attr_fld_name']] == '') {
                                            continue;
                                        }
                                        $attributeCount++;


                                    ?>
                        <td>
                            <span>
                                <?php
                                                echo ($attr['attr_name'] != '') ? $attr['attr_name'] : $attr['attr_identifier'];
                                                echo ":";
                                                ?>
                            </span>
                            <p>
                                <?php
                                                if (!isset($productCustomFields[$key][$attr['attr_fld_name']]) || $productCustomFields[$key][$attr['attr_fld_name']] == '') {
                                                    echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                } else {
                                                    if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX) {
                                                        $attrOpt = explode("\n", $attr['attr_options']);
                                                        $selectedOptions = $productCustomFields[$key][$attr['attr_fld_name']];
                                                        $selectedOptions = explode(',', $selectedOptions);
                                                        $i = 1;
                                                        if (!empty($selectedOptions)) {
                                                            foreach ($selectedOptions as $option) {
                                                                echo $attrOpt[$option] . ' ' . $attr['attr_postfix'];
                                                                if ($i < count($selectedOptions)) {
                                                                    echo ', ';
                                                                }
                                                                $i++;
                                                            }
                                                        } else {
                                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                        }
                                                    } else if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_NUMBER) {
                                                        echo intval($productCustomFields[$key][$attr['attr_fld_name']]);
                                                        echo $attr['attr_postfix'];
                                                    } else {
                                                        echo $productCustomFields[$key][$attr['attr_fld_name']];
                                                        echo $attr['attr_postfix'];
                                                    }
                                                }
                                                ?>
                            </p>
                        </td>
                        <?php
                                        echo ($i == 1) ? "</tr> " : "";
                                        $i = ($i >= 1) ? 0 : ++$i;
                                        ?>
                        <?php
                                    }
                                }
                            }
                            ?>
                    </table>
                </div>
                <?php } ?>
                <?php if (trim($product['product_description']) != '') { ?>
                <div class="product-details">
                    <h2 class="block-title"><?php echo Labels::getLabel('LBL_Description', $siteLangId); ?></h2>
                    <div class="product-details__text">
                        <?php echo CommonHelper::renderHtml($product['product_description']); ?></div>
                    <?php if (strlen($product['product_description']) > 300) { ?>
                    <a href="javascript:void(0);"
                        class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php $youtube_embed_code = UrlHelper::parseYoutubeUrl($product["product_youtube_video"]); ?>
                <?php if (trim($youtube_embed_code) != '') { ?>
                <div class="product-details">
                    <div class="mb-4 video-wrapper">
                        <iframe width="100%" height="315"
                            src="//www.youtube.com/embed/<?php echo $youtube_embed_code ?>" allowfullscreen></iframe>
                    </div>
                </div>
                <?php } ?>
                <?php if ($shop['shop_payment_policy'] != '' || !empty($shop["shop_delivery_policy"] != "") || !empty($shop["shop_delivery_policy"] != "")) { ?>
                <div class="product-details">
                    <h2><?php echo Labels::getLabel('LBL_Shop_Policies', $siteLangId); ?></h2>
                    <?php if ($shop['shop_payment_policy'] != '') { ?>
                    <h6><?php echo Labels::getLabel('LBL_Payment_Policy', $siteLangId) ?></h6>
                    <p class="product-details__text"><?php echo nl2br($shop['shop_payment_policy']); ?></p>
                    <?php if (strlen($shop['shop_payment_policy']) > 300) { ?>
                    <a href="javascript:void(0);"
                        class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <div class="product-details">
                    <?php if ($shop['shop_delivery_policy'] != '') { ?>
                    <h6><?php echo Labels::getLabel('LBL_Delivery_Policy', $siteLangId) ?></h6>
                    <p class="product-details__text"><?php echo nl2br($shop['shop_delivery_policy']); ?></p>
                    <?php if (strlen($shop['shop_delivery_policy']) > 300) { ?>
                    <a href="javascript:void(0);"
                        class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <div class="product-details">
                    <?php if ($shop['shop_refund_policy'] != '') { ?>
                    <h6><?php echo Labels::getLabel('LBL_Refund_Policy', $siteLangId) ?></h6>
                    <p class="product-details__text"><?php echo nl2br($shop['shop_refund_policy']); ?></p>
                    <?php if (strlen($shop['shop_refund_policy']) > 300) { ?>
                    <a href="javascript:void(0);"
                        class="link-plus readmore--js"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php if (!empty($product['selprodComments'])) { ?>
                <div class="product-details">
                    <h2><?php echo Labels::getLabel('LBL_Extra_comments', $siteLangId); ?></h2>
                    <p class="product-details__text">
                        <?php echo CommonHelper::displayNotApplicable($siteLangId, nl2br($product['selprodComments'])); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div>
</section>

<?php if ($relatedProductsRs) { ?>
<section class="section pb-0 detail-slide">
    <?php include(CONF_DEFAULT_THEME_PATH . 'products/related-products.php'); ?>
</section>
<?php } ?>
<!-- [ REVIEWS SECTION GOES HERE.... -->
<?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
<?php echo $frmReviewSearch->getFormHtml(); ?>
<?php $this->includeTemplate('_partial/product-reviews.php', array('reviews' => $reviews, 'siteLangId' => $siteLangId, 'product_id' => $product['product_id'], 'canSubmitFeedback' => $canSubmitFeedback, 'sellerId' => $product['selprod_user_id']), false); ?>
<?php } ?>
<!-- ] -->
<?php if ($recommendedProducts) { ?>
<section class="section pb-0 detail-slide">
    <?php include(CONF_DEFAULT_THEME_PATH . 'products/recommended-products.php'); ?>
</section>
<?php } ?>
<section class="section pb-0 detail-slide" id="recentlyViewedProductsDiv"></section>
<!-- compare products lists starts here-->
<div id="compare_product_list_js"></div>
<!-- compare products lists ends here-->
<?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && 0 < $compProdCount) {
?>
<script type="text/javascript">
var data = 'detail_page=1';
fcom.ajax(fcom.makeUrl('CompareProducts', 'listing'), data, function(res) {
    $("#compare_product_list_js").html(res);
    $('body').addClass('is-compare-visible');
});
</script>
<?php }
?>
<script>
var disableDates = <?php echo json_encode($unavailableDates); ?>;
var extendOrder = <?php echo (empty($extendedOrderData) ? 0 : 1) ?>;
var availableDate = new Date('<?php echo $rentalAvailableDate; ?>');
var rentalMinEndDate = new Date(availableDate.getTime() + <?php echo $addTimeToDate; ?>);
$('.rental_start_datetime').datetimepicker({
    dateFormat: 'yy-mm-dd',
    timeFormat: 'HH:00',
    stepMinute: 60,
    defaultDate: availableDate,
    minDate: availableDate,
    beforeShowDay: function(date) {
        var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
        if (disableDates.indexOf(string) == -1) {
            return [disableDates.indexOf(string) == -1, ''];
        } else {
            return [disableDates.indexOf(string) == -1, 'rental-unavailable-date'];
        }
    },
    onSelect: function(select_date) {
        getRentalDetails();
        var selectedDate = new Date(select_date);
        var msecsInAHour = 60 * 60 * 1000;
        var endDate = new Date(selectedDate.getTime() + msecsInAHour);
        console.log(endDate);
        var event = new Date(endDate);
        var time = event.toLocaleTimeString('it-IT');
        $(".rental_end_datetime").datetimepicker(
            "option", {
                minDate: new Date(endDate),
                minDateTime: new Date(endDate),
                defaultDate: new Date(endDate),
            });
    }
});

$('.rental_end_datetime').datetimepicker({
    dateFormat: 'yy-mm-dd',
    timeFormat: 'HH:00',
    stepMinute: 60,
    minDate: rentalMinEndDate,
    defaultDate: rentalMinEndDate,
    beforeShowDay: function(date) {
        var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
        if (disableDates.indexOf(string) == -1) {
            return [disableDates.indexOf(string) == -1, ''];
        } else {
            return [disableDates.indexOf(string) == -1, 'rental-unavailable-date'];
        }
    },
    onSelect: function(select_date) {
        getRentalDetails();
        var selectedDate = new Date(select_date);
        var msecsInAHour = 60 * 60 * 1000; // Miliseconds in hours
        var startDate = new Date(selectedDate.getTime() - msecsInAHour);
        if (extendOrder < 1) {
            $(".rental_start_datetime").datetimepicker(
                "option", {
                    maxDate: new Date(startDate),
                    maxDateTime: new Date(startDate),
                }
            );
        }
    }
});
</script>

<script type="text/javascript">
var mainSelprodId = <?php echo $product['selprod_id']; ?>;
var layout = '<?php echo CommonHelper::getLayoutDirection(); ?>';

$("document").ready(function() {
    recentlyViewedProducts(<?php echo $product['selprod_id']; ?>);
    /*zheight = $(window).height() - 180; */
    zwidth = $(window).width() / 3 - 15;

    if (layout == 'rtl') {
        $('.xzoom, .xzoom-gallery').xzoom({
            zoomWidth: zwidth,
            /*zoomHeight: zheight,*/
            title: true,
            tint: '#333',
            position: 'left'
        });
    } else {
        $('.xzoom, .xzoom-gallery').xzoom({
            zoomWidth: zwidth,
            /*zoomHeight: zheight,*/
            title: true,
            tint: '#333',
            Xoffset: 2
        });
    }

    window.setInterval(function() {
        var scrollPos = $(window).scrollTop();
        if (scrollPos > 0) {
            setProductWeightage('<?php echo $product['selprod_code']; ?>');
        }
    }, 5000);

});
</script>
<script>
$(document).ready(function() {
    $("#btnAddToCart").addClass("quickView");
    $('#slider-for').slick(getSlickGallerySettings(false));
    $('#slider-nav').slick(getSlickGallerySettings(true, '<?php echo CommonHelper::getLayoutDirection(); ?>'));

    /* for toggling of tab/list view[ */
    $('.list-js').hide();
    $('.view--link-js').on('click', function(e) {
        $('.view--link-js').removeClass("btn--active");
        $(this).addClass("btn--active");
        if ($(this).hasClass('list')) {
            $('.tab-js').hide();
            $('.list-js').show();
        } else if ($(this).hasClass('tab')) {
            $('.list-js').hide();
            $('.tab-js').show();
        }
    });
    /* ] */

    $(".nav-scroll-js").click(function(event) {
        event.preventDefault();
        var full_url = this.href;
        var parts = full_url.split("#");
        var trgt = parts[1];
        /* var target_offset = $("#" + trgt).offset();
         
         var target_top = target_offset.top - $('#header').height();
         $('html, body').animate({
         scrollTop: target_top
         }, 800); */
        $('html, body').animate({
            scrollTop: parseInt($("#" + trgt).position().top) + parseInt($("#scrollUpTo-js")
                .position().top)
        }, 800);

    });
    $('.nav-detail-js li a').click(function() {
        $('.nav-detail-js li a').removeClass('is-active');
        $(this).addClass('is-active');
    });

    var headerHeight = $("#header").height();
    $(".nav-detail-js").css('top', headerHeight);

});
</script>
<!-- Product Schema Code -->
<style>
.sale-products--js .slick-track {
    width: 723px !important;
}

.sale-products--js .slick-slide {
    width: 241px !important;
}
</style>
<script>
$('.spcification-image--js').magnificPopup({
    type: 'image'
});
</script>
<script>
$('.sizechart-image--js').magnificPopup({
    type: 'image'
});
</script>
<?php
if (FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '')) {
    $image = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id']);
?>
<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Product",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo round(FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT), 1); ?>",
        "reviewCount": "<?php echo FatUtility::int($reviews['totReviews']); ?>"
    },
    "description": "<?php echo CommonHelper::renderHtml($product['product_description']); ?>",
    "name": "<?php echo $product['selprod_title']; ?>",
    "image": "<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>",
    "offers": {
        "@type": "Offer",
        "availability": "http://schema.org/InStock",
        "price": "<?php echo $product['theprice']; ?>",
        "priceCurrency": "<?php echo CommonHelper::getCurrencyCode(); ?>"
    }
}
</script>
<?php } ?>
<!-- End Product Schema Code -->

<!--Here is the facebook OG for this product  -->
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
<?php if (1 > $attributeCount) { ?>
<style>
.hidespecificationtitle {
    display: none;
}
</style>
<?php } ?>
<script>
//$(document).ready(function() {
$('.readmore--js').on('click', function() {

    $(this).parents('.product-details').find('.product-details__text').toggleClass('detail-expand--js');
    if ($(this).parents('.product-details').find('.product-details__text').hasClass('detail-expand--js') ==
        true) {
        $(this).text('<?php echo Labels::getLabel('LBL_Read_Less', $siteLangId) ?>');
    } else {
        $(this).text('<?php echo Labels::getLabel('LBL_Read_More', $siteLangId) ?>');
    }

});
//});
</script>