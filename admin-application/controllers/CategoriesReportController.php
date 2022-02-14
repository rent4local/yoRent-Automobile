<?php

class CategoriesReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPerformanceReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPerformanceReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewPerformanceReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewPerformanceReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render(true, true, 'categories-report/index.php');
    }

    public function search($export = false)
    {
        $this->objPrivilege->canViewPerformanceReport();
        $db = FatApp::getDb();
        $productFor = FatApp::getPostedData('product_for', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_SALE);
        $srchFrm = $this->getSearchForm($productFor);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        $reportType = FatApp::getPostedData('topPerfomer', FatUtility::VAR_INT, 1);
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, 10);
        $orderBy = FatApp::getPostedData('order_by', FatUtility::VAR_STRING, 'DESC');
        
        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_STRING, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_STRING, '');

        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        /* $uWsrch = new UserWishListProductSearch(); */
        $uWsrch = new UserFavoriteProductSearch();
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        /* $uWsrch->joinWishLists(); */
        $uWsrch->joinSellerProducts();
        $uWsrch->joinProducts();
        $uWsrch->joinProductToCategory();
        $uWsrch->addGroupBy('ptc_prodcat_id');
        /* $uWsrch->addMultipleFields(array('uwlp_selprod_id', 'uwlist_user_id', 'ptc_prodcat_id', 'count(uwlist_user_id) as wishlist_user_counts')); */
        $uWsrch->addMultipleFields(array('ufp_selprod_id', 'ufp_user_id', 'ptc_prodcat_id', 'count(ufp_user_id) as wishlist_user_counts'));

        /* ] */

        $opSrch = new OrderProductSearch($this->adminLangId, true);
        $opSrch->joinPaymentMethod();
        $opSrch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'SUBSTRING(op_selprod_code, 1,(LOCATE( "_", op_selprod_code) - 1)) = ptc.ptc_product_id', 'ptc');
        $opSrch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'ptc.ptc_prodcat_id = pc.prodcat_id', 'pc');
        $opSrch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'op_selprod_id = sp.selprod_id', 'sp');
        $opSrch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'sp.selprod_id = spd.sprodata_selprod_id', 'spd');
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $cnd = $opSrch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'cashondelivery');
        /* $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"))); */
        
        if (trim($dateFrom) != '') { 
            $opSrch->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00'); 
        }
        if (trim($dateTo) != '') {
           $opSrch->addCondition('o.order_date_added', '<=', $dateTo . ' 00:00:00'); 
        }
        
        if ($reportType == 1) {
            $opSrch->addStatusCondition(OrderStatus::ORDER_COMPLETED);
        } else {
            $opSrch->addStatusCondition([OrderStatus::ORDER_CANCELLED, OrderStatus::ORDER_REFUNDED]);
        }
        
        $opSrch->addGroupBy('pc.prodcat_id');
        $opSrch->addCondition('opd.opd_sold_or_rented', '=', $productFor);
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $opSrch->addMultipleFields(
                    array('prodcat_id as soldProdCatId', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty) as totSoldQty', 'SUM(op_refund_qty) as totRefundedQty')
            );
        } else {
            $opSrch->addFld(
                    array('prodcat_id as soldProdCatId', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty - op_refund_qty) as totSoldQty', 'SUM(op_refund_qty) as totRefundedQty')
            );
        }

        $srch = new ProductCategorySearch($this->adminLangId, false, false, false, false);
        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'ptc');
        $srch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_product_id = ptc.ptc_product_id', 'sp');
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.ptc_prodcat_id = c.prodcat_id', 'tquwl');
        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT JOIN', 'c.prodcat_id = opSaleQry.soldProdCatId', 'opSaleQry');

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
                array(
                    'c.prodcat_id', 'IFNULL(c.prodcat_identifier, c_l.prodcat_name) as prodcat_name',
                    'GETCATCODE(prodcat_id) AS prodcat_code', 'prodcat_active', 'prodcat_deleted',
                    'IFNULL(tquwl.wishlist_user_counts, 0) as wishlistUserCounts',
                    'IFNULL(totSoldQty, 0) as totSoldQty', 'totOrders', 'IFNULL(totRefundedQty, 0) as totRefundedQty'
                )
        );

        $srch->addGroupBy('prodcat_id');
        if ($reportType == 1) {
            $srch->addHaving('totSoldQty', '>', 0);
            $srch->addOrder('totSoldQty', $orderBy);
        } else {
            $srch->addHaving('totRefundedQty', '>', 0);
        }
        $srch->addOrder('prodcat_name');
        if ($export == 'export') {
            /* Cat Tree Structure Assoc Arr[ */
            $catObj = new ProductCategory();
            $catTreeAssocArr = $catObj->getProdCatTreeStructure(0, $this->adminLangId, '', 0, '', false, false, true);
            /* ] */

            $rs = $srch->getResultSet();
            $sheetData = array();
            
            if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
                $qtyLabel = Labels::getLabel('LBL_Sold_Quantity', $this->adminLangId);
            } else {
                $qtyLabel = Labels::getLabel('LBL_Rented_Quantity', $this->adminLangId);
            }

            $arr = array(
                Labels::getLabel('LBL_Category', $this->adminLangId),
                $qtyLabel, 
                Labels::getLabel('LBL_Refunded_Quantity', $this->adminLangId),
                Labels::getLabel('LBL_Favorites', $this->adminLangId)
            );
            
            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $arr = array($catTreeAssocArr[$row['prodcat_id']], $row['totSoldQty'],  $row['totRefundedQty'], $row['wishlistUserCounts']);
                array_push($sheetData, $arr);
            }
            if ($reportType == 1) {
                CommonHelper::convertToCsv($sheetData, 'Top_Categories_Report_' . date("d-M-Y") . '.csv', ',');
                exit;
            } else {
                CommonHelper::convertToCsv($sheetData, 'Bad_Categories_Report_' . date("d-M-Y") . '.csv', ',');
                exit;
            }
        } else {
            /* Cat Tree Structure Assoc Arr[ */
            $catObj = new ProductCategory();
            $catTreeAssocArr = $catObj->getProdCatTreeStructure(0, $this->adminLangId, '', 0, '', false, false);
            /* ] */

            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);
            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->set('productFor', $productFor);
            $this->set('reportType', $reportType);
            $this->set('catTreeAssocArr', $catTreeAssocArr);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm($productFor)
    {
        $frm = new Form('frmTopCategoriesReportSearch');
        $frm->addHiddenField('', 'product_for', $productFor);
        $frm->addHiddenField('', 'page', 1);
        $frm->addSelectBox(Labels::getLabel('LBL_Record_Per_Page', $this->adminLangId), 'pagesize', array(10 => '10', 20 => '20', 30 => '30', 50 => '50'), '', array(), '');
        $frm->addHiddenField('', 'order_by', 'DESC');
        
        $typeArr = array(1 => Labels::getLabel('LBL_Top_Category', $this->adminLangId), 2 => Labels::getLabel('LBL_Bad_Category', $this->adminLangId));
        
        $frm->addSelectBox(Labels::getLabel('LBL_Report_For', $this->adminLangId), 'topPerfomer', $typeArr, '', array(), '');
        
        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
   
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

}
