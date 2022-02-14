<?php

class RfqCheckoutController extends MyAppController {
    
    private $errMessage;
    public function __construct($action) {
        parent::__construct($action);

        if (true === MOBILE_APP_API_CALL) {
            UserAuthentication::checkLogin();
        }
        if (!UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(UrlHelper::generateUrl('products', 'search'));
        }

		if (!User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer')) {
			$message = Labels::getLabel('MSG_Please_login_with_buyer_account', $this->siteLangId);
			if (FatUtility::isAjaxCall()) {
				Message::addErrorMessage($message);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
            }
			Message::addErrorMessage($message);
            FatApp::redirectUser(CommonHelper::generateUrl());
        }
        
		$userObj = new User(UserAuthentication::getLoggedUserId());
		$userInfo = $userObj->getUserInfo(array(), false, false);
		if (empty($userInfo['user_phone']) && empty($userInfo['credential_email'])) {
			if (true == SmsArchive::canSendSms()) {
				$message = Labels::getLabel('MSG_PLEASE_CONFIGURE_YOUR_EMAIL_OR_PHONE', $this->siteLangId);
			} else {
				$message = Labels::getLabel('MSG_PLEASE_CONFIGURE_YOUR_EMAIL', $this->siteLangId);
			}
			if (true === MOBILE_APP_API_CALL) {
				LibHelper::dieJsonError($message);
			}

			if (FatUtility::isAjaxCall()) {
				$json['status'] = applicationConstants::NO;
				$json['msg'] = $message;
				$json['url'] = UrlHelper::generateUrl('GuestUser', 'configureEmail');
				LibHelper::dieJsonError($json);
			}
			Message::addErrorMessage($message);
			FatApp::redirectUser(UrlHelper::generateUrl('GuestUser', 'configureEmail'));
		}
        
		$this->set('exculdeMainHeaderDiv', true);
    }

    private function isEligibleForNextStep(&$criteria = array())
    {
        if (empty($criteria)) {
            return true;
        }
        foreach ($criteria as $key => $val) {
            switch ($key) {
                case 'isUserLogged':
                    if (!UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                break;
            }
        }
        return true;
    }
	
	private function validatePaymentUrl(array $orderInfo): bool
    {
        if (empty($orderInfo)) {
            return false;
        }
        /* $invoiceValidTill = date('Y-m-d H:i:s', strtotime("+". FatUtility::int($orderInfo['rfq_quote_validity']) ." days", strtotime($orderInfo['invoice_added_on']))); */
        $invoiceValidTill = $orderInfo['rfq_quote_validity'];
        if (strtotime($invoiceValidTill) < strtotime(date('Y-m-d'))) {
            return false;
        }
        return true;
    }
	

    public function index($orderId) {
        if ($orderId =='' || strtolower($orderId) == 'undefined') {
            exit;
        }
    
        $orderInfo = $this->getOrderDetails($orderId);
        if (empty($orderInfo)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Payment_URL', $this->siteLangId));
            FatUtility::exitWithErrorCode(404);
            exit;
        }
                
        $rfqStatus = RequestForQuote::getRfqStatus($orderInfo['order_rfq_id']);
        if ($rfqStatus['rfq_status'] == RequestForQuote::REQUEST_QUOTE_VALIDITY) {
            Message::addErrorMessage(Labels::getLabel("LBL_Quote_Expired", $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('requestForQuotes', 'quotedRequests'));
        }
		if (!$this->validatePaymentUrl($orderInfo)) {
            if (User::isBuyer()) {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Payment_URL_OR_Payment_has_been_Made', $this->siteLangId));
                FatApp::redirectUser(CommonHelper::generateUrl('requestForQuotes', 'quotedRequests'));
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Payment_URL', $this->siteLangId));
                FatUtility::exitWithErrorCode(404);
                exit;
            }
        }
        
        $verficationData = SellerProduct::getProductVerificationFldsData($orderInfo['selprod_product_id'], $orderInfo['selprod_user_id']);
        
        $attchedArr = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_AGREEMENT, $orderInfo['shop_id'], 0, -1, true, 0, false);
        
        $isLoadVerificationSection = false;
        if ($orderInfo['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT && (!empty($verficationData) || (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1) && !empty($attchedArr)))) {
            $isLoadVerificationSection = true;
            if ((FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1) && !empty($attchedArr)) && 1 > $orderInfo['opd_rental_agreement_afile_id']) {
                $dataToUpdate = ['opd_rental_agreement_afile_id' => $attchedArr['afile_id']];
                $whr = array('smt' => 'opd_op_id = ?', 'vals' => array($orderInfo['opd_op_id']));
                if (!FatApp::getDb()->updateFromArray(OrderProductData::DB_TBL, $dataToUpdate, $whr)) {
                    Message::addErrorMessage(FatApp::getDb()->getError());
                    FatApp::redirectUser(CommonHelper::generateUrl('requestForQuotes', 'quotedRequests'));
                }
            }
            $this->_template->addJs('js/sign/jSignature.min.js');
            $this->_template->addJs('js/sign/modernizr.js');
        }
        
        $obj = new Extrapage();
        $this->set('orderInfo', $orderInfo);
        $this->set('pageData', $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_RIGHT_BLOCK, $this->siteLangId));
        $this->set('headerData', $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId));
        $this->set('isLoadVerificationSection', $isLoadVerificationSection);
        $this->_template->render(true, false);
    }

    public function verificationForm()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $criteria = array('isUserLogged' => true);
        $orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
        $orderInfo = $this->getOrderDetails($orderId);
        $shop_id = array();
        $attachmentArr = array();
        $count = 0;
        if ($orderInfo['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
            $attachmentArr = AttachedFile::getAttributesById($orderInfo['opd_rental_agreement_afile_id']);
        }
        $this->set('attachmentArr', $attachmentArr);
        /* ] */

        if (!$this->isEligibleForNextStep($criteria)) {
            $this->errMessage = !empty($this->errMessage) ? $this->errMessage : Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartSummary = $orderInfo['cart_summary'];
        
        $shippingAddressArr = array();
        $billingAddressArr = array();
        $shippingAddressId = $orderInfo['rfq_shipping_address_id'];
        $billingAddressId = $orderInfo['rfq_billing_address_id'];

        if ($shippingAddressId) {
            $address = new Address($shippingAddressId, $this->siteLangId);
            $shippingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }
        if ($billingAddressId) {
            $address = new Address($billingAddressId, $this->siteLangId);
            $billingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }
        
        $orderObj = new Orders();
        $orderPickUpData = '';
        $orderShippingData = '';

        $shippingData = [];
        if ($orderInfo['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP) {
            $orderPickUpData = $orderObj->getOrderPickUpData($orderId, $this->siteLangId, $orderInfo['opd_sold_or_rented']);
        }
        if ($orderInfo['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP) {
            $orderShippingData = $orderObj->getOrderShippingData($orderId, $this->siteLangId, $orderInfo['opd_sold_or_rented']);
            foreach ($orderShippingData as $data) {
                $shippingData[$data['opshipping_code']][] = $data;
            }
        }

        $verificationData = $this->getOrderVerificationData($orderId);
        $fillData = [];
        if (!empty($verificationData)) {
            foreach ($verificationData as $vData) {
                $prefix = 'textFld_';
                if ($vData['ovd_vflds_type'] == 2) {
                    $prefix = 'fileFld_';
                }
                
                $fillData[$prefix.$vData['ovd_vfld_id']] = $vData['ovd_value'];
            }
        }
        
        
        $orderNumericKey = $orderInfo['order_order_id'];
        $verificationForm = $this->getVerificationForm($orderId, $fillData, $orderNumericKey, $orderInfo);
        $verificationForm->fill($fillData);
        $vfldSortedData = $this->getSortedVerificationFldsData($orderInfo);

        $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $orderNumericKey, 0, -1, true, 0, false);
        
        
        $this->set('orderNumericId', $orderNumericKey);
        $this->set('orderId', $orderId);
        $this->set('signatureData', $signatureData);
        $this->set('cartProductData', $orderInfo);
        $this->set('vfldSortedData', $vfldSortedData);
        $this->set('verificationForm', $verificationForm);
        $this->set('cartSummary', $cartSummary);
        $this->set('fulfillmentType', $orderInfo['rfq_fulfilment_type']);
        $this->set('cartHasDigitalProduct', false);
        $this->set('cartHasPhysicalProduct', true);
        $this->set('shippingAddressId', $shippingAddressId);
        $this->set('billingAddressId', $billingAddressId);
        $this->set('billingAddressArr', $billingAddressArr);
        $this->set('shippingAddressArr', $shippingAddressArr);
        $this->set('orderPickUpData', $orderPickUpData);
        $this->set('orderShippingData', $shippingData);
        $this->_template->render(false, false);
    }

    public function setupVerificationFlds()
    {
        $post = FatApp::getPostedData();
        $orderNumericId = FatApp::getPostedData('orderNumericId', FatUtility::VAR_INT, 0);
        $orderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        
        unset($post['orderNumericId']);
        unset($post['orderId']);
        
        if (array_key_exists('is_Sign_added', $post)) {
            unset($post['is_Sign_added']);
        }
        if (array_key_exists('agreement', $post)) {
            unset($post['agreement']);
        }
        
        if (!empty($_FILES)) {
            foreach ($_FILES as $fldName => $data) {
                $fileHandlerObj = new AttachedFile();
                $vfldsId = filter_var($fldName, FILTER_SANITIZE_NUMBER_INT);
                if (!empty($data['tmp_name'])) {
                    if (filesize($_FILES[$fldName]['tmp_name']) > 10240000) { 
                        Message::addErrorMessage(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                
                    if (!$res = $fileHandlerObj->saveAttachment($_FILES[$fldName]['tmp_name'], AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderNumericId, $vfldsId, $_FILES[$fldName]['name'], -1, true, 0, 0, 0, false)) {
                        Message::addErrorMessage($fileHandlerObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
                $post['fileFld_' . $vfldsId] = '';
            }
        }
        
        foreach ($post as $key => $val) {
            $arr = explode("_", $key, 2);
            $vfldId = $arr[1];
            if (1 > $vfldId) {
                continue;
            }
      
            $vfldName = VerificationFields::getAttributesByLangId($this->siteLangId, $vfldId, 'vflds_name');
            $vfldType = VerificationFields::getAttributesById($vfldId, 'vflds_type');
            $verificationFldData = array(
                'ovd_order_id' => $orderId,
                'ovd_vfld_id' => $vfldId,
                'ovd_value' => $val,
                'ovd_vflds_type' => $vfldType,
                'ovd_vflds_name' => $vfldName,
            );
        
            if (!FatApp::getDb()->insertFromArray(Orders::DB_TBL_ORDER_VERIFICATION_FIELDS, $verificationFldData, true, array(), $verificationFldData)) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Verification_Submit_Successfully.', $this->siteLangId));
    }

    public function paymentSummary() {
		$orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
        $userId = UserAuthentication::getLoggedUserId();
        if ($orderId == '') {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invaild_Request', $this->siteLangId));
        }
		$userWalletBalance = User::getUserBalance($userId, true);
		
		if (true === MOBILE_APP_API_CALL) {
			$orderInfo = $this->getOrderDetails($orderId);
            $payFromWallet = FatApp::getPostedData('payFromWallet', Fatutility::VAR_INT, 0);
			$orderObj = new Orders();
			$walletSelectedAmount = 0;
			if ($payFromWallet == 1 && $userWalletBalance) {
				$walletSelectedAmount = min($userWalletBalance, $orderInfo['order_net_amount']);
			}
			$orderObj->updateOrderInfo($orderId, array('order_is_wallet_selected' => $isWalletSelected, 'order_wallet_amount_charge' => $walletSelectedAmount));
		}

        $criteria = array('isUserLogged' => true);
        
        $cartHasPhysicalProduct = true;
        
        if (!$this->isEligibleForNextStep($criteria)) {
            $this->errMessage = !empty($this->errMessage) ? $this->errMessage : Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->errMessage);
            }
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

		$orderInfo = $this->getOrderDetails($orderId);
        $cartSummary = $orderInfo['cart_summary'];
        
        $fulfillmentType = $orderInfo['rfq_fulfilment_type'];
        $cartType = $orderInfo['rfq_request_type'];
		
        /* Payment Methods[ */
        $splitPaymentMethodsPlugins = Plugin::getDataByType(Plugin::TYPE_SPLIT_PAYMENT_METHOD, $this->siteLangId);
        $regularPaymentMethodsPlugins = Plugin::getDataByType(Plugin::TYPE_REGULAR_PAYMENT_METHOD, $this->siteLangId);

        $codPlugInId = Plugin::getAttributesByCode('PayAtStore', 'plugin_id');
        if (array_key_exists($codPlugInId, $regularPaymentMethodsPlugins)) {
            unset($regularPaymentMethodsPlugins[$codPlugInId]);
        }
        $paymentMethods = array_merge($splitPaymentMethodsPlugins, $regularPaymentMethodsPlugins);
        /* ] */
		$orderObj = new Orders();
        $shippingAddressArr = array();
        $billingAddressArr = array();
        $shippingAddressId = $orderInfo['rfq_shipping_address_id'];
        $billingAddressId = $orderInfo['rfq_billing_address_id'];

        if ($shippingAddressId) {
            $address = new Address($shippingAddressId, $this->siteLangId);
            $shippingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }
        if ($billingAddressId) {
            $address = new Address($billingAddressId, $this->siteLangId);
            $billingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }
        
        
        

        $orderPickUpData = [];
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $orderPickUpData = $orderObj->getOrderPickUpData($orderId, $this->siteLangId, $cartType);
        }

        if (false === MOBILE_APP_API_CALL) {
            $WalletPaymentForm = $this->getWalletPaymentForm($this->siteLangId);
            $confirmForm = $this->getConfirmFormWithNoAmount($this->siteLangId);

            if ((FatUtility::convertToType($userWalletBalance, FatUtility::VAR_FLOAT) > 0) && $cartSummary['cartWalletSelected'] && $cartSummary['orderNetAmount'] > 0) {
                $WalletPaymentForm->addFormTagAttribute('action', UrlHelper::generateUrl('WalletPay', 'Charge', array($orderId)));
                $WalletPaymentForm->fill(array('order_id' => $orderId));
                $WalletPaymentForm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
                $WalletPaymentForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
            }

            if ($cartSummary['orderNetAmount'] <= 0) {
                $confirmForm->addFormTagAttribute('action', UrlHelper::generateUrl('ConfirmPay', 'Charge', array($orderId)));
                $confirmForm->fill(array('order_id' => $orderId));
                $confirmForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Order', $this->siteLangId));
            }
		}

        if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $orderShippingData = $orderObj->getOrderShippingData($orderId, $this->siteLangId, $cartType);
            foreach ($orderShippingData as $data) {
                $shippingData[$data['opshipping_code']][] = $data;
            }
        }

        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected']) {
            $orderObj->updateOrderInfo($orderId, array('order_pmethod_id' => 0));
        }
        
        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($orderId);
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);
        
        $this->set('verificationFldsData', $verificationFldsData);
        $this->set('paymentMethods', $paymentMethods);
        $this->set('userWalletBalance', $userWalletBalance);
        $this->set('cartSummary', $cartSummary);
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('cartHasDigitalProduct', false);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('orderOrderId', (isset($orderInfo['order_order_id'])) ? $orderInfo['order_order_id'] : 0);
        
        $this->set('excludePaymentGatewaysArr', applicationConstants::getExcludePaymentGatewayArr());
        if (false === MOBILE_APP_API_CALL) {
            $this->set('orderInfo', $orderInfo);
            $this->set('WalletPaymentForm', $WalletPaymentForm);
            $this->set('confirmForm', $confirmForm);
        }

        $this->set('canUseWalletForPayment', PaymentMethods::canUseWalletForPayment());
        $this->set('billingAddressArr', $billingAddressArr);
        $this->set('orderPickUpData', $orderPickUpData);
        $this->set('shippingAddressArr', $shippingAddressArr);
        $this->set('orderId', $orderId);
        
		if (true === MOBILE_APP_API_CALL) {
            $this->set('products', $cartProducts);
            $this->set('orderType', $orderInfo['order_type']);
            $this->_template->render();
        }
        $this->_template->render(false, false);
    }

    public function paymentTab($order_id, $plugin_id) {
        $plugin_id = FatUtility::int($plugin_id);
        if (!$plugin_id) {
            FatUtility::dieWithError(Labels::getLabel("MSG_Invalid_Request", $this->siteLangId));
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            /* Message::addErrorMessage( Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId) );
              FatUtility::dieWithError( Message::getHtml() ); */
            FatUtility::dieWithError(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
        }
        $user_id = UserAuthentication::getLoggedUserId();

        
        $orderInfo = $this->getOrderDetails($order_id);
        if (!$orderInfo) {
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        $methodCode = Plugin::getAttributesById($plugin_id, 'plugin_code');
		$this->plugin = PluginHelper::callPlugin($methodCode, [$this->siteLangId], $error, $this->siteLangId);
        if (false === $this->plugin) {
            FatUtility::dieWithError($error);
        }
        $paymentMethod = $this->plugin->getSettings();

        $frm = '';
        if (in_array(strtolower($methodCode), ['cashondelivery', 'payatstore']) && isset($paymentMethod["otp_verification"]) && 0 < $paymentMethod["otp_verification"]) {
            $userObj = new User($user_id);
            $userData = $userObj->getUserInfo([], false, false);
            $userDialCode = $userData['user_dial_code'];
            $phoneNumber = $userData['user_phone'];
            $canSendSms = (!empty($phoneNumber) && !empty($userDialCode) && SmsArchive::canSendSms(SmsTemplate::COD_OTP_VERIFICATION));
            $this->set('canSendSms', $canSendSms);
            $this->set('userData', $userData);
            $frm = $this->getOtpForm();
        }

        $frm = $this->getPaymentTabForm($this->siteLangId, $methodCode, $frm);
        $controller = $methodCode . 'Pay';
        $frm->setFormTagAttribute('action', UrlHelper::generateUrl($controller, 'charge', array($order_id)));
        $frm->setFormTagAttribute('data-method', $methodCode);
        $frm->setFormTagAttribute('data-external', UrlHelper::generateUrl($controller, 'getExternalLibraries'));

        $frm->fill(
                array(
                    'order_type' => $orderInfo['order_type'],
                    'order_id' => $order_id,
                    'plugin_id' => $plugin_id,
                )
        );

        $this->set('orderId', $order_id);
        $this->set('pluginId', $plugin_id);
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentMethod', $paymentMethod);
        $this->set('frm', $frm);
        /* Partial Payment is not allowed, Wallet + COD, So, disabling COD in case of Partial Payment Wallet Selected. [ */
        if (in_array(strtolower($methodCode), ['cashondelivery', 'payatstore'])) {
            $cartSummary = $orderInfo['cart_summary'];
            $user_id = UserAuthentication::getLoggedUserId();
            $userWalletBalance = User::getUserBalance($user_id, true);

            if (!$cartSummary['isCodValidForNetAmt']) {
                $str = Labels::getLabel('MSG_Sorry_{COD}_is_not_available_on_this_order.', $this->siteLangId) . ' <br/>' . Labels::getLabel('MSG_{COD}_is_available_on_payable_amount_between_{MIN}_and_{MAX}', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['plugin_name'], $str);
                $str = str_replace('{min}', CommonHelper::displayMoneyFormat(FatApp::getConfig("CONF_MIN_COD_ORDER_LIMIT")), $str);
                $str = str_replace('{max}', CommonHelper::displayMoneyFormat(FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT")), $str);
                FatUtility::dieWithError($str);
            }

            if ($cartSummary['cartWalletSelected'] && $userWalletBalance < $cartSummary['orderNetAmount']) {
                $str = Labels::getLabel('MSG_Wallet_can_not_be_used_along_with_{COD}', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['plugin_name'], $str);
                FatUtility::dieWithError($str);
                //$this->set('error', $str );
            }
        }
        /* ] */
        $this->_template->render(false, false, '', false, false);
    }

    public function walletSelection() {
        $orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
		$isWalletSelected = FatApp::getPostedData('payFromWallet', FatUtility::VAR_INT, 0);
		$userWalletBalance = User::getUserBalance(UserAuthentication::getLoggedUserId(), true);
		$orderInfo = $this->getOrderDetails($orderId);
		
		$orderObj = new Orders();
		$walletSelectedAmount = 0;
		if ($isWalletSelected == 1 && $userWalletBalance) {
			$walletSelectedAmount = min($userWalletBalance, $orderInfo['order_net_amount']);
		}
		
		if(!$orderObj->updateOrderInfo($orderId, array('order_is_wallet_selected' => $isWalletSelected, 'order_wallet_amount_charge' => $walletSelectedAmount)) ) {
               echo  "not working"; die();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    /* Used through payment summary api to rid off session functionality in case of APP calling. */
    public function confirmOrder() {
        $order_type = FatApp::getPostedData('order_type', FatUtility::VAR_INT, 0);
        $plugin_id = FatApp::getPostedData('plugin_id', FatUtility::VAR_INT, 0);

        $order_id = FatApp::getPostedData("order_id", FatUtility::VAR_STRING, "");
		$orderInfo = $this->getOrderDetails($order_id);
		
		if (empty($orderInfo)) {
			$this->errMessage = Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId);
			LibHelper::dieJsonError($this->errMessage);
		}

        $user_id = UserAuthentication::getLoggedUserId();
        $cartSummary = $orderInfo['cart_summary'];
        $userWalletBalance = FatUtility::convertToType(User::getUserBalance($user_id, true), FatUtility::VAR_FLOAT);
        $orderNetAmount = isset($cartSummary['orderNetAmount']) ? FatUtility::convertToType($cartSummary['orderNetAmount'], FatUtility::VAR_FLOAT) : 0;

        if (0 < $plugin_id) {
            $paymentMethodRow = Plugin::getAttributesById($plugin_id);
            $isActive = $paymentMethodRow['plugin_active'];
            $pmethodCode = $paymentMethodRow['plugin_code'];
            $pmethodIdentifier = $paymentMethodRow['plugin_identifier'];

            if (!$paymentMethodRow || $isActive != applicationConstants::ACTIVE) {
                $this->errMessage = Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId);
                LibHelper::dieJsonError($this->errMessage);
            }
        }

        if (!empty($paymentMethodRow) && in_array(strtolower($pmethodCode), ['cashondelivery', 'payatstore']) && $cartSummary['cartWalletSelected'] && $userWalletBalance < $orderNetAmount) {
            $str = Labels::getLabel('MSG_Wallet_can_not_be_used_along_with_{COD}', $this->siteLangId);
            $str = str_replace('{cod}', $pmethodIdentifier, $str);
            LibHelper::dieJsonError($str);
        }

        if (true === MOBILE_APP_API_CALL) {
            $paymentUrl = '';
            $sendToWeb = 1;
            if (0 < $plugin_id) {
                $controller = $pmethodCode . 'Pay';
                $paymentUrl = UrlHelper::generateFullUrl($controller, 'charge', array($order_id));
            }
            if ($cartSummary['cartWalletSelected'] && $userWalletBalance >= $orderNetAmount) {
                $sendToWeb = $plugin_id = 0;
                $paymentUrl = UrlHelper::generateFullUrl('WalletPay', 'charge', array($order_id));
            }
            if (empty($paymentUrl)) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_Please_Select_Payment_Method', $this->siteLangId));
            }
            $this->set('sendToWeb', $sendToWeb);
            $this->set('orderPayment', $paymentUrl);
        }

        /* confirmOrder function is called for both wallet payments and for paymentgateway selection as well. */
        $criteria = array('isUserLogged' => true);
        $fulfillmentType = Shipping::FULFILMENT_SHIP;
        if (!$this->isEligibleForNextStep($criteria)) {
            $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }

        if ($cartSummary['cartWalletSelected'] && $userWalletBalance >= $orderNetAmount && !$plugin_id) {
            if (true === MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php');
            exit;
        }

        $post = FatApp::getPostedData();

        if (!$paymentMethodRow || $isActive != applicationConstants::ACTIVE) {
            $this->errMessage = Labels::getLabel("LBL_Invalid_Payment_method,_Please_contact_Webadmin.", $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }

        if (false === MOBILE_APP_API_CALL && in_array(strtolower($pmethodCode), ['cashondelivery', 'payatstore']) && FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '' && FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '') != '')) {
            if (!CommonHelper::verifyCaptcha()) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_That_captcha_was_incorrect', $this->siteLangId));
            }
        }

        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected'] && !$plugin_id) {
            $frm = $this->getWalletPaymentForm($this->siteLangId);
        } else {
            $frm = $this->getPaymentTabForm($this->siteLangId);
        }

        $post = $frm->getFormDataFromArray($post);
        if (!isset($post['order_id']) || $post['order_id'] == '') {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }

        $orderObj = new Orders();
		$order_id = $post['order_id'];
        $orderInfo = $this->getOrderDetails($order_id);
	
        if (!$orderInfo) {
            $this->errMessage = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }
		$cartSummary = $orderInfo['cart_summary'];
        if ($cartSummary['cartWalletSelected'] && $cartSummary['orderPaymentGatewayCharges'] == 0) {
            $this->errMessage = Labels::getLabel('MSG_Try_to_pay_using_wallet_balance_as_amount_for_payment_gateway_is_not_enough.', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }

        if ($cartSummary['orderPaymentGatewayCharges'] == 0 && $plugin_id) {
            $this->errMessage = Labels::getLabel('MSG_Amount_for_payment_gateway_must_be_greater_than_zero.', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }

        if ($plugin_id) {
            $_SESSION['cart_order_id'] = $order_id;
            $_SESSION['order_type'] = $order_type;
            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $plugin_id));
        }

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getPaymentTabForm($langId, $paymentMethodCode = '', $externalFrm = '') 
    {
        $frm = $externalFrm;
        if (empty($externalFrm)) {
            $frm = new Form('frmPaymentTabForm');
        }

        $frm->setFormTagAttribute('id', 'frmPaymentTabForm');

        if (in_array(strtolower($paymentMethodCode), ["cashondelivery", "payatstore"])) {
            CommonHelper::addCaptchaField($frm);
        }

        $frm->addHiddenField('', 'order_type');
        $frm->addHiddenField('', 'order_id');
        $frm->addHiddenField('', 'plugin_id');
        if (empty($externalFrm)) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $langId));
        }
        return $frm;
    }

    private function getWalletPaymentForm($langId) {
        $frm = new Form('frmWalletPayment');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getConfirmFormWithNoAmount($langId) {
        $frm = new Form('frmConfirmForm');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    

    public function getFinancialSummary() {
        $orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
        $orderDetails = $this->getOrderDetails($orderId);
        $cartSummary = $orderDetails['cart_summary'];
        unset($orderDetails['cart_summary']);
        $orderObj = new Orders();
        $address = $orderObj->getOrderAddresses($orderId);
		$orderNetAmt = $cartSummary['orderNetAmount'];
		$netAmount = CommonHelper::displayMoneyFormat($orderNetAmt);

		if($orderDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP){
            $this->set('shippingAddress', $address[Orders::BILLING_ADDRESS_TYPE]);
        }else{
            $this->set('shippingAddress', $address[Orders::SHIPPING_ADDRESS_TYPE]);
        }

        $this->set('product', $orderDetails);
        $this->set('cartSummary', $cartSummary);
        
        $this->set('hasPhysicalProd', true);
        $data = $this->_template->render(false, false, 'rfq-checkout/get-financial-summary.php', true, false);
		
		$this->set('netAmount', $netAmount);
        $this->set('data', $data);
        $this->_template->render(false, false, 'json-success.php', false, false);
		
	}

    public function resendOtp() {
        $userId = UserAuthentication::getLoggedUserId();
        $userObj = new User($userId);
        $userData = $userObj->getUserInfo([], false, false);
        $userDialCode = $userData['user_dial_code'];
        $phoneNumber = $userData['user_phone'];

        $canSendSms = (!empty($phoneNumber) && !empty($userDialCode) && SmsArchive::canSendSms(SmsTemplate::COD_OTP_VERIFICATION));

        $otp = '';
        if (true == $canSendSms) {
            if (false == $userObj->resendOtp()) {
                FatUtility::dieJsonError($userObj->getError());
            }
            $data = $userObj->getOtpDetail();
            $otp = $data['upv_otp'];
        }

        if (empty($otp)) {
            $min = pow(10, User::OTP_LENGTH - 1);
            $max = pow(10, User::OTP_LENGTH) - 1;
            $otp = mt_rand($min, $max);
        }

        if (false === $userObj->prepareUserVerificationCode($userData['credential_email'], $userId . '_' . $otp)) {
            FatUtility::dieWithError($userObj->getError());
        }

        $replace = [
            'user_name' => $userData['user_name'],
            'otp' => $otp,
            'credential_email' => $userData['credential_email'],
        ];

        $email = new EmailHandler();
        if (false === $email->sendCodOtpVerification($this->siteLangId, $replace)) {
            FatUtility::dieWithError($userObj->getError());
        }

        $this->set('msg', Labels::getLabel('MSG_OTP_SENT!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function validateOtp() {
        $user_id = UserAuthentication::getLoggedUserId();
        $userObj = new User($user_id);
        $userData = $userObj->getUserInfo([], false, false);
        $userDialCode = $userData['user_dial_code'];
        $phoneNumber = $userData['user_phone'];

        $canSendSms = (!empty($phoneNumber) && !empty($userDialCode) && SmsArchive::canSendSms(SmsTemplate::COD_OTP_VERIFICATION));

        $verified = false;
        if (true == $canSendSms) {
            $this->validateOtpApi(0, false);
            $verified = true;
        }

        if (false === $verified) {
            $db = FatApp::getDb();
            $db->startTransaction();

            $otpFrm = $this->getOtpForm();
            $post = $otpFrm->getFormDataFromArray(FatApp::getPostedData());
            if (false === $post) {
                LibHelper::dieJsonError(current($otpFrm->getValidationErrors()));
            }

            if (true === MOBILE_APP_API_CALL) {
                if (User::OTP_LENGTH != strlen($post['upv_otp'])) {
                    LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_OTP', $this->siteLangId));
                }
                $otp = $post['upv_otp'];
            } else {
                if (!is_array($post['upv_otp']) || User::OTP_LENGTH != count($post['upv_otp'])) {
                    LibHelper::dieJsonError(Labels::getLabel('MSG_INVALID_OTP', $this->siteLangId));
                }
                $otp = implode("", $post['upv_otp']);
            }

            if (!$userObj->verifyUserEmailVerificationCode($user_id . '_' . $otp)) {
                $db->rollbackTransaction();
                LibHelper::dieJsonError($userObj->getError());
            }
            $db->commitTransaction();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

	private function getOrderDetails(string $orderId): array
    {
        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_id = op.op_order_id', 'o');
        $srch->joinTable(RequestForQuote::DB_TBL, 'INNER JOIN', 'rfq.rfq_id = o.order_rfq_id', 'rfq');
        $srch->joinTable(Invoice::DB_TBL, 'INNER JOIN', 'invoice.invoice_order_id = op.op_order_id', 'invoice');
        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', 'shop.shop_id = op.op_shop_id', 'shop');
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addMultipleFields(array('order_id' ,'order_type' ,'order_order_id' ,'order_rfq_id' ,'selprod_product_id' ,'selprod_user_id' ,'shop_id','opd_sold_or_rented','opd_rental_agreement_afile_id','opd_op_id','rfq_shipping_address_id','rfq_billing_address_id','rfq_status','invoice_added_on','rfq_fulfilment_type','order_net_amount' ,'rfq_request_type', 'order_is_wallet_selected', 'op_actual_shipping_charges', 'order_tax_charged', 'order_site_commission', 'order_wallet_amount_charge', 'op_product_tax_options', 'selprod_id', 'op_shop_name', 'selprod_type', 'selprod_title', 'op_product_name', 'op_qty', 'opd_rental_start_date', 'opd_rental_end_date', 'opd_rental_type', 'opd_rental_price', 'opd_rental_security', 'rfq_quote_validity', 'op_rounding_off'));
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addCondition('order_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('op_order_id', '=', $orderId);
        $srch->addCondition('order_is_rfq', '=', applicationConstants::YES);
        $srch->addCondition('order_payment_status', 'IN', Orders::getUnpaidStatus());
        $srch->addCondition('op_status_id', '!=', FatApp::getConfig('CONF_DEFAULT_CANCEL_ORDER_STATUS'));

        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);
        if (empty($orderDetail)) {
            return [];
        }
        $isWalletSelected = $orderDetail['order_is_wallet_selected'];
        $walletCharges = $orderDetail['order_wallet_amount_charge'];
     
        $orderObj = new Orders();
        $cartSummary = array(
            'cartTotal' => $orderDetail['order_net_amount'] - $orderDetail['op_actual_shipping_charges'] - $orderDetail['order_tax_charged'] - ($orderDetail['opd_rental_security'] * $orderDetail['op_qty']) + $orderDetail['op_rounding_off'],
            'shippingTotal' => $orderDetail['op_actual_shipping_charges'],
            'originalShipping' => $orderDetail['op_actual_shipping_charges'],
            'cartTaxTotal' => $orderDetail['order_tax_charged'],
            'cartDiscounts' => 0,
            'cartVolumeDiscount' => 0,
            'cartRewardPoints' => 0,
            'cartWalletSelected' => $isWalletSelected,
            'siteCommission' => $orderDetail['order_site_commission'],
            'orderNetAmount' => $orderDetail['order_net_amount'],
            'WalletAmountCharge' => $walletCharges,
            'isCodEnabled' => 0,
            'isCodValidForNetAmt' => 0,
            'orderPaymentGatewayCharges' => $orderDetail['order_net_amount'] - $walletCharges,
            'netChargeAmount' => $orderDetail['order_net_amount'] - $walletCharges,
            'taxOptions' => $orderDetail['op_product_tax_options'],
            'prodTaxOptions' => $orderDetail['op_product_tax_options'],
            'total_paid_amount' => $orderObj->getOrderPaymentPaid($orderId),
            'total_amount' => $orderDetail['order_net_amount'],
            'total_security' => $orderDetail['opd_rental_security'] * $orderDetail['op_qty'],
        );
        $orderDetail['cart_summary'] = $cartSummary;
        return $orderDetail;
    }
    
    private function getVerificationForm($orderId = '', $verificationData = [], $orderNumerickey = 0, $orderInfo)
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmCheckoutVerification');
        $data = $this->getFilteredVerificationFldsData(true, $orderInfo);

        foreach ($data as $val) {
            switch ($val['vflds_type']) {
                case VerificationFields::FLD_TYPE_TEXTBOX:
                    $fld = $frm->addTextBox($val['vflds_name'], 'textFld_' . $val['vflds_id']);
                    if ($val['vflds_required']) {
                        $fld->requirement->setRequired(true);
                    }
                    break;
                case VerificationFields::FLD_TYPE_FILE:
                    $fldKey = 'fileFld_' . $val['vflds_id'];
                    $fld = $frm->addFileUpload($val['vflds_name'], $fldKey, array('accept' => 'image/*,.pdf', 'enctype' => "multipart/form-data", "id" => $fldKey));

                    if (array_key_exists($fldKey, $verificationData) && !empty($orderId)) {
                        $downloadUrl = UrlHelper::generateUrl('Checkout', 'downloadAttachedFile', array($orderId, $val['vflds_id']));
                        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $orderNumerickey, $val['vflds_id']);
                        $hideFld = "";
                        $hideParent = "display:none;";
                        if ($file_row['afile_id'] > 0) {
                            $hideFld = "display:none;";
                            $hideParent = "";
                        }

                        $fld->htmlAfterField = '<label for=' . $fldKey . ' style="' . $hideFld . '">
                        <span>' . Labels::getLabel('LBL_Choose_File', $siteLangId) . ' </span></label>
                        <div class="uploaded-files ' . $fldKey . '" style="' . $hideParent . '">';
                        if ($file_row['afile_id'] > 0) {
                            $fld->htmlAfterField .= '<span class="file-name ">' . $file_row['afile_name'] . '
                            <a class="delete" href="javascript:void(0);" onClick="removeUploadedFile(this, ' . $file_row['afile_id'] . ')" data-id="' . $fldKey . '">
                                <svg class="svg" width="16px" height="16px">
                                    <use xlink:href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#remove" href="' . CONF_WEBROOT_URL . 'images/retina/sprite.svg#remove"> </use> </svg> </a></span>';
                        } else {
                            if ($val['vflds_required']) {
                                $fld->requirement->setRequired(true);
                            }
                        }
                        $fld->htmlAfterField .= '</div>';
                    } else {
                        $fld->htmlAfterField = '<label for=' . $fldKey . '><span>' . Labels::getLabel('LBL_Choose_File', $siteLangId) . '</span></label> <div class="uploaded-files ' . $fldKey . '" style="display:none;"> </div>';

                        if ($val['vflds_required']) {
                            $fld->requirement->setRequired(true);
                        }
                    }

                    break;
            }
        }
        $frm->addHiddenField('', 'orderId', $orderInfo['order_id']);
        $frm->addHiddenField('', 'orderNumericId', $orderInfo['order_order_id']);
        $frm->addSubmitButton(Labels::getLabel('LBL_Continue', $siteLangId), 'btn_submit', Labels::getLabel('LBL_Submit', $siteLangId));
        return $frm;
    }
    
    public function getSortedVerificationFldsData($orderInfo)
    {
        $filteredVfldData = $this->getFilteredVerificationFldsData(false, $orderInfo);
        $newArr = [];
        foreach ($filteredVfldData as $value) {
            $type = ($value['vflds_type'] == VerificationFields::FLD_TYPE_FILE) ? "f" : "t";
            $arr = array_key_exists($value['vflds_id'], $newArr) ? $newArr[$value['vflds_id']] : [];
            if (!in_array($value['product_id'], $arr)) {
                $newArr[$type . $value['vflds_id']][] = $value['product_id'];
            }
        }

        $secondArr = $newArr;
        $finalArray = $usedKeys = array();
        foreach ($newArr as $mainKey => $data) {
            $key = $mainKey;
            if (!in_array($key, $usedKeys)) {
                foreach ($secondArr as $subKey => $seconData) {
                    if ($data == $seconData && $mainKey != $subKey) {
                        $key = $key . '_' . $subKey;
                        unset($secondArr[$subKey]);
                        $usedKeys[] = $subKey;
                    }
                }
                $finalArray[$key] = $data;
            }
        }
        return $finalArray;
    }
    
    private function getFilteredVerificationFldsData($uniqueFlds = false, array $orderInfo) : array
    {
        $verificationFlds = [];
        if (isset($orderInfo['selprod_type']) && $orderInfo['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
            return [];
        }
        if ($data = SellerProduct::getProductVerificationFldsData($orderInfo['selprod_product_id'], $orderInfo['selprod_user_id'])) {
            foreach ($data as $val) {
                if ($uniqueFlds) {
                    $verificationFlds[$val['vflds_id']] = $val;
                } else {
                    $verificationFlds[] = $val;
                }
            }
        }
        return $verificationFlds;
    }
    
    public function store()
    {   
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
        
        $post = FatApp::getPostedData();
        $orderId = FatApp::getPostedData('record_id', FatUtility::VAR_INT, 0);
        if (empty($post) || 1 > $orderId) {
            $message = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($message);
            }
            Message::addErrorMessage($message);
            FatApp::redirectUser(UrlHelper::generateUrl());
        }

        $image_base64 = base64_decode($post['imgData'][1]);
        $arr = array(
            'afile_type' => AttachedFile::FILETYPE_SIGNATURE_IMAGE,
            'afile_record_id' => $orderId,
            'afile_record_subid' => 0,
            'afile_lang_id' => $this->siteLangId,
            'afile_screen' => 0,
            'afile_unique' => 1,
            'afile_display_order' => 0,
        );
        $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $orderId, 0, -1, true, 0, false);
        if (!empty($signatureData)) {
            $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_SIGNATURE_IMAGE_PATH . $signatureData['afile_physical_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $imageName = AttachedFile::uploadTempImage($image_base64, "signature", $arr, true);
        if (empty($imageName)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $this->set('msg', Labels::getLabel('LBL_Signature_Uploaded', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    
    private function getOrderVerificationData($orderId) : array
    {
        $orderObj = new Orders();
        $oVfldsSrch = $orderObj->getOrderVerificationDataSrchObj($orderId, false);
        $oVfldsSrch->doNotCalculateRecords();
        $oVfldsSrch->doNotLimitRecords();
        /* $oVfldsSrch->addMultipleFields(array('ovd_order_id', 'ovd_order_id', 'ovd_vflds_type', 'ovd_vflds_name', 'ovd_value', 'optvf_selprod_id', 'optvf_op_id', 'ovd_vfld_id')); */
        $rs = $oVfldsSrch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }
    
    public function downloadDigitalFile(int $recordId, int $aFileId, int $fileType, $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $recordId) {
            FatUtility::exitWithErrorCode(404);
        }

        $attachFileRow = AttachedFile::getAttributesById($aFileId);
        $folderName = AttachedFile::FILETYPE_SHOP_AGREEMENT_PATH;

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(CommonHelper::generateUrl('RequestForQuotes', 'RequestView', array($recordId)));
        }

        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }
    
    public function removeUploadedFile(int $fileId, int $recordId)
    {
        $attachObj = new AttachedFile();
        if (!$attachObj->deleteFile(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $recordId, $fileId)) {
            Message::addErrorMessage($attachObj->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->_template->render(false, false, 'json-success.php');
    }
    
    
}