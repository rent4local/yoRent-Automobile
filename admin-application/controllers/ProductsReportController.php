<?php

class ProductsReportController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewProductsReport($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditProductsReport($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        $this->objPrivilege->canViewProductsReport();
        $frmSearch = $this->getSearchForm(applicationConstants::ORDER_TYPE_SALE);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render();
    }

    public function rental()
    {
        $this->objPrivilege->canViewProductsReport();
        $frmSearch = $this->getSearchForm(applicationConstants::ORDER_TYPE_RENT);
        $this->set('frmSearch', $frmSearch);
        $this->_template->render(true, true, 'products-report/index.php');
    }

    public function search($type = false)
    {
        $this->objPrivilege->canViewProductsReport();
        $db = FatApp::getDb();
        $productFor = FatApp::getPostedData('product_for', FatUtility::VAR_INT, 0);

        $srchFrm = $this->getSearchForm($productFor);
        $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());
        
        $dateFrom = FatApp::getPostedData('date_from', FatUtility::VAR_STRING, '');
        $dateTo = FatApp::getPostedData('date_to', FatUtility::VAR_STRING, '');
        
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        /* get Seller Order Products[ */
        $opSrch = new OrderProductSearch($this->adminLangId, true);
        $opSrch->joinPaymentMethod();
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX, 'optax');
        $opSrch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING, 'opship');
        $opSrch->doNotCalculateRecords();
        $opSrch->doNotLimitRecords();
        $cnd = $opSrch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'cashondelivery');
        $cnd->attachCondition('plugin_code', '=', 'payatstore');
        $opSrch->addStatusCondition(unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
        
        if (trim($dateFrom) != '') {
           $opSrch->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00'); 
        }
        if (trim($dateTo) != '') {
           $opSrch->addCondition('o.order_date_added', '<=', $dateTo . ' 00:00:00'); 
        }
        
        if ($productFor > 0) {
            $opSrch->addCondition('opd.opd_sold_or_rented', '=', $productFor);
        }
        
        $opSrch->addGroupBy('op_selprod_id');
        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            $opSaleSrch = clone $opSrch;
            $opSaleSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_SALE);
            $opSaleSrch->addMultipleFields(
                    array('op_selprod_id as sellerSaleProdId', 'COUNT(op_order_id) as totOrders', 'SUM(op_qty - op_refund_qty) as totSoldQty')
            );
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $opRentalSrch = clone $opSrch;
            $opRentalSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
            $opRentalSrch->addMultipleFields(
                    array('op_selprod_id as sellerRentProdId', 'COUNT(op_order_id) as totRentalOrders', 'SUM(op_qty) as totRentedQty')
            );
        }

        $opSrch->addMultipleFields(
                array(
                    'op_selprod_id', 'SUM((op_unit_price) * op_qty - op_refund_amount) as total',
                    '(SUM(opship.opcharge_amount)) as shippingTotal', '(SUM(optax.opcharge_amount)) as taxTotal',
                    'SUM(op_commission_charged - op_refund_commission) as commission',
                    'SUM(opd_rental_security *op_qty) as rentalSecurity'
                )
        );

        /* get Seller product Options[ */
        $spOptionSrch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'spo');
        $spOptionSrch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
        $spOptionSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = ' . $this->adminLangId, 'ov_lang');
        $spOptionSrch->joinTable(Option::DB_TBL, 'INNER JOIN', '`option`.option_id = ov.optionvalue_option_id', '`option`');
        $spOptionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', '`option`.option_id = option_lang.optionlang_option_id AND option_lang.optionlang_lang_id = ' . $this->adminLangId, 'option_lang');
        $spOptionSrch->doNotCalculateRecords();
        $spOptionSrch->doNotLimitRecords();
        $spOptionSrch->addGroupBy('spo.selprodoption_selprod_id');
        $spOptionSrch->addMultipleFields(array('spo.selprodoption_selprod_id', 'IFNULL(option_name, option_identifier) as option_name', 'IFNULL(optionvalue_name, optionvalue_identifier) as optionvalue_name', 'GROUP_CONCAT(option_name) as grouped_option_name', 'GROUP_CONCAT(optionvalue_name) as grouped_optionvalue_name'));
        /* ] */

        /* Sub Query to get, how many users added current product in his/her wishlist[ */
        $uWsrch = new UserWishListProductSearch($this->adminLangId);
        $uWsrch->doNotCalculateRecords();
        $uWsrch->doNotLimitRecords();
        $uWsrch->joinWishLists();
        $uWsrch->addMultipleFields(array('uwlp_selprod_id', 'uwlist_user_id'));
        /* ] */

        $srch = new ProductSearch($this->adminLangId, '', '', false, false, false);
        $srch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'p.product_id = selprod.selprod_product_id', 'selprod');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'selprod.selprod_id = sprod_l.selprodlang_selprod_id AND sprod_l.selprodlang_lang_id = ' . $this->adminLangId, 'sprod_l');
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'selprod.selprod_id = spd.sprodata_selprod_id ', 'spd');
        $srch->joinSellers();
        $srch->joinBrands($this->adminLangId, false, true);
        //$srch->addCondition('brand_id', '!=', 'NULL');
        $srch->joinShops($this->adminLangId, false, false);
        $srch->joinTable('(' . $spOptionSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod_id = spoq.selprodoption_selprod_id', 'spoq');
        $srch->joinTable('(' . $opSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod.selprod_id = opq.op_selprod_id', 'opq');

        $srch->addMultipleFields(
                array(
                    'product_id', 'product_name', 'selprod_id', 'selprod_code', 'selprod_user_id', 'selprod_title',
                    'selprod_price', 'grouped_option_name', 'grouped_optionvalue_name',
                    'IFNULL(s_l.shop_name, shop_identifier) as shop_name', 'opq.total', 'opq.shippingTotal',
                    'opq.taxTotal', 'opq.commission', 'IFNULL(tb_l.brand_name, brand_identifier) as brand_name',
                    'count(distinct tquwl.uwlist_user_id) as followers',
                )
        );

        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->joinTable('(' . $opSaleSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod.selprod_id = opSaleQry.sellerSaleProdId', 'opSaleQry');
            $srch->addOrder('totSoldQty', 'desc');
            $srch->addFld(['IFNULL(totOrders, 0) as totOrders', 'IFNULL(totSoldQty, 0) as totSoldQty']);
        }
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $srch->joinTable('(' . $opRentalSrch->getQuery() . ')', 'LEFT OUTER JOIN', 'selprod.selprod_id = opRentQry.sellerRentProdId', 'opRentQry');
            $srch->addOrder('totRentedQty', 'desc');
            $srch->addFld(['IFNULL(rentalSecurity, 0) as rentalSecurity',
                'sprodata_rental_security', 'IFNULL(totRentalOrders, 0) as totRentalOrders',
                'sprodata_rental_price', 'IFNULL(totRentedQty, 0) as totRentedQty']
            );
        }

        $srch->joinTable('(' . $uWsrch->getQuery() . ')', 'LEFT OUTER JOIN', 'tquwl.uwlp_selprod_id = selprod.selprod_id', 'tquwl');
        $srch->joinProductToCategory();
        $srch->addCondition('selprod.selprod_id', '!=', 'NULL');
        $srch->addOrder('tp_l.product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        /* groupby added, because if same product is linked with multiple categories, then showing in repeat for each category[ */
        $srch->addGroupBy('selprod_id');
        /* ] */

        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING));
        if (!empty($keyword)) {
            $srch->addKeywordSearch($keyword);
        }

        $shop_id = FatApp::getPostedData('shop_id', null, '');
        if ($shop_id) {
            $shop_id = FatUtility::int($shop_id);
            $srch->addShopIdCondition($shop_id);
        }

        $brand_id = FatApp::getPostedData('brand_id', null, '');
        if ($brand_id) {
            $brand_id = FatUtility::int($brand_id);
            $srch->addBrandCondition($brand_id);
        }

        $category_id = FatApp::getPostedData('category_id', null, '');
        if ($category_id) {
            $category_id = FatUtility::int($category_id);
            $srch->addCategoryCondition($category_id);
        }

        $price_from = FatApp::getPostedData('price_from', FatUtility::VAR_FLOAT, 0);
        if (!empty($price_from)) {
            $min_price_range_default_currency = CommonHelper::getDefaultCurrencyValue($price_from, false, false);
            $srch->addCondition('selprod_price', '>=', $min_price_range_default_currency);
        }

        $price_to = FatApp::getPostedData('price_to', FatUtility::VAR_FLOAT, 0);
        if (!empty($price_to)) {
            $max_price_range_default_currency = CommonHelper::getDefaultCurrencyValue($price_to, false, false);
            $srch->addCondition('selprod_price', '<=', $max_price_range_default_currency);
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->addCondition('spd.sprodata_is_for_sell', '=', applicationConstants::YES);
            $flds = $this->getSaleFields();
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $srch->addCondition('spd.sprodata_is_for_rent', '=', applicationConstants::YES);
            $flds = $this->getRentalFields();
        }

        if ($type == 'export') {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $sheetData = array();

            array_push($sheetData, $flds);
            while ($row = $db->fetch($rs)) {
                $name = $row['product_name'];
                if ($row['selprod_title'] != '') {
                    $name .= "\n" . Labels::getLabel('LBL_Custom_Title', $this->adminLangId) . ': ' . $row['selprod_title'];
                }
                $optionsData = '';
                if ($row['grouped_option_name'] != '') {
                    $groupedOptionNameArr = explode(',', $row['grouped_option_name']);
                    $groupedOptionValueArr = explode(',', $row['grouped_optionvalue_name']);
                    if (!empty($groupedOptionNameArr)) {
                        foreach ($groupedOptionNameArr as $key => $optionName) {
                            $optionsData .= $optionName . ': ' . $groupedOptionValueArr[$key] . "\n";
                        }
                    }
                }

                $brandName = '';
                if ($row['brand_name'] != '') {
                    $brandName = $row['brand_name'];
                }

                $shopName = '';
                if ($row['shop_name'] != '') {
                    $shopName = $row['shop_name'];
                }

                $total = CommonHelper::displayMoneyFormat($row['total'], true, true);
                $shipping = CommonHelper::displayMoneyFormat($row['shippingTotal'], true, true);
                $tax = CommonHelper::displayMoneyFormat($row['taxTotal'], true, true);
                $commission = CommonHelper::displayMoneyFormat($row['commission'], true, true);

                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $rentalPrice = CommonHelper::displayMoneyFormat($row['sprodata_rental_price'], true, true);
                    $rentalSecurity = CommonHelper::displayMoneyFormat($row['rentalSecurity'], true, true);
                    $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'] + $row['rentalSecurity'];
                    $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                    $arr = array(
                        $name, $optionsData, $brandName, $shopName, $rentalPrice, $row['totRentalOrders'],
                        $row['totRentedQty'], $total, $shipping, $tax, $rentalSecurity,
                        $subTotal, $commission
                    );
                } else {
                    $price = CommonHelper::displayMoneyFormat($row['selprod_price'], true, true);
                    $subTotal = $row['total'] + $row['shippingTotal'] + $row['taxTotal'];
                    $subTotal = CommonHelper::displayMoneyFormat($subTotal, true, true);
                    $arr = array(
                        $name, $optionsData, $brandName, $shopName, $price, $row['totOrders'],
                        $row['totSoldQty'], $total, $shipping, $tax, $subTotal, $commission
                    );
                }
                array_push($sheetData, $arr);
            }
            CommonHelper::convertToCsv($sheetData, 'Products_Report_' . date("d-M-Y") . '.csv', ',');
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

    private function getSearchForm(int $type = 0)
    {
        $frm = new Form('frmProductsReportSearch');
        $frm->addHiddenField('', 'product_for', $type);
        $frm->addHiddenField('', 'page', 1);
        $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword');
        
        $financialYearDates = CommonHelper::getCurrentFinanceYearStartEndDates();
        $financialYearStart = $financialYearDates['start_date'];
        $financialYearEnd = $financialYearDates['end_date'];
        
        $frm->addDateField(Labels::getLabel('LBL_Date_From', $this->adminLangId), 'date_from', $financialYearStart, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
   
        $frm->addDateField(Labels::getLabel('LBL_Date_To', $this->adminLangId), 'date_to', $financialYearEnd, array('readonly' => 'readonly', 'class' => 'small dateTimeFld field--calender'));
        
        $frm->addTextBox(Labels::getLabel('LBL_Shop', $this->adminLangId), 'shop_name');
        $frm->addTextBox(Labels::getLabel('LBL_Brand', $this->adminLangId), 'brand_name');
        $frm->addHiddenField('', 'shop_id', 0);
        $frm->addHiddenField('', 'brand_id', 0);
        $prodCatObj = new ProductCategory();
        $categoriesAssocArr = $prodCatObj->getProdCatTreeStructure(0, $this->adminLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Category', $this->adminLangId), 'category_id', $categoriesAssocArr);
        
        $frm->addTextBox(Labels::getLabel('LBL_Price_From', $this->adminLangId), 'price_from');
        $frm->addTextBox(Labels::getLabel('LBL_Price_To', $this->adminLangId), 'price_to');

        /* $productForArr = array(applicationConstants::PRODUCT_FOR_SALE => Labels::getLabel('LBL_Sell', $this->adminLangId), applicationConstants::PRODUCT_FOR_RENT => Labels::getLabel('LBL_Rent', $this->adminLangId));
          $frm->addSelectBox(Labels::getLabel('LBL_Product_For', $this->adminLangId), 'product_for', $productForArr); */

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    private function getRentalFields(): array
    {
        return array(
            Labels::getLabel('LBL_Title', $this->adminLangId),
            Labels::getLabel('LBL_Options_(If_Any)', $this->adminLangId),
            Labels::getLabel('LBL_Brand', $this->adminLangId),
            Labels::getLabel('LBL_Shop_Name', $this->adminLangId),
            Labels::getLabel('LBL_Rental_Price', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Rental_Orders', $this->adminLangId),
            Labels::getLabel('LBL_Rented_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Total(A)', $this->adminLangId),
            Labels::getLabel('LBL_Shipping(B)', $this->adminLangId),
            Labels::getLabel('LBL_Tax(C)', $this->adminLangId),
            Labels::getLabel('LBL_Security(D)', $this->adminLangId),
            Labels::getLabel('LBL_Total(A+B+C+D)', $this->adminLangId),
            Labels::getLabel('LBL_Commission', $this->adminLangId)
        );
    }

    private function getSaleFields(): array
    {
        return array(
            Labels::getLabel('LBL_Title', $this->adminLangId),
            Labels::getLabel('LBL_Options_(If_Any)', $this->adminLangId),
            Labels::getLabel('LBL_Brand', $this->adminLangId),
            Labels::getLabel('LBL_Shop_Name', $this->adminLangId),
            Labels::getLabel('LBL_Unit_Price', $this->adminLangId),
            Labels::getLabel('LBL_No._Of_Sold_Orders', $this->adminLangId),
            Labels::getLabel('LBL_Sold_QTY', $this->adminLangId),
            Labels::getLabel('LBL_Total(A)', $this->adminLangId),
            Labels::getLabel('LBL_Shipping(B)', $this->adminLangId),
            Labels::getLabel('LBL_Tax(C)', $this->adminLangId),
            Labels::getLabel('LBL_Total(A+B+C)', $this->adminLangId),
            Labels::getLabel('LBL_Commission', $this->adminLangId)
        );
    }

}
