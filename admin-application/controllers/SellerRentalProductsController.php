<?php

class SellerRentalProductsController extends AdminBaseController
{

    private $canView;
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->objPrivilege->canViewSellerProducts();
        $this->canView = $this->objPrivilege->canViewSellerProducts($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSellerProducts($this->admin_id, true);
    }

    public function searchDurationDiscounts()
    {
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new SellerProductDurationDiscountSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'produr_selprod_id = selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'INNER JOIN', 'produr_selprod_id = sprodata_selprod_id', 'spd');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->adminLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        
        $srch->addMultipleFields(['dd.*', 'selprod_id', 'selprod_user_id', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'sprodata_duration_type', 'credential_username as seller_name']);

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);
        $this->set('arrListing', $arrListing);
        $this->set('canEdit', $this->canEdit);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pageSize);
        $this->set('durationTypes', ProductRental::durationTypeArr($this->adminLangId));
        $this->_template->render(false, false);
    }

    public function sellerProductDurationDiscounts()
    {
        $srchFrm = $this->getDurationDiscountSearchForm();
        $this->set('frmSearch', $srchFrm);
        $this->set('selprod_id', 0);
        $this->set("canEdit", $this->canView);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function sellerProductDurationDiscountForm(int $selprodId = 0, int $durDiscountId = 0)
    {
        $frm = $this->getSellerProductDurationDiscountForm($this->adminLangId, $selprodId);
        $durationDiscountRow = array();
        if ($durDiscountId) {
            $durationDiscountRow = SellerProductDurationDiscount::getAttributesById($durDiscountId);
            if (!$durationDiscountRow) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
        }
        $durationDiscountRow['produr_selprod_id'] = $selprodId;
        $frm->fill($durationDiscountRow);
        $this->set('frm', $frm);
        $this->set('selprod_id', $selprodId);
        $this->_template->render(false, false);
    }

    public function setUpSellerProductDurationDiscount()
    {
        $selprodId = FatApp::getPostedData('produr_selprod_id', FatUtility::VAR_INT, 0);
        if (!$selprodId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $proDurDiscountId = FatApp::getPostedData('produr_id', FatUtility::VAR_INT, 0);
        $frm = $this->getSellerProductDurationDiscountForm($this->adminLangId, $selprodId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()), $this->adminLangId);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->updateSelProdDurationDiscount($selprodId, $proDurDiscountId, $post['produr_rental_duration'], $post['produr_discount_percent']);
        $this->set('msg', Labels::getLabel('LBL_Duration_Discount_Setup_Successful', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSellerProductDurationDiscount()
    {
        $proDurDiscountId = FatApp::getPostedData('produr_id', FatUtility::VAR_INT, 0);
        $discountRow = SellerProductDurationDiscount::getAttributesById($proDurDiscountId);

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProductDurationDiscount::DB_TBL, array('smt' => 'produr_id = ? AND produr_selprod_id = ?', 'vals' => array($proDurDiscountId, $discountRow['produr_selprod_id'])))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->adminLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $discountRow['produr_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Duration_Discount_Record_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productRentalUnavailableDates()
    {
        $srchFrm = $this->getDurationDiscountSearchForm();
        $this->set("canEdit", $this->canEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selprod_id", 0);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('css/select2.min.css'));
        $this->_template->render();
    }

    public function searchUnavailbleDates()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $keyword = trim(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new SellerRentalProductUnavailableDateSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'pu_selprod_id = selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->adminLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->adminLangId, 'p_l');
        $srch->addMultipleFields(['spud.*', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title']);

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);

        $this->set('arrListing', $arrListing);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pageSize);
        $this->_template->render(false, false);
    }

    public function productRentalUnavailableDatesForm(int $selprodId = 0, int $prodUnavailDateId = 0)
    {
        $unavailableDatesForm = $this->getRentalProductUnavailableDatesForm($this->adminLangId);
        $datesData = array();
        if ($prodUnavailDateId) {
            $datesData = SellerRentalProductUnavailableDate::getAttributesById($prodUnavailDateId);
            if (!$datesData) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            }
        }
        $datesData['pu_selprod_id'] = $selprodId;
        $unavailableDatesForm->fill($datesData);

        $this->set('frm', $unavailableDatesForm);
        $this->set('selprod_id', $selprodId);
        $this->_template->render(false, false);
    }

    public function setUpRentalUnavailableDates()
    {
        $selprodId = FatApp::getPostedData('pu_selprod_id', FatUtility::VAR_INT, 0);
        $prodUnavailDateId = FatApp::getPostedData('pu_id', FatUtility::VAR_INT, 0);
        $puId = $prodUnavailDateId;
        if (!$selprodId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($puId > 0) {
            $datesRow = SellerRentalProductUnavailableDate::getAttributesById($puId);
        }

        $frm = $this->getRentalProductUnavailableDatesForm($this->adminLangId, $selprodId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['pu_id']);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()), $this->adminLangId);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        $srch = ProductRental::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprodId);
        $srch->addFld('sprodata_rental_stock');
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (empty($prodRentalData)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        if (!SellerRentalProductUnavailableDate::isValidDateRange($post['pu_start_date'], $post['pu_end_date'], $selprodId, $prodUnavailDateId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Quanties_already_added_for_this_date_range', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        
        if ($post['pu_quantity'] > $prodRentalData['sprodata_rental_stock']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Quantity_Must_be_less_then_Product_Stock', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        

        $record = new SellerRentalProductUnavailableDate($prodUnavailDateId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($puId > 0) {
            $renProObj = new ProductRental($datesRow['pu_selprod_id']);
            if (!$renProObj->updateRentalProductStock($datesRow['pu_quantity'], $datesRow['pu_start_date'], $datesRow['pu_end_date'], true)) {
                Message::addErrorMessage($renProObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            unset($renProObj);
        }

        $renProObj = new ProductRental($post['pu_selprod_id']);
        if (!$renProObj->updateRentalProductStock($post['pu_quantity'], $post['pu_start_date'], $post['pu_end_date'])) {
            Message::addErrorMessage($renProObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Unavailable_Dates_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRentalUnavailableDates()
    {
        $prodUnavailDateId = FatApp::getPostedData('pu_id', FatUtility::VAR_INT, 0);
        $selprodId = FatApp::getPostedData('selprodId', FatUtility::VAR_INT, 0);
        $datesRow = SellerRentalProductUnavailableDate::getAttributesById($prodUnavailDateId);

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerRentalProductUnavailableDate::DB_TBL, array('smt' => 'pu_id = ? AND pu_selprod_id = ?', 'vals' => array($prodUnavailDateId, $datesRow['pu_selprod_id'])))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $renProObj = new ProductRental($datesRow['pu_selprod_id']);
        if (!$renProObj->updateRentalProductStock($datesRow['pu_quantity'], $datesRow['pu_start_date'], $datesRow['pu_end_date'], true)) {
            Message::addErrorMessage($renProObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $datesRow['pu_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Unavailable_Dates_Deleted', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getSellerProductDurationDiscountForm(int $langId, int $selprodId = 0)
    {
        $frm = new Form('frmSellerProductDurationDiscount');
        $frm->addHiddenField('', 'produr_selprod_id', 0);
        $frm->addHiddenField('', 'produr_id', 0);
        if (0 >= $selprodId) {
            $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $langId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $langId)));
            $prodName->requirements()->setRequired();
        }

        $durFld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Duration", $langId), 'produr_rental_duration');
        $durFld->requirements()->setPositive();
        $durFld->requirements()->setRange(1, 365);

        $discountFld = $frm->addFloatField(Labels::getLabel("LBL_Discount_in_(%)", $langId), "produr_discount_percent");
        $discountFld->requirements()->setPositive();
        $discountFld->requirements()->setRange(1, 100);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function updateSelProdDurationDiscount(int $selprodId, int $produrId, int $produrRentalDuration, float $percentage)
    {
        $srch = ProductRental::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprodId);
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (empty($prodRentalData)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->adminLangId));
        }

        if ($produrRentalDuration < $prodRentalData['sprodata_minimum_rental_duration']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Duration_cannot_be_less_than_the_Minimum_Rent_Duration', $this->adminLangId) . ': ' . $prodRentalData['sprodata_minimum_rental_duration']);
        }

        if ($percentage > 100 || 1 > $percentage) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Percentage', $this->adminLangId));
        }

        /* Check if duration discount for same quantity already exists [ */
        $tblRecord = new TableRecord(SellerProductDurationDiscount::DB_TBL);
        if ($tblRecord->loadFromDb(array('smt' => 'produr_selprod_id = ? AND produr_rental_duration = ? ', 'vals' => array($selprodId, $produrRentalDuration)))) {
            $durDiscountRow = $tblRecord->getFlds();
            if ($durDiscountRow['produr_id'] != $produrId) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Duration_discount_for_this_duration_already_added', $this->adminLangId));
            }
        }
        /* ] */
        $dataToSave = array(
            'produr_selprod_id' => $selprodId,
            'produr_rental_duration' => $produrRentalDuration,
            'produr_discount_percent' => $percentage,
        );

        $record = new SellerProductDurationDiscount($produrId);
        $record->assignValues($dataToSave);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        return true;
    }

    private function getRentalProductUnavailableDatesForm(int $langId, int $selprodId = 0)
    {
        $frm = new Form('frmSellerProductRentalUnavailablDates');
        $frm->addHiddenField('', 'pu_selprod_id', 0);
        $frm->addHiddenField('', 'pu_id', 0);
        if (1 > $selprodId) {
            $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $langId), 'product_name', [], '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $langId)));
            $prodName->requirements()->setRequired();
        }

        $startDateFld = $frm->addDateField(Labels::getLabel('LBL_Start_Date', $this->adminLangId), 'pu_start_date', '', array('readonly' => 'readonly'));
        $startDateFld->requirements()->setRequired();

        $endDateFld = $frm->addDateField(Labels::getLabel('LBL_End_Date', $this->adminLangId), 'pu_end_date', '', array('readonly' => 'readonly'));
        $endDateFld->requirements()->setRequired();
        $endDateFld->requirements()->setCompareWith('pu_start_date', 'ge', '');

        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Unavailable_Quantity", $langId), 'pu_quantity');
        $qtyFld->requirements()->setPositive();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function getDurationDiscountSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $this->adminLangId)));
        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->adminLangId), array('onclick' => 'clearSearch();'));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

}
