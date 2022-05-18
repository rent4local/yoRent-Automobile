<?php

class CheckoutController extends MyAppController
{

    private $cartObj;
    private $errMessage;

    public function __construct($action)
    {
        parent::__construct($action);

        if (true === MOBILE_APP_API_CALL) {
            UserAuthentication::checkLogin();
        }
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            FatApp::redirectUser(UrlHelper::generateUrl('Cart'));
        }

        /*if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $geoAddress = Address::getYkGeoData();
            if (!array_key_exists('ykGeoLat', $geoAddress) || $geoAddress['ykGeoLat'] == '' || !array_key_exists('ykGeoLng', $geoAddress) || $geoAddress['ykGeoLng'] == '') {
                $this->errMessage = Labels::getLabel('MSG_PLEASE_CONFIGURE_YOUR_LOCATION', $this->siteLangId);
                LibHelper::exitWithError($this->errMessage, false, true);
                FatApp::redirectUser(UrlHelper::generateUrl('Cart'));
            }
        }*/

        if (UserAuthentication::isGuestUserLogged()) {
            $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
            if (!$user_is_buyer) {
                $this->errMessage = Labels::getLabel('MSG_Please_login_with_buyer_account', $this->siteLangId);
                Message::addErrorMessage($this->errMessage);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(UrlHelper::generateUrl('Cart'));
            }
        } else {
            /* $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
            if (!$user_is_buyer) {
                $errMsg = Labels::getLabel('MSG_Please_login_with_buyer_account_to_add_products_to_cart', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    LibHelper::dieJsonError($errMsg);
                }
                Message::addErrorMessage($errMsg);
                if (FatUtility::isAjaxCall()) {
                    FatUtility::dieWithError(Message::getHtml());
                }
                FatApp::redirectUser(UrlHelper::generateUrl('cart'));
            } */

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
        }


        $this->cartObj = new Cart(UserAuthentication::getLoggedUserId(), $this->siteLangId, $this->app_user['temp_user_id']);
        if (1 > $this->cartObj->getCartBillingAddress()) {
            $this->cartObj->setCartBillingAddress();
        }

        if ($this->cartObj->hasPhysicalProduct() && 1 > $this->cartObj->getCartShippingAddress()) {
            $this->cartObj->setShippingAddressSameAsBilling();
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
                    if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    /* if (UserAuthentication::isUserLogged()){
                        $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
                        if (!$user_is_buyer) {
                            $this->errMessage = Labels::getLabel('MSG_Please_login_with_buyer_account.', $this->siteLangId);
                            Message::addErrorMessage($this->errMessage);
                            return false;
                        }
                    } */

                    break;
                case 'hasProducts':
                    if (!$this->cartObj->hasProducts()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Your_cart_seems_to_be_empty,_Please_try_after_reloading_the_page.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'hasStock':
                    /* to check that product is temporary hold[ */
                    $cart_user_id = Cart::getCartUserId();
                    $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
                    //$srch->addCondition('pshold_user_id', '!=', $cart_user_id);

                    /* ] */

                    $cartProducts = $this->cartObj->getProducts($this->siteLangId);
                    //CommonHelper::printArray($cartProducts); exit;
                    foreach ($cartProducts as $product) {
                        if (!$product['in_stock']) {
                            $stock = false;
                            $key = false;
                            $this->errMessage = Labels::getLabel('MSG_Products_are_out_of_stock', $this->siteLangId);
                            Message::addErrorMessage($this->errMessage);
                            return false;
                            break;
                        }
                        if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                            $opId = $product['extendOrder'];
                            $orderData = OrderProduct::getAttributesById($opId, ['op_status_id', 'op_return_qty', 'op_qty']);
                            if (empty($orderData)) {
                                $this->cartObj->clear();
                                $this->cartObj->updateUserCart();

                                $this->errMessage = Labels::getLabel('MSG_This_Order_Is_Not_Applicable_For_Extend_Rental', $this->siteLangId);
                                Message::addErrorMessage($this->errMessage);
                                return false;
                                break;
                            }

                            $orderStatus = $orderData['op_status_id'];
                            $availQty = $orderData['op_qty'] - $orderData['op_return_qty'];
                            if ($orderStatus != FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS", FatUtility::VAR_INT, 0) || $product['quantity'] > $availQty) {
                                $this->cartObj->clear();
                                $this->cartObj->updateUserCart();
                                $this->errMessage = Labels::getLabel('MSG_This_Order_Is_Not_Applicable_For_Extend_Rental', $this->siteLangId);
                                Message::addErrorMessage($this->errMessage);
                                return false;
                                break;
                            }
                        }


                        if ($product['is_batch'] && !empty($product['products'])) {
                            foreach ($product['products'] as $pgproduct) {
                                $tempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id']);
                                $availableStock = $pgproduct['selprod_stock'] - $tempHoldStock;
                                $userTempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id'], $cart_user_id, $product['prodgroup_id'], true);
                                if ($availableStock < ($product['quantity'] - $userTempHoldStock)) {
                                    $key = false;
                                    $productName = (isset($pgproduct['selprod_title']) && $pgproduct['selprod_title'] != '') ? $pgproduct['selprod_title'] : $pgproduct['name'];

                                    $this->errMessage = str_replace('{product-name}', $productName, Labels::getLabel('MSG_{product-name}_is_temporary_out_of_stock_or_hold_by_other_customer', $this->siteLangId));
                                    Message::addErrorMessage($this->errMessage);
                                    return false;
                                }
                            }
                        } else {
                            if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                                $availableStock = $product['sprodata_rental_stock'];
                                $userTempHoldStock = ProductRental::rentalTempHoldStockCount($product['selprod_id'], $cart_user_id, $product['rentalStartDate'], $product['rentalEndDate']);
                            } else {
                                $tempHoldStock = Product::tempHoldStockCount($product['selprod_id']);
                                $availableStock = $product['selprod_stock'] - $tempHoldStock;
                                $userTempHoldStock = Product::tempHoldStockCount($product['selprod_id'], $cart_user_id, 0, true);
                            }

                            if ($availableStock < ($product['quantity'] - $userTempHoldStock) && $product['sellerProdType'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                                $key = false;
                                $productName = (isset($product['selprod_title']) && $product['selprod_title'] != '') ? $product['selprod_title'] : $product['name'];
                                $this->errMessage = str_replace('{product-name}', $productName, Labels::getLabel('MSG_{product-name}_is_temporary_out_of_stock_or_hold_by_other_customer', $this->siteLangId));
                                Message::addErrorMessage($this->errMessage);
                                return false;
                            }
                        }

                        /* $srch = new SearchBase('tbl_product_stock_hold');
                          $srch->doNotCalculateRecords();
                          $srch->addOrder('pshold_id', 'ASC');
                          $srch->addCondition( 'pshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true );
                          $srch->addCondition( 'pshold_selprod_id', '=', $product['selprod_id'] );
                          $srch->addOrder('pshold_id');
                          $srch->setPageNumber(1);
                          $srch->setPageSize(1);
                          $rs = $srch->getResultSet();
                          $stockHoldRow = FatApp::getDb()->fetch($rs);
                          if( $stockHoldRow && ($stockHoldRow['pshold_user_id'] != $cart_user_id) && ($product['selprod_stock'] - $stockHoldRow['pshold_selprod_stock']) < $product['quantity'] ){
                          $key = false;
                          $productName = ( isset($product['selprod_title']) && $product['selprod_title'] != '' ) ? $product['selprod_title'] : $product['name'];
                          Message::addErrorMessage($productName . " is temporary out of stock or hold by other customer, please try after some time.");
                          return false;
                          } */
                        /* if( array_key_exists($product['selprod_id'], $rows ) && ($product['selprod_stock'] - $rows[$product['selprod_id']]['pshold_selprod_stock'] < $product['quantity'] ) ){
                          $key = false;
                          Message::addErrorMessage("Product Stock is currently hold by some other user, please try after some time.");
                          return false;
                          } */
                    }
                    break;
                case 'hasBillingAddress':
                    if (!$this->cartObj->getCartBillingAddress()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Billing_Address_is_not_provided.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'hasShippingAddress':
                    if (!$this->cartObj->getCartShippingAddress()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Shipping_Address_is_not_provided.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'isProductShippingMethodSet':
                    if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                        return true;
                    }

                    if (!$this->cartObj->isProductShippingMethodSet()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
                case 'isProductPickUpAddrSet':
                    if (!$this->cartObj->isProductPickUpAddrSet()) {
                        $key = false;
                        $this->errMessage = Labels::getLabel('MSG_Pickup_Method_is_not_selected_on_products_in_cart.', $this->siteLangId);
                        Message::addErrorMessage($this->errMessage);
                        return false;
                    }
                    break;
            }
        }
        return true;
    }

    public function index($appParam = '', $appLang = '1', $appCurrency = '1')
    {
        if ($appParam == 'api') {
            $langId = FatUtility::int($appLang);
            if (0 < $langId) {
                $languages = Language::getAllNames();
                if (array_key_exists($langId, $languages)) {
                    setcookie('defaultSiteLang', $langId, time() + 3600 * 24 * 10, CONF_WEBROOT_URL);
                }
            }

            $currencyId = FatUtility::int($appCurrency);
            $currencyObj = new Currency();
            if (0 < $currencyId) {
                $currencies = Currency::getCurrencyAssoc($this->siteLangId);
                if (array_key_exists($currencyId, $currencies)) {
                    setcookie('defaultSiteCurrency', $currencyId, time() + 3600 * 24 * 10, CONF_WEBROOT_URL);
                }
            }
            commonhelper::setAppUser();
            FatApp::redirectUser(UrlHelper::generateUrl('checkout', 'index'));
        }

        $criteria = array('hasProducts' => true, 'hasStock' => true);
        if (!$this->isEligibleForNextStep($criteria)) {
            FatApp::redirectUser(UrlHelper::generateUrl('cart'));
        }
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $cartOrderData = Cart::getCartData($loggedUserId);
        $cartOrderData = !empty($cartOrderData) ? json_decode($cartOrderData, true) : [];
        $fulfillmentType = 0;
        if (isset($cartOrderData['shopping_cart']['checkout_type'])) {
            $fulfillmentType = $cartOrderData['shopping_cart']['checkout_type'];
        }
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId, false);
        if ($fulfillmentType == Shipping::FULFILMENT_SHIP && $cartSummary['shipProductsCount'] == 0) {
            FatApp::redirectUser(UrlHelper::generateUrl('cart'));
        }
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP && $cartSummary['pickUpProductsCount'] == 0) {
            FatApp::redirectUser(UrlHelper::generateUrl('cart'));
        }
        
        $obj = new Extrapage();
        $headerData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId);

        $address = new Address($this->cartObj->getCartShippingAddress(), $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);

        $this->_template->addJs('js/sign/jSignature.min.js');
        $this->_template->addJs('js/sign/modernizr.js');
        
        $_SESSION['referer_page_url'] = UrlHelper::getCurrUrl();

        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_RIGHT_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);
        $this->set('addresses', $addresses);
        $this->set('headerData', $headerData);
        $this->set('cartType', $this->cartObj->getCartType());
        $this->_template->render(true, false);
    }

    public function loadLoginDiv()
    {
        $this->_template->render(false, false);
    }

    public function login()
    {
        $socialLoginApis = Plugin::getDataByType(Plugin::TYPE_SOCIAL_LOGIN, $this->siteLangId);
        $loginFormData = array(
            'loginFrm' => $this->getLoginForm(),
            'guestLoginFrm' => $this->getGuestUserForm($this->siteLangId),
            'siteLangId' => $this->siteLangId,
            'showSignUpLink' => true,
            'socialLoginApis' => $socialLoginApis,
            'onSubmitFunctionName' => 'setUpLogin'
        );
        $this->set('loginFormData', $loginFormData);

        $cPageSrch = ContentPage::getSearchObject($this->siteLangId);
        $cPageSrch->addCondition('cpage_id', '=', FatApp::getConfig('CONF_TERMS_AND_CONDITIONS_PAGE', FatUtility::VAR_INT, 0));
        $cpage = FatApp::getDb()->fetch($cPageSrch->getResultSet());
        if (!empty($cpage) && is_array($cpage)) {
            $termsAndConditionsLinkHref = UrlHelper::generateUrl('Cms', 'view', array($cpage['cpage_id']));
        } else {
            $termsAndConditionsLinkHref = 'javascript:void(0)';
        }

        $signUpFrm = $this->getRegistrationForm(false);
        $signUpFrm->addHiddenField('', 'isCheckOutPage', 1);

        $signUpFormData = array(
            'frm' => $signUpFrm,
            'siteLangId' => $this->siteLangId,
            'showLogInLink' => false,
            'onSubmitFunctionName' => 'setUpRegisteration',
            'termsAndConditionsLinkHref' => $termsAndConditionsLinkHref,
        );

        $this->set('signUpFormData', $signUpFormData);
        $this->_template->render(false, false);
    }

    public function addresses()
    {
        $criteria = array('isUserLogged' => true);
        $cartObj = new Cart();
        if (!$this->isEligibleForNextStep($criteria)) {
            $this->set('redirectUrl', UrlHelper::generateUrl('GuestUser', 'LoginForm'));
            Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $addressFrm = $this->getUserAddressForm($this->siteLangId);
        $address = new Address(0, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        $cartHasPhysicalProduct = false;
        if ($cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $cart_products = $this->cartObj->getProducts($this->siteLangId);
        if (count($cart_products) == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $selected_billing_address_id = $cartObj->getCartBillingAddress();
        $selected_shipping_address_id = $cartObj->getCartShippingAddress();
        $fulfillmentType = $cartObj->getCartCheckoutType();

        $this->set('selected_billing_address_id', $selected_billing_address_id);
        $this->set('selected_shipping_address_id', $selected_shipping_address_id);
        $this->set('fulfillmentType', $fulfillmentType);

        $isShippingSameAsBilling = $cartObj->getShippingAddressSameAsBilling();
        $this->set('isShippingSameAsBilling', $isShippingSameAsBilling);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('addresses', $addresses);
        $this->set('stateId', 0);
        $this->set('addressFrm', $addressFrm);
        $this->set('checkoutAddressFrm', $this->getCheckoutAddressForm($this->siteLangId));

        $addressType = FatApp::getPostedData('address_type', FatUtility::VAR_INT, 0);
        $this->set('addressType', $addressType);
        $this->_template->render(false, false);
    }

    public function loadBillingShippingAddress()
    {
        $cartObj = new Cart();
        $addressId = $cartObj->getCartShippingAddress();

        $hasPhysicalProduct = $this->cartObj->hasPhysicalProduct();
        $this->set('hasPhysicalProduct', $hasPhysicalProduct);

        if (!$hasPhysicalProduct) {
            $addressId = $cartObj->getCartBillingAddress();
        }

        $address = new Address($addressId, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
        $this->set('defaultAddress', $addresses);
        $this->_template->render(false, false);
    }

    public function setUpAddressSelection()
    {
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', UrlHelper::generateUrl('GuestUser', 'LoginForm'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $shipping_address_id = FatApp::getPostedData('shipping_address_id', FatUtility::VAR_INT, 0);

        $billing_address_id = FatApp::getPostedData('billing_address_id', FatUtility::VAR_INT, 0);
        $isShippingSameAsBilling = FatApp::getPostedData('isShippingSameAsBilling', FatUtility::VAR_INT, 0);

        // Validate cart has products and has stock.
        //$this->cartObj = new Cart();

        $hasProducts = $this->cartObj->hasProducts();
        $hasStock = $this->cartObj->hasStock();

        if ((!$hasProducts) || (!$hasStock)) {
            $this->errMessage = Labels::getLabel('MSG_Cart_seems_to_be_empty_or_products_are_out_of_stock.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', UrlHelper::generateUrl('cart'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $hasPhysicalProduct = $this->cartObj->hasPhysicalProduct();

        if (1 > $billing_address_id) {
            $this->errMessage = Labels::getLabel('MSG_Please_select_Billing_address.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($hasPhysicalProduct && 1 > $shipping_address_id) {
            $this->errMessage = Labels::getLabel('MSG_Please_select_shipping_address.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('loadAddressDiv', true);
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        /* setup billing address[ */
        $address = new Address($billing_address_id);
        $billingAddressDetail = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        if (!$billingAddressDetail) {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Billing_Address.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->cartObj->setCartBillingAddress($billingAddressDetail['addr_id']);

        /* ] */

        if ($hasPhysicalProduct && $shipping_address_id) {
            if ($isShippingSameAsBilling) {
                $this->cartObj->setShippingAddressSameAsBilling();
                $shipping_address_id = $billing_address_id;
            }

            $address = new Address($shipping_address_id);
            $shippingAddressDetail = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
            if (!$shippingAddressDetail) {
                $this->errMessage = Labels::getLabel('MSG_Invalid_Shipping_Address.', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($this->errMessage);
                }
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }
            $this->cartObj->setCartShippingAddress($shippingAddressDetail['addr_id']);
        }

        if (!$isShippingSameAsBilling) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
        }

        if (!$hasPhysicalProduct) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
            $this->cartObj->unsetCartShippingAddress();
        }

        $this->set('hasPhysicalProduct', $hasPhysicalProduct);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel('MSG_Address_Selection_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shippingSummary()
    {
        $criteria = array('isUserLogged' => true);
        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            } else {
                Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId));
                $this->errMessage = Message::getHtml();
            }
            if (true === MOBILE_APP_API_CALL) {
                $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                FatUtility::dieJsonError($this->errMessage);
            }
            FatUtility::dieWithError($this->errMessage);
        }

        if (true === MOBILE_APP_API_CALL) {
            $fulfillmentType = FatApp::getPostedData('fulfillmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
        } else {
            $fulfillmentType = $this->cartObj->getCartCheckoutType();
        }

        $this->cartObj->setCartCheckoutType($fulfillmentType);
        $cartProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        $cartType = $this->cartObj->getCartType();

        if (count($cartProducts) == 0) {
            $this->errMessage = Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $hasPhysicalProd = $this->cartObj->hasPhysicalProduct();
        if (!$hasPhysicalProd) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
            $this->cartObj->unsetCartShippingAddress();
        }


        $template = 'checkout/shipping-summary-inner.php';
        if ($cartType == applicationConstants::PRODUCT_FOR_RENT || $cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $template = 'checkout/shipping-summary-inner-rental.php';
        }
        $shippingRates = [];
        $orderShippingData = '';

        switch ($fulfillmentType) {
            case Shipping::FULFILMENT_PICKUP:
                if ($cartType == applicationConstants::PRODUCT_FOR_RENT || $cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                    $shippingRates = $this->cartObj->getSelPickAddrDetails();
                    $template = 'checkout/shipping-summary-pickup-rental.php';
                } else {
                    $shippingRates = $this->cartObj->getSellPickupOptions($cartProducts, $cartType);
                    $template = 'checkout/shipping-summary-pickup.php';
                }
                break;
            case Shipping::FULFILMENT_SHIP:
                $shippingRates = $this->cartObj->getShippingOptions($cartType);
                if (!empty($_SESSION['order_id'])) {
                    $order = new Orders();
                    $orderShippingData = $order->getOrderShippingData($_SESSION['order_id'], $this->siteLangId);
                }
                break;
        }

        if (!$hasPhysicalProd) {
            $selected_shipping_address_id = $this->cartObj->getCartBillingAddress();
        } else {
            $selected_shipping_address_id = $this->cartObj->getCartShippingAddress();
        }

        $address = new Address($selected_shipping_address_id, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
        $this->_template->addJs('js/sign/jSignature.min.js');
        $this->_template->addJs('js/sign/modernizr.js');

        $this->set('cartSummary', $this->cartObj->getCartFinancialSummary($this->siteLangId));
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('addresses', $addresses);
        $this->set('products', $cartProducts);
        $this->set('shippingRates', $shippingRates);
        $this->set('hasPhysicalProd', $hasPhysicalProd);
        $this->set('orderShippingData', $orderShippingData);
        $this->set('cart_type', $cartType);

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $this->_template->render(false, false, $template);
    }

    public function getCarrierServicesList($product_key, $carrier_id = 0)
    {
        if (empty($product_key)) {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }
        $this->Cart = new Cart(UserAuthentication::getLoggedUserId());
        $carrierList = $this->Cart->getCarrierShipmentServicesList($product_key, $carrier_id, $this->siteLangId);
        if (false == $carrierList) {
            FatUtility::dieJsonError($this->Cart->getError());
        }

        $json = array('status' => 1, 'isCarriersFound' => 0);
        $isCarriersFound = 0;
        $html = $this->_template->render(false, false, 'checkout/shipping-api-carriers-services-not-found.php', true);
        if (isset($carrierList) && count($carrierList) > 1) {
            $json['isCarriersFound'] = 1;
            $this->set('options', $carrierList);
            $html = $this->_template->render(false, false, '', true);
        }
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $json['html'] = $html;
        die(json_encode($json));
    }

    /* public function setUpShippingMethod()
      {
      $this->cartObj->removeProductPickUpAddresses();
      $post = FatApp::getPostedData();
      $cartType = $this->cartObj->getCartType();

      $shippingServices = isset($post['shipping_services']) ? $post['shipping_services'] : [];
      $this->cartObj->setselectedShipping($shippingServices);

      $cartProducts = $this->cartObj->getProducts($this->siteLangId);
      $shippingRates = $this->cartObj->getShippingRates();
      //echo '<pre>'; print_r($shippingRates); echo '</pre>'; exit;

      if (false == $shippingRates) {
      $message = Labels::getLabel('MSG_Shipping_rates_are_not_available', $this->siteLangId);
      LibHelper::exitWithError($message, true);
      }

      $selectedShippingMethods = [];
      $shipProducts = [];

      $basketProducts = $this->cartObj->getBasketProducts($this->siteLangId);
      $shippingServices = isset($post['shipping_services']) ? $post['shipping_services'] : [];
      if ($cartType != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
      foreach ($shippingServices as $prodIdCobination => $rateId) {
      if (empty($rateId)) {
      $message = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart_3', $this->siteLangId);
      LibHelper::exitWithError($message, true);
      }

      if (!array_key_exists($prodIdCobination, $shippingRates)) {
      $message = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart_4', $this->siteLangId);
      LibHelper::exitWithError($message, true);
      }

      if (!array_key_exists($rateId, $shippingRates[$prodIdCobination])) {
      $message = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
      LibHelper::exitWithError($message, true);
      }

      $amt = 0;
      $productArr = explode('_', $prodIdCobination);
      if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productArr[] = $productArr[1];
      }

      foreach ($productArr as $selProdId) {
      foreach ($basketProducts as $cartKey => $product) {
      if ($product['selprod_id'] != $selProdId) {
      continue;
      }
      if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productKey = date('Y-m-d', strtotime($product['rentalStartDate'])) . '_' . $selProdId;
      if ($productKey != $prodIdCobination) {
      continue;
      }
      }
      $amt = $amt + ($product['quantity'] * $product['theprice']);
      }
      }

      $selectedShippingMethods[$prodIdCobination]['totalAmount'] = $amt;
      $selectedShippingMethods[$prodIdCobination]['rates'] = $shippingRates[$prodIdCobination][$rateId];
      }

      // break down shipping amount in proportional to each product price
      foreach ($shippingServices as $prodIdCobination => $rateId) {
      $shippingAmount[$prodIdCobination] = 0;
      $totalAmount = $selectedShippingMethods[$prodIdCobination]['totalAmount'];

      $counter = 1;
      $productArr = explode('_', $prodIdCobination);
      if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productArr[] = $productArr[1];
      }
      $productArrUnique = array_unique($productArr);
      $prodCount = count($productArrUnique);
      foreach ($productArrUnique as $selProdId) {
      //$productOccuranceCount = count(array_keys($productArr, $selProdId));
      //$shippingRates[$prodIdCobination][$rateId]['cost'] = $shippingRates[$prodIdCobination][$rateId]['cost'] * $productOccuranceCount;
      foreach ($basketProducts as $cartKey => $product) {
      if ($product['selprod_id'] != $selProdId) {
      continue;
      }
      $productKey = $selProdId;
      if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productKey = date('Y-m-d', strtotime($product['rentalStartDate'])) . '_' . $selProdId;
      if ($productKey != $prodIdCobination) {
      continue;
      }
      }

      if ($prodCount > 0 && $counter == $prodCount) {
      $shipProducts[$productKey]['cost'] = $shippingRates[$prodIdCobination][$rateId]['cost'] - $shippingAmount[$prodIdCobination];
      } else {
      $amt = (($product['quantity'] * $product['theprice']) * $shippingRates[$prodIdCobination][$rateId]['cost']) / $totalAmount;
      $shipProducts[$productKey]['cost'] = number_format($amt, 2);
      $shippingAmount[$prodIdCobination] = $shippingAmount[$prodIdCobination] + $shipProducts[$productKey]['cost'];
      }
      $shipProducts[$productKey]['info'] = $shippingRates[$prodIdCobination][$rateId];
      }
      $counter++;
      }
      }
      }

      if (empty($cartProducts)) {
      $message = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
      LibHelper::exitWithError($message, true);
      }

      $productToShippingMethods = array();
      $sn = 0;
      $json = array();
      $prodSrchObj = new ProductSearch();

      foreach ($cartProducts as $cartkey => $cartval) {
      $sn++;
      if ($cartval['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON || $cartval['product_type'] != Product::PRODUCT_TYPE_PHYSICAL || $cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
      continue;
      }

      $productKey = $cartval['selprod_id'];
      if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productKey = date('Y-m-d', strtotime($cartval['rentalStartDate'])) . '_' . $cartval['selprod_id'];
      }


      if (!array_key_exists($productKey, $shipProducts) || empty($shipProducts[$productKey])) {
      $json['error']['product'][$sn] = sprintf(Labels::getLabel('M_Shipping_Info_Required_for_%s', $this->siteLangId), htmlentities($cartval['product_name']));
      continue;
      }

      // get Product Data[
      $prodSrch = clone $prodSrchObj;
      $prodSrch->setDefinedCriteria();
      $prodSrch->joinProductToCategory();
      $prodSrch->joinProductShippedBy();
      $prodSrch->joinProductFreeShipping();
      $prodSrch->joinSellerSubscription();
      $prodSrch->addSubscriptionValidCondition();
      $prodSrch->doNotCalculateRecords();
      $prodSrch->doNotLimitRecords();
      $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
      $prodSrch->addCondition('selprod_id', '=', $cartval['selprod_id']);
      // $prodSrch->addDirectCondition( "( isnull(psbs.psbs_user_id) or psbs.psbs_user_id = '".$cartval['selprod_user_id']."')" );
      $prodSrch->addMultipleFields(array('selprod_id', 'product_seller_id', 'psbs_user_id as shippedBySellerId'));
      $productRs = $prodSrch->getResultSet();
      $product = FatApp::getDb()->fetch($productRs);
      // ]

      $shipInfo = $shipProducts[$productKey]['info'];
      $productToShippingMethods['product'][$productKey] = array(
      'selprod_id' => $cartval['selprod_id'],
      'mshipapi_code' => $shipInfo['code'],
      'mshipapi_id' => $shipInfo['id'],
      'mshipapi_label' => $shipInfo['title'],
      'mshipapi_carrier' => $shipInfo['carrier_code'],
      'mshipapi_type' => $shipInfo['shipping_type'],
      'mshipapi_cost' => $shipProducts[$productKey]['cost'],
      'shipped_by_seller' => Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']),
      'mshipapi_level' => $shipInfo['shipping_level']
      ); 
      }

      if (!$json) {
      $this->cartObj->setProductShippingMethod($productToShippingMethods);
      if (!$this->cartObj->isProductShippingMethodSet()) {
      $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart_5', $this->siteLangId);
      if (true === MOBILE_APP_API_CALL) {
      FatUtility::dieJsonError($this->errMessage);
      }
      //MSG_Error_in_Shipping_Method_Selection
      Message::addErrorMessage($this->errMessage);
      FatUtility::dieWithError(Message::getHtml());
      }

      $this->set('msg', Labels::getLabel('MSG_Shipping_Method_selected_successfully.', $this->siteLangId));
      if (true === MOBILE_APP_API_CALL) {
      $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
      $cartObj = new Cart();
      $cartObj->setFulfilmentType($fulfilmentType);
      $cartObj->setCartCheckoutType($fulfilmentType);
      $productsArr = $cartObj->getProducts($this->siteLangId);
      $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
      $this->set('products', $productsArr);
      $this->set('cartSummary', $cartSummary);
      $this->set('recordCount', !empty($cartProducts) ? count($cartProducts) : 0);
      $this->_template->render();
      }
      $this->_template->render(false, false, 'json-success.php');
      } else {
      $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart_1', $this->siteLangId);
      if (true === MOBILE_APP_API_CALL) {
      FatUtility::dieJsonError($this->errMessage);
      }
      Message::addErrorMessage($this->errMessage);
      FatUtility::dieWithError(Message::getHtml());
      }
      } */

    public function setUpShippingMethod()
    {
        $this->cartObj->removeProductPickUpAddresses();
        $post = FatApp::getPostedData();

        if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $this->_template->render(false, false, 'json-success.php');
        }


        /* $cartProducts = $this->cartObj->getProducts($this->siteLangId); */
        $cartProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        $shippingRates = $this->cartObj->getShippingRates();
        if (false == $shippingRates) {
            $message = Labels::getLabel('MSG_Shipping_rates_are_not_available', $this->siteLangId);
            LibHelper::exitWithError($message, true);
        }

        $selectedShippingMethods = [];
        $shipProducts = [];

        $basketProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        $shippingServices = isset($post['shipping_services']) ? $post['shipping_services'] : [];
        foreach ($shippingServices as $prodIdCobination => $rateId) {
            if (empty($rateId)) {
                $message = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            if (!array_key_exists($prodIdCobination, $shippingRates)) {
                $message = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            if (!array_key_exists($rateId, $shippingRates[$prodIdCobination])) {
                $message = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            $amt = 0;
            $productArr = explode('_', $prodIdCobination);
            foreach ($productArr as $selProdId) {
                foreach ($basketProducts as $cartKey => $product) {
                    if ($product['selprod_id'] != $selProdId) {
                        continue;
                    }
                    $amt = $amt + ($product['quantity'] * $product['theprice']);
                }
            }

            $selectedShippingMethods[$prodIdCobination]['totalAmount'] = $amt;
            $selectedShippingMethods[$prodIdCobination]['rates'] = $shippingRates[$prodIdCobination][$rateId];
        }

        /* break down shipping amount in proportional to each product price */
        $selprodIdsCount = $this->cartObj->getCartSelprodIdsCount();
        foreach ($shippingServices as $prodIdCobination => $rateId) {
            $shippingAmount[$prodIdCobination] = 0;
            $totalAmount = $selectedShippingMethods[$prodIdCobination]['totalAmount'];

            $counter = 1;
            $productArr = explode('_', $prodIdCobination);
            $prodCount = count($productArr);
            foreach ($productArr as $selProdId) {
                foreach ($basketProducts as $cartKey => $product) {
                    if ($product['selprod_id'] != $selProdId) {
                        continue;
                    }
                    $productCount = isset($selprodIdsCount[$selProdId]) ? $selprodIdsCount[$selProdId] : 1;
                    if ($prodCount > 0 && $counter == $prodCount) {
                        $shipProducts[$selProdId]['cost'] = $shippingRates[$prodIdCobination][$rateId]['cost'] - $shippingAmount[$prodIdCobination];
                    } else {
                        $amt = ((($product['quantity'] * $product['theprice']) * $shippingRates[$prodIdCobination][$rateId]['cost'])) / $totalAmount;
                        if (isset($shipProducts[$selProdId])) {
                            $shipProducts[$selProdId]['cost'] += number_format($amt, 2);
                        } else {
                            $shipProducts[$selProdId]['cost'] = number_format($amt, 2);
                        }
                        $shippingAmount[$prodIdCobination] = $shippingAmount[$prodIdCobination] + number_format($amt, 2);
                    }
                    $shipProducts[$selProdId]['info'] = $shippingRates[$prodIdCobination][$rateId];
                }
                $counter++;
            }
        }

        if (empty($cartProducts)) {
            $message = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
            LibHelper::exitWithError($message, true);
        }

        $productToShippingMethods = array();
        $sn = 0;
        $json = array();
        $prodSrchObj = new ProductSearch();

        $shopIds = [];
        foreach ($cartProducts as $cartkey => $cartval) {
            $sn++;
            if ($cartval['product_type'] != Product::PRODUCT_TYPE_PHYSICAL) {
                continue;
            }
            if ($cartval['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                continue;
            }

            if (!array_key_exists($cartval['selprod_id'], $shipProducts) || empty($shipProducts[$cartval['selprod_id']])) {
                $json['error']['product'][$sn] = sprintf(Labels::getLabel('M_Shipping_Info_Required_for_%s', $this->siteLangId), htmlentities($cartval['product_name']));
                continue;
            }
            $shopIds[$cartval['shop_id']] = $cartval['shop_id'];
            /* get Product Data[ */
            $prodSrch = clone $prodSrchObj;
            $prodSrch->setDefinedCriteria();
            $prodSrch->joinProductToCategory();
            $prodSrch->joinProductShippedBy();
            $prodSrch->joinProductFreeShipping();
            $prodSrch->joinSellerSubscription();
            $prodSrch->addSubscriptionValidCondition();
            $prodSrch->doNotCalculateRecords();
            $prodSrch->doNotLimitRecords();
            $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $prodSrch->addCondition('selprod_id', '=', $cartval['selprod_id']);
            /* $prodSrch->addDirectCondition( "( isnull(psbs.psbs_user_id) or psbs.psbs_user_id = '".$cartval['selprod_user_id']."')" ); */
            $prodSrch->addMultipleFields(array('selprod_id', 'product_seller_id', 'psbs_user_id as shippedBySellerId'));
            $productRs = $prodSrch->getResultSet();
            $product = FatApp::getDb()->fetch($productRs);
            /* ] */

            if ($this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
                $shipBySeller = $cartval['selprod_user_id'];
            } else {
                $shipBySeller = Product::isShippedBySeller($cartval['selprod_user_id'], $product['product_seller_id'], $product['shippedBySellerId']);
            }

            $shipInfo = $shipProducts[$cartval['selprod_id']]['info'];
            $productToShippingMethods['product'][$cartval['selprod_id']] = array(
                'selprod_id' => $cartval['selprod_id'],
                'mshipapi_code' => $shipInfo['code'],
                'mshipapi_id' => $shipInfo['id'],
                'mshipapi_label' => $shipInfo['title'],
                'mshipapi_carrier' => $shipInfo['carrier_code'],
                'mshipapi_type' => $shipInfo['shipping_type'],
                'mshipapi_cost' => $shipProducts[$cartval['selprod_id']]['cost'],
                'shipped_by_seller' => $shipBySeller,
                'mshipapi_level' => $shipInfo['shipping_level'],
                'mshipapi_min_duration' => (isset($shipInfo['shiprate_min_duration'])) ? $shipInfo['shiprate_min_duration'] : 1
            );
        }

        if (!$json) {
            $this->cartObj->setProductShippingMethod($productToShippingMethods);
            if (!$this->cartObj->isProductShippingMethodSet()) {
                $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                if (true === MOBILE_APP_API_CALL) {
                    FatUtility::dieJsonError($this->errMessage);
                }
                //MSG_Error_in_Shipping_Method_Selection
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }

            $this->set('section', 'paymentSummary');
            $attachmentArr = [];
            if (!empty($shopIds) && $this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
                $fileSrch = AttachedFile::getSearchObject();
                $fileSrch->addCondition('afile_type', '=', AttachedFile::FILETYPE_SHOP_AGREEMENT);
                $fileSrch->addCondition('afile_record_id', 'IN', $shopIds);
                $attachmentArr = FatApp::getDb()->fetchAll($fileSrch->getResultSet(), 'afile_record_id');
            }


            if ((FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1) && $this->cartObj->isRequireVerification() && $this->cartObj->getCartType() != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) || (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1) && !empty($attachmentArr))) {
                $this->set('section', 'verification');
            }

            $this->set('msg', Labels::getLabel('MSG_Shipping_Method_selected_successfully.', $this->siteLangId));
            if (true === MOBILE_APP_API_CALL) {
                $fulfilmentType = FatApp::getPostedData('fulfilmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);
                $cartObj = new Cart();
                $cartObj->setFulfilmentType($fulfilmentType);
                $cartObj->setCartCheckoutType($fulfilmentType);
                $productsArr = $cartObj->getProducts($this->siteLangId);
                $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
                $this->set('products', $productsArr);
                $this->set('cartSummary', $cartSummary);
                $this->set('recordCount', !empty($cartProducts) ? count($cartProducts) : 0);
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php');
        } else {
            $this->errMessage = Labels::getLabel('MSG_Shipping_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function verificationForm()
    {
        $criteria = array('isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true);
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
        $fulfillmentType = $this->cartObj->getCartCheckoutType();
        $cartType = $this->cartObj->getCartType();

        if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $criteria['isProductPickUpAddrSet'] = true;
        }
        /* ] */
        if ($this->cartObj->getError() != '') {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->cartObj->getError());
            }
            Message::addErrorMessage($this->cartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        $userId = UserAuthentication::getLoggedUserId();
        if (true === MOBILE_APP_API_CALL) {
            $order_id = FatApp::getPostedData('orderId', Fatutility::VAR_STRING, false);
        } else {
            $order_id = isset($_SESSION['shopping_cart']["order_id"]) ? $_SESSION['shopping_cart']["order_id"] : false;
        }

        $shippingAddressArr = array();
        $billingAddressArr = array();
        $shippingAddressId = $this->cartObj->getCartShippingAddress();
        $billingAddressId = $this->cartObj->getCartBillingAddress();

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
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $orderPickUpData = $orderObj->getOrderPickUpData($order_id, $this->siteLangId, $cartType);
        }
        if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $orderShippingData = $orderObj->getOrderShippingData($order_id, $this->siteLangId, $cartType);
            foreach ($orderShippingData as $data) {
                if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                    $shippingData[$data['opshipping_code']][] = $data;
                }
            }
        }

        $verificationData = $this->cartObj->getVerificationFldData();
        $orderNumericKey = $this->cartObj->getOrderNumericKey();

        $verificationForm = $this->getVerificationForm($order_id, $verificationData, $orderNumericKey);
        $verificationForm->fill($verificationData);
        $vfldSortedData = $this->getSortedVerificationFldsData();

        $cartProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        $cartProductData = [];
        $shopsList = [];
        foreach ($cartProducts as $value) {
            if (isset($value['selprod_type']) && $value['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                continue;
            }
            $cartProductData[$value['product_id']] = $value;
            $shopsList[$value['shop_id']] = $value['shop_name'];
        }

        $attachmentArr = [];
        $signatureAdded = 0;
        $sectionHtml = '';
        if (!empty($shopsList) && $this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
            $fileSrch = AttachedFile::getSearchObject();
            $fileSrch->addCondition('afile_type', '=', AttachedFile::FILETYPE_SHOP_AGREEMENT);
            $fileSrch->addCondition('afile_record_id', 'IN', array_unique(array_keys($shopsList)));
            $attachmentArr = FatApp::getDb()->fetchAll($fileSrch->getResultSet(), 'afile_record_id');
            $this->set('shopList', $shopsList);
            $this->set('attachmentArr', $attachmentArr);
            $signatureData = [];
            if (true == $this->cartObj->getSetDigintalSign()) {
                $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $userId, 0, -1, true, 0, false);
                /* $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $userId, 0, -1, true, 0, false);
                $this->set('signatureData', $signatureData); */
            }
            
            $this->set('signatureAdded', (empty($signatureData)) ? 0 : 1);
            $tpl = new FatTemplate('', '');
            $tpl->set('siteLangId', $this->siteLangId);
            $tpl->set('signatureData', $signatureData);
            $sectionHtml = $tpl->render(false, false, 'checkout/signature-section.php', true, true);
        }
        $this->set('sectionHtml', $sectionHtml);
        
        /*  [ SHOP AGGREMENT UPDATES START HERE  == Need to Update */

        $this->set('orderId', $order_id);
        $this->set('cartProductData', $cartProductData);
        $this->set('vfldSortedData', $vfldSortedData);
        $this->set('verificationForm', $verificationForm);
        $this->set('cartSummary', $cartSummary);
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('shippingAddressId', $shippingAddressId);
        $this->set('billingAddressId', $billingAddressId);
        $this->set('billingAddressArr', $billingAddressArr);
        $this->set('shippingAddressArr', $shippingAddressArr);
        $this->set('orderPickUpData', $orderPickUpData);
        $this->set('orderShippingData', $shippingData);
        $this->_template->render(false, false, 'checkout/verification-form.php', false, false);
    }
    public function signatureForm()
    {
        $this->set('signFrm', $this->getSignatureForm());
        $this->_template->render(false, false);
    }
    
    
    public function setupVerificationFlds()
    {
        $post = FatApp::getPostedData();

        if (array_key_exists('is_Sign_added', $post)) {
            unset($post['is_Sign_added']);
        }
        if (array_key_exists('agreement', $post)) {
            unset($post['agreement']);
        }

        $user_id = UserAuthentication::getLoggedUserId();

        /* Fatapp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, array('smt' => 'afile_type = ? AND afile_record_id = ?', 'vals' => array(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $user_id))); */

        $fileHandlerObj = new AttachedFile();
        if (!empty($_FILES)) {
            foreach ($_FILES as $fld_name => $data) {
                $vflds_id = filter_var($fld_name, FILTER_SANITIZE_NUMBER_INT);
                if (!empty($data['tmp_name'])) {
                    if (filesize($_FILES[$fld_name]['tmp_name']) > 10240000) { 
                        $message = Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId);
                        Message::addErrorMessage($message);
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                
                    if (!$res = $fileHandlerObj->saveAttachment($_FILES[$fld_name]['tmp_name'], AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $user_id, $vflds_id, $_FILES[$fld_name]['name'], -1, true, 0, 0, 0, true)) {
                        Message::addErrorMessage($fileHandlerObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }

                $post['fileFld_' . $vflds_id] = '';
            }
        }

        $this->cartObj->setCartVerificationData($post);

        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Verification_Submit_Successfully.', $this->siteLangId));
    }

    public function reviewCart()
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $cartOrderData = Cart::getCartData($loggedUserId);
        $cartOrderData = !empty($cartOrderData) ? json_decode($cartOrderData, true) : [];
        $fulfillmentType = 0;
        if (isset($cartOrderData['shopping_cart']['checkout_type'])) {
            $fulfillmentType = $cartOrderData['shopping_cart']['checkout_type'];
        }

        $criteria = array('isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true);
        if ($this->cartObj->hasPhysicalProduct()) {
            if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
                $criteria['hasShippingAddress'] = true;
                $criteria['isProductShippingMethodSet'] = true;
            } elseif ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
                $criteria['isProductPickUpAddrSet'] = true;
            }
        }

        if (!$this->isEligibleForNextStep($criteria)) {
            if (Message::getErrorCount()) {
                $this->errMessage = Message::getHtml();
            } else {
                $this->errMessage = Labels::getLabel('MSG_NOT_ALLOWED_TO_PROCEED_FOR_NEXT_STEP', $this->siteLangId);
            }
            LibHelper::exitWithError($this->errMessage, true);
        }

        if (0 >= $fulfillmentType) {
            $msg = Labels::getLabel("MSG_INVALID_FULFILLMENT_TYPE", $this->siteLangId);
            FatUtility::dieJsonError($msg);
        }

        $this->cartObj->setCartCheckoutType($fulfillmentType);
        $this->cartObj->setFulfilmentType($fulfillmentType);

        // $cartProducts = $this->cartObj->getProducts($this->siteLangId);
        $cartProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        if (count($cartProducts) == 0) {
            $this->errMessage = Labels::getLabel('MSG_Your_Cart_is_empty', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }

        $hasPhysicalProd = $this->cartObj->hasPhysicalProduct();
        $cartHasDigitalProduct = $this->cartObj->hasDigitalProduct();
        if (!$hasPhysicalProd) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
            $this->cartObj->unsetCartShippingAddress();
        }
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);

        $shippingRates = [];

        switch ($fulfillmentType) {
            case Shipping::FULFILMENT_PICKUP:
                $shippingRates = $this->cartObj->getPickupOptions($cartProducts, $this->cartObj->getCartType());
                break;
            case Shipping::FULFILMENT_SHIP:
                $shippingRates = $this->cartObj->getShippingOptions($this->cartObj->getCartType());
                break;
        }

        if (!$hasPhysicalProd) {
            $selected_shipping_address_id = $this->cartObj->getCartBillingAddress();
        } else {
            $selected_shipping_address_id = $this->cartObj->getCartShippingAddress();
        }

        $address = new Address($selected_shipping_address_id, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        $obj = new Extrapage();
        $headerData = $obj->getContentByPageType(Extrapage::CHECKOUT_PAGE_HEADER_BLOCK, $this->siteLangId);
        $this->set('cartSummary', $cartSummary);
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('addresses', $addresses);
        $this->set('products', $cartProducts);
        $this->set('hasPhysicalProd', $hasPhysicalProd);
        $this->set('cartHasDigitalProduct', $cartHasDigitalProduct);
        $this->set('cartOrderData', $cartOrderData);
        $this->set('shippingRates', $shippingRates);
        $this->set('headerData', $headerData);

        $this->_template->render();
    }

    private function getCartProductInfo($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->joinProductSpecifics();
        $prodSrch->joinBrands();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);

        $fields = array(
            'product_id', 'product_type', 'product_length', 'product_width', 'product_height',
            'product_dimension_unit', 'product_weight', 'product_weight_unit', 'product_model',
            'selprod_id', 'selprod_user_id', 'selprod_stock', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sku',
            'selprod_condition', 'selprod_code',
            'special_price_found', 'theprice', 'shop_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(brand_name, brand_identifier) as brand_name', 'shop_name',
            'seller_user.user_name as shop_onwer_name', 'seller_user_cred.credential_username as shop_owner_username',
            'seller_user.user_dial_code', 'seller_user.user_phone as shop_owner_phone', 'seller_user_cred.credential_email as shop_owner_email',
            'selprod_download_validity_in_days', 'selprod_max_download_times', 'ps.product_warranty',
            'COALESCE(sps.selprod_return_age, ss.shop_return_age) as return_age', 'COALESCE(sps.selprod_cancellation_age, ss.shop_cancellation_age) as cancellation_age',
            'sprodata_is_for_sell', 'sprodata_is_for_rent', 'sprodata_rental_security', 'sprodata_rental_stock', 'sprodata_rental_terms', 'sprodata_rental_price', 'sprodata_duration_type', 'product_identifier'
        );
        $prodSrch->addMultipleFields($fields);
        $rs = $prodSrch->getResultSet();
        return $productInfo = FatApp::getDb()->fetch($rs);
    }

    private function getCartProductLangData($selprod_id, $lang_id)
    {
        $langProdSrch = new ProductSearch($lang_id);
        $langProdSrch->setDefinedCriteria();
        $langProdSrch->joinBrands();
        $langProdSrch->joinProductToCategory();
        $langProdSrch->joinSellerSubscription();
        $langProdSrch->addSubscriptionValidCondition();
        $langProdSrch->doNotCalculateRecords();
        $langProdSrch->doNotLimitRecords();
        $langProdSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $langProdSrch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array('IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(shop_name, shop_identifier) as shop_name');
        $langProdSrch->addMultipleFields($fields);
        $langProdRs = $langProdSrch->getResultSet();
        return FatApp::getDb()->fetch($langProdRs);
    }

    public function paymentSummary()
    {
        if (UserAuthentication::isUserLogged()) {
            $user_is_buyer = User::getAttributesById(UserAuthentication::getLoggedUserId(), 'user_is_buyer');
            if (!$user_is_buyer) {
                $this->errMessage = Labels::getLabel('MSG_Please_login_with_buyer_account.', $this->siteLangId);
                Message::addErrorMessage($this->errMessage);
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        if (true === MOBILE_APP_API_CALL) {
            $payFromWallet = FatApp::getPostedData('payFromWallet', Fatutility::VAR_INT, 0);
            $this->cartObj->updateCartWalletOption($payFromWallet);

            $useRewardPoints = FatApp::getPostedData('redeem_rewards', FatUtility::VAR_INT, 0);
            if (0 < $useRewardPoints) {
                $this->useRewardPoints(true);
            }
        }

        $criteria = array('isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true);
        $fulfillmentType = $this->cartObj->getCartCheckoutType();
        $cartType = $this->cartObj->getCartType();
        $cartHasPhysicalProduct = $this->cartObj->hasPhysicalProduct();
        if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $criteria['isProductPickUpAddrSet'] = true;
        }

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

        if ($this->cartObj->getError() != '') {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($this->cartObj->getError());
            }
            Message::addErrorMessage($this->cartObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);

        $userId = UserAuthentication::getLoggedUserId();
        $userWalletBalance = User::getUserBalance($userId, true);

        /* Payment Methods[ */
        $splitPaymentMethodsPlugins = Plugin::getDataByType(Plugin::TYPE_SPLIT_PAYMENT_METHOD, $this->siteLangId);
        $regularPaymentMethodsPlugins = Plugin::getDataByType(Plugin::TYPE_REGULAR_PAYMENT_METHOD, $this->siteLangId);

        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $codPlugInId = Plugin::getAttributesByCode('CashOnDelivery', 'plugin_id');
            if (array_key_exists($codPlugInId, $regularPaymentMethodsPlugins)) {
                unset($regularPaymentMethodsPlugins[$codPlugInId]);
            }
        } else if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $codPlugInId = Plugin::getAttributesByCode('PayAtStore', 'plugin_id');
            if (array_key_exists($codPlugInId, $regularPaymentMethodsPlugins)) {
                unset($regularPaymentMethodsPlugins[$codPlugInId]);
            }
        }

        $paymentMethods = array_merge($splitPaymentMethodsPlugins, $regularPaymentMethodsPlugins);
        /* ] */

        $orderData = array();
        /* add Order Data[ */
        if (true === MOBILE_APP_API_CALL) {
            $order_id = FatApp::getPostedData('orderId', Fatutility::VAR_STRING, false);
        } else {
            $order_id = isset($_SESSION['shopping_cart']["order_id"]) ? $_SESSION['shopping_cart']["order_id"] : false;
        }

        $shippingAddressArr = array();
        $billingAddressArr = array();
        $shippingAddressId = $this->cartObj->getCartShippingAddress();
        $billingAddressId = $this->cartObj->getCartBillingAddress();

        if ($shippingAddressId) {
            $address = new Address($shippingAddressId, $this->siteLangId);
            $shippingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }
        if ($billingAddressId) {
            $address = new Address($billingAddressId, $this->siteLangId);
            $billingAddressArr = $address->getData(Address::TYPE_USER, $userId);
        }

        $orderData['order_id'] = $order_id;
        $orderData['order_user_id'] = $userId;
        /* $orderData['order_user_name'] = $userDataArr['user_name'];
          $orderData['order_user_email'] = $userDataArr['credential_email'];
          $orderData['order_user_phone'] = $userDataArr['user_phone']; */
        $orderData['order_payment_status'] = Orders::ORDER_PAYMENT_PENDING;
        $orderData['order_date_added'] = date('Y-m-d H:i:s');
        $orderProductType = $cartType;
        if ($cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $orderProductType = applicationConstants::PRODUCT_FOR_RENT;
        }

        if (isset($cartSummary['pendingLateCharges']) && $cartSummary['pendingLateCharges'] > 0) {
            $orderData['order_late_charges'] = $cartSummary['pendingLateCharges'];
            $orderData['order_late_charges_op_ids'] = $cartSummary['op_ids_for_pending_charges'];
        }

        $orderData['order_product_type'] = $orderProductType; /* sale or rent or extend rent */

        /* addresses[ */
        $userAddresses[0] = array(
            'oua_order_id' => $order_id,
            'oua_type' => Orders::BILLING_ADDRESS_TYPE,
            'oua_name' => $billingAddressArr['addr_name'],
            'oua_address1' => $billingAddressArr['addr_address1'],
            'oua_address2' => $billingAddressArr['addr_address2'],
            'oua_city' => $billingAddressArr['addr_city'],
            'oua_state' => $billingAddressArr['state_name'],
            'oua_country' => $billingAddressArr['country_name'],
            'oua_country_code' => $billingAddressArr['country_code'],
            'oua_country_code_alpha3' => $billingAddressArr['country_code_alpha3'],
            'oua_state_code' => $billingAddressArr['state_code'],
            'oua_dial_code' => $billingAddressArr['addr_dial_code'],
            'oua_phone' => $billingAddressArr['addr_phone'],
            'oua_zip' => $billingAddressArr['addr_zip'],
        );

        if (!empty($shippingAddressArr) && $fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $userAddresses[1] = array(
                'oua_order_id' => $order_id,
                'oua_type' => Orders::SHIPPING_ADDRESS_TYPE,
                'oua_name' => $shippingAddressArr['addr_name'],
                'oua_address1' => $shippingAddressArr['addr_address1'],
                'oua_address2' => $shippingAddressArr['addr_address2'],
                'oua_city' => $shippingAddressArr['addr_city'],
                'oua_state' => $shippingAddressArr['state_name'],
                'oua_country' => $shippingAddressArr['country_name'],
                'oua_country_code' => $shippingAddressArr['country_code'],
                'oua_country_code_alpha3' => $shippingAddressArr['country_code_alpha3'],
                'oua_state_code' => $shippingAddressArr['state_code'],
                'oua_dial_code' => $shippingAddressArr['addr_dial_code'],
                'oua_phone' => $shippingAddressArr['addr_phone'],
                'oua_zip' => $shippingAddressArr['addr_zip'],
            );
        }

        $orderData['userAddresses'] = $userAddresses;
        /* ] */

        /* order extras[ */
        $orderData['extra'] = array(
            'oextra_order_id' => $order_id,
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
        /* ] */

        $languageRow = Language::getAttributesById($this->siteLangId);
        $orderData['order_language_id'] = $languageRow['language_id'];
        $orderData['order_language_code'] = $languageRow['language_code'];

        $currencyRow = Currency::getAttributesById($this->siteCurrencyId);
        $orderData['order_currency_id'] = $currencyRow['currency_id'];
        $orderData['order_currency_code'] = $currencyRow['currency_code'];
        $orderData['order_currency_value'] = $currencyRow['currency_value'];

        $orderData['order_user_comments'] = '';
        $orderData['order_admin_comments'] = '';

        if (!empty($cartSummary["cartDiscounts"])) {
            $orderData['order_discount_coupon_code'] = $cartSummary["cartDiscounts"]["coupon_code"];
            $orderData['order_discount_type'] = $cartSummary["cartDiscounts"]["coupon_discount_type"];
            $orderData['order_discount_value'] = $cartSummary["cartDiscounts"]["coupon_discount_value"];
            $orderData['order_discount_total'] = $cartSummary["cartDiscounts"]["coupon_discount_total"];
            $orderData['order_discount_info'] = $cartSummary["cartDiscounts"]["coupon_info"];
        }

        $orderData['order_reward_point_used'] = $cartSummary["cartRewardPoints"];
        $orderData['order_reward_point_value'] = CommonHelper::convertRewardPointToCurrency($cartSummary["cartRewardPoints"]);

        //$orderData['order_payment_gateway_charges'] = $cartSummary["orderPaymentGatewayCharges"];
        //$orderData['order_cart_total'] = $cartSummary["cartTotal"];
        //$orderData['order_shipping_charged'] = $cartSummary["shippingTotal"];
        $orderData['order_tax_charged'] = $cartSummary["cartTaxTotal"];
        $orderData['order_site_commission'] = $cartSummary["siteCommission"];
        $orderData['order_volume_discount_total'] = $cartSummary["cartVolumeDiscount"];
        //$orderData['order_sub_total'] = $cartSummary["netTotalWithoutDiscount"];
        //$orderData['order_net_charged'] = $cartSummary["netTotalAfterDiscount"];
        //$orderData['order_actual_paid'] = $cartSummary["cartActualPaid"];
        $orderData['order_net_amount'] = $cartSummary["orderNetAmount"];
        $orderData['order_is_wallet_selected'] = $cartSummary["cartWalletSelected"];
        $orderData['order_wallet_amount_charge'] = $cartSummary["WalletAmountCharge"];
        $orderData['order_type'] = Orders::ORDER_PRODUCT;
        $orderData['order_security_total'] = $cartSummary['rentalSecurityTotal'];

        /* referrer details[ */
        $srchOrder = new OrderSearch();
        $srchOrder->doNotCalculateRecords();
        $srchOrder->doNotLimitRecords();
        $srchOrder->addCondition('order_user_id', '=', $userId);
        $srchOrder->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $srchOrder->addCondition('order_referrer_user_id', '!=', 0);
        $srchOrder->addMultipleFields(array('count(o.order_id) as totalOrders'));
        $rs = $srchOrder->getResultSet();
        $existingReferrerOrderRow = FatApp::getDb()->fetch($rs);

        $orderData['order_referrer_user_id'] = 0;
        $orderData['order_referrer_reward_points'] = 0;
        $orderData['order_referral_reward_points'] = 0;
        $orderData['order_cart_data'] = Cart::getCartData($userId);

        $referrerUserId = 0;
        if (isset($_COOKIE['referrer_code_checkout']) && !empty($_COOKIE['referrer_code_checkout'])) {
            $userReferrerCode = $_COOKIE['referrer_code_checkout'];

            $userSrchObj = User::getSearchObject();
            $userSrchObj->doNotCalculateRecords();
            $userSrchObj->doNotLimitRecords();
            $userSrchObj->addCondition('user_referral_code', '=', $userReferrerCode);
            $userSrchObj->addCondition('user_id', '!=', $userId);
            $userSrchObj->addMultipleFields(array('user_id', 'user_referral_code', 'user_name'));
            $rs = $userSrchObj->getResultSet();
            $referrerUserRow = FatApp::getDb()->fetch($rs);
            if ($referrerUserRow && $referrerUserRow['user_referral_code'] == $userReferrerCode && $userReferrerCode != '' && $referrerUserRow['user_referral_code'] != '') {
                $referrerUserId = $referrerUserRow['user_id'];
                //$referrerUserName = $referrerUserRow['user_name'];
            }
        }

        if ($referrerUserId > 0 && FatApp::getConfig("CONF_ENABLE_REFERRER_MODULE") && $existingReferrerOrderRow['totalOrders'] == 0) {
            $orderData['order_referrer_user_id'] = $referrerUserId;
            $orderData['order_referrer_reward_points'] = FatApp::getConfig("CONF_SALE_REFERRER_REWARD_POINTS", FatUtility::VAR_INT, 0);
            $orderData['order_referral_reward_points'] = FatApp::getConfig("CONF_SALE_REFERRAL_REWARD_POINTS", FatUtility::VAR_INT, 0);
        }
        /* ] */

        $allLanguages = Language::getAllNames();
        $productSelectedShippingMethodsArr = $this->cartObj->getProductShippingMethod();

        $productSelectedPickUpAddresses = $this->cartObj->getProductPickUpAddresses();
        $orderLangData = array();
        foreach ($allLanguages as $lang_id => $language_name) {
            $order_shippingapi_name = '';

            if ($this->cartObj->getCartShippingApi()) {
                $shippingApiLangRow = ShippingApi::getAttributesByLangId($lang_id, $this->cartObj->getCartShippingApi());
                $order_shippingapi_name = $shippingApiLangRow['shippingapi_name'];
                if (empty($order_shippingapi_name)) {
                    $order_shippingapi_name = $shippingApiLangRow['shippingapi_identifier'];
                }
            }

            $orderLangData[$lang_id] = array(
                'orderlang_lang_id' => $lang_id,
                'order_shippingapi_name' => $order_shippingapi_name
            );
        }
        $orderData['orderLangData'] = $orderLangData;

        /* order products[ */
        $cartProducts = $this->cartObj->getProducts($this->siteLangId);

        $orderData['products'] = array();
        $orderData['prodCharges'] = array();

        $order_affiliate_user_id = 0;
        $order_affiliate_total_commission = 0;
        $totalRoundingOff = 0;
        $addedRewardPoints = 0;
        $productIndex = 0;
        $totalProducts = count($cartProducts);

        if ($cartProducts) {
            foreach ($cartProducts as $cartkey => $cartProduct) {
                $productIndex++;
                $productShippingData = array();
                $productTaxChargesData = array();

                if ($cartProduct['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $productInfo = $cartProduct;
                } else {
                    $productInfo = $this->getCartProductInfo($cartProduct['selprod_id']);
                    $productInfo['sellerProdType'] = $cartProduct['sellerProdType'];
                    $productInfo['mainProductId'] = $cartProduct['mainProductId'];
                }

                if (!$productInfo) {
                    continue;
                }
                $codEnabled = $cartProduct['isProductShippedBySeller'] ? $cartProduct['selprod_cod_enabled'] : $cartProduct['product_cod_enabled'];
                if (applicationConstants::NO == $codEnabled) {
                    $key = array_search('CashOnDelivery', array_column($paymentMethods, 'plugin_code'));
                    if (false !== $key) unset($paymentMethods[$key]);
                }

                $sduration_name = '';
                $shippingDurationTitle = '';
                $shippingDurationRow = array();
                $productKey = $productInfo['selprod_id'];
                $shipKey = $productInfo['selprod_id'];
                if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                    $productKey = $cartkey;
                    /* $shipKey = date('Y-m-d', strtotime($cartProduct['rentalStartDate'])) . '_' . $productInfo['selprod_id']; */
                }


                if ($productInfo['product_type'] == Product::PRODUCT_TYPE_PHYSICAL && !empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$shipKey])) {
                    $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$shipKey];
                    $productShippingData = array(
                        'opshipping_code' => $shippingDurationRow['mshipapi_code'],
                        'opshipping_level' => $shippingDurationRow['mshipapi_level'],
                        'opshipping_label' => $shippingDurationRow['mshipapi_label'],
                        'opshipping_by_seller_user_id' => $shippingDurationRow['shipped_by_seller'],
                        'opshipping_ship_duration' => $shippingDurationRow['mshipapi_min_duration']
                    );

                    if ($shippingDurationRow['mshipapi_type'] == Shipping::TYPE_MANUAL) {
                        $productShippingData['opshipping_rate_id'] = $shippingDurationRow['mshipapi_id'];
                        $productShippingData['opshipping_type'] = Shipping::MANUAL_SHIPPING;
                    } else {
                        $productShippingData['opshipping_service_code'] = $shippingDurationRow['mshipapi_id'];
                        $productShippingData['opshipping_carrier_code'] = $shippingDurationRow['mshipapi_carrier'];
                        $productShippingData['opshipping_type'] = Shipping::SHIPPING_SERVICES;
                    }
                    
                    $productShippingData['opshipping_country_of_origin'] = ($shippingDurationRow['shipped_by_seller'] > 0) ? $cartProduct['shop_country_id'] : FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
                    
                }

                $productPickUpData = array();
                $productPickupAddress = array();
                if ($productInfo['product_type'] == Product::PRODUCT_TYPE_PHYSICAL && !empty($productSelectedPickUpAddresses) && isset($productSelectedPickUpAddresses[$productKey])) {
                    $pickUpDataRow = $productSelectedPickUpAddresses[$productKey];
                    $productPickUpData = array(
                        'opshipping_fulfillment_type' => Shipping::FULFILMENT_PICKUP,
                        'opshipping_by_seller_user_id' => $pickUpDataRow['shipped_by_seller'],
                        'opshipping_pickup_addr_id' => $pickUpDataRow['time_slot_addr_id'],
                        'opshipping_date' => $pickUpDataRow['time_slot_date'],
                        'opshipping_time_slot_from' => $pickUpDataRow['time_slot_from_time'],
                        'opshipping_time_slot_to' => $pickUpDataRow['time_slot_to_time'],
                    );

                    //$addressRecordId = Address::getAttributesById($pickUpDataRow['time_slot_addr_id'], 'addr_record_id');
                    //$addr = new Address($pickUpDataRow['time_slot_addr_id'], $this->siteLangId);
                    //$pickUpAddressArr = $addr->getData($pickUpDataRow['time_slot_type'], $addressRecordId);
                    $addr = new Address();
                    $pickUpAddressArr = $addr->detail($pickUpDataRow['time_slot_addr_id']);
                    if (!empty($pickUpAddressArr)) {
                        $pickUpAddressArr = current($pickUpAddressArr);
                        $productPickupAddress = array(
                            'oua_order_id' => $order_id,
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
                }

                $productTaxOption = array();
                if (array_key_exists($productInfo['selprod_id'], $cartSummary["prodTaxOptions"])) {
                    $productTaxOption = $cartSummary["prodTaxOptions"][$productInfo['selprod_id']];
                }

                foreach ($productTaxOption as $taxStroId => $taxStroName) {
                    $label = Labels::getLabel('LBL_Tax', $this->siteLangId);
                    if (array_key_exists('name', $taxStroName) && $taxStroName['name'] != '') {
                        $label = $taxStroName['name'];
                    }
                    $productTaxChargesData[$taxStroId] = array(
                        'opchargelog_type' => OrderProduct::CHARGE_TYPE_TAX,
                        'opchargelog_identifier' => $label,
                        'opchargelog_value' => $taxStroName['value'],
                        'opchargelog_is_percent' => $taxStroName['inPercentage'],
                        'opchargelog_percentvalue' => $taxStroName['percentageValue']
                    );
                }

                $productsLangData = array();
                $productShippingLangData = array();
                foreach ($allLanguages as $lang_id => $language_name) {
                    if (0 == $lang_id) {
                        continue;
                    }
                    if ($productInfo['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $langSpecificProductInfo = $this->getCartRentalAddonProductLangData($productInfo['selprod_id'], $lang_id);
                    } else {
                        $langSpecificProductInfo = $this->getCartProductLangData($productInfo['selprod_id'], $lang_id);
                    }

                    if (!$langSpecificProductInfo) {
                        continue;
                    }

                    if (!empty($shippingDurationRow)) {
                        $langData = ShippingRate::getAttributesByLangId($shippingDurationRow['mshipapi_id'], $lang_id);
                        $label = (isset($langData['shiprate_name']) && $langData['shiprate_name'] != '') ? $langData['shiprate_name'] : $shippingDurationRow['mshipapi_label'];
                        $productShippingLangData[$lang_id] = array(
                            'opshipping_title' => $label,
                            'opshipping_duration' => '',
                            'opshipping_duration_name' => $label . '-' . $shippingDurationRow['mshipapi_cost'],
                            'opshippinglang_lang_id' => $lang_id
                        );
                    }

                    $weightUnitsArr = applicationConstants::getWeightUnitsArr($lang_id);
                    $lengthUnitsArr = applicationConstants::getLengthUnitsArr($lang_id);
                    $op_selprod_title = ($langSpecificProductInfo['selprod_title'] != '') ? $langSpecificProductInfo['selprod_title'] : '';

                    /* stamping/locking of product options language based [ */
                    $op_selprod_options = '';
                    $productOptionsRows = SellerProduct::getSellerProductOptions($productInfo['selprod_id'], true, $lang_id);
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
                    /* ] */

                    $op_products_dimension_unit_name = (isset($productInfo['product_dimension_unit']) && $productInfo['product_dimension_unit']) ? $lengthUnitsArr[$productInfo['product_dimension_unit']] : '';
                    $op_product_weight_unit_name = (isset($productInfo['product_weight_unit']) && $productInfo['product_weight_unit']) ? $weightUnitsArr[$productInfo['product_weight_unit']] : '';

                    $op_product_tax_options = array();
                    foreach ($productTaxOption as $taxStroId => $taxStroName) {
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

                        $productTaxChargesData[$taxStroId]['langData'][$lang_id] = array(
                            'opchargeloglang_lang_id' => $lang_id,
                            'opchargelog_name' => $langLabel
                        );
                    }

                    $productsLangData[$lang_id] = array(
                        'oplang_lang_id' => $lang_id,
                        'op_product_name' => $langSpecificProductInfo['product_name'],
                        'op_selprod_title' => $op_selprod_title,
                        'op_selprod_options' => $op_selprod_options,
                        'op_brand_name' => !empty($langSpecificProductInfo['brand_name']) ? $langSpecificProductInfo['brand_name'] : '',
                        'op_shop_name' => $langSpecificProductInfo['shop_name'],
                        'op_shipping_duration_name' => $sduration_name,
                        'op_shipping_durations' => $shippingDurationTitle,
                        'op_products_dimension_unit_name' => $op_products_dimension_unit_name,
                        'op_product_weight_unit_name' => $op_product_weight_unit_name,
                        'op_product_tax_options' => json_encode($op_product_tax_options),
                    );
                }
                /* $taxCollectedBySeller = applicationConstants::NO;
                  if(FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER',FatUtility::VAR_INT,0)){
                  $taxCollectedBySeller = applicationConstants::YES;
                  } */

                $orderKey = CART::CART_KEY_PREFIX_PRODUCT . $productInfo['selprod_id'];
                $productPrice = $cartProduct['theprice'];
                $opdSoldOrRented = $cartProduct['productFor'];
                $opdRentalStartDate = '';
                $opdRentalEndDate = '';
                $opdRentalSecurity = '';
                $opdRentalIsPickup = '';
                $opdRentalTerms = '';
                $opdRentalPriceMultiplier = '';
                $opdRentalPriceMultiplierHours = '';
                $opdRentalPrice = '';
                $opdRefundedSecurityAmount = '';
                $opdRefundedSecurityType = '';
                $opdRefundedSecurityStatus = '';
                $opdRentalDurationDiscount = 0;

                $opdRentalType = 0;
                $opdDurationPrice = 0;
                $extendOrderId = 0;
                $rentalAttachmentId = 0;
                $isCancelEligible = 0;
                $isRefundEligible = 0;

                if ($cartProduct['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                    if ($productInfo['sellerProdType'] && $productInfo['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $isCancelEligible = $productInfo['selprod_is_eligible_cancel'];
                        $isRefundEligible = $productInfo['selprod_is_eligible_refund'];
                    }

                    $extendOrderId = $cartProduct['extendOrder'];
                    $opdRentalSecurity = $productInfo['sprodata_rental_security'];
                    if ($extendOrderId > 0) {
                        $opdRentalSecurity = 0;
                    }
                    //$productPrice = $cartProduct['total'];
                    $opdRentalStartDate = $cartProduct['rentalStartDate'];
                    $opdRentalEndDate = $cartProduct['rentalEndDate'];

                    $duration = CommonHelper::getDifferenceBetweenDates($opdRentalStartDate, $opdRentalEndDate, $cartProduct['selprod_user_id'], $productInfo['sprodata_duration_type']);
                    $opdRentalPriceMultiplier = Common::daysBetweenDates($opdRentalStartDate, $opdRentalEndDate);

                    if ($cartProduct['duration_discount_total'] > 0) {
                        $opdRentalDurationDiscount = $cartProduct['duration_discount_total'];
                    }

                    $opdRentalTerms = $productInfo['sprodata_rental_terms'];
                    $opdRentalPriceMultiplierHours = $duration;
                    $orderKey = CART::CART_KEY_PREFIX_PRODUCT . $productInfo['selprod_id'] . $opdRentalStartDate . $opdRentalEndDate;

                    $priceArr = CommonHelper::getRentalPricesArr($productInfo);
                    $opdRentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr);

                    $opdDurationPrice = $priceArr['rental_price'];
                    $opdRentalType = $productInfo['sprodata_duration_type'];

                    $rentalAttachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_AGREEMENT, $productInfo['shop_id'], 0, -1);
                    $rentalAttachmentId = ($rentalAttachment['afile_id'] > 0) ? $rentalAttachment['afile_id'] : 0;
                }

                $productsData = array(
                    'opd_sold_or_rented' => $opdSoldOrRented,
                    'opd_rental_start_date' => $opdRentalStartDate,
                    'opd_rental_end_date' => $opdRentalEndDate,
                    'opd_rental_security' => $opdRentalSecurity,
                    'opd_rental_terms' => $opdRentalTerms,
                    'opd_rental_price_multiplier_days' => $opdRentalPriceMultiplier,
                    'opd_rental_price_multiplier_hours' => $opdRentalPriceMultiplierHours,
                    'opd_rental_type' => $opdRentalType,
                    'opd_rental_price' => $opdRentalPrice,
                    'opd_duration_price' => $opdDurationPrice,
                    /* 'opd_daily_price' => $opdDailyPrice,
                      'opd_hourly_price' => $opdHourlyPrice,
                      'opd_weekly_price' => $opdWeeklyPrice,
                      'opd_monthly_price' => $opdMonthlyPrice, */
                    'opd_refunded_security_amount' => $opdRefundedSecurityAmount,
                    'opd_refunded_security_type' => $opdRefundedSecurityType,
                    'opd_refunded_security_status' => $opdRefundedSecurityStatus,
                    'opd_rental_duration_discount' => $opdRentalDurationDiscount,
                    'opd_extend_from_op_id' => $extendOrderId,
                    'opd_product_type' => (isset($productInfo['sellerProdType'])) ? $productInfo['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT,
                    'opd_main_product_id' => (isset($productInfo['mainProductId'])) ? $productInfo['mainProductId'] : 0,
                    'opd_tax_charge_address_type' => Tax::TAX_ON_SHIPPING_TO_ADDRESS,
                    'opd_rental_agreement_afile_id' => $rentalAttachmentId,
                    'opd_is_eligible_cancel' => $isCancelEligible,
                    'opd_is_eligible_refund' => $isRefundEligible,
                );
                $parentShippingDetails = [];
                if ($cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                    $parentShippingDetails = Orders::getOrderProductShippingType($extendOrderId);
                }

                $orderData['products'][$orderKey] = array(
                    'op_selprod_id' => $productInfo['selprod_id'],
                    'op_is_batch' => 0,
                    'op_selprod_user_id' => $productInfo['selprod_user_id'],
                    'op_selprod_product_id' => $productInfo['product_id'],
                    'op_product_identifier' => $productInfo['product_identifier'],
                    'op_selprod_code' => $productInfo['selprod_code'],
                    'op_qty' => $cartProduct['quantity'],
                    'op_unit_price' => $cartProduct['theprice'],
                    'op_unit_cost' => $cartProduct['selprod_cost'],
                    'op_selprod_sku' => $productInfo['selprod_sku'],
                    'op_selprod_condition' => $productInfo['selprod_condition'],
                    'op_product_model' => (isset($productInfo['product_model'])) ? $productInfo['product_model'] : "",
                    'op_product_type' => (isset($productInfo['product_type'])) ? $productInfo['product_type'] : "",
                    'op_product_length' => (isset($productInfo['product_length'])) ? $productInfo['product_length'] : "",
                    'op_product_width' => (isset($productInfo['product_width'])) ? $productInfo['product_width'] : "",
                    'op_product_height' => (isset($productInfo['product_height'])) ? $productInfo['product_height'] : "",
                    'op_product_dimension_unit' => (isset($productInfo['product_dimension_unit'])) ? $productInfo['product_dimension_unit'] : "",
                    'op_product_weight' => (isset($productInfo['product_weight'])) ? $productInfo['product_weight'] : "",
                    'op_product_weight_unit' => (isset($productInfo['product_weight_unit'])) ? $productInfo['product_weight_unit'] : "",
                    'op_shop_id' => $productInfo['shop_id'],
                    'op_shop_owner_username' => $productInfo['shop_owner_username'],
                    'op_shop_owner_name' => $productInfo['shop_onwer_name'],
                    'op_shop_owner_email' => $productInfo['shop_owner_email'],
                    'op_shop_owner_phone_code' => isset($productInfo['user_dial_code']) && !empty($productInfo['user_dial_code']) ? $productInfo['user_dial_code'] : '',
                    'op_shop_owner_phone' => isset($productInfo['shop_owner_phone']) && !empty($productInfo['shop_owner_phone']) ? $productInfo['shop_owner_phone'] : '',
                    'op_selprod_max_download_times' => ($productInfo['selprod_max_download_times'] != '-1') ? $cartProduct['quantity'] * $productInfo['selprod_max_download_times'] : $productInfo['selprod_max_download_times'],
                    'op_selprod_download_validity_in_days' => $productInfo['selprod_download_validity_in_days'],
                    'opshipping_rate_id' => $cartProduct['opshipping_rate_id'],
                    //'op_discount_total'    =>    0, //todo:: after coupon discount integration
                    //'op_tax_total'    =>    $cartProduct['tax'],
                    'op_commission_charged' => $cartProduct['commission'],
                    'op_commission_percentage' => $cartProduct['commission_percentage'],
                    'op_affiliate_commission_percentage' => $cartProduct['affiliate_commission_percentage'],
                    'op_affiliate_commission_charged' => $cartProduct['affiliate_commission'],
                    'op_status_id' => FatApp::getConfig("CONF_DEFAULT_ORDER_STATUS"),
                    // 'op_volume_discount_percentage'    =>    $cartProduct['volume_discount_percentage'],
                    'productsLangData' => $productsLangData,
                    'productShippingData' => ($cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) ? $parentShippingDetails : $productShippingData,
                    'productPickUpData' => $productPickUpData,
                    'productPickupAddress' => $productPickupAddress,
                    'productShippingLangData' => $productShippingLangData,
                    'productChargesLogData' => $productTaxChargesData,
                    /* 'op_tax_collected_by_seller'    =>    $taxCollectedBySeller, */
                    /* 'op_free_ship_upto' => $cartProduct['shop_free_ship_upto'], */
                    'op_actual_shipping_charges' => $cartProduct['shipping_cost'],
                    'op_tax_code' => $cartProduct['taxCode'],
                    'productSpecifics' => [
                        'op_selprod_return_age' => (isset($productInfo['return_age'])) ? $productInfo['return_age'] : "",
                        'op_selprod_cancellation_age' => (isset($productInfo['cancellation_age'])) ? $productInfo['cancellation_age'] : "",
                        'op_product_warranty' => (isset($productInfo['product_warranty'])) ? $productInfo['product_warranty'] : ""
                    ],
                    'op_rounding_off' => $cartProduct['rounding_off'],
                    'productsData' => $productsData, /* Order product rental data */
                );

                $order_affiliate_user_id = isset($cartProduct['affiliate_user_id']) ? $cartProduct['affiliate_user_id'] : '';
                $order_affiliate_total_commission += isset($cartProduct['affiliate_commission']) ? $cartProduct['affiliate_commission'] : '';

                $discount = 0;
                if (!empty($cartSummary["cartDiscounts"]["discountedSelProdIds"])) {
                    if (array_key_exists($productInfo['selprod_id'], $cartSummary["cartDiscounts"]["discountedSelProdIds"])) {
                        $discount = $cartSummary["cartDiscounts"]["discountedSelProdIds"][$productInfo['selprod_id']];
                    }
                }

                $shippingCost = $cartProduct['shipping_cost'];
                /* if ($cartProduct['shop_eligible_for_free_shipping'] && $cartProduct['psbs_user_id'] > 0) {
                  $shippingCost = 0;
                  } */

                $rewardPoints = 0;
                $rewardPoints = $orderData['order_reward_point_value'];
                $usedRewardPoint = 0;
                if ($rewardPoints > 0) {
                    $selProdAmount = ($cartProduct['quantity'] * $cartProduct['theprice']) + $shippingCost + $cartProduct['tax'] - $discount - $cartProduct['volume_discount_total'] - $cartProduct['duration_discount_total'];
                    $usedRewardPoint = round((($rewardPoints * $selProdAmount) / (($orderData['order_net_amount'] - $cartSummary['pendingLateCharges'] - $cartSummary['rentalSecurityTotal']) + $rewardPoints)), 2);
                    
                    $addedRewardPoints += $usedRewardPoint;
                    if ($productIndex == $totalProducts && $rewardPoints > $addedRewardPoints) { /* add pending reward points to last product after rounding off */
                        $pendingRewardPoints = $rewardPoints - $addedRewardPoints;
                        $usedRewardPoint = $pendingRewardPoints + $usedRewardPoint;
                    }
                }

                $orderData['prodCharges'][$orderKey] = array(
                    OrderProduct::CHARGE_TYPE_SHIPPING => array(
                        'amount' => $shippingCost
                    ),
                    OrderProduct::CHARGE_TYPE_TAX => array(
                        'amount' => $cartProduct['tax']
                    ),
                    OrderProduct::CHARGE_TYPE_DISCOUNT => array(
                        'amount' => -$discount /* [Should be negative value] */
                    ),
                    OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT => array(
                        'amount' => -$usedRewardPoint
                    ),
                    /* OrderProduct::CHARGE_TYPE_BATCH_DISCOUNT => array(
                      'amount' => -$cartProduct['batch_discount_single_product'] */
                    OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT => array(
                        'amount' => -$cartProduct['volume_discount_total']
                    ),
                    OrderProduct::CHARGE_TYPE_DURATION_DISCOUNT => array(
                        'amount' => -$cartProduct['duration_discount_total']
                    ),
                );
                $totalRoundingOff += $cartProduct['rounding_off'];
            }
            $orderData['order_rounding_off'] = $totalRoundingOff;
        }
        $orderData['order_affiliate_user_id'] = $order_affiliate_user_id;
        $orderData['order_affiliate_total_commission'] = $order_affiliate_total_commission;
        /* ] */
        /* ] */
        $orderObj = new Orders();
        if ($orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
            $order_id = $orderObj->getOrderId();
            $_SESSION['order_id'] = $order_id;

            // Get order_order_id from order table
            $srch = Orders::getSearchObject();
            $srch->addCondition('order_id', '=', $order_id);
            $srch->addFld('order_order_id');
            $rs = $srch->getResultSet();
            $newOrderInfo = (array) FatApp::getDb()->fetch($rs);
            $orderOrderId = (isset($newOrderInfo['order_order_id'])) ? $newOrderInfo['order_order_id'] : 0;
            $this->cartObj->updateNumericOrderId($orderOrderId);

            /* $attchObj = new AttachedFile();
              $attchObj->updateRecordId($orderOrderId['order_order_id'], $this->cartObj::getCartUserId()); */
        } else {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($orderObj->getError());
            }
            Message::addErrorMessage($orderObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* verification flds data */
        if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
            if (!empty($verificationData = $this->cartObj->getVerificationFldData())) {
                Orders::deleteOrderVerificationData($order_id);
                foreach ($verificationData as $key => $val) {
                    $arr = explode("_", $key, 2);
                    $fld_name = $arr[0];
                    $vfld_id = $arr[1];

                    $vfldName = VerificationFields::getAttributesByLangId($this->siteLangId, $vfld_id, 'vflds_name');
                    $vfldType = VerificationFields::getAttributesById($vfld_id, 'vflds_type');

                    if ($fld_name == 'fileFld') {
                        if (!$this->copyAttachmentsFromTemp($userId, $vfld_id, $orderOrderId)) {
                            Message::addErrorMessage(Labels::getLabel('LBL_Error_in_Copying_attachments', $this->siteLangId));
                            FatUtility::dieWithError(Message::getHtml());
                        }
                        /* Fatapp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, array('smt' => 'afile_type = ? AND afile_record_id = ?', 'vals' => array(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $userId))); */
                    }

                    $verificationFldData = array(
                        'ovd_order_id' => $order_id,
                        'ovd_vfld_id' => $vfld_id,
                        'ovd_value' => $val,
                        'ovd_vflds_type' => $vfldType,
                        'ovd_vflds_name' => $vfldName,
                    );

                    if (!FatApp::getDb()->insertFromArray(Orders::DB_TBL_ORDER_VERIFICATION_FIELDS, $verificationFldData, true, array(), $verificationFldData)) {
                        $this->error = FatApp::getDb()->getError();
                        return false;
                    }
                }
            }
        }


        /* --- */


        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        /* $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId, array('payment_status' => 0) ); */
        if (!$orderInfo) {
            $this->cartObj->clear();
            FatApp::redirectUser(UrlHelper::generateUrl('Buyer', 'viewOrder', array($order_id)));
        }

        $userWalletBalance = User::getUserBalance($userId, true);

        if (false === MOBILE_APP_API_CALL) {
            $WalletPaymentForm = $this->getWalletPaymentForm($this->siteLangId);
            $confirmForm = $this->getConfirmFormWithNoAmount($this->siteLangId);

            if ((FatUtility::convertToType($userWalletBalance, FatUtility::VAR_FLOAT) > 0) && $cartSummary['cartWalletSelected'] && $cartSummary['orderNetAmount'] > 0) {
                $WalletPaymentForm->addFormTagAttribute('action', UrlHelper::generateUrl('WalletPay', 'Charge', array($order_id)));
                $WalletPaymentForm->fill(array('order_id' => $order_id));
                $WalletPaymentForm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
                $WalletPaymentForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
            }

            if ($cartSummary['orderNetAmount'] <= 0) {
                $confirmForm->addFormTagAttribute('action', UrlHelper::generateUrl('ConfirmPay', 'Charge', array($order_id)));
                $confirmForm->fill(array('order_id' => $order_id));
                /* $confirmForm->setFormTagAttribute('onsubmit', 'confirmOrderWithoutPayment(this); return(false);'); */
                $confirmForm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Confirm_Order', $this->siteLangId));
            }

            $cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
            $cartDiscounts = isset($cartSummary['cartDiscounts']["coupon_discount_total"]) ? $cartSummary['cartDiscounts']["coupon_discount_total"] : 0;
            $canBeUsedPoints = min(min(UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId()), CommonHelper::convertCurrencyToRewardPoint($cartTotal - $cartDiscounts)), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));
            $redeemRewardFrm = $this->getRewardsForm($this->siteLangId, $canBeUsedPoints);
            $this->set('redeemRewardFrm', $redeemRewardFrm);
        }

        $orderPickUpData = '';
        $orderShippingData = '';
        $shippingData = [];
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $orderPickUpData = $orderObj->getOrderPickUpData($order_id, $this->siteLangId, $cartType);
        }
        if ($fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $orderShippingData = $orderObj->getOrderShippingData($order_id, $this->siteLangId, $cartType);
            foreach ($orderShippingData as $data) {
                if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                    $shippingData[$data['opshipping_code']][] = $data;
                }
            }
        }

        if ($userWalletBalance >= $cartSummary['orderNetAmount'] && $cartSummary['cartWalletSelected']) {
            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => 0));
        }

        $cartHasDigitalProduct = $this->cartObj->hasDigitalProduct();

        $oVfldsObj = $orderObj->getOrderVerificationDataSrchObj($order_id);
        $rs = $oVfldsObj->getResultSet();
        $verificationFldsData = FatApp::getDb()->fetchAll($rs);

        $this->set('verificationFldsData', $verificationFldsData);
        $this->set('paymentMethods', $paymentMethods);
        $this->set('userWalletBalance', $userWalletBalance);
        $this->set('cartSummary', $cartSummary);
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('cartHasDigitalProduct', $cartHasDigitalProduct);
        $excludePaymentGatewaysArr = applicationConstants::getExcludePaymentGatewayArr();
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('excludePaymentGatewaysArr', $excludePaymentGatewaysArr);
        if (false === MOBILE_APP_API_CALL) {
            $this->set('orderInfo', $orderInfo);
            $this->set('WalletPaymentForm', $WalletPaymentForm);
            $this->set('confirmForm', $confirmForm);
        }

        $this->set('canUseWalletForPayment', PaymentMethods::canUseWalletForPayment());
        $this->set('shippingAddressId', $shippingAddressId);
        $this->set('billingAddressId', $billingAddressId);
        $this->set('billingAddressArr', $billingAddressArr);
        $this->set('shippingAddressArr', $shippingAddressArr);
        $this->set('orderId', $order_id);
        $this->set('orderOrderId', (isset($orderOrderId)) ? $orderOrderId : 0);
        $this->set('orderPickUpData', $orderPickUpData);
        $this->set('orderShippingData', $shippingData);

        if (true === MOBILE_APP_API_CALL) {
            $this->set('products', $cartProducts);
            $this->set('orderType', $orderInfo['order_type']);
            if (0 < $useRewardPoints) {
                $this->set('msg', Labels::getLabel("MSG_Used_Reward_point", $this->siteLangId) . '-' . $useRewardPoints);
                $this->_template->render(true, true, 'checkout/use-reward-points.php');
            }
            $this->_template->render();
        }

        $this->_template->render(false, false);
    }

    public function paymentTab($order_id, $plugin_id)
    {
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

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        // CommonHelper::printArray($orderInfo);
        /* $orderObj = new Orders();
          $orderInfo = $orderObj->getOrderById( $order_id, $this->siteLangId, array('payment_status' => 0) ); */
        if (!$orderInfo) {
            /* Message::addErrorMessage( Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId) );
              $this->set('error', Message::getHtml() ); */
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId));
        }

        $methodCode = Plugin::getAttributesById($plugin_id, 'plugin_code');
        // $paymentMethod = Plugin::getAttributesByCode($methodCode, Plugin::ATTRS, $this->siteLangId);
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
            if ($this->cartObj->hasDigitalProduct()) {
                $str = Labels::getLabel('MSG_{COD}_is_not_available_if_your_cart_has_any_Digital_Product', $this->siteLangId);
                $str = str_replace('{cod}', $paymentMethod['plugin_name'], $str);
                FatUtility::dieWithError($str);
            }
            $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
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

    public function walletSelection()
    {
        $isWalletSelected = FatApp::getPostedData('payFromWallet', FatUtility::VAR_INT, 0);
        $orderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        //$this->cartObj = new Cart();
        $this->cartObj->updateCartWalletOption($isWalletSelected);
        /* UPDATE WALLET CALCULATIONS */
        /* $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
          $orderObj = new Orders();
          $orderObj->updateOrderInfo(
          $orderId,
          array(
          'order_is_wallet_selected' => $isWalletSelected,
          'order_wallet_amount_charge' => $cartSummary["WalletAmountCharge"],
          'order_net_amount' => $cartSummary["orderNetAmount"]
          )
          ); */
        //echo "<pre>"; print_r($cartSummary); echo "</pre>"; exit;

        /* ] */
        /* $this->set('isLoadPaymentTab', ($cartSummary['orderPaymentGatewayCharges'] > 0) ? 1 : 0); */
        $this->_template->render(false, false, 'json-success.php');
    }

    /* Used through payment summary api to rid off session functionality in case of APP calling. */

    public function useRewardPoints(bool $return = false)
    {
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();

        if (empty($post)) {
            $this->errMessage = Labels::getLabel('LBL_Invalid_Request', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $rewardPoints = floor($post['redeem_rewards']);

        if (empty($rewardPoints)) {
            $this->errMessage = Labels::getLabel('LBL_You_cannot_use_0_reward_points._Please_add_reward_points_greater_than_0', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        if (true === MOBILE_APP_API_CALL) {
            if (empty($post['orderId'])) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_Order_Id_Is_Required', $this->siteLangId));
            }
            $orderId = $post['orderId'];
        }

        $totalBalance = UserRewardBreakup::rewardPointBalance($loggedUserId, $orderId);

        /* var_dump($totalBalance);exit; */
        if ($totalBalance == 0 || $totalBalance < $rewardPoints) {
            $this->errMessage = Labels::getLabel('ERR_Insufficient_reward_point_balance', $this->siteLangId);
            FatUtility::dieJsonError($this->errMessage);
        }

        if (false == $return) {
            $this->cartObj = new Cart($loggedUserId, $this->siteLangId, $this->app_user['temp_user_id']);
        }

        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);

        $cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
        $cartDiscounts = isset($cartSummary['cartDiscounts']["coupon_discount_total"]) ? $cartSummary['cartDiscounts']["coupon_discount_total"] : 0;
        $cartTotalWithoutDiscount = $cartTotal - $cartDiscounts;

        $rewardPointValues = min(CommonHelper::convertRewardPointToCurrency($rewardPoints), $cartTotalWithoutDiscount);
        $rewardPoints = CommonHelper::convertCurrencyToRewardPoint($rewardPointValues);

        if ($rewardPoints < FatApp::getConfig('CONF_MIN_REWARD_POINT') || $rewardPoints > FatApp::getConfig('CONF_MAX_REWARD_POINT')) {
            $msg = Labels::getLabel('ERR_PLEASE_USE_REWARD_POINT_BETWEEN_{MIN}_TO_{MAX}', $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, array('{MIN}' => FatApp::getConfig('CONF_MIN_REWARD_POINT'), '{MAX}' => FatApp::getConfig('CONF_MAX_REWARD_POINT')));
            LibHelper::dieJsonError($msg);
        }
        if (!$this->cartObj->updateCartUseRewardPoints($rewardPoints)) {
            $this->errMessage = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (true === $return) {
            return true;
        }

        $this->set('msg', Labels::getLabel("MSG_Used_Reward_point", $this->siteLangId) . '-' . $rewardPoints);
        if (true === MOBILE_APP_API_CALL) {
            $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
            $cartProducts = $this->cartObj->getProducts($this->siteLangId);
            $this->set('cartSummary', $cartSummary);
            $this->set('products', $cartProducts);
            $this->_template->render();
        }

        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeRewardPoints()
    {
        $cartObj = new Cart(UserAuthentication::getLoggedUserId(true), $this->siteLangId, $this->app_user['temp_user_id']);
        if (!$cartObj->removeUsedRewardPoints()) {
            $this->errMessage = Labels::getLabel('LBL_Action_Trying_Perform_Not_Valid', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel("MSG_used_reward_point_removed", $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $orderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
            if (empty($orderId)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_ORDER_ID_IS_REQUIRED', $this->siteLangId));
            }

            $orderObj = new Orders();
            $orderInfo = $orderObj->getOrderById($orderId, $this->siteLangId);
            $financialSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $cartSummary = $cartObj->getCartFinancialSummary($this->siteLangId);
            $cartProducts = $cartObj->getProducts($this->siteLangId);

            $orderData['order_id'] = $orderId;
            $orderData['order_type'] = $orderInfo["order_type"];
            $orderData['order_net_amount'] = $financialSummary["orderNetAmount"];
            $orderData['order_reward_point_used'] = $financialSummary["cartRewardPoints"];
            $orderData['order_reward_point_value'] = CommonHelper::convertRewardPointToCurrency($financialSummary["cartRewardPoints"]);
            if (!$orderObj->addUpdateOrder($orderData, $this->siteLangId)) {
                FatUtility::dieJsonError($orderObj->getError());
            }

            $this->set('cartSummary', $cartSummary);
            $this->set('products', $cartProducts);
            $this->_template->render(true, true, 'checkout/use-reward-points.php');
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function confirmOrder()
    {
        $order_type = FatApp::getPostedData('order_type', FatUtility::VAR_INT, 0);
        $plugin_id = FatApp::getPostedData('plugin_id', FatUtility::VAR_INT, 0);

        $order_id = FatApp::getPostedData("order_id", FatUtility::VAR_STRING, "");

        $user_id = UserAuthentication::getLoggedUserId();
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
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
            if (Orders::ORDER_WALLET_RECHARGE != $order_type && $cartSummary['cartWalletSelected'] && $userWalletBalance >= $orderNetAmount) {
                $sendToWeb = $plugin_id = 0;
                $paymentUrl = UrlHelper::generateFullUrl('WalletPay', 'charge', array($order_id));
            }
            if (empty($paymentUrl)) {
                LibHelper::dieJsonError(Labels::getLabel('MSG_Please_Select_Payment_Method', $this->siteLangId));
            }
            $this->set('sendToWeb', $sendToWeb);
            $this->set('orderPayment', $paymentUrl);
        }

        /* Loading Money to wallet[ */
        if ($order_type == Orders::ORDER_WALLET_RECHARGE) {
            $criteria = array('isUserLogged' => true);
            if (!$this->isEligibleForNextStep($criteria)) {
                $this->errMessage = Labels::getLabel('MSG_Something_went_wrong,_please_try_after_some_time.', $this->siteLangId);
                LibHelper::dieJsonError($this->errMessage);
            }

            $user_id = UserAuthentication::getLoggedUserId();

            if ($order_id == '') {
                $this->errMessage = Labels::getLabel("MSG_INVALID_Request", $this->siteLangId);
                LibHelper::dieJsonError($this->errMessage);
            }
            $orderObj = new Orders();

            $srch = Orders::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addCondition('order_id', '=', $order_id);
            $srch->addCondition('order_user_id', '=', $user_id);
            $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PENDING);
            $srch->addCondition('order_type', '=', Orders::ORDER_WALLET_RECHARGE);
            $rs = $srch->getResultSet();
            $orderInfo = FatApp::getDb()->fetch($rs);
            if (!$orderInfo) {
                $this->errMessage = Labels::getLabel("MSG_INVALID_ORDER_PAID_CANCELLED", $this->siteLangId);
                LibHelper::dieJsonError($this->errMessage);
            }

            //No Need to clear cart in case of wallet recharge
            /* $this->cartObj->clear();
              $this->cartObj->updateUserCart(); */

            $orderObj->updateOrderInfo($order_id, array('order_pmethod_id' => $plugin_id));

            if (true === MOBILE_APP_API_CALL) {
                $this->_template->render();
            }
            $this->_template->render(false, false, 'json-success.php');
        }
        /* ] */

        /* confirmOrder function is called for both wallet payments and for paymentgateway selection as well. */
        $criteria = array('isUserLogged' => true, 'hasProducts' => true, 'hasStock' => true, 'hasBillingAddress' => true);
        $fulfillmentType = $this->cartObj->getCartCheckoutType();
        if ($this->cartObj->hasPhysicalProduct() && $fulfillmentType == Shipping::FULFILMENT_SHIP) {
            $criteria['hasShippingAddress'] = true;
            $criteria['isProductShippingMethodSet'] = true;
        }
        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP) {
            $criteria['isProductPickUpAddrSet'] = true;
        }

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

        $srch = Orders::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('order_id', '=', $order_id);
        $srch->addCondition('order_user_id', '=', $user_id);
        $srch->addCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_PENDING);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);

        if (!$orderInfo) {
            $this->errMessage = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            LibHelper::dieJsonError($this->errMessage);
        }
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
            // $this->cartObj->clear();
            // $this->cartObj->updateUserCart();
        }

        /* Deduct reward point in case of cashondelivery [ */
        if (in_array(strtolower($pmethodCode), ['cashondelivery', 'payatstore']) && FatUtility::float($orderInfo['order_reward_point_used']) > 0) {
            $rewardDebited = UserRewards::debit($orderInfo['order_user_id'], $orderInfo['order_reward_point_used'], $order_id, $orderInfo['order_language_id']);
            if (!$rewardDebited) {
                $msg = Labels::getLabel("MSG_UNABLE_TO_DEBIT_REWARD_POINTS", $this->siteLangId);
                LibHelper::dieJsonError($msg);
            }
        }

        /* ] */

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function editAddress()
    {
        $post = FatApp::getPostedData();
        $address_id = isset($post['address_id']) ? FatUtility::int($post['address_id']) : 0;
        $addressFrm = $this->getUserAddressForm($this->siteLangId);

        $address = new Address($address_id, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        if ($address_id) {
            $stateId = $addresses['addr_state_id'];
        } else {
            $stateId = 0;
        }
		if (!empty($addresses) && isset($addresses['addr_country_iso'])) {
			$this->set('countryIso',$addresses['addr_country_iso']);
		}
		
		$addressFrm->fill($addresses);
        $this->set('addressFrm', $addressFrm);
        $this->set('address_id', $address_id);
        if ($address_id > 0) {
            $labelHeading = Labels::getLabel('LBL_Edit_Address', $this->siteLangId);
        } else {
            $labelHeading = Labels::getLabel('LBL_Add_Address', $this->siteLangId);
        }

        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }

        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('labelHeading', $labelHeading);
        $this->set('stateId', $stateId);
        $addressType = FatApp::getPostedData('address_type', FatUtility::VAR_INT, 0);
        $this->set('addressType', $addressType);
        $this->_template->render(false, false, 'checkout/address-form.php');
    }

    private function getCheckoutAddressForm($langId)
    {
        $frm = new Form('frmAddress');
        $address = new Address(0, $langId);
        $addresses = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());

        $addressAssoc = array();
        foreach ($addresses as $address) {
            $city = $address['addr_city'];
            $state = (strlen($address['addr_city']) > 0) ? ', ' . $address['state_name'] : $address['state_name'];
            $country = (strlen($state) > 0) ? ', ' . $address['country_name'] : $address['country_name'];
            $location = $city . $state . $country;
            $addressAssoc[$address['addr_id']] = $location;
        }
        $frm->addRadioButtons('', 'shipping_address_id', $addressAssoc);
        $frm->addRadioButtons('', 'billing_address_id', $addressAssoc);
        return $frm;
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

    private function getWalletPaymentForm($langId)
    {
        $frm = new Form('frmWalletPayment');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getConfirmFormWithNoAmount($langId)
    {
        $frm = new Form('frmConfirmForm');
        $frm->addHiddenField('', 'order_id');
        return $frm;
    }

    private function getRewardsForm($langId, $canBeUsedPoints = 0)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmRewards');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Reward_Points', $langId), 'redeem_rewards', '', array());
        $fld->requirements()->setRequired();
        $minRewardPoints = FatApp::getConfig('CONF_MIN_REWARD_POINT', FatUtility::VAR_INT, 0);

        $fld->requirements()->setRange($minRewardPoints, $canBeUsedPoints);
        if ($minRewardPoints == $canBeUsedPoints) {
            $fld->requirements()->setCustomErrorMessage(sprintf(Labels::getLabel('LBL_Reward_Points_is_required_and_must_be_%s', $this->siteLangId), $minRewardPoints));
        } else if ($minRewardPoints > $canBeUsedPoints) {
            $fld->requirements()->setCustomErrorMessage(sprintf(Labels::getLabel('LBL_Reward_Points_is_required_and_must_be_more_than_%s', $this->siteLangId), $minRewardPoints));
        } else {
            $fld->requirements()->setCustomErrorMessage(sprintf(Labels::getLabel('LBL_Reward_Points_is_required_and_must_be_between_%s_and_%s', $this->siteLangId), $minRewardPoints, $canBeUsedPoints));
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function resetShippingSummary()
    {
        $this->_template->render(false, false);
    }

    public function resetCartReview()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->_template->render(false, false);
    }

    public function resetPaymentSummary()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->_template->render(false, false);
    }

    public function loadCartReview()
    {
        $cartHasPhysicalProduct = false;
        if ($this->cartObj->hasPhysicalProduct()) {
            $cartHasPhysicalProduct = true;
        }
        $products = $this->cartObj->getProducts($this->siteLangId);
        $this->set('cartHasPhysicalProduct', $cartHasPhysicalProduct);
        $this->set('products', $products);
        $this->_template->render(false, false);
    }

    public function loadShippingSummary()
    {
        $products = $this->cartObj->getProducts($this->siteLangId);
        $this->set('products', $products);
        $this->_template->render(false, false);
    }

    public function removeShippingSummary()
    {
        $this->cartObj->removeProductShippingMethod();
    }

    public function getFinancialSummary()
    {
        $this->cartObj->disableCache();
        $cartSummary = $this->cartObj->getCartFinancialSummary($this->siteLangId);
        /* $products = $this->cartObj->getProducts($this->siteLangId); */
        $products = $this->cartObj->getCartProductsDataAccToAddons($this->siteLangId, true);
        $shippingAddress = $this->cartObj->getCartShippingAddress();
        $this->set('shippingAddress', $shippingAddress);
        $this->set('products', $products);
        $this->set('cartSummary', $cartSummary);
        $data = $this->_template->render(false, false, 'checkout/get-financial-summary.php', true, false);
        $this->set('netAmount', CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount']));
        $this->set('data', $data);
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function getCouponForm()
    {
        /* if( !UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()){
          Message::addErrorMessage(Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId));
          FatUtility::dieWithError( Message::getHtml() );
          } */
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        $couponsList = DiscountCoupons::getValidCoupons($loggedUserId, $this->siteLangId, '', $orderId);
        $this->set('couponsList', $couponsList);

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $PromoCouponsFrm = $this->getPromoCouponsForm($this->siteLangId);
        $this->set('PromoCouponsFrm', $PromoCouponsFrm);
        $this->_template->render(false, false);
    }

    private function getPromoCouponsForm($langId)
    {
        $langId = FatUtility::int($langId);
        $frm = new Form('frmPromoCoupons');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Coupon_code', $langId), 'coupon_code', '', array('placeholder' => Labels::getLabel('LBL_Enter_Your_code', $langId)));
        $fld->requirements()->setRequired();
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Apply', $langId));
        return $frm;
    }

    public function setUpPickUp()
    {
        $this->cartObj->removeProductShippingMethod();
        $post = FatApp::getPostedData();
        $cartType = $this->cartObj->getCartType();

        $shopIds = [];
        if ($cartType != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $pickupOptions = $this->cartObj->getPickupOptions();
            $pickupAddressArr = [];
            $cartProducts = $this->cartObj->getCartProductsDataAccToAddons();

            foreach ($cartProducts as $key => $cartProduct) {

                if (!isset($post[$key]) || (isset($post[$key]) && intval($post[$key]) < 1) || empty($pickupOptions[$cartProduct['selprod_id']])) {
                    $message = Labels::getLabel('MSG_Pickup_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }

                if (!in_array($post[$key], $pickupOptions[$cartProduct['selprod_id']])) {
                    $message = Labels::getLabel('MSG_INVALID_PICKUP_ADDRESS', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }

                $shopIds[$cartProduct['shop_id']] = $cartProduct['shop_id'];
                
                $updatedKey = $key;
                if ($cartType == applicationConstants::PRODUCT_FOR_SALE) {
                    $updatedKey = $cartProduct['selprod_id'];
                }
                if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                    $shipBySeller = $cartProduct['selprod_user_id'];
                } else {
                    $shipBySeller = Product::isShippedBySeller($cartProduct['selprod_user_id'], $cartProduct['product_seller_id'], $cartProduct['shippedBySellerId']);
                }

                $pickupAddressArr[$updatedKey] = array(
                    'selprod_id' => $cartProduct['selprod_id'],
                    'shipped_by_seller' => $shipBySeller,
                    'time_slot_addr_id' => $post[$key],
                    'time_slot_id' => '',
                    'time_slot_type' => '',
                    'time_slot_from_time' => '',
                    'time_slot_to_time' => '',
                    'time_slot_date' => '',
                );

                if (!empty($cartProduct['addonsData'])) {
                    foreach ($cartProduct['addonsData'] as $addonKey => $addonProduct) {
                        $pickupAddressArr[$addonKey] = array(
                            'selprod_id' => $addonProduct['selprod_id'],
                            'shipped_by_seller' => 1,
                            'time_slot_addr_id' => $post[$key],
                            'time_slot_id' => '',
                            'time_slot_type' => '',
                            'time_slot_from_time' => '',
                            'time_slot_to_time' => '',
                            'time_slot_date' => '',
                        );
                    }
                }
            }

            if (!empty($pickupAddressArr)) {
                $this->cartObj->setProductPickUpAddresses($pickupAddressArr);
                $this->set('msg', Labels::getLabel('MSG_Pickup_Method_selected_successfully.', $this->siteLangId));
            }
        } else {
            $this->set('msg', Labels::getLabel('MSG_Self_Picked', $this->siteLangId));
        }
        $attachmentArr = [];
        if (!empty($shopIds) && $this->cartObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
            $fileSrch = AttachedFile::getSearchObject();
            $fileSrch->addCondition('afile_type', '=', AttachedFile::FILETYPE_SHOP_AGREEMENT);
            $fileSrch->addCondition('afile_record_id', 'IN', $shopIds);
            $attachmentArr = FatApp::getDb()->fetchAll($fileSrch->getResultSet(), 'afile_record_id');
        }
   
        if ((FatApp::getConfig("CONF_ENABLE_DOCUMENT_VERIFICATION", FatUtility::VAR_INT, 1) && $this->cartObj->isRequireVerification() && $this->cartObj->getCartType() != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) || (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1) && !empty($attachmentArr))) {
            $this->set('section', 'verification');
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUpPickUpForSell()
    {
        $this->cartObj->removeProductShippingMethod();
        $post = FatApp::getPostedData();

        $pickupAddressArr = array();
        // $basketProducts = $this->cartObj->getBasketProducts($this->siteLangId);
        $basketProducts = [];
        $pickupOptions = $this->cartObj->getSellPickupOptions($basketProducts);

        foreach ($post['slot_id'] as $pickUpBy => $slotId) {
            if (empty($slotId) || empty($post['slot_date'][$pickUpBy])) {
                $message = Labels::getLabel('MSG_Pickup_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            $slotData = TimeSlot::getAttributesById($slotId);
            if (empty($slotData)) {
                $message = Labels::getLabel('MSG_NO_TIME_SLOT_FOUND.', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            $selectedDate = $post['slot_date'][$pickUpBy];
            $selectedDay = date('w', strtotime($selectedDate));
            if ($selectedDay != $slotData['tslot_day']) {
                $message = Labels::getLabel('MSG_INVALID_SLOT_DAY.', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            if (array_search($slotData['tslot_record_id'], array_column($pickupOptions[$pickUpBy]['pickup_options'], 'addr_id')) === false) {
                $message = Labels::getLabel('MSG_INVALID_PICKUP_ADDRESS.', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            $cartProducts = $this->cartObj->getProducts($this->siteLangId);
            if (empty($cartProducts)) {
                $message = Labels::getLabel('MSG_YOUR_CART_IS_EMPTY', $this->siteLangId);
                LibHelper::exitWithError($message, true);
            }

            $cartType = $this->cartObj->getCartType();

            foreach ($pickupOptions[$pickUpBy]['products'] as $pickupProduct) {
                foreach ($cartProducts as $cartKey => $cartval) {
                    if ($cartval['selprod_id'] != $pickupProduct['selprod_id'] || $cartval['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                        continue;
                    }
                    /* get Product Data[ */
                    $prodSrch = new ProductSearch();
                    $prodSrch->setDefinedCriteria();
                    $prodSrch->joinProductToCategory();
                    $prodSrch->joinProductShippedBy();
                    $prodSrch->joinProductFreeShipping();
                    $prodSrch->joinSellerSubscription();
                    $prodSrch->addSubscriptionValidCondition();
                    $prodSrch->doNotCalculateRecords();
                    $prodSrch->doNotLimitRecords();
                    $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                    $prodSrch->addCondition('selprod_id', '=', $cartval['selprod_id']);
                    $prodSrch->addMultipleFields(array('selprod_id', 'product_seller_id', 'psbs_user_id as shippedBySellerId'));
                    $productRs = $prodSrch->getResultSet();
                    $productInfo = FatApp::getDb()->fetch($productRs);
                    /* ] */

                    if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                        $shipBySeller = $cartval['selprod_user_id'];
                    } else {
                        $shipBySeller = Product::isShippedBySeller($cartval['selprod_user_id'], $productInfo['product_seller_id'], $productInfo['shippedBySellerId']);
                    }

                    $pickupAddressArr[$cartval['selprod_id']] = array(
                        'selprod_id' => $cartval['selprod_id'],
                        'shipped_by_seller' => $shipBySeller,
                        'time_slot_addr_id' => $slotData['tslot_record_id'],
                        'time_slot_id' => $slotData['tslot_id'],
                        'time_slot_type' => $slotData['tslot_type'],
                        'time_slot_from_time' => $slotData['tslot_from_time'],
                        'time_slot_to_time' => $slotData['tslot_to_time'],
                        'time_slot_date' => $selectedDate,
                    );
                }
            }
        }

        $this->cartObj->setProductPickUpAddresses($pickupAddressArr);
        $this->set('msg', Labels::getLabel('MSG_Pickup_Method_selected_successfully.', $this->siteLangId));
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->_template->render(false, false, 'json-success.php');
    }


    public function setUpPickUpBackup()
    {
        $this->cartObj->removeProductShippingMethod();
        $post = FatApp::getPostedData();

        $pickupAddressArr = array();
        $basketProducts = [];
        $cartType = $this->cartObj->getCartType();
        $pickupOptions = $this->cartObj->getPickupOptions($basketProducts, $cartType);

        if ($cartType != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {

            foreach ($post['slot_id'] as $key => $slotId) {

                if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                    $rentalData = explode('_', $key);
                    $rentalStartDate = $rentalData[0];
                    $selprodKey = $rentalData[1];
                    $pickUpBy = $post['slot_pickupby'][$key];
                } else {
                    $pickUpBy = $post['slot_pickupby'][$key];
                }

                if (empty($slotId) || empty($post['slot_date'][$key])) {
                    $message = Labels::getLabel('MSG_Pickup_Method_is_not_selected_on_products_in_cart', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }

                $slotData = TimeSlot::getAttributesById($slotId);
                if (empty($slotData)) {
                    $message = Labels::getLabel('MSG_NO_TIME_SLOT_FOUND.', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }

                $selectedDate = $post['slot_date'][$key];
                $selectedDay = date('w', strtotime($selectedDate));

                if ($selectedDay != $slotData['tslot_day']) {
                    $message = Labels::getLabel('MSG_INVALID_SLOT_DAY.', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }

                if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                    if (array_search($slotData['tslot_record_id'], array_column($pickupOptions[$rentalStartDate][$pickUpBy]['pickup_options'][$selprodKey], 'addr_id')) === false) {
                        $message = Labels::getLabel('MSG_INVALID_PICKUP_ADDRESS.', $this->siteLangId);
                        LibHelper::exitWithError($message, true);
                    }
                } else {
                    if (array_search($slotData['tslot_record_id'], array_column($pickupOptions[$pickUpBy]['pickup_options'][$key], 'addr_id')) === false) {
                        $message = Labels::getLabel('MSG_INVALID_PICKUP_ADDRESS.', $this->siteLangId);
                        LibHelper::exitWithError($message, true);
                    }
                }

                $cartProducts = $this->cartObj->getProducts($this->siteLangId);
                if (empty($cartProducts)) {
                    $message = Labels::getLabel('MSG_YOUR_CART_IS_EMPTY', $this->siteLangId);
                    LibHelper::exitWithError($message, true);
                }


                if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                    //$productsArr = $pickupOptions[$rentalStartDate][$pickUpBy]['products'];
                    $productsGrpArr = isset($pickupOptions[$rentalStartDate][$pickUpBy]['pickupGrp']['g_' . $selprodKey]) ? $pickupOptions[$rentalStartDate][$pickUpBy]['pickupGrp']['g_' . $selprodKey] : [];
                } else {
                    $productsGrpArr = isset($pickupOptions[$pickUpBy]['pickupGrp']['g_' . $key]) ? $pickupOptions[$pickUpBy]['pickupGrp']['g_' . $key] : [];
                }

                if (!empty($productsGrpArr)) {
                    foreach ($productsGrpArr as $selprod_id_val) {
                        foreach ($cartProducts as $cartKey => $cartval) {
                            if ($cartval['selprod_id'] != $selprod_id_val) {
                                continue;
                            }

                            $prodSrch = new ProductSearch();
                            $prodSrch->setDefinedCriteria();
                            $prodSrch->joinProductToCategory();
                            $prodSrch->joinProductShippedBy();
                            $prodSrch->joinProductFreeShipping();
                            $prodSrch->joinSellerSubscription();
                            $prodSrch->addSubscriptionValidCondition();
                            $prodSrch->doNotCalculateRecords();
                            $prodSrch->doNotLimitRecords();
                            $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                            $prodSrch->addCondition('selprod_id', '=', $cartval['selprod_id']);
                            $prodSrch->addMultipleFields(array('selprod_id', 'product_seller_id', 'psbs_user_id as shippedBySellerId'));
                            $productRs = $prodSrch->getResultSet();
                            $productInfo = FatApp::getDb()->fetch($productRs);

                            if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                                $shippedBySeller = $cartval['selprod_user_id'];
                                $updateKey = $cartKey;
                                if (strtotime($rentalStartDate) != strtotime(date('Y-m-d', strtotime($cartval['rentalStartDate'])))) {
                                    continue;
                                }
                            } else {
                                $updateKey = $cartval['selprod_id'];
                                $shippedBySeller = Product::isShippedBySeller($cartval['selprod_user_id'], $productInfo['product_seller_id'], $productInfo['shippedBySellerId']);
                            }

                            $pickupAddressArr[$updateKey] = array(
                                'selprod_id' => $cartval['selprod_id'],
                                'shipped_by_seller' => $shippedBySeller,
                                'time_slot_addr_id' => $slotData['tslot_record_id'],
                                'time_slot_id' => $slotData['tslot_id'],
                                'time_slot_type' => $slotData['tslot_type'],
                                'time_slot_from_time' => $slotData['tslot_from_time'],
                                'time_slot_to_time' => $slotData['tslot_to_time'],
                                'time_slot_date' => $selectedDate,
                            );
                        }
                    }
                }

                $this->cartObj->setProductPickUpAddresses($pickupAddressArr);
                $this->set('msg', Labels::getLabel('MSG_Pickup_Method_selected_successfully.', $this->siteLangId));
            }
        } else {
            $this->set('msg', Labels::getLabel('MSG_Self_Picked', $this->siteLangId));
        }
        //echo"<pre>"; print_r($pickupAddressArr); echo"</pre>"; die();
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setUpBillingAddressSelection()
    {
        if (!UserAuthentication::isUserLogged() && !UserAuthentication::isGuestUserLogged()) {
            $this->errMessage = Labels::getLabel('MSG_Your_Session_seems_to_be_expired.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', UrlHelper::generateUrl('GuestUser', 'LoginForm'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $billing_address_id = FatApp::getPostedData('billing_address_id', FatUtility::VAR_INT, 0);
        $isShippingSameAsBilling = FatApp::getPostedData('isShippingSameAsBilling', FatUtility::VAR_INT, 0);

        $hasProducts = $this->cartObj->hasProducts();
        $hasStock = $this->cartObj->hasStock();
        if ((!$hasProducts) || (!$hasStock)) {
            $this->errMessage = Labels::getLabel('MSG_Cart_seems_to_be_empty_or_products_are_out_of_stock.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            $this->set('redirectUrl', UrlHelper::generateUrl('cart'));
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (1 > $billing_address_id) {
            $this->errMessage = Labels::getLabel('MSG_Please_select_Billing_address.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }

        $address = new Address($billing_address_id);
        $billingAddressDetail = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
        if (!$billingAddressDetail) {
            $this->errMessage = Labels::getLabel('MSG_Invalid_Billing_Address.', $this->siteLangId);
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError($this->errMessage);
            }
            Message::addErrorMessage($this->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->cartObj->setCartBillingAddress($billingAddressDetail['addr_id']);

        if ($isShippingSameAsBilling == 0) {
            $this->cartObj->unSetShippingAddressSameAsBilling();
        }

        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }
        $this->set('msg', Labels::getLabel('MSG_Address_Selection_Successfull', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function orderPickUpData()
    {
        $orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
        $order = new Orders();
        $orderPickUpData = $order->getOrderPickUpData($orderId, $this->siteLangId, $this->cartObj->getCartType());
        $this->set('orderPickUpData', $orderPickUpData);
        $this->_template->render(false, false);
    }

    public function resendOtp()
    {
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

    public function validateOtp()
    {
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

    public function orderShippingData()
    {
        $orderId = FatApp::getPostedData('order_id', FatUtility::VAR_STRING, '');
        $order = new Orders();
        $orderShippingData = $order->getOrderShippingData($orderId, $this->siteLangId);
        $shippingData = [];
        foreach ($orderShippingData as $data) {
            $shippingData[$data['opshipping_code']][] = $data;
        }
        $this->set('orderShippingData', $shippingData);
        $this->_template->render(false, false);
    }

    public function getVerificationForm($orderId = '', $verificationData = [], $orderNumerickey = 0)
    {
        $siteLangId = $this->siteLangId;
        $frm = new Form('frmCheckoutVerification');
        $data = $this->cartObj->getFilteredVerificationFldsData(true);

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

        $frm->addSubmitButton(Labels::getLabel('LBL_Continue', $siteLangId), 'btn_submit', Labels::getLabel('LBL_Submit', $siteLangId));
        return $frm;
    }

    private function getCartRentalAddonProductLangData($selprod_id, $langId)
    {
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(Shop::DB_TBL, 'INNER JOIN', 'shop.shop_user_id = sp.selprod_user_id', 'shop');
        $srch->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 's_l.' . Shop::DB_TBL_LANG_PREFIX . 'shop_id = shop.' .
            Shop::tblFld('id') . ' and s_l.' . Shop::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 's_l');
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_id', '=', $selprod_id);
        $fields = array('IFNULL(selprod_title, selprod_identifier) as selprod_title', 'IFNULL(shop_name, shop_identifier) as shop_name', 'IFNULL(selprod_title, selprod_identifier) as product_name');
        $srch->addMultipleFields($fields);
        $langProdRs = $srch->getResultSet();
        $langSpecificProductInfo = FatApp::getDb()->fetch($langProdRs);
        if (empty($langSpecificProductInfo)) {
            return $langSpecificProductInfo;
        }
        $langSpecificProductInfo['brand_name'] = '';
        return $langSpecificProductInfo;
    }

    private function copyAttachmentsFromTemp(int $userId, int $vfld_id, $orderId)
    {
        if (1 > $userId || 1 > $vfld_id || "" == $orderId) {
            return false;
        }

        $crt = array(
            'afile_type' => AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, 'afile_record_id' => $userId,
            'afile_record_subid' => $vfld_id
        );
        $attachedFiles = AttachedFile::getTempImagesWithCriteria($crt);

        if (!empty($attachedFiles)) {
            foreach ($attachedFiles as $attachedFile) {
                $attachedFile['afile_record_id'] = $orderId;
                unset($attachedFile['afile_id']);
                unset($attachedFile['afile_unique']);
                unset($attachedFile['afile_downloaded']);
                if (!FatApp::getDb()->insertFromArray(AttachedFile::DB_TBL, $attachedFile, false, array(), $attachedFile)) {
                    return false;
                }

                /* $record = new AttachedFile();
                  $record->assignValues($attachedFile);
                  if (!$record->save()) {
                  return false;
                  } */
            }

            $whr = array(
                'smt' => 'afile_type = ? and afile_record_id = ? AND afile_record_subid = ?',
                'vals' => array(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $userId, $vfld_id)
            );

            FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, $whr); /* DELETE RECORD FROM TEMP TABLE */
        }
        return true;
    }

    public function getSortedVerificationFldsData()
    {
        $filteredVfldData = $this->cartObj->getFilteredVerificationFldsData();

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

    public function downloadDigitalFile(int $recordId, int $aFileId, int $fileType, $isPreview = false, $w = 100, $h = 100)
    {
        if (1 > $aFileId || 1 > $recordId) {
            FatUtility::exitWithErrorCode(404);
        }

        $attachFileRow = AttachedFile::getAttributesById($aFileId);
        $folderName = AttachedFile::FILETYPE_SHOP_AGREEMENT_PATH;

        if (!file_exists(CONF_UPLOADS_PATH . $folderName . $attachFileRow['afile_physical_path'])) {
            /* Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId)); */
            FatUtility::exitWithErrorCode(404);
        }

        if ($isPreview) {
            AttachedFile::displayImage($folderName . $attachFileRow['afile_physical_path'], $w, $h);
        } else {
            AttachedFile::downloadAttachment($folderName . $attachFileRow['afile_physical_path'], $attachFileRow['afile_name']);
        }
    }

    public function removeUploadedFile(int $fileId)
    {
        $recordId = $this->cartObj->getOrderNumericKey();
        $attachObj = new AttachedFile();
        if (!$attachObj->deleteFile(AttachedFile::FILETYPE_VERIFICATION_ATTACHMENT, $recordId, $fileId)) {
            Message::addErrorMessage($attachObj->errMessage);
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function dummypopup()
    {
        $this->_template->render(false, false);
    }
    
    public function uploadSignFile() 
    {
        $signFile = (isset($_FILES['sign_file'])) ? $_FILES['sign_file'] : [];
        if (empty($signFile)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Signature_File_Is_Required', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        
        /* [ REMOVE PREVIOUS UPLOADED FILES */
        $signOldFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, Cart::getCartUserId(), 0, -1, true, 0, false);
        if (!empty($signOldFile)) {
            $path = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_SIGNATURE_IMAGE_PATH . $signOldFile['afile_physical_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
        /* ] */
        
        $fileHandlerObj = new AttachedFile();
        
        if (!$fileHandlerObj->saveAttachment($signFile['tmp_name'], AttachedFile::FILETYPE_SIGNATURE_IMAGE, Cart::getCartUserId(), 0, $signFile['name'], 0, true, $this->siteLangId, 0, 0, false)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        
        if (!$this->cartObj->setSetDigintalSign()) {
            Message::addErrorMessage(Labels::getLabel('LBL_Somthing_Went_Wrong', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        $signatureData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, Cart::getCartUserId(), 0, -1, true, 0, false);
        
        $tpl = new FatTemplate('', '');
        $tpl->set('siteLangId', $this->siteLangId);
        $tpl->set('isTempFile', true);
        $tpl->set('signatureData', $signatureData);
        $this->set('msg', Labels::getLabel('LBL_Signature_Uploaded_Successfully', $this->siteLangId));
        $this->set('sectionHtml', $tpl->render(false, false, 'checkout/signature-section.php', true, true));
        $this->_template->render(false, false, 'json-success.php', false, false);
    }
    
    private function getSignatureForm()
    {
        $frm = new Form('frmSignImage');
        $fld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_Your_Signature', $this->siteLangId), 'sign_file', array('accept' => 'image/*', 'enctype' => "multipart/form-data"))->requirements()->setRequired(true);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_File', $this->siteLangId));
        return $frm;
    }
    
}
