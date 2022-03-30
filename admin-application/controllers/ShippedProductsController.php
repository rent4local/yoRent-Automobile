<?php

class ShippedProductsController extends AdminBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewShippedProducts();
    }

    public function index()
    {
        $this->objPrivilege->canViewShippedProducts();
        $frmSearch = $this->getShippedProducts();
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewShippedProducts();
        $data = FatApp::getPostedData();
        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        $shippingProfile =  (isset($data['shipping_profile'])) ? $data['shipping_profile'] : 0;
        $userName =  (isset($data['user_name'])) ? trim($data['user_name']) : 0;
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : intval($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $srch = new ShippedProducts($this->adminLangId);
        $srch->joinShipProfileProd();
        $srch->joinShippingProfile();
        $srch->addProductByAdminCondition();
        $srch->addProductDeletedCondition();
        $srch->addProductAdminShipCondition();
        $srch->addPhyProductCheckCondition();
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addMultipleFields(array('sppro.shippro_shipprofile_id, sppro.shippro_product_id, ifnull(tp_l.product_name, tp.product_identifier) as product_name, COALESCE(spprof_l.shipprofile_name, spprof.shipprofile_identifier) as shipprofile_name, tp.product_added_by_admin_id'));
        $srch->addGroupBy('sppro.shippro_product_id');
        $srch->addOrder('shippro_product_id', 'DESC');
        if (!empty($keyword)) {
            $srch->addCondition('tp_l.product_name', 'like', '%' . $keyword . '%');
        }
        if (!empty($shippingProfile)) {
            $srch->addCondition('sppro.shippro_shipprofile_id', '=', $shippingProfile);
        }
        if (!empty($userName)) {
            $srch->joinSelProdTable();
            $srch->joinUserTable();
            $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'u.user_id = uc.credential_user_id', 'uc');
            $cond = $srch->addCondition('u.user_name', 'like', '%' . $userName . '%');
            $cond->attachCondition('uc.credential_email', 'like', '%' . $userName . '%');
        }

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);



        /* Get Catelog shipped by Admin/seller */
        if (!empty($records)) {
            $prodIdArr = array_column($records, 'shippro_product_id');
            foreach ($prodIdArr as $kay => $prodId) {
                $allProd = new ShippedProducts();
                $allProd->joinSelProdTable();
                $allProd->joinUserTable();
                $allProd->addProductDeletedCondition();
                $allProd->addPhyProductCheckCondition();
                $allProd->addCondition('tp.product_id', '=', $prodId);
                $allProd->addMultipleFields(array('u.user_name'));
                $allProd->addGroupBy('u.user_id');
                $res = $allProd->getResultSet();
                $allRecords = FatApp::getDb()->fetchAll($res);
                $totalProductsCount = (count($allRecords) > 0 && !empty($allRecords[0]['user_name'])) ? count($allRecords) : 0;

                $selProd = clone $allProd;
                $selProd->joinShippedBySeller();
                $resu = $selProd->getResultSet();
                $results = FatApp::getDb()->fetchAll($resu);
                $selProdCount = (count($results) > 0) ? count($results) : 0;

                $records[$kay]['total_seller_ship'] = $selProdCount;
                $records[$kay]['total_admin_seller_ship'] = $totalProductsCount - $selProdCount;
                if (count(array_filter($allRecords)) != count($allRecords)) {
                    $records[$kay]['total_admin_seller_ship'] = 0;
                }
            }
        }
        /* End here */

        $this->set("arrListing", $records);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $data);
        $this->set('canEdit', $this->objPrivilege->canEditShippedProducts(0, true));
        $this->_template->render(false, false);
    }
    
    public function viewSellerList($productId, $adminShip = false)
    {
        $this->objPrivilege->canViewShippedProducts();
        $productId = FatUtility::int($productId);

        if (1 > $productId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }
        $data = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1); 
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        /* Get all Products */
        $srch = new ShippedProducts($this->adminLangId);
        $srch->joinShipProfileProd();
        $srch->joinShippingProfile();
        $srch->joinSelProdTable();
        $srch->joinSellerShop();
        $srch->joinUserTable();
        $srch->addProductDeletedCondition();
        $srch->addPhyProductCheckCondition();
        $srch->addCondition('tp.product_id', '=', $productId);      
        $srch->addMultipleFields(array('u.user_name, u.user_id, shop.shop_identifier, shop.shop_id'));
        $srch->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = tp.product_id and psbs.psbs_user_id = sp.selprod_user_id', 'psbs');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addGroupBy('u.user_id');
        
        if( true == $adminShip){
            $srch->addCondition('psbs.psbs_product_id', 'is', 'mysql_func_NULL', 'AND', true);
        }else{
            $srch->addCondition('psbs.psbs_product_id', 'is not', 'mysql_func_NULL', 'AND', true);
        }
        
        $records = FatApp::getDb()->fetchAll($srch->getResultSet());
   
        $this->set("arrListing", $records);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('adminShip', $adminShip);
        $this->set('adminLangId', $this->adminLangId);
        $this->_template->render(false, false);
    }

    public function updateProductsShipping($productId, $shipProfileId)
    {
        $this->objPrivilege->canEditShippedProducts();
        $productId = FatUtility::int($productId);
        $shipProfileId = FatUtility::int($shipProfileId);

        if (1 > $productId || 1 > $shipProfileId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $data = array('productId' => $productId, 'shipping_profile' => $shipProfileId);
        $frm = $this->productsShippingForm();
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function updateStatus()
    {
        $this->objPrivilege->canEditShippedProducts();
        $frm = $this->productsShippingForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (1 > $post['productId']) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (isset($post['shipping_profile']) && $post['shipping_profile'] > 0) {
            $shipProProdData = array(
                'shippro_shipprofile_id' => $post['shipping_profile'],
                'shippro_product_id' => $post['productId'],
                'shippro_user_id' => 0
            );
            $spObj = new ShippingProfileProduct();
            if (!$spObj->addProduct($shipProProdData)) {
                Message::addErrorMessage($spObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }
        $this->set('msg', Labels::getLabel('LBL_Shipping_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getShippedProducts()
    {
        $frm = new Form('frmShippedProductsSearch');
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Name_Or_Email', $this->adminLangId), 'user_name', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $shipProfileArr = ShippingProfile::getProfileArr($this->adminLangId, 0, true, true);
        $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->adminLangId), 'shipping_profile', $shipProfileArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId));
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function productsShippingForm()
    {
        $frm = new Form('productsShippingForm');
        $shipProfileArr = ShippingProfile::getProfileArr($this->adminLangId, 0,  true, true);
        $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->adminLangId), 'shipping_profile', $shipProfileArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();
        $frm->addHiddenField('', 'productId', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->adminLangId));
        return $frm;
    }
}
