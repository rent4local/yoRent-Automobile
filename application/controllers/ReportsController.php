<?php

class ReportsController extends SellerBaseController
{

    public function __construct($action)
    {
        parent::__construct($action);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $this->set('bodyClass', 'is--dashboard');
    }

    public function index()
    {
        if (User::isSeller()) {
            FatApp::redirectUser(UrlHelper::generateUrl('seller'));
        } elseif (User::isBuyer()) {
            FatApp::redirectUser(UrlHelper::generateUrl('buyer'));
        } else {
            FatApp::redirectUser(UrlHelper::generateUrl(''));
        }
    }

    public function productsPerformance($reportType = 1)
    {
        /* 1 - top 2 - most refunded */
        $this->userPrivilege->canViewPerformanceReport(UserAuthentication::getLoggedUserId());
        if (!User::canAccessSupplierDashboard() || !User::isSellerVerified($this->userParentId)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $srchFrm = $this->getProdPerformanceSrchForm($reportType);
        $this->set('srchFrm', $srchFrm);
        $this->set('reportType', $reportType);
        $this->_template->render(true, true);
    }

    public function searchProductsPerformance($export = "")
    {
        if (!User::canAccessSupplierDashboard()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $reportType = FatApp::getPostedData('report_type', FatUtility::VAR_INT, 1);
        
        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_STRING, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_STRING, '');
        
        $sortBy = FatApp::getPostedData('sort_by', FatUtility::VAR_STRING, applicationConstants::ORDER_TYPE_RENT);
        
        if ($page < 2) {
            $page = 1;
        }
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        if (!$shopDetails) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        /* $uWsrch = new UserWishListProductSearch($this->siteLangId); */
        $uWsrch = new UserFavoriteProductSearch($this->siteLangId);
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        /* $uWsrch->joinWishLists(); */
        $uWsrch->addGroupBy('ufp_selprod_id');
        $uWsrch->addMultipleFields(array('ufp_selprod_id', 'count(ufp_user_id) as wishlist_user_counts'));
        /* ] */

        $srch = new OrderProductSearch($this->siteLangId, true);
        $srch->joinPaymentMethod();
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.ufp_selprod_id = op.op_selprod_id', 'tquwl');
        $srch->addCondition('op_shop_id', '=', $shopDetails['shop_id']);
        //$srch->doNotCalculateRecords();
        /* $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"))); */
        if (trim($dateFrom) != '') {
            $srch->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00');
        }
        if (trim($dateTo) != '') {
            $srch->addCondition('o.order_date_added', '<=', $dateTo . ' 00:00:00');
        }
        
        if ($reportType == 1) {
            $srch->addStatusCondition(OrderStatus::ORDER_COMPLETED);
        } else {
            $srch->addStatusCondition([OrderStatus::ORDER_CANCELLED, OrderStatus::ORDER_REFUNDED]);
        }
        
        $cnd = $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'cashondelivery');
        $cnd->attachCondition('plugin_code', '=', 'payatstore');
        $srch->addMultipleFields(array('op_selprod_title', 'op_product_name', 'op_selprod_options', 'op_brand_name', 'SUM(op_refund_qty) as totRefundQty', 'SUM(op_qty - op_refund_qty) as totSoldQty', 'op.op_selprod_id', 'IFNULL(tquwl.wishlist_user_counts, 0) as wishlist_user_counts', 'op_selprod_sku', 'SUM(IF(opd_sold_or_rented = 1, op_qty - op_refund_qty, 0)) as saleQty', 'SUM(IF(opd_sold_or_rented = 2, op_qty - op_refund_qty, 0)) as rentQty', 'SUM(IF(opd_sold_or_rented = 1, op_refund_qty, 0)) as saleRefundedQty', 'SUM(IF(opd_sold_or_rented = 2, op_refund_qty, 0)) as rentRefundedQty'));
        $srch->addGroupBy('op.op_selprod_id');
        $srch->addGroupBy('op.op_is_batch');
        if ($reportType == 1) {
            $orderByFld = 'rentQty';
            if ($sortBy == applicationConstants::ORDER_TYPE_SALE) {
                $orderByFld = 'saleQty';
            }
            $srch->addOrder($orderByFld, 'desc');
            $srch->addHaving($orderByFld, '>', 0);
        } else {
            $orderByFld = 'rentRefundedQty';
            if ($sortBy == applicationConstants::ORDER_TYPE_SALE) {
                $orderByFld = 'saleRefundedQty';
            }
            $srch->addOrder($orderByFld, 'desc');
            $srch->addHaving($orderByFld, '>', 0);
        }

        if ($export == "export") {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Product', $this->siteLangId), Labels::getLabel('LBL_Custom_Title', $this->siteLangId), Labels::getLabel('LBL_Options', $this->siteLangId), Labels::getLabel('LBL_Brand', $this->siteLangId), Labels::getLabel('LBL_SKU', $this->siteLangId), Labels::getLabel('LBL_WishList_User_Counts', $this->siteLangId));

            if ($reportType == 1) {
                array_push($arr, Labels::getLabel('LBL_Sold_Quantity', $this->siteLangId));
                array_push($arr, Labels::getLabel('LBL_Rented_Quantity', $this->siteLangId));
            } else {
                array_push($arr, Labels::getLabel('LBL_Sold_Refunded_Quantity', $this->siteLangId));
                array_push($arr, Labels::getLabel('LBL_Rented_Refunded_Quantity', $this->siteLangId));
            }

            array_push($sheetData, $arr);
            while ($row = FatApp::getDb()->fetch($rs)) {
                $arr = array($row['op_product_name'], $row['op_selprod_title'], $row['op_selprod_options'], $row['op_brand_name'], $row['op_selprod_sku'], $row['wishlist_user_counts']);

                if ($reportType == 1) {
                    array_push($arr, $row['saleQty']);
                    array_push($arr, $row['rentQty']);
                } else {
                    array_push($arr, $row['saleRefundedQty']);
                    array_push($arr, $row['rentRefundedQty']);
                }

                array_push($sheetData, $arr);
            }
            $csvName = '';
            if ($reportType == 1) {
                $csvName = Labels::getLabel('LBL_Top_Performing_Products_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
            } else {
                $csvName = Labels::getLabel('LBL_Most_Refunded_Products_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
            }
            CommonHelper::convertToCsv($sheetData, $csvName, ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arrListing = FatApp::getDb()->fetchAll($rs);
            $this->set('arrListing', $arrListing);
            $this->set('reportType', $reportType);
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('postedData', $post);
            $this->_template->render(false, false);
        }
    }

    public function searchMostWishListAddedProducts($export = "")
    {
        if (!User::canAccessSupplierDashboard()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pageSize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        if (!$shopDetails) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* $srch = new ProductSearch( $this->siteLangId );
          $srch->setDefinedCriteria( 0 );
          $srch->joinProductToCategory(); */

        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        /* $uWsrch = new UserWishListProductSearch($this->siteLangId); */
        $uWsrch = new UserFavoriteProductSearch($this->siteLangId);
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        /* $uWsrch->joinWishLists(); */
        $uWsrch->addGroupBy('ufp_selprod_id');
        /* $uWsrch->addMultipleFields(array('uwlp_selprod_id', 'count(uwlist_user_id) as wishlist_user_counts')); */
        $uWsrch->addMultipleFields(array('ufp_selprod_id', 'count(ufp_user_id) as wishlist_user_counts'));
        /* ] */

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'p.product_brand_id = b.brand_id', 'b');
        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'b.brand_id = b_l.brandlang_brand_id AND b_l.brandlang_lang_id = ' . $this->siteLangId, 'b_l');
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.ufp_selprod_id = sp.selprod_id', 'tquwl');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('wishlist_user_counts', '>', applicationConstants::NO);
        $srch->addOrder('wishlist_user_counts', 'DESC');
        $srch->addMultipleFields(array('selprod_id', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'selprod_active', 'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(tquwl.wishlist_user_counts, 0) as wishlist_user_counts', 'selprod_sku'));

        if ($export == "export") {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Product', $this->siteLangId), Labels::getLabel('LBL_Custom_Title', $this->siteLangId), Labels::getLabel('LBL_Brand', $this->siteLangId), Labels::getLabel('LBL_SKU', $this->siteLangId), Labels::getLabel('LBL_User_Counts', $this->siteLangId));
            array_push($sheetData, $arr);
            while ($row = FatApp::getDb()->fetch($rs)) {
                $arr = array($row['product_name'], $row['selprod_title'], $row['brand_name'], $row['selprod_sku'], $row['wishlist_user_counts']);
                array_push($sheetData, $arr);
            }
            CommonHelper::convertToCsv($sheetData, Labels::getLabel('LBL_Most_Favorites_Products_Report', $this->siteLangId) . date("Y-m-d") . '.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();

            $arrListing = FatApp::getDb()->fetchAll($rs);
            $this->set('arrListing', $arrListing);
            $this->set('pageCount', $srch->pages());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
            $this->_template->render(false, false);
        }
    }

    public function exportMostWishListAddedProducts()
    {
        $this->searchMostWishListAddedProducts("export");
    }

    public function exportProductPerformance($orderBy = 'DESC')
    {
        $this->searchProductsPerformance($orderBy, "export");
    }

    public function productsInventory()
    {
        $this->userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId());
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $frmSrch = $this->getProductInventorySearchForm($this->siteLangId);
        $this->set('frmSrch', $frmSrch);
        $this->_template->render(true, true);
    }

    public function searchProductsInventory($export = "")
    {
        if (!User::canAccessSupplierDashboard()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 0);
        if ($page < 2) {
            $page = 1;
        }
        $userId = $this->userParentId;
        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', 'p.product_brand_id = b.brand_id', 'b');
        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'b.brand_id = b_l.brandlang_brand_id  AND brandlang_lang_id = ' . $this->siteLangId, 'b_l');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $cnd = $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $cnd->attachCondition('sprodata_rental_active', '=', applicationConstants::ACTIVE, 'OR');
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addOrder('selprod_active', 'DESC');
        $srch->addOrder('product_name');
        $srch->addMultipleFields(
                array(
                    'selprod_id', 'selprod_user_id', 'selprod_cost', 'selprod_price', 'selprod_stock', 'selprod_product_id', 'selprod_sku',
                    'selprod_active', 'selprod_available_from', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'b_l.brand_name', 'spd.*'
                )
        );

        if ($keyword = FatApp::getPostedData('keyword')) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', "%$keyword%");
            $cnd->attachCondition('brand_name', 'LIKE', "%$keyword%");
        }

        if ($export == "export") {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Product', $this->siteLangId), Labels::getLabel('LBL_Custom_Title(If_Any)', $this->siteLangId), Labels::getLabel('LBL_Product_SKU', $this->siteLangId), Labels::getLabel('LBL_Brand', $this->siteLangId), Labels::getLabel('LBL_Stock_Quantity', $this->siteLangId));
            array_push($sheetData, $arr);
            while ($row = FatApp::getDb()->fetch($rs)) {
                $arr = array($row['product_name'], $row['selprod_title'], $row['selprod_sku'], $row['brand_name'], $row['selprod_stock']);
                array_push($sheetData, $arr);
            }
            CommonHelper::convertToCsv($sheetData, Labels::getLabel('LBL_Products_Inventory_Report', $this->siteLangId) . date("Y-m-d") . '.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arrListing = FatApp::getDb()->fetchAll($rs);

            if (count($arrListing)) {
                foreach ($arrListing as &$arr) {
                    $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->siteLangId);
                }
            }

            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
            $this->set('arrListing', $arrListing);
            $this->_template->render(false, false);
        }
    }

    public function exportProductsInventoryReport()
    {
        $this->searchProductsInventory("export");
    }

    public function productsInventoryStockStatus()
    {
        $this->userPrivilege->canViewInventoryReport(UserAuthentication::getLoggedUserId());
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $frmSrch = $this->getProductInventoryStockStatusSearchForm($this->siteLangId);
        $this->set('frmSrch', $frmSrch);
        $this->_template->render(true, true);
    }

    public function searchProductsInventoryStockStatus($export = "")
    {
        if (!User::canAccessSupplierDashboard()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 0);
        if ($page < 2) {
            $page = 1;
        }

        $userId = $this->userParentId;

        /* [ */
        $orderProductSrch = new OrderProductSearch($this->siteLangId, true);
        $orderProductSrch->joinPaymentMethod();
        $orderProductSrch->doNotCalculateRecords();
        $orderProductSrch->doNotLimitRecords();
        $orderProductSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_PRODUCT_IS_ON_ORDER_STATUSES")));
        $cnd = $orderProductSrch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('pm.plugin_code', '=', 'CashOnDelivery');
        $orderProductSrch->addCondition('op.op_is_batch', '=', 0);
        $orderProductSrch->addMultipleFields(array('op.op_selprod_id', 'SUM(op_qty) as stock_on_order', 'op_selprod_options'));
        $orderProductSrch->addGroupBy('op.op_selprod_id');
        /* ] */

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->joinTable('(' . $orderProductSrch->getQuery() . ')', 'INNER JOIN', 'sp.selprod_id = qryop.op_selprod_id', 'qryop');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', 'p.product_brand_id = b.brand_id', 'b');
        $srch->joinTable(Brand::DB_TBL_LANG, 'LEFT OUTER JOIN', 'b.brand_id = b_l.brandlang_brand_id  AND brandlang_lang_id = ' . $this->siteLangId, 'b_l');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addOrder('selprod_active', 'DESC');
        $srch->addOrder('product_name');
        $srch->addMultipleFields(
                array(
                    'selprod_id', 'selprod_user_id', 'selprod_cost', 'selprod_price', 'selprod_stock', 'selprod_product_id',
                    'selprod_active', 'selprod_available_from', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'selprod_sku',
                    'b_l.brand_name', 'IFNULL(qryop.stock_on_order, 0) as stock_on_order'
                )
        );

        if ($keyword = FatApp::getPostedData('keyword')) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', "%$keyword%");
            $cnd->attachCondition('brand_name', 'LIKE', "%$keyword%");
        }

        if ($export == "export") {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Product', $this->siteLangId), Labels::getLabel('LBL_Custom_title(if_any)', $this->siteLangId), Labels::getLabel('LBL_Brand', $this->siteLangId), Labels::getLabel('LBL_Stock_Available', $this->siteLangId), Labels::getLabel('LBL_Stock_on_order', $this->siteLangId), Labels::getLabel('LBL_Unit_Price', $this->siteLangId), Labels::getLabel('LBL_Total_Value(Stock_Available*unit_Price)', $this->siteLangId));
            array_push($sheetData, $arr);
            /* while( $row = FatApp::getDb()->fetch($rs) ){
              $arr = array( $row['product_name'], $row['selprod_title'], $row['brand_name'], $row['selprod_stock'] );
              array_push($sheetData,$arr);
              } */
            CommonHelper::convertToCsv($sheetData, Labels::getLabel('LBL_Products_Inventory_Report', $this->siteLangId) . date("Y-m-d") . '.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arrListing = FatApp::getDb()->fetchAll($rs);
            if (count($arrListing)) {
                foreach ($arrListing as &$arr) {
                    $arr['options'] = SellerProduct::getSellerProductOptions($arr['selprod_id'], true, $this->siteLangId);
                }
            }
            $this->set('arrListing', $arrListing);
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
            $this->_template->render(false, false);
        }
    }

    public function exportProductsInventoryStockStatusReport()
    {
        $this->searchProductsInventoryStockStatus("export");
    }

    private function getProductInventorySearchForm($langId)
    {
        $frm = new Form('frmProductInventorySrch');
        $frm->addTextBox('', 'keyword', '');
        $frm->addHiddenField('', 'page');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    private function getProductInventoryStockStatusSearchForm($langId)
    {
        $frm = new Form('frmProductInventoryStockStatusSrch');
        $frm->addTextBox('', 'keyword', '');
        $frm->addHiddenField('', 'page');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    private function getProdPerformanceSrchForm($reportType = 1)
    {
        $frm = new Form('frmProdPerformanceSrch');
        $frm->addHiddenField('','report_type', $reportType);
        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField('', 'date_from', $financialYearStart, array('placeholder' => Labels::getLabel('LBL_Date_From', $this->siteLangId), 'readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        $frm->addDateField('', 'date_to', $financialYearEnd, array('placeholder' => Labels::getLabel('LBL_Date_To', $this->siteLangId), 'readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        $typeArr = [
            applicationConstants::ORDER_TYPE_RENT => Labels::getLabel('LBL_Rental_Qty', $this->siteLangId),
            applicationConstants::ORDER_TYPE_SALE => Labels::getLabel('LBL_Sold_Qty', $this->siteLangId)
        ];
        
        $frm->addSelectBox('', 'sort_by', $typeArr, '', array(), Labels::getLabel('LBL_Sort_By', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("&nbsp;", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        //$frm->addHiddenField('','order_by');
        return $frm;
    }

    public function salesReport($orderDate = '')
    {
        $this->userPrivilege->canViewSalesReport(UserAuthentication::getLoggedUserId());
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(UrlHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $frmSrch = $this->getSalesReportSearchForm($orderDate, applicationConstants::PRODUCT_FOR_SALE);
        $this->set('frmSrch', $frmSrch);
        $this->set('orderDate', $orderDate);
        $this->_template->render(true, true);
    }

    public function searchSalesReport($export = "")
    {
        if (!User::canAccessSupplierDashboard()) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Access!", $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $orderDate = FatApp::getPostedData('orderDate', FatUtility::VAR_STRING, '');
        $orderType = FatApp::getPostedData('orderType', FatUtility::VAR_INT, 1);
        $srchFrm = $this->getSalesReportSearchForm($orderDate, $orderType);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 0);
        if ($page < 2) {
            $page = 1;
        }
        $userId = UserAuthentication::getLoggedUserId();

        $srch = Report::salesReportObject($this->siteLangId, false, [], $orderType);
        if (empty($orderDate)) {
            $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
            if (!empty($date_from)) {
                $srch->addCondition('o.order_date_added', '>=', $date_from . ' 00:00:00');
            }

            $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
            if (!empty($date_to)) {
                $srch->addCondition('o.order_date_added', '<=', $date_to . ' 23:59:59');
            }
            $srch->addGroupBy('DATE(o.order_date_added)');
        } else {
            $this->set('orderDate', $orderDate);
            $srch->addGroupBy('op_invoice_number');
            $srch->addCondition('o.order_date_added', '>=', $orderDate . ' 00:00:00');
            $srch->addCondition('o.order_date_added', '<=', $orderDate . ' 23:59:59');
            $srch->addFld(array('op_invoice_number'));
        }

        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addOrder('order_date', 'desc');

        if ($export == "export") {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = $this->getSaleReportExportFields($orderDate);
            if ($orderType == applicationConstants::PRODUCT_FOR_RENT) {
                $arr = $this->getRentalReportExportFields($orderDate);
            }

            array_push($sheetData, $arr);
            if (empty($orderDate)) {
                if ($orderType == applicationConstants::PRODUCT_FOR_RENT) {
                    while ($row = FatApp::getDb()->fetch($rs)) {
                        $arr = array($row['order_date'], $row['totOrders'], $row['cancelledOrders'], $row['totQtys'], $row['totRefundedQtys'], $row['cancelledOrdersQty'], $row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRentalSecurity'], $row['totalRefundedAmount'], $row['cancelledOrdersAmt'], $row['totalSalesEarnings']);
                        array_push($sheetData, $arr);
                    }
                    $sheetName = Labels::getLabel('LBL_Rental_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
                } else {
                    while ($row = FatApp::getDb()->fetch($rs)) {
                        $arr = array($row['order_date'], $row['totOrders'], $row['totQtys'], $row['totRefundedQtys'], $row['cancelledOrdersQty'], $row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRefundedAmount'], $row['cancelledOrdersAmt'], $row['totalSalesEarnings']);
                        array_push($sheetData, $arr);
                    }
                    $sheetName = Labels::getLabel('LBL_Sales_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
                }
                /* while ($row = FatApp::getDb()->fetch($rs)) {
                  $arr = array($row['order_date'], $row['totOrders'], $row['totQtys'], $row['totRefundedQtys'], $row['inventoryValue'], $row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRefundedAmount'], $row['totalSalesEarnings']);
                  array_push($sheetData, $arr);
                  } */
            } else {
                if ($orderType == applicationConstants::PRODUCT_FOR_RENT) {
                    while ($row = FatApp::getDb()->fetch($rs)) {
                        $arr = array($row['op_invoice_number'], $row['totQtys'], $row['totRefundedQtys'], $row['cancelledOrdersQty']  ,$row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRentalSecurity'], $row['totalRefundedAmount'], $row['cancelledOrdersAmt'] ,$row['totalSalesEarnings']);
                        array_push($sheetData, $arr);
                    }
                    $sheetName = Labels::getLabel('LBL_Rental_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
                } else {
                    while ($row = FatApp::getDb()->fetch($rs)) {
                        $arr = array($row['op_invoice_number'], $row['totQtys'], $row['totRefundedQtys'], $row['cancelledOrdersQty'], $row['inventoryValue'], $row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRefundedAmount'], $row['cancelledOrdersAmt'], $row['totalSalesEarnings']);
                        array_push($sheetData, $arr);
                    }
                    $sheetName = Labels::getLabel('LBL_Sales_Report', $this->siteLangId) . date("Y-m-d") . '.csv';
                }
                /* while ($row = FatApp::getDb()->fetch($rs)) {
                  $arr = array($row['op_invoice_number'], $row['totQtys'], $row['totRefundedQtys'], $row['inventoryValue'], $row['orderNetAmount'], $row['taxTotal'], $row['shippingTotal'], $row['totalRefundedAmount'], $row['totalSalesEarnings']);
                  array_push($sheetData, $arr);
                  } */
            }
            CommonHelper::convertToCsv($sheetData, $sheetName, ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arrListing = FatApp::getDb()->fetchAll($rs);
			$this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $post);
            $this->set('recordCount', $srch->recordCount());
            $this->set('arrListing', $arrListing);
            if ($orderType == applicationConstants::PRODUCT_FOR_RENT) {
                $this->_template->render(false, false, 'reports/search-rentals-report.php');
            } else {
                $this->_template->render(false, false);
            }
        }
    }

    public function exportSalesReport()
    {
        $this->searchSalesReport("export");
    }

    private function getSalesReportSearchForm($orderDate = '', int $orderType = applicationConstants::PRODUCT_FOR_SALE)
    {
        $frm = new Form('frmSalesReportSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orderDate', $orderDate);
        $frm->addHiddenField('', 'orderType', $orderType);
        if (empty($orderDate)) {
            $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
            $financialYearStart = $financialYearDates['start_date'];
            $financialYearEnd = $financialYearDates['end_date'];
        
            $frm->addDateField('', 'date_from', $financialYearStart, array('placeholder' => Labels::getLabel('LBL_Date_From', $this->siteLangId), 'readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
            $frm->addDateField('', 'date_to', $financialYearEnd, array('placeholder' => Labels::getLabel('LBL_Date_To', $this->siteLangId), 'readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
            $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        }
        return $frm;
    }

    public function rentalReport($orderDate = '')
    {
        if (!User::canAccessSupplierDashboard()) {
            FatApp::redirectUser(CommonHelper::generateUrl('Account', 'supplierApprovalForm'));
        }
        $frmSrch = $this->getSalesReportSearchForm($orderDate, applicationConstants::PRODUCT_FOR_RENT);
        $this->set('frmSrch', $frmSrch);
        $this->set('orderDate', $orderDate);
        $this->_template->render(true, true);
    }

    public function getSaleReportExportFields($orderDate = ''): array
    {
        if (empty($orderDate)) {
            $arr = array(
                Labels::getLabel('LBL_Date', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Orders', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Qty', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Qty', $this->siteLangId),
                /* Labels::getLabel('LBL_Inventory_Value', $this->siteLangId), */
                Labels::getLabel('LBL_Order_Net_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Tax_Charged', $this->siteLangId),
                Labels::getLabel('LBL_Shipping_Charges', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Amount', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Commission_Charges', $this->siteLangId)
            );
        } else {
            $arr = array(
                Labels::getLabel('LBL_Invoice_Number', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Qty', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Qty', $this->siteLangId),
                /* Labels::getLabel('LBL_Inventory_Value', $this->siteLangId), */
                Labels::getLabel('LBL_Order_Net_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Tax_Charged', $this->siteLangId),
                Labels::getLabel('LBL_Shipping_Charges', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Amount', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Commission_Charges', $this->siteLangId)
            );
        }
        return $arr;
    }

    public function getRentalReportExportFields($orderDate = ''): array
    {
        if (empty($orderDate)) {
            $arr = array(
                Labels::getLabel('LBL_Date', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Orders', $this->siteLangId),
                Labels::getLabel('LBL_Cancelled_Orders', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Cancelled_Orders_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Order_Net_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Tax_Charged', $this->siteLangId),
                Labels::getLabel('LBL_Shipping_Charges', $this->siteLangId),
                Labels::getLabel('LBL_Rental_Security', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Cancelled_Orders_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Commission_Charges', $this->siteLangId)
            );
        } else {
            $arr = array(
                Labels::getLabel('LBL_Invoice_Number', $this->siteLangId),
                Labels::getLabel('LBL_No._of_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Qty', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Qty', $this->siteLangId),
                Labels::getLabel('LBL_Order_Net_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Tax_Charged', $this->siteLangId),
                Labels::getLabel('LBL_Shipping_Charges', $this->siteLangId),
                Labels::getLabel('LBL_Rental_Security', $this->siteLangId),
                Labels::getLabel('LBL_Refunded_Amount', $this->siteLangId),
				Labels::getLabel('LBL_Cancelled_Orders_Amount', $this->siteLangId),
                Labels::getLabel('LBL_Commission_Charges', $this->siteLangId)
            );
        }
        return $arr;
    }

}
