<?php
$arr = [];
if ($orderDetail['order_is_rfq']) {
    $arr = array(
        'controller' => 'requestforquotes',
        'action' => 'vieworder'
    );
}
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php', $arr);
$shippingCharges = CommonHelper::orderProductAmount($orderDetail, 'shipping');
$orderStatusLbl = Labels::getLabel('LBL_AWAITING_SHIPMENT', $siteLangId);
$orderStatus = '';
if (!empty($orderDetail["thirdPartyorderInfo"]) && isset($orderDetail["thirdPartyorderInfo"]['orderStatus'])) {
    $orderStatus = $orderDetail["thirdPartyorderInfo"]['orderStatus'];
    $orderStatusLbl = strpos($orderStatus, "_") ? str_replace('_', ' ', $orderStatus) : $orderStatus;
}

$orderDetailLbl = Labels::getLabel('LBL_Rental_Order', $siteLangId);
if ($orderDetail['order_is_rfq']) {
    $orderDetailLbl = Labels::getLabel('LBL_RFQ_Order', $siteLangId);
}

$securityHtml = $taxOptionsHtml = $pickupAddressHtml = $durationDiscountHtml = $volumnDiscountHtml = $shippingHtml = $couponDiscountHtml = $addonAmountHtml = '';
$processingStatuses = array_diff($processingStatuses, [OrderStatus::ORDER_DELIVERED]);
$processingStatuses = array_merge($processingStatuses, [OrderStatus::ORDER_PAYMENT_CONFIRM, OrderStatus::ORDER_CASH_ON_DELIVERY, OrderStatus::ORDER_PAY_AT_STORE]);
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header">
            <div class="row">
                <div class="col">
                    <h2 class="content-header-title no-print"><?php echo $orderDetailLbl; ?></h2>
                </div>
                <div class="col-auto">
                    <div class="no-print">
                        <?php if (in_array($orderDetail['orderstatus_id'], $processingStatuses) && $canEdit && $orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                            <a class="btn btn-outline-brand btn-sm no-print"
                               href="<?php echo UrlHelper::generateUrl('seller', 'cancelOrder', array($orderDetail['op_id'])); ?>">
                                   <?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>
                            </a>
                        <?php } ?>

                        <?php
                        if ($shippedBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $orderDetail['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $orderDetail['order_payment_status']) && $orderDetail['opshipping_type'] == Shipping::SHIPPING_SERVICES) {
                            $opId = $orderDetail['op_id'];
                            $plugin = new Plugin();
                            $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
                            if (empty($orderDetail['opship_response']) && empty($orderDetail['opship_tracking_number']) && 'EasyPost' != $keyName) {
                                $orderId = $orderDetail['order_id'];
                                ?>
                                <a href="javascript:void(0)" onclick='generateLabel(<?php echo $opId; ?>)' class="btn btn-outline-brand btn-sm no-print" title="<?php echo Labels::getLabel('LBL_GENERATE_LABEL', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_GENERATE_LABEL', $siteLangId); ?></a>
                            <?php } elseif (!empty($orderDetail['opship_response']) && 'EasyPost' != $keyName) { ?>
                                <a target="_blank" href="<?php echo UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$orderDetail['op_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="<?php echo Labels::getLabel('LBL_PREVIEW_LABEL', $siteLangId); ?>"><?php echo Labels::getLabel('LBL_PREVIEW_LABEL', $siteLangId); ?></a>
                            <?php }
                               if ((!empty($orderStatus) && 'awaiting_shipment' == $orderStatus && !empty($orderDetail['opship_response']) || 'EasyPost' == $keyName) && empty($orderDetail['opship_tracking_number'])) {
                                   if ('EasyPost' == $keyName) {
                                       $label = Labels::getLabel('LBL_BUY_SHIPMENT_&_GENERATE_LABEL', $siteLangId);
                                   } else {
                                       $label = Labels::getLabel('LBL_PROCEED_TO_SHIPMENT', $siteLangId);
                                   }
                                   ?>
                                <a href="javascript:void(0)" onclick="proceedToShipment(<?php echo $orderDetail['op_id']; ?>)" class="btn btn-outline-brand btn-sm no-print" title="<?php echo $label; ?>"><?php echo $label; ?></a>
                                   <?php
                               }
                            }
                            if ($thread_id > 0) { ?>
                                <a class="btn btn-outline-brand btn-sm no-print" href="<?php echo UrlHelper::generateUrl('Account', 'viewMessages', array($thread_id, $message_id)); ?>"><?php echo Labels::getLabel('LBL_View_Order_Message', $siteLangId); ?></a>
                           <?php } else { ?>
                                <a href="javascript:void(0)" onclick="sendOrderMessage(<?php echo $orderDetail['op_id']; ?>, 'seller')" class="btn btn-outline-brand btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Send_message_to_buyer', $siteLangId); ?>">
                                    <?php echo Labels::getLabel('LBL_Send_message_to_buyer', $siteLangId); ?>
                                </a>
                        <?php } ?>

                        <?php if ($orderDetail['order_is_rfq'] == applicationConstants::YES) { ?>
                            <a class="btn btn-outline-brand btn-sm no-print" href="<?php echo UrlHelper::generateUrl('RequestForQuotes', 'view', array($orderDetail['order_rfq_id'])); ?>" target="_blank">
                                <?php echo Labels::getLabel('LBL_View_RFQ', $siteLangId); ?>
                            </a>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <div class="order-number">
                            <small class="sm-txt"><?php echo Labels::getLabel('LBL_Order', $siteLangId); ?> #</small>
                            <span class="numbers"> <?php echo $orderDetail['op_invoice_number']; ?>
                                <?php
                                if ($orderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                    if (strtotime($orderDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d h:i:s')) && strtotime($orderDetail['opd_rental_end_date']) > strtotime(date('Y-m-d h:i:s')) && in_array($orderDetail['op_status_id'], OrderStatus::statusArrForRentalExpireNote())) {
                                        $dateToRentalEnd = CommonHelper::getDifferenceBetweenDates(date('Y-m-d h:i:s'), $orderDetail['opd_rental_end_date'], 0, ProductRental::DURATION_TYPE_DAY);

                                        if ($dateToRentalEnd >= 3) {
                                            $rentalEndMsgClass = 'alert-success';
                                        } elseif ($dateToRentalEnd >= 2) {
                                            $rentalEndMsgClass = 'alert-warning';
                                        } else {
                                            $rentalEndMsgClass = 'alert-danger';
                                        }
                                        ?>
                                        <span class="notice <?php echo $rentalEndMsgClass; ?>"><?php echo sprintf(Labels::getLabel('LBL_%s_Day(s)_Remaining_to_end_Rental', $siteLangId), $dateToRentalEnd); ?></span>
                                    <?php } elseif (strtotime($orderDetail['opd_rental_end_date']) < strtotime(date('Y-m-d h:i:s')) && in_array($orderDetail['op_status_id'], OrderStatus::statusArrForLateChargeNote())) {
                                        ?>
                                        <span class="notice"><?php echo Labels::getLabel('LBL_Rental_Duration_Ended._Late_Charges_may_be_Apply', $siteLangId); ?></span>
                                        <?php
                                    }

                                    if (isset($parentOrderDetail) && !empty($parentOrderDetail)) {
                                        echo '<span class="notice ">' . Labels::getLabel('LBL_This_order_is_extended_from_', $siteLangId) . ' <a href="' . UrlHelper::generateUrl('SellerOrders', 'viewOrder', array($parentOrderDetail['op_id'])) . '">#' . $parentOrderDetail['op_invoice_number'] . '</a> </span>';
                                    }
                                    if (isset($extendedChildData) && !empty($extendedChildData)) {
                                        echo '<span class="notice ">' . Labels::getLabel('LBL_This_order_is_extended_By', $siteLangId) . ' <a href="' . UrlHelper::generateUrl('SellerOrders', 'viewOrder', array($extendedChildData['opd_op_id'])) . '">#' . $extendedChildData['opd_order_id'] . '</a> </span>';
                                    }
                                }
                                ?>
                            </span>
                        </div>
                    </h5>
                    <div class="btn-group orders-actions">
                        <a href="<?php echo UrlHelper::generateUrl('Seller', 'viewInvoice', [$orderDetail['op_id']]); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Print_order_detail', $siteLangId); ?></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="table-wrap">
                                <table class="table table-orders">
                                    <thead>
                                        <tr class="">
                                            <th><?php echo Labels::getLabel('LBL_Items_Summary', $siteLangId); ?></th>
                                            <?php if ($orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                                                <th>
                                                    <?php echo Labels::getLabel('LBL_Unit_Price', $siteLangId); ?>
                                                    <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="<?php echo Labels::getLabel('LBL_Per_Duration_Price', $siteLangId); ?>"><i class="fa fa-info-circle"></i></a>
                                                </th>
                                            <?php } ?>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Total_Price', $siteLangId); ?>
                                                <?php if ($orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                                                    <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="<?php echo Labels::getLabel('LBL_Total_Amount_tooltip', $siteLangId); ?>"><i class="fa fa-info-circle"></i></a>
                                                <?php } ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $cartTotalAmount = CommonHelper::orderProductAmount($orderDetail, 'cart_total');
                                        $couponDiscount = CommonHelper::orderProductAmount($orderDetail, 'DISCOUNT');
                                        $shippingCharges = CommonHelper::orderProductAmount($orderDetail, 'SHIPPING');
                                        $totalTaxes = CommonHelper::orderProductAmount($orderDetail, 'TAX');
                                        $volumnDiscountAmount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT');
                                        $durationDiscountAmount = CommonHelper::orderProductAmount($orderDetail, 'DURATION_DISCOUNT');
                                        $rewardPointTotal = CommonHelper::orderProductAmount($orderDetail, 'REWARDPOINT');
                                        $totalSecurityAmount = $orderDetail['opd_rental_security'] * $orderDetail['op_qty'];
                                        $orderNetTotal = CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER);

                                        $prodOrBatchUrl = 'javascript:void(0)';
                                        if ($orderDetail['op_is_batch']) {
                                            $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($orderDetail['op_selprod_id']));
                                            $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($orderDetail['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                        } else {
                                            if (Product::verifyProductIsValid($orderDetail['op_selprod_id']) == true) {
                                                $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($orderDetail['op_selprod_id']));
                                            }
                                            $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($orderDetail['selprod_product_id'], "SMALL", $orderDetail['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');

                                            if ($orderDetail['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                                $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($orderDetail['op_selprod_id'], "THUMB", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                            }
                                        }
                                        $optionsHtml = ($orderDetail['op_selprod_options'] != '') ? ' | ' . $orderDetail['op_selprod_options'] : "";

                                        $productTitle = (trim($orderDetail['op_selprod_title']) == '') ? $orderDetail['op_product_identifier'] : $orderDetail['op_selprod_title'];

                                        $productImgHtml = '<td><div class="item"> 
                                                <figure class="item__pic"> 
                                                <a href="' . $prodOrBatchUrl . '">
                                                    <img src="' . $prodOrBatchImgUrl . '" title="' . $orderDetail['op_product_name'] . '" alt="' . $orderDetail['op_product_name'] . '" />
                                                </a>
                                                </figure>
                                                <div class="item__description">
                                                    <div class="item__title">
                                                        <a title="' . $productTitle . '" href="' . $prodOrBatchUrl . '">
                                                            ' . $productTitle . '
                                                        </a>
                                                    </div>
                                                    <div class="item__options"> ' . Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $orderDetail['op_qty'] . ' ' . $optionsHtml . '
                                                </div>
                                                    
                                                </div>
                                                </div></td>';


                                        $roundingOffTotal = $orderDetail['op_rounding_off'];
                                        $totalLateCharges = $orderDetail['charge_total_amount'];
                                        $addonTotal = 0;

                                        $isShipping = true;
                                        if (Shipping::FULFILMENT_PICKUP == $orderDetail['opshipping_fulfillment_type']) {
                                            $isShipping = false;
                                            $pickupAddressHtml .= '<div class="address-info">';
                                            $pickupAddressHtml .= '<p>' . $orderDetail['addr_name'] . '</p>';
                                            $address1 = !empty($orderDetail['addr_address1']) ? '<p>' . $orderDetail['addr_address1'] . '</p>' : '';
                                            $address2 = !empty($orderDetail['addr_address2']) ? '<p>' . $orderDetail['addr_address2'] . '</p>' : '';
                                            $city = !empty($orderDetail['addr_city']) ? $orderDetail['addr_city'] : '';
                                            $state = !empty($orderDetail['state_name']) ? ', ' . $orderDetail['state_name'] : ', ' . $orderDetail['state_identifier'];
                                            $zip = !empty($orderDetail['addr_zip']) ? '(' . $orderDetail['addr_zip'] . ')' : '';
                                            $stateStr = '<p>' . $city . $state . $zip . '</p>';
                                            $country = !empty($orderDetail['country_name']) ? ' <p>' . $orderDetail['country_name'] . '</p>' : '<p> ' . $orderDetail['country_code'] . '</p>';
                                            $pickupAddressHtml .= $address1 . $address2 . $stateStr . $country;

                                            $pickupAddressHtml .= '<p class="c-info"><strong><i class="fas fa-mobile-alt mr-2"></i> ' . $orderDetail['addr_dial_code'] . ' ' . $orderDetail['addr_phone'] . '</strong></p>';

                                            $pickupAddressHtml .= '</div>';
                                        } else {
                                            $shippingHtml .= '<tr>' . $productImgHtml . '<td>' . $orderDetail['opshipping_label'] . '</td><td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'SHIPPING'), true, false, true, false, true) . '</td></tr>';
                                        }

                                        $couponDiscountHtml .= '<tr>' . $productImgHtml . '<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'DISCOUNT'), true, false, true, false, true) . '</td></tr>';

                                        if ($orderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                            $securityHtml .= '<tr>' . $productImgHtml . '<td>' . CommonHelper::displayMoneyFormat(($orderDetail['opd_rental_security'] * $orderDetail['op_qty']), true, false, true, false, true) . '</td></tr>';
                                            $durationDiscountHtml .= '<tr>' . $productImgHtml . '<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'DURATION_DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                        } else {
                                            $volumnDiscountHtml .= '<tr>' . $productImgHtml . '<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                        }

                                        if (empty($orderDetail['taxOptions'])) {
                                            $taxOptionsHtml .= '<tr>
                                        ' . $productImgHtml . '
                                        <td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true) . '</td>
                                        </tr>';
                                        } else {
                                            $taxOptionsHtml .= '<tr>' . $productImgHtml . '<td>';
                                            foreach ($orderDetail['taxOptions'] as $key => $val) {
                                                $taxOptionsHtml .= '<strong>' . CommonHelper::displayTaxPercantage($val, true) . '</strong> : ' . CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true) . '<br />';
                                            }
                                            $taxOptionsHtml .= '</td></tr>';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="item">
                                                    <figure class="item__pic">
                                                        <a href="<?php echo $prodOrBatchUrl; ?>">
                                                            <img src="<?php echo $prodOrBatchImgUrl; ?>"
                                                                 title="<?php echo $orderDetail['op_product_name']; ?>"
                                                                 alt="<?php echo $orderDetail['op_product_name']; ?>" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="<?php echo $productTitle; ?>"
                                                               href="<?php echo $prodOrBatchUrl; ?>">
                                                                   <?php echo $productTitle; ?>
                                                            </a><br />
                                                        </div>
                                                        <div class="item__options">
                                                            <?php
                                                            echo Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $orderDetail['op_qty'];
                                                            if ($orderDetail['op_selprod_options'] != '') {
                                                                echo ' | ' . $orderDetail['op_selprod_options'];
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="item__sold_by">
                                                            <?php echo Labels::getLabel('Lbl_Brand', $siteLangId) ?>:
                                                            <?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail['op_brand_name']); ?>
                                                        </div>
                                                        <?php
                                                        if ($orderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                                            $duration = CommonHelper::getDifferenceBetweenDates($orderDetail['opd_rental_start_date'], $orderDetail['opd_rental_end_date'], $orderDetail['op_selprod_user_id'], $orderDetail['opd_rental_type']);
                                                            ?>
                                                            <div class="item__date-range">
                                                                <small>
                                                                    <i class="icn">
                                                                        <svg width="12px" height="12px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#calender">
                                                                        </use>
                                                                        </svg>
                                                                    </i>
                                                                    <?php echo Labels::getLabel('LBL_From', $siteLangId) . ': ' . date('M d, Y ', strtotime($orderDetail['opd_rental_start_date'])); ?>
                                                                    |
                                                                    <?php echo Labels::getLabel('LBL_To', $siteLangId) . ': ' . date('M d, Y ', strtotime($orderDetail['opd_rental_end_date'])); ?>
                                                                </small>
                                                            </div>
                                                            <div class="item__date-range">
                                                                <small>
                                                                    <i class="icn">
                                                                        <svg width="12px" height="12px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#time">
                                                                        </use>
                                                                        </svg>
                                                                    </i>
                                                                    <?php
                                                                    echo Labels::getLabel('LBL_Duration', $siteLangId) . ': ';
                                                                    echo CommonHelper::displayProductRentalDuration($duration, $orderDetail['opd_rental_type'], $siteLangId);
                                                                    ?>
                                                                </small>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php if ($orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                                                <td>
                                                    <?php echo CommonHelper::displayMoneyFormat($orderDetail['opd_duration_price'], true, false, true, false, true); ?>
                                                </td>
                                            <?php } ?>
                                            <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'cart_total'), true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($attachedServices)) { ?>
                                            <tr class="row-addons">
                                                <td colspan="3">
                                                    <div class="addons">
                                                        <button class="addons_trigger collapsed" type="button"
                                                                data-toggle="collapse"
                                                                data-target="#service_<?php echo $orderDetail['op_id']; ?>"
                                                                aria-expanded="true"
                                                                aria-controls="service_<?php echo $orderDetail['op_id']; ?>">
                                                            <span
                                                                class="txt"><?php echo Labels::getLabel('LBL_Addons_And_Details', $siteLangId); ?>
                                                                <span class="count">
                                                                    <?php echo count($attachedServices); ?></span></span>
                                                            <i class="icn"></i>
                                                        </button>
                                                        <div class="collapse"
                                                             id="service_<?php echo $orderDetail['op_id']; ?>">
                                                            <ul class="addons-list">
                                                                <?php
                                                                foreach ($attachedServices as $service) {
                                                                    $addonImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($service['op_selprod_id'], "THUMB", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');

                                                                    $addonTitle = (trim($service['op_selprod_title']) == '') ? $service['op_product_identifier'] : $service['op_selprod_title'];


                                                                    $addonImgHtml = '<td><div class="item"> 
                                                                        <figure class="item__pic"> 
                                                                        <a href="javascript:void(0);">
                                                                            <img src="' . $addonImgUrl . '" title="' . $service['op_product_name'] . '" alt="' . $service['op_product_name'] . '" />
                                                                        </a>
                                                                        </figure>
                                                                        <div class="item__description">
                                                                            <div class="item__title">
                                                                                <a title="' . $addonTitle . '" href="javascript:void(0);">
                                                                                    ' . $addonTitle . '
                                                                                </a>
                                                                            </div> <div class="item__options"> ' . Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $service['op_qty'] . ' </div>
                                                                        </div></div></td>';

                                                                    $couponDiscountHtml .= '<tr>' . $addonImgHtml . '<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'DISCOUNT'), true, false, true, false, true) . '</td></tr>';


                                                                    $addonTotal += CommonHelper::orderProductAmount($service, 'cart_total');
                                                                    $couponDiscount += CommonHelper::orderProductAmount($service, 'DISCOUNT');
                                                                    $shippingCharges += CommonHelper::orderProductAmount($service, 'SHIPPING');
                                                                    $totalTaxes += CommonHelper::orderProductAmount($service, 'TAX');
                                                                    $volumnDiscountAmount += CommonHelper::orderProductAmount($service, 'VOLUME_DISCOUNT');
                                                                    $durationDiscountAmount += CommonHelper::orderProductAmount($service, 'DURATION_DISCOUNT');
                                                                    $rewardPointTotal += CommonHelper::orderProductAmount($service, 'REWARDPOINT');
                                                                    $totalSecurityAmount += $service['opd_rental_security'] * $service['op_qty'];
                                                                    $orderNetTotal += CommonHelper::orderProductAmount($service, 'netamount', false, User::USER_TYPE_SELLER);

                                                                    $roundingOffTotal += $service['op_rounding_off'];
                                                                    $totalLateCharges += $service['charge_total_amount'];

                                                                    $addonAmountHtml .= '<tr>' . $addonImgHtml . '<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'cart_total'), true, false, true, false, true) . '</td></tr>';

                                                                    if (empty($service['taxOptions'])) {
                                                                        $taxOptionsHtml .= '<tr> 
                                                                ' . $addonImgHtml . '
                                                                <td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'TAX'), true, false, true, false, true) . '</td>
                                                                </tr>';
                                                                    } else {
                                                                        $taxOptionsHtml .= '<tr>' . $addonImgHtml . '<td>';
                                                                        foreach ($service['taxOptions'] as $key => $val) {
                                                                            $taxOptionsHtml .= '<strong>' . CommonHelper::displayTaxPercantage($val, true) . '</strong> : ' . CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true) . '<br />';
                                                                        }
                                                                        $taxOptionsHtml .= '</td></tr>';
                                                                    }
                                                                    ?>
                                                                    <li>
                                                                        <div class="addons-img">
                                                                            <img src="<?php echo $addonImgUrl; ?>"
                                                                                 title="<?php echo $addonTitle; ?>"
                                                                                 alt="<?php echo $addonTitle; ?>">
                                                                        </div>
                                                                        <div class="addons-name">
                                                                            <?php echo $addonTitle; ?></div>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php
                            /* if (!empty($orderDetail['comments'])) { */
                            if (!empty($orderStatusList)) {
                                $classArr = OrderStatus::orderStatusClassess();
                                ?>
                                <div class="timelines-wrap">
                                    <h5 class="card-title">
                                        <?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId); ?></h5>
                                    <ul class="timeline">
                                        <?php
                                        $orderStatusList = array_values($orderStatusList);
                                        foreach ($orderStatusList as $key => $opStatus) {
                                            $postedComments = [array('oshistory_id' => 0)];
                                            $opStatusId = $opStatus['orderstatus_id'];
                                            if (!isset($orderDetail['comments'][$opStatusId])) {
                                                foreach ($orderStatusList as $keyChild => $opStatusChild) {
                                                    $opStatusIdChild = $opStatusChild['orderstatus_id'];
                                                    if ($keyChild > $key && isset($orderDetail['comments'][$opStatusIdChild])) {
                                                        $postedComments = [array('oshistory_id' => 0, 'oshistory_date_added' => $orderDetail['comments'][$opStatusIdChild][0]['oshistory_date_added'])];
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $postedComments = $orderDetail['comments'][$opStatusId];
                                            }

                                            $enableDisableClass = "disabled";
                                            if (isset($currentOrderStatusPriority) && $currentOrderStatusPriority >= $opStatus['priority']) {
                                                $enableDisableClass = "enable";
                                            }
                                            if ($opStatusId == $orderDetail['op_status_id']) {
                                                $enableDisableClass .= ' currently';
                                            }
                                            if (Shipping::FULFILMENT_PICKUP == $orderDetail['opshipping_fulfillment_type'] && $opStatusId == OrderStatus::ORDER_DELIVERED) {
                                                $opStatus['orderstatus_name'] = Labels::getLabel('LBL_Picked', $siteLangId);
                                            }
                                            
                                            
                                            ?>
                                            <li
                                                class="<?php echo $enableDisableClass; ?> <?php echo (isset($classArr[$opStatusId])) ? $classArr[$opStatusId] : "in-process" ?>">
                                                    <?php
                                                    foreach ($postedComments as $row) {
                                                        $attachedFiles = (isset($statusAttachedFiles[$row['oshistory_id']])) ? $statusAttachedFiles[$row['oshistory_id']] : [];
                                                        ?>
                                                    <div class="timeline_data">
                                                        <div class="timeline_data_head">
                                                            <?php if (isset($row['oshistory_date_added'])) { ?>
                                                                <time
                                                                    class="timeline_date"><?php echo FatDate::format($row['oshistory_date_added']); ?>
                                                                </time>
                                                            <?php } ?>
                                                            <span class="order-status"> <em class="dot"></em>
                                                                <?php echo $opStatus['orderstatus_name']; ?>
                                                            </span>
                                                        </div>
                                                        <?php if ($row['oshistory_id'] > 0) { ?>
                                                            <div class="timeline_data_body">
                                                                <?php if ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_SHIPPED || ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_READY_FOR_RENTAL_RETURN && $row['oshistory_fullfillment_type'] == OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP)) { ?>
                                                                    <?php if (empty($row['oshistory_courier'])) { ?>
                                                                        <h6><strong><?php echo Labels::getLabel('LBL_Tracking_Number', $siteLangId); ?></strong>
                                                                        </h6>
                                                                        <div class="d-flex">
                                                                            <div class="clipboard">
                                                                                <p class="clipboard_url"><?php echo $row['oshistory_tracking_number']; ?></p>
                                                                                <a class="clipboard_btn" data-toggle="tooltip" onclick="copyContent(this)" href="javascript:void(0);" data-original-title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId); ?>"><i class="far fa-copy"></i></a>
                                                                            </div>
                                                                            <?php
                                                                            $str = '';
                                                                            if (empty($orderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                                                $str .= "<span class=' mt-3 ml-2'> VIA <em>" . CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]) . "</em></span>";
                                                                            } elseif (!empty($orderDetail['opship_tracking_url'])) {
                                                                                $str .= " <a class='btn btn-brand mt-2 ml-2' href='" . $orderDetail['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                                            }
                                                                            echo $str
                                                                            ?>


                                                                        </div>
                                                                        <?php
                                                                    } else {
                                                                        $trackingNumber = $row['oshistory_tracking_number'];
                                                                        $carrier = $row['oshistory_courier'];
                                                                        echo ($row['oshistory_tracking_number']) ? '<h6><strong>' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) . '</strong> </h6>' : '';
                                                                        if (trim($trackingNumber) != '') {
                                                                            ?>
                                                                            <div class="d-flex">
                                                                                <div class="clipboard">
                                                                                    <p class="clipboard_url">
                                                                                        <?php echo $row['oshistory_tracking_number']; ?></p>
                                                                                    <a class="clipboard_btn" data-toggle="tooltip"
                                                                                       data-original-title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId); ?>"
                                                                                       onclick="copyContent(this)" href="javascript:void(0);"><i
                                                                                            class="far fa-copy"></i></a>
                                                                                </div>
                                                                                <?php
                                                                                if ($row['oshistory_orderstatus_id'] != OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                                                    echo "<span class=' mt-3 ml-2'>";
                                                                                    echo Labels::getLabel('LBL_VIA', $siteLangId);
                                                                                    ?>
                                                                                    <em><?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]); ?></em>
                                                                                    <?php
                                                                                    echo "</span>";
                                                                                } else {
                                                                                    if (trim($row['oshistory_tracking_url']) != '') {
                                                                                        echo " <a class='btn btn-brand mt-2 ml-2' href='" . $row['oshistory_tracking_url'] . "'  target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                                                    }

                                                                                    echo "<span class=' mt-3 ml-2'>" . Labels::getLabel('LBL_VIA', $siteLangId) . " <em>" . $carrier . " </em></span>";
                                                                                }
                                                                                ?>
                                                                            </div>    
                                                                            <?php
                                                                        }
                                                                    }
                                                                }
                                                                if (isset($statusAddressData[$row['oshistory_id']])) {
                                                                    $dropOffAddress = $statusAddressData[$row['oshistory_id']];
                                                                    ?>
                                                                    <div class="my-addresses__body mb-2 p-0">
                                                                        <address class="delivery-address">
                                                                            <h5><span><?php echo $dropOffAddress['addr_name']; ?></span><span
                                                                                    class="tag"><?php echo $dropOffAddress['addr_title']; ?></span>
                                                                            </h5>
                                                                            <p>
                                                                                <?php echo $dropOffAddress['addr_address1'] . ','; ?>

                                                                                <?php echo $dropOffAddress['addr_city'] . ','; ?>
                                                                                <?php echo $dropOffAddress['state_name'] . ','; ?>
                                                                                <?php echo $dropOffAddress['country_name']; ?>
                                                                                <br>
                                                                                <?php echo Labels::getLabel("LBL_Zip", $siteLangId) . ': ' . $dropOffAddress['addr_zip'] ?>

                                                                            </p>
                                                                            <p class="phone-txt">
                                                                                <i class="fas fa-mobile-alt"></i>
                                                                                <?php echo Labels::getLabel("LBL_Phone", $siteLangId) . ': ' . $dropOffAddress['addr_dial_code'] . ' ' . $dropOffAddress['addr_phone']; ?><br>
                                                                            </p>
                                                                        </address>
                                                                    </div>

                                                                    <?php
                                                                }
                                                                if (!empty($attachedFiles)) {
                                                                    ?>
                                                                    <div class="attached-files">
                                                                        <h6>
                                                                            <strong><?php echo Labels::getLabel('LBL_Attached_Files', $siteLangId); ?>
                                                                            </strong>
                                                                        </h6>
                                                                        <ul>
                                                                            <?php foreach ($attachedFiles as $attachedFile) { ?>
                                                                                <li>
                                                                                    <a href="<?php echo UrlHelper::generateUrl('seller', 'downloadBuyerAtatchedFile', array($attachedFile['afile_record_id'], 0, $attachedFile['afile_id'])); ?>"
                                                                                       title="<?php echo $attachedFile['afile_name']; ?>">
                                                                                        <i class="fa fa-download"></i>
                                                                                        <?php echo $attachedFile['afile_name']; ?>
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                <?php } ?>

                                                                <p><?php echo!empty(trim(($row['oshistory_comments']))) ? html_entity_decode(nl2br($row['oshistory_comments'])) : ""; ?>
                                                                </p>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>


                            <?php
                            if ($canEdit && $displayForm) {
                                if (!empty($extendChildOrder)) {
                                    echo '<div class="section--repeated no-print text-danger extend-order-section">' . sprintf(Labels::getLabel('LBL_This_order_is_extended_.To_check_or_change_status_of_this_order_click_here_%s', $siteLangId), '<a href="' . UrlHelper::generateUrl('SellerOrders', 'viewOrder', array($extendChildOrder['opd_op_id'])) . '">#' . $extendChildOrder['opd_order_id'] . '</a>') . '</div>';
                                } else {
                                    ?>
                                    <div class="section--repeated no-print">
                                        <h5><?php echo Labels::getLabel('LBL_Comments_on_order', $siteLangId); ?></h5>
                                        <?php
                                        $securityLabelHtml = '<small>' . sprintf(Labels::getLabel("LBL_Refunable_Security_amount_is_%s", $siteLangId), CommonHelper::displayMoneyFormat($orderDetail['totalSecurityAmount'])) . '</small>';
                                        $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                                        $frm->setFormTagAttribute('class', 'form markAsShipped-js');
                                        $frm->developerTags['colClassPrefix'] = 'col-md-';
                                        $frm->developerTags['fld_default_col'] = 6;

                                        $statusFld = $frm->getField('op_status_id');
                                        $statusFld->setFieldTagAttribute('class', 'status-js fieldsVisibility-js');

                                        $fld1 = $frm->getField('customer_notified');
                                        $fld1->setFieldTagAttribute('class', 'notifyCustomer-js');

                                        $fld = $frm->getField('opship_tracking_url');
                                        if (null != $fld) {
                                            $fld->setFieldTagAttribute('pattern', 'https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)');
                                            $fld->setFieldTagAttribute('placeholder', 'https://example.com');
                                            $fld->htmlAfterField = '<small>' . Labels::getLabel("LBL_Example", $siteLangId) . ' : https://example.com' . '</small>';
                                        }

                                        if ($orderDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                            $fldSecurityType = $frm->getField('refund_security_type');
                                            $fldSecurityType->setFieldTagAttribute('completeamount', $orderDetail['totalSecurityAmount']);

                                            $fldsAmount = $frm->getField('refund_security_amount');
                                            $fldsAmount->htmlAfterField = $securityLabelHtml;

                                            $returnDateFld = $frm->getField('opd_mark_rental_return_date');
                                            if ($orderDetail['opd_mark_rental_return_date'] != '0000-00-00 00:00:00' && $orderDetail['opd_mark_rental_return_date'] != "") {
                                                $returnDateFld->value = $orderDetail['opd_mark_rental_return_date'];
                                                $returnDateFld->setFieldTagAttribute('readonly', 'readonly');
                                                if ($orderDetail['charge_total_amount'] > 0) {
                                                    $returnDateFld->htmlAfterField = "<small class='note'>" . sprintf(Labels::getLabel("LBL_Note:_%s_Late_Charges_will_be_apply", $siteLangId), CommonHelper::displayMoneyFormat($orderDetail['charge_total_amount'], true, false)) . "</small>";
                                                }
                                            } else {
                                                $returnDateFld->value = date('Y-m-d h:i:s');
                                                if (strtotime($orderDetail['opd_rental_end_date']) < strtotime(date('Y-m-d h:i:s'))) {
                                                    $returnDateFld->htmlAfterField = "<small class='note'>" . Labels::getLabel("LBL_Note:_Late_Charges_May_be_apply", $siteLangId) . "</small>";
                                                }
                                            }

                                            $returnQty = $frm->getField('return_qty');
                                            $buyerReturnQty = ($orderDetail['op_return_qty'] > 0) ? $orderDetail['op_return_qty'] : $orderDetail['op_qty'];
                                            if ($buyerReturnQty == 1) {
                                                $returnQty->value = $buyerReturnQty;
                                                $returnQty->setFieldTagAttribute('class', 'disabled-input');
                                                $returnQty->setFieldTagAttribute('readonly', 'readonly');
                                            }

                                            if ($rentalReturnStatus != $orderDetail['op_status_id']) {
                                                $fldSecurityType->setWrapperAttribute('class', 'div_refund_security');
                                                $fldsAmount->setWrapperAttribute('class', 'div_refund_security');

                                                $fileFld = $frm->getField('file[]');
                                                $fileFld->setWrapperAttribute('class', 'div_refund_security');

                                                $lateChrgFld = $frm->getField('apply_late_charges');
                                                if (!empty($lateChrgFld)) {
                                                    $lateChrgFld->developerTags['col'] = 12;
                                                    $lateChrgFld->setWrapperAttribute('class', 'div_refund_security');
                                                }

                                                $returnDateFld->setWrapperAttribute('class', 'div_refund_security');
                                                $returnQty->setWrapperAttribute('class', 'div_refund_security');
                                            }
                                        }
                                        $cmtFld = $frm->getField('comments');
                                        $cmtFld->developerTags['col'] = 12;


                                        $fldBtn = $frm->getField('btn_submit');
                                        $fldBtn->setFieldTagAttribute('class', 'btn btn-brand');
                                        echo $frm->getFormHtml();
                                        ?>
                                    </div>
                                    <?php
                                }
                            }
                            ?>


                        </div>
                        <div class="col-md-4">
                            <div class="ml-xl-2">
                                <div class="order-block">
                                    <h4><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?></h4>
                                    <div class="cart-summary">
                                        <ul class="">
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo FatDate::format($orderDetail['order_date_added']); ?></span>
                                            </li>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($cartTotalAmount, true, false, true, false, true); ?></span>
                                            </li>
                                            <?php if ($addonTotal > 0) { ?>
                                                <li>
                                                    <span class="label">
                                                        <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                           data-target="#addon_modal">
                                                               <?php echo Labels::getLabel('LBL_Addon_Total', $siteLangId); ?>
                                                        </a>
                                                    </span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($addonTotal, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>


                                            <?php if ($durationDiscountAmount != 0) { ?>
                                                <li class="discounted">
                                                    <span class="label">
                                                        <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                           data-target="#duration_discount_modal">
                                                               <?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?>
                                                        </a>
                                                    </span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($durationDiscountAmount, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>

                                            <?php if ($volumnDiscountAmount != 0) { ?>
                                                <li class="discounted">
                                                    <span class="label">
                                                        <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                           data-target="#volume_modal">
                                                               <?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?>
                                                        </a>
                                                    </span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($volumnDiscountAmount, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>
                                            <?php if ($totalSecurityAmount > 0) { ?>
                                                <li>
                                                    <span class="label"><a class="dotted" href="javascript:void(0);"
                                                                           data-toggle="modal"
                                                                           data-target="#security_modal"><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></a></span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($totalSecurityAmount, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>

                                            <?php if ($shippingCharges > 0) { ?>
                                                <li>
                                                    <span class="label">
                                                        <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                           data-target="#shipping_modal"><?php echo Labels::getLabel('LBL_Shipping_Price', $siteLangId); ?></a>
                                                    </span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>
                                            <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                                <li>
                                                    <span class="label">
                                                        <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                           data-target="#tax_modal">
                                                               <?php echo Labels::getLabel('LBL_Taxes', $siteLangId); ?>
                                                               <?php /* <em class="count">5</em> */ ?>
                                                        </a>
                                                    </span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($totalTaxes, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>
                                            <?php if ($roundingOffTotal > 0) { ?>
                                                <li>
                                                    <span
                                                        class="label"><?php echo Labels::getLabel('LBL_Rounding_Up', $siteLangId); ?></span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } elseif (0 > $roundingOffTotal) { ?>
                                                <li>
                                                    <span
                                                        class="label"><?php echo Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?></span>
                                                    <span
                                                        class="value"><?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></span>
                                                </li>
                                            <?php } ?>

                                            <?php /* if ($couponDiscount != 0) { ?>
                                              <li class="discounted">
                                              <span class="label"><a class="dotted" href="javascript:void(0);"
                                              data-toggle="modal"
                                              data-target="#discount_modal"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></a></span>
                                              <span
                                              class="value"><?php echo CommonHelper::displayMoneyFormat($couponDiscount, true, false, true, false, true); ?></span>
                                              </li>
                                              <?php } */ ?>

                                            <?php /* if ($rewardPointTotal != 0) { ?>
                                              <li class="discounted">
                                              <span
                                              class="label"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?></span>
                                              <span
                                              class="value"><?php echo CommonHelper::displayMoneyFormat($rewardPointTotal, true, false, true, false, true); ?></span>
                                              </li>
                                              <?php } */ ?>
                                            <li class="highlighted">
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Order_Total_Amount', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($orderNetTotal, true, false, true, false, true); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php
                                if ($totalLateCharges > 0) {
                                    $chargeLbl = ($orderDetail['charge_status'] != BuyerLateChargesHistory::STATUS_PENDING) ? Labels::getLabel('Lbl_Late_Charges_Apply', $siteLangId) : Labels::getLabel('Lbl_Late_Charges_May_be_Apply', $siteLangId);
                                    ?>
                                    <div class="order-block">
                                        <div class="cart-summary pt-0">
                                            <ul class="">
                                                <li>
                                                    <span class="label"><?php echo Labels::getLabel('Lbl_Late_Charges', $siteLangId) ?></span>
                                                    <span class="value"><?php echo CommonHelper::displayMoneyFormat($totalLateCharges, true, false, true, false, true); ?></span>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                <?php } ?>


                                <?php /* $totalSaving = $couponDiscount + $volumnDiscountAmount + $durationDiscountAmount + $rewardPointTotal; */ ?>
                                <?php $totalSaving = $volumnDiscountAmount + $durationDiscountAmount; ?>
                                <?php if ($totalSaving != 0) { ?>
                                    <div class="total-savings">
                                        <img class="total-savings-img"
                                             src="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/savings.svg" alt="">
                                        <p><?php echo Labels::getLabel('LBL_Buyer\'s_total_savings_amount_on_this_order', $siteLangId); ?>
                                        </p>
                                        <span
                                            class="amount"><?php echo CommonHelper::displayMoneyFormat((-$totalSaving), true, false, true, false, true); ?></span>
                                    </div>
                                <?php } ?>

                                <div class="order-block">
                                    <h5 class="mt-0"><?php echo Labels::getLabel('LBL_Customer_Details', $siteLangId); ?></h5>
                                    <div class="list-specification">
                                        <ul>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId); ?></span>
                                                <span class="value"><?php echo $orderDetail['user_name']; ?></span>
                                            </li>
                                        </ul>        
                                    </div>
                                </div>

                                <?php if (!empty($orderDetail['payments']) || !empty($orderDetail['shippingAddress'])) { ?>
                                    <div class="order-block">
                                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?></h4>
                                        <?php if (!empty($orderDetail['shippingAddress'])) { ?>
                                            <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?></h5>
                                            <div class="address-info">
                                                <?php
                                                $shippingAddress = '<p>' . $orderDetail['shippingAddress']['oua_name'] . '</p>';
                                                if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                                    $shippingAddress .= '<p>' . $orderDetail['shippingAddress']['oua_address1'] . '</p>';
                                                }

                                                if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                                    $shippingAddress .= '<p>' . $orderDetail['shippingAddress']['oua_address2'] . '</p>';
                                                }

                                                $shippingAddress .= '<p>';
                                                if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ',';
                                                }

                                                if ($orderDetail['shippingAddress']['oua_state'] != '') {
                                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_state'] . ', ';
                                                }

                                                if ($orderDetail['shippingAddress']['oua_zip'] != '') {
                                                    $shippingAddress .= '-' . $orderDetail['shippingAddress']['oua_zip'];
                                                }
                                                $shippingAddress .= '</p>';

                                                if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                                    $shippingAddress .= '<p>' . $orderDetail['shippingAddress']['oua_country'] . '</p>';
                                                }

                                                if ($orderDetail['shippingAddress']['oua_phone'] != '') {
                                                    $shippingAddress .= '<p class="c-info"><strong><i class="fas fa-mobile-alt mr-2"></i> ' . $orderDetail['shippingAddress']['oua_dial_code'] . ' ' . $orderDetail['shippingAddress']['oua_phone'] . '</strong></p>';
                                                }
                                                echo $shippingAddress
                                                ?>
                                            </div>
                                        <?php } ?>

                                        <?php if (!empty($orderDetail['payments'])) { ?>
                                            <hr class="dotted">
                                            <h5><?php echo Labels::getLabel('LBL_Payment_History', $siteLangId); ?></h5>
                                            <?php foreach ($orderDetail['payments'] as $row) { ?>
                                                <div class="payment-mode">
                                                    <div class="cc-payment">
                                                        <span class="cc-num"><?php echo $row['opayment_method']; ?></span>
                                                        <span
                                                            class="cc-num"><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, false, true, false, true); ?></span>
                                                        <span
                                                            class="cc-num"><?php echo FatDate::format($row['opayment_date']); ?></span>
                                                    </div>
                                                    <div class="txt-id">
                                                        <p>
                                                            <strong><?php echo Labels::getLabel('LBL_Transaction_number', $siteLangId); ?></strong>
                                                            <br>
                                                            <?php echo $row['opayment_gateway_txn_id']; ?>
                                                        </p>

                                                    </div>
                                                    <div class="cc-payment">
                                                        <?php echo nl2br($row['opayment_comments']); ?>
                                                        <?php
                                                        $class = '';
                                                        if (Orders::ORDER_PAYMENT_CANCELLED == $row['opayment_txn_status']) {
                                                            $class = "label-danger";
                                                        } elseif (Orders::ORDER_PAYMENT_PENDING == $row['opayment_txn_status']) {
                                                            $class = "label-info";
                                                        } elseif (Orders::ORDER_PAYMENT_PAID == $row['opayment_txn_status']) {
                                                            $class = "label-success";
                                                        }
                                                        ?>
                                                        <span class="label label-inline <?php echo $class; ?>">
                                                            <?php echo $orderStatusArr[$row['opayment_txn_status']]; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <div class="order-block">
                                    <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                        data-target="#billing-address" aria-expanded="false"
                                        aria-controls="billing-address">
                                        <?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId); ?> : <i
                                            class="dropdown-toggle-custom-arrow"></i>
                                    </h4>
                                    <div class="collapse" id="billing-address">
                                        <div class="order-block-data">
                                            <div class="address-info">
                                                <?php
                                                $billingAddress = '<p>' . $orderDetail['billingAddress']['oua_name'] . '</p>';
                                                if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                                    $billingAddress .= '<p>' . $orderDetail['billingAddress']['oua_address1'] . '</p>';
                                                }

                                                if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                                    $billingAddress .= '<p>' . $orderDetail['billingAddress']['oua_address2'] . '</p>';
                                                }

                                                $billingAddress .= '<p>';

                                                if ($orderDetail['billingAddress']['oua_city'] != '') {
                                                    $billingAddress .= $orderDetail['billingAddress']['oua_city'] . ', ';
                                                }

                                                if ($orderDetail['billingAddress']['oua_state'] != '') {
                                                    $billingAddress .= $orderDetail['billingAddress']['oua_state'];
                                                }

                                                if ($orderDetail['billingAddress']['oua_zip'] != '') {
                                                    $billingAddress .= ', ' . $orderDetail['billingAddress']['oua_zip'];
                                                }
                                                $billingAddress .= '</p>';

                                                if ($orderDetail['billingAddress']['oua_country'] != '') {
                                                    $billingAddress .= '<p>' . $orderDetail['billingAddress']['oua_country'] . '</p>';
                                                }

                                                if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                                    $billingAddress .= '<p class="c-info"><strong> <i class="fas fa-mobile-alt mr-2"></i> ' . $orderDetail['billingAddress']['oua_dial_code'] . ' ' . $orderDetail['billingAddress']['oua_phone'] . '</strong></p>';
                                                }
                                                echo $billingAddress;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if (trim($pickupAddressHtml) != '') { ?>
                                    <div class="order-block">
                                        <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                            data-target="#pickup-address" aria-expanded="false"
                                            aria-controls="pickup-address">
                                                <?php echo Labels::getLabel('LBL_Pickup_Address:', $siteLangId); ?>
                                            <i class="dropdown-toggle-custom-arrow"></i>
                                        </h4>

                                        <div class="collapse" id="pickup-address">
                                            <?php /* <hr class="dotted"> */ ?>
                                            <div class="order-block-data">
                                                <?php echo $pickupAddressHtml; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if (!empty($verificationFldsData)) { ?>
                                    <div class="order-block">
                                        <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                            data-target="#verification-data" aria-expanded="false"
                                            aria-controls="verification-data">
                                            <?php echo Labels::getLabel('LBL_Verification_Data', $siteLangId); ?> <i
                                                class="dropdown-toggle-custom-arrow"></i>
                                        </h4>
                                        <?php
                                        $verificationData = [];
                                        foreach ($verificationFldsData as $val) {
                                            $verificationData[$val['ovd_vfld_id']][] = $val;
                                        }
                                        ?>
                                        <div class="collapse" id="verification-data">
                                            <div class="order-block-data">
                                                <div class="list-specification">
                                                    <ul class="">
                                                        <?php foreach ($verificationData as $key => $val) { ?>
                                                            <li>
                                                                <span class="label"><?php echo $val[0]['ovd_vflds_name']; ?>
                                                                </span>
                                                                <span class="value">
                                                                    <?php
                                                                    if ($val[0]['ovd_vflds_type'] == VerificationFields::FLD_TYPE_TEXTBOX) {
                                                                        echo (trim($val[0]['ovd_value']) != '') ? $val[0]['ovd_value'] : Labels::getLabel('LBL_N/A', $siteLangId);
                                                                    } else {
                                                                        $downloadUrl = UrlHelper::generateUrl('SellerOrders', 'downloadAttachedFile', array($orderDetail['order_order_id'], $val[0]['ovd_vfld_id']));
                                                                        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderDetail['order_order_id'], $val[0]['ovd_vfld_id'], 0, true, 0, false);
                                                                        if (empty($file_row)) {
                                                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                                        } else {
                                                                            echo '<a href="' . $downloadUrl . '"> ' . $file_row['afile_name'] . '</a>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </span>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if (!empty($shopAgreementArr) && 0) { ?>
                                    <div class="order-block">
                                        <h4 class="dropdown-toggle-custom collapsed" data-toggle="collapse"
                                            data-target="#rental-agreement" aria-expanded="false"
                                            aria-controls="rental-agreement">
                                            <?php echo Labels::getLabel('LBL_Rental_Agreement', $siteLangId); ?> <i
                                                class="dropdown-toggle-custom-arrow"></i>
                                        </h4>
                                        <div class="collapse" id="rental-agreement">
                                            <div class="order-block-data">
                                                <div class="list-specification">
                                                    <ul class="">
                                                        <?php
                                                        foreach ($shopAgreementArr as $key => $val) {
                                                            $afileName = AttachedFile::getAttributesById($val['agreementFileId'], 'afile_name');
                                                            ?>
                                                            <li>
                                                                <span
                                                                    class="label"><?php echo Labels::getLabel('LBL_Shop_Name', $siteLangId); ?></span>
                                                                <span class="value"><?php echo $val['shopName']; ?></span>
                                                            </li>
                                                            <li>
                                                                <span
                                                                    class="label"><?php echo Labels::getLabel('LBL_Shop_Agreement', $siteLangId); ?></span>
                                                                <span class="value">
                                                                    <a href="<?php echo UrlHelper::generateUrl('Seller', 'downloadDigitalFile', [$key, $val['agreementFileId'], AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>"
                                                                       title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                                                                           <?php echo $afileName; ?>
                                                                    </a>
                                                                </span>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                    <?php if (!empty($signatureData)) { ?>
                                                        <h5><?php echo Labels::getLabel('LBL_Signature', $siteLangId); ?></h5>
                                                        <img class="attached-img"
                                                             src="<?php echo CommonHelper::generateUrl('image', 'signature', array($signatureData['afile_record_id'], 0, 'ORIGINAL', $signatureData['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>"
                                                             title="<?php echo $signatureData['afile_name']; ?>"
                                                             alt="<?php echo $signatureData['afile_name']; ?>" />
                                                         <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if ($totalSecurityAmount > 0) { ?>
    <!-- Modal -->
    <div class="modal fade" id="security_modal" tabindex="-1" role="dialog" aria-labelledby="securityModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="securityModalLabel">
                        <?php echo Labels::getLabel('LBL_Security_List', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $securityHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($totalSecurityAmount, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($taxOptionsHtml != '') { ?>
    <div class="modal fade" id="tax_modal" tabindex="-1" role="dialog" aria-labelledby="taxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taxModalLabel"><?php echo Labels::getLabel('LBL_Tax_List', $siteLangId); ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $taxOptionsHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <?php if ($roundingOffTotal != 0) { ?>
                        <div class="row text-left"> 
                            <div class="col-auto">
                                <span><?php echo ($roundingOffTotal > 0) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?> : </span>
                            </div>
                            <div class="col-auto">
                                <span><?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></span>
                            </div>
                        </div> | 
                    <?php } ?>
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($totalTaxes, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($isShipping) { ?>
    <div class="modal fade" id="shipping_modal" tabindex="-1" role="dialog" aria-labelledby="shippingModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingModalLabel">
                        <?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $shippingHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($volumnDiscountAmount != 0) { ?>
    <div class="modal fade" id="volume_modal" tabindex="-1" role="dialog" aria-labelledby="volumeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="volumeModalLabel">
                        <?php echo Labels::getLabel('LBL_Volume_Discount_Details', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $volumnDiscountHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($volumnDiscountAmount, true, false, true, false, true); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php } ?>
<?php if ($durationDiscountAmount != 0) { ?>
    <div class="modal fade" id="duration_discount_modal" role="dialog" aria-labelledby="durationDiscountModalLabel"
         aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="durationDiscountModalLabel">
                        <?php echo Labels::getLabel('LBL_Duration_Discount_Details', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $durationDiscountHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($durationDiscountAmount, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if ($couponDiscount != 0) { ?>
    <div class="modal fade" id="discount_modal" role="dialog" aria-labelledby="discountModalLabel" aria-hidden="true"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discountModalLabel">
                        <?php echo Labels::getLabel('LBL_Discount_Details', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $couponDiscountHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($couponDiscount, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php if (!empty($attachedServices)) { ?>
    <div class="modal fade" id="addon_modal" role="dialog" aria-labelledby="addonModalLabel" aria-hidden="true"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addonModalLabel">
                        <?php echo Labels::getLabel('LBL_Addons_Details', $siteLangId); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table ">
                        <thead>
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Addon_Name', $siteLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo $addonAmountHtml; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="col-auto">
                        <span><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($addonTotal, true, false, true, false, true); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>


<script>
    var canShipByPlugin = <?php echo (true === $canShipByPlugin ? 1 : 0); ?>;
    var orderShippedStatus = <?php echo OrderStatus::ORDER_SHIPPED; ?>;

    var SHIPPING_STATUS = <?php echo FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"); ?>;
    var RENTAL_RETURN_STATUS = <?php echo FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"); ?>;


    $('#security_modal').insertAfter('.wrapper');
    $('#tax_modal').insertAfter('.wrapper');
    $('#shipping_modal').insertAfter('.wrapper');
    $('#volume_modal').insertAfter('.wrapper');
    $('#duration_discount_modal').insertAfter('.wrapper');
    $('#discount_modal').insertAfter('.wrapper');
    $('#addon_modal').insertAfter('.wrapper');

    $(document).ready(function () {
        $('input[name="opd_mark_rental_return_date"]').datetimepicker({
            /* minDate: new Date('<?php echo date('Y-m-d 00:00:00', strtotime($orderDetail['opd_rental_start_date'])); ?>'), */
            maxDate: new Date('<?php echo date('Y-m-d 00:00:00'); ?>')});
    });





</script>
<style>
    .disabled-input {
        color: rgba(0, 0, 0, 0.38) !important;
        background-color: rgba(0, 0, 0, 0.12) !important;
        box-shadow: none;
        cursor: initial;
        border-color: transparent !important;
    }
</style>