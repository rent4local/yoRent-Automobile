
<?php

class InvoicesController extends LoggedUserController
{

    public function __construct($action)
    {
        parent::__construct($action);
        if (UserAuthentication::isGuestUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('account'));
        }
    }

    private function createInvoice(string $orderId)
    {
        $invoiceData = Invoice::getInvoiceDetailsByOrderId($orderId);
        if (empty($invoiceData)) {
            $orderDetail = $this->getOrderDetails($orderId);
            if (empty($orderDetail) || (isset($orderDetail['invoice_status']) && $orderDetail['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER)) {
                if (User::canAccessSupplierDashboard() || User::isSellerVerified($this->userParentId)) {
                    FatApp::redirectUser(UrlHelper::generateUrl('seller', 'sales'));
                } else {
                    Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
                    FatApp::redirectUser(UrlHelper::generateUrl('home'));
                }
            }
            $rfqDetails = RequestForQuote::getAttributesById($orderDetail['order_rfq_id'], array('rfq_from_date', 'rfq_to_date'));
            

            $totalAmountToPay = $orderDetail['order_net_amount'] - $orderDetail['total_paid_amount'];

            $data['invoice_order_id'] = $orderId;
            $data['invoice_payment_amount'] = $totalAmountToPay;
            $data['invoice_status'] = Invoice::INVOICE_IS_PENDING;
            $data['invoice_delivery_from_date'] = $rfqDetails['rfq_from_date'];
            $data['invoice_delivery_to_date'] = $rfqDetails['rfq_to_date'];
         
            $invObj = new Invoice(0, $orderId);
            $invObj->assignValues($data);
            if (!$invObj->save()) {
                Message::addErrorMessage($invObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
    }
	
	public function create(string $orderId)
    {
        if (!$this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
            exit;
        }
        $orderDetail = $this->getOrderDetails($orderId);

        if (empty($orderDetail) || (isset($orderDetail['invoice_status']) && $orderDetail['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER)) {
            if (User::canAccessSupplierDashboard() || User::isSellerVerified($this->userParentId)) {
                //Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('seller', 'sales'));
            } else {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('home'));
            }
        }
        $rfqStatus = RequestForQuote::getRfqStatus($orderDetail['order_rfq_id']);
        if ($rfqStatus['rfq_status'] == RequestForQuote::REQUEST_QUOTE_VALIDITY) {
            Message::addErrorMessage(Labels::getLabel("LBL_Quote_Expired", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
            exit;
        }

        $totalAmountToPay = $orderDetail['order_net_amount'] - $orderDetail['total_paid_amount'];
        $frm = $this->getForm($orderDetail['order_payment_status'], $totalAmountToPay);
        $invoiceData = Invoice::getInvoiceDetailsByOrderId($orderId);

        if (empty($invoiceData)) {
            $invoiceData['invoice_order_id'] = $orderId;
            $coObj = new CounterOffer(0, $orderDetail['order_rfq_id']);

            $offerDetails = $coObj->getFinalOfferByRfqId(true);
            $invoiceData['invoice_delivery_duration'] = $offerDetails['counter_offer_from_date'];
        }
        $addresses = [];
        if(Shipping::FULFILMENT_PICKUP == $orderDetail['rfq_fulfilment_type']){
            $obj = new Address($orderDetail['rfq_pickup_address_id']);
            $addresses = $obj->getData(Address::TYPE_SHOP_PICKUP, $orderDetail['shop_id'], $this->siteLangId);
        }else{
            $obj = new Address($orderDetail['rfq_shipping_address_id']);
            $addresses = $obj->getData(Address::TYPE_USER, $orderDetail['rfq_user_id'], $this->siteLangId);
        }
        
		$frm->fill($invoiceData);
        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('orderId', $orderId);
        $this->set('addresses', $addresses);
        $this->set('orderDetails', $orderDetail);

        $this->_template->render();
    }
	
	public function generateInvoice()
    {
        if (!$this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* NEED TO ADD CONDITION FOR VALID SELLER */
        $orderId = FatApp::getPostedData('invoice_order_id', FatUtility::VAR_STRING, '');
        if ($orderId == '') {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderDetails = $this->getOrderDetails($orderId);

        if (empty($orderDetails)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $totalAmountToPay = $orderDetails['order_net_amount'] - $orderDetails['total_paid_amount'];
        $frm = $this->getForm($orderDetails['order_payment_status'], $totalAmountToPay);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if ($post === false) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $invoiceId = $post['invoice_id'];
		
		/* $post['invoice_status'] = Invoice::INVOICE_IS_PENDING; */
		$post['invoice_status'] = Invoice::INVOICE_IS_SHARED_WITH_BUYER;
        unset($post['invoice_id']);

        $invObj = new Invoice($invoiceId, $orderId);
        $invObj->assignValues($post);
        if (!$invObj->save()) {
            Message::addErrorMessage($invObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* [ UPDATE INVOICE REQUEST STATUS  */
        $dataToSave = array(
            'inreq_order_id' => $orderId,
            'inreq_status' => InvoiceRequest::INVOICE_REQUEST_COMPLETE,
        );

        $invReqObj = new InvoiceRequest($orderId);
        if (!$invReqObj->saveInvoiceRequest($dataToSave)) {
            Message::addErrorMessage($invReqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */
        
        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $this->siteLangId);
        $tpl->set('orderInfo', $orderDetails);
        $invoiceTableHtml = $tpl->render(false, false, '_partial/emails/invoice-email.php', true);
        $data = [
            'shop_logo' => FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopEmailLogo', array($orderDetails['op_shop_id'], $this->siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'),
            'user_id' => $orderDetails['user_id'],
            'user_name' => $orderDetails['buyer_name'],
            'user_email' => $orderDetails['buyer_email'],
            'invoice_url' => CommonHelper::generateFullUrl('RfqCheckout', 'index', [$orderId], CONF_WEBROOT_FRONT_URL),
            'invoice_details_table' => $invoiceTableHtml,
            'order_id' => $orderDetails['order_id']
        ];


        $emailHandler = new EmailHandler();
        if (!$emailHandler->sendInvoiceLink(FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1), $data)) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        
        $invoiceId = $invObj->getMainTableRecordId();
        $this->set('invoiceId', $invoiceId);
        $this->set('controllerName', ($orderDetails['rfq_request_type'] == applicationConstants::ORDER_TYPE_SALE) ? "seller" : "sellerOrders");
        $this->set('orderId', $orderDetails['op_id']);
        $this->set('msg', Labels::getLabel('MSG_Invoice_Generated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
	
	public function invoiceRequestForm(string $orderId)
    {
        $frm = $this->getRequestForm();
        $frm->fill(['inreq_order_id' => $orderId]);
        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    public function requestForInvoice()
    {
        $frm = $this->getRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if ($post === false) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = $this->getOrderSrchObj($post['inreq_order_id']);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $dataToSave = array(
            'inreq_order_id' => $post['inreq_order_id'],
            'inreq_reason' => $post['inreq_reason'],
            'inreq_user_id' => UserAuthentication::getLoggedUserId(),
            'inreq_status' => InvoiceRequest::INVOICE_REQUEST_PENDING,
            'inreq_added_on_date' => date('Y-m-d H:i:s'),
            'inreq_seller_user_id' => $orderDetail['op_selprod_user_id']
        );

        $invReqObj = new InvoiceRequest($post['inreq_order_id']);
        if (!$invReqObj->saveInvoiceRequest($dataToSave)) {
            Message::addErrorMessage($invReqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* [ UPDATE INVOICE STATUS */
        $invoiceObj = new Invoice($orderDetail['invoice_id']);
        $invoiceObj->assignValues(['invoice_status' => Invoice::INVOICE_IS_PENDING]);
        if (!$invoiceObj->save()) {
            Message::addErrorMessage($invoiceObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $this->siteLangId);
        $tpl->set('orderInfo', $orderDetail);
        $invoiceTableHtml = $tpl->render(false, false, '_partial/emails/invoice-email.php', true);

        $data = [
            'shop_logo' => FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopEmailLogo', array($orderDetail['op_shop_id'], $this->siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'),
            'user_id' => $orderDetail['user_id'],
            'user_name' => $orderDetail['user_name'],
            'user_email' => $orderDetail['credential_email'],
            'invoice_url' => CommonHelper::generateFullUrl('Invoices', 'create', [$post['inreq_order_id']], CONF_WEBROOT_FRONT_URL),
            'invoice_details_table' => $invoiceTableHtml,
            'order_id' => $orderDetail['order_id'],
            'seller_id' => $orderDetail['selprod_user_id'],
            'seller_name' => $orderDetail['op_shop_owner_name']. ' - '. $orderDetail['op_shop_name'],
            'product_name' => $orderDetail['op_selprod_title'],
            'seller_email' => $orderDetail['op_shop_owner_email']
        ];

        $emailHandler = new EmailHandler();
        if (!$emailHandler->requestForInvoice(FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1), $data)) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Your_Request_is_Submitted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getOrderDetails($orderId)
    {
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'invoice.invoice_order_id = op.op_order_id', 'invoice');
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinOrderUser();
        $srch->joinRfq();
        $srch->joinWithProduct();
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        $srch->addCondition('op_order_id', '=', $orderId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::YES);
        $srch->addCondition('order_payment_status', 'IN', Orders::getUnpaidStatus());
        $srch->addCondition('op_status_id', '!=', FatApp::getConfig('CONF_DEFAULT_CANCEL_ORDER_STATUS'));
        $srch->addMultipleFields(  
            array('rfq.*','order_net_amount', 'order_id', 'order_payment_status', 'IFNULL(invoice_status, 0) as invoice_status', 'order_rfq_id', 
            'order_tax_charged', 'op_actual_shipping_charges','selprod_title','selprod_product_id', 'selprod_price', 'shop_id','product_updated_on','IF(selprod_stock > 0, 1, 0) AS in_stock','user_name as buyer_name','selprod_id', 'user_id', 'credential_email as buyer_email', 'invoice.*', 'op_shop_name as shop_name', 'order_net_amount as total_amount', 'op_shop_owner_name as shop_owner', 'op_shop_id', 'opd.opd_rental_start_date', 'opd.opd_rental_end_date', 'op_id')
        );
    
        $rs = $srch->getResultSet();
        $orderData = FatApp::getDb()->fetch($rs);

        if ($orderData == false) {
            return [];
        }

        $orderData['total_paid_amount'] = 0;
        // if ($orderData['order_payment_status'] == Orders::ORDER_PAYMENT_PENDING) {
        //     $obj = new Orders();
        //     $orderData['total_paid_amount'] = $obj->getOrderPaymentPaid($orderId);
        // }
        return $orderData;
    }

	private function getForm($orderStatus, $totalAmountToPay)
    {
        $frm = new Form('frmInvoice');
      
        $frm->addHiddenField('', 'invoice_payment_amount', $totalAmountToPay, ['data-totalamount' => $totalAmountToPay]);
        
        $frm->addTextBox(Labels::getLabel("LBL_Expected_Delivery/Pickup_Date", $this->siteLangId), 'invoice_delivery_date', '', array('readonly' => 'readonly', 'class' => 'delivery-date-picker--js'));
        $frm->addTextarea(Labels::getLabel('LBL_Description', $this->siteLangId), 'invoice_description');
        $frm->addHiddenField('', 'invoice_order_id');
        $frm->addHiddenField('', 'invoice_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Generate_Invoice', $this->siteLangId));
        return $frm;
    }



    /* public function view($orderId, $print = false)
    {
        if (!$this->userPrivilege->canViewInvoices(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }
        
        $this->createInvoice($orderId);

        $this->setInvoiceCommonData($orderId, 2);
        // $invoiceData = Invoice::getInvoiceDetailsByOrderId($orderId);
        $this->set('print', $print);
        $this->set('action', 'view');
        $this->set('is_buyer', false);
        $this->set('is_seller', true);
        $this->set('canEdit', $this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render();
    } */

    private function setInvoiceCommonData($orderId, int $type) /* $type 1 = Buyer 2 = Seller */
    {
        $srch = $this->getOrderSrchObj($orderId);
        $srch->joinTable(RequestForQuote::DB_TBL, 'INNER JOIN', 'order_rfq_id = rfq_id', 'rfq');
        $srch->addCondition('order_payment_status', 'IN', Orders::getUnpaidStatus());

        if ($type == 1) {
            $srch->addCondition('order_user_id', '=', UserAuthentication::getLoggedUserId());
            // $srch->addCondition('invoice_status', '=', Invoice::INVOICE_IS_SHARED_WITH_BUYER);
        } else {
            $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
            // $srch->addCondition('invoice_status', 'IN', [Invoice::INVOICE_IS_SHARED_WITH_BUYER, Invoice::INVOICE_IS_PENDING]);
        }
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);
        $rfqStatus = RequestForQuote::getRfqStatus($orderDetail['order_rfq_id']);
        if ($rfqStatus['rfq_status'] == RequestForQuote::REQUEST_QUOTE_VALIDITY) {
            Message::addErrorMessage(Labels::getLabel("LBL_Quote_Expired", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('requestForQuotes', 'quotedRequests'));
            exit;
        }
        
        if (empty($orderDetail)) {
            if ($type == 2) {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Invoice_URL_OR_Invoice_is_already_paid", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('requestForQuotes', 'listing'));
            } else {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Invoice_URL_OR_Invoice_is_already_paid", $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('requestForQuotes', 'quotedRequests'));
            }
            exit;
        }

        $orderObj = new Orders();
        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();
        $orderDetail['pickupAddress'] = (isset($address[Orders::PICKUP_ADDRESS_TYPE])) ? $address[Orders::PICKUP_ADDRESS_TYPE] : array();
        $taxOptions = json_decode($orderDetail['op_product_tax_options'], true);
        $orderDetail['taxOptions'] = $taxOptions;

        $invoicePaymentHistory = $orderObj->getOrderPayments(array("order_id" => $orderDetail['order_id']));
        $orderDetail['total_paid_amount'] = 0;
        if (!empty($invoicePaymentHistory)) {
            $orderDetail['total_paid_amount'] = array_sum(array_column($invoicePaymentHistory, 'opayment_amount'));
        }

        $charges = $orderObj->getOrderProductChargesByOrderId($orderDetail['order_id']);
        $orderDetail['charges'] = $charges[$orderDetail['op_id']];

        $shopAddress = Shop::getShopAddress($orderDetail['op_shop_id'], true, $this->siteLangId);
        $this->set('shopAddress', $shopAddress);

        $this->set('orderDetail', $orderDetail);
        $this->set('paymentHistory', $invoicePaymentHistory);
        $this->set('orderStatuses', Orders::getOrderProductStatusArr($this->siteLangId));
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        // $this->set('paymentTypes', Orders::getPaymentTypeArr($this->siteLangId));
    }

    private function getOrderSrchObj($orderId)
    {
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinTable(Invoice::DB_TBL, 'INNER JOIN', 'invoice.invoice_order_id = op.op_order_id', 'invoice');
        $srch->joinTable(InvoiceRequest::DB_TBL, 'LEFT OUTER JOIN', 'invoiceReq.inreq_order_id = op.op_order_id AND inreq_status = '. InvoiceRequest::INVOICE_REQUEST_PENDING , 'invoiceReq');
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addCondition('op_order_id', '=', $orderId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::YES);
        $srch->addCondition('op_status_id', '!=', FatApp::getConfig('CONF_DEFAULT_CANCEL_ORDER_STATUS'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        return $srch;
    }

    /* public function payInvoice(string $orderId, $print = false)
    {
        $this->setInvoiceCommonData($orderId, 1);
        $this->set('print', $print);
        $this->set('action', 'payInvoice');
        $this->set('is_buyer', true);
        $this->set('is_seller', false);
        $this->_template->addCss(array('invoices/page-css/view.css'));
        if ($print) {
            $this->_template->render(true, true, 'invoices/print-invoice-view.php');
        } else {
            $this->_template->render(true, true, 'invoices/view.php');
        }
    } */

    public function sendInvoice(string $orderId)
    {
        if (!$this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinOrderUser();
        $srch->joinTable(Invoice::DB_TBL, 'INNER JOIN', 'invoice.invoice_order_id = op.op_order_id', 'invoice');
        $srch->joinSellerProducts();
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        $srch->addCondition('op_order_id', '=', $orderId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::YES);
        $srch->addMultipleFields(
                array(
                    'order_id', 'user_id', 'credential_email as buyer_email', 'user_name as buyer_name',
                    'selprod_title', 'invoice.*', 'op_shop_name as shop_name', 'order_net_amount as total_amount',
                    'op_shop_owner_name as shop_owner', 'op_shop_id'
                )
        );
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new Orders();
        $totalPaidAmount = $obj->getOrderPaymentPaid($orderId);
        $orderDetail['total_paid_amount'] = $totalPaidAmount;

        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $this->siteLangId);
        $tpl->set('orderInfo', $orderDetail);
        $invoiceTableHtml = $tpl->render(false, false, '_partial/emails/invoice-email.php', true);
        $data = [
            'shop_logo' => FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'shopEmailLogo', array($orderDetail['op_shop_id'], $this->siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'),
            'user_id' => $orderDetail['user_id'],
            'user_name' => $orderDetail['buyer_name'],
            'user_email' => $orderDetail['buyer_email'],
            'invoice_url' => CommonHelper::generateFullUrl('Invoices', 'PayInvoice', [$orderId], CONF_WEBROOT_FRONT_URL),
            'invoice_details_table' => $invoiceTableHtml,
            'order_id' => $orderDetail['order_id']
        ];


        /* [ UPDATE INVOICE STATUS TO SHARED */
        $dataToUpdate = array('invoice_status' => invoice::INVOICE_IS_SHARED_WITH_BUYER, 'invoice_added_on' => date('Y-m-d H:i:s'));
        $inObj = new Invoice($orderDetail['invoice_id']);
        $inObj->assignValues($dataToUpdate);
        if (!$inObj->save()) {
            Message::addErrorMessage($inObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $emailHandler = new EmailHandler();
        if (!$emailHandler->sendInvoiceLink(FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1), $data)) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }


        // $this->set('msg', Labels::getLabel('MSG_Invoice_Mail_Sent_to_Buyer', $this->siteLangId));
        Message::addMessage(Labels::getLabel('MSG_Invoice_Mail_Sent_to_Buyer', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
	
	private function getRequestForm()
    {
        $frm = new Form('frmInvoiceRequest');
        $frm->addTextarea(Labels::getLabel('LBL_Reason', $this->siteLangId), 'inreq_reason');
        $frm->addHiddenField('', 'inreq_order_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));
        return $frm;
    }
	
	public function invoiceRequests()
    {
        if (!$this->userPrivilege->canViewInvoices(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }

        $frmSearch = $this->searchForm($this->siteLangId);
        $this->set("frmSearch", $frmSearch);
        $this->_template->render();
    }

    public function searchRequests()
    {
        $frmSearch = $this->searchForm();
        $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = InvoiceRequest::getSearchObj();
        $srch->addCondition('inreq_seller_user_id', '=', $this->userParentId);
        $srch->joinTable(User::DB_TBL, 'INNER JOIN', 'user_id = inreq.inreq_user_id AND user_deleted = 0', 'buyer_user');
        $srch->addMultipleFields(array('inreq.*', 'buyer_user.user_name as buyer_name'));

        $srch->setPageSize($pagesize);
        $srch->setPageNumber($page);

        $rs = $srch->getResultSet();
        $requestListing = FatApp::getDB()->fetchAll($rs);

        $this->set('statusArr', InvoiceRequest::getRequestStatusArr($this->siteLangId));
        $this->set('listingData', $requestListing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set("siteLangId", $this->siteLangId);
        $this->set("canEdit", $this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }
	
	private function searchForm()
    {
        $frm = new Form('frmSearchQuotesRequests');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addHiddenField('', 'page');
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }
	
}