<?php

class RentalsReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewRentalsReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditRentalsReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index($orderDate = '')
    {
        $this->objPrivilege->canViewRentalsReport();

        $frmSearch = $this->getSearchForm($orderDate);
        //$frmSearch->fill(array('orderDate'=>$orderDate));

        $this->set('frmSearch', $frmSearch);
        $this->set('orderDate', $orderDate);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewRentalsReport();
        $db = FatApp::getDb();
        $orderDate = FatApp::getPostedData('orderDate');
        $srchFrm = $this->getSearchForm($orderDate);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        $srch = Report::salesReportObject($this->adminLangId, false, array(), applicationConstants::PRODUCT_FOR_RENT);
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

        $srch->addOrder('order_date', 'desc');
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

    public function export()
    {
        $this->objPrivilege->canViewRentalsReport();
        $db = FatApp::getDb();
        $orderDate = FatApp::getPostedData('orderDate', FatUtility::VAR_DATE, '');
        $srchFrm = $this->getSearchForm($orderDate);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

        $srch = Report::salesReportObject($this->adminLangId, false, array(), applicationConstants::PRODUCT_FOR_RENT);
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

        $srch->addOrder('order_date', 'desc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $sheetData = array();
        $arr1 = array(
            Labels::getLabel('LBL_Sr_No.', $this->adminLangId),
            Labels::getLabel('LBL_Date', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Orders', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Cancelled_Orders', $this->adminLangId),
        );
        $arr2 = array(
            Labels::getLabel('LBL_Sr_No.', $this->adminLangId),
            Labels::getLabel('LBL_Invoice_Number', $this->adminLangId)
        );
        $arr = array(
            Labels::getLabel('LBL_No._Of_Qty', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Cancelled_Qty', $this->adminLangId),
            Labels::getLabel('LBL_Refund_Qty', $this->adminLangId),
            //Labels::getLabel('LBL_Inventory_Value', $this->adminLangId),
            Labels::getLabel('LBL_Order_Net_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Tax_Charged', $this->adminLangId),
            Labels::getLabel('LBL_Shipping_Charges', $this->adminLangId),
            Labels::getLabel('LBL_Rental_Security', $this->adminLangId),
            Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Rentals_Earnings', $this->adminLangId)
        );
        if (empty($orderDate)) {
            $arr = array_merge($arr1, $arr);
        } else {
            $arr = array_merge($arr2, $arr);
        }
        array_push($sheetData, $arr);

        $count = 1;
        while ($row = $db->fetch($rs)) {
            if (empty($orderDate)) {
                $arr1 = array($count, FatDate::format($row['order_date']), $row['totOrders'], $row['cancelledOrders']);
            } else {
                $arr1 = array($count, $row['op_invoice_number']);
            }
            $arr = array(
                $row['totQtys'],
                $row['cancelledOrdersQty'],
                $row['totRefundedQtys'],
                //$row['inventoryValue'],
                CommonHelper::displayMoneyFormat($row['orderNetAmount'], true, true),
                CommonHelper::displayMoneyFormat($row['taxTotal'], true, true),
                CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true),
                CommonHelper::displayMoneyFormat($row['totalRentalSecurity'], true, true),
                CommonHelper::displayMoneyFormat($row['totalRefundedAmount'], true, true),
                CommonHelper::displayMoneyFormat($row['cancelledOrdersAmt'], true, true),
                CommonHelper::displayMoneyFormat($row['totalSalesEarnings'], true, true),
            );
            $arr = array_merge($arr1, $arr);
            array_push($sheetData, $arr);
            $count++;
        }

        CommonHelper::convertToCsv($sheetData, 'Rentals_Report_' . date("d-M-Y") . '.csv', ',');
        exit;
    }

    private function getSearchForm($orderDate = '')
    {
        $frm = new Form('frmRentalsReportSearch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'orderDate', $orderDate);
        if (empty($orderDate)) {
            $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
            $financialYearStart = $financialYearDates['start_date'];
            $financialYearEnd = $financialYearDates['end_date'];
        
            $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));

            $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));

            $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
            $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));

            $fld_submit->attachField($fld_cancel);
        }
        return $frm;
    }

}
