<?php
defined('SYSTEM_INIT') or die('Invalid Usage . ');
$canCancelOrder = true;
$canReturnRefund = true;
$canReviewOrders = false;
$canSubmitFeedback = false;
if (true == $primaryOrder) {
    $canCancelOrder = (in_array($childOrderDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses()));
    $canReturnRefund = (in_array($childOrderDetail["op_status_id"], (array) Orders::getBuyerAllowedOrderReturnStatuses()));
    if (in_array($childOrderDetail["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
        $canReviewOrders = true;
    }
    $canSubmitFeedback = Orders::canSubmitFeedback($childOrderDetail['order_user_id'], $childOrderDetail['order_id'], $childOrderDetail['op_selprod_id']);
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
} //isContainRentalProducts



$orderStatusArr = Orders::getOrderPaymentStatusArr($siteLangId);
if (!$print) {
    if($orderDetail['order_is_rfq']){
        $navData = array(
            'controller' => 'requestforquotes',
            'action' => 'vieworder'
        );
        $this->includeTemplate('_partial/dashboardNavigation.php' , $navData);
    }else {
        $navData = ($isContainRentalProducts) ? ['activeAction' => 'rentalOrders'] : [];
        $this->includeTemplate('_partial/dashboardNavigation.php', $navData);
    }
}
?>
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <?php if (!$print) { ?>
            <div class="content-header row">
                <div class="col">
                    <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                    <h2 class="content-header-title no-print">
                        <?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?>
                    </h2>
                </div>
                <?php if (true == $primaryOrder) { ?>
                    <div class="col-auto">
                        <div class="btn-group">
                            <?php if (!$print) { ?>
                                <ul class="actions no-print">
                                    <?php if ($canCancelOrder) { ?>
                                        <li>
                                            <a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderCancellationRequest', array($childOrderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && $canReviewOrders && $canSubmitFeedback) {
                                    ?>
                                        <li>
                                            <a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderFeedback', array($childOrderDetail['op_id'])); ?>" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Feedback', $siteLangId); ?>">
                                                <i class="fa fa-star"></i>
                                            </a>
                                        </li>
                                    <?php
                                    }
                                    if ($canReturnRefund) {
                                    ?>
                                        <li>
                                            <a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($childOrderDetail['op_id'])); ?>" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Refund', $siteLangId); ?>">
                                                <i class="fas fa-dollar-sign"></i>
                                            </a>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <?php if ($thread_id > 0) { ?>
                                            <a class="btn btn-outline-brand  btn-sm no-print" href="<?php echo UrlHelper::generateUrl('Account', 'viewMessages', array($thread_id, $message_id)); ?>"><i class="fa fa-envelope"></i></a>
                                        <?php } else { ?>
                                            <a href="javascript:void(0)" onclick="sendOrderMessage(<?php echo $childOrderDetail['op_id']; ?>,'buyer')" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Send_Messages', $siteLangId); ?>">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        <?php } ?>

                                    </li>
                                </ul>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?>
                    </h5>
                    <?php if (!$print) { ?>
                        <div>
                            <div class="">
                                <iframe src="<?php echo Fatutility::generateUrl('buyer', 'viewOrder', $urlParts) . '/print'; ?>" name="frame" class="printFrame-js" style="display:none" width="1" height="1"></iframe>
                                <?php 
                                
                                if($orderDetail['order_is_rfq']){ 
                                    $backUrl = UrlHelper::generateUrl('requestForQuotes', 'orders');
                                }else {
                                    $backAction = ($isContainRentalProducts) ? 'rentalOrders' : "orders"; 
                                    $backUrl = UrlHelper::generateUrl('Buyer', $backAction);
                                }
                                ?>
                                <a href="<?php echo $backUrl; ?>" class="btn btn-outline-brand btn-sm no-print" title="
                                   <?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId); ?>">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                                <a target="_blank" href="<?php echo (0 < $opId) ? UrlHelper::generateUrl('Buyer', 'viewInvoice', [$orderDetail['order_id'], $opId]) : UrlHelper::generateUrl('Buyer', 'viewInvoice', [$orderDetail['order_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="
                                   <?php echo Labels::getLabel('LBL_Print', $siteLangId); ?>">
                                    <i class="fas fa-print"></i>
                                </a>
                                <?php if (0 < $opId && !$orderDetail['order_deleted'] && !$orderDetail["order_payment_status"] && 'TransferBank' == $orderDetail['plugin_code']) { ?>
                                    <a href="<?php echo UrlHelper::generateUrl('Buyer', 'viewOrder', [$orderDetail['order_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="<?php echo Labels::getLabel('LBL_ADD_PAYMENT_DETAIL', $siteLangId); ?>">
                                        <i class="fas fa-box-open"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php }
                    ?>
                </div>
                <div class="card-body ">
                    <?php if ($primaryOrder) { ?>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 mb-4">
                                <div class="bg-gray p-3 rounded">
                                    <div class="info--order">
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId); ?>:
                                            </strong>
                                            <?php echo $childOrderDetail['user_name']; ?>
                                        </p>
                                        <?php
                                        $paymentMethodName = empty($childOrderDetail['plugin_name']) ? $childOrderDetail['plugin_identifier'] : $childOrderDetail['plugin_name'];
                                        // CommonHelper::printArray($childOrderDetail, true);
                                        if (!empty($paymentMethodName) && $childOrderDetail['order_pmethod_id'] > 0 && $childOrderDetail['order_is_wallet_selected'] > 0) {
                                            $paymentMethodName .= ' + ';
                                        }
                                        if ($childOrderDetail['order_is_wallet_selected'] > 0) {
                                            $paymentMethodName .= Labels::getLabel("LBL_Wallet", $siteLangId);
                                        }

                                        /* if (strtolower($childOrderDetail['plugin_code']) == 'cashondelivery' && $childOrderDetail['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
                                      $paymentMethodName = Labels::getLabel('LBL_PAY_ON_PICKUP', $siteLangId);
                                      } */
                                        ?>
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>:
                                            </strong>
                                            <?php echo $paymentMethodName; ?>
                                        </p>
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Payment_Status', $siteLangId); ?>:
                                            </strong>
                                            <?php echo $orderStatusArr[$childOrderDetail['order_payment_status']]; ?>
                                            <?php /* echo $orderStatuses[$childOrderDetail['op_status_id']]; */ ?>
                                        </p>
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId); ?>:
                                            </strong>
                                            <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'CART_TOTAL'), true, false, true, false, true); ?>
                                        </p>
                                        <?php if (CommonHelper::orderProductAmount($childOrderDetail, 'SHIPPING') > 0) { ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Delivery', $siteLangId); ?>:
                                                </strong>
                                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'SHIPPING'), true, false, true, false, true); ?>
                                            </p>
                                        <?php } ?>
                                        <?php if (empty($childOrderDetail['taxOptions'])) { ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?>:
                                                </strong>
                                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail, 'TAX'), true, false, true, false, true); ?>
                                            </p>
                                        <?php } else { ?>
                                            <?php foreach ($childOrderDetail['taxOptions'] as $key => $val) { ?>
                                                <p>
                                                    <strong>
                                                        <?php echo CommonHelper::displayTaxPercantage($val, true) ?>:
                                                    </strong>
                                                    <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?>
                                                </p>
                                        <?php
                                            }
                                        }
                                        ?>
                                        <?php
                                        $disc = CommonHelper::orderProductAmount($childOrderDetail, 'DISCOUNT');
                                        if (!empty($disc)) {
                                        ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?>:
                                                </strong>
                                                <?php echo CommonHelper::displayMoneyFormat($disc, true, false, true, false, true); ?>
                                            </p>
                                        <?php
                                        }

                                        $volumeDiscount = CommonHelper::orderProductAmount($childOrderDetail, 'VOLUME_DISCOUNT');
                                        if (!empty($volumeDiscount) && 0 < $volumeDiscount) {
                                        ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?>:
                                                </strong>
                                                <?php echo CommonHelper::displayMoneyFormat($volumeDiscount, true, false, true, false, true); ?>
                                            </p>
                                        <?php } ?>
                                        <?php
                                        $rewardPointDiscount = CommonHelper::orderProductAmount($childOrderDetail, 'REWARDPOINT');
                                        if (!empty($rewardPointDiscount) && 0 < $rewardPointDiscount) {
                                        ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?>:
                                                </strong>
                                                <?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?>
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
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Order_Total', $siteLangId); ?>:
                                            </strong>
                                            <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrderDetail), true, false, true, false, true); ?>
                                        </p>
                                        <?php if ($orderDetail['order_late_charges'] > 0) { ?>
                                        <p>
                                            <strong><?php echo Labels::getLabel('LBL_Pending_Charges_From_Previous_Orders', $siteLangId); ?></strong>
                                            <?php echo CommonHelper::displayMoneyFormat(($orderDetail['order_late_charges'])); ?>
                                        </p>
                                        <?php } ?>
                                        
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-4">
                                <div class="bg-gray p-3 rounded">
                                    <div class="info--order">
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Invoice', $siteLangId); ?> #:
                                            </strong>
                                            <?php echo $childOrderDetail['op_invoice_number']; ?>
                                        </p>
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>:
                                            </strong>
                                            <?php echo FatDate::format($childOrderDetail['order_date_added']); ?>
                                        </p>
                                        <?php if (0 && $childOrderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP && $childOrderDetail['order_is_rfq'] == applicationConstants::NO) { ?>
                                            <p>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Pickup_Date', $siteLangId); ?>:
                                                </strong>
                                                <?php
                                                $fromTime = isset($childOrderDetail["opshipping_time_slot_from"]) ? date('H:i', strtotime($childOrderDetail["opshipping_time_slot_from"])) : '';
                                                $toTime = isset($childOrderDetail["opshipping_time_slot_to"]) ? date('H:i', strtotime($childOrderDetail["opshipping_time_slot_to"])) : '';
                                                $date = isset($childOrderDetail["opshipping_date"]) ? FatDate::format($childOrderDetail["opshipping_date"]) : '';
                                                echo $date . ' ' . $fromTime . ' - ' . $toTime;
                                                ?>
                                            </p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="bg-gray p-3 rounded">
                                    <p>
                                        <strong>
                                            <?php echo Labels::getLabel('LBL_Order', $siteLangId); ?>:
                                        </strong>
                                        <?php echo $orderDetail['order_id']; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="bg-gray p-3 rounded">
                                    <div class="info--order">
                                        <p>
                                            <strong>
                                                <?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>:
                                            </strong>
                                            <?php echo FatDate::format($orderDetail['order_date_added']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                    ?>
                    <div class="scroll scroll-x js-scrollable table-wrap">
                        <table class="table table-justified">
                            <thead>
                                <?php
                                $cartTotal = 0;
                                $shippingCharges = 0;
                                $total = 0;
                                if ($primaryOrder) {
                                    $arr[] = $childOrderDetail;
                                } else {
                                    $arr = $childOrderDetail;
                                }
                                $taxOptionsTotal = array();
                                foreach ($arr as $childOrder) {
                                    $shippingCharges = $shippingCharges + CommonHelper::orderProductAmount($childOrder, 'shipping');
                                }
                                ?>
                                <tr class="">
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php
                                        if (!empty($orderDetail['pickupAddress'])) {
                                            echo Labels::getLabel('LBL_PICKUP_DETAIL', $siteLangId);
                                        }
                                        ?>
                                    </th>

                                    <th>
                                        <?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Price', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php if ($shippingCharges > 0) { ?>
                                            <?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId); ?>
                                        <?php } ?>
                                    </th>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?>
                                    </th>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Total', $siteLangId); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($arr as $childOrder) {
                                    $cartTotal = $cartTotal + CommonHelper::orderProductAmount($childOrder, 'cart_total');
                                    $volumeDiscount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
                                    $rewardPointDiscount = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
                                ?>
                                    <tr>
                                        <td>
                                            <div class="item">
                                                <?php
                                                $prodOrBatchUrl = 'javascript:void(0)';
                                                if ($childOrder['op_is_batch']) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($childOrder['op_selprod_id']));
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($childOrder['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                } else {
                                                    if (Product::verifyProductIsValid($childOrder['op_selprod_id']) == true) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($childOrder['op_selprod_id']));
                                                    }
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($childOrder['selprod_product_id'], "SMALL", $childOrder['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                    if ($childOrder['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                                        $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($childOrder['op_selprod_id'], "THUMB", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                    }
                                                }
                                                ?>
                                                <figure class="item__pic">
                                                    <a href="<?php echo $prodOrBatchUrl; ?>">
                                                        <img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $childOrder['op_product_name']; ?>" alt="<?php echo $childOrder['op_product_name']; ?>">
                                                    </a>
                                                </figure>


                                                <div class="item__description">
                                                    <?php if ($childOrder['op_selprod_title'] != '') { ?>
                                                        <div class="item__title">
                                                            <a title="<?php echo $childOrder['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>">
                                                                <?php echo $childOrder['op_selprod_title'] . '<br>'; ?>
                                                            </a>
                                                        </div>
                                                        <div class="item__category">
                                                            <?php echo $childOrder['op_product_name']; ?>
                                                        </div>
                                                    <?php } else { ?>
                                                        <div class="item__category">
                                                            <a title="<?php echo $childOrder['op_product_name']; ?>" href="<?php echo UrlHelper::generateUrl('Products', 'view', array($childOrder['op_selprod_id'])); ?>">
                                                                <?php echo $childOrder['op_product_name']; ?>
                                                            </a>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="item__brand">
                                                        <?php echo Labels::getLabel('Lbl_Brand', $siteLangId) ?>:
                                                        <?php echo CommonHelper::displayNotApplicable($siteLangId, $childOrder['op_brand_name']); ?>
                                                    </div>
                                                    <?php if ($childOrder['op_selprod_options'] != '') { ?>
                                                        <div class="item__specification">
                                                            <?php echo $childOrder['op_selprod_options']; ?>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="item__sold_by">
                                                        <?php echo Labels::getLabel('LBL_Sold_By', $siteLangId) . ': ' . $childOrder['op_shop_name']; ?>
                                                    </div>
                                                    <?php if ($childOrder['op_shipping_duration_name'] != '') { ?>
                                                        <div class="item__shipping">
                                                            <?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?>:
                                                            <?php echo $childOrder['op_shipping_durations'] . '-' . $childOrder['op_shipping_duration_name']; ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </td>
                                        <?php /* <td style="width:20%;" >
                                          <?php echo $childOrder['op_shipping_durations'] . '-' . $childOrder['op_shipping_duration_name']; ?>
                                    </td> */ ?>
                                        <?php /* if (Shipping::FULFILMENT_PICKUP == $childOrder['opshipping_fulfillment_type']) { */ ?>
                                        <td>
                                            <?php if (Shipping::FULFILMENT_PICKUP == $childOrder['opshipping_fulfillment_type']) { ?>
                                                <p>
                                                    <?php if ($childOrder['order_is_rfq'] == applicationConstants::NO && 0) { ?>
                                                    <strong class="abbb">
                                                        <?php
                                                        $opshippingDate = isset($childOrder['opshipping_date']) ? $childOrder['opshipping_date'] . ' ' : '';
                                                        $timeSlotFrom = isset($childOrder['opshipping_time_slot_from']) ? ' (' . date('H:i', strtotime($childOrder['opshipping_time_slot_from'])) . ' - ' : '';
                                                        $timeSlotTo = isset($childOrder['opshipping_time_slot_to']) ? date('H:i', strtotime($childOrder['opshipping_time_slot_to'])) . ')' : '';
                                                        echo $opshippingDate . $timeSlotFrom . $timeSlotTo;
                                                        ?>
                                                    </strong><br>
                                                    <?php } ?>
                                                    
                                                    <?php echo $childOrder['addr_name']; ?>,
                                                    <?php
                                                    $address1 = !empty($childOrder['addr_address1']) ? $childOrder['addr_address1'] : '';
                                                    $address2 = !empty($childOrder['addr_address2']) ? ', ' . $childOrder['addr_address2'] : '';
                                                    $city = !empty($childOrder['addr_city']) ? '<br>' . $childOrder['addr_city'] : '';
                                                    $state = !empty($childOrder['state_name']) ? ', ' . $childOrder['state_name'] : ', ' . $childOrder['state_identifier'];
                                                    $country = !empty($childOrder['country_name']) ? ' ' . $childOrder['country_name'] : ' ' . $childOrder['country_code'];
                                                    $zip = !empty($childOrder['addr_zip']) ? '(' . $childOrder['addr_zip'] . ')' : '';

                                                    echo $address1 . $address2 . $city . $state . $country . $zip;
                                                    ?>
                                                </p>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php echo $childOrder['op_qty']; ?>
                                        </td>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, true, false, true); ?>
                                        </td>
                                        <td>
                                            <?php if ($shippingCharges > 0) { ?>
                                                <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'shipping'), true, false, true, false, true); ?>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat($volumeDiscount, true, false, true, false, true); ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (empty($childOrder['taxOptions'])) {
                                                echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'TAX'), true, false, true, false, true);
                                            } else {
                                                foreach ($childOrder['taxOptions'] as $key => $val) {
                                            ?>
                                                    <p>
                                                        <strong>
                                                            <?php echo CommonHelper::displayTaxPercantage($val, true) ?> :
                                                        </strong>
                                                        <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?>
                                                    </p>
                                            <?php
                                                    if (!isset($taxOptionsTotal[$key]['value'])) {
                                                        $taxOptionsTotal[$key]['value'] = 0;
                                                    }
                                                    $taxOptionsTotal[$key]['value'] += $val['value'];
                                                    $taxOptionsTotal[$key]['title'] = CommonHelper::displayTaxPercantage($val);
                                                }
                                            }
                                            ?>
                                        </td>
                                        <?php /* <td>
                                          <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'tax'), true, false, true, false, true); ?>
                                    </td> */ ?>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder), true, false, true, false, true);

                                            /* if ($roundingOff = CommonHelper::getRoundingOff($childOrder)) {
                                              echo '(+' . $roundingOff . ')';
                                              } */
                                            ?>
                                        </td>
                                    </tr>
                                <?php
                                }
                                if (!$primaryOrder) {
                                ?>
                                    <tr>
                                        <td colspan="8">
                                            <?php echo Labels::getLabel('Lbl_Cart_Total', $siteLangId) ?>
                                        </td>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat($cartTotal, true, false, true, false, true); ?>
                                        </td>
                                    </tr>
                                    <?php if (0 < $shippingCharges) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId) ?>
                                            </td>
                                            <td>
                                                <?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (empty($taxOptionsTotal)) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId) ?>
                                            </td>
                                            <td>
                                                <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_tax_charged'], true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($taxOptionsTotal as $key => $val) {
                                            if (0 != $val['value']) {
                                                continue;
                                            }
                                        ?>
                                            <tr>
                                                <td colspan="8">
                                                    <?php echo $val['title']; ?>
                                                </td>
                                                <td>
                                                    <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <?php if (0 < $orderDetail['order_discount_total']) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo Labels::getLabel('LBL_Discount', $siteLangId) ?>
                                            </td>
                                            <td>-
                                                <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_discount_total'], true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (0 < $orderDetail['order_volume_discount_total']) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?>
                                            </td>
                                            <td>-
                                                <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_volume_discount_total'], true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (0 < $orderDetail['order_reward_point_value']) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo Labels::getLabel('LBL_REWARD_POINTS', $siteLangId) ?>
                                            </td>
                                            <td>-
                                                <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_reward_point_value'], true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (array_key_exists('order_rounding_off', $orderDetail) && 0 != $orderDetail['order_rounding_off']) { ?>
                                        <tr>
                                            <td colspan="8">
                                                <?php echo (0 < $orderDetail['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?>
                                            </td>
                                            <td>
                                                <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_rounding_off'], true, false, true, false, true); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td colspan="8">
                                            <?php echo Labels::getLabel('LBL_Total', $siteLangId) ?>
                                        </td>
                                        <td>
                                            <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_net_amount'], true, false, true, false, true); ?>
                                        </td>
                                    </tr>

                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row mt-4">
                        <?php
                        $transferBank = (isset($orderDetail['plugin_code']) && 'TransferBank' == $orderDetail['plugin_code']);
                        $class = $transferBank ? "col-lg-3 mb-4" : "col-lg-6 mb-4";
                        ?>
                        <div class="<?php echo $class; ?>">
                            <div class="bg-gray p-3 rounded">
                                <h6>
                                    <?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId); ?>
                                </h6>
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
                                    $billingAddress .= '<br>' . $orderDetail['billingAddress']['oua_dial_code'] . ' ' . $orderDetail['billingAddress']['oua_phone'];
                                }
                                ?>
                                <div class="info--order">
                                    <p>
                                        <?php echo $billingAddress; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($orderDetail['shippingAddress'])) { ?>
                            <div class="<?php echo $class; ?>">
                                <div class="bg-gray p-3 rounded">
                                    <h6>
                                        <?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?>
                                    </h6>
                                    <?php
                                    $shippingAddress = $orderDetail['shippingAddress']['oua_name'] . '<br>';
                                    if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_address1'] . '<br>';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_address2'] . '<br>';
                                    }

                                    if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                        $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ',';
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
                                        $shippingAddress .= '<br>' . $orderDetail['shippingAddress']['oua_dial_code'] . ' ' . $orderDetail['shippingAddress']['oua_phone'];
                                    }
                                    ?>
                                    <div class="info--order">
                                        <p>
                                            <?php echo $shippingAddress; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!$primaryOrder && !empty($shopAgreementArr)) {
                        ?>

                            <div class="col-lg-8 mb-4">
                                <div class="">
                                    <h6>
                                        <?php echo Labels::getLabel('LBL_Rental_Agreement', $siteLangId); ?>
                                    </h6>
                                    <div class="info--order">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Shop_Name', $siteLangId); ?></th>
                                                    <th><?php echo Labels::getLabel('LBL_Shop_Agreement', $siteLangId); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($shopAgreementArr as $key => $val) {
                                                    $afileName = AttachedFile::getAttributesById($val['agreementFileId'], 'afile_name');
                                                ?>
                                                    <tr>
                                                        <td><?php echo $val['shopName']; ?></td>
                                                        <td><a href="<?php echo CommonHelper::generateUrl('Buyer', 'downloadDigitalFile', [$key, $val['agreementFileId'], AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>"><?php echo $afileName; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($signatureData)) { ?>
                            <div class="col-lg-4 ">
                                <div class="">
                                    <h6>
                                        <?php echo Labels::getLabel('LBL_Signature', $siteLangId); ?>
                                    </h6>
                                    <div class="info--order">
                                        <img src="<?php echo UrlHelper::generateUrl('Image', 'signature', array($signatureData['afile_record_id'], 0, 'ORIGINAL', $signatureData['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>" title="<?php echo $signatureData['afile_name']; ?>" alt="<?php echo $signatureData['afile_name']; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                        <?php } ?>

                        <?php if (!empty($verificationFldsData)) { ?>
                            <div class="col-lg-12 mb-4">
                                <div class="">
                                    <h6>
                                        <?php echo Labels::getLabel('LBL_Verification_Data', $siteLangId); ?>
                                    </h6>
                                    <div class="info--order">
                                        <?php

                                        $verificationData = [];
                                        foreach ($verificationFldsData as $val) {
                                            $verificationData[$val['ovd_vfld_id']][] =  $val;
                                        }

                                        ?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Field_Name', $siteLangId); ?></th>
                                                    <th><?php echo Labels::getLabel('LBL_Field_Value', $siteLangId); ?></th>
                                                    <?php if (false == $primaryOrder) {
                                                        echo "<th>Attached with</th>";
                                                    } ?>
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
                                                                $downloadUrl = UrlHelper::generateUrl('Buyer', 'downloadAttachedFile', array($orderDetail['order_order_id'], $val[0]['ovd_vfld_id']));
                                                                $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderDetail['order_order_id'], $val[0]['ovd_vfld_id']);
                                                                echo '<a href="' . $downloadUrl . '"> ' . $file_row['afile_name'] . '</a>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <?php if (false == $primaryOrder) {
                                                            echo "<td>";
                                                            $count = count($val);
                                                            $i = 1;
                                                            foreach ($val as $vdata) {
                                                                echo  $childOrderDetail[$vdata['optvf_op_id']]['op_selprod_title'];
                                                                if ($count > $i) {
                                                                    echo "</br>";
                                                                }
                                                                $i++;
                                                            }
                                                            echo "</td>";
                                                        }
                                                        ?>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                        <?php } ?>

                        <?php if (true == $transferBank) {
                            $pluginSettingsObj = new PluginSetting(0, 'TransferBank');
                            $settings = $pluginSettingsObj->get($siteLangId); ?>
                            <div class="col-lg-6 mb-4">
                                <div class="bg-gray p-3 rounded">
                                    <h6>
                                        <?php echo Labels::getLabel('LBL_BANK_DETAIL', $siteLangId); ?>
                                    </h6>
                                    <div class="info--order">
                                        <ul class="transfer-payment-detail">
                                            <li>
                                                <i class="icn">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name">
                                                        </use>
                                                    </svg>
                                                </i>
                                                <div class="lable">
                                                    <h6><?php echo Labels::getLabel('LBL_BUSSINESS_NAME', $siteLangId); ?>
                                                    </h6>
                                                    <?php echo $settings['business_name']; ?>
                                                </div>

                                            </li>
                                            <li>
                                                <i class="icn">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name">
                                                        </use>
                                                    </svg>
                                                </i>
                                                <div class="lable">
                                                    <h6><?php echo Labels::getLabel('LBL_BANK_NAME', $siteLangId); ?></h6>
                                                    <?php echo $settings['bank_name']; ?>
                                                </div>

                                            </li>
                                            <li>
                                                <i class="icn">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch">
                                                        </use>
                                                    </svg>
                                                </i>
                                                <div class="lable">
                                                    <h6><?php echo Labels::getLabel('LBL_BANK_BRANCH', $siteLangId); ?></h6>
                                                    <?php echo $settings['bank_branch']; ?>
                                                </div>

                                            </li>
                                            <li>
                                                <i class="icn">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account">
                                                        </use>
                                                    </svg>
                                                </i>
                                                <div class="lable">
                                                    <h6><?php echo Labels::getLabel('LBL_ACCOUNT_#', $siteLangId); ?></h6>
                                                    <?php echo $settings['account_number']; ?>
                                                </div>

                                            </li>
                                            <li>
                                                <i class="icn">
                                                    <svg class="svg">
                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc">
                                                        </use>
                                                    </svg>
                                                </i>
                                                <div class="lable">
                                                    <h6><?php echo Labels::getLabel('LBL_IFSC_/_MICR', $siteLangId); ?></h6>
                                                    <?php echo $settings['ifsc']; ?>
                                                </div>

                                            </li>
                                            <?php if (!empty($settings['routing'])) { ?>
                                                <li>
                                                    <i class="icn">
                                                        <svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    <div class="lable">
                                                        <h6><?php echo Labels::getLabel('LBL_ROUTING_#', $siteLangId); ?></h6>
                                                        <?php echo $settings['routing']; ?>
                                                    </div>

                                                </li>
                                            <?php } ?>
                                            <?php if (!empty($settings['bank_notes'])) { ?>
                                                <li class="notes">
                                                    <i class="icn">
                                                        <svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    <div class="lable">
                                                        <h6><?php echo Labels::getLabel('LBL_OTHER_NOTES', $siteLangId); ?></h6>
                                                        <?php echo $settings['bank_notes']; ?>
                                                    </div>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (!empty($orderDetail['comments'])) { ?>

                        <div class="section--repeated mb-3">
                            <h6>
                                <?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId); ?>
                            </h6>
                            <div class="scroll scroll-x js-scrollable table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr class="">
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Status', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderDetail['comments'] as $row) {
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php echo FatDate::format($row['oshistory_date_added']); ?>
                                                </td>
                                                <td>
                                                    <?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    echo ($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($siteLangId, '');
                                                    if ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_SHIPPED) {
                                                        if (empty($row['oshistory_courier'])) {
                                                            $str = !empty($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) . ' ' . $row['oshistory_tracking_number'] : '';
                                                            if (empty($childOrderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                                $str .= " VIA <em>" . CommonHelper::displayNotApplicable($siteLangId, $childOrderDetail["opshipping_label"]) . "</em>";
                                                            } elseif (!empty($childOrderDetail['opship_tracking_url'])) {
                                                                $str .= " <a class='btn btn-outline-secondary btn-sm' href='" . $childOrderDetail['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                            }
                                                            echo $str;
                                                        } else {
                                                            echo ($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) : '';
                                                            $trackingNumber = $row['oshistory_tracking_number'];
                                                            $carrier = $row['oshistory_courier'];
                                                    ?>
                                                            <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_TRACK', $siteLangId); ?>" onClick="trackOrder('<?php echo trim($trackingNumber); ?>', '<?php echo trim($carrier); ?>', '<?php echo $childOrderDetail['op_invoice_number']; ?>')">
                                                                <?php echo $trackingNumber; ?>
                                                            </a>
                                                            <?php echo Labels::getLabel('LBL_VIA', $siteLangId); ?>
                                                            <em>
                                                                <?php echo CommonHelper::displayNotApplicable($siteLangId, $childOrderDetail["opshipping_label"]); ?>
                                                            </em>
                                                    <?php
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo !empty(trim(($row['oshistory_comments']))) ? nl2br($row['oshistory_comments']) : Labels::getLabel('LBL_N/A', $siteLangId); ?>
                                                </td>
                                            </tr>
                                        <?php }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if (!empty($orderDetail['payments'])) { ?>
                        <div class="section--repeated mb-3">
                            <h6>
                                <?php echo Labels::getLabel('LBL_Payment_History', $siteLangId); ?>
                            </h6>
                            <div class="scroll scroll-x js-scrollable table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr class="">
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Txn_Id', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?>
                                            </th>
                                            <th>
                                                <?php echo Labels::getLabel('LBL_STATUS', $siteLangId); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php foreach ($orderDetail['payments'] as $row) {
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php echo FatDate::format($row['opayment_date']); ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['opayment_gateway_txn_id']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['opayment_method']; ?>
                                                </td>
                                                <td>
                                                    <?php echo CommonHelper::displayMoneyFormat($row['opayment_amount'], true, false, true, false, true); ?>
                                                </td>
                                                <td>
                                                    <?php echo nl2br($row['opayment_comments']); ?>
                                                </td>
                                                <td>
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
                                                        <?php
                                                        $orderStatusArr = Orders::getOrderPaymentStatusArr($siteLangId);
                                                        echo $orderStatusArr[$row['opayment_txn_status']];
                                                        ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if (!$orderDetail['order_deleted'] && !$primaryOrder && !$orderDetail["order_payment_status"] && 'TransferBank' == $orderDetail['plugin_code']) { ?>


                        <div class="section--repeated mb-3">
                            <h6>
                                <?php echo Labels::getLabel('LBL_ORDER_PAYMENTS', $siteLangId); ?>
                            </h6>
                            <div class="info--order">
                                <?php
                                $frm->setFormTagAttribute('onsubmit', 'updatePayment(this); return(false);');
                                $frm->setFormTagAttribute('class', 'form');
                                $frm->developerTags['colClassPrefix'] = 'col-md-';
                                $frm->developerTags['fld_default_col'] = 12;


                                $paymentFld = $frm->getField('opayment_method');
                                $paymentFld->developerTags['col'] = 4;

                                $gatewayFld = $frm->getField('opayment_gateway_txn_id');
                                $gatewayFld->developerTags['col'] = 4;

                                $amountFld = $frm->getField('opayment_amount');
                                $amountFld->developerTags['col'] = 4;

                                $submitFld = $frm->getField('btn_submit');
                                $submitFld->developerTags['col'] = 4;
                                $submitFld->addFieldTagAttribute('class', 'btn btn-brand');
                                $submitFld->value = Labels::getLabel("LBL_SUBMIT_REQUEST", $siteLangId);
                                echo $frm->getFormHtml();
                                ?>
                            </div>
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
    $(document).ready(function() {
        setTimeout(function() {
            $('.printBtn-js').fadeIn();
        }, 500);
        $(document).on('click', '.printBtn-js', function() {
            $('.printFrame-js').show();
            setTimeout(function() {
                frames['frame'].print();
                $('.printFrame-js').hide();
            }, 500);
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
</script>