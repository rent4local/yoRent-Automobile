<?php

class TopProductsReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public const REPORT_TYPE_TODAY = 1;
    public const REPORT_TYPE_WEEKLY = 2;
    public const REPORT_TYPE_MONTHLY = 3;
    public const REPORT_TYPE_YEARLY = 4;
    
    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewPerformanceReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditPerformanceReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    private function getReportTypeArr()
    {
        return array(self::REPORT_TYPE_TODAY => 'Today', self::REPORT_TYPE_WEEKLY => 'Weekly', self::REPORT_TYPE_MONTHLY => 'Monthly', self::REPORT_TYPE_YEARLY => 'Yearly');
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
        $this->_template->render(true, true, 'top-products-report/index.php');
    }

    public function search($export = false)
    {
        $this->objPrivilege->canViewPerformanceReport();
        $db = FatApp::getDb();
        $productFor = FatApp::getPostedData('product_for', FatUtility::VAR_INT, 1);

        $srchFrm = $this->getSearchForm($productFor);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getPostedData('pagesize', FatUtility::VAR_INT, 10);
        $topPerformed = FatApp::getPostedData('top_perfomed', FatUtility::VAR_INT, 0);
        $sortBy = FatApp::getPostedData('sort_by', FatUtility::VAR_INT, 0);
        
        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_STRING, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_STRING, '');


        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        /* $uWsrch = new UserWishListProductSearch($this->adminLangId); */
        $uWsrch = new UserFavoriteProductSearch($this->adminLangId);
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        /* $uWsrch->joinWishLists(); */
        $uWsrch->addGroupBy('ufp_selprod_id');
        /* $uWsrch->addMultipleFields(array('uwlp_selprod_id', 'uwlist_user_id', 'count(uwlist_user_id) as wishlist_user_counts')); */
        $uWsrch->addMultipleFields(array('ufp_selprod_id', 'ufp_user_id', 'count(ufp_user_id) as wishlist_user_counts'));
        /* ] */

        $srch = new OrderProductSearch($this->adminLangId, true);
        $srch->joinPaymentMethod();
        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.ufp_selprod_id = op.op_selprod_id', 'tquwl');
        $srch->doNotCalculateRecords();
        if ($topPerformed) {
            $srch->addStatusCondition(OrderStatus::ORDER_COMPLETED);
        } else {
            $srch->addStatusCondition([OrderStatus::ORDER_CANCELLED, OrderStatus::ORDER_REFUNDED]);
        }
        
        /* $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"))); */
        $cancellOrderStatus = FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", FatUtility::VAR_INT, 0);
		
        if (trim($dateFrom) != '') { 
           $srch->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00'); 
        }
        if (trim($dateTo) != '') {
           $srch->addCondition('o.order_date_added', '<=', $dateTo . ' 00:00:00'); 
        }
        
        $cnd = $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
        $srch->addCondition('opd_sold_or_rented', '=', $productFor);

        $srch->addMultipleFields(
			array('op_selprod_title', 'op_product_name', 'op_shop_name', 'op_shop_id', 'op_selprod_options', 'op_brand_name', 'SUM(op_refund_qty) as totRefundQty', 'op.op_selprod_id', 'count(distinct tquwl.ufp_user_id) as followers', 'IFNULL(tquwl.wishlist_user_counts, 0) as wishlistUserCounts', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrderQty')
		);

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $srch->addFld('sum(IF(opd.opd_sold_or_rented = 2, op_qty, 0)) as totRentQty');
            if ($topPerformed) {
                $srch->addOrder('totRentQty', 'desc');
                $srch->addHaving('totRentQty', '>', 0);
            } else {
                $srch->addOrder('totRefundQty', 'desc');
                $srch->addOrder('cancelledOrderQty', 'desc');
                $cnd = $srch->addHaving('totRefundQty', '>', 0);
				$cnd->setDirectString('(totRefundQty > 0 OR cancelledOrderQty > 0)');
            }
        } else {
            $srch->addFld('SUM(IF(opd.opd_sold_or_rented = 1, op_qty - op_refund_qty, 0)) as totSoldQty');
            if ($topPerformed) {
                $srch->addOrder('totSoldQty', 'desc');
                $srch->addHaving('totSoldQty', '>', 0);
            } else {
                $srch->addOrder('totRefundQty', 'desc');
                $cnd = $srch->addHaving('totRefundQty', '>', 0);
				$cnd->setDirectString('(totRefundQty > 0 OR cancelledOrderQty > 0)');
            }
        }

		
        $srch->addGroupBy('op.op_selprod_id');
        $srch->addGroupBy('op.op_is_batch');

        /* echo $srch->getQuery(); die; */
        $reportType = FatApp::getPostedData('report_type', FatUtility::VAR_INT, 0);
        if ($reportType) {
            switch ($reportType) {
                case self::REPORT_TYPE_TODAY:
                    $srch->addDirectCondition('DATE(o.order_date_added)=DATE(NOW())');
                    break;

                case self::REPORT_TYPE_WEEKLY:
                    $srch->addDirectCondition('YEARWEEK(o.order_date_added)=YEARWEEK(NOW())');
                    break;

                case self::REPORT_TYPE_MONTHLY:
                    $srch->addDirectCondition('MONTH(o.order_date_added)=MONTH(NOW())');
                    break;

                case self::REPORT_TYPE_YEARLY:
                    $srch->addDirectCondition('YEAR(o.order_date_added)=YEAR(NOW())');
                    break;
            }
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);

        if ($export == 'export') {
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Product', $this->adminLangId), Labels::getLabel('LBL_Custom_Title', $this->adminLangId), Labels::getLabel('LBL_Options', $this->adminLangId), Labels::getLabel('LBL_Brand', $this->adminLangId), Labels::getLabel('LBL_Shop', $this->adminLangId), Labels::getLabel('LBL_WishList_User_Counts', $this->adminLangId));
            if ($productFor == applicationConstants::PRODUCT_FOR_RENT && $topPerformed) {
                array_push($arr, Labels::getLabel('LBL_Rental_Quantity', $this->adminLangId));
            } elseif ($productFor == applicationConstants::PRODUCT_FOR_SALE && $topPerformed) {
                array_push($arr, Labels::getLabel('LBL_Sold_Quantity', $this->adminLangId));
            } else {
                array_push($arr, Labels::getLabel('LBL_Refund_Quantity', $this->adminLangId), Labels::getLabel('LBL_Cancelled_Orders_Qty', $this->adminLangId));
            }

            array_push($sheetData, $arr);

            while ($row = $db->fetch($rs)) {
                $arr = array($row['op_product_name'], $row['op_selprod_title'], $row['op_selprod_options'], $row['op_brand_name'], $row['op_shop_name'], $row['followers']);
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT && $topPerformed) {
                    array_push($arr, $row['totRentQty']);
                } elseif ($productFor == applicationConstants::PRODUCT_FOR_SALE && $topPerformed) {
                    array_push($arr, $row['totSoldQty']);
                } else {
                    array_push($arr, $row['totRefundQty'], $row['cancelledOrderQty']);
                }
                array_push($sheetData, $arr);
            }
            if ($topPerformed) {
                CommonHelper::convertToCsv($sheetData, 'Top_Products_Report_' . date("d-M-Y") . '.csv', ',');
                exit;
            } else {
                CommonHelper::convertToCsv($sheetData, 'Most_Refunded_Products_Report_' . date("d-M-Y") . '.csv', ',');
                exit;
            }
        } else {
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);
            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('topPerformed', $topPerformed);
            $this->set('postedData', $post);
            $this->set('productFor', $productFor);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm($productFor)
    {
        $frm = new Form('frmTopProductsReportSearch');
        $frm->addSelectBox(Labels::getLabel('LBL_Type', $this->adminLangId), 'report_type', $this->getReportTypeArr(), '', array(), 'OverAll');
        $frm->addHiddenField('', 'page', 1);
        $frm->addSelectBox(Labels::getLabel('LBL_Record_Per_Page', $this->adminLangId), 'pagesize', array(10 => '10', 20 => '20', 30 => '30', 50 => '50'), '', array(), '');

        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
   
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        
        $productForArr = array(applicationConstants::PRODUCT_FOR_SALE => Labels::getLabel('LBL_Sell', $this->adminLangId), applicationConstants::PRODUCT_FOR_RENT => Labels::getLabel('LBL_Rent', $this->adminLangId));
        $frm->addSelectBox(Labels::getLabel('LBL_Sort_By', $this->adminLangId), 'sort_by', $productForArr, applicationConstants::PRODUCT_FOR_SALE);
        
        $frm->addHiddenField('', 'top_perfomed', 1);
        $frm->addHiddenField('', 'product_for', $productFor);
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

}
