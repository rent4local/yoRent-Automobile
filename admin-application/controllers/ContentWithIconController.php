<?php

class ContentWithIconController extends AdminBaseController
{

    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewContentWithIconSection();
        $this->canEdit = $this->objPrivilege->canEditContentWithIconSection($this->admin_id, true);
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();

        switch ($action) {
            case 'importInstructions':
                $nodes[] = array('title' => Labels::getLabel('LBL_Import_instructions', $this->adminLangId));
                break;
            case 'index':
                $className = get_class($this);
                $arr = explode('-', FatUtility::camel2dashed($className));
                array_pop($arr);
                $urlController = implode('-', $arr);
                $className = ucwords(implode(' ', $arr));
                $nodes[] = array('title' => $className);
                break;
            default:
                $nodes[] = array('title' => $action);
                break;
        }
        return $nodes;
    }

    public function index()
    {
        $this->set("canEdit", $this->canEdit);
        $this->set('includeEditor', true);
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js', 'js/jquery-sortable-lists.js', 'js/tagify.min.js', 'js/tagify.polyfills.min.js'));
        $this->_template->addCss(array('css/cropper.css'));
        $this->_template->render();
    }

    public function search()
    {
        $srch = ContentBlockWithIcon::getSearchObject($this->adminLangId, false);
        $srch->addOrder('cbs_display_order', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $this->set("activeInactiveArr", $activeInactiveArr);
        $this->set("arr_listing", $records);
        $this->set("canView", true);
        $this->set("canEdit", $this->canEdit);
        $this->set("importInstructions", []);
        $this->_template->render(false, false);
    }

    public function form(int $blockId = 0)
    {
        $frm = $this->getForm();
        if (0 < $blockId) {
            $data = ContentBlockWithIcon::getAttributesById($blockId);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $frm->fill($data);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('blockId', $blockId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditContentWithIconSection();
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $blockId = $post['cbs_id'];
        unset($post['cbs_id']);
        $record = new ContentBlockWithIcon($blockId);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $blockId = $record->getMainTableRecordId();

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!ContentBlockWithIcon::getAttributesByLangId($langId, $blockId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('blockId', $blockId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm(int $blockId, int $langId, int $autoFillLangData = 0)
    {
        $epageData = ContentBlockWithIcon::getAttributesById($blockId);
        if (empty($epageData) || $langId == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $blockLangFrm = $this->getLangForm($blockId, $langId);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(ContentBlockWithIcon::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($blockId, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = ContentBlockWithIcon::getAttributesByLangId($langId, $blockId);
        }

        if ($langData) {
            $blockLangFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('blockId', $blockId);
        $this->set('langId', $langId);
        $this->set('blockLangFrm', $blockLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $blockId = FatApp::getPostedData('cbs_id', FatUtility::VAR_INT, 0);
        $langId = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if ($blockId == 0 || $langId == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getLangForm($blockId, $langId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJSONError(Message::getHtml());
        }

        unset($post['cbs_id']);
        unset($post['lang_id']);
        $data = array(
            'cbslang_lang_id' => $langId,
            'cbslang_cbs_id' => $blockId,
            'cbs_name' => $post['cbs_name'],
            'cbslang_description' => $post['cbslang_description'],
        );

        $obj = new ContentBlockWithIcon($blockId);
        if (!$obj->updateLangData($langId, $data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(ContentBlockWithIcon::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($blockId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = ContentBlockWithIcon::getAttributesByLangId($langId, $blockId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('blockId', $blockId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteBlock(int $blockId)
    {
        $cbsObj = new ContentBlockWithIcon($blockId);
        if (!$cbsObj->deleteRecord(true)) {
            Message::addErrorMessage($cbsObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $blockId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function imagesForm(int $blockId)
    {
        if (!$row = ContentBlockWithIcon::getAttributesById($blockId)) {
            Message::addErrorMessage($this->str_no_record);
            FatUtility::dieWithError(Message::getHtml());
        }
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);

        $imagesFrm = $this->getImagesFrm($blockId);
        $this->set('imagesFrm', $imagesFrm);
        $this->set('blockId', $blockId);
        $this->_template->render(false, false);
    }

    public function deleteIcon(int $blockId, int $fileId, int $langId = 0)
    {
        if (1 > $blockId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $blockId, $fileId, 0, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function images(int $blockId = 0, int $langId = 0, int $collectionId = -1)
    {
        /* if (!$row = ContentBlockWithIcon::getAttributesById($blockId)) {
            Message::addErrorMessage($this->str_no_record);
            FatUtility::dieWithError(Message::getHtml());
        } */
        $blockImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $blockId, $collectionId, $langId, false, 0, 0, true);
        $this->set('images', $blockImages);
        $this->set('blockId', $blockId);
        $this->set('languages', Language::getAllNames());
        $this->set('canEdit', $this->objPrivilege->canEditProducts(0, true));
        $this->_template->render(false, false);
    }

    public function uploadBlockImages()
    {
        $this->objPrivilege->canEditContentWithIconSection();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $collectionId = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $blockId = FatUtility::int($post['cbs_id']);
        $langId = FatUtility::int($post['lang_id']);
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $blockId, $collectionId, $_FILES['cropped_image']['name'], -1, true, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditContentBlocks();
        $blockId = FatApp::getPostedData('cbs_id', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('cbs_active', FatUtility::VAR_INT, 0);
        if (0 == $blockId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $cbObj = new ContentBlockWithIcon($blockId);
        if (!$cbObj->changeStatus($status)) {
            Message::addErrorMessage($cbObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditContentWithIconSection();
        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $obj = new ContentBlockWithIcon();
            if (!$obj->updateOrder($post['blocksList'])) {
                Message::addErrorMessage($obj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function autoComplete()
    {
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();

        $srch = ContentBlockWithIcon::getSearchObject($this->adminLangId, true);
        $srch->addMultipleFields(array('cbs_id', 'IFNULL(cbs_name, cbs_identifier) as cbs_name'));

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('cbs_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cond->attachCondition('cbs_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $collectionId = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $alreadyAdded = Collections::getRecords($collectionId);
        if (!empty($alreadyAdded) && 0 < count($alreadyAdded)) {
            $srch->addCondition('cbs_id', 'NOT IN', array_keys($alreadyAdded));
        }

        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $posts = $db->fetchAll($rs, 'cbs_id');
        $json = array();
        foreach ($posts as $key => $post) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($post['cbs_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getForm()
    {
        $this->objPrivilege->canViewContentBlocks();
        $frm = new Form('frmBlock');
        $frm->addHiddenField('', 'cbs_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Block_Identifier', $this->adminLangId), 'cbs_identifier');

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'cbs_active', $activeInactiveArr, '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getLangForm(int $blockId = 0, int $langId = 0)
    {
        $frm = new Form('frmBlockLang');
        $frm->addHiddenField('', 'cbs_id', $blockId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Block_Title', $this->adminLangId), 'cbs_name');
        $frm->addHtmlEditor(Labels::getLabel('LBL_Block_Content', $this->adminLangId), 'cbslang_description');
        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $langId == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    private function getImagesFrm(int $blockId)
    {
        $frm = new Form('imageFrm');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId)) + $languagesAssocArr, '', array(), '');
        $frm->addHiddenField('', 'min_width', 100);
        $frm->addHiddenField('', 'min_height', 100);
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'block_image', array('id' => 'block_image'));
        $frm->addHiddenField('', 'cbs_id', $blockId);
        return $frm;
    }

}
