<?php

class ProductReturnsController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function upcomingProductReturns()
    {
        $this->userPrivilege->canViewUpcomingProductReturns(UserAuthentication::getLoggedUserId());
        $srchFrm = $this->searchProductReturnForm();
        $srchFrm->addHiddenField('', 'product_return_type', ProductReturns::UPCOMING_RETURN_TYPE);
        $this->set('frmSearch', $srchFrm);
        $this->_template->addJs('js/product-returns.js');
        $this->_template->render();
    }

    public function overdueProductReturns()
    {
        $this->userPrivilege->canViewUpcomingProductReturns(UserAuthentication::getLoggedUserId());
        $srchFrm = $this->searchProductReturnForm();
        $srchFrm->addHiddenField('', 'product_return_type', ProductReturns::OVERDUE_RETURN_TYPE);
        $this->set('frmSearch', $srchFrm);
        $this->_template->addJs('js/product-returns.js');
        $this->_template->render();
    }

    public function overdueProductNotification()
    {
        $this->userPrivilege->canViewUpcomingProductReturns(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $productReturns = new ProductReturns();
        if ($productReturns->overdueProductNotification($post['order_id'], $post['op_id'], $this->siteLangId)) {
            FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Email_sent_successfully', $this->siteLangId));
        } else {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong_with_order._,Please_contact_with_administrator', $this->siteLangId));
        }
    }

    public function searchProductReturns()
    {
        $this->userPrivilege->canViewUpcomingProductReturns(UserAuthentication::getLoggedUserId());
        $shopDetails = Shop::getAttributesByUserId($this->userParentId, null, false);
        $user_shop_id = 0;
        if (1 > $shopDetails['shop_id']) {
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $user_shop_id = $shopDetails['shop_id'];
        $data = FatApp::getPostedData();
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        if ($data['product_return_type'] == ProductReturns::UPCOMING_RETURN_TYPE) {
            if (empty($data['start_date'])) {
                $startDate = date('Y-m-d');
            }
        }
        if ($data['product_return_type'] == ProductReturns::OVERDUE_RETURN_TYPE) {
            if (empty($data['end_date'])) {
                $endDate = date('Y-m-d');
            }
        }
        $page = !empty($data['page']) ? $data['page'] : 1;
        $productReturns = new ProductReturns();
        $srch = $productReturns->getReturnProducts($user_shop_id, $startDate, $endDate, $this->siteLangId);
        $srch->addOrder('opd_rental_end_date', 'ASC');
        $srch->setPageSize(FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10));
        $srch->setPageNumber($page);
        
        /* $totalProductReturns = $productReturns->getReturnProducts($user_shop_id, $startDate, $endDate, $page, $this->siteLangId); */
        $rs = $srch->getResultSet();
        
        $totalProductReturns = FatApp::getDb()->fetchAll($rs);
        
        
        $this->set('arrayListing', $totalProductReturns);
        $this->set('startDate', $startDate);
        $this->set('endDate', $endDate);
        $this->set('product_return_type', $data['product_return_type']);
        
        $this->set('page', $page);
        $this->set('pageSize', FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10));
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        
        
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false);
    }

    private function searchProductReturnForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->addDateField(Labels::getLabel('LBL_Start_Date', $this->siteLangId), 'start_date');
        $frm->addDateField(Labels::getLabel('LBL_End_Date', $this->siteLangId), 'end_date');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId));
        return $frm;
    }

}
