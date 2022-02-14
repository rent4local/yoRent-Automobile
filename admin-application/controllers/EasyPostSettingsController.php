<?php

class EasyPostSettingsController extends ShippingServicesSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmEasyPost');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_API_KEY', $langId), 'api_key');
        $fld1 = new FormFieldRequirement('api_key', Labels::getLabel('LBL_API_KEY', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('api_key', Labels::getLabel('LBL_API_KEY', $langId));
        $reqFld1->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_API_KEY', $langId), 'live_api_key');
        $liveFld1 = new FormFieldRequirement('live_api_key', Labels::getLabel('LBL_API_KEY', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_api_key', Labels::getLabel('LBL_API_KEY', $langId));
        $reqLiveFld1->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'api_key', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_api_key', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'api_key', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_api_key', $reqLiveFld1);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
