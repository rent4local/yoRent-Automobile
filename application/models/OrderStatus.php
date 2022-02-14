<?php

class OrderStatus extends MyAppModel
{
    public const DB_TBL = 'tbl_orders_status';
    public const DB_TBL_PREFIX = 'orderstatus_';

    public const DB_TBL_LANG = 'tbl_orders_status_lang';
    public const DB_TBL_LANG_PREFIX = 'orderstatuslang_';

    public const ORDER_PAYMENT_PENDING = 1;
    public const ORDER_CASH_ON_DELIVERY = 2;
    public const ORDER_PAY_AT_STORE = 3;
    public const ORDER_PAYMENT_CONFIRM = 4;
    public const ORDER_IN_PROCESS = 5;
    public const ORDER_SHIPPED = 6;
    public const ORDER_DELIVERED = 7;
	public const ORDER_DELIVERED_MARKED_BY_BUYER = 8;
    public const ORDER_RETURN_REQUESTED = 9;
    public const ORDER_RENTAL_RETURNED = 10;
    public const ORDER_COMPLETED = 11;
    public const ORDER_CANCELLED = 12;
    public const ORDER_REFUNDED = 13;
    public const ORDER_SUBSCRIPTION_IN_ACTIVE = 14;
    public const ORDER_SUBSCRIPTION_ACTIVE = 15;
    public const ORDER_SUBSCRIPTION_CANCEL = 16;
	
	public const ORDER_READY_FOR_RENTAL_RETURN = 17;
	public const ORDER_RENTAL_EXTENDED = 18;
	 
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'ostatus');

        if ($isActive == true) {
            $srch->addCondition('ostatus.' . static::DB_TBL_PREFIX . 'is_active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ostatus_l.' . static::DB_TBL_LANG_PREFIX . 'orderstatus_id = ostatus.' . static::tblFld('id') . ' and
			ostatus_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'ostatus_l'
            );
        }

        return $srch;
    }

    public static function nonCancellableStatuses()
    {
        return array(
            static::ORDER_SHIPPED,
            static::ORDER_DELIVERED,
            static::ORDER_RETURN_REQUESTED,
            static::ORDER_READY_FOR_RENTAL_RETURN,
            static::ORDER_RENTAL_EXTENDED,
            static::ORDER_COMPLETED,
            static::ORDER_CANCELLED,
            static::ORDER_REFUNDED
        );
    }

    public static function getOrderStatusTypeArr($langId)
    {
        return array(
            Orders::ORDER_PRODUCT => Labels::getLabel('LBL_Product', $langId),
            Orders::ORDER_SUBSCRIPTION => Labels::getLabel('LBL_Subscriptions', $langId),
        );
    }

    public function updateOrder($order)
    {
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }

                FatApp::getDb()->updateFromArray(
                    static::DB_TBL,
                    array(
                    static::DB_TBL_PREFIX . 'priority' => $i
                    ),
                    array(
                    'smt' => static::DB_TBL_PREFIX . 'id = ?',
                    'vals' => array($id)
                    )
                );
            }
            return true;
        }
        return false;
    }
    public static function getOrderAllStatus() {
        return array(
            static::ORDER_PAYMENT_PENDING,
            static::ORDER_CASH_ON_DELIVERY,
            static::ORDER_PAY_AT_STORE,
            static::ORDER_PAYMENT_CONFIRM,
            static::ORDER_IN_PROCESS,
            static::ORDER_SHIPPED,
            static::ORDER_DELIVERED,
            static::ORDER_RENTAL_EXTENDED,
            /*static::ORDER_DELIVERED_MARKED_BY_BUYER,*/
            static::ORDER_RETURN_REQUESTED,
            static::ORDER_RENTAL_RETURNED,
            static::ORDER_COMPLETED,
            static::ORDER_CANCELLED,
            static::ORDER_REFUNDED
        );
    }
    
	public static function getStatusForMarkOrderReadyForReturn() // from buyer end
	{
		return [static::ORDER_DELIVERED, /*static::ORDER_DELIVERED_MARKED_BY_BUYER*/];
	}
	
	public static function getRentalReturnAvailStatus()
	{
		return [static::ORDER_RENTAL_RETURNED, static::ORDER_READY_FOR_RENTAL_RETURN, static::ORDER_DELIVERED];
	}
    
    public static function buyerUpdateAllowedStatus() : array
    {
        $inProcessStatusArr = unserialize(FatApp::getConfig('CONF_PROCESSING_ORDER_STATUS', FatUtility::VAR_STRING, ''));
        $allowedUpdateStatus = [static::ORDER_READY_FOR_RENTAL_RETURN, static::ORDER_DELIVERED];
        return array_merge($inProcessStatusArr, $allowedUpdateStatus);
    }
    
    public static function statusArrForRentalExpireNote()
    {
        return [
            static::ORDER_DELIVERED, static::ORDER_READY_FOR_RENTAL_RETURN, static::ORDER_RENTAL_EXTENDED
        ];
        
    }
    
    public static function statusArrForLateChargeNote()
    {
        return [
            static::ORDER_DELIVERED, static::ORDER_READY_FOR_RENTAL_RETURN
        ];
    }
    
    public static function orderStatusFlow(int $langId, int $paymentStatus = 0, array $orderStatusHistory = [], bool $isRent = false, int $fullfillmentType = Shipping::FULFILMENT_SHIP) : array
    {
        $rentalStatusIds = [
            static::ORDER_PAYMENT_CONFIRM, 
            static::ORDER_IN_PROCESS, 
            static::ORDER_SHIPPED, 
            static::ORDER_DELIVERED, 
            static::ORDER_COMPLETED
        ];
        
        if ($isRent) {
            $rentalStatusIds[] = static::ORDER_READY_FOR_RENTAL_RETURN;
            $rentalStatusIds[] = static::ORDER_RENTAL_RETURNED;
        }
        
        if ($paymentStatus == 0) {
            $rentalStatusIds[] = static::ORDER_PAYMENT_PENDING;
        }
    
        if (in_array(static::ORDER_CANCELLED, $orderStatusHistory)) {
            $rentalStatusIds = [
                static::ORDER_PAYMENT_CONFIRM, 
                static::ORDER_CANCELLED 
            ];
        }
        if (in_array(static::ORDER_REFUNDED, $orderStatusHistory) || in_array(static::ORDER_RETURN_REQUESTED, $orderStatusHistory)) {
            $rentalStatusIds[] = static::ORDER_REFUNDED; 
            $rentalStatusIds[] = static::ORDER_RETURN_REQUESTED; 
            $rentalStatusIds = array_diff($rentalStatusIds, [static::ORDER_READY_FOR_RENTAL_RETURN, static::ORDER_RENTAL_RETURNED, static::ORDER_COMPLETED]);
        }
        if (in_array(static::ORDER_RENTAL_EXTENDED, $orderStatusHistory)) {
            $rentalStatusIds[] = static::ORDER_RENTAL_EXTENDED; 
        }
        if ($fullfillmentType == Shipping::FULFILMENT_PICKUP) {
            $rentalStatusIds = array_diff($rentalStatusIds, [static::ORDER_SHIPPED]);
        }
        
    
        $srch = new SearchBase(Orders::DB_TBL_ORDERS_STATUS, 'ostatus');
        $srch->joinTable(Orders::DB_TBL_ORDERS_STATUS_LANG, 'LEFT OUTER JOIN', 'ostatus_l.orderstatuslang_orderstatus_id = ostatus.orderstatus_id AND ostatus_l.orderstatuslang_lang_id = ' . $langId, 'ostatus_l');
        $srch->addCondition('orderstatus_is_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('orderstatus_id', 'IN', $rentalStatusIds);
        $srch->addOrder('orderstatus_priority', 'ASC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orderstatus_id', 'IFNULL(orderstatus_name,orderstatus_identifier) as orderstatus_name', 'orderstatus_priority as priority'));
        $rs = $srch->getResultSet();
        return (array) FatApp::getDb()->fetchAll($rs, 'orderstatus_id');
    }
    
    public static function orderStatusClassess()
    {
        return [
            static::ORDER_PAYMENT_PENDING => "in-process", 
            static::ORDER_PAYMENT_CONFIRM => "in-process", 
            static::ORDER_IN_PROCESS => "in-process", 
            static::ORDER_SHIPPED => "shipped", 
            static::ORDER_DELIVERED => "delivered", 
            static::ORDER_RENTAL_EXTENDED => "delivered", 
            static::ORDER_READY_FOR_RENTAL_RETURN => "delivered", 
            static::ORDER_RETURN_REQUESTED => "in-process", 
            static::ORDER_RENTAL_RETURNED => "in-process", 
            static::ORDER_COMPLETED => "in-process", 
            static::ORDER_CANCELLED => "in-process",
            static::ORDER_REFUNDED => "in-process"
        ];
    }
    
}
