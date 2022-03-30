<?php

class ShopsReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewShopsReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditShopsReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewShopsReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->set('reportFor', applicationConstants::PRODUCT_FOR_SALE);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewShopsReport();
        $frmSearch = $this->getSearchForm(applicationConstants::PRODUCT_FOR_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->set('reportFor', applicationConstants::PRODUCT_FOR_RENT);
        $this->_template->render(true, true, 'shops-report/index.php');
    }

    public function search($type = false, $reportFor = false)
    {
        //echo '<pre>'; print_r(FatApp::getPostedData()); echo '</pre>'; exit;
        $this->objPrivilege->canViewShopsReport();
        $db = FatApp::getDb();
        if ($reportFor == false) {
            $reportFor = FatApp::getPostedData('reportFor', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_SALE);
        }
        $srchFrm = $this->getSearchForm($reportFor);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        if ($page < 2) {
            $page = 1;
        }
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        /* shop products count sub query [ */
        $prodSrch = new ProductSearch(0);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addGroupBy('selprod_user_id');
        $prodSrch->joinSellerProducts();
        $prodSrch->addMultipleFields(array('count(selprod_id) as totStoreProducts', 'selprod_user_id'));
        /* ] */

        /* shop reviews count Sub Query[ */
        $reviewSrch = new SelProdReviewSearch();
        $reviewSrch->doNotCalculateRecords();
        $reviewSrch->doNotLimitRecords();
        $reviewSrch->joinSelProdRatingByType(SelProdRating::TYPE_PRODUCT);
        $reviewSrch->addGroupby('spreview_seller_user_id');
        $reviewSrch->addMultipleFields(array('count(spreview_id) as totReviews', 'spreview_seller_user_id'));
        /* ] */

        /* shop rating count sub query[ */
        $ratingSrch = new SelProdReviewSearch();
        $ratingSrch->doNotCalculateRecords();
        $ratingSrch->doNotLimitRecords();
        $ratingSrch->joinSelProdRating();
        $ratingSrch->addCondition('sprating_rating_type', 'in', array(SelProdRating::TYPE_SELLER_SHIPPING_QUALITY, SelProdRating::TYPE_SELLER_STOCK_AVAILABILITY, SelProdRating::TYPE_SELLER_PACKAGING_QUALITY));
        $ratingSrch->addGroupby('spreview_seller_user_id');
        $ratingSrch->addMultipleFields(array('avg(sprating_rating) as avg_rating', 'spreview_seller_user_id', 'sprating_rating'));
        /* ] */

        /* get Shop Order Products Sub Query[ */
        $opSrch = new OrderProductSearch(0, true);
        $opSrch->joinPaymentMethod();
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $cnd = $opSrch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'cashondelivery');
        $cnd->attachCondition('plugin_code', '=', 'payatstore');
        $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        $opSrch->addGroupBy('op_shop_id');

		$cancellOrderStatus = FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", FatUtility::VAR_INT, 0);

        if ($reportFor == applicationConstants::PRODUCT_FOR_SALE) {
            $opSaleSrch = clone $opSrch;
            $opSaleSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_SALE);
            $opSaleSrch->addMultipleFields(
                    array('op_shop_id as shopIdSale', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty) as totSoldQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 0, (op_unit_price) * op_qty - op_refund_amount)) as saleTotal', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 0, op_commission_charged - op_refund_commission)) as sale_commission', 'SUM(op_refund_qty) as refundedQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrdersQty', 'SUM(op_refund_amount) as totalRefundedAmount', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', (op_unit_price) * op_qty - op_refund_amount, 0)) as cancelledOrderAmt')
            );
        }
        if ($reportFor == applicationConstants::PRODUCT_FOR_RENT) {
            $opRentalSrch = clone $opSrch;
            $opRentalSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
            $opRentalSrch->addMultipleFields(
				array('op_shop_id as shopIdRent', 'COUNT(op_order_id) as totRentalOrders', 'SUM(op_qty) as totRentedQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 0, (op_unit_price) * op_qty - op_refund_amount)) as rentTotal', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 0, op_commission_charged - op_refund_commission)) as rental_commission', 'SUM(op_refund_qty) as refundedQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrdersQty', 'SUM(op_refund_amount) as totalRefundedAmount', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', (op_unit_price) * op_qty - op_refund_amount, 0)) as cancelledOrderAmt')
            );
        }

        $opSrch->addMultipleFields(
			array('op_shop_id', 'SUM( (op_unit_price) * op_qty - op_refund_amount ) as total', 'SUM(op_commission_charged - op_refund_commission) as commission', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 0, opd_rental_security)) as rentalSecurity')
        );

        /* ] */

        /* Sub Query to get, how many users marked current shop as Favorites [ */
        $uFSsrch = new UserFavoriteShopSearch();
        $uFSsrch->doNotCalculateRecords();
        $uFSsrch->doNotLimitRecords();
        $uFSsrch->addGroupBy('ufs_shop_id');
        $uFSsrch->addMultipleFields(array('ufs_shop_id', 'count(ufs_user_id) as totalFavorites'));
        /* ] */

        $srch = new ShopSearch($this->adminLangId, false, false);
        $srch->joinShopOwner(false);
        $srch->joinTable('(' . $reviewSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'spreview.spreview_seller_user_id = s.shop_user_id', 'spreview');
        $srch->joinTable('(' . $ratingSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'sprating.spreview_seller_user_id = s.shop_user_id', 'sprating');
        $srch->joinTable('(' . $prodSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod.selprod_user_id = s.shop_user_id', 'selprod');

        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT OUTER JOIN', 's.shop_id = opq.op_shop_id', 'opq');

        $srch->addMultipleFields(
                array(
                    'shop_id', 'shop_user_id', 's.shop_created_on',
                    'IFNULL(shop_name, shop_identifier) as shop_name', 'u.user_id',
                    'u.user_name as owner_name', 'u_cred.credential_email as owner_email',
                    'IFNULL(spreview.totReviews, 0) as totReviews',
                    'IFNULL(sprating.avg_rating, 0) as totRating',
                    'IFNULL(selprod.totStoreProducts, 0) as totProducts',
                    'IFNULL(opq.total, 0) as total', 'IFNULL(commission, 0) as commission',
                    'IFNULL(ufsq.totalFavorites, 0) as totalFavorites',
        ));

        if ($reportFor == applicationConstants::PRODUCT_FOR_RENT) {
            $srch->joinTable('(' . $opRentalSrch->getQuery() . ')', 'LEFT OUTER JOIN', 's.shop_id = opRentQry.shopIdRent', 'opRentQry');
            $srch->addFld(['IFNULL(rentTotal, 0) as rentTotal', 'IFNULL(totRentalOrders, 0) as totRentalOrders', 'IFNULL(totRentedQty, 0) as totRentedQty', 'IFNULL(rentalSecurity, 0) as rentalSecurity', 'rental_commission', 'IFNULL(opRentQry.refundedQty, 0) as refundedQty', 'IFNULL(opRentQry.cancelledOrdersQty, 0) as cancelledOrdersQty', 'IFNULL(opRentQry.totalRefundedAmount, 0) as totalRefundedAmount', 'IFNULL(opRentQry.cancelledOrderAmt, 0) as cancelledOrderAmt']);
        }
        if ($reportFor == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->joinTable('(' . $opSaleSrch->getQuery() . ')', 'LEFT OUTER JOIN', 's.shop_id = opSaleQry.shopIdSale', 'opSaleQry');
            $srch->addFld(['IFNULL(totSoldQty, 0) as totSoldQty', 'IFNULL(saleTotal, 0) as saleTotal', 'sale_commission',  'IFNULL(opSaleQry.refundedQty, 0) as refundedQty', 'IFNULL(opSaleQry.cancelledOrdersQty, 0) as cancelledOrdersQty', 'IFNULL(opSaleQry.totalRefundedAmount, 0) as totalRefundedAmount', 'IFNULL(opSaleQry.cancelledOrderAmt, 0) as cancelledOrderAmt']);
        }
        $srch->joinTable('(' . $uFSsrch->getQuery() . ')', 'LEFT OUTER JOIN', 's.shop_id = ufsq.ufs_shop_id', 'ufsq');
        $srch->addOrder('shop_name');
        $shop_id = FatApp::getPostedData('shop_id', null, '');
        $shop_keyword = FatApp::getPostedData('shop_name', null, '');
        if ($shop_id) {
            $shop_id = FatUtility::int($shop_id);
            $srch->addCondition('s.shop_id', '=', $shop_id);
        }

        $shop_user_id = FatApp::getPostedData('shop_user_id', null, '');
        $shop_owner_keyword = FatApp::getPostedData('user_name', null, '');
        if ($shop_user_id) {
            $shop_user_id = FatUtility::int($shop_user_id);
            $srch->addCondition('s.shop_user_id', '=', $shop_user_id);
        }

        if ($shop_id == 0 and $shop_user_id == 0 and $shop_keyword != '') {
            $cond = $srch->addCondition('shop_name', '=', $shop_keyword);
            $cond->attachCondition('shop_name', 'like', '%' . $shop_keyword . '%', 'OR');
            $cond->attachCondition('shop_identifier', 'like', '%' . $shop_keyword . '%');
        }

        if ($shop_id == 0 and $shop_user_id == 0 and $shop_owner_keyword != '') {
            $cond1 = $srch->addCondition('user_name', '=', $shop_owner_keyword);
            $cond1->attachCondition('user_name', 'like', '%' . $shop_owner_keyword . '%', 'OR');
            $cond1->attachCondition('credential_email', 'like', '%' . $shop_owner_keyword . '%');
        }

        $date_from = FatApp::getPostedData('date_from', null, '');
        if ($date_from) {
            $srch->addCondition('s.shop_created_on', '>=', $date_from);
        }

        $date_to = FatApp::getPostedData('date_to', null, '');
        if ($date_to) {
            $srch->addCondition('s.shop_created_on', '<=', $date_to);
        }

		if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();
            if ($reportFor == applicationConstants::PRODUCT_FOR_RENT) {
                $arr = $this->getRentalFields();
            } else {
                $arr = $this->getSaleFields();
            }

            array_push($sheetData, $arr);
            while ($row = $db->fetch($rs)) {
                $ownerName = $row['owner_name'];
                $ownerEmail = $row['owner_email'];
                $shopCreatedDate = FatDate::format($row['shop_created_on'], false, true, FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get()));
                /* $total = CommonHelper::displayMoneyFormat($row['total'], true, true); */
                if ($reportFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $commission = CommonHelper::displayMoneyFormat($row['rental_commission'], true, true);
                    $arr = array($row['shop_name'], $shopCreatedDate, $ownerName, $ownerEmail, $row['totProducts'], $row['totRentedQty'], $row['refundedQty'], $row['cancelledOrdersQty'], $row['rentTotal'], $row['totalFavorites'], $row['totalRefundedAmount'], $row['cancelledOrderAmt'], $commission, $row['totReviews'], round($row['totRating']));
                } else {
                    $commission = CommonHelper::displayMoneyFormat($row['sale_commission'], true, true);
                    $arr = array($row['shop_name'], $shopCreatedDate, $ownerName, $ownerEmail, $row['totProducts'], $row['totSoldQty'], $row['refundedQty'], $row['cancelledOrdersQty'], $row['saleTotal'], $row['totalFavorites'], $row['totalRefundedAmount'], $row['cancelledOrderAmt'], $commission, $row['totReviews'], round($row['totRating']));
                }
                array_push($sheetData, $arr);
            }
            CommonHelper::convertToCsv($sheetData, 'Shops_Report_' . date("d-M-Y") . '.csv', ',');
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
            $this->set('reportType', $reportFor);
            $this->_template->render(false, false);
        }
    }

    public function export($reportType)
    {
        $this->search('export', $reportType);
    }

    private function getSearchForm($reportFor)
    {
        $frm = new Form('frmShopsReportSearch');
        $frm->addHiddenField('', 'page', 1);
        $frm->addHiddenField('', 'reportFor', $reportFor);
        $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->adminLangId), 'shop_name');
        $frm->addHiddenField('', 'shop_id', 0);
        $frm->addTextBox(Labels::getLabel('LBL_Shop_Owner', $this->adminLangId), 'user_name');
        $frm->addHiddenField('', 'shop_user_id', 0);
        $fld = $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', '', array('readonly' => 'readonly'));
        $fld->htmlAfterField = Labels::getLabel('LBL_Shop_Created_date_from', $this->adminLangId);
        $fld = $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', '', array('readonly' => 'readonly'));
        $fld->htmlAfterField = Labels::getLabel('LBL_Shop_Created_Date_To', $this->adminLangId);
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getRentalFields(): array
    {
        return array(
            Labels::getLabel('LBL_Shop_Name', $this->adminLangId),
            Labels::getLabel('LBL_Created_Date', $this->adminLangId),
            Labels::getLabel('LBL_Owner_Name', $this->adminLangId),
            Labels::getLabel('LBL_Owner_Email', $this->adminLangId),
            Labels::getLabel('LBL_Items', $this->adminLangId),
            Labels::getLabel('LBL_Rented_Qty', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Rental_Total', $this->adminLangId),
            Labels::getLabel('LBL_Favorites', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Site_Commission', $this->adminLangId),
            Labels::getLabel('LBL_Reviews', $this->adminLangId),
            Labels::getLabel('LBL_Rating', $this->adminLangId)
        );
    }

    private function getSaleFields(): array
    {
        return array(
            Labels::getLabel('LBL_Shop_Name', $this->adminLangId),
            Labels::getLabel('LBL_Created_Date', $this->adminLangId),
            Labels::getLabel('LBL_Owner_Name', $this->adminLangId),
            Labels::getLabel('LBL_Owner_Email', $this->adminLangId),
            Labels::getLabel('LBL_Items', $this->adminLangId),
            Labels::getLabel('LBL_Sold_Qty', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Sale_Total', $this->adminLangId),
            Labels::getLabel('LBL_Favorites', $this->adminLangId),
			Labels::getLabel('LBL_Refunded_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Cancelled_Order_Amount', $this->adminLangId),
            Labels::getLabel('LBL_Site_Commission', $this->adminLangId),
            Labels::getLabel('LBL_Reviews', $this->adminLangId),
            Labels::getLabel('LBL_Rating', $this->adminLangId)
        );
    }

}
