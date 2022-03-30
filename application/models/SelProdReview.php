<?php

class SelProdReview extends MyAppModel
{
    public const DB_TBL = 'tbl_seller_product_reviews';
    public const DB_TBL_PREFIX = 'spreview_';

    public const DB_TBL_ABUSE = 'tbl_seller_product_reviews_abuse';
    public const DB_TBL_ABUSE_PREFIX = 'spra_';

    public const STATUS_PENDING = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_CANCELLED = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public function addSelProdReviewAbuse($data = array(), $onDuplicateUpdateData = array())
    {
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_ABUSE, $data, false, array(), $onDuplicateUpdateData)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getReviewStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        $arr = array(
        static::STATUS_PENDING => Labels::getLabel('LBL_Pending', $langId),
        static::STATUS_APPROVED => Labels::getLabel('LBL_Approved', $langId),
        static::STATUS_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId),
        );
        return $arr;
    }

    public static function getBuyerAllowedOrderReviewStatuses()
    {
        $buyerAllowReviewStatuses = unserialize(FatApp::getConfig("CONF_REVIEW_READY_ORDER_STATUS"));
        return $buyerAllowReviewStatuses;
    }

    public static function getSellerTotalReviews($userId)
    {
        $userId = FatUtility::int($userId);

        $srch = new SelProdReviewSearch();
        $srch->joinUser();
        $srch->joinSeller();
        $srch->joinSellerProducts();
        $srch->joinProducts();
        $srch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $srch->addMultipleFields(array('count(*) as numOfReviews'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('spreview_seller_user_id', '=', $userId);
        $srch->addGroupby('spreview_seller_user_id');
        $srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);

        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if ($record == false) {
            return 0;
        }
        return $record['numOfReviews'];
    }

    public static function getProductOrderId($product_id, $loggedUserId, $sellerId = 0)
    {
        $selProdSrch = SellerProduct::getSearchObject(0);
        $selProdSrch->addCondition('selprod_product_id', '= ', $product_id);
        $cnd = $selProdSrch->addCondition('sprodata_rental_active', '=', applicationConstants::ACTIVE);
        $cnd->attachCondition('selprod_active', '=', applicationConstants::ACTIVE, 'OR');
        if ($sellerId > 0) {
            $selProdSrch->addCondition('selprod_user_id', '=', $sellerId);
        }
        $selProdSrch->addCondition('selprod_deleted', '= ', applicationConstants::NO);
        $selProdSrch->addMultipleFields(array('selprod_id'));
        $rs = $selProdSrch->getResultSet();
        $selprodListing = FatApp::getDb()->fetchAll($rs);
        $selProdList = array();
        foreach ($selprodListing as $key => $val) {
            $selProdList[$key] = $val['selprod_id'];
        }
        
        $srch = new OrderProductSearch(0, true);
        $srch->joinTable(self::DB_TBL, 'LEFT OUTER JOIN', 'spreview_order_id = op_order_id', 'spr');
        $srch->addMultipleFields(['op_order_id', 'op_id']);
        $srch->addCondition('spreview_id', 'IS', 'mysql_func_null', 'AND', true);
        $srch->addCondition('order_user_id', '=', $loggedUserId);
        $cnd = $srch->addCondition('op_selprod_id', 'IN', $selProdList);
        $cnd->attachCondition('op_is_batch', '=', 0, 'AND');
        $srch->addCondition('op_status_id', 'IN', SelProdReview::getBuyerAllowedOrderReviewStatuses());
        $orderProduct = FatApp::getDb()->fetch($srch->getResultSet());
        return $orderProduct;
    }

    public function reviews($prodIdArr, $langId, $sellerIds = [])	
    {	
        if (!is_array($prodIdArr)) {	
            return false;	
        }	
        	
        $srch = new SelProdReviewSearch();	
		$srch->joinProducts($langId);	
		$srch->joinSellerProducts($langId);	
		$srch->joinSelProdRating();	
		$srch->joinUser();	
		$srch->joinSelProdReviewHelpful();	
		$srch->addCondition('sprating_rating_type','=',SelProdRating::TYPE_PRODUCT);	
		$srch->addCondition('spr.spreview_product_id', 'IN', $prodIdArr);
        if (!empty($sellerIds)) {
            $srch->addCondition('spr.spreview_seller_user_id', 'IN', $sellerIds);
        }
        
		//$srch->addCondition('spr.spreview_selprod_id', 'IN', $selprodIds);	
		$srch->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);	
		$srch->addMultipleFields(array('spreview_id','spreview_selprod_id', 'spreview_product_id',"ROUND(AVG(sprating_rating),2) as prod_rating" ,'spreview_title','spreview_description','spreview_posted_on','spreview_postedby_user_id','user_name','group_concat(case when sprh_helpful = 1 then concat(sprh_user_id,"~",1) else concat(sprh_user_id,"~",0) end ) usersMarked' ,'sum(if(sprh_helpful = 1 , 1 ,0)) as helpful' ,'sum(if(sprh_helpful = 0 , 1 ,0)) as notHelpful','count(sprh_spreview_id) as countUsersMarked', 'spreview_seller_user_id' ));	
		$srch->addGroupBy('spr.spreview_id');	
		$srch->addOrder('spr.spreview_posted_on', 'desc');	
        	
		$records = FatApp::getDb()->fetchAll($srch->getResultSet());	
        return $records;	
    }	
}
