<?php

class Notifications extends MyAppModel
{
    public const DB_TBL = 'tbl_user_notifications';
    public const DB_TBL_PREFIX = 'unotification_';

    public const NEW_RFQ_SUBMISSION = 'NEW_RFQ_SUBMISSION';
    public const NEW_RFQ_OFFER_SUBMISSION_BY_SELLER = 'NEW_RFQ_OFFER_SUBMISSION_BY_SELLER';
    public const NEW_RFQ_OFFER_SUBMISSION_BY_BUYER = 'NEW_RFQ_OFFER_SUBMISSION_BY_BUYER';
    public const NEW_RFQ_OFFER_UPDATE_BY_SELLER = 'NEW_RFQ_OFFER_UPDATE_BY_SELLER';
    public const NEW_RFQ_OFFER_UPDATE_BY_BUYER = 'NEW_RFQ_OFFER_UPDATE_BY_BUYER';
    public const INVOICE_SHARED_BY_SELLER = 'INVOICE_SHARED_BY_SELLER';
    public const INVOICE_REGENERATE_REQUEST_BY_BUYER = 'INVOICE_REGENERATE_REQUEST_BY_BUYER';
    public const RFQ_CLOSED_BY_ADMIN = 'RFQ_CLOSED_BY_ADMIN';


    public function __construct($unotificationId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $unotificationId);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'unt');
        return $srch;
    }

    public function addNotification($data, $pushNotification = true)
    {
        $userId = FatUtility::int($data['unotification_user_id']);
        if ($userId < 1) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId), E_USER_ERROR);
            return false;
        }
        $data['unotification_date'] = date('Y-m-d H:i:s');
        $this->assignValues($data);
        if (!$this->save()) {
            return false;
        }


        if (true === $pushNotification) {
            $google_push_notification_api_key = FatApp::getConfig("CONF_GOOGLE_PUSH_NOTIFICATION_API_KEY", FatUtility::VAR_STRING, '');
            if (trim($google_push_notification_api_key) == '') {
                return $this->getMainTableRecordId();
            }

            $uObj = new User($userId);
            $fcmDeviceIds = $uObj->getPushNotificationTokens();
            if (empty($fcmDeviceIds)) {
                return $this->getMainTableRecordId();
            }

            /* require_once(CONF_INSTALLATION_PATH . 'library/APIs/notifications/pusher.php');
            $pusher = new Pusher($google_push_notification_api_key); */
            foreach ($fcmDeviceIds as $pushNotificationApiToken) {
                $siteName = FatApp::getConfig('CONF_WEBSITE_NAME_' . $this->commonLangId, FatUtility::VAR_STRING, 'Yo!Rent');
                $message = array('title' => empty($siteName) ? $_SERVER['SERVER_NAME'] : $siteName, 'text' => $data['unotification_body'], 'type' => $data['unotification_type']);
                self::sendPushNotification($google_push_notification_api_key, $pushNotificationApiToken['uauth_fcm_id'], $message);
                /* $pusher->notify($pushNotificationApiToken['uauth_fcm_id'], array('text'=>$data['unotification_body'],'type'=>$data['unotification_type'])); */
            }
        }

        return $this->getMainTableRecordId();
    }

    public static function sendPushNotification($serverKey, $deviceToken, $data = array())
    {
        if (!array_key_exists('body', $data)) {
            $data['body'] = '';
        }

        if (array_key_exists('text', $data)) {
            $data['body'] = $data['text'];
        }

        $fields = [
            'registration_ids' => [$deviceToken],
            'notification' => $data, /* Required For IOS */
            'data' => $data, /* Required For ANDROID */
            'priority' => 'high'
        ];
        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ];
        $url = "https://fcm.googleapis.com/fcm/send";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);

        $data = array();
        if ($response === false) {
            $data['status'] = false;
            $data['msg'] = curl_error($ch);
        } else {
            $data['status'] = true;
            $data['msg'] = $response;
        }
        curl_close($ch);
        return $data;
    }


    public function readUserNotification($notificationId, $userId)
    {
        $smt = array(
            'smt' => static::DB_TBL_PREFIX . 'id = ? AND ' . static::DB_TBL_PREFIX . 'user_id = ?',
            'vals' => array((int)$notificationId, (int)$userId)
        );
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX . 'is_read' => 1), $smt)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function getUnreadNotificationCount($userId)
    {
        $srch = new SearchBase(static::DB_TBL, 'unt');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $cnd = $srch->addCondition('unt.unotification_user_id', '=', $userId);
        $cnd = $srch->addCondition('unt.unotification_is_read', '=', 0);
        $srch->addCondition('unt.unotification_type', 'IN', Notifications::getRfqModuleNotificationTypes());
        $srch->addMultipleFields(array("count(unt.unotification_id) as UnReadNotificationCount"));
        $rs = $srch->getResultSet();
        if (!$rs) {
            return 0;
        }
        $res = FatApp::getDb()->fetch($rs);
        return $res['UnReadNotificationCount'];
    }

    public static function getRfqModuleNotificationTypes()
    {
        return [
            static::NEW_RFQ_SUBMISSION,
            static::NEW_RFQ_OFFER_SUBMISSION_BY_SELLER,
            static::NEW_RFQ_OFFER_SUBMISSION_BY_BUYER,
            static::NEW_RFQ_OFFER_UPDATE_BY_SELLER,
            static::NEW_RFQ_OFFER_UPDATE_BY_BUYER,
            static::INVOICE_SHARED_BY_SELLER,
            static::INVOICE_REGENERATE_REQUEST_BY_BUYER,
            static::RFQ_CLOSED_BY_ADMIN,
        ];
    }
}
