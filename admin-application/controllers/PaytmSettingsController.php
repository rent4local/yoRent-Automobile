<?php

class PaytmSettingsController extends PaymentMethodSettingsController
{
    public static function form($langId)
    {
        $frm = new Form('frmPaymentMethods');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_ID', $langId), 'merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Merchant_Key', $langId), 'merchant_key');
        $frm->addRequiredField(Labels::getLabel('LBL_Website', $langId), 'merchant_website');
        $frm->addRequiredField(Labels::getLabel('LBL_Channel_ID', $langId), 'merchant_channel_id');
        $frm->addRequiredField(Labels::getLabel('LBL_Industry_Type_ID', $langId), 'merchant_industry_type');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
