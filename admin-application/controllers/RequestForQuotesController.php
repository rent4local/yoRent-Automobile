<?php

class RequestForQuotesController extends AdminBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewRfqManagement();
    }

    public function index()
    {
        $frmSearch = $this->getSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearchQuotesRequests');
        $frm->addTextBox('', 'keyword');
        $frm->addDateField('', 'request_from_date', '', array('readonly' => 'readonly'));
        $frm->addDateField('', 'request_to_date', '', array('readonly' => 'readonly'));
        $frm->addSelectBox('', 'rfq_status', RequestForQuote::statusArray($this->adminLangId), '', array(), Labels::getLabel('LBL_Select_Status', $this->adminLangId));
        $frm->addSelectBox('', 'prod_type', SellerProduct::selProdType($this->adminLangId), '', array(), Labels::getLabel('LBL_Type', $this->adminLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $frm->addHiddenField('', 'page');
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->adminLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function search()
    {
        $frmSearch = $this->getSearchForm();
        $post = $frmSearch->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);

        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new RequestForQuoteSearch();
        $srch->joinUsers();
        $srch->joinWithSellerProduct($this->adminLangId);
        $srch->joinForSeller();
        $srch->joinForShop($this->adminLangId);
        $srch->addMultipleFields(array('rfq_id', 'shop_name', 'seller.user_name as seller_name', 'user.user_name as buyer_name', 'selprod_title', 'rfq_selprod_id', 'rfq_added_on', 'rfq_status', 'rfq_quantity'));
        $srch->setPageSize($pagesize);

        $keyword = isset($post['keyword']) ? trim($post['keyword']) : '';
        if ('' != $keyword) {
            $cnd = $srch->addCondition('selprod_title', 'like', "%$keyword%");
            $keyword = str_replace("#", "", $keyword);
            $cnd->attachCondition('rfq_id', 'like', "%" . $keyword . "%", 'OR');
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

        $srch->setPageNumber($page);

        //echo $srch->getQuery(); exit;
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('statusArr', RequestForQuote::statusArray($this->adminLangId));

        $this->set("adminLangId", $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function view(int $rfqId)
    {
        if (1 > $rfqId) {
            FatUtility::exitWithErrorCode(404);
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->adminLangId);
        $srch->joinWithProduct();
        $srch->joinUsers();
        $srch->addMultipleFields(array('rfq.*', 'selprod_title', 'selprod_price', 'user_name as buyer_name', 'product_updated_on', 'selprod_product_id', 'selprod_id', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'sprodata_duration_type', 'sprodata_rental_price', 'IF(sprodata_rental_stock > 0, 1, 0) AS rent_in_stock'));
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            FatUtility::exitWithErrorCode(404);
        }
        $counterOffer = new CounterOffer(0, $rfqId);
        $quotedOfferDetail = $counterOffer->getDetailByStatus(RequestForQuote::REQUEST_QUOTED);

        $obj = new Address($record['rfq_billing_address_id']);
        $record['billingAddress'] = $obj->getData(Address::TYPE_USER, $record['rfq_user_id'], $this->adminLangId);

        $record['pickupAddress'] = [];
        $record['shippingAddress'] = [];
        if($record['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP){
            $prodSrch = new ProductSearch($this->adminLangId);
            $prodSrch->setDefinedCriteria(0, 0, array(), false);
            $prodSrch->joinShopSpecifics();
            $prodSrch->addCondition('selprod_id', '=', $record['rfq_selprod_id']);
            $prodSrch->addMultipleFields(
                array(
                    'shop_id', 'selprod_type','selprod_id')
                );
                $productRs = $prodSrch->getResultSet();
            $product = FatApp::getDb()->fetch($productRs);
        
            $obj = new Address($record['rfq_pickup_address_id']);

            $record['pickupAddress'] = $obj->getData(Address::TYPE_SHOP_PICKUP, $product['shop_id'], $this->adminLangId);
            
        }else{
            $obj = new Address($record['rfq_shipping_address_id']);
            $record['shippingAddress'] = $obj->getData(Address::TYPE_USER, $record['rfq_user_id'], $this->adminLangId);
        }


        $this->set('rfqData', $record);
        $this->set('quotedOfferDetail', $quotedOfferDetail);
        $this->set("attachments", AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER, $rfqId, 0, -1));
        $this->set("quotedAttachments", AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_QUOTED_DOCUMENT, $rfqId, 0, -1));
        $this->set('statusArr', RequestForQuote::statusArray($this->adminLangId));
        $this->set("adminLangId", $this->adminLangId);
        $this->_template->render();
    }

    public function offersListing()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);
        if (1 > $rfqId) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->adminLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = CounterOffer::getSearchObject($rfqId);
        $srch->addOrder('counter_offer_added_on', 'DESC');
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);

        $this->set("rfqData", $record);
        $this->set("arr_listing", $records);
        $this->set("adminLangId", $this->adminLangId);
        $this->set('statusArr', RequestForQuote::statusArray($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function changeStatus()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);
        if (1 > $rfqId) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srchRs = $srch->getResultSet();
        $rfqData = FatApp::getDb()->fetch($srchRs);
        
        if (empty($rfqData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $dataToUpdate = array(
            'rfq_status' => RequestForQuote::REQUEST_CLOSED_BY_ADMIN
        );
        $record = new RequestForQuote($rfqId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $rfqData['rfq_user_id'],
            'unotification_body' => Labels::getLabel('APP_OFFER_STATUS_CLOSED_BY_ADMIN', $this->adminLangId),
            'unotification_type' => Notifications::RFQ_CLOSED_BY_ADMIN,
            'unotification_data' => json_encode(['rfq_id' => $rfqId, 'action' => 'requestView', 'new_status_id' => RequestForQuote::REQUEST_CLOSED_BY_ADMIN])
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            Message::addErrorMessage($notificationObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($notificationObj);
        $notificationObj = new Notifications();
        $notificationDataArr = array(
            'unotification_user_id' => $rfqData['selprod_user_id'],
            'unotification_body' => Labels::getLabel('APP_OFFER_STATUS_CLOSED_BY_ADMIN', $this->adminLangId),
            'unotification_type' => Notifications::RFQ_CLOSED_BY_ADMIN,
            'unotification_data' => json_encode(['rfq_id' => $rfqId, 'action' => 'view' ,'new_status_id' => RequestForQuote::REQUEST_CLOSED_BY_ADMIN])
        );
        if (!$notificationObj->addNotification($notificationDataArr)) {
            Message::addErrorMessage($notificationObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        Message::addMessage(Labels::getLabel('MSG_Status_updated_Successfully', $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function downloadDigitalFile(int $rfqId, int $aFileId, int $fileType, bool $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $rfqId) {
            FatUtility::exitWithErrorCode(404);
        }

        $reqForQuote = new RequestForQuote($rfqId);
        $rfqDetail = $reqForQuote->getRequestDetail();
        if (empty($rfqDetail)) {
            FatUtility::exitWithErrorCode(404);
        }

        $attachFileRow = AttachedFile::getAttributesById($aFileId);

        /* files path[ */
        $folderName = '';
        switch ($fileType) {
            case AttachedFile::FILETYPE_QUOTED_DOCUMENT:
                $folderName = AttachedFile::FILETYPE_RFQ_DOCUMENT_PATH;
                break;
            case AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER:
                $folderName = AttachedFile::FILETYPE_SERVICE_DOCUMENT_PATH;
                break;
        }
        /* ] */

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('RequestForQuotes', 'view', array($rfqId)));
        }
        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }
    
}