<?php

/* This Class is used for to list sellable products, sellable means,
  => shop must be active
  => Shop Display Status must be on
  => Seller/User must be active
  => Seller/User email must be verified.
  => Seller/User must be supplier = 1
  => Product Must be active
  => Product must be approved
  => Associated Brand must be active and not deleted.
  => Product Category must be active and category must not be deleted.
 */

class ProductSearch extends SearchBase
{

    private $langId;
    private $sellerProductsJoined = false;
    private $sellerUserJoined = false;
    private $shopsJoined = false;
    private $commonLangId;
    private $sellerSubscriptionOrderJoined = false;
    private $joinProductShippedBy = false;
    private $geoAddress = [];
    private $locationBasedInnerJoin = true;

    public function __construct($langId = 0, $otherTbl = null, $prodIdColumName = null, $isProductActive = true, $isProductApproved = true, $isProductDeleted = true)
    {
        $this->langId = FatUtility::int($langId);
        $this->commonLangId = CommonHelper::getLangId();

        if ($otherTbl == null) {
            parent::__construct(Product::DB_TBL, 'p');
        } else {
            /* Same productsearch class used to fetch products under any batch/group, do not call setDefinedCriteria, call setBatchProductsCriteria().[ */
            parent::__construct($otherTbl, 'temp');
            $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'temp.' . ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
            if ($this->langId > 0) {
                $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND selprodlang_lang_id = ' . $this->langId, 'sp_l');
            }
            $this->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
            /* ] */
            //$this->addOrder('ptg_is_main_product', 'DESC');
            /* $this->addCondition('p.product_deleted','=',applicationConstants::NO); */
        }

        if ($langId > 0) {
            $this->joinProductsLang($this->langId);
        }

        if ($isProductActive) {
            $this->addCondition('product_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        if ($isProductDeleted) {
            $this->addCondition('product_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        }

        if ($isProductApproved) {
            $this->addCondition('product_approved', '=', 'mysql_func_' .Product::APPROVED, 'AND', true);
        }
    }

    public function setGeoAddress($address = [])
    {
        $this->geoAddress = Address::getYkGeoData($address);
    }

    public function joinProductsLang($langId)
    {
        $this->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'productlang_product_id = p.product_id AND productlang_lang_id = ' . $langId, 'tp_l');
    }

    public function setDefaultLangForJoins($langId)
    {
        $this->langId = FatUtility::int($langId);
    }

    public function unsetDefaultLangForJoins()
    {
        $this->langId = 0;
    }

    public function setDefinedCriteria($joinPrice = 0, $bySeller = 0, $criteria = array(), $checkAvailableFrom = true, $useTempTable = false)
    {
        $joinPrice = FatUtility::int($joinPrice);
        if (0 < $joinPrice) {
            $this->joinForPrice('', $criteria, $checkAvailableFrom, $useTempTable);
        } else {
            $this->joinSellerProducts($bySeller, '', $criteria, $checkAvailableFrom);
        }
        $this->joinSellers();
        $this->joinShops();
        $this->joinShopCountry();
        $this->joinShopState();
        $this->joinBrands();
        $this->joinShippingPackages();
    }

    public function setBatchProductsCriteria($splPriceForDate = '')
    {
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $splPriceForDate) {
            $splPriceForDate = $now;
        }
        $this->joinProductToCategory();
        $this->joinSellers();
        $this->joinShops();
        $this->joinShopCountry();
        $this->joinShopState();
        $this->joinBrands();
        $this->doNotCalculateRecords();
        $this->doNotLimitRecords();
        /* groupby added, beacouse if same product is linked with multiple categories, then showing in repeat for each category[ */
        $this->addGroupBy('selprod_id');
        /* ] */
        $this->joinTable(
                SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );
    }

    /* Only used for product listing page for Home page, categories or search page */
    public function joinSellerProductWithData($criteria = array()) 
    {
        if ($this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('ERR_SellerProducts_can_be_joined_only_once.', $this->commonLangId), E_USER_ERROR);
        }
        $this->sellerProductsJoined = true;
        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id and selprod_deleted = ' . applicationConstants::NO, 'sp');
        if (isset($criteria['optionvalue']) && $criteria['optionvalue'] != '') {
            $this->addOptionCondition($criteria['optionvalue']);
        }

        if ($this->langId) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND sp_l.selprodlang_lang_id = ' . $this->langId, 'sp_l');
            /* $fields2 = array('selprod_title', 'selprod_warranty', 'selprod_return_policy', 'sp_l.selprod_comments as selprodComments'); */
        }

        $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'sp.selprod_id = spd.sprodata_selprod_id ', 'spd');
    }
    
    
    public function joinForPrice2($splPriceForDate = '', $criteria = array(), $checkAvailableFrom = true, $useTempTable = true, $langJoin = true) 
	{
        $useTempTable = false;
		/* if ($this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('ERR_SellerProducts_can_be_joined_only_once.', $this->commonLangId), E_USER_ERROR);
        } */
        /* $this->sellerProductsJoined = true; */
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $splPriceForDate) {
            $splPriceForDate = $now;
        }

        /* $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'msplpric.splprice_selprod_id = msellprod.selprod_id AND \'' . $splPriceForDate . '\' BETWEEN msplpric.splprice_start_date AND msplpric.splprice_end_date', 'msplpric'); */
        
        /* [ NEED TO CHECK */
        $this->joinBasedOnPriceCondition($splPriceForDate, $criteria, $checkAvailableFrom, $langJoin);
        /* ] */
    }
    
    public function joinForPrice($splPriceForDate = '', $criteria = array(), $checkAvailableFrom = true, $useTempTable = true)
    {
        if ($this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('ERR_SellerProducts_can_be_joined_only_once.', $this->commonLangId), E_USER_ERROR);
        }
        $this->sellerProductsJoined = true;
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $splPriceForDate) {
            $splPriceForDate = $now;
        }

        $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'msellprod.selprod_product_id = p.product_id and selprod_deleted = ' . applicationConstants::NO, 'msellprod');
        if (isset($criteria['optionvalue']) && $criteria['optionvalue'] != '') {
            $this->addOptionCondition($criteria['optionvalue']);
        }

        if ($this->langId) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'msellprod.selprod_id = sprods_l.selprodlang_selprod_id AND sprods_l.selprodlang_lang_id = ' . $this->langId, 'sprods_l');
            $fields2 = array('selprod_title', 'selprod_warranty', 'selprod_return_policy', 'sprods_l.selprod_comments as selprodComments');
        }

        $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'msellprod.selprod_id = spd.sprodata_selprod_id ', 'spd');
        
        /*$cnd = $this->addCondition('spd.sprodata_rental_active', '=', applicationConstants::ACTIVE);
        if (ALLOW_SALE) {
            $cnd->attachCondition('msellprod.selprod_active', '=', applicationConstants::ACTIVE, 'OR');
        }*/

        if (isset($criteria['optionvalue']) && $criteria['optionvalue'] != '') {
            $this->addOptionCondition($criteria['optionvalue']);
            $useTempTable = false;
        }

        if (!empty($criteria['keyword']) || !empty($criteria['shop']) || !empty($criteria['shop_id'])) {
            $useTempTable = false;
        }

        if (array_key_exists('price-min-range', $criteria) || array_key_exists('min_price_range', $criteria)) {
            $useTempTable = false;
        }
        /* Need to cross check results in case of temp table */
        if ($useTempTable === true) {
            $productType = applicationConstants::PRODUCT_FOR_RENT;
            if (!empty($criteria['producttype']) && in_array(Product::PRODUCT_FOR_SALE, $criteria['producttype'])) {
                $productType = applicationConstants::PRODUCT_FOR_SALE;
            }

            $srch = new SearchBase(Product::DB_PRODUCT_MIN_PRICE);
            $srch->doNotLimitRecords();
            $srch->doNotCalculateRecords();
            $srch->addCondition('pmp_price_type', '=', 'mysql_func_'. $productType, 'AND', true);
            $srch->addMultipleFields(array('pmp_product_id', 'pmp_selprod_id', 'pmp_min_price as theprice', 'pmp_max_price as maxprice', 'pmp_splprice_id', 'if(pmp_splprice_id,1,0) as special_price_found', 'pmp_price_type'));
            $tmpQry = $srch->getQuery();
            $this->joinTable('(' . $tmpQry . ')', 'INNER JOIN', 'pricetbl.pmp_product_id = msellprod.selprod_product_id and msellprod.selprod_id = pricetbl.pmp_selprod_id AND pricetbl.pmp_price_type = ' . $productType, 'pricetbl');
            $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'msplpric.splprice_selprod_id = pricetbl.pmp_selprod_id and pricetbl.pmp_splprice_id = msplpric.splprice_id', 'msplpric');
            // $this->addFld('pricetbl.maxprice');
        } else {
            $this->joinBasedOnPriceCondition($splPriceForDate, $criteria, $checkAvailableFrom);
        }
        // $this->joinBasedOnPriceCondition($splPriceForDate, $criteria, $checkAvailableFrom);
        $productType = (!empty($criteria['producttype'])) ? $criteria['producttype'] : array();
        $this->addProductTypeCondition($productType);
    }

    public function joinBasedOnPriceConditionInnerQry($splPriceForDate = '', $criteria = array(), $checkAvailableFrom = true, $mainQuery = true)
    {
        $productType = applicationConstants::PRODUCT_FOR_RENT;
        if (!empty($criteria['producttype']) && in_array(Product::PRODUCT_FOR_SALE, $criteria['producttype'])) {
            $productType = applicationConstants::PRODUCT_FOR_SALE;
        }
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        if ('' == $splPriceForDate) {
            $splPriceForDate = $now;
        }
        
        $this->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'tsp.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN tsp.splprice_start_date AND tsp.splprice_end_date AND splprice_type = '. $productType, 'tsp'
        );
        
        if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
            $this->addCondition('sp.selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            if ($checkAvailableFrom) {
                $this->addCondition('sp.selprod_available_from', '<=', $splPriceForDate);
            }
        } else {
            $this->addCondition('spd.sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }
        
        $this->addCondition('selprod_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $priceKey = 'spd.sprodata_rental_price';
        if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
            $priceKey = 'sp.selprod_price';
        }
        
        if ($mainQuery) {
            $this->addFld('MIN(COALESCE(tsp.splprice_price, '. $priceKey .')) AS theprice');
        } else {
            $this->addFld('COALESCE(tsp.splprice_price, '. $priceKey .') AS theprice');
            $this->addMultipleFields(array('sp.selprod_product_id', '(CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END) AS special_price_found'));
        }
        
        if (!empty($criteria['keyword'])) {
            $this->addFld('if(sp_l.selprod_title LIKE ' . FatApp::getDb()->quoteVariable('%' . $criteria['keyword'] . '%') . ',  1, 0 ) as keywordFound');
        } else {
            $this->addFld('0 as keywordFound');
        }
        
        if (isset($criteria['condition']) && !empty($criteria['condition'])) {
            $this->addConditionCondition($criteria['condition'], $this);
        }

        if (isset($criteria['out_of_stock']) && !empty($criteria['out_of_stock'])) {
            if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                $this->addCondition('sp.selprod_stock', '>', 'mysql_func_0', 'AND', true);
            } else {
                $this->addCondition('sp.sprodata_rental_stock', '>', 'mysql_func_0', 'AND', true);
            }
        }
        
        $startDate = '';
        $endDate = '';
        $daysToRentStart = 365;
        if (array_key_exists('rentalstart', $criteria)) {
            $startDate = $criteria['rentalstart'];
            $daysToRentStart = abs((strtotime(date('Y-m-d')) - strtotime($startDate)) / (60 * 60 * 24));
        }
        if (array_key_exists('rentalend', $criteria)) {
            $endDate = $criteria['rentalend'];
        }

        if ($startDate != '' && $endDate != '' && $productType == applicationConstants::PRODUCT_FOR_RENT) {
            $dateSrch = $this->addDateCondition($startDate, $endDate);
            $dateSrchQry = $dateSrch->getQuery();
            $this->addFld(array('IFNULL(pbs.pbs_quantity, 0) as bookedQty', 'pbs.pbs_date', '(sprodata_rental_stock - pbs.pbs_quantity) as availableQty'));
            /* $this->addDirectCondition("(IFNULL(pbs.pbs_quantity, 0) = 0 OR (sprodata_rental_stock - IFNULL(pbs.pbs_quantity, 0)) > 0)"); */
            /* $this->joinTable('(' . $dateSrchQry . ')', 'LEFT OUTER JOIN', 'pbs.pbs_selprod_id = selprod_id', 'pbs'); */
			$this->joinTable('(' . $dateSrchQry . ')', 'LEFT OUTER JOIN', 'pbs.pbs_selprod_id = selprod_id AND (IFNULL(pbs.pbs_quantity, 0) = 0 OR (sprodata_rental_stock - IFNULL(pbs.pbs_quantity, 0)) > 0)', 'pbs');
        }
        

        $shopId = 0;
        if (array_key_exists('shop_id', $criteria) && $criteria['shop_id'] > 0) {
            $shopId = FatUtility::int($criteria['shop_id']);
        }
        
        /* if (1 > $shopId) { */
            if (array_key_exists('selectedFulfillmentType', $criteria) && $criteria['selectedFulfillmentType'] == Shipping::FULFILMENT_PICKUP) {
                $latitude = '';
                $longitude = '';
                if (array_key_exists('ykGeoLat', $this->geoAddress) && FatUtility::float($this->geoAddress['ykGeoLat']) != 0) {
                    $latitude = $this->geoAddress['ykGeoLat'];
                }
                if (array_key_exists('ykGeoLng', $this->geoAddress) && FatUtility::float($this->geoAddress['ykGeoLng']) != 0) {
                    $longitude = $this->geoAddress['ykGeoLng'];
                }
                
                if ($latitude != '' && $longitude != '') {
                    $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS, 'LEFT JOIN', 'sp.selprod_id = sptpa_selprod_id', 'linkedAddr');
                    $this->joinTable(Address::DB_TBL, 'LEFT OUTER JOIN', '(sptpa_addr_id = addr_id OR (addr_record_id = shop_id AND sptpa_addr_id IS NULL)) AND addr_type = '. Address::TYPE_SHOP_PICKUP, 'pickupaddress');
                    
                    if ($mainQuery) {
                        $this->addFld('IFNULL(MIN((((acos(sin(('.$latitude.'*pi()/180)) * sin((pickupaddress.`addr_lat`*pi()/180))+cos(('.$latitude.'*pi()/180)) * cos((pickupaddress.`addr_lat`*pi()/180)) * cos((('.$longitude.'- pickupaddress.`addr_lng`) *pi()/180))))*180/pi())*60*1.1515*1.609344)), 12800) AS distance');
                    } else {
                        $this->addFld(['IFNULL((((acos(sin(('.$latitude.'*pi()/180)) * sin((pickupaddress.`addr_lat`*pi()/180))+cos(('.$latitude.'*pi()/180)) * cos((pickupaddress.`addr_lat`*pi()/180)) * cos((('.$longitude.'- pickupaddress.`addr_lng`) *pi()/180))))*180/pi())*60*1.1515*1.609344), 12800) AS distance', 'addr_id']);
                    }
                } else {
                    $this->addFld('0 as distance');
                }
                if (!isset($criteria['sortBy'])) {
                    $this->addOrder('distance', 'ASC');
                    $this->addOrder('theprice', 'ASC');
                }
                if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                    $this->addFld('IF(selprod_fulfillment_type = '. Shipping::FULFILMENT_SHIP .', 0, 1) as availableForPickup');
                } else {
                    $this->addFld('IF(sprodata_fullfillment_type = '. Shipping::FULFILMENT_SHIP .', 0, 1) as availableForPickup');
                }
            } else {
                $countryId = 0;
                $stateId = 0;
                if (array_key_exists('ykGeoCountryId', $this->geoAddress) && $this->geoAddress['ykGeoCountryId'] > 0) {
                    $countryId = $this->geoAddress['ykGeoCountryId'];
                }
    
                if (array_key_exists('ykGeoStateId', $this->geoAddress) && $this->geoAddress['ykGeoStateId'] > 0) {
                    $stateId = $this->geoAddress['ykGeoStateId'];
                }
                
                if ($countryId > 0 /* && $stateId > 0 */ ) {
                    if ($productType == applicationConstants::PRODUCT_FOR_RENT) {
                        $this->joinTable(ShippingProfileProduct::DB_TBL, 'LEFT OUTER JOIN', 'spprot.shippro_product_id = p.product_id and spprot.shippro_user_id = sp.selprod_user_id', 'spprot');
                        $locCondition = '((sloc.shiploc_country_id = "-1" OR (sloc.shiploc_country_id = ' . $countryId . ' AND (sloc.shiploc_state_id = "-1" OR sloc.shiploc_state_id = ' . $stateId . '))) AND sprodata_fullfillment_type != '. Shipping::FULFILMENT_PICKUP .')';
                    } else {
                        $this->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = p.product_id and psbs.psbs_user_id = sp.selprod_user_id', 'psbs');
                        
                        $joinCondition = 'if(product_seller_id = 0, (if(psbs.psbs_user_id > 0, spprot.shippro_user_id = psbs.psbs_user_id, spprot.shippro_user_id = 0)), (spprot.shippro_user_id = selprod_user_id))';
                        
                        $this->joinTable(ShippingProfileProduct::DB_TBL, 'LEFT OUTER JOIN', 'spprot.shippro_product_id = selprod_product_id and ' . $joinCondition, 'spprot');
                        
                        $locCondition = '((sloc.shiploc_country_id = "-1" OR (sloc.shiploc_country_id = ' . $countryId . ' AND (sloc.shiploc_state_id = "-1" OR sloc.shiploc_state_id = ' . $stateId . '))) AND (IF(psbs.psbs_user_id > 0, selprod_fulfillment_type !='.  Shipping::FULFILMENT_PICKUP .', product_fulfillment_type != '. Shipping::FULFILMENT_PICKUP .')))';
                    }
    
                    $this->joinTable(ShippingProfileZone::DB_TBL, 'LEFT OUTER JOIN', 'shippz.shipprozone_shipprofile_id = spprot.shippro_shipprofile_id', 'shippz');
                    
                    if (array_key_exists('rentalstart', $criteria) && $productType == applicationConstants::PRODUCT_FOR_RENT) {
                        $this->joinTable(ShippingRate::DB_TBL, 'LEFT OUTER JOIN', 'shipr.shiprate_shipprozone_id = shippz.shipprozone_id', 'shipr');
                        $locCondition = '((sloc.shiploc_country_id = "-1" OR (sloc.shiploc_country_id = ' . $countryId . ' AND (sloc.shiploc_state_id = "-1" OR sloc.shiploc_state_id = ' . $stateId . '))) AND sprodata_fullfillment_type != '. Shipping::FULFILMENT_PICKUP .' AND shiprate_min_duration <= '. $daysToRentStart .')';
                    }
                    
                    $this->joinTable(ShippingZone::DB_SHIP_LOC_TBL, 'LEFT JOIN', 'shippz.shipprozone_shipzone_id = sloc.shiploc_shipzone_id AND '. $locCondition, 'sloc');
                    
                    if ($mainQuery) {
                        $this->addFld(['MAX(IF('. $locCondition .', 1, 0)) as availableForShip']);
                    } else {
                        $this->addFld(['IF('. $locCondition .', 1, 0) as availableForShip']);
                    }
                    if (!isset($criteria['sortBy'])) {
                        $this->addOrder('availableForShip', 'DESC');
                    }
                }
            }
        /* } */
        if ($mainQuery){
            if (isset($criteria['collection_product_id']) && $criteria['collection_product_id'] > 0) {
                $this->addGroupBy('sp.selprod_id');
            } else {
                $this->addGroupBy('product_id');
                /* $this->addGroupBy('keywordFound'); */
            }
        }
        //echo $this->getQuery(); die();
        
        
    }

    public function joinBasedOnPriceCondition($splPriceForDate = '', $criteria = array(), $checkAvailableFrom = true, $langJoin = true)
    {
        $productType = applicationConstants::PRODUCT_FOR_RENT;
        if (!empty($criteria['producttype']) && in_array(Product::PRODUCT_FOR_SALE, $criteria['producttype'])) {
            $productType = applicationConstants::PRODUCT_FOR_SALE;
        }

        $this->joinBasedOnPriceConditionInnerQry($splPriceForDate, $criteria, $checkAvailableFrom, $langJoin);
    }

    public function joinSellerProducts($bySeller = 0, $splPriceForDate = '', $criteria = array(), $checkAvailableFrom = true, $isProductActive = true)
    {
        if ($this->sellerProductsJoined) {
            trigger_error(Labels::getLabel('ERR_SellerProducts_can_be_joined_only_once.', $this->commonLangId), E_USER_ERROR);
        }
        $this->sellerProductsJoined = true;
        $joinSpecialPrice = true;
        if (array_key_exists('doNotJoinSpecialPrice', $criteria) && $criteria['doNotJoinSpecialPrice'] == true) {
            $joinSpecialPrice = false;
        }

        if ($joinSpecialPrice == true) {
            $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
            if ('' == $splPriceForDate) {
                $splPriceForDate = $now;
            }
            $bySeller = FatUtility::int($bySeller);
            $srch = new SearchBase(SellerProduct::DB_TBL, 'sprods');

            $fields2 = array();
            if ($this->langId) {
                $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sprods.selprod_id = sprods_l.selprodlang_selprod_id AND sprods_l.selprodlang_lang_id = ' . $this->langId, 'sprods_l');
                $fields2 = array('selprod_title', 'selprod_warranty', 'selprod_return_policy', 'sprods_l.selprod_comments as selprodComments', 'sprods_l.selprod_rental_terms as selprodRentalTerms');
            }

            $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'sprods.selprod_id = spd.sprodata_selprod_id', 'spd');
            
            if ($isProductActive == true) {
                if (isset($criteria['producttype']) && $criteria['producttype'] == applicationConstants::PRODUCT_FOR_SALE) {
                    $srch->addCondition('selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
                } elseif(isset($criteria['producttype']) && $criteria['producttype'] == applicationConstants::PRODUCT_FOR_RENT) {
                    $srch->addCondition('sprodata_rental_active', '=', 'mysql_func_'.applicationConstants::ACTIVE, 'AND', true);
                } else {
                    $cnd = $srch->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
                    if (ALLOW_SALE) {
                        $cnd->attachCondition('selprod_active', '=', 'mysql_func_'.applicationConstants::ACTIVE, 'OR', true);
                    }
                }
            }
            
            $productType = Product::PRODUCT_FOR_RENT;
            $priceFld = 'sprodata_rental_price';
            if (isset($criteria['producttype']) && $criteria['producttype'] == applicationConstants::PRODUCT_FOR_SALE) {
                $productType = Product::PRODUCT_FOR_SALE;
                $priceFld = 'selprod_price';
            } 
            $srch->joinTable(
                SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'm.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN m.splprice_start_date AND m.splprice_end_date AND m.splprice_type = ' . $productType, 'm'
            );
            
            /* $srch->addCondition('m.splprice_selprod_id', 'IS', 'mysql_func_NULL', 'AND', true); */
            $srch->addCondition('selprod_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
            
            if (isset($criteria['producttype']) && $criteria['producttype'] == applicationConstants::PRODUCT_FOR_SALE && $checkAvailableFrom) {
                $srch->addCondition('sprods.selprod_available_from', '<=', $splPriceForDate);
            }

            $fields1 = array(
                'sprods.*', 'm.*',
                '(CASE WHEN m.splprice_selprod_id IS NULL THEN 0 ELSE 1 END) AS special_price_found',
                'COALESCE(m.splprice_price, '. $priceFld .') AS theprice', 'spd.*', 'COALESCE(m.splprice_price, '. $priceFld .') AS rent_price', 'm.splprice_type as specialPriceType'
            );
            $srch->addMultipleFields(array_merge($fields1, $fields2));

            $srch->doNotLimitRecords();
            $srch->doNotCalculateRecords();
            $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'p.product_id = pricetbl.selprod_product_id', 'pricetbl');
        } else {
            $this->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'p.product_id = sprods.selprod_product_id and  selprod_deleted = ' . applicationConstants::NO, 'sprods');
            if ($this->langId) {
                $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sprods.selprod_id = sprods_l.selprodlang_selprod_id AND sprods_l.selprodlang_lang_id = ' . $this->langId, 'sprods_l');
            }
            $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'sprods.selprod_id = spd.sprodata_selprod_id', 'spd');
        }

        $productType = (!empty($criteria['producttype'])) ? $criteria['producttype'] : array();
        if (!is_array($productType)) {
            $productType = (array) $productType;
        }
        
        $this->addProductTypeCondition($productType);

        if (0 < $bySeller) {
            $this->addCondition('selprod_user_id', '=', 'mysql_func_'. $bySeller, 'AND', true);
        } else {
            $this->addCondition('selprod_user_id', '>', 'mysql_func_0', 'AND', true);
        }
    }

    /* public function joinProductVariantOptions(){
      $this->joinTable( SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'pricetbl.selprod_id = tspo.selprodoption_selprod_id', 'tspo');
      $this->joinTable( OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval' );
      $this->joinTable( Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op' );
      $this->addGroupBy('tspo.selprodoption_selprod_id');

      // $this->addMultipleFields(array('GROUP_CONCAT( selprodoption_option_id ) as option_ids', 'GROUP_CONCAT( selprodoption_optionvalue_id ) as option_value_ids'));
      // 'GROUP_CONCAT( option_name ) as option_names', 'GROUP_CONCAT( optionvalue_name ) as option_value_names'

      if( $this->langId ){
      $this->joinTable( Option::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = '. $this->langId, 'op_l' );
      $this->joinTable( OptionValue::DB_TBL.'_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = '. $this->langId, 'opval_l' );
      }
      } */

    public function joinSellers()
    {
        $this->sellerUserJoined = true;
        $this->joinTable(User::DB_TBL, 'INNER JOIN', 'selprod_user_id = seller_user.user_id and seller_user.user_is_supplier = ' . applicationConstants::YES . ' AND seller_user.user_deleted = ' . applicationConstants::NO, 'seller_user');
        $this->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'credential_user_id = seller_user.user_id and credential_active = ' . applicationConstants::ACTIVE . ' and credential_verified = ' . applicationConstants::YES, 'seller_user_cred');
    }

    public function joinProductShippedBySeller($sellerId = 0)
    {
        $sellerId = FatUtility::int($sellerId);
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $sellerId = 0;
        }
        $this->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = p.product_id and psbs.psbs_user_id = ' . $sellerId, 'psbs');
    }

    public function joinProductShippedBy()
    {
        $this->joinProductShippedBy = true;
        $cond = 'and psbs.psbs_user_id = selprod_user_id';
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $cond = 'and psbs.psbs_user_id = 0';
        }
        $this->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = p.product_id ' . $cond, 'psbs');
    }

    public function joinProductFreeShipping()
    {
        $cond = 'and ps.ps_user_id = selprod_user_id';
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $cond = 'and ps.ps_user_id = 0';
        }
        $this->joinTable(Product::DB_TBL_PRODUCT_SHIPPING, 'LEFT OUTER JOIN', 'ps.ps_product_id = p.product_id ' . $cond, 'ps');
    }

    public function setLocationBasedInnerJoin($innerJoin = true)
    {
        $this->locationBasedInnerJoin = $innerJoin;
    }

    public function joinShops($langId = 0, $isActive = true, $isDisplayStatus = true, $shopId = 0)
    {
        if (!$this->sellerUserJoined) {
            trigger_error(Labels::getLabel('ERR_joinShops_cannot_be_joined,_unless_joinSellers_is_not_applied.', $this->commonLangId), E_USER_ERROR);
        }

        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $shopCondition = '';
        if ($isActive) {
            $shopCondition .= ' and shop.shop_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('shop.shop_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        if ($isDisplayStatus) {
            $shopCondition .= ' and shop.shop_supplier_display_status = ' . applicationConstants::ON;
            $this->addCondition('shop.shop_supplier_display_status', '=', 'mysql_func_'. applicationConstants::ON, 'AND', true);
        }

        $shopId = FatUtility::int($shopId);
        if (0 < $shopId) {
            $shopCondition .= ' and shop.shop_id = ' . $shopId;
        }

        $joinShopWithSubQuery = false;
        /* if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
            $prodGeoCondition = FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0);
            switch ($prodGeoCondition) {
                case applicationConstants::BASED_ON_RADIUS:
                    if (array_key_exists('ykGeoLat', $this->geoAddress) && $this->geoAddress['ykGeoLat'] != '' && array_key_exists('ykGeoLng', $this->geoAddress) && $this->geoAddress['ykGeoLng'] != '') {
                        $shopSearch = new SearchBase(Shop::DB_TBL, 'shop');
                        $shopSearch->doNotCalculateRecords();
                        $shopSearch->doNotLimitRecords();
                        $shopSearch->addCondition('shop.shop_supplier_display_status', '=', applicationConstants::ON);
                        $shopSearch->addCondition(Shop::tblFld('active'), '=', applicationConstants::ACTIVE);
                        $shopSearch->addFld('*');
                        $shopSearch->addFld('( 6371 * acos( cos( radians(' . $this->geoAddress['ykGeoLat'] . ') ) * cos( radians( shop.`shop_lat` ) ) * cos( radians( shop.`shop_lng` ) - radians(' . $this->geoAddress['ykGeoLng'] . ') ) + sin( radians(' . $this->geoAddress['ykGeoLat'] . ') ) * sin( radians( shop.`shop_lat` ) ) ) ) AS distance');
                        $shopSearch->addHaving('distance', '<=', FatApp::getConfig('CONF_RADIUS_DISTANCE_IN_MILES', FatUtility::VAR_INT, 10));
                        if (false == $this->locationBasedInnerJoin) {
                            $shopSubQuery = $shopSearch->getQuery();
                            $shopSearch = new SearchBase(Shop::DB_TBL, 'sshop');
                            $shopSearch->doNotCalculateRecords();
                            $shopSearch->doNotLimitRecords();
                            $shopSearch->addCondition('sshop.shop_supplier_display_status', '=', applicationConstants::ON);
                            $shopSearch->addCondition('sshop.' . Shop::tblFld('active'), '=', applicationConstants::ACTIVE);
                            $shopSearch->addMultipleFields(array('sshop.*', 'shop.distance'));
                            $shopSearch->joinTable('(' . $shopSubQuery . ')', 'LEFT OUTER JOIN', 'shop.shop_id = sshop.shop_id', 'shop');
                        }
                        $joinShopWithSubQuery = true;
                    }
                    break;
                case applicationConstants::BASED_ON_CURRENT_LOCATION:
                    $level = FatApp::getConfig('CONF_LOCATION_LEVEL', FatUtility::VAR_INT, 0);
                    $countryBased = $stateBased = $zipBased = false;
                    if (applicationConstants::LOCATION_COUNTRY == $level) {
                        $countryBased = true;
                    } elseif (applicationConstants::LOCATION_STATE == $level) {
                        $countryBased = $stateBased = true;
                    } elseif (applicationConstants::LOCATION_ZIP == $level) {
                        $countryBased = $stateBased = $zipBased = true;
                    }

                    $locCondition = '';
                    if ($countryBased && array_key_exists('ykGeoCountryId', $this->geoAddress) && $this->geoAddress['ykGeoCountryId'] > 0) {
                        $locCondition .= ' and shop.shop_country_id = ' . $this->geoAddress['ykGeoCountryId'];
                    }

                    if ($stateBased && array_key_exists('ykGeoStateId', $this->geoAddress) && $this->geoAddress['ykGeoStateId'] > 0) {
                        $locCondition .= ' and shop.shop_state_id = ' . $this->geoAddress['ykGeoStateId'];
                    }

                    if ($zipBased && array_key_exists('ykGeoZip', $this->geoAddress) && $this->geoAddress['ykGeoZip'] > 0) {
                        $locCondition .= ' and shop.shop_postalcode = ' . $this->geoAddress['ykGeoZip'];
                    }

                    if (true == $this->locationBasedInnerJoin) {
                        $shopCondition .= $locCondition;
                        $this->addFld('1 as availableInLocation');
                    } else {
                        if (!empty($locCondition)) {
                            $this->addFld('if ((1 ' . $locCondition . '), 1, 0) as availableInLocation');
                        } else {
                            $this->addFld('1 as availableInLocation');
                        }
                    }
                    break;
            }
        } */

        $locationBasedInnerJoin = (true == $this->locationBasedInnerJoin) ? 'INNER JOIN' : 'LEFT OUTER JOIN';
        if ($joinShopWithSubQuery) {
            $this->joinTable('(' . $shopSearch->getQuery() . ')', $locationBasedInnerJoin, 'seller_user.user_id = shop.shop_user_id  ' . $shopCondition, 'shop');
        } else {
            $this->joinTable(Shop::DB_TBL, 'INNER JOIN', 'seller_user.user_id = shop.shop_user_id ' . $shopCondition, 'shop');
        }

        $this->shopsJoined = true;

        if ($langId) {
            $this->joinShopsLang($langId);
        }
    }

    public function joinShopSpecifics()
    {
        if (!$this->shopsJoined) {
            trigger_error('Shops are not joined', E_USER_ERROR);
        }
        $this->joinTable(ShopSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'shop.shop_id = ss.ss_shop_id', 'ss');
    }

    public function joinShopsLang($langId)
    {
        $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = s_l.shoplang_shop_id AND shoplang_lang_id = ' . $langId, 's_l');
    }

    public function joinShopCountry($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $countryActiveCondition = '';
        if ($isActive) {
            $countryActiveCondition = 'and shop_country.country_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('shop_country.country_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        $this->joinTable(Countries::DB_TBL, 'INNER JOIN', 'shop.shop_country_id = shop_country.country_id ' . $countryActiveCondition, 'shop_country');

        if ($langId) {
            $this->joinShopCountryLang($langId);
        }
    }

    public function joinShopCountryLang($langId)
    {
        $langId = FatUtility::int($langId);
        $this->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_country.country_id = shop_country_l.countrylang_country_id AND shop_country_l.countrylang_lang_id = ' . $langId, 'shop_country_l');
    }

    public function joinShopState($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $stateActiveCondition = '';
        if ($isActive) {
            $stateActiveCondition = 'and shop_state.state_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('shop_state.state_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        $this->joinTable(States::DB_TBL, 'INNER JOIN', 'shop.shop_state_id = shop_state.state_id ' . $stateActiveCondition, 'shop_state');

        if ($langId) {
            $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_state.state_id = shop_state_l.statelang_state_id AND shop_state_l.statelang_lang_id = ' . $langId, 'shop_state_l');
        }
    }

    public function joinBrands($langId = 0, $isActive = true, $isDeleted = true, $useInnerJoin = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }
        $join = ($useInnerJoin && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) ? 'INNER JOIN' : 'LEFT OUTER JOIN';

        $brandActiveCondition = '';
        if ($isActive && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $brandActiveCondition = 'and brand.brand_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('brand.brand_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        $brandDeletedCondition = '';
        if ($isDeleted && FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $brandDeletedCondition = 'and brand.brand_deleted = ' . applicationConstants::NO;
            $this->addCondition('brand.brand_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        }

        $this->joinTable(Brand::DB_TBL, $join, 'p.product_brand_id = brand.brand_id ' . $brandActiveCondition . ' ' . $brandDeletedCondition, 'brand');

        if ($langId) {
            $this->joinBrandsLang($langId);
        }
    }

    public function joinBrandsLang($langId, $keyword = '')
    {
        $joinCondition = '';
        $joinBy = 'LEFT OUTER JOIN';
        if (!empty($keyword)) {
            $joinBy = 'INNER JOIN';
            $joinCondition = ' and tb_l.brand_name like ' . FatApp::getDb()->quoteVariable($keyword);
        }
        $this->joinTable(Brand::DB_TBL_LANG, $joinBy, 'brand.brand_id = tb_l.brandlang_brand_id AND brandlang_lang_id = ' . $langId . $joinCondition, 'tb_l');
    }

    public function joinProductToCategory($langId = 0, $isActive = true, $isDeleted = true, $useInnerJoin = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }
        $join = ($useInnerJoin) ? 'INNER JOIN' : 'LEFT OUTER JOIN';
        $this->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, $join, 'ptc.ptc_product_id = p.product_id', 'ptc');

        $categoryActiveCondition = '';
        if ($isActive) {
            $categoryActiveCondition = 'and c.prodcat_active = ' . applicationConstants::ACTIVE;
            $this->addCondition('c.prodcat_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        }

        $categoryDeletedCondition = '';
        if ($isDeleted) {
            $categoryDeletedCondition = 'and c.prodcat_deleted = ' . applicationConstants::NO;
            $this->addCondition('c.prodcat_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        }

        $this->addCondition('c.prodcat_status', '=', 'mysql_func_'. ProductCategory::REQUEST_APPROVED, 'AND', true);

        $this->joinTable(ProductCategory::DB_TBL, $join, 'c.prodcat_id = ptc.ptc_prodcat_id ' . $categoryActiveCondition . ' ' . $categoryDeletedCondition, 'c');

        if ($langId) {
            $this->joinProductToCategoryLang($langId);
        }
    }

    public function joinProductToCategoryLang($langId)
    {
        $this->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.prodcatlang_prodcat_id = c.prodcat_id AND prodcatlang_lang_id = ' . $langId, 'c_l');
    }

    public function joinFavouriteProducts($user_id)
    {
        $this->joinTable(Product::DB_TBL_PRODUCT_FAVORITE, 'LEFT OUTER JOIN', 'ufp.ufp_selprod_id = selprod_id and ufp.ufp_user_id = ' . $user_id, 'ufp');
    }

    public function joinProductToTax()
    {
        $this->joinTable(Tax::DB_TBL_PRODUCT_TO_TAX, 'LEFT OUTER JOIN', 'ptt.ptt_product_id = product_id', 'ptt');
    }

    public function joinUserWishListProducts($user_id)
    {
        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', 'mysql_func_'. $user_id, 'AND', true);
        $wislistPSrchObj->addMultipleFields(array('uwlp_selprod_id', 'uwlp_uwlist_id'));
        $wishListSubQuery = $wislistPSrchObj->getQuery();
        $this->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');
    }

    public function addCategoryCondition($category)
    {
        if (is_numeric($category)) {
            $category_id = FatUtility::int($category);
            if (!$category_id) {
                return;
            }
            $catCode = ProductCategory::getAttributesById($category_id, 'prodcat_code');
            /* $this->addCondition('GETCATCODE(`prodcat_id`)', 'LIKE', '%' . str_pad($category_id, 6, '0', STR_PAD_LEFT ) . '%', 'AND', true); */
            $this->addCondition('c.prodcat_code', 'LIKE', $catCode . '%', 'AND', true);
        } else {
            if (!is_array($category)) {
                $category = explode(",", $category);
            }
            /* $category = explode(",", $category);
              $category = FatUtility::int($category);
              $this->addCondition('prodcat_id', 'IN', $category ); */

            if (0 < count(array_filter($category))) {
                $condition = '(';
                foreach ($category as $catId) {
                    $catId = FatUtility::int($catId);
                    if (1 > $catId) {
                        continue;
                    }
                    $catCode = ProductCategory::getAttributesById($catId, 'prodcat_code');
                    $condition .= " c.prodcat_code LIKE '" . $catCode . "%' OR";
                }
                $condition = substr($condition, 0, -2);
                $condition .= ')';

                $this->addDirectCondition($condition);
            }
        }
    }

    public function addProductShippedBySellerCondition($sellerId = 0)
    {
        $sellerId = FatUtility::int($sellerId);
        $this->addDirectCondition(" ( isnull(psbs.psbs_user_id) or  psbs.psbs_user_id ='" . $sellerId . "')");
    }

    public function addKeywordSearch($keyword, $obj = false, $useRelevancy = true, $langJoin = true)
    {
        if (empty($keyword) || $keyword == '') {
            return;
        }

        if (false === $obj) {
            $obj = $this;
        }

        $keywordLength = mb_strlen($keyword);
        $cnd = $obj->addCondition('product_isbn', 'LIKE', '%' . $keyword . '%');
        /* $cnd->attachCondition('product_upc', 'LIKE', '%' . $keyword . '%'); */

        $arr = explode(' ', $keyword);
        $arr_keywords = array();
        foreach ($arr as $value) {
            $value = trim($value);
            if (strlen($value) < 3) {
                continue;
            }
            $arr_keywords[] = $value;
        }

        if (count($arr_keywords) > 0) {
            if ($keywordLength <= 80) {
                foreach ($arr_keywords as $value) {
                    $cnd->attachCondition('product_tags_string', 'LIKE', '%' . $value . '%');
                    $cnd->attachCondition('selprod_title', 'LIKE', '%' . $value . '%');
                    /*  $cnd->attachCondition('product_name', 'LIKE', '%' . $value . '%'); */
                    if ($langJoin) {
                        $cnd->attachCondition('brand_name', 'LIKE', '%' . $value . '%');
                        $cnd->attachCondition('prodcat_name', 'LIKE', '%' . $value . '%');
                    }
                    
                }
            }
            $strKeyword = FatApp::getDb()->quoteVariable('%' . $keyword . '%');
            if ($useRelevancy === true) {
                $obj->addFld(
                        "IF(product_isbn LIKE $strKeyword, 15, 0)
                + IF(selprod_title LIKE $strKeyword, 4, 0)                
                + IF(product_tags_string LIKE $strKeyword, 4, 0)
                AS keyword_relevancy"
                );
            } else {
                $obj->addFld('0 AS keyword_relevancy');
            }
        } else {
            // $cnd->attachCondition('product_tags_string', 'LIKE', '%' . $value . '%');
            $obj->addFld('0 AS keyword_relevancy');
        }
    }

    public function addProductIdCondition($product_id)
    {
        if (!$product_id) {
            trigger_error(Labels::getLabel('ERR_Product_Id_not_Passed!', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('product_id', '=', 'mysql_func_'. $product_id, 'AND', true);
    }

    public function addBrandCondition($brand)
    {
        //@todo enhancements
        if (FatApp::getConfig('CONF_PRODUCT_BRAND_MANDATORY', FatUtility::VAR_INT, 1) == 1) {
            if (is_numeric($brand)) {
                $brandId = FatUtility::int($brand);
                $this->addCondition('brand_id', '=', 'mysql_func_'. $brandId, 'AND', true);
            } elseif (is_array($brand) && 0 < count($brand)) {
                $brand = array_filter(array_unique($brand));
                if (!empty($brand)) {
                    $this->addDirectCondition('brand_id IN (' . implode(',', $brand) . ')');
                }
            } else {
                if (!empty($brand)) {
                    $brand = explode(",", $brand);
                    $brand = array_filter(array_unique($brand));
                    $this->addDirectCondition('brand_id IN (' . implode(',', $brand) . ')');
                }
            }
        } else {
            if (is_numeric($brand)) {
                $brandId = FatUtility::int($brand);
                $brandId = ($brandId <= 0) ? 0 : $brandId;
                $this->addCondition('product_brand_id', '=', 'mysql_func_'.$brandId, 'AND', true);
            } elseif (is_array($brand) && 0 < count($brand)) {
                $brand = array_filter(array_unique($brand));
                $brandString = str_replace('-1', '0', implode(',', $brand));
                $this->addDirectCondition('product_brand_id IN (' . $brandString . ')');
            } else {
                if (!empty($brand)) {
                    $brand = explode(",", $brand);
                    $brand = array_filter(array_unique($brand));
                    $brandString = str_replace('-1', '0', implode(',', $brand));
                    $this->addDirectCondition('product_brand_id IN (' . $brandString . ')');
                }
            }
        }
    }

    public function addOptionCondition($optionValue, $obj = false, $alias = '')
    {
        if ($obj === false) {
            $obj = $this;
        }

        if ($alias != '') {
            $alias .= '.';
        }

        if (is_array($optionValue)) {
            $str = '( ';
            $orCnd = '';
            $andCnd = '';
            foreach ($optionValue as $val) {
                //$str.= $andCnd;
                if (1 > FatUtility::int($val)) {
                    continue;
                }
                $str .= $orCnd . " " . $alias . "selprod_code like '%\_" . $val . "\_%' or " . $alias . "selprod_code like '%\_" . $val . "'";
                $orCnd = ' or';
                //$andCnd = ") and (";
            }
            $str .= " )";
            $obj->addDirectCondition($str);
        } elseif (strpos($optionValue, ",") === false) {
            if (strpos($optionValue, "_") === false) {
                $opVal = $optionValue;
            } else {
                $opVal = substr($optionValue, strpos($optionValue, "_") + 1);
            }

            $opVal = FatUtility::int($opVal);
            $obj->addDirectCondition(" (" . $alias . "selprod_code like '%\_" . $opVal . "\_%' or " . $alias . "selprod_code like '%\_" . $opVal . "') ");
        } else {
            $optionValueArr = explode(",", $optionValue);
            sort($optionValueArr);
            $opValArr = array();
            foreach ($optionValueArr as $val) {
                $opVal = explode("_", $val);
                $opValArr[$opVal[0]][] = $opVal[1];
            }
            $str = '( ';
            $orCnd = '';
            $andCnd = '';
            foreach ($opValArr as $row) {
                $str .= $andCnd;
                foreach ($row as $val) {
                    if (1 > FatUtility::int($val)) {
                        continue;
                    }
                    $str .= $orCnd . " " . $alias . "selprod_code like '%\_" . $val . "\_%' or " . $alias . "selprod_code like '%\_" . $val . "'";
                    $orCnd = 'or';
                }
                $orCnd = "";
                $andCnd = ") and (";
            }
            $str .= " )";
            $obj->addDirectCondition($str);
        }
    }

    /* public function addUPCCondition($upc){
      $this->addCondition('product_upc', 'like', $upc );
      }

      public function addISBNCondition($isbn){
      $this->addCondition('product_isbn', 'like', $isbn );
      } */

    public function addShopIdCondition($shop_id)
    {
        $shop_id = FatUtility::int($shop_id);
        if (!$shop_id) {
            trigger_error(Labels::getLabel('ERR_Shop_Id_not_Passed', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('shop_id', '=', 'mysql_func_'.$shop_id, 'AND', true);
    }

    public function addCollectionIdCondition($collection_id)
    {
        $collection_id = FatUtility::int($collection_id);
        if (!$collection_id) {
            trigger_error(Labels::getLabel('ERR_Collection_Id_not_Passed', $this->commonLangId), E_USER_ERROR);
        }

        $this->joinTable(ShopCollection::DB_TBL_SHOP_COLLECTION_PRODUCTS, 'INNER JOIN', ShopCollection::DB_SELLER_PRODUCTS_PREFIX . 'id = ' . ShopCollection::DB_TBL_SHOP_COLLECTION_PRODUCTS_PREFIX . 'selprod_id and ' . ShopCollection::DB_TBL_SHOP_COLLECTION_PRODUCTS_PREFIX . 'scollection_id = ' . $collection_id);
        //return $srch;
    }

    public function addConditionCondition($condition, $obj = false, $productType = applicationConstants::PRODUCT_FOR_RENT)
    {
        if ($obj === false) {
            $obj = $this;
        }
        if ($productType == applicationConstants::PRODUCT_FOR_RENT) {
            $columnName = "sprodata_rental_condition";
        } else {
            $columnName = "selprod_condition";
        }
        

        if (is_numeric($condition)) {
            $condition = FatUtility::int($condition);
            $obj->addCondition($columnName, '=', 'mysql_func_'. $condition, 'AND', true);
        } elseif (is_array($condition)) {
            $condition = array_filter(array_unique($condition));
            $obj->addDirectCondition($columnName.' IN (' . implode(',', $condition) . ')');
        } else {
            $condition = explode(",", $condition);
            $condition = FatUtility::int($condition);
            $condition = array_filter(array_unique($condition));
            $obj->addDirectCondition($columnName . ' IN (' . implode(',', $condition) . ')');
        }
    }

    public function addMoreSellerCriteria($productCode, $sellerId = 0)
    {
        $sellerId = FatUtility::int($sellerId);
        if ($productCode == '') {
            trigger_error(Labels::getLabel('ERR_Invalid_Argument_Passed', $this->commonLangId), E_USER_ERROR);
        }

        //$this->setDefinedCriteria();
        $this->joinSellerProducts();
        $this->joinSellers();
        $this->joinShops();
        $this->joinShopCountry();
        $this->joinShopState();
        $this->joinBrands();
        $this->joinSellerSubscription();
        $this->addSubscriptionValidCondition();
        $this->joinProductToCategory();
        $this->doNotCalculateRecords();
        $this->doNotLimitRecords();
        $this->addCondition('selprod_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        if ($sellerId > 0) {
            $this->addCondition('selprod_user_id', '!=', 'mysql_func_'. $sellerId, 'AND', true);
        }
        /* $this->addCondition('selprod_code', '=', $productCode); */
        if (is_array($productCode)) {
            $this->addCondition('selprod_code', 'IN', $productCode);
        } else {
            $this->addCondition('selprod_code', '=', $productCode);
        }
    }

    public function excludeOutOfStockProducts()
    {
        $this->addCondition('selprod_stock', '>', 'mysql_func_0', 'AND', true);
    }

    public function addAttributesCriteria($product_id, $lang_id)
    {
        $product_id = FatUtility::int($product_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$product_id || !$lang_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Argument_Passed', $this->commonLangId), E_USER_ERROR);
        }

        $this->joinTable(AttributeGroup::DB_TBL_ATTRIBUTES, 'INNER JOIN', 'p.product_attrgrp_id = attr.attr_attrgrp_id', 'attr');
        $this->joinTable(AttributeGroup::DB_TBL_ATTRIBUTES . '_lang', 'LEFT OUTER JOIN', 'attr.attr_id = attr_l.attrlang_attr_id AND attr_l.attrlang_lang_id = ' . $lang_id, 'attr_l');
        $this->addProductIdCondition($product_id);
    }

    public function joinProductRating()
    {
        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSellerProducts();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', 'mysql_func_'. SelProdRating::TYPE_PRODUCT, 'AND', true);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', 'mysql_func_'. SelProdReview::STATUS_APPROVED, 'AND', true);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $this->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');
    }

    public function joinSellerOrder($langId = 0)
    {
        if (!$this->sellerUserJoined) {
            trigger_error(Labels::getLabel('ERR_Seller_must_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $this->sellerSubscriptionOrderJoined = true;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type=' . ORDERS::ORDER_SUBSCRIPTION . ' AND o.order_payment_status =1', 'o');
        }
    }

    public function joinSellerOrderSubscription($langId = 0, $includeDateCondition = false)
    {
        $langId = FatUtility::int($langId);

        if (!$this->sellerSubscriptionOrderJoined) {
            trigger_error(Labels::getLabel('ERR_Seller_Subscription_Order_must_joined.', $this->commonLangId), E_USER_ERROR);
        }

        $validDateCondition = '';
        if ($includeDateCondition) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
        }

        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
            if ($langId > 0) {
                $this->joinTable(OrderSubscription::DB_TBL_LANG, 'LEFT OUTER JOIN', 'oss.ossubs_id = ossl.' . OrderSubscription::DB_TBL_LANG_PREFIX . 'ossubs_id AND ossubslang_lang_id = ' . $langId, 'ossl');
            }
        }
    }

    public function joinSellerSubscription($langId = 0, $joinSeller = false, $includeDateCondition = false)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        if ($joinSeller) {
            $this->joinSellers();
        }

        if (!$this->sellerUserJoined) {
            trigger_error(Labels::getLabel('ERR_Seller_must_joined.', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $validDateCondition = '';
        if ($includeDateCondition) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
        }

        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $srch = new searchBase(Orders::DB_TBL, 'o');
            $srch->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id =' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
            if ($langId > 0) {
                $srch->joinTable(OrderSubscription::DB_TBL_LANG, 'LEFT OUTER JOIN', 'oss.ossubs_id = ossl.' . OrderSubscription::DB_TBL_LANG_PREFIX . 'ossubs_id AND ossubslang_lang_id = ' . $langId, 'ossl');
            }
            $srch->addCondition('o.order_type', '=', 'mysql_func_'. ORDERS::ORDER_SUBSCRIPTION, 'AND', true);
            $srch->addCondition('o.order_payment_status', '=', 'mysql_func_1', 'AND', true);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            //$srch->addGroupBy('o.order_user_id');
            $srch->addMultipleFields(array('oss.*', 'order_user_id', 'order_id', 'order_type'));
            $this->joinTable('(' . $srch->getQuery() . ')', 'INNER JOIN', 'oss.order_user_id=seller_user.user_id', 'oss');
        }

        /* $this->joinSellerOrder();
          $this->joinSellerOrderSubscription($langId, $includeDateCondition); */

        //$this->addSubscriptionValidCondition();
    }

    public function addSubscriptionValidCondition($date = '')
    {
        if ($date == '') {
            $date = date("Y-m-d");
        }
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $this->addCondition('oss.ossubs_till_date', '>=', $date);
            $this->addCondition('ossubs_status_id', 'IN ', Orders::getActiveSubscriptionStatusArr());
        }
    }

    /* public function joinSellerProductOptionsWithSelProdCode($langId = 0){
      $langId = FatUtility::int( $langId );
      if ($this->langId && 1 > $langId) {
      $langId = $this->langId;
      }

      $this->joinTable( OptionValue::DB_TBL, 'LEFT OUTER JOIN', "selprod_code LIKE CONCAT('%_', ov.optionvalue_id)" , 'ov' );
      $this->joinTable( Option::DB_TBL, 'INNER JOIN', 'spo.option_id = ov.optionvalue_option_id', 'spo' );

      if( $langId ){
      $this->joinTable( OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = '.$langId, 'ov_lang' );

      $this->joinTable( Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'spo.option_id = spo_lang.optionlang_option_id AND spo_lang.optionlang_lang_id = '.$langId, 'spo_lang' );
      }
      } */

    public function joinSellerProductSpecifics()
    {
        $this->joinTable(SellerProductSpecifics::DB_TBL, 'LEFT JOIN', 'sps.sps_selprod_id = selprod_id', 'sps');
    }

    public function joinProductSpecifics()
    {
        $this->joinTable(ProductSpecifics::DB_TBL, 'LEFT JOIN', 'ps.ps_product_id = p.product_id', 'ps');
    }

    public function joinShippingPackages($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $this->joinTable(ShippingPackage::DB_TBL, 'LEFT OUTER JOIN', 'shipkg.shippack_id = p.product_ship_package', 'shipkg');
    }

    public function joinShippingProfileProducts($cartType = applicationConstants::PRODUCT_FOR_SALE)
    {
        if (!$this->joinProductShippedBy) {
            trigger_error(Labels::getLabel('ERR_joinProductShippedBy_function_not_joined.', $this->commonLangId), E_USER_ERROR);
        }

        if ($cartType == applicationConstants::PRODUCT_FOR_RENT) {
            $joinCondition = 'spprod.shippro_user_id = selprod_user_id';
        } else {
            $joinCondition = 'if(product_seller_id = 0, (if(psbs.psbs_user_id > 0, spprod.shippro_user_id = psbs.psbs_user_id, spprod.shippro_user_id = 0)), (spprod.shippro_user_id = selprod_user_id))';

            if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                $joinCondition = 'spprod.shippro_user_id = 0';
            }
        }


        $this->joinTable(ShippingProfileProduct::DB_TBL, 'LEFT OUTER JOIN', 'spprod.shippro_product_id = selprod_product_id and ' . $joinCondition, 'spprod');
    }

    // public function joinShippingProfile()
    // {
    //     $this->joinTable(ShippingProfile::DB_TBL, 'LEFT OUTER JOIN', 'spprod.shippro_shipprofile_id = spprof.shipprofile_id and spprof.shipprofile_active = ' . applicationConstants::YES, 'spprof');
    // }

    public function joinShippingProfile($langId = 0)
    {
        $this->joinTable(ShippingProfile::DB_TBL, 'LEFT OUTER JOIN', 'spprod.shippro_shipprofile_id = spprof.shipprofile_id and spprof.shipprofile_active = ' . applicationConstants::YES, 'spprof');
        if (0 < $langId) {
            $this->joinTable(ShippingProfile::DB_TBL_LANG, 'LEFT OUTER JOIN', 'spprof_l.shipprofilelang_shipprofile_id = spprof.shipprofile_id and spprof_l.shipprofilelang_lang_id = ' . $langId, 'spprof_l');
        }
    }

    public function joinShippingProfileZones()
    {
        $this->joinTable(ShippingProfileZone::DB_TBL, 'LEFT OUTER JOIN', 'shippz.shipprozone_shipprofile_id = spprof.shipprofile_id', 'shippz');
    }

    public function joinShippingZones()
    {
        $this->joinTable(ShippingZone::DB_TBL, 'LEFT OUTER JOIN', 'shipz.shipzone_id = shippz.shipprozone_shipzone_id and shipz.shipzone_active = ' . applicationConstants::YES, 'shipz');
    }

    public function joinShippingRates($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $this->joinTable(ShippingRate::DB_TBL, 'LEFT OUTER JOIN', 'shipr.shiprate_shipprozone_id = shippz.shipprozone_id', 'shipr');
        if (0 < $langId) {
            $this->joinTable(ShippingRate::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shipr_l.shipratelang_shiprate_id = shipr.shiprate_id and shipr_l.shipratelang_lang_id = ' . $langId, 'shipr_l');
        }
    }

    public function joinShippingLocations($countryId, $stateId, $langId = 0, $innerJoin = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        $srch = ShippingZone::getZoneLocationSearchObject($langId);
        $srch->addDirectCondition("(shiploc_country_id = '-1' or (shiploc_country_id = '" . $countryId . "' and (shiploc_state_id = '-1' or shiploc_state_id = '" . $stateId . "')) )");
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $joinCondition = (true == $innerJoin) ? 'INNER JOIN' : 'LEFT OUTER JOIN';

        $this->joinTable('(' . $srch->getQuery() . ')', $joinCondition, 'shiploc.shiploc_shipzone_id = shippz.shipprozone_shipzone_id', 'shiploc');
    }

    public function validateAndJoinDeliveryLocation($includeShipingProfileCheck = false)
    {
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $prodGeoCondition = FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0);
            switch ($prodGeoCondition) {
                case applicationConstants::BASED_ON_DELIVERY_LOCATION:
                    $shippingServiceActive = Plugin::isActiveByType(Plugin::TYPE_SHIPPING_SERVICES);
                    if (!$shippingServiceActive) {
                        $this->joinDeliveryLocations();
                        if (true == $includeShipingProfileCheck) {
                            $this->addHaving('shippingProfile', 'IS NOT', 'mysql_func_null', 'and', true);
                            $this->addFld('1 as availableInLocation');
                        } else {
                            $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', ifnull(shipprofile.shippro_product_id,0), 1) as availableInLocation');
                        }
                    }

                    break;
                case applicationConstants::BASED_ON_RADIUS:
                    if (array_key_exists('ykGeoLat', $this->geoAddress) && $this->geoAddress['ykGeoLat'] != '' && array_key_exists('ykGeoLng', $this->geoAddress) && $this->geoAddress['ykGeoLng'] != '') {
                        $distanceInMiles = FatApp::getConfig('CONF_RADIUS_DISTANCE_IN_MILES', FatUtility::VAR_INT, 10);
                        $this->addFld('if(shop.distance <= ' . $distanceInMiles . ', 1, 0) as availableInLocation');
                    } else {
                        $this->addFld('0 as availableInLocation');
                    }
                    break;
                case applicationConstants::BASED_ON_CURRENT_LOCATION:

                    break;
                default:
                    $this->addFld('1 as availableInLocation');
                    break;
            }
        } else {
            $this->addFld('1 as availableInLocation');
        }
    }

    public function joinDeliveryLocations($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId && 1 > $langId) {
            $langId = $this->langId;
        }

        if (empty($this->geoAddress)) {
            trigger_error(Labels::getLabel('ERR_setGoeAddress_function_not_joined.', $langId), E_USER_ERROR);
        }

        $countryId = 0;
        $stateId = 0;
        if (array_key_exists('ykGeoCountryId', $this->geoAddress) && $this->geoAddress['ykGeoCountryId'] > 0) {
            $countryId = $this->geoAddress['ykGeoCountryId'];
        }

        if (array_key_exists('ykGeoStateId', $this->geoAddress) && $this->geoAddress['ykGeoStateId'] > 0) {
            $stateId = $this->geoAddress['ykGeoStateId'];
        }

        $srch = ShippingProfileProduct::getUserSearchObject(0, true);
        $srch->joinTable(ShippingProfile::DB_TBL, 'INNER JOIN', 'sppro.shippro_shipprofile_id = spprof.shipprofile_id and spprof.shipprofile_active = ' . applicationConstants::YES, 'spprof');
        $srch->joinTable(ShippingProfileZone::DB_TBL, 'INNER JOIN', 'shippz.shipprozone_shipprofile_id = spprof.shipprofile_id', 'shippz');
        $srch->joinTable(ShippingZone::DB_TBL, 'INNER JOIN', 'shipz.shipzone_id = shippz.shipprozone_shipzone_id and shipz.shipzone_active = ' . applicationConstants::YES, 'shipz');
        $srch->joinTable(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'LEFT OUTER JOIN', 'psbs.psbs_product_id = tp.product_id', 'psbs');

        $joinCondition = 'if(tp.product_seller_id = 0, (if(psbs.psbs_user_id > 0, sppro.shippro_user_id = psbs.psbs_user_id, sppro.shippro_user_id = 0)), (sppro.shippro_user_id = psbs.psbs_user_id))';
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $joinCondition = 'sppro.shippro_user_id = 0';
        }
        $srch->addDirectCondition($joinCondition);

        $tempSrch = ShippingZone::getZoneLocationSearchObject();
        $tempSrch->addDirectCondition("(shiploc_country_id = '-1' or (shiploc_country_id = '" . $countryId . "' and (shiploc_state_id = '-1' or shiploc_state_id = '" . $stateId . "')) )");
        $tempSrch->doNotCalculateRecords();
        $tempSrch->doNotLimitRecords();

        $srch->joinTable('(' . $tempSrch->getQuery() . ')', 'INNER JOIN', 'shiploc.shiploc_shipzone_id = shippz.shipprozone_shipzone_id', 'shiploc');

        $this->joinTable('(' . $srch->getQuery() . ')', 'LEFT OUTER JOIN', 'shipprofile.shippro_product_id = p.product_id', 'shipprofile');
        $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id, -1) as shippingProfile');
        // $this->joinTable('(' . $srch->getQuery() . ')', 'INNER JOIN', '(if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id = p.product_id, p.product_id =  p.product_id))', 'shipprofile');
        /* $this->addFld('if(p.product_type = ' . Product::PRODUCT_TYPE_PHYSICAL . ', shipprofile.shippro_product_id, -1) as shippingProfile');
          $this->addHaving('shippingProfile', '!=', 'null'); */
    }

    public function joinAttributes(array $attributes)
    {
        if (!empty($attributes)) {
            foreach ($attributes as $group => $attribute) {
                $attrData = unserialize($attribute);
                $this->joinTable(Product::DB_NUMERIC_ATTRIBUTES_TBL, 'INNER JOIN', 'pna_' . $group . '.prodnumattr_product_id = p.product_id AND pna_' . $group . '.prodnumattr_attrgrp_id = ' . $group, 'pna_' . $group);

                if (!empty($attrData)) {
                    foreach ($attrData as $key => $attr) {
                        /* $this->addCondition('pna_'. $group .'.'. $key, 'IN',  $attr); */
                        $findInSetQry = " (";
                        $i = 1;
                        foreach ($attr as $val) {
                            $findInSetQry .= " FIND_IN_SET('" . $val . "', pna_" . $group . "." . $key . ")";
                            if ($i < count($attr)) {
                                $findInSetQry .= ' OR ';
                            }
                            $i++;
                        }
                        $findInSetQry .= " )";
                        $this->addDirectCondition($findInSetQry);
                    }
                }
            }
        }
    }

    public function addProductTypeCondition($productType = array())
    {
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        $prodForSale = $prodForRent = 0;
        if (!empty($productType) && in_array(Product::PRODUCT_FOR_SALE, $productType)) {
            $prodForSale = 1;
        }

        if (!empty($productType) && in_array(Product::PRODUCT_FOR_RENT, $productType)) {
            $prodForRent = 1;
        }

        if ((ALLOW_SALE && ALLOW_RENT) && (($prodForSale == 0 && $prodForRent == 0) || ($prodForSale == 1 && $prodForRent == 1))) {
            $cnd = $this->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            $cnd->attachCondition('selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'OR', true);
        } else if (ALLOW_RENT && $prodForRent == 1) {
            $this->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            /* $this->addCondition('sprodata_rental_available_from', '<=', $now); */
        } else if (ALLOW_SALE && $prodForSale == 1) {
            $this->addCondition('selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            $this->addCondition('selprod_available_from', '<=', $now);
        } else if (ALLOW_SALE && $prodForSale == 0 && $prodForRent == 0) {
            $this->addCondition('selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            $this->addCondition('selprod_available_from', '<=', $now);
        } else if (ALLOW_RENT && $prodForSale == 0 && $prodForRent == 0) {
            $this->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            /* $this->addCondition('sprodata_rental_available_from', '<=', $now); */
        } else {
            $cnd = $this->addCondition('sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
            $cnd->attachCondition('selprod_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'OR', true);

            $cnd = $this->addCondition('selprod_available_from', '<=', $now);
            /* $cnd->attachCondition('sprodata_rental_available_from', '<=', $now, 'OR'); */
        }
    }

    public function addDateCondition($startDate, $endDate)
    {
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $srch = new SearchBase(ProductRental::DB_TBL_BOOKED_STOCK);
        $srch->doNotLimitRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('pbs_selprod_id', 'pbs_date', 'max(pbs_quantity) as pbs_quantity'));
        $srch->addCondition('pbs_date', '>=', $startDate);
        $srch->addCondition('pbs_date', '<=', $endDate);
        $srch->addOrder('pbs_quantity', 'DESC');
        $srch->addGroupBy('pbs_selprod_id');
        return $srch;
        //$srchQuery = $srch->getQuery();
        //$this->joinTable('(' . $srchQuery . ')', 'LEFT OUTER JOIN', 'pbs.pbs_selprod_id = msellprod.selprod_id', 'pbs');
    }

    public static function getSeachObjForRentMinPrice()
    {
        $srch = new ProductSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'rmsellprod.selprod_product_id = p.product_id and selprod_deleted = ' . applicationConstants::NO, 'rmsellprod');
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'rmsellprod.selprod_id = spd.sprodata_selprod_id ', 'spd');
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinBrands();
        $srch->joinShippingPackages();
        $srch->addCondition('spd.sprodata_rental_active', '=', 'mysql_func_'. applicationConstants::ACTIVE, 'AND', true);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
        $srch->addCondition('spd.sprodata_rental_stock', '>', 'mysql_func_0', 'AND', true);
        $srch->addCondition('sprodata_rental_available_from', '<=', FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        return $srch;
    }

}
