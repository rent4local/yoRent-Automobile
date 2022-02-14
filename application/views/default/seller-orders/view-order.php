<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
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

$orderProducts = array_merge(array($orderDetail), $attachedServices);
?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title">
                    <?php
                    if ($orderDetail['order_is_rfq']) {
                        echo Labels::getLabel('LBL_View_RFQ_Order', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_View_Rental_Order', $siteLangId);
                    }
                    ?>
                </h2>
                <?php
                    if (isset($parentOrderDetail) && !empty($parentOrderDetail)) {
                    echo '<h6 class="text-danger">' . Labels::getLabel('LBL_This_order_is_extended_from_', $siteLangId) . ' <a href="' . CommonHelper::generateUrl('SellerOrders', 'viewOrder', array($parentOrderDetail['op_id'])) . '">#' . $parentOrderDetail['op_invoice_number'] . '</a> </h6>';
                }

                if (isset($extendedChildData) && !empty($extendedChildData)) {
                    echo '<h6 class="text-danger">' . Labels::getLabel('LBL_This_order_is_extended_by', $siteLangId) . ' <a href="' . CommonHelper::generateUrl('SellerOrders', 'viewOrder', array($extendedChildData['opd_op_id'])) . '">#' . $extendedChildData['opd_order_id'] . '</a> </h6>';
                }
                ?>
            </div>
            <?php if (in_array($orderDetail['orderstatus_id'], $processingStatuses) && $canEdit && $orderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON && $orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                <div class="col-auto">
                    <div class="btn-group">
                        <ul class="actions">
                            <li>
                                <a href="<?php echo UrlHelper::generateUrl('seller', 'cancelOrder', array($orderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>"><i class="fas fa-times"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php /*  [ RENTAL END COUNTER NOTE  */ ?>
        <?php
        if (strtotime($orderDetail['opd_rental_start_date']) <= strtotime(date('Y-m-d 00:00:00')) && strtotime($orderDetail['opd_rental_end_date']) >= strtotime(date('Y-m-d 00:00:00')) && in_array($orderDetail['op_status_id'], OrderStatus::statusArrForRentalExpireNote())) {
            $dateToRentalEnd = CommonHelper::getDifferenceBetweenDates(date('Y-m-d 00:00:00'), $orderDetail['opd_rental_end_date'], 0, ProductRental::DURATION_TYPE_DAY);
            if ($dateToRentalEnd >= 3) {
                $rentalEndMsgClass = 'alert-success';
            } elseif ($dateToRentalEnd >= 2) {
                $rentalEndMsgClass = 'alert-warning';
            } else {
                $rentalEndMsgClass = 'alert-danger';
            }
            ?>
            <div class="alert text-center  alert-dismissible fade show <?php echo $rentalEndMsgClass; ?>" role="alert">
                <h5><?php echo sprintf(Labels::getLabel('LBL_%s_Day(s)_Remaining_to_end_Rental', $siteLangId), $dateToRentalEnd); ?>
                </h5>
            </div>
        <?php } elseif (strtotime($orderDetail['opd_rental_end_date']) < strtotime(date('Y-m-d 00:00:00')) && in_array($orderDetail['op_status_id'], OrderStatus::statusArrForLateChargeNote())) { ?>
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <?php echo Labels::getLabel('LBL_Rental_Duration_Ended._Late_Charges_may_be_Apply', $siteLangId); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>
        <?php /* ]  */ ?>


        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?></h5>
                    <div class="">
                        <?php
                        $backUrl = UrlHelper::generateUrl('sellerOrders', 'rentals');
                        if ($orderDetail['order_is_rfq']) {
                            $backUrl = UrlHelper::generateUrl('requestForQuotes', 'rfqOrder');
                        }
                        ?>
                        <a href="<?php echo $backUrl; ?>" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId); ?>">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <a target="_blank" href="<?php echo UrlHelper::generateUrl('Seller', 'viewInvoice', [$orderDetail['op_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="
                           <?php echo Labels::getLabel('LBL_Print', $siteLangId); ?>">
                            <i class="fas fa-print"></i>
                        </a>
                        <?php
                        if ($shippedBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $orderDetail['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $orderDetail['order_payment_status'])) {
                            $opId = $orderDetail['op_id'];
                            if (empty($orderDetail['opship_response']) && empty($orderDetail['opship_tracking_number'])) {
                                $orderId = $orderDetail['order_id'];
                                ?>
                                <a href="javascript:void(0)"
                                   onclick='generateLabel("<?php echo $orderId; ?>", <?php echo $opId; ?>)'
                                   class="btn btn-outline-brand  btn-sm no-print"
                                   title="<?php echo Labels::getLabel('LBL_GENERATE_LABEL', $siteLangId); ?>"><i
                                        class="fas fa-file-download"></i></a>
                                <?php } elseif (!empty($orderDetail['opship_response'])) { ?>
                                <a target="_blank"
                                   href="<?php echo UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$orderDetail['op_id']]); ?>"
                                   class="btn btn-outline-brand  btn-sm no-print"
                                   title="<?php echo Labels::getLabel('LBL_PREVIEW_LABEL', $siteLangId); ?>"><i
                                        class="fas fa-file-export"></i></a>
                                    <?php
                                }

                                if (!empty($orderStatus) && 'awaiting_shipment' == $orderStatus && !empty($orderDetail['opship_response'])) {
                                    ?>
                                <a href="javascript:void(0)" onclick="proceedToShipment(<?php echo $opId; ?>)"
                                   class="btn btn-outline-brand  btn-sm no-print"
                                   title="<?php echo Labels::getLabel('LBL_PROCEED_TO_SHIPMENT', $siteLangId); ?>"><i
                                        class="fas fa-shipping-fast"></i></a>
                                    <?php
                                }
                        }
                        if ($thread_id > 0) { ?>
                            <a class="btn btn-outline-brand  btn-sm no-print"
                               href="<?php echo UrlHelper::generateUrl('Account', 'viewMessages', array($thread_id, $message_id)); ?>"><?php echo Labels::getLabel('LBL_View_Order_Message', $siteLangId); ?></a>
                        <?php } else { ?>
                            <a href="javascript:void(0)"
                               onclick="sendOrderMessage(<?php echo $orderDetail['op_id']; ?>, 'seller')"
                               class="btn btn-outline-brand  btn-sm no-print"
                               title="<?php echo Labels::getLabel('LBL_Send_message_to_buyer', $siteLangId); ?>">
                                <i class="fas fa-envelope"></i>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="bg-gray p-3 rounded">
                                <div class="info--order">
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId); ?>:
                                        </strong><?php echo $orderDetail['user_name']; ?>
                                    </p>
                                    <?php
                                    $selected_method = '';
                                    if ($orderDetail['order_pmethod_id'] > 0) {
                                        $selected_method .= CommonHelper::displayNotApplicable($siteLangId, $orderDetail["plugin_name"]);
                                    }
                                    if ($orderDetail['order_is_wallet_selected'] > 0) {
                                        $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Wallet", $siteLangId) : Labels::getLabel("LBL_Wallet", $siteLangId);
                                    }
                                    if ($orderDetail['order_reward_point_used'] > 0) {
                                        $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Rewards", $siteLangId) : Labels::getLabel("LBL_Rewards", $siteLangId);
                                    }

                                    if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore'])) {
                                        $selected_method = (empty($orderDetail['plugin_name'])) ? $orderDetail['plugin_identifier'] : $orderDetail['plugin_name'];
                                    }
                                    ?>
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>:
                                        </strong><?php echo $selected_method; ?>
                                    </p>
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?>: </strong>
                                        <?php
                                        echo Orders::getOrderPaymentStatusArr($siteLangId)[$orderDetail['order_payment_status']];
                                        if ('' != $orderDetail['plugin_name'] && 'CashOnDelivery' == $orderDetail['plugin_code']) {
                                            echo ' (' . $orderDetail['plugin_name'] . ' )';
                                        }
                                        ?>
                                    </p>
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId); ?>: </strong>
                                        <?php echo CommonHelper::displayMoneyFormat((CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL') + $serviceTotalPriceArr['cart_total']), true, false, true, false, true); ?>
                                    </p>
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Rental_Security_Total', $siteLangId); ?>: </strong>
                                        <?php echo CommonHelper::displayMoneyFormat(($orderDetail['opd_rental_security'] * $orderDetail['op_qty']), true, false, true, false, true); ?>
                                    </p>

                                    <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                        <p><strong><?php echo Labels::getLabel('LBL_Delivery', $siteLangId); ?>:
                                            </strong><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?>
                                        </p>
                                    <?php } ?>

                                    <?php if ($orderDetail['op_tax_collected_by_seller']) { 
                                        if (empty($orderDetail['taxOptions'])) { ?>
                                            <p>
                                                <strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?>:</strong>
                                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true); ?>
                                            </p>
                                            <?php
                                        } else {
                                            foreach ($orderDetail['taxOptions'] as $key => $val) {
                                                ?>
                                                <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?>:</strong>
                                                    <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?>
                                                </p>
                                                <?php
                                            }
                                        }
                                    } 
                                    if ($serviceTotalPriceArr['tax_total'] > 0) { ?>
                                        <p>
                                            <strong><?php echo Labels::getLabel('LBL_Addons_Tax', $siteLangId); ?>:</strong>
                                            <?php echo CommonHelper::displayMoneyFormat($serviceTotalPriceArr['tax_total'], true, false, true, false, true); ?>
                                        </p>
                                    <?php } 
                                    
                                    $durationDiscount = CommonHelper::orderProductAmount($orderDetail, 'DURATION_DISCOUNT');
                                    if (!empty($durationDiscount)) { ?>
                                        <p>
                                            <strong><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?>:</strong>
                                            <?php echo CommonHelper::displayMoneyFormat($durationDiscount, true, false, true, false, true); ?>
                                        </p>
                                    <?php } ?>
                                    <?php if (array_key_exists('order_rounding_off', $orderDetail) && 0 != $orderDetail['order_rounding_off']) { ?>
                                        <p>
                                            <strong>
                                                <?php echo (0 < $orderDetail['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?>:
                                            </strong>
                                            <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_rounding_off'], true, false, true, false, true); ?>
                                        </p>
                                    <?php } ?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Order_Total', $siteLangId); ?>:
                                        </strong><?php echo CommonHelper::displayMoneyFormat((CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER) + $serviceTotalPriceArr['net_total']), true, false, true, false, true); ?>
                                    </p>
                                    <?php if (($orderDetail['charge_total_amount'] + $serviceTotalPriceArr['late_charges_total']) > 0) { ?>
                                        <p><strong><?php echo Labels::getLabel('Lbl_Late_Charges', $siteLangId); ?>
                                            </strong>
                                            <?php echo CommonHelper::displayMoneyFormat(($orderDetail['charge_total_amount'] + $serviceTotalPriceArr['late_charges_total'])); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="bg-gray p-3 rounded">
                                <div class="info--order">
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId); ?> #:
                                        </strong><?php echo $orderDetail['op_invoice_number']; ?>
                                    </p>
                                    <p>
                                        <strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>:
                                        </strong><?php echo FatDate::format($orderDetail['order_date_added']); ?>
                                    </p>
                                    <?php /* if (0 && $orderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP && $orderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                                        <p>
                                            <strong><?php echo Labels::getLabel('LBL_Pickup_Date', $siteLangId); ?>:</strong>
                                            <?php
                                            $fromTime = isset($orderDetail["opshipping_time_slot_from"]) ? date('H:i', strtotime($orderDetail["opshipping_time_slot_from"])) : '';
                                            $toTime = isset($orderDetail["opshipping_time_slot_to"]) ? date('H:i', strtotime($orderDetail["opshipping_time_slot_to"])) : '';
                                            $shippingDate = isset($orderDetail["opshipping_date"]) ? FatDate::format($orderDetail["opshipping_date"]) : '';
                                            echo $shippingDate . ' ' . $fromTime . ' - ' . $toTime;
                                            ?>
                                        </p>
                                    <?php } */ ?>
                                    <span class="gap"></span>
                                    <?php if (!empty($attachment)) { ?>
                                        <!-- @todo -->
                                        <p><?php echo Labels::getLabel('LBL_The_buyer_has_accepted_the_rental_terms_&_conditions_for_this_order.', $siteLangId) ?>
                                        </p>

                                        <img src="<?php echo UrlHelper::generateUrl('Image', 'signature', array($attachment['afile_record_id'], 0, 'ORIGINAL', $attachment['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>" title="<?php echo $attachment['afile_name']; ?>" alt="<?php echo $attachment['afile_name']; ?>">
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="scroll scroll-x js-scrollable table-wrap">
                        <table class="table">
                            <thead>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                    <th class="no-print"></th>
                                    <?php if (!empty($orderDetail['pickupAddress'])) { ?>
                                        <th> <?php echo Labels::getLabel('LBL_PICKUP_DETAIL', $siteLangId); ?></th>
                                    <?php } ?>
                                    <th><?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Unit_Price', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></th>
                                    <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId); ?></th>
                                    <?php } ?>
                                    <?php if ($durationDiscount) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?></th>
                                    <?php } ?>
                                    <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId); ?></th>
                                    <?php } ?>
                                    <th><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderProducts as $orderProduct) { ?>
                                    <tr>
                                        <td>
                                            <div class="pic--cell-left">
                                                <?php
                                                $prodOrBatchUrl = 'javascript:void(0)';
                                                if ($orderProduct['op_is_batch']) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($orderProduct['op_selprod_id']));
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($orderProduct['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                } else {
                                                    if (Product::verifyProductIsValid($orderProduct['op_selprod_id']) == true) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($orderProduct['op_selprod_id']));
                                                    }
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($orderProduct['selprod_product_id'], "SMALL", $orderProduct['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
   
                                                    if ($orderProduct['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                                        $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($orderProduct['op_selprod_id'], "THUMB", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                    }
                                                }
                                                ?>
                                                <figure class="item__pic">
                                                    <a href="<?php echo $prodOrBatchUrl; ?>">
                                                        <img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $orderProduct['op_product_name']; ?>" alt="<?php echo $orderProduct['op_product_name']; ?>">
                                                    </a>
                                                </figure>
                                            </div>
                                        </td>
                                        
                                        <td>
                                            <div class="item__description">
                                                <?php if ($orderProduct['op_selprod_title'] != '') { ?>
                                                    <div class="item__title">
                                                        <a title="<?php echo $orderProduct['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $orderProduct['op_selprod_title']; ?></a>
                                                    </div>
                                                    <div class="item__category"><?php echo $orderProduct['op_product_name']; ?></div>
                                                <?php } else { ?>
                                                    <div class="item__brand">
                                                        <a title="<?php echo $orderProduct['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $orderProduct['op_product_name']; ?>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                                <div class="item__brand">
                                                    <?php echo Labels::getLabel('Lbl_Brand', $siteLangId) ?>:
                                                    <?php echo CommonHelper::displayNotApplicable($siteLangId, $orderProduct['op_brand_name']); ?>
                                                </div>
                                                <?php if ($orderProduct['op_selprod_options'] != '') { ?>
                                                    <div class="item__specification"><?php echo $orderProduct['op_selprod_options']; ?></div>
                                                <?php } ?>
                                                <?php if ($orderProduct['op_shipping_duration_name'] != '') { ?>
                                                    <div class="item__shipping">
                                                        <?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?>:
                                                        <?php echo $orderProduct['op_shipping_durations'] . '-' . $orderProduct['op_shipping_duration_name']; ?>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <?php /* RENTAL DETAILS UPDATE [ */ ?>
                                            <div class="item__category">
                                                <?php
                                                $duration = CommonHelper::getDifferenceBetweenDates($orderProduct['opd_rental_start_date'], $orderProduct['opd_rental_end_date'], $orderProduct['op_selprod_user_id'], $orderProduct['opd_rental_type']);
                                                echo Labels::getLabel('LBL_Duration', $siteLangId) . ': ';
                                                echo CommonHelper::displayProductRentalDuration($duration, $orderProduct['opd_rental_type'], $siteLangId);
                                                ?><br />

                                                <small>
                                                    <i class="icn"> <svg class="svg">
                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME;?>/retina/sprite-front.svg#calendar"></use>
                                                        </svg></i><?php echo Labels::getLabel('LBL_From', $siteLangId) . ': ' . date('M d, Y ', strtotime($orderProduct['opd_rental_start_date'])); ?>
                                                </small><br />
                                                <small>
                                                    <i class="icn">
                                                        <svg class="svg">
                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME;?>/retina/sprite-front.svg#calendar"></use>
                                                        </svg>
                                                    </i><?php echo Labels::getLabel('LBL_To', $siteLangId) . ': ' . date('M d, Y ', strtotime($orderProduct['opd_rental_end_date'])); ?>
                                                </small><br />
                                                <?php if ($orderProduct['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                                                    <br />
                                                    <small>(<?php echo Labels::getLabel('M_Rental_Security_Refundable_after_item_returned', $siteLangId); ?>)</small>
                                                <?php } ?>
                                            </div>
                                            <?php /* ] */ ?>

                                        </td>
                                        <?php if (Shipping::FULFILMENT_PICKUP == $orderProduct['opshipping_fulfillment_type']) { ?>
                                            <td>
                                                <p>
                                                    <?php if (0 && $orderProduct['order_is_rfq'] == applicationConstants::NO) { ?>
                                                        <strong>
                                                            <?php
                                                            $opshippingDate = isset($orderProduct['opshipping_date']) ? $orderProduct['opshipping_date'] . ' ' : '';
                                                            $timeSlotFrom = isset($orderProduct['opshipping_time_slot_from']) ? ' (' . date('H:i', strtotime($orderProduct['opshipping_time_slot_from'])) . ' - ' : '';
                                                            $timeSlotTo = isset($orderProduct['opshipping_time_slot_to']) ? date('H:i', strtotime($orderProduct['opshipping_time_slot_to'])) . ')' : '';
                                                            echo $opshippingDate . $timeSlotFrom . $timeSlotTo;
                                                            ?>
                                                        </strong><br>
                                                    <?php } ?>
                                                    <?php echo $orderProduct['addr_name']; ?>,
                                                    <?php
                                                    $address1 = !empty($orderProduct['addr_address1']) ? $orderProduct['addr_address1'] : '';
                                                    $address2 = !empty($orderProduct['addr_address2']) ? ', ' . $orderProduct['addr_address2'] : '';
                                                    $city = !empty($orderProduct['addr_city']) ? '<br>' . $orderProduct['addr_city'] : '';
                                                    $state = !empty($orderProduct['state_name']) ? ', ' . $orderProduct['state_name'] : '';
                                                    $country = !empty($orderProduct['country_name']) ? ' ' . $orderProduct['country_name'] : '';
                                                    $zip = !empty($orderProduct['addr_zip']) ? '(' . $orderProduct['addr_zip'] . ')' : '';

                                                    echo $address1 . $address2 . $city . $state . $country . $zip;
                                                    ?>
                                                </p>
                                            </td>
                                        <?php } ?>
                                        <td><?php echo $orderProduct['op_qty']; ?></td>
                                        <td><?php echo CommonHelper::displayMoneyFormat($orderProduct['op_unit_price'], true, false, true, false, true); ?></td>
                                        <td>
                                            <?php
                                            if ($orderProduct['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                                                echo CommonHelper::displayMoneyFormat($orderProduct['opd_rental_security']);
                                            } else {
                                                echo CommonHelper::displayMoneyFormat(0);
                                            }
                                            ?>
                                        </td>

                                        <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                            <td>
                                                <?php
                                                $productShipping = ($orderProduct['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) ? 0 : $shippingCharges;
                                                echo CommonHelper::displayMoneyFormat($productShipping, true, false, true, false, true);
                                                ?>
                                            </td>
                                        <?php } ?>
                                       
                                        <?php if ($durationDiscount) { ?>
                                            <td><?php
                                                $productDurDiscount = ($orderProduct['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) ? 0 : $durationDiscount;
                                                echo CommonHelper::displayMoneyFormat($productDurDiscount, true, false, true, false, true);
                                                ?>
                                            </td>
                                        <?php } ?>

                                        <?php if ($orderProduct['op_tax_collected_by_seller']) { ?>
                                            <td>
                                                <?php
                                                if (empty($orderProduct['taxOptions'])) {
                                                    echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'TAX'), true, false, true, false, true);
                                                } else {
                                                    foreach ($orderProduct['taxOptions'] as $key => $val) {
                                                        ?>
                                                        <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?>:</strong>
                                                            <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?>
                                                        </p>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </td>
                                        <?php } ?>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'netamount', false, User::USER_TYPE_SELLER), true, false, true, false, true); ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>

                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="bg-gray p-3 rounded">
                                <h5><?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId); ?></h5>
                                <?php
                                $billingAddress = $orderDetail['billingAddress']['oua_name'] . '<br>';
                                if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                    $billingAddress .= $orderDetail['billingAddress']['oua_address1'] . '<br>';
                                }

                                if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                    $billingAddress .= $orderDetail['billingAddress']['oua_address2'] . '<br>';
                                }

                                if ($orderDetail['billingAddress']['oua_city'] != '') {
                                    $billingAddress .= $orderDetail['billingAddress']['oua_city'] . ', ';
                                }

                                if ($orderDetail['billingAddress']['oua_state'] != '') {
                                    $billingAddress .= $orderDetail['billingAddress']['oua_state'] . ', ';
                                }

                                if ($orderDetail['billingAddress']['oua_country'] != '') {
                                    $billingAddress .= $orderDetail['billingAddress']['oua_country'];
                                }

                                if ($orderDetail['billingAddress']['oua_zip'] != '') {
                                    $billingAddress .= '-' . $orderDetail['billingAddress']['oua_zip'];
                                }

                                if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                    $billingAddress .= '<br>' . $orderDetail['billingAddress']['oua_phone'];
                                }
                                ?>
                                <div class="info--order">
                                    <p><?php echo $billingAddress; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($orderDetail['shippingAddress'])) { ?>
                            <div class="col-lg-6 col-md-6 mb-4">
                                <div class="bg-gray p-3 rounded">
                                    <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?></h5>
                                    <?php
                                    $shippingAddress = $orderDetail['shippingAddress']['oua_name'] . '<br>';
                                    if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_address1'] . '<br>';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_address2'] . '<br>';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ', ';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_state'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_state'] . ', ';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_country'];
                                    }

                                    if ($orderDetail['shippingAddress']['oua_zip'] != '') {
                                        $shippingAddress .= '-' . $orderDetail['shippingAddress']['oua_zip'];
                                    }

                                    if ($orderDetail['shippingAddress']['oua_phone'] != '') {
                                        $shippingAddress .= '<br>' . $orderDetail['shippingAddress']['oua_phone'];
                                    }
                                    ?>
                                    <div class="info--order">
                                        <p><?php echo $shippingAddress; ?></p>
                                    </div>
                                </div>

                            </div>
                        <?php } ?>
                    </div>

                    <?php if (!empty($verificationFldsData)) { ?>
                        <div class="col-lg-12 mb-4">
                            <div class="bg-gray p-3 rounded">
                                <h6>
                                    <?php echo Labels::getLabel('LBL_Verification_Data', $siteLangId); ?>
                                </h6>
                                <div class="info--order">
                                    <?php
                                    $verificationData = [];
                                    foreach ($verificationFldsData as $val) {
                                        $verificationData[$val['ovd_vfld_id']][] = $val;
                                    }
                                    ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Field_Name', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Field_Value', $siteLangId); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($verificationData as $key => $val) { ?>
                                                <tr>
                                                    <td><?php echo $val[0]['ovd_vflds_name']; ?></td>
                                                    <td><?php
                                                        if ($val[0]['ovd_vflds_type'] == VerificationFields::FLD_TYPE_TEXTBOX) {
                                                            echo $val[0]['ovd_value'];
                                                        } else {
                                                            $downloadUrl = UrlHelper::generateUrl('SellerOrders', 'downloadAttachedFile', array($orderDetail['order_order_id'], $val[0]['ovd_vfld_id']));
                                                            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderDetail['order_order_id'], $val[0]['ovd_vfld_id']);
                                                            echo '<a href="' . $downloadUrl . '"> ' . $file_row['afile_name'] . '</a>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    <?php } ?>

                    <?php
                    if ($canEdit && $displayForm && !$print) {
                        if (!empty($extendChildOrder)) {
                            echo '<div class="section--repeated no-print text-danger extend-order-section">' . sprintf(Labels::getLabel('LBL_This_order_is_extended_.To_check_or_change_status_of_this_order_click_here_%s', $siteLangId), '<a href="' . CommonHelper::generateUrl('SellerOrders', 'viewOrder', array($extendChildOrder['opd_op_id'])) . '">#' . $extendChildOrder['opd_order_id'] . '</a>') . '</div>';
                        } else {
                            ?>
                            <div class="section--repeated no-print">
                                <h5><?php echo Labels::getLabel('LBL_Comments_on_order', $siteLangId); ?></h5>
                                <?php
                                $securityLabelHtml = '<small>' . sprintf(Labels::getLabel("LBL_Refunable_Security_amount_is_%s", $siteLangId), CommonHelper::displayMoneyFormat($orderDetail['totalSecurityAmount'])) . '</small>';
                                $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                                $frm->setFormTagAttribute('class', 'form markAsShipped-js');
                                $frm->developerTags['colClassPrefix'] = 'col-md-';
                                $frm->developerTags['fld_default_col'] = 12;

                                $manualFld = $frm->getField('manual_shipping');
                                $fld = $frm->getField('op_status_id');
                                if (null != $fld) {
                                    $fld->developerTags['col'] = (null != $manualFld) ? 4 : 6;
                                }

                                $statusFld = $frm->getField('op_status_id');
                                $statusFld->setFieldTagAttribute('class', 'status-js fieldsVisibility-js');

                                $fld1 = $frm->getField('customer_notified');
                                $fld1->setFieldTagAttribute('class', 'notifyCustomer-js');
                                $fld1->developerTags['col'] = (null != $manualFld) ? 4 : 6;

                                $fld = $frm->getField('tracking_number');
                                if (null != $fld) {
                                    $fld->developerTags['col'] = 6;
                                }

                                if (null != $manualFld) {
                                    $manualFld->setFieldTagAttribute('class', 'manualShipping-js fieldsVisibility-js');
                                    $manualFld->developerTags['col'] = 4;

                                    $fld = $frm->getField('opship_tracking_url');
                                    if (null != $fld) {
                                        $fld->developerTags['col'] = 6;
                                    }

                                    $fld = $frm->getField('oshistory_courier');
                                    if (null != $fld) {
                                        $fld->developerTags['col'] = 6;
                                    }
                                }

                                if ($orderDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                    $fldSecurityType = $frm->getField('refund_security_type');
                                    $fldSecurityType->setFieldTagAttribute('completeamount', $orderDetail['totalSecurityAmount']);
                                    $fldSecurityType->developerTags['col'] = 12;

                                    $fldsAmount = $frm->getField('refund_security_amount');
                                    $fldsAmount->htmlAfterField = $securityLabelHtml;
                                    $fldsAmount->developerTags['col'] = 6; // 

                                    $lateChrgFld = $frm->getField('apply_late_charges');
                                    $lateChrgFld->developerTags['col'] = 12;

                                    $returnDateFld = $frm->getField('opd_mark_rental_return_date');
                                    $returnDateFld->developerTags['col'] = 6;
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

                                    $fileFld = $frm->getField('file[]');
                                    $returnQty = $frm->getField('return_qty');
                                    if ($rentalReturnStatus != $orderDetail['op_status_id']) {
                                        $fldSecurityType->setWrapperAttribute('class', 'div_refund_security');
                                        $fldsAmount->setWrapperAttribute('class', 'div_refund_security');
                                        $fileFld->setWrapperAttribute('class', 'div_refund_security');
                                        $lateChrgFld->setWrapperAttribute('class', 'div_refund_security');
                                        $returnDateFld->setWrapperAttribute('class', 'div_refund_security');
                                        $returnQty->setWrapperAttribute('class', 'div_refund_security');
                                    }
                                }

                                $fldBtn = $frm->getField('btn_submit');
                                $fldBtn->setFieldTagAttribute('class', 'btn btn-brand');
                                $fldBtn->developerTags['col'] = 6;
                                echo $frm->getFormHtml();
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    <?php if (!empty($orderDetail['comments']) && !$print) { ?>
                        <div class="section--repeated no-print js-scrollable table-wrap">
                            <h5><?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId); ?></h5>
                            <table class="table table--orders">
                                <thead>
                                    <tr class="">
                                        <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Attached_Files', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($orderDetail['comments'] as $row) {
                                        $attachedFiles = (isset($statusAttachedFiles) && isset($statusAttachedFiles[$row['oshistory_id']])) ? $statusAttachedFiles[$row['oshistory_id']] : [];
                                        ?>
                                        <tr>
                                            <td><?php echo FatDate::format($row['oshistory_date_added'], true); ?></td>
                                            <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                            <td style="width:30%;">
                                                <?php if (!empty($attachedFiles)) { ?>
                                                    <div class="download-files">
                                                        <?php foreach ($attachedFiles as $attachedFile) { ?>
                                                            <a class="btn-download"
                                                               href="<?php echo UrlHelper::generateUrl('seller', 'downloadBuyerAtatchedFile', array($attachedFile['afile_record_id'], 0, $attachedFile['afile_id'])); ?>"
                                                               title="<?php echo $attachedFile['afile_name']; ?>">
                                                                <span class="icn"><i class="fa fa-download"></i></span>
                                                                <span
                                                                    class="icn-txt"><?php echo $attachedFile['afile_name']; ?></span></a>
                                                            <?php } ?>
                                                    </div>
                                                    <?php
                                                } else {
                                                    echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                echo ($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($siteLangId, '');

                                                if ($row['oshistory_orderstatus_id'] == FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS') && $row['oshistory_status_updated_by'] == $orderDetail['order_user_id']) {
                                                    echo ' - ' . Labels::getLabel('LBL_Marked_by_Buyer', $siteLangId);
                                                }

                                                if ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_SHIPPED || $row['oshistory_orderstatus_id'] == OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                    if (empty($row['oshistory_courier'])) {
                                                        $str = !empty($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) . ' ' . $row['oshistory_tracking_number'] : '';
                                                        if (empty($orderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                            $str .= " VIA <em>" . CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]) . "</em>";
                                                        } elseif (!empty($orderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                            $str .= " <a class='btn btn-outline-secondary btn-sm' href='" . $orderDetail['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                        }
                                                        echo $str;
                                                    } else {
                                                        echo ($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) : '';
                                                        $trackingNumber = $row['oshistory_tracking_number'];
                                                        $carrier = $row['oshistory_courier'];
                                                        ?>
                                                        <a href="javascript:void(0)"
                                                           title="<?php echo Labels::getLabel('LBL_TRACK', $siteLangId); ?>"
                                                           onClick="trackOrder('<?php echo trim($trackingNumber); ?>', '<?php echo trim($carrier); ?>', '<?php echo $orderDetail['op_invoice_number']; ?>')">
                                                               <?php echo $trackingNumber; ?>
                                                        </a>
                                                        <?php
                                                        if ($row['oshistory_orderstatus_id'] != OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                            echo Labels::getLabel('LBL_VIA', $siteLangId);
                                                            ?>
                                                            <em><?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]); ?></em>
                                                            <?php
                                                        }
                                                    }
                                                }

                                                if (isset($statusAddressData[$row['oshistory_id']])) {
                                                    $dropOffAddress = $statusAddressData[$row['oshistory_id']];
                                                    echo '<br /><br /><p><strong>' . Labels::getLabel('LBL_DROPOFF_ADDRESS', $siteLangId) . '</strong></p><address class="delivery-address"><h5>' . $dropOffAddress['addr_name'] . ' <span>' . $dropOffAddress['addr_title'] . '</span></h5><p>' . $dropOffAddress['addr_address1'] . '<br>' . $dropOffAddress['addr_city'] . ',' . $dropOffAddress['state_name'] . '<br>' . $dropOffAddress['country_name'] . '<br>' . Labels::getLabel("LBL_Zip", $siteLangId) . ':' . $dropOffAddress['addr_zip'] . '<br></p><p class="phone-txt"><i class="fas fa-mobile-alt"></i>' . Labels::getLabel("LBL_Phone", $siteLangId) . ':' . $dropOffAddress['addr_phone'] . '<br></p></address>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo!empty($row['oshistory_comments']) ? html_entity_decode(nl2br($row['oshistory_comments'])) : Labels::getLabel('LBL_N/A', $siteLangId); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>
</main>
<?php if ($print) { ?>
    <script>
        $(".sidebar-is-expanded").addClass('sidebar-is-reduced').removeClass('sidebar-is-expanded');
        /*window.print();
         window.onafterprint = function() {
         location.href = history.back();
         }*/
    </script>
<?php } ?>

<script>
    $(document).ready(function () {
        $('#viewSignatureModal').insertAfter('.wrapper');

        setTimeout(function () {
            $('.printBtn-js').fadeIn();
        }, 500);
        $(document).on('click', '.printBtn-js', function () {
            $('.printFrame-js').show();
            setTimeout(function () {
                frames['frame'].print();
                $('.printFrame-js').hide();
            }, 500);
        });
    });
    var canShipByPlugin = <?php echo (true === $canShipByPlugin ? 1 : 0); ?>;
    var orderShippedStatus = <?php echo OrderStatus::ORDER_SHIPPED; ?>;
</script>

<style>
.disabled-input{
    color: rgba(0,0,0,0.38) !important;
    background-color: rgba(0,0,0,0.12) !important;
    box-shadow: none;
    cursor: initial;
    border-color: transparent !important;
}    
</style>