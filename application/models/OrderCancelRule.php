<?php

class OrderCancelRule extends MyAppModel
{

    public const DB_TBL = 'tbl_order_cancel_rules';
    public const DB_TBL_PREFIX = 'ocrule_';

    public const MIN_VALUE = 1;
    public const MAX_VALUE = 2;
    
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ocrule');
        return $srch;
    }

    public static function getOrderCancelRules(int $userId,$isActive = false)
    {
        $srch = static::getSearchObject();
        $cnd = $srch->addCondition('ocrule_user_id', '=', $userId);
        $cnd->attachCondition('ocrule_user_id', '=', 0);

        if($isActive) {
            $srch->addCondition('ocrule_active','=', applicationConstants::YES);
        }

        $srch->addOrder('ocrule_user_id', 'DESC');
        $srch->addOrder('ocrule_duration_min', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        if (empty($records)) {
            return [];
        }
        $userIds = array_column($records, 'ocrule_user_id');
        if (!in_array($userId, $userIds)) {
            return $records;
        }
        $rules = [];
        foreach($records as $record) {
            if($record['ocrule_user_id'] == $userId) {
                $rules[] = $record;
            }
        }
        return $rules;
        
    }
    
    public static function getCancelRefundAmountByDuration(int $duration, int $userId, $returnRow = false)
    {
        $duration = ($duration < 0) ? 0 : $duration;
    
        $srch = static::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->addCondition('ocrule_active', '=', applicationConstants::YES);
        $srch->addCondition('ocrule_duration_min', '<=', $duration);
        $cnd = $srch->addCondition('ocrule_duration_max', '>', $duration);
        $cnd->attachCondition('ocrule_duration_max', '=', '-1');
        $cnd = $srch->addCondition('ocrule_user_id', '=', $userId);
        $cnd->attachCondition('ocrule_user_id', '=', 0);
        if (!$returnRow) {
            $srch->addFld('ocrule_refund_amount');
        }
        $srch->addOrder('ocrule_user_id', 'DESC');
        /* $srch->addOrder('ocrule_duration', 'ASC'); */
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs, []);
        if ($returnRow) {
            return $record;
        }
        
        return (empty($record)) ? 100 : $record['ocrule_refund_amount'];
    }

    public static function getSellerDefaultCancleRules(int $userId, $isActive = false) : array
    {
        $srch = static::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->addCondition('ocrule_user_id', '=', $userId);
        $srch->addCondition('ocrule_is_default', '!=', applicationConstants::NO);
        if ($isActive) {
            $srch->addCondition('ocrule_active', '=', applicationConstants::ACTIVE);
        }
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }
    
    public static function checkDurationRangeIsValid(array $data) : bool 
    {
        if (empty($data)) {
            return false;
        }
        /* [ @TO DO */
        /* $srch = static::getSearchObject();
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', $data['user_id']);
        $srch->addCondition('ocrule_id', '!=', $data['rule_id']);
        $srch->addCondition('ocrule_active', '=', applicationConstants::ACTIVE);
        $srch->addDirectCondition('((ocrule_duration_min BETWEEN '. $data['min_duration'] .' AND '. ($data['max_duration'] - 1) . ') OR (ocrule_duration_max BETWEEN '. ($data['min_duration'] + 1)  .' AND '. ($data['max_duration'] - 1) .') OR ('. $data['min_duration'] .' BETWEEN ocrule_duration_min AND ocrule_duration_max) OR ('. $data['max_duration'] .' BETWEEN ocrule_duration_min AND ocrule_duration_max))'); 
        $srch->addFld('count(ocrule_id) as duplicateRuleCount');
        $rs = $srch->getResultSet();
        $data = FatApp::getdb()->fetch($rs); 
        return ($data['duplicateRuleCount'] > 0) ? false : true; */
        /* ] */
        
        $srch = static::getSearchObject();
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', $data['user_id']);
        $srch->addCondition('ocrule_id', '!=', $data['rule_id']);
        $srch->addCondition('ocrule_active', '=', applicationConstants::ACTIVE);
        $srch->addOrder('ocrule_duration_min', 'ASC');

        $rs = $srch->getResultSet();
        $oldRecords = FatApp::getdb()->fetchAll($rs); 
        
        if (empty($oldRecords)) {
            return true;
        }
        

        $flag = false;
        if ($data['rule_id'] > 0 && ($data['is_default'] == OrderCancelRule::MIN_VALUE || $data['is_default'] == OrderCancelRule::MAX_VALUE)) {
            if ($data['is_default'] == OrderCancelRule::MIN_VALUE && $oldRecords[0]['ocrule_duration_min'] >= $data['max_duration']) {
                $flag = true;
            }elseif($data['is_default'] == OrderCancelRule::MAX_VALUE && $oldRecords[count($oldRecords) - 1]['ocrule_duration_max'] <= $data['min_duration']) {
                $flag = true;
            }
        } else {
            if ($oldRecords[0]['ocrule_duration_max'] > $data['min_duration']){
                $flag = false;
            } elseif($oldRecords[count($oldRecords) - 1]['ocrule_duration_min'] < $data['max_duration']) {
                $flag = false;
            } else {
                for ($i = 1; $i < count($oldRecords); $i++) { 
                    if ($oldRecords[$i]['ocrule_duration_min'] >= $data['max_duration'] && $oldRecords[$i-1]['ocrule_duration_max'] <= $data['min_duration']){
                        $flag = true;
                        break;
                    }
                }
            }
        }
        return $flag;
    }
    
}
