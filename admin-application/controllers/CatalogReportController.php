<?php

class CatalogReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewCatalogReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditCatalogReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewCatalogReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewCatalogReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render(true, true, 'catalog-report/index.php');
    }

    public function search($type = false)
    {
        $this->objPrivilege->canViewProductsReport();
        $db = FatApp::getDb();
        $productFor = FatApp::getPostedData('product_for', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_SALE);

        $srchFrm = $this->getSearchForm($productFor);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        
        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_STRING, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_STRING, '');


        /* get Seller Order Products[ */
        $opSrch = new OrderProductSearch($this->adminLangId, true);
        $opSrch->joinPaymentMethod();
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX, 'optax');
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING, 'opship');
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $cnd = $opSrch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'CashOnDelivery');
        if ($productFor > 0) {
            $opSrch->addCondition('opd.opd_sold_or_rented', '=', $productFor);
        }

        $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
		$cancellOrderStatus = FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", FatUtility::VAR_INT, 0);
        
        if (trim($dateFrom) != '') {
           $opSrch->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00'); 
        }
        if (trim($dateTo) != '') {
           $opSrch->addCondition('o.order_date_added', '<=', $dateTo . ' 00:00:00'); 
        }
        
        
        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
			$opSaleSrch = clone $opSrch;
			$opSaleSrch->addMultipleFields(
					array('SUBSTRING(op_selprod_code, 1, (LOCATE( "_", op_selprod_code ) - 1 )) as sellerSaleProdId', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty) as totSoldQty', 'opd_sold_or_rented', 'SUM(op_refund_qty) as refundedQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrdersQty')
			);
            $opSaleSrch->addGroupBy('opd_sold_or_rented');
            $opSaleSrch->addGroupBy('sellerSaleProdId');
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $opRentalSrch = clone $opSrch;
            $opRentalSrch->addMultipleFields(
                    array('SUBSTRING(op_selprod_code, 1, (LOCATE( "_", op_selprod_code ) - 1 )) as sellerRentProdId', 'COUNT(op_order_id) as totRentalOrders', 'SUM(op_qty) as totRentedQty', 'opd_sold_or_rented', 'SUM(op_refund_qty) as refundedQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrdersQty')
            );
            $opRentalSrch->addGroupBy('opd_sold_or_rented');
            $opRentalSrch->addGroupBy('sellerRentProdId');
        }

        $opSrch->addMultipleFields(
                array(
                    'SUBSTRING(op_selprod_code, 1, (LOCATE( "_", op_selprod_code ) - 1 )) as op_product_id',
                    'SUM(IF(op_status_id != '. $cancellOrderStatus .', ((op_unit_price) * op_qty) - (op_unit_price * op_refund_qty)  , 0)) as total', '(SUM(IF(op_status_id != '. $cancellOrderStatus .', opship.opcharge_amount - op_refund_shipping, 0))) as shippingTotal', '(SUM(IF(op_status_id != '. $cancellOrderStatus .', optax.opcharge_amount - (optax.opcharge_amount/op_qty * op_refund_qty) , 0))) as taxTotal', 'SUM(IF(op_status_id != '. $cancellOrderStatus .', op_commission_charged - op_refund_commission, 0)) as commission', 'SUM(IF(op_status_id != '. $cancellOrderStatus .', opd_rental_security * op_qty, 0)) as rentalSecurity', 'sum(op_refund_amount) as totalRefundedAmount', 'SUM(IF(op_status_id = '. $cancellOrderStatus .',((op_unit_price) * op_qty - op_refund_amount), 0)) as cancelledOrderAmt'
                )
        );
        $opSrch->addGroupBy('op_product_id');

        /* ] */

        $srch = new ProductSearch($this->adminLangId, '', '', false, false, false);
        $srch->joinBrands($this->adminLangId, false, true);
        $srch->joinProductToCategory();
        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'p.product_id = opq.op_product_id', 'opq');

        $srch->addMultipleFields(
			array(
				'product_id', 'IFNULL(tp_l.product_name,p.product_identifier) as product_name',
				'IFNULL(tb_l.brand_name, brand_identifier) as brand_name',
				'opq.total', 'opq.shippingTotal', 'opq.taxTotal', 'opq.commission', 'IFNULL(opq.totalRefundedAmount, 0) as totalRefundedAmount', 'IFNULL(opq.cancelledOrderAmt, 0) as cancelledOrderAmt'
			)
        );

        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->joinTable('(' . $opSaleSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'p.product_id = opSaleQry.sellerSaleProdId AND opSaleQry.opd_sold_or_rented = ' . applicationConstants::PRODUCT_FOR_SALE, 'opSaleQry');
            $srch->addOrder('totSoldQty', 'DESC');
            $srch->addFld(['IFNULL(opSaleQry.totOrders, 0) as totOrders', 'IFNULL(opSaleQry.totSoldQty, 0) as totSoldQty', 'IFNULL(opSaleQry.refundedQty, 0) as refundedQty', 'IFNULL(opSaleQry.cancelledOrdersQty, 0) as cancelledOrdersQty']);
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $srch->joinTable('(' . $opRentalSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'p.product_id = opRentQry.sellerRentProdId AND opRentQry.opd_sold_or_rented =' . applicationConstants::PRODUCT_FOR_RENT, 'opRentQry');
            $srch->addOrder('totRentedQty', 'DESC');
            $srch->addFld(
				['IFNULL(totRentedQty, 0) as totRentedQty', 'IFNULL(rentalSecurity, 0) as rentalSecurity', 'IFNULL(totRentalOrders, 0) as totRentalOrders', 'IFNULL(opRentQry.refundedQty, 0) as refundedQty', 'IFNULL(opRentQry.cancelledOrdersQty, 0) as cancelledOrdersQty']
            );
        }

        $srch->addGroupBy('product_id');
        $srch->addOrder('product_name');
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING));
        if (!empty($keyword)) {
            $srch->addCondition('product_name', 'LIKE', '%' . $keyword . '%');
        }

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                $arr = $this->getRentalFields();
            } else {
                $arr = $this->getSaleFields();
            }

            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $name = $row['product_name'];
                if ($row['brand_name'] != '') {
                    $name .= "\nBrand: " . $row['brand_name'];
                }
                $total = CommonHelper::displayMoneyFormat($row['total'], true, true);
                $totalRefundedAmount = CommonHelper::displayMoneyFormat($row['totalRefundedAmount'], true, true);
                $cancelledOrderAmt = CommonHelper::displayMoneyFormat($row['cancelledOrderAmt'], true, true);
                $total = CommonHelper::displayMoneyFormat($row['total'], true, true);
                $shipping = CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true);
                $tax = CommonHelper::displayMoneyFormat($row['taxTotal'], true, true);
                $commission = CommonHelper::displayMoneyFormat($row['commission'], true, true);

                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $rentalSecurity = CommonHelper::displayMoneyFormat($row['rentalSecurity'], true, true);
                    $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'] + $row['rentalSecurity'];
                    $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                    $arr = array(
                        $name, $row['totRentalOrders'], $row['totRentedQty'], $row['refundedQty'], $row['cancelledOrdersQty'],
                        $total, $shipping, $tax, $rentalSecurity, $subTotal, $totalRefundedAmount, $cancelledOrderAmt, $commission
                    );
                } else {
                    $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'];
                    $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                    $arr = array(
                        $name, $row['totOrders'], $row['totSoldQty'], $row['refundedQty'], $row['cancelledOrdersQty'],
                        $total, $shipping, $tax, $subTotal, $totalRefundedAmount, $cancelledOrderAmt, $commission
                    );
                }
                array_push($sheetData, $arr);
            }

            CommonHelper::convertToCsv($sheetData, 'Catalog_Report_' . date("d-M-Y") . '.csv', ',');
            exit;
        } else {
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $rs = $srch->getResultSet();
            $arr_listing = $db->fetchAll($rs);
            $this->set("arr_listing", $arr_listing);
            $this->set('pageCount', $srch->pages());
            $this->set('recordCount', $srch->recordCount());
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('postedData', $post);
            $this->set('reportType', $productFor);
            $this->_template->render(false, false);
        }
    }

    public function export()
    {
        $this->search('export');
    }

    private function getSearchForm($productFor)
    {
        $frm = new Form('frmCatalogReportSearch');
        $frm->addHiddenField('', 'product_for', $productFor);
        $frm->addHiddenField('', 'page', 1);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        
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

    private function getRentalFields(): array
    {
        return array(
            Labels::getLabel('LBL_Title', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Rental_Orders', $this->adminLangId),
            Labels::getLabel('LBL_Rented_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Refunded_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Total(A)', $this->adminLangId),
            Labels::getLabel('LBL_Shipping(B)', $this->adminLangId),
            Labels::getLabel('LBL_Tax(C)', $this->adminLangId),
            Labels::getLabel('LBL_Security(D)', $this->adminLangId),
            Labels::getLabel('LBL_Total(A+B+C+D)', $this->adminLangId),
            Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Commission', $this->adminLangId)
        );
    }

    private function getSaleFields(): array
    {
        return array(
            Labels::getLabel('LBL_Title', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Sold_Orders', $this->adminLangId),
            Labels::getLabel('LBL_Sold_QTY', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Total(A)', $this->adminLangId),
            Labels::getLabel('LBL_Shipping(B)', $this->adminLangId),
            Labels::getLabel('LBL_Tax(C)', $this->adminLangId),
            Labels::getLabel('LBL_Total(A+B+C)', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Commission', $this->adminLangId)
        );
    }

}
