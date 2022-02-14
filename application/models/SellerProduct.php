<?php

/* created this class to access direct functions of getAttributesById and save function for below mentioned DB table. */

class SellerProduct extends MyAppModel
{

    public const DB_TBL = 'tbl_seller_products';
    public const DB_TBL_PREFIX = 'selprod_';
    
    public const DB_TBL_SELLER_PROD_DATA = 'tbl_seller_products_data';
    public const DB_TBL_SELLER_PROD_DATA_PREFIX = 'sprodata_';
    
    public const DB_PROD_TBL = 'tbl_products';
    public const DB_PROD_TBL_PREFIX = 'product_';

    public const DB_TBL_LANG = 'tbl_seller_products_lang';
    public const DB_TBL_LANG_PREFIX = 'selprodlang_';

    public const DB_TBL_SELLER_PROD_OPTIONS = 'tbl_seller_product_options';
    public const DB_TBL_SELLER_PROD_OPTIONS_PREFIX = 'selprodoption_';

    public const DB_TBL_SELLER_PROD_SPCL_PRICE = 'tbl_product_special_prices';
    public const DB_TBL_SELLER_PROD_POLICY = 'tbl_seller_product_policies';

    public const DB_TBL_UPSELL_PRODUCTS = 'tbl_upsell_products';
    public const DB_TBL_UPSELL_PRODUCTS_PREFIX = 'upsell_';
    public const DB_TBL_RELATED_PRODUCTS = 'tbl_related_products';
    public const DB_TBL_RELATED_PRODUCTS_PREFIX = 'related_';

    public const DB_TBL_EXTERNAL_RELATIONS = 'tbl_seller_product_external_relations';
    public const DB_TBL_EXTERNAL_RELATIONS_PREFIX = 'sperel_';
    public const MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY = 9999;
    
    public const DB_TBL_SELLER_PROD_ADDON = 'tbl_seller_products_addon';
    public const DB_TBL_SELLER_PROD_ADDON_PREFIX = 'selprodaddon_';
    
    public const DB_TBL_SELLER_PRODUCT_MEMBERSHIP = 'tbl_seller_product_to_membership';

    public const DB_TBL_PRODUCT_TO_VERIFICATION_FLD = 'tbl_product_to_verification_field';       
    public const DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX = 'ptvf_';       
    
    public const DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS = 'tbl_seller_products_to_pickup_address';
    public const DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX = 'sptpa_';

    public const VOL_DISCOUNT_MIN_QTY = 2;
    public const VOL_DISCOUNT_MAX_QTY = 9999;

    public const UPDATE_OPTIONS_COUNT = 10;
    
    public const PRODUCT_TYPE_PRODUCT = 1;
    public const PRODUCT_TYPE_ADDON = 2;
    
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->objMainTableRecord->setSensitiveFields(
                array('selprod_id')
        );
    }

    public static function getSearchObject($langId = 0, $joinSpecifics = false, int $specificsType = applicationConstants::PRODUCT_FOR_RENT )
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'sp');

        if ($langId) {
            $srch->joinTable(
                    static::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.' . static::DB_TBL_LANG_PREFIX . 'selprod_id = sp.' . static::tblFld('id') . ' and
			sp_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'sp_l'
            );
        }

        if (true === $joinSpecifics) {
            $srch->joinTable(
                    SellerProductSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'sps.' . SellerProductSpecifics::DB_TBL_PREFIX . 'selprod_id = sp.' . static::tblFld('id') .' AND sps.selprod_specific_type = '. $specificsType, 'sps'
            );
        }

        $srch->joinTable(static::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'spd.' . static::DB_TBL_SELLER_PROD_DATA_PREFIX . 'selprod_id = sp.' . static::tblFld('id'), 'spd');
        return $srch;
    }

    public static function requiredGenDataFields()
    {
        $arr = array(
            ImportexportCommon::VALIDATE_INT => array(
                'selprod_max_download_times',
                'selprod_download_validity_in_days'
            ),
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'selprod_product_id',
                'selprod_stock',
                'selprod_min_order_qty',
                'selprod_condition'
            ),
            ImportexportCommon::VALIDATE_FLOAT => array(
                'selprod_price',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'credential_username',
                'selprod_subtract_stock',
                'selprod_track_inventory',
                'selprod_threshold_stock_level',
                'selprod_title',
                'selprod_url_keyword',
                'selprod_available_from',
            ),
        );

        if (FatApp::getConfig('CONF_PRODUCT_SKU_MANDATORY', FatUtility::VAR_INT, 1)) {
            $physical = array(
                'selprod_sku'
            );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        return $arr;
    }

    public static function validateGenDataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredGenDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredOptionDataFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprodoption_selprod_id',
                'option_id',
                'optionvalue_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'option_identifier',
                'optionvalue_identifier',
            ),
        );
    }

    public static function validateOptionDataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredOptionDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredSEODataFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
            ),
        );
    }

    public static function validateSEODataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredSEODataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredSplPriceFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'splprice_start_date',
                'splprice_end_date',
                'splprice_price',
            ),
        );
    }

    public static function validateSplPriceFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredSplPriceFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredVolDiscountFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'voldiscount_min_qty',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'voldiscount_percentage',
            ),
            ImportexportCommon::VALIDATE_FLOAT => array(
                'voldiscount_percentage',
            ),
        );
    }

    public static function validateVolDiscountFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredVolDiscountFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredBuyTogetherFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'upsell_recommend_sellerproduct_id',
            ),
        );
    }

    public static function validateBuyTogetherFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredBuyTogetherFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredRelatedProdFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'related_recommend_sellerproduct_id',
            ),
        );
    }

    public static function validateRelatedProdFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredRelatedProdFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredProdPolicyFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'sppolicy_ppoint_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'ppoint_identifier',
            ),
        );
    }

    public static function validateProdPolicyFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredProdPolicyFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function addUpdateSellerUpsellProducts($selprod_id, $upsellProds = array())
    {
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_UPSELL_PRODUCTS, array('smt' => static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id = ?', 'vals' => array($selprod_id)));
        if (empty($upsellProds)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_UPSELL_PRODUCTS);
        foreach ($upsellProds as $upsell_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id'] = $selprod_id;
            $to_save_arr[static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'recommend_sellerproduct_id'] = $upsell_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function addUpdateSellerRelatedProdcts($selprod_id, $relatedProds = array())
    {
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_RELATED_PRODUCTS, array('smt' => static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id = ?', 'vals' => array($selprod_id)));
        if (empty($relatedProds)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_RELATED_PRODUCTS);
        foreach ($relatedProds as $relprod_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id'] = $selprod_id;
            $to_save_arr[static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'recommend_sellerproduct_id'] = $relprod_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function addUpdatePickupAddressToSelprod($addr_id, $selprod_arr = array())
    {
        if (!$addr_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS, array('smt' => 'sptpa_addr_id = ?', 'vals' => array($addr_id)));
        
        if (empty($selprod_arr)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS);
        foreach ($selprod_arr as $selprod_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX . 'selprod_id'] = $selprod_id;
            $to_save_arr[static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX . 'addr_id'] = $addr_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public static function searchLinkedPickupAddresses($lang_id, $criteria = [])
    {
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS);
        $srch->joinTable(Address::DB_TBL,'INNER JOIN',Address::DB_TBL_PREFIX.'id =' . static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX . 'addr_id','ad' );
        $srch->joinTable(static::DB_TBL,'INNER JOIN',static::DB_TBL_PREFIX.'id = '.
        static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX.'selprod_id','sp');
        $srch->joinTable(static::DB_TBL_LANG,'INNER JOIN',static::DB_TBL_LANG_PREFIX.'selprod_id = sp.selprod_id AND selprodlang_lang_id = '. $lang_id, 'splang');

        if (!empty($criteria)) {
            if (is_string($criteria)) {
                $srch->addFld($criteria);
            } else {
                $srch->addMultipleFields($criteria);
            }
        } else {
            $srch->addMultipleFields(array('selprod_id', 'selprod_title', 'addr_address1', 'addr_address2','addr_city','addr_state_id','addr_country_id','addr_phone','addr_id'));
        }
        return $srch;
    }

    public static function getSelprodLinkedPickupAdd(int $selprodId)
    {
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX.'selprod_id', '=', $selprodId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs,'sptpa_addr_id');
    }
	
	public function getPickupAddress(int $langId =0, int $shopId = 0)
	{
		$srch = new SearchBase(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS);
		$srch->addMultipleFields(array('addr.*', 'state_code', 'country_code', 'IFNULL(country_name, country_code) as country_name', 'IFNULL(state_name, state_identifier) as state_name', 'country_code_alpha3'));
		$srch->joinTable(Address::DB_TBL, 'INNER JOIN', 'sptpa_addr_id = addr_id', 'addr');
		$srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'c.country_id = addr.addr_country_id', 'c');
		$srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 's.state_id = addr.addr_state_id', 's');
        if (0 < $langId) {
			$srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c.country_id = c_l.countrylang_country_id AND countrylang_lang_id = ' . $langId, 'c_l');
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.state_id = s_l.statelang_state_id AND s_l.statelang_lang_id = ' . $langId, 's_l');
        }
		
		$srch->doNotCalculateRecords();
		$srch->addCondition(static::DB_TBL_SELLER_PROD_TO_PICKUP_ADDRESS_PREFIX.'selprod_id', '=', $this->mainTableRecordId);
		
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetchAll($rs);
		if(!empty($result)) {
			return $result;
		} 

	}

    public function addUpdateVerificationField($product_id, $user_id, $verificationFlds = array())
    {
        if (!$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD, array('smt' => 'ptvf_product_id = ? AND ptvf_user_id = ?', 'vals' => array($product_id, $user_id)));
        if (empty($verificationFlds)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD);
        foreach ($verificationFlds as $vflds_id) {
            $to_save_arr = array();
            $to_save_arr[static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'product_id'] = $product_id;
            $to_save_arr[static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'user_id'] = $user_id;
            $to_save_arr[static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'vflds_id'] = $vflds_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public static function searchProductsVerificationFields($lang_id, $criteria = [])
    {
        $srch = new SearchBase(static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD);
        $srch->joinTable(VerificationFields::DB_TBL,'INNER JOIN',VerificationFields::DB_TBL_PREFIX.'id =' . static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'vflds_id' );
        
        $srch->joinTable(VerificationFields::DB_TBL_LANG,'LEFT OUTER JOIN', VerificationFields::DB_TBL_LANG_PREFIX.'vflds_id =' . VerificationFields::DB_TBL_PREFIX . 'id AND vfldslang_lang_id = '. $lang_id, 'vlang');
        
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', Product::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_PRODUCT_TO_VERIFICATION_FLD_PREFIX . 'product_id');
        $srch->joinTable(Product::DB_TBL . '_lang', 'LEFT JOIN', 'lang.productlang_product_id =  product_id AND productlang_lang_id = ' . $lang_id, 'lang');
        
        if (!empty($criteria)) {
            if (is_string($criteria)) {
                $srch->addFld($criteria);
            } else {
                $srch->addMultipleFields($criteria);
            }
        } else {
            $srch->addMultipleFields(array('product_id', 'IFNULL(product_name ,product_identifier) as product_name', 'IFNULL(vflds_name, vflds_identifier) as vflds_name', 'vflds_type','vflds_required','vflds_id','vflds_active'));
        }
        return $srch;
    }

    public static function getProductVerificationFldsData($product_id,$seller_id, $criteria = [])
	{
        if (1 > $product_id && 1 > $seller_id ) {
            return false;
        }

		$srch = static::searchProductsVerificationFields(CommonHelper::getLangId(),$criteria);
        $srch->addCondition('ptvf_product_id', '=', $product_id);
        $srch->addCondition('ptvf_user_id', '=', $seller_id);
        $srch->addCondition('vflds_active', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        if (!$row = $db->fetchAll($rs)) {
            return false;
        }
        return $row;
	}


    public static function searchUpsellProducts($lang_id, $attr = [], $addExtaJoins = true)
    {
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        $srch = new SearchBase(static::DB_TBL_UPSELL_PRODUCTS);
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'recommend_sellerproduct_id', 'selprod');
        $srch->joinTable(static::DB_TBL . '_lang', 'LEFT JOIN', 'slang.' . static::DB_TBL_LANG_PREFIX . 'selprod_id = ' . static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'recommend_sellerproduct_id AND ' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $lang_id, 'slang');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_PREFIX . 'product_id', 'p');
        $srch->joinTable(Product::DB_TBL . '_lang', 'LEFT JOIN', 'lang.productlang_product_id = p.' . Product::DB_TBL_PREFIX . 'id AND productlang_lang_id = ' . $lang_id, 'lang');
        
        $commonFlds = [
            'upsell_sellerproduct_id', 'selprod.selprod_id as selprod_id', 'p.product_id as product_id', 'IFNULL(selprod_title, IFNULL(lang.product_name, p.product_identifier)) as selprod_title', 'selprod.selprod_price as selprod_price', 'selprod.selprod_stock as selprod_stock', 'IFNULL(p.product_identifier ,lang.product_name) as product_name', 'p.product_identifier as product_identifier', 'selprod.selprod_product_id as selprod_product_id'
        ];
        
        $extraFlds = [];
        if ($addExtaJoins) {
            $srch->joinTable(
                SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'm.splprice_selprod_id = selprod.selprod_id AND \'' . $splPriceForDate . '\' BETWEEN m.splprice_start_date AND m.splprice_end_date', 'm'
            );

            $srch->joinTable(
                    SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 's.splprice_selprod_id = selprod.selprod_id AND s.splprice_price < m.splprice_price
                AND \'' . $splPriceForDate . '\' BETWEEN s.splprice_start_date AND s.splprice_end_date', 's'
            ); 
            
            $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = p.product_id', 'ptc');
            $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');
            $srch->joinTable(ProductCategory::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_lang.prodcatlang_prodcat_id = c.prodcat_id AND c_lang.prodcatlang_lang_id='. $lang_id , 'c_lang');
        
            if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
                $srch->joinTable(Brand::DB_TBL, 'INNER JOIN', 'p.product_brand_id = brand.brand_id and brand.brand_active = ' . applicationConstants::YES . ' and brand.brand_deleted = ' . applicationConstants::NO, 'brand');
            } else {
                $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'p.product_brand_id = brand.brand_id', 'brand');
                $srch->addDirectCondition("(CASE WHEN p.product_brand_id > 0 THEN (brand.brand_active = " . applicationConstants::YES . " AND brand.brand_deleted = " . applicationConstants::NO . ") ELSE 1=1 END)");
            }
            
            if ($lang_id > 0) {
                $srch->joinTable(Brand::DB_TBL_LANG,'LEFT OUTER JOIN', 'b_l.' . Brand::DB_TBL_LANG_PREFIX . 'brand_id = brand.' . Brand::tblFld('id') . ' and b_l.' . Brand::DB_TBL_LANG_PREFIX . 'lang_id = ' . $lang_id, 'b_l');
            }
            
            $srch->addCondition('c.prodcat_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('c.prodcat_deleted', '=', applicationConstants::NO);
            
            $extraFlds = [
                'CASE WHEN m.splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found',
                'IFNULL(m.splprice_price, selprod.selprod_price) AS theprice', 'selprod.selprod_min_order_qty as selprod_min_order_qty', 'p.product_updated_on as product_updated_on', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_id', 'prodcat_comparison', 'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'sprodata_rental_price as rent_price', 'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'sprodata_duration_type','p.product_model as product_model','p.product_brand_id as product_brand_id','IFNULL(brand_name, brand_identifier) as brand_name'
            ];
            
        }
        
        $srch->joinTable(static::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'spd.' . static::DB_TBL_SELLER_PROD_DATA_PREFIX . 'selprod_id = ' . static::DB_TBL_LANG_PREFIX . 'selprod_id', 'spd');
        
        /* [ JOINS TO CHECK MAIN SELLER PRODUCT CATALOG STATUS */
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'relSelprod.'.static::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id AND relSelprod.selprod_active = '. applicationConstants::YES, 'relSelprod');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN ', 'relCatlog.'.Product::DB_TBL_PREFIX . 'id = relSelprod.' . static::DB_TBL_PREFIX . 'product_id', 'relCatlog');
        
        $srch->addCondition('relCatlog.product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('relCatlog.product_active', '=', applicationConstants::YES);
        $srch->addCondition('relCatlog.product_approved', '=', applicationConstants::YES);
        /* ]*/
        if (!empty($attr)) {
            if (is_string($attr)) {
                $srch->addFld($attr);
            } else {
                $srch->addMultipleFields($attr);
            }
        } else {
            $srch->addMultipleFields(array_merge($commonFlds, $extraFlds));
        }
        /* $srch->addCondition('p.'.Product::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES); */
        $srch->addCondition('selprod.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('selprod.selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('p.product_approved', '=', applicationConstants::ACTIVE);
        $srch->addOrder('selprod.selprod_id', 'DESC');
        return $srch;
    }

    public function getUpsellProducts($sellProdId, $lang_id, $userId = 0)
    {
        $sellProdId = FatUtility::convertToType($sellProdId, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$sellProdId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $srch = static::searchUpsellProducts($lang_id);
        $srch->addCondition(static::DB_TBL_UPSELL_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $sellProdId);
        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if (true === MOBILE_APP_API_CALL) {
            if ($favVar == applicationConstants::NO) {
                $this->joinFavouriteProducts($srch, $userId);
                $srch->addFld('IFNULL(ufp_id, 0) as ufp_id');
            } else {
                $this->joinUserWishListProducts($srch, $userId);
                $srch->addFld('IFNULL(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
            }
        }
        $srch->addGroupBy('selprod_id');
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        if ($row = $db->fetchAll($rs)) {
            return $row;
        }
        return $data;
    }

    public static function getAttributesById($recordId, $attr = null, $fetchOptions = true, $joinSpecifics = false, $joinProduct = false, $type = applicationConstants::PRODUCT_FOR_SALE, $joinRentalDetails = false)
    {
        $recordId = FatUtility::int($recordId);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL, 'sp');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('id'), '=', $recordId);

        if (true === $joinSpecifics) {
            $srch->joinTable(
                    SellerProductSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'ps.' . SellerProductSpecifics::DB_TBL_PREFIX . 'selprod_id = sp.' . static::tblFld('id'). '  AND ps.selprod_specific_type ='. $type, 'ps'
            );
        }

        if (true == $joinProduct) {
            $srch->joinTable(
                    Product::DB_TBL, 'INNER JOIN', 'pro.' . Product::DB_TBL_PREFIX . 'id = sp.' . static::tblFld('product_id'), 'pro'
            );
        }
        if (true == $joinRentalDetails) {
           $srch->joinTable(static::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'spd.' . static::DB_TBL_SELLER_PROD_DATA_PREFIX . 'selprod_id = sp.' . static::tblFld('id'), 'spd');
        }
        
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }

        /* get seller product options[ */
        if ($fetchOptions) {
            $op = static::getSellerProductOptions($recordId, false);
            if (is_array($op) && count($op)) {
                foreach ($op as $o) {
                    $row['selprodoption_optionvalue_id'][$o['selprodoption_option_id']] = array($o['selprodoption_option_id'] => $o['selprodoption_optionvalue_id']);
                }
            }
        }
        /* ] */
        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public function addUpdateSellerProductOptions($selprod_id, $data)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (!$selprod_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }
        $db = FatApp::getDb();
        $db->deleteRecords(static::DB_TBL_SELLER_PROD_OPTIONS, array('smt' => static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id = ?', 'vals' => array($selprod_id)));
        if (is_array($data) && count($data)) {
            $record = new TableRecord(static::DB_TBL_SELLER_PROD_OPTIONS);
            foreach ($data as $option_id => $optionvalue_id) {
                $data_to_save = array(
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id' => $selprod_id,
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'option_id' => $option_id,
                    static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'optionvalue_id' => $optionvalue_id
                );
                $record->assignValues($data_to_save);
                if (!$record->addNew()) {
                    $this->error = $record->getError();
                    return false;
                }
            }
        }
        return true;
    }

    public static function getSellerProductOptions($selprod_id, $withAllJoins = true, $lang_id = 0, $option_id = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $lang_id = FatUtility::int($lang_id);
        $option_id = FatUtility::int($option_id);
        if (!$selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_OPTIONS, 'spo');

        if ($option_id) {
            $srch->addCondition(static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'option_id', '=', $option_id);
        }

        if ($withAllJoins) {
            if (!$lang_id) {
                trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
            }

            $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
            $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = ' . $lang_id, 'ov_lang');

            $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
            $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = ' . $lang_id, 'o_lang');
            $srch->addMultipleFields(array('o.option_id', 'ov.optionvalue_id', 'IFNULL(option_name, option_identifier) as option_name', 'IFNULL(optionvalue_name, optionvalue_identifier) as optionvalue_name'));
        }

        $srch->addCondition(static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id', '=', $selprod_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function getSellerProductOptionsBySelProdCode($selprod_code = '', $langId = 0, $displayInFilterOnly = false)
    {
        if ($selprod_code == '') {
            return array();
        }
        $opValArr = explode("_", $selprod_code);

        /* removing product_id from the begining of the array[ */
        $opValArr = array_reverse($opValArr);
        array_pop($opValArr);
        $opValArr = array_reverse($opValArr);
        if (empty($opValArr)) {
            return array();
        }
        /* ] */

        $srch = new SearchBase(OptionValue::DB_TBL, 'ov');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');

        if ($langId) {
            $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = ' . $langId, 'ov_lang');

            $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = ' . $langId, 'o_lang');
        }

        $srch->addCondition('optionvalue_id', 'IN', $opValArr);
        if ($displayInFilterOnly) {
            $srch->addCondition('option_display_in_filter', '=', applicationConstants::YES);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'optionvalue_id');
    }

    public static function getSellerProductSpecialPrices($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_SPCL_PRICE);
        $srch->addCondition('splprice_selprod_id', '=', $selprod_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('splprice_id');
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        return $db->fetchAll($rs);
    }

    public static function getSellerProductSpecialPriceById($splprice_id)
    {
        $splprice_id = FatUtility::int($splprice_id);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_SPCL_PRICE, 'prodSp');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'prodSp.splprice_selprod_id = slrPrd.selprod_id', 'slrPrd');
        $srch->addCondition('splprice_id', '=', $splprice_id);
        $srch->addMultipleFields(array('prodSp.*', 'slrPrd.selprod_id', 'slrPrd.selprod_user_id'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetch($rs);
    }

    public function deleteSellerProductSpecialPrice($splprice_id, $splprice_selprod_id, $userId = 0)
    {
        $splprice_id = FatUtility::int($splprice_id);
        $splprice_selprod_id = FatUtility::int($splprice_selprod_id);
        if (!$splprice_id || !$splprice_selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (0 < $userId) {
            $selProdUserId = SellerProduct::getAttributesById($splprice_selprod_id, 'selprod_user_id', false);
            if ($selProdUserId != $userId) {
                $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
                return false;
            }
        }
        $db = FatApp::getDb();
        $smt = 'splprice_id = ? AND splprice_selprod_id = ? ';
        $smtValues = array($splprice_id, $splprice_selprod_id);
        if (!$db->deleteRecords(static::DB_TBL_SELLER_PROD_SPCL_PRICE, array('smt' => $smt, 'vals' => $smtValues))) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function addUpdateSellerProductSpecialPrice($data, $return = false)
    {
        $db = FatApp::getDb();
        if (!$db->insertFromArray(static::DB_TBL_SELLER_PROD_SPCL_PRICE, $data, false, array(), $data)) {
            $this->error = $db->getError();
            return false;
        }
        if (true === $return) {
            if (!empty($data['splprice_id'])) {
                return $data['splprice_id'];
            }
            return FatApp::getDb()->getInsertId();
        }
        return true;
    }

    public static function getProductCommission(int $selprod_id, int $productType = applicationConstants::PRODUCT_FOR_SALE)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (!$selprod_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        //return 10;
        $sellerProductRow = static::getAttributesById($selprod_id, array('selprod_id', 'selprod_product_id', 'selprod_user_id'));
        $product_id = $sellerProductRow['selprod_product_id'];
        $selprod_user_id = $sellerProductRow['selprod_user_id'];

        $prodObj = new Product();
        $productCategories = $prodObj->getProductCategories($sellerProductRow['selprod_product_id']);
        $catIds = array();
        if ($productCategories) {
            foreach ($productCategories as $catId) {
                $catIds[] = $catId['prodcat_id'];
            }
        }

        /* to fetch the single row from the commission settings table, if single product is connected with multiple categories then will fetch the category according to price min or max, for now we have added min price i.e sort order of price is asc. [ */
        /* $srch = new SearchBase( Commission::DB_TBL );
          $srch->doNotCalculateRecords();
          $srch->addMultipleFields(array('commsetting_prodcat_id'));
          $srch->addCondition( 'commsetting_prodcat_id', 'IN', $catIds );
          $srch->addOrder('commsetting_fees', 'ASC');
          $srch->setPageSize(1);
          $rs = $srch->getResultSet();
          $row = FatApp::getDb()->fetch($rs);
          if( !$row ){
          $category_id = 0;
          } else {
          $category_id = $row['commsetting_prodcat_id'];
          } */
        /* ] */

        $db = FatApp::getDb();
        $sql = "SELECT commsetting_fees,
			CASE
				WHEN commsetting_product_id = '" . $product_id . "' AND commsetting_user_id = '" . $selprod_user_id . "' AND commsetting_prodcat_id IN (" . implode(",", $catIds) . ") THEN 10
  				WHEN commsetting_product_id = '" . $product_id . "' AND commsetting_user_id = '" . $selprod_user_id . "' AND commsetting_prodcat_id = '0' THEN 9
				WHEN commsetting_product_id = '" . $product_id . "' AND commsetting_user_id = 0 AND commsetting_prodcat_id IN (" . implode(",", $catIds) . ") THEN 8
				WHEN commsetting_product_id = '" . $product_id . "' AND commsetting_user_id = '0' AND commsetting_prodcat_id = '0' THEN 7

				WHEN commsetting_product_id = 0 AND commsetting_user_id = '" . $selprod_user_id . "' AND commsetting_prodcat_id IN (" . implode(",", $catIds) . ") THEN 6
				WHEN commsetting_product_id = 0 AND commsetting_user_id = '" . $selprod_user_id . "' AND commsetting_prodcat_id = 0 THEN 5

				WHEN commsetting_product_id = 0 AND commsetting_user_id = 0 AND commsetting_prodcat_id IN (" . implode(",", $catIds) . ") THEN 4

				WHEN (commsetting_product_id = '0' AND commsetting_user_id = '0' AND commsetting_prodcat_id = '0') THEN 1
			END
       		as matches FROM " . Commission::DB_TBL . " WHERE commsetting_deleted = 0 AND commsetting_type = " . $productType . "  order by matches desc, commsetting_fees desc  limit 0,1";
        $rs = $db->query($sql);
        if ($row = $db->fetch($rs)) {
            return $row['commsetting_fees'];
        }
    }

    public function getProductsToGroup($prodgroup_id, $lang_id = 0)
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $lang_id = FatUtility::int($lang_id);
        $now = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        $forDate = $now;

        if ($prodgroup_id <= 0) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $srch = new SearchBase(ProductGroup::DB_PRODUCT_TO_GROUP, 'ptg');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'ptg.' . ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(ProductGroup::DB_TBL, 'INNER JOIN', 'ptg.' . ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'prodgroup_id = pg.prodgroup_id', 'pg');
        $srch->joinTable(
                SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'splprice_selprod_id = selprod_id AND \'' . $forDate . '\' BETWEEN splprice_start_date AND splprice_end_date'
        );

        $srch->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'prodgroup_id', '=', $prodgroup_id);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('sp.selprod_available_from', '<=', $now);

        if ($lang_id > 0) {
            $srch->joinTable(static::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp.selprod_id = sp_l.selprodlang_selprod_id AND selprodlang_lang_id = ' . $lang_id, 'sp_l');
            $srch->addFld('selprod_title');

            $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND productlang_lang_id = ' . $lang_id, 'p_l');
            $srch->addFld('IFNULL(product_name, product_identifier) as product_name');
        }

        /* if( $selprod_id > 0 ){
          $srch->addCondition( ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id', '=', $selprod_id );
          } */

        $srch->addMultipleFields(array('selprod_id', 'product_id', 'IFNULL(splprice_price, selprod_price) AS theprice', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sold_count', 'CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found', 'ptg.ptg_is_main_product'));
        $srch->addOrder('ptg_is_main_product', 'DESC');
        $srch->addOrder('product_name');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $products = FatApp::getDb()->fetchAll($rs);
        return $products;
    }

    public function getGroupsToProduct($lang_id = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return array();
        }

        $lang_id = FatUtility::int($lang_id);

        $srch = new SearchBase(ProductGroup::DB_PRODUCT_TO_GROUP, 'ptg');
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'ptg.' . ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id = sp.selprod_id', 'sp');
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'p');
        $srch->joinTable(ProductGroup::DB_TBL, 'INNER JOIN', 'ptg.' . ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'prodgroup_id = pg.prodgroup_id', 'pg');

        $srch->addCondition(ProductGroup::DB_PRODUCT_TO_GROUP_PREFIX . 'selprod_id', '=', $this->mainTableRecordId);
        $srch->addCondition('pg.prodgroup_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        if ($lang_id > 0) {
            $srch->joinTable(ProductGroup::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pg.prodgroup_id = pg_l.prodgrouplang_prodgroup_id AND pg_l.prodgrouplang_lang_id = ' . $lang_id, 'pg_l');
            $srch->addFld('IFNULL(prodgroup_name, prodgroup_identifier) as prodgroup_name');
        }
        $srch->addMultipleFields(array('selprod_id', 'ptg_prodgroup_id', 'pg.prodgroup_price'));
        $srch->addOrder('pg.prodgroup_price');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function addPolicyPointToSelProd($data)
    {
        $record = new TableRecord(self::DB_TBL_SELLER_PROD_POLICY);
        $record->assignValues($data);

        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public static function searchRelatedProducts($lang_id, $criteria = array())
    {
        $srch = new SearchBase(static::DB_TBL_RELATED_PRODUCTS);
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', static::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'recommend_sellerproduct_id', 'selprod');
        $srch->joinTable(static::DB_TBL . '_lang', 'LEFT JOIN', 'slang.' . static::DB_TBL_LANG_PREFIX . 'selprod_id = ' . static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'recommend_sellerproduct_id AND ' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $lang_id, 'slang');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_PREFIX . 'product_id', 'p');
        $srch->joinTable(Product::DB_TBL . '_lang', 'LEFT JOIN', 'lang.productlang_product_id = ' . static::DB_TBL_LANG_PREFIX . 'selprod_id AND productlang_lang_id = ' . $lang_id, 'lang');
        
        $srch->addCondition('p.product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('p.product_active', '=', applicationConstants::YES);
        $srch->addCondition('p.product_approved', '=', applicationConstants::YES);
        
        
        /* [ JOINS TO CHECK MAIN SELLER PRODUCT CATALOG STATUS */
        $srch->joinTable(static::DB_TBL, 'INNER JOIN', 'relSelprod.'.static::DB_TBL_PREFIX . 'id = ' . static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', 'relSelprod');
        $srch->joinTable(Product::DB_TBL, 'LEFT JOIN ', 'relCatlog.'.Product::DB_TBL_PREFIX . 'id = relSelprod.' . static::DB_TBL_PREFIX . 'product_id', 'relCatlog');
        
        $srch->addCondition('relCatlog.product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('relCatlog.product_active', '=', applicationConstants::YES);
        $srch->addCondition('relCatlog.product_approved', '=', applicationConstants::YES);
        /* ]*/
        
        
        if (!empty($criteria)) {
            if (is_string($criteria)) {
                $srch->addFld($criteria);
            } else {
                $srch->addMultipleFields($criteria);
            }
        } else {
            $srch->addMultipleFields(array('related_sellerproduct_id', 'selprod.selprod_id as selprod_id', 'IFNULL(p.product_identifier, lang.product_name) as product_name', 'IFNULL(slang.selprod_title, IFNULL(lang.product_name, p.product_identifier)) as selprod_title', 'p.product_identifier as product_identifier', 'selprod.selprod_price as selprod_price', 'p.product_updated_on as product_updated_on'));
        }
        
        return $srch;
    }

    public function getRelatedProducts($lang_id = 0, $sellProdId = 0, $criteria = array())
    {
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        $sellProdId = FatUtility::convertToType($sellProdId, FatUtility::VAR_INT);

        $srch = static::searchRelatedProducts($lang_id, $criteria);
        if ($sellProdId > 0) {
            $srch->addCondition(static::DB_TBL_RELATED_PRODUCTS_PREFIX . 'sellerproduct_id', '=', $sellProdId);
        }
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        if ($sellProdId > 0) {
            return $db->fetchAll($rs, 'selprod_id');
        } else {
            return $db->fetchAll($rs);
        }
    }

    public function deleteSellerProduct($selprodId)
    {
        if (!$selprodId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        /* $sellerProdObj = new SellerProduct($selprodId);
        if (!$sellerProdObj->deleteRecord(true)) {
            $this->error = $sellerProdObj->getError();
            return false;
        }
        return true; */

        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX . 'deleted' => 1), array('smt' => static::DB_TBL_PREFIX . 'id = ?','vals' => array($selprodId)))) {
            $this->error = $db->getError();
            return false;
        }
        return true; 
    }

    public static function getSelprodPolicies($selprod_id, $policy_type, $langId, $limit = null, $active = true, $deleted = false)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $policy_type = FatUtility::int($policy_type);
        $limit = FatUtility::int($limit);
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_POLICY);
        $srch->joinTable(PolicyPoint::DB_TBL, 'left outer join', 'sppolicy_ppoint_id = ppoint_id', 'pp');
        $srch->joinTable(
                PolicyPoint::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pp_l.ppointlang_ppoint_id = pp.ppoint_id
			AND ppointlang_lang_id = ' . $langId, 'pp_l'
        );
        $srch->addCondition('pp.ppoint_type', '=', $policy_type);
        $srch->addCondition('sppolicy_selprod_id', '=', $selprod_id);
        $srch->addMultipleFields(array('ppoint_id', 'ifnull(ppoint_title,ppoint_identifier) ppoint_title'));
        $srch->doNotCalculateRecords();
        $srch->addOrder('pp.ppoint_display_order');
        if ($deleted == false) {
            $srch->addCondition('pp.ppoint_deleted', '=', applicationConstants::NO);
        }

        if ($active == true) {
            $srch->addCondition('pp.ppoint_active', '=', applicationConstants::ACTIVE);
        }

        if ($limit) {
            $srch->setPageSize($limit);
        }
        return FatApp::getDb()->fetch($srch->getResultSet());
    }

    public static function getProductDisplayTitle($selProdId, $langId, $toHtml = false)
    {
        $prodSrch = new ProductSearch($langId, null, null, false, false);
        $prodSrch->joinSellerProducts(0, '', array(), false, false);
        if (is_array($selProdId) && 0 < count($selProdId)) {
            $prodSrch->addCondition('selprod_id', 'IN', $selProdId);
        } else {
            $prodSrch->addCondition('selprod_id', '=', $selProdId);
        }
        $prodSrch->addMultipleFields(array('selprod_id', 'product_id', 'product_identifier', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title'));
        $prodSrch->addGroupBy('selprod_id');
        $productRs = $prodSrch->getResultSet();
        $products = FatApp::getDb()->fetchAll($productRs, 'selprod_id');

        $productTitle = SellerProduct::getProductsOptionsString($products, $langId, $toHtml);
        if (false == $productTitle) {
            return $productTitle;
        }

        if (!is_array($selProdId) && array_key_exists($selProdId, $productTitle)) {
            return $productTitle[$selProdId];
        }

        if (is_array($selProdId)) {
            return $productTitle;
        }

        return false;
    }

    public static function getProductsOptionsString($products, $langId, $toHtml = false)
    {
        if (empty($products) || empty($langId)) {
            return false;
        }
        $optionsStringArr = array();
        foreach ($products as $selProdId => $product) {
            $variantStr = (!empty($product['selprod_title'])) ? $product['selprod_title'] : $product['product_name'];

            $options = static::getSellerProductOptions($selProdId, true, $langId);
            if (is_array($options) && count($options)) {
                $variantStr .= (true === $toHtml) ? '<br/>' : ' - ';
                $counter = 1;
                foreach ($options as $op) {
                    $variantStr .= (true === $toHtml) ? $op['option_name'] . ': ' . $op['optionvalue_name'] : $op['optionvalue_name'];
                    if ($counter != count($options)) {
                        $variantStr .= (true === $toHtml) ? '<br/>' : ' - ';
                    }
                    $counter++;
                }
            }
            $optionsStringArr[$selProdId] = $variantStr;
        }
        return $optionsStringArr;
    }

    public function getVolumeDiscounts()
    {
        if ($this->mainTableRecordId < 1) {
            return array();
        }

        $srch = new SellerProductVolumeDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('voldiscount_min_qty', 'voldiscount_percentage'));
        $srch->addCondition('voldiscount_selprod_id', '=', $this->mainTableRecordId);
        $srch->addOrder('voldiscount_min_qty', 'ASC');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    private function rewriteUrl($keyword, $type = 'product')
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }        
        
        $originalUrl = $this->getRewriteOriginalUrl($type);        
        $seoUrl = $this->sanitizeSeoUrl($keyword,$type);        
        
        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);
        return UrlRewrite::update($originalUrl, $customUrl);
    }    
    
    private function getRewriteOriginalUrl($type = 'product')
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }
        
        switch (strtolower($type)) {
            case 'reviews':
                $originalUrl = Product::PRODUCT_REVIEWS_ORGINAL_URL . $this->mainTableRecordId;
                break;
            case 'moresellers':
                $originalUrl = Product::PRODUCT_MORE_SELLERS_ORGINAL_URL . $this->mainTableRecordId;
                break;
            default:
                $originalUrl = Product::PRODUCT_VIEW_ORGINAL_URL . $this->mainTableRecordId;
                break;
        }
        return $originalUrl;
    }

    public function sanitizeSeoUrl($keyword, $type = 'product')
    {
        $seoUrl = CommonHelper::seoUrl($keyword);
        switch (strtolower($type)) {
            case 'reviews':
                $seoUrl = preg_replace('/-reviews$/', '', $seoUrl);
                $seoUrl .= '-reviews';
                break;
            case 'moresellers':
                $seoUrl = preg_replace('/-sellers$/', '', $seoUrl);
                $seoUrl .= '-sellers';
                break;
            default:
                break;
        }
        return $seoUrl;
    }

    public function rewriteUrlProduct($keyword)
    {
        return $this->rewriteUrl($keyword, 'product');
    }
    
    public function getRewriteProductOriginalUrl()
    {
        return $this->getRewriteOriginalUrl('product');
    }

    public function rewriteUrlReviews($keyword)
    {
        return $this->rewriteUrl($keyword, 'reviews');
    }

    public function rewriteUrlMoreSellers($keyword)
    {
        return $this->rewriteUrl($keyword, 'moresellers');
    }

    /* private function rewriteUrl($keyword, $type = 'product')
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $keyword = preg_replace('/-' . $this->mainTableRecordId . '$/', '', $keyword);
        $seoUrl = CommonHelper::seoUrl($keyword);

        switch (strtolower($type)) {
            case 'reviews':
                $originalUrl = Product::PRODUCT_REVIEWS_ORGINAL_URL . $this->mainTableRecordId;
                $seoUrl = preg_replace('/-reviews$/', '', $seoUrl);
                $seoUrl .= '-reviews';
                break;
            case 'moresellers':
                $originalUrl = Product::PRODUCT_MORE_SELLERS_ORGINAL_URL . $this->mainTableRecordId;
                $seoUrl = preg_replace('/-sellers$/', '', $seoUrl);
                $seoUrl .= '-sellers';
                break;
            default:
                $originalUrl = Product::PRODUCT_VIEW_ORGINAL_URL . $this->mainTableRecordId;
                break;
        }

        $seoUrl .= '-' . $this->mainTableRecordId;

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl);
        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public function rewriteUrlProduct($keyword)
    {
        return $this->rewriteUrl($keyword, 'product');
    }

    public function rewriteUrlReviews($keyword)
    {
        return $this->rewriteUrl($keyword, 'reviews');
    }

    public function rewriteUrlMoreSellers($keyword)
    {
        return $this->rewriteUrl($keyword, 'moresellers');
    } */

    public static function getActiveCount($userId, $selprodId = 0)
    {
        $selprodId = FatUtility::int($selprodId);
        $userId = FatUtility::int($userId);

        $srch = static::getSearchObject();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id and p.product_deleted = ' . applicationConstants::NO . ' and p.product_active = ' . applicationConstants::YES, 'p');

        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_user_id', '=', $userId);
        if ($selprodId) {
            $srch->addCondition('selprod_id', '!=', $selprodId);
        }

        $srch->addMultipleFields(array('selprod_id'));
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $records = $db->fetchAll($rs);
        return $srch->recordCount();
    }

    public function joinUserWishListProducts($srch, $user_id)
    {
        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotCalculateRecords();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $user_id);
        $wislistPSrchObj->addMultipleFields(array('uwlp_selprod_id', 'uwlp_uwlist_id'));
        $wishListSubQuery = $wislistPSrchObj->getQuery();
        $srch->joinTable('(' . $wishListSubQuery . ')', 'LEFT OUTER JOIN', 'uwlp.uwlp_selprod_id = selprod_id', 'uwlp');
    }

    public function joinFavouriteProducts($srch, $user_id)
    {
        $srch->joinTable(Product::DB_TBL_PRODUCT_FAVORITE, 'LEFT OUTER JOIN', 'ufp.ufp_selprod_id = selprod_id and ufp.ufp_user_id = ' . $user_id, 'ufp');
    }

    public static function specialPriceForm($langId, $productFor = Product::PRODUCT_FOR_RENT)
    {
        $frm = new Form('frmSellerProductSpecialPrice');
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Special_Price', $langId) . CommonHelper::concatCurrencySymbolWithAmtLbl(), 'splprice_price');
        $fld->requirements()->setPositive();
        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_Start_Date', $langId), 'splprice_start_date', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $fld->requirements()->setRequired();

        $fld = $frm->addDateField(Labels::getLabel('LBL_Price_End_Date', $langId), 'splprice_end_date', '', array('readonly' => 'readonly', 'class' => 'field--calender'));
        $fld->requirements()->setRequired();
        $fld->requirements()->setCompareWith('splprice_start_date', 'ge', Labels::getLabel('LBL_Price_Start_Date', $langId));

        $frm->addHiddenField('', 'splprice_selprod_id');
        $frm->addHiddenField('', 'splprice_id');
        $frm->addHiddenField('', 'product_for', $productFor);
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $langId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public static function volumeDiscountForm($langId)
    {
        $frm = new Form('frmSellerProductSpecialPrice');

        $frm->addHiddenField('', 'voldiscount_selprod_id', 0);
        $frm->addHiddenField('', 'voldiscount_id', 0);
        $qtyFld = $frm->addIntegerField(Labels::getLabel("LBL_Minimum_Purchase_Quantity", $langId), 'voldiscount_min_qty');
        //$qtyFld->requirements()->setRange(self::VOL_DISCOUNT_MIN_QTY, self::VOL_DISCOUNT_MAX_QTY);
        $discountFld = $frm->addFloatField(Labels::getLabel("LBL_Discount_in_(%)", $langId), "voldiscount_percentage");
        $discountFld->requirements()->setPositive();
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $langId), array('onClick' => 'javascript:$("#sellerProductsForm").html(\'\')'));
        $fld1->attachField($fld2);
        return $frm;
    }

    public static function searchSpecialPriceProductsObj($langId, $selProdId = 0, $keyword = '', $userId = 0, $productFor = Product::PRODUCT_FOR_SALE)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = static::getSearchObject($langId);

        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->joinTable(static::DB_TBL_SELLER_PROD_SPCL_PRICE, 'INNER JOIN', 'spp.splprice_selprod_id = sp.selprod_id', 'spp');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $langId, 'p_l');

        $srch->addMultipleFields(
                array(
                    'selprod_id', 'credential_username', 'selprod_price', 'sprodata_rental_price', 'date(splprice_start_date) as splprice_start_date', 'splprice_end_date', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'splprice_id', 'splprice_price'
                )
        );

        if (0 < $selProdId) {
            $srch->addCondition('selprod_id', '=', $selProdId);
        }
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        if (0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }

        
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        if ($productFor == Product::PRODUCT_FOR_SALE) {
            $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        } else {
            $srch->addCondition('sprodata_rental_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        $srch->addCondition('splprice_type', '=', $productFor);
        $srch->addOrder('splprice_id', 'DESC');
        $srch->setPageSize($pageSize);
        return $srch;
    }

    public static function searchVolumeDiscountProducts($langId, $selProdId = 0, $keyword = '', $userId = 0)
    {
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $srch = static::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tuc.credential_user_id = sp.selprod_user_id', 'tuc');
        $srch->joinTable(SellerProductVolumeDiscount::DB_TBL, 'INNER JOIN', 'vd.voldiscount_selprod_id = sp.selprod_id', 'vd');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $langId, 'p_l');
        $srch->addMultipleFields(
                array(
                    'selprod_id', 'credential_username', 'voldiscount_min_qty', 'voldiscount_percentage', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_title', 'voldiscount_id'
                )
        );

        if (0 < $selProdId) {
            $srch->addCondition('selprod_id', '=', $selProdId);
        }


        if ($keyword != '') {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        if (0 < $userId) {
            $srch->addCondition('selprod_user_id', '=', $userId);
        }

        $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->setPageSize($pageSize);
        $srch->addOrder('voldiscount_id', 'DESC');
        return $srch;
    }

    public static function getSelProdDataById($selProdId, $langId = 0)
    {
        $srch = static::getSearchObject($langId);
        $srch->addCondition('selprod_id', '=', $selProdId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public static function searchSellerProducts($langId, $userId, $keyword = '', $productType = applicationConstants::PRODUCT_FOR_SALE)
    {
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id and p.product_deleted = ' . applicationConstants::NO . ' and p.product_active = ' . applicationConstants::YES, 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $langId, 'p_l');
        if ($keyword) {
            $cnd = $srch->addCondition('product_name', 'like', "%$keyword%");
            $cnd->attachCondition('selprod_title', 'LIKE', "%$keyword%");
            $cnd->attachCondition('product_identifier', 'LIKE', "%$keyword%");
        }
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
            $srch->addOrder('selprod_active', 'DESC');
        } else {
            $srch->addOrder('sprodata_rental_active', 'DESC');
        }
        
        $srch->addOrder('selprod_added_on', 'DESC');
        $srch->addOrder('selprod_id', 'DESC');
        $srch->addOrder('product_name');
        $srch->addCondition('selprod_user_id', '=', $userId);
        return $srch;
    }

    public function saveMetaData()
    {
        if ($this->mainTableRecordId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }
        $selprod_id = $this->mainTableRecordId;
        $metaData = array();
        $tabsArr = MetaTag::getTabsArr();
        $metaType = MetaTag::META_GROUP_PRODUCT_DETAIL;

        if ($metaType == '' || !isset($tabsArr[$metaType])) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }

        $metaData['meta_controller'] = $tabsArr[$metaType]['controller'];
        $metaData['meta_action'] = $tabsArr[$metaType]['action'];
        $metaData['meta_record_id'] = $selprod_id;
        $metaData['meta_subrecord_id'] = 0;

        $metaIdentifier = static::getProductDisplayTitle($selprod_id, FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1));

        $meta = new MetaTag();
        $meta->assignValues($metaData);

        if (!$meta->save()) {
            $this->error = $meta->getError();
            return false;
        }
        $metaId = $meta->getMainTableRecordId();
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $selProdMeta = array(
                'metalang_lang_id' => $langId,
                'metalang_meta_id' => $metaId,
                'meta_title' => static::getProductDisplayTitle($selprod_id, $langId),
            );

            $metaObj = new MetaTag($metaId);

            if (!$metaObj->updateLangData($langId, $selProdMeta)) {
                $this->error = $metaObj->getError();
                return false;
            }
        }
        return true;
    }

    public static function getCatelogFromProductId($productId)
    {
        $productId = FatUtility::int($productId);
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->addCondition('selprod_deleted', '=', 0);
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addFld('selprod_id');
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record;
        }
        return [];
    }

    public static function prodShipByseller($productId)
    {
        $productId = FatUtility::int($productId);
        $loggedUserId = UserAuthentication::getLoggedUserId();
        $srch = new ProductSearch();
        $srch->joinProductShippedBySeller($loggedUserId);
        $srch->addCondition('psbs_user_id', '=', $loggedUserId);
        $srch->addCondition('product_id', '=', $productId);
        $srch->addFld('psbs_user_id');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    /**
     * setSellerProdFulfillmentType - Need to enhance later.
     *
     * @param  int $fulfillmentType
     * @return int
     */
    public static function setSellerProdFulfillmentType(int $fulfillmentType): int
    {
        return $fulfillmentType;
    }

    public static function isProductRental(int $selprod_id): bool
    {
        if (!$selprod_id) {
            return false;
        }
        $srch = self::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprod_id);
        $rs = $srch->getResultSet();
        $prodRentalData = FatApp::getDb()->fetch($rs);
        if (!empty($prodRentalData) && $prodRentalData['sprodata_is_for_rent'] > 0) {
            return true;
        }
        return false;
    }

    public static function isProductSale(int $selprod_id): bool
    {
        if (!$selprod_id) {
            return false;
        }
        $srch = self::getSearchObject();
        $srch->addCondition('sprodata_selprod_id', '=', $selprod_id);
        $rs = $srch->getResultSet();
        $prodData = FatApp::getDb()->fetch($rs);
        if (!empty($prodData) && $prodData['sprodata_is_for_sell'] > 0) {
            return true;
        }
        return false;
    }

    public function getAddonProducts(int $lang_id, bool $returnIds = false, $selprodIds = []): array
    {
        $srch = new SearchBase(static::DB_TBL_SELLER_PROD_ADDON);
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spa_addon_product_id and sp.selprod_active = ' . applicationConstants::YES, 'sp');
        $srch->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', SellerProduct::DB_TBL_LANG_PREFIX . 'selprod_id = selprod_id AND ' . SellerProduct::DB_TBL_LANG_PREFIX . 'lang_id = ' . $lang_id);
        
        if (!empty($selprodIds)) {
            $srch->addCondition('spa_seller_prod_id', 'IN', $selprodIds);
        } else {
            $srch->addCondition('spa_seller_prod_id', '=', $this->mainTableRecordId);
        }
        
        $srch->addCondition('selprod_type', '=', static::PRODUCT_TYPE_ADDON);
        $srch->addMultipleFields(array('spa_seller_prod_id','spa_addon_product_id','selprod_id','selprod_user_id', 'IFNULL(selprod_title, selprod_identifier) as selprod_title','selprod_price', 'selprod_rental_terms'));	
        
        $data = FatApp::getDb()->fetchAll($srch->getResultSet());
        if ($returnIds) {
            return (empty($data)) ? [] : array_column($data, 'selprod_id');
        }
        
        if (!empty($selprodIds)) {
            $formatedArr = [];
            foreach ($data as $dataVal) {
                $formatedArr[$dataVal['spa_seller_prod_id']][] = $dataVal;
            }
            $data = $formatedArr;
        }
        return $data;
    }

    public function updateAddonToProduct(array $selectedSelProducts, int $addonProdId): bool
    {
        if (empty($selectedSelProducts) || $addonProdId < 0) {
            $this->error = Labels::getLabel('pleaseSelectAddonAndProducts');
            return false;
        }
        $db = FatApp::getDb();
        foreach ($selectedSelProducts as $product_id) {
            $dataToUpdate = array(
                'spa_seller_prod_id' => $product_id,
                'spa_addon_product_id' => $addonProdId
            );
            if (!$db->insertFromArray(static::DB_TBL_SELLER_PROD_ADDON, $dataToUpdate, false, array(), $dataToUpdate)) {
                $this->error = $db->getError();
                return FALSE;
            }
        }
        return TRUE;
    }

    public function deleteAttachedAddonProduct(int $addonProdId, int $selProdId): bool
    {
        if ($addonProdId <= 0 || $selProdId <= 0) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', CommonHelper::getLangId());
            return false;
        }
        $db = FatApp::getDb();
        if (!$db->deleteRecords(static::DB_TBL_SELLER_PROD_ADDON, array('smt' => 'spa_seller_prod_id = ? AND spa_addon_product_id = ?', 'vals' => array($selProdId, $addonProdId)))) {
            $this->error = $db->getError();
            return FALSE;
        }
        return true;
    }

    public static function validateAddonsGenDataFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredAddonsGenDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredAddonsGenDataFields()
    {
        $arr = array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'selprod_product_id',
                'selprod_price',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'selprod_title',
                'credential_username',
                'selprod_url_keyword',
                'selprod_available_from',
            ),
        );

        if (FatApp::getConfig('CONF_PRODUCT_SKU_MANDATORY', FatUtility::VAR_INT, 1)) {
            $physical = array(
                'selprod_sku'
            );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        return $arr;
    }

    public static function requiredMediaFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_title',
                'afile_physical_path',
                'afile_name',
            ),
        );
    }

    public static function validateAddonMediaFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredMediaFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredAddonProdFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'spa_addon_product_id',
            ),
        );
    }

    public static function validateAddonProdFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredAddonProdFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function updateMembershipDetails(array $planIds, int $langId): bool
    {
        if ($this->mainTableRecordId < 1 || empty($planIds)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $langId);
            return false;
        }
        
        $whr = [
                'smt' => 'spm_selprod_id= ?',
                'vals' => [$this->mainTableRecordId]
        ];
        
        if (!FatApp::getDb()->deleteRecords(static::DB_TBL_SELLER_PRODUCT_MEMBERSHIP, $whr)) {
            $this->error = Labels::getLabel('ERR_' . FatApp::getDb()->getError(), $langId);
            return false;
        }
        
        foreach ($planIds as $planId) {
            $dataToSave = [
                'spm_selprod_id' => $this->mainTableRecordId,
                'spm_membership_id' => $planId
            ];
            
            if (!FatApp::getDb()->insertFromArray(static::DB_TBL_SELLER_PRODUCT_MEMBERSHIP, $dataToSave, false, array(), $dataToSave)) {
                $this->error = Labels::getLabel('ERR_' . FatApp::getDb()->getError(), $langId);
                return false;
            }
        }
        return true;
    }
    
    public static function getMembershipPlanBySelprod(int $selprodId)
    {
        $srch = new searchBase(static::DB_TBL_SELLER_PRODUCT_MEMBERSHIP, 'spm');
        $srch->addCondition('spm_selprod_id', '=', $selprodId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function selProdType($langId = 1) 
    {
        return array(
            static::PRODUCT_TYPE_PRODUCT => Labels::getLabel('LBL_Product', $langId),
            static::PRODUCT_TYPE_ADDON => Labels::getLabel('LBL_Addon', $langId),
        );
    }

    public function getProdDetail($selProdId, $langId)	
	{	
		$selProdId = intval($selProdId);	
		if (1 > $selProdId) {	
		    return false;	
		}	
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');	
        	
		$srch = static::getSearchObject();	
		$srch->joinTable( Product::DB_TBL, 'INNER JOIN', 'selprod_product_id = product_id');	
        $srch->joinTable( Product::DB_TBL_LANG, 'LEFT JOIN',	
			'productlang_product_id = product_id	
			AND productlang_lang_id = ' . $langId, 'tp_l');	
        $srch->joinTable( SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT JOIN',	
            'm.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN m.splprice_start_date AND m.splprice_end_date', 'm');	
        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'ptc.ptc_product_id = product_id', 'ptc');	
        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'c');	
            	
        $srch->joinTable( SellerProduct::DB_TBL_LANG ,'LEFT JOIN', 'selprod_id = sprods_l.selprodlang_selprod_id AND sprods_l.selprodlang_lang_id = '.$langId, 'sprods_l');   	
        	
		$srch->addCondition( 'selprod_id', '=', $selProdId);	
		$srch->addMultipleFields( array('prodcat_id as product_spec_cat_id','prodcat_comparison','selprod_product_id','IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'selprod_price', 'product_name', 'CASE WHEN m.splprice_selprod_id IS NULL THEN 0 ELSE 1 END AS special_price_found', 'IFNULL(m.splprice_price, selprod_price) AS theprice','sprodata_rental_price','sprodata_duration_type') );	
		$rs = $srch->getResultSet();	
        //echo $srch->getQuery();	
        return FatApp::getDb()->fetch($rs);	
	}	
    	
    public static function getSellersProductOptions($selProdIds)	
    {	
        if (empty($selProdIds)) {	
            return false;	
        }	
        	
		$srch = new SearchBase(static::DB_TBL_SELLER_PROD_OPTIONS, 'spo');	
		$srch->addCondition( static::DB_TBL_SELLER_PROD_OPTIONS_PREFIX . 'selprod_id', 'IN', $selProdIds );	
		$srch->doNotCalculateRecords();	
		$srch->doNotLimitRecords();	
		$rs = $srch->getResultSet();	
		return FatApp::getDb()->fetchAll($rs);	
	}	
    
    public function saveProductLangData($langData) // $langData => POSTED DATA
    {
        if ($this->mainTableRecordId < 1 || empty($langData)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $autoUpdateOtherLangsData = isset($langData['auto_update_other_langs_data']) ? FatUtility::int($langData['auto_update_other_langs_data']) : 0;
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        /* [ update default language data */
        if (isset($langData['selprod_title'. $siteDefaultLangId])) {
            $data = array(
                static::DB_TBL_LANG_PREFIX . 'selprod_id' => $this->mainTableRecordId,
                static::DB_TBL_LANG_PREFIX . 'lang_id' => $siteDefaultLangId,
                'selprod_title' => $langData['selprod_title'. $siteDefaultLangId],
                'selprod_comments' => $langData['selprod_comments' . $siteDefaultLangId],
                'selprod_rental_terms' => $langData['selprod_rental_terms'.$siteDefaultLangId],
            );
            if (!$this->updateLangData($siteDefaultLangId, $data)) {
                $this->error = $this->getError();
                return false;
            }
        }
        /* ] */
        
        $languages = Language::getAllNames();
        unset($languages[$siteDefaultLangId]);
        if (!empty($languages)) {
            foreach($languages as $langId => $langName) {
                if ($autoUpdateOtherLangsData > 0) {
                    $this->saveTranslatedProductLangData($langId);
                } else {
                    $data = array(
                        static::DB_TBL_LANG_PREFIX . 'selprod_id' => $this->mainTableRecordId,
                        static::DB_TBL_LANG_PREFIX . 'lang_id' => $langId,
                        'selprod_title' => $langData['selprod_title'. $langId],
                        'selprod_comments' => $langData['selprod_comments' . $langId],
                        'selprod_rental_terms' => $langData['selprod_rental_terms'.$langId],
                    );
                    if (!$this->updateLangData($langId, $data)) {
                        $this->error = $this->getError();
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    
    public function saveTranslatedProductLangData($langId)
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $translateLangobj = new TranslateLangData(static::DB_TBL_LANG);
        if (false === $translateLangobj->updateTranslatedData($this->mainTableRecordId, 0, $langId)) {
            $this->error = $translateLangobj->getError();
            return false;
        }
        return true;
    }
    	
}

