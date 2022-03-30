<?php

use Braintree\Collection;

class CollectionsController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCollections($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCollections($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCollections();
        $this->_template->addCss('css/cropper.css');
        $this->_template->addJs('js/cropper.js');
        $this->_template->addJs('js/cropper-main.js');
        $search = $this->getSearchForm();
        $this->set("search", $search);
        $typeLayouts = Collections::getTypeSpecificLayouts($this->adminLangId);
		$activeTheme = applicationConstants::getActiveTheme();
		$activeThemeLayoutsArr = (isset(Collections::layoutArrByTheme()[$activeTheme])) ? Collections::layoutArrByTheme()[$activeTheme] : [];
		$this->set('typeLayouts', $typeLayouts);
        $this->set('activeThemeLayouts', array_keys($activeThemeLayoutsArr));
        $this->set('activeThemeLayoutsArr', $activeThemeLayoutsArr);
        $this->set('includeEditor', true);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');

        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'collection_type', Collections::getTypeArr($this->adminLangId));
        $frm->addSelectBox(Labels::getLabel('LBL_Layout_Type', $this->adminLangId), 'collection_layout_type', array(-1 => Labels::getLabel('LBL_Does_Not_matter', $this->adminLangId)) + Collections::getLayoutTypeArr($this->adminLangId), '', array(), '');

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function search()
    {
		$activeTheme = applicationConstants::getActiveTheme();
		$activeThemeLayoutsArr = (isset(Collections::layoutArrByTheme()[$activeTheme])) ? Collections::layoutArrByTheme()[$activeTheme] : [];
        $activeThemeLayouts = array_keys($activeThemeLayoutsArr);
		
        $typeLayouts = Collections::getTypeSpecificLayouts($this->adminLangId);
        $layoutNameArr = [];
        
        foreach ($typeLayouts as $type => $layouts) {
            $index = 1;
            foreach ($layouts as $layoutType => $layout) { 
                if (!in_array($layoutType, $activeThemeLayouts)) {
                    continue;
                }
                $layoutNameArr[$layoutType] = $layout. ' '. $index;
                $index++;
            }
        }
        
        $this->objPrivilege->canViewCollections();
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();

        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $post = $searchForm->getFormDataFromArray($data);
        $records = [];
        if (!empty($activeThemeLayouts)) {
            $srch = Collections::getSearchObject(false, $this->adminLangId);
            $srch->addCondition('collection_parent_id', '=', 0);
            $srch->addCondition('collection_layout_type', 'IN', $activeThemeLayouts);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();

            if (!empty($post['keyword'])) {
                $keyword = trim($post['keyword']);
                $condition = $srch->addCondition('c.collection_identifier', 'like', '%' . $keyword . '%');
                $condition->attachCondition('c_l.collection_name', 'like', '%' . $keyword . '%', 'OR');
            }

            $collection_type = FatApp::getPostedData('collection_type', FatUtility::VAR_INT, '');
            if ($collection_type) {
                $srch->addCondition('collection_type', '=', $collection_type);
            }

            $srch->addOrder('collection_active', 'DESC');
            $collection_layout_type = FatApp::getPostedData('collection_layout_type', FatUtility::VAR_INT, '');
            if ($collection_layout_type > 0) {
                $srch->addCondition('collection_layout_type', '=', $collection_layout_type);
            }
            $srch->addOrder('collection_display_order', 'ASC');
            $srch->addMultipleFields(array('c.*', 'c_l.collection_name'));

            $rs = $srch->getResultSet();
            $records = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set("arr_listing", $records);
        $this->set('page', $page);
        $this->set('layoutNameArr', $layoutNameArr);
        $this->set('collection_layout_type', FatApp::getPostedData('collection_layout_type', FatUtility::VAR_INT, ''));
        $this->_template->render(false, false);
    }

    public function form(int $type, int $layoutType, int $collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = $this->getForm($type, $layoutType, $collectionId);
        if (0 < $collectionId) {
            $data = Collections::getAttributesById($collectionId);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $langData = Collections::getLangDataArr($collectionId, array(Collections::DB_TBL_LANG_PREFIX . 'lang_id', Collections::DB_TBL_PREFIX . 'name', Collections::DB_TBL_PREFIX . 'description', Collections::DB_TBL_PREFIX . 'text'));
            $catNameArr = array();
            foreach ($langData as $value) {
                $catNameArr[Collections::DB_TBL_PREFIX . 'name'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'name'];
                $catNameArr[Collections::DB_TBL_PREFIX . 'description'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'description'];
                $catNameArr[Collections::DB_TBL_PREFIX . 'text'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'text'];
            }
            $data = array_merge($data, $catNameArr);
            if ($type == Collections::COLLECTION_TYPE_BANNER) {
                $bannerLocation = BannerLocation::getDataByCollectionId($collectionId, ['blocation_promotion_cost', 'blocation_promotion_cost_second']);
                $data['blocation_promotion_cost'] = (isset($bannerLocation['blocation_promotion_cost'])) ? $bannerLocation['blocation_promotion_cost'] : '';
                $data['blocation_promotion_cost_second'] = (isset($bannerLocation['blocation_promotion_cost_second'])) ? $bannerLocation['blocation_promotion_cost_second'] : '';
            }
            $frm->fill($data);
        }

        if ($layoutType == Collections::TYPE_PRODUCT_LAYOUT5) {
            $this->set('subCollectionsList', $this->getSubcategoriesList($collectionId));
        }

        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $this->set('otherLangData', $langData);
        $this->set('languages', Language::getAllNames());
        $this->set('collection_id', $collectionId);
        $this->set('collection_type', $type);
        $this->set('collection_layout_type', $layoutType);
        $this->set('frm', $frm);
		$this->set('recordLimit', $this->getLayoutLimit($layoutType));
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->_template->render(false, false);
    }

    private function getSubcategoriesList(int $collectionId): array
    {
        $srch = Collections::getSearchObject(false, $this->adminLangId);
        $srch->addCondition('collection_type', '=', Collections::COLLECTION_TYPE_SUB_COLLECTION);
        $srch->addCondition('collection_parent_id', '=', $collectionId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('collection_id', 'ASC');
        $srch->addMultipleFields(array('collection_id', 'IFNULL(c_l.collection_name, collection_identifier) as collection_name'));
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function subCollectionForm(int $collectionId, int $subCollectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = $this->getForm(Collections::COLLECTION_TYPE_SUB_COLLECTION, Collections::TYPE_SUB_COLLECTION_LAYOUT1, $subCollectionId);
        $data = ['collection_parent_id' => $collectionId];

        if (0 < $subCollectionId) {
            $data = Collections::getAttributesById($subCollectionId);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $langData = Collections::getLangDataArr($subCollectionId, array(Collections::DB_TBL_LANG_PREFIX . 'lang_id', Collections::DB_TBL_PREFIX . 'name', Collections::DB_TBL_PREFIX . 'description', Collections::DB_TBL_PREFIX . 'text'));
            $catNameArr = array();
            foreach ($langData as $value) {
                $catNameArr[Collections::DB_TBL_PREFIX . 'name'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'name'];
                $catNameArr[Collections::DB_TBL_PREFIX . 'description'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'description'];
                $catNameArr[Collections::DB_TBL_PREFIX . 'text'][$value[Collections::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Collections::DB_TBL_PREFIX . 'text'];
            }
            $data = array_merge($data, $catNameArr);
        }
        $frm->fill($data);
        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $this->set('subCollectionsList', $this->getSubcategoriesList($collectionId));
        $this->set('otherLangData', $langData);
        $this->set('languages', Language::getAllNames());
        $this->set('collection_id', $subCollectionId);
        $this->set('collection_parent_id', $collectionId);
        $this->set('collection_type', Collections::COLLECTION_TYPE_SUB_COLLECTION);
        $this->set('collection_layout_type', Collections::TYPE_SUB_COLLECTION_LAYOUT1);
        $this->set('collection_parent_type', Collections::COLLECTION_TYPE_PRODUCT);
        $this->set('collection_parent_type_layout', Collections::TYPE_PRODUCT_LAYOUT5);
		$this->set('recordLimit', $this->getLayoutLimit(Collections::TYPE_PRODUCT_LAYOUT5));
		$this->set('childRecordLimit', $this->getLayoutLimit(Collections::TYPE_SUB_COLLECTION_LAYOUT1));
        $this->set('frm', $frm);
        $this->set('recordFrm', $this->getRecordsForm($collectionId, Collections::COLLECTION_TYPE_SUB_COLLECTION));
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function translatedData()
    {
        $collectionName = FatApp::getPostedData('collectionName', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $description = FatApp::getPostedData('collectionDescription', FatUtility::VAR_STRING, '');
        $collectionText = FatApp::getPostedData('collectionText', FatUtility::VAR_STRING, '');
        $data['collection_name'] = $collectionName;
        $data['collection_description'] = $description;
        $data['collection_text'] = $collectionText;
        $productCategory = new ProductCategory();
        $translatedData = $productCategory->getTranslatedCategoryData($data, $toLangId);

        if (!$translatedData) {
            Message::addErrorMessage($productCategory->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!empty(FatApp::getPostedData('epageContent', FatUtility::VAR_STRING, ''))) {
            $data['epage_label'] = $collectionName;
            $data['epage_content'] = FatApp::getPostedData('epageContent', FatUtility::VAR_STRING, '');
            $extrapage = new Extrapage();
            $translatedContent = $extrapage->getTranslatedData($data, $toLangId);
            if (!$translatedContent) {
                Message::addErrorMessage($extrapage->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $this->set('epageContent', $translatedContent[$toLangId]['epage_content']);
        }

        $this->set('collectionName', $translatedData[$toLangId]['collection_name']);
        $this->set('collectionDescription', $translatedData[$toLangId]['collection_description']);
        $this->set('collectionText', $translatedData[$toLangId]['collection_text']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setup()
    {
        $this->objPrivilege->canEditCollections();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $attachedRecords = FatApp::getPostedData('record_ids', FatUtility::VAR_STRING, '');
        $data = FatApp::getPostedData();
        $frm = $this->getForm($data['collection_type'], $data['collection_layout_type']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionId = $post['collection_id'];

        unset($post['collection_id']);
        unset($post['btn_submit']);

        $post['collection_identifier'] = $post['collection_name'][$siteDefaultLangId];
        $post['collection_primary_records'] = $this->getLayoutLimit($post['collection_layout_type']);

        if ($collectionId == 0) {
            $record = Collections::getAttributesByIdentifier($post['collection_identifier']);
            if (!empty($record) && $record['collection_deleted'] == applicationConstants::YES) {
                $collectionId = $record['collection_id'];
                $post['collection_deleted'] = applicationConstants::NO;
            }
        }
        $collectionForApp = isset($post['collection_for_app']) ? $post['collection_for_app'] : 0;
        $post['collection_for_app'] = in_array($data['collection_layout_type'], Collections::APP_COLLECTIONS_ONLY) ? 1 : $collectionForApp;
        $collection = new Collections($collectionId);
        $collection->assignValues($post);
        if (!$collection->save()) {
            Message::addErrorMessage($collection->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionId = $collection->getMainTableRecordId();
        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);

        $collection = new Collections($collectionId);
        $desc = (isset($post['collection_description'][$siteDefaultLangId])) ? $post['collection_description'][$siteDefaultLangId] : "";
        $colText = (isset($post['collection_text'][$siteDefaultLangId])) ? $post['collection_text'][$siteDefaultLangId] : "";
        $collection->saveLangData($siteDefaultLangId, $post['collection_name'][$siteDefaultLangId], $desc, $colText); // For site default language
        $nameArr = $post['collection_name'];
        unset($nameArr[$siteDefaultLangId]);

        foreach ($nameArr as $langId => $catName) {
            $desc = (isset($post['collection_description'][$langId])) ? $post['collection_description'][$langId] : "";
            $colText = (isset($post['collection_text'][$langId])) ? $post['collection_text'][$langId] : "";
            if (empty($catName) && $autoUpdateOtherLangsData > 0) {
                $collection->saveTranslatedLangData($langId);
            } elseif (!empty($catName)) {
                $collection->saveLangData($langId, $catName, $desc, $colText);
            }
        }
        $post['collection_id'] = $collectionId;
        if ($post['collection_type'] == Collections::COLLECTION_TYPE_BANNER) {
            $this->saveBannerLocation($post);
            $this->set('openBannersForm', true);
        }

        if (!in_array($post['collection_type'], Collections::COLLECTION_WITHOUT_RECORDS)) {
            $this->set('openRecordForm', true);
        }

        if (in_array($post['collection_layout_type'], Collections::LAYOUT_WITH_MEDIA)) {
            $this->set('openMediaForm', true);
        }
        if ($post['collection_type'] == Collections::COLLECTION_TYPE_SUB_COLLECTION) {
            $attachedRecords = array_unique(explode(',', $attachedRecords));
            if (!empty($attachedRecords)) {
                $collectionObj = new Collections($collectionId);
                foreach ($attachedRecords as $recordId) {
                    if (1 > $recordId) {
                        continue;
                    }
                    if (!$collectionObj->addUpdateCollectionRecord($collectionId, $recordId)) {
                        Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
            $this->set('openTabForm', true);
            $this->set('parentCollectionId', $post['collection_parent_id']);
        }
        if ($post['collection_layout_type'] == Collections::TYPE_PRODUCT_LAYOUT5) {
            $this->set('openTabForm', true);
            $this->set('parentCollectionId', $collectionId);
            $this->set('isParent', true);
        }
        if ($post['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT4) {
            $this->set('openRecordForm', false);
        }
		
		if ($post['collection_type'] == Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON) {
            $this->set('openContentForm', true);
			$this->set('openRecordForm', false);
        }
		

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('collectionId', $collectionId);
        $this->set('collectionType', $post['collection_type']);
        $this->set('collectionLayoutType', $post['collection_layout_type']);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function saveBannerLocation($post)
    {
        $layoutType = $post['collection_layout_type'];
        $activeTheme = applicationConstants::getActiveTheme();
        $bannerSizeArr = imagesSizes::getBannersDimensions();
        $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
        $bannerDimensions = $bannerSizeArr[$layoutType];

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $blocationId = 0;
        $bannerLocation = BannerLocation::getDataByCollectionId($post['collection_id'], 'blocation_id');
        if (!empty($bannerLocation)) {
            $blocationId = $bannerLocation['blocation_id'];
        }
        $dataToSave = [
            'blocation_identifier' => $post['collection_name'][$siteDefaultLangId],
            'blocation_collection_id' => $post['collection_id'],
            'blocation_banner_count' => Collections::getBannersCount()[$post['collection_layout_type']],
            'blocation_promotion_cost' => $post['blocation_promotion_cost'],
            'blocation_active' => applicationConstants::ACTIVE,
            'blocation_promotion_cost_second' => (isset($post['blocation_promotion_cost_second'])) ? $post['blocation_promotion_cost_second'] : 0,
        ];
        $bannerLoc = new BannerLocation($blocationId);
        $bannerLoc->assignValues($dataToSave);
        if (!$bannerLoc->save()) {
            Message::addErrorMessage($bannerLoc->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $blocationId = $bannerLoc->getMainTableRecordId();
        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);

        $bannerLoc = new BannerLocation($blocationId);
        $bannerLoc->saveLangData($siteDefaultLangId, $post['collection_name'][$siteDefaultLangId]); // For site default language
        $nameArr = $post['collection_name'];
        unset($nameArr[$siteDefaultLangId]);
        foreach ($nameArr as $langId => $name) {
            if (empty($name) && $autoUpdateOtherLangsData > 0) {
                $bannerLoc->saveTranslatedLangData($langId);
            } elseif (!empty($name)) {
                $bannerLoc->saveLangData($langId, $name);
            }
        }

        if ($post['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) {
            $bannerDimensions = $bannerDimensions[Collections::BANNER_POSITION_LEFT];
        }

        foreach ($bannerDimensions as $key => $val) {
            $dataToSave = [
                'bldimension_blocation_id' => $blocationId,
                'bldimension_device_type' => $key,
                'blocation_banner_width' => $val['width'],
                'blocation_banner_height' => $val['height']
            ];
            if (!FatApp::getDb()->insertFromArray(BannerLocation::DB_DIMENSIONS_TBL, $dataToSave, false, array(), $dataToSave)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_save_banner_dimensions', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
    }

    private function getForm(int $type, int $layoutType, int $collectionId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = new Form('frmCollection');
        $titleCharacter = 255;
        if ($type == Collections::COLLECTION_TYPE_SUB_COLLECTION) {
            $titleCharacter = 20;
        }
        
        $nameLbl = ($type != Collections::COLLECTION_TYPE_SUB_COLLECTION) ? Labels::getLabel('LBL_Collection_Name', $this->adminLangId) : Labels::getLabel('LBL_Tab_Name', $this->adminLangId);

        $frm->addRequiredField($nameLbl, 'collection_name[' . $siteDefaultLangId . ']', '', array('maxlength' => $titleCharacter));
        
        $activeTheme = applicationConstants::getActiveTheme();
        if ($type != Collections::COLLECTION_TYPE_SUB_COLLECTION) {
            $frm->addTextBox(Labels::getLabel('LBL_Collection_Subheading', $this->adminLangId), 'collection_description[' . $siteDefaultLangId . ']');
        }

        if( $activeTheme == applicationConstants::THEME_FASHION  && $type != Collections::TYPE_PRODUCT_LAYOUT5) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_View_more_button_link', $this->adminLangId), 'collection_link_url');
            $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_Please_add_full_url_eg:-_https://v2.demo.yo-rent.com/', $this->adminLangId) . ' </small>';
        }
        
        if ($type == Collections::COLLECTION_TYPE_BANNER) {
            $costFldLbl = Labels::getLabel('LBL_Promotion_Cost', $this->adminLangId);
            if ($layoutType == Collections::TYPE_BANNER_LAYOUT4) {
                $frm->addTextBox(Labels::getLabel('LBL_Promotion_Right_Banner_Cost', $this->adminLangId), 'blocation_promotion_cost_second');
                $costFldLbl = Labels::getLabel('LBL_Promotion_Left_Banner_Cost', $this->adminLangId);
            }
            $frm->addTextBox(Labels::getLabel($costFldLbl, $this->adminLangId), 'blocation_promotion_cost');
        }

        if (!in_array($layoutType, Collections::APP_COLLECTIONS_ONLY)) {
            $frm->addHiddenField(Labels::getLabel("LBL_APPLICABLE_FOR_WEB", $this->adminLangId), 'collection_for_web', 1, array(), true, 0);
        }
        if ($activeTheme == applicationConstants::THEME_FASHION  && $layoutType == Collections::TYPE_PRODUCT_LAYOUT5) {
            $frm->addTextArea(Labels::getLabel("LBL_Content", $this->adminLangId), 'collection_text[' . $siteDefaultLangId . ']','',array('maxlength' => 162));
        }

        $frm->addHiddenField(Labels::getLabel("LBL_APPLICABLE_FOR_APP", $this->adminLangId), 'collection_for_app', 1, array(), true, 0);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($langData) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        foreach ($langData as $langId => $data) {
            $frm->addTextBox($nameLbl, 'collection_name[' . $langId . ']', '', array('maxlength' => $titleCharacter));
            if ($type != Collections::COLLECTION_TYPE_SUB_COLLECTION) {
                $frm->addTextBox(Labels::getLabel('LBL_Collection_Subheading', $this->adminLangId), 'collection_description[' . $langId . ']');
            }
            if ($activeTheme == applicationConstants::THEME_FASHION  && $layoutType == Collections::TYPE_PRODUCT_LAYOUT5) {
                $frm->addTextArea(Labels::getLabel("LBL_Content", $this->adminLangId), 'collection_text[' . $langId . ']');
            }
        }

        $frm->addHiddenField('', 'record_ids', '');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addHiddenField('', 'collection_active', applicationConstants::ACTIVE);
        $frm->addHiddenField('', 'collection_type', $type);
        $frm->addHiddenField('', 'collection_layout_type', $layoutType);
        $frm->addHiddenField('', 'collection_parent_id', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditCollections();
        $collectionId = FatApp::getPostedData('collectionId', FatUtility::VAR_INT, 0);
        if (0 >= $collectionId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = Collections::getAttributesById($collectionId, array('collection_id', 'collection_active'));

        if ($data == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $status = ($data['collection_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;
        $this->updateCollectionStatus($collectionId, $status);
        FatUtility::dieJsonSuccess($this->str_update_record);
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditCollections();
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $collectionIdsArr = FatUtility::int(FatApp::getPostedData('collection_ids'));
        if (empty($collectionIdsArr) || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($collectionIdsArr as $collectionId) {
            if (1 > $collectionId) {
                continue;
            }
            $this->updateCollectionStatus($collectionId, $status);
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateCollectionStatus($collectionId, $status)
    {
        $status = FatUtility::int($status);
        $collectionId = FatUtility::int($collectionId);
        if (1 > $collectionId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $collectionObj = new Collections($collectionId);
        if (!$collectionObj->changeStatus($status)) {
            Message::addErrorMessage($collectionObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function updateCollectionRecords()
    {
        $this->objPrivilege->canEditCollections();
        $displayOrder = FatApp::getPostedData('displayOrder', FatUtility::VAR_INT, 0);
        $post = FatApp::getPostedData();

        //echo "<pre>"; print_r($post); print_r($_FILES); echo "</pre>"; die();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collection_id = FatUtility::int($post['collection_id']);
        $record_id = FatUtility::int($post['record_id']);
        if (!$collection_id || !$record_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionDetails = Collections::getAttributesById($collection_id);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
		$recordLimit = $this->getLayoutLimit($collectionDetails['collection_layout_type']);
        if (($collectionDetails['collection_layout_type'] == Collections::TYPE_SUB_COLLECTION_LAYOUT1 || $collectionDetails['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT3 || $collectionDetails['collection_type'] == Collections::COLLECTION_TYPE_BLOG || $collectionDetails['collection_layout_type'] == Collections::TYPE_PRODUCT_LAYOUT3 ) && count(Collections::getRecords($collection_id)) >= $recordLimit) {
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_allowed_record_for_this_layout_is', $this->adminLangId) . ' ' . $recordLimit);
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionObj = new Collections($collection_id);
        /* [ DELETE OLD ATTACHED CATEGORY FOR GIVER ORDER */
        if ($collectionDetails['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT4) {
            $whr = array('smt' => 'ctr_collection_id = ? and ctr_display_order = ?', 'vals' => array($collection_id, $displayOrder));
            FatApp::getDb()->deleteRecords(Collections::DB_TBL_COLLECTION_TO_RECORDS, $whr);
        }
        /* ] */

        if (!$collectionObj->addUpdateCollectionRecord($collection_id, $record_id, $displayOrder)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
       
        /* attach file with request [ */
        if (isset($_FILES['cropped_image'])) {
             if (is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
                if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) {
                    $message = Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB';
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($message);
                    }
                    Message::addErrorMessage($message);
                    FatUtility::dieJsonError(Message::getHtml());
                }
                
                /* if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  {
                    Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
                    FatUtility::dieJsonError(Message::getHtml());
                } */

                $fileHandlerObj = new AttachedFile();
                if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_BRAND_FEATURED_IMAGE, $record_id, 0, $_FILES['cropped_image']['name'], -1, true)) {
                    if (true === MOBILE_APP_API_CALL) {
                        LibHelper::dieJsonError($fileHandlerObj->getError());
                    }
                    Message::addErrorMessage($fileHandlerObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            } 
        }
        /* ] */

        $this->set('adminLangId', $this->adminLangId);
        $this->set('collection_id', $collection_id);
        $this->set('collection_type', $collectionDetails['collection_type']);
        $this->set('msg', Labels::getLabel('MSG_Record_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCollectionRecord()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionId = FatUtility::int($post['collection_id']);
        $recordId = FatUtility::int($post['record_id']);
        if (1 > $collectionId || 1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $collectionObj = new Collections();
        if (!$collectionObj->removeCollectionRecord($collectionId, $recordId)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Removed_Successfully', $this->adminLangId));
        $this->set('collection_type', $collectionDetails['collection_type']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function collectionRecords($collectionId, $collectionType)
    {
        $this->objPrivilege->canViewCollections();
        $collectionId = FatUtility::int($collectionId);
        if ($collectionId == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        switch ($collectionType) {
            case Collections::COLLECTION_TYPE_PRODUCT:
            case Collections::COLLECTION_TYPE_SUB_COLLECTION:
                $records = Collections::getSellProds($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_CATEGORY:
                $records = Collections::getCategories($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_SHOP:
                $records = Collections::getShops($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_BRAND:
                $records = Collections::getBrands($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_BLOG:
                $records = Collections::getBlogs($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_FAQ:
                $records = Collections::getFaqs($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_TESTIMONIAL:
                $records = Collections::getTestimonials($collectionId, $this->adminLangId);
                break;
            case Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON:
                $records = Collections::getContentBlocks($collectionId, $this->adminLangId);
                break;
        }

        $this->set('collectionId', $collectionId);
        $this->set('collectionType', $collectionType);
        $this->set('collectionRecords', $records);
        $this->_template->render(false, false);
    }

    public function recordForm(int $collectionId, int $collectionType)
    {
        $this->objPrivilege->canViewCollections();
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $imgSizeArr = imagesSizes::brandFeaturedImageSizeArr()[applicationConstants::getActiveTheme()];
        $frm = $this->getRecordsForm($collectionId, $collectionType);
        $this->set('imgSizeArr', $imgSizeArr);
        $this->set('collection_id', $collectionId);
        $this->set('collection_type', $collectionType);
        $this->set('collection_layout_type', $collectionDetails['collection_layout_type']);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function banners(int $collectionId)
    {
        $this->objPrivilege->canViewBanners();
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('collection_id', $collectionId);
        $this->set('collection_type', $collectionDetails['collection_type']);
        $this->set('collection_layout_type', $collectionDetails['collection_layout_type']);
        $this->_template->render(false, false);
    }

    public function searchBanners(int $collectionId)
    {
        $this->objPrivilege->canViewBanners();
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $records = Collections::getBanners($collectionId, $this->adminLangId);
        $this->set('collection_id', $collectionId);
        $this->set('arr_listing', $records);
        $this->set('bannerTypeArr', Banner::getBannerTypesArr($this->adminLangId));
        $this->set('linkTargetsArr', applicationConstants::getLinkTargetsArr($this->adminLangId));
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function bannerForm(int $collectionId, int $bannerId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $bannerLocation = BannerLocation::getDataByCollectionId($collectionId);
        $blocationId = $bannerLocation['blocation_id'];
        $frm = $this->getBannerForm($collectionId, $bannerId, $blocationId);

        if (0 < $bannerId) {
            $srch = new BannerSearch($this->adminLangId, false);
            $srch->joinCollectionToRecords();
            $srch->joinLocations();
            $srch->joinPromotions($this->adminLangId, true);
            $srch->addPromotionTypeCondition();
            $srch->addMultipleFields(array('IFNULL(promotion_name,promotion_identifier) as promotion_name', 'banner_id', 'banner_type', 'banner_url', 'banner_target', 'banner_active', 'banner_blocation_id', 'banner_title', 'banner_updated_on', 'banner_position'));
            $srch->addCondition('banner_id', '=', $bannerId);
            $srch->addOrder('banner_active', 'DESC');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $langData = Banner::getLangDataArr($bannerId, array(Banner::DB_TBL_LANG_PREFIX . 'lang_id', Banner::DB_TBL_PREFIX . 'title'));
            $bannerTitleArr = array();
            foreach ($langData as $value) {
                $bannerTitleArr[Banner::DB_TBL_PREFIX . 'title'][$value[Banner::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[Banner::DB_TBL_PREFIX . 'title'];
            }

            $data = array_merge($data, $bannerTitleArr);
            $frm->fill($data);
        }

        $mediaLanguages = applicationConstants::bannerTypeArr();
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $activeTheme = applicationConstants::getActiveTheme();
        $bannerSizeArr = imagesSizes::getBannersDimensions();
        $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
        $bannerSizeArr = $bannerSizeArr[$collectionDetails['collection_layout_type']];

        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        $this->set('otherLangData', $langData);
        $this->set('languages', Language::getAllNames());
        $this->set('mediaLanguages', $mediaLanguages);
        $this->set('screenArr', $screenArr);
        $this->set('collection_id', $collectionId);
        $this->set('bannerId', $bannerId);
        $this->set('blocationId', $blocationId);
        $this->set('collection_type', $collectionDetails['collection_type']);
        $this->set('collection_layout_type', $collectionDetails['collection_layout_type']);
        $this->set('dimensions', $bannerSizeArr);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function bannerImages(int $collection_id, int $banner_id, int $lang_id = 0, int $screen = 0)
    {
        if (1 > $collection_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $collectionDetails = Collections::getAttributesById($collection_id);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $bannerLocation = BannerLocation::getDataByCollectionId($collection_id);
        $blocation_id = $bannerLocation['blocation_id'];
        $bannerImgArr = AttachedFile::getAttachment(AttachedFile::FILETYPE_BANNER, $banner_id, 0, $lang_id, false, $screen);

        $this->set('images', $bannerImgArr);
        $this->set('languages', Language::getAllNames());
        $this->set('screenTypeArr', $this->getDisplayScreenName());
        $this->set('blocation_id', $blocation_id);
        $this->set('banner_id', $banner_id);
        $this->_template->render(false, false);
    }

    public function removeBanner(int $afileId, int $bannerId, int $langId = 0, int $slide_screen = 0)
    {
        $this->objPrivilege->canEditProductCategories();
        if (!$afileId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $fileType = AttachedFile::FILETYPE_BANNER;
        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $bannerId, $afileId, 0, $langId, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Image_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function bannerTypeArr()
    {
        return applicationConstants::bannerTypeArr();
    }

    private function getDisplayScreenName()
    {
        $screenTypesArr = applicationConstants::getDisplaysArr($this->adminLangId);
        return array(0 => '') + $screenTypesArr;
    }

    public function setupBanner()
    {
        $this->objPrivilege->canEditBanners();
        $bannerPosition = FatApp::getPostedData('banner_position', FatUtility::VAR_INT, 1);
        $collection_id = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $frm = $this->getBannerForm($collection_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $collection_id = $post['collection_id'];
        $banner_id = $post['banner_id'];
        $bannerId = $post['banner_id'];
        unset($post['banner_id']);

        $collectionDetails = Collections::getAttributesById($collection_id);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $bannerLocation = BannerLocation::getDataByCollectionId($collection_id);
        $post['banner_blocation_id'] = $bannerLocation['blocation_id'];
        $post['banner_type'] = Banner::TYPE_BANNER;
        $post['banner_active'] = applicationConstants::ACTIVE;
        if ($collectionDetails['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT4) {
            $post['banner_position'] = $bannerPosition;
        }

        $record = new Banner($banner_id);
        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $banner_id = $record->getMainTableRecordId();

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);

        $data = array(
            'bannerlang_banner_id' => $banner_id,
            'bannerlang_lang_id' => $siteDefaultLangId,
            'banner_title' => $post['banner_title'][$siteDefaultLangId],
        );

        $bannerObj = new Banner($banner_id);
        if (!$bannerObj->updateLangData($siteDefaultLangId, $data)) {
            Message::addErrorMessage($bannerObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Banner::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($banner_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->addUpdateCollectionRecord($collection_id, $banner_id)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($bannerId == 0 && isset($post['banner_image_id'])) {
            $banner = new Banner($banner_id);
            $banner->updateMedia($post['banner_image_id']);
        }
        
        /* [ SAVE UPDATED SCREENSHOT OF HOME PAGE */
        /* $this->updateHomePageScreenShot(); */
        /* ] */
        

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('collection_id', $collection_id);
        $this->set('banner_id', $banner_id);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupBannerImage()
    {
        $this->objPrivilege->canEditProductCategories();
        $banner_id = FatApp::getPostedData('banner_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $slide_screen = FatApp::getPostedData('banner_screen', FatUtility::VAR_INT, 0);
        $afileId = FatApp::getPostedData('afile_id', FatUtility::VAR_INT, 0);

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_BANNER);

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $file_type = AttachedFile::FILETYPE_BANNER;
        Banner::deleteImagesWithoutBannerId($file_type);

        $fileHandlerObj = new AttachedFile($afileId);
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], $file_type, $banner_id, 0, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id, $_FILES['cropped_image']['type'], $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        ProductCategory::setImageUpdatedOn($banner_id);
        /* [ SAVE UPDATED SCREENSHOT OF HOME PAGE */
        /* $this->updateHomePageScreenShot(); */
        /* ] */
        
        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('banner_id', $banner_id);
        $this->set('msg', $_FILES['cropped_image']['name'] . ' ' . Labels::getLabel('LBL_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getBannerForm(int $collectionId = 0, int $bannerId = 0, int $bannerLocationId = 0)
    {
        $this->objPrivilege->canViewCollections();
        $collectionDetails = Collections::getAttributesById($collectionId);

        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $collectionLayout = (isset($collectionDetails['collection_layout_type'])) ? $collectionDetails['collection_layout_type'] : '';
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = new Form('frmBanner');

        $frm->addRequiredField(Labels::getLabel('LBL_Banner_Title', $this->adminLangId), 'banner_title[' . $siteDefaultLangId . ']');
        switch ($collectionLayout) {
            case Collections::TYPE_BANNER_LAYOUT4:
                $bannerPosArr = Collections::getBannerPositionType($this->adminLangId);
                $frm->addSelectBox(Labels::getLabel('LBL_Banner_Position', $this->adminLangId), 'banner_position', $bannerPosArr, 1, [], '')->requirements()->setRequired(true);
                break;
        }

        $fld = $frm->addHiddenField('', 'collection_layout_type', $collectionLayout);
        $fld = $frm->addHiddenField('', 'collection_id', $collectionId);
        $fld->requirements()->setInt();
        $fld->requirements()->setIntPositive();
        $fld = $frm->addHiddenField('', 'banner_id', $bannerId);
        $fld->requirements()->setInt();
        $fld->requirements()->setIntPositive();
        $fld = $frm->addHiddenField('', 'blocation_id', $bannerLocationId);
        $fld->requirements()->setInt();
        $fld->requirements()->setIntPositive();
        $frm->addTextBox(Labels::getLabel('LBL_Url', $this->adminLangId), 'banner_url');
        $linkTargetsArr = applicationConstants::getLinkTargetsArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Open_In', $this->adminLangId), 'banner_target', $linkTargetsArr, '', array(), '');

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($langData) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        foreach ($langData as $langId => $data) {
            $frm->addTextBox(Labels::getLabel('LBL_Banner_Title', $this->adminLangId), 'banner_title[' . $langId . ']');
        }
        $mediaLanguages = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'banner_lang_id', $mediaLanguages, '', array(), '');
        $screenArr = applicationConstants::getDisplaysArr($this->adminLangId);
        $displayFor = ($collectionDetails && $collectionDetails['collection_layout_type'] == Collections::TYPE_BANNER_LAYOUT3) ? applicationConstants::SCREEN_MOBILE : '';
        $frm->addSelectBox(Labels::getLabel("LBL_Device", $this->adminLangId), 'banner_screen', $screenArr, $displayFor, array(), '');
        $frm->addHiddenField('', 'banner_min_width');
        $frm->addHiddenField('', 'banner_min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'banner', array('accept' => 'image/*', 'data-frm' => 'frmCategoryBanner'));
        foreach ($mediaLanguages as $key => $data) {
            foreach ($screenArr as $key1 => $screen) {
                $frm->addHiddenField('', 'banner_image_id[' . $key . '_' . $key1 . ']');
            }
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getRecordsForm(int $collectionId = 0, int $collectionType = 0)
    {
        $this->objPrivilege->canViewCollections();
        $frm = new Form('frmCollectionRecords');
        $fld = $frm->addHiddenField('', 'collection_id', $collectionId);
        $fld->requirements()->setInt();
        $fld->requirements()->setIntPositive();
        switch ($collectionType) {
            case Collections::COLLECTION_TYPE_PRODUCT:
            case Collections::COLLECTION_TYPE_SUB_COLLECTION:
                $frm->addSelectBox(Labels::getLabel('LBL_Products', $this->adminLangId), 'collection_records', [], '', array('placeholder' => Labels::getLabel('LBL_Select_Product', $this->adminLangId)));
                break;
            case Collections::COLLECTION_TYPE_CATEGORY:
                $frm->addTextbox(Labels::getLabel('LBL_Categories', $this->adminLangId), 'collection_records');
                break;
            case Collections::COLLECTION_TYPE_SHOP:
                $frm->addTextbox(Labels::getLabel('LBL_Shops', $this->adminLangId), 'collection_records');
                break;
            case Collections::COLLECTION_TYPE_BRAND:
                $frm->addTextbox(Labels::getLabel('LBL_Brands', $this->adminLangId), 'collection_records');
                $fld = $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'brand_image', array('accept' => 'image/*', 'data-frm' => 'frmBrandCollectionMedia'));
                $frm->addHiddenField('', 'collection_brand_id');
                $imgSizeArr = imagesSizes::brandFeaturedImageSizeArr()[applicationConstants::getActiveTheme()];
                $frm->addHiddenField('', 'max_height', $imgSizeArr['height']);
                $frm->addHiddenField('', 'max_width',$imgSizeArr['width']);

                break;
            case Collections::COLLECTION_TYPE_BLOG:
                $frm->addTextbox(Labels::getLabel('LBL_Blogs', $this->adminLangId), 'collection_records');
                break;
            case Collections::COLLECTION_TYPE_FAQ:
                $frm->addTextbox(Labels::getLabel('LBL_Faqs', $this->adminLangId), 'collection_records');
                break;
            case Collections::COLLECTION_TYPE_TESTIMONIAL:
                $frm->addTextbox(Labels::getLabel('LBL_Testimonials', $this->adminLangId), 'collection_records');
                break;
            case Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON:
                $frm->addTextbox(Labels::getLabel('LBL_Content_Blocks', $this->adminLangId), 'collection_records');
                break;
        }
        return $frm;
    }

    public function autoCompleteSelprods()
    {
        $this->objPrivilege->canViewCollections();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $productIds = FatApp::getPostedData('productIds', FatUtility::VAR_STRING, '');
        if ($page < 2) {
            $page = 1;
        }
        $db = FatApp::getDb();
        $srch = new ProductSearch($this->adminLangId);
        $srch->setDefinedCriteria(0);
        $srch->joinSellers();
        $srch->joinShops(0, true, true);
        $srch->joinBrands(0);
        $srch->joinProductToCategory(0);
        $srch->joinSellerSubscription(0, false, true);
        
        $srch->addCondition('selprod_id', '>', 0);
        if (!empty($post['keyword'])) {
            $srch->addDirectCondition("(selprod_title like " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_name LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . " or product_identifier LIKE " . $db->quoteVariable('%' . $post['keyword'] . '%') . " )", 'and');
        }

        $srch->setPageSize(20);
        $srch->setPageNumber($page);
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(product_name,product_identifier) as product_name, IFNULL(selprod_title,product_identifier) as selprod_title', 'credential_username'));

        $collectionId = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $alreadyAdded = Collections::getRecords($collectionId);
        if (!empty($alreadyAdded) && 0 < count($alreadyAdded)) {
            $srch->addCondition('selprod_id', 'NOT IN', array_keys($alreadyAdded));
        }
        if (1 > $collectionId) {
            $oldAttachedIds = explode(',', $productIds);
            if (!empty($oldAttachedIds)) {
                $srch->addCondition('selprod_id', 'NOT IN', $oldAttachedIds);
            }
        }
        
        /* echo $srch->getQuery(); die(); */
        
        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');
        $pageCount = $srch->pages();
        $json = array();
        foreach ($products as $key => $product) {
            $options = SellerProduct::getSellerProductOptions($key, true, $this->adminLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });
            $userName = isset($product["credential_username"]) ? " | " . $product["credential_username"] : '';
            $productName = strip_tags(html_entity_decode(($product['selprod_title'] != '') ? $product['selprod_title'] : $product['product_name'], ENT_QUOTES, 'UTF-8'));
            $productName .= $variantsStr . $userName;
            $json[] = array(
                'id' => $key,
                'name' => $productName
            );
        }
        die(json_encode(['pageCount' => $pageCount, 'products' => $json]));
    }

    public function mediaForm(int $collectionId = 0)
    {
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (false != $collectionDetails) {
            $collectionImages = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_IMAGE, $collectionId);
            $this->set('collectionImages', $collectionImages);
        }

        if ($collectionDetails['collection_layout_type'] == Collections::TYPE_CATEGORY_LAYOUT4) {
            $this->set('attachedCategories', Collections::getCategories($collectionId, $this->adminLangId, true));
            $this->set('gridImageSizeArr', imagesSizes::gridViewImagesSizeArr()[applicationConstants::getActiveTheme()]);
            $collectionImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_COLLECTION_CATEGORY_IMAGE, $collectionId);
            $this->set('collectionImages', $this->formatAttachmentArr($collectionImages, 'afile_record_subid'));
        }
        if (in_array($collectionDetails['collection_layout_type'], Collections::LAYOUT_WITH_MEDIA)) {
            $collectionDetails['collection_display_media_only'] = 1;
        }

        $activeTheme = applicationConstants::getActiveTheme();
        $bannerSizeArr = imagesSizes::getBannersDimensions();
        $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
        $bannerSizeArr = isset($bannerSizeArr[$collectionDetails['collection_layout_type']]) ? $bannerSizeArr[$collectionDetails['collection_layout_type']]:[];

        $this->set('collection_type', $collectionDetails['collection_type']);
        $this->set('collection_layout_type', $collectionDetails['collection_layout_type']);
        $this->set('collection_id', $collectionId);
        $this->set('imgUpdatedOn', Collections::getAttributesById($collectionId, 'collection_img_updated_on'));
		$this->set('recordLimit', $this->getLayoutLimit($collectionDetails['collection_layout_type']));
        $this->set('displayMediaOnly', $collectionDetails['collection_display_media_only']);
        $this->set('collectionMediaFrm', $this->getMediaForm($collectionId));
        $this->set('languages', Language::getAllNames());
        $this->set('dimensions', $bannerSizeArr);
        $this->_template->render(false, false);
    }

    private function getMediaForm($collectionId)
    {
        $frm = new Form('frmCollectionMedia');
        $languagesAssocArr = Language::getAllNames();
        $frm->addHTML('', 'collection_image_heading', '');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addCheckBox(Labels::getLabel("LBL_Display_Media_Only", $this->adminLangId), 'collection_display_media_only', 1, array(), false, 0);
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'image_lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId)) + $languagesAssocArr, '', array(), '');
        $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_COLLECTION_IMAGE);
        $frm->addHiddenField('', 'min_width');
        $frm->addHiddenField('', 'min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'collection_image', array('accept' => 'image/*', 'data-frm' => 'frmCollectionMedia'));
        $frm->addHtml('', 'collection_image_display_div', '');
        return $frm;
    }

    public function uploadImage()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $collection_id = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $order = FatApp::getPostedData('order_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);

        if (!$collection_id || !$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collectionData = Collections::getAttributesById($collection_id, ['collection_type', 'collection_layout_type']);

        $collectionType = (isset($collectionData['collection_type'])) ? $collectionData['collection_type'] : Collections::COLLECTION_TYPE_PRODUCT;

        $layoutType = (isset($collectionData['collection_layout_type'])) ? $collectionData['collection_layout_type'] : Collections::TYPE_PRODUCT_LAYOUT1;

        if (!in_array($layoutType, Collections::LAYOUT_WITH_MEDIA)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Not_Allowed_To_Update_Media_For_This_Collection', $this->adminLangId));
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_COLLECTION_IMAGE, AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, AttachedFile::FILETYPE_COLLECTION_CATEGORY_IMAGE);
        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], $file_type, $collection_id, $order, $_FILES['cropped_image']['name'], -1, true, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collection = new Collections($collection_id);
        $collection->addUpdateData(array('collection_img_updated_on' => date('Y-m-d H:i:s')));

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('collection_id', $collection_id);
        $this->set('msg', $_FILES['cropped_image']['name'] . Labels::getLabel('MSG_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeImage(int $collection_id = 0, int $lang_id = 0)
    {
        if (1 > $collection_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_COLLECTION_IMAGE, $collection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collection = new Collections($collection_id);
        $collection->addUpdateData(array('collection_img_updated_on' => date('Y-m-d H:i:s')));

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeBgImage(int $collection_id = 0, int $lang_id = 0)
    {
        if (1 > $collection_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, $collection_id, 0, 0, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Deleted_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditCollections();
        $collection_id = FatApp::getPostedData('collectionId', FatUtility::VAR_INT, 0);
        if ($collection_id < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->markAsDeleted($collection_id);
        /* [ SAVE UPDATED SCREENSHOT OF HOME PAGE */
        /* $this->updateHomePageScreenShot(); */
        /* ] */
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditCollections();
        $collectionIdsArr = FatUtility::int(FatApp::getPostedData('collection_ids'));

        if (empty($collectionIdsArr)) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($collectionIdsArr as $collection_id) {
            if (1 > $collection_id) {
                continue;
            }
            $this->markAsDeleted($collection_id);
        }
        $this->set('msg', $this->str_delete_record);
        /* [ SAVE UPDATED SCREENSHOT OF HOME PAGE */
        /* $this->updateHomePageScreenShot(); */
        /* ] */
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted(int $collection_id)
    {
        if (1 > $collection_id) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }
        $collectionObj = new Collections($collection_id);
        if (!$collectionObj->canRecordMarkDelete($collection_id)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collectionObj->assignValues(array(Collections::tblFld('deleted') => 1));
        if (!$collectionObj->save()) {
            Message::addErrorMessage($collectionObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function updateOrder()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $collectionObj = new Collections();
            if (!$collectionObj->updateOrder($post['collectionList'])) {
                Message::addErrorMessage($collectionObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            /* [ SAVE UPDATED SCREENSHOT OF HOME PAGE */
                /* $this->updateHomePageScreenShot(); */
            /* ] */
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function updateCollectionRecordOrder()
    {
        $this->objPrivilege->canEditCollections();
        $post = FatApp::getPostedData();
        if (!empty($post)) {
            $collectionObj = new Collections();
            if (!$collectionObj->updateCollectionRecordOrder($post['collection_id'], $post['collection-record'])) {
                Message::addErrorMessage($collectionObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Order_Updated_Successfully', $this->adminLangId));
        }
    }

    public function layouts()
    {
        $this->_template->render(false, false);
    }

    public function getCollectionTypeLayout($collectionType, $searchForm = 0)
    {
        $this->objPrivilege->canEditCollections();
        $this->set('collectionType', $collectionType);
        $availableLayouts = Collections::getTypeSpecificLayouts($this->adminLangId)[$collectionType];
        if ($searchForm > 0) {
            $availableLayouts = array(-1 => Labels::getLabel('LBL_Does_Not_matter', $this->adminLangId)) + $availableLayouts;
        }
        $this->set('availableLayouts', $availableLayouts);
        $this->_template->render(false, false);
    }

    public function getLayoutLimit(int $collectionLayoutType)
    {
		$activeTheme = applicationConstants::getActiveTheme();
		$activeThemeLayoutsArr = (isset(Collections::layoutArrByTheme()[$activeTheme])) ? Collections::layoutArrByTheme()[$activeTheme] : [];
		return (isset($activeThemeLayoutsArr[$collectionLayoutType])) ? $activeThemeLayoutsArr[$collectionLayoutType] : 1;
		
		
		
        /*switch ($collection_layout_type) { 
            case Collections::TYPE_PRODUCT_LAYOUT1:
                return Collections::LIMIT_PRODUCT_LAYOUT1;
                break;
            case Collections::TYPE_PRODUCT_LAYOUT2:
                return Collections::LIMIT_PRODUCT_LAYOUT2;
                break;
            case Collections::TYPE_PRODUCT_LAYOUT3:
                return Collections::LIMIT_PRODUCT_LAYOUT3;
                break;
            case Collections::TYPE_PRODUCT_LAYOUT4:
                return Collections::LIMIT_PRODUCT_LAYOUT4;
                break;
            case Collections::TYPE_CATEGORY_LAYOUT1:
                return Collections::LIMIT_CATEGORY_LAYOUT1;
                break;
            case Collections::TYPE_CATEGORY_LAYOUT2:
                return Collections::LIMIT_CATEGORY_LAYOUT2;
                break;
            case Collections::TYPE_CATEGORY_LAYOUT3:
                return Collections::LIMIT_CATEGORY_LAYOUT3;
                break;
            case Collections::TYPE_SHOP_LAYOUT1:
                return Collections::LIMIT_SHOP_LAYOUT1;
                break;
            case Collections::TYPE_SHOP_LAYOUT2:
                return Collections::LIMIT_SHOP_LAYOUT2;
                break;
            case Collections::TYPE_BRAND_LAYOUT1:
                return Collections::LIMIT_BRAND_LAYOUT1;
                break;
            case Collections::TYPE_BRAND_LAYOUT2:
                return Collections::LIMIT_BRAND_LAYOUT2;
                break;
            case Collections::TYPE_BLOG_LAYOUT1:
                return Collections::LIMIT_BLOG_LAYOUT1;
                break;
            case Collections::TYPE_BLOG_LAYOUT2:
                return Collections::LIMIT_BLOG_LAYOUT2;
                break;
            case Collections::TYPE_CATEGORY_LAYOUT4:
                return Collections::LIMIT_CATEGORY_LAYOUT4;
                break;
            case Collections::TYPE_PRODUCT_LAYOUT5:
                return Collections::LIMIT_PRODUCT_LAYOUT5;
                break;
        } */
    }

    public function displayMediaOnly(int $collectionId, $value = 0)
    {
        if (1 > $collectionId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $collectionData = Collections::getAttributesById($collectionId, ['collection_type', 'collection_layout_type']);

        $collectionType = (isset($collectionData['collection_type'])) ? $collectionData['collection_type'] : Collections::COLLECTION_TYPE_PRODUCT;

        $layoutType = (isset($collectionData['collection_layout_type'])) ? $collectionData['collection_layout_type'] : Collections::TYPE_PRODUCT_LAYOUT1;

        if (!in_array($layoutType, Collections::LAYOUT_WITH_MEDIA)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Not_Allowed_To_Update_Media_For_This_Collection', $this->adminLangId));
        }

        $collectionObj = new Collections($collectionId);
        $collectionObj->addUpdateData(array('collection_display_media_only' => $value));
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function uploadCategoryImage()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $collection_id = FatApp::getPostedData('collection_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);

        if (!$collection_id || !$file_type) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* $collectionType = (0 < $collection_id) ? Collections::getAttributesById($collection_id, 'collection_type') : Collections::COLLECTION_TYPE_PRODUCT;
        if (!in_array($collectionType, Collections::COLLECTION_WITHOUT_MEDIA)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Not_Allowed_To_Update_Media_For_This_Collection', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        } */

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_COLLECTION_IMAGE, AttachedFile::FILETYPE_COLLECTION_BG_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], $file_type, $collection_id, 0, $_FILES['cropped_image']['name'], -1, $unique_record = true, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $collection = new Collections($collection_id);
        $collection->addUpdateData(array('collection_img_updated_on' => date('Y-m-d H:i:s')));

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('collection_id', $collection_id);
        $this->set('msg', $_FILES['cropped_image']['name'] . Labels::getLabel('MSG_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function formatAttachmentArr(array $attactmentArr, string $key = 'afile_record_subid'): array
    {
        if (empty($attactmentArr)) {
            return [];
        }
        $finalArr = [];
        foreach ($attactmentArr as $attachment) {
            $finalArr[$attachment['afile_record_subid']][] = $attachment;
        }
        return $finalArr;
    }

    public function addRecordForm(int $collectionId, int $collectionType, int $displayOrder)
    {
        $this->objPrivilege->canViewCollections();
        $collectionDetails = Collections::getAttributesById($collectionId);
        if (false != $collectionDetails && ($collectionDetails['collection_active'] != applicationConstants::ACTIVE || $collectionDetails['collection_deleted'] == applicationConstants::YES)) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);

        $frm = $this->getAddRecordsForm($collectionId, $collectionType, $displayOrder);

        if (0 < $collectionId) {
            $langData =  Collections::getContentBlocksRecords($collectionId, $displayOrder );
            
            $data = array();
            foreach ($langData as $value) {
                $data[ContentBlockWithIcon::DB_TBL_PREFIX . 'id'] = $value[ContentBlockWithIcon::DB_TBL_PREFIX . 'id'];
                $data[ContentBlockWithIcon::DB_TBL_PREFIX . 'display_order'] = $value[ContentBlockWithIcon::DB_TBL_PREFIX . 'display_order'];
                $data[ContentBlockWithIcon::DB_TBL_PREFIX . 'identifier'] = $value[ContentBlockWithIcon::DB_TBL_PREFIX . 'identifier'];
                $data[ContentBlockWithIcon::DB_TBL_PREFIX . 'name'][$value[ContentBlockWithIcon::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[ContentBlockWithIcon::DB_TBL_PREFIX . 'name'];
                $data[ContentBlockWithIcon::DB_TBL_LANG_PREFIX . 'description_'.$value[ContentBlockWithIcon::DB_TBL_LANG_PREFIX . 'lang_id']] = $value[ContentBlockWithIcon::DB_TBL_LANG_PREFIX . 'description'];
            }
            $frm->fill($data);
        }


        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);

        $this->set('otherLangData', $langData);
        $this->set('languages', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
		$this->set('recordLimit', $this->getLayoutLimit($collectionDetails['collection_layout_type']));
        $this->set('displayOrder', $displayOrder);
        $this->set('collection_id', $collectionId);
        $this->set('collection_type', $collectionType);
        $this->set('collection_layout_type', $collectionDetails['collection_layout_type']);
        $this->set('frm', $frm);
        $this->_template->render(false, false);

    }

    private function getAddRecordsForm(int $collectionId, int $collectionType, int $displayOrder)
    {
        $this->objPrivilege->canViewCollections();
        $maxContentLength = 300;

        if($collectionType == Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON) {
            $maxContentLength = 105;
        }

        $minWidth = 100; 
        $minHeight = 100; 
        if($collectionType == Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON && applicationConstants::getActiveTheme() == applicationConstants::THEME_FASHION){
            $minWidth = 32;
            $minHeight = 32;
        }


        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $frm = new Form('frmCollection');
        
        $frm->addRequiredField(Labels::getLabel('LBL_Block_Identifier', $this->adminLangId), 'cbs_identifier');

        $frm->addRequiredField(Labels::getLabel('LBL_Block_Title', $this->adminLangId), 'cbs_name['. $siteDefaultLangId .']');

        $frm->addTextArea(Labels::getLabel('LBL_Block_Content', $this->adminLangId), 'cbslang_description_'.$siteDefaultLangId, '', array('maxlength' => $maxContentLength));

        $frm->addHiddenField('', 'min_width', $minWidth);
        $frm->addHiddenField('', 'min_height', $minHeight);
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->adminLangId), 'block_image', array('id' => 'block_image'));

        $langData = Language::getAllNames();
        unset($langData[$siteDefaultLangId]);

        foreach ($langData as $langId => $data) {
            $frm->addTextBox(Labels::getLabel('LBL_Block_Title', $this->adminLangId), 'cbs_name['. $langId .']');
            $frm->addTextArea(Labels::getLabel('LBL_Block_Content', $this->adminLangId), 'cbslang_description_'.$langId, '', array('maxlength' => $maxContentLength));
        }
        $frm->addHiddenField('', 'cbs_id');
        $frm->addHiddenField('', 'collection_id', $collectionId);
        $frm->addHiddenField('', 'collection_type', $collectionType);
        $frm->addHiddenField('', 'cbs_display_order', $displayOrder);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupRecord() 
    {
        $this->objPrivilege->canEditContentWithIconSection();
        $data = FatApp::getPostedData();
        $collectionId = $data['collection_id'];
        $collectionType = $data['collection_type'];
        $blockId = isset($data['cbs_id'])? $data['cbs_id'] : 0 ;
        $displayOrder = $data['cbs_display_order'];
        $frm = $this->getAddRecordsForm($collectionId, $collectionType,$displayOrder);
        $post = $frm->getFormDataFromArray($data);
        
        unset($post['collection_id']);
        unset($post['btn_submit']);
        $post['cbs_active'] = applicationConstants::YES;

        $content = new ContentBlockWithIcon($blockId);
        $content->assignValues($post);
        if (!$content->save()) {
            Message::addErrorMessage($content->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!$blockId){
            $blockId = $content->getMainTableRecordId();
            if (!$this->upldateAttachedFilesData($collectionId, $displayOrder, $blockId)) {
                Message::addErrorMessage($this->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        
        $collectionObj = new Collections($collectionId);
        if (!$collectionObj->addUpdateCollectionRecord($collectionId, $blockId, $displayOrder)) {
            Message::addErrorMessage(Labels::getLabel($collectionObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $contentLang = new ContentBlockWithIcon($blockId);
        $nameArr = $post['cbs_name'];
        foreach ($nameArr as $langId => $blockName) {
            $desc = (isset($post['cbslang_description_'.$langId])) ? $post['cbslang_description_'.$langId] : "";
            $name = (isset($post['cbs_name'][$langId])) ? $post['cbs_name'][$langId] : "";

            if(!$contentLang->saveLangData($langId, $name, $desc)){
                Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_save_Language_data', $this->adminLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->set('collectionId', $collectionId);
        $this->set('collectionType', $post['collection_type']);
        $this->set('displayOrder', $displayOrder);
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function upldateAttachedFilesData(int $collectionId, int $displayOrder, int $blockId) : bool
    {
        $whr = array('smt' => 'afile_record_id = ? AND afile_record_subid = ?', 'vals' => array($displayOrder, $collectionId));
        if (!FatApp::getDb()->updateFromArray(AttachedFile::DB_TBL, ['afile_record_id' => $blockId], $whr, false)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }
    
    private function updateHomePageScreenShot()
    {
        $api = FatApp::getConfig('CONF_GOOGLE_PAGE_INSIGHT_API_KEY', FatUtility::VAR_STRING, '');
        $site = UrlHelper::generateFullUrl('home', 'index', [], CONF_WEBROOT_FRONTEND).'?isPreview=1';
        /* $site = 'https://dev2.yorent-v2.4livedemo.com/?isPreview=true'; */
        $apiUrl = "https://pagespeedonline.googleapis.com/pagespeedonline/v5/runPagespeed?url=$site&category=CATEGORY_UNSPECIFIED&strategy=DESKTOP"; //
        if (trim($api) != '') {
            $apiUrl.="&key=$api";
        }
        $curl_init = curl_init($apiUrl);
        curl_setopt($curl_init,CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($curl_init);
        curl_close($curl_init);
        $googledata = json_decode($response, true);
        if (!empty($googledata) && isset($googledata["lighthouseResult"])) {
            $snapdata = $googledata["lighthouseResult"]["audits"]["full-page-screenshot"]["details"];
            $screenshot = $snapdata["screenshot"]['data'];
            list($type, $data) = explode(';', $screenshot);
            list(, $data) = explode(',', $data);
            $data = base64_decode($data);
            
            $filePath = CONF_UPLOADS_PATH .'cropped/screenshot';
            if (!file_exists($filePath)) { 
                mkdir($filePath, 0777, true);
            }
            file_put_contents($filePath.'/homescreen.png', $data);
        }
    }
    
}