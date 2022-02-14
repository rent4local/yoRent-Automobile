<?php

class Thread extends MyAppModel
{
    public const DB_TBL = 'tbl_threads';
    public const DB_TBL_PREFIX = 'thread_';

    public const DB_TBL_THREAD_MESSAGES = 'tbl_thread_messages';
    public const DB_TBL_THREAD_MESSAGES_PREFIX = 'message_';

    public const THREAD_TYPE_PRODUCT = 1;
    public const THREAD_TYPE_SHOP = 2;
    public const THREAD_TYPE_ORDER_PRODUCT = 3;
    public const THREAD_TYPE_RFQ = 4;

    public const RFQ_SUBJECT = "RFQ";

    public const MESSAGE_IS_READ = 0;
    public const MESSAGE_IS_UNREAD = 1;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 't');
        return $srch;
    }

    public static function getThreadTypeArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
			static::THREAD_TYPE_PRODUCT => Labels::getLabel('LBL_Product_Message', $langId),
            static::THREAD_TYPE_SHOP => Labels::getLabel('LBL_Shop_Message', $langId),
            static::THREAD_TYPE_ORDER_PRODUCT => Labels::getLabel('LBL_Order_Message', $langId),
			static::THREAD_TYPE_RFQ => Labels::getLabel('LBL_Message_RFQ_Product', $langId),
        );
    }

    public static function getAttributesById($recordId, $attr = null)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        if (1 > $recordId) {
            return false;
        }
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL_THREAD_MESSAGES);
        $srch->addCondition('message_id', '=', $recordId);
        $srch->joinTable(static::DB_TBL, 'LEFT JOIN', 'message_thread_id = th.thread_id', 'th');
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public function addThreadMessages($data)
    {
        if (empty($data)) {
            return false;
        }
        if (!FatApp::getDb()->insertFromArray(Thread::DB_TBL_THREAD_MESSAGES, $data)) {
            $this->error = FatApp::getDb()->getError();
        }
        return FatApp::getDb()->getInsertId();
    }

    public function updateThreadMessages($data, $messageId)
    {
        if (empty($data)) {
            return false;
        }
        if (!FatApp::getDb()->updateFromArray(
            Thread::DB_TBL_THREAD_MESSAGES,
            $data,
            array(
                'smt' => '`message_id` = ?',
                'vals' => array(
                    $messageId
                )
            )
        )) {
            $this->error = FatApp::getDb()->getError();
        }
        return true;
    }

    public function getMessageCount($userId, $type = false, $startDate = false, $endDate = false)
    {
        $srch = new SearchBase(static::DB_TBL_THREAD_MESSAGES, 'ttm');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('message_deleted', '=', 0);
        if ($type) {
            $srch->addCondition('message_is_unread', '=', $type);
        }
        if ($startDate) {
            $startDate = FatDate::convertDatetimeToTimestamp($startDate);
            $startDate = date('Y-m-d', strtotime($startDate));
            $srch->addCondition('ttm.message_date', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $endDate = FatDate::convertDatetimeToTimestamp($endDate);
            $endDate = date('Y-m-d', strtotime($endDate));
            $srch->addCondition('ttm.message_date', '<=', $endDate . ' 23:59:59');
        }

        if ($this->mainTableRecordId > 0) {
            $srch->addCondition('message_thread_id', '=', $this->mainTableRecordId);
        }

        $parentAndThierChildIds = User::getParentAndTheirChildIds($userId);

        $cnd = $srch->addCondition('ttm.message_to', 'in', $parentAndThierChildIds);
        /* $cnd->attachCondition('ttm.message_to','=',$userId,'OR'); */

        $srch->addMultipleFields(array("count(ttm.message_id) as UnreadMessageCount"));
        $rs = $srch->getResultSet();
        if (!$rs) {
            return 0;
        }
        $res = FatApp::getDb()->fetch($rs);
        return $res['UnreadMessageCount'];
    }

    public function markUserMessageRead($threadId, $userId)
    {
        if (FatApp::getDb()->updateFromArray('tbl_thread_messages', array('message_is_unread' => self::MESSAGE_IS_READ), array('smt' => '`message_thread_id`=? AND `message_to`=? ', 'vals' => array($threadId, $userId)))) {
            return true;
        }

        $this->error = FatApp::getDb()->getError();
        return false;
    }

    public function markMessageReadFromUserArr($threadId, $userIds)
    {
        if (FatApp::getDb()->query('update tbl_thread_messages set message_is_unread = ' . self::MESSAGE_IS_READ . ' where message_thread_id = ' . $threadId . ' and  message_to in (' . implode(',', $userIds) . ')')) {
            return true;
        }
        $this->error = FatApp::getDb()->getError();
        return false;
    }

    public function deleteThreadMessage($message_id)
    {
        if (!$message_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL_THREAD_MESSAGES, array(static::DB_TBL_THREAD_MESSAGES_PREFIX . 'deleted' => 1), array('smt' => static::DB_TBL_THREAD_MESSAGES_PREFIX . 'id = ?', 'vals' => array($message_id)))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }


    public static function getMsgThreadByRecordId($recordId,$type)
    {
        if($recordId < 1 || $type < 1) {
            return [];
        }
        $thSrchObj = new SearchBase(Thread::DB_TBL);
        $thSrchObj->joinTable(Thread::DB_TBL_THREAD_MESSAGES, 'LEFT JOIN', 'thread_id = message_thread_id', 'msg');
        $thSrchObj->doNotCalculateRecords();
        $thSrchObj->doNotLimitRecords();
        $thSrchObj->addCondition('thread_record_id', '=', $recordId);
        $thSrchObj->addCondition('thread_type', '=', $type);
        $thSrchObj->addMultipleFields(array('thread_id', 'message_id'));
        $thSrchObj->addOrder("message_date", "DESC");
        $rs = $thSrchObj->getResultSet();
        return FatApp::getDb()->fetch($rs);  
	}
	
    public static function getAttributesByRfqId($rfq_id)
    {
        $rfq_id = FatUtility::convertToType($rfq_id, FatUtility::VAR_INT);
        if (1 > $rfq_id) {
            return false;
        }
        $db = FatApp::getDb();
        $srch = new SearchBase(static::DB_TBL);
        $srch->addCondition('thread_record_id', '=', $rfq_id);
        $srch->addCondition('thread_type', '=', SELF::THREAD_TYPE_RFQ);

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }

        return $row;
    }

    public static function getThreadDataById($recordId, $attr = null)
    {
        return parent::getAttributesById($recordId, $attr);
    }

    public static function getThreadByRecordId($recordId,$attr=null) {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_STRING);
        $db = FatApp::getDb();

        $srch = static::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition('thread_record_id', '=', $recordId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
        
    }

}
