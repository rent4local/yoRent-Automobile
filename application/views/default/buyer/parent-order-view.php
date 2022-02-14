<?php
$navData = (isset($activeAction)) ? ['action' => $activeAction, 'controller' => 'buyer'] : ['action' => 'orders', 'controller' => 'buyer'];
if ($orderDetail['order_is_rfq'] == applicationConstants::YES) {
    $navData = array(
        'controller' => 'requestforquotes',
        'action' => 'orders'
    );
}
$this->includeTemplate('_partial/dashboardNavigation.php', ['navData' => $navData]);

$canCancelOrder = false;
$canReturnRefund = false;
$canReviewOrders = false;
$canSubmitFeedback = false;
$orderStatusArr = Orders::getOrderPaymentStatusArr($siteLangId);

$isrentalOrder = false;
if (true == $primaryOrder) {
    $canCancelOrder = (in_array($childOrderDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses()));
    $canReturnRefund = (in_array($childOrderDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderReturnStatuses()));
    if (in_array($childOrderDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
        $canReviewOrders = true;
    }
    $canSubmitFeedback = Orders::canSubmitFeedback($childOrderDetail['order_user_id'], $childOrderDetail['order_id'], $childOrderDetail['op_selprod_id']);
    if ($childOrderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) {
        $cancelAge = $childOrderDetail['selprod_cancellation_age'];
        $returnAge = $childOrderDetail['selprod_return_age'];
        if ($childOrderDetail['selprod_cancellation_age'] == '') {
            $cancelAge = $childOrderDetail['shop_cancellation_age'];
        }
        if ($childOrderDetail['selprod_return_age'] == '') {
            $returnAge = $childOrderDetail['shop_return_age'];
        }
        $daysSpent = round((time() - strtotime($childOrderDetail['order_date_added'])) / (60 * 60 * 24));
        $canCancelOrder = ($cancelAge < $daysSpent) ? false : $canCancelOrder;
        $canReturnRefund = ($returnAge < $daysSpent) ? false : $canReturnRefund;
    }
    if ($childOrderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
        $isrentalOrder = true;
    }
    
    if ($childOrderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && strtotime($childOrderDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d'))) {
        $canReturnRefund = false;
    }
    
    if ($childOrderDetail['opd_extend_from_op_id'] > 0) {
        $canReturnRefund = false;
    }
    
}
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header">
            <div class="row">
                <div class="col">
                    <h2 class="content-header-title no-print">
                    <?php if ($orderDetail['order_is_rfq'] == applicationConstants::YES) { 
                        echo Labels::getLabel('LBL_RFQ_Order', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_Order_Details', $siteLangId);
                    }?>
                    </h2>
                </div>
                <?php if (true == $primaryOrder) { ?>
                <div class="col-auto">
                    <div class="no-print">
                        <?php if ($canCancelOrder && $childOrderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                        <a class="btn btn-outline-brand btn-sm no-print"
                            href="<?php echo UrlHelper::generateUrl('Buyer', 'orderCancellationRequest', array($childOrderDetail['op_id'])); ?>">
                            <?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>
                        </a>
                        <?php } ?>
                        <?php if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && $canReviewOrders && $canSubmitFeedback && $childOrderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                        <a class="btn btn-outline-brand btn-sm no-print"
                            href="<?php echo UrlHelper::generateUrl('Buyer', 'orderFeedback', array($childOrderDetail['op_id'])); ?>">
                            <?php echo Labels::getLabel('LBL_Feedback', $siteLangId); ?>
                        </a>
                        <?php } ?>
                        <?php if ($canReturnRefund && $childOrderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON && !$childOrderDetail['deliveredMarkedBy']) { ?>
                        <a class="btn btn-outline-brand btn-sm no-print"
                            href="<?php echo UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($childOrderDetail['op_id'])); ?>">
                            <?php echo Labels::getLabel('LBL_Refund', $siteLangId); ?>
                        </a>
                        <?php } ?>
                        <?php if ($thread_id > 0) { ?>
                        <a class="btn btn-outline-brand btn-sm no-print"
                            href="<?php echo UrlHelper::generateUrl('Account', 'viewMessages', array($thread_id, $message_id)); ?>">
                            <?php echo Labels::getLabel('LBL_View_Order_Message', $siteLangId); ?>
                        </a>
                        <?php } else { ?>
                        <a class="btn btn-outline-brand btn-sm no-print" href="javascript:void(0);"
                            onclick="sendOrderMessage(<?php echo $childOrderDetail['op_id']; ?>, 'buyer')">
                            <?php echo Labels::getLabel('LBL_Send_message_to_seller', $siteLangId); ?>
                        </a>
                        <?php } ?>
                        <?php if (0 < $opId && !$orderDetail['order_deleted'] && !$orderDetail["order_payment_status"] && 'TransferBank' == $orderDetail['plugin_code']) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('Buyer', 'viewOrder', [$orderDetail['order_id']]); ?>"
                            class="btn btn-outline-brand btn-sm no-print"
                            title="<?php echo Labels::getLabel('LBL_ADD_PAYMENT_DETAIL', $siteLangId); ?>">
                            <?php echo Labels::getLabel('LBL_ADD_PAYMENT_DETAIL', $siteLangId); ?>
                        </a>
                        <?php } ?>
                        <?php if ($orderDetail['order_is_rfq'] == applicationConstants::YES) { ?>
                            <a class="btn btn-outline-brand btn-sm no-print" href="<?php echo UrlHelper::generateUrl('RequestForQuotes', 'requestView', array($childOrderDetail['order_rfq_id'])); ?>" target="_blank">
                                <?php echo Labels::getLabel('LBL_View_RFQ', $siteLangId); ?>
                            </a>
                        <?php } ?>
                        
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <div class="order-number">
                            <small class="sm-txt"><?php echo Labels::getLabel('LBL_Order', $siteLangId); ?> #</small>
                            <span class="numbers">
                                <?php echo (true == $primaryOrder) ? $childOrderDetail['op_invoice_number'] : $orderDetail['order_id']; ?>
                                <?php
                                if (true == $primaryOrder) {
                                    if ($childOrderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                        if (strtotime($childOrderDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d 00:00:00')) && strtotime($childOrderDetail['opd_rental_end_date']) >= strtotime(date('Y-m-d 00:00:00')) && in_array($childOrderDetail['op_status_id'], OrderStatus::statusArrForRentalExpireNote())) {
                                            $dateToRentalEnd = CommonHelper::getDifferenceBetweenDates(date('Y-m-d 00:00:00'), $childOrderDetail['opd_rental_end_date'], 0, ProductRental::DURATION_TYPE_DAY);
                                            if ($dateToRentalEnd >= 3) {
                                                $rentalEndMsgClass = 'alert-success';
                                            } elseif ($dateToRentalEnd >= 2) {
                                                $rentalEndMsgClass = 'alert-warning';
                                            } else {
                                                $rentalEndMsgClass = 'alert-danger';
                                            }
                                            ?>
                                <span
                                    class="notice <?php echo $rentalEndMsgClass; ?>"><?php echo sprintf(Labels::getLabel('LBL_%s_Day(s)_Remaining_to_end_Rental', $siteLangId), $dateToRentalEnd); ?></span>
                                <?php } elseif (strtotime($childOrderDetail['opd_rental_end_date']) < strtotime(date('Y-m-d 00:00:00')) && in_array($childOrderDetail['op_status_id'], OrderStatus::statusArrForLateChargeNote())) {
                                                ?>
                                <span
                                    class="notice"><?php echo Labels::getLabel('LBL_Rental_Duration_Ended._Late_Charges_may_be_Apply', $siteLangId); ?></span>
                                <?php
                                            }

                                            if ($parentOrderId != '') {
                                                echo '<span class="notice ">' . Labels::getLabel('LBL_This_order_is_extended_from_', $siteLangId) . ' <a href="' . CommonHelper::generateUrl('Buyer', 'viewOrder', array($parentOrderId, $extendFromOpId)) . '">#' . $parentOrderId . '</a> </span>';
                                            }
                                            if (!empty($extendChildOrder)) {
                                                echo '<span class="notice ">' . Labels::getLabel('LBL_This_order_is_extended_By', $siteLangId) . ' <a href="' . CommonHelper::generateUrl('Buyer', 'viewOrder', array($extendChildOrder['opd_order_id'], $extendChildOrder['opd_op_id'])) . '">#' . $extendChildOrder['opd_order_id'] . '</a> </span>';
                                            }
                                        }
                                    }
                                    ?>
                            </span>
                        </div>
                    </h5>
                    <div class="btn-group orders-actions">
                        <?php if (true == $primaryOrder && $childOrderDetail['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                        <?php if (FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS") == $childOrderDetail["op_status_id"] && true == $primaryOrder && $childOrderDetail['opd_extend_from_op_id'] <= 0 && empty($extendChildOrder) && $childOrderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                        <a href="javascript:void(0);" class="btn btn-brand btn-sm"
                            onClick="extendRentalOrderForm(<?php echo $childOrderDetail['op_id'] ?>, 0);"><?php echo Labels::getLabel('LBL_Extend_Order', $siteLangId); ?></a>
                        <?php
                               }
                           }
                           ?>
                        <a href="<?php echo (0 < $opId) ? UrlHelper::generateUrl('Buyer', 'viewInvoice', [$orderDetail['order_id'], $opId]) : UrlHelper::generateUrl('Buyer', 'viewInvoice', [$orderDetail['order_id']]); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Print_order_detail', $siteLangId); ?></a>
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
                                            <th><?php echo Labels::getLabel('LBL_Unit_Price', $siteLangId); ?>
                                                <?php if ($isrentalOrder) { ?>
                                                    <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="<?php echo Labels::getLabel('LBL_Per_Duration_Price', $siteLangId); ?>"><i class="fa fa-info-circle"></i></a>
                                                <?php } ?>
                                            </th>
                                            <th><?php echo Labels::getLabel('LBL_Total_Price', $siteLangId); ?>
                                                <?php if ($isrentalOrder) { ?>
                                                    <a data-toggle="tooltip" href="javascript:void(0);" data-original-title="<?php echo Labels::getLabel('LBL_Total_Amount_tooltip', $siteLangId); ?>"><i class="fa fa-info-circle"></i></a>
                                                <?php } ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($primaryOrder) {
                                            $arr[] = $childOrderDetail;
                                        } else {
                                            $arr = $childOrderDetail;
                                        }
                                        $couponDiscount = $volumnDiscountAmount = $durationDiscountAmount = $shippingCharges = $totalTaxes = $rewardPointTotal = $cartTotalAmount = $totalSecurityAmount = $orderNetTotal = $roundingOffTotal = $addonTotal = $totalLateCharges = 0;
                                        $isShipping = false;
                                        $securityHtml = $taxOptionsHtml = $pickupAddressHtml = $durationDiscountHtml = $volumnDiscountHtml = $shippingHtml = $couponDiscountHtml = $addonAmountHtml = '';
                                        foreach ($arr as $childOrder) {
                                            $services = (isset($attachedServicesArr[$childOrder['op_id']])) ? $attachedServicesArr[$childOrder['op_id']] : [];


                                            $prodOrBatchUrl = 'javascript:void(0)';
                                            if ($childOrder['op_is_batch']) {
                                                $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($childOrder['op_selprod_id']));
                                                $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($childOrder['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                            } else {
                                                if (Product::verifyProductIsValid($childOrder['op_selprod_id']) == true) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($childOrder['op_selprod_id']));
                                                }
                                                $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($childOrder['selprod_product_id'], "SMALL", $childOrder['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                            }

                                            $optionsHtml = ($childOrder['op_selprod_options'] != '') ? ' | ' . $childOrder['op_selprod_options'] : "";
                                            
                                            $productTitle = (trim($childOrder['op_selprod_title']) == '') ? $childOrder['op_product_identifier'] : $childOrder['op_selprod_title'];
                                            
                                           
                                            $productImgHtml = '<td><div class="item"> 
                                                <figure class="item__pic"> 
                                                <a href="' . $prodOrBatchUrl . '">
                                                    <img src="' . $prodOrBatchImgUrl . '" title="' . $childOrder['op_product_name'] . '" alt="' . $childOrder['op_product_name'] . '" />
                                                </a>
                                                </figure>
                                                <div class="item__description">
                                                    <div class="item__title">
                                                        <a title="' . $productTitle . '" href="' . $prodOrBatchUrl . '">
                                                            ' . $productTitle . '
                                                        </a>
                                                    </div>
                                                    <div class="item__options"> '. Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $childOrder['op_qty'] .' '. $optionsHtml . '
                                                </div>
                                                    
                                                </div>
                                                </div></td>';



                                            if (Shipping::FULFILMENT_PICKUP == $childOrder['opshipping_fulfillment_type']) {
                                                $pickupAddressHtml .= '<div class="address-info">';
                                                if (!$primaryOrder) {
                                                    $pickupAddressHtml .= '<h5>' . $childOrder['op_selprod_title'] . '</h5>';
                                                }

                                                $pickupAddressHtml .= '<p>' . $childOrder['addr_name'] . ', ';
                                                $address1 = !empty($childOrder['addr_address1']) ? ' ' . $childOrder['addr_address1'] . ' ' : '';
                                                $address2 = !empty($childOrder['addr_address2']) ? ', ' . $childOrder['addr_address2'] . ' ' : '';
                                                $city = !empty($childOrder['addr_city']) ? ', '. $childOrder['addr_city'] : '';
                                                $state = !empty($childOrder['state_name']) ? ', ' . $childOrder['state_name'] : ', ' . $childOrder['state_identifier'];
                                                $zip = !empty($childOrder['addr_zip']) ? '(' . $childOrder['addr_zip'] . ')' : '';
                                                $stateStr = $city . $state . $zip;
                                                $country = !empty($childOrder['country_name']) ? ' , ' . $childOrder['country_name'] . ' ' : ', ' . $childOrder['country_code'] . ' ';
                                                $pickupAddressHtml .= $address1 . $address2 . $stateStr . $country . '</p>'; 

                                                $pickupAddressHtml .= '<p class="c-info"><strong><i class="fas fa-mobile-alt mr-2"></i>' . $childOrder['addr_phone'] . '</strong></p>';

                                                if ($childOrder['order_is_rfq'] == applicationConstants::NO && $childOrder['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) {
                                                    $fromTime = isset($childOrder["opshipping_time_slot_from"]) ? date('H:i', strtotime($childOrder["opshipping_time_slot_from"])) : '';
                                                    $toTime = isset($childOrder["opshipping_time_slot_to"]) ? date('H:i', strtotime($childOrder["opshipping_time_slot_to"])) : '';
                                                    $date = isset($childOrder["opshipping_date"]) ? FatDate::format($childOrder["opshipping_date"]) : '';
                                                    $pickupAddressHtml .= '<p class="c-info"><strong><i class="fas fa-calendar mr-2"></i>' . $date . ' ' . $fromTime . ' - ' . $toTime . '</strong></p>';
                                                }

                                                if (!$primaryOrder) {
                                                    $pickupAddressHtml .= '<hr class="dotted" />';
                                                }
                                                $pickupAddressHtml .= '</div>';
                                            } else {
                                                $isShipping = true;
                                                $shippingHtml .= '<tr>'. $productImgHtml .'<td>' . $childOrder['opshipping_label'] . '</td><td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'SHIPPING'), true, false, true, false, true) . '</td></tr>';
                                            }

                                            $cartTotalAmount += CommonHelper::orderProductAmount($childOrder, 'cart_total');
                                            $couponDiscount += CommonHelper::orderProductAmount($childOrder, 'DISCOUNT');
                                            $shippingCharges += CommonHelper::orderProductAmount($childOrder, 'SHIPPING');
                                            $totalTaxes += CommonHelper::orderProductAmount($childOrder, 'TAX');
                                            $volumnDiscountAmount += CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
                                            $durationDiscountAmount += CommonHelper::orderProductAmount($childOrder, 'DURATION_DISCOUNT');
                                            $rewardPointTotal += CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
                                            $totalSecurityAmount += $childOrder['opd_rental_security'] * $childOrder['op_qty'];
                                            $orderNetTotal += CommonHelper::orderProductAmount($childOrder, 'NETAMOUNT');
                                            $roundingOffTotal += $childOrder['op_rounding_off'];
                                            $totalLateCharges += $childOrder['charge_total_amount'];
                                            $couponDiscountHtml .= '<tr>'. $productImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                            if ($childOrder['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                                $securityHtml .= '<tr>'. $productImgHtml .'<td>' . CommonHelper::displayMoneyFormat(($childOrder['opd_rental_security'] * $childOrder['op_qty']), true, false, true, false, true) . '</td></tr>';

                                                $durationDiscountHtml .= '<tr>'. $productImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'DURATION_DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                            } else {
                                                $volumnDiscountHtml .= '<tr>'. $productImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                            }
                                            if (empty($childOrder['taxOptions'])) {
                                                $taxOptionsHtml .= '<tr>
                                                '. $productImgHtml .'
                                                <td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'TAX'), true, false, true, false, true) . '</td>
                                                </tr>';
                                            } else {
                                                $taxOptionsHtml .= '<tr>'. $productImgHtml . '<td>';
                                                foreach ($childOrder['taxOptions'] as $key => $val) {
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
                                                                title="<?php echo $childOrder['op_product_name']; ?>"
                                                                alt="<?php echo $childOrder['op_product_name']; ?>" />
                                                        </a>
                                                    </figure>
                                                    <div class="item__description">
                                                        <div class="item__title">
                                                            <a title="<?php echo $productTitle; ?>"
                                                                href="<?php echo $prodOrBatchUrl; ?>">
                                                                <?php echo $productTitle; ?></a><br>
                                                            <?php /* echo $childOrder['op_product_name']; */ ?>
                                                        </div>
                                                        <div class="item__options">
                                                            <?php
                                                                echo Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $childOrder['op_qty'];
                                                                if ($childOrder['op_selprod_options'] != '') {
                                                                    echo ' | ' . $childOrder['op_selprod_options'];
                                                                }
                                                                ?>
                                                        </div>
                                                        <div class="item__sold_by">
                                                            <?php echo Labels::getLabel('LBL_Sold_By', $siteLangId) . ': ' . $childOrder['op_shop_name']; ?>
                                                        </div>
                                                        <?php
                                                            if ($childOrder['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                                                $duration = CommonHelper::getDifferenceBetweenDates($childOrder['opd_rental_start_date'], $childOrder['opd_rental_end_date'], $childOrder['op_selprod_user_id'], $childOrder['opd_rental_type']);
                                                            ?>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="13px" height="13px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#calender">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                                <?php echo Labels::getLabel('LBL_From', $siteLangId) . ': ' . date('M d, Y ', strtotime($childOrder['opd_rental_start_date'])); ?>
                                                                |
                                                                <?php echo Labels::getLabel('LBL_To', $siteLangId) . ': ' . date('M d, Y ', strtotime($childOrder['opd_rental_end_date'])); ?>
                                                            </small>
                                                        </div>
                                                        <div class="item__date-range">
                                                            <small>
                                                                <i class="icn">
                                                                    <svg width="13px" height="13px" class="svg">
                                                                        <use
                                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/sprite.svg#time">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                                <?php
                                                                        echo Labels::getLabel('LBL_Duration', $siteLangId) . ': ';
                                                                        echo CommonHelper::displayProductRentalDuration($duration, $childOrder['opd_rental_type'], $siteLangId);
                                                                        ?>
                                                            </small>
                                                        </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php 
                                                if ($childOrder['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                                    echo CommonHelper::displayMoneyFormat($childOrder['opd_duration_price'], true, false, true, false, true);
                                                } else {
                                                    echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, true, false, true);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'cart_total'), true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($services)) { ?>
                                        <tr class="row-addons">
                                            <td colspan="3">
                                                <div class="addons">
                                                    <button class="addons_trigger collapsed" type="button"
                                                        data-toggle="collapse"
                                                        data-target="#service_<?php echo $childOrder['op_id']; ?>"
                                                        aria-expanded="true"
                                                        aria-controls="service_<?php echo $childOrder['op_id']; ?>">
                                                        <span
                                                            class="txt"><?php echo Labels::getLabel('LBL_Addons_And_Details', $siteLangId); ?>
                                                            <span class="count">
                                                                <?php echo count($services); ?></span></span>
                                                        <i class="icn"></i>
                                                    </button>
                                                    <div class="collapse"
                                                        id="service_<?php echo $childOrder['op_id']; ?>">
                                                        <ul class="addons-list">
                                                            <?php
                                                                    foreach ($services as $service) {
                                                                        $addonImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($service['op_selprod_id'], "THUMB", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                                        $addonTotal += CommonHelper::orderProductAmount($service, 'cart_total');
                                                                        $couponDiscount += CommonHelper::orderProductAmount($service, 'DISCOUNT');
                                                                        $shippingCharges += CommonHelper::orderProductAmount($service, 'SHIPPING');
                                                                        $totalTaxes += CommonHelper::orderProductAmount($service, 'TAX');
                                                                        $volumnDiscountAmount += CommonHelper::orderProductAmount($service, 'VOLUME_DISCOUNT');
                                                                        $durationDiscountAmount += CommonHelper::orderProductAmount($service, 'DURATION_DISCOUNT');
                                                                        
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
                                                                            </div> <div class="item__options"> '. Labels::getLabel('LBL_QTY', $siteLangId) . ' : ' . $service['op_qty'] . ' </div>
                                                                        </div></div></td>';
                                                                        
                                                                        $couponDiscountHtml .= '<tr>'. $addonImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'DISCOUNT'), true, false, true, false, true) . '</td></tr>';
                                                                        
                                                                        
                                                                        $rewardPointTotal += CommonHelper::orderProductAmount($service, 'REWARDPOINT');
                                                                        $totalSecurityAmount += $service['opd_rental_security'] * $service['op_qty'];
                                                                        $orderNetTotal += CommonHelper::orderProductAmount($service, 'NETAMOUNT');
                                                                        $roundingOffTotal += $service['op_rounding_off'];
                                                                        $totalLateCharges += $service['charge_total_amount'];
                                                                        $addonAmountHtml .= '<tr>'. $addonImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'cart_total'), true, false, true, false, true) . '</td></tr>';
                                                                        if (empty($service['taxOptions'])) {
                                                                            $taxOptionsHtml .= '<tr>'. $addonImgHtml .'<td>' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($service, 'TAX'), true, false, true, false, true) . '</td></tr>';
                                                                        } else {
                                                                            $taxOptionsHtml .= '<tr>'. $addonImgHtml .'<td>';
                                                                            foreach ($service['taxOptions'] as $key => $val) {
                                                                                $taxOptionsHtml .= '<strong>' . CommonHelper::displayTaxPercantage($val, true) . '</strong> : ' . CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true) . '<br />';
                                                                            }
                                                                            $taxOptionsHtml .= '</td></tr>';
                                                                        }
                                                                        ?>
                                                            <li>
                                                                <div class="addons-img">
                                                                    <img src="<?php echo $addonImgUrl; ?>" title="<?php echo $addonTitle; ?>" alt="<?php echo $addonTitle; ?>">
                                                                </div>
                                                                <div class="addons-name"><?php echo $addonTitle; ?></div>
                                                            </li>
                                                            <?php } ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>
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
                                            if ($primaryOrder && $opStatusId == $childOrderDetail['op_status_id']) {
                                                $enableDisableClass .= ' currently';
                                            }
                                            if (!$primaryOrder && $orderDetail['order_payment_status'] == Orders::ORDER_PAYMENT_PAID) {
                                                $enableDisableClass = "enable";
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
                                                        <p class="clipboard_url">
                                                            <?php echo $row['oshistory_tracking_number']; ?></p>
                                                        <a class="clipboard_btn" data-toggle="tooltip"
                                                            onclick="copyContent(this)" href="javascript:void(0);"
                                                            data-original-title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId); ?>"><i
                                                                class="far fa-copy"></i></a>
                                                    </div>    
                                                    <?php $str = '';
                                                    if (empty($childOrderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                        $str .= "<span class=' mt-3 ml-2'> VIA <em>" . CommonHelper::displayNotApplicable($siteLangId, $childOrderDetail["opshipping_label"]) . "</em></span>";
                                                    } elseif (!empty($childOrderDetail['opship_tracking_url'])) {
                                                        $str .= " <a class='btn btn-brand mt-2 ml-2' href='" . $childOrderDetail['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                    }
                                                    echo $str ?>
                                                </div>
                                                
                                                <?php
                                                } else {
                                                    $trackingNumber = $row['oshistory_tracking_number'];
                                                    $carrier = $row['oshistory_courier'];
                                                    echo ($row['oshistory_tracking_number']) ? '<h6><strong>' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) . '</strong> </h6>' : '';
                                                if (trim($trackingNumber) != '') { ?>
                                                <div class="d-flex">
                                                    <div class="clipboard">
                                                        <p class="clipboard_url">
                                                            <?php echo $row['oshistory_tracking_number']; ?></p>
                                                        <a class="clipboard_btn" data-toggle="tooltip" data-original-title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId); ?>" onclick="copyContent(this)" href="javascript:void(0);"><i class="far fa-copy"></i>
                                                        </a>
                                                    </div>
                                                    <?php  if ($row['oshistory_orderstatus_id'] != OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) { 
                                                        echo "<span class=' mt-3 ml-2'>"; 
                                                        echo Labels::getLabel('LBL_VIA', $siteLangId); ?>
                                                            <em><?php echo CommonHelper::displayNotApplicable($siteLangId, $childOrderDetail["opshipping_label"]); ?></em>
                                                        <?php 
                                                        echo "</span>";
                                                        } else {
                                                            if (trim($row['oshistory_tracking_url']) != '') {
                                                                echo  " <a class='btn btn-brand mt-2 ml-2' href='" . $row['oshistory_tracking_url'] . "'  target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                            }
                                                        
                                                            echo "<span class=' mt-3 ml-2'>".  Labels::getLabel('LBL_VIA', $siteLangId). " <em>". $carrier. " </em></span>";
                                                        } ?>
                                                </div>
                                                <?php }
                                                    }
                                                }
                                                                if (isset($statusAddressData[$row['oshistory_id']])) { 
																$dropOffAddress = $statusAddressData[$row['oshistory_id']];
																?>

                                                <div class="my-addresses__body mb-2 p-0">
                                                    <address class="delivery-address">
                                                        <h5><span><?php echo $dropOffAddress['addr_name'];?></span><span
                                                                class="tag"><?php echo $dropOffAddress['addr_title'];?></span>
                                                        </h5>
                                                        <p>
                                                            <?php  echo $dropOffAddress['addr_address1'].',' ; ?>

                                                            <?php  echo $dropOffAddress['addr_city'].','; ?>
                                                            <?php  echo $dropOffAddress['state_name'].','; ?>
                                                            <?php  echo $dropOffAddress['country_name']; ?>
                                                            <br>
                                                            <?php 	echo Labels::getLabel("LBL_Zip", $siteLangId) . ':' . $dropOffAddress['addr_zip']?>

                                                        </p>
                                                        <p class="phone-txt">
                                                            <i class="fas fa-mobile-alt"></i>
                                                            <?php echo Labels::getLabel("LBL_Phone", $siteLangId) . ':' . $dropOffAddress['addr_phone']; ?><br>
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
                                                           <a href="<?php echo UrlHelper::generateUrl('buyer', 'downloadBuyerAtatchedFile', array($attachedFile['afile_record_id'], 0, $attachedFile['afile_id'])); ?>"
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
                            <!-- [ RENTAL STATUS FORM --->
                            <?php if (isset($orderStatusFrm)) { ?>
                            <div class="section--repeated mb-3">
                                <h6><?php echo Labels::getLabel('LBL_ORDER_STATUS', $siteLangId); ?></h6>
                                <div class="info--order">
                                    <?php
                                        $orderStatusFrm->setFormTagAttribute('onsubmit', 'updateOrderStatus(this); return(false);');
                                        $orderStatusFrm->setFormTagAttribute('class', 'form');
                                        $orderStatusFrm->developerTags['colClassPrefix'] = 'col-md-';
                                        $orderStatusFrm->developerTags['fld_default_col'] = 6;
                                        if (1 >= $childOrderDetail['op_qty'] || $childOrderDetail['opd_extend_from_op_id'] > 0) {
                                            $qtyFld = $orderStatusFrm->getField('return_qty');
                                            if (!empty($qtyFld)) {
                                                $qtyFld->value = $childOrderDetail['op_qty'];
                                                $qtyFld->setFieldTagAttribute('readonly', 'readonly');
                                                $qtyFld->setFieldTagAttribute('class', 'disabled-input');
                                            }
                                        }

                                        if ($childOrderDetail['op_status_id'] == FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS')) {
                                            $orderStatusFrm->developerTags['fld_default_col'] = 6;
                                        }

                                        $fileFld = $orderStatusFrm->getField('file_field');
                                        $fileFld->developerTags['col'] = 12;
                                        $cmtFld = $orderStatusFrm->getField('comments');
                                        $cmtFld->developerTags['col'] = 12;

                                        $fld = $orderStatusFrm->getField('tracking_number');
                                        if (!empty($fld)) {
                                            $fld->developerTags['col'] = 6;
                                            $fld->setWrapperAttribute('class', 'shipfield');
                                        }
                                        $couFld = $orderStatusFrm->getField('tracking_courier');
                                        if (!empty($couFld)) {
                                            $couFld->developerTags['col'] = 6;
                                            $couFld->setWrapperAttribute('class', 'shipfield');
                                        }
                                        
                                        $trackingUrlFld = $orderStatusFrm->getField('tracking_url');
                                        if (!empty($trackingUrlFld)) {
                                            $couFld->developerTags['col'] = 6;
                                            $trackingUrlFld->setWrapperAttribute('class', 'shipfield');
                                            $trackingUrlFld->setFieldTagAttribute('pattern', 'https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)');
                                            
                                            $trackingUrlFld->setFieldTagAttribute('placeholder', 'https://example.com');
                                            $trackingUrlFld->htmlAfterField = '<small>' . Labels::getLabel("LBL_Example", $siteLangId). ' : https://example.com' . '</small>';
                                            
                                        }
                                        
                                        $opReturnResFld = $orderStatusFrm->getField('op_return_reason');
                                        if (!empty($opReturnResFld)) {
                                            $opReturnResFld->setWrapperAttribute('class', 'orderReturnReson--js');
                                        }

                                        $addressFld = $orderStatusFrm->getField('shop_address');
                                        $addressFld->developerTags['col'] = 12;
                                        if (!empty($shopAddresses) && !empty($addressFld)) {
                                            $addressFld->setWrapperAttribute('class', 'dropfld ship-fld--js');
                                            $addressHtml = '<ul class="my-addresses">';
                                            foreach ($shopAddresses as $shopAddress) {
                                                $addressHtml .= '<li><div class="my-addresses__body">
												<span class="radio">
												<input type="radio" name="address_id" value="' . $shopAddress['addr_id'] . '"> </span>
												<address class="delivery-address"><h5>' . $shopAddress['addr_name'] . '<span class="tag">' . $shopAddress['addr_title'] . '</span></h5><p>' . $shopAddress['addr_address1'] . '<br>' . $shopAddress['addr_city'] . ',' . $shopAddress['state_name'] . '<br>' . $shopAddress['country_name'] . '<br>' . Labels::getLabel("LBL_Zip", $siteLangId) . ':' . $shopAddress['addr_zip'] . '<br></p><p class="phone-txt"><i class="fas fa-mobile-alt"></i>' . Labels::getLabel("LBL_Phone", $siteLangId) . ':' . $shopAddress['addr_phone'] . '<br></p></address></div></li>';
                                            }
                                            $addressHtml .= '</ul>';
                                            $addressFld->value = $addressHtml;
                                        }
                                        $submitFld = $orderStatusFrm->getField('btn_submit');
                                        $submitFld->developerTags['col'] = 4;
                                        $submitFld->addFieldTagAttribute('class', 'btn btn-brand');
                                        $submitFld->value = Labels::getLabel("LBL_SUBMIT_REQUEST", $siteLangId);
                                        echo $orderStatusFrm->getFormHtml();
                                        ?>
                                </div>
                            </div>
                            <?php } ?>
                            <!-- RENTAL STATUS FORM END] -->
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
                                            <?php if ($isShipping > 0) { ?>
                                            <li>
                                                <span class="label">
                                                    <a class="dotted" href="javascript:void(0);" data-toggle="modal"
                                                        data-target="#shipping_modal"><?php echo Labels::getLabel('LBL_Shipping_Price', $siteLangId); ?></a>
                                                </span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></span>
                                            </li>
                                            <?php } ?>
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
                                            <?php if ($couponDiscount != 0) { ?>
                                            <li class="discounted">
                                                <span class="label"><a class="dotted" href="javascript:void(0);"
                                                        data-toggle="modal"
                                                        data-target="#discount_modal"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></a></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($couponDiscount, true, false, true, false, true); ?></span>
                                            </li>
                                            <?php } ?>
                                            <?php if ($rewardPointTotal != 0) { ?>
                                            <li class="discounted">
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($rewardPointTotal, true, false, true, false, true); ?></span>
                                            </li>
                                            <?php } ?>
                                            <?php /* <li>
                                              <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span>
                                            <span
                                                class="value"><?php echo CommonHelper::displayMoneyFormat($cartTotalAmount, true, false, true, false, true); ?></span>
                                            </li> */ ?>
                                            <li class="highlighted">
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Order_Total_Amount', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($orderNetTotal, true, false, true, false, true); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php if (isset($orderDetail['order_late_charges']) && $orderDetail['order_late_charges'] > 0) { ?>
									<div class="order-block">
                                        <div class="cart-summary pt-0">
                                            <ul class="">
                                                <li>
                                                    <span class="label"><?php echo Labels::getLabel('LBL_Pending_Charges_From_Previous_Orders', $siteLangId) ?></span>
                                                    <span class="value"><?php echo CommonHelper::displayMoneyFormat($orderDetail['order_late_charges'], true, false, true, false, true); ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
								<?php } ?>
                                <?php
                                if ($totalLateCharges > 0) {
                                    $chargeLbl = ($primaryOrder && $childOrderDetail['charge_status'] != BuyerLateChargesHistory::STATUS_PENDING) ? Labels::getLabel('Lbl_Late_Charges_Apply', $siteLangId) : Labels::getLabel('Lbl_Late_Charges_May_be_Apply', $siteLangId);
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


                                <?php $totalSaving = $couponDiscount + $volumnDiscountAmount + $durationDiscountAmount + $rewardPointTotal; ?>
                                <?php if ($totalSaving != 0) { ?>
                                <div class="total-savings">
                                    <img class="total-savings-img"
                                        src="<?php echo CONF_WEBROOT_URL; ?>images/dashboard/retina/savings.svg" alt="">
                                    <p><?php echo Labels::getLabel('LBL_Your_total_savings_amount_on_this_order', $siteLangId); ?>
                                    </p>
                                    <span
                                        class="amount"><?php echo CommonHelper::displayMoneyFormat((-$totalSaving), true, false, true, false, true); ?></span>
                                </div>
                                <?php } ?>
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
                                                    $shippingAddress .= '<p class="c-info"><strong><i class="fas fa-mobile-alt mr-2"></i>' . $orderDetail['shippingAddress']['oua_phone'] . '</strong></p>';
                                                }
                                                echo $shippingAddress
                                                ?>
                                    </div>
                                    <?php } ?>

                                    <?php if (!empty($orderDetail['payments'])) { ?>
                                    <hr class="dotted">
                                    <h5><?php echo Labels::getLabel('LBL_Payment_History', $siteLangId); ?></h5>
                                    <div class="list-specification">
                                        <ul>
                                            <?php 
                                            $mCount = 1;
                                            $totalRows = count($orderDetail['payments']);
                                            foreach ($orderDetail['payments'] as $row) { ?>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?></span>
                                                <span class="value"><?php echo $row['opayment_method']; ?></span>
                                            </li>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, false, true, false, true); ?></span>
                                            </li>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo FatDate::format($row['opayment_date']); ?></span>
                                            </li>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Transaction_number', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo $row['opayment_gateway_txn_id']; ?></span>
                                            </li>
                                            <li>
                                                <?php 
                                                /* $class = '';
                                                if (Orders::ORDER_PAYMENT_CANCELLED == $row['opayment_txn_status']) {
                                                    $class = "label-danger";
                                                } elseif (Orders::ORDER_PAYMENT_PENDING == $row['opayment_txn_status']) {
                                                    $class = "label-info";
                                                } elseif (Orders::ORDER_PAYMENT_PAID == $row['opayment_txn_status']) {
                                                    $class = "label-success";
                                                }  */ ?>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo $orderStatusArr[$row['opayment_txn_status']]; ?></span>
                                            </li>
                                            <li>
                                                <span
                                                    class="label"><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></span>
                                                <span
                                                    class="value"><?php echo nl2br($row['opayment_comments']); ?></span>
                                            </li>
                                            <?php if ($mCount < $totalRows) { ?>
                                            <hr class="dotted" />
                                            <?php }  
                                            $mCount++;
                                            
                                            } ?>
                                        </ul>
                                    </div>
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
                                                    $billingAddress .= '<p class="c-info"><strong><i class="fas fa-mobile-alt mr-2"></i>' . $orderDetail['billingAddress']['oua_phone'] . '</strong></p>';
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
                                                                        $downloadUrl = UrlHelper::generateUrl('Buyer', 'downloadAttachedFile', array($orderDetail['order_order_id'], $val[0]['ovd_vfld_id']));
                                                                        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderDetail['order_order_id'], $val[0]['ovd_vfld_id'], 0, true, 0, false);
                                                                        if (empty($file_row)) {
                                                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                                        } else {
                                                                            echo '<a href="' . $downloadUrl . '"> ' . $file_row['afile_name'] . '</a>';
                                                                        }
                                                                    }
                                                                    ?>
                                                        </span>
                                                        <?php if (false == $primaryOrder) { ?>
                                                        <span>
                                                            <?php
                                                                        $count = count($val);
                                                                        $i = 1;
                                                                        foreach ($val as $vdata) {
                                                                            if (!isset($childOrderDetail[$vdata['optvf_op_id']]['op_selprod_title'])) {
                                                                                continue;
                                                                            }
                                                                            echo $childOrderDetail[$vdata['optvf_op_id']]['op_selprod_title'];
                                                                            if ($count > $i) {
                                                                                echo "</br>";
                                                                            }
                                                                            $i++;
                                                                        }
                                                                        ?>
                                                        </span>
                                                        <?php } ?>

                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php if (!empty($shopAgreementArr)) { ?>
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
                                                            <a href="<?php echo CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', [$key, $val['agreementFileId'], AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>"
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
                                                    src="<?php echo UrlHelper::generateUrl('Image', 'signature', array($signatureData['afile_record_id'], 0, 'ORIGINAL', $signatureData['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>"
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
                <?php if($roundingOffTotal != 0) { ?>
                <div class="row text-left">
                    <div class="col-auto">
                        <span><?php echo ($roundingOffTotal > 0) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId) ;?> : </span>
                    </div>
                    <div class="col-auto">
                        <span><?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></span>
                    </div>
                </div> | 
                <?php } ?>
                <div class="row">
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
<?php if (!empty($attachedServicesArr)) { ?>
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
var RENTAL_RETURN_STATUS_ID =
    <?php echo FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END', FatUtility::VAR_INT, 0); ?>;
$(document).ready(function() {
    $('.orderReturnReson--js').hide();
    $('select[name="op_status_id"]').on('change', function() {
        if ($(this).val() == <?php echo FatApp::getConfig('CONF_RETURN_REQUEST_ORDER_STATUS'); ?>) {
            $('.orderReturnReson--js').show();
        } else {
            $('.orderReturnReson--js').hide();
        }
    });

    $('select[name="op_return_fullfillment_type"]').on('change', function() {
        if (parseInt($(this).val()) == '<?php echo OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP; ?>') {
            $('.shipfield').removeClass('ship-fld--js');
            $('.dropfld').addClass('ship-fld--js');
            /* $('.dropfld input').val(""); */
        } else {
            $('.shipfield input').val("");
            $('.shipfield').addClass('ship-fld--js');
            $('.dropfld').removeClass('ship-fld--js');
        }
    });
});

trackOrder = function(trackingNumber, courier, orderNumber) {
    $.facebox(function() {
        fcom.ajax(fcom.makeUrl('Buyer', 'orderTrackingInfo', [trackingNumber, courier, orderNumber]), '',
            function(res) {
                $.facebox(res, 'medium-fb-width');
            });
    });
};

var FULLFILLMENT_SHIP = '<?php echo OrderProduct::RENTAL_ORDER_RETURN_TYPE_SHIP; ?>';

$('#security_modal').insertAfter('.wrapper');
$('#tax_modal').insertAfter('.wrapper');
$('#shipping_modal').insertAfter('.wrapper');
$('#volume_modal').insertAfter('.wrapper');
$('#duration_discount_modal').insertAfter('.wrapper');
$('#discount_modal').insertAfter('.wrapper');
$('#addon_modal').insertAfter('.wrapper');
</script>
<style>
.ship-fld--js {
    display: none;
}

input[type="file"] {
    width: 100% !important;
}

.disabled-input {
    color: rgba(0, 0, 0, 0.38) !important;
    background-color: rgba(0, 0, 0, 0.12) !important;
    box-shadow: none;
    cursor: initial;
    border-color: transparent !important;
}
</style>