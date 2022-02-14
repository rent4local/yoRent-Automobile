<?php
$selected_method = '';
if ($order['order_pmethod_id']) {
    $selected_method .= CommonHelper::displayNotApplicable($adminLangId, $order["plugin_name"]);
}
if ($order['order_is_wallet_selected'] == applicationConstants::YES) {
    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Wallet", $adminLangId) : Labels::getLabel("LBL_Wallet", $adminLangId);
}
if ($order['order_reward_point_used'] > 0) {
    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Rewards", $adminLangId) : Labels::getLabel("LBL_Rewards", $adminLangId);
}
if (strtolower($order['plugin_code']) == 'cashondelivery' && $order['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
    $selected_method = Labels::getLabel('LBL_PAY_ON_PICKUP', $adminLangId);
}

$orderStatusLbl = Labels::getLabel('LBL_AWAITING_SHIPMENT', $adminLangId);
$orderStatus = '';
if (!empty($order["thirdPartyorderInfo"]) && isset($order["thirdPartyorderInfo"]['orderStatus'])) {
    $orderStatus = $order["thirdPartyorderInfo"]['orderStatus'];
    $orderStatusLbl = strpos($orderStatus, "_") ? str_replace('_', ' ', $orderStatus) : $orderStatus;
}

if (!empty($order['opship_tracking_url'])) {
    $orderStatusLbl = Labels::getLabel('LBL_SHIPPED', $adminLangId);
}
$completedOrderStatus = FatApp::getConfig("CONF_RENTAL_COMPLETED_ORDER_STATUS");
$orderProducts = array_merge(array($order), $attachedServices);
?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <?php if (!$print) { ?>
                    <div class="page__title no-print">
                        <div class="row">
                            <div class="col--first col-lg-6">
                                <span class="page__icon"><i class="ion-android-star"></i></span>
                                <h5><?php echo Labels::getLabel('LBL_Orders_Details', $adminLangId); ?></h5>
                                <?php
                                $breadcrumbData = [];
                                if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                    $breadcrumbData['hrefRental'] = '/rental';
                                }
                                $this->includeTemplate('_partial/header/header-breadcrumb.php', $breadcrumbData);
                                ?>
                                <?php
                                if (isset($parentOrderDetail) && !empty($parentOrderDetail)) {
                                    echo '<h6 class="text-danger">' . Labels::getLabel('LBL_This_order_is_extended_from_', $adminLangId) . ' <a href="' . CommonHelper::generateUrl('SellerOrders', 'view', array($parentOrderDetail['op_id'])) . '">#' . $parentOrderDetail['op_invoice_number'] . '</a> </h6>';
                                }

                                if (isset($extendedChildData) && !empty($extendedChildData)) {
                                    echo '<h6 class="text-danger">' . Labels::getLabel('LBL_This_order_is_extended_by', $adminLangId) . ' <a href="' . CommonHelper::generateUrl('SellerOrders', 'view', array($extendedChildData['opd_op_id'])) . '">#' . $extendedChildData['opd_order_id'] . '</a> </h6>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php /*  [ RENTAL END COUNTER NOTE  */ ?>
                    <?php if ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                        <?php
                        if (strtotime($order['opd_rental_start_date']) <= strtotime(date('Y-m-d 00:00:00')) && strtotime($order['opd_rental_end_date']) >= strtotime(date('Y-m-d 00:00:00')) && in_array($order['op_status_id'], OrderStatus::statusArrForRentalExpireNote())) {
                            $dateToRentalEnd = CommonHelper::getDifferenceBetweenDates(date('Y-m-d 00:00:00'), $order['opd_rental_end_date'], 0, ProductRental::DURATION_TYPE_DAY);
                            if ($dateToRentalEnd >= 3) {
                                $rentalEndMsgClass = 'alert-success';
                            } elseif ($dateToRentalEnd >= 2) {
                                $rentalEndMsgClass = 'alert-warning';
                            } else {
                                $rentalEndMsgClass = 'alert-danger';
                            }
                            ?>
                            <div class="alert text-center  alert-dismissible fade show <?php echo $rentalEndMsgClass; ?>" role="alert">
                                <h5><?php echo sprintf(Labels::getLabel('LBL_%s_Day(s)_Remaining_to_end_Rental', $adminLangId), $dateToRentalEnd); ?>
                                </h5>
                            </div>
                        <?php } elseif (strtotime($order['opd_rental_end_date']) < strtotime(date('Y-m-d 00:00:00')) && in_array($order['op_status_id'], OrderStatus::statusArrForLateChargeNote())) { ?>
                            <div class="alert alert-primary text-center alert-dismissible fade show" role="alert">
                                <h5> <?php echo Labels::getLabel('LBL_Rental_Duration_Ended._Late_Charges_may_be_Apply', $adminLangId); ?> </h5>
                                <!-- <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button> -->
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?php /* ]  */ ?>

                <?php } ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_Order_Details', $adminLangId); ?></h4>
                        <?php
                        if (!$print) {
                            $backUrl = UrlHelper::generateUrl('SellerOrders');
                            if ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                                $backUrl = UrlHelper::generateUrl('SellerOrders', 'rental');
                            }
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => $backUrl,
                                            'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                ]
                            ];

                            $data['otherButtons'][] = [
                                'attr' => [
                                    'href' => Fatutility::generateUrl('sellerOrders', 'viewInvoice', [$order['op_id']]),
                                    'target' => '_blank',
                                    'title' => Labels::getLabel('LBL_Print_Order_Detail', $adminLangId)
                                ],
                                'label' => '<i class="fas fa-print"></i>'
                            ];

                            if (!$shippingHanldedBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $order['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $order['order_payment_status'])) {
                                $plugin = new Plugin();
                                $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);

                                if (empty($order['opship_response']) && empty($order['opship_tracking_number']) && 'EasyPost' != $keyName) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'generateLabel(' . $order['op_id'] . ')',
                                            'title' => Labels::getLabel('LBL_GENERATE_LABEL', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-file-download"></i>'
                                    ];
                                } elseif (!empty($order['opship_response']) && 'EasyPost' != $keyName) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$order['op_id']]),
                                            'target' => "_blank",
                                            'title' => Labels::getLabel('LBL_PREVIEW_LABEL', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-file-export"></i>'
                                    ];
                                }

                                if ((!empty($orderStatus) && 'awaiting_shipment' == $orderStatus && !empty($order['opship_response']) || 'EasyPost' == $keyName) && empty($order['opship_tracking_number'])) {
                                    if ('EasyPost' == $keyName) {
                                        $label = Labels::getLabel('LBL_BUY_SHIPMENT_&_GENERATE_LABEL', $adminLangId);
                                    } else {
                                        $label = Labels::getLabel('LBL_PROCEED_TO_SHIPMENT', $adminLangId);
                                    }
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'proceedToShipment(' . $order['op_id'] . ')',
                                            'title' => $label
                                        ],
                                        'label' => '<i class="fas fa-shipping-fast"></i>'
                                    ];
                                }
                            }

                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <?php /* echo "<pre>"; print_r($order); echo "</pre>";  */ ?>
                        <table class="table table--details">
                        <?php 
                            $fldKeys = [
                                'op_invoice_number' => Labels::getLabel('LBL_Invoice_Id', $adminLangId),
                                'order_date_added' => Labels::getLabel('LBL_Order_Date', $adminLangId),
                                'orderstatus_name' => Labels::getLabel('LBL_Status', $adminLangId),
                                'buyer_user_name' => Labels::getLabel('LBL_Customer/Guest', $adminLangId),
                                'selected_method' => Labels::getLabel('LBL_Payment_Method', $adminLangId),
                                'op_refund_qty' => Labels::getLabel('LBL_Refund_for_Qty.', $adminLangId) . '['. $order["op_refund_qty"] .']',
                                'op_commission_row' => Labels::getLabel('LBL_Commission_Charged', $adminLangId) . '['.  $order["op_commission_percentage"] . '%] <a href="javascript:void(0);" onClick="showBreakdownPopup(this);"  data-target="#commsion--js"><i class="fa fa-question-circle"></i></a>',
                                'delivery_shipping' => Labels::getLabel('LBL_Delivery/Shipping', $adminLangId),
                                'tax_row' => Labels::getLabel('LBL_Tax', $adminLangId),
                                'cart_total' => Labels::getLabel('LBL_Cart_Total', $adminLangId),
                                'volumn_discount' => Labels::getLabel('LBL_Volume_Discount', $adminLangId),
                                'duration_discount' => Labels::getLabel('LBL_Duration_Discount', $adminLangId),
                                'op_rounding_off' => Labels::getLabel('LBL_Rounding_Down', $adminLangId),
                                'net_amount' => Labels::getLabel('LBL_Total_Paid', $adminLangId),
                                'pickup_row' => Labels::getLabel('LBL_Pickup_Date', $adminLangId),
                                'late_charges' => Labels::getLabel('LBL_Late_Charges', $adminLangId)
                            ];
                            if (1 > $order["op_refund_qty"]) {
                                unset($fldKeys['op_refund_qty']);
                            }
                            if (!$shippingHanldedBySeller || $order["opshipping_fulfillment_type"] != Shipping::FULFILMENT_SHIP) {
                                unset($fldKeys['delivery_shipping']);
                            }
                            if (!$order['op_tax_collected_by_seller']) { 
                                unset($fldKeys['tax_row']);
                            }
                            if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_SALE) {
                                unset($fldKeys['duration_discount']);
                            }
                            if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                unset($fldKeys['volumn_discount']);
                            }
                            $roundingOff = (array_key_exists('op_rounding_off', $order)) ? $order['op_rounding_off'] : 0;
                            if ($roundingOff == 0) {
                                unset($fldKeys['op_rounding_off']);
                            }
                            
                            if ($order["opshipping_fulfillment_type"] != Shipping::FULFILMENT_PICKUP || $order["order_is_rfq"] == applicationConstants::YES || $order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                unset($fldKeys['pickup_row']);
                            }
                            $lateCharges = $order['charge_total_amount'] + $serviceTotalPriceArr['late_charges_total'];
                            if (0 >= $lateCharges) {
                                unset($fldKeys['late_charges']);
                            }
                                
                            $keyIndex = 0;
                            foreach ($fldKeys as $key => $fldKey) {
                                if ($keyIndex == 0) {
                                    echo "<tr>";
                                }
                            
                                if ($keyIndex % 3 == 0 && $keyIndex != 0) {
                                    echo "</tr><tr>";
                                } 
                                $value = "";
                                switch (strtolower($key)) {
                                    case 'order_payment_status' : 
                                        $value = $order["orderstatus_name"];
                                        if (Orders::ORDER_PAYMENT_CANCELLED == $order["order_payment_status"]) {
                                            $value = Orders::getOrderPaymentStatusArr($adminLangId)[$order["order_payment_status"]];
                                        }
                                        break;
                                    case 'buyer_user_name' :
                                        $value = $order["buyer_user_name"] . ' (' . $order['buyer_username'] . ')';
                                        break;
                                    case 'selected_method' : 
                                        $value = $selected_method;
                                        break;
                                    case 'op_commission_row' : 
                                        $value = CommonHelper::displayMoneyFormat($order['op_commission_charged'] - $order['op_refund_commission'], true, true);
                                        
                                        $productComm = ($order['op_unit_price'] * $order['op_commission_percentage'] / 100) * ($order['op_qty'] - $order['op_refund_qty']);
                                        
                                        $commisionHtml = '<tr><th>'. Labels::getLabel('LBL_Product_Amount', $adminLangId) .'</th><td>'. CommonHelper::displayMoneyFormat($productComm, true, true) .'</td></tr>';
                                        
                                        if ($order['op_commission_include_shipping'] && $shippingHanldedBySeller) {
                                            $shippingCost = CommonHelper::orderProductAmount($order, 'SHIPPING') / $order['op_qty'];
                                            
                                            $shipcmmission = ($shippingCost * $order['op_commission_percentage'] / 100) * ($order['op_qty'] - $order['op_refund_qty']);
                                        
                                            $commisionHtml .= '<tr><th>'. Labels::getLabel('LBL_Shipping', $adminLangId) .'</th><td>'. CommonHelper::displayMoneyFormat($shipcmmission, true, true) .'</td></tr>';
                                        }
                                        
                                        if ($order['op_commission_include_tax'] && $order['op_tax_collected_by_seller']) {
                                            $taxCost = CommonHelper::orderProductAmount($order, 'TAX') / $order['op_qty'];
                                            
                                            $taxcmmission = ($taxCost * $order['op_commission_percentage'] / 100) * ($order['op_qty'] - $order['op_refund_qty']);
                                        
                                            $commisionHtml .= '<tr><th>'. Labels::getLabel('LBL_Tax', $adminLangId) .'</th><td>'. CommonHelper::displayMoneyFormat($taxcmmission, true, true) .'</td></tr>';
                                        }
                                        
                                        if ($order['op_commission_on_security']) {
                                            $remaingSecAmt = ($order['opd_rental_security'] * $order['op_qty']) - $order['refund_security_amount'];
                                            $secCommission = $remaingSecAmt * $order['op_commission_percentage'] / 100;
                                            
                                            $commisionHtml .= '<tr><th>'. Labels::getLabel('LBL_REntal_Security', $adminLangId) .'</th><td>'. CommonHelper::displayMoneyFormat($secCommission, true, true) .'</td></tr>';
                                        }
                                        
                                        $commisionHtml .= '<tr><th>'. Labels::getLabel('LBL_Total_Commission', $adminLangId) .'</th><td>'. $value .'</td></tr>';
                                        
                                        break;
                                    case 'op_refund_qty' : 
                                        $value = CommonHelper::displayMoneyFormat($order["op_refund_amount"], true, true);
                                        break;
                                    case 'cart_total' : 
                                        $value = CommonHelper::displayMoneyFormat((CommonHelper::orderProductAmount($order, 'CART_TOTAL') + $serviceTotalPriceArr['cart_total']), true, true);
                                        break;
                                    case 'delivery_shipping' :
                                        $value = '+' . CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'SHIPPING'), true, true);
                                        break;
                                    case 'tax_row' :
                                        $totalTaxes = CommonHelper::orderProductAmount($order, 'TAX');
                                        if (!empty($serviceTotalPriceArr)) {
                                            $totalTaxes += $serviceTotalPriceArr['tax_total'];
                                        }
                                        $value = CommonHelper::displayMoneyFormat($totalTaxes, true, true);
                                        break;
                                    case 'volumn_discount' : 
                                        $value = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'VOLUME_DISCOUNT'), true, true);
                                        break;
                                    case 'duration_discount' : 
                                        $value = CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'DURATION_DISCOUNT'), true, true);
                                        break;
                                    case 'net_amount' : 
                                        $value = CommonHelper::displayMoneyFormat((CommonHelper::orderProductAmount($order, 'netamount', false, User::USER_TYPE_SELLER) + $serviceTotalPriceArr['net_total']), true, true);
                                        break;
                                    case 'op_rounding_off' : 
                                        $fldKey = (0 < $order['op_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $adminLangId) : Labels::getLabel('LBL_Rounding_Down', $adminLangId);
                                        $value = CommonHelper::displayMoneyFormat($order['op_rounding_off'], true, true);
                                        break;
                                    case 'pickup_row' : 
                                        $fromTime = date('H:i', strtotime($order["opshipping_time_slot_from"]));
                                        $toTime = date('H:i', strtotime($order["opshipping_time_slot_to"]));
                                        $value = FatDate::format($order["opshipping_date"]) . ' ' . $fromTime . ' - ' . $toTime;
                                        break;
                                    case 'late_charges' : 
                                        $value = CommonHelper::displayMoneyFormat(($order['charge_total_amount'] + $serviceTotalPriceArr['late_charges_total']));
                                        break;
                                    default :
                                        $value = (isset($order[$key])) ? $order[$key] : "";
                                        break;
                                }
                                
                                echo '<td><strong>'. $fldKey .' : </strong>'. $value .'</td>';
                                if ($keyIndex == (count($fldKeys) - 1)) {
                                    echo "</tr>";
                                }
                                $keyIndex++;
                            }
                        ?>
                        </table>
                    </div>
                </section>
                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_SELLER_/_CUSTOMER_DETAILS', $adminLangId); ?></h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Shop_Name', $adminLangId); ?> :
                                        </strong><?php echo $order["op_shop_name"] ?><br><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>:
                                        </strong><?php echo $order["op_shop_owner_name"] ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>
                                            : </strong>
                                        <?php echo $order["op_shop_owner_email"] ?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>
                                            : </strong> <?php echo $order["op_shop_owner_phone"] ?></p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></h5>
                                    <p><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?> :
                                        </strong><?php echo $order["buyer_name"] ?><br><strong><?php echo Labels::getLabel('LBL_UserName', $adminLangId); ?>:
                                        </strong><?php echo $order["buyer_username"]; ?><br><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>
                                            :
                                        </strong><?php echo $order["buyer_email"] ?><br><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>
                                            : </strong>
                                        <?php echo $order["buyer_phone"] ?></p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4>
                                    <?php
                                    if (!empty($order['pickupAddress'])) {
                                        echo Labels::getLabel('LBL_Billing_/_Pickup_Details', $adminLangId);
                                    } else {
                                        echo Labels::getLabel('LBL_Billing_/_Shipping_Details', $adminLangId);
                                    }
                                    ?>
                                </h4>
                            </div>
                            <div class="row space">
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Billing_Details', $adminLangId); ?> </h5>
                                    <p><strong><?php echo $order['billingAddress']['oua_name']; ?></strong><br>
                                        <?php
                                        $billingAddress = '';
                                        if ($order['billingAddress']['oua_address1'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_address1'] . '<br>';
                                        }

                                        if ($order['billingAddress']['oua_address2'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_address2'] . '<br>';
                                        }

                                        if ($order['billingAddress']['oua_city'] != '') {
                                            $billingAddress .= $order['billingAddress']['oua_city'] . ',';
                                        }

                                        if ($order['billingAddress']['oua_zip'] != '') {
                                            $billingAddress .= ' ' . $order['billingAddress']['oua_state'];
                                        }

                                        if ($order['billingAddress']['oua_zip'] != '') {
                                            $billingAddress .= '-' . $order['billingAddress']['oua_zip'];
                                        }

                                        if ($order['billingAddress']['oua_phone'] != '') {
                                            $billingAddress .= '<br>Phone: ' . $order['billingAddress']['oua_phone'];
                                        }
                                        echo $billingAddress;
                                        ?><br>
                                    </p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <?php if (!empty($order['shippingAddress'])) { ?>
                                        <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?></h5>
                                        <p>
                                            <strong>
                                                <?php echo $order['shippingAddress']['oua_name']; ?></strong><br>
                                            <?php
                                            $shippingAddress = '';
                                            if ($order['shippingAddress']['oua_address1'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_address1'] . '<br>';
                                            }

                                            if ($order['shippingAddress']['oua_address2'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_address2'] . '<br>';
                                            }

                                            if ($order['shippingAddress']['oua_city'] != '') {
                                                $shippingAddress .= $order['shippingAddress']['oua_city'] . ',';
                                            }

                                            if ($order['shippingAddress']['oua_zip'] != '') {
                                                $shippingAddress .= ' ' . $order['shippingAddress']['oua_state'];
                                            }

                                            if ($order['shippingAddress']['oua_zip'] != '') {
                                                $shippingAddress .= '-' . $order['shippingAddress']['oua_zip'];
                                            }

                                            if ($order['shippingAddress']['oua_phone'] != '') {
                                                $shippingAddress .= '<br>Phone: ' . $order['shippingAddress']['oua_phone'];
                                            }

                                            echo $shippingAddress;
                                        }
                                        if (!empty($order['pickupAddress'])) {
                                            ?>
                                        <h5><?php echo Labels::getLabel('LBL_Pickup_Details', $adminLangId); ?></h5>
                                        <p>
                                            <?php if ($order['order_is_rfq'] == applicationConstants::NO && $order['opd_sold_or_rented'] != applicationConstants::PRODUCT_FOR_RENT) { ?>
                                                <strong>
                                                    <?php
                                                    $opshippingDate = isset($order['opshipping_date']) ? $order['opshipping_date'] . ' ' : '';
                                                    $timeSlotFrom = isset($order['opshipping_time_slot_from']) ? date('H:i', strtotime($order['opshipping_time_slot_from'])) . ' - ' : '';
                                                    $timeSlotTo = isset($order['opshipping_time_slot_to']) ? date('H:i', strtotime($order['opshipping_time_slot_to'])) : '';
                                                    echo $opshippingDate . ' (' . $timeSlotFrom . $timeSlotTo . ')';
                                                    ?>
                                                </strong><br>
                                            <?php } ?>

                                            <?php echo $order['pickupAddress']['oua_name']; ?>,
                                            <?php
                                            $pickupAddress = '';
                                            if ($order['pickupAddress']['oua_address1'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_address1'] . '<br>';
                                            }

                                            if ($order['pickupAddress']['oua_address2'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_address2'] . '<br>';
                                            }

                                            if ($order['pickupAddress']['oua_city'] != '') {
                                                $pickupAddress .= $order['pickupAddress']['oua_city'] . ',';
                                            }

                                            if ($order['pickupAddress']['oua_zip'] != '') {
                                                $pickupAddress .= ' ' . $order['pickupAddress']['oua_state'];
                                            }

                                            if ($order['pickupAddress']['oua_zip'] != '') {
                                                $pickupAddress .= '-' . $order['pickupAddress']['oua_zip'];
                                            }

                                            if ($order['pickupAddress']['oua_phone'] != '') {
                                                $pickupAddress .= '<br>Phone: ' . $order['pickupAddress']['oua_phone'];
                                            }
                                            echo $pickupAddress;
                                        }
                                        ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table">
                            <tr>
                                <th>#</th>
                                <th><?php echo Labels::getLabel('LBL_Product_Name', $adminLangId); ?></th>
                                <?php if (empty($order['pickupAddress'])) { ?>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?>
                                    </th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Unit_Price', $adminLangId); ?></th>

                                <?php if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                                    <th>
                                        <?php echo Labels::getLabel('LBL_Security_Amount', $adminLangId); ?>
                                    </th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?></th>

                                <?php if ($shippingHanldedBySeller) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Shipping', $adminLangId); ?></th>
                                <?php } ?>
                                <?php if ($order['op_tax_collected_by_seller']) { ?>
                                    <th><?php echo Labels::getLabel('LBL_Tax', $adminLangId); ?></th>
                                <?php } ?>
                                <th><?php echo Labels::getLabel('LBL_Volume/Duration_Discount', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Total', $adminLangId); ?></th>
                            </tr>
                            <?php foreach ($orderProducts as $orderProduct) { ?>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <?php
                                        $txt = '';
                                        if ($orderProduct['op_selprod_title'] != '') {
                                            $txt .= $orderProduct['op_selprod_title'] . '<br/>';
                                        }
                                        $txt .= $orderProduct['op_product_name'];
                                        $txt .= '<br/>';
                                        if (!empty($orderProduct['op_brand_name'])) {
                                            $txt .= Labels::getLabel('LBL_Brand', $adminLangId) . ': ' . $orderProduct['op_brand_name'];
                                        }
                                        if (!empty($orderProduct['op_brand_name']) && !empty($orderProduct['op_selprod_options'])) {
                                            $txt .= ' | ';
                                        }
                                        if ($orderProduct['op_selprod_options'] != '') {
                                            $txt .= $orderProduct['op_selprod_options'];
                                        }
                                        if ($orderProduct['op_selprod_sku'] != '') {
                                            $txt .= '<br/>' . Labels::getLabel('LBL_SKU', $adminLangId) . ':  ' . $orderProduct['op_selprod_sku'];
                                        }
                                        if ($orderProduct['op_product_model'] != '') {
                                            $txt .= '<br/>' . Labels::getLabel('LBL_Model', $adminLangId) . ':  ' . $orderProduct['op_product_model'];
                                        }
                                        echo $txt;
                                        ?>
                                    </td>
                                    <?php if (Shipping::FULFILMENT_PICKUP != $orderProduct['opshipping_fulfillment_type']) { ?>
                                        <td>
                                            <?php if ($orderProduct['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) { ?>
                                                <strong>
                                                    <?php echo Labels::getLabel('LBL_Shipping_Class', $adminLangId); ?> :
                                                </strong>
                                                <?php echo CommonHelper::displayNotApplicable($adminLangId, $orderProduct["opshipping_label"]); ?><br>
                                                <?php if (!empty($orderProduct["opshipping_service_code"])) { ?>
                                                    <strong>
                                                        <?php echo Labels::getLabel('LBL_SHIPPING_SERVICES', $adminLangId); ?> :
                                                    </strong>
                                                    <?php echo $orderProduct["opshipping_service_code"]; ?><br>
                                                <?php } ?>
                                                <?php if (!empty($orderStatusLbl)) { ?>
                                                    <strong>
                                                        <?php echo Labels::getLabel('LBL_ORDER_STATUS', $adminLangId); ?>:
                                                    </strong>
                                                    <?php echo ucwords($orderStatusLbl); ?>
                                                <?php } ?>
                                                <?php
                                            } else {
                                                echo Labels::getLabel('LBL_N/A', $adminLangId);
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php
                                        if ($orderProduct['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT && $orderProduct['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                                            echo CommonHelper::displayMoneyFormat($orderProduct["opd_duration_price"], true, true);
                                            $duration = CommonHelper::getDifferenceBetweenDates($orderProduct['opd_rental_start_date'], $orderProduct['opd_rental_end_date'], $orderProduct['op_selprod_user_id'], $orderProduct['opd_rental_type']);
                                            $durationStr = CommonHelper::displayProductRentalDuration($duration, $orderProduct['opd_rental_type'], $adminLangId);
                                            echo'<br />( <strong>' . Labels::getLabel('LBL_Rental_Price(Unit_Price*Duration):', $adminLangId) . ' ' . CommonHelper::displayMoneyFormat($orderProduct['opd_rental_price']) . ' ' . '</strong> <br />' . Labels::getLabel('LBL_Duration:', $adminLangId) . ' ' . $durationStr . '<br />' . Labels::getLabel('LBL_From:', $adminLangId) . ' ' . date('M d, Y', strtotime($orderProduct['opd_rental_start_date'])) . '<br />' . Labels::getLabel('LBL_to', $adminLangId) . ': ' . date('M d, Y', strtotime($orderProduct['opd_rental_end_date'])) . ') ';
                                        } else {
                                            echo CommonHelper::displayMoneyFormat($orderProduct["op_unit_price"], true, true);
                                        }
                                        ?>
                                    </td>
                                    <?php if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                                        <td> <?php echo CommonHelper::displayMoneyFormat($orderProduct["opd_rental_security"], true, true); ?></td>
                                    <?php } ?>    
                                    <td><?php echo $orderProduct["op_qty"] ?></td>
                                    <?php if ($shippingHanldedBySeller) { ?>
                                        <td><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'SHIPPING'), true, true); ?>
                                        </td>
                                    <?php } ?>
                                    <?php if ($orderProduct['op_tax_collected_by_seller']) { ?>
                                        <td>
                                            <?php
                                            if (empty($orderProduct['taxOptions'])) {
                                                echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'TAX'), true, true);
                                            } else {
                                                foreach ($orderProduct['taxOptions'] as $key => $val) {
                                                    ?>
                                                    <p>
                                                        <strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?> : </strong>
                                                        <?php echo CommonHelper::displayMoneyFormat($val['value']); ?>
                                                    </p>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php
                                        if ($orderProduct['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                            echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'DURATION_DISCOUNT'), true, true);
                                        } else {
                                            echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'VOLUME_DISCOUNT'), true, true);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderProduct, 'netamount', false, User::USER_TYPE_SELLER), true, true);

                                        /* if ($roundingOff = CommonHelper::getRoundingOff($order)) {
                                          echo '(+' . $roundingOff . ')';
                                          } */
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                </section>

                <?php
                if (!empty($verificationFldsData) && $order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                    $verificationData = [];
                    foreach ($verificationFldsData as $val) {
                        $verificationData[$val['ovd_vfld_id']][] = $val;
                    }
                    ?>
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Verification_Data', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th width="33%"><?php echo Labels::getLabel('LBL_Field_Name', $adminLangId); ?>
                                        </th>
                                        <th width="33%"><?php echo Labels::getLabel('LBL_Field_Value', $adminLangId); ?>
                                        </th>
                                    </tr>
                                    <?php foreach ($verificationData as $key => $val) { ?>
                                        <tr>
                                            <td><?php echo $val[0]['ovd_vflds_name']; ?></td>
                                            <td><?php
                                                if ($val[0]['ovd_vflds_type'] == VerificationFields::FLD_TYPE_TEXTBOX) {
                                                    echo $val[0]['ovd_value'];
                                                } else {
                                                    $downloadUrl = UrlHelper::generateUrl('SellerOrders', 'downloadAttachedFile', array($order['order_order_id'], $val[0]['ovd_vfld_id']), CONF_WEBROOT_FRONTEND);
                                                    $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $order['order_order_id'], $val[0]['ovd_vfld_id']);
                                                    echo '<a href="' . $downloadUrl . '"> ' . $file_row['afile_name'] . '</a>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php } ?>

                <?php
                $orderCompleteExtended = false;
                if (!empty($order['comments']) && !$print) {
                    ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Order_Comments', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody">
                            <table class="table">
                                <tr>
                                    <th><?php echo Labels::getLabel('LBL_Date_Added', $adminLangId); ?></td>
                                    <th><?php echo Labels::getLabel('LBL_Customer_Notified', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Attached_Files', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?></th>
                                </tr>
                                <?php
                                foreach ($order['comments'] as $row) {
                                    $attachedFiles = (isset($statusAttachedFiles) && isset($statusAttachedFiles[$row['oshistory_id']])) ? $statusAttachedFiles[$row['oshistory_id']] : [];
                                    if ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_RENTAL_EXTENDED) {
                                        $orderCompleteExtended = true;
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo FatDate::format($row['oshistory_date_added']); ?></td>
                                        <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                        <td style="width:30%;">
                                            <?php if (!empty($attachedFiles)) { ?>
                                                <div>
                                                    <?php foreach ($attachedFiles as $attachedFile) { ?>
                                                        <a class="btn btn-outline-secondary btn-sm"
                                                           href="<?php echo UrlHelper::generateUrl('sellerOrders', 'downloadBuyerAtatchedFile', array($attachedFile['afile_record_id'], 0, $attachedFile['afile_id'])); ?>"
                                                           title="<?php echo $attachedFile['afile_name']; ?>"><i
                                                                class="fa fa-download"></i>
                                                            <?php echo $attachedFile['afile_name']; ?></a>
                                                    <?php } ?>
                                                </div>
                                                <?php
                                            } else {
                                                echo Labels::getLabel('LBL_N/A', $adminLangId);
                                            }
                                            ?>
                                        </td>
                                        <td><?php
                                            echo $orderStatuses[$row['oshistory_orderstatus_id']];
                                            if ($row['oshistory_orderstatus_id'] == FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS') && $row['oshistory_status_updated_by'] == $order['order_user_id']) {
                                                echo ' - ' . Labels::getLabel('LBL_Marked_by_Buyer', $adminLangId);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span> <?php echo html_entity_decode(nl2br($row['oshistory_comments'])); ?> </span>
                                            <?php
                                            /* echo ' ' . (($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($adminLangId, '')) . ' '; */
                                            if ($row['oshistory_orderstatus_id'] == OrderStatus::ORDER_SHIPPED || $row['oshistory_orderstatus_id'] == OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                if (empty($row['oshistory_courier'])) {
                                                    $str = !empty($row['oshistory_tracking_number']) ? ' <p> <strong>' . Labels::getLabel('LBL_Tracking_Number', $adminLangId) . '</strong> : ' . ' ' . $row['oshistory_tracking_number'] : '';
                                                    if (empty($order['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                        $str .= " VIA <em>" . CommonHelper::displayNotApplicable($adminLangId, $order["opshipping_label"]) . "</em>";
                                                    } elseif (!empty($order['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                        $str .= " <a class='btn btn-outline-secondary btn-sm' href='" . $order['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $adminLangId) . "</a>";
                                                    }

                                                    $str .= '</p>';
                                                    echo $str;
                                                } else {
                                                    echo"<p>";
                                                    echo ($row['oshistory_tracking_number']) ? '<strong>' . Labels::getLabel('LBL_Tracking_Number', $adminLangId) . '</strong> : ' : '';
                                                    $trackingNumber = $row['oshistory_tracking_number'];
                                                    $carrier = $row['oshistory_courier'];

                                                    if ($trackingPluginEnable && $row['oshistory_orderstatus_id'] != OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                        ?>
                                                        <a href="javascript:void(0)"
                                                           title="<?php echo Labels::getLabel('LBL_TRACK', $adminLangId); ?>"
                                                           onClick="trackOrder('<?php echo trim($trackingNumber); ?>', '<?php echo trim($carrier); ?>', '<?php echo $order["op_invoice_number"]; ?>')"  >
                                                               <?php echo $trackingNumber; ?>
                                                        </a>
                                                        <?php
                                                    } else {
                                                        echo $trackingNumber;
                                                        if (trim($row['oshistory_tracking_url']) != '') {
                                                            echo " <a class='btn btn-outline-secondary btn-sm' href='" . $row['oshistory_tracking_url'] . "'  target='_blank'>" . Labels::getLabel("MSG_TRACK", $adminLangId) . "</a>";
                                                        }
                                                    }

                                                    echo '</p>';

                                                    if ($row['oshistory_orderstatus_id'] != OrderStatus::ORDER_READY_FOR_RENTAL_RETURN) {
                                                        echo '<p> ' . Labels::getLabel('LBL_VIA', $adminLangId);
                                                        echo "<em>" . CommonHelper::displayNotApplicable($adminLangId, $order["opshipping_label"]) . "</em></p>";
                                                    }
                                                }
                                            }
                                            if (isset($statusAddressData[$row['oshistory_id']])) {
                                                $dropOffAddress = $statusAddressData[$row['oshistory_id']];
                                                echo '<br /><br /><p><strong>' . Labels::getLabel('LBL_DROPOFF_ADDRESS', $adminLangId) . '</strong></p><address class="delivery-address"><h5>' . $dropOffAddress['addr_name'] . ' <span>' . $dropOffAddress['addr_title'] . '</span></h5><p>' . $dropOffAddress['addr_address1'] . '<br>' . $dropOffAddress['addr_city'] . ',' . $dropOffAddress['state_name'] . '<br>' . $dropOffAddress['country_name'] . '<br>' . Labels::getLabel("LBL_Zip", $adminLangId) . ':' . $dropOffAddress['addr_zip'] . '<br></p><p class="phone-txt"><i class="fas fa-mobile-alt"></i>' . Labels::getLabel("LBL_Phone", $adminLangId) . ':' . $dropOffAddress['addr_phone'] . '<br></p></address>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </section>
                    <?php
                }

                if (!empty($attachment) && $order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                    ?>
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Signature', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <img src="<?php echo CommonHelper::generateUrl('Orders', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachment['afile_id'], AttachedFile::FILETYPE_SIGNATURE_IMAGE]); ?>" />
                        </div>
                    </section>
                    <?php
                }

                if ($displayForm && !$print && ($completedOrderStatus != $order['op_status_id']) && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                    <section class="section no-print">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Comments_on_order', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                            if (isset($extendedChildData) && !empty($extendedChildData) && $orderCompleteExtended) {
                                echo '<div class="section--repeated no-print text-danger extend-order-section">' . sprintf(Labels::getLabel('LBL_This_order_is_extended_.To_check_or_change_status_of_this_order_click_here_%s', $adminLangId), '<a href="' . CommonHelper::generateUrl('SellerOrders', 'view', array($extendedChildData['opd_op_id'])) . '">#' . $extendedChildData['opd_order_id'] . '</a>') . '</div>';
                            } else {
                                /* [ RENTAL UPDATES */

                                $securityLabelHtml = '<small>' . sprintf(Labels::getLabel("LBL_Complete_security_amount_is_%s_._Owner's_recommendation_is_%s", $adminLangId), CommonHelper::displayMoneyFormat($order['totalSecurityAmount']), CommonHelper::displayMoneyFormat($order['recommended_security_refund'])) . '</small>';
                                /* ] */

                                $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                                $frm->setFormTagAttribute('class', 'web_form markAsShipped-js');
                                $frm->developerTags['colClassPrefix'] = 'col-md-';
                                $frm->developerTags['fld_default_col'] = 6;

                                $cmtFld = $frm->getField('comments');
                                $cmtFld->developerTags['col'] = 12;


                                $statusFld = $frm->getField('op_status_id');
                                $statusFld->setFieldTagAttribute('class', 'status-js fieldsVisibility-js');

                                $notiFld = $frm->getField('customer_notified');
                                $notiFld->setFieldTagAttribute('class', 'notifyCustomer-js');

                                $fldTracking = $frm->getField('tracking_number');
                                $fld = $frm->getField('opship_tracking_url');
                                if (null != $fld) {
                                    $fld->setFieldTagAttribute('pattern', 'https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)');
                                    $fld->setFieldTagAttribute('placeholder', 'https://example.com');
                                    $fld->htmlAfterField = '<small>' . Labels::getLabel("LBL_Example", $adminLangId) . ' : https://example.com' . '</small>';
                                }

                                /* RENTAL UPDATES */
                                if ($order['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                                    $fldSecRefType = $frm->getField('refund_security_type');
                                    $fldSecRefType->setFieldTagAttribute('completeamount', $order['totalSecurityAmount']);
                                    $fldSecRefType->developerTags['col'] = 4;

                                    $fldSecAmnt = $frm->getField('refund_security_amount');
                                    $fldSecAmnt->htmlAfterField = $securityLabelHtml;
                                    $fldSecAmnt->requirements()->setRange(0, $order['maxSecurityAmount']);
                                    $fldSecAmnt->developerTags['col'] = 4;

                                    $returnDateFld = $frm->getField('opd_mark_rental_return_date');
                                    $returnDateFld->developerTags['col'] = 4;

                                    if ($order['opd_mark_rental_return_date'] != '0000-00-00 00:00:00' && $order['opd_mark_rental_return_date'] != "") {
                                        $returnDateFld->value = $order['opd_mark_rental_return_date'];
                                        $returnDateFld->setFieldTagAttribute('disabled', 'disabled');
                                        if ($order['charge_total_amount'] > 0) {
                                            $returnDateFld->htmlAfterField = "<small class='note'>" . sprintf(Labels::getLabel("LBL_Note:_%s_Late_Charges_will_be_apply", $adminLangId), CommonHelper::displayMoneyFormat($order['charge_total_amount'], true, false)) . "</small>";
                                        }
                                    } else {
                                        $returnDateFld->value = date('Y-m-d h:i:s');
                                        if (strtotime($order['opd_rental_end_date']) < strtotime(date('Y-m-d h:i:s'))) {
                                            $returnDateFld->htmlAfterField = "<small class='note'>" . Labels::getLabel("LBL_Note:_Late_Charges_May_be_apply", $adminLangId) . "</small>";
                                        }
                                    }


                                    if ($completedOrderStatus != $order['op_status_id']) {
                                        $fldSecRefType->setWrapperAttribute('class', 'div_refund_security');
                                        $fldSecAmnt->setWrapperAttribute('class', 'div_refund_security');
                                        $returnDateFld->setWrapperAttribute('class', 'div_refund_security');
                                    }
                                }
                                /* ] */
                                echo $frm->getFormHtml();
                            }
                            ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div id="commsion--js" style="display: none;">
<table class="table table-bordered ">
    <thead>
        <tr class="text-center"><th colspan="2"><?php echo Labels::getLabel("LBL_Commission_Details", $adminLangId); ?></th></tr>
    </thead>
    <tbody>
        <?php echo $commisionHtml; ?>
    </tbody>
    
</table>
</div>

<?php if ($print) { ?>
    <script>
        window.print();
        window.onafterprint = function () {
            location.href = history.back();
        }
    </script>
<?php } ?>

<script>
    var canShipByPlugin = <?php echo (true === $canShipByPlugin ? 1 : 0); ?>;
    var orderShippedStatus = <?php echo OrderStatus::ORDER_SHIPPED; ?>;
    trackOrder = function (trackingNumber, courier, orderNumber) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('SellerOrders', 'orderTrackingInfo', [trackingNumber, courier, orderNumber]),
                    '',
                    function (res) {
                        $.facebox(res, 'medium-fb-width');
                        $(".medium-fb-width").parent('.popup').addClass('h-min-auto');
                    });
        });
    };
</script>

<style>
    .disabled-input{
        color: rgba(0,0,0,0.38) !important;
        background-color: rgba(0,0,0,0.05) !important;
        box-shadow: none;
        cursor: initial;
        border: transparent !important;
    }    
</style>
