<?php

class CustomProductsController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCustomProductRequests($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCustomProductRequests($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
        $this->set("includeEditor", true);
    }

    public function index()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $frmSearch = $this->catalogCustomProductRequestSearchForm();
        $this->set('frmSearch', $frmSearch);
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js'));
        $this->_template->addCss('css/cropper.css');
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srchForm = $this->catalogCustomProductRequestSearchForm();

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $post = $srchForm->getFormDataFromArray($data);

        $srch = ProductRequest::getSearchObject($this->adminLangId, false, true);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'preq_user_id = u.user_id', 'u');
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', Shop::DB_TBL_PREFIX . 'user_id = if(u.user_parent > 0, u.user_parent, u.user_id)', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->adminLangId, 's_l');
        /* $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc'); */
        $srch->addOrder('preq_added_on', 'desc');
        $srch->addMultipleFields(array('preq.*', 'user_id', 'user_name', 'user_parent', 'ifnull(shop_name, shop_identifier) as shop_name, shop_id'));
        if (!empty($post['keyword'])) {
            $keyword = trim($post['keyword']);
            $cond = $srch->addCondition('preq.preq_content', 'like', '%' . $keyword . '%');
            $cond->attachCondition('preq_l.preq_lang_data', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%', 'OR');
            /* $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
              $cond->attachCondition('uc.credential_username', 'like', '%' . $keyword . '%', 'OR'); */
        }

        if (!empty($post['date_from'])) {
            $srch->addCondition('preq.preq_added_on', '>=', $post['date_from'] . ' 00:00:00');
        }

        if ($post['status'] > -1) {
            $srch->addCondition('preq.preq_status', '=', $post['status']);
        }

        if (!empty($post['date_to'])) {
            $srch->addCondition('preq.preq_added_on', '<=', $post['date_to'] . ' 23:59:59');
        }
        $srch->addOrder('preq.preq_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $rs = $srch->getResultSet();
        $records = array();
        while ($res = FatApp::getDb()->fetch($rs)) {
            $content = (!empty($res['preq_content'])) ? json_decode($res['preq_content'], true) : array();
            $langContent = (!empty($res['preq_lang_data'])) ? json_decode($res['preq_lang_data'], true) : array();

            $res = array_merge($res, $content);
            if (!empty($langContent)) {
                $res = array_merge($res, $langContent);
            }
            $arr = array(
                'preq_id' => $res['preq_id'],
                'preq_user_id' => $res['preq_user_id'] ?? 0,
                'preq_added_on' => $res['preq_added_on'] ?? '',
                'preq_status' => $res['preq_status'] ?? '',
                'preq_requested_on' => $res['preq_requested_on'] ?? '',
                'preq_status_updated_on' => $res['preq_status_updated_on'] ?? '',
                'user_id' => $res['user_id'] ?? 0,
                'user_name' => $res['user_name'] ?? '',
                'user_parent' => $res['user_parent'] ?? 0,
                'shop_name' => $res['shop_name'] ?? '',
                'shop_id' => $res['shop_id'] ?? '',
                /* 'credential_username' => $res['credential_username']  ?? '',
                  'credential_email' => $res['credential_email']  ?? '', */
                'product_identifier' => $res['product_identifier'],
                'product_name' => (!empty($res['product_name'])) ? $res['product_name'] : '',
            );
            $records[] = $arr;
        }

        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('reqStatusClassArr', User::getCatalogRequestClassArr());
        $this->set('reqStatusArr', ProductRequest::getStatusArr($this->adminLangId));
        $this->set('canViewCustomProductRequests', $this->objPrivilege->canViewCustomProductRequests($this->admin_id, true));
        $this->set('canEditCustomProductRequests', $this->objPrivilege->canEditCustomProductRequests($this->admin_id, true));
        $this->_template->render(false, false);
    }

    /* public function form($preqId = 0)
      {
      $this->objPrivilege->canViewCustomProductRequests();
      $preqId = FatUtility::int($preqId);
      if (!$preqId) {
      FatUtility::dieWithError($this->str_invalid_request);
      }
      $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id'));
      $preq_prodcat_id = $productReqRow['preq_prodcat_id'];

      $customProductFrm = $this->getForm(0);
      $productOptions = array();
      $productTags = array();
      if ($preqId > 0) {
      $row_data = ProductRequest::getAttributesById($preqId, array('preq_id', 'preq_user_id', 'preq_prodcat_id', 'preq_content', 'preq_status', 'preq_deleted', 'preq_added_on', 'preq_taxcat_id as ptt_taxcat_id', 'preq_taxcat_id_rent as ptt_taxcat_id_rent'));
      $productData = json_decode($row_data['preq_content'], true);
      unset($row_data['preq_content']);
      $row_data = array_merge($row_data, $productData);
      $productOptions = !empty($row_data['product_option']) ? $row_data['product_option'] : array();
      $productTags = !(empty($row_data['product_tags'])) ? $row_data['product_tags'] : array();

      $customProductFrm = $this->getForm(0, $productData['product_type']);
      $customProductFrm->fill($row_data);
      }

      $this->set('customProductFrm', $customProductFrm);
      $this->set('preqId', $preqId);
      $this->set('preq_prodcat_id', $preq_prodcat_id);
      $this->set('productOptions', $productOptions);
      $this->set('productTags', $productTags);
      $this->set('languages', Language::getAllNames());
      $this->_template->render(false, false);
      } */

    public function form($preqId)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id'));
        $preq_prodcat_id = $productReqRow['preq_prodcat_id'];

        $customProductFrm = $this->getCustomProductIntialSetUpFrm(0, $preqId);
        $productOptions = array();
        $productTags = array();
        if ($preqId > 0) {
            $row_data = ProductRequest::getAttributesById($preqId, array('preq_id', 'preq_user_id', 'preq_prodcat_id', 'preq_content', 'preq_status', 'preq_deleted', 'preq_added_on', 'preq_taxcat_id as ptt_taxcat_id', 'preq_taxcat_id_rent as ptt_taxcat_id_rent'));
            $productData = json_decode($row_data['preq_content'], true);
            unset($row_data['preq_content']);
            $row_data = array_merge($row_data, $productData);
            $productOptions = !empty($row_data['product_option']) ? $row_data['product_option'] : array();
            $productTags = !(empty($row_data['product_tags'])) ? $row_data['product_tags'] : array();

            $customProductFrm = $this->getCustomProductIntialSetUpFrm(0, $productData['product_type']);
            $customProductFrm->fill($row_data);
        }

        $this->set('customProductFrm', $customProductFrm);
        $this->set('preq_prodcat_id', $preq_prodcat_id);
        $this->set('productOptions', $productOptions);
        $this->set('productTags', $productTags);
        //$this->set('languages', Language::getAllNames());

        $pcObj = new ProductCategory($preq_prodcat_id);
        $this->set('isCustomFields', $pcObj->isCategoryHasCustomFields($this->adminLangId));

        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js', 'js/jquery-sortable-lists.js', 'js/tagify.min.js', 'js/tagify.polyfills.min.js'));
        $this->_template->addCss(array('css/cropper.css', 'css/tagify.css'));
        $this->set("includeEditor", true);
        $this->set('preqId', $preqId);
        $this->_template->render();
    }

    public function productInitialSetupFrm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        $customProductFrm = $this->getCustomProductIntialSetUpFrm(0, $preqId);
        $languages = Language::getAllNames();
        if ($preqId > 0) {
            $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_id', 'preq_user_id', 'preq_prodcat_id', 'preq_content', 'preq_status', 'preq_deleted', 'preq_added_on', 'preq_taxcat_id as ptt_taxcat_id', 'preq_taxcat_id_rent as ptt_taxcat_id_rent'));

            $prodcatId = $productReqRow['preq_prodcat_id'];
            $prodcatId = FatUtility::int($prodcatId);
            $productData = json_decode($productReqRow['preq_content'], true);
            unset($productReqRow['preq_content']);
            $productReqRow = array_merge($productReqRow, $productData, array('preq_prodcat_id' => $prodcatId));
            $productReqRow['ptc_prodcat_id'] = $prodcatId;
            $prodCat = new ProductCategory();
            $selectedCatName = $prodCat->getParentTreeStructure($prodcatId, 0, '', $this->adminLangId);
            $productReqRow['category_name'] = html_entity_decode($selectedCatName);

            $langData = array();
            foreach ($languages as $langId => $data) {
                $prodReq = new ProductRequest($preqId);
                $customProductLangData = $prodReq->getAttributesByLangId($langId, $preqId);
                if (is_array($customProductLangData)) {
                    $langContent = json_decode($customProductLangData['preq_lang_data'], true);
                    $langData['product_name'][$langId] = $langContent['product_name'];
                    $langData['product_youtube_video'][$langId] = $langContent['product_youtube_video'];
                    //$langData['product_description'][$langId] = $langContent['product_description'];
                    $langData['product_description_' . $langId] = $langContent['product_description'];
                }
            }
            $productReqRow = array_merge($productReqRow, $langData);
            $customProductFrm->fill($productReqRow);
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        unset($languages[$siteDefaultLangId]);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('otherLanguages', $languages);
        $this->set('customProductFrm', $customProductFrm);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $preq_user_id = FatApp::getPostedData('product_seller_id', FatUtility::VAR_INT, 0);

        $frm = $this->getCustomProductIntialSetUpFrm(0, $preqId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($post['product_brand_id'] < 1 && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Brand_From_List', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($post['ptc_prodcat_id'] < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Category_From_List', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (FatUtility::int($post['ptt_taxcat_id_rent']) < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Choose_Tax_Category_From_List', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = array();
        if ($preqId > 0) {
            $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_status', 'preq_content'));
            $prodContent = json_decode($productRow['preq_content'], true);
        }

        $preqProdCatId = FatUtility::int($post['ptc_prodcat_id']);
        $preqTaxCatId = FatUtility::int($post['ptt_taxcat_id']);
        $preqTaxCatIdRent = FatUtility::int($post['ptt_taxcat_id_rent']);
        $autoUpdateOtherLangsData = isset($post['auto_update_other_langs_data']) ? FatUtility::int($post['auto_update_other_langs_data']) : 0;
        $prodName = $post['product_name'];
        $prodYouTubeUrl = $post['product_youtube_video'];
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $data) {
            $prodDesc[$langId] = $post['product_description_' . $langId];
            unset($post['product_description_' . $langId]);
        }
        unset($post['preq_id']);
        unset($post['ptc_prodcat_id']);
        unset($post['ptt_taxcat_id']);
        unset($post['ptt_taxcat_id_rent']);
        unset($post['ptc_prodcat_id']);
        unset($post['product_name']);
        unset($post['product_youtube_video']);
        unset($post['btn_submit']);
        unset($post['auto_update_other_langs_data']);

        $dataForSave = array_merge($prodContent, $post);
        $dataForSave['preq_prodcat_id'] = $preqProdCatId;
        $dataForSave['product_added_by_admin_id'] = 0;
        $dataForSave['product_seller_id'] = $preq_user_id;
        $data = array(
            'preq_prodcat_id' => $preqProdCatId,
            'preq_taxcat_id' => $preqTaxCatId,
            'preq_taxcat_id_rent' => $preqTaxCatIdRent,
            'preq_content' => FatUtility::convertToJson($dataForSave),
            'preq_status' => ProductRequest::STATUS_PENDING,
            'preq_added_on' => date('Y-m-d H:i:s')
        );
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $prodReqObj = new ProductRequest($preqId);
        if (!$prodReqObj->saveProductRequestLangData($siteDefaultLangId, $autoUpdateOtherLangsData, $prodName, $prodDesc, $prodYouTubeUrl)) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $pcObj = new ProductCategory($preqProdCatId);
        $this->set('isCustomFields', $pcObj->isCategoryHasCustomFields($this->adminLangId));
        $this->set('status', 1);
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->set('productType', $post['product_type']);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function sellerProductForm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $productReqRow = ProductRequest::getAttributesById($preqId);
        if (!$productReqRow) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $productReqRow = array_merge($productReqRow, json_decode($productReqRow['preq_content'], true));

        if ($productReqRow['preq_sel_prod_data'] != '') {
            $productReqRow = array_merge($productReqRow, json_decode($productReqRow['preq_sel_prod_data'], true));
        }

        $productOptions = !empty($productReqRow['product_option']) ? $productReqRow['product_option'] : array();
        $preq_user_id = $productReqRow['preq_user_id'];

        $frmSellerProduct = $this->getSellerProductForm($preqId, 'REQUESTED_CATALOG_PRODUCT');
        $frmSellerProduct->fill($productReqRow);

        $this->set('preqId', $preqId);
        $this->set('preq_user_id', $preq_user_id);
        $this->set('productReqRow', $productReqRow);
        $this->set('productOptions', $productOptions);
        $this->set('frmSellerProduct', $frmSellerProduct);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setupSellerProduct()
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preqId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        if (!$preqId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        $post['use_shop_policy'] = $useShopPolicy;

        $frm = $this->getSellerProductForm($preqId, 'REQUESTED_CATALOG_PRODUCT');
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($post['btn_cancel']);
        unset($post['btn_submit']);

        $prodReqObj = new ProductRequest($preqId);
        $data = array(
            'preq_sel_prod_data' => FatUtility::convertToJson($post),
        );
        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            Message::addErrorMessage($prodReqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* Specification Module [ */

    /* public function specificationForm($preqId = 0, $prodspecId = 0)
      {
      $preqId = FatUtility::int($preqId);
      $productOptions = array();
      $productRow = array();

      if ($preqId) {
      $productRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_prodcat_id', 'preq_content', 'preq_specifications'));
      $preqCatId = $productRow['preq_prodcat_id'];
      $productReqData = json_decode($productRow['preq_content'], true);
      // CommonHelper::printArray($productRow);
      $productOptions = !empty($productReqData['product_option']) ? $productReqData['product_option'] : [];
      }
      $productSpecData = !empty($productRow['preq_specifications']) ? json_decode($productRow['preq_specifications'], true) : [];

      $this->set('productSpecifications', $productSpecData);
      $this->set('preqId', $preqId);
      $this->set('preqCatId', $preqCatId);
      $this->set('productOptions', $productOptions);
      $this->set('languages', Language::getAllNames());
      $this->_template->render(false, false);
      }
     */

    public function productAttributeAndSpecifications($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));


        $productFrm = $this->getProductAttributeAndSpecificationsFrm(0, $preqId);
        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productFrm->fill($preqContentData);
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);

        /* $languages = Language::getAllNames();
          unset($languages[$siteDefaultLangId]); */

        $this->set('productFrm', $productFrm);
        $this->set('productType', $preqContentData['product_type']);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        /* $this->set('otherLanguages', $languages); */
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function catalogProdSpecForm($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        $prodSpecData = array();
        if ($key >= 0) {
            $specificationsData = json_decode($productReqRow['preq_specifications'], true);
            $specifications = (isset($specificationsData['text_specification'])) ? $specificationsData['text_specification'] : [];
            foreach ($languages as $otherLangId => $langName) {
                $specName = (isset($specifications['prod_spec_name'][$otherLangId][$key])) ? $specifications['prod_spec_name'][$otherLangId][$key] : "";
                $specValue = (isset($specifications['prod_spec_value'][$otherLangId][$key])) ? $specifications['prod_spec_value'][$otherLangId][$key] : "";
                $specGroup = (isset($specifications['prod_spec_group'][$key])) ? $specifications['prod_spec_group'][$key] : "";
                $specIdentifier = (isset($specifications['prodspec_identifier'][$key])) ? $specifications['prodspec_identifier'][$key] : "";
                
                $prodSpecData['prod_spec_name'][$otherLangId] = $specName;
                $prodSpecData['prod_spec_value'][$otherLangId] = $specValue;
                $prodSpecData['prod_spec_group'] = $specGroup;
                $prodSpecData['prodspec_identifier'] = $specIdentifier;
                $prodSpecData['key'][$otherLangId] = $key;
            }
        }
        
        unset($languages[$siteDefaultLangId]);
        $this->set('otherLanguages', $languages);
        $this->set('langId', $langId);
        $this->set('prodSpecData', $prodSpecData);
        $this->set('siteDefaultLangId', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $this->_template->render(false, false);
    }

    public function catalogSpecificationsByLangId()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        /* $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0); */
        $langId = $this->adminLangId;
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);
        $productSpecifications = array();
        $specificationsData = json_decode($productReqRow['preq_specifications'], true);
        $specifications = (isset($specificationsData['text_specification'])) ? $specificationsData['text_specification'] : [];
        if (!empty($specifications['prodspec_identifier'])) {
            $namesArr = $specifications['prod_spec_name'];
            $valuesArr = $specifications['prod_spec_value'];
            $groupArr = $specifications['prod_spec_group'];
            foreach($specifications['prodspec_identifier'] as $key => $identifier) {
                $productSpecifications[$key]['identifier'] = $identifier;
                $productSpecifications[$key]['prod_spec_name'] = (isset($namesArr[$langId][$key])) ? $namesArr[$langId][$key] : $identifier;
                $productSpecifications[$key]['prod_spec_value'] = (isset($valuesArr[$langId][$key])) ? $valuesArr[$langId][$key] : "";
                $productSpecifications[$key]['prod_spec_group'] = (isset($groupArr[$key])) ? $groupArr[$key] : "";
            }
        }
        
        $this->set('productSpecifications', $productSpecifications);
        $this->set('langId', FatApp::getPostedData('langId', FatUtility::VAR_INT, 0));
        $this->set('siteDefaultLang', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $this->_template->render(false, false, 'custom-products/catalog-specifications.php');
    }

    public function setUpCustomCatalogSpecifications()
    {
        $post = FatApp::getPostedData();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        $prodSpecGroup = FatApp::getPostedData('prodspec_group', FatUtility::VAR_STRING, '');
        $autoCompleteLangData = FatApp::getPostedData('autocomplete_lang_data', FatUtility::VAR_INT, 0);
        $isFileForm = FatApp::getPostedData('isFileForm', FatUtility::VAR_INT, 0);
        $fileIndex = FatApp::getPostedData('prod_spec_file_index', FatUtility::VAR_INT, 0);
        $prodspecIdentifier = FatApp::getPostedData('prodspec_identifier', FatUtility::VAR_STRING, '');

        if ($langId < 1 || (!isset($post['prodspec_name'][$langId]) || empty($post['prodspec_name'][$langId])) || ((!isset($post['prodspec_value'][$langId]) || empty($post['prodspec_value'][$langId])) && $isFileForm == 0)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $prodReqSpecificationData = json_decode($prodReqData['preq_specifications'], true);
        $textSpecifications = (isset($prodReqSpecificationData['text_specification'])) ? $prodReqSpecificationData['text_specification'] : [];
        $mediaSpecifications = (isset($prodReqSpecificationData['media_specification'])) ? $prodReqSpecificationData['media_specification'] : [];
        
        $prodReqSpecification = $textSpecifications;
        if ($isFileForm) {
            $prodReqSpecification = $mediaSpecifications;
        }
        $dataToTranslate = [
            'prod_spec_name' => $post['prodspec_name'][$langId],
            'prod_spec_value' => (isset($post['prodspec_value'][$langId])) ? $post['prodspec_value'][$langId] : "",
            'prod_spec_group' => $prodSpecGroup,
            'prod_spec_is_file' => $isFileForm,
            'prod_spec_file_index' => $fileIndex,
        ];
        $newkey = $key;
        if ($key >= 0) {
            $prodReqSpecification['prodspec_identifier'][$key] = $prodspecIdentifier;
            $prodReqSpecification['prod_spec_name'][$langId][$key] = $post['prodspec_name'][$langId];
            if ($isFileForm) {
                $prodReqSpecification['prod_spec_file_index'][$langId][$key] = $fileIndex;
            } else {
                $prodReqSpecification['prod_spec_value'][$langId][$key] = (isset($post['prodspec_value'][$langId])) ? $post['prodspec_value'][$langId] : "";
                $prodReqSpecification['prod_spec_group'][$key] = $prodSpecGroup;
            }
        } else {
            $prodReqSpecification['prodspec_identifier'][] = $prodspecIdentifier;
            $prodReqSpecification['prod_spec_name'][$langId][] = $post['prodspec_name'][$langId];
            if ($isFileForm) {
                $prodReqSpecification['prod_spec_file_index'][$langId][] = $fileIndex;
            } else {
                $prodReqSpecification['prod_spec_value'][$langId][] = (isset($post['prodspec_value'][$langId])) ? $post['prodspec_value'][$langId] : "";
                $prodReqSpecification['prod_spec_group'][] = $prodSpecGroup;
            }
        }

        /* [ AUTO TRANSLATE THE LANGUGAE DATA */
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        if ($langId == $siteDefaultLangId) {
            $prodReqSpecification = $this->updateAutoTranslateData($preqId, $prodReqSpecification, $dataToTranslate, $key, $post, $autoCompleteLangData);
        }
        /* ] */

        $specificationType = "text_specification";
        if ($isFileForm) {
            $dataToConvert = [
                'text_specification' => $textSpecifications,
                'media_specification' => $prodReqSpecification
            ];
        } else {
            $dataToConvert = [
                'text_specification' => $prodReqSpecification,
                'media_specification' => $mediaSpecifications
            ];
        }
        
        $data['preq_specifications'] = FatUtility::convertToJson($dataToConvert);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Specification_updated_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteCustomCatalogSpecification($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        if ($langId < 1 || $key < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodReqSpecification = json_decode($prodReqData['preq_specifications'], true);
        $languages = Language::getAllNames();
        $fileKey = $prodReqSpecification['prod_spec_file_index'][$langId][$key];
        foreach ($languages as $otherLangId => $langName) {
            unset($prodReqSpecification['prod_spec_name'][$otherLangId][$key]);
            unset($prodReqSpecification['prod_spec_value'][$otherLangId][$key]);
            unset($prodReqSpecification['prod_spec_group'][$otherLangId][$key]);
            unset($prodReqSpecification['prod_spec_is_file'][$otherLangId][$key]);
            unset($prodReqSpecification['prod_spec_file_index'][$otherLangId][$key]);
            unset($prodReqSpecification['prod_spec_identifier'][$otherLangId][$key]);
            $prodReqSpecification['prod_spec_name'][$otherLangId] = array_values($prodReqSpecification['prod_spec_name'][$otherLangId]);
            $prodReqSpecification['prod_spec_value'][$otherLangId] = array_values($prodReqSpecification['prod_spec_value'][$otherLangId]);
            $prodReqSpecification['prod_spec_group'][$otherLangId] = array_values($prodReqSpecification['prod_spec_group'][$otherLangId]);
            $prodReqSpecification['prod_spec_is_file'][$otherLangId] = array_values($prodReqSpecification['prod_spec_is_file'][$otherLangId]);
            $prodReqSpecification['prod_spec_file_index'][$otherLangId] = array_values($prodReqSpecification['prod_spec_file_index'][$otherLangId]);
            $prodReqSpecification['prod_spec_identifier'][$otherLangId] = array_values($prodReqSpecification['prod_spec_identifier'][$otherLangId]);
        }

        $data['preq_specifications'] = FatUtility::convertToJson($prodReqSpecification);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* [ DELETE UPLOADED FILE */
        $this->deleteCatalogSpecFile($preqId, $fileKey);
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Specification_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateAutoTranslateData(int $preqId, array $prodReqSpecification, array $dataToTranslate, $key = '', array $post, int $autoCompleteLangData = 0)
    {
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        $preqObj = new ProductRequest($preqId);
        foreach ($languages as $toTransLangId => $langName) {
            if ((isset($post['prodspec_name'][$toTransLangId]) && !empty($post['prodspec_name'][$toTransLangId])) || (isset($post['prodspec_value'][$toTransLangId]) && !empty($post['prodspec_value'][$toTransLangId]) )) {
                if ($key >= 0) {
                    $prodReqSpecification['prod_spec_name'][$toTransLangId][$key] = (isset($post['prodspec_name'][$toTransLangId])) ? $post['prodspec_name'][$toTransLangId] : "";
                    $prodReqSpecification['prod_spec_value'][$toTransLangId][$key] = (isset($post['prodspec_value'][$toTransLangId])) ? $post['prodspec_value'][$toTransLangId] : "";
                    $prodReqSpecification['prod_spec_group'][$toTransLangId][$key] = (isset($post['prodspec_group'])) ? $post['prodspec_group'] : 0;
                    $prodReqSpecification['prod_spec_is_file'][$toTransLangId][$key] = $dataToTranslate['prod_spec_is_file'];
                    $prodReqSpecification['prod_spec_file_index'][$toTransLangId][$key] = $dataToTranslate['prod_spec_file_index'];
                } else {
                    $prodReqSpecification['prod_spec_name'][$toTransLangId][] = (isset($post['prodspec_name'][$toTransLangId])) ? $post['prodspec_name'][$toTransLangId] : "";
                    $prodReqSpecification['prod_spec_value'][$toTransLangId][] = (isset($post['prodspec_value'][$toTransLangId])) ? $post['prodspec_value'][$toTransLangId] : "";
                    $prodReqSpecification['prod_spec_group'][$toTransLangId][] = (isset($post['prodspec_group'])) ? $post['prodspec_group'] : 0;
                    $prodReqSpecification['prod_spec_is_file'][$toTransLangId][] = $dataToTranslate['prod_spec_is_file'];
                    $prodReqSpecification['prod_spec_file_index'][$toTransLangId][] = $dataToTranslate['prod_spec_file_index'];
                }
            } elseif ($autoCompleteLangData) {
                $translatedData = $preqObj->getTranslatedProductSpecData($dataToTranslate, $toTransLangId);
                if (!empty($translatedData)) {
                    if ($key >= 0) {
                        $prodReqSpecification['prod_spec_name'][$toTransLangId][$key] = $translatedData[$toTransLangId]['prod_spec_name'];
                        $prodReqSpecification['prod_spec_value'][$toTransLangId][$key] = $translatedData[$toTransLangId]['prod_spec_value'];
                        $prodReqSpecification['prod_spec_group'][$toTransLangId][$key] = $translatedData[$toTransLangId]['prod_spec_group'];
                        $prodReqSpecification['prod_spec_is_file'][$toTransLangId][$key] = $dataToTranslate['prod_spec_is_file'];
                        $prodReqSpecification['prod_spec_file_index'][$toTransLangId][$key] = $dataToTranslate['prod_spec_file_index'];
                    } else {
                        $prodReqSpecification['prod_spec_name'][$toTransLangId][] = $translatedData[$toTransLangId]['prod_spec_name'];
                        $prodReqSpecification['prod_spec_value'][$toTransLangId][] = $translatedData[$toTransLangId]['prod_spec_value'];
                        $prodReqSpecification['prod_spec_group'][$toTransLangId][] = $translatedData[$toTransLangId]['prod_spec_group'];
                        $prodReqSpecification['prod_spec_is_file'][$toTransLangId][] = $dataToTranslate['prod_spec_is_file'];
                        $prodReqSpecification['prod_spec_file_index'][$toTransLangId][] = $dataToTranslate['prod_spec_file_index'];
                    }
                }
            }
        }
        return $prodReqSpecification;
    }

    public function getSpecificationForm($preqId, $prodspecId = 0, $divCount = 0)
    {
        $post = FatApp::getPostedData();
        $data = array();
        $data['product_id'] = $preqId;
        $data['prodspec_id'] = $prodspecId;
        $this->set('adminLangId', $this->adminLangId);
        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preqId);
        $this->set('divCount', $divCount);
        $this->_template->render(false, false);
    }

    public function setupSpecification($preqId, $prodSpecId = 0)
    {
        $preqId = FatUtility::int($preqId);

        $post = FatApp::getPostedData();
        if (false === $post) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Please_fill_Specifications', $this->adminLangId));
        }

        $languages = Language::getAllNames();
        foreach ($post['prod_spec_name'][CommonHelper::getLangId()] as $specKey => $specval) {
            $count = 0;
            foreach ($languages as $langId => $langName) {
                if ($post['prod_spec_name'][$langId][$specKey] == '') {
                    $count++;
                }

                if ($count == count($languages)) {
                    foreach ($languages as $langId => $langName) {
                        unset($post['prod_spec_name'][$langId][$specKey]);
                        unset($post['prod_spec_value'][$langId][$specKey]);
                    }
                }
            }
        }

        unset($post['btn_submit']);
        unset($post['fOutMode']);
        unset($post['fIsAjax']);
        $prodReqObj = new ProductRequest($preqId);
        $data = array(
            'preq_specifications' => FatUtility::convertToJson($post)
        );

        $prodReqObj->assignValues($data);

        if (!$prodReqObj->save()) {
            FatUtility::dieWithError($prodReqObj->getError());
        }
        $languages = Language::getAllNames();
        reset($languages);
        $nextLangId = key($languages);

        $preqId = $prodReqObj->getMainTableRecordId();
        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->adminLangId));
        $this->set('preqId', $preqId);
        $this->set('lang_id', $nextLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /* ] */

    public function setUpCatalogProductAttributes()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductAttributeAndSpecificationsFrm(0, $preqId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productData = ProductRequest::getAttributesById($preqId);

        unset($post['preq_id']);
        unset($post['btn_submit']);
        $prodContent = json_decode($productData['preq_content'], true);
        $data['preq_content'] = FatUtility::convertToJson(array_merge($prodContent, $post));
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Attributes_Setup_Successful', $this->adminLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogShippingFrm($preqId)
    {

        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));

        $productFrm = $this->getProductShippingFrm(0, $preqId);
        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productFrm->fill($preqContentData);

        $this->set('productFrm', $productFrm);
        $this->set('productType', $preqContentData['product_type']);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function setUpCustomCatalogShipping()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $frm = $this->getProductShippingFrm(0, $preqId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqData = ProductRequest::getAttributesById($preqId);

        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $post['ps_from_country_id'] = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        }

        unset($post['preq_id']);
        unset($post['product_id']);
        unset($post['btn_submit']);
        $prodContent = json_decode($productReqData['preq_content'], true);
        $prodContent = array_merge($prodContent, $post);
        /* $productShiping = FatApp::getPostedData('product_shipping');
          if (!empty($productShiping)) {
          $prodContent['product_shipping'] = $productShiping;
          } */
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodcat_id = $prodContent['preq_prodcat_id'];
        $isCustomFields = false;
        if (FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0)) {
            $pcObj = new ProductCategory($prodcat_id);
            $isCustomFields = $pcObj->isCategoryHasCustomFields($this->adminLangId);
        }
        $this->set('msg', Labels::getLabel('LBL_Product_Shipping_Setup_Successful', $this->adminLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->set('isUseCustomFields', $isCustomFields);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customCatalogOptionsAndTag($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));

        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $productOptions = array();
        if (!empty($preqContentData['product_option'])) {
            $srch = Option::getSearchObject($this->adminLangId);
            $srch->addMultipleFields(array('option_id, option_name, option_identifier'));
            $srch->addCondition('option_id', 'IN', $preqContentData['product_option']);
            $srch->addOrder('option_identifier');
            $rs = $srch->getResultSet();
            $productOptions = FatApp::getDb()->fetchAll($rs);
        }
        $productTags = array();
        if (!empty($preqContentData['product_tags'])) {
            $srch = Tag::getSearchObject();
            $srch->addOrder('tag_identifier');
            $srch->joinTable(
                    Tag::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->adminLangId
            );
            $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));
            $srch->addCondition('tag_id', 'IN', $preqContentData['product_tags']);

            $rs = $srch->getResultSet();
            $productTags = FatApp::getDb()->fetchAll($rs);
        }

        $this->set('productOptions', $productOptions);
        $this->set('productTags', $productTags);
        $this->set('preq_user_id', $productReqRow['preq_user_id']);
        $this->_template->addJs(array('js/tagify.min.js', 'js/tagify.polyfills.min.js', 'js/cropper.js', 'js/cropper-main.js'));
        $this->set('preqId', $preqId);
        $this->set('productType', $preqContentData['product_type']);
        $this->_template->render(false, false);
    }

    public function updateCustomCatalogOption()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $optionId = FatApp::getPostedData('option_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        if ($preqId < 1 || $optionId < 0) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);

        $separateImageOptionAdded = false;
        if (!empty($prodContent['product_option'])) {
            foreach ($prodContent['product_option'] as $option) {
                $optionWithImage = Option::getAttributesById($option, 'option_is_separate_images');
                if ($optionWithImage == 1) {
                    $separateImageOptionAdded = true;
                    break;
                }
            }
        }
        $optionSeparateImage = Option::getAttributesById($optionId, 'option_is_separate_images');
        if ($separateImageOptionAdded == true && $optionSeparateImage == 1) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_you_have_already_added_option_having_separate_image', $this->adminLangId));
        }


        $prodContent['product_option'][] = $optionId;
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            FatUtility::dieJsonError($prodReq->getError());
        }
        $this->set('msg', Labels::getLabel('LBL_Option_updated_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCustomCatalogOption()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $optionId = FatApp::getPostedData('option_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        if ($preqId < 1 || $optionId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $key = array_search($optionId, $prodContent['product_option']);
        unset($prodContent['product_option'][$key]);
        $prodContent['product_option'] = array_values($prodContent['product_option']);
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Option_removed_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function langForm($preq_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();

        $preq_id = FatUtility::int($preq_id);
        $lang_id = FatUtility::int($lang_id);

        if ($preq_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $customProductLangFrm = $this->getLangForm($preq_id, $lang_id);

        $prodObj = new ProductRequest($preq_id);

        if (0 < $autoFillLangData) {
            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            $customProductLangData = $prodObj->getAttributesByLangId($siteDefaultLangId, $preq_id);
        } else {
            $customProductLangData = $prodObj->getAttributesByLangId($lang_id, $preq_id);
        }

        if ($customProductLangData) {
            $customProductLangData['preq_id'] = $preq_id;
            $productData = json_decode($customProductLangData['preq_lang_data'], true);

            unset($customProductLangData['preq_lang_data']);

            if (0 < $autoFillLangData) {
                $updateLangDataobj = new TranslateLangData(ProductRequest::DB_TBL_LANG);
                $translatedData = $updateLangDataobj->directTranslate($productData, $lang_id);
                if (false === $translatedData) {
                    Message::addErrorMessage($updateLangDataobj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
                $productData = current($translatedData);
            }

            if (is_array($productData)) {
                $customProductLangData = array_merge($customProductLangData, $productData);
            }
        }

        $customProductLangFrm->fill($customProductLangData);

        $row_data = ProductRequest::getAttributesById($preq_id, array('preq_content'));
        $productData = json_decode($row_data['preq_content'], true);
        $row_data = array_merge($row_data, $productData);
        $productOptions = !empty($row_data['product_option']) ? $row_data['product_option'] : array();

        $customProductLangData['preq_id'] = $preq_id;

        $this->set('languages', Language::getAllNames());
        $this->set('preqId', $preq_id);
        $this->set('productOptions', $productOptions);
        $this->set('product_lang_id', $lang_id);
        $this->set('customProductLangFrm', $customProductLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function langSetup()
    {
        $this->objPrivilege->canEditCustomProductRequests();

        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $preq_id = FatUtility::int($post['preq_id']);

        if ($preq_id == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getLangForm($preq_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        unset($post['preq_id']);
        unset($post['lang_id']);
        unset($post['btn_submit']);
        if (array_key_exists('auto_update_other_langs_data', $post)) {
            unset($post['auto_update_other_langs_data']);
        }
        $data_to_update = array(
            'preqlang_preq_id' => $preq_id,
            'preqlang_lang_id' => $lang_id,
            'preq_lang_data' => FatUtility::convertToJson($post),
        );

        $prodObj = new ProductRequest($preq_id);
        if (!$prodObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($prodObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
          if (0 < $autoUpdateOtherLangsData) {
          $updateLangDataobj = new TranslateLangData(ProductRequest::DB_TBL_LANG);
          if (false === $updateLangDataobj->updateTranslatedData($preq_id)) {
          Message::addErrorMessage($updateLangDataobj->getError());
          FatUtility::dieWithError(Message::getHtml());
          }
          } */

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = ProductRequest::getAttributesByLangId($langId, $preq_id)) {
                $newTabLangId = $langId;
                break;
            }
        }
        $data = ProductRequest::getAttributesById($preq_id, array('preq_content'));
        $productData = json_decode($data['preq_content'], true);
        $productOptions = !empty($productData['product_option']) ? $productData['product_option'] : array();

        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->set('preq_id', $preq_id);
        $this->set('productOptions', $productOptions);
        $this->set('lang_id', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function updateStatusForm($preqId = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        if (!$preqId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = ProductRequest::getAttributesById($preqId, array('preq_id,preq_content'));

        $productData = json_decode($data['preq_content'], true);
        $productOptions = !empty($productData['product_option']) ? $productData['product_option'] : array();

        $frm = $this->getStatusForm();
        $frm->fill($data);

        $this->set('frm', $frm);
        $this->set('preqId', $preqId);
        $this->set('productOptions', $productOptions);
        $this->set('languages', Language::getAllNames());
        $this->set('formLayout', Language::getLayoutDirection($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function updateStatus()
    {
        $this->objPrivilege->canEditCustomProductRequests();

        $frm = $this->getStatusForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }
        $preqId = $post['preq_id'];
        $status = $post['preq_status'];
        //$update_withselprod = $post['preq_update_withselprod'];
        $update_withselprod = 0;

        $srch = ProductRequest::getSearchObject($this->adminLangId);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = preq.preq_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'c.credential_user_id = u.user_id', 'c');
        $srch->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', Shop::DB_TBL_PREFIX . 'user_id = u.user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $this->adminLangId, 's_l');
        $srch->addCondition('preq_id', '=', $preqId);
        $srch->addMultipleFields(array('preq.*', 'user_id', 'user_name', 'credential_email', 'user_dial_code', 'user_phone', 'ifnull(shop_name, shop_identifier) as shop_name'));
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetch($rs);

        if ($data == false || $data['preq_deleted'] == applicationConstants::YES || $data['preq_status'] == ProductRequest::STATUS_APPROVED) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($status != ProductRequest::STATUS_APPROVED && $status != ProductRequest::STATUS_CANCELLED) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $upcCodeData = array();
        if (isset($data['preq_ean_upc_code'])) {
            $upcCodeData = json_decode($data['preq_ean_upc_code'], true);
        }
        if (!empty($upcCodeData)) {
            $srch = UpcCode::getSearchObject();
            $srch->addCondition('upc_code', 'IN', $upcCodeData);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);

            if (!empty($row)) {
                Message::addErrorMessage(Labels::getLabel('MSG_UPC/EAN_code_already_assigned_to_another_product', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $db = FatApp::getDb();
        $db->startTransaction();
        $prodReqObj = new ProductRequest($preqId);
        $updateData = array('preq_status' => $status, 'preq_comment' => $post['preq_comment'], 'preq_status_updated_on' => date('Y-m-d H:i:s'));
        $prodReqObj->assignValues($updateData);

        if (!$prodReqObj->save()) {
            Message::addErrorMessage($prodReqObj->getError());
            $db->rollbackTransaction();
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($status == ProductRequest::STATUS_APPROVED) {
            $data = array_merge($data, json_decode($data['preq_content'], true));
            $prodObj = new Product();
            $productData = array(
                'product_identifier' => isset($data['product_identifier']) ? $data['product_identifier'] : '',
                'product_type' => isset($data['product_type']) ? $data['product_type'] : '',
                'product_model' => isset($data['product_model']) ? $data['product_model'] : '',
                'product_brand_id' => isset($data['product_brand_id']) ? $data['product_brand_id'] : 0,
                'product_added_by_admin_id' => applicationConstants::YES,
                /* 'product_seller_id'=>isset($data['preq_user_id'])?$data['preq_user_id']:0, */
                'product_min_selling_price' => isset($data['product_min_selling_price']) ? $data['product_min_selling_price'] : 0,
                'product_length' => isset($data['product_length']) ? $data['product_length'] : 0,
                'product_width' => isset($data['product_width']) ? $data['product_width'] : 0,
                'product_height' => isset($data['product_height']) ? $data['product_height'] : 0,
                'product_dimension_unit' => isset($data['product_dimension_unit']) ? $data['product_dimension_unit'] : 0,
                'product_weight' => isset($data['product_weight']) ? $data['product_weight'] : 0,
                'product_weight_unit' => isset($data['product_weight_unit']) ? $data['product_weight_unit'] : 0,
                'product_cod_enabled' => isset($data['product_cod_enabled']) ? $data['product_cod_enabled'] : 0,
                'product_ship_free' => isset($data['ps_free']) ? $data['ps_free'] : 0,
                'product_ship_country' => isset($data['ps_from_country_id']) ? $data['ps_from_country_id'] : 0,
                'product_ship_package' => isset($data['product_ship_package']) ? $data['product_ship_package'] : 0,
                'product_added_on' => date('Y-m-d H:i:s'),
                'product_featured' => isset($data['product_featured']) ? $data['product_featured'] : applicationConstants::NO,
                'product_upc' => isset($data['product_upc']) ? $data['product_upc'] : applicationConstants::NO,
                'product_active' => applicationConstants::YES,
                'product_approved' => applicationConstants::YES,
            );

            $prodObj->assignValues($productData);
            if (!$prodObj->save()) {
                Message::addErrorMessage($prodObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }

            $product_id = $prodObj->getMainTableRecordId();

            if (isset($data['shipping_profile']) && $data['shipping_profile'] > 0) {
                $shipProProdData = array(
                    'shippro_shipprofile_id' => $data['shipping_profile'],
                    'shippro_product_id' => $product_id,
                    'shippro_user_id' => 0
                );
                $spObj = new ShippingProfileProduct();
                if (!$spObj->addProduct($shipProProdData)) {
                    Message::addErrorMessage($spObj->getError());
                    FatUtility::dieWithError(Message::getHtml());
                }
            }

            $prodSepc = [
                'ps_product_id' => $product_id,
                'product_warranty' => isset($data['product_warranty']) ? $data['product_warranty'] : 0
            ];

            $productSpecificsObj = new ProductSpecifics($product_id);
            $productSpecificsObj->assignValues($prodSepc);
            if (!$productSpecificsObj->addNew(array(), $prodSepc)) {
                Message::addErrorMessage($productSpecificsObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }

            /* saving of product categories[ */
            $product_categories = array($data['preq_prodcat_id']);
            if (!$prodObj->addUpdateProductCategories($product_id, $product_categories)) {
                Message::addErrorMessage($prodObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            /* Save Prodcut tax category [ */
            $prodTaxData = array(
                'ptt_product_id' => $product_id,
                'ptt_taxcat_id' => $data['preq_taxcat_id'],
                'ptt_taxcat_id_rent' => $data['preq_taxcat_id_rent'],
                'ptt_type' => SellerProduct::PRODUCT_TYPE_PRODUCT,
            );
            $taxObj = new Tax();
            if (!$taxObj->addUpdateProductTaxCat($prodTaxData)) {
                Message::addErrorMessage($taxObj->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            /* saving of product options[ */
            $optons = isset($data['product_option']) ? $data['product_option'] : array();
            if (!empty($optons)) {
                foreach ($optons as $option_id) {
                    if (!$prodObj->addUpdateProductOption($option_id)) {
                        Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
            /* ] */

            /* Saving of product tags[ */
            $tags = isset($data['product_tags']) ? $data['product_tags'] : array();
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    if (!$prodObj->addUpdateProductTag($tag_id)) {
                        Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }
            /* ] */

            /* Update Product seller shipping [ */
            $prodSellerShipArr = array(
                'ps_from_country_id' => $productData['product_ship_country'],
                'ps_free' => $productData['product_ship_free']
            );

            if (!Product::addUpdateProductSellerShipping($product_id, $prodSellerShipArr, 0)) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                $db->rollbackTransaction();
                FatUtility::dieWithError(Message::getHtml());
            }
            /* ] */

            /* Saving product shippings [ */
            $shippingArr = isset($data['product_shipping']) ? $data['product_shipping'] : array();
            if (!empty($shippingArr)) {
                if (!Product::addUpdateProductShippingRates($product_id, $shippingArr, 0)) {
                    Message::addErrorMessage(FatApp::getDb()->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
            /* ] */

            /* Product Lang data insert[ */
            $languages = Language::getAllNames();
            foreach ($languages as $lang_id => $langName) {
                $reqLangData = ProductRequest::getAttributesByLangId($lang_id, $preqId);
                if ($reqLangData == false) {
                    continue;
                }

                $arr = json_decode($reqLangData['preq_lang_data'], true);
                if (!empty($arr)) {
                    $reqLangData = array_merge($reqLangData, json_decode($reqLangData['preq_lang_data'], true));
                }

                $productLangData = array(
                    'productlang_product_id' => $product_id,
                    'productlang_lang_id' => $lang_id,
                    'product_name' => isset($reqLangData['product_name']) ? $reqLangData['product_name'] : $data['product_identifier'],
                    'product_description' => isset($reqLangData['product_description']) ? $reqLangData['product_description'] : '',
                    'product_youtube_video' => isset($reqLangData['product_youtube_video']) ? $reqLangData['product_youtube_video'] : '',
                    'product_tags_string' => '',
                );
                if (!$prodObj->updateLangData($lang_id, $productLangData)) {
                    Message::addErrorMessage($prodObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
            /* ] */

            /* [ Saving product UPC/EAN/ISBN */
            $srch = UpcCode::getSearchObject();
            $srch->addCondition('upc_product_id', '!=', $product_id);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(1);
            if (!empty($upcCodeData)) {
                foreach ($upcCodeData as $key => $code) {
                    if (trim($code) == '') {
                        continue;
                    }

                    $options = str_replace('|', ',', $key);

                    $rSrch = clone $srch;
                    $rSrch->addCondition('upc_code', '=', $code);
                    $rs = $rSrch->getResultSet();
                    $totalRecords = FatApp::getDb()->totalRecords($rs);
                    if ($totalRecords > 0) {
                        continue;
                    }

                    $optionSrch = clone $srch;
                    $optionSrch->addCondition('upc_options', '=', $options);
                    $rs = $optionSrch->getResultSet();
                    $row = FatApp::getDb()->fetch($rs);

                    $upcData = array(
                        'upc_code' => $code,
                        'upc_product_id' => $product_id,
                        'upc_options' => $options,
                    );

                    if ($row && $row['upc_product_id'] == $product_id && $row['upc_options'] == $options) {
                        $upcObj = new UpcCode($row['upc_code_id']);
                    } else {
                        $upcObj = new UpcCode();
                    }

                    $upcObj->assignValues($upcData);
                    if (!$upcObj->save()) {
                        Message::addErrorMessage($upcObj->getError());
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }
            }

            /* ] */

            Tag::updateProductTagString($product_id);

            /* BOC to update custom fields data of product */
            if (!empty($data['preq_custom_fields'])) {
                $customFieldsData = json_decode($data['preq_custom_fields'], true);
                if (!empty($customFieldsData['num_attributes'])) {
                    foreach ($customFieldsData['num_attributes'] as $key => $attributes) {
                        foreach ($attributes as $numericKey => $attr) {
                            $num_data_update_arr = array(
                                'prodnumattr_product_id' => $product_id,
                                'prodnumattr_attrgrp_id' => $key,
                                $numericKey => (is_array($attr)) ? implode(',', $attr) : $attr,
                            );
                            if (!$prodObj->addUpdateNumericAttributes($num_data_update_arr)) {
                                Message::addErrorMessage($prodObj->getError());
                                FatUtility::dieWithError(Message::getHtml());
                            }
                        }
                    }
                }

                if (!empty($customFieldsData['text_attributes'])) {
                    foreach ($customFieldsData['text_attributes'] as $key => $textAttributes) {
                        foreach ($textAttributes as $langId => $attributes) {
                            $text_data_update = array(
                                'prodtxtattr_product_id' => $product_id,
                                'prodtxtattr_attrgrp_id' => $key,
                                'prodtxtattr_lang_id' => $langId,
                            );
                            $text_data_update = array_merge($text_data_update, $attributes);

                            if (!$prodObj->addUpdateTextualAttributes($text_data_update)) {
                                Message::addErrorMessage($prodObj->getError());
                                FatUtility::dieWithError(Message::getHtml());
                            }
                        }
                    }
                }
            }
            /* EOC to update custom fields data of product */

            /* Updating images [ */
            $where = array('smt' => 'afile_record_id = ? and afile_type = ?', 'vals' => array($preqId, AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE));
            $db->updateFromArray(AttachedFile::DB_TBL, array('afile_record_id' => $product_id, 'afile_type' => AttachedFile::FILETYPE_PRODUCT_IMAGE), $where);
            /* ] */


            /* [ UPDATE SIZE CHART */
            $where = array('smt' => 'afile_record_id = ? and afile_type = ?', 'vals' => array($preqId, AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART));
            $db->updateFromArray(AttachedFile::DB_TBL, array('afile_record_id' => $product_id, 'afile_type' => AttachedFile::FILETYPE_PRODUCT_SIZE_CHART), $where);
            /* ] */

            $selProdData = isset($data['preq_sel_prod_data']) ? json_decode($data['preq_sel_prod_data'], true) : array();
            if ($update_withselprod && !empty($selProdData)) {
                $updateSelProdData = array(
                    'selprod_user_id' => isset($selProdData['preq_user_id']) ? $selProdData['preq_user_id'] : $data['preq_user_id'],
                    'selprod_product_id' => $product_id,
                    'selprod_cost' => isset($selProdData['selprod_cost']) ? $selProdData['selprod_cost'] : 0,
                    'selprod_price' => isset($selProdData['selprod_price']) ? $selProdData['selprod_price'] : 0,
                    'selprod_stock' => isset($selProdData['selprod_stock']) ? $selProdData['selprod_stock'] : 0,
                    'selprod_min_order_qty' => isset($selProdData['selprod_min_order_qty']) ? $selProdData['selprod_min_order_qty'] : 0,
                    /* 'selprod_max_order_qty'=>isset($selProdData['selprod_min_order_qty'])?$selProdData['selprod_max_order_qty']:0, */
                    'selprod_subtract_stock' => isset($selProdData['selprod_subtract_stock']) ? $selProdData['selprod_subtract_stock'] : 0,
                    'selprod_track_inventory' => isset($selProdData['selprod_track_inventory']) ? $selProdData['selprod_track_inventory'] : 0,
                    'selprod_sku' => isset($selProdData['selprod_sku']) ? $selProdData['selprod_sku'] : '',
                    'selprod_condition' => isset($selProdData['selprod_condition']) ? $selProdData['selprod_condition'] : Product::CONDITION_NEW,
                    'selprod_available_from' => isset($selProdData['selprod_available_from']) ? $selProdData['selprod_available_from'] : '',
                    'selprod_active' => isset($selProdData['selprod_active']) ? $selProdData['selprod_active'] : '',
                    'selprod_cod_enabled' => isset($selProdData['selprod_cod_enabled']) ? $selProdData['selprod_cod_enabled'] : '',
                );

                $options = array();

                $optionValueIdArr = (isset($selProdData['selprodoption_optionvalue_id']) && count($selProdData['selprodoption_optionvalue_id']) > 0) ? $selProdData['selprodoption_optionvalue_id'] : array();
                foreach ($optionValueIdArr as $optionValueId) {
                    $row = OptionValue::getAttributesById($optionValueId, array('optionvalue_option_id'));
                    if ($row == false) {
                        continue;
                    }
                    $options[$row['optionvalue_option_id']] = $optionValueId;
                }

                asort($options);


                $selProdCode = $product_id . '_' . implode('_', $options);
                $updateSelProdData['selprod_code'] = $selProdCode;

                if (isset($data['selprod_track_inventory']) && $data['selprod_track_inventory'] == Product::INVENTORY_NOT_TRACK) {
                    $updateSelProdData['selprod_threshold_stock_level'] = 0;
                }

                $sellerProdObj = new SellerProduct();
                $sellerProdObj->assignValues($updateSelProdData);
                if (!$sellerProdObj->save()) {
                    Message::addErrorMessage($sellerProdObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
                $selprod_id = $sellerProdObj->getMainTableRecordId();

                if (!empty($selprod_id)) {
                    $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
                    $selProdSepc = [
                        'sps_selprod_id' => $selprod_id,
                        'selprod_return_age' => $selProdData['selprod_return_age'],
                        'selprod_cancellation_age' => $selProdData['selprod_cancellation_age'],
                    ];
                    $selProdSpecificsObj->assignValues($selProdSepc);
                    if (!$selProdSpecificsObj->addNew(array(), $selProdSepc)) {
                        Message::addErrorMessage($selProdSpecificsObj->getError());
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                }

                /* Save url keyword [ */
                $urlKeyword = strtolower(CommonHelper::createSlug($selProdData['selprod_url_keyword']));
                $seoUrl = CommonHelper::seoUrl($urlKeyword) . '-' . $selprod_id;
                $originalUrl = Product::PRODUCT_VIEW_ORGINAL_URL . $selprod_id;
                $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);
                $seoUrlKeyword = array(
                    'urlrewrite_original' => $originalUrl,
                    'urlrewrite_custom' => $customUrl
                );
                FatApp::getDb()->insertFromArray(UrlRewrite::DB_TBL, $seoUrlKeyword, false, array(), array('urlrewrite_custom' => $customUrl));
                /* ] */

                /* save options data, if any [ */
                if (!$sellerProdObj->addUpdateSellerProductOptions($selprod_id, $options)) {
                    Message::addErrorMessage($sellerProdObj->getError());
                    $db->rollbackTransaction();
                    FatUtility::dieWithError(Message::getHtml());
                }
                /* ] */

                /* Seller product lang data [ */
                $sellerProdObj = new SellerProduct($selprod_id);
                foreach ($languages as $lang_id => $langName) {
                    $reqLangData = ProductRequest::getAttributesByLangId($lang_id, $preqId);
                    if ($reqLangData == false) {
                        continue;
                    }

                    $arr = json_decode($reqLangData['preq_lang_data'], true);
                    if (!empty($arr)) {
                        $reqLangData = array_merge($reqLangData, json_decode($reqLangData['preq_lang_data'], true));
                    }
                    $title = $reqLangData['product_name'] ?? '';

                    $selProdLangData = array(
                        'selprodlang_selprod_id' => $selprod_id,
                        'selprodlang_lang_id' => $lang_id,
                        'selprod_title' => $reqLangData['selprod_title'] ?? $title,
                        'selprod_comments' => $reqLangData['selprod_comments'] ?? '',
                    );

                    if (!$sellerProdObj->updateLangData($lang_id, $selProdLangData)) {
                        Message::addErrorMessage($prodObj->getError());
                        $db->rollbackTransaction();
                        FatUtility::dieWithError(Message::getHtml());
                    }
                } /* ] */
            }

            $prodSpecData = array();
            if (isset($data['preq_specifications'])) {
                $prodSpecData = json_decode($data['preq_specifications'], true);
                $textSpecifications = (isset($prodSpecData['text_specification'])) ? $prodSpecData['text_specification'] : [];
                $mediaSpecifications = (isset($prodSpecData['media_specification'])) ? $prodSpecData['media_specification'] : [];
                
                /* [ FOMAT SPECIFICATION DATA */ 
                $textSpecGroupedData = [];
                if (!empty($textSpecifications)) {
                    $identifierArr = $textSpecifications['prodspec_identifier'];
                    $namesArr = $textSpecifications['prod_spec_name'];
                    $valuesArr = $textSpecifications['prod_spec_value'];
                    $groupsArr = $textSpecifications['prod_spec_group'];
                    
                    foreach ($namesArr as $langId => $names) {
                        foreach ($names as $key => $name) {
                            if (!empty($identifierArr[$key])) {
                                $textSpecGroupedData[$key]['prodspec_name'][$langId] = $name;
                                $value = (isset($valuesArr[$langId][$key])) ? $valuesArr[$langId][$key] : "";
                                $group = (isset($groupsArr[$key])) ? $groupsArr[$key] : "";
                                $textSpecGroupedData[$key]['prodspec_value'][$langId] = $value;
                                $textSpecGroupedData[$key]['prodspec_group'][$langId] = $group;
                                $textSpecGroupedData[$key]['prodspec_identifier'] = $identifierArr[$key];
                                $textSpecGroupedData[$key]['prodspec_is_file'][$langId] = 0;
                                $textSpecGroupedData[$key]['fileIndex'][$langId] = 0;
                            }
                        }
                    }
                }
                
                $mediaSpecGroupedData = [];
                if (!empty($mediaSpecifications)) {
                    $identifierArr = $mediaSpecifications['prodspec_identifier'];
                    $namesArr = $mediaSpecifications['prod_spec_name'];
                    $fileIndexArr = $mediaSpecifications['prod_spec_file_index'];
                    foreach ($namesArr as $langId => $names) {
                        foreach ($names as $key => $name) {
                            if (!empty($identifierArr[$key])) {
                                $mediaSpecGroupedData[$key]['prodspec_name'][$langId] = $name;
                                $fileIndex = (isset($fileIndexArr[$langId][$key])) ? $fileIndexArr[$langId][$key] : 0;
                                $mediaSpecGroupedData[$key]['prodspec_value'][$langId] = "";
                                $mediaSpecGroupedData[$key]['prodspec_group'][$langId] = "";
                                $mediaSpecGroupedData[$key]['prodspec_is_file'][$langId] = 1;
                                $mediaSpecGroupedData[$key]['prodspec_identifier'] = $identifierArr[$key];
                                $mediaSpecGroupedData[$key]['fileIndex'][$langId] = $fileIndex;
                            }
                        }
                    }
                }
                $specificationGroupedData = array_merge($textSpecGroupedData, $mediaSpecGroupedData);
                
                /* ] */
                if (!empty($specificationGroupedData)) {
                    /* [ INSERT SPECIFICATIONS IN DATABASE */
                            $defaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                            $otherLanguages = Language::getAllNames();
                            unset($otherLanguages[$defaultLangId]);
    
                            foreach ($specificationGroupedData as $key => $specData) {
                                $specName = (isset($specData['prodspec_name'][$defaultLangId])) ? $specData['prodspec_name'][$defaultLangId] : '';
                                $specValue = (isset($specData['prodspec_value'][$defaultLangId])) ? $specData['prodspec_value'][$defaultLangId] : '';
                                $specGroup = (isset($specData['prodspec_group'][$defaultLangId])) ? $specData['prodspec_group'][$defaultLangId] : '';
                                $isFile = (isset($specData['prodspec_is_file'][$defaultLangId])) ? $specData['prodspec_is_file'][$defaultLangId] : '';
                                $fileIndex = (isset($specData['fileIndex'][$defaultLangId])) ? $specData['fileIndex'][$defaultLangId] : '';
    
                                $prod = new Product($product_id);
                                if (!$prodSpecId = $prod->saveProductSpecifications(0, $defaultLangId, $specName, $specValue, $specGroup, $isFile, true, 0, $specData)) {
                                    Message::addErrorMessage($prod->getError());
                                    FatUtility::dieWithError(Message::getHtml());
                                }
    
                                if ($isFile) {
                                    $mediaDataToUpdate = [
                                        'afile_type' => AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE,
                                        'afile_record_id' => $product_id,
                                        'afile_record_subid' => $prodSpecId,
                                    ];
    
                                    $whr = [
                                        'smt' => 'afile_record_id = ? AND afile_type = ? AND afile_record_subid =? ',
                                        'vals' => [$preqId, AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $fileIndex]
                                    ];
                                    if (!FatApp::getDb()->updateFromArray(AttachedFile::DB_TBL, $mediaDataToUpdate, $whr)) {
                                        Message::addErrorMessage(FatApp::getDb()->getError());
                                        FatUtility::dieWithError(Message::getHtml());
                                    }
                                }
                            }
                        /* DELETE UNATTACHED MEDIA FILE */
                        $unattachedFiles = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $preqId, 0, 0, true, 0, 0, false, true);
                        if (!empty($unattachedFiles)) {
                            foreach ($unattachedFiles as $fileData) {
                                if ($fileData['afile_id'] > 0) {
                                    $whr = array('smt' => 'afile_id = ?', 'vals' => array($fileData['afile_id']));
                                    if (!FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL, $whr)) {
                                        Message::addErrorMessage(FatApp::getDb()->getError());
                                        FatUtility::dieWithError(Message::getHtml());
                                    }

                                    if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                                        unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                                    }
                                }
                            }
                        }

                        /* ] */
                        /* ] */
                    
                }
            }

            /* ] */
        }

        $email = new EmailHandler();
        $customCatalogReq = array();
        $customCatalogReq = $data;
        $customCatalogReq['preq_status'] = $post['preq_status'];
        $customCatalogReq['preq_comment'] = $post['preq_comment'];
        if (!$email->sendCustomCatalogRequestStatusChangeNotification($this->adminLangId, $customCatalogReq)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel('MSG_Email_could_not_be_Sent', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($status == ProductRequest::STATUS_APPROVED) {
            Product::updateMinPrices($product_id);
        }
        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_Status_updated_successfully', $this->adminLangId));
        $this->set('preq_id', $preqId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function customEanUpcForm($preqId)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId);

        $upcCodeData = array();
        if (!empty($productReqRow['preq_ean_upc_code'])) {
            $upcCodeData = json_decode($productReqRow['preq_ean_upc_code'], true);
        }
        $optionCombinations = array();
        $productOptions = ProductRequest::getProductReqOptions($preqId, $this->adminLangId, true);
        if (!empty($productOptions)) {
            $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '|');
        }
        $this->set('upcCodeData', $upcCodeData);
        $this->set('optionCombinations', $optionCombinations);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function autoCompleteOptions($userId)
    {
        //$pagesize = 10;
        $post = FatApp::getPostedData();

        $srch = Option::getSearchObject($this->adminLangId);
        $srch->addOrder('option_identifier');

        $cnd = $srch->addCondition('option_seller_id', '=', $userId);
        $cnd->attachCondition('option_seller_id', '=', 0, 'OR');


        /* $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN',
          'optionlang_option_id = option_id AND optionlang_lang_id = ' . FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 1)); */
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('option_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('option_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        //$srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'option_id');

        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['option_name'], ENT_QUOTES, 'UTF-8')),
                'option_identifier' => strip_tags(html_entity_decode($option['option_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function tagsAutoComplete()
    {
        $post = FatApp::getPostedData();

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
                Tag::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));

        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('tag_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('tag_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'tag_id');
        $json = array();
        foreach ($options as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['tag_name'], ENT_QUOTES, 'UTF-8')),
                'tag_identifier' => strip_tags(html_entity_decode($option['tag_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function updateCustomCatalogTag()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        if ($preqId < 1 || $tagId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $prodContent['product_tags'][] = $tagId;
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Tag_updated_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeCustomCatalogTag()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $tagId = FatApp::getPostedData('tag_id', FatUtility::VAR_INT, 0);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        if ($preqId < 1 || $tagId < 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodContent = json_decode($prodReqData['preq_content'], true);
        $key = array_search($tagId, $prodContent['product_tags']);
        unset($prodContent['product_tags'][$key]);
        $prodContent['product_tags'] = array_values($prodContent['product_tags']);
        $data['preq_content'] = FatUtility::convertToJson($prodContent);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Tag_removed_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function validateUpcCode()
    {
        $post = FatApp::getPostedData();
        if (empty($post) || $post['code'] == '') {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_code', '=', $post['code']);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $totalRecords = FatApp::getDb()->totalRecords($rs);
        if ($totalRecords > 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupEanUpcCode($preqId)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preqId = FatUtility::int($preqId);
        $prodReqData = ProductRequest::getAttributesById($preqId);

        $optionValueId = FatApp::getPostedData('optionValueId');
        if (empty($optionValueId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $code = FatApp::getPostedData('code', FatUtility::VAR_STRING, '');
        if (empty($code)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_fill_UPC/EAN_code', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $srch = UpcCode::getSearchObject();
        $srch->addCondition('upc_code', '=', $code);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!empty($row)) {
            Message::addErrorMessage(Labels::getLabel('MSG_This_UPC/EAN_code_already_assigned_to_another_product', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }


        $productUpcData = array();
        if (!empty($prodReqData['preq_ean_upc_code'])) {
            $productUpcData = json_decode($prodReqData['preq_ean_upc_code'], true);
        }
        $productUpcData[$optionValueId] = $code;
        $data['preq_ean_upc_code'] = FatUtility::convertToJson($productUpcData);
        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($data);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_ean/upc_code_added_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function loadCustomProductTags()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post['tags'])) {
            return false;
        }

        $srch = Tag::getSearchObject();
        $srch->addOrder('tag_identifier');
        $srch->joinTable(
                Tag::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'taglang_tag_id = tag_id AND taglang_lang_id = ' . $this->adminLangId
        );
        $srch->addMultipleFields(array('tag_id, tag_name, tag_identifier'));
        $srch->addCondition('tag_id', 'IN', $post['tags']);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $tags = $db->fetchAll($rs, 'tag_id');
        $li = '';
        foreach ($tags as $key => $tag) {
            $li .= '<li id="product-tag' . $tag['tag_id'] . '"><span class="left "><a href="javascript:void(0)" title="Remove" onClick="removeProductTag(' . $tag['tag_id'] . ');"><i class="icon ion-close remove_tag-js" data-tag-id="' . $tag['tag_id'] . '"></i></a></span>';
            $li .= '<span class="left">' . $tag['tag_name'] . ' (' . $tag['tag_identifier'] . ')' . '<input type="hidden" value="' . $tag['tag_id'] . '"  name="product_tags[]"></span></li>';
        }
        echo $li;
        exit;
    }

    public function loadCustomProductOptionss()
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post['options'])) {
            return false;
        }

        $srch = Option::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('option_id, option_name, option_identifier'));
        $srch->addCondition('option_id', 'IN', $post['options']);
        $srch->addOrder('option_identifier');

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $options = $db->fetchAll($rs, 'option_id');
        $li = '';
        foreach ($options as $key => $option) {
            $li .= '<li id="product-option' . $option['option_id'] . '"><span class="left" ><a href="javascript:void(0)" title="Remove" onClick="removeProductOption(' . $option['option_id'] . ');"><i class="icon ion-close" data-option-id="' . $option['option_id'] . '"></i></a></span>';
            $li .= '<span class="left">' . $option['option_name'] . ' (' . $option['option_identifier'] . ')' . '<input type="hidden" value="' . $option['option_id'] . '"  name="product_option[]"></span></li>';
        }

        echo $li;
        exit;
    }

    public function getShippingTab()
    {
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $preq_id = $post['preq_id'];

        $shipping_rates = array();
        $productReqData = ProductRequest::getAttributesById($preq_id, array('preq_id', 'preq_user_id'));
        $shipping_rates = ProductRequest::getProductShippingRates($preq_id, $this->adminLangId, 0, $productReqData['preq_user_id']);
        /* $shipping_rates = array();
          $productReqData = ProductRequest::getAttributesById($preq_id);
          $productReqData = json_decode($productReqData['preq_content'],true);
          $shipping_rates = !(empty($productReqData['product_shipping']))?$productReqData['product_shipping']:array(); */
        $this->set('adminLangId', $this->adminLangId);
        $this->set('product_id', $preq_id);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false, 'products/get-shipping-tab.php');
    }

    public function imagesForm($preq_id)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preq_id = FatUtility::int($preq_id);
        if (!$preq_id) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        if (!$row = ProductRequest::getAttributesById($preq_id)) {
            FatUtility::dieWithError($this->str_no_record);
        }

        $preqContent = $row['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $optionIds = (isset($preqContentData['product_option'])) ? $preqContentData['product_option'] : [];
        $isOptWithSizeChart = false;
        if (!empty($optionIds)) {
            $isOptWithSizeChart = $this->checkOptionWithSizeChart($optionIds);
        }

        $imagesFrm = $this->getImagesFrm($preq_id, $this->adminLangId, $isOptWithSizeChart);
        $this->set('preq_id', $preq_id);
        $this->set('imagesFrm', $imagesFrm);
        $this->_template->render(false, false);
    }

    public function images($preq_id, $option_id = 0, $lang_id = 0)
    {
        $this->objPrivilege->canViewCustomProductRequests();
        $preq_id = FatUtility::int($preq_id);
        if (!$preq_id) {
            Message::addErrorMessage($this->str_invalid_request);
        }

        if (!$row = ProductRequest::getAttributesById($preq_id)) {
            Message::addErrorMessage($this->str_no_record);
        }

        $preqContent = $row['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $optionIds = (isset($preqContentData['product_option'])) ? $preqContentData['product_option'] : [];

        $productSizeChartArr = [];
        if (!empty($optionIds)) {
            $isOptWithSizeChart = $this->checkOptionWithSizeChart($optionIds);
            if ($isOptWithSizeChart) {
                $productSizeChartArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $preq_id, 0, $lang_id, false, 0, 0, true);
            }
        }
        $this->set('sizeChartArr', $productSizeChartArr);

        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptions($preq_id, $this->adminLangId);

        $this->set('images', $product_images);
        $this->set('preq_id', $preq_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setImageOrder()
    {
        $preqObj = new ProductRequest();
        $post = FatApp::getPostedData();
        $preq_id = FatUtility::int($post['preq_id']);

        $imageIds = explode('-', $post['ids']);
        $count = 1;
        foreach ($imageIds as $row) {
            $order[$count] = $row;
            $count++;
        }

        if (!$preqObj->updateProdImagesOrder($preq_id, $order)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Ordered_Successfully!", $this->adminLangId));
    }

    public function uploadProductImages()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $preq_id = FatUtility::int($post['preq_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteImage($preq_id, $image_id, int $isSizeChart = 0)
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $preq_id = FatUtility::int($preq_id);
        $image_id = FatUtility::int($image_id);
        if (!$image_id || !$preq_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileData = AttachedFile::getAttributesById($image_id);
        $preqObj = new ProductRequest();
        if (!$preqObj->deleteProductImage($preq_id, $image_id, $isSizeChart)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!empty($fileData)) {
            if ($isSizeChart) {
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            } else {
                if (file_exists(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path']);
                }
            }
        }

        $this->set("msg", Labels::getLabel('LBL_Image_Removed_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getImagesFrm($preq_id = 0, $lang_id = 0, bool $isUploadSizeChart = false)
    {
        $sizeArr = imagesSizes::productImageSizeArr()[applicationConstants::getActiveTheme()];
        $this->objPrivilege->canViewCustomProductRequests();
        $imgTypesArr = $this->getSeparateImageOptions($preq_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->adminLangId), 'option_id', $imgTypesArr, 0, array(), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId)) + $languagesAssocArr, '', array(), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->adminLangId), 'prod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fldImg->htmlAfterField = '<label class="filelabel">' . Labels::getLabel('LBL_Browse_File', $this->adminLangId) . '</label></div><br/><small>' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_' . $sizeArr['width'] . '_x_' . $sizeArr['height'], $this->adminLangId) . '</small>';

        $frm->addHiddenField('', 'min_width', $sizeArr['width']);
        $frm->addHiddenField('', 'min_height', $sizeArr['height']);
        $frm->addHiddenField('', 'preq_id', $preq_id);
        /* [ UPLOAD SIZE CHART  */
        if ($isUploadSizeChart) {
            $frm->addFileUpload(Labels::getLabel('LBL_Upload_Size_Chart', $this->adminLangId), 'prod_size_chart', array('id' => 'prod_size_chart'));
        }
        /* ] */

        return $frm;
    }

    private function getSeparateImageOptions($preq_id, $lang_id)
    {
        $imgTypesArr = array(0 => Labels::getLabel('LBL_For_All_Options', $this->adminLangId));

        if ($preq_id) {
            $reqData = ProductRequest::getAttributesById($preq_id, array('preq_content'));
            if (!empty($reqData)) {
                $reqData = json_decode($reqData['preq_content'], true);
            }
            $productOptions = isset($reqData['product_option']) ? $reqData['product_option'] : array();
            if (!empty($productOptions)) {
                foreach ($productOptions as $optionId) {
                    $optionData = Option::getAttributesById($optionId, array('option_is_separate_images'));

                    if (!$optionData || !$optionData['option_is_separate_images']) {
                        continue;
                    }

                    $optionValues = Product::getOptionValues($optionId, $lang_id);
                    if (!empty($optionValues)) {
                        foreach ($optionValues as $k => $v) {
                            $imgTypesArr[$k] = $v;
                        }
                    }
                }
            }
        }
        return $imgTypesArr;
    }

    private function getForm($attrgrp_id = 0, $productType = Product::PRODUCT_TYPE_PHYSICAL)
    {
        return $this->getProductCatalogForm($attrgrp_id, 'REQUESTED_CATALOG_PRODUCT', $productType);
    }

    private function getLangForm($preqId, $langId)
    {
        $frm = new Form('frmCustomProductLang');
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name');
        $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'product_description');
        $frm->addTextBox(Labels::getLabel('LBL_YouTube_Video', $this->adminLangId), 'product_youtube_video');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getStatusForm()
    {
        $frm = new Form('frmUpdateStatus');

        $statusArr = ProductRequest::getStatusArr($this->adminLangId);
        unset($statusArr[ProductRequest::STATUS_PENDING]);
        $frm->addSelectBox(Labels::getLabel('LBL_Select_Status', $this->adminLangId), 'preq_status', $statusArr, '', array(), 'Select')->requirements()->setRequired();

        $frm->addHiddenField('', 'preq_id');
        $frm->addTextArea(Labels::getLabel('LBL_Add_Comment', $this->adminLangId), 'preq_comment', '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function catalogCustomProductRequestSearchForm()
    {
        $frm = new Form('frmCustomProdReqSrch');
        $frm->addTextBox('Keyword', 'keyword', '');

        $statusArr = array('-1' => Labels::getLabel('LBL_All', $this->adminLangId)) + ProductRequest::getStatusArr($this->adminLangId);
        $frm->addSelectBox('Status', 'status', $statusArr, '', array(), '');
        $frm->addDateField('Date From', 'date_from', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $frm->addDateField('Date To', 'date_to', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', 'Search');
        $fld_cancel = $frm->addButton("", "btn_clear", "Clear Search", array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function getTranslatedData()
    {
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $prodSpecName = FatApp::getPostedData('prod_spec_name', FatUtility::VAR_STRING, '');
        $prodSpecValue = FatApp::getPostedData('prod_spec_value', FatUtility::VAR_STRING, '');

        if (!empty($prodSpecName) && !empty($prodSpecValue)) {
            $data = [];

            $translatedText = $this->translateLangFields(ProductRequest::DB_TBL_LANG, $prodSpecName[$siteDefaultLangId]);
            foreach ($translatedText as $langId => $textArr) {
                foreach ($textArr as $index => $value) {
                    if ('preqlang_lang_id' === $index) {
                        continue;
                    }
                    $data[$langId]['prod_spec_name[' . $langId . '][' . $index . ']'] = $value;
                }
            }

            $translatedText = $this->translateLangFields(ProductRequest::DB_TBL_LANG, $prodSpecValue[$siteDefaultLangId]);
            foreach ($translatedText as $langId => $textArr) {
                foreach ($textArr as $index => $value) {
                    if ('preqlang_lang_id' === $index) {
                        continue;
                    }
                    $data[$langId]['prod_spec_value[' . $langId . '][' . $index . ']'] = $value;
                }
            }

            CommonHelper::jsonEncodeUnicode($data, true);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
    }

    public function customFieldsForm(int $preqId)
    {
        if (1 > $preqId) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodCatAttr = array();
        $productReqData = ProductRequest::getAttributesById($preqId, ['preq_prodcat_id', 'preq_custom_fields']);

        if (empty($productReqData)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $defaultLangId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
        if (0 < $productReqData['preq_prodcat_id']) {

            $preqData = json_decode($productReqData['preq_custom_fields'], true);
            $preqData['preq_id'] = $preqId;
            $preqData['product_id'] = $preqId;

            $prodCatObj = new ProductCategory($productReqData['preq_prodcat_id']);
            $prodCatAttr = $prodCatObj->getAttrDetail(0, 0, 'attr_type');

            $prod = new Product();

            $updatedProdCatAttr = array();
            foreach ($prodCatAttr as $attr) {
                $updatedProdCatAttr[$attr['attr_attrgrp_id']][$attr['attr_id']][$attr['attrlang_lang_id']] = $attr;
            }

            $prodCatAttr = $prod->formatAttributesData($prodCatAttr);
            $frm = $prod->getProdCatCustomFieldsForm($prodCatAttr, $defaultLangId, true, $preqData);
            //$frm->fill($preqData);
            $this->set('updatedProdCatAttr', $updatedProdCatAttr);
            $this->set('frm', $frm);
        }

        $languages = Language::getAllNames();
        unset($languages[$defaultLangId]);

        $this->set('otherLangData', $languages);
        $this->set('prodCat', $productReqData['preq_prodcat_id']);
        $this->set('siteDefaultLangId', $defaultLangId);
        $this->set('prodCatAttr', $prodCatAttr);
        $this->set('productOptions', $productReqData['product_option'] ?? []);
        $this->set('preqId', $preqId);
        $this->_template->render(false, false);
    }

    public function setupCustomFields()
    {
        $this->objPrivilege->canEditCustomProductRequests();
        $post = FatApp::getPostedData();
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        $productReqData = ProductRequest::getAttributesById($preqId);

        $data = array();
        if (!empty($post['num_attributes'])) {
            $data['num_attributes'] = $post['num_attributes'];
        }

        if (!empty($post['text_attributes'])) {
            $data['text_attributes'] = $post['text_attributes'];
        }
        $dataToSave['preq_custom_fields'] = FatUtility::convertToJson($data);

        $prodReq = new ProductRequest($preqId);
        $prodReq->assignValues($dataToSave);
        if (!$prodReq->save()) {
            Message::addErrorMessage($prodReq->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Product_custom_fields_saved_Successful', $this->adminLangId));
        $this->set('preqId', $prodReq->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    private function checkOptionWithSizeChart(array $optionsIds): bool
    {
        $srch = new SearchBase(Option::DB_TBL);
        $srch->addCondition(Option::DB_TBL_PREFIX . 'attach_sizechart', '=', applicationConstants::YES);
        $srch->addCondition(Option::DB_TBL_PREFIX . 'id', 'IN', $optionsIds);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (empty($row)) {
            return false;
        }
        return true;
    }

    public function uploadSizeChart()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
        }
        $preqId = FatUtility::int($post['preq_id']);
        $langId = FatUtility::int($post['lang_id']);

        $fileHandlerObj = new AttachedFile();
        /* [  DELETE OLD SIZE CHART */
        $productSizeChart = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $preqId, 0, $langId, false, 0, 0, true);
        if (!empty($productSizeChart)) {
            foreach ($productSizeChart as $fileData) {
                if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $preqId, $fileData['afile_id'])) {
                    Message::addErrorMessage($fileHandlerObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            }
        }
        /* ] */

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_select_a_file", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        

        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $preqId, 0, $_FILES['cropped_image']['name'], -1, $unique_record = false, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function customCatalogProductImages($preqId, $displaySpec = 1)
    {
        $preqId = FatUtility::int($preqId);
        $productReqRow = ProductRequest::getAttributesById($preqId, array('preq_user_id', 'preq_content'));

        $preqContent = $productReqRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $optionIds = (isset($preqContentData['product_option'])) ? $preqContentData['product_option'] : [];

        $isOptWithSizeChart = false;
        if (!empty($optionIds)) {
            $isOptWithSizeChart = $this->checkOptionWithSizeChart($optionIds);
        }

        $imagesFrm = $this->getCustomProductImagesFrm($preqId, $this->adminLangId, $isOptWithSizeChart);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('displaySpec', $displaySpec);
        $this->set('imagesFrm', $imagesFrm);
        $this->set('preqId', $preqId);
        $this->set('productType', $preqContentData['product_type']);
        $this->_template->render(false, false);
    }

    public function deleteCustomCatalogProductImage($preq_id, $image_id, int $isSizeChart = 0)
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());
        $preq_id = FatUtility::int($preq_id);
        $image_id = FatUtility::int($image_id);
        if (!$image_id || !$preq_id) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request!", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileData = AttachedFile::getAttributesById($image_id);
        $preqObj = new ProductRequest();
        if (!$preqObj->deleteProductImage($preq_id, $image_id, $isSizeChart)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!empty($fileData)) {
            if ($isSizeChart) {
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            } else {
                if (file_exists(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $fileData['afile_physical_path']);
                }
            }
        }
        Message::addMessage(Labels::getLabel('LBL_Image_removed_successfully.', $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function customCatalogImages($preq_id, $option_id = 0, $lang_id = 0)
    {
        $this->userPrivilege->canViewSellerRequests(UserAuthentication::getLoggedUserId());
        $preq_id = FatUtility::int($preq_id);

        if (!$preq_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if (!$productRow = ProductRequest::getAttributesById($preq_id, array('preq_user_id', 'preq_content'))) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $product_images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $lang_id, false, 0, 0, true);
        $imgTypesArr = $this->getSeparateImageOptionsOfCustomProduct($preq_id, $this->adminLangId);

        $preqContent = $productRow['preq_content'];
        $preqContentData = json_decode($preqContent, true);
        $optionIds = (isset($preqContentData['product_option'])) ? $preqContentData['product_option'] : [];
        $productSizeChartArr = [];
        if (!empty($optionIds)) {
            $isOptWithSizeChart = $this->checkOptionWithSizeChart($optionIds);
            if ($isOptWithSizeChart) {
                $productSizeChartArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $preq_id, 0, $lang_id, false, 0, 0, true);
            }
        }
        $this->set('sizeChartArr', $productSizeChartArr);
        $this->set('images', $product_images);
        $this->set('preq_id', $preq_id);
        $this->set('imgTypesArr', $imgTypesArr);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setCustomCatalogProductImagesOrder()
    {
        $this->userPrivilege->canEditSellerRequests(UserAuthentication::getLoggedUserId());

        $preqObj = new ProductRequest();
        $post = FatApp::getPostedData();
        $preq_id = FatUtility::int($post['preq_id']);

        $imageIds = explode('-', $post['ids']);
        $count = 1;
        foreach ($imageIds as $row) {
            $order[$count] = $row;
            $count++;
        }

        if (!$preqObj->updateProdImagesOrder($preq_id, $order)) {
            Message::addErrorMessage($preqObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        FatUtility::dieJsonSuccess(Labels::getLabel("LBL_Ordered_Successfully!", $this->adminLangId));
    }

    public function setupCustomCatalogProductImages()
    {

        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
        }
        $preq_id = FatUtility::int($post['preq_id']);
        $option_id = FatUtility::int($post['option_id']);
        $lang_id = FatUtility::int($post['lang_id']);

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel("MSG_Please_select_a_file", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES)  { /* in kbs */
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveImage($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $preq_id, $option_id, $_FILES['cropped_image']['name'], -1, $unique_record = false, $lang_id)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        Message::addMessage(Labels::getLabel("MSG_Image_Uploaded_Successfully", $this->adminLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    private function getCustomProductIntialSetUpFrm($productId = 0, $preqId = 0)
    {
        $frm = new Form('frmProductIntialSetUp');
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Identifier', $this->adminLangId), 'product_identifier');
        $frm->addHiddenField('', 'product_type', Product::PRODUCT_TYPE_PHYSICAL);
        $brandFld = $frm->addTextBox(Labels::getLabel('LBL_Brand', $this->adminLangId), 'brand_name');
        if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $brandFld->requirements()->setRequired();
        }
        $frm->addRequiredField(Labels::getLabel('LBL_Category', $this->adminLangId), 'category_name');

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $lang) {
            if ($langId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name[' . $langId . ']');
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_Product_Name', $this->adminLangId), 'product_name[' . $langId . ']');
            }
            //$frm->addTextArea(Labels::getLabel('LBL_Description', $this->adminLangId), 'product_description[' . $langId . ']');
            $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->adminLangId), 'product_description_' . $langId);
            $frm->addTextBox(Labels::getLabel('LBL_Youtube_Video_Url', $this->adminLangId), 'product_youtube_video[' . $langId . ']');
        }

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        unset($languages[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addRequiredField(Labels::getLabel('LBL_Tax_Category[Sale]', $this->adminLangId), 'taxcat_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Tax_Category[Rent]', $this->adminLangId), 'taxcat_name_rent');
        $fldMinSelPrice = $frm->addFloatField(Labels::getLabel('LBL_Minimum_Selling_Price', $this->adminLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'product_min_selling_price', '');
        $fldMinSelPrice->requirements()->setPositive();

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'product_active', $activeInactiveArr, applicationConstants::YES, array(), '');
        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addHiddenField('', 'product_brand_id');
        $frm->addHiddenField('', 'ptc_prodcat_id');
        $frm->addHiddenField('', 'ptt_taxcat_id');
        $frm->addHiddenField('', 'ptt_taxcat_id_rent');
        $frm->addHiddenField('', 'product_seller_id');
        $frm->addButton('', 'btn_discard', Labels::getLabel('LBL_Discard', $this->adminLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->adminLangId));
        return $frm;
    }

    private function getProductAttributeAndSpecificationsFrm($productId, $preqId = 0)
    {
        $frm = new Form('frmProductAttributeAndSpecifications');
        $fldModel = $frm->addTextBox(Labels::getLabel('LBL_Model', $this->adminLangId), 'product_model');
        if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fldModel->requirements()->setRequired();
        }
        $warrantyFld = $frm->addRequiredField(Labels::getLabel('LBL_PRODUCT_WARRANTY', $this->adminLangId), 'product_warranty');
        $warrantyFld->requirements()->setInt();
        $warrantyFld->requirements()->setPositive();
        $frm->addCheckBox(Labels::getLabel('LBL_Mark_This_Product_As_Featured?', $this->adminLangId), 'product_featured', 1, array(), false, 0);

        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addButton('', 'btn_back', Labels::getLabel('LBL_Back', $this->adminLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->adminLangId));
        return $frm;
    }

    private function getProductShippingFrm($productId, $preqId = 0)
    {
        $frm = new Form('frmProductShipping');
        if ($preqId > 0) {
            $preqContent = ProductRequest::getAttributesById($preqId, 'preq_content');
            $preqContentData = json_decode($preqContent, true);
            $productType = $preqContentData['product_type'];
        } else {
            $productType = Product::getAttributesById($productId, 'product_type');
        }


        if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $shipProfileArr = ShippingProfile::getProfileArr($this->adminLangId, 0, true, true);
            $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->adminLangId), 'shipping_profile', $shipProfileArr)->requirements()->setRequired();
        }
        if ($productType == Product::PRODUCT_TYPE_PHYSICAL) {
            if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 0)) {
                $shipPackArr = ShippingPackage::getAllNames();
                $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Package', $this->adminLangId), 'product_ship_package', $shipPackArr)->requirements()->setRequired();


                $weightUnitsArr = applicationConstants::getWeightUnitsArr($this->adminLangId);
                $frm->addSelectBox(Labels::getLabel('LBL_Weight_Unit', $this->adminLangId), 'product_weight_unit', $weightUnitsArr)->requirements()->setRequired();

                $weightFld = $frm->addFloatField(Labels::getLabel('LBL_Weight', $this->adminLangId), 'product_weight', '0.00');
                $weightFld->requirements()->setRequired(true);
                $weightFld->requirements()->setFloatPositive();
                $weightFld->requirements()->setRange('0.01', '9999999999');
            }

            if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                /*  $frm->addCheckBox(Labels::getLabel('LBL_Product_Is_Eligible_For_Free_Shipping?', $this->adminLangId), 'ps_free', 1, array(), false, 0); */

                $codFld = $frm->addCheckBox(Labels::getLabel('LBL_Enable_Cash_On_Delivery', $this->adminLangId), 'product_cod_enabled', 1, array(), false, 0);
                $paymentMethod = new PaymentMethods();
                if (!$paymentMethod->cashOnDeliveryIsActive()) {
                    $codFld->addFieldTagAttribute('disabled', 'disabled');
                    $codFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->adminLangId) . '</small>';
                }
            }

            /* ] */
        }
        if ($preqId == 0 && !FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $frm->addTextBox(Labels::getLabel('LBL_Country_the_Product_is_being_shipped_from', $this->adminLangId), 'shipping_country');
            //$frm->addHtml('', '', '<div id="tab_shipping"></div>');
        }

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addButton('', 'btn_back', Labels::getLabel('LBL_Back', $this->adminLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->adminLangId));
        return $frm;
    }

    private function getCustomProductImagesFrm(int $preq_id = 0, int $lang_id = 0, bool $isUploadSizeChart = false)
    {
        $sizeArr = imagesSizes::productImageSizeArr()[applicationConstants::getActiveTheme()];
        $imgTypesArr = $this->getSeparateImageOptionsOfCustomProduct($preq_id, $lang_id);
        $frm = new Form('imageFrm', array('id' => 'imageFrm'));
        $frm->addSelectBox(Labels::getLabel('LBL_Image_File_Type', $this->adminLangId), 'option_id', $imgTypesArr, 0, array('class' => 'option'), '');
        $languagesAssocArr = Language::getAllNames();
        $frm->addSelectBox(Labels::getLabel('LBL_Language', $this->adminLangId), 'lang_id', array(0 => Labels::getLabel('LBL_All_Languages', $this->adminLangId)) + $languagesAssocArr, '', array('class' => 'language'), '');
        $fldImg = $frm->addFileUpload(Labels::getLabel('LBL_Photo(s)', $this->adminLangId), 'prod_image', array('id' => 'prod_image'));
        $fldImg->htmlBeforeField = '<div class="filefield">';
        $fldImg->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_' . $sizeArr['width'] . '_x_' . $sizeArr['height'], $this->adminLangId) . '</span>';
        $frm->addHiddenField('', 'min_width', $sizeArr['width']);
        $frm->addHiddenField('', 'min_height', $sizeArr['height']);
        $frm->addHiddenField('', 'preq_id', $preq_id);
        /* [ UPLOAD SIZE CHART  */
        if ($isUploadSizeChart) {
            $frm->addFileUpload(Labels::getLabel('LBL_Upload_Size_Chart', $this->adminLangId), 'prod_size_chart', array('id' => 'prod_size_chart'));
        }
        /* ] */
        return $frm;
    }

    private function getSeparateImageOptionsOfCustomProduct($preq_id = 0, $lang_id = 0)
    {
        $preq_id = FatUtility::int($preq_id);
        $imgTypesArr = array(0 => Labels::getLabel('LBL_For_All_Options', $this->adminLangId));
        if ($preq_id) {
            $reqData = ProductRequest::getAttributesById($preq_id, array('preq_content'));
            if (!empty($reqData)) {
                $reqData = json_decode($reqData['preq_content'], true);
            }
            $productOptions = isset($reqData['product_option']) ? $reqData['product_option'] : array();
            if (!empty($productOptions)) {
                foreach ($productOptions as $optionId) {
                    $optionData = Option::getAttributesById($optionId, array('option_is_separate_images'));

                    if (!$optionData || !$optionData['option_is_separate_images']) {
                        continue;
                    }

                    $optionValues = Product::getOptionValues($optionId, $lang_id);
                    if (!empty($optionValues)) {
                        foreach ($optionValues as $k => $v) {
                            $imgTypesArr[$k] = $v;
                        }
                    }
                }
            }
        }
        return $imgTypesArr;
    }

    public function catalogProdSpecMediaForm($preqId)
    {
        $preqId = FatUtility::int($preqId);
        $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0);
        $key = FatApp::getPostedData('key', FatUtility::VAR_INT, -1);
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        $prodSpecData = array();
        if ($key >= 0) {
            $specificationsData = json_decode($productReqRow['preq_specifications'], true);
            $specifications = (isset($specificationsData['media_specification'])) ? $specificationsData['media_specification'] : [];
            foreach ($languages as $otherLangId => $langName) {
                $specName = (isset($specifications['prod_spec_name'][$otherLangId][$key])) ? $specifications['prod_spec_name'][$otherLangId][$key] : "";
                $specValue = (isset($specifications['prod_spec_file_index'][$otherLangId][$key])) ? $specifications['prod_spec_file_index'][$otherLangId][$key] : "";
                $specGroup = (isset($specifications['prod_spec_group'][$key])) ? $specifications['prod_spec_group'][$key] : "";
                $specIdentifier = (isset($specifications['prodspec_identifier'][$key])) ? $specifications['prodspec_identifier'][$key] : "";
                
                $prodSpecData['prod_spec_name'][$otherLangId] = $specName;
                $prodSpecData['prod_spec_file_index'][$otherLangId] = $specValue;
                $prodSpecData['prodspec_identifier'] = $specIdentifier;
                $prodSpecData['key'][$otherLangId] = $key;
            }
        }

        unset($languages[$siteDefaultLangId]);
        $this->set('otherLanguages', $languages);
        $this->set('langId', $langId);
        $this->set('prodSpecData', $prodSpecData);
        $this->set('preqId', $preqId);
        $this->set('siteDefaultLangId', FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $this->_template->render(false, false, 'custom-products/custom-catalog-prod-spec-media-form.php');
    }

    public function uploadCatalogProductSpecificationMediaData()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $key = FatUtility::int($post['key']);
        $isImage = FatUtility::int($post['is_image']);
        $langId = FatUtility::int($post['langId']);
        $preqId = FatUtility::int($post['preq_id']);
        $prodSpecFileIndex = FatUtility::int($post['prod_spec_file_index']);


        if ($key < 0 && (($isImage < 1 && !is_uploaded_file($_FILES['prodspec_files_' . $langId]['tmp_name'])) && ($isImage == 1 && !is_uploaded_file($_FILES['cropped_image']['tmp_name'])))) {
            Message::addErrorMessage(Labels::getLabel('LBL_Please_Select_A_File', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $fileHandlerObj = new AttachedFile();
        $isImage = true;
        $fileId = 0;
        if (isset($_FILES['cropped_image']) && is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            if ($_FILES['cropped_image']['size'] > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) { /* in kbs */
                Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
                FatUtility::dieJsonError(Message::getHtml());
            }
        
            $this->deleteCatalogSpecFile($preqId, $prodSpecFileIndex, $langId);
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $preqId, $prodSpecFileIndex, $_FILES['cropped_image']['name'], -1, $unique_record = false, $langId)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $fileId = $fileHandlerObj->getMainTableRecordId();
        } else if (is_uploaded_file($_FILES['prodspec_files_' . $langId]['tmp_name'])) {
            $isImage = false;
            $this->deleteCatalogSpecFile($preqId, $prodSpecFileIndex, $langId);
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['prodspec_files_' . $langId]['tmp_name'], AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $preqId, $prodSpecFileIndex, $_FILES['prodspec_files_' . $langId]['name'], -1, $unique_record = false, $langId)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            $fileId = $fileHandlerObj->getMainTableRecordId();
        }
        if ($fileId > 0) {
            $attachmentUrl = UrlHelper::generateUrl('Image', 'attachment', [$fileId, false], CONF_WEBROOT_FRONTEND);
            if ($isImage) {
                $fileHtml = "<img src='" . $attachmentUrl . "' class='img-thumbnail image-small' />";
            } else {
                $fileHtml = "<a href='" . $attachmentUrl . "' download><i class='fa fa-download' aria-hidden='true'></i></a>";
            }
            $this->set('uploadedFileData', $fileHtml);
        }

        $this->set("msg", Labels::getLabel('LBL_Specification_File_Uploaded_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function catalogSpecificationsMediaByLangId()
    {
        $preqId = FatApp::getPostedData('preq_id', FatUtility::VAR_INT, 0);
        /* $langId = FatApp::getPostedData('langId', FatUtility::VAR_INT, 0); */
        $langId = $this->adminLangId;
        if ($preqId < 1 || $langId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productReqRow = ProductRequest::getAttributesById($preqId);
        $productSpecifications = array();
        $specificationsData = json_decode($productReqRow['preq_specifications'], true);
        $specifications = (isset($specificationsData['media_specification'])) ? $specificationsData['media_specification'] : [];
        if (!empty($specifications['prodspec_identifier'])) {
            $namesArr = $specifications['prod_spec_name'];
            $valuesArr = $specifications['prod_spec_file_index'];
            foreach($specifications['prodspec_identifier'] as $key => $identifier) {
                $productSpecifications[$key]['identifier'] = $identifier;
                $productSpecifications[$key]['prod_spec_name'] = (isset($namesArr[$langId][$key])) ? $namesArr[$langId][$key] : $identifier;
                $productSpecifications[$key]['prod_spec_file_index'] = (isset($valuesArr[$langId][$key])) ? $valuesArr[$langId][$key] : "";
            }
        }
        $this->set('productSpecifications', $productSpecifications);
        $this->set('langId', FatApp::getPostedData('langId', FatUtility::VAR_INT, 0));
        $this->set('preqId', $preqId);
        $this->_template->render(false, false, 'custom-products/catalog-specifications-media.php');
    }

    private function deleteCatalogSpecFile(int $prodReqId, int $key, int $langId = 0): bool
    {
        $displayAll = true;
        if ($langId > 0) {
            $displayAll = false;
        }
        $filesData = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $prodReqId, $key, $langId, $displayAll, 0, 0, false, $displayAll);
        if (!empty($filesData)) {
            foreach ($filesData as $fileData) {
                $fileId = $fileData['afile_id'];
                $prodObj = new Product();
                if (!$prodObj->deleteProductSpecFile(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $prodReqId, $fileId)) {
                    Message::addErrorMessage($prodObj->getError());
                    //FatUtility::dieJsonError(Message::getHtml());
                    return false;
                }
                if (file_exists(CONF_UPLOADS_PATH . $fileData['afile_physical_path'])) {
                    unlink(CONF_UPLOADS_PATH . $fileData['afile_physical_path']);
                }
            }
        }
        return true;
    }

}
