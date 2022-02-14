<?php

class DeletedBrandsController extends AdminBaseController
{
    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewBrands($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditBrands($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewBrands();
        $frmSearch = $this->getSearchForm();
        $data = FatApp::getPostedData();
        if ($data) {
            $data['brand_id'] = $data['id'];
            unset($data['id']);
            $frmSearch->fill($data);
        }
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewBrands();

        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $searchForm = $this->getSearchForm();
        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $searchForm->getFormDataFromArray($data);

        $prodBrandObj = new Brand();
        $srch = $prodBrandObj->getSearchObject($this->adminLangId, false);
        $srch->addCondition('brand_deleted', '=', applicationConstants::YES);
        $srch->addFld('b.*');

        if (!empty($post['keyword'])) {
            $keyword = trim($post['keyword']);
            $condition = $srch->addCondition('b.brand_identifier', 'like', '%' . $keyword . '%');
            $condition->attachCondition('b_l.brand_name', 'like', '%' . $keyword . '%', 'OR');
        }
        
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addMultipleFields(array("b_l.brand_name"));
        $srch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        $srch->addOrder('brand_id', 'DESC');
        $rs = $srch->getResultSet();

        $this->set("arr_listing", FatApp::getDb()->fetchAll($rs));
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->_template->render(false, false);
    }

    public function restore()
    {
        $this->objPrivilege->canEditBrands();
        $post = FatApp::getPostedData();
        if ($post == false) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandId = FatUtility::int($post['brand_id']);
        if ($brandId < 1) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $brandObj = new Brand($brandId);
        $brandObj->assignValues(array('brand_deleted' => applicationConstants::NO));
        if (!$brandObj->save()) {
            Message::addErrorMessage($brandObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_setup_successful);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSearchForm()
    {
        $frm = new Form('frmDeletedBrandSearch', array('id' => 'frmDeletedBrandSearch'));
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('class' => 'search-input'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }
}
