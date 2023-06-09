<?php

trait SellerCollections
{
    public function shopCollections()
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        $this->commonShopCollection();
        $this->_template->render(false, false);
    }

    public function searchShopCollections()
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $records = ShopCollection::getCollectionGeneralDetail($shopDetails['shop_id']);
        $this->set('canEdit', $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $records);
        $this->_template->render(false, false);
    }

    public function commonShopCollection()
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        return $shop_id;
    }

    public function shopCollection()
    {
        $userId = $this->userParentId;
        if (!UserPrivilege::canEditSellerCollection($userId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->commonShopCollection();

        $this->_template->render(false, false);
    }

    public function shopCollectionGeneralForm($scollection_id = 0)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $userId = $this->userParentId;
        $shop_id = $this->commonShopCollection();
        $colectionForm = $this->getCollectionGeneralForm('', $shop_id);
        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollection_id);
        $baseUrl = Shop::getRewriteCustomUrl($shop_id);
        if (!empty($shopcolDetails)) {
            /* url data[ */
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');

            $urlSrch->addCondition('urlrewrite_original', '=', 'shops/collection/' . $shop_id . '/' . $scollection_id);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $shopcolDetails['urlrewrite_custom'] = str_replace('-' . $baseUrl, '', $urlRow['urlrewrite_custom']);
            }
            /* ] */
            $scollection_id = (array_key_exists('scollection_id', $shopcolDetails)) ? $shopcolDetails['scollection_id'] : 0;
            $colectionForm->fill($shopcolDetails);
        }
        $this->set('scollection_id', $scollection_id);
        $this->set('baseUrl', $baseUrl);
        $this->set('shop_id', $shop_id);
        $this->set('colectionForm', $colectionForm);
        $this->_template->render(false, false);
    }

    public function deleteShopCollection($scollection_id)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $shop_id = $this->commonShopCollection();
        $this->markCollectionAsDeleted($shop_id, $scollection_id);
        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED', $this->siteLangId)
        );
    }

    public function deleteSelectedCollections()
    {
        $scollectionIdsArr = FatUtility::int(FatApp::getPostedData('scollection_ids'));
        $shop_id = $this->commonShopCollection();

        if (empty($scollectionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }

        foreach ($scollectionIdsArr as $scollection_id) {
            if (1 > $scollection_id) {
                continue;
            }
            $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollection_id);
            if (empty($shopcolDetails)) {
                continue;
            }

            $this->markCollectionAsDeleted($shop_id, $scollection_id);
        }
        FatUtility::dieJsonSuccess(
            Labels::getLabel('MSG_RECORD_DELETED', $this->siteLangId)
        );
    }

    private function markCollectionAsDeleted($shop_id, $scollection_id)
    {
        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shop_id, $scollection_id);
        if (empty($shopcolDetails)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $collection = new ShopCollection();
        if (!$collection->deleteCollection($scollection_id)) {
            Message::addErrorMessage($collection->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function getCollectionGeneralForm($scollection_id = 0, $shop_id = 0)
    {
        $shop_id = FatUtility::int($shop_id);
        $frm = new Form('frmShopCollection');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $frm->addHiddenField('', 'scollection_shop_id', $shop_id);
        $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->siteLangId), 'scollection_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->siteLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'scollection_active', $activeInactiveArr, applicationConstants::YES, array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function setupShopCollection()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $shop_id = FatUtility::int($post['scollection_shop_id']);
        $scollection_id = FatUtility::int($post['scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($shop_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getCollectionGeneralForm($scollection_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $record = new ShopCollection($scollection_id);

        $record->assignValues($post);
        if (!$collection_id = $record->save()) {
            Message::addErrorMessage(Labels::getLabel("MSG_This_identifier_is_not_available._Please_try_with_another_one.", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* url data[ */


        $shopOriginalUrl = Shop::SHOP_COLLECTION_ORGINAL_URL . $shop_id . '/' . $collection_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array( 'smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $shop = new Shop($shop_id);
            $shop->setupCollectionUrl($post['urlrewrite_custom'], $collection_id);
        }
        /* ] */
        $newTabLangId = 0;
        if ($collection_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                $row = ShopCollection::getAttributesByLangId($langId, $collection_id);
                if (!$row) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $collection_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('collection_id', $collection_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeShopCollectionStatus()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $scollectionId = FatApp::getPostedData('scollection_id', FatUtility::VAR_INT, 0);
        $shopId = $this->commonShopCollection();
        $shopcolDetails = ShopCollection::getCollectionGeneralDetail($shopId, $scollectionId);
        $status = ($shopcolDetails['scollection_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateShopCollectionStatus($scollectionId, $status);

        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkCollectionStatuses()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $this->commonShopCollection();
        $status = FatApp::getPostedData('collection_status', FatUtility::VAR_INT, -1);
        $scollectionIdsArr = FatUtility::int(FatApp::getPostedData('scollection_ids'));

        if (empty($scollectionIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }

        foreach ($scollectionIdsArr as $scollectionId) {
            if (1 > $scollectionId) {
                continue;
            }
            $this->updateShopCollectionStatus($scollectionId, $status);
        }
        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateShopCollectionStatus($scollectionId, $status)
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $scollectionId = FatUtility::int($scollectionId);
        $status = FatUtility::int($status);
        if (1 > $scollectionId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }
        $scollection = new ShopCollection($scollectionId);
        if (!$scollection->changeStatus($status)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function shopCollectionLangForm($scollection_id, $langId, $autoFillLangData = 0)
    {
        $scollection_id = Fatutility::int($scollection_id);
        if (!$scollection_id) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopColLangFrm = $this->getCollectionLangForm($scollection_id, $langId);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(ShopCollection::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($scollection_id, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $row = current($translatedData);
        } else {
            $row = ShopCollection::getAttributesByLangId($langId, $scollection_id);
        }

        if (!empty($row) && 0 < count($row)) {
            $data['scollection_id'] = $row['scollectionlang_scollection_id'];
            $data['lang_id'] = $row['scollectionlang_lang_id'];
            $data['name'] = $row['scollection_name'];
            $shopColLangFrm ->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('shopColLangFrm', $shopColLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('userId', $this->userParentId);
        $this->set('scollection_id', $scollection_id);
        $this->set('langId', $langId);
        $this->commonShopCollection();
        $this->_template->render(false, false);
    }

    private function getCollectionLangForm($scollection_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmMetaTagLang');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Collection_Name', $this->siteLangId), 'name');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function setupShopCollectionLang()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($post['scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getCollectionLangForm($scollection_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $record = new ShopCollection($scollection_id);

        if (!$record->addUpdateShopCollectionLang($post)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(ShopCollection::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($scollection_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        if ($scollection_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = ShopCollection::getAttributesByLangId($langId, $scollection_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $collection_id = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        if ($newTabLangId == 0 && !$this->isCollectionLinkFormFilled($scollection_id)) {
            $this->set('openCollectionLinkForm', true);
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('scollection_id', $scollection_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function isCollectionLinkFormFilled($scollection_id)
    {
        $sCollectionobj = new ShopCollection();
        if ($row = $sCollectionobj->getShopCollectionProducts($scollection_id, $this->siteLangId)) {
            return true;
        }
        return false;
    }


    /*  - --- Seller Product Links  ----- [*/

    public function shopCollectionProductLinkFrm($scollection_id)
    {
        $post = FatApp::getPostedData();
        $scollection_id = FatUtility::int($scollection_id);
        $shop_id = $this->commonShopCollection();
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $sellProdObj = new ShopCollection();
        $products = $sellProdObj->getShopCollectionProducts($scollection_id, $this->siteLangId);

        $collectionLinkFrm = $this->getCollectionLinksFrm();
        $data['scp_scollection_id'] = $scollection_id;
        $collectionLinkFrm->fill($data);
        $this->set('collectionLinkFrm', $collectionLinkFrm);
        $this->set('scollection_id', $scollection_id);
        $this->set('products', $products);



        $this->set('activeTab', 'LINKS');
        $this->_template->render(false, false);
    }

    private function getCollectionLinksFrm()
    {
        $frm = new Form('frmLinks1', array('id' => 'frmLinks1'));
        $frm->addSelectBox(Labels::getLabel('LBL_COLLECTION', $this->siteLangId), 'scp_selprod_id', [], '', array('id' => 'scp_selprod_id'));
        //$frm->addTextBox(Labels::getLabel('LBL_COLLECTION', $this->siteLangId), 'scp_selprod_id', '', array('id' => 'scp_selprod_id'));

        $frm->addHtml('', 'buy_together', '<div id="selprod-products"><ul class="list-vertical" ></ul></div><div class="gap"></div>');
        $frm->addHiddenField('', 'scp_scollection_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function setupSellerCollectionProductLinks()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        //print_r($post); die();
        $scollection_id = FatUtility::int($post['scp_scollection_id']);
        if (!UserPrivilege::canEditSellerCollection($scollection_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $product_ids = (isset($post['product_ids'])) ? $post['product_ids'] : array();

        unset($post['scp_selprod_id']);

        if ($scollection_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shopColObj = new ShopCollection();
        /* saving of product Upsell Product[ */
        if (!$shopColObj->addUpdateSellerCollectionProducts($scollection_id, $product_ids)) {
            Message::addErrorMessage($shopColObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shopCollectionMediaForm($scollection_id)
    {
        $collectionMediaFrm = $this->getShopCollectionMediaForm($scollection_id);
        $this->set('frm', $collectionMediaFrm);
        $this->set('language', Language::getAllNames());
        $this->set('scollection_id', $scollection_id);
        $this->_template->render(false, false);
    }

    private function getShopCollectionMediaForm($scollection_id)
    {
        $frm = new Form('frmCollectionMedia');
        $frm->addHiddenField('', 'scollection_id', $scollection_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $this->siteLangId), 'lang_id', $bannerTypeArr, '', array('class' => 'collection-language-js'), '');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->siteLangId), 'collection_image', array('accept' => 'image/*', 'data-frm' => 'frmCollectionMedia'));
        return $frm;
    }

    public function shopCollectionImages($scollection_id, $lang_id = 0)
    {
        $scollection_id = FatUtility::int($scollection_id);
        $lang_id = FatUtility::int($lang_id);
        $this->commonShopCollection();
        if (1 > $scollection_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $collectionImg = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, $lang_id, false);
        $this->set('images', $collectionImg);
        $this->set('languages', applicationConstants::bannerTypeArr());
        $this->set('scollection_id', $scollection_id);
        $this->set('lang_id', $lang_id);
        $this->_template->render(false, false);
    }

    public function uploadCollectionImage()
    {
        if (!$this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unauthorized_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $scollection_id = FatApp::getPostedData('scollection_id', FatUtility::VAR_INT, 0);

        if ($scollection_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->siteLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, $_FILES['cropped_image']['name'], -1, true, $lang_id)
        ) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('scollection_id', $scollection_id);
        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionImage($scollection_id, $lang_id = 0)
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $scollection_id = FatUtility::int($scollection_id);
        $lang_id = FatUtility::int($lang_id);

        $this->commonShopCollection();
        if (1 > $scollection_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $scollection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
