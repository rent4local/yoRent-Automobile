<?php

class RequestForQuotesController extends LoggedUserController
{
    private $shippingService;

    public function __construct($action)
    {
        parent::__construct($action);
        if (UserAuthentication::isGuestUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
    }

    public function form(int $selProdId)
    {
        if (1 > $selProdId) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $parentId = FatApp::getPostedData('parent_id', FatUtility::VAR_INT, 0);
		$services = FatApp::getPostedData('rental_addons', FatUtility::VAR_INT, []);
        $servicesArr = [];
        if (!empty($services)) {
            $servicesArr = $this->getServiceArrWithData(array_keys($services));
        }
		
		if ($parentId > 0) {
            $rfqObj = new RequestForQuote($parentId);
            $servicesArr = $rfqObj->getAttachedServices($this->siteLangId);
        }

        /* fetch requested product[ */
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinProductSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selProdId);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->doNotLimitRecords();
        $prodSrch->addMultipleFields(
                array(
                    'shop_id','product_id', 'selprod_type', 'selprod_fulfillment_type', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name', 'product_seller_id', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title')
        );

        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */
        
        if (!$product) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        // $addresses = UserAddress::getUserAddresses(UserAuthentication::getLoggedUserId(), $this->siteLangId);
        $obj = new Address();
        $addresses = $obj->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId(), $this->siteLangId);

        $shopAddress = $obj->getData(Address::TYPE_SHOP_PICKUP, $product['shop_id']);

        $frm = $this->getForm($services, -1);
        $frm->fill(array('selprod_id' => $selProdId, 'parent_id' => $parentId, 'shop_id'=>$product['shop_id']));

        $this->set('frm', $frm);
		$this->set('services', $servicesArr);
        $this->set('addresses', $addresses);
        $this->set('shopAddress', $shopAddress);
        $this->set('productData', $product);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function getForm(array $services = [], $type = -1)
    {
        $frm = new Form('rfqFrm');
        // $qtyFld = $frm->addTextBox(Labels::getLabel('LBL_Required_capacity', $this->siteLangId), 'rfq_required_capacity');
        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Required_Quantity", $this->siteLangId), 'rfq_quantity','1');
        $qtyFld->requirements()->setPositive();
        
        $orderTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->siteLangId), 'rfq_request_type', applicationConstants::getOrderTypeArr($this->siteLangId), '', array(), Labels::getLabel('LBL_Type', $this->siteLangId));
        $orderTypeFld->requirement->setRequired(true);

        $shipArr = Shipping::getFulFillmentArr($this->siteLangId, $type,1);
        unset($shipArr[Shipping::FULFILMENT_ALL]);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_TYPE', $this->siteLangId), 'rfq_fulfilment_type', $shipArr, '', array('id'=>'rfq_fulfilment_type'), Labels::getLabel('LBL_FULFILLMENT_TYPE', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel("LBL_From_&_To_Date", $this->siteLangId), 'rfq_date_range', '', array('class' => 'rfq_dates--js', 'readonly' => 'readonly'));
        $frm->addHiddenField('', 'rfq_from_date');
        $frm->addHiddenField('', 'rfq_to_date');

        $dateReq = new FormFieldRequirement('rfq_date_range', Labels::getLabel('LBL_From_date', $this->siteLangId));
        $dateReq->setRequired(true);

        $dateUnReq = new FormFieldRequirement('rfq_date_range', Labels::getLabel('LBL_From_date', $this->siteLangId));
        $dateUnReq->setRequired(false);
        
        $orderTypeFld->requirements()->addOnChangerequirementUpdate(applicationConstants::PRODUCT_FOR_RENT, 'eq', 'rfq_date_range', $dateReq);
        $orderTypeFld->requirements()->addOnChangerequirementUpdate(applicationConstants::PRODUCT_FOR_SALE, 'eq', 'rfq_date_range', $dateUnReq);

        
        $frm->addTextarea(Labels::getLabel('LBL_Comment_for_Seller', $this->siteLangId), 'rfq_comments');
		
		if (!empty($services)) {
            foreach ($services as $serviceId => $val) {
                $frm->addTextBox(Labels::getLabel('LBL_Required_capacity', $this->siteLangId), 'rfq_required_capacity_service[' . $serviceId . ']');
                $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Required_Quantity", $this->siteLangId), 'rfq_quantity_service[' . $serviceId . ']');
                $qtyFld->requirements()->setPositive();
                $frm->addHiddenField('', 'service_group_id[' . $serviceId . ']');
            }
        }
		
        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'shop_id');
        $frm->addHiddenField('', 'group_id');
        $frm->addHiddenField('', 'parent_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit_request', $this->siteLangId));

        return $frm;
    }

    public function setup()
    {
        $quantity = FatApp::getPostedData('rfq_quantity', FatUtility::VAR_INT, 0);
        $rfq_pickup_address_id = FatApp::getPostedData('rfq_pickup_address_id', FatUtility::VAR_INT, 0);
        $rfq_ship_address_id = FatApp::getPostedData('rfq_ship_address_id', FatUtility::VAR_INT, 0);
        $parentId = FatApp::getPostedData('parent_id', FatUtility::VAR_INT, 0);
		$servicesCapacities = FatApp::getPostedData('rfq_required_capacity_service', FatUtility::VAR_STRING, []);
        $servicesQuantities = FatApp::getPostedData('rfq_quantity_service', FatUtility::VAR_INT, []);

        $frm = $this->getForm();
        $postedData = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (!User::isBuyer()) {
            Message::addErrorMessage(Labels::getLabel("LBL_You_Must_Logged_in_as_buyer_for_Submit_RFQ", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fulfilmentType = $postedData['rfq_fulfilment_type'];

        if (1 > $quantity) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Quantity", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (0 < $parentId) {
            $reqForQuote = new RequestForQuote($parentId);
            $rfqDetail = $reqForQuote->getRequestDetail(UserAuthentication::getLoggedUserId());
            if (empty($rfqDetail)) {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request_for_Re-Quote", $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        if (1 > $rfq_ship_address_id && 1 > $rfq_pickup_address_id) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Address", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shipAddressId = 0;
        if($fulfilmentType == Shipping::FULFILMENT_SHIP){
            $billAddressId = $rfq_ship_address_id;
            $shipAddressId = $rfq_ship_address_id;

        }else{
            if (1 > $rfq_ship_address_id || 1 > $rfq_pickup_address_id) {
                Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Address", $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $billAddressId = $rfq_ship_address_id;
        }
        
        

        $data = array(
            'rfq_quantity' => $quantity,
            'rfq_user_id' => UserAuthentication::getLoggedUserId(),
            'rfq_selprod_id' => $postedData['selprod_id'],
            // 'rfq_capacity' => $postedData['rfq_required_capacity'],
            'rfq_from_date' => $postedData['rfq_from_date'],
            'rfq_to_date' => $postedData['rfq_to_date'],
            'rfq_parent_id' => $parentId,
            'rfq_request_type' => $postedData['rfq_request_type'],
            'rfq_comments' => $postedData['rfq_comments'],
            'rfq_fulfilment_type' => $fulfilmentType,
            'rfq_shipping_address_id' => $shipAddressId,
            'rfq_pickup_address_id' => $rfq_pickup_address_id,
            'rfq_billing_address_id' => $billAddressId,
            'rfq_added_on' => date('Y-m-d H:i:s'),
            'rfq_status' => RequestForQuote::REQUEST_INPROGRESS,
        );

        $reqForQuote = new RequestForQuote();
        $reqForQuote->assignValues($data);
        if (!$reqForQuote->save()) {
            Message::addErrorMessage(Labels::getLabel("LBL_something_went_wrong_Please_contact_with_administrator", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rfqId = $reqForQuote->getMainTableRecordId();
		if (!empty($servicesQuantities)) {
            if (!$this->updateSericesWithProduct($rfqId, $servicesQuantities, $servicesCapacities)) {
                Message::addErrorMessage($this->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $groupId = FatApp::getPostedData('group_id', FatUtility::VAR_INT, 0);
        if (0 < $groupId) {
            $shiftRfqDocuments = $this->shiftRfqDocuments($rfqId, $groupId);
            if ($shiftRfqDocuments === true) {
                $db = FatApp::getDb();
                if (!$db->deleteRecords(AttachedFile::DB_TBL_TEMP, array('smt' => 'afile_record_subid = ? ', 'vals' => array($groupId)))) {
                    Message::addErrorMessage($db->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }

        if ($parentId > 0) {
            $reqForQuote = new RequestForQuote($parentId);
            $response = $reqForQuote->updateStatus(RequestForQuote::REQUEST_RE_QUOTED);
            if (false === $response) {
                Message::addErrorMessage($reqForQuote->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* [ SEND EMAIL NOTIFICATION FOR NEW RFQ */
        $emailHandler = new EmailHandler();
        if (!$emailHandler->newRfqNotificationSeller($this->siteLangId, $rfqId)) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $this->set('msg', Labels::getLabel("LBL_Request_submitted_successfully", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    // public function listing()
    // {
    //     $frmSearch = $this->searchForm($this->siteLangId);
    //     $this->set("frmSearch", $frmSearch);
    //     $this->_template->render();
    // }

    private function searchForm()
    {
        $frm = new Form('frmSearchQuotesRequests');
        $frm->addTextBox('', 'keyword');
        $frm->addSelectBox('', 'prod_type', SellerProduct::selProdType($this->siteLangId), '', array(), Labels::getLabel('LBL_Type', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addHiddenField('', 'page');
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    // public function search()
    // {
    //     $frmSearch = $this->searchForm();
    //     $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());

    //     $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
    //     $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

    //     $srch = new RequestForQuoteSearch();
    //     $srch->joinWithSellerProduct($this->siteLangId);
    //     $srch->addMultipleFields(array('count(rfq_id) as requestd_count', 'selprod_title', 'rfq_selprod_id', 'IFNULL(unread.unread_count, 0) as unread_count'));
    //     $srch->addCondition('selprod_user_id', '=', $this->userParentId);
    //     $srch->addCondition('rfq_parent_id', '=', applicationConstants::NO);
    //     $srch->addGroupBy('rfq_selprod_id');
    //     $srch->setPageSize($pagesize);
    //     $srch->setPageNumber($page);

    //     $srchUnreadCount = new RequestForQuoteSearch();
    //     $srchUnreadCount->doNotCalculateRecords();
    //     $srchUnreadCount->doNotLimitRecords();
    //     $srchUnreadCount->addCondition('rfq_marked_read', '=', applicationConstants::NO);
    //     $srch->addCondition('rfq_parent_id', '=', applicationConstants::NO);
    //     $srchUnreadCount->addGroupBy('rfq_selprod_id');
    //     $srchUnreadCount->addMultipleFields(array('count(rfq_id) as unread_count', "rfq_selprod_id as unread_selprod_id"));
    //     $srchUnreadCountQuery = $srchUnreadCount->getQuery();
    //     $srch->joinTable('(' . $srchUnreadCountQuery . ')', 'LEFT OUTER JOIN', 'unread.unread_selprod_id = rfq_selprod_id', 'unread');

    //     if ($keyword = FatApp::getPostedData('keyword')) {
    //         $srch->addCondition('selprod_title', 'like', "%$keyword%");
    //     }

    //     if (isset($post['prod_type']) && intval($post['prod_type']) != '') {
    //         $srch->addCondition('selprod_type', '=', intval($post['prod_type']));
    //     }

    //     $srchRs = $srch->getResultSet();
    //     $records = FatApp::getDb()->fetchAll($srchRs);

    //     $this->set("arr_listing", $records);
    //     $this->set('pageCount', $srch->pages());
    //     $this->set('recordCount', $srch->recordCount());
    //     $this->set('page', $page);
    //     $this->set('pageSize', $pagesize);
    //     $this->set('postedData', $post);
    //     $this->set("siteLangId", $this->siteLangId);
    //     $this->_template->render(false, false);
    // }

    public function productQuotes()
    {
        if (!$this->userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true) || (!User::canAccessSupplierDashboard() || !User::isSellerVerified($this->userParentId))) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('seller'));
        }

        $frmSearch = $this->searchProdQuotesForm(RequestForQuote::INPROGRESS_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::INPROGRESS_LIST ));

        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_RFQ_Listings_(_In-Progress_)', $this->siteLangId));
        $this->_template->render();
    }

    private function validateSellerProduct(int $selProdId): bool
    {
        $sellerProductRow = SellerProduct::getAttributesById($selProdId);
        if (empty($sellerProductRow) || ($sellerProductRow['selprod_user_id'] != $this->userParentId)) {
            return false;
        }
        return true;
    }

    private function searchQuoteRequests()
    {
        $frm = new Form('frmSearchQuotesRequests');
        $frm->addTextBox('', 'keyword');
        $frm->addDateField('', 'request_from_date', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField('', 'request_to_date', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addSelectBox('', 'prod_type', SellerProduct::selProdType($this->siteLangId), '', array(), Labels::getLabel('LBL_Type', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addHiddenField('', 'page');
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    // public function searchProdQuotes()
    // {
    //     $frmSearch = $this->searchProdQuotesForm();
    //     $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());
    //     if (false === $post) {
    //         Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
    //         FatUtility::dieJsonError(Message::getHtml());
    //     }

    //     // $checkValidProd = $this->validateSellerProduct($post['selprod_id']);
    //     // if ($checkValidProd === false) {
    //     //     Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
    //     //     FatUtility::dieJsonError(Message::getHtml());
    //     // }

    //     $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
    //     $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
    //     $srch = new RequestForQuoteSearch();
    //     $srch->joinWithSellerProduct($this->siteLangId);
    //     $srch->joinUsers();
    //     $srch->addMultipleFields(array('rfq_id', 'selprod_title', 'rfq_selprod_id', 'user_name as buyer_name', 'rfq_added_on', 'rfq_status'));
    //     $srch->addCondition('selprod_user_id', '=', $this->userParentId);
    //     // $srch->addCondition('rfq_selprod_id', '=', $post['selprod_id']);
    //     $srch->addCondition('rfq_parent_id', '=', applicationConstants::NO);
        
    //     $statusArr = array(
    //         RequestForQuote::REQUEST_INPROGRESS, 
    //         RequestForQuote::REQUEST_QUOTED,
    //         RequestForQuote::REQUEST_COUNTER_BY_BUYER,
    //         RequestForQuote::REQUEST_COUNTER_BY_SELLER
    //     );

    //     $srch->addCondition('rfq_status', 'IN', $statusArr);

    //     $srch->setPageSize($pagesize);
    //     $srch->setPageNumber($page);

    //     if ($keyword = FatApp::getPostedData('keyword')) {
    //         $cnd = $srch->addCondition('selprod_title', 'like', "%$keyword%");
    //         $cnd->attachCondition('user_name', 'like', "%" . $keyword . "%", 'OR');
    //         $keyword = str_replace("#", "", $keyword);
    //         $cnd->attachCondition('rfq_id', 'like', "%" . $keyword . "%", 'OR');
    //     }

    //     if (!empty($post['request_from_date'])) {
    //         $srch->addCondition('rfq_added_on', '>=', $post['request_from_date'] . ' 00:00:00');
    //     }

    //     if (!empty($post['request_to_date'])) {
    //         $srch->addCondition('rfq_added_on', '<=', $post['request_to_date'] . ' 23:59:59');
    //     }

    //     if (isset($post['rfq_status']) && $post['rfq_status'] != '') {
    //         $srch->addCondition('rfq_status', '=', intval($post['rfq_status']));
    //     }

    //     if (isset($post['prod_type']) && intval($post['prod_type']) != '') {
    //         $srch->addCondition('selprod_type', '=', intval($post['prod_type']));
    //     }

    //     $srch->addOrder('rfq_added_on', 'DESC');
    //     $srchRs = $srch->getResultSet();
    //     $records = FatApp::getDb()->fetchAll($srchRs);

    //     $this->set("arr_listing", $records);
    //     $this->set('pageCount', $srch->pages());
    //     $this->set('recordCount', $srch->recordCount());
    //     $this->set('page', $page);
    //     $this->set('pageSize', $pagesize);
    //     $this->set('postedData', $post);
    //     $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));

    //     $this->set("siteLangId", $this->siteLangId);
    //     $this->_template->render(false, false);
    // }

    public function view(int $rfqId)
    {
        if (1 > $rfqId || !$this->userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            FatUtility::exitWithErrorCode(404);
        }
        
        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinForShop($this->siteLangId);
        $srch->joinWithProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addMultipleFields(array('rfq.*', 'selprod_type', 'rfq_quote_validity', 'selprod_price', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title', 'product_updated_on', 'selprod_product_id', 'selprod_id', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'shop_id', 'selprod_user_id', 'selprod_stock', 'user_name', 'sprodata_duration_type', 'sprodata_rental_price', 'IF(sprodata_rental_stock > 0, 1, 0) AS rent_in_stock'));
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        
        $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'order_rfq_id = rfq_id', 'o');
        $srch->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'o.order_id = invoice.invoice_order_id', 'invoice');
        $srch->addFld(['o.order_payment_status', 'IFNULL(invoice_status, 0) as invoice_status', 'o.order_id']);
        
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        
        
        if (empty($record)) {
            FatUtility::exitWithErrorCode(404);
        }

        $quotedOfferDetail = [];
        if ($record['rfq_status'] > RequestForQuote::REQUEST_INPROGRESS) {
            $counterOffer = new CounterOffer(0, $rfqId);
            $quotedOfferDetail = $counterOffer->getDetailByStatus(RequestForQuote::REQUEST_QUOTED);
        }

        $quotedOfferForm = $this->getQuotedOfferForm($record['rfq_fulfilment_type'], $record['rfq_request_type']);
        $dataToFile = ['counter_offer_rfq_id' => $rfqId];
        if ($record['rfq_request_type'] == applicationConstants::PRODUCT_FOR_RENT) {
            $rentStartDate = date('Y-m-d', strtotime($record['rfq_from_date']));
            $rentEndDate = date('Y-m-d', strtotime($record['rfq_to_date']));
        
            $dataToFile = ['counter_offer_rfq_id' => $rfqId, 'counter_offer_from_date' => $rentStartDate , 'counter_offer_to_date' => $rentEndDate, 'counter_offer_date_range' => $rentStartDate . ' to '. $rentEndDate ];
        }
        
        $quotedOfferForm->fill($dataToFile);

        if (1 > $record['rfq_marked_read']) {
            $this->markAsRead($rfqId);
        }

        /* [ GET UPLOADED DOCUMENTS SECTION */
        $sellerAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_QUOTED_DOCUMENT, $rfqId, 0, -1);
        $etpl = new FatTemplate('', '');
        $etpl->set('rfqDetail', ['selprod_user_id' => $record['selprod_user_id']]);
        $etpl->set('siteLangId', $this->siteLangId);
        $etpl->set('attachments', $sellerAttachments);
        $etpl->set('hideNoRecordMsg', true);
        $uploadedAttachmentSection = $etpl->render(false, false, 'request-for-quotes/get-uploaded-documents.php', true);
        $this->set('uploadedAttachmentSection', $uploadedAttachmentSection);
		/* ] */
		
        /* [ GET ATTACHED SERVICES WITH RENTAL PRODUCT */
		
        /* $servicesList = [];
        if ($record['selprod_type'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $rfqObj = new RequestForQuote($rfqId);
            $servicesList = $rfqObj->getAttachedServices($this->siteLangId);
			$this->set('servicesList', $servicesList);
        }
        $this->set("attachments", AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER, $rfqId, 0, -1)); */
		
		/* ]*/
		
        $shippingAddressDetail = [];
        $billingDetails = [];
		if ($record['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP) {
            $obj = new Address($record['rfq_shipping_address_id']);
            $shippingAddressDetail = $obj->getData(Address::TYPE_USER, $record['rfq_user_id']);
            $billingDetails = $shippingAddressDetail;
            
        } else {
            $obj = new Address($record['rfq_billing_address_id']);
            $billingDetails = $obj->getData(Address::TYPE_USER, $record['rfq_user_id']);
        
            $obj = new Address($record['rfq_pickup_address_id']);
            $shippingAddressDetail = $obj->getData(Address::TYPE_SHOP_PICKUP, $record['shop_id']);
        }
        
        $action = "productQuotes";
        if (in_array($record['rfq_status'], array_keys(RequestForQuote::statusArray($this->siteLangId, RequestForQuote::APPROVED_LIST)) ) ) {
            $action = "acceptedOffers";
        } elseif(in_array($record['rfq_status'], array_keys(RequestForQuote::statusArray($this->siteLangId, RequestForQuote::REJECTED_LIST)) )) {
            $action = "rejectedOffers";
        }
        
        $this->set('rfqData', $record);
        $this->set('quotedOfferDetail', $quotedOfferDetail);
        $this->set('shippingAddressDetail', $shippingAddressDetail);
        $this->set('billingDetails', $billingDetails);
        $this->set('selProdOptions', SellerProduct::getSellerProductOptions($record['rfq_selprod_id'], true, $this->siteLangId));
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));
        $this->set('quotedOfferForm', $quotedOfferForm);
        $this->set("siteLangId", $this->siteLangId);
        $this->set("action", $action);
        $this->set("canEdit", $this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render();
    }

    private function getQuotedOfferForm($rfqFulfilmentType, $rfqReqType)
    {
        $frm = new Form('rfqFrm');

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Product_Total_Cost', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_total_cost');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setFloatPositive();
        $fld->requirements()->setRange(0, 99999999);

        if($rfqFulfilmentType != Shipping::FULFILMENT_PICKUP) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_price_cost', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_shipping_cost');
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive();
            $fld->requirements()->setRange(0, 99999999);
        }
        
        $fldValid = $frm->addDateField(Labels::getLabel('LBL_Quote_Valid_till_Date', $this->siteLangId), 'quote_validity');
        $fldValid->requirements()->setRequired(true);
        
        if ($rfqReqType != applicationConstants::PRODUCT_FOR_SALE) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Rental_Security_Amount', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_rental_security');
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive();
            $fld->requirements()->setRange(0, 99999999);

            $fld = $frm->addTextBox(Labels::getLabel("LBL_Rental_Dates", $this->siteLangId), 'counter_offer_date_range', '', array('class'=>'delivery-date-range-picker--js'));
            $fld->requirements()->setRequired(true);
            // $frm->addTextBox(Labels::getLabel("LBL_Date_To", $this->siteLangId), 'counter_offer_to_date', '', array('readonly' => 'readonly'));

            $frm->addHiddenField('', 'counter_offer_from_date');
            if($rfqFulfilmentType == Shipping::FULFILMENT_PICKUP) {
                $fldValid->requirements()->setCompareWith('counter_offer_from_date', 'le'); 
                $fldValid->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Quote_Valid_Date_Must_be_less_then_or_equal_to_rent_start_date', $this->siteLangId));
            } else {
                $fldValid->requirements()->setCompareWith('counter_offer_from_date', 'lt'); 
                $fldValid->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Quote_Valid_Date_Must_be_less_then_rent_start_date', $this->siteLangId));
            }
            
            
            $frm->addHiddenField('', 'counter_offer_to_date');
        }
		
        $frm->addTextarea(Labels::getLabel('LBL_Comments_for_Buyer', $this->siteLangId), 'counter_offer_comment');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload_document', $this->siteLangId), 'counter_offer_document');

        $frm->addHiddenField('', 'counter_offer_rfq_id');
        $frm->addHiddenField('', 'counter_offer_id');
        $frm->addHiddenField('', 'rfq_fulfilment_type', $rfqFulfilmentType);
        $frm->addHiddenField('', 'rfq_request_type',$rfqReqType);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));

        return $frm;
    }

    private function markAsRead(int $rfqId)
    {
        $whr = array('smt' => 'rfq_id = ?', 'vals' => array($rfqId));
        FatApp::getDb()->updateFromArray(RequestForQuote::DB_TBL, array('rfq_marked_read' => 1), $whr);
    }


    public function getUploadedDocuments()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $cond = $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $cond->attachCondition('rfq_user_id', '=', UserAuthentication::getLoggedUserId(), 'OR');
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $attachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_QUOTED_DOCUMENT, $rfqId, 0, -1);
        $this->set("rfqDetail", $record);
        $this->set("siteLangId", $this->siteLangId);
        $this->set("attachments", $attachments);
        $this->set("hideNoRecordMsg", false);
        $this->_template->render(false, false);
    }

    public function removeDocument()
    {
        if (!$this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);
        $afileId = FatApp::getPostedData('afile_id', FatUtility::VAR_INT, 0);
        if (1 > $rfqId || 1 > $afileId) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(AttachedFile::DB_TBL, array('smt' => 'afile_id = ? AND afile_record_id = ? ', 'vals' => array($afileId, $rfqId)))) {
            Message::addErrorMessage($db->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel('MSG_File_Removed_Successfully.', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function uploadDocument()
    {
        if (!$this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);
        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        // $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (empty($_FILES) || empty($_FILES['counter_offer_document']) || !is_uploaded_file($_FILES['counter_offer_document']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if (filesize($_FILES['counter_offer_document']['tmp_name']) > 10240000) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment($_FILES['counter_offer_document']['tmp_name'], AttachedFile::FILETYPE_QUOTED_DOCUMENT, $rfqId, 0, $_FILES['counter_offer_document']['name'], -1)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel('MSG_File_Uploaded_Successfully', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function quotedRequests()
    {
        $frmSearch = $this->quotedReqSearchForm(RequestForQuote::INPROGRESS_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::INPROGRESS_LIST ));
        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_My_Requests_(_In-Progress_)', $this->siteLangId));
        $this->_template->render();
    }

    public function acceptedBuyerOffers()
    {
        $frmSearch = $this->quotedReqSearchForm(RequestForQuote::APPROVED_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::APPROVED_LIST ));
        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_My_Requests_(_Accepted_Offers_)', $this->siteLangId));
        $this->_template->render(true, true, 'request-for-quotes/quoted-requests.php');
    }

    public function rejectedBuyerOffers()
    {
        $frmSearch = $this->quotedReqSearchForm(RequestForQuote::REJECTED_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::REJECTED_LIST ));
        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_My_Requests_(_Rejected_Offers_)', $this->siteLangId));
        $this->_template->render(true, true, 'request-for-quotes/quoted-requests.php');
    }

    private function quotedReqSearchForm(int $pageType = 1)
    {
        $arr = RequestForQuote::statusArray($this->siteLangId, $pageType);
        $frm = $this->searchQuoteRequests();
        $frm->addSelectBox('', 'rfq_status', $arr, '', array(), Labels::getLabel('LBL_Select_Status', $this->siteLangId));
        $frm->addHiddenField('', 'quote_type');
        return $frm;
    }

    // private function reQuotedReqSearchForm()
    // {
    //     $frm = $this->searchQuoteRequests();
    //     $statusArr = RequestForQuote::statusArray($this->siteLangId);
    //     unset($statusArr[RequestForQuote::REQUEST_RE_QUOTED]);
    //     $frm->addSelectBox('', 'rfq_status', $statusArr, '', array(), Labels::getLabel('LBL_Select_Status', $this->siteLangId));
    //     return $frm;
    // }

    public function searchBuyerQuotes()
    {
        $postedData = FatApp::getPostedData();
        $frmSearch = $this->quotedReqSearchForm($postedData['quote_type']);
        $post = $frmSearch->getFormDataFromArray($postedData);
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $type = $post['quote_type'];

        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);

        $srch = $this->searchQuotes($post);
        $srch->joinWithInvoice();
        $srch->addFld('invoice_status');
        
        $srch->addCondition('rfq_user_id', '=', UserAuthentication::getLoggedUserId());
        $statusArr = array(
            RequestForQuote::REQUEST_INPROGRESS, 
            RequestForQuote::REQUEST_QUOTED,
            RequestForQuote::REQUEST_COUNTER_BY_BUYER,
            RequestForQuote::REQUEST_COUNTER_BY_SELLER
        );
        if($type == RequestForQuote::APPROVED_LIST) {
            $statusArr = array(
                RequestForQuote::REQUEST_APPROVED, 
                RequestForQuote::REQUEST_ACCEPTED_BY_BUYER
            );
        }elseif($type == RequestForQuote::REJECTED_LIST){
            $statusArr = array(
                RequestForQuote::REQUEST_CANCELLED_BY_BUYER,
                RequestForQuote::REQUEST_DECLINED_BY_SELLER,
                RequestForQuote::REQUEST_CLOSED_BY_ADMIN,
                RequestForQuote::REQUEST_QUOTE_VALIDITY 
            );
        }

        $cnd = $srch->addCondition('rfq_status', 'IN', $statusArr);
        if (isset($postedData['requote']) && $postedData['requote'] == applicationConstants::YES) {
            $srch->addCondition('rfq_parent_id', '>', 0);
        } else {
            $srch->addCondition('rfq_parent_id', '=', applicationConstants::NO);
        }

        $srch->setPageNumber($page);
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);


        $this->set("arr_listing", $records);
        $this->set("type", $type);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));

        $this->set("siteLangId", $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function searchQuotes($post)
    {
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinWithProduct($this->siteLangId);
        $srch->joinWithOrder();
        $srch->addMultipleFields(array('rfq_id', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title', 'rfq_quote_validity', 'rfq_selprod_id', 'rfq_added_on', 'rfq_status', 'rfq_quantity', 'selprod_type', 'rfq_request_type', 'order_id', 'order_payment_status'));
        $srch->setPageSize($pagesize);

        $keyword = isset($post['keyword']) ? $post['keyword'] : '';
        if ('' != $keyword) {
            $cnd = $srch->addCondition('selprod_title', 'like', "%$keyword%");
            $keyword = str_replace("#", "", $keyword);
            $cnd->attachCondition('rfq_id', 'like', "%" . $keyword . "%", 'OR');
            $cnd->attachCondition('order_id', 'like', "%" . $keyword . "%", 'OR');
        }

        if (!empty($post['request_from_date'])) {
            $srch->addCondition('rfq_added_on', '>=', $post['request_from_date'] . ' 00:00:00');
        }

        if (!empty($post['request_to_date'])) {
            $srch->addCondition('rfq_added_on', '<=', $post['request_to_date'] . ' 23:59:59');
        }

        if (isset($post['rfq_status']) && $post['rfq_status'] != '') {
            $srch->addCondition('rfq_status', '=', intval($post['rfq_status']));
        }

        if (isset($post['prod_type']) && intval($post['prod_type']) != '') {
            $srch->addCondition('selprod_type', '=', intval($post['prod_type']));
        }

        $srch->addOrder('rfq_added_on', 'DESC');
        return $srch;
    }


    public function requestView(int $rfqId)
    {
        if (1 > $rfqId) {
            FatUtility::exitWithErrorCode(404);
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinWithOrder($this->siteLangId);
        $srch->joinWithInvoice();
        $srch->joinForShop($this->siteLangId);
        $srch->joinWithProduct($this->siteLangId);
        $srch->addMultipleFields(array('rfq.*', 'selprod_type', 'rfq_quote_validity', 'selprod_price', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title', 'product_updated_on', 'selprod_product_id', 'selprod_id', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'shop_id', 'sprodata_duration_type', 'sprodata_rental_price', 'IF(sprodata_rental_stock > 0, 1, 0) AS rent_in_stock', 'order_id', 'order_payment_status', 'invoice_status'));
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srch->addCondition('rfq_user_id', '=', UserAuthentication::getLoggedUserId());
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        if (empty($record)) {
            FatUtility::exitWithErrorCode(404);
        }
        $counterOffer = new CounterOffer(0, $rfqId);
        $quotedOfferDetail = $counterOffer->getDetailByStatus(RequestForQuote::REQUEST_QUOTED);

        $srch = new RequestForQuote($rfqId);
        $reQuoteOfferDetail = $srch->getRequestDetailWithParentId(UserAuthentication::getLoggedUserId(), $this->siteLangId);
		
		/* [ GET ATTACHED SERVICES WITH RENTAL PRODUCT */
		$servicesList = [];
        if ($record['selprod_type'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $rfqObj = new RequestForQuote($rfqId);
            $servicesList = $rfqObj->getAttachedServices($this->siteLangId);
			$this->set('servicesList', $servicesList);
        }
		
		/* ]*/
        $action = "quotedRequests";
        if(in_array($record['rfq_status'],array_keys(RequestForQuote::statusArray($this->siteLangId, RequestForQuote::APPROVED_LIST)) ) ){
            $action = "acceptedBuyerOffers";
        }elseif(in_array($record['rfq_status'], array_keys(RequestForQuote::statusArray($this->siteLangId, RequestForQuote::REJECTED_LIST)) )){
            $action = "rejectedBuyerOffers";
        }
        
        $shippingAddressDetail = [];
       
        if ($record['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP) {
            $obj = new Address($record['rfq_shipping_address_id']);
            $shippingAddressDetail = $obj->getData(Address::TYPE_USER, $record['rfq_user_id']);
        } else {
            $obj = new Address($record['rfq_pickup_address_id']);
            $shippingAddressDetail = $obj->getData(Address::TYPE_SHOP_PICKUP, $record['shop_id']);
        }
        
        $this->set('shippingAddressDetail', $shippingAddressDetail);
        $this->set('rfqData', $record);
        $this->set('reQuoteOfferDetail', $reQuoteOfferDetail);
        $this->set('quotedOfferDetail', $quotedOfferDetail);
        $this->set("attachments", AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER, $rfqId, 0, -1));
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));
        $this->set("siteLangId", $this->siteLangId);
        $this->set("action", $action);
        $this->_template->render();
    }


    public function setupOffer()
    {
        $fulfilmentType = FatApp::getPostedData('rfq_fulfilment_type');
        $reqType = FatApp::getPostedData('rfq_request_type');

        $frm = $this->getQuotedOfferForm($fulfilmentType, $reqType);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (!$this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (false === $this->validateRequest($post['counter_offer_rfq_id'])) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Requrest", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $defaultData = array(
            'counter_offer_by' => UserAuthentication::getLoggedUserId(),
            'counter_offer_added_on' => date('Y-m-d H:i:s'),
            'counter_offer_status' => RequestForQuote::REQUEST_QUOTED,
        );
        // echo "<pre>";
        // print_r($post);
        // echo "<pre>";
        // print_r($defaultData);
        // die();
        $dataToSave = array_merge($post, $defaultData);

        $record = new CounterOffer($post['counter_offer_id']);
        $record->assignValues($dataToSave);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $dataToUpdate = array(
            'rfq_status' => RequestForQuote::REQUEST_QUOTED,
            'rfq_quote_validity' => $post['quote_validity']
        );
        $record = new RequestForQuote($post['counter_offer_rfq_id']);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* [ OFFER SUBMISSION EMAIL NOTIFICATION */
        $emailHandler = new EmailHandler();
        if (!$emailHandler->newRfqOfferNotification($this->siteLangId, $post['counter_offer_rfq_id'])) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        Message::addMessage(Labels::getLabel('MSG_Offer_submitted_Successfully.', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    private function validateRequest(int $rfqId): bool
    {
        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        if (empty($record)) {
            return false;
        }
        return true;
    }


    public function acceptedOffers()
    {
        if (!$this->userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true) || (!User::canAccessSupplierDashboard() || !User::isSellerVerified($this->userParentId))) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('seller'));
        }

        $frmSearch = $this->searchProdQuotesForm(RequestForQuote::APPROVED_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::APPROVED_LIST ));
        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_RFQ_Listings_(_Accepted_Offers_)', $this->siteLangId));
        $this->_template->render(true, true, 'request-for-quotes/product-quotes.php');
    }

    public function rejectedOffers()
    {
        if (!$this->userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true) || (!User::canAccessSupplierDashboard() || !User::isSellerVerified($this->userParentId))) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('seller'));
        }

        $frmSearch = $this->searchProdQuotesForm(RequestForQuote::REJECTED_LIST);
        $frmSearch->fill(array('quote_type'=>RequestForQuote::REJECTED_LIST ));
        $this->set("frmSearch", $frmSearch);
        $this->set("pageTitle", Labels::getLabel('Lbl_RFQ_Listings_(_Rejected_Offers_)', $this->siteLangId));
        $this->_template->render(true, true, 'request-for-quotes/product-quotes.php');
    }


    private function searchProdQuotesForm(int $pageType = 0)
    {
        $frm = $this->searchQuoteRequests();

        $statusArr = RequestForQuote::statusArray($this->siteLangId, $pageType);
        $frm->addSelectBox('', 'rfq_status', $statusArr, '', array(), Labels::getLabel('LBL_Select_Status', $this->siteLangId));
        $frm->addHiddenField('', 'quote_type');
        return $frm;
    }


    public function searchProdQuotes()
    { 
        $frmSearch = $this->searchProdQuotesForm(FatApp::getPostedData('quote_type'));
        $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);

        $srch = $this->searchQuotes($post);
        /* $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'order_rfq_id = rfq_id', 'o'); */
        $srch->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'order_id = invoice.invoice_order_id', 'invoice');
        $srch->addFld(['invoice.*']);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $statusArr = RequestForQuote::statusArray($this->siteLangId, $post['quote_type']);
        $srch->addCondition('rfq_status', 'IN',  array_keys($statusArr));

        $srch->setPageNumber($page);
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);
        
        $this->set("arr_listing", $records);
        $this->set("type", FatUtility::int($post['quote_type']));
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));

        $this->set("siteLangId", $this->siteLangId);
        $this->set("canEdit", $this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }


    // public function reQuotedRequests()
    // {
    //     $frmSearch = $this->reQuotedReqSearchForm($this->siteLangId);
    //     $this->set("frmSearch", $frmSearch);
    //     $this->_template->render();
    // }

    // public function reQuotedOffers()
    // {
    //     $frmSearch = $this->reQuotedReqSearchForm($this->siteLangId);
    //     $this->set("frmSearch", $frmSearch);
    //     $this->_template->render();
    // }

    // public function searchReQuotedRequests()
    // {
    //     $frmSearch = $this->reQuotedReqSearchForm();
    //     $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());
    //     if (false === $post) {
    //         Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
    //         FatUtility::dieJsonError(Message::getHtml());
    //     }

    //     $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
    //     $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);

    //     $srch = new RequestForQuoteSearch();
    //     $srch->joinWithSellerProduct($this->siteLangId);
    //     $srch->joinUsers();
    //     $srch->addMultipleFields(array('rfq_id', 'selprod_title', 'rfq_selprod_id', 'user_name as buyer_name', 'rfq_added_on', 'rfq_status'));
    //     $srch->addCondition('selprod_user_id', '=', $this->userParentId);
    //     $srch->addCondition('rfq_parent_id', '>', 0);
    //     $srch->setPageSize($pagesize);
    //     $srch->setPageNumber($page);

    //     if ($keyword = FatApp::getPostedData('keyword')) {
    //         $cnd = $srch->addCondition('selprod_title', 'like', "%$keyword%");
    //         $cnd->attachCondition('user_name', 'like', "%" . $keyword . "%", 'OR');
    //         $keyword = str_replace("#", "", $keyword);
    //         $cnd->attachCondition('rfq_id', 'like', "%" . $keyword . "%", 'OR');
    //     }

    //     if (!empty($post['request_from_date'])) {
    //         $srch->addCondition('rfq_added_on', '>=', $post['request_from_date'] . ' 00:00:00');
    //     }

    //     if (!empty($post['request_to_date'])) {
    //         $srch->addCondition('rfq_added_on', '<=', $post['request_to_date'] . ' 23:59:59');
    //     }

    //     if (isset($post['rfq_status']) && $post['rfq_status'] != '') {
    //         $srch->addCondition('rfq_status', '=', intval($post['rfq_status']));
    //     }

    //     if (isset($post['prod_type']) && intval($post['prod_type']) != '') {
    //         $srch->addCondition('selprod_type', '=', intval($post['prod_type']));
    //     }

    //     $srch->addOrder('rfq_added_on', 'DESC');
    //     $srchRs = $srch->getResultSet();
    //     $records = FatApp::getDb()->fetchAll($srchRs);

    //     $this->set("arr_listing", $records);
    //     $this->set('pageCount', $srch->pages());
    //     $this->set('recordCount', $srch->recordCount());
    //     $this->set('page', $page);
    //     $this->set('pageSize', $pagesize);
    //     $this->set('postedData', $post);
    //     $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));
    //     $this->set("siteLangId", $this->siteLangId);
    //     $this->_template->render(false, false, '/request-for-quotes/search-prod-quotes.php');
    // }

	public function getServiceArrWithData(array $services): array
    {
        if (empty($services)) {
            return [];
        }
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->addCondition('selprod_id', 'in', $services);
        $srch->addCondition('selprod_active', '=', applicationConstants::YES);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        $srch->addMultipleFields(['selprod_id', 'selprod_title']);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'selprod_id');
    }
	
	private function updateSericesWithProduct(int $rfqId, array $servicesQuantities, array $servicesCapacities): bool
    {
        if (empty($servicesQuantities) || 1 > $rfqId) {
            return false;
        }
        $rfqObj = new RequestForQuote();
        foreach ($servicesQuantities as $serviceId => $quantity) {
            $dataToUpdate = [
                'rfqattser_rfq_id' => $rfqId,
                'rfqattser_selprod_id' => $serviceId,
                'rfqattser_quantity' => $quantity,
                'rfqattser_required_capacity' => (isset($servicesCapacities[$serviceId])) ? $servicesCapacities[$serviceId] : "",
            ];
            if (!$rfqObj->saveServiceWithRfq($dataToUpdate)) {
                $this->error = $rfqObj->getError();
                return false;
            }
        }

        return true;
    }

    public function orders()
    {
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        $data = array('order_type' => applicationConstants::PRODUCT_FOR_RENT);
        $frmOrderSrch->fill($data);

        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->set('orderType', applicationConstants::PRODUCT_FOR_RENT);
        $this->_template->render(true, true);
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId)));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addSelectBox('', 'rent_or_sale', applicationConstants::getOrderTypeArr($langId), '', array(), Labels::getLabel('LBL_Order_Type', $langId));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId), 'readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId) . ' [' . $currencySymbol . ']'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function orderSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $orderType = (isset($post['order_type'])) ? $post['order_type'] : applicationConstants::PRODUCT_FOR_RENT;
        $user_id = UserAuthentication::getLoggedUserId();

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addCountsOfOrderedProducts();
        $srch->joinShippingCharges();
        $srch->joinShopSpecifics();
        $srch->joinSellerProductSpecifics();
        $srch->joinOrderProductSpecifics();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(Invoice::DB_TBL, 'INNER JOIN', 'invoice.invoice_order_id = order_id', 'invoice');
        $srch->joinTable(RequestForQuote::DB_TBL, 'INNER JOIN', 'order_rfq_id = rfq.rfq_id', 'rfq');
        $srch->joinTable(
                OrderReturnRequest::DB_TBL, 'LEFT OUTER JOIN', 'orr.orrequest_op_id = op.op_id', 'orr'
        );
        $srch->joinTable(
                OrderCancelRequest::DB_TBL, 'LEFT OUTER JOIN', 'ocr.ocrequest_op_id = op.op_id', 'ocr'
        );

        if (true === MOBILE_APP_API_CALL) {
            $srch->joinSellerProducts();
            $srch->addfld('selprod_product_id');
        }

        $srch->addCondition('order_is_rfq', '!=', applicationConstants::NO);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->joinPaymentMethod();
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(
                array(
                    'order_id', 'order_user_id', 'order_date_added', 'order_net_amount', 'op_invoice_number',
                    'totCombinedOrders as totOrders', 'op_selprod_id', 'op_selprod_title', 'op_product_name', 'op_id', 'op_other_charges', 'op_unit_price',
                    'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_status_id', 'op_product_type',
                    'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'orderstatus_color_class',
                    'order_pmethod_id', 'order_status', 'plugin_name', 'IFNULL(orrequest_id, 0) as return_request',
                    'IFNULL(ocrequest_id, 0) as cancel_request', 'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age',
                    'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age', 'order_payment_status',
                    'order_deleted', 'plugin_code', 'opshipping_fulfillment_type', 'op_rounding_off', 'opd.*', 'op_delivery_time', 'invoice_status', 'rfq_status'
                )
        );

        if ($orderType == applicationConstants::ORDER_TYPE_RENT) {
            $statusCheckSrch = new SearchBase(Orders::DB_TBL_ORDER_STATUS_HISTORY, 'opstatus');
            $statusCheckSrch->addCondition('oshistory_orderstatus_id', '=', FatApp::getConfig('CONF_DEFAULT_DEIVERED_ORDER_STATUS'));
            $statusCheckSrch->addFld('oshistory_status_updated_by');
            $statusCheckSrch->addDirectCondition('opstatus.oshistory_status_updated_by = order_user_id');
            $statusCheckSrch->addDirectCondition('opstatus.oshistory_op_id = op_id');
            $statusCheckSrch->doNotCalculateRecords();
            $statusCheckSrch->doNotLimitRecords();
            $statusCheckQry = $statusCheckSrch->getQuery();
            $srch->addFld('IFNULL((' . $statusCheckQry . '), 0) as deliveredMarkedBy');
        }


        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');
        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")), ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        }

        $type = FatApp::getPostedData('rent_or_sale', FatUtility::VAR_INT, 0);
        if($type > 0){
            $srch->addCondition('opd.opd_sold_or_rented', '=', $type);
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


        $orderProductStatusArr = [];
        if (!empty($orders)) {
            $opIds = array_column($orders, 'op_id');
            $opStatusObj = new OrderProduct();
            $orderProductStatusArr = $opStatusObj->getStatusHistoryArr($opIds, true);
        }

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id'], MOBILE_APP_API_CALL);
            $order['charges'] = $charges;
            $order['status_history'] = (isset($orderProductStatusArr[$order['op_id']])) ? $orderProductStatusArr[$order['op_id']] : [];
        }
        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('statusForReadyToReturn', OrderStatus::getStatusForMarkOrderReadyForReturn());
        $this->set('orderTypeArr', applicationConstants::getOrderTypeArr($this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId, unserialize(FatApp::getConfig("CONF_BUYER_ORDER_STATUS")), 0, 0, false);
            $this->set('orderStatuses', $orderStatuses);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }


    public function rfqMessage(int $rfq_id)
    {
        $messageId = 0;
        $userId = UserAuthentication::getLoggedUserId();
        if (1 > $rfq_id || !$this->userPrivilege->canViewOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            $message = Labels::getLabel('MSG_INVALID_ACCESS', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            CommonHelper::redirectUserReferer();
        }

        $reqForQuote = new RequestForQuote($rfq_id);
        $rfqDetail = $reqForQuote->getRequestDetail(UserAuthentication::getLoggedUserId());
        if (empty($rfqDetail)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request_for_Re-Quote", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($userId == $rfqDetail['rfq_user_id']) {
            $message_to = $rfqDetail['selprod_user_id'];
        } else {
            $message_to = $rfqDetail['rfq_user_id'];
        }

        $threadDetails = Thread::getAttributesByRfqId($rfq_id);
        $threadId = 0;
        if (empty($threadDetails)) {

            $threadObj = new Thread();
            $threadDataToSave = array(
                'thread_subject' => Thread::RFQ_SUBJECT,
                'thread_started_by' => $userId,
                'thread_start_date' => date('Y-m-d H:i:s'),
                'thread_type' => Thread::THREAD_TYPE_RFQ,
                'thread_record_id' => $rfq_id
            );

            $threadObj->assignValues($threadDataToSave);

            if (!$threadObj->save()) {
                $message = Labels::getLabel($threadObj->getError(), $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($message);
                }
                Message::addErrorMessage($message);
                FatUtility::dieWithError(Message::getHtml());
            }
            $threadId = $threadObj->getMainTableRecordId();
        } else {
            $threadId = $threadDetails['thread_id'];
        }

        $redirectUrl = UrlHelper::generateUrl('account', 'viewMessages/'.$threadId);

        $this->set('redirectUrl', $redirectUrl);
        $this->_template->render(false, false, 'json-success.php');

    }

    public function rfqOrder()
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
    

    public function rfqOrderSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = $this->userParentId;

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'invoice.invoice_order_id = order_id', 'invoice');
        $srch->joinTable(RequestForQuote::DB_TBL, 'LEFT JOIN', 'order_rfq_id = rfq.rfq_id', 'rfq');
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::YES);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);

        $srch->addMultipleFields(
                array('order_id', 'order_status', 'order_payment_status', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id',
                    'order_date_added', 'order_net_amount', 'op_invoice_number', 'totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name',
                    'op_id', 'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller',
                    'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_id', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name',
                    'orderstatus_color_class', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*',
                    'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'opd.*', 'order_is_rfq', 'rfq_status', 'op_status_id', 'invoice_status', 'opshipping_type')
        );



        // $srch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")), ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        }

        $type = FatApp::getPostedData('rent_or_sale', FatUtility::VAR_INT, 0);
        if($type > 0){
            $srch->addCondition('opd.opd_sold_or_rented', '=', $type);
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
        $orders = FatApp::getDb()->fetchAll($rs);


        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (null !== $this->shippingService));
        /* ShipStation */

        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('orderTypeArr', applicationConstants::getOrderTypeArr($this->siteLangId));
        $this->set('canEditInvoice', $this->userPrivilege->canEditInvoices(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
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

}