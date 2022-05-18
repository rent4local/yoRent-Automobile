<?php

class UsersReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewUsersReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditUsersReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($orderDate = '')
    {
        if(!FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) {
            FatUtility::exitWithErrorCode(404);
        }

        $this->objPrivilege->canViewUsersReport();
        $frmSearch = $this->getSearchForm(applicationConstants::ORDER_TYPE_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function rental($orderDate = '')
    {
        $this->objPrivilege->canViewUsersReport();
        $frmSearch = $this->getSearchForm(applicationConstants::ORDER_TYPE_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render(true, true, 'users-report/index.php');
    }

    public function search($type = false)
    {
        $this->objPrivilege->canViewUsersReport();
        $db = FatApp::getDb();

        $reportType = FatApp::getPostedData('report_for', FatUtility::VAR_INT, applicationConstants::ORDER_TYPE_SALE);
        $srchFrm = $this->getSearchForm($reportType);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = new OrderProductSearch(0, true);
        $srch->joinPaymentMethod();
        $cnd = $srch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS')));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('o.order_user_id');
        $srch->addCondition('opd_sold_or_rented', '=', $reportType);
        //$srch->addMultipleFields(array('op.op_order_id', "SUM(op_qty - op_refund_qty) as totQtys", "SUM((op_qty*op_unit_price) - op_refund_amount - o.order_volume_discount_total) as totUserPurchase"));
        $srch->addMultipleFields(array('o.order_user_id', "count(order_id) as totUserOrders", 'op.op_order_id', "SUM(op_qty - op_refund_qty) as totUserOrderQtys", "SUM((op_qty*op_unit_price) - op_refund_amount - o.order_volume_discount_total) as totUserOrderPurchases"));
        $qryOrderProductQty = $srch->getQuery();

        /* Get Order count,total order purchase */
        /*
          $srch = new SearchBase(orders::DB_TBL, 'tord');
          $srch->joinTable(Plugin::DB_TBL, 'LEFT OUTER JOIN', 'o.order_pmethod_id = pm.plugin_id', 'pm');
          $cnd = $srch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
          $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
          $srch->joinTable('(' . $qryOrderProductQty . ')', 'LEFT OUTER JOIN', 'o.order_id = top.op_order_id', 'top');
          $srch->doNotCalculateRecords();
          $srch->doNotLimitRecords();
          $srch->addGroupBy('o.order_user_id');
          $srch->addMultipleFields(array('o.order_user_id', "count(order_id) as totUserOrders", "SUM(totQtys) as totUserOrderQtys", "SUM(totUserPurchase) as totUserOrderPurchases"));
          $srch->addCondition('opd_sold_or_rented', '=', $reportType);
          $qryOrderQty = $srch->getQuery();
         */


        /* Get User Transaction */
        $srch = Transactions::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('utxn.utxn_user_id');
        $srch->addMultipleFields(array('utxn.utxn_user_id', "SUM(utxn_credit - utxn_debit) as userBalance"));
        $qryUserBalance = $srch->getQuery();

        /* Vendor Orders */
        $srch = new OrderProductSearch(0, true);
        $srch->joinPaymentMethod();
        $cnd = $srch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS')));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('op.op_selprod_user_id');
        $srch->addCondition('opd_sold_or_rented', '=', $reportType);
        $srch->addMultipleFields(array("op_selprod_user_id as shop_owner", "COUNT(distinct op_order_id) as totVendorOrders", "SUM(op_qty - op_refund_qty) as totSoldQty", "SUM((op_unit_price*op_qty) - op_refund_amount - o.order_volume_discount_total) as totalVendorSales"));
        $qryVendorOrders = $srch->getQuery();

        $srch = User::getSearchObject(true, 0, false);
        //$srch->joinTable('(' . $qryOrderQty . ')', 'LEFT OUTER JOIN', 'u.user_id = tqoq.order_user_id', 'tqoq');
        $srch->joinTable('(' . $qryOrderProductQty . ')', 'LEFT OUTER JOIN', 'u.user_id = tqoq.order_user_id', 'tqoq');
        $srch->joinTable('(' . $qryUserBalance . ')', 'LEFT OUTER JOIN', 'u.user_id = tqub.utxn_user_id', 'tqub');
        $srch->joinTable('(' . $qryVendorOrders . ')', 'LEFT OUTER JOIN', 'u.user_id = tqvo.shop_owner', 'tqvo');
        $srch->addMultipleFields(
                array('u.*', 'uc.credential_email', 'COALESCE(tqvo.totVendorOrders,0) as totVendorOrders', 'COALESCE(tqvo.totSoldQty,0) as totSoldQty', 'COALESCE(tqvo.totalVendorSales,0) as totalVendorSales', 'user_name',
                    'u.user_regdate', 'COALESCE(tqoq.totUserOrders,0) as totUserOrders', 'COALESCE(tqoq.totUserOrderQtys,0) as totUserOrderQtys', 'COALESCE(tqoq.totUserOrderPurchases,0) as totUserOrderPurchases', 'COALESCE(tqub.userBalance,0) as totUserBalance')
        );
        $srch->addOrder('u.user_regdate', 'DESC');
        $cnd = $srch->addCondition('u.user_is_buyer', '=', '1');
        $cnd->attachCondition('u.user_is_supplier', '=', '1');

        $date_from = FatApp::getPostedData('date_from', FatUtility::VAR_DATE, '');
        if (!empty($date_from)) {
            $srch->addCondition('u.user_regdate', '>=', $date_from . ' 00:00:00');
        }

        $date_to = FatApp::getPostedData('date_to', FatUtility::VAR_DATE, '');
        if (!empty($date_to)) {
            $srch->addCondition('u.user_regdate', '<=', $date_to . ' 23:59:59');
        }

        $keyword = trim(FatApp::getPostedData('keyword', null, ''));
        if (!empty($keyword)) {
            $cond = $srch->addCondition('uc.credential_username', '=', $keyword);
            $cond->attachCondition('uc.credential_email', 'like', '%' . $keyword . '%', 'OR');
            $cond->attachCondition('u.user_name', 'like', '%' . $keyword . '%');
        }

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            $arr = array(Labels::getLabel('LBL_Name', $this->adminLangId), Labels::getLabel('LBL_Email', $this->adminLangId), Labels::getLabel('LBL_Date', $this->adminLangId), Labels::getLabel('LBL_Bought_Qty', $this->adminLangId), Labels::getLabel('LBL_Sold_Qty', $this->adminLangId), Labels::getLabel('LBL_Orders_Placed', $this->adminLangId), Labels::getLabel('LBL_Orders_Reviewed', $this->adminLangId), Labels::getLabel('LBL_Purchases', $this->adminLangId), Labels::getLabel('LBL_Sales', $this->adminLangId), Labels::getLabel('LBL_Balance', $this->adminLangId));
            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $orderPurchase = CommonHelper::displayMoneyFormat($row['totUserOrderPurchases'], true, true);
                $vendorSales = CommonHelper::displayMoneyFormat($row['totalVendorSales'], true, true);
                $userBalance = CommonHelper::displayMoneyFormat($row['totUserBalance'], true, true);
                $arr = array($row['user_name'], $row['credential_email'], FatDate::format($row['user_regdate']), $row['totUserOrderQtys'], $row['totSoldQty'], $row['totUserOrders'], $row['totVendorOrders'], $orderPurchase, $vendorSales, $userBalance);
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, str_replace("{reportgenerationdate}", date("d-M-Y"), Labels::getLabel("LBL_Buyers/Sellers_Report_{reportgenerationdate}", $this->adminLangId)) . '.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pagesize);
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);

            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pagesize);
            $this->set('postedData', $post);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm(int $reportType = applicationConstants::ORDER_TYPE_SALE)
    {
        $frm = new Form('frmUsersReportSearch');
        $frm->addHiddenField('', 'report_for', $reportType);
        $frm->addHiddenField('', 'page', 1);
        
        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        $frm->addDateField(Labels::getLabel('LBL_Reg._Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        $frm->addTextBox(Labels::getLabel('LBL_Name_Or_Email', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

}
