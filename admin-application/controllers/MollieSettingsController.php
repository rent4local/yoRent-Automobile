<?php

class MollieSettingsController extends PaymentMethodSettingsController
{
	public static function form(int $langId)
    {
		$frm = new Form('frmMollie');
        $frm->addRequiredField(Labels::getLabel('LBL_Secret_Key', $langId), 'privateKey');
        //$frm->addRequiredField(Labels::getLabel('LBL_Publishable_Key', $langId), 'publishableKey');
        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
	}
    
}