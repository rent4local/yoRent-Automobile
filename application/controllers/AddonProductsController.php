<?php

class AddonProductsController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $this->userPrivilege->canViewAddons(UserAuthentication::getLoggedUserId());
        if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 0) {
            FatUtility::exitWithErrorCode(404);
        }
        $searchForm = $this->searchForm();
        $this->set('searchForm', $searchForm);
        $this->set('canEdit', $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(true, true);
    }

    public function search()
    {
        $userId = $this->userParentId;
        $searchForm = $this->searchForm();
        $post = $searchForm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(['selprod_id', 'selprod_price', 'selprod_user_id', 'selprod_is_eligible_cancel', 'selprod_is_eligible_refund', 'IFNULL(selprod_title, selprod_identifier) as selprod_title', 'selprod_active']);
        
        
        $srch->addCondition('selprod_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $srch->addCondition('selprod_deleted', '=', 'mysql_func_0', 'AND', true);
        $srch->addCondition('selprod_type', '=', 'mysql_func_2', 'AND', true);

        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        if ($keyword != '') {
            $cnd = $srch->addCondition('selprod_title', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('selprod_title', 'like', '%' . $keyword . '%');
        }

        $addonProdStatus = FatApp::getPostedData('addonprod_active');

        if ($addonProdStatus != '') {
            $srch->addCondition('selprod_active', '=', 'mysql_func_'. $addonProdStatus, 'AND', true);
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);

        $this->set('canEdit', $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId(), true));
        $this->set('arr_listing', $records);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());

        $this->_template->render(false, false);
    }

    public function form(int $id = 0)
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
        $addonProdData = array();
        if (0 < $id) {
            $addonProdData = SellerProduct::getAttributesById($id, ['selprod_id as addonprod_id', 'selprod_price as addonprod_price', 'selprod_user_id as addonprod_user_id', 'selprod_is_eligible_cancel as is_eligible_cancel', 'selprod_is_eligible_refund as is_eligible_refund', 'selprod_identifier as addon_identifier']);
            $addonProdLangData = SellerProduct::getLangDataArr($id);

            if (empty($addonProdData) || $addonProdData['addonprod_user_id'] != $this->userParentId) {
                FatUtility::exitWithErrorCode(404);
            }


            if (!empty($addonProdLangData)) {
                foreach ($addonProdLangData as $langData) {
                    $addonProdData['addonprod_title'][$langData['selprodlang_lang_id']] = $langData['selprod_title'];
                    $addonProdData['addonprod_description_' . $langData['selprodlang_lang_id']] = $langData['selprod_rental_terms'];
                }
            }

            $taxData = array();
            $tax = Tax::getTaxCatObjByProductId($addonProdData['addonprod_id'], $this->siteLangId, SellerProduct::PRODUCT_TYPE_ADDON, $addonProdData['addonprod_id']);
            $tax->addCondition('ptt_seller_user_id', '=', 'mysql_func_'. $addonProdData['addonprod_user_id'], 'AND', true);
            $activatedTaxServiceId = Tax::getActivatedServiceId();
            $tax->addFld('ptt_taxcat_id');
            if ($activatedTaxServiceId) {
                $tax->addFld(array('concat(IFNULL(t_l.taxcat_name,t.taxcat_identifier), " (",t.taxcat_code,")")as taxcat_name', 'concat(IFNULL(t_lrent.taxcat_name,trent.taxcat_identifier), " (",trent.taxcat_code,")")as taxcat_name_rent'));
            } else {
                $tax->addFld(array('IFNULL(t_l.taxcat_name,t.taxcat_identifier)as taxcat_name', 'IFNULL(t_lrent.taxcat_name,trent.taxcat_identifier)as taxcat_name_rent'));
            }

            $tax->doNotCalculateRecords();
            $tax->setPageSize(1);
            $tax->addOrder('ptt_seller_user_id', 'ASC');
            $rs = $tax->getResultSet();
            $taxData = FatApp::getDb()->fetch($rs);
            if (!empty($taxData)) {
                $addonProdData['ptt_taxcat_id'] = $taxData['ptt_taxcat_id'];
                $addonProdData['taxcat_name'] = $taxData['taxcat_name'];
            }
            unset($addonProdData['addonprod_user_id']);
        }
        $form = $this->getForm();
        $form->fill($addonProdData);

        $this->set('form', $form);
        /* $this->set('includeEditor', true); */
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('addonId', $id);
        $this->_template->render(true, true);
    }

    private function getForm()
    {
        $frm = new Form('addonProductsForm', array('id' => 'addonProductsForm'));

        $priceLbl = Labels::getLabel('LBL_price', $this->siteLangId);
        if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
            $priceLbl = Labels::getLabel('LBL_price(Including_Tax)', $this->siteLangId);
        }
        $priceFld = $frm->addFloatField($priceLbl, 'addonprod_price');
        $priceFld->requirements()->setRequired(true);
        $priceFld->requirements()->setFloatPositive();
        $priceFld->requirements()->setRange('0', '9999999999');

        $frm->addTextBox(Labels::getLabel('LBL_Tax_Category', $this->siteLangId), 'taxcat_name');
        $frm->addCheckBox(Labels::getLabel('LBL_Refund_Amount_on_Cancellation', $this->siteLangId), 'is_eligible_cancel', 1, []);
        $frm->addCheckBox(Labels::getLabel('LBL_Refund_Amount_On_Return', $this->siteLangId), 'is_eligible_refund', 1, []);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        $frm->addRequiredField(Labels::getLabel('LBL_Addon_Identifier', $this->siteLangId), 'addon_identifier');
        
        foreach ($languages as $langId => $lang) {
            if ($langId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->siteLangId), 'addonprod_title[' . $langId . ']');
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_Name', $this->siteLangId), 'addonprod_title[' . $langId . ']');
            }
            $frm->addTextArea(Labels::getLabel('LBL_Terms_and_conditions', $this->siteLangId), 'addonprod_description_' . $langId);
        }

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        unset($languages[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addHiddenField('', 'addonprod_id');
        $frm->addHiddenField('', 'ptt_taxcat_id');
        $frm->addButton('', 'btn_discard', Labels::getLabel('LBL_Discard', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->siteLangId));
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js'));
        return $frm;
    }

    public function setup()
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
    
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $isCancel = (isset($post['is_eligible_cancel'])) ? FatUtility::int($post['is_eligible_cancel']) : 0;
        $isRefund = (isset($post['is_eligible_refund'])) ? FatUtility::int($post['is_eligible_refund']) : 0;

        if (1 > FatUtility::int($post['ptt_taxcat_id']) && trim($post['taxcat_name']) != "") {
          Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Tax_Category_From_List', $this->siteLangId));
          FatUtility::dieWithError(Message::getHtml());
        }
        $addonProdId = FatUtility::int($post['addonprod_id']);
        $data = array(
            'selprod_user_id' => $this->userParentId,
            'selprod_price' => $post['addonprod_price'],
            'selprod_type' => 2,
            'selprod_stock' => 1,
            'selprod_min_order_qty' => 1,
            'selprod_subtract_stock' => 0,
            'selprod_condition' => 1,
            'selprod_active' => 1,
            'selprod_is_eligible_cancel' => $isCancel,
            'selprod_is_eligible_refund' => $isRefund,
            'selprod_identifier' => $post['addon_identifier']
        );
        if (1 > $addonProdId) {
            $data['selprod_added_on'] = date('Y-m-d H:i:s');
            $data['selprod_available_from'] = date('Y-m-d H:i:s');
        }

        $sellerProdObj = new SellerProduct($addonProdId);
        $sellerProdObj->assignValues($data);
        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $addonProdId = $sellerProdObj->getMainTableRecordId();
        /* REMOVE TAX CATEGORY IF NOT SELECTED */
        if (1 > FatUtility::int($post['ptt_taxcat_id'])) {
            $where = ['smt' => 'ptt_product_id = ? AND ptt_type = ?', 'vals' => [$addonProdId, SellerProduct::PRODUCT_TYPE_ADDON]];
            if (!FatApp::getDb()->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, $where)) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        /* ] */
        if (FatUtility::int($post['ptt_taxcat_id']) > 0) {
            $prod = new Product($addonProdId);
            if (!$prod->saveProductTax(FatUtility::int($post['ptt_taxcat_id']), $this->userParentId, SellerProduct::PRODUCT_TYPE_ADDON, FatUtility::int($post['ptt_taxcat_id']))) {
                Message::addErrorMessage($prod->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        LateChargesProfile::checkAndUpdateProfile($addonProdId, $this->userParentId, SellerProduct::PRODUCT_TYPE_ADDON);
        $this->setupLangData($addonProdId, $post);
        $this->setupOtherData($addonProdId);

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->set('addonId', $addonProdId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function setupOtherData(int $addonProdId)
    {
        $prodRentalData = array(
            'sprodata_is_for_rent' => applicationConstants::YES,
            'sprodata_is_for_sell' => applicationConstants::NO,
            'sprodata_selprod_id' => $addonProdId,
        );
        $rentProdObj = new ProductRental();
        if (!$rentProdObj->addUpdateSelProData($prodRentalData)) {
            Message::addErrorMessage($rentProdObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function setupLangData(int $addonProdId, array $data)
    {
        /* Update seller product language data[ */
        $languages = Language::getAllNames();
        $productNames = (isset($data['addonprod_title'])) ? $data['addonprod_title'] : [];

        $autoUpdateOtherLangsData = isset($data['auto_update_other_langs_data']) ? FatUtility::int($data['auto_update_other_langs_data']) : 0;
        if (!empty($productNames)) {
            $sellerProdObj = new SellerProduct($addonProdId);
            foreach ($productNames as $langId => $prodName) {
                if (empty($prodName) && $autoUpdateOtherLangsData > 0) {
                    $sellerProdObj->saveTranslatedProductLangData($langId);
                } else {
                    $selProdData = array(
                        'selprodlang_selprod_id' => $addonProdId,
                        'selprodlang_lang_id' => $langId,
                        'selprod_title' => $prodName,
                        'selprod_rental_terms' => $data['addonprod_description_' . $langId],
                    );

                    if (!$sellerProdObj->updateLangData($langId, $selProdData)) {
                        Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
        }
        /* ] */
    }

    public function changeStatus()
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        if (empty($post) || 1 > FatUtility::int($post['addonProdId']) || 0 > FatUtility::int($post['status'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $addonProdData = SellerProduct::getAttributesById($post['addonProdId'], ['selprod_user_id']);

        if (empty($addonProdData['selprod_user_id']) || $addonProdData['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $addonProductObj = new SellerProduct($post['addonProdId']);
        $resp = $addonProductObj->changeStatus($post['status']);
        if (!$resp) {
            Message::addErrorMessage($addonProductObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $msg = Labels::getLabel('MSG_Status_Updated_Successfully', $this->siteLangId);
        FatUtility::dieJsonSuccess($msg);
    }

    private function searchForm()
    {
        $frm = new Form('frmSearchAddonProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By_Addon_Name', $this->siteLangId), 'keyword');
        $frm->addSelectBox(Labels::getLabel('LBL_Select_Status', $this->siteLangId), 'addonprod_active', applicationConstants::getActiveInactiveArr($this->siteLangId),'',[],Labels::getLabel('LBL_Select_Status', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));
        $frm->addButton('', 'btn_clear', Labels::getLabel('LBL_Clear', $this->siteLangId));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function mediaForm()
    {
        $mediaForm = $this->getMediaForm();
        $this->set('mediaForm', $mediaForm);

        $this->_template->render(false, false);
    }

    private function getMediaForm()
    {

        $frm = new Form('imageFrm');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->siteLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->siteLangId)) + $languagesAssocArr, '', [], '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->siteLangId), 'addonprod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $this->siteLangId) . '</label></div><small>' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500', $this->siteLangId) . '</small>';
        $frm->addHiddenField('', 'min_width', 500);
        $frm->addHiddenField('', 'min_height', 500);
        //$frm->addHiddenField('', 'addonprod_id');
        return $frm;
    }

    public function setupImage()
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
        }

        $addonprod_id = FatUtility::int($post['addonprod_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        $addonProdData = SellerProduct::getAttributesById($addonprod_id, ['selprod_user_id']);
        if (empty($addonProdData['selprod_user_id']) || $addonProdData['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_select_a_file", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, $addonprod_id, 0, 0, $lang_id)) {
            FatUtility::dieWithError($fileHandlerObj->getError());
        }

        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, $addonprod_id, 0, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function mediaListing()
    {
        $addonProdId = FatApp::getPostedData('addon_prod_id', FatUtility::VAR_INT, 0);
        $addonProdImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, $addonProdId, 0, -1, false, 0, 0, true);
        $this->set('addonProdImages', $addonProdImages);
        $this->set('addonProdId', $addonProdId);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function deleteImage(int $addonProdId, int $imageId)
    {
        $this->userPrivilege->canEditAddons(UserAuthentication::getLoggedUserId());
        $addonProdData = SellerProduct::getAttributesById($addonProdId, ['selprod_user_id']);
        if (empty($addonProdData['selprod_user_id']) || $addonProdData['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, $addonProdId, $imageId)) {
            FatUtility::dieWithError($fileHandlerObj->getError());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Image_removed_successfully', $this->siteLangId));
    }

}
