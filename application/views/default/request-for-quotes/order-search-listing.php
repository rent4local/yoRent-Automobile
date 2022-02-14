<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $buyerUpdateStatus = unserialize(FatApp::getConfig('CONF_DELIVERED_MARK_STATUS_FOR_BUYER', FatUtility::VAR_STRING, ''));
    $arr_flds = array(
        'order_id' => Labels::getLabel('LBL_Order_ID_Date', $siteLangId),
        'product' => Labels::getLabel('LBL_Details', $siteLangId),
        'total' => Labels::getLabel('LBL_Total', $siteLangId),
        'opd_sold_or_rented' => Labels::getLabel('LBL_Order_Type', $siteLangId),
        'status' => Labels::getLabel('LBL_Status', $siteLangId),
        'action' => '',
    );
    $tableClass = '';
    if (0 < count($orders)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = 0;
    // $canCancelOrder = true;
    $canReturnRefund = true;
    foreach ($orders as $sn => $order) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));
        $orderDetailUrl = UrlHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'], $order['op_id']));

        // $canCancelOrder = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderCancellationStatuses()));
        $canReturnRefund = (in_array($order["op_status_id"], (array) Orders::getBuyerAllowedOrderReturnStatuses()));

        $isValidForReview = false;
        if (in_array($order["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
            $isValidForReview = true;
        }
        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'order_id':
                    $txt = '<a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . $orderDetailUrl . '">';
                    if ($order['totOrders'] > 1) {
                        $txt .= $order['op_invoice_number'];
                    } else {
                        $txt .= $order['order_id'];
                    }
                    $txt .= '</a><br/>' . FatDate::format($order['order_date_added']);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'product':
                    $txt = '<div class="item__description">';
                    if ($order['op_selprod_title'] != '') {
                        $txt .= '<div class="item__title">' . $order['op_selprod_title'] . '</div>';
                    }
                    $txt .= '<div class="item__sub_title">' . $order['op_product_name'] . ' (' . Labels::getLabel('LBL_Qty', $siteLangId) . ': ' . $order['op_qty'] . ')</div>';
                    $txt .= '<div class="item__brand">';
                    if (!empty($order['op_brand_name'])) {
                        $txt .= Labels::getLabel('LBL_Brand', $siteLangId) . ': ' . $order['op_brand_name'];
                    }
                    if (!empty($order['op_brand_name']) && !empty($order['op_selprod_options'])) {
                        $txt .= ' | ';
                    }
                    if ($order['op_selprod_options'] != '') {
                        $txt .= $order['op_selprod_options'];
                    }
                    $txt .= '</div>';
                    if ($order['totOrders'] > 1) {
                        $txt .= '<div class="item__specification">' . Labels::getLabel('LBL_Part_combined_order', $siteLangId) . ' <a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . UrlHelper::generateUrl('Buyer', 'viewOrder', array($order['order_id'])) . '">' . $order['order_id'] . '</div>';
                    }
                    $txt .= '</div>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'total':
                    $txt = '';
                    $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order));
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'opd_sold_or_rented':
                    $td->appendElement('plaintext', array(), '' . $orderTypeArr[$order[$key]], true);
                    break;
                case 'status':
                    $orderStatus = ucwords($order['orderstatus_name']);
                    if (Orders::ORDER_PAYMENT_CANCELLED == $order["order_payment_status"]) {
                        $orderStatus = Labels::getLabel('LBL_CANCELLED', $siteLangId);
                        $labelClass = 'label-danger';
                    } else {
                        $pMethod = '';
                        $paymentMethodCode = Plugin::getAttributesById($order['order_pmethod_id'], 'plugin_code');
                        if (in_array(strtolower($paymentMethodCode), ['cashondelivery', 'payatstore'])) {
                            if ($orderStatus != $order['plugin_name']) {
                                $orderStatus .= " - " . $order['plugin_name'];
                            }
                        }
                        $labelClass = isset($classArr[$order['orderstatus_color_class']]) ? $classArr[$order['orderstatus_color_class']] : 'label-info';
                    }

                    $td->appendElement('span', array('class' => 'label label-inline ' . $labelClass), $orderStatus . '<br>', true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                    // $opCancelUrl = UrlHelper::generateUrl('Buyer', 'orderCancellationRequest', array($order['op_id']));
                    $now = time(); // or your date as well
                    $orderDate = strtotime($order['order_date_added']);
                    $datediff = $now - $orderDate;
                    $returnDayDiff = $now - strtotime($order['op_delivery_time']);

                    if ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                        $daysSpent = ceil($datediff / (60 * 60)); // Cancel and Return Age in Hours
                        $deliveredDaysSpent = ceil($returnDayDiff / (60 * 60)); // Cancel and Return Age in Hours
                    } else {
                        $daysSpent = round($datediff / (60 * 60 * 24)); // Cancel and Return Age in Days
                        $deliveredDaysSpent = round($returnDayDiff / (60 * 60 * 24)); // Cancel and Return Age in Days
                    }
                    $returnAge = !empty($order['return_age']) ? $order['return_age'] : FatApp::getConfig("CONF_DEFAULT_RETURN_AGE", FatUtility::VAR_INT, 7);

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                            'a', array(
                        'href' => $orderDetailUrl, 'class' => '',
                        'title' => Labels::getLabel('LBL_View_Order', $siteLangId)
                            ), '<i class="fa fa-eye"></i>', true
                    );

                    if($order['invoice_status'] ==  Invoice::INVOICE_IS_SHARED_WITH_BUYER && Orders::ORDER_PAYMENT_PAID != $order["order_payment_status"] && $order['rfq_status'] != RequestForQuote::REQUEST_QUOTE_VALIDITY){
                        $payUrl = UrlHelper::generateUrl('RfqCheckout', 'index', array($order['order_id']));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => $payUrl, 'class' => '',
                            'title' => Labels::getLabel('LBL_Pay_Now', $siteLangId)
                                ), '<i class="fa fa-file-invoice"></i>', true
                        );
                    }
                    // if (!FatApp::getConfig('CONF_ALLOW_RENTAL_ORDER_CANCEL_FROM_BUYER_END', FatUtility::VAR_INT, 0) && $order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                    //     $canCancelOrder = false;
                    // }

                    // if ($canCancelOrder && false === OrderCancelRequest::getCancelRequestById($order['op_id']) && $order['cancellation_age'] >= $daysSpent && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                    //     $li = $ul->appendElement("li");
                    //     $li->appendElement(
                    //             'a', array(
                    //         'href' => $opCancelUrl, 'class' => '',
                    //         'title' => Labels::getLabel('LBL_Cancel_Order', $siteLangId)
                    //             ), '<i class="fas fa-times"></i>', true
                    //     );
                    // }
                    $canSubmitFeedback = Orders::canSubmitFeedback($order['order_user_id'], $order['order_id'], $order['op_selprod_id']);
                    if ($canSubmitFeedback && $isValidForReview && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                        $opFeedBackUrl = UrlHelper::generateUrl('Buyer', 'orderFeedback', array($order['op_id']));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => $opFeedBackUrl, 'class' => '',
                            'title' => Labels::getLabel('LBL_Feedback', $siteLangId)
                                ), '<i class="fa fa-star"></i>', true
                        );
                    }
                    
                    $deliveredMarkedByBuyer = false; 
                    if ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $order['deliveredMarkedBy'] == $order['order_user_id']) {
                        $deliveredMarkedByBuyer = true; 
                    }

                    if ($canReturnRefund && ($order['return_request'] == 0 && $order['cancel_request'] == 0) && $returnAge >= $deliveredDaysSpent && $order['opd_extend_from_op_id'] < 1 && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON && !$deliveredMarkedByBuyer) {
                        $opRefundRequestUrl = UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($order['op_id']));
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => $opRefundRequestUrl, 'class' => '',
                            'title' => Labels::getLabel('LBL_Refund', $siteLangId)
                                ), '<i class="fas fa-dollar-sign"></i>', true
                        );
                    }

                    // if ($order['opd_sold_or_rented'] != applicationConstants::PRODUCT_FOR_RENT) {
                    //     $cartUrl = UrlHelper::generateUrl('cart');
                    //     $li = $ul->appendElement("li");
                    //     $li->appendElement(
                    //             'a', array(
                    //         'href' => 'javascript:void(0)', 'onClick' => 'return addItemsToCart("' . $order['order_id'] . '");',
                    //         'title' => Labels::getLabel('LBL_Re-Order', $siteLangId)
                    //             ), '<i class="fa fa-cart-plus"></i>', true
                    //     );
                    // }

                    if (!$order['order_deleted'] && !$order["order_payment_status"] && 'TransferBank' == $order['plugin_code']) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => UrlHelper::generateUrl('Buyer', 'viewOrder', [$order['order_id']]),
                            'title' => Labels::getLabel('LBL_ADD_PAYMENT_DETAIL', $siteLangId)
                                ), '<i class="fas fa-box-open"></i>', true
                        );
                    }

                    /* if (in_array($order["op_status_id"], $buyerUpdateStatus) && $order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && (!in_array(FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"), $order['status_history'])) && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => "javascript:void(0);",
                            'onClick' => 'markOrderDeliveredOrReturn(' . $order['op_id'] . ')',
                            'title' => Labels::getLabel('LBL_Mark_Delivered_Or_Place_Return_Request', $siteLangId)
                                ), '<i class="fas fa-truck"></i>', true
                        );
                    } */
                    
                    /* $rentalEndDate = date('Y-m-d', strtotime($order['opd_rental_end_date']));
                    if (in_array($order["op_status_id"], $statusForReadyToReturn) && $order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) { 
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => "javascript:void(0);",
                            'onClick' => 'markOrderReadyForReturn(' . $order['op_id'] . ')',
                            'title' => Labels::getLabel('LBL_Mark_Order_Ready_for_Return', $siteLangId)
                                ), '<i class="fas fa-truck"></i>', true
                        );
                    } */

                    break;
                default:
                    $td->appendElement('plaintext', array(), $order[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($orders) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToOrderSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
