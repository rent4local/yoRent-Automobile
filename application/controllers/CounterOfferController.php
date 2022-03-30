<?php

class CounterOfferController extends LoggedUserController
{

    public function __construct($action)
    {
        parent::__construct($action);
        if (UserAuthentication::isGuestUserLogged()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('account'));
        }
    }


    public function listingForSeller()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);

        $reqForQuote = new RequestForQuote($rfqId);
        $rfqDetail = $reqForQuote->getRequestDetail($this->userParentId);

        if (empty($rfqDetail) || $rfqDetail['selprod_user_id'] != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = CounterOffer::getSearchObject($rfqId);
        $srch->addOrder('counter_offer_added_on', 'DESC');
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);

        $this->set("rfqData", $rfqDetail);
        $this->set("arr_listing", $records);
        $this->set("siteLangId", $this->siteLangId);
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));
        $this->set('canEdit', $this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    public function listingForBuyer()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);
        if (1 > $rfqId) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $rfqId);
        $srch->addCondition('rfq_user_id', '=', UserAuthentication::getLoggedUserId());
        $srchRs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($srchRs);

        if (empty($record)) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = CounterOffer::getSearchObject($rfqId);
        $srch->joinTable(RequestForQuote::DB_TBL, 'INNER JOIN', 'co.counter_offer_rfq_id = rfq.rfq_id', 'rfq');
        $srch->addOrder('counter_offer_added_on', 'DESC');
        $srch->addMultipleFields(array('co.*', 'rfq.rfq_added_on'));
        $srchRs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($srchRs);

        $this->set("rfqData", $record);
        $this->set("arr_listing", $records);
        $this->set("siteLangId", $this->siteLangId);
        $this->set('statusArr', RequestForQuote::statusArray($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function updateStatusByBuyer()
    {
        $post = FatApp::getPostedData();
        if (1 > $post['rfq_id'] || !RequestForQuote::canBuyerUpdateStatus($post['status'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        if(!$this->changeStatus($post)){
            FatUtility::dieJsonError(Labels::getLabel("LBL_Somthing_Went_Wrong", $this->siteLangId));
        }
        Message::addMessage(Labels::getLabel('MSG_Status_updated_Successfully', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }

    public function changeStatus($post)
    {
        if (1 > $post['rfq_id']) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new RequestForQuoteSearch();
        $srch->joinWithSellerProduct($this->siteLangId);
        $srch->joinUsers();
        $srch->addCondition('rfq_id', '=', $post['rfq_id']);
        $cond = $srch->addCondition('selprod_user_id', '=', $this->userParentId);
        $cond->attachCondition('rfq_user_id', '=', UserAuthentication::getLoggedUserId(), 'OR');
        $srchRs = $srch->getResultSet();
        $rfqData = FatApp::getDb()->fetch($srchRs);

        if (empty($rfqData) || $rfqData['rfq_status'] == RequestForQuote::REQUEST_QUOTE_VALIDITY) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $dataToUpdate = array(
            'rfq_status' => $post['status']
        );
        $db = FatApp::getDb();
        $db->startTransaction();

        $record = new RequestForQuote($post['rfq_id']);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($post['status'] == RequestForQuote::REQUEST_APPROVED) {
            if (!$this->addOrderForRfq($post['rfq_id'])) {
                $db->rollbackTransaction();
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $db->commitTransaction();
        /* [ OFFER SUBMISSION EMAIL NOTIFICATION */
        $emailHandler = new EmailHandler();
        if (!$emailHandler->offerStatusUpdateNotification($this->siteLangId, $post['rfq_id'])) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        return true;
    }

    public function updateStatusBySeller()
    {
        if (!$this->userPrivilege->canEditOfferManagement(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        if (1 > $post['rfq_id'] || !RequestForQuote::canSellerUpdateStatus($post['status'])) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$this->changeStatus($post)){
            FatUtility::dieJsonError(Labels::getLabel("LBL_Somthing_Went_Wrong", $this->siteLangId));
        }
        if ($post['status'] == RequestForQuote::REQUEST_APPROVED) {
            Message::addMessage(Labels::getLabel('MSG_Status_updated_Successfully._Please_Generate_Invoice', $this->siteLangId));
        } else {
            Message::addMessage(Labels::getLabel('MSG_Status_updated_Successfully', $this->siteLangId));
        }
        
        FatUtility::dieJsonSuccess(Message::getHtml());
        
    }

    private function addOrderForRfq(int $rfqId)
    {
        $rfqDetails = RequestForQuote::getAttributesById($rfqId);
        $coObj = new CounterOffer(0, $rfqId);
        $offerDetails = $coObj->getFinalOfferByRfqId();
        
        $initialCounterOffer = $coObj->getFinalOfferByRfqId(true);
        if (empty($rfqDetails) || empty($offerDetails)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            return false;
        }

        if (!Orders::validRfqForOrder($rfqId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            return false;
        }

        $productType = SellerProduct::getAttributesById($rfqDetails['rfq_selprod_id'], 'selprod_type');
        $productInfo = $this->getCartProductInfo($rfqDetails['rfq_selprod_id'], $productType);
        $unitPrice = round($offerDetails["counter_offer_total_cost"] / $rfqDetails['rfq_quantity'], 2);

        $roundingOff = $offerDetails["counter_offer_total_cost"] - ($unitPrice * $rfqDetails['rfq_quantity']);
        
        $obj = new Address($rfqDetails['rfq_billing_address_id']);
        $billingAdress = $obj->getData(Address::TYPE_USER, $rfqDetails['rfq_user_id'], $this->siteLangId);
        if (empty($billingAdress) ) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_BILLING_ADRESS', $this->siteLangId));
            return false;
        }
        
        $obj = new Address($rfqDetails['rfq_shipping_address_id']);
        $shippingAdress = $obj->getData(Address::TYPE_USER, $rfqDetails['rfq_user_id'], $this->siteLangId);
        if (empty($billingAdress) ) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_BILLING_ADRESS', $this->siteLangId));
            return false;
        }
        
        $shipFromStateId = Shop::getAttributesByUserId($productInfo['selprod_user_id'], 'shop_state_id');
        $taxObj = new Tax();
        
        $addressForTax = ($rfqDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP) ? $billingAdress : $shippingAdress;
        $extraInfo = [
			'shippedBySeller' => 1,
			'shippingAddress' => [
				'addr_country_id' => $addressForTax['addr_country_id'],
				'addr_state_id' => $addressForTax['addr_state_id'],
			]
		];
		
		
        $taxData = $taxObj->calculateTaxRates($productInfo['product_id'], $unitPrice, $productInfo['selprod_user_id'], $this->siteLangId, $rfqDetails['rfq_quantity'], $extraInfo, false, $productInfo['selprod_type'], $rfqDetails['rfq_request_type']);
		$productInfo['tax'] = $taxData['tax'];
        $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION", FatUtility::VAR_FLOAT, 0);
        $commissionPercentage = SellerProduct::getProductCommission($rfqDetails['rfq_selprod_id'], $rfqDetails['rfq_request_type']);
        $commission = MIN(ROUND(($offerDetails["counter_offer_total_cost"] + $offerDetails['counter_offer_shipping_cost'] + $taxData['tax']) * $commissionPercentage / 100, 2), $maxConfiguredCommissionVal);
        $productInfo['commission_percentage'] = $commissionPercentage;
        $productInfo['commission'] = ROUND($commission, 2);

        $orderData = [];
        $orderData['order_id'] = '';
        $orderData['order_user_id'] = $rfqDetails['rfq_user_id'];
        $orderData['order_date_added'] = date('Y-m-d H:i:s');
        $orderData['order_date_updated'] = date('Y-m-d H:i:s');
        $orderData['order_is_rfq'] = applicationConstants::YES;
        $orderData['order_rfq_id'] = $rfqId;

        $userAddresses[0] = array(
            'oua_order_id' => '',
            'oua_type' => Orders::BILLING_ADDRESS_TYPE,
            'oua_name' => $rfqDetails['rfq_name'],
            'oua_address1' => $billingAdress['addr_address1'],
            'oua_address2' => $billingAdress['addr_address2'],
            'oua_city' => $billingAdress['addr_city'],
            'oua_state' => $billingAdress['state_name'],
            'oua_country' => $billingAdress['country_name'],
            /* 'oua_country_code' => $billingAdress['country_code'], */
            'oua_dial_code' => $billingAdress['addr_dial_code'],
            'oua_phone' => $billingAdress['addr_phone'],
            'oua_zip' => $billingAdress['addr_zip'],
        );

        if ($rfqDetails['rfq_fulfilment_type'] != Shipping::FULFILMENT_PICKUP) {
            $userAddresses[1] = array(
                'oua_order_id' => '',
                'oua_type' => Orders::SHIPPING_ADDRESS_TYPE,
                'oua_name' => $rfqDetails['rfq_name'],
                'oua_address1' => $shippingAdress['addr_address1'],
                'oua_address2' => $shippingAdress['addr_address2'],
                'oua_city' => $shippingAdress['addr_city'],
                'oua_state' => $shippingAdress['state_name'],
                'oua_country' => $shippingAdress['country_name'],
                /* 'oua_country_code' => $shippingAdress['country_code'], */
                'oua_dial_code' => $shippingAdress['addr_dial_code'],
                'oua_phone' => $shippingAdress['addr_phone'],
                'oua_zip' => $shippingAdress['addr_zip'],
            );

        }
        $orderData['userAddresses'] = $userAddresses;

        $orderData['extra'] = array(
            'oextra_order_id' => '',
            'order_ip_address' => $_SERVER['REMOTE_ADDR']
        );

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $orderData['extra']['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $orderData['extra']['order_forwarded_ip'] = '';
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $orderData['extra']['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $orderData['extra']['order_user_agent'] = '';
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $orderData['extra']['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $orderData['extra']['order_accept_language'] = '';
        }

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] = $languageRow['language_id'];
        $orderData['order_language_code'] = $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] = $currencyRow['currency_id'];
        $orderData['order_currency_code'] = $currencyRow['currency_code'];
        $orderData['order_currency_value'] = $currencyRow['currency_value'];
        $orderData['order_default_currency_to_cop'] = 1;
        $orderData['order_user_comments'] = '';
        $orderData['order_admin_comments'] = '';

        $orderData['order_tax_charged'] = $productInfo['tax'];
        $orderData['order_site_commission'] = $productInfo['commission'];
        $orderData['order_volume_discount_total'] = 0;

        $orderData['order_net_amount'] = $offerDetails["counter_offer_total_cost"] + $offerDetails["counter_offer_shipping_cost"] + $productInfo['tax'] + $offerDetails["counter_offer_rental_security"];
        $orderData['order_is_wallet_selected'] = 0;
        $orderData['order_wallet_amount_charge'] = 0;
        $orderData['order_type'] = Orders::ORDER_PRODUCT;
        $orderData['order_product_type'] = $rfqDetails['rfq_request_type'];

        $allLanguages = Language::getAllNames();
        $orderLangData = array();
        $productShippingData['opshipping_by_seller_user_id'] = $productInfo['selprod_user_id'];
        $productShippingData['opshipping_fulfillment_type'] = $rfqDetails['rfq_fulfilment_type'];

        $productPickUpData = [];
        $productPickupAddress = [];
        if($rfqDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP){
            $productShippingData = [];
            $productPickUpData = array(
                'opshipping_fulfillment_type' => Shipping::FULFILMENT_PICKUP,
                'opshipping_by_seller_user_id' => $productInfo['selprod_user_id'],
                'opshipping_pickup_addr_id' => $rfqDetails['rfq_pickup_address_id'],
                'opshipping_date' => '',
                'opshipping_time_slot_from' => '',
                'opshipping_time_slot_to' => '',
            );


            $addr = new Address($rfqDetails['rfq_pickup_address_id'], $this->siteLangId);
            $pickUpAddressArr = $addr->getData(Address::TYPE_SHOP_PICKUP, $productInfo['shop_id']);
            $productPickupAddress = array(
                'oua_order_id' => '',
                'oua_op_id' => '',
                'oua_type' => Orders::PICKUP_ADDRESS_TYPE,
                'oua_name' => $pickUpAddressArr['addr_name'],
                'oua_address1' => $pickUpAddressArr['addr_address1'],
                'oua_address2' => $pickUpAddressArr['addr_address2'],
                'oua_city' => $pickUpAddressArr['addr_city'],
                'oua_state' => $pickUpAddressArr['state_name'],
                'oua_country' => $pickUpAddressArr['country_name'],
                'oua_country_code' => $pickUpAddressArr['country_code'],
                'oua_country_code_alpha3' => $pickUpAddressArr['country_code_alpha3'],
                'oua_state_code' => $pickUpAddressArr['state_code'],
                'oua_dial_code' => $pickUpAddressArr['addr_dial_code'],
                'oua_phone' => $pickUpAddressArr['addr_phone'],
                'oua_zip' => $pickUpAddressArr['addr_zip'],
            );
        }


        foreach ($allLanguages as $lang_id => $language_name) {
            $orderLangData[$lang_id] = array(
                'orderlang_lang_id' => $lang_id,
                'order_shippingapi_name' => ''
            );
            $langSpecificProductInfo = $this->getCartProductLangData($rfqDetails['rfq_selprod_id'], $lang_id, $productType);
            if (!$langSpecificProductInfo) {
                continue;
            }

            $weightUnitsArr = applicationConstants::getWeightUnitsArr($lang_id);
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($lang_id);
            $op_selprod_title = ($langSpecificProductInfo['selprod_title'] != '') ? $langSpecificProductInfo['selprod_title'] : '';

            $op_selprod_options = '';
            $productOptionsRows = SellerProduct::getSellerProductOptions($rfqDetails['rfq_selprod_id'], true, $lang_id);
            if (!empty($productOptionsRows)) {
                $optionCounter = 1;
                foreach ($productOptionsRows as $poLang) {
                    $op_selprod_options .= $poLang['option_name'] . ': ' . $poLang['optionvalue_name'];
                    if ($optionCounter != count($productOptionsRows)) {
                        $op_selprod_options .= ' | ';
                    }
                    $optionCounter++;
                }
            }

            $op_products_dimension_unit_name = ($productInfo['product_dimension_unit']) ? $lengthUnitsArr[$productInfo['product_dimension_unit']] : '';
            $op_product_weight_unit_name = ($productInfo['product_weight_unit']) ? $weightUnitsArr[$productInfo['product_weight_unit']] : '';
            $op_product_tax_options = array();
			$productTaxChargesData = [];
            if (array_key_exists('options', $taxData)) {
                    foreach ($taxData['options'] as $taxStroId => $taxStroName) {
                        $label = Labels::getLabel('LBL_Tax', $lang_id);
                        if (array_key_exists('name', $taxStroName) && $taxStroName['name'] != '') {
                            $label = $taxStroName['name'];
                        }
                        $op_product_tax_options[$label]['name'] = $label;
                        $op_product_tax_options[$label]['value'] = $taxStroName['value'];
                        $op_product_tax_options[$label]['percentageValue'] = $taxStroName['percentageValue'];
                        $op_product_tax_options[$label]['inPercentage'] = $taxStroName['inPercentage'];

                        if (isset($taxStroName['taxstr_id']) && $taxStroName['taxstr_id'] != '') {
                            $langData = TaxStructure::getAttributesByLangId($lang_id, $taxStroName['taxstr_id'], array(), 1);
                            $langLabel = (isset($langData['taxstr_name']) && $langData['taxstr_name'] != '') ? $langData['taxstr_name'] : $label;
                        } else {
                            $langLabel = $label;
                        }

                        $productTaxChargesData[$taxStroId] = array(
                            'opchargelog_type' => OrderProduct::CHARGE_TYPE_TAX,
                            'opchargelog_identifier' => $label,
                            'opchargelog_value' => $taxStroName['value'],
                            'opchargelog_is_percent' => $taxStroName['inPercentage'],
                            'opchargelog_percentvalue' => $taxStroName['percentageValue']
                        );
                        
                        $productTaxChargesData[$taxStroId]['langData'][$lang_id] = array(
                            'opchargeloglang_lang_id' => $lang_id,
                            'opchargelog_name' => $langLabel
                        );
                        
                    }
            }
            
            $sduration_name = '';
            $shippingDurationTitle = '';
            $productsLangData[$lang_id] = [
                'oplang_lang_id' => $lang_id,
                'op_product_name' => $langSpecificProductInfo['product_name'],
                'op_selprod_title' => $op_selprod_title,
                'op_selprod_options' => $op_selprod_options,
                'op_brand_name' => (isset($langSpecificProductInfo['brand_name']) && !empty($langSpecificProductInfo['brand_name'])) ? $langSpecificProductInfo['brand_name'] : '',
                'op_shop_name' => $langSpecificProductInfo['shop_name'],
                'op_shipping_duration_name' => $sduration_name,
                'op_shipping_durations' => $shippingDurationTitle,
                'op_products_dimension_unit_name' => $op_products_dimension_unit_name,
                'op_product_weight_unit_name' => $op_product_weight_unit_name,
                'op_product_tax_options' => json_encode($op_product_tax_options),
            ];
        }

        $opdRentalSecurity = $offerDetails['counter_offer_rental_security'];
        /* $opdRentalStartDate = $rfqDetails['rfq_from_date'];
        $opdRentalEndDate = $rfqDetails['rfq_to_date']; */
        
        $opdRentalStartDate = $initialCounterOffer['counter_offer_from_date'];
        $opdRentalEndDate = $initialCounterOffer['counter_offer_to_date'];

        $duration = CommonHelper::getDifferenceBetweenDates($opdRentalStartDate, $opdRentalEndDate, $productInfo['selprod_user_id'], $productInfo['sprodata_duration_type']);
        $opdRentalPriceMultiplier = Common::daysBetweenDates($opdRentalStartDate, $opdRentalEndDate);

        $opdRentalTerms = $productInfo['sprodata_rental_terms'];
        $opdRentalPriceMultiplierHours = $duration;
        $orderKey = CART::CART_KEY_PREFIX_PRODUCT . $productInfo['selprod_id'] . $opdRentalStartDate . $opdRentalEndDate;

        $priceArr = CommonHelper::getRentalPricesArr($productInfo);
        $opdRentalPrice = $offerDetails["counter_offer_total_cost"];
        $productsData = array(
            'opd_sold_or_rented' => $rfqDetails['rfq_request_type'],
            'opd_rental_type' => $productInfo['sprodata_duration_type'],
            'opd_rental_start_date' => $opdRentalStartDate,
            'opd_rental_end_date' => $opdRentalEndDate,
            'opd_rental_security' => $opdRentalSecurity / $rfqDetails['rfq_quantity'],
            'opd_rental_terms' => $opdRentalTerms,
            'opd_rental_price_multiplier_days' => $opdRentalPriceMultiplier,
            'opd_rental_price_multiplier_hours' => $opdRentalPriceMultiplierHours,
            'opd_rental_price' => $opdRentalPrice,
            'opd_refunded_security_amount' => 0,
            'opd_refunded_security_type' => '',
            'opd_refunded_security_status' => '',
            'opd_rental_duration_discount' => 0,
            'opd_extend_from_op_id' => 0,
            'opd_product_type' => (isset($productInfo['sellerProdType'])) ? $productInfo['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT, // TO DO 
            'opd_main_product_id' => (isset($productInfo['mainProductId'])) ? $productInfo['mainProductId'] : 0,
            'opd_tax_charge_address_type' => Tax::TAX_ON_SHIPPING_TO_ADDRESS,
        );

        $orderData['products'][CART::CART_KEY_PREFIX_PRODUCT . $productInfo['selprod_id']] = [
            'op_selprod_product_id' => $productInfo['selprod_product_id'],
            'op_product_identifier' => $productInfo['product_identifier'],
            'op_selprod_id' => $productInfo['selprod_id'],
            'op_is_batch' => 0,
            'op_type' => $productInfo['selprod_type'],
            'op_selprod_user_id' => $productInfo['selprod_user_id'],
            'op_selprod_code' => $productInfo['selprod_code'],
            'op_qty' => $rfqDetails['rfq_quantity'],
            'op_unit_price' => $unitPrice,
            'op_unit_cost' => ($offerDetails["counter_offer_total_cost"] + $offerDetails["counter_offer_shipping_cost"] + $productInfo['tax']) / $rfqDetails['rfq_quantity'],
            'op_selprod_sku' => $productInfo['selprod_sku'],
            'op_selprod_condition' => $productInfo['selprod_condition'],
            'op_product_model' => $productInfo['product_model'],
            'op_product_type' => $productInfo['product_type'],
            'op_product_length' => $productInfo['product_length'],
            'op_product_width' => $productInfo['product_width'],
            'op_product_height' => $productInfo['product_height'],
            'op_product_dimension_unit' => $productInfo['product_dimension_unit'],
            'op_product_weight' => $productInfo['product_weight'],
            'op_product_weight_unit' => $productInfo['product_weight_unit'],
            'op_shop_id' => $productInfo['shop_id'],
            'op_shop_owner_username' => $productInfo['shop_owner_username'],
            'op_shop_owner_name' => $productInfo['shop_onwer_name'],
            'op_shop_owner_email' => $productInfo['shop_owner_email'],
            'op_shop_owner_phone_code' => isset($productInfo['user_dial_code']) && !empty($productInfo['user_dial_code']) ? $productInfo['user_dial_code'] : '',
            'op_shop_owner_phone' => isset($productInfo['shop_owner_phone']) && !empty($productInfo['shop_owner_phone']) ? $productInfo['shop_owner_phone'] : '',
            'op_selprod_max_download_times' => ($productInfo['selprod_max_download_times'] != '-1') ? $rfqDetails['rfq_quantity'] * $productInfo['selprod_max_download_times'] : $productInfo['selprod_max_download_times'],
            'op_selprod_download_validity_in_days' => $productInfo['selprod_download_validity_in_days'],
            'op_sduration_id' => 0,
            'op_commission_charged' => $productInfo['commission'],
            'op_commission_percentage' => $productInfo['commission_percentage'],
            'op_affiliate_commission_percentage' => 0,
            'op_affiliate_commission_charged' => 0,
            'op_status_id' => FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS", FatUtility::VAR_INT, 0),
            'productsLangData' => $productsLangData,
            'productShippingData' => $productShippingData,
            'productPickUpData' => $productPickUpData,
            'productPickupAddress' => $productPickupAddress,
            'productShippingLangData' => [],
			'productChargesLogData' => $productTaxChargesData,
            'op_free_ship_upto' => 0,
            'op_actual_shipping_charges' => $offerDetails['counter_offer_shipping_cost'],
            'op_tax_collected_by_seller' => FatApp::getConfig("CONF_TAX_COLLECTED_BY_SELLER", FatUtility::VAR_INT, 0),
            'op_rounding_off' => $roundingOff,
            'productSpecifics' => [
                'op_selprod_return_age' => $productInfo['return_age'],
                'op_selprod_cancellation_age' => $productInfo['cancellation_age'],
                'op_product_warranty' => $productInfo['product_warranty']
            ],
            'productsData' => $productsData, /* Order product rental data */
        ];

        $shippingCost = $offerDetails["counter_offer_shipping_cost"];
        $orderData['prodCharges'][CART::CART_KEY_PREFIX_PRODUCT . $productInfo['selprod_id']] = [
            OrderProduct::CHARGE_TYPE_SHIPPING => ['amount' => $shippingCost],
            OrderProduct::CHARGE_TYPE_TAX => ['amount' => $taxData['tax']],
            OrderProduct::CHARGE_TYPE_DISCOUNT => ['amount' => 0],
            OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT => ['amount' => 0],
            OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT => ['amount' => 0],
        ];

        $orderData['orderLangData'] = $orderLangData;

        $orderObj = new Orders();
        if (!$orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            Message::addErrorMessage($orderObj->getError());
            return false;
        }

        return true;
    }

    private function getCartProductInfo(int $selprod_id, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrch = new ProductSearch($this->siteLangId);
        $joinBrands = false;
        if ($type == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $joinBrands = true;
        }
        $prodSrch->setDefinedCriteria(0, 0, array(), true, false, $joinBrands);
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->joinProductSpecifics();
        if ($type == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            
        }

        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array('product_id', 'product_type', 'product_length', 'product_width', 'product_height',
            'product_dimension_unit', 'product_weight', 'product_weight_unit', 'product_model',
            'selprod_id', 'selprod_user_id', 'selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sku',
            'selprod_condition', 'selprod_code', 'selprod_type',
            'special_price_found', 'theprice', 'shop_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'shop_name',
            'seller_user.user_name as shop_onwer_name', 'seller_user_cred.credential_username as shop_owner_username',
            'seller_user.user_dial_code', 'seller_user.user_phone as shop_owner_phone', 'seller_user_cred.credential_email as shop_owner_email', 'selprod_download_validity_in_days',
            'selprod_max_download_times', 'ps.product_warranty', 'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age',
            'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age', 'sprodata_rental_security', 'sprodata_rental_stock',
            'sprodata_rental_terms', 'sprodata_duration_type', 'sprodata_rental_price', 'selprod_product_id', 'product_identifier');
        $prodSrch->addMultipleFields($fields);
        $rs = $prodSrch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }


    private function getCartProductLangData(int $selprod_id, $lang_id, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $langProdSrch = new ProductSearch($lang_id);
        $joinBrands = false;
        if ($type == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $joinBrands = true;
        }

        $langProdSrch->setDefinedCriteria(0, 0, array(), true, false, $joinBrands);
        $langProdSrch->joinProductToCategory();
        $langProdSrch->joinSellerSubscription();
        $langProdSrch->addSubscriptionValidCondition();
        $langProdSrch->doNotCalculateRecords();
        $langProdSrch->doNotLimitRecords();
        $langProdSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $langProdSrch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array('IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(shop_name, shop_identifier) as shop_name');
        $langProdSrch->addMultipleFields($fields);
        if ($type == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $langProdSrch->addFld(['IFNULL(brand_name, brand_identifier) as brand_name']);
        }

        $langProdRs = $langProdSrch->getResultSet();
        return FatApp::getDb()->fetch($langProdRs);
    }


    public function form()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);

        $rfqData = RequestForQuote::getAttributesById($rfqId, array('rfq_fulfilment_type', 'rfq_request_type'));
		$data = $this->getLatestSellerOfferDetails($rfqId);
		$data['counter_offer_total_cost'] = 0;
		$data['counter_offer_comment'] = '';
		
        $frm = $this->getForm($rfqData['rfq_request_type'], $rfqData['rfq_fulfilment_type']);
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->set('rfqData', $rfqData);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('isSeller', applicationConstants::NO);
        $this->_template->render(false, false);
    }
	
	private function getLatestSellerOfferDetails(int $rfqId) : array
	{
		$srch = CounterOffer::getSearchObject($rfqId);
        $srch->addOrder('co.counter_offer_id', 'DESC');
        $srch->addFld('co.*', 'rfq_id');
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);
		if (empty($data)) {
			return [];
		} 
		return $data;
	}
	

    private function getShippingRate(int $rfqId)
    {
        /* [ GET SHIPPING PRICE FROM SELLER OFFER */
        $srch = CounterOffer::getSearchObject($rfqId);
        $srch->addOrder('co.counter_offer_id', 'DESC');
        $srch->addFld('counter_offer_shipping_cost');
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);
        if (empty($data)) {
            return 0;
        }
        return $data['counter_offer_shipping_cost'];
    }

    private function getForm($OrderType, $fulfilmentType)
    {
        $frm = new Form('counterOfferFrm');

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Offer_Price', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_total_cost');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setFloatPositive();
        $fld->requirements()->setRange(0.1, 999999);
		
        if($OrderType != applicationConstants::PRODUCT_FOR_SALE){
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Rental_Security', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_rental_security');
		
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive();
            $fld->requirements()->setRange(0, 999999);
        }

        if($fulfilmentType != Shipping::FULFILMENT_PICKUP){
            $shipFld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_Cost', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_shipping_cost');
            $frm->addHTML('', 'total_price', '<div ><span>' . Labels::getLabel('LBL_Total_Price', $this->siteLangId) . ' : </span><span>'. CommonHelper::getSystemDefaultCurrenySymbolLeft() .'</span><span class="total_price--js">' . $shipFld->value . '</span><span>'. CommonHelper::getSystemDefaultCurrenySymbolRight() .'</span></div>');
        } else{
            $frm->addHTML('', 'total_price', '<div ><span>' . Labels::getLabel('LBL_Total_Price', $this->siteLangId) . ' : </span><span>'. CommonHelper::getSystemDefaultCurrenySymbolLeft() .'</span><span class="total_price--js">0</span><span>'. CommonHelper::getSystemDefaultCurrenySymbolRight() .'</span></div>');
        }
		
		
        
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->siteLangId), 'counter_offer_comment');
        $frm->addHiddenField('', 'counter_offer_rfq_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Counter_Offer', $this->siteLangId));

        return $frm;
    }

    public function setupBuyerCounterOffer()
    {
        $rfqData = RequestForQuote::getAttributesById(FatApp::getPostedData('counter_offer_rfq_id'), array('rfq_fulfilment_type', 'rfq_request_type'));
        
        $frm = $this->getForm($rfqData['rfq_request_type'], $rfqData['rfq_fulfilment_type']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        /* if (isset($post['counter_offer_rfq_id'])) {
			$data = $this->getLatestSellerOfferDetails($post['counter_offer_rfq_id']);
            $post['counter_offer_shipping_cost'] = $data['counter_offer_shipping_cost'];
            $post['counter_offer_rental_security'] = $data['counter_offer_rental_security'];
        } */
        $this->setup($post, RequestForQuote::REQUEST_COUNTER_BY_BUYER);
    }

    public function setupSellerCounterOffer()
    {
        $rfqData = RequestForQuote::getAttributesById(FatApp::getPostedData('counter_offer_rfq_id'), array('rfq_fulfilment_type', 'rfq_request_type'));

        $frm = $this->getSellerCounterForm($rfqData['rfq_request_type'], $rfqData['rfq_fulfilment_type']);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $this->setup($post, RequestForQuote::REQUEST_COUNTER_BY_SELLER);
    }

    public function setup(array $post, int $status)
    {
        if ($post === false) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $rfqId = intval($post['counter_offer_rfq_id']);
        if (1 > $rfqId) {
            Message::addErrorMessage(Labels::getLabel("LBL_Invalid_Request", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $defaultData = array(
            'counter_offer_by' => UserAuthentication::getLoggedUserId(),
            'counter_offer_added_on' => date('Y-m-d H:i:s'),
            'counter_offer_status' => $status,
        );

        $dataToSave = array_merge($post, $defaultData);

        $record = new CounterOffer();
        $record->assignValues($dataToSave);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $counterOfferId = $record->getMainTableRecordId();

        $dataToUpdate = array(
            'rfq_status' => $status
        );

        $record = new RequestForQuote($rfqId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* [ OFFER SUBMISSION EMAIL NOTIFICATION */
        $emailHandler = new EmailHandler();
        if (!$emailHandler->newRfqOfferNotification($this->siteLangId, $rfqId)) {
            Message::addErrorMessage($emailHandler->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        Message::addMessage(Labels::getLabel('MSG_Offer_submitted_Successfully.', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
    }


    public function formForSeller()
    {
        $rfqId = FatApp::getPostedData('rfq_id', FatUtility::VAR_INT, 0);

        $rfqData = RequestForQuote::getAttributesById($rfqId, array('rfq_fulfilment_type', 'rfq_request_type'));

        $shippingCost = $this->getShippingRate($rfqId);
		$data = $this->getLatestSellerOfferDetails($rfqId);
		
		$data['counter_offer_total_cost'] = 0;
		$data['counter_offer_comment'] = '';
		
        $frm = $this->getSellerCounterForm($rfqData['rfq_request_type'], $rfqData['rfq_fulfilment_type']);
        $frm->fill($data);
        $this->set('frm', $frm);
        $this->set('rfqData', $rfqData);
        $this->set('isSeller', applicationConstants::YES);
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->render(false, false, '/counter-offer/form.php');
    }

    private function getSellerCounterForm($orderType, $fulfilmetType)
    {
        $frm = new Form('counterOfferFrm');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Offer_Price', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_total_cost');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setFloatPositive();
        $fld->requirements()->setRange(0.1, 999999);
		
		
        if($orderType != applicationConstants::PRODUCT_FOR_SALE){
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Rental_Security', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_rental_security');
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive();
            $fld->requirements()->setRange(0, 999999);
        }
        if ($fulfilmetType != Shipping::FULFILMENT_PICKUP){
            $shipFld = $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_price_cost', $this->siteLangId) . ' [' . CommonHelper::getSystemDefaultCurrenyCode() . ']', 'counter_offer_shipping_cost');
            $fld->requirements()->setRequired(true);
            $fld->requirements()->setFloatPositive();
            $fld->requirements()->setRange(0.1, 999999);

            $frm->addHTML('', 'total_price', '<div ><span>' . Labels::getLabel('LBL_Total_Price', $this->siteLangId) . ' : </span><span>'. CommonHelper::getSystemDefaultCurrenySymbolLeft() .'</span><span class="total_price--js">' . $shipFld->value . '</span><span>'. CommonHelper::getSystemDefaultCurrenySymbolRight() .'</span></div>');
        } else {
            $frm->addHTML('', 'total_price', '<div ><span>' . Labels::getLabel('LBL_Total_Price', $this->siteLangId) . ' : </span><span>'. CommonHelper::getSystemDefaultCurrenySymbolLeft() .'</span><span class="total_price--js"></span><span>'. CommonHelper::getSystemDefaultCurrenySymbolRight() .'</span></div>');
        }
        
        
        $frm->addTextarea(Labels::getLabel('LBL_Comment', $this->siteLangId), 'counter_offer_comment');
        $frm->addHiddenField('', 'counter_offer_rfq_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Counter_Offer', $this->siteLangId));

        return $frm;
    }

    public function downloadDigitalFile(int $rfqId, int $aFileId, int $fileType, $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $rfqId) {
            FatUtility::exitWithErrorCode(404);
        }

        $reqForQuote = new RequestForQuote($rfqId);
        $rfqDetail = $reqForQuote->getRequestDetail($this->userParentId);

       

        if (empty($rfqDetail)) {
            FatUtility::exitWithErrorCode(404);
        }

        $attachFileRow = AttachedFile::getAttributesById($aFileId);

        
        /* files path[ */
  
        $folderName = '';
        switch ($fileType) {
            case AttachedFile::FILETYPE_QUOTED_DOCUMENT:
                $folderName = AttachedFile::FILETYPE_RFQ_DOCUMENT_PATH;
                break;
            case AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER:
                $folderName = AttachedFile::FILETYPE_SERVICE_DOCUMENT_PATH;
                break;
        }
        /* ] */

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('RequestForQuotes', 'RequestView', array($rfqId)));
        }

        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }

}
