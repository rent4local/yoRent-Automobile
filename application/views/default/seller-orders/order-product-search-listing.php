<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'order_id' => Labels::getLabel('LBL_Order_Id_Date', $siteLangId),
        'product' => Labels::getLabel('LBL_Ordered_Product', $siteLangId),
        'op_qty' => Labels::getLabel('LBL_Qty', $siteLangId),
        'total' => Labels::getLabel('LBL_Total', $siteLangId),
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
    $orderObj = new Orders();
    $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();
    $processingStatuses = array_diff($processingStatuses, [OrderStatus::ORDER_DELIVERED]);
    $processingStatuses = array_merge($processingStatuses, [OrderStatus::ORDER_PAYMENT_CONFIRM, OrderStatus::ORDER_CASH_ON_DELIVERY, OrderStatus::ORDER_PAY_AT_STORE]);

    foreach ($orders as $sn => $order) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));
        $orderDetailUrl = UrlHelper::generateUrl('sellerOrders', 'viewOrder', array($order['op_id']));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'order_id':
                    $txt = '<a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . $orderDetailUrl . '">';
                    $txt .= $order['op_invoice_number'];
                    $txt .= '</a><br/>' . FatDate::format($order['order_date_added']);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'product':
                    $txt = '<div class="item__description">';
                    if ($order['op_selprod_title'] != '') {
                        $txt .= '<div class="item__title">' . $order['op_selprod_title'] . '</div>';
                    }
                    $txt .= '<div class="item__sub_title">' . $order['op_product_name'] . '</div>';

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
                    $txt .= '</div>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'total':
                    $txt = '';
                    // $txt .= CommonHelper::displayMoneyFormat($order['order_net_amount']);
                    $txt .= CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount', false, User::USER_TYPE_SELLER) + $order['addon_amount']);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'status':
                    $orderStatus = ucwords($order['orderstatus_name']);
                    if (Orders::ORDER_PAYMENT_CANCELLED == $order["order_payment_status"]) {
                        $orderStatus = Labels::getLabel('LBL_CANCELLED', $siteLangId);
                        $labelClass = 'label-danger';
                    } else { 
                        $paymentMethodCode = Plugin::getAttributesById($order['order_pmethod_id'], 'plugin_code');
                        if (in_array(strtolower($paymentMethodCode), ['cashondelivery', 'payatstore'])) {
                            if (strtolower(trim($orderStatus)) != strtolower(trim($order['plugin_name'])) && !empty(trim($order['plugin_name']))) {
                                $orderStatus .= " - " . $order['plugin_name'];
                            }
                        }
                       
                        $labelClass = isset($classArr[$order['orderstatus_color_class']]) ? $classArr[$order['orderstatus_color_class']] : 'label-info';
                    } 
                    $td->appendElement('span', array('class' => 'label label-inline ' . $labelClass), $orderStatus . '<br>', true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);

                    $li = $ul->appendElement("li");
                    $li->appendElement(
                            'a', array(
                        'href' => $orderDetailUrl, 'class' => '',
                        'title' => Labels::getLabel('LBL_View_Order', $siteLangId)
                            ), '<i class="fa fa-eye"></i>', true
                    );

                    if (in_array($order['orderstatus_id'], $processingStatuses) && $canEdit && $order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                        $li = $ul->appendElement("li");
                        $li->appendElement(
                                'a', array(
                            'href' => UrlHelper::generateUrl('seller', 'cancelOrder', array($order['op_id'])), 'class' => '',
                            'title' => Labels::getLabel('LBL_Cancel_Order', $siteLangId)
                                ), '<i class="fas fa-times"></i>', true
                        );
                    }

                    $shipBySeller = CommonHelper::canAvailShippingChargesBySeller($order['op_selprod_user_id'], $order['opshipping_by_seller_user_id']);
                    if ($order['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON && $order['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL && $shipBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $order['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $order['order_payment_status'])) {
                        $li = $ul->appendElement("li");
                        if (empty($order['opship_response']) && empty($order['opship_tracking_number'])) {
                            $li->appendElement('a', array('href' => 'javascript:void(0)', 'onclick' => 'generateLabel(' . $order['op_id'] . ')', 'title' => Labels::getLabel('LBL_GENERATE_LABEL', $siteLangId)), '<i class="fas fa-file-download"></i>', true);
                        } elseif (!empty($order['opship_response'])) {
                            $li->appendElement('a', array('href' => UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$order['op_id']]), 'target' => '_blank', 'title' => Labels::getLabel('LBL_PREVIEW_LABEL', $siteLangId)), '<i class="fas fa-file-export"></i>', true);
                        }
                    }

                    // if ($order['order_is_rfq'] == applicationConstants::YES && in_array($order['order_payment_status'], Orders::getUnpaidStatus()) && $canEditInvoice && $order['op_status_id'] != FatApp::getConfig('CONF_DEFAULT_CANCEL_ORDER_STATUS')) {
                    //     if ($order['invoice_status'] != Invoice::INVOICE_IS_SHARED_WITH_BUYER && $order['rfq_status'] != RequestForQuote::REQUEST_QUOTE_VALIDITY) {
                    //         $li = $ul->appendElement("li");
                    //         $li->appendElement(
                    //                 'a', array('href' => CommonHelper::generateUrl('invoices', 'create', [$order['order_id']]), 'class' => '',
                    //             'title' => Labels::getLabel('LBL_Create_Invoice', $siteLangId)), '<i class="fa fa-file-invoice"></i>', true
                    //         );
                    //     }
                    //     if ($order['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER && $order['rfq_status'] != RequestForQuote::REQUEST_QUOTE_VALIDITY) {
                    //         $li = $ul->appendElement("li");
                    //         $li->appendElement(
                    //                 'a', array('href' => CommonHelper::generateUrl('invoices', 'view', [$order['order_id']]), 'class' => '',
                    //             'title' => Labels::getLabel('LBL_View_Invoice', $siteLangId)), '<i class="fa fa-file-invoice"></i>', true
                    //         );
                    //     }
                    // }

                    break;
                default:
                    $td->appendElement('plaintext', array(), '' . $order[$key], true);
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
