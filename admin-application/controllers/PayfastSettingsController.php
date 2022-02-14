<?php

class PayfastSettingsController extends PaymentMethodSettingsController
{
    public static function form(int $langId)
    {
        $frm = new Form('frmPayfast');

        $envoirment = Plugin::getEnvArr($langId);
        $envFld = $frm->addSelectBox(Labels::getLabel('LBL_ENVOIRMENT', $langId), 'env', $envoirment, '', ['class' => 'fieldsVisibility-js'], '');
        $envFld->requirement->setRequired(true);

        $fld = $frm->addRequiredField(Labels::getLabel('LBL_PASSPHRASE', $langId), 'passphrase');
        $fld->htmlAfterField = '<span class="form-text text-muted">' . Labels::getLabel("LBL_PAYFAST_PASSPHRASE_DESCRIPTION", $langId) . '</span>';
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SIGNATURE', $langId), 'signature');
        $fld->htmlAfterField = '<span class="form-text text-muted">' . Labels::getLabel("LBL_PAYFAST_SIGNATURE_DESCRIPTION", $langId) . '</span>';

        $frm->addTextBox(Labels::getLabel('LBL_MERCHANT_ID', $langId), 'merchant_id');
        $fld1 = new FormFieldRequirement('merchant_id', Labels::getLabel('LBL_MERCHANT_ID', $langId));
        $fld1->setRequired(false);
        $reqFld1 = new FormFieldRequirement('merchant_id', Labels::getLabel('LBL_MERCHANT_ID', $langId));
        $reqFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_MERCHANT_KEY', $langId), 'merchant_key');
        $fld2 = new FormFieldRequirement('merchant_key', Labels::getLabel('LBL_MERCHANT_KEY', $langId));
        $fld2->setRequired(false);
        $reqFld2 = new FormFieldRequirement('merchant_key', Labels::getLabel('LBL_MERCHANT_KEY', $langId));
        $reqFld2->setRequired(true);


        $frm->addTextBox(Labels::getLabel('LBL_MERCHANT_ID', $langId), 'live_merchant_id');
        $liveFld1 = new FormFieldRequirement('live_merchant_id', Labels::getLabel('LBL_MERCHANT_ID', $langId));
        $liveFld1->setRequired(false);
        $reqLiveFld1 = new FormFieldRequirement('live_merchant_id', Labels::getLabel('LBL_MERCHANT_ID', $langId));
        $reqLiveFld1->setRequired(true);

        $frm->addTextBox(Labels::getLabel('LBL_MERCHANT_KEY', $langId), 'live_merchant_key');
        $liveFld2 = new FormFieldRequirement('live_merchant_key', Labels::getLabel('LBL_MERCHANT_KEY', $langId));
        $liveFld2->setRequired(false);
        $reqLiveFld2 = new FormFieldRequirement('live_merchant_key', Labels::getLabel('LBL_MERCHANT_KEY', $langId));
        $reqLiveFld2->setRequired(true);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'merchant_id', $reqFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'merchant_key', $reqFld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_merchant_id', $liveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_SANDBOX, 'eq', 'live_merchant_key', $liveFld2);


        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'merchant_id', $fld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'merchant_key', $fld2);

        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_merchant_id', $reqLiveFld1);
        $envFld->requirements()->addOnChangerequirementUpdate(Plugin::ENV_PRODUCTION, 'eq', 'live_merchant_key', $reqLiveFld2);

        $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }
}
