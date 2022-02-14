<?php

class ElavonSettingsController extends PaymentMethodSettingsController
{
	public static function form(int $langId)
    {
		$frm = new Form('frmElavon');
        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment);
        $envFld->requirement->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_SSL_Merchant_Id', $langId), 'ssl_merchant_id');
        $frm->addRequiredField(Labels::getLabel('LBL_SSL_User_Id', $langId), 'ssl_user_id');
        $frm->addRequiredField(Labels::getLabel('LBL_SSL_Pin', $langId), 'ssl_pin');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
	}
    
}