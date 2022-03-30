<?php

class SellerOrdersController extends AdminBaseController
{

    private $shippingService;
    private $trackingService;
    private $paymentPlugin;
    private $method = '';

    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        $this->method = $action;
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSellerOrders($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSellerOrders($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    /**
     * loadShippingService
     *
     * @return void
     */
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

        $this->shippingService = PluginHelper::callPlugin($keyName, [$this->adminLangId], $error, $this->adminLangId, false);

        if (false === $this->shippingService) {
            if ('search' == strtolower($this->method)) {
                Message::addErrorMessage($error);
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
        }

        if (false === $this->shippingService->init()) {
            if ('search' == strtolower($this->method)) {
                Message::addErrorMessage($this->shippingService->getError());
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
        }
    }

    /**
     * loadTrackingService
     *
     * @return void
     */
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

        $this->trackingService = PluginHelper::callPlugin($keyName, [$this->adminLangId], $error, $this->adminLangId, false);
        if (false === $this->trackingService) {
            Message::addErrorMessage($error);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (false === $this->trackingService->init()) {
            Message::addErrorMessage($this->shippingService->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function index($order_id = '')
    {
        $this->objPrivilege->canViewSellerOrders();
        $frm = $this->getOrderSearchForm($this->adminLangId, applicationConstants::ORDER_TYPE_SALE);
        $frm->fill(array('order_id' => $order_id));
        $this->set('frmSearch', $frm);
        $this->set('isRental', applicationConstants::NO);
        $this->_template->render();
    }

    public function rental($order_id = '')
    {
        $this->objPrivilege->canViewSellerOrders();
        $frm = $this->getOrderSearchForm($this->adminLangId, applicationConstants::ORDER_TYPE_RENT);
        $frm->fill(array('order_id' => $order_id));
        $this->set('frmSearch', $frm);
        $this->set('isRental', applicationConstants::YES);
        $this->_template->render(true, true, 'seller-orders/index.php');
    }

    public function search()
    {
        $this->objPrivilege->canViewSellerOrders();
        $orderType = FatApp::getPostedData('order_product_for', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $frmSearch = $this->getOrderSearchForm($this->adminLangId, $orderType);

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('op_id', 'DESC');
        $srch->addCondition('opd_sold_or_rented', '=', $orderType);
        
        $addonSrch = clone $srch;
        $addonSrch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        
        $srch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);
        $srch->addMultipleFields(array('op_id', 'order_id', 'order_payment_status', 'op_order_id', 'op_invoice_number', 'order_net_amount', 'order_date_added', 'ou.user_id', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_dial_code', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_code', 'op.op_shop_owner_phone', 'op_shop_name', 'op_other_charges', 'op.op_qty', 'op.op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'op_status_id', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'orderstatus_color_class', 'op_rounding_off', 'op_product_type', 'opshipping_carrier_code', 'opshipping_service_code', 'opd_product_type', 'opd_sold_or_rented', 'opd_rental_security', 'opshipping_type'));
        if (isset($post['order_id']) && $post['order_id'] != '') {
            $srch->addCondition('op_order_id', '=', $post['order_id']);
        }

        $keyword = trim(FatApp::getPostedData('keyword', null, ''));

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op.op_order_id', 'like', '%' . $keyword . '%');
            $srch->addKeywordSearch($keyword, $cnd);
            $cnd->attachCondition('op.op_shop_owner_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_username', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_email', 'like', '%' . $keyword . '%', 'OR');
        }

        $user_id = FatApp::getPostedData('user_id', '', -1);
        if ($user_id > 0) {
            $srch->addCondition('user_id', '=', $user_id);
        } else {
            $customer_name = trim(FatApp::getPostedData('buyer', null, ''));
            if (!empty($customer_name)) {
                $cnd = $srch->addCondition('ou.user_name', 'like', '%' . $customer_name . '%');
                $cnd->attachCondition('ou.user_phone', 'like', '%' . $customer_name . '%', 'OR');
                $cnd->attachCondition('ouc.credential_email', 'like', '%' . $customer_name . '%', 'OR');
            }
        }

        $shipping_company_user_id = FatApp::getPostedData('shipping_company_user_id', FatUtility::VAR_INT, 0);
        if ($shipping_company_user_id > 0) {
            $srch->joinShippingUsers();
            $srch->addCondition('optsu_user_id', '=', $shipping_company_user_id);
        }

        if (isset($post['op_status_id']) && $post['op_status_id'] != '') {
            $op_status_id = FatUtility::int($post['op_status_id']);
            $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        }

        $shop_name = trim(FatApp::getPostedData('shop_name', null, ''));
        if (!empty($shop_name)) {
            $cnd = $srch->addCondition('op_l.op_shop_name', 'like', '%' . $shop_name . '%');
            $cnd->attachCondition('op.op_shop_owner_name', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_username', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_email', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_phone', 'like', '%' . $shop_name . '%', 'OR');
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

        $rs = $srch->getResultSet();
        $vendorOrdersList = FatApp::getDb()->fetchAll($rs);
        $oObj = new Orders();
        
        $addonAmountArr = [];
        if (!empty($vendorOrdersList) && $orderType == applicationConstants::ORDER_TYPE_RENT) {
            $opIds  = array_column($vendorOrdersList, 'op_id');
            $addonSrch->addCondition('op_attached_op_id', 'IN', $opIds);
            $addonSrch->addMultipleFields(
                    array('order_net_amount', 'op_id', 'op_qty', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller',
                        'op_selprod_user_id', 'opshipping_by_seller_user_id', 'opship.*', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'op_status_id', 'op_attached_op_id')
            );
            
            $addonRs = $addonSrch->getResultSet();
            $addons = FatApp::getDb()->fetchAll($addonRs); 
            if (!empty($addons)) {
                foreach ($addons as $addon) {
                    $charges = $oObj->getOrderProductChargesArr($addon['op_id']);
                    $addon['charges'] = $charges;
                    /* $totalAmount = CommonHelper::orderProductAmount($addon, 'netamount', false, User::USER_TYPE_SELLER); */
                    $totalAmount = CommonHelper::orderProductAmount($addon, 'netamount', false, User::USER_TYPE_SELLER);
                    if (isset($addonAmountArr[$addon['op_attached_op_id']])) {
                        $addonAmountArr[$addon['op_attached_op_id']] += $totalAmount;
                    } else {
                        $addonAmountArr[$addon['op_attached_op_id']] = $totalAmount; 
                    }
                }
            }
        }
        
        $isMannulShipOrder = 1;
        foreach ($vendorOrdersList as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
            $order['addon_amount'] = (isset($addonAmountArr[$order['op_id']])) ? $addonAmountArr[$order['op_id']] : 0;
            if ($order['opshipping_type'] == Shipping::SHIPPING_SERVICES) {
                $isMannulShipOrder = 0;
            }
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (NULL !== $this->shippingService && $isMannulShipOrder == 0));
        /* ShipStation */

        $this->set("vendorOrdersList", $vendorOrdersList);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function view(int $op_id, $print = false)
    {
        $this->objPrivilege->canViewSellerOrders();
        $srch = $this->getOrderDetailsSrchObjById($op_id, true);
        $opRs = $srch->getResultSet();
        $opRows = FatApp::getDb()->fetchAll($opRs, 'op_id');
        if (empty($opRows)) {
            Message::addErrorMessage($this->str_invalid_request);
            CommonHelper::redirectUserReferer();
        }
        $opRow = $opRows[$op_id];
        unset($opRows[$op_id]);
        $attachedServices = $opRows;
        if ($opRow['opshipping_fulfillment_type'] == Shipping::FULFILMENT_SHIP) {
            /* ShipStation */
            $this->loadShippingService();
            $this->set('canShipByPlugin', (null !== $this->shippingService && $opRow['opshipping_type'] == Shipping::SHIPPING_SERVICES));

            if (!empty($opRow["opship_orderid"])) {
                if (null != $this->shippingService && false === $this->shippingService->loadOrder($opRow["opship_orderid"])) {
                    Message::addErrorMessage($this->shippingService->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
                }
                $opRow['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
            }
            /* ShipStation */

            /* AfterShip */
            $this->loadTrackingService();
            $this->set('canTrackByPlugin', (null !== $this->trackingService));
            /* AfterShip */

            if (null !== $this->shippingService && null !== $this->trackingService) {
                $srch = TrackingCourierCodeRelation::getSearchObject();
                $srch->addCondition("tccr_shipapi_courier_code", "=", $opRow['opshipping_carrier_code']);
                $srch->doNotCalculateRecords();
                $srch->setPageSize(1);
                $rs = $srch->getResultSet();
                $data = FatApp::getDb()->fetch($rs);
                if (null === $data) {
                    Message::addErrorMessage(Labels::getLabel("MSG_PLEASE_MAP_YOUR_SHIPPING_CARRIER_CODE_WITH_TRACKING_CARRIER_CODE", $this->adminLangId));
                    FatApp::redirectUser(UrlHelper::generateUrl("TrackingCodeRelation"));
                }
            }
        } else {
            $this->set('canShipByPlugin', '');
        }

        $orderObj = new Orders($opRow['order_id']);
        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $opRow['charges'] = $charges;

        $addresses = $orderObj->getOrderAddresses($opRow['order_id']);
        $opRow['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $opRow['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($opRow['order_id'], $op_id);
        $opRow['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $opRow['comments'] = $orderObj->getOrderComments($this->adminLangId, array("op_id" => $op_id));

        /* [ SERVICES UPDATES */
        $servicesCartTotal = 0;
        $servicesNetTotal = 0;
        $servicesTaxTotal = 0;
        $servicesLateCharges = 0;

        if (!empty($attachedServices)) {
            foreach ($attachedServices as $serviceId => $service) {
                $charges = $orderObj->getOrderProductChargesArr($serviceId);
                $attachedServices[$serviceId]['charges'] = $charges;

                $opChargesLog = new OrderProductChargeLog($serviceId);
                $taxOptions = $opChargesLog->getData($this->adminLangId);
                $attachedServices[$serviceId]['taxOptions'] = $taxOptions;
                $servicesCartTotal += CommonHelper::orderProductAmount($attachedServices[$serviceId], 'CART_TOTAL');
                $servicesTaxTotal += CommonHelper::orderProductAmount($attachedServices[$serviceId], 'TAX');
                $servicesNetTotal += CommonHelper::orderProductAmount($attachedServices[$serviceId], 'netamount', false, User::USER_TYPE_SELLER);
                $servicesLateCharges += $service['charge_total_amount'];
            }
        }
        $serviceTotalPriceArr = [
            'cart_total' => $servicesCartTotal,
            'tax_total' => $servicesTaxTotal,
            'net_total' => $servicesNetTotal,
            'late_charges_total' => $servicesLateCharges,
        ];
        $this->set('serviceTotalPriceArr', $serviceTotalPriceArr);
        $this->set('attachedServices', $attachedServices);
        /* ] */

        if ($opRow['plugin_code'] == 'CashOnDelivery') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(true);
        } else if ($opRow['plugin_code'] == 'PayAtStore') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, false, true);
        } else {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, $opRow['op_product_type']);
        }

        $statusArray = array(FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"), FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS"), FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END")/* FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS_MARKED_BY_BUYER") */);
        if ($opRow['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT && in_array($opRow["op_status_id"], $statusArray)) {
            $processingStatuses[] = FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS");
            $processingStatuses[] = FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END");
        }

        $data = [
            'op_id' => $op_id,
            'op_status_id' => $opRow['op_status_id'],
            'tracking_number' => $opRow['opship_tracking_number']
        ];
        /* [ RENTAL UPDATES */
        $data['refund_security_type'] = $opRow['opd_refunded_security_type'];
        $data['refund_security_amount'] = $opRow['opd_refunded_security_amount'];

        $rental_security = $opRow['opd_rental_security'];
        $op_qty = ($opRow['op_return_qty'] == 0) ? $opRow['op_qty'] : $opRow['op_return_qty'];
        $recommendedSecAmnt = $opRow['opd_refunded_security_amount'];
        $maxSecurityAmount = $opRow['opd_rental_price'] * $op_qty;

        $shippingUserId = $opRow['opshipping_by_seller_user_id'];
        $parentOrderDetail = array();
        if ($opRow['opd_extend_from_op_id'] > 0) {
            $pSrch = $this->getOrderDetailsSrchObjById($opRow['opd_extend_from_op_id']);
            $pRs = $pSrch->getResultSet();
            $parentOrderDetail = FatApp::getDb()->fetch($pRs);
            if (!empty($parentOrderDetail)) {
                $data['refund_security_type'] = $parentOrderDetail['opd_refunded_security_type'];
                $data['refund_security_amount'] = $parentOrderDetail['opd_refunded_security_amount'];
                $recommendedSecAmnt = $parentOrderDetail['opd_refunded_security_amount'];
                if ($parentOrderDetail['op_qty'] == $parentOrderDetail['op_return_qty']) {
                    $maxSecurityAmount = $parentOrderDetail['opd_rental_price'] * $op_qty;
                } else {
                    $maxSecurityAmount = $opRow['opd_rental_price'] * $op_qty;
                }
                $rental_security = $parentOrderDetail['opd_rental_security'];
            }
            $shippingUserId = $parentOrderDetail['opshipping_by_seller_user_id'];
            $this->set('parentOrderDetail', $parentOrderDetail);
        }

        $totalSecurityRefundAmount = $rental_security * $op_qty;
        $maxSecurityAmount = $maxSecurityAmount + $totalSecurityRefundAmount;
        $opRow['totalSecurityAmount'] = $totalSecurityRefundAmount;
        $opRow['maxSecurityAmount'] = $maxSecurityAmount;
        $opRow['recommended_security_refund'] = $recommendedSecAmnt;
        $data['return_qty'] = $opRow['op_return_qty'];
        if ($opRow["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, 0));
            // $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        }

        if ($opRow['order_rfq_id'] > 0){
            $opRow['maxSecurityAmount'] = $rental_security;
            $opRow['totalSecurityAmount'] = $rental_security;
        }

        $frm = $this->getOrderCommentsForm($opRow, $processingStatuses);
        $frm->fill($data);
        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);
        $shippingHanldedBySeller = CommonHelper::canAvailShippingChargesBySeller($opRow['op_selprod_user_id'], $opRow['opshipping_by_seller_user_id']);
        $allowedShippingUserStatuses = $orderObj->getAdminAllowedUpdateShippingUser();
        
        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($opRow['order_id'], true);
        if ($op_id > 0) {
            $oVfldsObj->addCondition('optvf_op_id', '=', $op_id);
        } 
        $oVfldsObj->doNotCalculateRecords();
        $oVfldsObj->doNotLimitRecords();
        $oVfldsObj->addMultipleFields(array('ovd_order_id', 'ovd_order_id', 'ovd_vflds_type', 'ovd_vflds_name', 'ovd_value', 'optvf_selprod_id', 'optvf_op_id', 'ovd_vfld_id'));
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);
        $this->set('verificationFldsData', $verificationFldsData);

        $attachmentArr = array();
        if (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1)) {
            $attachmentArr = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $opRow['order_order_id'], 0, -1, true, 0, false);
        }
        $this->set('attachment', $attachmentArr);

        /* [ RENTAL UPDATES */
        if (!empty($opRow['comments'])) {
            $statusArr = array_column($opRow['comments'], 'oshistory_orderstatus_id');
            /* if (in_array(OrderStatus::ORDER_RENTAL_EXTENDED, $statusArr)) { */
            $extendChildOrderdata = OrderProductData::getOrderProductData($op_id, true);
            $this->set('extendedChildData', $extendChildOrderdata);
            /* } */
        }
        $this->set('rentalTypeArr', applicationConstants:: rentalTypeArr($this->adminLangId));
        /* ] */

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->adminLangId);
        $opRow['taxOptions'] = $taxOptions;

        $this->set('allLanguages', Language::getAllNames(false, 0, false, false));
        $this->set('frm', $frm);
        $this->set('shippingHanldedBySeller', $shippingHanldedBySeller);
        $this->set('order', $opRow);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('yesNoArr', applicationConstants ::getYesNoArr($this->adminLangId));
        $this->set('displayForm', (in_array($opRow['op_status_id'], $processingStatuses) && $this->canEdit && $opRow['order_payment_status'] != Orders:: ORDER_PAYMENT_CANCELLED));
        if ($print) {
            $print = true;
        }
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);
        $this->set('statusAddressData', $this->getDropOffAddressData($opRow['comments']));
        if ($attachedFile = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $op_id)) {
            $this->set('statusAttachedFiles', CommonHelper::groupAttachmentFilesData($attachedFile, 'afile_record_subid'));
        }
        $trackingPluginEnable = true;
        $shipmentTracking = new ShipmentTracking();
        if (!$shipmentTracking->init($this->adminLangId)) {
            $trackingPluginEnable = false;
        }
        $this->set('trackingPluginEnable', $trackingPluginEnable);
        $this->_template->render(true, !$print);
    }

    public function viewInvoice($op_id)
    {
        $this->objPrivilege->canViewSellerOrders();
        $op_id = FatUtility::int(
                        $op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinLateChargesHistory();
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinShopSpecifics();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $addonProductIds = Orders::getAddonsIdsByProduct($op_id);
        $addonProductIds = array_merge($addonProductIds, array($op_id));
        $srch->addCondition('op_id', 'IN', $addonProductIds);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addMultipleFields(array('*', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city', 'charge_total_amount'));
        $rs = $srch->getResultSet();
        $orderDetails = FatApp::getDb()->fetchAll($rs, 'op_id');

        if (empty($orderDetails)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }
        $orderDetail = $orderDetails[$op_id];
        unset($orderDetails[$op_id]);
        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $attachedServices = $orderDetails;
        $orderDetail['charges'] = $charges;
        /* [ SERVICES DETAILS */
        $servicesCartTotal = 0;
        $servicesNetTotal = 0;
        $servicesTaxTotal = 0;
        $servicesLateCharges = 0;

        if (!empty($attachedServices)) {
            foreach ($attachedServices as $serviceId => $service) {
                $charges = $orderObj->getOrderProductChargesArr($serviceId);
                $attachedServices[$serviceId]['charges'] = $charges;
                $opChargesLog = new OrderProductChargeLog($serviceId);
                $taxOptions = $opChargesLog->getData($this->adminLangId);
                $attachedServices[$serviceId]['taxOptions'] = $taxOptions;
                $servicesCartTotal += CommonHelper::orderProductAmount($service, 'CART_TOTAL');
                $servicesTaxTotal += CommonHelper::orderProductAmount($service, 'TAX');
                $servicesNetTotal += CommonHelper::orderProductAmount($service, 'netamount');
                $servicesLateCharges += $service['charge_total_amount'];
            }
        }
        $serviceTotalPriceArr = [
            'cart_total' => $servicesCartTotal,
            'tax_total' => $servicesTaxTotal,
            'net_total' => $servicesNetTotal,
            'late_charges_total' => $servicesLateCharges,
        ];
        
        /* ] */


        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        } if (!empty($orderDetail["opship_orderid"])) {
            if (null != $this->shippingService && false === $this->shippingService->loadOrder($orderDetail["opship_orderid"])) {
                Message::addErrorMessage($this->shippingService->getError());
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
            $orderDetail['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
        }

        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['op_order_id'], $orderDetail['op_id']);
        $orderDetail['pickupAddress'] = (isset($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->adminLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        /* $this->set('orderDetail', $orderDetail);
          $this->set('languages', Language::getAllNames());
          $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
          $this->set('canEdit', $this->objPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
          $this->_template->render(true, true); */

        $template = new FatTemplate('', '');
        $template->set('adminLangId', $this->adminLangId);
        $template->set('orderDetail', $orderDetail);
        $template->set('shippedBySeller', $shippedBySeller);
        $template->set('serviceTotalPriceArr', $serviceTotalPriceArr);
        $template->set('attachedServices', $attachedServices);

        /* get invoice attachment */
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, 0,0,$this->adminLangId);
        $logoImgUrl = '';
        if($file_row['afile_id'] > 0 ) {
            $logoImgUrl =  UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . 'user-uploads/' . AttachedFile::FILETYPE_INVOICE_LOGO_PATH  . $file_row['afile_physical_path'];
        }
        $template->set('logoImgUrl', $logoImgUrl);
        /* ---- */
        

        require_once(CONF_INSTALLATION_PATH . 'library/tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->adminLangId));
        $pdf->SetKeywords(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->adminLangId));
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetHeaderMargin(0);
        $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
        $pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetTitle(Labels::getLabel('LBL_Tax_Invoice', $this->adminLangId));
        $pdf->SetSubject(Labels::getLabel('LBL_Tax_Invoice', $this->adminLangId));

        // set LTR direction for english translation
        $pdf->setRTL(('rtl' == Language::getLayoutDirection($this->adminLangId)));
        // set font
        $pdf->SetFont('dejavusans');
        $templatePath = "seller-orders/view-invoice.php";
        $html = $template->render(false, false, $templatePath, true, true); 
        /* $html = addslashes($template->render(false, false, $templatePath, true, true)); */
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->lastPage();

        ob_end_clean();
        // $saveFile = CONF_UPLOADS_PATH . 'demo-pdf.pdf';
        //$pdf->Output($saveFile, 'F');
        $pdf->Output('tax-invoice.pdf', 'I');
        return true;
    }

    public function updateShippingCompany()
    {
        $this->objPrivilege->canEditSellerOrders();
        $post = FatApp::getPostedData();
        $op_id = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        if (1 > $op_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_id', '=', $op_id);
        //$srch->addMultipleFields(array('op_id','op_order_id','optsu_user_id'));
        $srch->addMultipleFields(
                array(
                    'order_id', 'order_pmethod_id', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price',
                    'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_commission_percentage', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_dial_code', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_code', 'op.op_shop_owner_phone', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_other_charges', 'op_rounding_off', 'optsu_user_id', 'op_product_weight', 'credential_email', 'plugin_code'
                )
        );
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new SearchBase(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'optosu');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('optosu.optsu_op_id', '=', $orderDetail['op_id']);
        $rs = $srch->getResultSet();
        $shippingUserRow = FatApp::getDb()->fetch($rs);
        if ($shippingUserRow) {
            Message::addErrorMessage('Already Assigned to shipping company user');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getShippingCompanyUserForm();
        $post = $frm->getFormDataFromArray($post);

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $data = array('optsu_op_id' => $op_id, 'optsu_user_id' => $post['optsu_user_id']);
        if ($orderDetail['optsu_user_id'] == null) {
            $row = $db->insertFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $data);
        } else {
            $row = $db->updateFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $data, array('smt' => 'optsu_op_id = ?', 'vals' => array($op_id)));
        }

        if (!$row) {
            Message::addErrorMessage($db->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderObj = new Orders($orderDetail['order_id']);
        $addresses = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : $addresses[Orders::BILLING_ADDRESS_TYPE];
        $shopSrch = new ShopSearch(1);
        $shopSrch->joinShopCountry();
        $shopSrch->joinShopState();
        $shopSrch->addCondition('shop_id', '=', 1);
        $shopSrch->addMultipleFields(array('ifnull(country_name,country_code) as country_name', 'ifnull(state_name,state_identifier) as state_name', 'shop_city', 'shop_address_line_1', 'shop_address_line_2'));
        $rs = $shopSrch->getResultSet();
        $orderDetail['shopDetail'] = FatApp::getDb()->fetch($rs);


        $srch = new SearchBase(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'optosu');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('optosu.optsu_op_id', '=', $orderDetail['op_id']);
        $rs = $srch->getResultSet();
        $shippingUserRow = FatApp::getDb()->fetch($rs);
        if ($shippingUserRow && $orderDetail['plugin_code'] == "CashOnDelivery") {
            $comments = Labels::getLabel('Msg_Cash_will_collect_against_COD_order', $this->adminLangId) . ' ' . $orderDetail['op_invoice_number'];
            $amt = CommonHelper::orderProductAmount($orderDetail);
            $txnObj = new Transactions();
            $txnDataArr = array(
                'utxn_user_id' => $shippingUserRow['optsu_user_id'],
                'utxn_comments' => $comments,
                'utxn_status' => Transactions::STATUS_COMPLETED,
                'utxn_debit' => $amt,
                'utxn_op_id' => $orderDetail['op_id'],
            );
            if (!$txnObj->addTransaction($txnDataArr)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($txnObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $db->commitTransaction();

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeOrderStatus()
    {
        $this->objPrivilege->canEditSellerOrders();
        $db = FatApp::getDb();
        $db->startTransaction();

        $post = FatApp::getPostedData();
        if (!isset($post['op_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $plugin = new Plugin();
        $this->keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
        $pluginValidation = true;
        if (!empty($this->keyName) && in_array($this->keyName, ['EasyPost'])) {
            $pluginValidation = false;
        }

        $status = FatApp::getPostedData('op_status_id', FatUtility::VAR_INT, 0);
        /* $manualShipping = FatApp::getPostedData('manual_shipping', FatUtility::VAR_INT, 0); */
        $trackingNumber = FatApp::getPostedData('tracking_number', FatUtility::VAR_STRING, '');
        if ($status == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS") && isset($post['tracking_number']) && empty($trackingNumber) /* && 1 > $manualShipping */ && $pluginValidation) {
            Message::addErrorMessage(Labels::getLabel('MSG_PLEASE_SELECT_SELF_SHIPPING', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Cancel_request_is_submitted_for_this_order', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderObj = new Orders();
        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinOrderProductShipment();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderUser();
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $extendFromOpId = $orderDetail['opd_extend_from_op_id'];
        $parentOrderDetail = array();
        if ($extendFromOpId > 0) {
            $pSrch = new OrderProductSearch($this->adminLangId, true, true);
            $pSrch->joinPaymentMethod();
            $pSrch->joinShippingUsers();
            $pSrch->joinOrderUser();
            $pSrch->addCondition('op_id', '=', $extendFromOpId);
            $pRs = $pSrch->getResultSet();
            $parentOrderDetail = FatApp::getDb()->fetch($pRs);
        }


        if ($orderDetail['plugin_code'] == 'CashOnDelivery') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(true);
        } else if ($orderDetail['plugin_code'] == 'PayAtStore') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, false, true);
        } else {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, $orderDetail['op_product_type']);
        }

        if ($orderDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
            $processingStatuses[] = FatApp::getConfig("CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END");
            $processingStatuses[] = FatApp::getConfig("CONF_DEFAULT_RENTAL_RETURNED_ORDER_STATUS");
            /* $processingStatuses[] = FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS_MARKED_BY_BUYER"); */
        }

        /* [ maximum security refund calculation */
        $rental_security = $orderDetail['opd_rental_security'];
        $op_qty = $orderDetail['op_qty'];
        $recommendedSecAmnt = $orderDetail['opd_refunded_security_amount'];
        $maxSecurityAmount = $orderDetail['opd_rental_price'] * $op_qty;

        if (!empty($parentOrderDetail)) {
            $rental_security = $parentOrderDetail['opd_rental_security'];
            $op_qty = $parentOrderDetail['op_qty'];
            $recommendedSecAmnt = $parentOrderDetail['opd_refunded_security_amount'];
            $maxSecurityAmount = $parentOrderDetail['opd_rental_price'] * $op_qty;
        }

        $totalSecurityRefundAmount = $rental_security * $op_qty;
        $orderDetail['totalSecurityAmount'] = $totalSecurityRefundAmount;
        /* ] */

        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $post = $frm->getFormDataFromArray($post);

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $restrictOrderStatusChange = array_merge(
                (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"), (array) FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")
        );

        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore']) && !CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id']) && !$orderDetail['optsu_user_id'] && in_array($post["op_status_id"], $restrictOrderStatusChange) && $orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_assign_shipping_user', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            $trackingCourierCode = '';
            
            $opship_tracking_url = FatApp::getPostedData('opship_tracking_url', FatUtility::VAR_STRING, '');
            // print_r($opship_tracking_url);
            // die();
            $rentalSecurityRefundData = array();
            if ($orderDetail['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
                $rentalSecurityRefundData = array(
                    'refund_security_type' => $post['refund_security_type'],
                    'refund_security_amount' => $post['refund_security_amount'],
                    'is_admin' => 1
                );
            }

            if ($post["op_status_id"] == OrderStatus::ORDER_SHIPPED && $pluginValidation && isset($post['tracking_number'])) {
                /* if (array_key_exists('manual_shipping', $post) && 0 < $post['manual_shipping']) { */
                    $updateData = [
                        'opship_op_id' => $post['op_id'],
                        "opship_tracking_number" => (isset($post['tracking_number'])) ? $post['tracking_number'] : "",
                            //    "opship_tracking_url" => $post['opship_tracking_url'],
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
                    $trackingRelation = new TrackingCourierCodeRelation();
                    $trackData = $trackingRelation->getDataByShipCourierCode($orderDetail['opshipping_carrier_code']);
                    $trackingCourierCode = !empty($trackData['tccr_tracking_courier_code']) ? $trackData['tccr_tracking_courier_code'] : '';
                } */
            }
            
            if (OrderStatus::ORDER_COMPLETED == $post["op_status_id"] && $orderDetail ['opd_sold_or_rented'] == applicationConstants:: PRODUCT_FOR_RENT) { /* UPDATE LATE CHARGES HISTORY IF NOT UPDATED */
            $opReturnDate = (isset($post['opd_mark_rental_return_date']) && $post['opd_mark_rental_return_date'] != '') ? $post['opd_mark_rental_return_date'] : date('Y-m-d h:i:s');
                if (!$orderObj->updateLateChargesHistory($op_id, $this->adminLangId, $opReturnDate, 0, BuyerLateChargesHistory::STATUS_UNPAID)) {
                    $db->rollbackTransaction();
                    Message::addErrorMessage(Labels::getLabel('MSG_Unable_to_update_late_charges_history', $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
            
            $id = 0;
            
            if (!$orderObj->addChildProductOrderHistory($op_id, 0, $orderDetail["order_language_id"], $post ["op_status_id"], $post["comments"], $post["customer_notified"], $trackingNumber, 0, true, $trackingCourierCode, $rentalSecurityRefundData, '',0,0,$id, $opship_tracking_url)) {
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore']) && (OrderStatus::ORDER_DELIVERED == $post["op_status_id"] || OrderStatus::ORDER_COMPLETED == $post["op_status_id"]) && Orders::ORDER_PAYMENT_PAID != $orderDetail['order_payment_status']) {
            $orderProducts = new OrderProductSearch($this->adminLangId, true, true);
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
                        Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
        }
        /* [ UPDATE ATTACHED SERVICES STATUS */
        $mainOpId = ($extendFromOpId > 0) ? $extendFromOpId : $op_id;
        $addonProductIds = Orders::getAddonsIdsByProduct($mainOpId, false, true);
        if (!empty($addonProductIds)) {
            foreach ($addonProductIds as $keyIndex => $addonProduct) {
                /* $isRefundAmount = $addonProduct['opd_is_eligible_refund']; */
                if (!$orderObj->addChildProductOrderHistory($addonProduct['op_id'], 0, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $trackingNumber, 0, true, $trackingCourierCode)) {
                    Message::addErrorMessage($this->str_invalid_request);
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }
        /* ] */

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    // exists in MyAppController
    public function checkIsShippingMode()
    {
        $json = array();
        $post = FatApp::getPostedData();
        if (isset($post["val"])) {
            if ($post["val"] == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) {
                $json["shipping"] = 1;
            } elseif ($post["val"] == FatApp::getConfig("CONF_RENTAL_COMPLETED_ORDER_STATUS")) {
                $json["refundSecurity"] = 1;
            }
        }
        echo json_encode($json);
    }

    public function CancelOrder($op_id)
    {
        $this->objPrivilege->canEditSellerOrders();
        $op_id = FatUtility::int($op_id);

        if (false !== OrderCancelRequest::getCancelRequestById($op_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_have_already_sent_the_cancellation_request_for_this_order', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
                array(
                    'order_id', 'order_pmethod_id', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price',
                    'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_commission_percentage', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_dial_code', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_code', 'op.op_shop_owner_phone', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_other_charges', 'op_rounding_off'
                )
        );
        $srch->addCondition('op_id', '=', $op_id);
        $opRs = $srch->getResultSet();
        $opRow = FatApp::getDb()->fetch($opRs);
        if (!$opRow) {
            Message::addErrorMessage($this->str_invalid_request);
            CommonHelper::redirectUserReferer();
        }
        $orderObj = new Orders($opRow['order_id']);

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $opRow['charges'] = $charges;

        $addresses = $orderObj->getOrderAddresses($opRow['order_id']);
        $opRow['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $opRow['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();
        $opRow['comments'] = $orderObj->getOrderComments($this->adminLangId, array("op_id" => $op_id));

        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);

        $notEligible = false;
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        if (in_array($opRow["op_status_id"], $notAllowedStatues)) {
            $notEligible = true;
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->adminLangId), $orderStatuses[$opRow ["op_status_id"]]));
            //FatUtility::dieWithError( Message::getHtml() );
            CommonHelper::redirectUserReferer();
        }

        $frm = $this->getOrderCancelForm($this->adminLangId);
        $frm->fill(array('op_id' => $op_id));

        $this->set('notEligible', $notEligible);
        $this->set('frm', $frm);
        $this->set('order', $opRow);
        $this->_template->render();
    }

    public function cancelReason()
    {
        $frm = $this->getOrderCancelForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (false !== OrderCancelRequest::getCancelRequestById($op_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_have_already_sent_the_cancellation_request_for_this_order', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        
        $addonProductIds = Orders::getAddonsIdsByProduct($op_id);
        $addonProductIds = array_merge($addonProductIds, array($op_id));
        
        $srch->addCondition('op_id', 'IN', $addonProductIds);
        $rs = $srch->getResultSet();
        $orderDetail = array();
        if ($rs) {
            $orderDetails = FatApp::getDb()->fetchAll($rs, 'op_id');
        }

        if (empty($orderDetails)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $orderDetail = $orderDetails[$op_id];
        unset($orderDetails[$op_id]);
        $addonOrders = $orderDetails;
        
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->adminLangId), $orderStatuses[$orderDetail["op_status_id"]]));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$orderObj->addChildProductOrderHistory($op_id, 0, $this->adminLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pluginKey = Plugin::getAttributesById($orderDetail['order_pmethod_id'], 'plugin_code');

        $paymentMethodObj = new PaymentMethods();
        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->adminLangId)) {
            if (false == $paymentMethodObj->initiateRefund($orderDetail, PaymentMethods::REFUND_TYPE_CANCEL)) {
                FatUtility::dieJsonError($paymentMethodObj->getError());
            }

            $resp = $paymentMethodObj->getResponse();
            if (empty($resp)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $this->adminLangId));
            }

            // Debit from wallet if plugin/payment method support's direct payment to card of customer.
            if (!empty($resp->id)) {
                $childOrderInfo = $orderObj->getOrderProductsByOpId($op_id, $this->adminLangId);
                $txnAmount = $paymentMethodObj->getTxnAmount();
                $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $op_id, $resp->id);
            }
        }
        
        /* CANCEL RENTAL ADDON ORDER IF MAIN ORDER CANCELLED */
        if (!empty($addonOrders)) {
            foreach ($addonOrders as $orderDetail) {
                if (!$orderObj->addChildProductOrderHistory($orderDetail['op_id'], 0, $this->adminLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
                    Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $pluginKey = Plugin::getAttributesById($orderDetail['order_pmethod_id'], 'plugin_code');

                $paymentMethodObj = new PaymentMethods();
                if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->adminLangId)) {
                    if (false == $paymentMethodObj->initiateRefund($orderDetail, PaymentMethods::REFUND_TYPE_CANCEL)) {
                        FatUtility::dieJsonError($paymentMethodObj->getError());
                    }
        
                    $resp = $paymentMethodObj->getResponse();
                    if (empty($resp)) {
                        FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $this->adminLangId));
                    }
        
                    // Debit from wallet if plugin/payment method support's direct payment to card of customer.
                    if (!empty($resp->id)) {
                        $childOrderInfo = $orderObj->getOrderProductsByOpId($orderDetail['op_id'], $this->adminLangId);
                        $txnAmount = $paymentMethodObj->getTxnAmount();
                        $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                        $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                        Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $orderDetail['op_id'], $resp->id);
                    }
                }
            }
        }
        /* ] */
        
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getShippingCompanyUserForm($displayShippingUserForm = false)
    {
        $frm = new Form('frmShippingUser');
        $srch = User::getSearchObject(true);
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::YES);
        $srch->addMultipleFields(array('user_id', 'credential_username'));
        $srch->addCondition('uc.credential_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('uc.credential_verified', '=', applicationConstants::YES);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $frm->addSelectBox('Shipping User', 'optsu_user_id', $records)->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id', 0);
        if ($displayShippingUserForm) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        }
        return $frm;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus = [])
    {
        //echo "<pre>"; print_r($orderData); echo "</pre>"; exit;
    
        $frm = new Form('frmOrderComments');
        $orderStatusArr = Orders::getOrderProductStatusArr($this->adminLangId, $processingOrderStatus, $orderData['op_status_id']);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'op_status_id', $orderStatusArr);
        $fld->requirements()->setRequired();
        
        $statusFld = $fld;

        $frm->addSelectBox(Labels::getLabel('LBL_Notify_Customer_by_email', $this->adminLangId), 'customer_notified', applicationConstants::getYesNoArr($this->adminLangId))->requirements()->setRequired();

        $attr = [];
        $labelGenerated = false;
        if (isset($orderData['opship_tracking_number']) && !empty($orderData['opship_tracking_number'])) {
            $attr = [
                'disabled' => 'disabled'
            ];
            $labelGenerated = true;
        } else {
            /* $manualFld = $frm->addCheckBox(Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId), 'manual_shipping', 1, array(), false, 0);
            $manualShipUnReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId));
            $manualShipUnReqObj->setRequired(false);
            $manualShipReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId));
            $manualShipReqObj->setRequired(true);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'manual_shipping', $manualShipReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'manual_shipping', $manualShipUnReqObj);

            $fld = $manualFld; */
        }

        if (false === $labelGenerated) {
            $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->adminLangId), 'tracking_number', '', $attr);

            $trackingUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->adminLangId));
            $trackingUnReqObj->setRequired(false);
    
            $trackingReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->adminLangId));
            $trackingReqObj->setRequired(true);
    
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'tracking_number', $trackingReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'tracking_number', $trackingUnReqObj);
        
        
            $plugin = new Plugin();
            $afterShipData = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);
            if ($afterShipData != false) {
                $shipmentTracking = new ShipmentTracking();
                $shipmentTracking->init($this->adminLangId);
                $shipmentTracking->getTrackingCouriers();
                $trackCarriers = $shipmentTracking->getResponse();

                $trackCarrierFld = $frm->addSelectBox(Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId), 'oshistory_courier', $trackCarriers, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));

                $trackCarrierFldUnReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId));
                $trackCarrierFldUnReqObj->setRequired(false);

                $trackCarrierFldReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId));
                $trackCarrierFldReqObj->setRequired(true);

                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'oshistory_courier', $trackCarrierFldReqObj);
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'oshistory_courier', $trackCarrierFldUnReqObj);
            } else {
                $trackUrlFld = $frm->addTextBox(Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId), 'opship_tracking_url', '', $attr);
                $trackUrlFld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_ENTER_THE_URL_TO_TRACK_THE_SHIPMENT.', $this->adminLangId) . '</small>';

                $trackingUrlUnReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId));
                $trackingUrlUnReqObj->setRequired(false);

                $trackingurlReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACK_THROUGH', $this->adminLangId));
                $trackingurlReqObj->setRequired(true);

                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'opship_tracking_url', $trackingurlReqObj);
                $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'opship_tracking_url', $trackingUrlUnReqObj);
            }
        }

        $frm->addHiddenField('', 'op_id', 0);
        /* [ Rental Security Refund fields */
        if ($orderData['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {

            $refundTypeOptions = Orders::refundSecurityTypeOptions($this->adminLangId);

            $frm->addSelectBox(Labels::getLabel('LBL_Refund_Type', $this->adminLangId), 'refund_security_type', $refundTypeOptions, '', array(), Labels::getLabel('Lbl_Select', $this->adminLangId));

            $refundTypeUnReqFld = new FormFieldRequirement('refund_security_type', Labels::getLabel('LBL_Refund_Type', $this->adminLangId));
            $refundTypeUnReqFld->setRequired(false);

            $refundTypeReqFld = new FormFieldRequirement('refund_security_type', Labels::getLabel('LBL_Refund_Type', $this->adminLangId));
            $refundTypeReqFld->setRequired(true);

            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'eq', 'refund_security_type', $refundTypeReqFld);
            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'ne', 'refund_security_type', $refundTypeUnReqFld);

            $frm->addFloatField(Labels::getLabel('LBL_Refund_Security_Amount', $this->adminLangId), 'refund_security_amount');

            $sAmountUnReqObj = new FormFieldRequirement('refund_security_amount', Labels::getLabel('LBL_Refund_Security_Amount', $this->adminLangId));
            $sAmountUnReqObj->setRequired(false);

            $sAmountReqObj = new FormFieldRequirement('refund_security_amount', Labels::getLabel('LBL_Refund_Security_Amount', $this->adminLangId));
            $sAmountReqObj->setRequired(true);
            $sAmountReqObj->setFloatPositive();
            $sAmountReqObj->setRange('0.00000', $orderData['totalSecurityAmount']);

            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'eq', 'refund_security_amount', $sAmountReqObj);
            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'ne', 'refund_security_amount', $sAmountUnReqObj);

            $frm->addDateField(Labels::getLabel('LBL_Return_Date', $this->adminLangId), 'opd_mark_rental_return_date', '', array('class' => ''));

            $returnDateUnReqFld = new FormFieldRequirement('opd_mark_rental_return_date', Labels::getLabel('LBL_Return_Date', $this->adminLangId));
            $returnDateUnReqFld->setRequired(false);

            $returnDateReqFld = new FormFieldRequirement('opd_mark_rental_return_date', Labels::getLabel('LBL_Return_Date', $this->adminLangId));
            $returnDateReqFld->setRequired(true);

            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'eq', 'opd_mark_rental_return_date', $returnDateReqFld);

            $statusFld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS"), 'ne', 'opd_mark_rental_return_date', $returnDateUnReqFld);
        }
        /* Rental Security Refund fields ] */
        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->adminLangId), 'comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOrderCancelForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $frm->addHiddenField('', 'op_id');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'comments');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('ERR_Reason_cancellation', $langId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOrderSearchForm(int $langId, int $orderType = applicationConstants::ORDER_TYPE_SALE)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData ['currency_symbol_right'];
        $frm = new Form('frmVendorOrderSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' =>
            'keyword', 'autocomplete' => 'off'));
        $frm->addTextBox(Labels ::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'op_status_id', Orders::getOrderProductStatusArr($langId), '', array(), Labels::getLabel('LBL_All', $this->adminLangId));
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Shop', $this->adminLangId), 'shop_name');
        /* $frm->addTextBox(Labels::getLabel('LBL_Customer',$this->adminLangId),'customer_name'); */

        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $this->adminLangId), 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $this->adminLangId), 'readonly' => 'readonly'));
        // $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Order_From', $this->adminLangId) . ' [' . $currencySymbol . ']'));
        // $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Order_to', $this->adminLangId) . ' [' . $currencySymbol . ']'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id');
        $frm->addHiddenField('', 'order_id');
        $frm->addHiddenField('', 'order_product_for', $orderType);
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function orderTrackingInfo($trackingNumber, $courier, $orderNumber)
    {
        if (empty($trackingNumber) || empty($courier)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking = new ShipmentTracking();
        if (false === $shipmentTracking->init($this->adminLangId)) {
            Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking->createTracking($trackingNumber, $courier, $orderNumber);

        if (false === $shipmentTracking->getTrackingInfo($trackingNumber, $courier)) {
            Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $trackingInfo = $shipmentTracking->getResponse();

        $this->set('trackingInfo', $trackingInfo);
        $this->_template->render(false, false);
    }

    private function getOrderDetailsSrchObjById(int $opId, bool $includeAddons = false)
    {
        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinLateChargesHistory();
        $srch->joinOrderProductShipment();
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
                array(
                    'ops.*', 'order_id', 'order_order_id', 'order_payment_status', 'order_pmethod_id', 'order_tax_charged', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price', 'op_selprod_user_id', 'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_id as buyer_user_id', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_qty', 'op_commission_percentage', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_dial_code', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op.op_shop_id', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_code', 'op.op_shop_owner_phone', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_product_type', 'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_refund_qty', 'op_refund_amount', 'op_refund_commission', 'op_other_charges', 'optosu.optsu_user_id', 'order_is_wallet_selected', 'order_reward_point_used', 'op_product_tax_options', 'opship.*', 'addr.*', 'op_rounding_off', 'opd.*', 'latecharge.*', 'op_return_qty', 'order_user_id' ,'order_rfq_id', 'order_is_rfq', 'opst.*'
                )
        );
        $addonProductIds = [];
        if ($includeAddons) {
            $addonProductIds = Orders::getAddonsIdsByProduct($opId);
        }
        $addonProductIds = array_merge($addonProductIds, array($opId));
        $srch->addCondition('op_id', 'IN', $addonProductIds);
        return $srch;
    }

    public function downloadBuyerAtatchedFile($recordId, $recordSubid = 0, $afileId = 0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_ORDER_CONFIRM_FILE, $recordId, $recordSubid);

        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        }

        if (false == $file_row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }
        if (!file_exists(CONF_UPLOADS_PATH . $file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    private function getDropOffAddressData(array $orderStatusArr): array
    {
        if (empty($orderStatusArr)) {
            return [];
        }
        $addressDataArr = [];
        foreach ($orderStatusArr as $statusArr) {
            if ($statusArr['oshistory_orderstatus_id'] != FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END') || $statusArr ['oshistory_fullfillment_type'] != OrderProduct:: RENTAL_ORDER_RETURN_TYPE_DROP || 1 > $statusArr['oshistorydropoff_addr_id']) {
                continue;
            }
            $addObj = new Address($statusArr['oshistorydropoff_addr_id'], $this->adminLangId);
            $addressDataArr[$statusArr ['oshistory_id']] = $addObj->getData(Address :: TYPE_SHOP_PICKUP, $statusArr['op_shop_id']);
        }
        return $addressDataArr;
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
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = BuyerLateChargesHistory::getSearchObject();
        $srch->joinTable(OrderProduct::DB_TBL, 'INNER JOIN', 'op_id = charge_op_id', 'op');
        $srch->joinTable(User::DB_TBL, 'INNER JOIN', 'buyer.user_id = charge_user_id', 'buyer');
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'buyer_cred.credential_user_id = buyer.user_id', 'buyer_cred');
        
        $srch->joinTable(User::DB_TBL, 'INNER JOIN', 'seller.user_id = op_selprod_user_id', 'seller');
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'seller_cred.credential_user_id = seller.user_id', 'seller_cred');
        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', 'shop_user_id = seller.user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shoplang_shop_id = shop_id AND shoplang_lang_id ='. $this->adminLangId, 'shopLng');
        
        $srch->addMultipleFields(['charges.*', 'op_invoice_number', 'op_order_id', 'op_id', 'buyer.user_name as buyer_name', 'seller.user_name as seller_name', 'IFNULL(shop_name, shop_identifier) as shop_name', 'buyer_cred.credential_email as buyer_email', 'buyer_cred.credential_username as buyer_username', 'buyer.user_dial_code', 'buyer.user_phone as buyer_phone', 'buyer.user_id as buyer_id', 'seller_cred.credential_email as seller_email', 'seller_cred.credential_username as seller_username', 'seller.user_dial_code', 'seller.user_phone as seller_phone', 'seller.user_id as seller_id', 'shop_id']);
        
        if (isset($post['buyer']) && trim($post['buyer']) != '') {
            $cnd = $srch->addCondition('buyer.user_name', 'LIKE', '%'. trim($post['buyer']) .'%');
            $cnd->attachCondition('buyer_cred.credential_email', 'LIKE', '%'. trim($post['buyer']) .'%', 'OR');
            $cnd->attachCondition('buyer_cred.credential_username', 'LIKE', '%'. trim($post['buyer']) .'%');
        }
        
        if (isset($post['keyword']) && trim($post['keyword']) != '') {
            $cnd = $srch->addCondition('op_invoice_number', 'LIKE', '%'. trim($post['keyword']) .'%');
            $cnd->attachCondition('op_order_id', 'LIKE', '%'. trim($post['keyword']) .'%');
        }
        
        if (isset($post['shop_name']) && trim($post['shop_name']) != '') {
            $cnd = $srch->addCondition('seller.user_name', 'LIKE', '%'. trim($post['shop_name']) .'%');
            $cnd->attachCondition('seller_cred.credential_email', 'LIKE', '%'. trim($post['shop_name']) .'%', 'OR');
            $cnd->attachCondition('seller_cred.credential_username', 'LIKE', '%'. trim($post['shop_name']) .'%');
            $cnd->attachCondition('shop_name', 'LIKE', '%'. trim($post['shop_name']) .'%');
            $cnd->attachCondition('shop_identifier', 'LIKE', '%'. trim($post['shop_name']) .'%');
            $cnd->attachCondition('seller.user_phone', 'LIKE', '%'. trim($post['shop_name']) .'%');
        }
        
        $srch->setPageNumber($page);
        $srch->addOrder('charge_status', 'ASC');
        $srch->addOrder('charge_op_id', 'DESC');
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $chargesListing = FatApp::getDb()->fetchAll($rs);

        $this->set('chargesListing', $chargesListing);
        $this->set('chargesSmountType', LateChargesProfile::getAmountType($this->adminLangId));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('rentalDurationType', ProductRental::durationTypeArr($this->adminLangId));
        $this->set('statusArr', BuyerLateChargesHistory::chargesStatusArr($this->adminLangId));
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->_template->render(false, false);
    }
    
    private function getSearchForm()
    {
        $frm = new Form('frmChargesSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addTextBox(Labels ::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Shop', $this->adminLangId), 'shop_name');
        $frm->addHiddenField('', 'page');
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
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

        $srch = new OrderCancelRequestSearch($this->adminLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', 'op.op_id = opst.opsetting_op_id', 'opst');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = op.op_id', 'ops');
        $srch->addMultipleFields(array('ocrequest_refund_amount', 'ocrequest_hours_before_rental', 'opd_rental_start_date', 'ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'op_id', 'op_qty', 'op_unit_price', 'op_rounding_off', 'opd_rental_security', 'opcc.*', 'buyer.user_name as buyer_name', 'buyer.user_id', 'op_commission_percentage', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id'));
        $srch->addOrder('ocrequest_date', 'DESC');
        $srch->addCondition('ocrequest_is_penalty_applicable', '=', applicationConstants::YES);
        
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
    
}