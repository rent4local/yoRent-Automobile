<?php

class SmsNotificationController extends PluginBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function callback($keyName)
    {
        $error = '';
        if (false === PluginHelper::includePlugin($keyName, 'sms-notification', $error, $this->siteLangId)) {
            $this->error = $error;
            return false;
        }
        $smsNotification = new $keyName($this->siteLangId);
        $smsNotification->callback();
    }
}
