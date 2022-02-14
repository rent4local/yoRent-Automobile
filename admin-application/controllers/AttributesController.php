<?php

class AttributesController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewAttributes($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditAttributes($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function form()
    {
        $this->objPrivilege->canEditAttributes();
        $prodCatId = FatApp::getPostedData('prodCategoryId', FatUtility::VAR_INT, 0);
        if (0 > $prodCatId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $attrData = array('attr_prodcat_id' => $prodCatId);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = $this->getForm();

        $attrId = FatApp::getPostedData('attrId', FatUtility::VAR_INT, 0);
        if ($attrId > 0) {
            $attr = new AttrGroupAttribute($attrId);
            $attrData = $attr->getDetail();
            unset($attrData[$siteDefaultLangId]['attr_type']);
            $attrData = $this->formatAttrData($attrData);
        }

        $frm->fill($attrData);

        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $this->set('prodCatId', $prodCatId);
        $this->set('attrId', $attrId);
        $this->set('frm', $frm);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLangData', $languages);
        $this->_template->render(false, false);
    }

    private function getForm()
    {
        $this->objPrivilege->canEditAttributes();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $yesNoArr = applicationConstants::getYesNoArr($siteDefaultLangId);
        $attrTypes = AttrGroupAttribute::getNumericTypeArr($siteDefaultLangId) + AttrGroupAttribute::getTextualTypeArr($siteDefaultLangId);

        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $frm = new Form('frmAttr', array('id' => 'frmAttr'));

        $frm->addRequiredField(Labels::getLabel('LBL_Field_Name', $siteDefaultLangId), 'attr_name[' . $siteDefaultLangId . ']');
        $fldType = $frm->addSelectBox(Labels::getLabel('LBL_Field_type', $siteDefaultLangId), 'attr_type', $attrTypes, '', array(), '');

        $fld = $frm->addTextArea(Labels::getLabel('LBL_Option_Data', $siteDefaultLangId), 'attr_options[' . $siteDefaultLangId . ']');
        $fld->htmlAfterField = Labels::getLabel('LBL_Enter_Data_Separated_By_New_Line:<br>_E.g:', $siteDefaultLangId) . '<br />' . Labels::getLabel('LBL_Yes', $siteDefaultLangId) . '<br />' . Labels::getLabel('LBL_NO', $siteDefaultLangId);
        $optionDataFldReqObj = new FormFieldRequirement('attr_options[' . $siteDefaultLangId . ']', Labels::getLabel('LBL_Option_Data', $siteDefaultLangId));
        $optionDataFldReqObj->setRequired(true);

        $optionDataFldUnReqObj = new FormFieldRequirement('attr_options[' . $siteDefaultLangId . ']', Labels::getLabel('LBL_Option_Data', $siteDefaultLangId));
        $optionDataFldUnReqObj->setRequired(false);

        $fldType->requirements()->addOnChangerequirementUpdate(AttrGroupAttribute::ATTRTYPE_SELECT_BOX, 'eq', 'attr_options[' . $siteDefaultLangId . ']', $optionDataFldReqObj);
        $fldType->requirements()->addOnChangerequirementUpdate(AttrGroupAttribute::ATTRTYPE_CHECKBOXES, 'eq', 'attr_options[' . $siteDefaultLangId . ']', $optionDataFldReqObj);
        $fldType->requirements()->addOnChangerequirementUpdate(AttrGroupAttribute::ATTRTYPE_SELECT_BOX, 'ne', 'attr_options[' . $siteDefaultLangId . ']', $optionDataFldUnReqObj);
        $fldType->requirements()->addOnChangerequirementUpdate(AttrGroupAttribute::ATTRTYPE_CHECKBOXES, 'ne', 'attr_options[' . $siteDefaultLangId . ']', $optionDataFldUnReqObj);

        $frm->addTextBox(Labels::getLabel('LBL_Field_postfix', $siteDefaultLangId), 'attr_postfix[' . $siteDefaultLangId . ']');
        $frm->addTextBox(Labels::getLabel('LBL_Field_Group_Name', $siteDefaultLangId), 'attrgrp_name[' . $siteDefaultLangId . ']');
        $frm->addSelectBox(Labels::getLabel('LBL_Display_in_filter', $siteDefaultLangId), 'attr_display_in_filter', $yesNoArr);
		
		if (applicationConstants::getActiveTheme() == applicationConstants::THEME_AUTOMOBILE) {
			$frm->addSelectBox(Labels::getLabel('LBL_Display_with_Product_Listing', $siteDefaultLangId), 'attr_display_in_listing', $yesNoArr);
		}
		
        $frm->addHiddenField('', 'attr_prodcat_id');
        $frm->addHiddenField('', 'attr_attrgrp_id');
        $frm->addHiddenField('', 'attr_id');
        $frm->addHiddenField('', 'lang_id', $siteDefaultLangId);

        foreach ($languages as $key => $language) {
            $frm->addTextBox(Labels::getLabel('LBL_Field_Name', $key), 'attr_name[' . $key . ']');

            $fld = $frm->addTextArea(Labels::getLabel('LBL_Option_Data', $key), 'attr_options[' . $key . ']');
            $fld->htmlAfterField = Labels::getLabel('LBL_Enter_Data_Separated_By_New_Line:<br>_E.g:', $key) . '<br />' . Labels::getLabel('LBL_Yes:', $key) . '<br />' . Labels::getLabel('LBL_NO:', $key);

            $frm->addTextBox(Labels::getLabel('LBL_Field_postfix', $key), 'attr_postfix[' . $key . ']');
            $frm->addTextBox(Labels::getLabel('LBL_Field_Group_Name', $key), 'attrgrp_name[' . $key . ']');
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->adminLangId));
        $frm->addButton('', 'btn_discard', Labels::getLabel('LBL_Clear', $this->adminLangId));
        return $frm;
    }

    public function setup()
    {
        $this->objPrivilege->canEditAttributes();
        $flagToDeletOldEntry = 0;

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if ($post == false) {
            FatUtility::dieWithError(current($frm->getValidationErrors()));
        }

        $languages = Language::getAllNames();
        $prodCatId = FatUtility::int($post['attr_prodcat_id']);
        $attrGrpId = FatUtility::int($post['attr_attrgrp_id']);
        $attrId = FatUtility::int($post['attr_id']);

        if (0 < $attrId) {
            $attrData = AttrGroupAttribute::getAttributesById($attrId, array('attr_fld_name', 'attr_identifier', 'attr_type', 'attr_attrgrp_id'));
            if ($attrData === false) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $attrFldName = $attrData['attr_fld_name'];
        }
        $siteDefaultId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);

        if (0 < $attrGrpId) {
            $attrGrpRow = AttributeGroup::getAttributesById($attrGrpId);
            if (empty($attrGrpRow)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Group', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            $attrGrpLangData = AttributeGroup::getLangDataArr($attrGrpId);
            foreach ($languages as $key => $language) {
                if (!array_key_exists($key, $attrGrpLangData) && !empty($post['attrgrp_name'][$key])) {
                    $grpLangData[$key] = $post['attrgrp_name'][$key];
                }
            }

            if (!empty($grpLangData)) {
                $attrGrpObj = new AttributeGroup($attrGrpId);
                $attrGrpObj->setupLangData($grpLangData);
            }
        } else if (!empty($post['attrgrp_name'][$siteDefaultId])) {

            $attrGrpObj = new AttributeGroup($attrGrpId);
            $response = $attrGrpObj->getAttrGrpByName($post['attrgrp_name'][$siteDefaultId], $siteDefaultId);
            
            if (!empty($response)) {
                $attrGrpId = $response['attrgrp_id'];
            } else {
                $attrGrpObj = new AttributeGroup();
                $attrGrpObj->setup($post['attrgrp_name'], $siteDefaultId);
                $attrGrpId = $attrGrpObj->getMainTableRecordId();

                $attrGrpObj = new AttributeGroup($attrGrpId);
                $attrGrpObj->setupLangData($post['attrgrp_name']);
            }
        }

        if ((0 == $attrId) || (0 < $attrId && ($attrData['attr_type'] != $post['attr_type'] || $attrData['attr_attrgrp_id'] != $attrGrpId))) {
            $numAttributes = AttrGroupAttribute::getNumericTypeArr($this->adminLangId);

            $attrGrpObj = new AttributeGroup($attrGrpId);
            $lastNumAndTxtAttr = $attrGrpObj->getLastNumAndTxtAttr($prodCatId);

            $nextNumAttr = intval($lastNumAndTxtAttr['num_attributes']) + 1;
            $nextTextAttr = intval($lastNumAndTxtAttr['text_attributes']) + 1;

            if (0 < $attrId && array_key_exists($attrData['attr_type'], $numAttributes) && array_key_exists($post['attr_type'], $numAttributes) && $attrData['attr_attrgrp_id'] == $attrGrpId) {
                $attrFldName = $attrData['attr_fld_name'];
            } else if ($post['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                if ($nextTextAttr > 30) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Text_Field_Limit_Error', $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $attrFldName = 'prodtxtattr_text_' . $nextTextAttr;
                $flagToDeletOldEntry = 1;
            } else {
                if ($nextNumAttr > 30) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Numeric_Field_Limit_Error', $this->adminLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
                $attrFldName = 'prodnumattr_num_' . $nextNumAttr;
                $flagToDeletOldEntry = 1;
            }
        }

        if ($flagToDeletOldEntry == 1 && $attrId > 0) {
            $attrGrpAttrObj = new AttrGroupAttribute($attrId);
            if (!$attrGrpAttrObj->resetLangData()) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $dataToSaveArr = array(
            'attr_attrgrp_id' => $attrGrpId,
            'attr_prodcat_id' => $prodCatId,
            'attr_identifier' => $post['attr_name'][$this->adminLangId],
            'attr_type' => $post['attr_type'],
            'attr_fld_name' => $attrFldName,
            'attr_display_in_filter' => isset($post['attr_display_in_filter']) ? $post['attr_display_in_filter'] : 0,
            'attr_display_in_listing' => isset($post['attr_display_in_listing']) ? $post['attr_display_in_listing'] : 0,
        );

        $record = new AttrGroupAttribute($attrId);
        $record->assignValues($dataToSaveArr);
        if (!$record->save()) {
            FatUtility::dieWithError($record->getError());
        }

        foreach ($languages as $key => $language) {
            $data = array(
                'attr_name' => $post['attr_name'][$key],
                'attr_prefix' => '',
                'attr_postfix' => $post['attr_postfix'][$key],
                'attr_options' => $post['attr_options'][$key],
            );

            $this->attrLangSetup($record->getMainTableRecordId(), $data, $key);
        }

        $this->set('msg', $this->str_update_record);
        $this->set('attr_id', $record->getMainTableRecordId());
        $this->set('lang_id', 1);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function attrLangSetup(int $attrId, array $data, int $langId)
    {

        if ($attrId < 1 || $langId < 1 || empty($data)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data_to_update = array(
            'attrlang_attr_id' => $attrId,
            'attrlang_lang_id' => $langId,
            'attr_name' => $data['attr_name'],
            'attr_prefix' => $data['attr_prefix'],
            'attr_postfix' => $data['attr_postfix'],
        );
        if (isset($data['attr_options'])) {
            $data_to_update['attr_options'] = $data['attr_options'];
        }
        $attrGrpAttrObj = new AttrGroupAttribute($attrId);
        if (!$attrGrpAttrObj->updateLangData($langId, $data_to_update)) {
            Message::addErrorMessage($attrGrpAttrObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        return true;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditAttributes();
        $post = FatApp::getPostedData();
        $attrId = FatUtility::int($post['attrId']);
        $status = FatUtility::int($post['status']);

        if (1 > $attrId || 0 > $status) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $attrGrpAttrObj = new AttrGroupAttribute($attrId);
        if (!$attrGrpAttrObj->changeStatus($status)) {
            Message::addErrorMessage($adminObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Record_deleted_Successfully', $this->adminLangId));
    }

    private function formatAttrData(array $attrData = []): array
    {
        $response = array();
        $langSpecificFlds = array('attr_name', 'attrgrp_name', 'attr_postfix', 'attr_options');
        foreach ($attrData as $lang => $data) {
            foreach ($data as $key => $attr) {
                if (in_array($key, $langSpecificFlds)) {
                    $response[$key][$lang] = $attr;
                } else {
                    $response[$key] = $attr;
                }
            }
        }
        return $response;
    }

}
