<?php

class RequestForQuote extends MyAppModel
{
    public const DB_TBL = 'tbl_request_for_quotes';
    public const DB_TBL_PREFIX = 'rfq_';
	
	public const DB_TBL_RFQ_TO_SERVICES = 'tbl_rfq_attached_services';
    public const DB_TBL_RFQ_TO_SERVICES_PREFIX = 'rfqattser_';
    
    public const REQUEST_INPROGRESS = 0;
    public const REQUEST_QUOTED = 1;
    public const REQUEST_APPROVED = 2;
    public const REQUEST_COUNTER_BY_BUYER = 3;
    public const REQUEST_COUNTER_BY_SELLER = 4;
    public const REQUEST_CANCELLED_BY_BUYER = 5;
    public const REQUEST_DECLINED_BY_SELLER = 6;
    public const REQUEST_ACCEPTED_BY_BUYER = 7;
    // public const REQUEST_RE_QUOTED = 8;
    public const REQUEST_CLOSED_BY_ADMIN = 9;
    public const REQUEST_QUOTE_VALIDITY = 10;

    public const INPROGRESS_LIST = 1;
    public const APPROVED_LIST = 2;
    public const REJECTED_LIST = 3;
   
    public function __construct($rfqId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $rfqId);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'rfq');
        return $srch;
    }
    
    public static function statusArray($langId, int $pageType = 0): array
    {
        
        if($pageType == static::INPROGRESS_LIST) {
            return array(
                static::REQUEST_INPROGRESS => Labels::getLabel('LBL_In-Progress', $langId),
                static::REQUEST_QUOTED => Labels::getLabel('LBL_Quoted', $langId),
                static::REQUEST_COUNTER_BY_BUYER => Labels::getLabel('LBL_Buyer_counter_offer', $langId),
                static::REQUEST_COUNTER_BY_SELLER => Labels::getLabel('LBL_Seller_counter_offer', $langId),
    
            );
        }elseif ($pageType == static::APPROVED_LIST) {
            return array(
                static::REQUEST_APPROVED => Labels::getLabel('LBL_Accepted_&_Confirmed', $langId),
                static::REQUEST_ACCEPTED_BY_BUYER => Labels::getLabel('LBL_Accepted_by_buyer', $langId),
    
            );
        }elseif ($pageType == static::REJECTED_LIST) {
            return array(
                static::REQUEST_CANCELLED_BY_BUYER => Labels::getLabel('LBL_Closed_by_buyer', $langId),
                static::REQUEST_DECLINED_BY_SELLER => Labels::getLabel('LBL_Declined_by_seller', $langId),
                static::REQUEST_CLOSED_BY_ADMIN => Labels::getLabel('LBL_Closed_by_admin', $langId),
                static::REQUEST_QUOTE_VALIDITY => Labels::getLabel('LBL_Quote_Expired', $langId),
    
            );
        }else {
            return array(
                static::REQUEST_INPROGRESS => Labels::getLabel('LBL_In-Progress', $langId),
                static::REQUEST_APPROVED => Labels::getLabel('LBL_Accepted_&_Confirmed', $langId),
                static::REQUEST_QUOTED => Labels::getLabel('LBL_Quoted', $langId),
                static::REQUEST_COUNTER_BY_BUYER => Labels::getLabel('LBL_Buyer_counter_offer', $langId),
                static::REQUEST_COUNTER_BY_SELLER => Labels::getLabel('LBL_Seller_counter_offer', $langId),
                static::REQUEST_CANCELLED_BY_BUYER => Labels::getLabel('LBL_Closed_by_buyer', $langId),
                static::REQUEST_DECLINED_BY_SELLER => Labels::getLabel('LBL_Declined_by_seller', $langId),
                static::REQUEST_ACCEPTED_BY_BUYER => Labels::getLabel('LBL_Accepted_by_buyer', $langId),
                // static::REQUEST_RE_QUOTED => Labels::getLabel('LBL_Re-quoted', $langId),
                static::REQUEST_CLOSED_BY_ADMIN => Labels::getLabel('LBL_Closed_by_admin', $langId),
                static::REQUEST_QUOTE_VALIDITY => Labels::getLabel('LBL_Quote_Expired', $langId),
            );
        }
        
    }

    
    public static function canBuyerUpdateStatus(int $status): bool
    {
        $buyerStatusArr = array(
            static::REQUEST_COUNTER_BY_BUYER,
            static::REQUEST_CANCELLED_BY_BUYER,
            static::REQUEST_ACCEPTED_BY_BUYER,
        );
        
        if (in_array($status, $buyerStatusArr)) {
            return true;
        }
        return false;
    }
    
    public static function canSellerUpdateStatus(int $status): bool
    {
        $sellerStatusArr = array(
            static::REQUEST_APPROVED,
            static::REQUEST_COUNTER_BY_SELLER,
            static::REQUEST_DECLINED_BY_SELLER,
        );
        
        if (in_array($status, $sellerStatusArr)) {
            return true;
        }
        return false;
    }
    
    public static function canAdminUpdateStatus(int $status): bool
    {
        $sellerStatusArr = array(
            static::REQUEST_INPROGRESS,
            static::REQUEST_QUOTED,
            static::REQUEST_COUNTER_BY_BUYER,
            static::REQUEST_COUNTER_BY_SELLER,  
            static::REQUEST_ACCEPTED_BY_BUYER,  
            // static::REQUEST_RE_QUOTED,
        );
        
        if (in_array($status, $sellerStatusArr)) {
            return true;
        }
        return false;
    }
    
    public function getRequestDetail(int $userId = 0, int $langId = 0, int $sellerId= 0): array
    {
        $srch = $this->getRequestDetailObject($userId, $langId);
        $srch->addCondition('rfq_id', '=', $this->mainTableRecordId);
        if($sellerId){
            $srch->addCondition('selprod_user_id', '=', $sellerId);
        }
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        if (empty($record)) {
            return array();
        }
        return $record;
    }
    
    public function getRequestDetailWithParentId(int $userId = 0, int $langId = 0): array
    {
        $srch = $this->getRequestDetailObject($userId, $langId);
        $srch->addCondition('rfq_parent_id', '=', $this->mainTableRecordId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        if (empty($record)) {
            return array();
        }
        return $record;
    }
    
    private function getRequestDetailObject(int $userId = 0, int $langId = 0)
    {
        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($langId);
        $srch->joinWithProduct();
        $srch->joinUsers();
        if (0 < $userId) {
            $cond = $srch->addCondition('selprod_user_id', '=', $userId);
            $cond->attachCondition('rfq_user_id', '=', $userId, 'OR');
        }
        return $srch;
    }
    
    public function updateStatus(int $status)
    {
        $dataToUpdate = array(
            'rfq_status' => $status
        );
        $db = FatApp::getDb();
        
        $record = new RequestForQuote($this->mainTableRecordId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            $this->error = $record->getError();
            return false;
        }
        
        return true;
    }

    public static function getSearchObjForEmail($rfqId, $langId)
    {
        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($langId);
        $srch->joinWithProduct();
        $srch->joinForShop($langId);
        $srch->joinUsers(true);
        $srch->joinForSeller(true);
        $srch->addMultipleFields(
                array('rfq.*', 'selprod_title', 'user.user_name as sender_name',
                    'product_updated_on', 'IFNULL(selprod_title, product_identifier) as selprod_title',
                    'CONCAT(seller.user_name, " - ", IFNULL(shop_name, shop_identifier)) as receiver_name', 'seller_cred.credential_email as receiver_email',
                    'seller.user_id as receiver_id', 'user.user_id as sender_id',
                    'user_cred.credential_email as sender_email'
                )
        );
        $srch->addCondition('rfq_id', '=', $rfqId);
        return $srch;
    }

    public static function getRfqStatus($rfqId)
    {
        $srch = self::getSearchObject();
        $srch->addMultipleFields(array('rfq_status'));
        $srch->addCondition('rfq_id','=', $rfqId);
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);
        if (empty($record)) {
            return array();
        }
        return $record;
    } 
	
	public function saveServiceWithRfq(array $dataToSave): bool
    {
        if (empty($dataToSave)) {
            $this->error = Labels::getLabel("LBL_Request_submitted_successfully", CommonHelper::getLangId());
            return false;
        }
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_RFQ_TO_SERVICES, $dataToSave, false, array(), $dataToSave)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
	
	public function getAttachedServices(int $langId): array
    {
        $srch = new SearchBase(static::DB_TBL_RFQ_TO_SERVICES, 'rfqattser');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = '. static::DB_TBL_RFQ_TO_SERVICES_PREFIX .'selprod_id');
        $srch->joinTable(
                SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = rfqattser.' . static::DB_TBL_RFQ_TO_SERVICES_PREFIX. 'selprod_id and
			sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'sp_l'
        );
        $srch->addMultipleFields(['rfqattser.*', 'sp_l.selprod_title as selprod_title', 'selprod_price', 'selprod_id']);
        $srch->addCondition('rfqattser_rfq_id', '=', $this->mainTableRecordId);
        $rs = $srch->getResultset();
        return FatApp::getDb()->fetchAll($rs);
    }

}
