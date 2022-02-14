<?php

class SellerRentalProductUnavailableDate extends MyAppModel
{

    public const DB_TBL = 'tbl_prod_unavailable_rental_durations';
    public const DB_TBL_PREFIX = 'pu_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function updateData($data, $return = false)
    {
        $db = FatApp::getDb();
        if (!$db->insertFromArray(static::DB_TBL, $data, false, array(), $data)) {
            return false;
        }
        if (true === $return) {
            if (!empty($data['pu_id'])) {
                return $data['pu_id'];
            }
            return $db->getInsertId();
        }
        return true;
    }
    
    public static function isValidDateRange($startDate, $endDate, int $selprodId, int $prodUnavailDateId = 0) : bool
    {
        $dateSrch = new SearchBase(SellerRentalProductUnavailableDate::DB_TBL, 'dd');
        if ($prodUnavailDateId > 0){
            $dateSrch->addCondition('pu_id', '!=', $prodUnavailDateId);
        }
        
        $dateSrch->addCondition('pu_selprod_id', '=', $selprodId);
        $dateSrch->addDirectCondition('(("' . $startDate . '" >= pu_start_date AND "' . $startDate . '" <= pu_end_date) OR ("' . $endDate . '" >= pu_start_date AND "' . $endDate . '" <= pu_end_date) OR ("' . $startDate . '" <= pu_start_date AND "' .  $endDate . '" >=  pu_end_date))');
        
        $dateSrch->addFld('pu_id');
        $rs = $dateSrch->getResultSet();
        $oldRecordCountRow = (array) FatApp::getDb()->fetch($rs);
        if (!empty($oldRecordCountRow)) {
            return false;
        }
            
        return true;
    }
    

}
