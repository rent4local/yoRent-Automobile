<?php

class ContentPagesController extends AdminBaseController
{
    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewContentPages($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditContentPages($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->rewriteUrl = ContentPage::REWRITE_URL_PREFIX;
    }

    public function index()
    {
        $this->objPrivilege->canViewContentPages();
        $frmSearch = $this->getSearchForm();
        $this->_template->addCss('css/cropper.css');
        $this->_template->addJs('js/cropper.js');
        $this->_template->addJs('js/cropper-main.js');
        $this->set('includeEditor', true);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewContentPages();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $srch = ContentPage::getSearchObject($this->adminLangId);

        if (!empty($post['keyword'])) {
            $srch->addCondition('p.cpage_identifier', 'like', '%' . trim($post['keyword']) . '%');
        }

        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->addOrder('cpage_id', 'DESC');
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();

        $records = array();
        if ($rs) {
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function layouts()
    {
        $this->_template->render(false, false);
    }

    public function form($cpage_id = 0)
    {
        $this->objPrivilege->canViewContentPages();

        $cpage_id = FatUtility::int($cpage_id);
        $blockFrm = $this->getForm($cpage_id);

        if (0 < $cpage_id) {
            $data = ContentPage::getAttributesById($cpage_id, array('cpage_id', 'cpage_identifier', 'cpage_layout'));
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            /* url data[ */
            $urlRow = UrlRewrite::getDataByOriginalUrl($this->rewriteUrl . $cpage_id);
            if (!empty($urlRow)) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            /*]*/

            $blockFrm->fill($data);
            $this->set('cpage_layout', $data['cpage_layout']);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('cpage_id', $cpage_id);
        $this->set('blockFrm', $blockFrm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditContentPages();

        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cpage_id = $post['cpage_id'];
        unset($post['cpage_id']);
        $contentPage = new ContentPage($cpage_id);
        $contentPage->assignValues($post);

        if (!$contentPage->save()) {
            Message::addErrorMessage($contentPage->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cpage_id = $contentPage->getMainTableRecordId();

        /* url data[ */
        $originalUrl = $this->rewriteUrl . $cpage_id;
        if ($post['urlrewrite_custom'] == '') {
            UrlRewrite::remove($originalUrl);
        } else {
            $contentPage->rewriteUrl($post['urlrewrite_custom']);
        }
        /* ] */

        $newTabLangId = 0;
        if ($cpage_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = ContentPage::getAttributesByLangId($langId, $cpage_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $cpage_id = $contentPage->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('pageId', $cpage_id);
        $this->set('langId', $newTabLangId);
        $this->set('cpage_layout', $post['cpage_layout']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($cpage_id = 0, $lang_id = 0, $cpage_layout = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canViewContentPages();

        $cpage_id = FatUtility::int($cpage_id);
        $lang_id = FatUtility::int($lang_id);

        if ($cpage_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $blockLangFrm = $this->getLangForm($cpage_id, $lang_id, $cpage_layout);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(ContentPage::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($cpage_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = ContentPage::getAttributesByLangId($lang_id, $cpage_id);
        }

        if ($langData) {
            $srch = new searchBase(ContentPage::DB_TBL_CONTENT_PAGES_BLOCK_LANG);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array("cpblocklang_text", 'cpblocklang_block_id'));
            $srch->addCondition('cpblocklang_cpage_id', '=', $cpage_id);

            if (0 < $autoFillLangData) {
                $srch->addCondition('cpblocklang_lang_id', '=', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
            } else {
                $srch->addCondition('cpblocklang_lang_id', '=', $lang_id);
            }

            $srchRs = $srch->getResultSet();
            $blockData = FatApp::getDb()->fetchAll($srchRs, 'cpblocklang_block_id');
            foreach ($blockData as $blockKey => $blockContent) {
                if (0 < $autoFillLangData) {
                    $blockContent = $updateLangDataobj->directTranslate(['cpblocklang_text' => $blockContent['cpblocklang_text']], $lang_id);
                    if (false === $blockContent) {
                        Message::addErrorMessage($updateLangDataobj->getError());
                        FatUtility::dieWithError(Message::getHtml());
                    }
                    $blockContent = current($blockContent);
                }
                $langData['cpblock_content_block_' . $blockKey] = $blockContent['cpblocklang_text'];
            }
            $blockLangFrm->fill($langData);
        }
        $bgImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE, $cpage_id, 0, $lang_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $this->set('bgImages', $bgImages);
        $this->set('bannerTypeArr', $bannerTypeArr);
        $this->set('languages', Language::getAllNames());
        $this->set('cpage_id', $cpage_id);
        $this->set('cpage_lang_id', $lang_id);
        $this->set('cpage_layout', $cpage_layout);
        $this->set('blockLangFrm', $blockLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditContentPages();
        $post = FatApp::getPostedData();
        /* CommonHelper::printArray($post); die; */
        $cpage_id = $post['cpage_id'];
        $lang_id = $post['lang_id'];
        $cpage_layout = $post['cpage_layout'];

        if ($cpage_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* $frm = $this->getLangForm( $cpage_id , $lang_id );
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        */
        unset($post['cpage_id']);
        unset($post['lang_id']);
        $data = array(
        'cpagelang_lang_id' => $lang_id,
        'cpagelang_cpage_id' => $cpage_id,
        'cpage_title' => $post['cpage_title'],

        );

        if ($cpage_layout == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE) {
            $data['cpage_image_title'] = $post['cpage_image_title'];
            $data['cpage_image_content'] = $post['cpage_image_content'];
        } else {
            $data['cpage_content'] = $post['cpage_content'];
        }

        $pageObj = new ContentPage($cpage_id);
        if (!$pageObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($pageObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $cpage_id = $pageObj->getMainTableRecordId();
        if (!$cpage_id) {
            $cpage_id = FatApp::getDb()->getInsertId();
        }
        $pageObj = new ContentPage($cpage_id);
        if ($cpage_layout == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE) {
            for ($i = 1; $i <= ContentPage::CONTENT_PAGE_LAYOUT1_BLOCK_COUNT; $i++) {
                $data['cpblocklang_text'] = $post['cpblock_content_block_' . $i];
                $data['cpblocklang_block_id'] = $i;
                if (!$pageObj->addUpdateContentPageBlocks($lang_id, $cpage_id, $data)) {
                    Message::addErrorMessage($pageObj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(ContentPage::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($cpage_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = ContentPage::getAttributesByLangId($langId, $cpage_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('pageId', $cpage_id);
        $this->set('langId', $newTabLangId);
        $this->set('cpage_layout', $cpage_layout);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditContentPages();

        $cpage_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($cpage_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($cpage_id);

        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditContentPages();
        $cpageIdsArr = FatUtility::int(FatApp::getPostedData('cpage_ids'));

        if (empty($cpageIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($cpageIdsArr as $cpage_id) {
            if (1 > $cpage_id) {
                continue;
            }
            $this->markAsDeleted($cpage_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($cpage_id)
    {
        $cpage_id = FatUtility::int($cpage_id);
        if (1 > $cpage_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $obj = new ContentPage($cpage_id);
        if (!$obj->canRecordMarkDelete($cpage_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj->assignValues(array(ContentPage::tblFld('deleted') => 1));
        if (!$obj->save()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function autoComplete()
    {
        $this->objPrivilege->canViewContentPages();

        $srch = ContentPage::getSearchObject($this->adminLangId);

        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $srch->addCondition('cpage_title', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        $srch->addMultipleFields(array('cpage_id', 'IFNULL(cpage_title,cpage_identifier) as cpage_name'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs, 'cpage_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
            'id' => $key,
            'name' => strip_tags(html_entity_decode($product['cpage_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getSearchForm()
    {
        $frm = new Form('frmPagesSearch');
        $f1 = $frm->addTextBox(Labels::getLabel('LBL_Page_Identifier', $this->adminLangId), 'keyword', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getForm($cpage_id = 0)
    {
        $this->objPrivilege->canViewContentPages();
        $cpage_id = FatUtility::int($cpage_id);

        $frm = new Form('frmBlock');
        $frm->addHiddenField('', 'cpage_id', 0);
        $frm->addRequiredField(Labels::getLabel('LBL_Page_Identifier', $this->adminLangId), 'cpage_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SEO_Friendly_URL', $this->adminLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Layout_Type', $this->adminLangId), 'cpage_layout', $this->getAvailableLayouts(), '', array('id' => 'cpage_layout'))->requirements()->setRequired();


        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getAvailableLayouts()
    {
        $collectionLayouts = array(
        ContentPage::CONTENT_PAGE_LAYOUT1_TYPE => Labels::getLabel('LBL_Content_Page_Layout1', $this->adminLangId),
        ContentPage::CONTENT_PAGE_LAYOUT2_TYPE => Labels::getLabel('LBL_Content_Page_Layout2', $this->adminLangId),
        );
        return $collectionLayouts;
    }

    private function getLangForm($cpage_id = 0, $lang_id = 0, $cpage_layout = 0)
    {
        $frm = new Form('frmBlockLang');
        $frm->addHiddenField('', 'cpage_id', $cpage_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addHiddenField('', 'cpage_layout', $cpage_layout);
        $frm->addRequiredField(Labels::getLabel('LBL_Page_Title', $this->adminLangId), 'cpage_title');
        if ($cpage_layout == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE) {
            $bannerTypeArr = applicationConstants::bannerTypeArr();
            /*$frm->addButton(
                Labels::getLabel('LBL_BACKGROUND_IMAGE', $this->adminLangId),
                'cpage_bg_image',
                Labels::getLabel('LBL_Upload_Image', $this->adminLangId),
                array('class' => 'bgImageFile-Js', 'id' => 'cpage_bg_image', 'data-file_type' => AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE, 'data-frm' => 'frmBlock')
            );*/
            $frm->addFileUpload(Labels::getLabel('LBL_BACKGROUND_IMAGE', $this->adminLangId), 'cpage_bg_image', array('accept' => 'image/*', 'data-frm' => 'frmBlockLang'));
            $frm->addTextBox(Labels::getLabel('LBL_Background_Image_Title', $this->adminLangId), 'cpage_image_title');
            $frm->addTextarea(Labels::getLabel('LBL_Background_Image_Description', $this->adminLangId), 'cpage_image_content');
            for ($i = 1; $i <= ContentPage::CONTENT_PAGE_LAYOUT1_BLOCK_COUNT; $i++) {
                $frm->addHtmlEditor(Labels::getLabel('LBL_Content_Block_' . $i, $this->adminLangId), 'cpblock_content_block_' . $i);
            }
            $frm->addHiddenField('', 'min_width', 1300);
            $frm->addHiddenField('', 'min_height', 400);
            $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE);
        } else {
            $frm->addHtmlEditor(Labels::getLabel('LBL_Page_Content', $this->adminLangId), 'cpage_content');
        }

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }

    public function setUpBgImage()
    {
        $post = FatApp::getPostedData();
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $cpage_id = FatApp::getPostedData('cpage_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $cpage_layout = FatApp::getPostedData('cpage_layout', FatUtility::VAR_INT, 0);
        if (!$file_type || !$cpage_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
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

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], $file_type, $cpage_id, 0, $_FILES['cropped_image']['name'], -1,
            $unique_record = true, $lang_id, $_FILES['cropped_image']['type'])) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
            // FatUtility::dieJsonError($fileHandlerObj->getError());
        }

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('cpage_id', $cpage_id);
        $this->set('cpage_layout', $cpage_layout);
        $this->set('lang_id', $lang_id);
        $this->set('msg', $_FILES['cropped_image']['name'] . ' ' . Labels::getLabel('LBL_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBgImage($cpage_id = 0, $langId = 0)
    {
        $cpage_id = FatUtility::int($cpage_id);
        $langId = FatUtility::int($langId);
        if (!$cpage_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE, $cpage_id, 0, 0, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function cmsLayout($layoutId)
    {
        $layoutId = FatUtility::int($layoutId);
        if (1 > $layoutId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('layoutId', $layoutId);
        $this->_template->render(false, false);
    }
}
