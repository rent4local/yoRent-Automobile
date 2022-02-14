<?php

class AttachVerificationFieldsController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
        if (!FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1)) {
            Message::addInfo(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller'));
        }
    }

    public function index()
    {
        $this->userPrivilege->canViewVerificationFields(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $this->set("canEdit", $this->userPrivilege->canEditVerificationFields(UserAuthentication::getLoggedUserId(), true));
        $this->set("verFldFrm", $this->getAttachVerificationFldForm());
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchAttachedFldProducts()
    {
        $this->userPrivilege->canViewVerificationFields(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;

        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $productId = FatApp::getPostedData('product_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $prodSrch = SellerProduct::searchProductsVerificationFields($this->siteLangId, 'product_id');

        if ($keyword != '') {
            $cnd = $prodSrch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('product_identifier', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $prodSrch->addCondition('ptvf_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
        $prodSrch->setPageNumber($page);
        $prodSrch->setPageSize($pagesize);
        $prodSrch->addGroupBy('product_id');
        $rs = $prodSrch->getResultSet();
        $db = FatApp::getDb();
        $data = $db->fetchAll($rs);
        
        $arrListing = array();
        foreach ($data as $val) {
            $productId = $val['product_id'];
            $srch = SellerProduct::searchProductsVerificationFields($this->siteLangId);
            $srch->addCondition('ptvf_user_id', '=', 'mysql_func_'. $userId, 'AND', true);
            $srch->addCondition('ptvf_product_id', '=', 'mysql_func_'. $productId, 'AND', true);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $arrListing[$productId] = $db->fetchAll($rs);
        }

        $this->set("arrListing", $arrListing);
        $this->set('page', $page);
        $this->set('pageCount', $prodSrch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $prodSrch->recordCount());
        $this->set('canEdit', $this->userPrivilege->canEditVerificationFields(UserAuthentication::getLoggedUserId(), true));
        $this->set('pageSize', $pagesize);
        $this->_template->render(false, false);
    }

    public function attachVerificationField()
    {
        $this->userPrivilege->canEditVerificationFields(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();

        $product_id = FatUtility::int($post['product_id']);

        if ($product_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_Select_A_Valid_Product', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $verificationFlds = (isset($post['verification_fields']) && is_array($post['verification_fields'])) ? $post['verification_fields'] : array();

        if (count($verificationFlds) < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_You_need_to_add_atleast_one_verification_field", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['product_id']);
        $sellerProdObj = new SellerProduct();
        if (!$sellerProdObj->addUpdateVerificationField($product_id, $this->userParentId, $verificationFlds)) {
            Message::addErrorMessage($sellerProdObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Verification_Fields_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteProductsVerificationFlds($product_id, $vflds_id)
    {
        $product_id = FatUtility::int($product_id);
        $vflds_id = FatUtility::int($vflds_id);
        if (!$product_id || !$vflds_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProduct::DB_TBL_PRODUCT_TO_VERIFICATION_FLD, array('smt' => 'ptvf_product_id = ? AND ptvf_vflds_id = ?', 'vals' => array($product_id, $vflds_id)))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('product_id', $product_id);
        $this->set('msg', Labels::getLabel('LBL_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function verificationFieldsAutoComplete()
    {
        $pagesize = 20;
        $db = FatApp::getDb();
        $json = array();
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selectedFields = FatApp::getPostedData('selectedFields', FatUtility::VAR_INT, []);
        
        if ($page < 2) {
            $page = 1;
        }

        $srch = VerificationFields::getSearchObject($this->siteLangId, true);
        $srch->doNotCalculateRecords();
        $srch->addOrder('vflds_id');
        $srch->addMultipleFields(array('vflds_id as id', 'IFNULL(vflds_name,vflds_identifier) as vflds_name', 'vflds_required', 'vflds_type'));
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('vflds_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('vflds_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }
        
        if (!empty($selectedFields) && is_array($selectedFields)) {
            $srch->addCondition('vflds_id', 'NOT IN', $selectedFields);
        }
        

        $srch->setPageSize($pagesize);
        $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $vFlds = $db->fetchAll($rs, 'id');
        $pageCount = $srch->pages();

        $json = array();
        $fldTypeArr = VerificationFields::getFldTypeArr($this->siteLangId);
        foreach ($vFlds as $key => $option) {
            $is_required = ($option['vflds_required']) ? '<span class="spn_must_field">*</span>' : '';
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['vflds_name'] . ' ' . $is_required . ' [ ' . $fldTypeArr[$option['vflds_type']] . ' ]', ENT_QUOTES, 'UTF-8')),
            );
        }
        die(json_encode(['pageCount' => $pageCount, 'verificationFlds' => $json]));
    }

    public function getAttachedFieldsList($product_id)
    {
        $product_id = FatUtility::int($product_id);
        $srch = SellerProduct::searchProductsVerificationFields($this->siteLangId);
        $srch->addCondition('ptvf_user_id', '=', 'mysql_func_'. $this->userParentId, 'AND', true);
        $srch->addCondition(SellerProduct::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'product_id', '=', 'mysql_func_'. $product_id, 'AND', true);
        $srch->addOrder('ptvf_vflds_id', 'DESC');
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetchAll($rs);

        $fldTypeArr = VerificationFields::getFldTypeArr($this->siteLangId);
        foreach ($data as $key => $value) {
            $is_required = ($value['vflds_required']) ? '<span class="spn_must_field">*</span>' : '';
            $data[$key]['name'] = strip_tags(html_entity_decode($value['vflds_name'] . ' ' . $is_required . ' [ ' . $fldTypeArr[$value['vflds_type']] . ' ]', ENT_QUOTES, 'UTF-8'));
        }

        $json = array(
            'verificationFlds' => $data
        );
        FatUtility::dieJsonSuccess($json);
    }

    private function getAttachVerificationFldForm()
    {
        $frm = new Form('frmAttachVerificationFldFrm');

        $frm->addHiddenField('', 'product_id', 0);

        $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'product_name', [], '', array('id' => 'ver-products-js', 'class' => 'products--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $this->siteLangId)));
        $prodName->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Verification_Flds', $this->siteLangId), 'verification_fields', [], '');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save', $this->siteLangId));
        return $frm;
    }

    public function view($vfld_id = 0)
    {
        $this->userPrivilege->canViewVerificationFields(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        // $vfld_id = FatUtility::int($vfld_id);
        $this->set('frmSearch', $this->getVerificationFldSearchForm());
        // $this->set('product_id', $product_id);

        $srch = VerificationFields::getSearchObject($this->siteLangId, true);
        $srch->addOrder('vflds_id');
        $srch->addMultipleFields(array('vflds_id as id', 'IFNULL(vflds_name,vflds_identifier) as vflds_name', 'vflds_required', 'vflds_type'));
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('vflds_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('vflds_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }

        // $srch->setPageSize($pagesize);
        // $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $vFlds = FatApp::getDb()->fetchAll($rs, 'id');
        $pageCount = $srch->pages();

        $adminCatalogs = $srch->recordCount();
        $this->set('adminCatalogs', $adminCatalogs);
        $this->_template->addJs(['js/tagify.min.js', 'js/tagify.polyfills.min.js']);
        $this->_template->render(true, true);
    }

    public function verificationFieldsList()
    {
        $frmVerificationSrch = $this->getVerificationFldSearchForm($this->siteLangId);
        $this->set('frmSrch', $frmVerificationSrch);
        $this->_template->render(true, true);
    }

    public function verificationFldSearchListing()
    {
        $frm = $this->getVerificationFldSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = VerificationFields::getSearchObject($this->siteLangId, true);
        $srch->addOrder('vflds_id');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('vflds_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('vflds_identifier', 'LIKE', '%' . $post['keyword'] . '%');
        }

        $srch->setPageSize($pagesize);
        $srch->setPageNumber($page);
        $rs = $srch->getResultSet();
        $vFlds = FatApp::getDb()->fetchAll($rs);

        $this->set('vFlds', $vFlds);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    private function getVerificationFldSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $this->siteLangId)));

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page', 1);
        return $frm;
    }

}
