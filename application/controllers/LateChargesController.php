<?php

class LateChargesController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
        $this->userPrivilege->canViewLateChargesManagement(UserAuthentication::getLoggedUserId());
		$isModuleActiveOnShop = Shop::getAttributesByUserId($this->userParentId, 'shop_is_enable_late_charges');
		if (!FatApp::getConfig('CONF_ENABLE_RENTAL_PRODUCT_LATE_CHARGES_MODULE', FatUtility::VAR_INT, 0) || !$isModuleActiveOnShop) {
            Message::addErrorMessage(Labels::getLabel('LBL_Late_Charges_Module_is_disabled_on_shop_or_admin_level', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('seller'));
		}
	}

    public function index()
    {
		if (1 > LateChargesProfile::getDefaultProfileId($this->userParentId)) {
			$defaultProfileId = LateChargesProfile::setDefaultProfile($this->userParentId);
		}
		
        $searchFrm = $this->getSearchForm();
        $this->set("searchFrm", $searchFrm);
        $this->set('canEdit', $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render();
    }

    public function search()
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $post = $searchForm->getFormDataFromArray($data);

        $prodCountSrch = LateChargesProfileProduct::getSearchObject();
        $prodCountSrch->doNotCalculateRecords();
        $prodCountSrch->doNotLimitRecords();
        $prodCountSrch->addGroupBy('lcptp_lcp_id');
        $prodCountSrch->addMultipleFields(array("COUNT(*) as totalProducts, lcptp_lcp_id"));
        $prodCountQuery = $prodCountSrch->getQuery();
		
		$srch = LateChargesProfile::getSearchObject();
        $srch->addCondition('lcpro.lcp_user_id', '=', $this->userParentId); /* only user added profiles */
        $srch->joinTable('(' . $prodCountQuery . ')', 'LEFT OUTER JOIN', 'lcpprod.lcptp_lcp_id = lcpro.lcp_id', 'lcpprod');

        $srch->addMultipleFields(array('lcpro.*', 'if(lcpprod.totalProducts is null, 0, lcpprod.totalProducts) as totalProducts'));

        $srch->addOrder('lcp_is_default', 'DESC');
        $srch->addOrder('lcp_id', 'ASC');

        if (!empty($post['keyword'])) {
            $srch->addCondition('lcpro.lcp_identifier', 'like', '%' . $post['keyword'] . '%');
        }
		
		$srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
		$this->set('arr_listing', $records);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('chargesType', LateChargesProfile::getAmountType($this->siteLangId));
        $this->set('canEdit', $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    public function form(int $profileId = 0)
    {
        $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId());
        $frm = $this->getForm($profileId);
        $data = [];
        $productCount = 0;
        if (0 < $profileId) {
            $data = LateChargesProfile::getAttributesById($profileId);
            if (empty($data)) {
                FatUtility::dieWithError($this->str_invalid_request);
            }

            if ($data['lcp_user_id'] != $this->userParentId) {
                Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('lateCharges'));
            }

            $frm->fill($data);

            $prodCountSrch = new SearchBase(LateChargesProfileProduct::DB_TBL, 'selsppro');
            $prodCountSrch->doNotCalculateRecords();
            $prodCountSrch->doNotLimitRecords();
            $prodCountSrch->addCondition('lcptp_lcp_id', '=', $profileId);
            $rs = $prodCountSrch->getResultSet();
            $productCount = FatApp::getDb()->totalRecords($rs);
        }
        $this->set('profile_id', $profileId);
        $this->set('profileData', $data);
        $this->set('productCount', $productCount);
        $this->set('frm', $frm);
		$this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function setup()
    {
        $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId());
        $frm = $this->getForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $profileId = $post['lcp_id'];
        unset($post['lcp_id']);

        $spObj = new LateChargesProfile($profileId);
        $spObj->assignValues($post);

        if (!$spObj->save()) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $profileId = $spObj->getMainTableRecordId();
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->set('profileId', $profileId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId());
        
        $profileId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($profileId < 1) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $profileData = LateChargesProfile::getAttributesById($profileId);
        if (false == $profileData || (!empty($profileData) && $profileData['lcp_user_id'] != $this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $whr = array('smt' => 'lcp_id = ? and lcp_is_default != ?', 'vals' => array($profileId, applicationConstants::YES));
        if (!FatApp::getDb()->deleteRecords(LateChargesProfile::DB_TBL, $whr)) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $defaultProfileId = LateChargesProfile::getDefaultProfileId($this->userParentId);
		if (1 > $defaultProfileId) {
			$defaultProfileId = LateChargesProfile::setDefaultProfile($this->userParentId);
		}
        
        $data = [
            'lcptp_lcp_id' => $defaultProfileId
        ];
        $whr = array('smt' => 'lcptp_lcp_id = ? and lcptp_user_id = ?', 'vals' => array($profileId, $this->userParentId));
        FatApp::getDb()->updateFromArray(LateChargesProfileProduct::DB_TBL, $data, $whr);
        
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }


    private function getSearchForm()
    {
        $frm = new Form('frmSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->siteLangId), 'keyword');
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId));
		//$fldSubmit->attachField($fldCancel);
        return $frm;
    }

    private function getForm(int $profileId = 0)
    {
        $frm = new Form('frmProfile');
        $frm->addHiddenField('', 'lcp_id', $profileId);
        $frm->addHiddenField('', 'lcp_user_id', $this->userParentId);
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Profile_Name', $this->siteLangId), 'lcp_identifier'); 
        
        $lateChargeProfilesArr = [
            LateChargesProfile::AMOUNT_TYPE_PERCENTAGE => Labels::getLabel('LBL_Charge_Type_In_Percentage', $this->siteLangId),
            LateChargesProfile::AMOUNT_TYPE_FIXED => Labels::getLabel('LBL_Charge_Type_In_Fixed_Amount', $this->siteLangId),
        ];

		$frm->addSelectBox(Labels::getLabel('LBL_Type', $this->siteLangId), 'lcp_amount_type', $lateChargeProfilesArr, LateChargesProfile::AMOUNT_TYPE_PERCENTAGE, array(), '');
		
		$fldMinSelPrice = $frm->addFloatField(Labels::getLabel('LBL_Amount', $this->siteLangId), 'lcp_amount', '');
        $fldMinSelPrice->requirements()->setPositive();
		
		$frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }
	
	/*** product section goes here ***/
	public function productSection($profileId, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $frm = $this->getProductForm($profileId, $type);
        $this->set("frm", $frm);
        $this->set("type", $type);
        $this->_template->render(false, false);
    }
	
	public function searchProducts($profileId)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);

        $srch = LateChargesProfileProduct::getSearchObject();
        $srch->addCondition('lcptp_lcp_id', '=', $profileId);
        $srch->addCondition('lcptp_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);
        $srch->addCondition('lcptp_user_id', '=', $this->userParentId);
        $srch->addOrder('product_name', 'ASC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $profileData = LateChargesProfile::getAttributesById($profileId);

        $this->set('productsData', $records);
        $this->set('profileData', $profileData);
        $this->set('profile_id', $profileId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('type',  SellerProduct::PRODUCT_TYPE_PRODUCT);
        $this->set('canEdit', $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId(),  true));
        $this->_template->render(false, false);
    }
    
    public function searchServices($profileId)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        
        $srch = new SearchBase(LateChargesProfileProduct::DB_TBL, 'lcpprod');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'selprod_id = lcptp_product_id AND selprod_type ='. SellerProduct::PRODUCT_TYPE_ADDON, 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->siteLangId, 'sp_l');
        
        $srch->addMultipleFields(['lcpprod.*', 'selprod_id as product_id', 'selprod_title as product_name']);
        $srch->addCondition('lcptp_lcp_id', '=', $profileId);
        $srch->addCondition('lcptp_product_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        $srch->addCondition('lcptp_user_id', '=', $this->userParentId);
        $srch->addOrder('selprod_title', 'ASC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $profileData = LateChargesProfile::getAttributesById($profileId);

        $this->set('productsData', $records);
        $this->set('profileData', $profileData);
        $this->set('profile_id', $profileId);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('type',  SellerProduct::PRODUCT_TYPE_ADDON);
        $this->set('canEdit', $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId(),  true));
        $this->_template->render(false, false, 'late-charges/search-products.php');
    }

    public function autoComplete()
    {
        $post = FatApp::getPostedData();
        $profileId = FatApp::getPostedData('profileId', FatUtility::VAR_INT, 0);
        $srch = new ProductSearch($this->siteLangId);
        $srch->joinSellerProductWithData();
        $srch->addOrder('product_name');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }
        $cnd = $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $cnd->attachCondition('product_seller_id', '=', $this->userParentId);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(Product::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);

        $srch->addMultipleFields(array('product_id as id', 'product_name', 'product_identifier'));

        if (0 < $profileId) {
            $srch->joinTable(LateChargesProfileProduct::DB_TBL, 'LEFT JOIN', 'p.product_id = sppro.lcptp_product_id and sppro.lcptp_user_id = '. $this->userParentId . ' AND lcptp_product_type='. SellerProduct::PRODUCT_TYPE_PRODUCT, 'sppro');
            //$srch->addCondition('lcptp_lcp_id', '!=', $profileId);
        }
        $srch->addGroupBy('product_id');
		
		$db = FatApp::getDb();
        $rs = $srch->getResultSet();
		$products = $db->fetchAll($rs, 'id');
        
        
        $json = array();
        foreach ($products as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')),
                'product_identifier' => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        //die(json_encode($json));
		die(json_encode(['pageCount' => $srch->pages(), 'products' => $json]));
    }

    public function servicesAutoComplete()
    {
        $post = FatApp::getPostedData();
        $profileId = FatApp::getPostedData('profileId', FatUtility::VAR_INT, 0);
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->addCondition('selprod_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        $srch->addOrder('selprod_title', 'ASC');
        if (!empty($post['keyword'])) {
            $srch->addCondition('selprod_title', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $srch->addCondition(SellerProduct::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(SellerProduct::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('selprod_id as id', 'selprod_title as product_name', 'selprod_title as product_identifier'));

        if (0 < $profileId) {
            $srch->joinTable(LateChargesProfileProduct::DB_TBL, 'LEFT JOIN', 'p.product_id = sppro.lcptp_product_id and sppro.lcptp_user_id = '. $this->userParentId .' AND lcptp_product_type = '. SellerProduct::PRODUCT_TYPE_ADDON, 'sppro');
            //$srch->addCondition('lcptp_lcp_id', '!=', $profileId);
        }
        
        $srch->addGroupBy('selprod_id');
		$db = FatApp::getDb();
        $rs = $srch->getResultSet();
		$products = $db->fetchAll($rs, 'id');
        
        $json = array();
        foreach ($products as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['product_name'], ENT_QUOTES, 'UTF-8')),
                'product_identifier' => strip_tags(html_entity_decode($option['product_identifier'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode(['pageCount' => $srch->pages(), 'products' => $json]));
    }
    
    public function setupProduct()
    {
        $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId());
        $frm = $this->getProductForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $data = array(
            'lcptp_user_id' => $this->userParentId,
            'lcptp_product_id' => $post['lcptp_product_id'],
            'lcptp_lcp_id' => $post['lcptp_lcp_id'],
            'lcptp_product_type' => $post['lcptp_product_type']
        );

        $spObj = new LateChargesProfileProduct();
        if (!$spObj->addProduct($data)) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProduct(int $productId, int $type)
    {
        $this->userPrivilege->canEditLateChargesManagement(UserAuthentication::getLoggedUserId());
        $defaultProfileId = LateChargesProfile::getDefaultProfileId($this->userParentId);
		if (1 > $defaultProfileId) {
			$defaultProfileId = LateChargesProfile::setDefaultProfile($this->userParentId);
		}
		
        /* [ REMOVE PRODUCT FROM CURRENT PROFILE AND ADD TO DEFAULT PROFILE */
        $data = array(
            'lcptp_user_id' => $this->userParentId,
            'lcptp_lcp_id' => $defaultProfileId,
            'lcptp_product_id' => $productId,
            'lcptp_product_type' => $type
        );

        $spObj = new LateChargesProfileProduct();
        if (!$spObj->addProduct($data)) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $this->set('msg', Labels::getLabel('LBL_Product_Removed_from_current_profile.', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getProductForm($profileId = 0, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $profileId = FatUtility::int($profileId);
        $frm = new Form('frmProfileProducts_'. $type);
        $frm->addHiddenField('LBL_Product_Name', 'lcptp_product_type', $type);
        $frm->addHiddenField('LBL_Product_Name', 'lcptp_lcp_id', $profileId)->requirement->setRequired(true);
        $productNameLlb = Labels::getLabel('LBL_Product_Name', $this->siteLangId);
        if ($type == SellerProduct::PRODUCT_TYPE_ADDON){
            $productNameLlb = Labels::getLabel('LBL_Addon_Name', $this->siteLangId);
        }
        
        $frm->addHiddenField($productNameLlb, 'lcptp_product_id', '')->requirements()->setRequired(true);
        //$fld = $frm->addTextBox('', 'product_name');
		$frm->addSelectBox($productNameLlb, 'product_name', [], '', array(), '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

	
	/* ] */

}
