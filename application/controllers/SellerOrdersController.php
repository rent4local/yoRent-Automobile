<?php

class SellerOrdersController extends SellerBaseController
{

    private $shippingService;
    private $trackingService;
    private $paymentPlugin;
    private $method = '';

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function rentals()
    {
        $data = FatApp::getPostedData();
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        if (!empty($data)) {
            $frmOrderSrch->fill($data);
        }
        $this->userPrivilege->canViewSales(UserAuthentication::getLoggedUserId());
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->_template->render(true, true);
    }

    public function orderProductSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $orderReportType = FatApp::getPostedData('orderReportType', FatUtility::VAR_INT, 0);
        
        $userId = $this->userParentId;
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_TAX .', opcharge_amount, 0)) as tax_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_SHIPPING .', opcharge_amount, 0)) as shipping_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT .', opcharge_amount, 0)) as reward_point_amount'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        // $srch->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'invoice.invoice_order_id = order_id', 'invoice');
        // $srch->joinTable(RequestForQuote::DB_TBL, 'LEFT JOIN', 'order_rfq_id = rfq.rfq_id', 'rfq');
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $srch->addOrder("op_id", "DESC");
        
        /* $addonSrch = clone $srch; */
        $addonSrch = new OrderProductSearch(0, true, true);
        $addonSrch->joinSellerProducts();
        $addonSrch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $addonSrch->addCondition('op_selprod_user_id', '=', $userId);
        $addonSrch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $addonSrch->doNotCalculateRecords();
        $addonSrch->addMultipleFields(['IFNULL(SUM(op_qty * op_unit_price + (opd_rental_security * op_qty) + (IF(op_tax_collected_by_seller > 0, IFNULL(tax_amount, 0) , 0 )) + IFNULL(shipping_amount, 0) + IFNULL(reward_point_amount, 0)), 0) as addonAmount', 'op_attached_op_id']);
        $addonSrch->addGroupBy('op_attached_op_id');
        $addonSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $addonSrch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        
        $srch->joinTable('(' . $addonSrch->getQuery() . ')', 'LEFT JOIN', 'op.op_id = addonQry.op_attached_op_id', 'addonQry');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);
        $srch->addMultipleFields(
            array('order_id', 'order_status', 'order_payment_status', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number', 'totCombinedOrders as totOrders', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_id', 'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_id', 'IF(opshipping_fulfillment_type = '. Shipping::FULFILMENT_PICKUP .' AND op_status_id = '. OrderStatus::ORDER_DELIVERED .', "'. Labels::getLabel('LBL_Picked', $this->siteLangId) .'", IFNULL(orderstatus_name, orderstatus_identifier)) as orderstatus_name', 'orderstatus_color_class', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'opd.*', 'op_status_id', '(op_qty * op_unit_price + (opd_rental_security * op_qty) + (IF(op_tax_collected_by_seller > 0, tax_amount , 0 )) + IF(opshipping_by_seller_user_id > 0, shipping_amount, 0) + reward_point_amount + addonQry.addonAmount) as vendorAmount', 'order_pmethod_id', 'addonQry.addonAmount as addon_amount', 'opshipping_type')
        );
        $srch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        if ($orderReportType == 0) {
            $op_status_id = FatApp::getPostedData('status', null, '0');
            if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")))) {
                $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
            } else {
                $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")), ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
            }
        } else {
            $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS", FatUtility::VAR_STRING, ''));
            switch ($orderReportType) {
                case Stats::COMPLETED_SALES: 
                    $srch->addCondition('op_status_id', 'IN', $completedOrderStatus);
                    break;
                case Stats::INPROCESS_SALES:
                    $completedOrderStatus[] = FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS');
                    $srch->addCondition('op_status_id', 'NOT IN', $completedOrderStatus);
                    break;
            }
        }
        

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('vendorAmount', '>=', $priceFrom);
            /* $srch->addMinPriceCondition($priceFrom); */
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('vendorAmount', '<=', $priceTo);
            /* $srch->addMaxPriceCondition($priceTo); */
        }
        
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        
        $oObj = new Orders();
        $addonAmountArr = [];
        $isMannulShipOrder = 1;
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
            if ($order['opshipping_type'] == Shipping::SHIPPING_SERVICES) {
                $isMannulShipOrder = 0;
            }
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (null !== $this->shippingService && $isMannulShipOrder == 0));
        /* ShipStation */

        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('pageSize', $pagesize);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('canEditInvoice', $this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];
        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId)));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'orderReportType');
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function orderSearchListing()
    {
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(
                    Labels::getLabel('MSG_INVALID_REQUEST_1', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getSubscriptionOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = UserAuthentication::getLoggedUserId();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addCondition('opcharge_order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);
        $srch->joinSubscription();
        $srch->joinOrderUser();
        //$srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.ossubs_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addOrder("ossubs_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
                array('order_id', 'order_user_id', 'user_autorenew_subscription', 'ossubs_id', 'ossubs_type', 'ossubs_plan_id', 'order_date_added', 'order_net_amount', 'ossubs_invoice_number', 'ossubs_subscription_name', 'ossubs_id', 'op_other_charges', 'ossubs_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ossubs_interval', 'ossubs_frequency', 'ossubs_till_date', 'ossubs_status_id', 'ossubs_from_date', 'order_language_id')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo);
        }
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['ossubs_id']);
            $order['charges'] = $charges;
        }
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $this->set('orders', $orders);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function viewOrder($op_id, $print = false)
    {
        $this->userPrivilege->canViewSales(UserAuthentication::getLoggedUserId());
        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $userId = $this->userParentId;

        $srch = $this->getOrderDetailsSrchObjById($op_id, true);
        $rs = $srch->getResultSet();
        $orderDetails = FatApp::getDb()->fetchAll($rs, 'op_id');
        
        if (!$orderDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }
        
        $orderDetail = $orderDetails[$op_id];
        unset($orderDetails[$op_id]);
        $attachedServices = $orderDetails;

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (null !== $this->shippingService && $orderDetail['opshipping_type'] == Shipping::SHIPPING_SERVICES));

        if (!empty($orderDetail["opship_orderid"])) {
            if (null != $this->shippingService && false === $this->shippingService->loadOrder($orderDetail["opship_orderid"])) {
                Message::addErrorMessage($this->shippingService->getError());
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
            $orderDetail['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
        }
        /* ShipStation */

        /* AfterShip */
        $this->loadTrackingService();
        $this->set('canTrackByPlugin', (null !== $this->trackingService));
        /* AfterShip */

        $codOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        $pickupOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'payatstore') {
            $pickupOrder = true;
        }

        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder, true);

        //$orderDetail['opshipping_by_seller_user_id'] = 4; // NEED to remove this line
        /* [ if shipping not handled by seller then seller can not update status to ship and delived */
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            if ($pickupOrder) {
                $processingStatuses = [];
            } else {
                $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
            }
        }
        if (!in_array($orderDetail['op_status_id'], OrderStatus::getRentalReturnAvailStatus())) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"));
        }

        $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END"));
        
        if ($orderDetail['op_status_id'] == FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END", FatUtility::VAR_INT, 17) || $orderDetail['op_status_id'] == FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS", FatUtility::VAR_INT, 8)) { 
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        } 
        /* ] */
        $isSelfPickup = false;
        if ($orderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            $isSelfPickup = true;
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;
        if (!empty($attachedServices)) {
            foreach ($attachedServices as $serviceId => $service) {
                $charges = $orderObj->getOrderProductChargesArr($serviceId);
                $attachedServices[$serviceId]['charges'] = $charges;
                $opChargesLog = new OrderProductChargeLog($serviceId);
                $taxOptions = $opChargesLog->getData($this->siteLangId);
                $attachedServices[$serviceId]['taxOptions'] = $taxOptions;
            }
        }
        
        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['op_order_id'], $orderDetail['op_id']);
        $orderDetail['pickupAddress'] = (isset($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id" => $op_id, 'seller_id' => $userId), 0, true);

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->siteLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        $data = array(
            'op_id' => $op_id,
            'op_status_id' => $orderDetail['op_status_id'],
            'tracking_number' => $orderDetail['opship_tracking_number']
        );

        /* RENTAL SECURITY UPDATES [ */
        $data['refund_security_type'] = $orderDetail['opd_refunded_security_type'];
        $data['refund_security_amount'] = $orderDetail['opd_refunded_security_amount'];

        $rental_security = $orderDetail['opd_rental_security']; 
        $op_qty = ($orderDetail['op_return_qty'] > 0) ? $orderDetail['op_return_qty'] : $orderDetail['op_qty'];
        $recommendedSecAmnt = $orderDetail['opd_refunded_security_amount'];
        $maxSecurityAmount = $orderDetail['opd_rental_price'] * $op_qty;

        $shippingUserId = $orderDetail['opshipping_by_seller_user_id'];
        $parentOrderDetail = array();
        if ($orderDetail['opd_extend_from_op_id'] > 0) {
            $pSrch = $this->getOrderDetailsSrchObjById($orderDetail['opd_extend_from_op_id']);
            $pRs = $pSrch->getResultSet();
            $parentOrderDetail = FatApp::getDb()->fetch($pRs);

            if (!empty($parentOrderDetail)) {
                if ($parentOrderDetail['op_qty'] == $parentOrderDetail['op_return_qty']) {
                    $data['refund_security_type'] = $parentOrderDetail['opd_refunded_security_type'];
                    $data['refund_security_amount'] = $parentOrderDetail['opd_refunded_security_amount'];
                    $recommendedSecAmnt = $parentOrderDetail['opd_refunded_security_amount'];
                    /* $maxSecurityAmount = $parentOrderDetail['opd_rental_price'] * $op_qty; */
                } else {
                    $maxSecurityAmount = $orderDetail['opd_rental_price'] * $op_qty;
                }
                $rental_security = $parentOrderDetail['opd_rental_security'];
            }
            $shippingUserId = $parentOrderDetail['opshipping_by_seller_user_id'];
            $this->set('parentOrderDetail', $parentOrderDetail);
        }

        $totalSecurityRefundAmount = $rental_security * $op_qty;
        $maxSecurityAmount = $maxSecurityAmount + $totalSecurityRefundAmount;
        $orderDetail['totalSecurityAmount'] = $totalSecurityRefundAmount;
        $orderDetail['maxSecurityAmount'] = $maxSecurityAmount;
        $orderDetail['recommended_security_refund'] = $recommendedSecAmnt;
        $data['return_qty'] = $orderDetail['op_return_qty'];
        /* ] */
        if($orderDetail['order_rfq_id'] > 0){
            $orderDetail['maxSecurityAmount'] = $rental_security;
            $orderDetail['totalSecurityAmount'] = $rental_security;
        }
        if ($orderDetail['charge_total_amount'] > 0 && $orderDetail['charge_status'] == BuyerLateChargesHistory::STATUS_EXCLUDE) {
            $data['apply_late_charges'] = 0;
        }

        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses, $isSelfPickup);
        $frm->fill($data);

        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        }
        $statusArr = [];
        if (!empty($orderDetail['comments'])) {
            $statusArr = array_keys($orderDetail['comments']);
            /* if (in_array(OrderStatus::ORDER_RENTAL_EXTENDED, $statusArr)) { */
            $extendChildOrderdata = OrderProductData::getOrderProductData($op_id, true);
            $this->set('extendedChildData', $extendChildOrderdata);
            /* } */
        }
        
        
        
        $orderStatusList = OrderStatus::orderStatusFlow($this->siteLangId, $orderDetail['order_payment_status'], $statusArr, true, FatUtility::int($orderDetail["opshipping_fulfillment_type"])); 
        
        $this->set('currentOrderStatusPriority', FatUtility::int(OrderStatus::getAttributesById($orderDetail['op_status_id'], 'orderstatus_priority')));
        $this->set('orderStatusList', $orderStatusList);
       
        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($orderDetail['op_order_id'], true);
        $oVfldsObj->addCondition('optvf_op_id', '=', $op_id);
        $oVfldsObj->doNotCalculateRecords();
        $oVfldsObj->doNotLimitRecords();
        $oVfldsObj->addMultipleFields(array('ovd_order_id', 'ovd_order_id', 'ovd_vflds_type', 'ovd_vflds_name', 'ovd_value', 'optvf_selprod_id', 'optvf_op_id', 'ovd_vfld_id'));
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);

        $attachmentArr = array();
        if (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1)) {
            $attachmentArr = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $orderDetail['order_order_id'], 0, -1, true, 0, false);
        }
        $this->set('statusAddressData', $this->getDropOffAddressData($orderDetail['comments']));
        $this->set('attachment', $attachmentArr);

        /* ---- check message thread --- */
        $thData = Thread::getMsgThreadByRecordId($op_id, Thread::THREAD_TYPE_ORDER_PRODUCT);
        $thread_id = 0;
        $message_id = 0;
        if (!empty($thData)) {
            $thread_id = $thData['thread_id'];
            $message_id = $thData['message_id'];
        }
        $this->set('thread_id', $thread_id);
        $this->set('message_id', $message_id);
        /*====*/

        $this->set('verificationFldsData', $verificationFldsData);
        $this->set('rentalReturnStatus', FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS", FatUtility::VAR_INT, 10));

        $this->set('orderDetail', $orderDetail);
        $this->set('attachedServices', $attachedServices);
        
        $this->set('processingStatuses', unserialize(FatApp::getConfig("CONF_PROCESSING_ORDER_STATUS")));
        $this->set('orderStatuses', $orderStatuses);
        $this->set('shippedBySeller', $shippedBySeller);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('frm', $frm);
        
        $statusForOrderForm = [
            OrderStatus::ORDER_CASH_ON_DELIVERY, 
            OrderStatus::ORDER_PAY_AT_STORE, 
            OrderStatus::ORDER_PAYMENT_CONFIRM, 
            OrderStatus::ORDER_IN_PROCESS, 
            OrderStatus::ORDER_SHIPPED, 
            OrderStatus::ORDER_DELIVERED, 
            OrderStatus::ORDER_READY_FOR_RENTAL_RETURN
        ];
        
        $this->set('displayForm', (in_array($orderDetail['op_status_id'], $statusForOrderForm) && $orderDetail['opd_product_type'] != SellerProduct::PRODUCT_TYPE_ADDON));
        if ($print) {
            $print = true;
        }
        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);
        if ($attachedFile = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $op_id)) {
            $this->set('statusAttachedFiles', CommonHelper::groupAttachmentFilesData($attachedFile, 'afile_record_subid'));
        }
        $this->_template->addJs(['seller-orders/page-js/view-order.js']);
        $this->_template->render();
    }

    private function getDropOffAddressData(array $orderStatusArr): array
    {
        if (empty($orderStatusArr)) {
            return [];
        }
        $addressDataArr = [];
        
        $rentalReturnStatus = FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END', FatUtility::VAR_INT, 17);
        $rentalReturnStatusArr = (isset($orderStatusArr[$rentalReturnStatus])) ? $orderStatusArr[$rentalReturnStatus] : [];
        if (empty($rentalReturnStatusArr)) {
            return [];
        }
        
        foreach ($rentalReturnStatusArr as $statusArr) {
            if ($statusArr['oshistory_fullfillment_type'] != OrderProduct::RENTAL_ORDER_RETURN_TYPE_DROP || 1 > $statusArr['oshistorydropoff_addr_id']) {
                continue;
            }
            $addObj = new Address($statusArr['oshistorydropoff_addr_id'], $this->siteLangId);
            $addressDataArr[$statusArr['oshistory_id']] = $addObj->getData(Address::TYPE_SHOP_PICKUP, $statusArr['op_shop_id']);
        }
        return $addressDataArr;
    }

    public function changeOrderStatus()
    {
        $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();

        if (!isset($post['op_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $applyLateCharges = FatApp::getPostedData('apply_late_charges', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('op_status_id', FatUtility::VAR_INT, 0);
        /* $manualShipping = FatApp::getPostedData('manual_shipping', FatUtility::VAR_INT, 0); */
        $trackingNumber = FatApp::getPostedData('tracking_number', FatUtility::VAR_STRING, '');
        if ($status == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS") && empty($trackingNumber) /* && 1 > $manualShipping */ ) {
            Message::addErrorMessage(Labels::getLabel('MSG_PLEASE_SELECT_SELF_SHIPPING', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();
        $op_id = FatUtility::int($post['op_id']);

        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Cancel_request_is_submitted_for_this_order', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderObj = new Orders();
        $srch = $this->getOrderDetailsSrchObjById($op_id);
        $srch->joinOrderCancellationRequest();
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($orderDetail["op_status_id"] != $post['op_status_id'] && $orderDetail['ocrequest_status'] != '' && $orderDetail['ocrequest_status'] == OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Buyer_Order_Cancellation_request_is_pending', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $codOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        $pickupOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'payatstore') {
            $pickupOrder = true;
        }

        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder, true);
        $extendFromOpId = $orderDetail['opd_extend_from_op_id'];
        $shippingUserId = $orderDetail['opshipping_by_seller_user_id'];
        $parentOrderDetail = array();
        if ($extendFromOpId > 0) {
            $pSrch = $this->getOrderDetailsSrchObjById($extendFromOpId);
            $pRs = $pSrch->getResultSet();
            $parentOrderDetail = FatApp::getDb()->fetch($pRs);
            $shippingUserId = $parentOrderDetail['opshipping_by_seller_user_id'];
        }

        /* [ if shipping not handled by seller then seller can not update status to ship and delived */
        $opshipping_by_seller_user_id = isset($shippingUserId) ? $shippingUserId : 0; // NEED To Update

        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $opshipping_by_seller_user_id)) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            if ($pickupOrder) {
                $processingStatuses = [];
            } else {
                $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
            }
        } else {
            if (!in_array($orderDetail['op_status_id'], OrderStatus::getRentalReturnAvailStatus())) {
                $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"));
            }
        }
        /* ] */
        /* [ maximum security refund calculation */
        $rental_security = $orderDetail['opd_rental_security'];
        $op_qty = $orderDetail['op_qty'];
        $recommendedSecAmnt = $orderDetail['opd_refunded_security_amount'];
        if (!empty($parentOrderDetail)) {
            $rental_security = $parentOrderDetail['opd_rental_security'];
            $op_qty = $parentOrderDetail['op_qty'];
            $recommendedSecAmnt = $parentOrderDetail['opd_refunded_security_amount'];
        }
        $totalSecurityRefundAmount = $rental_security * $op_qty;
        $orderDetail['totalSecurityAmount'] = $totalSecurityRefundAmount;
        /* ] */

        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $post = $frm->getFormDataFromArray($post);

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            $trackingCourierCode = '';
            $rentalSecurityRefundData = array();
            if ($orderDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                $rentalSecurityRefundData = array(
                    'refund_security_type' => $post['refund_security_type'],
                    'refund_security_amount' => $post['refund_security_amount']
                );
            }

            if ($post["op_status_id"] == OrderStatus::ORDER_SHIPPED) {
                //if (array_key_exists('manual_shipping', $post) && 0 < $post['manual_shipping'] && array_key_exists('opship_tracking_url', $post)) {
                /* if (array_key_exists('manual_shipping', $post) && 0 < $post['manual_shipping']) { */
                    $updateData = [
                        'opship_op_id' => $post['op_id'],
                        "opship_tracking_number" => $post['tracking_number'],
                            //"opship_tracking_url" => $post['opship_tracking_url'],
                    ];

                    if (array_key_exists('opship_tracking_url', $post)) {
                        $updateData['opship_tracking_url'] = $post['opship_tracking_url'];
                    }
                    if (array_key_exists('oshistory_courier', $post)) {
                        $trackingCourierCode = $post['oshistory_courier'];
                    }

                    if (!FatApp::getDb()->insertFromArray(OrderProductShipment::DB_TBL, $updateData, false, array(), $updateData)) {
                        LibHelper::dieJsonError(FatApp::getDb()->getError());
                    }
                /* } else {
                    $trackingCourierCode = '';
                    if ($orderDetail['opshipping_carrier_code'] != '') {
                        $trackingRelation = new TrackingCourierCodeRelation();
                        $trackData = $trackingRelation->getDataByShipCourierCode($orderDetail['opshipping_carrier_code']);
                        $trackingCourierCode = !empty($trackData['tccr_tracking_courier_code']) ? $trackData['tccr_tracking_courier_code'] : '';
                    }
                } */
            }
            $returnQty = (isset($post['return_qty']) && $post['return_qty'] > 0) ? $post['return_qty'] : 0;
            $trackingNumber = (isset($post["tracking_number"])) ? $post["tracking_number"] : "";
            $trackingURL = (isset($post["opship_tracking_url"])) ? $post["opship_tracking_url"] : "";
            $commentId = 0; // 
            if (!$orderObj->addChildProductOrderHistory($op_id, $this->userParentId, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $trackingNumber, 0, true, $trackingCourierCode, $rentalSecurityRefundData, '', 0, $returnQty, $commentId,$trackingURL)) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            /* [ upload files if attached */
            if (isset($_FILES['file'])) {
                $uploadedFiles = $_FILES['file']['tmp_name'];
                foreach ($uploadedFiles as $fileIndex => $uploadedFile) {
                    if (is_uploaded_file($_FILES['file']['tmp_name'][$fileIndex])) {
                        if (filesize($uploadedFile) > 10240000) {
                            $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                            if (true === MOBILE_APP_API_CALL) {
                                LibHelper::dieJsonError($message);
                            }
                            Message::addErrorMessage($message);
                            FatUtility::dieJsonError(Message::getHtml());
                        }

                        $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);
                        if (getimagesize($uploadedFile) === false && in_array($uploadedFileExt, array('.zip'))) {
                            $message = Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId);
                            if (true === MOBILE_APP_API_CALL) {
                                LibHelper::dieJsonError($message);
                            }
                            Message::addErrorMessage($message);
                            FatUtility::dieJsonError(Message::getHtml());
                        }

                        $fileHandlerObj = new AttachedFile();
                        if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'][$fileIndex], AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $op_id, $commentId, $_FILES['file']['name'][$fileIndex], -1, false)) {
                            if (true === MOBILE_APP_API_CALL) {
                                LibHelper::dieJsonError($fileHandlerObj->getError());
                            }
                            Message::addErrorMessage($fileHandlerObj->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }
                }
            }
            /* ] */


            /* [ UPDATE LATE CHARGES HISTORY FOR THIS ORDER */
            if ($post["op_status_id"] == OrderStatus::ORDER_RENTAL_RETURNED) {
                $opReturnDate = (isset($post['opd_mark_rental_return_date']) && $post['opd_mark_rental_return_date'] != '') ? $post['opd_mark_rental_return_date'] : date('Y-m-d h:i:s');
                $chargeStatus = ($applyLateCharges == 1) ? BuyerLateChargesHistory::STATUS_UNPAID : BuyerLateChargesHistory::STATUS_EXCLUDE;
                if (!$orderObj->updateLateChargesHistory($orderDetail['op_id'], $this->siteLangId, $opReturnDate, 0, $chargeStatus)) {
                    $db->rollbackTransaction();
                    Message::addErrorMessage(Labels::getLabel('MSG_Unable_to_update_late_charges_history', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
            /* ] */
        } else {
            Message::addErrorMessage(Labels::getLabel('M_ERROR_INVALID_REQUEST_2', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatshop']) && (OrderStatus::ORDER_DELIVERED == $post["op_status_id"] || OrderStatus::ORDER_COMPLETED == $post["op_status_id"]) && Orders::ORDER_PAYMENT_PAID != $orderDetail['order_payment_status']) {
            $orderProducts = new OrderProductSearch($this->siteLangId, true, true);
            $orderProducts->joinPaymentMethod();
            $orderProducts->addMultipleFields(['op_status_id']);
            $orderProducts->addCondition('op_order_id', '=', $orderDetail['order_id']);
            $orderProducts->addCondition('op_status_id', '!=', OrderStatus::ORDER_DELIVERED);
            $orderProducts->addCondition('op_status_id', '!=', OrderStatus::ORDER_COMPLETED);
            $rs = $orderProducts->getResultSet();
            if ($rs) {
                $childOrders = FatApp::getDb()->fetchAll($rs);
                if (empty($childOrders)) {
                    $updateArray = array('order_payment_status' => Orders::ORDER_PAYMENT_PAID);
                    $whr = array('smt' => 'order_id = ?', 'vals' => array($orderDetail['order_id']));
                    if (!FatApp::getDb()->updateFromArray(Orders::DB_TBL, $updateArray, $whr)) {
                        Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
        }
        /* CHECK AND UPDATE STATUS OF ATTACHED SERVICES */
        $mainOpId = ($extendFromOpId > 0) ? $extendFromOpId : $op_id;
        $addonProductIds = Orders::getAddonsIdsByProduct($mainOpId, true, true);
        if (!empty($addonProductIds)) {
            foreach ($addonProductIds as $key => $addonProduct) {
                if (!$orderObj->addChildProductOrderHistory($addonProduct, $this->userParentId, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $trackingNumber, 0, true, $trackingCourierCode)) {
                    Message::addErrorMessage($orderObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }
        /* ] */
        $db->commitTransaction();
        $this->set('op_id', $op_id);
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getOrderDetailsSrchObjById(int $opId = 0, $includeAddons = false)
    {
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinOrderProductShipment();
        $srch->joinLateChargesHistory();
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addOrderProductCharges();
        $srch->joinOrderCancellationRequest();
        $srch->addCondition('opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        if ($opId > 0) {
            $cnd = $srch->addCondition('op_id', '=', $opId);
            if ($includeAddons) {
               $cnd->attachCondition('op_attached_op_id', '=', $opId, 'OR');
            }
        }
        return $srch;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus = [], $isSelfPickup = false)
    {
        $frm = new Form('frmOrderComments');
        $orderStatusArr = Orders::getOrderProductStatusArr($this->siteLangId, $processingOrderStatus, $orderData['op_status_id']);
        if ($isSelfPickup && isset($orderStatusArr[OrderStatus::ORDER_DELIVERED])) {
            $orderStatusArr[OrderStatus::ORDER_DELIVERED] = Labels::getLabel('LBL_Picked', $this->siteLangId);
        }
        
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'op_status_id', $orderStatusArr, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        $fld->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Notify_Customer_by_email', $this->siteLangId), 'customer_notified', applicationConstants::getYesNoArr($this->siteLangId), '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired();
        
        $attr = [];
        $labelGenerated = false;
        if (isset($orderData['opship_tracking_number']) && !empty($orderData['opship_tracking_number'])) {
            $attr = [
                'disabled' => 'disabled'
            ];
            $labelGenerated = true;
        } /* else {
            $manualFld = $frm->addCheckBox(Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId), 'manual_shipping', 1, array(), false, 0);
            $manualShipUnReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId));
            $manualShipUnReqObj->setRequired(false);
            $manualShipReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId));
            $manualShipReqObj->setRequired(true);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'eq', 'manual_shipping', $manualShipReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'ne', 'manual_shipping', $manualShipUnReqObj);
        } */

        

        if (false === $labelGenerated) {
            $plugin = new Plugin();
            $afterShipData = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);
            
            $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->siteLangId), 'tracking_number', '', $attr)->requirements()->setRequired();
            $trackingUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingUnReqObj->setRequired(false);
            $trackingReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingReqObj->setRequired(true);
       
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'eq', 'tracking_number', $trackingReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'ne', 'tracking_number', $trackingUnReqObj);
            
            if ($afterShipData != false) {
                $shipmentTracking = new ShipmentTracking();
                $shipmentTracking->init($this->siteLangId);
                $shipmentTracking->getTrackingCouriers();
                $trackCarriers = $shipmentTracking->getResponse();
                
                $frm->addSelectBox(Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId), 'oshistory_courier', $trackCarriers, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirements()->setRequired();
                $trackCarrierFld = $frm->getField('oshistory_courier');

                $trackCarrierFldUnReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId));
                $trackCarrierFldUnReqObj->setRequired(false);

                $trackCarrierFldReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId));
                $trackCarrierFldReqObj->setRequired(true);
                
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'eq', 'oshistory_courier', $trackCarrierFldReqObj);
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'ne', 'oshistory_courier', $trackCarrierFldUnReqObj);
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId), 'opship_tracking_url', '', $attr)->requirements()->setRequired();
                $trackUrlFld = $frm->getField('opship_tracking_url');
                $trackUrlFld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_ENTER_THE_URL_TO_TRACK_THE_SHIPMENT.', $this->siteLangId) . '</small>';

                $trackingUrlUnReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId));
                $trackingUrlUnReqObj->setRequired(false);
                $trackingurlReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACK_THROUGH', $this->siteLangId));
                $trackingurlReqObj->setRequired(true);
                $trackingurlReqObj->setCustomErrorMessage(Labels::getLabel('LBL_Tracking_Url_is_required', $this->siteLangId));
                
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'eq', 'opship_tracking_url', $trackingurlReqObj);
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'ne', 'opship_tracking_url', $trackingUrlUnReqObj);
            }
        }
        
        $frm->addHiddenField('', 'op_id', 0);
        /* [ Rental Security Refund fields */
        if ($orderData['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
            $frm->addIntegerField(Labels::getLabel('LBL_Received_Qty', $this->siteLangId), 'return_qty', '')->requirements()->setRequired();
            $qtyFldUnReqFld = new FormFieldRequirement('return_qty', Labels::getLabel('LBL_Received_Qty', $this->siteLangId));
            $qtyFldUnReqFld->setRequired(false);

            $qtyFldReqFld = new FormFieldRequirement('return_qty', Labels::getLabel('LBL_Received_Qty', $this->siteLangId));
            $qtyFldReqFld->setRequired(true);
            $qtyFldReqFld->setIntPositive(true);
            $qtyFldReqFld->setRange(1, ($orderData['op_return_qty'] > 0) ? $orderData['op_return_qty'] : $orderData['op_qty']);
            /* $qtyFldReqFld->setCustomErrorMessage(Labels::getLabel('LBL_Received_Qty_is_required_and_cannot_be_greater_then_returned_quantity_by_buyer', $this->siteLangId)); */
            
            $refundTypeOptions = Orders::refundSecurityTypeOptions($this->siteLangId);
            $frm->addSelectBox(Labels::getLabel('LBL_Refund_Type', $this->siteLangId), 'refund_security_type', $refundTypeOptions, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired();
            $refundTypeUnReqFld = new FormFieldRequirement('refund_security_type', Labels::getLabel('LBL_Refund_Type', $this->siteLangId));
            $refundTypeUnReqFld->setRequired(false);

            $refundTypeReqFld = new FormFieldRequirement('refund_security_type', Labels::getLabel('LBL_Refund_Type', $this->siteLangId));
            $refundTypeReqFld->setRequired(true);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'eq', 'refund_security_type', $refundTypeReqFld);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'ne', 'refund_security_type', $refundTypeUnReqFld);

            $frm->addFloatField(Labels::getLabel('LBL_Refund_Security_Amount', $this->siteLangId), 'refund_security_amount')->requirements()->setRequired();

            $sAmountUnReqObj = new FormFieldRequirement('refund_security_amount', Labels::getLabel('LBL_Refund_Security_Amount', $this->siteLangId));
            $sAmountUnReqObj->setRequired(false);

            $sAmountReqObj = new FormFieldRequirement('refund_security_amount', Labels::getLabel('LBL_Refund_Security_Amount', $this->siteLangId));
            $sAmountReqObj->setRequired(true);
            $sAmountReqObj->setFloatPositive();
            $sAmountReqObj->setRange('0.00000', $orderData['totalSecurityAmount']);

            $frm->addTextBox(Labels::getLabel('LBL_Return_Date', $this->siteLangId), 'opd_mark_rental_return_date', '', array())->requirements()->setRequired();

            $returnDateUnReqFld = new FormFieldRequirement('opd_mark_rental_return_date', Labels::getLabel('LBL_Return_Date', $this->siteLangId));
            $returnDateUnReqFld->setRequired(false);

            $returnDateReqFld = new FormFieldRequirement('opd_mark_rental_return_date', Labels::getLabel('LBL_Return_Date', $this->siteLangId));
            $returnDateReqFld->setRequired(true);

            /* [ LATE CHARGES CHECKBOX */
            
            if (($orderData['charge_amount'] > 0 || (FatUtility::int($orderData['charge_op_id']) == 0 && ( strtotime(date('Y-m-d', strtotime($orderData['opd_rental_end_date']))) < strtotime(date('Y-m-d'))))) && FatApp::getConfig('CONF_ENABLE_RENTAL_PRODUCT_LATE_CHARGES_MODULE', FatUtility::VAR_INT, 0)) {
                $frm->addCheckBox(Labels::getLabel('LBL_Deduct_Late_Charges_from_buyer', $this->siteLangId), 'apply_late_charges', 1, array(), true);
            }
            
            /* ] */
            

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'eq', 'refund_security_amount', $sAmountReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'ne', 'refund_security_amount', $sAmountUnReqObj);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'eq', 'opd_mark_rental_return_date', $returnDateReqFld);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'ne', 'opd_mark_rental_return_date', $returnDateUnReqFld);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'eq', 'return_qty', $qtyFldReqFld);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), 'ne', 'return_qty', $qtyFldUnReqFld);
            
            $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Product_Images', $this->siteLangId), 'file[]', array('accept' => 'image/*,.zip', 'multiple' => 'multiple'));
            $fileFld->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
            $fileFld->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed._You_Can_Upload_multiple_files_At_same_time', $this->siteLangId) . '</span>';
            
        }
        /* Rental Security Refund fields ] */
        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->siteLangId), 'comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function loadShippingService()
    {
        /* Return if already loaded. */
        if (!empty($this->shippingService)) {
            return;
        }

        $plugin = new Plugin();
        $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);

        /* Carry on with default functionality if plugin not active. */
        if (false === $keyName) {
            return;
        }

        $this->shippingService = PluginHelper::callPlugin($keyName, [$this->siteLangId], $error, $this->siteLangId, false);
        if (false === $this->shippingService) {
            if ('orderproductsearchlisting' == strtolower($this->method)) {
                Message::addErrorMessage($error);
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
            }
        };
        if (false === $this->shippingService->init()) {
            if ('orderproductsearchlisting' == strtolower($this->method)) {
                Message::addErrorMessage($this->shippingService->getError());
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
            }
        }
    }

    private function loadTrackingService()
    {
        /* Return if already loaded. */
        if (!empty($this->trackingService)) {
            return;
        }

        $plugin = new Plugin();
        $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);

        /* Carry on with default functionality if plugin not active. */
        if (false === $keyName) {
            return;
        }

        $this->trackingService = PluginHelper::callPlugin($keyName, [$this->siteLangId], $error, $this->siteLangId, false);
        if (false === $this->trackingService) {
            Message::addErrorMessage($error);
            FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
        }

        if (false === $this->trackingService->init()) {
            Message::addErrorMessage($this->trackingService->getError());
            FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
        }
    }

    public function downloadDigitalFile(int $recordId, int $aFileId, int $fileType, $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $recordId) {
            FatUtility::exitWithErrorCode(404);
        }


        $attachFileRow = AttachedFile::getAttributesById($aFileId);

        /* files path[ */
        $folderName = AttachedFile::FILETYPE_SIGNATURE_IMAGE_PATH;
        /* ] */

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('sellerOrders', 'rentals'));
        }

        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }
    
    public function lateChargesHistory()
    {
        $this->set('frmSearch', $this->getSearchForm());
        $this->_template->render();
    }
    
    public function lateChargesSearchListing()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $srch = BuyerLateChargesHistory::getSearchObject();
        $srch->joinTable(OrderProduct::DB_TBL, 'INNER JOIN', 'op_id = charge_op_id', 'op');
        $srch->joinTable(User::DB_TBL, 'INNER JOIN', 'buyer.user_id = charge_user_id', 'buyer');
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'buyer_cred.credential_user_id = buyer.user_id', 'buyer_cred');
        $srch->addMultipleFields(['charges.*', 'op_invoice_number', 'op_order_id', 'op_id', 'buyer.user_name as buyer_name']);
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        
        if (isset($post['keyword']) && trim($post['keyword']) != '') {
            $cnd = $srch->addCondition('op_invoice_number', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('op_order_id', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('buyer.user_name', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', '%'. trim($post['keyword']) .'%', 'OR');
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', '%'. trim($post['keyword']) .'%');
        }
        
        $srch->setPageNumber($page);
        $srch->addOrder('charge_status', 'ASC');
        $srch->addOrder('charge_op_id', 'DESC');
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $chargesListing = FatApp::getDb()->fetchAll($rs);

        $this->set('chargesListing', $chargesListing);
        $this->set('chargesSmountType', LateChargesProfile::getAmountType($this->siteLangId));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('rentalDurationType', ProductRental::durationTypeArr($this->siteLangId));
        $this->set('statusArr', BuyerLateChargesHistory::chargesStatusArr($this->siteLangId));
        $this->_template->render(false, false);
    }
    
    public function cancelPenaltyHistory()
    {
        $this->set('frmSearch', $this->getSearchForm());
        $this->_template->render();
    }
    
    public function cancelPenaltySearchListing()
    {
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        
        
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'IFNULL(sum(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_SHIPPING .', opcharge_amount, 0)), 0) as shipping_charges', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', 'op.op_id = opst.opsetting_op_id', 'opst');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = op.op_id', 'ops');
        $srch->addMultipleFields(array('ocrequest_refund_amount', 'ocrequest_hours_before_rental', 'opd_rental_start_date', 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'op_id', 'op_qty', 'op_unit_price', 'op_rounding_off', 'opd_rental_security', 'opcc.*', 'buyer.user_name as buyer_name', 'op_commission_percentage', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id'));
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addCondition('ocrequest_is_penalty_applicable', '=', applicationConstants::YES);
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        
        if (isset($post['keyword']) && trim($post['keyword']) != '') {
            $cnd = $srch->addCondition('op_invoice_number', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('op_order_id', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('buyer.user_name', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', '%'. trim($post['keyword']) .'%', 'OR');
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', '%'. trim($post['keyword']) .'%');
        }
        
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $chargesListing = FatApp::getDb()->fetchAll($rs);

        $this->set('chargesListing', $chargesListing);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmChargesSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addHiddenField('', 'page');
        
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        
        return $frm;
    }
}