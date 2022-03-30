<?php

class Cart extends FatModel
{

    private $products = array();
    private $SYSTEM_ARR = array();
    private $warning;
    private $shippingService;
    private $cartCache;
    private $valdateCheckoutType;
    private $fulfilmentType = 0;
    private $includeTax = true;
    private $pageType = 0;
    private $discounts = 0;
    private $selectedShippingService = [];

    public const DB_TBL = 'tbl_user_cart';
    public const DB_TBL_PREFIX = 'usercart_';
    public const CART_KEY_PREFIX_PRODUCT = 'SP_'; /* SP stands for Seller Product */
    public const CART_KEY_PREFIX_BATCH = 'SB_'; /* SB stands for Seller Batch/Combo Product */
    public const TYPE_PRODUCT = 1;
    public const TYPE_SUBSCRIPTION = 2;
    public const PAGE_TYPE_CART = 1;
    public const PAGE_TYPE_CHECKOUT = 2;
    public const CART_MAX_DISPLAY_QTY = 9;

    public function __construct(int $user_id = 0, int $langId = 0, $tempCartUserId = 0, $pageType = 0)
    {
        parent::__construct();
        $this->valdateCheckoutType = true;
        $this->includeTax = true;
        $this->cart_lang_id = $langId;
        if (1 > $langId) {
            $this->cart_lang_id = CommonHelper::getLangId();
        }

        if (empty($tempCartUserId)) {
            $user_id = (0 < $user_id) ? $user_id : UserAuthentication::getLoggedUserId(true);
            $tempCartUserId = (0 < $user_id) ? $user_id : session_id();
        }

        $this->cart_user_id = $tempCartUserId;
        $this->cart_id = $tempCartUserId;

        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged() || ($user_id > 0)) {
            if ($user_id > 0) {
                $this->cart_user_id = $user_id;
            } else {
                $this->cart_user_id = UserAuthentication::getLoggedUserId();
            }
        }

        $srch = new SearchBase('tbl_user_cart');
		$srch->doNotCalculateRecords();
        $srch->addCondition('usercart_user_id', '=', $this->cart_user_id);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();
        $this->cartSameSessionUser = true;
        if ($row = FatApp::getDb()->fetch($rs)) {
            if ($row['usercart_last_session_id'] != $this->cart_id) {
                $this->cartSameSessionUser = false;
            }

            $this->SYSTEM_ARR['cart'] = json_decode($row["usercart_details"], true);
            // CommonHelper::printArray($this->SYSTEM_ARR['cart'], true);
            if (isset($this->SYSTEM_ARR['cart']['shopping_cart'])) {
                $this->SYSTEM_ARR['shopping_cart'] = $this->SYSTEM_ARR['cart']['shopping_cart'];
                unset($this->SYSTEM_ARR['cart']['shopping_cart']);
            }
        }

        if (!$this->cartSameSessionUser) {
            $this->removeUsedRewardPoints();
        }

        if (!isset($this->SYSTEM_ARR['cart']) || !is_array($this->SYSTEM_ARR['cart'])) {
            $this->SYSTEM_ARR['cart'] = array();
        }
        if (!isset($this->SYSTEM_ARR['shopping_cart']) || !is_array($this->SYSTEM_ARR['shopping_cart'])) {
            $this->SYSTEM_ARR['shopping_cart'] = array();
        }

        if (!isset($this->SYSTEM_ARR['cart']['products']) || !is_array($this->SYSTEM_ARR['cart']['products'])) {
            $this->SYSTEM_ARR['cart']['products'] = array();
        }

        $this->cartCache = true;
        $this->pageType = $pageType;
        $this->discounts = [];
    }

    public static function getCartKeyPrefixArr()
    {
        return array(
            static::CART_KEY_PREFIX_PRODUCT => static::CART_KEY_PREFIX_PRODUCT,
            static::CART_KEY_PREFIX_BATCH => static::CART_KEY_PREFIX_BATCH,
        );
    }

    public static function getCartUserId($tempUserId = 0)
    {
        $cart_user_id = session_id();
        if ($tempUserId != 0) {
            $cart_user_id = $tempUserId;
        }
        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
            $cart_user_id = UserAuthentication::getLoggedUserId();
        }
        return $cart_user_id;
    }

    public static function getCartData($userId)
    {
        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $userId);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();
        if ($row = FatApp::getDb()->fetch($rs)) {
            return $row["usercart_details"];
        }
        return;
    }

    public function add(int $selprod_id, int $qty = 1, int $prodgroup_id = 0, bool $returnUserId = false, int $productFor, array $extraData = array()): bool
    {
        $this->products = array();
        if ($selprod_id < 1 || $qty < 1) {
            return false;
        }
        if (!empty($extraData) && isset($extraData['extendOrder']) && $extraData['extendOrder'] > 0) {
            $cartType = applicationConstants::PRODUCT_FOR_EXTEND_RENTAL;
        } else {
            $cartType = $productFor;
        }

        if ($qty < 1) {
            return false;
        }

        if (!isset($this->SYSTEM_ARR['cart']['cart_type']) || $this->SYSTEM_ARR['cart']['cart_type'] == null) {
            $this->SYSTEM_ARR['cart']['cart_type'] = $cartType;
        }

        /* if ($qty > 0) { */
        $key = static::CART_KEY_PREFIX_PRODUCT . $selprod_id;
        if ($prodgroup_id) {
            $key = static::CART_KEY_PREFIX_BATCH . $prodgroup_id;
        }

        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $key = static::CART_KEY_PREFIX_PRODUCT . $selprod_id . $extraData['rental_start_date'] . $extraData['rental_end_date'];
        }

        $key = base64_encode(json_encode($key));
        if (!isset($this->SYSTEM_ARR['cart']['products'][$key])) {
            $this->SYSTEM_ARR['cart']['products'][$key]['quantity'] = $qty;
        } else {
            $this->SYSTEM_ARR['cart']['products'][$key]['quantity'] += $qty;
        }

        $this->SYSTEM_ARR['cart']['products'][$key]['productFor'] = $productFor;
        if (!empty($extraData)) {
            if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                $this->SYSTEM_ARR['cart']['products'][$key]['duration_type'] = (isset($extraData['duration_type'])) ? $extraData['duration_type'] : ProductRental::DURATION_TYPE_DAY;
                $this->SYSTEM_ARR['cart']['products'][$key]['rental_start_date'] = $extraData['rental_start_date'];
                $this->SYSTEM_ARR['cart']['products'][$key]['rental_end_date'] = $extraData['rental_end_date'];
                $this->SYSTEM_ARR['cart']['products'][$key]['extendOrder'] = (isset($extraData['extendOrder'])) ? $extraData['extendOrder'] : 0;
                $this->SYSTEM_ARR['cart']['products'][$key]['mainProductId'] = (isset($extraData['mainProductId'])) ? $extraData['mainProductId'] : 0;
                $this->SYSTEM_ARR['cart']['products'][$key]['sellerProdType'] = isset($extraData['sellerProdType']) ? $extraData['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT;
                $this->SYSTEM_ARR['cart']['products'][$key]['hasAddonProduct'] = isset($extraData['hasAddonProduct']) ? $extraData['hasAddonProduct'] : applicationConstants::NO;
            }
        }

        /* } */
        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            if ($prodgroup_id > 0) {
                $products = $this->getBasketProducts($this->cart_lang_id);
                if ($products) {
                    foreach ($products as $cartKey => $product) {
                        if ($product['is_batch'] && $prodgroup_id == $product['prodgroup_id']) {
                            foreach ($product['products'] as $pgProduct) {
                                $this->updateTempStockHold($pgProduct['selprod_id'], $this->SYSTEM_ARR['cart']['products'][$key]['quantity'], $product['prodgroup_id']);
                            }
                        }
                    }
                }
            } else {
                $this->updateTempStockHold($selprod_id, $this->SYSTEM_ARR['cart']['products'][$key]['quantity']);
            }
        }
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $this->updateRentalTempStockHold($selprod_id, $this->SYSTEM_ARR['cart']['products'][$key]['quantity'], $extraData['rental_start_date'], $extraData['rental_end_date'], $key);
        }


        $this->removeCartDiscountCoupon();
        $this->updateUserCart();
        if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
            AbandonedCart::saveAbandonedCart($this->cart_user_id, $selprod_id, $this->SYSTEM_ARR['cart']['products'][$key]['quantity'], AbandonedCart::ACTION_ADDED);
        }

        if ($returnUserId) {
            return $this->cart_user_id;
        }
        return true;
    }

    public function countProducts()
    {
        if (empty($this->SYSTEM_ARR['cart']['products']) || $this->getCartType() == applicationConstants::PRODUCT_FOR_SALE) {
            return count($this->SYSTEM_ARR['cart']['products']);
        }
        $count = 0;
        foreach ($this->SYSTEM_ARR['cart']['products'] as $product) {
            if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                $count ++;
            }
        }
    
        return $count;
    }

    public function hasProducts()
    {
        return count($this->SYSTEM_ARR['cart']['products']);
    }

    public function hasStock()
    {
        $stock = true;
        foreach ($this->getBasketProducts($this->cart_lang_id) as $product) {
            if (!$product['in_stock']) {
                $stock = false;
                break;
            }
        }
        return $stock;
    }

    public function hasDigitalProduct()
    {
        $isDigital = false;
        foreach ($this->getBasketProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch'] && !empty($product['products'])) {
                foreach ($product['products'] as $pgproduct) {
                    if ($pgproduct['is_digital_product']) {
                        $isDigital = true;
                        break;
                    }
                }
            }
        }
        $this->products = array();
        return $isDigital;
    }

    public function hasPhysicalProduct()
    {
        $isPhysical = false;
        foreach ($this->getBasketProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch'] && !empty($product['products'])) {
                foreach ($product['products'] as $pgproduct) {
                    if ($pgproduct['is_physical_product']) {
                        $isPhysical = true;
                        break;
                    }
                }
            } else {
                if (!empty($product['is_physical_product'])) {
                    $isPhysical = true;
                    break;
                }
            }
        }
        $this->products = array();
        return $isPhysical;
    }

    public function isRequireVerification()
    {
        $isRequireVerification = false;
        foreach ($this->getBasketProducts($this->cart_lang_id) as $product) {
            if (!SellerProduct::getProductVerificationFldsData($product['product_id'], $product['selprod_user_id'])) {
                $isRequireVerification = false;
            } else {
                $isRequireVerification = true;
                break;
            }
        }

        return $isRequireVerification;
    }

    public function getFilteredVerificationFldsData($uniqueFlds = false)
    {
        $verificationFlds = [];
        $tempProdId = [];
        foreach ($this->getBasketProducts() as $product) {
            if ((isset($product['selprod_type']) && $product['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) || in_array($product['product_id'], $tempProdId)) {
                continue;
            }
            
            $tempProdId[] = $product['product_id'];
            if ($data = SellerProduct::getProductVerificationFldsData($product['product_id'], $product['selprod_user_id'])) {
                foreach ($data as $val) {
                    if ($uniqueFlds) {
                        $verificationFlds[$val['vflds_id']] = $val;
                    } else {
                        $verificationFlds[] = $val;
                    }
                }
            }
        }

        return $verificationFlds;
    }

    public function getBasketProducts($siteLangId = 0, $addFullfillmentTypeCheck = true)
    {
        if (!$this->products) {
            $loggedUserId = 0;
            if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                $loggedUserId = UserAuthentication::getLoggedUserId();
            }

            foreach ($this->SYSTEM_ARR['cart']['products'] as $key => $product) {
                $quantity = FatUtility::int($product['quantity']);
                $productFor = (isset($product['productFor'])) ? $product['productFor'] : applicationConstants::PRODUCT_FOR_SALE;
                $selprod_id = 0;
                $durationType = '';

                $sellerProdType = (isset($product['sellerProdType'])) ? $product['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT;
                $mainProductId = (isset($product['mainProductId'])) ? $product['mainProductId'] : 0;
                $hasAddonProduct = (isset($product['hasAddonProduct'])) ? $product['hasAddonProduct'] : 0;

                $keyDecoded = json_decode(base64_decode($key), true);
                if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                    if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                        $rentalStartDate = $product['rental_start_date'];
                        $rentalEndDate = $product['rental_end_date'];
                        $durationType = $product['duration_type'];
                        $keyDecoded = str_replace($rentalStartDate . $rentalEndDate, '', $keyDecoded);
                    }
                    $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                }

                if (1 > $selprod_id) {
                    unset($this->SYSTEM_ARR['cart']['products'][$key]);
                    continue;
                }

                $extraData = [];
                $duration = [];
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $extraData = ['rental_start_date' => $product['rental_start_date'], 'rental_end_date' => $product['rental_end_date']];
                }

                if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $sellerProductRow = $this->getSelAddonProductData($selprod_id, $mainProductId, $quantity, $siteLangId);
                } else {
                    $sellerProductRow = $this->getSellerProductData($selprod_id, $quantity, $siteLangId, $loggedUserId, $productFor, $extraData, $key);
                }

                if (!$sellerProductRow) {
                    $this->removeCartKey($key, $selprod_id, $quantity);
                    continue;
                }
                
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    if ($sellerProdType != SellerProduct::PRODUCT_TYPE_ADDON && $durationType != $sellerProductRow['sprodata_duration_type']) {
                        Message::addErrorMessage(Labels::getLabel('MSG_Product_Duration_type_changed.So_removed_from_the_cart', $siteLangId));
                        $this->removeCartKey($key, $selprod_id, $quantity);
                        continue;
                    }
                    
                    $sellerId = SellerProduct::getAttributesById($selprod_id, 'selprod_user_id');
                    $duration = CommonHelper::getDifferenceBetweenDates($product['rental_start_date'], $product['rental_end_date'], $sellerId, $sellerProductRow['sprodata_duration_type']);
                }

                $fulfilmentType = $this->fulfilmentType;
                if (isset($this->SYSTEM_ARR['shopping_cart']['checkout_type'])) {
                    $fulfilmentType = $this->SYSTEM_ARR['shopping_cart']['checkout_type'];
                }

                if ($this->valdateCheckoutType && isset($fulfilmentType) && $fulfilmentType > 0 && $sellerProductRow['selprod_fulfillment_type'] != Shipping::FULFILMENT_ALL && $sellerProductRow['selprod_fulfillment_type'] != $fulfilmentType && $addFullfillmentTypeCheck) {
                    unset($this->products[$key]);
                    continue;
                }
                
                if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON && $addFullfillmentTypeCheck) {
                    $mainProductKey = static::CART_KEY_PREFIX_PRODUCT .  $mainProductId . $rentalStartDate . $rentalEndDate;
                    $mainProductKey = base64_encode(json_encode($mainProductKey)); 
                    if (!isset($this->products[$mainProductKey])) {
                        unset($this->products[$key]);
                        continue;
                    }
                }
                

                $this->products[$key] = [
                    'shipping_cost' => 0,
                    'opshipping_rate_id' => 0,
                    'commission_percentage' => '',
                    'commission' => 0,
                    'tax' => 0,
                    'taxOptions' => [],
                    'reward_point' => 0,
                    'volume_discount' => 0,
                    'volume_discount_total' => 0,
                    'is_shipping_selected' => false,
                    'volume_discount_total' => 0,
                    'duration_discount_total' => 0,
                ];

                $this->products[$key] = $sellerProductRow;
                $this->products[$key]['key'] = $key;
                $this->products[$key]['is_batch'] = 0;
                $this->products[$key]['selprod_id'] = $selprod_id;
                $this->products[$key]['quantity'] = $quantity;
                $this->products[$key]['has_physical_product'] = 0;
                $this->products[$key]['has_digital_product'] = 0;
                $this->products[$key]['is_cod_enabled'] = 0;
                $this->products[$key]['productFor'] = $productFor;
                $this->products[$key]['sellerProdType'] = $sellerProdType;
                $this->products[$key]['mainProductId'] = $mainProductId;
                $this->products[$key]['hasAddonProduct'] = $hasAddonProduct;
                /* $this->products[$key]['shop_eligible_for_free_shipping'] = 0; */
                
                $shipableAmount = $sellerProductRow['theprice'];
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $this->products[$key]['rentalStartDate'] = $product['rental_start_date'];
                    $this->products[$key]['rentalEndDate'] = $product['rental_end_date'];
                    $this->products[$key]['extendOrder'] = $product['extendOrder'];
                    $rentalSecurity = ($product['extendOrder'] > 0) ? 0 : $sellerProductRow['sprodata_rental_security'];
                    if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $rentalPrice = $sellerProductRow['selprod_price'];
                    } else {
                        $priceArr = CommonHelper::getRentalPricesArr($sellerProductRow);
                        $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr);
                    }
                    /* $this->products[$key]['theprice'] = $rentalPrice + $rentalSecurity; */
                    /* $this->products[$key]['theprice'] = $rentalPrice + $rentalSecurity; */
                    $this->products[$key]['total'] = $this->products[$key]['theprice'] * $quantity;
                    $this->products[$key]['sprodata_rental_security'] = $rentalSecurity;
                    
                    $shipableAmount = $rentalPrice;
                    
                }
                $this->products[$key]['shipableAmount'] = $shipableAmount * $quantity;
                
                
            }
        }

        uasort($this->products, function ($a, $b) {
            return $a['shop_id'] - $b['shop_id'];
        });
        return $this->products;
    }

    public function getProducts($siteLangId = 0, $addFullfillmentTypeCheck = true)
    {
        if (!$this->products) {
            //$this->getBasketProducts($siteLangId);
            $productSelectedShippingMethodsArr = $this->getProductShippingMethod();
            $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION", FatUtility::VAR_INT, 0);
            $associatedAffiliateUserId = 0;
            /* detect current logged user has associated affiliate user[ */
            $loggedUserId = 0;
            if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                $loggedUserId = UserAuthentication::getLoggedUserId();
                $associatedAffiliateUserId = User::getAttributesById($loggedUserId, 'user_affiliate_referrer_user_id');
                if ($associatedAffiliateUserId > 0) {
                    $prodObj = new Product();
                }
            }
            /* ] */

            $is_cod_enabled = true;
            if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                $cartDiscounts = $this->getCouponDiscounts();
            }

            if (isset($this->SYSTEM_ARR['cart']['products'])) {
                $selprodIdsCount = $this->getCartSelprodIdsCount();
                foreach ($this->SYSTEM_ARR['cart']['products'] as $key => $product) {
                    $quantity = $product['quantity'];
                    $productFor = $product['productFor'];
                    $mainProductId = (isset($product['mainProductId'])) ? $product['mainProductId'] : 0;
                    $hasAddonProduct = (isset($product['hasAddonProduct'])) ? $product['hasAddonProduct'] : 0;
                    $selprod_id = 0;
                    // $prodgroup_id = 0;
                    $sellerProductRow = array();
                    $sellerProdType = (isset($product['sellerProdType'])) ? $product['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT;
                    $durationType = '';

                    $affiliateCommissionPercentage = '';
                    $affiliateCommission = 0;

                    $keyDecoded = json_decode(base64_decode($key), true);
                    if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                            $rentalStartDate = $product['rental_start_date'];
                            $rentalEndDate = $product['rental_end_date'];
                            $mainProductId = (isset($product['mainProductId'])) ? $product['mainProductId'] : 0;
                            $hasAddonProduct = (isset($product['hasAddonProduct'])) ? $product['hasAddonProduct'] : 0;
                            $sellerProdType = (isset($product['sellerProdType'])) ? $product['sellerProdType'] : SellerProduct::PRODUCT_TYPE_PRODUCT;
                            $keyDecoded = str_replace($rentalStartDate . $rentalEndDate, '', $keyDecoded);
                            $durationType = $product['duration_type'];
                        }

                        $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                        if ($sellerProdType == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                            $mainProductId = $selprod_id;
                        }
                    }

                    //To rid of from invalid product detail in listing.
                    if (1 > $selprod_id) {
                        unset($this->SYSTEM_ARR['cart']['products'][$key]);
                        continue;
                    }

                    /* CommonHelper::printArray($keyDecoded); die ; */
                    // if( strpos($keyDecoded, static::CART_KEY_PREFIX_BATCH ) !== FALSE ){
                    // $prodgroup_id = FatUtility::int(str_replace( static::CART_KEY_PREFIX_BATCH, '', $keyDecoded ));
                    // }

                    $this->products[$key]['shipping_cost'] = 0;
                    $this->products[$key]['opshipping_rate_id'] = 0;
                    $this->products[$key]['commission_percentage'] = '';
                    $this->products[$key]['commission'] = 0;
                    $this->products[$key]['tax'] = 0;
                    $this->products[$key]['taxOptions'] = [];
                    $this->products[$key]['reward_point'] = 0;
                    $this->products[$key]['volume_discount'] = 0;
                    $this->products[$key]['volume_discount_total'] = 0;
                    $this->products[$key]['is_shipping_selected'] = false;
                    $this->products[$key]['duration_discount'] = 0;
                    $this->products[$key]['duration_discount_total'] = 0;
                    $selProdCost = $shopId = '';
                    $duration = [];
                    $rentalPrice = 0;
                    $productShipKey = $selprod_id;
                    $extraData = [];

                    if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                        $extraData = ['rental_start_date' => $product['rental_start_date'], 'rental_end_date' => $product['rental_end_date']];
                    }

                    /* seller products[ */
                    if ($selprod_id > 0) {
                        if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                            $sellerProductRow = $this->getSelAddonProductData($selprod_id, $mainProductId, $quantity, $siteLangId);
                        } else {
                            $sellerProductRow = $this->getSellerProductData($selprod_id, $quantity, $siteLangId, $loggedUserId, $productFor, $extraData, $key);
                        }

                        if (!$sellerProductRow) {
                            $this->removeCartKey($key, $selprod_id, $quantity);
                            continue;
                        }

                        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                            if ($sellerProdType != SellerProduct::PRODUCT_TYPE_ADDON && $durationType != $sellerProductRow['sprodata_duration_type']) {
                                Message::addErrorMessage(Labels::getLabel('MSG_Product_Duration_type_changed.So_removed_from_the_cart', $siteLangId));
                                $this->removeCartKey($key, $selprod_id, $quantity);
                                continue;
                            }
                            $duration = CommonHelper::getDifferenceBetweenDates($product['rental_start_date'], $product['rental_end_date'], 0, $sellerProductRow['sprodata_duration_type']);
                        }

                        $sellerProductRow['sellerProdType'] = $sellerProdType;
                        $sellerProductRow['mainProductId'] = $mainProductId;
                        $sellerProductRow['hasAddonProduct'] = $hasAddonProduct;

                        $fulfilmentType = $this->fulfilmentType;
                        if (isset($this->SYSTEM_ARR['shopping_cart']['checkout_type'])) {
                            $fulfilmentType = $this->SYSTEM_ARR['shopping_cart']['checkout_type'];
                        }
                        
                        if ($addFullfillmentTypeCheck) {
                            if ($this->valdateCheckoutType && isset($fulfilmentType) && $fulfilmentType > 0 && $sellerProductRow['selprod_fulfillment_type'] != Shipping::FULFILMENT_ALL && $sellerProductRow['selprod_fulfillment_type'] != $fulfilmentType) {
                                unset($this->products[$key]);
                                continue;
                            }
                            
                            if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                               $mainProductKey = static::CART_KEY_PREFIX_PRODUCT .  $mainProductId . $rentalStartDate . $rentalEndDate;
                                $mainProductKey = base64_encode(json_encode($mainProductKey)); 
                                if (!isset($this->products[$mainProductKey])) {
                                    unset($this->products[$key]);
                                    continue;
                                }
                            }
                        }


                        $this->products[$key] = $sellerProductRow;

                        /* [COD available */
                        $codEnabled = false;
                        // $isProductShippedBySeller = Product::isProductShippedBySeller($sellerProductRow['product_id'], $sellerProductRow['product_seller_id'], $sellerProductRow['selprod_user_id']);
                        $isProductShippedBySeller = $sellerProductRow['isProductShippedBySeller'];

                        if ($is_cod_enabled && $isProductShippedBySeller) {
                            $walletBalance = User::getUserBalance($sellerProductRow['selprod_user_id']);
                            if ($sellerProductRow['selprod_cod_enabled'] == 1 && $sellerProductRow['product_cod_enabled'] == 1) {
                                $codEnabled = true;
                            }
                            $codMinWalletBalance = -1;
                            $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($sellerProductRow['selprod_user_id'], 'shop_cod_min_wallet_balance');
                            if ($shop_cod_min_wallet_balance > -1) {
                                $codMinWalletBalance = $shop_cod_min_wallet_balance;
                            } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                                $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
                            }
                            if ($codMinWalletBalance > -1 && $codMinWalletBalance > $walletBalance) {
                                $codEnabled = false;
                            }
                        } else {
                            if ($sellerProductRow['product_cod_enabled']) {
                                $codEnabled = true;
                            }
                        }
                        $is_cod_enabled = $codEnabled;
                        /* ] */

                        /* [ Product shipping cost */
                        $shippingCost = 0;

                        if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$productShipKey])) {
                            $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$productShipKey];
                            $productCount = (isset($selprodIdsCount[$productShipKey])) ? $selprodIdsCount[$productShipKey] : 1;

                            $this->products[$key]['opshipping_rate_id'] = isset($shippingDurationRow['mshipapi_id']) ? $shippingDurationRow['mshipapi_id'] : '';
                            $shippingCost = ROUND(($shippingDurationRow['mshipapi_cost'] / $productCount), 2);
                            $this->products[$key]['shipping_cost'] = $shippingCost;
                        }

                        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                            $address = new Address($this->getCartShippingAddress(), $siteLangId);
                            $this->products[$key]['shipping_address'] = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
                        }
                        /* ] */
                        $shipingAddress = isset($this->products[$key]['shipping_address']) ? $this->products[$key]['shipping_address'] : '';

                        /* $addressForTax = $this->getTaxAddress($key, 1); */
                        $extraData = array(
                            'billingAddress' => isset($this->products[$key]['billing_address']) ? $this->products[$key]['billing_address'] : '',
                            'shippingAddress' => $shipingAddress /* $addressForTax */,
                            'shippedBySeller' => $isProductShippedBySeller,
                            'shippingCost' => $shippingCost,
                            'buyerId' => $this->cart_user_id
                        );

                        /* [ Product Tax */
                        $shipableAmount = $sellerProductRow['theprice'];
                        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                            $rentalPrice = $sellerProductRow['theprice'];
                            if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                                /* $rentalPrice = $sellerProductRow['theprice']; */
                                $rentalSecurity = 0;
                            } else {
                                /* $priceArr = CommonHelper::getRentalPricesArr($sellerProductRow);
                                $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr); */
                                $rentalSecurity = ($product['extendOrder'] > 0) ? 0 : $sellerProductRow['sprodata_rental_security'];
                            }
                            $taxableProdPrice = $rentalPrice;
                            if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                                $taxableProdPrice = $rentalPrice - $sellerProductRow['duration_discount'];
                            }
                            $this->products[$key]['rentalStartDate'] = $product['rental_start_date'];
                            $this->products[$key]['rentalEndDate'] = $product['rental_end_date'];
                            $this->products[$key]['extendOrder'] = $product['extendOrder'];
                            
                            /* $this->products[$key]['theprice'] = $rentalPrice + $rentalSecurity; */
                            $this->products[$key]['theprice'] = $sellerProductRow['theprice'] ;
                            $this->products[$key]['total'] = $this->products[$key]['theprice'] * $quantity;
                            $this->products[$key]['sprodata_rental_security'] = $rentalSecurity;
                            /* $sellerProductRow['theprice'] = $rentalPrice + $rentalSecurity; */
                            $shipableAmount = $rentalPrice;
                        } else {
                            $rentalSecurity = 0;
                            $taxableProdPrice = $sellerProductRow['theprice'];
                            if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                                $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['volume_discount'];
                            }
                        }
                        
                        $this->products[$key]['shipableAmount'] = $shipableAmount * $quantity;
                        if (isset($cartDiscounts['discountedSelProdIds']) && array_key_exists($sellerProductRow['selprod_id'], $cartDiscounts['discountedSelProdIds'])) {
                            $taxableProdPrice = $taxableProdPrice - ($cartDiscounts['discountedSelProdIds'][$sellerProductRow['selprod_id']]) / $quantity;
                        }

                        $taxObj = new Tax();
                        $productId = $sellerProductRow['product_id'];
                        if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                            $productId = $sellerProductRow['selprod_id'];
                        }

                        $taxData = $taxObj->calculateTaxRates($productId, $taxableProdPrice, $sellerProductRow['selprod_user_id'], $siteLangId, $quantity, $extraData, $this->cartCache, $sellerProdType, $productFor);
                        if (false == $taxData['status'] && $taxData['msg'] != '') {
                            $this->error = $taxData['msg'];
                        }


                        $taxOptions = [];
                        //if (array_key_exists('options', $taxData)) {
                        /* foreach ($taxData['options'] as $optionId => $optionval) {
                          if (0 < $optionval['value']) {
                          $taxOptions[$optionval['name']] = isset($taxOptions[$optionval['name']]) ? ($taxOptions[$optionval['name']] + $optionval['value']) : $optionval['value'];
                          }
                          } */
                        if (array_key_exists('options', $taxData)) {
                            foreach ($taxData['options'] as $optionId => $optionval) {
                                $prodTaxOptions[$sellerProductRow['selprod_id']][$optionId] = $optionval;
                                if (isset($optionval['value']) && 0 < $optionval['value']) {
                                    $taxOptions[$optionval['name']]['value'] = isset($taxOptions[$optionval['name']]['value']) ? ($taxOptions[$optionval['name']]['value'] + $optionval['value']) : $optionval['value'];
                                    $taxOptions[$optionval['name']]['title'] = CommonHelper::displayTaxPercantage($optionval);
                                }
                            }
                        }

                        $tax = $taxData['tax'];

                        $this->products[$key]['tax'] = $tax;
                        $this->products[$key]['taxCode'] = $taxData['taxCode'];
                        $this->products[$key]['taxOptions'] = $taxOptions;
                        /* ] */

                        /* [ Product Commission */
                        $commissionPercentage = SellerProduct::getProductCommission($mainProductId, $productFor);
                        $commissionCostValue = $sellerProductRow['theprice'];

                        if (FatApp::getConfig('CONF_COMMISSION_INCLUDING_TAX', FatUtility::VAR_INT, 0) && FatApp::getConfig('CONF_TAX_COLLECTED_BY_SELLER', FatUtility::VAR_INT, 0) && $tax) {
                            $commissionCostValue = $commissionCostValue + ($tax / $quantity);
                        }

                        if (FatApp::getConfig('CONF_COMMISSION_INCLUDING_SHIPPING', FatUtility::VAR_INT, 0) && $shippingCost && $this->products[$key]['psbs_user_id'] > 0) {
                            $commissionCostValue = $commissionCostValue + ($shippingCost / $quantity);
                        }

                        $commissionCostValue = ROUND(($commissionCostValue * $quantity), 2);
                        $commission = ROUND(($commissionCostValue * $commissionPercentage / 100), 2);
                        $commission = MIN($commission, $maxConfiguredCommissionVal);

                        $this->products[$key]['commission_percentage'] = $commissionPercentage;
                        $this->products[$key]['commission'] = ROUND($commission, 2);
                        /* ] */

                        /* Affiliate Commission[ */
                        if ($associatedAffiliateUserId > 0) {
                            $affiliateCommissionPercentage = AffiliateCommission::getAffiliateCommission($associatedAffiliateUserId, $sellerProductRow['product_id'], $prodObj);
                            $affiliateCommissionCostValue = ROUND($sellerProductRow['theprice'] * $quantity, 2);
                            $affiliateCommission = ROUND($affiliateCommissionCostValue * $affiliateCommissionPercentage / 100, 2);
                        }
                        /* ] */
                        $selProdCost = $sellerProductRow['selprod_cost'];
                        $shopId = $sellerProductRow['shop_id'];
                    } else {
                        $is_cod_enabled = false;
                        if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                            $address = new Address($this->getCartShippingAddress(), $siteLangId);
                            $this->products[$key]['shipping_address'] = $address->getData(Address::TYPE_USER, UserAuthentication::getLoggedUserId());
                        }
                    }
                    /* ] */

                    $this->products[$key]['key'] = $key;
                    $this->products[$key]['is_batch'] = 0;
                    $this->products[$key]['is_cod_enabled'] = $is_cod_enabled;
                    $this->products[$key]['selprod_id'] = $selprod_id;
                    $this->products[$key]['quantity'] = $quantity;
                    $this->products[$key]['has_physical_product'] = 0;
                    $this->products[$key]['has_digital_product'] = 0;

                    $this->products[$key]['sellerProdType'] = $sellerProdType;
                    $this->products[$key]['productFor'] = $productFor;


                    /* $this->products[$key]['product_ship_free'] = $sellerProductRow['product_ship_free']; */
                    $this->products[$key]['selprod_cost'] = $selProdCost;
                    $this->products[$key]['affiliate_commission_percentage'] = $affiliateCommissionPercentage;
                    $this->products[$key]['affiliate_commission'] = $affiliateCommission;
                    $this->products[$key]['affiliate_user_id'] = $associatedAffiliateUserId;
                    if (UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) {
                        $this->products[$key]['seller_address'] = Shop::getShopAddress($shopId, true, $siteLangId);
                    }
                    /* UPDATE FOR EXTEND RENTAL [ */
                    if ($this->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                        $sellerProductRow['fulfillment_type'] = Orders::getOrderProductShippingType($product['extendOrder'], 'opshipping_fulfillment_type');
                    }
                    /* ] */

                    $this->products[$key]['fulfillment_type'] = $sellerProductRow['fulfillment_type'];
                    $this->products[$key]['rounding_off'] = $sellerProductRow['rounding_off'];
                }
            }
        }
        return $this->products;
    }

    public function getSellerProductData(int $selprod_id, int &$quantity, int $siteLangId, int $loggedUserId = 0, int $productFor = applicationConstants::PRODUCT_FOR_SALE, array $rentalDetails = [], $productKey = '')
    {

        $prodSrch = new ProductSearch($siteLangId);
        /* $prodSrch->setDefinedCriteria(); */
        $prodSrch->setDefinedCriteria(0, 0, ['producttype' => $productFor]);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->joinProductShippedBy();
        $prodSrch->joinProductFreeShipping();
        $prodSrch->joinSellers();
        $prodSrch->joinShops();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $prodSrch->addCondition('sprodata_is_for_rent', '=', applicationConstants::YES);
        } else {
            $prodSrch->addCondition('sprodata_is_for_sell', '=', applicationConstants::YES);
        }

        $prodSrch->addMultipleFields(array(
            'product_id', 'product_type', 'product_length', 'product_width', 'product_height', 'product_ship_free',
            'product_dimension_unit', 'product_weight', 'product_weight_unit', 'product_fulfillment_type',
            'selprod_id', 'selprod_code', 'selprod_stock', 'selprod_user_id', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_min_order_qty',
            'special_price_found', 'theprice', 'shop_id', 'shop_is_free_ship_active', 'shop_free_shipping_amount' ,'shop_state_id', 'shop_country_id',
            'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_price', 'selprod_cost', 'case when product_seller_id=0 then IFNULL(psbs_user_id,0)   else product_seller_id end  as psbs_user_id', 'product_seller_id', 'product_cod_enabled', 'shop_fulfillment_type', 'selprod_cod_enabled', 'shippack_length', 'shippack_width', 'shippack_height', 'shippack_units',
            'sprodata_is_for_sell', 'sprodata_is_for_rent', 'sprodata_duration_type', 'sprodata_rental_security', 'sprodata_rental_stock', 'sprodata_rental_terms', 'theprice as sprodata_rental_price', 'sprodata_minimum_rental_duration', 'sprodata_minimum_rental_quantity', 'product_identifier'
        ));
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $prodSrch->addFld('sprodata_fullfillment_type as selprod_fulfillment_type');
        } else {
            $prodSrch->addFld('selprod_fulfillment_type');
        }

        if ($siteLangId) {
            $prodSrch->joinBrands();
            $prodSrch->addFld(array('IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(brand_name, brand_identifier) as brand_name', 'IFNULL(shop_name, shop_identifier) as shop_name', 'brand_id'));
        }

        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if (0 < $loggedUserId) {
            if ($favVar == applicationConstants::NO) {
                $prodSrch->joinFavouriteProducts($loggedUserId);
                $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
            } else {
                $prodSrch->joinUserWishListProducts($loggedUserId);
                $prodSrch->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist, IFNULL(uwlp.uwlp_uwlist_id, 0) as uwlp_uwlist_id');
            }
        } else {
            $prodSrch->addFld('0 as ufp_id');
        }

        $rs = $prodSrch->getResultSet();
        $sellerProductRow = FatApp::getDb()->fetch($rs);
        if (!$sellerProductRow || ($sellerProductRow['selprod_stock'] <= 0 && $productFor == applicationConstants::PRODUCT_FOR_SALE)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Product_not_available_or_out_of_stock_so_removed_from_cart_listing', $siteLangId));
            return false;
        }

        $productSelectedShippingMethodsArr = $this->getProductShippingMethod();

        if (($quantity > $sellerProductRow['selprod_stock'] && $productFor == applicationConstants::PRODUCT_FOR_SALE)) {
            /* requested quantity cannot more than stock available */
            $quantity = $sellerProductRow['selprod_stock'];
        }

        $sellerProductRow['actualPrice'] = $sellerProductRow['theprice'];
        $rentalPrice = 0;
        
        if ($productFor != applicationConstants::PRODUCT_FOR_RENT) {
            $isProductShippedBySeller = Product::isProductShippedBySeller($sellerProductRow['product_id'], $sellerProductRow['product_seller_id'], $sellerProductRow['selprod_user_id']);
        }
        
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $priceArr = CommonHelper::getRentalPricesArr($sellerProductRow);
            $duration = CommonHelper::getDifferenceBetweenDates($rentalDetails['rental_start_date'], $rentalDetails['rental_end_date'], $sellerProductRow['selprod_user_id'], $sellerProductRow['sprodata_duration_type']);
            $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr);
            $sellerProductRow['theprice'] = $rentalPrice;
            $sellerProductRow['actualPrice'] = $rentalPrice;
            $isProductShippedBySeller = true;
        }

        $extraData = [];
        if ($this->includeTax == true) {
            $shipFromStateId = $sellerProductRow['shop_state_id'];
            $shipFromCountryId = $sellerProductRow['shop_country_id'];
            $shipToStateId = 0;
            $shipToCountryId = 0;
            $shippingAddressId = $this->getCartShippingAddress();

            $shippingAddressDetail = [];
            if (0 < $shippingAddressId) {
                $address = new Address($shippingAddressId, $this->cart_lang_id);
                $shippingAddressDetail = $address->getData(Address::TYPE_USER, $this->cart_user_id);

                if (isset($shippingAddressDetail['addr_country_id'])) {
                    $shipToCountryId = FatUtility::int($shippingAddressDetail['addr_country_id']);
                }

                if (isset($shippingAddressDetail['addr_state_id'])) {
                    $shipToStateId = FatUtility::int($shippingAddressDetail['addr_state_id']);
                }
            }

            $shippingCost = 0;
            $selprodIdsCount = $this->getCartSelprodIdsCount();
            if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']])) {
                $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']];
                $productCount = (isset($selprodIdsCount[$selprod_id])) ? $selprodIdsCount[$selprod_id] : 1;
                $shippingCost = ROUND(($shippingDurationRow['mshipapi_cost'] / $productCount), 2);
            }

            if (!$isProductShippedBySeller) {
                $shipFromCountryId = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
                $shipFromStateId = FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 0);
            }

            $extraData = array(
                'billingAddress' => isset($sellerProductRow['billing_address']) ? $sellerProductRow['billing_address'] : '',
                'shippingAddress' => $shippingAddressDetail,
                'shippedBySeller' => $isProductShippedBySeller,
                'shippingCost' => $shippingCost,
                'buyerId' => $this->cart_user_id
            );
        }

        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0) && $this->includeTax == true) {
            $tax = new Tax();
            $tax->setFromCountryId($shipFromCountryId);
            $tax->setFromStateId($shipFromStateId);
            $tax->setToCountryId($shipToCountryId);
            $tax->setToStateId($shipToStateId);
            $taxCategoryRow = $tax->getTaxRates($sellerProductRow['product_id'], $sellerProductRow['selprod_user_id'], $siteLangId);
            if (array_key_exists('trr_rate', $taxCategoryRow) && 0 == Tax::getActivatedServiceId()) {
                $sellerProductRow['theprice'] = round($sellerProductRow['theprice'] / (1 + ($taxCategoryRow['trr_rate'] / 100)), 2);
            } else {
                $taxObj = new Tax();
                $taxData = $taxObj->calculateTaxRates($sellerProductRow['product_id'], $sellerProductRow['theprice'], $sellerProductRow['selprod_user_id'], $siteLangId, $quantity, $extraData, $this->cartCache, SellerProduct::PRODUCT_TYPE_PRODUCT, $productFor);
                if (isset($taxData['rate'])) {
                    $ruleRate = ($taxData['tax'] * 100) / ($sellerProductRow['theprice'] * $quantity);
                    $sellerProductRow['theprice'] = round((($sellerProductRow['theprice'] * $quantity) / (1 + ($ruleRate / 100))) / $quantity, 2);
                }
            }
        }

        /* update/fetch/apply theprice, according to volume discount module[ */
        $sellerProductRow['volume_discount'] = 0;
        $sellerProductRow['volume_discount_percentage'] = 0;
        $sellerProductRow['volume_discount_total'] = 0;
        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
            $srch = new SellerProductVolumeDiscountSearch();
            $srch->doNotCalculateRecords();
            $srch->addCondition('voldiscount_selprod_id', '=', $sellerProductRow['selprod_id']);
            $srch->addCondition('voldiscount_min_qty', '<=', $quantity);
            $srch->addOrder('voldiscount_min_qty', 'DESC');
            $srch->setPageSize(1);
            $srch->addMultipleFields(array('voldiscount_percentage'));
            $rs = $srch->getResultSet();
            $volumeDiscountRow = FatApp::getDb()->fetch($rs);
            if ($volumeDiscountRow) {
                $volumeDiscount = $sellerProductRow['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
                $sellerProductRow['volume_discount_percentage'] = $volumeDiscountRow['voldiscount_percentage'];
                $sellerProductRow['volume_discount'] = $volumeDiscount;
                $sellerProductRow['volume_discount_total'] = $volumeDiscount * $quantity;
            }
        }
        /* ] */
        /* [ get duration discount for rental products */
        $sellerProductRow['duration_discount'] = 0;
        $sellerProductRow['duration_discount_percentage'] = 0;
        $sellerProductRow['duration_discount_total'] = 0;
        $rentalPrice = 0;
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            $priceArr = CommonHelper::getRentalPricesArr($sellerProductRow);
            $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr);
            $srch = new SellerProductDurationDiscountSearch();
            $srch->doNotCalculateRecords();
            $srch->addCondition('produr_selprod_id', '=', $sellerProductRow['selprod_id'], 'AND');
            if (!empty($duration)) {
                $srch->addCondition('produr_rental_duration', '<=', $duration);
            }
            $srch->addOrder('produr_rental_duration', 'DESC');
            $srch->setPageSize(1);
            $srch->addMultipleFields(array('produr_discount_percent'));
            $rs = $srch->getResultSet();
            $durationDiscountRow = FatApp::getDb()->fetch($rs);
            if ($durationDiscountRow) {
                $durationDiscount = $rentalPrice * ($durationDiscountRow['produr_discount_percent'] / 100);
                $sellerProductRow['duration_discount_percentage'] = $durationDiscountRow['produr_discount_percent'];
                $sellerProductRow['duration_discount'] = $durationDiscount;
                $sellerProductRow['duration_discount_total'] = $durationDiscount * $quantity;
            }
            if ($sellerProductRow['sprodata_rental_stock'] > 0) {
                $sellerProductRow['in_stock'] = 1;
            }
        }
        /* ] */


        /* set variable of shipping cost of the product, if shipping already selected[ */
        $sellerProductRow['shipping_cost'] = 0;
        $sellerProductRow['opshipping_rate_id'] = 0;
        if (!empty($productSelectedShippingMethodsArr) && isset($productSelectedShippingMethodsArr[$selprod_id])) {
            $shippingDurationRow = $productSelectedShippingMethodsArr[$selprod_id];
            $sellerProductRow['opshipping_rate_id'] = $shippingDurationRow['mshipapi_id'];
            $sellerProductRow['shipping_cost'] = ROUND(($shippingDurationRow['mshipapi_cost'] * $quantity), 2);
        }
        /* ] */

        /* calculation of commission and tax against each product[ */
        $commission = 0;
        $tax = 0;
        $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION");

        $commissionPercentage = SellerProduct::getProductCommission($selprod_id, $productFor);
        $commission = MIN(ROUND($sellerProductRow['theprice'] * $commissionPercentage / 100, 2), $maxConfiguredCommissionVal);
        $sellerProductRow['commission_percentage'] = $commissionPercentage;
        $sellerProductRow['commission'] = ROUND($commission * $quantity, 2);

        $totalPrice = $sellerProductRow['theprice'] * $quantity;
        $taxableProdPrice = $sellerProductRow['theprice'];
        
        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
            if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['duration_discount'];
            }
        } else {
            if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['volume_discount'];
            }
        }
        
        $discountedPrice = 0;
        if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            if (!empty($this->discounts) && isset($this->discounts['discountedSelProdIds'][$sellerProductRow['selprod_id']])) {
                $discountedPrice = $this->discounts['discountedSelProdIds'][$sellerProductRow['selprod_id']];
                $taxableProdPrice = $taxableProdPrice - $discountedPrice;
            }
        }
        
        $taxObj = new Tax();
        $taxData = $taxObj->calculateTaxRates($sellerProductRow['product_id'], $taxableProdPrice, $sellerProductRow['selprod_user_id'], $siteLangId, $quantity, $extraData, false, SellerProduct::PRODUCT_TYPE_PRODUCT, $productFor);
        // CommonHelper::printArray($taxData);
        if (false == $taxData['status'] && $taxData['msg'] != '') {
            //$this->error = $taxData['msg'];
        }

        $tax = $taxData['tax'];
        $roundingOff = 0;
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $originalTotalPrice = ($sellerProductRow['actualPrice'] * $quantity);
            $thePriceincludingTax = $taxData['tax'] + $totalPrice;
            if (0 < $sellerProductRow['volume_discount_total'] && array_key_exists('rate', $taxData)) {
                $thePriceincludingTax = $thePriceincludingTax + (($sellerProductRow['volume_discount_total'] * $taxData['rate']) / 100);
            }

            if (0 < $discountedPrice) {
                $thePriceincludingTax = $thePriceincludingTax + (($discountedPrice * $taxData['rate']) / 100);
            }

            if ($originalTotalPrice != $thePriceincludingTax && 0 < $taxableProdPrice && 0 < $taxData['rate']) {
                $roundingOff = round($originalTotalPrice - $thePriceincludingTax, 2);
            }
        } else {
            if (array_key_exists('optionsSum', $taxData) && $taxData['tax'] != $taxData['optionsSum']) {
                $roundingOff = round($taxData['tax'] - $taxData['optionsSum'], 2);
            }
        }
        $sellerProductRow['rounding_off'] = $roundingOff;

        $sellerProductRow['tax'] = $tax;
        $sellerProductRow['optionsTaxSum'] = isset($taxData['optionsSum']) ? $taxData['optionsSum'] : 0;
        $sellerProductRow['taxCode'] = $taxData['taxCode'];
        /* ] */

        $sellerProductRow['total'] = $totalPrice;
        $sellerProductRow['netTotal'] = $sellerProductRow['total'] + $sellerProductRow['shipping_cost'] + $roundingOff;

        $sellerProductRow['is_physical_product'] = ($sellerProductRow['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) ? 1 : 0;


        if ($siteLangId) {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, true, $siteLangId);
        } else {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, false);
        }

        
        $sellerProductRow['isProductShippedBySeller'] = $isProductShippedBySeller;

        $fulfillmentType = $sellerProductRow['selprod_fulfillment_type'];
        if (true == $isProductShippedBySeller) {
            if ($sellerProductRow['shop_fulfillment_type'] != Shipping::FULFILMENT_ALL) {
                $fulfillmentType = $sellerProductRow['shop_fulfillment_type'];
                $sellerProductRow['selprod_fulfillment_type'] = $fulfillmentType;
            }
        } else {
            $fulfillmentType = isset($sellerProductRow['product_fulfillment_type']) ? $sellerProductRow['product_fulfillment_type'] : Shipping::FULFILMENT_SHIP;
            $sellerProductRow['selprod_fulfillment_type'] = $fulfillmentType;
            if (FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1) != Shipping::FULFILMENT_ALL && $fulfillmentType != FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1)) {
                $fulfillmentType = FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1);
                $sellerProductRow['selprod_fulfillment_type'] = $fulfillmentType;
            }
        }

        $sellerProductRow['fulfillment_type'] = $fulfillmentType;
        return $sellerProductRow;
    }

    public function removeCartKey($key, $selProdId, $quantity)
    {
        if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
            AbandonedCart::saveAbandonedCart($this->cart_user_id, $selProdId, $quantity, AbandonedCart::ACTION_DELETED);
        }
        unset($this->products[$key]);
        unset($this->SYSTEM_ARR['cart']['products'][$key]);
        if ($this->countProducts() <= 0) {
            unset($this->SYSTEM_ARR['cart']['cart_type']);
        }
        $this->updateUserCart();
        return true;
    }

    public function remove($key)
    {
        $this->products = array();
        $this->invalidateCheckoutType();
        $cartProducts = $this->getProducts($this->cart_lang_id);
        $found = false;
        if (is_array($cartProducts)) {
            foreach ($cartProducts as $cartKey => $product) {
                if (($key == 'all' || (md5($product['key']) == $key) && !$product['is_batch'])) {
                    $found = true;
                    unset($this->SYSTEM_ARR['cart']['products'][$cartKey]);
                    if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                        $this->updateRentalTempStockHold($product['selprod_id'], 0, $product['rentalStartDate'], $product['rentalEndDate'], $cartKey);
                    } else {
                        $this->updateTempStockHold($product['selprod_id'], 0, 0);
                    }
                    if (($key == 'all' || md5($product['key']) == $key) && !$product['is_batch']) {
                        if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                            AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $product['quantity'], AbandonedCart::ACTION_DELETED);
                        }
                        if ($product['productFor'] == applicationConstants::PRODUCT_FOR_SALE) {
                            break;
                        }
                    }
                }

                /* [ REMOVE ADDON FROM CART IF MAIN PRODUCT IS REMOVED */
                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT && (isset($product['sellerProdType']) && $product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON)) {
                    $mainProductKey = static::CART_KEY_PREFIX_PRODUCT . $product['mainProductId'] . $product['rentalStartDate'] . $product['rentalEndDate'];
                    $mainProductKey = md5(base64_encode(json_encode($mainProductKey)));
                    if ($key == $mainProductKey) {
                        unset($this->SYSTEM_ARR['cart']['products'][$cartKey]);
                        if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                            AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $product['quantity'], AbandonedCart::ACTION_DELETED);
                        }
                    }
                }
                /* ] */
            }
        }
        if ($this->countProducts() <= 0) {
            unset($this->SYSTEM_ARR['cart']['cart_type']);
        }
        $this->updateUserCart();
        if (false === $found) {
            $this->error = Labels::getLabel('ERR_Invalid_Product', $this->cart_lang_id);
        }
        return $found;
    }

    public function removeGroup($prodgroup_id)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $this->products = array();
        $cartProducts = $this->getProducts($this->cart_lang_id);
        if (is_array($cartProducts)) {
            foreach ($cartProducts as $cartKey => $product) {
                if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                    unset($this->SYSTEM_ARR['cart'][$cartKey]);

                    /* to keep track of temporary hold the product stock[ */
                    foreach ($product['products'] as $pgproduct) {
                        $this->updateTempStockHold($pgproduct['selprod_id'], 0, $prodgroup_id);
                    }
                    /* ] */
                    break;
                }
            }
        }
        if ($this->countProducts() <= 0) {
            unset($this->SYSTEM_ARR['cart']['cart_type']);
        }
        $this->updateUserCart();
        return true;
    }

    public function getWarning()
    {
        return $this->warning;
    }

    public function update($key, $quantity)
    {
        $quantity = FatUtility::int($quantity);
        $found = false;
        if ($quantity > 0) {
            $cartProducts = $this->getBasketProducts($this->cart_lang_id, false);
            $cart_user_id = $this->cart_user_id;
            if (is_array($cartProducts)) {
                foreach ($cartProducts as $cartKey => $product) {
                    if (md5($product['key']) == $key) {
                        $found = true;
                        $productFor = $product['productFor'];
                        /* minimum quantity check[ */
                        if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
                            $minimum_quantity = ($product['selprod_min_order_qty']) ? $product['selprod_min_order_qty'] : 1;
                            if ($quantity < $minimum_quantity) {
                                $str = Labels::getLabel('LBL_Please_add_minimum_{minimumquantity}', $this->cart_lang_id);
                                $str = str_replace("{minimumquantity}", $minimum_quantity, $str);
                                $this->warning = $str . " " . FatUtility::decodeHtmlEntities($product['product_name']);
                                break;
                            }
                            /* ] */


                            $tempHoldStock = Product::tempHoldStockCount($product['selprod_id']);
                            $availableStock = $cartProducts[$cartKey]['selprod_stock'] - $tempHoldStock;
                            $userTempHoldStock = Product::tempHoldStockCount($product['selprod_id'], $cart_user_id, 0, true);

                            if ($quantity > $userTempHoldStock) {
                                if ($availableStock == 0 || ($availableStock < ($quantity - $userTempHoldStock))) {
                                    $this->warning = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->cart_lang_id);
                                    $quantity = $userTempHoldStock + $availableStock;
                                }
                            }

                            if ($quantity) {
                                $this->SYSTEM_ARR['cart']['products'][$cartKey]['quantity'] = $quantity;
                                /* to keep track of temporary hold the product stock[ */
                                $this->updateTempStockHold($product['selprod_id'], $quantity);
                                /* ] */
                                if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                                    AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $quantity, AbandonedCart::ACTION_ADDED);
                                }
                                break;
                            } else {
                                $this->remove($key);
                            }
                        }

                        /* [ update rental products */
                        if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                            $rentProObj = new ProductRental($product['selprod_id']);
                            $product['sprodata_rental_stock'];
                            $availedStock = $rentProObj->getRentalProductQuantity($product['rentalStartDate'], $product['rentalEndDate'], 0);
                            $userTempHoldStock = $rentProObj->getRentalTempHoldByCartKey($cart_user_id, $cartKey);
                            $rentalAvailedStock = $availedStock - $userTempHoldStock;
                            $rentStock = $product['sprodata_rental_stock'] - $rentalAvailedStock;
                            if ($quantity > $rentStock) {
                                $this->warning = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->cart_lang_id);
                                break;
                            }
                            if ($quantity > 0) {
                                $productListWithAddons = $this->getCartProductsDataAccToAddons($this->cart_lang_id, true)[$cartKey];
                                $this->SYSTEM_ARR['cart']['products'][$cartKey]['quantity'] = $quantity;
                                if (isset($productListWithAddons['addonsData']) && !empty($productListWithAddons['addonsData'])) {
                                    foreach($productListWithAddons['addonsData'] as $addonKey =>  $adsonProduct) {
                                        $this->SYSTEM_ARR['cart']['products'][$addonKey]['quantity'] = $quantity;
                                    }
                                }
                                
                                $this->updateRentalTempStockHold($product['selprod_id'], $quantity, $product['rentalStartDate'], $product['rentalEndDate'], $cartKey);
                                break;
                            } else {
                                $this->remove($key);
                            }
                        }
                        /* ] */
                    }
                }
            }
            $this->updateUserCart();
        } else {
            $this->error = Labels::getLabel('ERR_Quantity_should_be_greater_than_0', $this->cart_lang_id);
            return false;
        }
        if (false === $found) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->cart_lang_id);
        }
        return $found;
    }

    public function updateGroup($prodgroup_id, $quantity)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $quantity = FatUtility::int($quantity);

        $cart_user_id = $this->cart_user_id;
        /* not handled the case, if any product from the group is added separately, stock sum from that product and product in group is not checked, need to handle the same. */

        if ($quantity > 0) {
            $cartProducts = $this->getBasketProducts($this->cart_lang_id);
            if (is_array($cartProducts)) {
                $prodGroupQtyArr = array();
                $inStock = true;
                foreach ($cartProducts as $cartKey => $product) {
                    if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                        foreach ($product['products'] as $pgproduct) {
                            $tempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id']);
                            $availableStock = $pgproduct['selprod_stock'] - $tempHoldStock;
                            $userTempHoldStock = Product::tempHoldStockCount($pgproduct['selprod_id'], $cart_user_id, $product['prodgroup_id'], true);

                            if ($availableStock == 0 || ($availableStock < ($quantity - $userTempHoldStock))) {
                                $this->warning = Labels::getLabel('MSG_Requested_quantity_more_than_stock_available', $this->cart_lang_id);
                                $quantity = $userTempHoldStock + $availableStock;
                                $inStock = false;
                                break;
                            }
                            $prodGroupQtyArr[$pgproduct['selprod_id']] = $quantity;
                        }

                        if (!$inStock) {
                            break;
                        }
                    }
                }

                if (!empty($prodGroupQtyArr)) {
                    $maxAvailableQty = min($prodGroupQtyArr);
                    if ($quantity > $maxAvailableQty) {
                        /* $msgString = str_replace("{n}", $maxAvailableQty, "MSG_One_of_the_product_in_combo_is_not_available_in_requested_quantity,_you_can_buy_upto_max_{n}_quantity."); */
                        $this->warning = Labels::getLabel("MSG_One_of_the_product_in_combo_is_not_available_in_requested_quantity,_you_can_buy_upto_max_{n}_quantity.", $this->cart_lang_id);
                        $this->warning = str_replace("{n}", $maxAvailableQty, $this->warning);
                        return true;
                    }
                }

                if ($inStock) {
                    foreach ($cartProducts as $cartKey => $product) {
                        if ($product['is_batch'] && $product['prodgroup_id'] == $prodgroup_id) {
                            $this->SYSTEM_ARR['cart'][$cartKey] = $quantity;
                            foreach ($product['products'] as $pgproduct) {
                                $this->updateTempStockHold($pgproduct['selprod_id'], $quantity, $prodgroup_id);
                            }
                        }
                    }
                }
            }
            $this->updateUserCart();
        }
        return true;
    }

    public function setCartBillingAddress($address_id = 0)
    {
        $address_id = FatUtility::int($address_id);
        if (1 > $address_id) {
            $address = Address::getDefaultByRecordId(Address::TYPE_USER, $this->cart_user_id);
            if (!empty($address)) {
                $address_id = $address['addr_id'];
            }
        }
        $this->SYSTEM_ARR['shopping_cart']['billing_address_id'] = $address_id;
        $this->updateUserCart();
        return true;
    }

    public function setCartShippingAddress($address_id)
    {
        $this->SYSTEM_ARR['shopping_cart']['shipping_address_id'] = $address_id;
        $this->updateUserCart();
        return true;
    }

    public function unsetCartShippingAddress()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']);
        $this->updateUserCart();
        return true;
    }

    public function setShippingAddressSameAsBilling()
    {
        $billing_address_id = $this->getCartBillingAddress();
        if ($billing_address_id) {
            $this->setCartShippingAddress($billing_address_id);
            $this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling'] = true;
        }
    }

    public function unSetShippingAddressSameAsBilling()
    {
        if (isset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling'])) {
            unset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']);
        }
    }

    public function setCartShippingApi($shippingapi_id)
    {
        $this->SYSTEM_ARR['shopping_cart']['shippingapi_id'] = FatUtility::int($shippingapi_id);
        $this->updateUserCart();
        return true;
    }

    public function getCartBillingAddress()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['billing_address_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['billing_address_id']) : 0;
    }

    public function getCartShippingAddress()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['shipping_address_id']) : 0;
    }

    public function getCartShippingApi()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['shippingapi_id']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['shippingapi_id']) : 0;
    }

    public function getShippingAddressSameAsBilling()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['isShippingSameAsBilling']) : 0;
    }

    public function setProductShippingMethod($arr)
    {
        $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods'] = $arr;
        $this->updateUserCart();
        return true;
    }

    public function removeProductShippingMethod()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']);
        $this->updateUserCart();
        return true;
    }

    public function getProductShippingMethod()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']) ? $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods'] : array();
    }

    /* public function isProductShippingMethodSet()
      {
      if ($this->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
      return true;
      }

      foreach ($this->getProducts($this->cart_lang_id) as $product) {
      if ($product['is_batch']) {
      if ($product['has_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']])) {
      return false;
      }
      if (isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'])) {
      $mshipapi_id = $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'];
      $manualShipingApiRow = ManualShippingApi::getAttributesById($mshipapi_id, 'mshipapi_id');
      if (!$manualShipingApiRow) {
      return false;
      }
      }
      } else {
      $productKey = $product['selprod_id'];
      if ($this->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
      $productKey = date('Y-m-d', strtotime($product['rentalStartDate'])). '_'. $product['selprod_id'];
      }

      if ($product['is_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$productKey])) {
      return false; // NEED TO CHECK
      }
      }
      }
      return true;
      } */

    public function isProductShippingMethodSet()
    {
        foreach ($this->getProducts($this->cart_lang_id) as $product) {
            if ($product['is_batch']) {
                if ($product['has_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']])) {
                    return false;
                }
                if (isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'])) {
                    $mshipapi_id = $this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['group'][$product['prodgroup_id']]['mshipapi_id'];
                    $manualShipingApiRow = ManualShippingApi::getAttributesById($mshipapi_id, 'mshipapi_id');
                    if (!$manualShipingApiRow) {
                        return false;
                    }
                }
            } else {

                if ($product['is_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$product['selprod_id']]) && $product['sellerProdType'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                    return false;
                }

                //@to do : A
                /* $productKey = $product['selprod_id'];
                  if ($this->getCartType() == applicationConstants::PRODUCT_FOR_RENT) {
                  $productKey = date('Y-m-d', strtotime($product['rentalStartDate'])) . '_' . $product['selprod_id'];
                  }

                  if ($product['is_physical_product'] && !isset($this->SYSTEM_ARR['shopping_cart']['product_shipping_methods']['product'][$productKey])) {
                  return false; // NEED TO CHECK
                  } */
            }
        }
        return true;
    }

    public function getSubTotal()
    {
        $cartTotal = 0;
        $products = $this->getBasketProducts($this->cart_lang_id);
        // CommonHelper::printArray($products); die;
        if (is_array($products) && count($products) > 0) {
            foreach ($products as $product) {
                $cartTotal += $product['total'] - $product['duration_discount_total'] - $product['volume_discount_total'];
            }
        }
        return $cartTotal;
    }

    public function getCartFinancialSummary($langId, $addFullfillmentTypeCheck = true)
    {
        $products = $this->getProducts($langId, $addFullfillmentTypeCheck);
        $cartTotal = 0;
        $totalSecurity = 0;
        $cartTotalNonBatch = 0;
        $cartTotalBatch = 0;
        $shippingTotal = 0;
        $originalShipping = 0;
        $cartTotalAfterBatch = 0;
        $orderPaymentGatewayCharges = 0;
        $cartTaxTotal = 0;
        $cartDiscounts = $this->getCouponDiscounts();

        $totalSiteCommission = 0;
        $orderNetAmount = 0;
        $cartRewardPoints = $this->getCartRewardPoint();
        $cartVolumeDiscount = 0;
        $cartDurationDiscount = 0;

        $isCodEnabled = true;
        $taxOptions = [];
        $prodTaxOptions = [];
        $roundingOff = 0;
        $originalTotalPrice = 0;
        $productSelectedShippingMethodsArr = $this->getProductShippingMethod();
        $selprodIdsCount = $this->getCartSelprodIdsCount();
        $addonTotalAmount = 0;
        
        $fulfillmentProdArr = [
            Shipping::FULFILMENT_SHIP => [],
            Shipping::FULFILMENT_PICKUP => [],
        ];
        
        
        if (is_array($products) && count($products)) {
            foreach ($products as $key => $product) {
                $sellerProdType = (isset($product['sellerProdType'])) ? $product['sellerProdType'] : SellerProduct::PRODUCT_TYPE_ADDON;
                $codEnabled = false;
                if ($isCodEnabled && $product['is_cod_enabled']) {
                    $codEnabled = true;
                }
                $isCodEnabled = $codEnabled;
                if ($product['is_batch']) {
                    $cartTotal += $product['prodgroup_total'];
                } else {
                    if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $addonTotalAmount += !empty($product['total']) ? $product['total'] : 0;
                    } else {
                        $cartTotal += !empty($product['total']) ? $product['total'] : 0;
                    }
                }
                
                if ($sellerProdType != SellerProduct::PRODUCT_TYPE_ADDON) {
                    switch ($product['fulfillment_type']) {
                        case Shipping::FULFILMENT_SHIP:
                        $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                        break;
                    case Shipping::FULFILMENT_PICKUP:
                        $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                        break;
                    default:
                        $fulfillmentProdArr[Shipping::FULFILMENT_SHIP][] = $product['selprod_id'];
                        $fulfillmentProdArr[Shipping::FULFILMENT_PICKUP][] = $product['selprod_id'];
                        break;
                    }
                }
                
                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                    $cartDurationDiscount += $product['duration_discount_total'];
                    $duration = CommonHelper::getDifferenceBetweenDates($product['rentalStartDate'], $product['rentalEndDate'], $product['selprod_user_id'], $product['sprodata_duration_type']);
                    $priceArr = CommonHelper::getRentalPricesArr($product);

                    /* $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr); */
                    /* $taxableProdPrice = $rentalPrice - $product['duration_discount']; */
                    $taxableProdPrice = $product['theprice'];
                    if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                        $taxableProdPrice = $product['theprice'] - $product['duration_discount'];
                    }
                    $totalSecurity += $product['sprodata_rental_security'] * $product['quantity'];
                } else {
                    $cartVolumeDiscount += $product['volume_discount_total'];
                    $taxableProdPrice = $product['theprice'];
                    if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                        $taxableProdPrice = $product['theprice'] - $product['volume_discount'];
                    }
                }

                if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0)) {
                    if (isset($cartDiscounts['discountedSelProdIds']) && array_key_exists($product['selprod_id'], $cartDiscounts['discountedSelProdIds'])) {
                        $taxableProdPrice = $taxableProdPrice - ($cartDiscounts['discountedSelProdIds'][$product['selprod_id']]) / $product['quantity'];
                    }
                }

                $isProductShippedBySeller = Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id']);
                $productId = $product['product_id'];
                if ($product['productFor'] == applicationConstants::PRODUCT_FOR_RENT) {
                    $isProductShippedBySeller = 1;
                }
                if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $productId = $product['selprod_id'];
                    $isProductShippedBySeller = 1;
                }

                $shippingCost = 0;
                if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$product['selprod_id']])) {
                    $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$product['selprod_id']];
                    $productCount = (isset($selprodIdsCount[$product['selprod_id']])) ? $selprodIdsCount[$product['selprod_id']] : 1;
                    $shippingCost = ROUND(($shippingDurationRow['mshipapi_cost'] / $productCount), 2);
                }

                $taxObj = new Tax();
                $shippingAddressId = $this->getCartShippingAddress();

                $address = new Address($shippingAddressId, $this->cart_lang_id);
                $shippingAddressDetail = $address->getData(Address::TYPE_USER, $this->cart_user_id);
                /* $addressForTax = $this->getTaxAddress($key, 3); */
                $extraData = array(
                    'billingAddress' => isset($product['billing_address']) ? $product['billing_address'] : '',
                    'shippingAddress' => $shippingAddressDetail,
                    'shippedBySeller' => $isProductShippedBySeller,
                    'shippingCost' => $shippingCost,
                    'buyerId' => $this->cart_user_id
                );

                if (self::PAGE_TYPE_CART != $this->pageType) {
                    $productId = $product['product_id'];
                    if ($sellerProdType == SellerProduct::PRODUCT_TYPE_ADDON) {
                        $productId = $product['selprod_id'];
                    }

                    $taxData = $taxObj->calculateTaxRates($productId, $taxableProdPrice, $product['selprod_user_id'], $langId, $product['quantity'], $extraData, $this->cartCache, $sellerProdType, $product['productFor']);

                    if (false == $taxData['status'] && $taxData['msg'] != '') {
                        $this->error = $taxData['msg'];
                    }
                    if (array_key_exists('options', $taxData)) {
                        foreach ($taxData['options'] as $optionId => $optionval) {
                            $prodTaxOptions[$product['selprod_id']][$optionId] = $optionval;
                            if (isset($optionval['value']) && 0 < $optionval['value']) {
                                $taxOptions[$optionval['name']]['value'] = isset($taxOptions[$optionval['name']]['value']) ? ($taxOptions[$optionval['name']]['value'] + $optionval['value']) : $optionval['value'];
                                $taxOptions[$optionval['name']]['title'] = CommonHelper::displayTaxPercantage($optionval);
                            }
                        }
                    }

                    $tax = $taxData['tax'];
                    $cartTaxTotal += $tax;
                }

                $originalShipping += $product['shipping_cost'];
                $totalSiteCommission += $product['commission'];
                $shippingTotal += $product['shipping_cost'];
                /* if (!$product['shop_eligible_for_free_shipping'] ||  $product['psbs_user_id'] == 0) {
                  $shippingTotal += $product['shipping_cost'];
                  } */

                $roundingOff += $product['rounding_off'];
                $originalTotalPrice += ($product['actualPrice'] * $product['quantity']);
            }
        }

        $cartTotalAfterBatch = $cartTotalBatch + $cartTotalNonBatch;
        //$netTotalAfterDiscount = $netTotalWithoutDiscount;
        $userWalletBalance = User::getUserBalance($this->cart_user_id);
        //$orderCreditsCharge = $this->isCartUserWalletSelected() ? min($netTotalAfterDiscount, $userWalletBalance) : 0;
        //$orderPaymentGatewayCharges = $netTotalAfterDiscount - $orderCreditsCharge;

        $totalDiscountAmount = (isset($cartDiscounts['coupon_discount_total'])) ? $cartDiscounts['coupon_discount_total'] : 0;
        $orderNetAmount = (max(($cartTotal + $addonTotalAmount + $totalSecurity) - $cartVolumeDiscount - $cartDurationDiscount - $totalDiscountAmount, 0) + $shippingTotal + $cartTaxTotal + $roundingOff);

        $orderNetAmount = $orderNetAmount - CommonHelper::rewardPointDiscount($orderNetAmount, $cartRewardPoints);
        if ($cartDurationDiscount > 0) {
            $orderNetAmount = (max($cartTotal - $cartDurationDiscount - $totalDiscountAmount, 0) + $totalSecurity + $shippingTotal + $cartTaxTotal + $addonTotalAmount + $roundingOff - CommonHelper::rewardPointDiscount($orderNetAmount, $cartRewardPoints));
        }
        $WalletAmountCharge = ($this->isCartUserWalletSelected()) ? min($orderNetAmount, $userWalletBalance) : 0;
        $orderPaymentGatewayCharges = $orderNetAmount - $WalletAmountCharge;

        $isCodValidForNetAmt = true;
        if (FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0) > 0) {
            if (($orderPaymentGatewayCharges >= FatApp::getConfig("CONF_MIN_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0)) && ($orderPaymentGatewayCharges <= FatApp::getConfig("CONF_MAX_COD_ORDER_LIMIT", FatUtility::VAR_INT, 0)) && ($isCodEnabled)) {
                $isCodValidForNetAmt = true;
            } else {
                $isCodValidForNetAmt = false;
            }
        }

        $netChargeAmt = $cartTotal + $addonTotalAmount + $cartTaxTotal + $totalSecurity - ((0 < $cartVolumeDiscount) ? $cartVolumeDiscount : 0) - ((0 < $cartDurationDiscount) ? $cartDurationDiscount : 0);
        $pendingLateCharges = 0;
        $opIdsStr = '';
        if (UserAuthentication::isUserLogged() && FatUtility::int($this->cart_user_id) > 0) {
            $pendingLateChargesDetails = BuyerLateChargesHistory::getUserPendingChargesTotalDetails($this->cart_user_id);
            if (!empty($pendingLateChargesDetails)) {
                $opIdsStr = $pendingLateChargesDetails['op_ids'];
                $pendingLateCharges = $pendingLateChargesDetails['pendingCharges'];
            }
        }

        $cartSummary = array(
            'pendingLateCharges' => $pendingLateCharges,
            'op_ids_for_pending_charges' => $opIdsStr,
            'cartTotal' => $cartTotal,
            'addonTotalAmount' => $addonTotalAmount,
            'shippingTotal' => $shippingTotal,
            'originalShipping' => $originalShipping,
            'cartTaxTotal' => $cartTaxTotal,
            'cartDiscounts' => $cartDiscounts,
            'cartVolumeDiscount' => $cartVolumeDiscount,
            'cartDurationDiscount' => $cartDurationDiscount,
            'cartRewardPoints' => $cartRewardPoints,
            'cartWalletSelected' => $this->isCartUserWalletSelected(),
            'siteCommission' => $totalSiteCommission,
            'orderNetAmount' => $orderNetAmount + $pendingLateCharges,
            'WalletAmountCharge' => $WalletAmountCharge,
            'isCodEnabled' => $isCodEnabled,
            'isCodValidForNetAmt' => $isCodValidForNetAmt,
            'orderPaymentGatewayCharges' => $orderPaymentGatewayCharges,
            'netChargeAmount' => $netChargeAmt,
            'taxOptions' => $taxOptions,
            'prodTaxOptions' => $prodTaxOptions,
            'roundingOff' => $roundingOff,
            'rentalSecurityTotal' => $totalSecurity,
            'cartType' => $this->getCartType(),
            'shipProductsCount' => count($fulfillmentProdArr[Shipping::FULFILMENT_SHIP]),
            'pickUpProductsCount' => count($fulfillmentProdArr[Shipping::FULFILMENT_PICKUP]),
        );
        return $cartSummary;
    }

    public function getCouponDiscounts()
    {
        $couponObj = new DiscountCoupons();
        if (!$this->getCartDiscountCoupon()) {
            return false;
        }

        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        $couponInfo = $couponObj->getValidCoupons($this->cart_user_id, $this->cart_lang_id, $this->getCartDiscountCoupon(), $orderId);
        $cartSubTotal = $this->getSubTotal();

        $couponData = array();

        if ($couponInfo) {
            $discountTotal = 0;
            $cartProducts = $this->getBasketProducts($this->cart_lang_id);

            $prodObj = new Product();

            /* binded product_ids are not in array, are in string, so converting the same to array[ */

            if (!empty($couponInfo['grouped_coupon_products'])) {
                $couponInfo['grouped_coupon_products'] = explode(",", $couponInfo['grouped_coupon_products']);
            } else {
                $couponInfo['grouped_coupon_products'] = array();
            }

            if (!empty($couponInfo['grouped_coupon_users'])) {
                $couponInfo['grouped_coupon_users'] = explode(",", $couponInfo['grouped_coupon_users']);
            } else {
                $couponInfo['grouped_coupon_users'] = array();
            }

            if (!empty($couponInfo['grouped_coupon_categories'])) {
                $couponInfo['grouped_coupon_categories'] = explode(",", $couponInfo['grouped_coupon_categories']);
                $productIdsArr = array();

                foreach ($cartProducts as $cartProduct) {
                    $cartProdCategoriesArr = $prodObj->getProductCategories($cartProduct['product_id']);
                    if ($cartProdCategoriesArr == false || empty($cartProdCategoriesArr)) {
                        continue;
                    }

                    foreach ($cartProdCategoriesArr as $cartProdCategory) {
                        if (in_array($cartProdCategory['prodcat_id'], $couponInfo['grouped_coupon_categories'])) {
                            $productIdsArr[] = $cartProduct['product_id'];
                        }
                    }
                }

                if (!empty($productIdsArr)) {
                    $couponInfo['grouped_coupon_products'] = array_merge($couponInfo['grouped_coupon_products'], $productIdsArr);
                    /*
                      if (empty($couponInfo['grouped_coupon_products']) || $this->cart_user_id == $couponInfo['grouped_coupon_users']) {
                      $couponInfo['grouped_coupon_products'] = $productIdsArr;
                      } else {
                      $couponInfo['grouped_coupon_products'] = array_merge($couponInfo['grouped_coupon_products'], $productIdsArr);
                      }
                     * 
                     */
                }
            }
            /* ] */

            if (!empty($couponInfo['grouped_coupon_shops'])) {
                $couponInfo['grouped_coupon_shops'] = explode(",", $couponInfo['grouped_coupon_shops']);
                $productIdsArr = array();
                foreach ($cartProducts as $cartProduct) {
                    if (in_array($cartProduct['shop_id'], $couponInfo['grouped_coupon_shops'])) {
                        $productIdsArr[] = $cartProduct['product_id'];
                    }
                }
                if (!empty($productIdsArr)) {
                    $couponInfo['grouped_coupon_products'] = array_merge($couponInfo['grouped_coupon_products'], $productIdsArr);
                }
            }
            if (!empty($couponInfo['grouped_coupon_brands'])) {
                $couponInfo['grouped_coupon_brands'] = explode(",", $couponInfo['grouped_coupon_brands']);

                $productIdsArr = array();
                foreach ($cartProducts as $cartProduct) {
                    if (in_array($cartProduct['brand_id'], $couponInfo['grouped_coupon_brands'])) {
                        $productIdsArr[] = $cartProduct['product_id'];
                    }
                }
                if (!empty($productIdsArr)) {
                    $couponInfo['grouped_coupon_products'] = array_merge($couponInfo['grouped_coupon_products'], $productIdsArr);
                }
            }

            if ((empty($couponInfo['grouped_coupon_products']) && in_array($this->cart_user_id, $couponInfo['grouped_coupon_users'])) or empty($couponInfo['grouped_coupon_products'])) {
                $subTotal = $cartSubTotal;
            } else {
                $subTotal = 0;
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* if ( in_array($product['prodgroup_id'], $couponInfo['groups']) ){
                          $subTotal += $product['prodgroup_total'];
                          } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $subTotal += $cartProduct['total'] - $cartProduct['duration_discount_total'];
                        }
                    }
                }
            }
            
            if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                $couponInfo['coupon_discount_value'] = min($couponInfo['coupon_discount_value'], $subTotal);
            }

            $cartVolumeDiscount = 0;
            $cartDurationDiscount = 0;
            foreach ($cartProducts as $cartProduct) {
                $discount = 0;
                $cartVolumeDiscount += $cartProduct['volume_discount_total'];
                $cartDurationDiscount += $cartProduct['duration_discount_total'];
                if ((empty($couponInfo['grouped_coupon_products']) && in_array($this->cart_user_id, $couponInfo['grouped_coupon_users'])) || empty($couponInfo['grouped_coupon_products'])) {
                    $status = true;
                } else {
                    if ($cartProduct['is_batch']) {
                        /* if (in_array($cartProduct['prodgroup_id'], $couponInfo['groups'])) {
                          $status = true;
                          } else {
                          $status = false;
                          } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                    }
                }

                if ($status) {
                    if ($cartProduct['is_batch']) {
                        /* if (!$couponInfo['coupon_discount_in_percent']) {
                          $discount = $couponInfo['coupon_discount_value'] * ($cartProduct['prodgroup_total'] / $subTotal);
                          }else{
                          $discount = ( $cartProduct['prodgroup_total'] / 100 ) * $couponInfo['coupon_discount_value'];
                          } */
                    } else {
                        if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                            $discount = $couponInfo['coupon_discount_value'] * (($cartProduct['total'] - $cartProduct['volume_discount_total'] - $cartProduct['duration_discount_total']) / $subTotal);
                        } else {
                            $discount = (($cartProduct['total'] - $cartProduct['volume_discount_total'] - $cartProduct['duration_discount_total']) / 100) * $couponInfo['coupon_discount_value'];
                        }
                    }
                }
                $discountTotal += $discount;
            }

            if ($discountTotal > $couponInfo['coupon_max_discount_value'] && $couponInfo['coupon_discount_in_percent'] == applicationConstants::PERCENTAGE) {
                $discountTotal = $couponInfo['coupon_max_discount_value'];
            }

            $selProdDiscountTotal = 0;
            $discountTypeArr = DiscountCoupons::getTypeArr($this->cart_lang_id);

            /* [ Calculate discounts for each Seller Products */
            $discountedSelProdIds = array();
            $discountedProdGroupIds = array();

            if ((empty($couponInfo['grouped_coupon_products']) && in_array($this->cart_user_id, $couponInfo['grouped_coupon_users'])) or empty($couponInfo['grouped_coupon_products'])) {
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* $totalSelProdDiscount = round(($discountTotal*$cartProduct['prodgroup_total'])/$subTotal,2);
                          $selProdDiscountTotal += $totalSelProdDiscount;
                          $discountedProdGroupIds[$cartProduct['prodgroup_id']] = round($totalSelProdDiscount,2); */
                    } else {
                        $balTotal = ($subTotal /* - $cartVolumeDiscount - $cartDurationDiscount */ );
                        $balTotal = 1 > $balTotal ? 1 : $balTotal;
                        
                        $totalSelProdDiscount = 1 > $discountTotal ? 0 : round(($discountTotal * ($cartProduct['total'] - $cartProduct['volume_discount_total'] - $cartProduct['duration_discount_total'])) / $balTotal, 2); 

                        $selProdDiscountTotal += $totalSelProdDiscount;
                        $discountedSelProdIds[$cartProduct['selprod_id']] = round($totalSelProdDiscount, 2);
                    }
                }
            } else {
                foreach ($cartProducts as $cartProduct) {
                    if ($cartProduct['is_batch']) {
                        /* if (in_array($cartProduct['prodgroup_id'], $couponInfo['groups'])) {
                          $totalSelProdDiscount = round(($discountTotal*$cartProduct['prodgroup_total'])/$subTotal,2);
                          $selProdDiscountTotal += $totalSelProdDiscount;
                          $discountedProdGroupIds[$cartProduct['prodgroup_id']] = round($totalSelProdDiscount,2);
                          } */
                    } else {
                        if (in_array($cartProduct['product_id'], $couponInfo['grouped_coupon_products'])) {
                            $balTotal = ($subTotal - $cartVolumeDiscount - $cartDurationDiscount);
                            $balTotal = 1 > $balTotal ? 1 : $balTotal;

                            $totalSelProdDiscount = 1 > $discountTotal ? 0 : round(($discountTotal * ($cartProduct['total'] - $cartProduct['volume_discount_total'] - $cartProduct['duration_discount_total'])) / $balTotal, 2);
                            $selProdDiscountTotal += $totalSelProdDiscount;
                            $discountedSelProdIds[$cartProduct['selprod_id']] = round($totalSelProdDiscount, 2);
                        }
                    }
                }
            }
            /* ] */
            $selProdDiscountTotal = $selProdDiscountTotal /* - $cartVolumeDiscount */;
            $labelArr = array(
                'coupon_label' => $couponInfo["coupon_title"],
                'coupon_id' => $couponInfo["coupon_id"],
                'coupon_discount_in_percent' => $couponInfo["coupon_discount_in_percent"],
                'max_discount_value' => $couponInfo["coupon_max_discount_value"]
            );

            if ($couponInfo['coupon_discount_in_percent'] == applicationConstants::PERCENTAGE) {
                if ($selProdDiscountTotal > $couponInfo['coupon_max_discount_value']) {
                    $selProdDiscountTotal = $couponInfo['coupon_max_discount_value'];
                }
            } elseif ($couponInfo['coupon_discount_in_percent'] == applicationConstants::FLAT) {
                if ($selProdDiscountTotal > $couponInfo["coupon_discount_value"]) {
                    $selProdDiscountTotal = $couponInfo["coupon_discount_value"];
                }
            }

            $couponData = array(
                'coupon_discount_type' => $couponInfo["coupon_type"],
                'coupon_code' => $couponInfo["coupon_code"],
                'coupon_discount_value' => $couponInfo["coupon_discount_value"],
                'coupon_discount_total' => ($selProdDiscountTotal < 0) ? 0 : $selProdDiscountTotal,
                'coupon_info' => json_encode($labelArr),
                'discountedSelProdIds' => $discountedSelProdIds,
                'discountedProdGroupIds' => $discountedProdGroupIds,
            );
        }

        if (empty($couponData)) {
            return false;
        }
        $this->discounts = $couponData;
        return $couponData;
    }

    public function updateCartWalletOption($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function updateNumericOrderId($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['order_numeric_id'] = $val;
        $this->updateUserCart();
        return true;
    }
    
    public function updateCartDiscountCoupon($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function removeCartDiscountCoupon()
    {
        $couponCode = array_key_exists('discount_coupon', $this->SYSTEM_ARR['shopping_cart']) ? $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] : '';
        unset($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);

        /* Removing from temp hold[ */
        if ((UserAuthentication::isUserLogged() || UserAuthentication::isGuestUserLogged()) && $couponCode != '') {
            $loggedUserId = UserAuthentication::getLoggedUserId();

            $srch = DiscountCoupons::getSearchObject(0, false, false);
            $srch->addCondition('coupon_code', '=', $couponCode);
            $srch->setPageSize(1);
            $srch->addMultipleFields(array('coupon_id'));
            $rs = $srch->getResultSet();
            $couponRow = FatApp::getDb()->fetch($rs);

            if ($couponRow && $loggedUserId) {
                FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD, array('smt' => 'couponhold_coupon_id = ? AND couponhold_user_id = ?', 'vals' => array($couponRow['coupon_id'], $loggedUserId)));
            }
        }

        $orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';
        if ($orderId != '') {
            FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER, array('smt' => 'ochold_order_id = ?', 'vals' => array($orderId)));
        }

        /* ] */

        $this->updateUserCart();
        return true;
    }

    public function updateCartUseRewardPoints($val)
    {
        $this->SYSTEM_ARR['shopping_cart']['reward_points'] = $val;
        $this->updateUserCart();
        return true;
    }

    public function removeUsedRewardPoints()
    {
        if (isset($this->SYSTEM_ARR['shopping_cart']) && array_key_exists('reward_points', $this->SYSTEM_ARR['shopping_cart'])) {
            unset($this->SYSTEM_ARR['shopping_cart']['reward_points']);
            $this->updateUserCart();
        }
        return true;
    }

    public function getCartRewardPoint()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['reward_points']) ? $this->SYSTEM_ARR['shopping_cart']['reward_points'] : 0;
    }

    public function getCartDiscountCoupon()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['discount_coupon']) ? $this->SYSTEM_ARR['shopping_cart']['discount_coupon'] : '';
    }

    public function isDiscountCouponSet()
    {
        return !empty($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);
    }

    public function isCartUserWalletSelected()
    {
        return (isset($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet']) && intval($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet']) == 1) ? 1 : 0;
    }

    public function updateUserCart()
    {
        if (isset($this->cart_user_id)) {
            $record = new TableRecord('tbl_user_cart');
            $cart_arr = $this->SYSTEM_ARR['cart'];
            if (isset($this->SYSTEM_ARR['shopping_cart']) && is_array($this->SYSTEM_ARR['shopping_cart']) && (!empty($this->SYSTEM_ARR['shopping_cart']))) {
                $cart_arr["shopping_cart"] = $this->SYSTEM_ARR['shopping_cart'];
            }
            $cart_arr = json_encode($cart_arr);
            $record->assignValues(array("usercart_user_id" => $this->cart_user_id, "usercart_type" => CART::TYPE_PRODUCT, "usercart_details" => $cart_arr, "usercart_added_date" => date('Y-m-d H:i:s'), "usercart_last_used_date" => date('Y-m-d H:i:s'), "usercart_last_session_id" => $this->cart_id));
            if (!$record->addNew(array(), array('usercart_details' => $cart_arr, "usercart_added_date" => date('Y-m-d H:i:s'), "usercart_last_used_date" => date('Y-m-d H:i:s'), "usercart_last_session_id" => $this->cart_id, "usercart_sent_reminder" => 0))) {
                Message::addErrorMessage($record->getError());
                throw new Exception('');
            }
        }
    }

    /* to keep track of temporary hold the product stock[ */

    public function updateTempStockHold($selprod_id, $quantity = 0, $prodgroup_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $quantity = FatUtility::int($quantity);
        $prodgroup_id = FatUtility::int($prodgroup_id);
        if (!$selprod_id) {
            return;
        }
        $db = FatApp::getDb();

        if ($quantity <= 0) {
            $db->deleteRecords('tbl_product_stock_hold', array('smt' => 'pshold_selprod_id = ? AND pshold_user_id = ? AND pshold_prodgroup_id = ?', 'vals' => array($selprod_id, $this->cart_user_id, $prodgroup_id)));
            return;
        }

        $dataArrToSave = array(
            'pshold_selprod_id' => $selprod_id,
            'pshold_user_id' => $this->cart_user_id,
            'pshold_prodgroup_id' => $prodgroup_id,
            'pshold_selprod_stock' => $quantity,
            'pshold_added_on' => date('Y-m-d H:i:s')
        );
        if (!$db->insertFromArray('tbl_product_stock_hold', $dataArrToSave, true, array(), $dataArrToSave)) {
            Message::addErrorMessage($db->getError());
            throw new Exception('');
        }

        /* delete old records[ */
        $this->deleteProductStockHold();
        /* ] */
    }

    /* ] */

    public function clear($includeAbandonedCart = false)
    {
        if ($includeAbandonedCart == true) {
            $cartProducts = $this->getProducts($this->cart_lang_id);
            if (is_array($cartProducts)) {
                foreach ($cartProducts as $cartKey => $product) {
                    if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                        AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $product['quantity'], AbandonedCart::ACTION_DELETED);
                    }
                }
            }
        }

        $this->products = array();
        $this->SYSTEM_ARR['cart'] = array();
        $this->SYSTEM_ARR['shopping_cart'] = array();
        unset($_SESSION['shopping_cart']["order_id"]);
        unset($_SESSION['wallet_recharge_cart']["order_id"]);
        unset($_SESSION["order_id"]);
    }

    public static function setCartAttributes($userId = 0, $tempUserId = 0)
    {
        $db = FatApp::getDb();

        $cart_user_id = static::getCartUserId($tempUserId);

        if (empty($tempUserId)) {
            $tempUserId = session_id();
        }

        /* to keep track of temporary hold the product stock[ */
        $cObj = new Cart();
        if ($cObj->getCartType() == applicationConstants::PRODUCT_FOR_RENT || $cObj->getCartType() == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            $db->updateFromArray(ProductRental::DB_TBL_RENTAl_STOCK_HOLD, array('rentpshold_user_id' => $cart_user_id), array('smt' => 'rentpshold_user_id = ?', 'vals' => array($tempUserId)));
        } else {
            $db->updateFromArray('tbl_product_stock_hold', array('pshold_user_id' => $cart_user_id), array('smt' => 'pshold_user_id = ?', 'vals' => array($tempUserId)));
        }
        /* ] */

        $userId = FatUtility::int($userId);
        if ($userId == 0 && $tempUserId == 0) {
            return false;
        }

        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $tempUserId);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();

        if (!$row = FatApp::getDb()->fetch($rs)) {
            return false;
        }

        $cartInfo = json_decode($row["usercart_details"], true);

        /* [ OLD CART DATA */
        $srch = new SearchBase('tbl_user_cart');
        $srch->addCondition('usercart_user_id', '=', $userId);
        $srch->addCondition('usercart_type', '=', CART::TYPE_PRODUCT);
        $rs = $srch->getResultSet();
        $oldCartData = FatApp::getDb()->fetch($rs);
        $oldCartData = ($oldCartData == false) ? [] : $oldCartData;

        if (!empty($oldCartData)) { /* REMOVE OLD PRODUCTS FROM CART IF CART TYPE IS NOT SAME */
            $oldcartInfo = json_decode($oldCartData["usercart_details"], true);
            $oldcartType = $oldcartInfo['cart_type'];
            $newcartType = $cartInfo['cart_type'];
            if (FatUtility::int($oldcartType) != FatUtility::int($newcartType)) {
                if ($oldcartType == applicationConstants::PRODUCT_FOR_RENT || $oldcartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
                    $db->deleteRecords(ProductRental::DB_TBL_RENTAl_STOCK_HOLD, array('smt' => '`rentpshold_user_id`=?', 'vals' => array($userId)));
                } else {
                    $db->deleteRecords('tbl_product_stock_hold', array('smt' => '`pshold_user_id`=? ', 'vals' => array($userId)));
                }
                $db->deleteRecords('tbl_user_cart', array('smt' => '`usercart_user_id`=? and usercart_type=?', 'vals' => array($userId, CART::TYPE_PRODUCT)));
                //Message::addErrorMessage(Labels::getLabel('MSG_Products_in_cart_must_be_extend_rental_or_all_for_buy_or_all_for_rent', CommonHelper::getLangId()));
                //return false;
            }
        }
        /* ] */


        $cartObj = new Cart($userId, 0, $tempUserId);
        foreach ($cartInfo['products'] as $key => $product) {
            if (false === $keyDecoded = base64_decode($key, true)) {
                continue;
            }
            $keyDecoded = json_decode($keyDecoded, true);
            $quantity = $product['quantity'];
            $productFor = $product['productFor'];
            $rentalData = array();
            if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                $rentalData = array(
                    'rental_start_date' => $product['rental_start_date'],
                    'rental_end_date' => $product['rental_end_date'],
                    'mainProductId' => $product['mainProductId'],
                    'sellerProdType' => $product['sellerProdType'],
                    'duration_type' => $product['duration_type'],
                );
            }

            $selprod_id = 0;
            $prodgroup_id = 0;
            if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $rentalStartDate = $product['rental_start_date'];
                    $rentalEndDate = $product['rental_end_date'];
                    $keyDecoded = str_replace($rentalStartDate . $rentalEndDate, '', $keyDecoded);
                }
                $str = filter_var(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded), FILTER_SANITIZE_NUMBER_INT);
                $selprod_id = FatUtility::int($str);
            }
            if (strpos($keyDecoded, static::CART_KEY_PREFIX_BATCH) !== false) {
                $str = filter_var(str_replace(static::CART_KEY_PREFIX_BATCH, '', $keyDecoded), FILTER_SANITIZE_NUMBER_INT);
                $prodgroup_id = FatUtility::int($str);
            }

            $cartObj->add($selprod_id, $quantity, $prodgroup_id, '', $productFor, $rentalData);
            $db->deleteRecords('tbl_user_cart', array('smt' => '`usercart_user_id`=? and usercart_type=?', 'vals' => array($tempUserId, CART::TYPE_PRODUCT)));
        }
        $cartObj->updateUserCart();
    }

    /* public function shippingCarrierList(int $langId = 0)
      {
      $langId = (0 < $langId) ? $langId : commonHelper::getLangId();

      $plugin = new Plugin();
      $shippingServiceName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
      $carriers = [];
      if (false !== $shippingServiceName) {
      $error = '';
      $shippingService = PluginHelper::callPlugin($shippingServiceName, [$langId], $error, $langId);
      if (false === $shippingService) {
      $this->error = $error;
      return false;
      }
      $carriers = $shippingService->getCarriers(true, $langId);
      if (empty($carriers) && !empty($shippingService->getError())) {
      $this->error = $shippingService->getError();
      return false;
      }
      }
      return $carriers;
      } */

    public function getCache($key)
    {
        require_once(CONF_INSTALLATION_PATH . 'library/phpfastcache.php');
        phpFastCache::setup("storage", "files");

        phpFastCache::setup("path", CONF_UPLOADS_PATH . "caching");

        $cache = phpFastCache();
        return $cache->get($key);
    }

    private function setCache($key, $value)
    {
        require_once(CONF_INSTALLATION_PATH . 'library/phpfastcache.php');
        phpFastCache::setup("storage", "files");
        phpFastCache::setup("path", CONF_UPLOADS_PATH . "caching");
        $cache = phpFastCache();
        return $cache->set($key, $value, 60 * 60);
    }

    public function getCarrierShipmentServicesList($cartKey, $carrier_id = 0, $lang_id = 0)
    {
        /* $servicesList = array();

          $servicesList[0] = Labels::getLabel('MSG_Select_Services', $lang_id);

          if (!empty($carrier_id)) {
          foreach ($services as $key => $value) {
          $code = $value->serviceCode;
          $price = $value->shipmentCost + $value->otherCost;
          $name = $value->serviceName;
          $displayPrice = CommonHelper::displayMoneyFormat($price);
          $label = $name . " (" . $displayPrice . " )";
          $servicesList[$code . "-" . $price] = $label;
          }
          }

          $products = $this->getProducts($this->cart_lang_id);
          $prodKey = $this->getProductByKey($cartKey); */

        return $this->getCarrierShipmentServices($cartKey, $carrier_id, $lang_id);
    }

    public function getProductByKey($find_key)
    {
        if (!$this->hasPhysicalProduct()) {
            return false;
        }

        foreach ($this->SYSTEM_ARR['cart']['products'] as $key => $cart) {
            if ($find_key == md5($key)) {
                return $key;
            }
        }
        return false;
    }

    public function getCarrierShipmentServices($product_key, $carrier_id, $lang_id)
    {
        $key = $this->getProductByKey($product_key);
        if (false === $key || empty($carrier_id)) {
            return array();
        }

        $products = $this->getProducts($this->cart_lang_id);
        $weightUnitsArr = applicationConstants::getWeightUnitsArr($lang_id, true);
        $lengthUnitsArr = applicationConstants::getLengthUnitsArr($lang_id, true);

        $product = $products[$key];
        $productShippingAddress = $product['shipping_address'];
        $productShopAddress = $product['seller_address'];

        $sellerPinCode = $productShopAddress['shop_postalcode'];
        $quantity = $product['quantity'];
        $productWeight = $product['product_weight'] / $quantity;
        $productWeightClass = ($product['product_weight_unit']) ? $lengthUnitsArr[$product['product_weight_unit']] : '';

        $productLengthUnit = ($product['product_dimension_unit']) ? $weightUnitsArr[$product['product_dimension_unit']] : '';
        $productLength = $product['product_length'];
        $productWidth = $product['product_width'];
        $productHeight = $product['product_height'];

        $productWeightInOunce = Shipping::convertWeightInOunce($productWeight, $productWeightClass);
        $productLengthInCenti = Shipping::convertLengthInCenti($productLength, $productLengthUnit);
        $productWidthInCenti = Shipping::convertLengthInCenti($productWidth, $productLengthUnit);
        $productHeightInCenti = Shipping::convertLengthInCenti($productHeight, $productLengthUnit);

        $product_rates = array();

        $plugin = new Plugin();
        $shippingServiceName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
        if (false !== $shippingServiceName) {
            $error = '';
            $this->shippingService = PluginHelper::callPlugin($shippingServiceName, [$lang_id], $error, $lang_id);
            if (false === $this->shippingService) {
                LibHelper::dieJsonError($error);
            }

            $this->shippingService->setAddress($productShippingAddress['addr_name'], $productShippingAddress['addr_address1'], $productShippingAddress['addr_address2'], $productShippingAddress['addr_city'], $productShippingAddress['state_code'], $productShippingAddress['addr_zip'], $productShippingAddress['country_code'], $productShippingAddress['addr_phone']);

            $this->shippingService->setWeight($productWeightInOunce);

            if ($productLengthInCenti > 0 && $productWidthInCenti > 0 && $productHeightInCenti > 0) {
                $this->shippingService->setDimensions($productLengthInCenti, $productWidthInCenti, $productHeightInCenti);
            }

            $product_rates = $this->shippingService->getRates($carrier_id, $sellerPinCode);
            if (empty($product_rates)) {
                $this->error = $this->shippingService->getError();
                return false;
            }

            $product_rates = Shipping::formatShippingRates($product_rates, $lang_id);
        }

        return $product_rates;
    }

    public function setselectedShipping(array $selectedShippingService)
    {
        $this->selectedShippingService = $selectedShippingService; /* Selected Shipping Service */
    }

    public function getShippingRates()
    {
        $shippingOptions = $this->getShippingOptions($this->getCartType());

        if (false == $shippingOptions) {
            return false;
        }

        $shippedByArr = array_keys($shippingOptions);
        $shippingRates = [];
        foreach ($shippedByArr as $hippedBy) {
            $shopLevelTotalAmount = (isset($shippingOptions[$hippedBy]['shopTotalAmount'])) ? $shippingOptions[$hippedBy]['shopTotalAmount'] : 0;
        
            foreach ($shippingOptions[$hippedBy] as $level => $levelItems) {
                $rates = isset($levelItems['rates']) ? $levelItems['rates'] : [];
                if (count($rates) <= 0) {
                    continue;
                }
                if ($level != Shipping::LEVEL_PRODUCT) {
                    $name = current($rates)['code'];
                    $isFreeShipEnable = current($levelItems['products'])['shop_is_free_ship_active'];
                    $isFreeShipAmount = current($levelItems['products'])['shop_free_shipping_amount'];
                    if ($isFreeShipEnable && $shopLevelTotalAmount >= $isFreeShipAmount) {
                        foreach ($rates as $key => $rate) {
                            $rates[$key]['cost'] = 0;
                        }
                    }
                    $shippingRates[$name] = $rates;
                } else if (isset($levelItems['products'])) {
                    foreach ($levelItems['products'] as $key => $product) {
                        $isFreeShipEnable = $product['shop_is_free_ship_active'];
                        $isFreeShipAmount = $product['shop_free_shipping_amount'];
                        if (count($rates[$key]) <= 0) {
                            continue;
                        }
                        $name = current($rates[$key])['code'];
                        if ($isFreeShipEnable && $shopLevelTotalAmount >= $isFreeShipAmount) {
                            foreach ($rates[$key] as $rkey => $rate) {
                                $rates[$key][$rkey]['cost'] = 0;
                            }
                        }
                        $shippingRates[$name] = $rates[$key];
                    }
                }
            }
        }

        return $shippingRates;
    }


    public function getPickupOptions($cartProducts = array())
    {
        if (empty($cartProducts)) {
            $cartProducts = $this->getBasketProducts($this->cart_lang_id);
        }
        $selProdIdArr = array();
        $shopIdArr = array();
        $selProdWithShopId = array();
        foreach ($cartProducts as $product) {
            /*if ($product['isProductShippedBySeller'] == applicationConstants::NO) {
                continue;
            }*/
            if($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                $selProdIdArr[] = $product['selprod_id'];
                $shopIdArr[] = $product['shop_id'];
                $selProdWithShopId[$product['selprod_id']] = $product['shop_id'];
            }
        }

        $prodPickAddArr = [];
        $addrAvailForSelProd = [];
        $shopAddressGroup = [];
        if (!empty($selProdIdArr)) {
            $address = new Address();
            $selProdIdArr = array_unique($selProdIdArr);
            $addresses = $address->getPickupData(Address::TYPE_SHOP_PICKUP, $selProdIdArr, 0, false);
            
            if (!empty($addresses)) {
                foreach ($addresses as $pickAddr) {
                    $prodPickAddArr[$pickAddr['sptpa_selprod_id']][] = $pickAddr['addr_id'];
                    $addrAvailForSelProd[] = $pickAddr['sptpa_selprod_id'];
                }
            }
        }

        $addrNotAvailForSelProd = array_diff($selProdIdArr, $addrAvailForSelProd);
        foreach ($addrNotAvailForSelProd as $selProdId) {
            $shopIdArrToFetchPickupAddress[] = $selProdWithShopId[$selProdId];
        }
        
        if (!empty($shopIdArrToFetchPickupAddress)) {
            $shopIdArrToFetchPickupAddress = array_unique($shopIdArrToFetchPickupAddress);
            $shopAddresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopIdArrToFetchPickupAddress, 0, false);
            foreach ($shopAddresses as $shopAddress) {
                $shopAddressGroup[$shopAddress['addr_record_id']][] = $shopAddress;
            }

            foreach ($addrNotAvailForSelProd as $selProductId) {
                $shopId = $selProdWithShopId[$selProductId];
                if (!empty($shopAddressGroup[$shopId])) {
                    foreach($shopAddressGroup[$shopId] as $shopAddr) {
                        $prodPickAddArr[$selProductId][] = $shopAddr['addr_id'];
                    }
                    
                }
            }
        }

        return $prodPickAddArr;
    }
    
    public function getSellPickupOptions($cartProducts)
    {
        $shippedByArr = [];
        $address = new Address();
        $pickupAddress = [];
        $selectedPickUpAddresses = [];
        $pickUpData = $this->getProductPickUpAddresses();
        if (empty($cartProducts)) {
            $cartProducts =  $this->getProducts($this->cart_lang_id);
        }

        foreach ($cartProducts as $product) {
            $selProdId = $product['selprod_id'];
            $pickUpBy = 0;
            $pickUpType = Address::TYPE_ADMIN_PICKUP;

            if ($product['isProductShippedBySeller']) {
                $pickUpBy = $product['shop_id'];
                $pickUpType = Address::TYPE_SHOP_PICKUP;
            }

            if ($product['is_physical_product']) {
                $shippedByArr[$pickUpBy]['products'][$selProdId] = $product;

                if (!in_array($pickUpBy, $pickupAddress)) {
                    $addresses = $address->getData($pickUpType, $pickUpBy);
                    $shippedByArr[$pickUpBy]['pickup_options'] = $addresses;
                }
                $pickupAddress[] = $pickUpBy;

                if (!in_array($pickUpBy, $selectedPickUpAddresses) && !empty($pickUpData[$product['selprod_id']])) {
                    $addressObj = new Address($pickUpData[$selProdId]['time_slot_addr_id']);
                    $pickUpAddr = $addressObj->getData($pickUpType, $pickUpBy);
                    $shippedByArr[$pickUpBy]['pickup_address'] = $pickUpAddr;
                    $shippedByArr[$pickUpBy]['pickup_address']['time_slot_id'] = $pickUpData[$selProdId]['time_slot_id'];
                    $shippedByArr[$pickUpBy]['pickup_address']['time_slot_date'] = $pickUpData[$selProdId]['time_slot_date'];
                    $shippedByArr[$pickUpBy]['pickup_address']['time_slot_from'] = $pickUpData[$selProdId]['time_slot_from_time'];
                    $shippedByArr[$pickUpBy]['pickup_address']['time_slot_to'] = $pickUpData[$selProdId]['time_slot_to_time'];
                }
                $selectedPickUpAddresses[] = $pickUpBy;
            } else {
                $shippedByArr[$pickUpBy]['digital_products'][$selProdId] = $product;
            }
        }
        return $shippedByArr;
    }
	
    public function checkProdWithSamePickupAdd($selprod, $arr)
    {
        $addr = SellerProduct::getSelprodLinkedPickupAdd($selprod);
        $addr = !empty($addr) ? array_keys($addr) : [];

        foreach ($arr as $key => $val) {
            foreach ($val as $k => $v) {
                $selprodId = $v;
                $compareWith = SellerProduct::getSelprodLinkedPickupAdd($selprodId);
                $compareWith = !empty($compareWith) ? array_keys($compareWith) : [];
                if ((count($addr) == count($compareWith)) && (count(array_intersect($addr, $compareWith)) ==  count($compareWith))) {
                    return $key;
                    break;
                }
            }
        }
        return false;
    }

    public function getShippingOptions(int $cartType = applicationConstants::PRODUCT_FOR_SALE)
    {
        $shippedByArr = [];
        $physicalSelProdIdArr = [];
        $digitalSelProdIdArr = [];
        $productInfo = [];
        $cartProducts = $this->getBasketProducts($this->cart_lang_id);

        foreach ($cartProducts as $val) {
            if (0 < $val['is_physical_product'] && isset($this->SYSTEM_ARR['shopping_cart']['checkout_type']) && $val['selprod_fulfillment_type'] != Shipping::FULFILMENT_ALL && $val['selprod_fulfillment_type'] != $this->SYSTEM_ARR['shopping_cart']['checkout_type']) {
                continue;
            }
            if ((isset($val['sellerProdType'])) && $val['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                continue;
            }
            

            $selprodKey = $val['selprod_id'];
            $daysToRentStart = 365; /* MAXIMUM LIMIT FOR DAYS WITH SHIPPING RATES */
            if ($cartType == applicationConstants::PRODUCT_FOR_RENT /* || $cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL */ ) {
                $rentalDatesData = [
                    'rentalStartDate' => $val['rentalStartDate'],
                    'rentalEndDate' => $val['rentalEndDate'],
                ];
                $selprodKey = $val['selprod_id'] . '_' . $val['rentalStartDate'] . '-' . $val['rentalEndDate'];
                $selprodKey = str_replace(' ', ':', $selprodKey);
                $productInfo[$selprodKey] = $val;
                $daysToRentStart = abs((strtotime(date('Y-m-d')) - strtotime($val['rentalStartDate'])) / (60 * 60 * 24));
            }

            $productInfo[$selprodKey] = $val;
            $productInfo[$selprodKey]['productkey'] = $selprodKey;

            if (0 < $val['is_physical_product']) {
                if (isset($physicalSelProdIdArr[$val['selprod_id']]) &&  $physicalSelProdIdArr[$val['selprod_id']] < $daysToRentStart) {
                    $daysToRentStart = $physicalSelProdIdArr[$val['selprod_id']];
                }
                $physicalSelProdIdArr[$val['selprod_id']] = $daysToRentStart.'&'.$selprodKey;
            } else {
                $digitalSelProdIdArr[$val['selprod_id']] = $val['selprod_id'];
            }
            
        }

        if (!empty($physicalSelProdIdArr)) {
            $address = new Address($this->getCartShippingAddress(), $this->cart_lang_id);
            $shippingAddressDetail = $address->getData(Address::TYPE_USER, $this->cart_user_id);

            $shipping = new Shipping($this->cart_lang_id);
            $response = $shipping->calculateCharges($physicalSelProdIdArr, $shippingAddressDetail, $productInfo, $cartType);
            $shippedByArr = $response['data'];
            if (!empty($shippedByArr)) {
                foreach ($shippedByArr as $shippedBy => $rates) {
                    if ($shippedBy > 0) {
                        $shippedByArr[$shippedBy]['shopTotalAmount'] = 0;
                        foreach ($shippedByArr[$shippedBy] as $productsArr) {
                            if (isset($productsArr['products'])) {
                                $shippedByArr[$shippedBy]['shopTotalAmount'] += array_sum(array_column($productsArr['products'], 'shipableAmount'));
                            }
                            
                        }
                    }
                }
            }
        }

        /* Include digital products */
        if (!empty($digitalSelProdIdArr)) {
            foreach ($digitalSelProdIdArr as $selProdId) {
                $shippedByArr[$productInfo[$selProdId]['shop_id']][Shipping::LEVEL_PRODUCT]['digital_products'][$selProdId] = $productInfo[$selProdId];
                $shippedByArr[$productInfo[$selProdId]['shop_id']][Shipping::LEVEL_PRODUCT]['shipping_options'][$selProdId] = [];
                $shippedByArr[$productInfo[$selProdId]['shop_id']][Shipping::LEVEL_PRODUCT]['rates'][$selProdId] = [];
            }
        }
        return $shippedByArr;
    }

    public function getSellersProductItemsPrice($cartProducts)
    {
        $sellerPrice = array();
        if (is_array($cartProducts) && count($cartProducts)) {
            foreach ($cartProducts as $selprod) {
                $shipBy = 0;
                if (!empty($selprod['psbs_user_id'])) {
                    $shipBy = $selprod['psbs_user_id'];
                }

                if (!empty($selprod['selprod_user_id']) && (!array_key_exists($selprod['selprod_user_id'], $sellerPrice) || $shipBy == 0)) {
                    $sellerPrice[$selprod['selprod_user_id']]['totalPrice'] = 0;
                }

                if ($shipBy) {
                    $sellerPrice[$selprod['selprod_user_id']]['totalPrice'] += $selprod['theprice'] * $selprod['quantity'];
                }
            }
        }
        return $sellerPrice;
    }

    public function getSelprodIdByKey($key)
    {
        $keyDecoded = json_decode(base64_decode($key), true);
        if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
            $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
            return $selprod_id;
        }
    }

    public function deleteProductStockHold()
    {
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
        $deleteQuery = "DELETE FROM tbl_product_stock_hold WHERE pshold_added_on < DATE_SUB(NOW(), INTERVAL " . $intervalInMinutes . " MINUTE)";
        FatApp::getDb()->query($deleteQuery);
        return true;
    }

    public function getError()
    {
        return $this->error;
    }

    public function enableCache()
    {
        $this->cartCache = true;
    }

    public function disableCache()
    {
        $this->cartCache = false;
    }

    public function removePickupOnlyProducts()
    {
        $cartProducts = $this->getProducts($this->cart_lang_id, false);
        foreach ($cartProducts as $cartKey => $product) {
            if ($product['fulfillment_type'] != Shipping::FULFILMENT_PICKUP) {
                continue;
            }

            unset($this->SYSTEM_ARR['cart']['products'][$cartKey]);
            $this->updateTempStockHold($product['selprod_id'], 0, 0);
            if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $product['quantity'], AbandonedCart::ACTION_DELETED);
            }
        }
        $this->updateUserCart();
        return true;
    }

    public function removeShippedOnlyProducts()
    {
        $cartProducts = $this->getProducts($this->cart_lang_id, false);
        foreach ($cartProducts as $cartKey => $product) {
            if ($product['fulfillment_type'] != Shipping::FULFILMENT_SHIP) {
                continue;
            }

            unset($this->SYSTEM_ARR['cart']['products'][$cartKey]);
            $this->updateTempStockHold($product['selprod_id'], 0, 0);
            if (is_numeric($this->cart_user_id) && $this->cart_user_id > 0) {
                AbandonedCart::saveAbandonedCart($this->cart_user_id, $product['selprod_id'], $product['quantity'], AbandonedCart::ACTION_DELETED);
            }
        }
        $this->updateUserCart();
        return true;
    }

    public function setCartCheckoutType($type)
    {
        $type = FatUtility::int($type);
        $this->SYSTEM_ARR['shopping_cart']['checkout_type'] = $type;
        $this->updateUserCart();
        return true;
    }

    public function setFulfilmentType(int $type)
    {
        $this->fulfilmentType = $type;
    }

    public function getCartCheckoutType()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['checkout_type']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['checkout_type']) : Shipping::FULFILMENT_SHIP;
    }

    public function unsetCartCheckoutType()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['checkout_type']);
        $this->updateUserCart();
        return true;
    }

    public function checkCartCheckoutType()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['checkout_type']) ? FatUtility::int($this->SYSTEM_ARR['shopping_cart']['checkout_type']) : 0;
    }

    public function setProductPickUpAddresses($arr)
    {
        $this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses'] = $arr;
        $this->updateUserCart();
        return true;
    }

    public function getProductPickUpAddresses()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses']) ? $this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses'] : array();
    }

    public function removeProductPickUpAddresses()
    {
        unset($this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses']);
        $this->updateUserCart();
        return true;
    }

    public function isProductPickUpAddrSet()
    {
        $cartType = $this->getCartType();
        if ($cartType == applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            return true;
        }

        foreach ($this->getProducts($this->cart_lang_id) as $cartKey => $product) {
            if ($product['sellerProdType'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                continue;
            }
            if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
                if (!isset($this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses'][$cartKey]) && $product['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                    return false;
                }
            } else {
                if (!isset($this->SYSTEM_ARR['shopping_cart']['product_pickup_Addresses'][$product['selprod_id']]) && $product['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
                    return false;
                }
            }
        }
        return true;
    }

    public function invalidateCheckoutType()
    {
        $this->valdateCheckoutType = false;
    }

    public function excludeTax()
    {
        $this->includeTax = false;
    }

    public function checkCartType($cartType)
    { //== cart type is sale or rent
        $cartType = FatUtility::int($cartType);
        if (isset($this->SYSTEM_ARR['cart']['cart_type']) && $this->SYSTEM_ARR['cart']['cart_type'] !== $cartType) {
            return false;
        }
        return true;
    }

    public function checkValidExtentRentalCartType()
    {
        if (!isset($this->SYSTEM_ARR['cart']['cart_type']) || $this->SYSTEM_ARR['cart']['cart_type'] == '' || $this->SYSTEM_ARR['cart']['cart_type'] != applicationConstants::PRODUCT_FOR_EXTEND_RENTAL) {
            return true;
        } else {
            return false;
        }
    }

    public function getCartType()
    {
        return (isset($this->SYSTEM_ARR['cart']['cart_type'])) ? $this->SYSTEM_ARR['cart']['cart_type'] : '';
    }

    public function updateRentalTempStockHold(int $selprodId, int $quantity = 0, string $rentalStartDate = '', string $rentalEndDate = '', string $cartKey = '')
    {
        $rentalStartDate = date('Y-m-d H:i:s', strtotime($rentalStartDate));
        $rentalEndDate = date('Y-m-d H:i:s', strtotime($rentalEndDate));
        if (!$selprodId) {
            return;
        }
        $db = FatApp::getDb();
        if ($quantity <= 0) {
            $db->deleteRecords(ProductRental::DB_TBL_RENTAl_STOCK_HOLD, array('smt' => 'rentpshold_selprod_id = ? AND rentpshold_user_id = ? AND rentpshold_rental_start_date = ? AND rentpshold_rental_end_date = ?', 'vals' => array($selprodId, $this->cart_user_id, $rentalStartDate, $rentalEndDate)));
            return;
        }
        $dataArrToSave = array(
            'rentpshold_selprod_id' => $selprodId,
            'rentpshold_user_id' => $this->cart_user_id,
            'rentpshold_selprod_stock' => $quantity,
            'rentpshold_rental_start_date' => $rentalStartDate,
            'rentpshold_rental_end_date' => $rentalEndDate,
            'rentpshold_added_on' => date('Y-m-d H:i:s'),
            'rentpshold_cart_key' => $cartKey
        );
        $dataUpdateOnDuplicate = array_merge($dataArrToSave, array('rentpshold_selprod_stock' => $quantity));
        if (!$db->insertFromArray(ProductRental::DB_TBL_RENTAl_STOCK_HOLD, $dataArrToSave, true, array(), $dataUpdateOnDuplicate)) {
            Message::addErrorMessage($db->getError());
            throw new Exception('');
        }
        /* delete old records[ */
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);
        $deleteQuery = "DELETE FROM tbl_rental_product_stock_hold WHERE rentpshold_added_on < DATE_SUB(NOW(), INTERVAL " . $intervalInMinutes . " MINUTE)";
        $db->query($deleteQuery);
        /* ] */
    }

    public function getSelAddonProductData($selprod_id, $mainProductId, &$quantity, $siteLangId)
    {
        $srch = SellerProduct::getSearchObject($siteLangId);
        $srch->joinTable(User::DB_TBL, 'INNER JOIN', 'selprod_user_id = seller_user.user_id and seller_user.user_is_supplier = ' . applicationConstants::YES . ' AND seller_user.user_deleted = ' . applicationConstants::NO, 'seller_user');
        $srch->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'credential_user_id = seller_user.user_id and credential_active = ' . applicationConstants::ACTIVE . ' and credential_verified = ' . applicationConstants::YES, 'seller_user_cred');
        $srch->addCondition('selprod_id', '=', $selprod_id);
        $srch->addCondition('selprod_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        $srch->addMultipleFields(
            array(
                'sp.*', 'spd.*', 'sp_l.*', 'seller_user.user_name as shop_onwer_name', 'IFNULL(selprod_title, selprod_identifier) as product_name', 'IFNULL(selprod_title, selprod_identifier) as selprod_title',
                'seller_user_cred.credential_username as shop_owner_username',
                'seller_user.user_dial_code', 'seller_user.user_phone as shop_owner_phone', 'seller_user_cred.credential_email as shop_owner_email', 'selprod_identifier as product_identifier'
            )
        );
        $rs = $srch->getResultSet();
        $sellerProductRow = FatApp::getDb()->fetch($rs);

        if (!$sellerProductRow || $sellerProductRow['selprod_stock'] <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Product_not_available_or_out_of_stock_so_removed_from_cart_listing', $siteLangId));
            return false;
        }
        $sellerProductRow['sprodata_rental_price'] = $sellerProductRow['selprod_price'];
        $mainProdData = SellerProduct::getAttributesById($mainProductId, null, true, false, true);
        $productSelectedShippingMethodsArr = $this->getProductShippingMethod();
        /* if ($quantity > $sellerProductRow['selprod_stock']) {
            $quantity = $sellerProductRow['selprod_stock'];
        } */

        $sellerProductRow['theprice'] = $sellerProductRow['selprod_price'];
        $sellerProductRow['actualPrice'] = $sellerProductRow['selprod_price'];
        $sellerProductRow['product_id'] = $mainProdData['selprod_product_id'];
        $sellerProductRow['selprod_product_id'] = $mainProdData['selprod_product_id'];
        $sellerProductRow['selprod_seller_id'] = $mainProdData['selprod_user_id'];
        $sellerProductRow['product_seller_id'] = $mainProdData['selprod_user_id'];
        $sellerProductRow['productFor'] = applicationConstants::PRODUCT_FOR_RENT;
        $sellerProductRow['volume_discount'] = 0;
        $sellerProductRow['volume_discount_percentage'] = 0;
        $sellerProductRow['volume_discount_total'] = 0;
        $sellerProductRow['duration_discount'] = 0;
        $sellerProductRow['duration_discount_percentage'] = 0;
        $sellerProductRow['duration_discount_total'] = 0;
        $rentalPrice = $sellerProductRow['selprod_price'];

        if ($sellerProductRow['selprod_stock'] > 0) {
            $sellerProductRow['in_stock'] = 1;
        }

        $shopDetails = Shop::getAttributesByUserId($sellerProductRow['selprod_user_id'], null, true, $siteLangId);
        $sellerProductRow += $shopDetails;
        $isProductShippedBySeller = Product::isProductShippedBySeller($sellerProductRow['product_id'], $mainProdData['product_seller_id'], $mainProdData['selprod_user_id']);
        $extraData = [];
        if ($this->includeTax == true) {
            $shipToStateId = 0;
            $shipToCountryId = 0;
            $shippingAddressId = $this->getCartShippingAddress();


            $shippingAddressDetail = [];
            if (0 < $shippingAddressId) {
                $address = new Address($shippingAddressId, $this->cart_lang_id);
                $shippingAddressDetail = $address->getData(Address::TYPE_USER, $this->cart_user_id);

                if (isset($shippingAddressDetail['addr_country_id'])) {
                    $shipToCountryId = FatUtility::int($shippingAddressDetail['addr_country_id']);
                }

                if (isset($shippingAddressDetail['addr_state_id'])) {
                    $shipToStateId = FatUtility::int($shippingAddressDetail['addr_state_id']);
                }
            }

            $shippingCost = 0;

            /* if (!empty($productSelectedShippingMethodsArr['product']) && isset($productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']])) {
              $shippingDurationRow = $productSelectedShippingMethodsArr['product'][$sellerProductRow['selprod_id']];
              $shippingCost = ROUND(($shippingDurationRow['mshipapi_cost']), 2);
              } */
            $extraData = array(
                'billingAddress' => isset($sellerProductRow['billing_address']) ? $sellerProductRow['billing_address'] : '',
                'shippingAddress' => $shippingAddressDetail,
                'shippedBySeller' => $isProductShippedBySeller,
                'shippingCost' => $shippingCost,
                'buyerId' => $this->cart_user_id
            );
        }

        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0) && $this->includeTax == true) {
            $tax = new Tax();
            $taxCategoryRow = $tax->getTaxRates($sellerProductRow['selprod_id'], $sellerProductRow['selprod_user_id'], $siteLangId,  applicationConstants::PRODUCT_FOR_RENT, SellerProduct::PRODUCT_TYPE_ADDON);

            if (array_key_exists('taxrule_rate', $taxCategoryRow) && 0 == Tax::getActivatedServiceId()) {
                $sellerProductRow['theprice'] = round($sellerProductRow['theprice'] / (1 + ($taxCategoryRow['taxrule_rate'] / 100)), 2);
            } else {
                $taxObj = new Tax();
                $taxData = $taxObj->calculateTaxRates($sellerProductRow['selprod_id'], $sellerProductRow['theprice'], $sellerProductRow['selprod_user_id'], $siteLangId, $quantity, $extraData, $this->cartCache, SellerProduct::PRODUCT_TYPE_ADDON, applicationConstants::PRODUCT_FOR_RENT);

                if (isset($taxData['rate'])) {
                    $ruleRate = ($taxData['tax'] * 100) / ($sellerProductRow['theprice'] * $quantity);
                    $sellerProductRow['theprice'] = round((($sellerProductRow['theprice'] * $quantity) / (1 + ($ruleRate / 100))) / $quantity, 2);
                }
            }
        }

        /* update/fetch/apply theprice, according to volume discount module[ */
        $sellerProductRow['volume_discount'] = 0;
        $sellerProductRow['volume_discount_percentage'] = 0;
        $sellerProductRow['volume_discount_total'] = 0;
        /* ] */
        /* [ get duration discount for rental products */
        $sellerProductRow['duration_discount'] = 0;
        $sellerProductRow['duration_discount_percentage'] = 0;
        $sellerProductRow['duration_discount_total'] = 0;
        /* ] */


        /* set variable of shipping cost of the product, if shipping already selected[ */
        $sellerProductRow['shipping_cost'] = 0;
        $sellerProductRow['opshipping_rate_id'] = 0;
        if (!empty($productSelectedShippingMethodsArr) && isset($productSelectedShippingMethodsArr[$selprod_id])) {
            $shippingDurationRow = $productSelectedShippingMethodsArr[$selprod_id];
            $sellerProductRow['opshipping_rate_id'] = $shippingDurationRow['mshipapi_id'];
            $sellerProductRow['shipping_cost'] = ROUND(($shippingDurationRow['mshipapi_cost'] * $quantity), 2);
        }
        /* ] */

        /* calculation of commission and tax against each product[ */
        $commission = 0;
        $tax = 0;
        $maxConfiguredCommissionVal = FatApp::getConfig("CONF_MAX_COMMISSION");

        $commissionPercentage = SellerProduct::getProductCommission($mainProductId, applicationConstants::PRODUCT_FOR_RENT);
        $commission = MIN(ROUND($sellerProductRow['theprice'] * $commissionPercentage / 100, 2), $maxConfiguredCommissionVal);
        $sellerProductRow['commission_percentage'] = $commissionPercentage;
        $sellerProductRow['commission'] = ROUND($commission * $quantity, 2);

        $totalPrice = $sellerProductRow['theprice'] * $quantity;
        $taxableProdPrice = $sellerProductRow['theprice'] - $sellerProductRow['volume_discount'];
        $discountedPrice = 0;
        if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            if (!empty($this->discounts) && isset($this->discounts['discountedSelProdIds'][$sellerProductRow['selprod_id']])) {
                $discountedPrice = $this->discounts['discountedSelProdIds'][$sellerProductRow['selprod_id']];
                $taxableProdPrice = $taxableProdPrice - $discountedPrice;
            }
        }
        $taxObj = new Tax();
        $taxData = $taxObj->calculateTaxRates($sellerProductRow['selprod_id'], $taxableProdPrice, $sellerProductRow['selprod_user_id'], $siteLangId, $quantity, $extraData, false, SellerProduct::PRODUCT_TYPE_ADDON);
        /* echo '<pre>'; print_r($taxData); echo '</pre>'; exit; */
        // CommonHelper::printArray($taxData);
        if (false == $taxData['status'] && $taxData['msg'] != '') {
            //$this->error = $taxData['msg'];
        }

        $tax = $taxData['tax'];
        $roundingOff = 0;
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $originalTotalPrice = ($sellerProductRow['actualPrice'] * $quantity);
            $thePriceincludingTax = $taxData['tax'] + $totalPrice;
            if (0 < $sellerProductRow['volume_discount_total'] && array_key_exists('rate', $taxData)) {
                $thePriceincludingTax = $thePriceincludingTax + (($sellerProductRow['volume_discount_total'] * $taxData['rate']) / 100);
            }

            if (0 < $discountedPrice) {
                $thePriceincludingTax = $thePriceincludingTax + (($discountedPrice * $taxData['rate']) / 100);
            }

            if ($originalTotalPrice != $thePriceincludingTax && 0 < $taxableProdPrice && 0 < $taxData['rate']) {
                $roundingOff = round($originalTotalPrice - $thePriceincludingTax, 2);
            }
        } else {
            if (array_key_exists('optionsSum', $taxData) && $taxData['tax'] != $taxData['optionsSum']) {
                $roundingOff = round($taxData['tax'] - $taxData['optionsSum'], 2);
            }
        }
        $sellerProductRow['rounding_off'] = $roundingOff;

        $sellerProductRow['tax'] = $tax;
        $sellerProductRow['optionsTaxSum'] = isset($taxData['optionsSum']) ? $taxData['optionsSum'] : 0;
        $sellerProductRow['taxCode'] = $taxData['taxCode'];
        /* ] */

        $sellerProductRow['total'] = $totalPrice;
        $sellerProductRow['netTotal'] = $sellerProductRow['total'] + $sellerProductRow['shipping_cost'] + $roundingOff;

        $sellerProductRow['is_digital_product'] = 0; // Need to Update
        $sellerProductRow['is_physical_product'] = 1; // Need to Update
        $sellerProductRow['product_type'] = 1; // Need to Update
        if ($siteLangId) {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, true, $siteLangId);
        } else {
            $sellerProductRow['options'] = SellerProduct::getSellerProductOptions($selprod_id, false);
        }

        $sellerProductRow['isProductShippedBySeller'] = $isProductShippedBySeller;
        $sellerProductRow['product_cod_enabled'] = $mainProdData['product_cod_enabled'];

        /* $fulfillmentType = $sellerProductRow['selprod_fulfillment_type']; */
        $fulfillmentType = -1;
        if (true == $isProductShippedBySeller) {
            $fulfillmentType = $mainProdData['selprod_fulfillment_type'];
            $sellerProductRow['selprod_fulfillment_type'] = $fulfillmentType;
        } else {
            $fulfillmentType = Product::getAttributesById($mainProdData['selprod_product_id'], 'product_fulfillment_type');
            $sellerProductRow['selprod_fulfillment_type'] = $fulfillmentType;
        }

        $sellerProductRow['fulfillment_type'] = $fulfillmentType;
        return $sellerProductRow;
    }

    public function getTaxAddress($productKey, $step = '')
    {
        $productData = $this->products[$productKey];
        $sellerId = $productData['selprod_user_id'];
        $isProductShippedBySeller = $productData['isProductShippedBySeller'];

        $pickupAddressId = (isset($productData['pickup_address_id'])) ? $productData['pickup_address_id'] : 0;
        /* $addressType = FatApp::getConfig("CONF_TAX_APPLY_ADDRESS_TYPE", FatUtility::VAR_INT, 0); */
        $addressType = Tax::TAX_ON_SHIPPING_TO_ADDRESS;

        $this->products[$productKey]['tax_applied_address_type'] = $addressType;
        if ($addressType == Tax::TAX_ON_SHIPPING_TO_ADDRESS) {
            $address['addr_state_id'] = (isset($productData['shipping_address']['addr_state_id'])) ? $productData['shipping_address']['addr_state_id'] : 0;
            $address['addr_country_id'] = (isset($productData['shipping_address']['addr_country_id'])) ? $productData['shipping_address']['addr_country_id'] : 0;
        } elseif ($addressType == Tax::TAX_ON_SHIPPING_FROM_ADDRESS) {
            $pickupAddresses = $this->getProductPickUpAddresses();
            $pickupAddress = isset($pickupAddresses[$productKey]) ? $pickupAddresses[$productKey] : [];
            //echo '<pre>'; print_r($pickupAddress); echo '</pre>'; exit;
            if (!empty($pickupAddress)) {
                $addressObj = new Address($pickupAddress['time_slot_addr_id'], $this->cart_lang_id);
                $shopId = 0;
                $pickupAddressType = Address::TYPE_ADMIN_PICKUP;

                if ($isProductShippedBySeller) {
                    $pickupAddressType = Address::TYPE_SHOP_PICKUP;
                    $shopId = $productData['shop_id'];
                }

                $shippingAddressDetail = $addressObj->getData($pickupAddressType, $shopId);
                $address['addr_state_id'] = $shippingAddressDetail['addr_state_id'];
                $address['addr_country_id'] = $shippingAddressDetail['addr_country_id'];
            } else {
                if ($isProductShippedBySeller) {
                    $shopId = $productData['shop_id'];
                    $shopData = Shop::getAttributesById($shopId, array('shop_state_id', 'shop_country_id', 'shop_city_id'));
                    $address['addr_state_id'] = $shopData['shop_state_id'];
                    $address['addr_country_id'] = $shopData['shop_country_id'];
                } else {
                    $address['addr_state_id'] = FatApp::getConfig("CONF_STATE", FatUtility::VAR_INT, 0);
                    $address['addr_country_id'] = FatApp::getConfig("CONF_COUNTRY", FatUtility::VAR_INT, 0);
                }
            }
        }
        return $address;
    }

    public function setCartVerificationData($data)
    {
        $this->SYSTEM_ARR['shopping_cart']['verification_data'] = $data;
        $this->updateUserCart();
        return true;
    }

    public function getCartSelprodIdsCount()
    {
        if (!isset($this->SYSTEM_ARR['cart']['products'])) {
            return [];
        }
        $selprodIdsCount = [];
        foreach ($this->SYSTEM_ARR['cart']['products'] as $key => $product) {
            $productFor = $product['productFor'];
            $selprod_id = 0;
            $keyDecoded = json_decode(base64_decode($key), true);
            if (strpos($keyDecoded, static::CART_KEY_PREFIX_PRODUCT) !== false) {
                if ($productFor == applicationConstants::PRODUCT_FOR_RENT) {
                    $rentalStartDate = $product['rental_start_date'];
                    $rentalEndDate = $product['rental_end_date'];
                    $keyDecoded = str_replace($rentalStartDate . $rentalEndDate, '', $keyDecoded);
                }
                $selprod_id = FatUtility::int(str_replace(static::CART_KEY_PREFIX_PRODUCT, '', $keyDecoded));
                $selprodIdsCount[$selprod_id] = (isset($selprodIdsCount[$selprod_id])) ? $selprodIdsCount[$selprod_id] + 1 : 1;
            }
        }
        return $selprodIdsCount;
    }

    public function getVerificationFldData()
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['verification_data']) ? $this->SYSTEM_ARR['shopping_cart']['verification_data'] : array();
    }

    public function setSetDigintalSign()
    {
        $this->SYSTEM_ARR['shopping_cart']['digital_sign'] = true;
        $this->updateUserCart();
        return true;
    }

    public function getSetDigintalSign()
    {
        // return $this->SYSTEM_ARR['shopping_cart']['digital_sign'];
        return isset($this->SYSTEM_ARR['shopping_cart']['digital_sign']) ? $this->SYSTEM_ARR['shopping_cart']['digital_sign'] : false;
    }
    public function getOrderNumericKey() : int
    {
        return isset($this->SYSTEM_ARR['shopping_cart']['order_numeric_id']) ? $this->SYSTEM_ARR['shopping_cart']['order_numeric_id'] : 0;
    }
    

    public function getCartProductsDataAccToAddons($siteLangId = 0, $addAddonsList = false)
    {
        $siteLangId = ($siteLangId > 0) ? $siteLangId : $this->cart_lang_id;
        $cartProducts = $this->getProducts($siteLangId);
        if (1 > FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0)) {
            return $cartProducts;
        }
        
        if (empty($cartProducts)) {
            return [];
        }
        $attachedAddonsList = [];
        if ($this->getCartType() == applicationConstants::PRODUCT_FOR_RENT && $addAddonsList) {
            $selProdIds = array_unique(array_column($cartProducts, 'selprod_id'));
            $selprodObj = new SellerProduct();
            $attachedAddonsList = $selprodObj->getAddonProducts($siteLangId, false, $selProdIds);
        }
        
        $formattedData = [];
        foreach ($cartProducts as $key => $val) {
            if ($val['sellerProdType'] == SellerProduct::PRODUCT_TYPE_PRODUCT) {
                if ($addAddonsList) {
                    $val['attachedAddonsList'] = (isset($attachedAddonsList[$val['selprod_id']])) ? $attachedAddonsList[$val['selprod_id']] : [];
                }
                if (isset($formattedData[$key])) {
                   $tempAddonData = (isset($formattedData[$key]['addonsData'])) ? $formattedData[$key]['addonsData'] : [];
                   $formattedData[$key] = $val;
                   $formattedData[$key]['addonsData'] = $tempAddonData;
                } else {
                    $formattedData[$key] = $val;
                }
            } else {
               $mainProductKey = static::CART_KEY_PREFIX_PRODUCT .  $val['mainProductId'] . $val['rentalStartDate'] . $val['rentalEndDate'];
               $mainProductKey = base64_encode(json_encode($mainProductKey));
               $formattedData[$mainProductKey]['addonsData'][$key] = $val;
            }
        }
        return $formattedData;
    }
    
    public function getSelPickAddrDetails()
    {
        $selectedPickupAddresses = $this->getProductPickUpAddresses();
        $addressDetailsArr = [];
        if(!empty($selectedPickupAddresses)) {
            $addressIdArr = array_unique(array_column($selectedPickupAddresses, 'time_slot_addr_id'));
            
            $address = new Address();
            $addressDetails = $address->detail($addressIdArr);
            
            if (!empty($addressDetails)) {
                foreach($selectedPickupAddresses as $key=>$selectedPickupAddress) {
                    if (!empty($addressDetails[$selectedPickupAddress['time_slot_addr_id']])) {
                        $addressDetailsArr[$key] = $addressDetails[$selectedPickupAddress['time_slot_addr_id']];
                    }
                }
            }
        }
        return $addressDetailsArr;
    }
}
