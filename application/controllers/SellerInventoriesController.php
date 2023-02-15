<?php

class SellerInventoriesController extends SellerBaseController
{

    use SellerProducts;

    public function __construct($action)
    {
        $this->method = $action;
        parent::__construct($action);
    }

    public function productSaleDetailsForm(int $productId, int $selprodId = 0, int $userId = 0)
    {
        /* NEED TO UPDATE FUNCTION NAME */
        $post = FatApp::getPostedData();
        if ($selprodId > 1) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId);
            if (!$this->checkProductOwner($sellerProductRow['selprod_user_id'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                //FatApp::redirectUser($_SESSION['referer_page_url']);
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
        }

        $replaceCode = $productId . '_';
        $productRow = Product::getAttributesById($productId, array('product_type'));
        if (!$productRow) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        if ($userId == 0) {
            $userId = UserAuthentication::getLoggedUserId();
        }
        $srch = SellerProduct::searchSellerProducts($this->siteLangId, $userId);
        $srch->addCondition('selprod_product_id', '=', $productId);
        if ($selprodId > 0) {
            $srch->addCondition('selprod_id', '=', $selprodId);
        } else {
            /* $srch->addCondition('sprodata.sprodata_is_rental_data_updated', '=', 0); */
        }

        $srch->joinTable(
                SellerProductSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'ps.' . SellerProductSpecifics::DB_TBL_PREFIX . 'selprod_id = sp.selprod_id   AND ps.selprod_specific_type =' . applicationConstants::PRODUCT_FOR_SALE, 'ps'
        );

        /* $srch->addMultipleFields(array('selprod_id', 'IFNULL(REPLACE(selprod_code, "' . $replaceCode . '", ""), 0) as selprod_code')); */
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $selprodListing = $db->fetchAll($rs, 'selprod_id');

        $selprodIds = [];
        if (!empty($selprodListing)) {
            foreach ($selprodListing as $key => $selprod) {
                $selprod['selprod_code'] = str_replace($replaceCode, "", $selprod['selprod_code']);
            }
            $selprodIds = array_column($selprodListing, 'selprod_id');
        }

        $productOptions = Product::getProductOptions($productId, $this->siteLangId, true);
        $optionCombinations = [];
        if (!empty($productOptions)) {
            $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        }

        $productRentalForm = $this->getProductSaleForm($selprodListing, $userId);
        if ($selprodId > 0) {
            $generalData = isset($selprodListing[$selprodId]) ? $selprodListing[$selprodId] : [];
            if ((isset($generalData['selprod_cancellation_age']) && $generalData['selprod_cancellation_age'] != '') || (isset($generalData['selprod_return_age']) && $generalData['selprod_return_age'] != '')) {
                $generalData['use_shop_policy'] = 0;
            }
            unset($generalData['selprod_price']);
            unset($generalData['selprod_stock']);
            unset($generalData['selprod_sku']);
            $productRentalForm->fill($generalData);
        }

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);

        $this->set('frm', $productRentalForm);
        $this->set('activeTab', 'RENT');
        $this->set('product_id', $productId);
        $this->set('selprod_id', $selprodId);
        $this->set('is_rent', SellerProduct::isProductRental($selprodId));
        $this->set('is_sale', SellerProduct::isProductSale($selprodId));
        $this->set('product_type', $productRow['product_type']);
        $this->set('selprodListing', $selprodListing);
        $this->set('otherLanguages', $languages);
        $this->set('siteDefaultLangId', $siteDefaultLangId);
        $this->set('userId', $userId);
        $this->set('optionCombinations', $optionCombinations);
        $this->_template->render(false, false);
    }

    public function setupProdSaleDetails()
    {
        $post = FatApp::getPostedData();
        /* $selprodIds = FatUtility::int($post['selprod_id']); */
        $selprodIds = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, []);

        if (empty($selprodIds) || empty($post)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* $costPrices = $post['selprod_cost']; */
        $sellingPrices = $post['selprod_price']; 
        $saleStockArr = $post['selprod_stock'];
        $skuArr = $post['selprod_sku'];
        $sellingPrices = array_filter($sellingPrices, 'is_numeric');
        $saleStockArr = array_filter($saleStockArr);
        if (1 > count($sellingPrices)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Minimum_One_Inventory_is_required', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        /* if (!empty($sellingPrices)) {
            $productId = SellerProduct::getAttributesById($selprodIds[0], 'selprod_product_id');
            $minPrice = Product::getAttributesById($productId, 'product_min_selling_price');
            foreach($sellingPrices as $price) {
                if ($minPrice > $price) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Selling_Price_must_be_greater_then_minimum_selling_Price', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        } */
        
        if (!empty($saleStockArr)) {
            foreach($saleStockArr as $stock) {
                if ($post['selprod_min_order_qty'] > $stock) {
                    Message::addErrorMessage(Labels::getLabel('MSG_Available_Quantity_must_be_greater_then_minimum_purchase_quantity', $this->siteLangId));
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
        }
        
        foreach ($selprodIds as $selprodId) {
            if (!isset($sellingPrices[$selprodId])) {
                continue;
            }
        
            $dataToSave = array(
                /* 'selprod_cost' => (isset($costPrices[$selprodId])) ? $costPrices[$selprodId] : 0, */
                'selprod_price' => (isset($sellingPrices[$selprodId])) ? $sellingPrices[$selprodId] : 0,
                'selprod_stock' => (isset($saleStockArr[$selprodId])) ? $saleStockArr[$selprodId] : 0,
                'selprod_sku' => (isset($skuArr[$selprodId])) ? $skuArr[$selprodId] : 0,
                'selprod_track_inventory' => (isset($post['selprod_track_inventory'])) ? $post['selprod_track_inventory'] : 0,
                'selprod_threshold_stock_level' => $post['selprod_threshold_stock_level'],
                'selprod_min_order_qty' => $post['selprod_min_order_qty'],
                'selprod_condition' => $post['selprod_condition'],
                'selprod_available_from' => $post['selprod_available_from'],
                'selprod_active' => $post['selprod_active'],
                'selprod_subtract_stock' => (isset($post['selprod_subtract_stock'])) ? $post['selprod_subtract_stock'] : 0,
            );

            $sellerProdObj = new SellerProduct($selprodId);
            $sellerProdObj->assignValues($dataToSave);
            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            $selprodId = $sellerProdObj->getMainTableRecordId();
            $prodRentalData = [
                            'sprodata_is_for_sell' => applicationConstants::YES, 
                            'sprodata_selprod_id' =>  $selprodId
                        ];
            $record = new ProductRental($selprodId);
            if (!$record->addUpdateSelProData($prodRentalData)) {
                Message::addErrorMessage($record->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
            
            $selProdSpecificsObj = new SellerProductSpecifics($selprodId);
            $useShopPolicy = FatApp::getPostedData('use_shop_policy', FatUtility::VAR_INT, 0);
            if (0 < $useShopPolicy) {
                $whr = [
                    'smt' => 'sps_selprod_id = ? AND selprod_specific_type = ?',
                    'vals' => [$selprodId, applicationConstants::PRODUCT_FOR_SALE]
                ];
                if (!FatApp::getDb()->deleteRecords(SellerProductSpecifics::DB_TBL, $whr)) {
                    FatUtility::dieJsonError(FatApp::getDb()->getError());
                }
            } else {
                $post['sps_selprod_id'] = $selprodId;
                $post['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_SALE;
                $selProdSpecificsObj->assignValues($post);
                $data = $selProdSpecificsObj->getFlds();
                if (!$selProdSpecificsObj->addNew(array(), $data)) {
                    FatUtility::dieJsonError($selProdSpecificsObj->getError());
                }
            }
        }
        
        Product::updateMinPrices(SellerProduct::getAttributesById($selprodId, 'selprod_product_id'));
        $this->updateAllCategoryConfig();
        $this->set('already_updated', true);
        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function translatedProductRenatlData()
    {
        $rentalTerm = FatApp::getPostedData('sprodata_rental_terms', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data = array(
            'selprod_rental_terms' => $rentalTerm,
        );
        $product = new Product();
        $translatedData = $product->getTranslatedProductData($data, $toLangId);
        if (!$translatedData) {
            Message::addErrorMessage($product->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('sprodata_rental_terms', $translatedData[$toLangId]['selprod_rental_terms']);
        $this->set('msg', Labels::getLabel('LBL_Product_Rental_Data_Translated_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchDurationDiscounts()
    {
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new SellerProductDurationDiscountSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'produr_selprod_id = selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'INNER JOIN', 'produr_selprod_id = sprodata_selprod_id', 'spd');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->siteLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->addMultipleFields(['dd.*', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'sprodata_duration_type']);

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);
        $this->set('arrListing', $arrListing);
        $this->set('canEdit', $this->userPrivilege->canEditDurationDiscount(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', $pageSize);
        $this->set('durationTypes', ProductRental::durationTypeArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function sellerProductDurationDiscounts()
    {
        $this->userPrivilege->canViewDurationDiscount(UserAuthentication::getLoggedUserId());
        $srchFrm = $this->getDurationDiscountSearchForm();
        $this->set('frmSearch', $srchFrm);
        $this->set('selprod_id', 0);
        $this->set("canEdit", $this->userPrivilege->canEditDurationDiscount(UserAuthentication::getLoggedUserId(), true));
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function sellerProductDurationDiscountForm(int $selprodId = 0, int $durDiscountId = 0)
    {
        if ($selprodId > 0) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId, array('selprod_id', 'selprod_user_id', 'selprod_product_id'));
            if ((!$this->checkProductOwner($sellerProductRow['selprod_user_id'])) || $selprodId != $sellerProductRow['selprod_id']) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }

        $frm = $this->getSellerProductDurationDiscountForm($this->siteLangId, $selprodId);
        $durationDiscountRow = array();
        $durationLabel = "";
        if ($durDiscountId) {
            $durationDiscountRow = SellerProductDurationDiscount::getAttributesById($durDiscountId);
            if (!$durationDiscountRow) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
        }
        
        $durationDiscountRow['produr_selprod_id'] = $selprodId;
        $frm->fill($durationDiscountRow);

        $this->set('frm', $frm);
        $this->set('durationLabel', $durationLabel);
        $this->set('selprod_id', $selprodId);
        $this->_template->render(false, false);
    }

    public function setUpSellerProductDurationDiscount()
    {
        $this->userPrivilege->canEditDurationDiscount(UserAuthentication::getLoggedUserId());
        $selprodId = FatApp::getPostedData('produr_selprod_id', FatUtility::VAR_INT, 0);
        if (!$selprodId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $proDurDiscountId = FatApp::getPostedData('produr_id', FatUtility::VAR_INT, 0);
        $frm = $this->getSellerProductDurationDiscountForm($this->siteLangId, $selprodId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->updateSelProdDurationDiscount($selprodId, $proDurDiscountId, $post['produr_rental_duration'], $post['produr_discount_percent']);
        $this->set('msg', Labels::getLabel('LBL_Duration_Discount_Setup_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSellerProductDurationDiscount()
    {
        $this->userPrivilege->canEditDurationDiscount(UserAuthentication::getLoggedUserId());
        $proDurDiscountId = FatApp::getPostedData('produr_id', FatUtility::VAR_INT, 0);
        $discountRow = SellerProductDurationDiscount::getAttributesById($proDurDiscountId);
        $sellerProductRow = SellerProduct::getAttributesById($discountRow['produr_selprod_id'], array('selprod_user_id'), false);
        if (!$discountRow || !$sellerProductRow || (!$this->checkProductOwner($sellerProductRow['selprod_user_id']))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerProductDurationDiscount::DB_TBL, array('smt' => 'produr_id = ? AND produr_selprod_id = ?', 'vals' => array($proDurDiscountId, $discountRow['produr_selprod_id'])))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatApp::redirectUser($_SESSION['referer_page_url']);
        }

        $this->set('selprod_id', $discountRow['produr_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Duration_Discount_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productRentalUnavailableDates()
    {
        $this->userPrivilege->canViewUnavailbleDates(UserAuthentication::getLoggedUserId());
        $srchFrm = $this->getDurationDiscountSearchForm();
        
        $this->set("frmSearch", $srchFrm);
        $this->set("selprod_id", 0);
        $this->set('canEdit', $this->userPrivilege->canEditUnavailbleDates(UserAuthentication::getLoggedUserId(), true));
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchUnavailbleDates()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = new SellerRentalProductUnavailableDateSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'pu_selprod_id = selprod_id', 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . SellerProduct::tblFld('id') . ' and sp_l.' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $this->siteLangId, 'sp_l');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->addMultipleFields(['spud.*', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title']);

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $rs = $srch->getResultSet();
        $arrListing = FatApp::getDb()->fetchAll($rs);

        $this->set('canEdit', $this->userPrivilege->canEditUnavailbleDates(UserAuthentication::getLoggedUserId(), true));
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
        if ($selprodId > 0) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId, array('selprod_id', 'selprod_user_id', 'selprod_product_id'));
            if ((!$this->checkProductOwner($sellerProductRow['selprod_user_id'])) || $selprodId != $sellerProductRow['selprod_id']) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }

        $unavailableDatesForm = $this->getRentalProductUnavailableDatesForm($this->siteLangId);
        $datesData = array();
        if ($prodUnavailDateId) {
            $datesData = SellerRentalProductUnavailableDate::getAttributesById($prodUnavailDateId);
            if (!$datesData) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
            $datesData['dates'] = $datesData['pu_start_date']. ' to '. $datesData['pu_end_date'];
        }
        $datesData['pu_selprod_id'] = $selprodId;
        $unavailableDatesForm->fill($datesData);

        $this->set('frm', $unavailableDatesForm);
        $this->set('selprod_id', $selprodId);
        $this->set('prodUnavailDateId', $prodUnavailDateId);
        $this->_template->render(false, false);
    }

    public function setUpRentalUnavailableDates()
    {
        $this->userPrivilege->canEditUnavailbleDates(UserAuthentication::getLoggedUserId());
        $selprodId = FatApp::getPostedData('pu_selprod_id', FatUtility::VAR_INT, 0);
        $prodUnavailDateId = FatApp::getPostedData('pu_id', FatUtility::VAR_INT, 0);
        $puId = $prodUnavailDateId;
        if (!$selprodId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if ($puId > 0) {
            $datesRow = SellerRentalProductUnavailableDate::getAttributesById($puId);
        }
        
        $srch = ProductRental::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprodId);
        $srch->addFld('sprodata_rental_stock');
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (empty($prodRentalData)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        
        
        
        $frm = $this->getRentalProductUnavailableDatesForm($this->siteLangId, $selprodId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['pu_id']);
        
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()), $this->siteLangId);
            FatUtility::dieWithError(Message::getHtml());
        }
        
        if (!SellerRentalProductUnavailableDate::isValidDateRange($post['pu_start_date'], $post['pu_end_date'], $selprodId, $prodUnavailDateId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Quanties_already_added_for_this_date_range', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        
        if ($post['pu_quantity'] > $prodRentalData['sprodata_rental_stock']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Quantity_Must_be_less_then_Product_Stock', $this->siteLangId));
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

        $this->set('msg', Labels::getLabel('LBL_Unavailable_Dates_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRentalUnavailableDates()
    {
        $this->userPrivilege->canEditUnavailbleDates(UserAuthentication::getLoggedUserId());
        $prodUnavailDateId = FatApp::getPostedData('pu_id', FatUtility::VAR_INT, 0);
        $selprodId = FatApp::getPostedData('selprodId', FatUtility::VAR_INT, 0);
        $datesRow = SellerRentalProductUnavailableDate::getAttributesById($prodUnavailDateId);
        $sellerProductRow = SellerProduct::getAttributesById($selprodId, array('selprod_user_id'), false);
        if (!$datesRow || !$sellerProductRow || (!$this->checkProductOwner($sellerProductRow['selprod_user_id']))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        if (!$db->deleteRecords(SellerRentalProductUnavailableDate::DB_TBL, array('smt' => 'pu_id = ? AND pu_selprod_id = ?', 'vals' => array($prodUnavailDateId, $datesRow['pu_selprod_id'])))) {
            Message::addErrorMessage(Labels::getLabel("LBL_" . $db->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $renProObj = new ProductRental($datesRow['pu_selprod_id']);
        if (!$renProObj->updateRentalProductStock($datesRow['pu_quantity'], $datesRow['pu_start_date'], $datesRow['pu_end_date'], true)) {
            Message::addErrorMessage($renProObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('selprod_id', $datesRow['pu_selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Unavailable_Dates_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getProductSaleForm(array $selprodListing, int $sellerId, int $selprodId = 0)
    {
        $langId = $this->siteLangId;
        $rentalTypeArr = applicationConstants::rentalTypeArr($langId);
        $frm = new Form('frmProductRentalDetails');
        /* [ GENERAL FIELDS */
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Subtract_Stock_on_Purchase', $langId), 'selprod_subtract_stock', applicationConstants::YES, array(), false, 0);
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Track_Product_Inventory', $langId), 'selprod_track_inventory', applicationConstants::YES, array(), false, 0);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Alert_Stock_Level', $langId), 'selprod_threshold_stock_level', '');
        $fld->requirements()->setInt();
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Purchase_Quantity', $langId), 'selprod_min_order_qty', '');
        $fld->requirements()->setRange(1, SellerProduct::MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $langId), 'selprod_condition', Product::getConditionArr($langId), '', array(), Labels::getLabel('LBL_Select_Condition', $langId));
        $fld->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $langId), 'selprod_available_from', '', array('readonly' => 'readonly', 'class' => 'field--calender'))->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Publish', $langId), 'selprod_active', applicationConstants::getYesNoArr($langId), applicationConstants::YES, array(), '');

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_POLICY', $langId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], true, 0);
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);
        /* ] */

        if (!empty($selprodListing)) {
            $shopId = Shop::getAttributesByUserId($sellerId, 'shop_id');
            $isRequired = (count($selprodListing) > 1) ? false : true;
            
            foreach ($selprodListing as $selprodId => $selprod) {
                $frm->addHiddenField('', 'selprod_id[' . $selprodId . ']', $selprodId);
                /* $fld = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $langId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'selprod_cost[' . $selprodId . ']', $selprod['selprod_cost']);
                  $fld->requirements()->setPositive();
                  $fld->requirements()->setRange(0.001, 9999999); */

                $fld = $frm->addFloatField(Labels::getLabel('LBL_Selling_Price', $langId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'selprod_price[' . $selprodId . ']', ($isRequired) ? $selprod['selprod_price'] : '' );
                $fld->requirements()->setPositive();
                $fld->requirements()->setRequired($isRequired);
                $fld->requirements()->setRange($selprod['product_min_selling_price'], 9999999);

                $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $langId), 'selprod_stock[' . $selprodId . ']', ($isRequired) ? $selprod['selprod_stock'] : '');
                $fld->requirements()->setPositive();
                $fld->requirements()->setRange(0, 9999999);
                $fld->requirements()->setRequired($isRequired);
                if ($isRequired) {
                    $fld->requirements()->setCompareWith('selprod_min_order_qty', 'ge', '');
                }
                
                $fld = $frm->addTextBox(Labels::getLabel('LBL_SKU', $langId), 'selprod_sku[' . $selprodId . ']', $selprod['selprod_sku']);
            }

            /* $frm->addHiddenField('', 'sprodata_is_rental_data_updated', $selprod['sprodata_is_rental_data_updated']); */
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $langId));
        return $frm;
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

        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Duration_(_Hours_/_Days_/_Weeks_/_Months_)", $langId), 'produr_rental_duration');
        $qtyFld->requirements()->setPositive();

        $discountFld = $frm->addFloatField(Labels::getLabel("LBL_Discount_in_(%)", $langId), "produr_discount_percent");
        $discountFld->requirements()->setPositive();
        $discountFld->requirements()->setRange(1, 100);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function updateSelProdDurationDiscount($selprodId, $produrId, $produrRentalDuration, $percentage)
    {
        $sellerProductRow = SellerProduct::getAttributesById($selprodId, array('selprod_user_id', 'selprod_stock', 'selprod_min_order_qty'), false);

        if (!$this->checkProductOwner($sellerProductRow['selprod_user_id'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $srch = ProductRental::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprodId);
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (empty($prodRentalData)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if ($produrRentalDuration < $prodRentalData['sprodata_minimum_rental_duration']) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Duration_cannot_be_less_than_the_Minimum_Rent_Duration', $this->siteLangId) . ': ' . $prodRentalData['sprodata_minimum_rental_duration']);
        }

        if ($percentage > 100 || 1 > $percentage) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Percentage', $this->siteLangId));
        }

        /* Check if duration discount for same quantity already exists [ */
        $tblRecord = new TableRecord(SellerProductDurationDiscount::DB_TBL);
        if ($tblRecord->loadFromDb(array('smt' => 'produr_selprod_id = ? AND produr_rental_duration = ? ', 'vals' => array($selprodId, $produrRentalDuration)))) {
            $durDiscountRow = $tblRecord->getFlds();
            if ($durDiscountRow['produr_id'] != $produrId) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Duration_discount_for_this_duration_already_added', $this->siteLangId));
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
        } else {
            return true;
        }
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
        
        $startDateFld = $frm->addTextBox(Labels::getLabel('LBL_Dates', $this->siteLangId), 'dates', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        

        $frm->addHiddenField(Labels::getLabel('LBL_Start_Date', $this->siteLangId), 'pu_start_date', '', array('readonly' => 'readonly'));
        $frm->addHiddenField(Labels::getLabel('LBL_End_Date', $this->siteLangId), 'pu_end_date', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        

        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Unavailable_Quantity", $langId), 'pu_quantity');
        $qtyFld->requirements()->setPositive();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    private function getDurationDiscountSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $this->siteLangId)));

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    /* ] */
    /* [ MEMBERSHIP TAB UPDATES */

    public function productMembershipDetailsForm(int $productId, int $selprodId = 0, int $userId = 0)
    { /* NEED TO UPDATE FUNCTION NAME */
        $post = FatApp::getPostedData();
        if ($selprodId > 1) {
            $sellerProductRow = SellerProduct::getAttributesById($selprodId, ['selprod_enable_rfq', 'selprod_cost', 'selprod_id', 'spd.*', 'selprod_user_id '], true, true, true, applicationConstants::PRODUCT_FOR_RENT, true);
            if (!$this->checkProductOwner($sellerProductRow['selprod_user_id'])) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                //FatApp::redirectUser($_SESSION['referer_page_url']);
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            }
        }

        $replaceCode = $productId . '_';
        $productRow = Product::getAttributesById($productId, array('product_type'));
        if (!$productRow) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        if ($userId == 0) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $memberShipPlans = [];
        $frm = $this->getProductMembershipForm($productId, $selprodId, $userId);
        $memberShipPlans = [];
        if ($selprodId > 0) {
            $memberShipPlans = SellerProduct::getMembershipPlanBySelprod($selprodId);
            $frm->fill($sellerProductRow);
        }

        $productOptions = Product::getProductOptions($productId, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        $availableOptions = CommonHelper::validOptionsForSeller($productId, $optionCombinations, $this->userParentId, $this->siteLangId);

        $this->set('frm', $frm);
        $this->set('membershipPlans', $memberShipPlans);
        $this->set('product_id', $productId);
        $this->set('selprod_id', $selprodId);
        $this->set('userId', $userId);
        $this->set('availableOptions', $availableOptions);
        $this->_template->render(false, false);
    }

    private function getProductMembershipForm(int $productId, $selprodId, int $sellerId = 0)
    {
        $langId = $this->siteLangId;
        $frm = new Form('frmProductMembershipDetails');
        /* [ GENERAL FIELDS */
        $minQtyfld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Rental_Quantity', $langId), 'sprodata_minimum_rental_quantity', '');
        $minQtyfld->requirements()->setRange(1, SellerProduct::MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY); 

        $frm->addCheckBox(Labels::getLabel('LBL_ENABLE_REQUEST_FOR_QUOTE', $langId), 'selprod_enable_rfq', 1, array(), false, 0);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $langId), 'sprodata_rental_condition', Product::getConditionArr($langId), '', array(), Labels::getLabel('LBL_Select_Condition', $langId));
        $fld->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $langId), 'sprodata_rental_available_from', '', array('readonly' => 'readonly', 'class' => 'field--calender'))->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Publish', $langId), 'sprodata_rental_active', applicationConstants::getYesNoArr($langId), applicationConstants::YES, array(), '');

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_POLICY', $langId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], true, 0);
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $langId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $langId) . ' </small>';
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $langId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $langId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);
        /* ] */
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Membership_Plan', $langId), 'membership_plan', '');

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Rental_Duration', $this->siteLangId), 'sprodata_minimum_rental_duration');
        $fld->requirements()->setPositive();
        $fld->requirements()->setRange(1, 99999);
        $durationTypes = ProductRental::durationTypeArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Rental_Duration_Type', $this->siteLangId), 'sprodata_duration_type', $durationTypes, '', array())->requirements()->setRequired();
        $productOptions = Product::getProductOptions($productId, $this->siteLangId, true);

        if (!empty($productOptions)) {
            $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
            $validOptionsFoeSeller = CommonHelper::validOptionsForSeller($productId, $optionCombinations, $this->userParentId, $this->siteLangId);
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Varient', $this->siteLangId), 'varient_id', $validOptionsFoeSeller, '', array(), Labels::getLabel('LBL_Select_Varient', $this->siteLangId));
            $fld->requirement->setRequired(true);
        }
        $frm->addHiddenField('', 'selprod_id', $selprodId);
        $frm->addHiddenField('', 'selprod_product_id', $productId);
        $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Original_Price', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'selprod_cost');
        $costPrice->requirements()->setPositive();

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel("LBL_Save_Changes", $langId));
        return $frm;
    }

    public function setupProductMembershipDetails()
    {
        $post = FatApp::getPostedData();
        $selprodId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $productId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        $membershipPlansArr = json_decode(FatApp::getPostedData('membership_plan'), true);
        if (empty($post) || empty($membershipPlansArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_or_membership_plan_is_required', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        unset($post['selprod_id']);
        $membershipPlanIds = array_column($membershipPlansArr, 'id');

        $sellerProdObj = new SellerProduct($selprodId);
        if (0 > $selprodId) {
            $post['selprod_code'] = $productId . '_';
            $post['selprod_active'] = applicationConstants::YES;
        }
        $useShopPolicy = (isset($post['use_shop_policy'])) ? $post['use_shop_policy'] : 0;
        $sellerProdObj->assignValues($post);
        if (!$sellerProdObj->save()) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $selprod_id = $sellerProdObj->getMainTableRecordId();

        if (!$sellerProdObj->updateMembershipDetails($membershipPlanIds, $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $prodRentalData = array();
        $prodRentalData['sprodata_is_for_sell'] = 1;
        $prodRentalData['sprodata_is_for_rent'] = 1;
        $prodRentalData['sprodata_selprod_id'] = $selprod_id;
        $prodRentalData['sprodata_minimum_rental_quantity'] = $post['sprodata_minimum_rental_quantity'];
        $prodRentalData['sprodata_minimum_rental_duration'] = $post['sprodata_minimum_rental_duration'];
        $prodRentalData['sprodata_duration_type'] = $post['sprodata_duration_type'];
        $prodRentalData['sprodata_rental_active'] = $post['sprodata_rental_active'];
        $prodRentalData['sprodata_rental_available_from'] = $post['sprodata_rental_available_from'];
        $prodRentalData['sprodata_rental_condition'] = $post['sprodata_rental_condition'];
        $record = new ProductRental();
        /* $record->assignValues($post); */
        if (!$record->addUpdateSelProData($prodRentalData)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
        if (0 < $useShopPolicy) {
            $whr = [
                'smt' => 'sps_selprod_id = ? AND selprod_specific_type = ?',
                'vals' => [$selprod_id, applicationConstants::PRODUCT_FOR_RENT]
            ];
            if (!FatApp::getDb()->deleteRecords(SellerProductSpecifics::DB_TBL, $whr)) {
                FatUtility::dieJsonError(FatApp::getDb()->getError());
            }
        } else {
            $post['sps_selprod_id'] = $selprod_id;
            $post['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_RENT;
            $selProdSpecificsObj->assignValues($post);
            $data = $selProdSpecificsObj->getFlds();
            if (!$selProdSpecificsObj->addNew(array(), $data)) {
                Message::addErrorMessage(Labels::getLabel($selProdSpecificsObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupProductsMembershipDetails()
    {
        $post = FatApp::getPostedData();
        $selprodIds = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $productId = FatApp::getPostedData('selprod_product_id', FatUtility::VAR_INT, 0);
        $membershipPlansArr = json_decode(FatApp::getPostedData('membership_plan'), true);

        if (empty($post) || empty($membershipPlansArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_or_membership_plan_is_required', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $productOptions = Product::getProductOptions($productId, $this->siteLangId, true);
        $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
        if (empty($productOptions) || empty($optionCombinations)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $useShopPolicy = (isset($post['use_shop_policy'])) ? $post['use_shop_policy'] : 0;

        $membershipPlanIds = array_column($membershipPlansArr, 'id');
        foreach ($optionCombinations as $optionKey => $optionValue) {
            /* Check if product already added for this option [ */
            if (!isset($post['selprod_cost' . $optionKey])) {
                continue;
            }

            $selProdCode = $productId . '_' . $optionKey;
            $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId);
            if (!empty($selProdAvailable)) {
                if (!$selProdAvailable['selprod_deleted']) {
                    continue;
                }
                $data_to_be_save['selprod_deleted'] = applicationConstants::NO;
            }

            $data_to_be_save['selprod_code'] = $selProdCode;
            $data_to_be_save['selprod_user_id'] = $this->userParentId;
            $data_to_be_save['selprod_product_id'] = $productId;
            $data_to_be_save['selprod_active'] = applicationConstants::YES;
            $data_to_be_save['selprod_cost'] = (isset($post['selprod_cost' . $optionKey])) ? $post['selprod_cost' . $optionKey] : 0;

            $sellerProdObj = new SellerProduct();
            $sellerProdObj->assignValues($data_to_be_save);
            if (!$sellerProdObj->save()) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $selprod_id = $sellerProdObj->getMainTableRecordId();

            if (!$sellerProdObj->updateMembershipDetails($membershipPlanIds, $this->siteLangId)) {
                Message::addErrorMessage(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }

            /* [ Save Rental Data ] */
            if ($selprod_id) {
                $prodRentalData = array();
                $prodRentalData['sprodata_is_for_sell'] = 1;
                $prodRentalData['sprodata_is_for_rent'] = 1;
                $prodRentalData['sprodata_selprod_id'] = $selprod_id;
                $prodRentalData['sprodata_minimum_rental_quantity'] = $post['sprodata_minimum_rental_quantity'];
                $prodRentalData['sprodata_minimum_rental_duration'] = $post['sprodata_minimum_rental_duration'];
                $prodRentalData['sprodata_duration_type'] = $post['sprodata_duration_type'];
                $prodRentalData['sprodata_rental_active'] = $post['sprodata_rental_active'];
                $prodRentalData['sprodata_rental_available_from'] = $post['sprodata_rental_available_from'];
                $prodRentalData['sprodata_rental_condition'] = $post['sprodata_rental_condition'];
                $record = new ProductRental();
                /* $record->assignValues($prodRentalData); */
                if (!$record->addUpdateSelProData($prodRentalData)) {
                    Message::addErrorMessage($record->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            }
            /* [ Save Rental Data ] */
            $selProdSpecificsObj = new SellerProductSpecifics($selprod_id);
            if (0 < $useShopPolicy) {
                $whr = [
                    'smt' => 'sps_selprod_id = ? AND selprod_specific_type = ?',
                    'vals' => [$selprod_id, applicationConstants::PRODUCT_FOR_RENT]
                ];
                if (!FatApp::getDb()->deleteRecords(SellerProductSpecifics::DB_TBL, $whr)) {
                    FatUtility::dieJsonError(FatApp::getDb()->getError());
                }
            } else {
                $post['sps_selprod_id'] = $selprod_id;
                $post['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_RENT;
                $selProdSpecificsObj->assignValues($post);
                $data = $selProdSpecificsObj->getFlds();
                if (!$selProdSpecificsObj->addNew(array(), $data)) {
                    Message::addErrorMessage(Labels::getLabel($selProdSpecificsObj->getError(), $this->siteLangId));
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
        }

        $this->set('msg', Labels::getLabel("MSG_Setup_Successful", $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function autoCompleteMembershipPlans()
    { /* WILL UPDATE AFTER MEMBERSHIP MODULE MERGE */
        $post = FatApp::getPostedData();
        $memberShipArr = [
            1 => 'Silver',
            2 => 'Gold',
            3 => 'Free'
        ];

        $json = array();
        foreach ($memberShipArr as $key => $plan) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($plan, ENT_QUOTES, 'UTF-8')),
                'plan_identifier' => strip_tags(html_entity_decode($plan, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    /* ] */

    public function checkProductOwner($productSellerId)
    {
        if (UserAuthentication::isUserLogged() && $productSellerId != $this->userParentId) {
            return false;
        }
        return true;
    }

    private function isShopActive($userId, $shopId = 0, $returnResult = false)
    {
        $shop = new Shop($shopId, $userId);
        if (false == $returnResult) {
            return $shop->isActive();
        }

        if ($shop->isActive()) {
            return $shop->getData();
        }

        return false;
        //return Shop::isShopActive($userId, $shopId, $returnResult);
    }

    private function getSellerProductForm(int $product_id, int $selprod_id = 0, $type = 'SELLER_PRODUCT')
    {
        /* Type is used when we called this form for custom catalog request with selprod data */
        $defaultProductCond = '';
        $frm = new Form('frmSellerProduct');
        if ($type == 'CUSTOM_CATALOG') {
            $reqData = ProductRequest::getAttributesById($product_id, array('preq_content'));
            $productData = array_merge($reqData, json_decode($reqData['preq_content'], true));
            $productData['sellerProduct'] = 0;
            $optionArr = isset($productData['product_option']) ? $productData['product_option'] : array();
            foreach ($optionArr as $val) {
                $optionSrch = Option::getSearchObject($this->siteLangId);
                $optionSrch->addMultipleFields(array('IFNULL(option_name,option_identifier) as option_name', 'option_id'));
                $optionSrch->doNotCalculateRecords();
                $optionSrch->setPageSize(1);
                $optionSrch->addCondition('option_id', '=', $val);
                $rs = $optionSrch->getResultSet();
                $option = FatApp::getDb()->fetch($rs);
                if ($option == false) {
                    continue;
                }
                $optionValues = Product::getOptionValues($option['option_id'], $this->siteLangId);
                $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id[' . $option['option_id'] . ']', $optionValues, '', array('class' => 'selprodoption_optionvalue_id'), Labels::getLabel('LBL_Select', $this->siteLangId));
                $fld->requirements()->setRequired();
            }
        } else {
            $productData = Product::getAttributesById($product_id, array('product_type', 'product_min_selling_price', 'product_cod_enabled', 'if(product_seller_id > 0, 1, 0) as sellerProduct', 'product_seller_id'));
        }

        $frm->addCheckBox(Labels::getLabel('LBL_ENABLE_REQUEST_FOR_QUOTE', $this->siteLangId), 'selprod_enable_rfq', 1, array(), false, 0);

        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $minQtyfld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Rental_Quantity', $this->siteLangId), 'sprodata_minimum_rental_quantity');
        $minQtyfld->requirements()->setRange(1, SellerProduct::MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY);


        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $this->siteLangId), 'sprodata_rental_condition', Product::getConditionArr($this->siteLangId), $defaultProductCond, array(), Labels::getLabel('LBL_Select_Condition', $this->siteLangId));
        $fld->requirements()->setRequired();

        $frm->addDateField(Labels::getLabel('LBL_Available_From', $this->siteLangId), 'sprodata_rental_available_from', '', array('readonly' => 'readonly', 'class' => 'field--calender'))->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Publish', $this->siteLangId), 'sprodata_rental_active', applicationConstants::getYesNoArr($this->siteLangId), applicationConstants::YES, array(), '');

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Rental_Duration', $this->siteLangId), 'sprodata_minimum_rental_duration');
        $fld->requirements()->setPositive();
        $fld->requirements()->setRange(1, 99999);
        $durationTypes = ProductRental::durationTypeArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Rental_Duration_Type', $this->siteLangId), 'sprodata_duration_type', $durationTypes, '', array())->requirements()->setRequired();

        if ($type != 'CUSTOM_CATALOG') {
            $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
            $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $this->siteLangId), 'selprod_cod_enabled', $yesNoArr, '0', array(), '');

            $paymentMethod = new PaymentMethods();
            if (!$paymentMethod->cashOnDeliveryIsActive() || $productData['product_cod_enabled'] != applicationConstants::YES) {
                $codFld->addFieldTagAttribute('disabled', 'disabled');
                if ($productData['product_cod_enabled'] != applicationConstants::YES) {
                    $codFld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_COD_option_is_disabled_in_Product', $this->siteLangId) . '</small>';
                } else {
                    $codFld->htmlAfterField = '<small class="text--small">' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->siteLangId) . '</small>';
                }
            }

            //$shipBySeller = Product::isProductShippedBySeller($product_id, $productData['product_seller_id'], UserAuthentication::getLoggedUserId());
            
            $shopDetails = Shop::getAttributesByUserId($this->userParentId, null, false);
            $address = new Address(0, $this->siteLangId);
            $addresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);

            $fulfillmentType = empty($addresses) ? Shipping::FULFILMENT_SHIP : Shop::getAttributesByUserId(UserAuthentication::getLoggedUserId(), 'shop_fulfillment_type');

            $fulFillmentArr = Shipping::getFulFillmentArr($this->siteLangId, $fulfillmentType);
            /*if ($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL && true == $shipBySeller) {*/
            if ($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                $fld = $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_METHOD', $this->siteLangId), 'sprodata_fullfillment_type', $fulFillmentArr, applicationConstants::NO, []);
                $fld->requirements()->setRequired();
                if (empty($addresses)) {
                    $fld->htmlAfterField = '<span class="note">'.Labels::getLabel('LBL_Add_Pickup_Address_MSG', $this->siteLangId).'</span>';
                }
            }
            
            $shipProfileArr = ShippingProfile::getProfileArr($this->siteLangId, $this->userParentId, true, true);
            $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->siteLangId), 'shipping_profile', $shipProfileArr)->requirements()->setRequired(true);
            
            $frm->addRequiredField(Labels::getLabel('LBL_Url_Keyword', $this->siteLangId), 'selprod_url_keyword');
            $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);

            if (!empty($productOptions)) {
                $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
                $validOptionsFoeSeller = CommonHelper::validOptionsForSeller($product_id, $optionCombinations, $this->userParentId, $this->siteLangId);
                $fld = $frm->addSelectBox(Labels::getLabel('LBL_Varient', $this->siteLangId), 'varient_id', $validOptionsFoeSeller, '', array(), Labels::getLabel('LBL_Select_Varient', $this->siteLangId));
                $fld->requirement->setRequired(true);
            } else {
                $frm->addHiddenField('', 'varient_id[0]');
            }

            $secAmtFld = $frm->addFloatField(Labels::getLabel('LBL_Security_Amount', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'sprodata_rental_security');
            $secAmtFld->requirements()->setPositive();
            $secAmtFld->requirements()->setRange(1, 99999999.99);

            $bufferDaysFld = $frm->addIntegerField(Labels::getLabel('LBL_Buffer_Days', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'sprodata_rental_buffer_days');
            /* $bufferDaysFld->requirements()->setLength(1, 10); */
            $bufferDaysFld->requirements()->setPositive();
            $bufferDaysFld->requirements()->setRange(1, 365);

            $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Original_Price', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'selprod_cost');
            $costPrice->requirements()->setPositive();
            $costPrice->requirements()->setRange(1, 99999999.99);

            $fld = $frm->addFloatField(Labels::getLabel('LBL_Rental_Price', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'sprodata_rental_price', 0, ['placeholder' => Labels::getLabel('LBL_Rental_Price', $this->siteLangId)]);
            $fld->requirements()->setPositive();
            $fld->requirements()->setRange(1, 99999999.99);

            $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $this->siteLangId), 'sprodata_rental_stock');
            $fld->requirements()->setPositive();
            $fld->requirements()->setRange(0, 999999999);
            $fld->requirements()->setCompareWith('sprodata_minimum_rental_quantity', 'ge', '');
            
        }
        $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->siteLangId), 'selprod_comments' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $frm->addTextArea(Labels::getLabel('LBL_Rental_Terms_&_Conditions', $this->siteLangId), 'selprod_rental_terms' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $languages = Language::getAllNames();
        unset($languages[FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        foreach ($languages as $langId => $langName) {
            $frm->addTextBox(Labels::getLabel('LBL_Title', $this->siteLangId), 'selprod_title' . $langId);
            $frm->addTextArea(Labels::getLabel('LBL_Rental_Terms_&_Conditions', $this->siteLangId), 'selprod_rental_terms' . $langId);
            $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->siteLangId), 'selprod_comments' . $langId);
        }
        $frm->addHiddenField('', 'selprod_product_id', $product_id);
        $frm->addHiddenField('', 'selprod_urlrewrite_id');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        if ($type != 'CUSTOM_CATALOG') {
            $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Discard', $this->siteLangId), array('onClick' => 'gotToProucts()'));
        }
        return $frm;
    }

}
