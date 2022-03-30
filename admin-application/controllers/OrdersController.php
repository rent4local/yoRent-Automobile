<?php

class OrdersController extends AdminBaseController
{

    private $shippingService;

    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewOrders($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditOrders($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);

        $this->shippingService = (object) [];
    }

    /**
     * loadShippingService
     *
     * @return void
     */
    private function loadShippingService()
    {
        // Return if already loaded.
        if (!empty($this->shippingService)) {
            return;
        }

        $plugin = new Plugin();
        $this->keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);

        $this->shippingService = PluginHelper::callPlugin($this->keyName, [$this->adminLangId], $error, $this->adminLangId);
        if (false === $this->shippingService) {
            Message::addErrorMessage($error);
            FatApp::redirectUser(UrlHelper::generateUrl("Orders"));
        }

        if (false === $this->shippingService->init()) {
            Message::addErrorMessage($this->shippingService->getError());
            FatApp::redirectUser(UrlHelper::generateUrl("Orders"));
        }
    }

    public function index()
    {
        $this->objPrivilege->canViewOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, false, applicationConstants::ORDER_TYPE_SALE);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['keyword'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('deletedOrderAction', 'deletedOrders');
        $this->set('isRentalOrder', false);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, false, applicationConstants::ORDER_TYPE_RENT);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['keyword'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('deletedOrderAction', 'deletedRentalOrders');
        $this->set('isRentalOrder', true);
        $this->_template->render(true, true, 'orders/index.php');
    }

    public function deletedOrders()
    {
        $this->objPrivilege->canViewOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, true, applicationConstants::ORDER_TYPE_SALE);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['keyword'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('backOrderAction', 'index');
        $this->_template->render();
    }

    public function deletedRentalOrders()
    {
        $this->objPrivilege->canViewOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, true, applicationConstants::ORDER_TYPE_RENT);
        $data = FatApp::getPostedData();
        if ($data) {
            $data['keyword'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->set('backOrderAction', 'rental');
        $this->_template->render(true, true, 'orders/deleted-orders.php');
    }

    public function search()
    {
        $this->objPrivilege->canViewOrders();
        $orderType = FatApp::getPostedData('order_product_for', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, false, $orderType);
        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new OrderSearch();
        $srch->joinTable(OrderProduct::DB_TBL, 'INNER JOIN', 'op_order_id = order_id', 'op');
        $srch->joinTable(OrderProductData::DB_TBL, 'INNER JOIN', 'op_id = opd_op_id AND opd_sold_or_rented = ' . $orderType, 'opd');

        $srch->joinOrderBuyerUser();
        $srch->joinOrderPaymentMethod($this->adminLangId);

        $srch->addOrder('order_date_added', 'DESC');
        $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addGroupBy('order_id');
        $srch->addMultipleFields(array('order_id', 'order_date_added', 'order_payment_status', 'order_status', 'buyer.user_id', 'buyer.user_name as buyer_user_name', 'buyer_cred.credential_email as buyer_email', 'order_net_amount', 'order_wallet_amount_charge', 'order_pmethod_id', 'IFNULL(plugin_name, plugin_identifier) as plugin_name', 'plugin_code', 'order_is_wallet_selected', 'order_deleted', 'order_cart_data'));

        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
        }

        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, -1);
        if ($user_id) {
            $srch->addCondition('buyer.user_id', '=', $user_id);
        }

        if (isset($post['order_payment_status']) && $post['order_payment_status'] != '') {
            $order_payment_status = FatUtility::int($post['order_payment_status']);
            $srch->addCondition('order_payment_status', '=', $order_payment_status);
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
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addMaxPriceCondition($priceTo);
        }

        $isDeleted = FatApp::getPostedData('is_deleted', FatUtility::VAR_INT, 0);
        if (!empty($isDeleted)) {
            $srch->addCondition('order_deleted', '=', applicationConstants::YES);
            $this->set("deletedOrders", true);
        } else {
            $srch->addCondition('order_deleted', '=', applicationConstants::NO);
            $this->set("deletedOrders", false);
        }

        $rs = $srch->getResultSet();
        $ordersList = FatApp::getDb()->fetchAll($rs);

        $this->set("ordersList", $ordersList);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());

        $this->set('canViewSellerOrders', $this->objPrivilege->canViewSellerOrders($this->admin_id, true));
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->set('orderType', $orderType);
        $this->_template->render(false, false);
    }

    public function view($order_id)
    {
        $this->objPrivilege->canViewOrders();

        $srch = new OrderSearch($this->adminLangId);
        $srch->joinOrderPaymentMethod();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinOrderBuyerUser();
        $srch->addMultipleFields(
                array(
                    'order_id', 'order_user_id', 'order_date_added', 'order_payment_status', 'order_tax_charged', 'order_site_commission',
                    'order_reward_point_value', 'order_volume_discount_total', 'buyer.user_name as buyer_user_name',
                    'buyer_cred.credential_email as buyer_email', 'buyer.user_dial_code', 'buyer.user_phone as buyer_phone', 'order_net_amount',
                    'order_shippingapi_name', 'order_pmethod_id', 'ifnull(plugin_name,plugin_identifier)as plugin_name',
                    'order_discount_total', 'plugin_code', 'order_is_wallet_selected', 'order_reward_point_used',
                    'order_deleted', 'order_rounding_off', 'order_product_type','order_order_id','order_rfq_id', 'order_is_rfq', 'order_late_charges'
                )
        );
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        $rs = $srch->getResultSet();
        $order = FatApp::getDb()->fetch($rs);
        if (!$order) {
            Message::addErrorMessage(Labels::getLabel('MSG_Order_Data_Not_Found', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl("Orders"));
        }


        $opSrch = new OrderProductSearch($this->adminLangId, false, true, true);
        $opSrch->joinShippingCharges();
        $opSrch->joinLateChargesHistory();
        $opSrch->joinAddress();
        $opSrch->joinTable(OrderProductShipment::DB_TBL, 'LEFT JOIN', OrderProductShipment::DB_TBL_PREFIX . 'op_id = op.op_id', 'opship');
        $opSrch->addCountsOfOrderedProducts();
        $opSrch->addOrderProductCharges();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $opSrch->addCondition('op.op_order_id', '=', $order['order_id']);

        $opSrch->addMultipleFields(
                array(
                    'op_id', 'op_selprod_user_id', 'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name',
                    'op_qty', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model',
                    'op_shop_id', 'op_shop_name', 'op_shop_owner_name', 'op_shop_owner_email', 'op_shop_owner_phone_code', 'op_shop_owner_phone', 'op_unit_price',
                    'totCombinedOrders as totOrders', 'op_shipping_duration_name', 'op_shipping_durations', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'op_other_charges', 'op_product_tax_options', 'ops.*', 'opship.*', 'addr.*', 'ts.state_code', 'tc.country_code', 'op_rounding_off', 'opd.*', 'IFNULL(latecharge.charge_total_amount, 0) as charge_total_amount', 'op_commission_charged', 'op_refund_commission', 'op_commission_percentage', 'op_refund_qty', 'op_commission_include_shipping', 'op_commission_include_tax', 'op_commission_on_security', 'op_tax_collected_by_seller'
                )
        );

        $opRs = $opSrch->getResultSet();
        $order['products'] = FatApp::getDb()->fetchAll($opRs, 'op_id');
        // CommonHelper::printArray($order['products'], true);
        $orderObj = new Orders($order['order_id']);
        $orderDurationDiscountTotal = 0;

        $charges = $orderObj->getOrderProductChargesByOrderId($order['order_id']);
        $isRentalOrder = false;
        foreach ($order['products'] as $opId => $opVal) {
            $order['products'][$opId]['charges'] = $charges[$opId];
            $opChargesLog = new OrderProductChargeLog($opId);
            $taxOptions = $opChargesLog->getData($this->adminLangId);
            $order['products'][$opId]['taxOptions'] = $taxOptions;
            $orderDurationDiscountTotal += $opVal['opd_rental_duration_discount'];
            if ($opVal['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) {
                $isRentalOrder = true;    
            }
            

            if (!empty($opVal["opship_orderid"])) {
                $this->loadShippingService();
                if (false === $this->shippingService->loadOrder($opVal["opship_orderid"])) {
                    Message::addErrorMessage($this->shippingService->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl("Orders"));
                }
                $order['products'][$opId]['thirdPartyorderInfo'] = $this->shippingService->getResponse();
            }
        }

        $addresses = $orderObj->getOrderAddresses($order['order_id']);
        $order['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $order['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $order['comments'] = $orderObj->getOrderComments($this->adminLangId, array("order_id" => $order['order_id']));
        $order['payments'] = $orderObj->getOrderPayments(array("order_id" => $order['order_id']));


        $attachmentArr = array();
        if(FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1)) {
            $attachmentArr = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $order['order_order_id'], 0, -1, true, 0, false);
        }
        $this->set('attachment', $attachmentArr);


        $frm = $this->getPaymentForm($this->adminLangId, $order['order_id']);

        $this->set('isRentalOrder', $isRentalOrder);
        $this->set('frm', $frm);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('order', $order);
        $this->set('orderDurationDiscountTotal', $orderDurationDiscountTotal);

        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($order['order_id'],true);
        $oVfldsObj->doNotCalculateRecords();
        $oVfldsObj->doNotLimitRecords();
        $oVfldsObj->addMultipleFields(array('ovd_order_id', 'ovd_order_id', 'ovd_vflds_type', 'ovd_vflds_name', 'ovd_value', 'optvf_selprod_id','optvf_op_id','ovd_vfld_id'));
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);
        $this->set('verificationFldsData', $verificationFldsData);

        $this->_template->render();
    }

    public function listChildOrderStatusLog($op_id)
    {
        $this->objPrivilege->canViewOrders();
        $op_id = FatUtility::int($op_id);
        $orderObj = new Orders();
        $comments = $orderObj->getOrderComments($this->adminLangId, array("op_id" => $op_id));
        //CommonHelper::printArray( $comments );
        $this->set('comments', $comments);
        $this->_template->render(false, false);
    }

    public function updatePayment()
    {
        $this->objPrivilege->canEditOrders();
        $frm = $this->getPaymentForm($this->adminLangId);

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderId = $post['opayment_order_id'];

        if ($orderId == '' || $orderId == null) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderSearch($this->adminLangId);
        $srch->joinOrderPaymentMethod();
        $srch->addMultipleFields(array('plugin_code'));
        $srch->addCondition('order_id', '=', $orderId);
        $srch->addCondition('order_type', '=', Orders::ORDER_PRODUCT);
        $rs = $srch->getResultSet();
        $order = FatApp::getDb()->fetch($rs);
        if (!empty($order) && array_key_exists('plugin_code', $order) && 'CashOnDelivery' == $order['plugin_code']) {
            Message::addErrorMessage(Labels::getLabel('LBL_COD_orders_are_not_eligible_for_payment_status_update', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderPaymentObj = new OrderPayment($orderId, $this->adminLangId);
        if (!$orderPaymentObj->addOrderPayment($post["opayment_method"], $post['opayment_gateway_txn_id'], $post["opayment_amount"], $post["opayment_comments"])) {
            Message::addErrorMessage($orderPaymentObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Payment_Details_Added_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function Cancel($order_id)
    {
        $this->objPrivilege->canEditOrders();

        $orderObj = new Orders();
        $order = $orderObj->getOrderById($order_id);

        if ($order == false) {
            Message::addErrorMessage(Labels::getLabel('LBL_Error:_Please_perform_this_action_on_valid_record.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedCancellationArr = Orders::getBuyerAllowedOrderCancellationStatuses();
        $srch = new OrderProductSearch(0, true);
        $srch->addMultipleFields(array('op.op_status_id', 'o.order_id'));
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('op_status_id', 'NOT IN', $allowedCancellationArr);
        $opDetails = FatApp::getDb()->fetchAll($srch->getResultSet());
        if (!empty($opDetails)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Error:_Orders_that_are_completed_cannot_be_Cancelled.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($order["order_payment_status"]) {
            if (!$orderObj->addOrderPaymentHistory($order_id, Orders::ORDER_PAYMENT_CANCELLED, Labels::getLabel('MSG_Order_Cancelled', $order['order_language_id']), 1)) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            if (!$orderObj->refundOrderPaidAmount($order_id, $order['order_language_id'])) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }

            $pluginKey = Plugin::getAttributesById($order['order_pmethod_id'], 'plugin_code');
            $paymentMethodObj = new PaymentMethods();
            if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->adminLangId)) {
                $orderProducts = $orderObj->getChildOrders(array('order_id' => $order_id), $order['order_type'], $order['order_language_id']);

                foreach ($orderProducts as $op) {
                    if (false == $paymentMethodObj->initiateRefund($op, PaymentMethods::REFUND_TYPE_CANCEL)) {
                        FatUtility::dieJsonError($paymentMethodObj->getError());
                    }

                    $resp = $paymentMethodObj->getResponse();
                    if (empty($resp)) {
                        FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $this->adminLangId));
                    }

                    // Debit from wallet if plugin/payment method support's direct payment to card of customer.
                    if (!empty($resp->id)) {
                        $childOrderInfo = $orderObj->getOrderProductsByOpId($op['op_id'], $this->adminLangId);
                        $txnAmount = $paymentMethodObj->getTxnAmount();
                        $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                        $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                        Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $op['op_id'], $resp->id);
                    }
                }
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Payment_Details_Cancelled_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function delete($order_id)
    {
        $this->objPrivilege->canEditOrders();

        $orderObj = new Orders();
        $order = $orderObj->getOrderById($order_id);

        if ($order == false) {
            Message::addErrorMessage(Labels::getLabel('LBL_Error:_Please_perform_this_action_on_valid_record.', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$order["order_payment_status"]) {
            $updateArray = array('order_deleted' => applicationConstants::YES);
            $whr = array('smt' => 'order_id = ?', 'vals' => array($order_id));

            if (!FatApp::getDb()->updateFromArray(Orders::DB_TBL, $updateArray, $whr)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Order_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getPaymentForm($langId, $orderId = '')
    {
        $frm = new Form('frmPayment');
        $frm->addHiddenField('', 'opayment_order_id', $orderId);
        $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'opayment_comments', '')->requirements()->setRequired();
        $frm->addRequiredField(Labels::getLabel('LBL_Payment_Method', $this->adminLangId), 'opayment_method');
        $frm->addRequiredField(Labels::getLabel('LBL_Txn_ID', $this->adminLangId), 'opayment_gateway_txn_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Amount', $this->adminLangId), 'opayment_amount')->requirements()->setFloatPositive(true);
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOrderSearchForm($langId, $deletedOrders = false, $orderType = applicationConstants::ORDER_TYPE_SALE)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));

        $frm->addTextBox(Labels::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');

        $frm->addSelectBox(Labels::getLabel('LBL_Payment_Status', $this->adminLangId), 'order_payment_status', Orders::getOrderPaymentStatusArr($langId), '', array(), Labels::getLabel('LBL_Select_Payment_Status', $this->adminLangId));

        $frm->addDateField('', 'date_from', '', array('placeholder' => 'Date From', 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => 'Date To', 'readonly' => 'readonly'));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => 'Order From' . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => 'Order To [' . $currencySymbol . ']'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id');
        $frm->addHiddenField('', 'order_product_for', $orderType);
        $deleted = ($deletedOrders) ? 1 : 0;
        $frm->addHiddenField('', 'is_deleted', $deleted);
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function approvePayment(int $orderPaymentId)
    {
        $orederObj = new Orders();
        $result = current($orederObj->getOrderPayments(['id' => $orderPaymentId]));
        if (!empty($result)) {
            $db = FatApp::getDb();
            $db->startTransaction();
            if (!$db->updateFromArray(
                            Orders::DB_TBL, array('order_payment_status' => Orders::ORDER_PAYMENT_PAID, 'order_date_updated' => date('Y-m-d H:i:s')), array('smt' => 'order_id = ? ', 'vals' => array($result['opayment_order_id']))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }

            if (!$db->updateFromArray(
                            Orders::DB_TBL_ORDER_PAYMENTS, array('opayment_txn_status' => Orders::ORDER_PAYMENT_PAID), array('smt' => 'opayment_id = ? ', 'vals' => array($orderPaymentId))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }

            if (!$db->updateFromArray(
                            Orders::DB_TBL_ORDER_PRODUCTS, array('op_status_id' => FatApp::getConfig("CONF_DEFAULT_PAID_ORDER_STATUS")), array('smt' => 'op_order_id = ? ', 'vals' => array($result['opayment_order_id']))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel("MSG_APPROVED", $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function rejectPayment(int $orderPaymentId)
    {
        $orederObj = new Orders();
        $result = current($orederObj->getOrderPayments(['id' => $orderPaymentId]));
        if (!empty($result)) {
            $db = FatApp::getDb();
            $db->startTransaction();
            if (!$db->updateFromArray(
                            Orders::DB_TBL, array('order_payment_status' => Orders::ORDER_PAYMENT_CANCELLED, 'order_date_updated' => date('Y-m-d H:i:s')), array('smt' => 'order_id = ? ', 'vals' => array($result['opayment_order_id']))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }

            if (!$db->updateFromArray(
                            Orders::DB_TBL_ORDER_PAYMENTS, array('opayment_txn_status' => Orders::ORDER_PAYMENT_CANCELLED), array('smt' => 'opayment_id = ? ', 'vals' => array($orderPaymentId))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }

            if (!$db->updateFromArray(
                            Orders::DB_TBL_ORDER_PRODUCTS, array('op_status_id' => FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")), array('smt' => 'op_order_id = ? ', 'vals' => array($result['opayment_order_id']))
                    )) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError($db->getError());
            }
        }
        $db->commitTransaction();
        $this->set('msg', Labels::getLabel("MSG_REJECTED", $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
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
        
        echo CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path']; die();

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

    public function downloadAttachedFile($recordId, $recordSubid = 0)
    {
        if ('' == $recordId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

}
