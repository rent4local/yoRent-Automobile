<?php

class BuyerLateChargesHistory extends MyAppModel {

    public const DB_TBL = 'tbl_buyer_late_charges_history';
    public const DB_TBL_PREFIX = 'charge_';
    public const STATUS_PENDING = 0;
    public const STATUS_UNPAID = 1;
    public const STATUS_PAID = 2;
    public const STATUS_EXCLUDE = 3;
    

    public function __construct() 
    {
        
    }

    public static function getSearchObject() 
    {
        $srch = new SearchBase(static::DB_TBL, 'charges');
        return $srch;
    }

    public static function getDetailsById(int $opId, $isUnpaid = false) 
    {
        $srch = static::getSearchObject();
        $srch->addCondition('charge_op_id', '=', $opId);
        if ($isUnpaid) {
            $srch->addCondition('charge_status', '=', static::STATUS_UNPAID);
        }
        $rs = $srch->getResultSet();
        return (array) FatApp::getDb()->fetch($rs);
    }

    public static function getUserPendingChargesTotalDetails(int $userId, $retunOnlyTotal = false) 
    {
        $srch = static::getSearchObject();
        $srch->joinTable(OrderProduct::DB_TBL, 'INNER JOIN', 'op_id = charge_op_id AND op_status_id = '. OrderStatus::ORDER_COMPLETED, 'op');
        $srch->addCondition('charge_user_id', '=', $userId);
        $srch->addCondition('charge_status', '=', static::STATUS_UNPAID);
        $srch->addFld(['sum(charge_total_amount-charge_paid) as pendingCharges', 'GROUP_CONCAT(charge_op_id) as op_ids']);
        $rs = $srch->getResultSet();
        $chargesRow = FatApp::getDb()->fetch($rs);
        if (empty($chargesRow)) {
            return ($retunOnlyTotal) ? 0 : [];
        }
        return ($retunOnlyTotal) ? $chargesRow['pendingCharges'] : $chargesRow;
    }
    
    public static function chargesStatusArr($langId)
    {
        return [
            static::STATUS_UNPAID => Labels::getLabel('LBL_Unpaid', $langId),
            static::STATUS_PAID => Labels::getLabel('LBL_Paid', $langId),
            static::STATUS_EXCLUDE => Labels::getLabel('LBL_Exclude', $langId),
            static::STATUS_PENDING => Labels::getLabel('LBL_Confirmation_Pending', $langId),
        ];
    }
    

}
