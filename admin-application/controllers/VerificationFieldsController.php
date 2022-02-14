<?php

class VerificationFieldsController extends AdminBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
        
        if(!FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1)) {
            Message::addInfo(Labels::getLabel("MSG_Invalid_Request", $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Home'));
        }
        $this->objPrivilege->canViewDocumentVerification();
    }

    public function index()
    {
        $this->set("canEdit", $this->objPrivilege->canEditDocumentVerification($this->admin_id, true));
        $this->_template->render();
    }

    public function search()
    {
        $srch = VerificationFields::getSearchObject($this->adminLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set("listing", $records);

        $this->set("canEdit", $this->objPrivilege->canEditDocumentVerification($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function form($fldId = 0)
    {
        $this->objPrivilege->canEditDocumentVerification();
		$fldId = FatUtility::int($fldId);
		
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
		$languages = Language::getAllNames();
       
        $frm = static::getForm($this->adminLangId, $fldId);
        $vFldsData = [];
        if (0 < $fldId) {
			$vFldsData = VerificationFields::getAttributesById($fldId);
            foreach ($languages as $langId => $data) {
                $verificationFlds = new VerificationFields();
                $verificationFldsData = $verificationFlds->getAttributesByLangId($langId, $fldId);
                if (!empty($verificationFldsData)) {
                    $vFldsData['vflds_name'][$langId] = $verificationFldsData['vflds_name'];
                }
            }
			
            if ($vFldsData === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($vFldsData);
        }

		$langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $this->set('vFldsData', $vFldsData);
        $this->set('otherLangData', $langData);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('languages', Language::getAllNames());
        $this->set('fldId', $fldId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditDocumentVerification();

        $frm = static::getForm($this->adminLangId);
        $post = FatApp::getPostedData();

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fldId = $post['vflds_id'];
        unset($post['vflds_id']);
		
        $record = new VerificationFields($fldId);
        if (!$record->addUpdateData($post,$this->adminLangId)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }
	
	public function translatedData()
    {
        $vFldsName = FatApp::getPostedData('vFldsName', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data['vflds_name'] = $vFldsName;
        $vfldsObj = new VerificationFields();
        $vfldsData = $vfldsObj->getTranslatedData($data, $toLangId);
        if (!$vfldsData) {
            Message::addErrorMessage($vfldsData->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('vFldsName', $vfldsData[$toLangId]['vflds_name']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditDocumentVerification();
        $vfldsId = FatApp::getPostedData('vfldsId', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        if (0 == $vfldsId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $vfldsObj = new VerificationFields($vfldsId);
        $srch = $vfldsObj->getSearchObject();
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs);

        $this->updateFieldStatus($vfldsId, $status);
    
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateFieldStatus($vfldsId, $status)
    {
        $status = FatUtility::int($status);
        $vfldsId = FatUtility::int($vfldsId);
        if (1 > $vfldsId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $vfldsObj = new VerificationFields($vfldsId);

        if (!$vfldsObj->activateField($status)) {
            Message::addErrorMessage($vfldsObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public static function getForm($langId, $fldId = 0)
    {
        $fldId = FatUtility::int($fldId);

        $frm = new Form('frmVerificationField');
        $frm->addHiddenField('', 'vflds_id', $fldId);

        $fldTypeArr = VerificationFields::getFldTypeArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Field_type', $langId), 'vflds_type', $fldTypeArr, '', array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Is_required', $langId), 'vflds_required', $yesNoArr, '', array(), '');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $langId), 'vflds_active', $activeInactiveArr, '', array(), '');
        
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        foreach ($languages as $languageId => $lang) {
            if ($languageId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Field_name', $languageId), 'vflds_name[' . $languageId . ']');
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_Field_name', $languageId), 'vflds_name[' . $languageId . ']');
            }
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }



}