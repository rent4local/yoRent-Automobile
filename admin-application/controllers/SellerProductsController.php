<?php

class SellerProductsController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->canView = $this->objPrivilege->canViewSellerProducts($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSellerProducts($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($product_id = 0)
    {
        $this->setListingCommonData($product_id);
        $this->set('productType', applicationConstants::PRODUCT_FOR_RENT);
        $this->_template->render();
    }
    
    public function form(int $productId, int $selprodId, $productType = applicationConstants::PRODUCT_FOR_RENT)
    {
        $this->objPrivilege->canEditProducts();
        $this->set('productId', $productId);
        $this->set('selprodId', $selprodId);
        $this->set('activeTab', $productType);
        $this->_template->addJs(array('js/tagify.min.js', 'js/tagify.polyfills.min.js'));
        $this->_template->addCss(array('css/tagify.css'));
        $this->_template->render();
    }
    
    public function sales($product_id = 0)
    {
        
        $this->setListingCommonData($product_id);
        $this->set('productType', applicationConstants::PRODUCT_FOR_SALE);
        $this->_template->render(true, true, 'seller-products/index.php');
    }
    
    private function setListingCommonData($product_id = 0)
    {
        $data = FatApp::getPostedData();
        $srchFrm = $this->getSearchForm();
        if ($data) {
            $data['user_id'] = $data['id'];
            unset($data['id']);
            $srchFrm->fill($data);
        }
        $this->objPrivilege->canViewSellerProducts();
        $this->includeDateTimeFiles();
        $this->set('includeEditor', true);
        $this->set("frmSearch", $srchFrm);
        $this->set("product_id", $product_id);
        $this->_template->addJs(['js/import-export.js', 'js/tagify.min.js', 'js/tagify.polyfills.min.js']);
        $this->_template->addCss(array('css/tagify.css'));
    }
    

    private function getSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Name_Or_Email', $this->adminLangId), 'user_name', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $prodCatObj = new ProductCategory();
        $arrCategories = $prodCatObj->getCategoriesForSelectBox($this->adminLangId);
        $categories = $prodCatObj->makeAssociativeArray($arrCategories);
        $frm->addSelectBox(Labels::getLabel('LBL_category', $this->adminLangId), 'prodcat_id', array(-1 => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + $categories, '', array(), '');
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Active', $this->adminLangId), 'active', array(-1 => Labels::getLabel('LBL_Does_Not_Matter', $this->adminLangId)) + $activeInactiveArr, '', array(), '');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id', '');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function sellerProducts($product_id = 0, $productType = applicationConstants::PRODUCT_FOR_RENT)
    {
        $post = FatApp::getPostedData();
        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'u.user_id = uc.credential_user_id', 'uc');
        $srch->addCondition('selprod_deleted', '=', 0);

        if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->addCondition('sprodata_is_for_sell', '=', applicationConstants::YES);
        }
        
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $is_custom_or_catalog = FatApp::getPostedData('is_custom_or_catalog', FatUtility::VAR_INT - 1);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        if ($keyword != '') {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $user_id = FatApp::getPostedData('user_id', FatUtility::VAR_INT, -1);
        if ($user_id > 0) {
            $srch->addCondition('selprod_user_id', '=', $user_id);
        } else {
            $user_name = FatApp::getPostedData('user_name', null, '');
            if (!empty($user_name)) {
                $cond = $srch->addCondition('u.user_name', 'like', '%' . $keyword . '%');
                $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
                $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%');
            }
        }


        $product_attrgrp_id = FatApp::getPostedData('product_attrgrp_id', FatUtility::VAR_INT, -1);
        if ($product_attrgrp_id != -1) {
            $srch->addCondition('product_attrgrp_id', '=', $product_attrgrp_id);
        }

        $active = FatApp::getPostedData('active', FatUtility::VAR_INT, -1);
        if ($active != -1) {
            if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
               $srch->addCondition('selprod_active', '=', $active); 
               $srch->addOrder('selprod_active', 'DESC');
            } else {
                $srch->addCondition('sprodata_rental_active', '=', $active);
                $srch->addOrder('sprodata_rental_active', 'DESC');
            }
        }

        $prodcat_id = FatApp::getPostedData('prodcat_id', FatUtility::VAR_INT, -1);
        if ($prodcat_id > -1) {
            $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'p.product_id = ptc_product_id', 'ptcat');
            $srch->addCondition('ptcat.ptc_prodcat_id', '=', $prodcat_id);
        }
        $product_id = 0;
        if (isset($post['product_id'])) {
            $product_id = FatUtility::int($post['product_id']);
        }
        if ($product_id) {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
        }
        if ($product_id) {
            $row = Product::getAttributesById($product_id, array('product_id'));
            if (!$row) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
            $srch->addCondition('selprod_product_id', '=', $product_id);
        }

        $srch->addMultipleFields(
                array(
                    'selprod_id', 'selprod_user_id', 'selprod_price', 'selprod_stock', 'selprod_product_id',
                    'selprod_active', 'selprod_available_from', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'u.user_name', 'uc.credential_email', 'product_type', 'spd.*'
                )
        );

        
        $srch->addOrder('selprod_added_on', 'DESC');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        if (count($arrListing)) {
            foreach ($arrListing as &$arr) {
                $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->adminLangId);
            }
        }

        $this->set("arrListing", $arrListing);
        $this->set('product_id', $product_id);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->adminLangId));
        $this->set('canViewProducts', $this->objPrivilege->canViewProducts($this->admin_id, true));
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));

        if (!$product_id) {
            $this->set('page', $page);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
        }
        $this->set('productType', $productType);
        $this->set('pageSize', $pageSize);
        $this->set('durationTypes', applicationConstants::rentalTypeArr($this->admin_id));
        $this->_template->render(false, false);
    }

    public function sellerProductForm(int $productId, int $selprodId = 0)
    {
        $productRow = Product::getAttributesById($productId);
        if (empty($productRow)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        if ($productRow['product_active'] != applicationConstants::ACTIVE) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Catalog_is_no_more_active', $this->adminLangId));
        }
        $productLangRow = Product::getProductDataById(FatApp::getConfig('CONF_DEFAULT_SITE_LANG'), $productId, array('IFNULL(product_name,product_identifier) as product_url_keyword'));
        
        $frmSellerProduct = $this->getSellerProductForm($productId);
        $sellerProductRow = [];
        if ($selprodId) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId, null, true, true, false, applicationConstants::PRODUCT_FOR_RENT, true);
            if (!$sellerProductRow) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
            $urlSrch = UrlRewrite::getSearchObject();
            $urlSrch->doNotCalculateRecords();
            $urlSrch->doNotLimitRecords();
            $urlSrch->addFld('urlrewrite_custom');
            $urlSrch->addCondition('urlrewrite_original', '=', 'products/view/' . $selprodId);
            $rs = $urlSrch->getResultSet();
            $urlRow = FatApp::getDb()->fetch($rs);
            if ($urlRow) {
                $data['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
            }
            $customUrl = explode("/", $urlRow['urlrewrite_custom']);
            $sellerProductRow['selprod_url_keyword'] = $customUrl[0];
        } else {
            $sellerProductRow['selprod_url_keyword'] = strtolower(CommonHelper::createSlug($productLangRow['product_url_keyword']));
        }
        $user_shop_name = User::getUserShopName($sellerProductRow['selprod_user_id']);
        $sellerProductRow['selprod_user_shop_name'] = $user_shop_name['user_name'] . ' - ' . $user_shop_name['shop_identifier'];

        $productWarranty = Product::getAttributesById($productId, 'product_warranty', true);
        $sellerProductRow['product_warranty'] = FatUtility::int($productWarranty);

        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $langData = SellerProduct::getAttributesByLangId($langId, $selprodId);
            $sellerProductRow['selprod_title' . $langId] = isset($langData['selprod_title']) ? $langData['selprod_title'] : '';
            $sellerProductRow['selprod_comments' . $langId] = isset($langData['selprod_comments']) ? $langData['selprod_comments'] : '';
            $sellerProductRow['selprod_rental_terms' . $langId] = isset($langData['selprod_rental_terms']) ? $langData['selprod_rental_terms'] : '';
        }

        $frmSellerProduct->fill($sellerProductRow);
        $product_added_by_admin_arr = Product::getAttributesById($productId, array('product_added_by_admin_id,product_type', 'product_seller_id'));
        
        $shippedBySeller = 0;
        if (Product::isProductShippedBySeller($productId, $product_added_by_admin_arr['product_seller_id'], $sellerProductRow['selprod_user_id'])) {
            $shippedBySeller = 1;
        }
        $optionValues = array();
        if (isset($sellerProductRow['selprodoption_optionvalue_id'])) {
            foreach ($sellerProductRow['selprodoption_optionvalue_id'] as $opId => $op) {
                $optionValue = new OptionValue($op[$opId]);
                $option = $optionValue->getOptionValue($opId);
                $optionValues[] = $option['optionvalue_name' . $this->adminLangId];
            }
        }
        $memberShipPlans = [];
        if ($selprodId > 0 && FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) {
            $memberShipPlans = SellerProduct::getMembershipPlanBySelprod($selprodId);
        }

        $this->set('product_added_by_admin', $product_added_by_admin_arr['product_added_by_admin_id']);
        $this->set('shippedBySeller', $shippedBySeller);
        $this->set('frmSellerProduct', $frmSellerProduct);
        $this->set('optionValues', $optionValues);
        $this->set('product_id', $productId);
        $this->set('selprod_id', $selprodId);
        $this->set('language', Language::getAllNames());
        $this->set('memberShipPlans', $memberShipPlans);
        $this->_template->render(false, false);
    }

    public function setUpSellerProduct()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        if (FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) {
            $membershipPlans = json_decode(FatApp::getPostedData('membership_plan'), true);
            if (empty($membershipPlans)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Membership_Plan_is_required', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $membershipPlanIds = array_column($membershipPlans, 'id');
        }

        $selprod_id = Fatutility::int($post['selprod_id']);
        $selprod_product_id = Fatutility::int($post['selprod_product_id']);
        
        $selprod_stock = Fatutility::int($post['sprodata_rental_stock']);
        $selprod_min_order_qty = Fatutility::int($post['sprodata_minimum_rental_quantity']);
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);

        if (!$selprod_product_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $productRow = Product::getAttributesById($selprod_product_id, array('product_id', 'product_active', 'product_seller_id'));
        if (!$productRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (($selprod_min_order_qty > $selprod_stock || 1 > $selprod_min_order_qty)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Minimum_quantity_should_be_less_than_equal_to_stock_quantity.', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSellerProductForm($selprod_product_id);
        $post['use_shop_policy'] = $useShopPolicy;
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        unset($post['selprod_id']);
        $post['selprod_enable_rfq'] = (isset($post['selprod_enable_rfq'])) ? $post['selprod_enable_rfq'] : 0;
        $sellerProdObj = new SellerProduct($selprod_id);
        $data_to_be_save = $post;
        $sellerProdObj->assignValues($data_to_be_save);

        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = $sellerProdObj->getMainTableRecordId();

        /* [ Save Rental Data ] */
        if ($selprod_id) {
            if (FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) {
                if (!$sellerProdObj->updateMembershipDetails($membershipPlanIds, $this->adminLangId)) {
                    Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            }

            $srch = ProductRental::getSearchObject();
            $srch->addCondition('sprodata_selprod_id', '=', $selprod_id);
            $rs = $srch->getResultSet();
            $prodRentalData = FatApp::getDb()->fetch($rs);
            if ($prodRentalData == false) {
                $prodRentalData = [];
            }

            $prodRentalData['sprodata_is_for_sell'] = 1;
            $prodRentalData['sprodata_is_for_rent'] = 1;
            $prodRentalData['sprodata_selprod_id'] = $selprod_id;
            $prodRentalData['sprodata_minimum_rental_quantity'] = $post['sprodata_minimum_rental_quantity'];
            $prodRentalData['sprodata_rental_condition'] = $post['sprodata_rental_condition'];
            $prodRentalData['sprodata_rental_available_from'] = $post['sprodata_rental_available_from'];
            $prodRentalData['sprodata_rental_active'] = $post['sprodata_rental_active'];
            $prodRentalData['sprodata_minimum_rental_duration'] = $post['sprodata_minimum_rental_duration'];
            $prodRentalData['sprodata_rental_stock'] = $post['sprodata_rental_stock'];
            $prodRentalData['sprodata_is_rental_data_updated'] = date('Y-m-d H:i:s');

            if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) {
                $prodRentalData['sprodata_rental_security'] = $post['sprodata_rental_security'];
                $prodRentalData['sprodata_rental_price'] = $post['sprodata_rental_price'];
                $prodRentalData['sprodata_rental_buffer_days'] = $post['sprodata_rental_buffer_days'];
            }

            $record = new ProductRental();
            if (!$record->addUpdateSelProData($prodRentalData)) {
                Message::addErrorMessage($record->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        /* [ Save Rental Data ] */

        $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
        if (0 < $useShopPolicy) {
            $whr = [
                'smt' => 'sps_selprod_id = ? AND selprod_specific_type = ?',
                'vals' => [$selprod_id, applicationConstants::PRODUCT_FOR_RENT]
            ];

            if (!FatApp::getDb()->deleteRecords(SellerProductSpecifics::DB_TBL, $whr)) {
                FatUtility::dieJsonError(FatApp::getDb()->getError());
            }
        } else {
            $post['sps_selprod_id'] = $selprod_id;
            $post['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_RENT;
            $selProdSpecificsObj->assignValues($post);
            $data = $selProdSpecificsObj->getFlds();
            if (!$selProdSpecificsObj->addNew(array(), $data)) {
                FatUtility::dieJsonError($selProdSpecificsObj->getError());
            }
        }

        /* Add Url rewriting  [  ---- */
        $sellerProdObj->rewriteUrlProduct($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlReviews($post['selprod_url_keyword']);
        $sellerProdObj->rewriteUrlMoreSellers($post['selprod_url_keyword']);
        /* --------  ] */
        
        $sellerProdObj = new SellerProduct($selprod_id);
        if (!$sellerProdObj->saveProductLangData($post)) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);
        Product::updateMinPrices($productId);

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('LBL_Product_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function translatedProductData()
    {
        $prodName = FatApp::getPostedData('product_name', FatUtility::VAR_STRING, '');
        $prodDesc = FatApp::getPostedData('product_description', FatUtility::VAR_STRING, '');
        $rentalTerm = FatApp::getPostedData('rentalTerm', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data = array(
            'product_name' => $prodName,
            'product_description' => $prodDesc,
            'rentalTerm' => $rentalTerm,
        );
        $product = new Product();
        $translatedData = $product->getTranslatedProductData($data, $toLangId);
        if (!$translatedData) {
            Message::addErrorMessage($product->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('productName', $translatedData[$toLangId]['product_name']);
        $this->set('productDesc', $translatedData[$toLangId]['product_description']);
        $this->set('rentalTerm', $translatedData[$toLangId]['rentalTerm']);
        $this->set('msg', Labels::getLabel('LBL_Product_Data_Translated_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function sellerProductLangForm($selprod_id, $langId, $autoFillLangData = 0)
    {
        $langId = FatUtility::int($langId);
        $selprod_id = FatUtility::int($selprod_id);

        if ($langId == 0 || $selprod_id == 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        if (!$sellerProductRow) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }


        $frmSellerProdLangFrm = $this->getSellerProductLangForm($langId, $selprod_id);
        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(SellerProduct::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($selprod_id, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = SellerProduct::getAttributesByLangId($langId, $selprod_id);
        }

        $langData['selprod_product_id'] = $sellerProductRow['selprod_product_id'];
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        if ($langData) {
            $frmSellerProdLangFrm->fill($langData);
        }
        $this->set('customActiveTab', '');
        $this->set('frmSellerProdLangFrm', $frmSellerProdLangFrm);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('selprod_id', $selprod_id);
        $this->set('formLangId', $langId);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('language', Language::getAllNames());
        $this->set('product_type', $productRow['product_type']);
        $this->set('activeTab', 'GENERAL');
        $this->_template->render(false, false);
    }

    private function getSellerProductLangForm($formLangId, $selprod_id = 0)
    {
        $formLangId = FatUtility::int($formLangId);

        $frm = new Form('frmSellerProductLang');
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $formLangId, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $formLangId), 'selprod_title');
        /* $frm->addTextArea( Labels::getLabel( 'LBL_Features', $formLangId), 'selprod_features');
          $frm->addTextArea( Labels::getLabel( 'LBL_Warranty', $formLangId), 'selprod_warranty');
          $frm->addTextArea( Labels::getLabel( 'LBL_Return_Policy', $formLangId), 'selprod_return_policy'); */
        $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $formLangId), 'selprod_comments');
        $frm->addHiddenField('', 'selprod_product_id');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);

        $adminLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $formLangId === $adminLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $formLangId));
        /* $fld2 = $frm->addButton('','btn_cancel', Labels::getLabel('LBL_Cancel', $formLangId), array('onClick' => 'cancelForm(this)') );
          $fld1->attachField($fld2); */
        return $frm;
    }

    public function setUpSellerProductLang()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $selprod_id = Fatutility::int($post['selprod_id']);
        $lang_id = Fatutility::int($post['lang_id']);
        $selprod_product_id = Fatutility::int($post['selprod_product_id']);

        if ($selprod_id == 0 || $selprod_product_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSellerProductLangForm($lang_id, $selprod_id);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id'));

        $data = array(
            'selprodlang_selprod_id' => $selprod_id,
            'selprodlang_lang_id' => $lang_id,
            'selprod_title' => $post['selprod_title'],
            'selprod_comments' => $post['selprod_comments'],
        );

        $obj = new SellerProduct($selprod_id);
        if (!$obj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage(Labels::getLabel($obj->getError(), $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(SellerProduct::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($selprod_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        if ($selprod_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = SellerProduct::getAttributesByLangId($langId, $selprod_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $selprod_id = $sellerProdObj->getMainTableRecordId();
        }
        $this->set('selprod_id', $selprod_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function addPolicyPoint()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        if (empty($post['selprod_id']) || empty($post['ppoint_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = FatUtility::int($post['selprod_id']);
        $ppoint_id = FatUtility::int($post['ppoint_id']);
        $dataToSave = array('sppolicy_ppoint_id' => $ppoint_id, 'sppolicy_selprod_id' => $selprod_id);
        $obj = new SellerProduct();
        if (!$obj->addPolicyPointToSelProd($dataToSave)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set("msg", Labels::getLabel('LBL_Policy_Added_Successfully', $this->adminLangId));

        $this->_template->render(false, false, 'json-success.php');
    }

    public function removePolicyPoint()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        if (empty($post['selprod_id']) || empty($post['ppoint_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = FatUtility::int($post['selprod_id']);
        $ppoint_id = FatUtility::int($post['ppoint_id']);
        $whereCond = array('smt' => 'sppolicy_ppoint_id = ? and sppolicy_selprod_id = ?', 'vals' => array($ppoint_id, $selprod_id));
        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $whereCond)) {
            Message::addErrorMessage($db->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set("msg", Labels::getLabel('LBL_Policy_Removed_Successfully', $this->adminLangId));

        $this->_template->render(false, false, 'json-success.php');

        //FatUtility::dieJsonSuccess(Labels::getLabel('LBL_Policy_Removed_Successfully',$this->adminLangId));
    }

    /* Seller Product Seo [ */

    public function productSeo($selprod_id = 0)
    {
        $selprod_id = Fatutility::int($selprod_id);

        $this->set('activeTab', 'SEO');
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;
        $this->set('metaType', $metaType);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('selprod_id', $selprod_id);
        $this->_template->render(false, false);
    }

    public function productSeoGeneralForm()
    {
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;
        $this->set('metaType', $metaType);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));
        $prodMetaData = Product::getProductMetaData($selprod_id);

        $metaId = 0;

        if (!empty($prodMetaData)) {
            $metaId = $prodMetaData['meta_id'];
        }
        $productSeoForm = $this->getProductSeoForm($metaId, $metaType, $selprod_id);
        $productSeoForm->fill($prodMetaData);
        $this->set('metaId', $metaId);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('selprod_id', $selprod_id);
        $this->set('selprod_lang_id', '');
        $this->set('languages', Language::getAllNames());
        $this->set('productSeoForm', $productSeoForm);
        $this->set('activeTab', 'SEO');
        $this->set('seoActiveTab', 'GENERAL');
        $this->set('product_type', $productRow['product_type']);
        $this->_template->render(false, false);
    }

    /* private function getProductSeoForm($metaTagId = 0, $metaType = 'default', $recordId = 0)
      {
      $metaTagId = FatUtility::int($metaTagId);
      $frm = new Form('frmMetaTag');
      $frm->addHiddenField('', 'meta_id', $metaTagId);
      $tabsArr = MetaTag::getTabsArr();
      $frm->addHiddenField('', 'meta_type', $metaType);

      if ($metaTagId != 0 && ($metaType == '' || !isset($tabsArr[$metaType]))) {
      Message::addErrorMessage($this->str_invalid_request);
      FatUtility::dieJsonError(Message::getHtml());
      }
      $frm->addHiddenField(Labels::getLabel('LBL_Entity_Id', $this->adminLangId), 'meta_record_id', $recordId);
      $frm->addRequiredField(Labels::getLabel('LBL_Identifier', $this->adminLangId), 'meta_identifier');
      $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
      return $frm;
      } */

    public function productSeoLangForm($metaId, $langId, $autoFillLangData = 0)
    {
        $metaId = Fatutility::int($metaId);
        $metaData = MetaTag::getAttributesById($metaId);
        $meta_record_id = $metaData['meta_record_id'];
        if (!UserPrivilege::canEditMetaTag($metaId, $meta_record_id)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sellerProductRow = SellerProduct::getAttributesById($metaData['meta_record_id']);

        $this->set('activeTab', 'SEO');
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;
        $this->set('metaType', $metaType);
        $prodSeoLangFrm = $this->getSeoLangForm($metaId, $langId);

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(MetaTag::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($metaId, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $metaData = current($translatedData);
        } else {
            $metaData = MetaTag::getAttributesByLangId($langId, $metaId);
        }

        $prodSeoLangFrm->fill($metaData);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        $this->set('languages', Language::getAllNames());
        $this->set('productSeoLangForm', $prodSeoLangFrm);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('metaId', $metaId);
        $this->set('selprod_id', $sellerProductRow[SellerProduct::DB_TBL_PREFIX . 'id']);
        $this->set('product_id', $sellerProductRow[SellerProduct::DB_TBL_PREFIX . 'product_id']);
        $this->set('selprod_lang_id', $langId);
        $this->set('product_type', $productRow['product_type']);
        $this->set('seoActiveTab', '');

        $this->_template->render(false, false);
    }

    private function getSeoLangForm($metaId = 0, $lang_id = 0)
    {
        $frm = new Form('frmMetaTagLang');
        $frm->addHiddenField('', 'meta_id', $metaId);
        $tabsArr = MetaTag::getTabsArr();
        $frm->addHiddenField('', 'meta_type', $metaType);

        if ($metaTagId != 0 && ($metaType == '' || !isset($tabsArr[$metaType]))) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm->addHiddenField('', 'meta_record_id', $recordId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->adminLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Meta_Title', $this->adminLangId), 'meta_title');
        $frm->addTextarea(Labels::getLabel('LBL_Meta_Keywords', $this->adminLangId), 'meta_keywords')->requirements()->setRequired(true);
        $frm->addTextarea(Labels::getLabel('LBL_Meta_Description', $this->adminLangId), 'meta_description')->requirements()->setRequired(true);
        $fld = $frm->addTextarea(Labels::getLabel('LBL_Other_Meta_Tags', $this->adminLangId), 'meta_other_meta_tags');
        $fld->htmlAfterField = '<small>' . Labels::getLabel('LBL_For_Example:', $this->adminLangId) . ' ' . htmlspecialchars('<meta name="copyright" content="text">') . '</small>';

        $adminLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $adminLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->adminLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function setupProdMeta()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $metaId = FatUtility::int($post['meta_id']);
        $metaReocrdId = FatUtility::int($post['meta_record_id']);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }



        $tabsArr = MetaTag::getTabsArr();
        $metaType = FatUtility::convertToType($post['meta_type'], FatUtility::VAR_STRING);

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getProductSeoForm($metaId, $metaType, $post['meta_record_id']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());



        $post['meta_controller'] = $tabsArr[$metaType]['controller'];
        $post['meta_action'] = $tabsArr[$metaType]['action'];
        if ($metaId == 0) {
            $post['meta_subrecord_id'] = 0;
        }


        $record = new MetaTag($metaId);

        $record->assignValues($post);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $newTabLangId = 0;
        if ($metaId > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = MetaTag::getAttributesByLangId($langId, $metaId)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $metaId = $record->getMainTableRecordId();
            $newTabLangId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('metaId', $metaId);
        $this->set('metaType', $metaType);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupProdMetaLang()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();

        $metaId = $post['meta_id'];
        $lang_id = $post['lang_id'];

        if ($metaId == 0 || $lang_id == 0) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!UserPrivilege::canEditMetaTag($metaId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if (!empty($post['meta_other_meta_tags']) && $post['meta_other_meta_tags'] == strip_tags($post['meta_other_meta_tags'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Other_Meta_Tag', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->getSeoLangForm($metaId, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['meta_id']);
        unset($post['lang_id']);

        $data = array(
            'metalang_lang_id' => $lang_id,
            'metalang_meta_id' => $metaId,
            'meta_title' => strip_tags($post['meta_title']),
            'meta_keywords' => strip_tags($post['meta_keywords']),
            'meta_description' => strip_tags($post['meta_description']),
            'meta_other_meta_tags' => $post['meta_other_meta_tags'],
        );

        $metaObj = new MetaTag($metaId);

        if (!$metaObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($metaObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(MetaTag::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($metaId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = MetaTag::getAttributesByLangId($langId, $metaId)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('metaId', $metaId);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    /*  --- ] Seller Product Seo  --- -   */

    /*  - --- Seller Product Links  ----- [ */

    public function sellerProductLinkFrm($selProd_id)
    {
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($selProd_id);
        $sellProdObj = new SellerProduct();
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));
        $upsellProds = $sellProdObj->getUpsellProducts($selprod_id, $this->adminLangId);
        $relatedProds = $sellProdObj->getRelatedProducts($this->adminLangId, $selprod_id);
        $sellerproductLinkFrm = $this->getLinksFrm();
        $data['selprod_id'] = $selProd_id;
        $sellerproductLinkFrm->fill($data);
        $this->set('sellerproductLinkFrm', $sellerproductLinkFrm);
        $this->set('upsellProducts', $upsellProds);
        $this->set('relatedProducts', $relatedProds);
        $this->set('selprod_id', $selProd_id);
        $this->set('product_id', $sellerProductRow[SellerProduct::DB_TBL_PREFIX . 'product_id']);
        $this->set('product_type', $productRow['product_type']);
        $this->set('activeTab', 'LINKS');
        $this->_template->render(false, false);
    }

    private function getLinksFrm()
    {
        $prodObj = new Product();

        $frm = new Form('frmLinks', array('id' => 'frmLinks'));

        $frm->addTextBox(Labels::getLabel('LBL_Buy_Together_Products', $this->adminLangId), 'products_buy_together');

        $frm->addHtml('', 'buy_together', '<div id="buy-together-products" class="box--scroller"><ul class="links--vertical"></ul></div>');

        $frm->addTextBox(Labels::getLabel('LBL_Related_Products', $this->adminLangId), 'products_related');

        $frm->addHtml('', 'related_products', '<div id="related-products" class="box--scroller"><ul class="links_vertical"></ul></div>');

        $frm->addHiddenField('', 'selprod_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function autoCompleteProducts(int $saleOnly = 0, int $rentOnly = 0)
    {
        $pagesize = 20;
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }

        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = product_id', 'ptc');
        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');
        $srch->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_lang.prodcatlang_prodcat_id = c.prodcat_id AND c_lang.prodcatlang_lang_id='. $this->adminLangId, 'c_lang');

        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');

        $srch->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);
        if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', 'tb.brand_id = product_brand_id and tb.brand_active = ' . applicationConstants::YES . ' and tb.brand_deleted = ' . applicationConstants::NO, 'tb');
        } else {
            $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'tb.brand_id = product_brand_id', 'tb');
            $srch->addDirectCondition("(case WHEN brand_id > 0 THEN (tb.brand_active = " . applicationConstants::YES . " AND tb.brand_deleted = " . applicationConstants::NO . ") else TRUE end)");
        }

        $srch->addOrder('product_name');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd = $cnd->attachCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        if (!empty($post['selProdId']) && 0 < FatUtility::int($post['selProdId'])) {
            $selprod_user = SellerProduct::getAttributesById($post['selProdId'], array('selprod_user_id'));
            $srch->addCondition('selprod_user_id', '=', $selprod_user['selprod_user_id']);
            $srch->addCondition('selprod_id', '!=', $post['selProdId']);
        }
        
        if ($saleOnly > 0) {
            $srch->addCondition('selprod_active', '=', applicationConstants::YES);
        }
        if ($rentOnly > 0) {
            $srch->addCondition('sprodata_rental_active', '=', applicationConstants::YES);
        }
        if ($saleOnly == 0 && $rentOnly == 0) {
            $cnd = $srch->addCondition('selprod_active', '=', applicationConstants::YES);
            $cnd->attachCondition('sprodata_rental_active', '=', applicationConstants::YES);
        }
        
        $srch->addCondition(Product::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
/*         $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE); */
        $srch->addMultipleFields(array('selprod_id as id', 'IFNULL(selprod_title ,product_name) as product_name', 'product_identifier', 'credential_username', 'selprod_price', 'selprod_stock','sprodata_rental_price', 'sprodata_duration_type'));
        
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('product_approved', '=', applicationConstants::YES);

        $srch->addOrder('selprod_active', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $products = array();
        if ($rs) {
            $products = $db->fetchAll($rs, 'id');
        }
        $pageCount = $srch->pages();
        $json = array();
        $durationTypes = ProductRental::durationTypeArr($this->adminLangId);
        foreach ($products as $key => $option) {
            $options = SellerProduct::getSellerProductOptions($key, true, $this->adminLangId);
            $variantsStr = '';
            array_walk($options, function ($item, $key) use (&$variantsStr) {
                $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
            });
            $userName = isset($option["credential_username"]) ? " | " . $option["credential_username"] : '';
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')) . $variantsStr . $userName,
                'product_identifier' => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8')),
                'price' => $option['selprod_price'],
                'rental_price' => $option['sprodata_rental_price'],
                'stock' => $option['selprod_stock'],
                'duration_type' => $option['sprodata_duration_type'],
                'duration_label' => (isset($durationTypes[$option['sprodata_duration_type']])) ? $durationTypes[$option['sprodata_duration_type']] : "",
            );
        }
        die(json_encode(['pageCount' => $pageCount, 'products' => $json]));
    }

    public function autoCompleteUserShopName()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $srch = new SearchBase(User::DB_TBL, 'tu');
        $srch->joinTable('tbl_shops', 'INNER JOIN', 'tu.user_id=ts.shop_user_id', 'ts');
        $srch->addOrder('user_name', 'asc');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('user_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd = $cnd->attachCondition('shop_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.' . User::DB_TBL_CRED_PREFIX . 'user_id = tu.user_id', 'uc');
        $srch->addCondition('uc.' . User::DB_TBL_CRED_PREFIX . 'active', '=', 1);
        $srch->addCondition('user_is_supplier', '=', 1);

        $srch->addMultipleFields(array('user_id', 'user_name', 'shop_identifier'));
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        // echo  $srch->getQuery(); die;
        $users = array();
        if ($rs) {
            $users = $db->fetchAll($rs);
        }
        foreach ($users as $key => $option) {
            $json[] = array(
                'user_id' => $option['user_id'],
                'user_name' => strip_tags(html_entity_decode($option['user_name'], ENT_QUOTES, 'UTF-8')),
                'shop_identifier' => strip_tags(html_entity_decode($option['shop_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function setupSellerProductLinks()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        /* if(!UserPrivilege::canEditSellerProduct($selprod_id))
          {
          Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS",$this->adminLangId));
          FatUtility::dieJsonError( Message::getHtml() );
          } */
        $upsellProducts = (isset($post['product_upsell'])) ? $post['product_upsell'] : array();
        $relatedProducts = (isset($post['product_related'])) ? $post['product_related'] : array();
        unset($post['selprod_id']);

        if ($selprod_id <= 0) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }

        $sellerProdObj = new SellerProduct();
        /* saving of product Upsell Product[ */
        if (!$sellerProdObj->addUpdateSellerUpsellProducts($selprod_id, $upsellProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */
        /* saving of Related Products[ */


        if (!$sellerProdObj->addUpdateSellerRelatedProdcts($selprod_id, $relatedProducts)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('msg', 'Record Updated Successfully!');
        $this->_template->render(false, false, 'json-success.php');
    }

    /*  - ---  ] Seller Product Links  ----- */

    public function sellerProductSpecialPrices($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        $arrListing = SellerProduct::getSellerProductSpecialPrices($selprod_id);
        $this->set('arrListing', $arrListing);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('product_type', $productRow['product_type']);
        $this->set('activeTab', 'SPECIAL_PRICE');
        $this->_template->render(false, false);
    }

    public function sellerProductSpecialPriceForm($selprod_id, $splprice_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $splprice_id = FatUtility::int($splprice_id);
        if (!$selprod_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        $frmSellerProductSpecialPrice = $this->getSellerProductSpecialPriceForm();
        $specialPriceRow = array();
        if ($splprice_id) {
            $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);
            if (!$tblRecord->loadFromDb(array('smt' => 'splprice_id = ?', 'vals' => array($splprice_id)))) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
            $specialPriceRow = $tblRecord->getFlds();
        }
        $specialPriceRow['splprice_selprod_id'] = $selprod_id;
        $frmSellerProductSpecialPrice->fill($specialPriceRow);

        $this->set('frmSellerProductSpecialPrice', $frmSellerProductSpecialPrice);
        $this->set('selprod_id', $selprod_id);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('adminLangId', $this->adminLangId);
        $this->set('activeTab', 'SPECIAL_PRICE');
        $this->_template->render(false, false);
    }

    private function getSellerProductSpecialPriceForm()
    {
        return SellerProduct::specialPriceForm($this->adminLangId);
    }

    public function setUpSellerProductSpecialPrice()
    {
        $this->objPrivilege->canEditSellerProducts();
        $frm = $this->getSellerProductSpecialPriceForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            FatUtility::dieJsonError(current($frm->getValidationErrors()));
        }

        $resp = $this->updateSelProdSplPrice($post);
        if (!$resp) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if (!empty($post['splprice_selprod_id'])) {
            $productId = SellerProduct::getAttributesById($post['splprice_selprod_id'], 'selprod_product_id', false);
            Product::updateMinPrices($productId);
        }

        $this->set('msg', Labels::getLabel('LBL_Special_Price_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSelProdSplPrice($post, $return = false)
    {
        $selprod_id = !empty($post['splprice_selprod_id']) ? FatUtility::int($post['splprice_selprod_id']) : 0;
        $splprice_id = !empty($post['splprice_id']) ? FatUtility::int($post['splprice_id']) : 0;

        if (1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if (strtotime($post['splprice_start_date']) > strtotime($post['splprice_end_date'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Dates', $this->adminLangId));
        }

        $prodSrch = new ProductSearch($this->adminLangId);
        $prodSrch->joinSellerProducts();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addMultipleFields(array('product_min_selling_price', 'selprod_price'));
        $prodSrch->setPageSize(1);
        $rs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($rs);

        /* if (!isset($post['splprice_price']) || $post['splprice_price'] < $product['product_min_selling_price'] || $post['splprice_price'] >= $product['selprod_price']) {
            $str = Labels::getLabel('MSG_Price_must_between_min_selling_price_{minsellingprice}_and_selling_price_{sellingprice}', $this->adminLangId);
            $minSellingPrice = CommonHelper::displayMoneyFormat($product['product_min_selling_price'], false, true, true);
            $sellingPrice = CommonHelper::displayMoneyFormat($product['selprod_price'], false, true, true);

            $message = CommonHelper::replaceStringData($str, array('{minsellingprice}' => $minSellingPrice, '{sellingprice}' => $sellingPrice));
            FatUtility::dieJsonError($message);
        } */

        /* Check if same date already exists [ */
        $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);

        $smt = 'splprice_selprod_id = ? AND ';
        $smt .= '(
                    ((splprice_start_date between ? AND ?) OR (splprice_end_date between ? AND ?))
                    OR
                    ((? BETWEEN splprice_start_date AND splprice_end_date) OR (? BETWEEN  splprice_start_date AND splprice_end_date))
                ) AND splprice_type = ?';
                
        $smtValues = array(
            $selprod_id,
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['product_for'],
        );

        if (0 < $splprice_id) {
            $smt .= 'AND splprice_id != ?';
            $smtValues[] = $splprice_id;
        }
        $condition = array(
            'smt' => $smt,
            'vals' => $smtValues
        );
        // CommonHelper::printArray($condition, true);
        if ($tblRecord->loadFromDb($condition)) {
            $specialPriceRow = $tblRecord->getFlds();
            if ($specialPriceRow['splprice_id'] != $splprice_id) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Special_price_for_this_date_already_added', $this->adminLangId));
            }
        }
        /* ] */

        $data_to_save = array(
            'splprice_selprod_id' => $selprod_id,
            'splprice_start_date' => $post['splprice_start_date'],
            'splprice_end_date' => $post['splprice_end_date'],
            'splprice_price' => $post['splprice_price'],
            'splprice_type' => $post['product_for'],
        );

        if (0 < $splprice_id) {
            $data_to_save['splprice_id'] = $splprice_id;
        }

        $sellerProdObj = new SellerProduct();

        // Return Special Price ID if $return is true else it will return bool value.
        $splPriceId = $sellerProdObj->addUpdateSellerProductSpecialPrice($data_to_save, $return);
        if (false === $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
        }

        return $splPriceId;
    }

    public function deleteSellerProductSpecialPrice()
    {
        $this->objPrivilege->canEditSellerProducts();
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
        if (empty($specialPriceRow) || 1 > count($specialPriceRow)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Already_Deleted', $this->adminLangId));
        }
        $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSpecialPriceRows()
    {
        $this->objPrivilege->canEditSellerProducts();
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $splPriceId => $selProdId) {
            $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
            $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        }
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteSpecialPrice($splPriceId, $selProdId)
    {
        $sellerProdObj = new SellerProduct($selProdId);
        if (!$sellerProdObj->deleteSellerProductSpecialPrice($splPriceId, $selProdId)) {
            FatUtility::dieWithError(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
        }
        return true;
    }

    /* Seller Product Volume Discount [ */

    public function sellerProductVolumeDiscounts($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id', 'selprod_id', 'selprod_product_id'));
        $productRow = Product::getAttributesById($sellerProductRow['selprod_product_id'], array('product_type'));

        $srch = new SellerProductVolumeDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('voldiscount_selprod_id', '=', $selprod_id);
        $rs = $srch->getResultSet();

        $arrListing = FatApp::getDb()->fetchAll($rs);
        $this->set('arrListing', $arrListing);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('activeTab', 'VOLUME_DISCOUNT');
        $this->set('product_type', $productRow['product_type']);
        $productLangRow = Product::getAttributesByLangId($this->adminLangId, $sellerProductRow['selprod_product_id'], array('product_name'));
        $this->set('productCatalogName', $productLangRow['product_name']);

        $this->_template->render(false, false);
    }

    public function sellerProductVolumeDiscountForm($selprod_id, $voldiscount_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $voldiscount_id = FatUtility::int($voldiscount_id);
        if ($selprod_id <= 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_id', 'selprod_user_id', 'selprod_product_id'));
        if ($selprod_id != $sellerProductRow['selprod_id']) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
        }

        $frmSellerProductVolDiscount = $this->getSellerProductVolumeDiscountForm($this->adminLangId);
        $volumeDiscountRow = array();
        if ($voldiscount_id) {
            $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
            if (!$volumeDiscountRow) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
        }
        $volumeDiscountRow['voldiscount_selprod_id'] = $sellerProductRow['selprod_id'];
        $frmSellerProductVolDiscount->fill($volumeDiscountRow);
        $this->set('frmSellerProductVolDiscount', $frmSellerProductVolDiscount);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->set('activeTab', 'VOLUME_DISCOUNT');
        $this->_template->render(false, false);
    }

    public function setUpSellerProductVolumeDiscount()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['voldiscount_selprod_id']);
        $voldiscount_id = FatUtility::int($post['voldiscount_id']);

        if (!$selprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSellerProductVolumeDiscountForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $resp = $this->updateSelProdVolDiscount($selprod_id, $voldiscount_id, $post['voldiscount_min_qty'], $post['voldiscount_percentage']);

        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSelProdVolDiscount($selprod_id, $voldiscount_id, $minQty, $perc)
    {
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id, array('selprod_user_id', 'selprod_stock', 'selprod_min_order_qty'), false);
        if ($minQty > $sellerProductRow['selprod_stock']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Quantity_cannot_be_more_than_the_Stock', $this->adminLangId));
        }

        if ($minQty < $sellerProductRow['selprod_min_order_qty']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Quantity_cannot_be_less_than_the_Minimum_Order_Quantity', $this->adminLangId) . ': ' . $sellerProductRow['selprod_min_order_qty']);
        }

        if ($perc > 100 || 1 > $perc) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Percentage', $this->adminLangId));
        }

        /* Check if volume discount for same quantity already exists [ */
        $tblRecord = new TableRecord(SellerProductVolumeDiscount::DB_TBL);
        $smt = 'voldiscount_selprod_id = ? AND voldiscount_min_qty = ? ';
        $smtValues = array($selprod_id, $minQty);

        if (0 < $voldiscount_id) {
            $smt .= 'AND voldiscount_id != ?';
            $smtValues[] = $voldiscount_id;
        }
        $condition = array(
            'smt' => $smt,
            'vals' => $smtValues
        );
        if ($tblRecord->loadFromDb($condition)) {
            $volDiscountRow = $tblRecord->getFlds();
            if ($volDiscountRow['voldiscount_id'] != $voldiscount_id) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Volume_discount_for_this_quantity_already_added', $this->adminLangId));
            }
        }
        /* ] */

        $data_to_save = array(
            'voldiscount_selprod_id' => $selprod_id,
            'voldiscount_min_qty' => $minQty,
            'voldiscount_percentage' => $perc
        );

        if ($voldiscount_id > 0) {
            $data_to_save['voldiscount_id'] = $voldiscount_id;
        }

        $record = new TableRecord(SellerProductVolumeDiscount::DB_TBL);
        $record->assignValues($data_to_save);
        if (!$record->addNew(array(), $data_to_save)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        return ($voldiscount_id > 0) ? $voldiscount_id : $record->getId();
    }

    public function deleteSellerProductVolumeDiscount()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $voldiscount_id = FatApp::getPostedData('voldiscount_id', FatUtility::VAR_INT, 0);
        if (!$voldiscount_id) {
            Message::addErrorMessge(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
        $sellerProductRow = SellerProduct::getAttributesById($volumeDiscountRow['voldiscount_selprod_id'], array('selprod_user_id'), false);
        if (!$volumeDiscountRow || !$sellerProductRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->deleteVolumeDiscount($voldiscount_id, $volumeDiscountRow['voldiscount_selprod_id']);

        $this->set('selprod_id', $volumeDiscountRow['voldiscount_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteVolumeDiscountArr()
    {
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $voldiscount_id => $selProdId) {
            $volumeDiscountRow = SellerProductVolumeDiscount::getAttributesById($voldiscount_id);
            $sellerProductRow = SellerProduct::getAttributesById($volumeDiscountRow['voldiscount_selprod_id'], array('selprod_user_id'), false);
            if (!$volumeDiscountRow || !$sellerProductRow) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $this->deleteVolumeDiscount($voldiscount_id, $volumeDiscountRow['voldiscount_selprod_id']);
        }
        $this->set('msg', Labels::getLabel('LBL_Volume_Discount_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteVolumeDiscount($volumeDiscountId, $volumeDiscountSelprodId)
    {
        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProductVolumeDiscount::DB_TBL, array('smt' => 'voldiscount_id = ? AND voldiscount_selprod_id = ?', 'vals' => array($volumeDiscountId, $volumeDiscountSelprodId)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        return true;
    }

    private function getSellerProductVolumeDiscountForm($langId)
    {
        return SellerProduct::volumeDiscountForm($langId);
    }

    /* ] */

    public function productTaxRates($selprod_id)
    {
        $selprod_id = Fatutility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        $taxRates[] = $this->getTaxRates($sellerProductRow['selprod_product_id']);

        $this->set('arrListing', $taxRates);
        $this->set('activeTab', 'TAX');
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);

        $this->_template->render(false, false);
    }

    private function getTaxRates($productId, $userId = 0)
    {
        $productId = Fatutility::int($productId);
        $userId = Fatutility::int($userId);

        $taxRates = array();
        $taxObj = Tax::getTaxCatObjByProductId($productId, $this->adminLangId);
        $taxObj->addMultipleFields(array('IFNULL(taxcat_name,taxcat_identifier) as taxcat_name', 'ptt_seller_user_id', 'ptt_taxcat_id', 'ptt_product_id'));
        $taxObj->doNotCalculateRecords();

        $cnd = $taxObj->addCondition('ptt_seller_user_id', '=', 0);
        if ($userId > 0) {
            $cnd->attachCondition('ptt_seller_user_id', '=', $userId, 'OR');
        }
        $taxObj->setPageSize(1);
        $taxObj->addOrder('ptt_seller_user_id', 'DESC');

        $rs = $taxObj->getResultSet();
        if ($rs) {
            $taxRates = FatApp::getDb()->fetch($rs);
        }
        return $taxRates ? $taxRates : array();
    }

    private function changeTaxCategoryForm($langId)
    {
        $frm = new Form('frmTaxRate');
        $frm->addHiddenField('', 'selprod_id');
        $taxCatArr = Tax::getSaleTaxCatArr($langId);

        $frm->addSelectBox(Labels::getLabel('LBL_Tax_Category', $langId), 'ptt_taxcat_id', $taxCatArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function changeTaxCategory($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        /* $srch = Tax::getSearchObject($this->adminLangId);
          $srch->addMultipleFields(array('taxcat_id','IFNULL(taxcat_name,taxcat_identifier) as taxcat_name'));
          $rs =  $srch->getResultSet();
          if($rs){
          $records = FatApp::getDb()->fetchAll($rs,'taxcat_id');
          }
          var_dump($records); */
        $taxRates = $this->getTaxRates($sellerProductRow['selprod_product_id'], $sellerProductRow['selprod_user_id']);
        $frm = $this->changeTaxCategoryForm($this->adminLangId);

        $frm->fill($taxRates + array('selprod_id' => $sellerProductRow['selprod_id']));

        $this->set('frm', $frm);
        $this->set('selprod_id', $sellerProductRow['selprod_id']);
        $this->set('product_id', $sellerProductRow['selprod_product_id']);
        $this->_template->render(false, false);
    }

    public function setUpTaxCategory()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if (!$selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        $data = array(
            'ptt_product_id' => $sellerProductRow['selprod_product_id'],
            'ptt_taxcat_id' => $post['ptt_taxcat_id'],
            'ptt_seller_user_id' => $sellerProductRow['selprod_user_id']
        );

        $obj = new Tax();
        if (!$obj->addUpdateProductTaxCat($data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('MSG_SETUP_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function resetTaxRates($selprod_id)
    {
        $this->objPrivilege->canEditSellerProducts();
        $selprod_id = FatUtility::int($selprod_id);
        $sellerProductRow = SellerProduct::getAttributesById($selprod_id);

        if (!FatApp::getDb()->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, array('smt' => 'ptt_product_id = ? and ptt_seller_user_id = ?', 'vals' => array($sellerProductRow['selprod_product_id'], $sellerProductRow['selprod_user_id'])))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('MSG_Reset_Successfull', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $className = get_class($this);
        $arr = explode('-', FatUtility::camel2dashed($className));
        array_pop($arr);
        $urlController = implode('-', $arr);
        $className = ucwords(implode(' ', $arr));
        if ($action == 'index') {
            $nodes[] = array('title' => Labels::getLabel('LBL_SELLER_INVENTORY', $this->adminLangId));
        } elseif ($action == 'upsellProducts') {
            $nodes[] = array('title' => Labels::getLabel('LBL_BUY_TOGETHER_PRODUCTS', $this->adminLangId));
        } else {
            $arr = explode('-', FatUtility::camel2dashed($action));
            $action = ucwords(implode(' ', $arr));
            $nodes[] = array('title' => $action);
        }
        return $nodes;
    }

    public function autoComplete()
    {
        $this->objPrivilege->canViewSellerProducts();

        $srch = SellerProduct::getSearchObject($this->adminLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $post = FatApp::getPostedData();
        if (!empty($post['keyword'])) {
            $condition = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $condition->attachCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize(FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10));
        $srch->addMultipleFields(array('selprod_id', 'IF(selprod_title is NULL or selprod_title = "" ,product_name, selprod_title) as product_name'));

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs, 'selprod_id');
        $json = array();
        foreach ($products as $key => $product) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($product['product_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function linkPoliciesForm($product_id, $selprod_id, $ppoint_type)
    {
        $product_id = FatUtility::int($product_id);
        $ppoint_type = FatUtility::int($ppoint_type);
        $selprod_id = FatUtility::int($selprod_id);
        if ($product_id <= 0 || $selprod_id <= 0 || $ppoint_type <= 0) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $productRow = Product::getAttributesById($product_id, array('product_type'));
        $frm = $this->getLinkPoliciesForm($selprod_id, $ppoint_type);
        $data = array('selprod_id' => $selprod_id);
        $frm->fill($data);
        $this->set('product_id', $product_id);
        $this->set('selprod_id', $selprod_id);
        $this->set('frm', $frm);
        $this->set('language', Language::getAllNames());
        $this->set('activeTab', 'GENERAL');
        $this->set('product_type', $productRow['product_type']);
        $this->set('ppoint_type', $ppoint_type);
        $this->_template->render(false, false);
    }

    private function getLinkPoliciesForm($selprod_id, $ppoint_type)
    {
        $frm = new Form('frmLinkWarrantyPolicies');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);
        $frm->addHiddenField('', 'ppoint_type', $ppoint_type);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function searchPoliciesToLink()
    {
        $selprod_id = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $ppoint_type = FatApp::getPostedData('ppoint_type', FatUtility::VAR_INT, 0);
        $searchForm = $this->getLinkPoliciesForm($selprod_id, $ppoint_type);
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $post = $searchForm->getFormDataFromArray($data);
        $srch = PolicyPoint::getSearchObject($this->adminLangId);
        $srch->joinTable('tbl_seller_product_policies', 'left outer join', 'spp.sppolicy_ppoint_id = pp.ppoint_id and spp.sppolicy_selprod_id=' . $selprod_id, 'spp');
        $srch->addCondition('pp.ppoint_type', '=', $ppoint_type);
        $srch->addMultipleFields(array('*', 'ifnull(sppolicy_selprod_id,0) selProdId'));
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('selProdId', 'desc');
        $records = FatApp::getDb()->fetchAll($srch->getResultSet(), 'ppoint_id');
        $this->set("selprod_id", $selprod_id);
        $this->set("arr_listing", $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false, 'seller-products/search-policies-to-link.php', false, false);
    }

    /* Catalog Section [ */

    public function catalog()
    {
        $this->objPrivilege->canViewSellerProducts();
        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set('canRequestProduct', User::canRequestProduct());
        $this->_template->render();
    }

    public function requestedCatalog()
    {
        $this->_template->render();
    }

    public function searchRequestedCatalog()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $cRequestObj = new User();
        $srch = $cRequestObj->getUserCatalogRequestsObj();
        $srch->addMultipleFields(
                array(
                    'scatrequest_id',
                    'scatrequest_user_id',
                    'scatrequest_reference',
                    'scatrequest_title',
                    'scatrequest_comments',
                    'scatrequest_status',
                    'scatrequest_date'
                )
        );
        $srch->addOrder('scatrequest_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = array();
        if ($rs) {
            $arr_listing = $db->fetchAll($rs);
        }

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('catalogReqStatusArr', User::getCatalogReqStatusArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function addCatalogRequest()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->addNewCatalogRequestForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setUpCatalogRequest()
    {
        $this->objPrivilege->canEditSellerProducts();
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $userId = UserAuthentication::getLoggedUserId();

        $frm = $this->addNewCatalogRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new User($userId);
        $reference_number = $userId . '-' . time();

        $db = FatApp::getDb();
        $db->startTransaction();

        $data = array(
            'scatrequest_user_id' => $userId,
            'scatrequest_reference' => $reference_number,
            'scatrequest_title' => $post['scatrequest_title'],
            'scatrequest_content' => $post['scatrequest_content'],
            'scatrequest_date' => date('Y-m-d H:i:s'),
        );

        if (!$obj->addCatalogRequest($data)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scatrequest_id = FatApp::getDb()->getInsertId();
        if (!$scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* attach file with request [ */

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);

            if (filesize($uploadedFile) > AttachedFile::IMAGE_MAX_SIZE_IN_BYTES) {
                Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Upload_Size_is', $this->adminLangId). ' ' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . 'KB');
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_SELLER_CATALOG_REQUEST, $scatrequest_id, 0, $_FILES['file']['name'], -1, true)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* ] */

        if (!$obj->notifyAdminCatalogRequest($data, $this->adminLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_CATALOG_REQUESTED_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewRequestedCatalog($scatrequest_id)
    {
        $scatrequest_id = FatUtility::int($scatrequest_id);
        if (1 > $scatrequest_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $cRequestObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_title', 'scatrequest_content', 'scatrequest_comments', 'scatrequest_reference'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();
        if ($rs == false) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $row = FatApp::getDb()->fetch($rs);
        if ($row == false) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $this->set("data", $row);
        $this->_template->render(false, false);
    }

    public function catalogRequestMsgForm($requestId = 0)
    {
        $requestId = FatUtility::int($requestId);
        $frm = $this->getCatalogRequestMessageForm($requestId);

        if (0 >= $requestId) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
        }
        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');

        $rs = $srch->getResultSet();

        if (!$rs || FatApp::getDb()->fetch($rs) === false) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
        }

        $this->set('requestId', $requestId);

        $this->set('frm', $frm);
        $this->set('logged_user_id', UserAuthentication::getLoggedUserId());
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));

        $searchFrm = $this->getCatalogRequestMessageSearchForm();
        $searchFrm->getField('requestId')->value = $requestId;
        $this->set('searchFrm', $searchFrm);

        $this->_template->render(false, false);
    }

    public function catalogRequestMessageSearch()
    {
        $frm = $this->getCatalogRequestMessageSearchForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = 1;

        $requestId = isset($post['requestId']) ? FatUtility::int($post['requestId']) : 0;

        $srch = new CatalogRequestMessageSearch();
        $srch->joinCatalogRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('scatrequestmsg_scatrequest_id', '=', $requestId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('scatrequestmsg_id', 'DESC');
        $srch->addMultipleFields(
                array(
                    'scatrequestmsg_id', 'scatrequestmsg_from_user_id', 'scatrequestmsg_from_admin_id',
                    'admin_name', 'admin_username', 'admin_email', 'scatrequestmsg_msg',
                    'scatrequestmsg_date', 'msg_user.user_name as msg_user_name', 'msg_user_cred.credential_username as msg_username',
                    'msg_user_cred.credential_email as msg_user_email',
                    'scatrequest_status'
                )
        );

        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'scatrequestmsg_id');
        ksort($messagesList);

        $this->set('messagesList', $messagesList);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page - 1) * $pageSize + 1;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;

        $json['html'] = $this->_template->render(false, false, 'seller-products/catalog-request-messages-list.php', true);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'seller-products/catalog-request-messages-list-load-more-btn.php', true);
        //FatUtility::dieJsonSuccess($json);

        $this->set('msg', $json);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUpCatalogRequestMessage()
    {
        $this->objPrivilege->canEditSellerProducts();
        $requestId = FatApp::getPostedData('requestId', null, '0');
        $frm = $this->getCatalogRequestMessageForm($requestId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $requestId = FatUtility::int($requestId);

        $srch = new CatalogRequestSearch($this->adminLangId);
        $srch->addCondition('scatrequest_id', '=', $requestId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save catalog request message[ */
        $dataToSave = array(
            'scatrequestmsg_scatrequest_id' => $requestRow['scatrequest_id'],
            'scatrequestmsg_from_user_id' => UserAuthentication::getLoggedUserId(),
            'scatrequestmsg_from_admin_id' => 0,
            'scatrequestmsg_msg' => $post['message'],
            'scatrequestmsg_date' => date('Y-m-d H:i:s'),
        );
        $catRequestMsgObj = new CatalogRequestMessage();
        $catRequestMsgObj->assignValues($dataToSave, true);
        if (!$catRequestMsgObj->save()) {
            Message::addErrorMessage($catRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $scatrequestmsg_id = $catRequestMsgObj->getMainTableRecordId();
        if (!$scatrequestmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_Technical_team', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendCatalogRequestMessageNotification($scatrequestmsg_id, $this->adminLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('scatrequestmsg_scatrequest_id', $requestId);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRequestedCatalog()
    {
        $this->objPrivilege->canEditSellerProducts();
        $post = FatApp::getPostedData();
        $scatrequest_id = FatUtility::int($post['scatrequest_id']);

        if (1 > $scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cRequestObj = new User(UserAuthentication::getLoggedUserId());
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('tucr.scatrequest_status', '=', 0);
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        if ($rs == false) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $row = FatApp::getDb()->fetch($rs);

        if ($row == false || ($row != false && $row['scatrequest_status'] != User::CATALOG_REQUEST_PENDING)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$cRequestObj->deleteCatalogRequest($row['scatrequest_id'])) {
            Message::addErrorMessage(Labels::getLabel($cRequestObj->getError(), $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('scatrequest_id', $row['scatrequest_id']);
        $this->set('msg', Labels::getLabel('LBL_Record_deleted_successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchCatalogProduct()
    {
        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $post = $frmSearchCatalogProduct->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = Product::getSearchObject($this->adminLangId);
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        //$cnd = $srch->addCondition( 'product_seller_id', '=',0);
        /* if( User::canAddCustomProduct() ){
          $cnd->attachCondition( 'product_seller_id', '=',UserAuthentication::getLoggedUserId(),'OR');
          } */
        $srch->addCondition('product_active', '=', applicationConstants::ACTIVE);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            /* $cnd->attachCondition('attrgrp_name', 'like', '%' . $keyword . '%'); */
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
        }

        $srch->addMultipleFields(
                array(
                    'product_id',
                    'product_identifier',
                    'product_name',
                    'product_added_on',
                    'product_model',
                    'product_attrgrp_id')
        );
        $srch->addOrder('product_added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('adminLangId', $this->adminLangId);

        unset($post['page']);
        $frmSearchCatalogProduct->fill($post);
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    private function getCatalogRequestMessageSearchForm()
    {
        $frm = new Form('frmCatalogRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'requestId');
        return $frm;
    }

    private function getCatalogProductSearchForm()
    {
        $frm = new Form('frmSearchCatalogProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function getCatalogRequestMessageForm($requestId)
    {
        $frm = new Form('catalogRequestMsgForm');

        $frm->addHiddenField('', 'requestId', $requestId);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->adminLangId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->adminLangId));
        return $frm;
    }

    /* Catalog section closed ] */

    private function isShopActive($userId, $shopId = 0, $returnResult = false)
    {
        $shop = new Shop($shopId, $userId);
        if (false == $returnResult) {
            return $shop->isActive();
        }

        if ($shop->isActive()) {
            return $shop->getData();
        }

        return false;
    }

    private function addNewCatalogRequestForm()
    {
        $frm = new Form('frmAddCatalogRequest', array('enctype' => "multipart/form-data"));
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->adminLangId), 'scatrequest_title');
        /* $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Content',$this->adminLangId),'scatrequest_content');
          $fld->htmlBeforeField = '<div class="editor-bar">';
          $fld->htmlAfterField = '</div>'; */
        $frm->addTextArea(Labels::getLabel('LBL_Content', $this->adminLangId), 'scatrequest_content');
        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_File', $this->adminLangId), 'file', array('accept' => 'image/*,.zip', 'enctype' => "multipart/form-data"));
        $fileFld->htmlAfterField = '<span class="text--small">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->adminLangId) . '</span>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function thresholdProducts()
    {
        $this->objPrivilege->canViewSellerProducts();
        $this->set('frmSearch', $this->getThresholdLevelProductsSearchForm());
        $this->_template->render();
    }

    public function searchThresholdLevelProducts()
    {
        $frmSearch = $this->getThresholdLevelProductsSearchForm();

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);

        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = SellerProduct::getSearchObject($this->adminLangId);

        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'cred.credential_user_id = selprod_user_id', 'cred');
        $srch->joinTable('tbl_email_archives', 'LEFT OUTER JOIN', 'arch.emailarchive_to_email = cred.credential_email', 'arch');
        if (!empty($post['keyword'])) {
            $keyword = trim($post['keyword']);
            $condition = $srch->addCondition('product_name', 'LIKE', '%' . $keyword . '%');
            $condition->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%');
        }
        /* $cnd = $srch->addCondition('emailarchive_tpl_name', 'LIKE', 'threshold_notification_vendor_custom');
          $cnd->attachCondition('emailarchive_tpl_name', 'LIKE', 'threshold_notification_vendor', 'OR'); */
        $srch->addDirectCondition('selprod_stock <= selprod_threshold_stock_level');
        $srch->addDirectCondition('selprod_track_inventory = ' . Product::INVENTORY_TRACK);
        $srch->addMultipleFields(array('selprod_id', 'selprod_user_id', 'IF(selprod_title is NULL or selprod_title = "" ,product_name, selprod_title) as product_name', 'selprod_stock', 'selprod_threshold_stock_level', 'emailarchive_sent_on'));

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addGroupBy('selprod_id');
        $srch->addOrder('selprod_id', 'DESC');

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $products = $db->fetchAll($rs, 'selprod_id');
        $this->set("arr_listing", $products);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function sendMailForm($user_id, $selprod_id)
    {
        $user_id = FatUtility::int($user_id);
        $selprod_id = FatUtility::int($selprod_id);
        $userObj = new User($user_id);
        $user = $userObj->getUserInfo(null, false, false);
        if (!$user) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $frm = $this->getSendMailForm($user_id, $selprod_id);

        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function sendMailThresholdStock($user_id, $selprod_id)
    {
        $user_id = FatUtility::int($user_id);
        $selprod_id = FatUtility::int($selprod_id);

        $userObj = new User($user_id);
        $user = $userObj->getUserInfo(null, false, false);
        if (!$user) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendProductStockAlert($selprod_id, $this->adminLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Your_message_sent_to', $this->adminLangId) . ' - ' . $user["credential_email"]);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSendMailForm($user_id = 0, $selprod_id = 0)
    {
        $user_id = FatUtility::int($user_id);
        $selprod_id = FatUtility::int($selprod_id);
        $frm = new Form('sendMailFrm');
        $frm->addHiddenField('', 'user_id', $user_id);
        $frm->addHiddenField('', 'selprod_id', $selprod_id);

        $frm->addTextBox(Labels::getLabel('LBL_Subject', $this->adminLangId), 'mail_subject')->requirements()->setRequired(true);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->adminLangId), 'mail_message')->requirements()->setRequired(true);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->adminLangId), array('id' => 'btn_submit'));
        return $frm;
    }

    private function getThresholdLevelProductsSearchForm()
    {
        $frm = new Form('frmProductSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function sellerProductDelete()
    {
        $this->objPrivilege->canEditSellerProducts();
        $selprod_id = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($selprod_id < 1) {
            Message::addErrorMessage(
                    Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->markAsDeleted($selprod_id);

        $this->set("msg", Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->adminLangId));
        /* FatUtility::dieJsonSuccess(
          Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY',$this->adminLangId)
          ); */
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function sellerProductDeleteSale()
    {
        $selprodId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        $dataToSave = [
            'sprodata_is_for_sell' => applicationConstants::NO, 
            'sprodata_selprod_id' =>  $selprodId
        ];
        $record = new ProductRental($selprodId);
        if (!$record->addUpdateSelProData($dataToSave)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $sellerProdObj = new SellerProduct($selprodId);
        $sellerProdObj->assignValues(array('selprod_active' => applicationConstants::NO));
        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $this->set("msg", Labels::getLabel('MSG_RECORD_DELETED_SUCCESSFULLY', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    public function deleteSelected()
    {
        $this->objPrivilege->canEditSellerProducts();
        $selprod_ids_arr = FatUtility::int(FatApp::getPostedData('selprod_ids'));
        if (empty($selprod_ids_arr)) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($selprod_ids_arr as $selprod_id) {
            if (0 >= $selprod_id) {
                continue;
            }
            $this->markAsDeleted($selprod_id);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $selprodObj = new SellerProduct($selprod_id);
        if (!$selprodObj->deleteSellerProduct($selprod_id)) {
            Message::addErrorMessage(
                    Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->adminLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function changeStatus()
    {
        $this->objPrivilege->canEditSellerProducts();
        $selprodId = FatApp::getPostedData('selprodId', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        $productType = FatApp::getPostedData('productType', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_RENT);
        if (0 == $selprodId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $sellerProductData = SellerProduct::getAttributesById($selprodId, array('selprod_active'));

        if (!$sellerProductData) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($productType == applicationConstants::PRODUCT_FOR_RENT) {
            $prodRentalData = [
                'sprodata_selprod_id' => $selprodId,
                'sprodata_rental_active' => $status
            ];
            $selproObj = new ProductRental();
            if (!$selproObj->addUpdateSelProData($prodRentalData)) {
                Message::addErrorMessage($selproObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }  
        } else {
            $this->updateSellerProductStatus($selprodId, $status);
        }
        
        $productId = SellerProduct::getAttributesById($selprodId, 'selprod_product_id', false);
        Product::updateMinPrices($productId);
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleBulkStatuses()
    {
        $this->objPrivilege->canEditSellerProducts();
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, -1);
        $productType = FatApp::getPostedData('productType', FatUtility::VAR_INT, 0);
        // $productType = FatApp::getPostedData('productType', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_RENT);
        $selprodIdsArr = FatUtility::int(FatApp::getPostedData('selprod_ids'));
        if (empty($selprodIdsArr) || -1 == $status || $productType < 1) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        foreach ($selprodIdsArr as $selprodId) {
            if (1 > $selprodId) {
                continue;
            }
            if ($productType == applicationConstants::PRODUCT_FOR_RENT) {
                $prodRentalData = [
                    'sprodata_selprod_id' => $selprodId,
                    'sprodata_rental_active' => $status
                ];
                $selproObj = new ProductRental();
                if (!$selproObj->addUpdateSelProData($prodRentalData)) {
                    Message::addErrorMessage($selproObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                } 
            } else {
                $this->updateSellerProductStatus($selprodId, $status);
            }
        }
        $this->set('msg', $this->str_update_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSellerProductStatus($selprodId, $status)
    {
        $status = FatUtility::int($status);
        $selprodId = FatUtility::int($selprodId);
        if (1 > $selprodId || -1 == $status) {
            FatUtility::dieWithError(
                    Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId)
            );
        }

        $sellerProdObj = new SellerProduct($selprodId);
        if (!$sellerProdObj->changeStatus($status)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function rentalSpecialPrice($selProd_id = 0)
    {
        $srchFrm = $this->getSpecialPriceSearchForm();
        $srchFrm->fill(array('product_for' => Product::PRODUCT_FOR_RENT));

        $selProd_id = FatUtility::int($selProd_id);
        $this->set("selProd_id", $selProd_id);
        $this->set("frmSearch", $srchFrm);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function specialPrice($selProd_id = 0)
    {
        $selProd_id = FatUtility::int($selProd_id);

        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('SellerProducts', 'specialPrice'));
            }
        }

        $srchFrm = $this->getSpecialPriceSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->adminLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'splprice_selprod_id' => $selProdId,
                    'selprod_price' => SellerProduct::getAttributesById($selProdId, 'selprod_price')
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
            if (false === $post) {
                FatUtility::dieJsonError(current($srchFrm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $post['product_for'] = Product::PRODUCT_FOR_SALE;
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProd_id], 'product_for' => Product::PRODUCT_FOR_SALE));
        }

        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->set("productFor",  Product::PRODUCT_FOR_SALE);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }
    
    /* public function rentalSpecialPrice(int $selProd_id = 0)
    {
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('SellerProducts', 'specialPrice'));
            }
        }

        $srchFrm = $this->getSpecialPriceSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);
        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->adminLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'splprice_selprod_id' => $selProdId,
                    'selprod_price' => SellerProduct::getAttributesById($selProdId, 'selprod_price')
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $post['product_for'] = Product::PRODUCT_FOR_RENT;
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProd_id], 'product_for' => Product::PRODUCT_FOR_RENT));
        }

        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->set("productFor",  Product::PRODUCT_FOR_RENT);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render(true, true, 'seller-products/special-price.php');
    } */

    public function volumeDiscount($selProd_id = 0)
    {
        $selProd_id = FatUtility::int($selProd_id);

        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('SellerProducts', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->adminLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId,
                    'selprod_stock' => SellerProduct::getAttributesById($selProdId, 'selprod_stock')
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProd_id]));
        }
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function searchSpecialPriceProducts()
    {
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        $sellerId = FatApp::getPostedData('product_seller_id', FatUtility::VAR_INT, 0);
        $productFor = FatApp::getPostedData('product_for', FatUtility::VAR_INT, Product::PRODUCT_FOR_SALE);
        $srch = SellerProduct::searchSpecialPriceProductsObj($this->adminLangId, $selProdId, $keyword, $sellerId, $productFor);
        $srch->setPageNumber($page);
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);
        $this->set("arrListing", $arrListing);
        $this->set("productFor", $productFor);

        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    public function searchVolumeDiscountProducts()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        $sellerId = FatApp::getPostedData('product_seller_id', FatUtility::VAR_INT, 0);
        $srch = SellerProduct::searchVolumeDiscountProducts($this->adminLangId, $selProdId, $keyword, $sellerId);

        $srch->setPageNumber($page);
        $srch->addOrder('voldiscount_id', 'DESC');

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getSpecialPriceSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'product_seller', '');
        $frm->addHiddenField('', 'product_seller_id');
        $frm->addHiddenField('', 'product_for', Product::PRODUCT_FOR_SALE);
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getVolumeDiscountSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_User', $this->adminLangId), 'product_seller', '');
        $frm->addHiddenField('', 'product_seller_id');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function updateSpecialPriceRow()
    {
        $data = FatApp::getPostedData();
        if (empty($data)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $splPriceId = $this->updateSelProdSplPrice($data, true);
        if (!$splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        // last Param of getProductDisplayTitle function used to get title in html form.
        $productName = SellerProduct::getProductDisplayTitle($data['splprice_selprod_id'], $this->adminLangId, true);

        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->addMultipleFields(array('credential_username', 'selprod_price','sprodata_rental_price'));
        $srch->addCondition('selprod_id', '=', $data['splprice_selprod_id']);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $data['credential_username'] = $row['credential_username'];
        $data['selprod_price'] = $row['selprod_price'];
        $data['sprodata_rental_price'] = $row['sprodata_rental_price'];
        $data['product_name'] = $productName;
        
        $this->set('data', $data);
        $this->set('splPriceId', $splPriceId);
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('LBL_Special_Price_Setup_Successful', $this->adminLangId),
            'data' => $this->_template->render(false, false, 'seller-products/update-special-price-row.php', true)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function updateVolumeDiscountRow()
    {
        $data = FatApp::getPostedData();

        if (empty($data)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $selprod_id = FatUtility::int($data['voldiscount_selprod_id']);

        if (1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $volDiscountId = $this->updateSelProdVolDiscount($selprod_id, 0, $data['voldiscount_min_qty'], $data['voldiscount_percentage']);
        if (!$volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Response', $this->adminLangId));
        }

        // last Param of getProductDisplayTitle function used to get title in html form.
        $productName = SellerProduct::getProductDisplayTitle($data['voldiscount_selprod_id'], $this->adminLangId, true);

        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->addMultipleFields(array('credential_username'));
        $srch->addCondition('selprod_id', '=', $data['voldiscount_selprod_id']);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $data['credential_username'] = $row['credential_username'];
        $data['product_name'] = $productName;
        $this->set('data', $data);
        $this->set('volDiscountId', $volDiscountId);
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('LBL_Volume_Discount_Setup_Successful', $this->adminLangId),
            'data' => $this->_template->render(false, false, 'seller-products/update-volume-discount-row.php', true)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function updateSpecialPriceColValue()
    {
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $attribute = FatApp::getPostedData('attribute', FatUtility::VAR_STRING, '');

        $columns = array('splprice_start_date', 'splprice_end_date', 'splprice_price','splprice_type');
        if (!in_array($attribute, $columns)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $otherColumns = array_values(array_diff($columns, [$attribute]));
        $otherColumnsValue = SellerProductSpecialPrice::getAttributesById($splPriceId, $otherColumns);
        if (empty($otherColumnsValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $value = FatApp::getPostedData('value');
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);

        $dataToUpdate = array(
            'splprice_selprod_id' => $selProdId,
            'splprice_id' => $splPriceId,
            $attribute => $value,
            'product_for' => $otherColumnsValue['splprice_type'],
        );

        $dataToUpdate += $otherColumnsValue;

        if (!$this->updateSelProdSplPrice($dataToUpdate)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong._Please_Try_Again.', $this->adminLangId));
        }

        if ('splprice_price' == $attribute) {
            $value = CommonHelper::displayMoneyFormat($value, true, true);
        }
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('MSG_Success', $this->adminLangId),
            'data' => array('value' => $value)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function updateVolumeDiscountColValue()
    {
        $volDiscountId = FatApp::getPostedData('voldiscount_id', FatUtility::VAR_INT, 0);
        if (1 > $volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $attribute = FatApp::getPostedData('attribute', FatUtility::VAR_STRING, '');

        $columns = array('voldiscount_min_qty', 'voldiscount_percentage');
        if (!in_array($attribute, $columns)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        $otherColumns = array_values(array_diff($columns, [$attribute]));
        $otherColumnsValue = SellerProductVolumeDiscount::getAttributesById($volDiscountId, $otherColumns);
        if (empty($otherColumnsValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        $value = FatApp::getPostedData('value');
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);

        $dataToUpdate = array(
            'voldiscount_id' => $volDiscountId,
            'voldiscount_selprod_id' => $selProdId,
            $attribute => $value
        );
        $dataToUpdate += $otherColumnsValue;

        $volDiscountId = $this->updateSelProdVolDiscount($selProdId, $volDiscountId, $dataToUpdate['voldiscount_min_qty'], $dataToUpdate['voldiscount_percentage']);
        if (!$volDiscountId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Response', $this->adminLangId));
        }

        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('MSG_Success', $this->adminLangId),
            'data' => array('value' => $value)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function getRelatedProductsList($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = SellerProduct::searchRelatedProducts($this->adminLangId);
        $srch->addCondition(SellerProduct::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $selprod_id);
        $srch->addOrder('selprod.selprod_id', 'DESC');
        $rs = $srch->getResultSet();
        $relatedProds = FatApp::getDb()->fetchAll($rs);
        $json = array(
            'selprodId' => $selprod_id,
            'relatedProducts' => $relatedProds
        );
        FatUtility::dieJsonSuccess($json);
        /* $this->set('relatedProducts', $relatedProds);
          $this->set('selprod_id', $selprod_id);
          $this->_template->render(false, false, 'json-success.php'); */
    }

    private function getRelatedProductsForm()
    {
        $frm = new Form('frmRelatedSellerProduct');

        $frm->addHiddenField('', 'selprod_id', 0);
        $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->adminLangId)));
        //$prodName = $frm->addTextBox('', 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->adminLangId)));
        $prodName->requirements()->setRequired();
        //$fld1 = $frm->addTextBox('', 'products_related');        
        $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'products_related', [], '');
        // $fld1->htmlAfterField= '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="related-products"></ul></div></div>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->adminLangId));
        return $frm;
    }

    public function relatedProducts($selProd_id = 0)
    {
        $selProd_id = FatUtility::int($selProd_id);
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->adminLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProdId]));
        }

        // $this->_template->addJs(array('js/tagify.min.js','js/tagify.polyfills.js'));
        $this->_template->addCss(array('css/custom-tagify.css'));

        $relProdFrm = $this->getRelatedProductsForm();
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("relProdFrm", $relProdFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function searchRelatedProducts()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $db = FatApp::getDb();

        $prodSrch = SellerProduct::searchRelatedProducts($this->adminLangId, 'related_sellerproduct_id');
        if ($keyword != '') {
            $cnd = $prodSrch->addCondition('lang.product_name', 'like', "%$keyword%");
            $cnd->attachCondition('p.product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        }
        $prodSrch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = selprod.selprod_user_id', 'tuc');
        $prodSrch->addFld('credential_username');
        $prodSrch->setPageNumber($page);
        $prodSrch->setPageSize($pagesize);
        $prodSrch->addGroupBy('related_sellerproduct_id');
        $rs = $prodSrch->getResultSet();
        $relatedProds = $db->fetchAll($rs);

        $arrListing = array();
        foreach ($relatedProds as $key => $relatedProd) {
            $productId = $relatedProd['related_sellerproduct_id'];
            $srch = SellerProduct::searchRelatedProducts($this->adminLangId);
            $srch->addFld('if(related_sellerproduct_id = ' . $selProdId . ', 1 , 0) as priority');
            $srch->addOrder('priority', 'DESC');
            $srch->addCondition('related_sellerproduct_id', '=', $productId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $arrListing[$productId] = $db->fetchAll($rs);
            $arrListing[$productId]['credential_username'] = $relatedProd['credential_username'];
        }

        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $prodSrch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $prodSrch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getRelatedProductsSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function setupRelatedProduct()
    {
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if ($selprod_id <= 0) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_Select_A_Valid_Product", $this->adminLangId));
        }

        if (!isset($post['selected_products']) || !is_array($post['selected_products']) || 1 > count($post['selected_products'])) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_MUST_SELECT_ATLEAST_ONE_PRODUCT_TO_RELATED_PRODUCTS", $this->adminLangId));
        }

        $relatedProducts = $post['selected_products'];
        unset($post['selprod_id']);
        $sellerProdObj = new SellerProduct();
        if (!$sellerProdObj->addUpdateSellerRelatedProdcts($selprod_id, $relatedProducts)) {
            FatUtility::dieJsonError($sellerProdObj->getError());
        }

        $this->set('msg', Labels::getLabel('LBL_Related_Product_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelprodRelatedProduct($selprod_id, $relprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $relprod_id = FatUtility::int($relprod_id);
        if (!$selprod_id || !$relprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_RELATED_PRODUCTS, array('smt' => 'related_sellerproduct_id = ? AND related_recommend_sellerproduct_id = ?', 'vals' => array($selprod_id, $relprod_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->adminLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getUpsellProductsList($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = SellerProduct::searchUpsellProducts($this->adminLangId);
        $srch->addCondition(SellerProduct::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $selprod_id);
        $srch->addGroupBy('selprod_id');
        $srch->addGroupBy('upsell_sellerproduct_id');
        $srch->addOrder('selprod_id', 'DESC');
        $rs = $srch->getResultSet();
        $upsellProds = FatApp::getDb()->fetchAll($rs);
        $json = array(
            'selprodId' => $selprod_id,
            'upsellProducts' => $upsellProds
        );
        FatUtility::dieJsonSuccess($json);
    }

    private function getUpsellProductsForm()
    {
        $frm = new Form('frmUpsellSellerProduct');

        $frm->addHiddenField('', 'selprod_id', 0);
        $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->adminLangId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->adminLangId)));
        //$prodName = $frm->addTextBox('', 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->adminLangId)));
        $prodName->requirements()->setRequired();
        //$fld1 = $frm->addTextBox('', 'products_upsell');
        $fld1 = $frm->addSelectBox(Labels::getLabel('LBL_Buy_Together_Products', $this->adminLangId), 'products_upsell', [], '');
        // $fld1->htmlAfterField= '<div class="row"><div class="col-md-12"><ul class="list-vertical" id="upsell-products"></ul></div></div>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->adminLangId));
        return $frm;
    }

    public function upsellProducts($selProd_id = 0)
    {
        $selProd_id = FatUtility::int($selProd_id);
        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'volumeDiscount'));
            }
        }

        $srchFrm = $this->getVolumeDiscountSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->adminLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'voldiscount_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($frm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProdId]));
        }

        // $this->_template->addJs(array('js/tagify.min.js','js/tagify.polyfills.js'));
        $this->_template->addCss(array('css/custom-tagify.css'));

        $relProdFrm = $this->getUpsellProductsForm();
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("relProdFrm", $relProdFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function searchUpsellProducts()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $db = FatApp::getDb();

        $prodSrch = SellerProduct::searchUpsellProducts($this->adminLangId, 'upsell_sellerproduct_id', false);
        if ($keyword != '') {
            $cnd = $prodSrch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('p.product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        }
        $prodSrch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = selprod.selprod_user_id', 'tuc');
        $prodSrch->addFld('credential_username');
        $prodSrch->setPageNumber($page);
        $prodSrch->setPageSize($pagesize);
        $prodSrch->addGroupBy('upsell_sellerproduct_id');
        $rs = $prodSrch->getResultSet();
        $upsellProds = $db->fetchAll($rs);

        $arrListing = array();
        foreach ($upsellProds as $key => $upsellProd) {
            $productId = $upsellProd['upsell_sellerproduct_id'];
            $srch = SellerProduct::searchUpsellProducts($this->adminLangId);
            $srch->addFld('if(upsell_sellerproduct_id = ' . $selProdId . ', 1 , 0) as priority');
            $srch->addOrder('priority', 'DESC');
            $srch->addCondition('upsell_sellerproduct_id', '=', $productId);
            $srch->addGroupBy('selprod_id');
            $srch->addGroupBy('upsell_sellerproduct_id');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $arrListing[$productId] = $db->fetchAll($rs);
            $arrListing[$productId]['credential_username'] = $upsellProd['credential_username'];
        }
        $this->set("arrListing", $arrListing);

        $this->set('page', $page);
        $this->set('pageCount', $prodSrch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $prodSrch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getUpsellProductsSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->setRequiredStarWith('caption');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function setupUpsellProduct()
    {
        $post = FatApp::getPostedData();
        $selprod_id = FatUtility::int($post['selprod_id']);
        if ($selprod_id <= 0) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_Please_Select_A_Valid_Product", $this->adminLangId));
        }
        if (!isset($post['selected_products']) || !is_array($post['selected_products']) || 1 > count($post['selected_products'])) {
            FatUtility::dieJsonError(Labels::getLabel("MSG_MUST_SELECT_ATLEAST_ONE_PRODUCT_TO_BUY_TOGETHER", $this->adminLangId));
        }

        $upsellProducts = $post['selected_products'];

        $sellerProdObj = new SellerProduct();
        /* saving of product Upsell Product[ */
        if (!$sellerProdObj->addUpdateSellerUpsellProducts($selprod_id, $upsellProducts)) {
            FatUtility::dieJsonError($sellerProdObj->getError());
        }
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Buy_Together_Product_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSelprodUpsellProduct($selprod_id, $relprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $relprod_id = FatUtility::int($relprod_id);
        if (!$selprod_id || !$relprod_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_UPSELL_PRODUCTS, array('smt' => 'upsell_sellerproduct_id = ? AND upsell_recommend_sellerproduct_id = ?', 'vals' => array($selprod_id, $relprod_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->adminLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $selprod_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteMembershipPlans()
    { /* WILL UPDATE AFTER MEMBERSHIP MODULE MERGE */
        $post = FatApp::getPostedData();
        $memberShipArr = [
            1 => 'Silver',
            2 => 'Gold',
            3 => 'Free'
        ];

        $json = array();
        foreach ($memberShipArr as $key => $plan) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($plan, ENT_QUOTES, 'UTF-8')),
                'plan_identifier' => strip_tags(html_entity_decode($plan, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function productSaleDetailsForm(int $productId, int $selprodId = 0)
    {
        $productRow = Product::getAttributesById($productId);
        if (!$productRow) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }
        if ($productRow['product_active'] != applicationConstants::ACTIVE) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Catalog_is_no_more_active', $this->adminLangId));
        }
        $frmSellerProduct = $this->getProductSaleForm($productId);
        $sellerProductRow = [];
        if ($selprodId) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId, null, true, true, false, applicationConstants::PRODUCT_FOR_SALE);
        }

        $returnAge = isset($sellerProductRow['selprod_return_age']) ? FatUtility::int($sellerProductRow['selprod_return_age']) : '';
        $cancellationAge = isset($sellerProductRow['selprod_cancellation_age']) ? FatUtility::int($sellerProductRow['selprod_cancellation_age']) : '';

        if ('' === $returnAge || '' === $cancellationAge) {
            $sellerProductRow['use_shop_policy'] = 1;
        }
        $frmSellerProduct->fill($sellerProductRow);
        $this->set('frm', $frmSellerProduct);
        $this->set('product_id', $productId);
        $this->set('selprod_id', $selprodId);
        $this->_template->render(false, false);
    }

    public function setupProdSaleDetails()
    {
        $post = FatApp::getPostedData();
        $selprodId = FatUtility::int($post['selprod_id']);

        if (1 > $selprodId || empty($post)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sellerProdObj = new SellerProduct($selprodId);
        $sellerProdObj->assignValues($post);
        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selprodId = $sellerProdObj->getMainTableRecordId();
        $prodRentalData = [
                        'sprodata_is_for_sell' => applicationConstants::YES, 
                        'sprodata_selprod_id' =>  $selprodId
                    ];
        $record = new ProductRental($selprodId);
        if (!$record->addUpdateSelProData($prodRentalData)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
        $selProdSpecificsObj = new SellerProductSpecifics($selprodId);
        if (0 < $useShopPolicy) {
            $whr = [
                'smt' => 'sps_selprod_id = ? AND selprod_specific_type = ?',
                'vals' => [$selprodId, applicationConstants::PRODUCT_FOR_SALE]
            ];
            if (!FatApp::getDb()->deleteRecords(SellerProductSpecifics::DB_TBL, $whr)) {
                FatUtility::dieJsonError(FatApp::getDb()->getError());
            }
        } else {
            $post['sps_selprod_id'] = $selprodId;
            $post['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_SALE;
            $selProdSpecificsObj->assignValues($post);
            $data = $selProdSpecificsObj->getFlds();
            if (!$selProdSpecificsObj->addNew(array(), $data)) {
                FatUtility::dieJsonError($selProdSpecificsObj->getError());
            }
        }

        Product::updateMinPrices(SellerProduct::getAttributesByID($selprodId, 'selprod_product_id'));
        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getProductSaleForm(int $productId)
    {
        $langId = $this->adminLangId;
        $rentalTypeArr = applicationConstants::rentalTypeArr($langId);
        $frm = new Form('frmProductRentalDetails');
        /* [ GENERAL FIELDS */
        $productData = Product::getAttributesById($productId, array('product_min_selling_price', 'if(product_seller_id > 0, 1, 0) as sellerProduct', 'product_seller_id'));
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Maintain_Stock_Levels', $langId), 'selprod_subtract_stock', applicationConstants::YES, array(), false, 0);
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Track_Product_Inventory', $langId), 'selprod_track_inventory', applicationConstants::YES, array(), false, 0);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Alert_Stock_Level', $langId), 'selprod_threshold_stock_level', '');
        $fld->requirements()->setInt();
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Purchase_Quantity', $langId), 'selprod_min_order_qty', '');
        $fld->requirements()->setRange(1, SellerProduct::MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $langId), 'selprod_condition', Product::getConditionArr($langId), '', array(), Labels::getLabel('LBL_Select_Condition', $langId));
        $fld->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $langId), 'selprod_available_from', '', array('readonly' => 'readonly'))->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Publish', $langId), 'selprod_active', applicationConstants::getYesNoArr($langId), applicationConstants::YES, array(), '');

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_POLICY', $langId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], true, 0);
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);
        /* ] */

        $frm->addHiddenField('', 'selprod_id');
        /* $fld = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $langId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_cost');
          $fld->requirements()->setPositive();
          $fld->requirements()->setRange(0.001, 9999999); */

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Selling_Price', $langId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_price');
        $fld->requirements()->setPositive();
        $fld->requirements()->setRange($productData['product_min_selling_price'], 9999999);

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $langId), 'selprod_stock');
        $fld->requirements()->setPositive();
        $fld->requirements()->setRange(0, 9999999);
        $fld->requirements()->setCompareWith('selprod_min_order_qty', 'ge', '');
        
        $fld = $frm->addTextBox(Labels::getLabel('LBL_SKU', $langId), 'selprod_sku');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $langId));
        return $frm;
    }

    public function isProductRewriteUrlUnique()
    {
        $selprod_id = FatApp::getPostedData('recordId', FatUtility::VAR_INT, 0);
        $urlKeyword = FatApp::getPostedData('url_keyword');
        $sellerProdObj = new SellerProduct($selprod_id);
        $seoUrl = $sellerProdObj->sanitizeSeoUrl($urlKeyword);
        if (1 > $selprod_id) {
            $isUnique = UrlRewrite::isCustomUrlUnique($seoUrl);
            if ($isUnique) {
                FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
            }
            FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->adminLangId));
        }

        $originalUrl = $sellerProdObj->getRewriteProductOriginalUrl();
        $customUrlData = UrlRewrite::getDataByCustomUrl($seoUrl, $originalUrl);
        if (empty($customUrlData)) {
            FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->adminLangId));
    }

}