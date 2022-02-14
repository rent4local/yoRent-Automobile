<?php

class GdprRequestsController extends LoggedUserController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
		$srch = new UserGdprRequestSearch();
        $srch->addCondition('ureq_user_id', '=', $this->userParentId);
        $srch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $srch->addOrder('ureq_id', 'DESC');
        $rs = $srch->getResultSet();
        $requestRow = (array) FatApp::getDb()->fetch($rs);
        $this->set('requestRow', $requestRow);
        $this->_template->render(false, false);
    }
    
    public function downloadRequestData(int $requestType)
    {
        
        switch($requestType) {
            case UserGdprRequest::REQUEST_TYPE_GDPR_REQUEST: 
                $this->downloadGdprRequest();
                break;
            case UserGdprRequest::REQUEST_TYPE_PERSONAL_INFO: 
                $this->downloadPersonalInfo();
                break;
            case UserGdprRequest::REQUEST_TYPE_SHOP_INFO: 
                $this->downloadShopData();
            case UserGdprRequest::REQUEST_TYPE_SOCIAL_PLATFORM: 
                $this->downloadSocialPlatformData();    
                break; 
            case UserGdprRequest::REQUEST_TYPE_PICKUP_ADDRESS: 
                $this->downloadPickupAddressData();    
                break;     
            case UserGdprRequest::REQUEST_TYPE_SALES: 
                $this->downloadSaleData();
                break;    
            case UserGdprRequest::REQUEST_TYPE_PURCHASE: 
                $this->downloadPurchaseData();
                break;     
        }
        
    }
    
    private function downloadGdprRequest()
    {
        $srch = new UserGdprRequestSearch();
        $srch->addCondition('ureq_user_id', '=', $this->userParentId);
        $srch->addCondition('ureq_deleted', '=', applicationConstants::NO);
        $srch->addOrder('ureq_id', 'DESC');
        $rs = $srch->getResultSet();
        $requestRow = (array) FatApp::getDb()->fetchAll($rs);
        $arr = array(
            Labels::getLabel('LBL_request_id', $this->siteLangId),
            Labels::getLabel('LBL_user_id', $this->siteLangId),
            Labels::getLabel('LBL_Type', $this->siteLangId),
            Labels::getLabel('LBL_Purpose', $this->siteLangId),
            Labels::getLabel('LBL_Status', $this->siteLangId),
            Labels::getLabel('LBL_Date', $this->siteLangId),
            Labels::getLabel('LBL_Approved_Date', $this->siteLangId)
        );
        $exportSheet = [];
        $requestTypeArr = UserGdprRequest::getUserRequestTypesArr($this->siteLangId);
        $statusArr = UserGdprRequest::getUserRequestStatusesArr($this->siteLangId);
        array_push($exportSheet, $arr);
        if (!empty($requestRow)) {
            foreach($requestRow as $row) {
                $rowArr = [
                    $row['ureq_id'],
                    $row['ureq_user_id'],
                    (isset($requestTypeArr[$row['ureq_type']])) ? $requestTypeArr[$row['ureq_type']] : "",
                    $row['ureq_purpose'],
                    (isset($statusArr[$row['ureq_status']])) ? $statusArr[$row['ureq_status']] : "",
                    FatDate::format($row['ureq_date'], true),
                    FatDate::format($row['ureq_approved_date'], true)
                ];
                array_push($exportSheet, $rowArr);
            }
        }
        
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Requests.csv', ',');
        die();
    }
    
    private function downloadPersonalInfo()
    {
        $userObj = new User($this->userParentId);
        $srch = $userObj->getUserSearchObj();
        /* $srch->addMultipleFields(array('u.*')); */
        $rs = $srch->getResultSet();
        $data = (array) FatApp::getDb()->fetch($rs, 'user_id');
        $arr = array(
            Labels::getLabel('LBL_user_id', $this->siteLangId),
            Labels::getLabel('LBL_Name', $this->siteLangId),
            Labels::getLabel('LBL_Phone', $this->siteLangId),
            Labels::getLabel('LBL_Profile_Info', $this->siteLangId),
            Labels::getLabel('LBL_Registration_Date', $this->siteLangId),
            Labels::getLabel('LBL_Username', $this->siteLangId),
            Labels::getLabel('LBL_Email', $this->siteLangId),
            Labels::getLabel('LBL_Active', $this->siteLangId),
            Labels::getLabel('LBL_Verified', $this->siteLangId),
            Labels::getLabel('LBL_Profile_Image', $this->siteLangId)
        );
        
        $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
        
        $exportSheet = [];
        array_push($exportSheet, $arr);
        if (!empty($data)) {
            $rowArr = [
                $data['user_id'],
                $data['user_name'],
                $data['user_dial_code'] .' '. $data['user_phone'],
                $data['user_profile_info'],
                FatDate::format($data['user_regdate'], true),
                $data['credential_username'],
                $data['credential_email'],
                (isset($yesNoArr[$data['credential_active']])) ? $yesNoArr[$data['credential_active']] : "",
                (isset($yesNoArr[$data['credential_verified']])) ? $yesNoArr[$data['credential_verified']] : "",
                UrlHelper::generateFullFileUrl('Image', 'user', array($data['user_id']))
            ];
            array_push($exportSheet, $rowArr);
        }
        
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Personal_Info.csv', ',');
        die();
    }
    
    private function downloadShopData()
    {
        $srch = new SearchBase(Shop::DB_TBL, 's');
        $srch->doNotCalculateRecords();
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 's_l.shoplang_shop_id = s.shop_id', 's_l');
        $srch->joinTable(ShopSpecifics::DB_TBL, 'LEFT JOIN', 'ss_shop_id = shop_id', 'ss');
        $srch->joinTable(Countries::DB_TBL, 'LEFT JOIN', 'shop_country_id = country_id', 'c');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'countrylang_country_id = country_id AND countrylang_lang_id = '. $this->siteLangId, 'clang');
        $srch->joinTable(States::DB_TBL, 'LEFT JOIN', 'state_country_id = state_id', 'st');
        $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'statelang_state_id = state_id AND statelang_lang_id = '. $this->siteLangId, 'slang');
        $srch->addCondition('shop_user_id', '=', $this->userParentId);
        $srch->addMultipleFields(['s.*', 's_l.*', 'IFNULL(clang.country_name, c.country_code) as country_name', 'IFNULL(slang.state_name, st.state_identifier) as state_name', 'ss.*']);
        $rs = $srch->getResultSet();
        $shopData = FatApp::getDb()->fetchAll($rs);
        
        $addSrch = new SearchBase(User::DB_TBL_USR_RETURN_ADDR, 'tura');
        $addSrch->joinTable(User::DB_TBL_USR_RETURN_ADDR_LANG, 'LEFT OUTER JOIN', 'tura_l.uralang_user_id = tura.ura_user_id', 'tura_l');
        $addSrch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'urc.country_id = tura.ura_country_id', 'urc');
        $addSrch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'urs.state_id = tura.ura_state_id', 'urs');
        $addSrch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'urc_l.countrylang_country_id = tura.ura_country_id and urc_l.countrylang_lang_id = ' . $this->siteLangId, 'urc_l');
        $addSrch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'urs_l.statelang_state_id = tura.ura_state_id and urs_l.statelang_lang_id = ' . $this->siteLangId, 'urs_l');
        $addSrch->addCondition('ura_user_id', '=', $this->userParentId);
        $addSrch->addMultipleFields(['tura.*', 'tura_l.*', 'IFNULL(urc_l.country_name, urc.country_code) as ura_country_name', 'IFNULL(urs_l.state_name, urs.state_identifier) as ura_state_name']);
        
        $addRs = $addSrch->getResultSet();
        $returnAddressArr = FatApp::getDb()->fetchAll($addRs, 'uralang_lang_id');
        
        $arr = array(
            Labels::getLabel('LBL_Shop_id', $this->siteLangId),
            Labels::getLabel('LBL_User_id', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Identifier', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Url', $this->siteLangId),
            Labels::getLabel('LBL_Display_Status', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Name', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Country', $this->siteLangId),
            Labels::getLabel('LBL_Shop_State', $this->siteLangId),
            Labels::getLabel('LBL_Shop_City', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Postal_Code', $this->siteLangId),
            Labels::getLabel('LBL_Address_Line_1', $this->siteLangId),
            Labels::getLabel('LBL_Address_Line_2', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Phone', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Created_on', $this->siteLangId),
            Labels::getLabel('LBL_Free_Shipping_above', $this->siteLangId),
            Labels::getLabel('LBL_Cancellation_Age(Sale)', $this->siteLangId),
            Labels::getLabel('LBL_Return_Age(Sale)', $this->siteLangId),
            Labels::getLabel('LBL_Time_Sloat_for_pickup', $this->siteLangId),
            Labels::getLabel('LBL_Fulfillment_Type', $this->siteLangId),
            Labels::getLabel('LBL_Late_Charges_Enabled', $this->siteLangId),
            Labels::getLabel('LBL_Government_Information_On_Invoices', $this->siteLangId),
            Labels::getLabel('LBL_Contact_Person', $this->siteLangId),
            Labels::getLabel('LBL_Longitude', $this->siteLangId),
            Labels::getLabel('LBL_Latitude', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Description', $this->siteLangId),
            Labels::getLabel('LBL_Additional_Info', $this->siteLangId),
            Labels::getLabel('LBL_Payment_Policy', $this->siteLangId),
            Labels::getLabel('LBL_Delivery_Policy', $this->siteLangId),
            Labels::getLabel('LBL_Refund_Policy', $this->siteLangId),
            Labels::getLabel('LBL_Seller_Info', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Country', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_State', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_City', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Postcode', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Phone', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Name', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Line_1', $this->siteLangId),
            Labels::getLabel('LBL_Return_Address_Line_2', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Logo', $this->siteLangId),
            Labels::getLabel('LBL_Shop_Banner', $this->siteLangId),
            Labels::getLabel('LBL_Rental_Agreement', $this->siteLangId)
        );
        $exportSheet = [];
        array_push($exportSheet, $arr);
        $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
        $fullfillmentTypeArr = Shipping::getFulFillmentArr($this->siteLangId);
        if (!empty($shopData)) {
            $sellerAttachments = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_AGREEMENT, $shopData[0]['shop_id'], 0, -1, true, 0, false);
            $agreementUrl = (!empty($sellerAttachments)) ? UrlHelper::generateFullFileUrl('Seller', 'downloadDigitalFile', [$sellerAttachments["afile_record_id"], $sellerAttachments['afile_id'], AttachedFile::FILETYPE_SHOP_AGREEMENT]) : "";
            
            foreach ($shopData as $shop) {
                $address = (isset($returnAddressArr[$shop['shoplang_lang_id']])) ? $returnAddressArr[$shop['shoplang_lang_id']] : [];
                $shopRow = [
                    $shop['shop_id'],
                    $shop['shop_user_id'],
                    $shop['shop_identifier'],
                    UrlHelper::generateFullUrl('shops', 'view', [$shop['shop_id']]),
                    (isset($yesNoArr[$shop['shop_supplier_display_status']])) ? $yesNoArr[$shop['shop_supplier_display_status']] : "", 
                    $shop['shop_name'],
                    $shop['country_name'],
                    $shop['state_name'],
                    $shop['shop_city'],
                    $shop['shop_postalcode'],
                    $shop['shop_address_line_1'],
                    $shop['shop_address_line_2'],
                    $shop['shop_phone'],
                    FatDate::format($shop['shop_created_on'], true),
                    $shop['shop_free_ship_upto'],
                    $shop['shop_cancellation_age'],
                    $shop['shop_return_age'],
                    $shop['shop_pickup_interval'],
                    (isset($fullfillmentTypeArr[$shop['shop_fulfillment_type']])) ? $fullfillmentTypeArr[$shop['shop_fulfillment_type']] : "",
                    (isset($yesNoArr[$shop['shop_is_enable_late_charges']])) ? $yesNoArr[$shop['shop_is_enable_late_charges']] : "",
                    $shop['shop_invoice_codes'],
                    $shop['shop_contact_person'],
                    $shop['shop_lng'],
                    $shop['shop_lat'],
                    $shop['shop_description'],
                    $shop['shop_additional_info'],
                    $shop['shop_payment_policy'],
                    $shop['shop_delivery_policy'],
                    $shop['shop_refund_policy'],
                    $shop['shop_seller_info'],
                    (isset($address['ura_country_name'])) ? $address['ura_country_name'] : "",
                    (isset($address['ura_state_name'])) ? $address['ura_state_name'] : "",
                    (isset($address['ura_city'])) ? $address['ura_city'] : "",
                    (isset($address['ura_zip'])) ? $address['ura_zip'] : "",
                    (isset($address['ura_phone'])) ? $address['ura_phone'] : "",
                    (isset($address['ura_name'])) ? $address['ura_name'] : "",
                    (isset($address['ura_address_line_1'])) ? $address['ura_address_line_1'] : "",
                    (isset($address['ura_address_line_2'])) ? $address['ura_address_line_2'] : "",
                    UrlHelper::generateFullFileUrl('Image', 'shopLogo', array($shop['shop_id'], $shop['shoplang_lang_id'], 'ORIGINAL', 0, true)),
                    UrlHelper::generateFullFileUrl('Image', 'shopBanner', array($shop['shop_id'], $shop['shoplang_lang_id'], 'ORIGINAL' ,0, true)),
                    $agreementUrl
                ];
                array_push($exportSheet, $shopRow);
            }
        }
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Shop_Data.csv', ','); die();
    }
    
    private function downloadSocialPlatformData()
    {
        $srch = new SearchBase(SocialPlatform::DB_TBL, 'sp');
        $srch->joinTable(SocialPlatform::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . SocialPlatform::DB_TBL_LANG_PREFIX . 'splatform_id = sp.' . SocialPlatform::tblFld('id'), 'sp_l');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', $this->userParentId);
        $srch->addOrder('splatform_id');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        
        $arr = array(
            Labels::getLabel('LBL_Platform_id', $this->siteLangId),
            Labels::getLabel('LBL_User_Id', $this->siteLangId),
            Labels::getLabel('LBL_Platform_Identifier', $this->siteLangId),
            Labels::getLabel('LBL_Platform_Title', $this->siteLangId),
            Labels::getLabel('LBL_Platform_URL', $this->siteLangId),
            Labels::getLabel('LBL_Platform_Active', $this->siteLangId)
            
        );
        
        $exportSheet = [];
        array_push($exportSheet, $arr);
        $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
        
        if (!empty($records)) {
            foreach ($records as $data) {
                $shopRow = [
                    $data['splatform_id'],
                    $data['splatform_user_id'],
                    $data['splatform_identifier'],
                    $data['splatform_title'],
                    $data['splatform_url'],
                    (isset($yesNoArr[$data['splatform_active']])) ? $yesNoArr[$data['splatform_active']] : ""
                ];
                array_push($exportSheet, $shopRow);
            }
        }
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Shop_Social_Platform_data.csv', ','); die();
    }
    
    private function downloadPickupAddressData()
    {
        $shopDetails = Shop::getAttributesByUserId($this->userParentId, null, false);
        $address = new Address(0, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);
        
        $arr = array(
            Labels::getLabel('LBL_Name', $this->siteLangId),
            Labels::getLabel('LBL_Address_Label', $this->siteLangId),
            Labels::getLabel('LBL_Address_Line_1', $this->siteLangId),
            Labels::getLabel('LBL_Address_Line_2', $this->siteLangId),
            Labels::getLabel('LBL_Country', $this->siteLangId),
            Labels::getLabel('LBL_State', $this->siteLangId),
            Labels::getLabel('LBL_City', $this->siteLangId),
            Labels::getLabel('LBL_Postcode', $this->siteLangId),
            Labels::getLabel('LBL_Phone_Number', $this->siteLangId)
        );
        
        $exportSheet = [];
        array_push($exportSheet, $arr);
        
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $addRow = [
                    $address['addr_name'],
                    $address['addr_title'],
                    $address['addr_address1'],
                    $address['addr_address2'],
                    $address['country_name'],
                    $address['state_name'],
                    $address['addr_city'],
                    $address['addr_zip'],
                    $address['addr_phone']
                ];
                
                array_push($exportSheet, $addRow);
            }
        }
        
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Shop_Pickup_Address_data.csv', ','); die();
    }
    
    private function downloadSaleData()
    {
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_TAX .', opcharge_amount, 0)) as tax_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_SHIPPING .', opcharge_amount, 0)) as shipping_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT .', opcharge_amount, 0)) as reward_point_amount'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinOrderUser();
        $srch->joinSellerProducts();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $srch->addOrder("op_id", "DESC");
        
        $addonSrch = new OrderProductSearch(0, true, true);
        $addonSrch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $addonSrch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        $addonSrch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $addonSrch->doNotCalculateRecords();
        $addonSrch->addMultipleFields(['IFNULL(SUM(op_qty * op_unit_price + (opd_rental_security * op_qty) + (IF(op_tax_collected_by_seller > 0, IFNULL(tax_amount, 0) , 0 )) + IFNULL(shipping_amount, 0) + IFNULL(reward_point_amount, 0)), 0) as addonAmount', 'op_attached_op_id']);
        $addonSrch->addGroupBy('op_attached_op_id');
        $addonSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $addonSrch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        
        $srch->joinTable('(' . $addonSrch->getQuery() . ')', 'LEFT JOIN', 'op.op_id = addonQry.op_attached_op_id', 'addonQry');
        $srch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);
        $srch->addMultipleFields(
            array('order_id', 'order_status', 'order_payment_status', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_id', 'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_id', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'orderstatus_color_class', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'opd.*', 'op_status_id', '(op_qty * op_unit_price + (opd_rental_security * op_qty) + (IF(op_tax_collected_by_seller > 0, tax_amount , 0 )) + IF(opshipping_by_seller_user_id > 0, shipping_amount, 0) + reward_point_amount + addonQry.addonAmount) as vendorAmount', 'order_pmethod_id', 'addonQry.addonAmount as addon_amount', 'user_name as buyer_name')
        );
        $srch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        
        $arr = array(
            Labels::getLabel('LBL_Invoice_Number', $this->siteLangId),
            Labels::getLabel('LBL_Customer_Name', $this->siteLangId),
            Labels::getLabel('LBL_Order_Date', $this->siteLangId),
            Labels::getLabel('LBL_Product_Name', $this->siteLangId),
            Labels::getLabel('LBL_Order_Status', $this->siteLangId),
            Labels::getLabel('LBL_Order_Type', $this->siteLangId),
            Labels::getLabel('LBL_Order_Amount', $this->siteLangId),
            Labels::getLabel('LBL_Addon_Amount', $this->siteLangId),
        );
        
        $exportSheet = [];
        array_push($exportSheet, $arr);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $orderRow = [
                    $order['op_invoice_number'],
                    $order['buyer_name'],
                    FatDate::format($order['order_date_added'], true),
                    $order['op_selprod_title'],
                    $order['orderstatus_name'],
                    ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) ? Labels::getLabel('LBL_Sale', $this->siteLangId) : Labels::getLabel('LBL_Rent', $this->siteLangId),
                    CommonHelper::displayMoneyFormat($order['vendorAmount']),
                    CommonHelper::displayMoneyFormat($order['addon_amount']),
                ];
                array_push($exportSheet, $orderRow);
            }
        }
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Sale_data.csv', ','); die();
    }
    
    private function downloadPurchaseData()
    {
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_TAX .', opcharge_amount, 0)) as tax_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_SHIPPING .', opcharge_amount, 0)) as shipping_amount', 'SUM(IF(opcharge_type = '. OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT .', opcharge_amount, 0)) as reward_point_amount'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->joinSellerUser();
        $srch->joinSellerProducts();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $this->userParentId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $srch->addOrder("op_id", "DESC");
        
        $addonSrch = new OrderProductSearch(0, true, true);
        $addonSrch->joinTable('(' . $qryOtherCharges . ')', 'LEFT  JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $addonSrch->addCondition('order_user_id', '=', $this->userParentId);
        $addonSrch->addCondition('order_is_rfq', '=', applicationConstants::NO);
        $addonSrch->doNotCalculateRecords();
        $addonSrch->addMultipleFields(['IFNULL(SUM(op_qty * op_unit_price + (opd_rental_security * op_qty) + (IF(op_tax_collected_by_seller > 0, IFNULL(tax_amount, 0) , 0 )) + IFNULL(shipping_amount, 0) + IFNULL(reward_point_amount, 0)), 0) as addonAmount', 'op_attached_op_id']);
        $addonSrch->addGroupBy('op_attached_op_id');
        $addonSrch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $addonSrch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        
        $srch->joinTable('(' . $addonSrch->getQuery() . ')', 'LEFT JOIN', 'op.op_id = addonQry.op_attached_op_id', 'addonQry');
        $srch->addCondition('opd_product_type', '=', SellerProduct::PRODUCT_TYPE_PRODUCT);
        $srch->addMultipleFields(
            array('order_id', 'order_status', 'order_payment_status', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number', 'IFNULL(op_selprod_title, op_product_identifier) as op_selprod_title', 'IFNULL(op_product_name, op_product_identifier) as op_product_name', 'op_id', 'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_id', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'orderstatus_color_class', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'opd.*', 'op_status_id', '(op_qty * op_unit_price + (opd_rental_security * op_qty) + tax_amount + shipping_amount + reward_point_amount + addonQry.addonAmount) as vendorAmount', 'order_pmethod_id', 'addonQry.addonAmount as addon_amount', 'user_name as seller_name')
        );
        $srch->addCondition('opd.opd_sold_or_rented', '=', applicationConstants::PRODUCT_FOR_RENT);
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        
        $arr = array(
            Labels::getLabel('LBL_Invoice_Number', $this->siteLangId),
            Labels::getLabel('LBL_Seller_Name', $this->siteLangId),
            Labels::getLabel('LBL_Order_Date', $this->siteLangId),
            Labels::getLabel('LBL_Product_Name', $this->siteLangId),
            Labels::getLabel('LBL_Order_Status', $this->siteLangId),
            Labels::getLabel('LBL_Order_Type', $this->siteLangId),
            Labels::getLabel('LBL_Order_Amount', $this->siteLangId)
        );
        
        $exportSheet = [];
        array_push($exportSheet, $arr);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $orderRow = [
                    $order['op_invoice_number'],
                    $order['seller_name'],
                    FatDate::format($order['order_date_added'], true),
                    $order['op_selprod_title'],
                    $order['orderstatus_name'],
                    ($order['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_SALE) ? Labels::getLabel('LBL_Sale', $this->siteLangId) : Labels::getLabel('LBL_Rent', $this->siteLangId),
                    CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($order, 'netamount'))
                ];
                array_push($exportSheet, $orderRow);
            }
        }
        CommonHelper::convertToCsv($exportSheet, 'GDPR_Sale_data.csv', ','); die();
    }
}