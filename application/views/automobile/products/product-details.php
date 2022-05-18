<?php
$checkType = $selectedFullfillmentType;
$extendOrderId = 0;
if ($frmBuyProduct->getField('extend_order')) {
    $extOrderFld = $frmBuyProduct->getField('extend_order');
    $extendOrderId = $extOrderFld->value;
}

/* $rentalStartDateFld = $frmBuyProduct->getField('rental_start_date');
$rentalEndDateFld = $frmBuyProduct->getField('rental_end_date');

$rentalStartDateFld->addFieldTagAttribute('class', 'date-picker-field rental_start_datetime');
$rentalEndDateFld->addFieldTagAttribute('class', 'date-picker-field rental_end_datetime'); */

$rentalStartDateFld = $frmBuyProduct->getField('rental_dates');
$rentalStartDateFld->addFieldTagAttribute('class', 'rental_datescalendar--js field--calender');

$qtyField = $frmBuyProduct->getField('quantity');
$qtyField->setFieldTagAttribute('data-errordiv', 'qty-fld-err-js');
$minQty = $product['sprodata_minimum_rental_quantity'];
if ($availableForRent && $availableForSale) {
    $minQty = min($product['sprodata_minimum_rental_quantity'], $product['selprod_min_order_qty']);
} elseif ($availableForSale) {
    $minQty = $product['selprod_min_order_qty'];
}
$qtyField->value = $minQty;

if ($extendOrderId > 0) {
    $rentalStartDateFld->addFieldTagAttribute('disabled', 'true');
    $qtyField->addFieldTagAttribute('disabled', 'true');
}
?>
<section class="section">
    <div class="container">
        <div class="row">
            <div class="col-xl-5 order-xl-2">
                <div class="sticky">
                    <div class="block">
                        <div class="block__header">
                            <div class="tabs tabs--rent-buy">
                                <ul class="nav nav-tabs" role="tablist">
                                    <?php if ($availableForRent) { ?>
                                        <li>
                                            <a href="#tb-1" data-toggle="tab" class="active">
                                                <?php echo Labels::getLabel('LBL_Rent', $siteLangId); ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <?php if (ALLOW_SALE && $availableForSale) { ?>
                                        <li>
                                            <a href="#tb-2" data-toggle="tab" class="">
                                                <?php echo Labels::getLabel('LBL_Buy', $siteLangId); ?>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="block__body">
                            <?php echo $frmBuyProduct->getFormTag(); ?>
                            <div class="tab-content">
                                <div class="row">
                                    <div class="col-xl-6 col-md-4">
                                        <label class="field_label"><?php echo $qtyField->getCaption(); ?></label>
                                        <div class="qty-wrapper">
                                            <div class="quantity quantity-theme" data-stock="<?php echo $product['selprod_stock']; ?>">
                                                <span class="<?php echo (1 > $extendOrderId) ? "decrease decrease-js" : ""; ?> not-allowed">
                                                    <i class="fas fa-minus"></i>
                                                </span>
                                                <div class="qty-input-wrapper qty-wrapper--js" data-stock="<?php echo $product['selprod_stock']; ?>" data-minsaleqty="<?php echo $product['selprod_min_order_qty']; ?>" data-minrentqty="<?php echo $product['sprodata_minimum_rental_quantity']; ?>">
                                                    <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                                </div>
                                                <span class=" <?php echo (1 > $extendOrderId) ? "increase increase-js" : " not-allowed"; ?>">
                                                    <i class="fas fa-plus"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="qty-fld-err-js"></div>
                                    </div>
                                    <div class="col-xl-6 col-md-8">
                                        <?php if (!empty($optionsFinalArr) && FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 1)) { ?>
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"><?php echo Labels::getLabel('LBL_Select_the_Variations', $siteLangId); ?></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <button class="btn btn-outline-gray dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                                            <span><span class="color-dot" style="background-color:<?php echo $optionsFinalArr[$product['selprod_id']]['color_code']; ?>;"></span> <?php echo $optionsFinalArr[$product['selprod_id']]['value']; ?>
                                                            </span>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-anim">
                                                            <ul class="nav nav-block scroll scroll-y no-url--js">
                                                                <?php
                                                                foreach ($optionsFinalArr as $optId => $optRow) { ?>
                                                                    <li class="nav__item  <?php echo ($optId == $product['selprod_id']) ? "is-active" : ""; ?>">
                                                                        <a title="<?php echo $optRow['value']; ?>" class="dropdown-item nav__link " href="<?php echo UrlHelper::generateUrl('Products', 'view', [$optId]); ?>">
                                                                            <span class="color-dot" style="background-color: <?php echo $optRow['color_code']; ?>;"></span> <?php echo $optRow['value']; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } elseif (!empty($optionRows) && !FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) { ?>
                                            <div class="row">
                                                <div class="dashed-separater"></div>
                                                <div class="col-xl-12 col-md-6">
                                                    <?php
                                                    $selectedOptionsArr = $product['selectedOptionValues'];
                                                    $count = 0;
                                                    foreach ($optionRows as $key => $option) {
                                                        $selectedOptionValue = $option['values'][$selectedOptionsArr[$key]]['optionvalue_name'];
                                                        $selectedOptionColor = $option['values'][$selectedOptionsArr[$key]]['optionvalue_color_code'];
                                                    ?>
                                                        <label class="label d-flex justify-content-between">
                                                            <?php echo $option['option_name']; ?>
                                                            <?php if (!empty($product['size_chart'])) { ?>
                                                                <?php
                                                                $chartFileRow = $product['size_chart'];
                                                                $sizeChartUrl = CommonHelper::generateUrl('image', 'productSizeChart', array($chartFileRow['afile_record_id'], "ORIGINAL", $chartFileRow['afile_id']));
                                                                ?>
                                                                <a href="<?php echo $sizeChartUrl; ?>" class="size-chart sizechart-image--js" title="">
                                                                    <i class="icn">
                                                                        <svg class="svg">
                                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#size-chart" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#size-chart">
                                                                            </use>
                                                                        </svg>
                                                                    </i>
                                                                    <?php echo Labels::getLabel('LBL_SIZE_CHART', $siteLangId); ?>
                                                                </a>
                                                            <?php } ?>
                                                        </label>
                                                        <div class="dropdown dropdown-options">
                                                            <button class="btn btn-outline-gray dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                                                <span>
                                                                    <?php if ($option['option_is_color']) { ?>
                                                                        <span class="colors" style="background-color:#<?php echo $selectedOptionColor; ?>;"></span>
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
                                                                                    <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php
                                                                                                                                                                                                                                echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                                echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                                                                                                                                                                                ?>" class="dropdown-item nav__link <?php
                                                                                                                                                                                                                                                                    echo (!$option['option_is_color']) ? 'selector__link' : '';
                                                                                                                                                                                                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? ' ' : ' ';
                                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                                                                                                                                                                                                    ?>" href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
                                                                                        <span class="colors" style="background-color:#<?php echo $opVal['optionvalue_color_code']; ?>;"></span><?php echo $opVal['optionvalue_name']; ?></a>
                                                                                <?php } else { ?>
                                                                                    <a optionValueId="<?php echo $opVal['optionvalue_id']; ?>" selectedOptionValues="<?php echo implode("_", $selectedOptionsArr); ?>" title="<?php
                                                                                                                                                                                                                                echo $opVal['optionvalue_name'];
                                                                                                                                                                                                                                echo (!$isAvailable) ? ' ' . Labels::getLabel('LBL_Not_Available', $siteLangId) : '';
                                                                                                                                                                                                                                ?>" class="dropdown-item nav__link <?php
                                                                                                                                                                                                                                                                    echo (in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) ? '' : ' ';
                                                                                                                                                                                                                                                                    echo (!$optionUrl) ? ' is-disabled' : '';
                                                                                                                                                                                                                                                                    ?>" href="<?php echo ($optionUrl) ? $optionUrl : 'javascript:void(0)'; ?>">
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
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!-- <hr> -->
                                <div id="tb-1" role="tabpanel" class="tabs-pane active">
                                    <?php if ($availableForRent) { ?>
                                        <div class="atom-radio-reveal-container rent-section--js">
                                            <div class="atom-radio-drawer">
                                                <label class="atom-radio-drawer_head atom-section-js for-rent--js" for="rent-section-js" data-toggle="tooltip" title="<?php echo Labels::getLabel('LBL_Rental_Detail_Box_Tooltip', $siteLangId); ?>">
                                                    <div class="atom-radio-drawer_head_left <?php echo ($cartType == applicationConstants::PRODUCT_FOR_SALE) ? "disabled" : ""; ?>">
                                                        <h3 class="title">
                                                            <span><span class="brand-color">
                                                                    <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?></span>
                                                                <?php if ($product['sprodata_rental_price'] > $product['rent_price']) { ?>
                                                                    <small><strike><?php echo CommonHelper::displayMoneyFormat($product['sprodata_rental_price']); ?></strike></small>
                                                                <?php } ?>
                                                                <?php echo Labels::getLabel('LBL_Per', $siteLangId) . ' ' . $rentalTypeArr[$product['sprodata_duration_type']]; ?>
                                                            </span>
                                                            <?php if (!empty($addonProducts)) { ?>
                                                                <a class="link service-link--js" href="javascript:void(0);" onClick="showAddonModal()" data-displaybox="1">
                                                                    <?php echo Labels::getLabel('LBL_Addons', $siteLangId); ?>
                                                                </a>
                                                            <?php } ?>
                                                        </h3>
                                                        <?php if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                                            echo '<p class=" txt-normal">'. Labels::getLabel('LBL_Inclusive_All_Taxes', $siteLangId)  .'</p>';
                                                        } ?>
                                                        <p class="txt-normal">
                                                            <?php $minimum_duration = $product['sprodata_minimum_rental_duration'] . ' ' . $rentalTypeArr[$product['sprodata_duration_type']];
                                                            echo sprintf(Labels::getLabel('LBL_Minimum_Rental_Duration_:_%s', $siteLangId), $minimum_duration); ?>
                                                        </p>
                                                        <p class="txt-normal">
                                                            <?php echo sprintf(Labels::getLabel('LBL_Minimum_Rental_Quantity_:_%s', $siteLangId), $product['sprodata_minimum_rental_quantity']); ?>
                                                        </p>

                                                        <p class="txt-normal">
                                                            <span class="lable"><?php echo Labels::getLabel('LBL_Original_Price', $siteLangId); ?></span>
                                                            <span class="price"><?php echo CommonHelper::displayMoneyFormat($product['selprod_cost']); ?></span>
                                                        </p>
                                                    </div>
                                                    <div class="radio <?php echo ($cartType == applicationConstants::PRODUCT_FOR_SALE) ? "disabled" : ""; ?>">
                                                        <input id="rent-section-js" class="radio-switch" name="radio_for_rent_sale_section" type="radio" value="2">
                                                    </div>

                                                    <div class="sale-rent-only" data-toggle="tooltip" data-placement="top" style="display: <?php echo ($cartType == applicationConstants::PRODUCT_FOR_SALE) ? "block" : "none"; ?>;" title="<?php echo Labels::getLabel('LBL_You_have_sale_item(s)_in_your_cart_you_cannot_add_items_for_Rent', $siteLangId); ?>">
                                                        <i class="icn">
                                                            <svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#info">
                                                                </use>
                                                            </svg>
                                                        </i>
                                                    </div>
                                                </label>
                                                <div class="atom-radio-drawer_body">
                                                    <ul class="list-bullet list-bullet-tick">
                                                        <li>
                                                            <span class="lable"><?php echo Labels::getLabel('LBL_Security', $siteLangId); ?></span>
                                                            <span class="price"><?php echo CommonHelper::displayMoneyFormat($product['sprodata_rental_security']); ?>
                                                                <span class="slash"><?php echo Labels::getLabel('LBL_(Refundable)', $siteLangId); ?></span>
                                                                <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="<?php echo Labels::getLabel('LBL_Security_Amount_is_refundable_after_product_return', $siteLangId); ?>"></i>
                                                            </span>
                                                        </li>

                                                        <li>
                                                            <?php if ($selectedFullfillmentType == Shipping::FULFILMENT_SHIP && $fulfillmentType != $selectedFullfillmentType && $fulfillmentType != Shipping::FULFILMENT_ALL) {
                                                                $selectedFullfillmentType = $fulfillmentType;
                                                            ?>
                                                                <span class="lable"><?php echo sprintf(Labels::getLabel('LBL_This_Product_Is_Not_Available_for_Shipping.', $siteLangId), $minShipDuration); ?></span>

                                                            <?php } elseif ($selectedFullfillmentType == Shipping::FULFILMENT_PICKUP && $fulfillmentType != $selectedFullfillmentType && $fulfillmentType != Shipping::FULFILMENT_ALL) {
                                                                $selectedFullfillmentType = $fulfillmentType;
                                                            ?>
                                                                <span class="lable"><?php echo sprintf(Labels::getLabel('LBL_This_Product_Is_Not_Available_for_Pickup.', $siteLangId), $minShipDuration); ?></span>
                                                            <?php } ?>

                                                            <?php if ($selectedFullfillmentType == Shipping::FULFILMENT_SHIP) { ?>
                                                                <span class="lable"><?php echo sprintf(Labels::getLabel('LBL_Ships_to_your_location_in_%s_day(s)', $siteLangId), $minShipDuration); ?></span>
                                                            <?php } else { ?>
                                                                <span class="lable"><?php echo sprintf(Labels::getLabel('LBL_Available_for_pickup_on_%s', $siteLangId), date('d M, Y', strtotime($rentalAvailableDate))); ?></span>
                                                            <?php } ?>
                                                            
                                                            <?php $labelTxt = ($checkType == Shipping::FULFILMENT_PICKUP) ? Labels::getLabel('LBL_Check_Pickup_Locations', $siteLangId) : Labels::getLabel('LBL_Check_Shipping_Locations', $siteLangId);?>
                                                            
                                                            <?php if (($checkType == Shipping::FULFILMENT_PICKUP && $product['sprodata_fullfillment_type'] != Shipping::FULFILMENT_SHIP) || ($checkType == Shipping::FULFILMENT_SHIP && $product['sprodata_fullfillment_type'] != Shipping::FULFILMENT_PICKUP)) { ?>
                                                                    <span class="lable"><a href="javascript:void(0);" class="link" onClick="getFullfillmentData(<?php echo $checkType;?>, <?php echo applicationConstants::PRODUCT_FOR_RENT;?>, <?php echo $product['selprod_id'];?>)"><?php echo $labelTxt; ?></a></span>
                                                            <?php } ?>
                                                        </li>
                                                    </ul>

                                                    <div class="get-dates">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <!--  <div class="caption-wraper">
                                                                    <label class="field-label"><?php echo $frmBuyProduct->getField('rental_dates')->getCaption(); ?></label>
                                                                </div> -->
                                                                <div class="field-wraper">
                                                                    <div class="field_cover rent-calender date-selector">
                                                                        <?php echo $frmBuyProduct->getFieldHtml('rental_dates'); ?>
                                                                        <?php echo $frmBuyProduct->getFieldHtml('rental_start_date'); ?>
                                                                        <?php echo $frmBuyProduct->getFieldHtml('rental_end_date'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php if ($product['sprodata_rental_stock'] > 0 && 0) { ?>
                                                        <div class="bg-gray p-4 rounded my-4">
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
                                                                        <?php echo Labels::getLabel('LBL_Total_Payment', $siteLangId); ?>
                                                                        :
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
                                                    <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/duration-discount.php'); ?>
                                                    <?php if (!empty($addonProducts)) { ?>
                                                        <div class="modal fade" id="aditional-services-js" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">
                                                                            <!-- @todo -->
                                                                            <?php echo Labels::getLabel('LBL_Addon\'s_to_go_with_your_rental_order!', $siteLangId) ?>

                                                                        </h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="detail--js">
                                                                            <div class="detial-content scroll scroll-y detial-content--js">
                                                                            </div>
                                                                        </div>
                                                                        <?php if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                                                            echo '<p class="text-small text-muted">'. Labels::getLabel('LBL_Price_Inclusive_All_Taxes', $siteLangId)  .'</p>';
                                                                        } ?>

                                                                        <div class="rental-addons rental-cart-tbl-js">
                                                                            <ul class="list-cart">
                                                                                <?php foreach ($addonProducts as $rentalAddon) { ?>
                                                                                    <li class="">
                                                                                        <label class="rental-addons_list" for="">
                                                                                            <?php if (1 > $extendOrderId) { ?>
                                                                                                <div class="cell cell_checkbox">
                                                                                                    <div class="checkbox">
                                                                                                        <input type="checkbox" name="check_addons" id="check_addons" data-attrname="rental_addons[<?php echo $rentalAddon['selprod_id'] ?>]" data-rentalqty="1">
                                                                                                    </div>
                                                                                                </div>
                                                                                            <?php } ?>
                                                                                            <div class="cell cell_product">
                                                                                                <div class="product-profile">
                                                                                                    <div class="product-profile__thumbnail">
                                                                                                        <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'addonProduct', array($rentalAddon['selprod_id'], 'THUMB', 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo html_entity_decode($rentalAddon['selprod_title']); ?>">
                                                                                                    </div>
                                                                                                    <div class="product-profile__data">
                                                                                                        <div class="title">
                                                                                                            <?php echo html_entity_decode($rentalAddon['selprod_title']); ?>
                                                                                                        </div>
                                                                                                        <a href="javascript:void(0);" onClick="viewServiceDetails(<?php echo $rentalAddon['selprod_id']; ?>)" class="link"><?php echo Labels::getLabel('LBL_Details', $siteLangId); ?></a>

                                                                                                        <div class="service-details--js" id="service_detail_<?php echo $rentalAddon['selprod_id']; ?>" style="display:none;">
                                                                                                            <?php echo html_entity_decode(nl2br($rentalAddon['selprod_rental_terms'])); ?>
                                                                                                        </div>

                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="cell cell_price">
                                                                                                <div class="price">
                                                                                                    <strong><?php echo CommonHelper::displayMoneyFormat($rentalAddon['selprod_price']); ?></strong>
                                                                                                </div>
                                                                                            </div>
                                                                                        </label>

                                                                                    </li>

                                                                                <?php } ?>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <div class="addon-actions--js">
                                                                            <a href="javascript:void(0);" class="btn btn-brand continue-rental-cart--js skipServicesJs"><?php echo Labels::getLabel('LBL_Skip', $siteLangId); ?></a>

                                                                            <a href="javascript:void(0);" class="btn btn-brand continue-rental-cart--js"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a>
                                                                        </div>

                                                                        <a href="javascript:void(0);" class="btn btn-brand close-detail--js" style="display: none;"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="atom-radio-drawer_foot">
                                                    <div class="action">
                                                        <p class="text-center">
                                                            <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                                                                <a href="javascript:void(0);" data-toggle="modal" data-target="#penaltyModal"><?php echo Labels::getLabel('LBL_check_order_cancellation_policy', $siteLangId); ?></a>
                                                            <?php } ?>
                                                        </p>
                                                        <?php
                                                        $btnAddToCartFld = $frmBuyProduct->getField('btnAddToCart');
                                                        $btnAddToCartFld->addFieldTagAttribute('class', 'btn btn-brand');
                                                        echo $frmBuyProduct->getFieldHtml('selprod_id');
                                                        echo $frmBuyProduct->getFieldHtml('fulfillmentType');
                                                        echo $frmBuyProduct->getFieldHtml('product_for');
                                                        echo $frmBuyProduct->getFieldHtml('extend_order');
                                                        echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if (FatApp::getConfig('CONF_ENABLE_RFQ_MODULE_WITH_PRODUCTS', FatUtility::VAR_INT, 0) == 1 && $product['selprod_enable_rfq'] == 1) {  ?>
                                        <div class="atom-radio-reveal-container">
                                            <div class="atom-radio-drawer">
                                                <label class="atom-radio-drawer_head atom-section-js" for="rfq-js" data-toggle="tooltip" title="<?php echo Labels::getLabel('LBL_RFQ_Detail_Box_Tooltip', $siteLangId); ?>">
                                                    <div class="atom-radio-drawer_head_left">
                                                        <h3 class="title"><span><span class="brand-color"></span>
                                                                <?php echo Labels::getLabel('LBL_Request_for_quote', $siteLangId); ?></span>
                                                        </h3>
                                                        <p class="txt-normal"></p>
                                                    </div>
                                                    <div class="radio">
                                                        <input id="rfq-js" class="radio-switch" name="radio_for_rent_sale_section" type="radio" value="3">
                                                    </div>
                                                </label>
                                                <!--<div class="atom-radio-drawer_body">Body</div>-->
                                                <div class="atom-radio-drawer_foot">
                                                    <div class="action">
                                                        <button type="button" class="btn btn-brand" onclick="RequestForQuote('<?php echo $product['selprod_id']; ?>')">
                                                            <?php echo Labels::getLabel('LBL_Request_for_quote', $siteLangId); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                                        <div class="modal fade" id="penaltyModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display:none;">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">
                                                            <?php echo Labels::getLabel('LBL_Penalty_Rules', $siteLangId); ?>
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- <div id="penaltyRules" style="display:none;"> -->
                                                        <?php
                                                        if (!empty($orderCancelPenaltyRules)) {
                                                            echo '<table class="table ">
                                    <tr>
                                    <th>' . Labels::getLabel('LBL_Cancellation_Duration(Hours)', $siteLangId) . '</th>
                                    <th>' . Labels::getLabel('LBL_Refundable_Amount', $siteLangId) . '</th>
                                    </tr>';
                                                            foreach ($orderCancelPenaltyRules as $rule) { ?>
                                                                <tr>
                                                                    <td>
                                                                        <?php $maxDurationLabel = ($rule['ocrule_duration_max'] == -1) ? Labels::getLabel('LBL_Infinity', $siteLangId) : $rule['ocrule_duration_max'];  ?>


                                                                        <?php echo $rule['ocrule_duration_min'] . ' - ' . $maxDurationLabel . ' ' . Labels::getLabel('LBL_Hours', $siteLangId); ?>

                                                                    </td>
                                                                    <td><?php echo $rule['ocrule_refund_amount'] . ' %'; ?></td>
                                                                    <?php /* echo sprintf(Labels::getLabel('LBL_if_Order_cancel_before_%s_hours_then_%s_amount_will_be_refunded', $siteLangId), $rule['ocrule_duration'], $rule['ocrule_refund_amount'] . '%'); */ ?>
                                                                </tr>
                                                        <?php
                                                            }
                                                            echo '</table>';
                                                        }
                                                        ?>
                                                        <!-- </div> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                                <div id="tb-2" role="tabpanel" class="tabs-pane">
                                    <?php if (ALLOW_SALE && $availableForSale) { ?>
                                        <div class="atom-radio-reveal-container">
                                            <div class="atom-radio-drawer">
                                                <label class="atom-radio-drawer_head atom-section-js for-sale--js" for="buy-now-js" data-toggle="tooltip" title="<?php echo Labels::getLabel('LBL_Sale_Detail_Box_Tooltip', $siteLangId); ?>">
                                                    <div class="atom-radio-drawer_head_left <?php echo ($cartType == applicationConstants::PRODUCT_FOR_RENT) ? "disabled" : ""; ?>">
                                                        <h3 class="title">
                                                            <span>
                                                                <span class="brand-color"><?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?></span>
                                                                <?php if ($product['selprod_price'] > $product['theprice']) { ?>
                                                                    <small><strike><?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></strike></small>
                                                                <?php } ?>
                                                                <?php echo Labels::getLabel('LBL_Buy_Now', $siteLangId); ?>
                                                            </span>
                                                        </h3>
                                                        <?php if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                                            echo '<p class=" txt-normal">'. Labels::getLabel('LBL_Inclusive_All_Taxes', $siteLangId)  .'</p>';
                                                        } ?>
                                                        <p class="txt-normal">
                                                            <?php echo sprintf(Labels::getLabel('LBL_Minimum_Sale_Quantity_:_%s', $siteLangId), $product['selprod_min_order_qty']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="radio <?php echo ($cartType == applicationConstants::PRODUCT_FOR_RENT) ? "disabled" : ""; ?>">
                                                        <input id="buy-now-js" class="radio-switch" name="radio_for_rent_sale_section" type="radio" value="1">
                                                    </div>

                                                    <div class="sale-rent-only " data-toggle="tooltip" data-placement="top" title="<?php echo Labels::getLabel('LBL_You_have_Rental_item(s)_in_your_cart_you_cannot_add_items_for_purchase', $siteLangId); ?>" style="display: <?php echo ($cartType == applicationConstants::PRODUCT_FOR_RENT) ? "block" : "none"; ?>;">
                                                        <i class="icn">
                                                            <svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#info">
                                                                </use>
                                                            </svg>
                                                        </i>
                                                    </div>
                                                </label>

                                                <div class="atom-radio-drawer_body">
                                                    <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/volume-discount.php'); ?>
                                                    <?php if (($checkType == Shipping::FULFILMENT_PICKUP && $fulfillmentTypeSale != Shipping::FULFILMENT_SHIP) || ($checkType == Shipping::FULFILMENT_SHIP && $fulfillmentTypeSale != Shipping::FULFILMENT_PICKUP)) { ?>
                                                    <ul class="list-bullet list-bullet-tick">
                                                        <li> 
                                                            <?php $labelTxt = ($checkType == Shipping::FULFILMENT_PICKUP) ? Labels::getLabel('LBL_Check_Pickup_Locations', $siteLangId) : Labels::getLabel('LBL_Check_Shipping_Locations', $siteLangId);?>
                                                            <span class="lable"><a href="javascript:void(0);" class="link" onClick="getFullfillmentData(<?php echo $checkType;?>, <?php echo applicationConstants::PRODUCT_FOR_SALE;?>, <?php echo $product['selprod_id'];?>);"><?php echo $labelTxt; ?></a></span>
                                                        </li>
                                                    </ul>
                                                    <?php } ?>
                                                </div>

                                                <div class="atom-radio-drawer_foot">
                                                    <div class="action">
                                                        <?php if ($availableForSale) { ?>

                                                            <?php
                                                            $btnFld = $frmBuyProduct->getField('btnAddToCartSale');
                                                            $btnFld->addFieldTagAttribute('class', 'btn btn-brand');
                                                            echo $frmBuyProduct->getFieldHtml('selprod_id');

                                                            if (strtotime($product['selprod_available_from']) > strtotime(date('Y-m-d h:i:s'))) {
                                                                $btnFld->value = Labels::getLabel('LBL_Availabe_from', $siteLangId) . ' ' . date('M d, Y ', strtotime($product['selprod_available_from']));
                                                                $btnFld->setFieldTagAttribute('disabled', 'disabled');
                                                            } elseif (!$product['in_stock']) {
                                                                $btnFld->value = Labels::getLabel('LBL_Out_Of_Stock', $siteLangId);
                                                                $btnFld->setFieldTagAttribute('disabled', 'disabled');
                                                            }

                                                            echo $frmBuyProduct->getFieldHtml('btnAddToCartSale');
                                                            ?>

                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            </form>
                            <?php echo $frmBuyProduct->getExternalJs(); ?>
                            <!-- [ -->
                            <?php if (count($productFileSpecifications) > 0) { ?>
                                <div>
                                    <label class="block-title">
                                        <?php echo Labels::getLabel('LBL_Documents', $siteLangId);?>
                                    </label>
                                    <ul class="list-uploaded-media">
                                        <?php
                                        $i = 0;

                                        foreach ($productFileSpecifications as $key => $specification) {
                                        ?>
                                            <li>
                                                <?php
                                                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $product['product_id'], $specification['prodspec_id'], $siteLangId);
                                                if (!empty($fileData)) {
                                                    $fileArr = explode('.', $fileData['afile_name']);
                                                    $fileTypeIndex = count($fileArr) - 1;
                                                    $fileType = strtolower($fileArr[$fileTypeIndex]);
                                                    $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');

                                                    $attachmentUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id']), CONF_WEBROOT_FRONT_URL);
                                                    $prodSpecName = trim($specification['prodspec_name']);
                                                    if (in_array($fileType, $imageTypes)) {
                                                        $imageUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id'], 50, 50), CONF_WEBROOT_FRONT_URL);

                                                        $fileHtml = "<a href='" . $attachmentUrl . "' class='popup-image--js'  data-toggle='tooltip' data-placement='top' data-original-title='" . $prodSpecName . "'><img src='" . $imageUrl . "' class='thumbnail'/></a>";
                                                    } else {
                                                        $fileHtml = "<a href='" . $attachmentUrl . "' data-toggle='tooltip' data-placement='top' data-original-title='" . $prodSpecName . "' title='" . $prodSpecName . "' download><div class='thumbnail'><i class='fas fa-file-alt'></i></div></a>";
                                                    }
                                                    echo $fileHtml;
                                                }
                                                ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                            <!-- ] -->
                        </div>
                    </div>
                    <div class="sold-by mt-md-5 mt-3">
                        <?php if (isset($product['moreSellersArr']) && count($product['moreSellersArr']) > 0) { ?>
                            <a href="<?php echo UrlHelper::generateUrl('products', 'sellers', array($product['selprod_id'])); ?>" class="text-link"><?php echo Labels::getLabel('LBL_Compare_price_with_other_Sellers', $siteLangId); ?></a>
                        <?php } ?>
                        <h5 class="section-title"><?php echo Labels::getLabel('LBL_Seller', $siteLangId); ?></h5>
                        <h5>
                            <a class="shop--title" href="<?php echo UrlHelper::generateUrl('shops', 'View', array($shop['shop_id'])); ?>"><?php echo $shop['shop_name']; ?></a>
                        </h5>

                        <?php
                        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                            $rating = round($shop_rating);
                        ?>
                            <div class="product-rating product-rating-inline">
                                <ul>
                                    <?php for ($ii = 0; $ii < 5; $ii++) {
                                        $liClass = '';
                                        if ($ii < $rating) {
                                            $liClass = 'active';
                                        }
                                    ?>
                                        <li class="<?php echo $liClass; ?>"></li>
                                    <?php } ?>
                                </ul>
                                <p>(<?php echo round($shopTotalReviews, 1); ?>
                                    <?php echo Labels::getLabel('LBL_Customer_Reviews', $siteLangId); ?>)</p>
                            </div>

                        <?php } ?>
                        <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller())) && (UserAuthentication::getLoggedUserId() != $shop['shop_user_id']))) { ?>
                            <div class="sold-by__actions">
                                <a title="<?php echo Labels::getLabel('LBL_Ask_Question', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'], $product['selprod_id'])); ?>">
                                    <svg class="icon" width="24px" height="24px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#ask-question">
                                        </use>
                                    </svg>
                                    <?php echo Labels::getLabel('LBL_Ask_Question', $siteLangId); ?>
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-7 order-xl-1">
                <div class="detail pr-xl-3 pt-4 pt-xl-0">
                    <div class="detail__head">
                        <h1><?php echo html_entity_decode($product['selprod_title']); ?></h1>
                        <ul class="higlight-features">
                            <?php if (!empty($product['prodcat_name'])) { ?>
                                <li>
                                    <a href="<?php echo UrlHelper::generateFileUrl('Category', 'view', array($product['prodcat_id'])); ?>"><?php echo $product['prodcat_name']; ?></a>
                                </li><?php } ?>

                            <?php if (!empty($product['brand_name'])) { ?>
                                <li>
                                    <a href="<?php echo UrlHelper::generateFileUrl('Brands', 'view', array($product['brand_id'])); ?>"><?php echo $product['brand_name']; ?></a>
                                </li><?php } ?>
                            <?php if (!empty($product['product_model'])) { ?>
                                <li><?php echo $product['product_model']; ?></li>
                            <?php } ?>
                        </ul>
                        <div class="d-md-flex align-items-center justify-content-between pt-3">
                            <?php
                            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                                $rating = round($product['prod_rating'], 1);
                            ?>
                                <div class="product-rating product-rating-inline">
                                    <ul>
                                        <?php for ($ii = 0; $ii < 5; $ii++) {
                                            $liClass = '';
                                            if ($ii < $rating) {
                                                $liClass = 'active';
                                            }
                                        ?>
                                            <li class="<?php echo $liClass; ?>"></li>
                                        <?php } ?>
                                    </ul>
                                    <p>(<?php echo round($product['totReviews'], 1); ?>
                                        <?php echo Labels::getLabel('LBL_Customer_Reviews', $siteLangId); ?>)</p>
                                </div>

                            <?php } ?>
                            <div class="product-action">
                                <ul>

                                    <li>
                                        <?php
                                        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
                                        $favVar = 0;
                                        if ($favVar == applicationConstants::NO) {
                                            $jsFunc = 0 < $product['ufp_id'] ? 'removeFromFavorite(' . $product['selprod_id'] . ')' : 'markAsFavorite(' . $product['selprod_id'] . ')';
                                        ?>
                                            <a href="javascript:void(0)" class="heart-wrapper--js heart-wrapper <?php echo ($product['ufp_id']) ? 'is-active' : ''; ?>" onclick="<?php echo $jsFunc; ?>" data-id="<?php echo $product['selprod_id']; ?>" title="<?php echo ($product['ufp_id']) ? Labels::getLabel('LBL_Remove_product_from_favourite_list', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_favourite_list', $siteLangId); ?>">
                                                <i class="icn icn-fav-heart">
                                                    <svg class="svg icon-unchecked--js" height="18" width="18" <?php if ($product['ufp_id']) { ?> style="display:none" <?php } ?>>
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#fav-heart">
                                                        </use>
                                                    </svg>
                                                    <svg class="svg icon-checked--js" height="18" width="18" <?php if (!$product['ufp_id']) { ?> style="display:none" <?php } ?>>
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#fav-heart-fill">
                                                        </use>
                                                    </svg>
                                                </i>
                                            </a>
                                        <?php } else { ?>
                                            <a href="javascript:void(0)" class="heart-wrapper heart-wrapper--js wishListLink-Js <?php echo ($product['is_in_any_wishlist']) ? 'is-active' : ''; ?>" data-id="<?php echo $product['selprod_id']; ?>" onClick="viewWishList(<?php echo $product['selprod_id']; ?>, this, event);" title="<?php echo ($product['is_in_any_wishlist']) ? Labels::getLabel('LBL_Remove_product_from_your_wishlist', $siteLangId) : Labels::getLabel('LBL_Add_Product_to_your_wishlist', $siteLangId); ?>">
                                                <i class="icn icn-fav-heart">
                                                    <svg class="svg icon-unchecked--js" <?php if ($product['is_in_any_wishlist']) { ?> style="display:none" <?php } ?>>
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#fav-heart">
                                                        </use>
                                                    </svg>
                                                    <svg class="svg icon-checked--js" <?php if (!$product['is_in_any_wishlist']) { ?> style="display:none" <?php } ?>>
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#fav-heart-fill">
                                                        </use>
                                                    </svg>
                                                </i>
                                            </a>
                                        <?php }
                                        ?>
                                    </li>

                                    <li>
                                        <a href="javascript:void(0);" class="no-after share-icon" data-toggle="modal" data-target="#shareModal">
                                            <i class="icn icn-share">
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share">
                                                    </use>
                                                </svg>
                                            </i>
                                        </a>
                                    </li>

                                    <!-- Modal -->
                                    <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModal" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLongTitle">
                                                        <?php echo Labels::getLabel('LBL_Share', $siteLangId); ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <ul class="social-sharing">
                                                        <li class="social-facebook">
                                                            <a class="st-custom-button" data-network="facebook" data-url="<?php echo UrlHelper::generateFullUrl('Products', 'view', array($product['selprod_id'])); ?>/">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-twitter">
                                                            <a class="st-custom-button" data-network="twitter">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-pintrest">
                                                            <a class="st-custom-button" data-network="pinterest">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                        <li class="social-email">
                                                            <a class="st-custom-button" data-network="email">
                                                                <i class="icn"><svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope">
                                                                        </use>
                                                                    </svg></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <li>
                                        <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller())) && (UserAuthentication::getLoggedUserId() != $shop['shop_user_id']))) { ?>
                                            <a href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'], $product['selprod_id'])); ?>">
                                                <i class="icn">
                                                    <svg class="svg" width="24" height="24">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#ask">
                                                        </use>
                                                    </svg>
                                                </i></a>
                                        <?php } ?>
                                    </li>
                                    <li>
                                        <div class="compare-wrapper">
                                            <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0  && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) {
                                                include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/compare-label-ui.php');
                                            } ?>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/product-description-section.php'); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    var $linkMoreText = '<?php echo Labels::getLabel('Lbl_Read_More', $siteLangId); ?>';
    var $linkLessText = '<?php echo Labels::getLabel('Lbl_Read_Less', $siteLangId); ?>';

    function viewServiceDetails(addonId) {
        var details = $('#service_detail_' + addonId).html();
        $('.detial-content--js').html(details);
        $('.detail--js').css('display', 'block');
        $('.rental-cart-tbl-js').hide();
        $('.addon-actions--js').hide();
        $('.close-detail--js').show();
    }

    $(document).ready(function() {
        $('.close-detail--js').on('click', function(e) {
            e.preventDefault();
            $('.detial-content--js').html(' ');
            $('.close-detail--js').hide();
            $('.rental-cart-tbl-js').show();
            $('.addon-actions--js').show();
            $('.detail--js').css('display', 'none');
        });
    });
</script>