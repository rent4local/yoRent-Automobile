<?php

class Importexport extends ImportexportCommon
{

    public const DB_TBL_SETTINGS = 'tbl_import_export_settings';
    public const DB_TBL_TEMP_SELPROD_IDS = 'tbl_seller_products_temp_ids';
    public const DB_TBL_TEMP_PRODUCT_IDS = 'tbl_products_temp_ids';

    public const TYPE_CATEGORIES = 1;
    public const TYPE_BRANDS = 2;
    public const TYPE_PRODUCTS = 3;
    public const TYPE_INVENTORIES = 4;
    public const TYPE_OPTIONS = 5;
    public const TYPE_OPTION_VALUES = 6;
    public const TYPE_TAG = 7;
    public const TYPE_COUNTRY = 8;
    public const TYPE_STATE = 9;
    public const TYPE_POLICY_POINTS = 10;
    public const TYPE_USERS = 11;
    public const TYPE_TAX_CATEGORY = 12;
    public const TYPE_LANGUAGE_LABELS = 13;
    public const TYPE_INVENTORY_UPDATE = 14;
    public const TYPE_SELLER_PRODUCTS = 15;
    public const TYPE_CUSTOM_FIELDS = 16;
    public const TYPE_ADDONS = 17;


    public const MAX_LIMIT = 1000;

    public const PRODUCT_CATALOG = 1;
    public const PRODUCT_OPTION = 2;
    public const PRODUCT_TAG = 3;
    public const PRODUCT_SPECIFICATION = 4;
    public const PRODUCT_SHIPPING = 5;
    public const PRODUCT_CUSTOM_FIELDS = 14;

    public const LABEL_OPTIONS = 1;
    public const LABEL_OPTIONS_VALUES = 2;

    public const SELLER_PROD_GENERAL_DATA = 6;
    public const SELLER_PROD_OPTION = 7;
    public const SELLER_PROD_SEO = 8;
    public const SELLER_PROD_SPECIAL_PRICE = 9;
    public const SELLER_PROD_VOLUME_DISCOUNT = 10;
    public const SELLER_PROD_BUY_TOGTHER = 11;
    public const SELLER_PROD_RELATED_PRODUCT = 12;
    public const SELLER_PROD_POLICY = 13;
    public const ADDON_GENERAL_DATA = 14;
    public const SELLER_PROD_DURATION_DISCOUNT = 15;
    public const SELLER_PROD_UNAVAILABLE_DATES = 16;
    public const SELLER_PROD_ADDON_PRODUCTS = 17;

    public const BY_ID_RANGE = 1;
    public const BY_BATCHES = 2;

    private $headingIndexArr = array();
    private $CSVfileObj;

    public const ACTION_ALL_PRODUCTS = 1;
    public const ACTION_ADMIN_PRODUCTS = 2;
    public const ACTION_SELLER_PRODUCTS = 3;

    private $actionType = self::ACTION_ALL_PRODUCTS;
    
    public static function getImportExportTypeArr($type, $langId, $sellerDashboard = false)
    {
        switch (strtoupper($type)) {
            case 'EXPORT':
                $arr[static::TYPE_CATEGORIES] = Labels::getLabel('LBL_Categories', $langId);
                $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_Marketplace_Products', $langId);
                $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_Seller_Products', $langId);
                $arr[static::TYPE_INVENTORIES] = Labels::getLabel('LBL_Seller_Inventories', $langId);
                // $arr[static::TYPE_INVENTORY_UPDATE] = Labels::getLabel('LBL_INVENTORY_UPDATE', $langId);
                $arr[static::TYPE_BRANDS] = Labels::getLabel('LBL_Brands', $langId);
                $arr[static::TYPE_OPTIONS] = Labels::getLabel('LBL_Options', $langId);
                $arr[static::TYPE_OPTION_VALUES] = Labels::getLabel('LBL_Option_Values', $langId);
                //$arr[static::TYPE_TAG] = Labels::getLabel('LBL_Tags', $langId);
                $arr[static::TYPE_COUNTRY] = Labels::getLabel('LBL_Countries', $langId);
                $arr[static::TYPE_STATE] = Labels::getLabel('LBL_States', $langId);
                //$arr[static::TYPE_POLICY_POINTS] = Labels::getLabel('LBL_Policy_Points', $langId);
                $arr[static::TYPE_TAX_CATEGORY] = Labels::getLabel('LBL_Tax_Category', $langId);
                if (FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
                    $arr[static::TYPE_CUSTOM_FIELDS] = Labels::getLabel('LBL_Custom_Fields', $langId);
                }
                if (!$sellerDashboard) {
                    $arr[static::TYPE_USERS] = Labels::getLabel('LBL_users', $langId);
                    $arr[static::TYPE_LANGUAGE_LABELS] = Labels::getLabel('LBL_Language_Labels', $langId);
                    $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_My_Products', $langId);
                } else {
                    $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_My_Products', $langId);
                    if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 1) {
                        $arr[static::TYPE_ADDONS] = Labels::getLabel('LBL_RENTAL_SERVICES(FOR_RENTAL_PRODUCTS)', $langId);
                    }
                }
                break;
            case 'IMPORT':
                $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_Marketplace_Products', $langId);
                $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_Seller_Products', $langId);
                $arr[static::TYPE_INVENTORIES] = Labels::getLabel('LBL_Seller_Inventories', $langId);
                // $arr[static::TYPE_INVENTORY_UPDATE] = Labels::getLabel('LBL_INVENTORY_UPDATE', $langId);
                if (!$sellerDashboard) {
                    $arr[static::TYPE_CATEGORIES] = Labels::getLabel('LBL_Categories', $langId);
                    $arr[static::TYPE_BRANDS] = Labels::getLabel('LBL_Brands', $langId);
                    $arr[static::TYPE_OPTIONS] = Labels::getLabel('LBL_Options', $langId);
                    $arr[static::TYPE_OPTION_VALUES] = Labels::getLabel('LBL_Option_Values', $langId);
                    //$arr[static::TYPE_TAG] = Labels::getLabel('LBL_Tags', $langId);
                    $arr[static::TYPE_COUNTRY] = Labels::getLabel('LBL_Countries', $langId);
                    $arr[static::TYPE_STATE] = Labels::getLabel('LBL_States', $langId);
                    $arr[static::TYPE_LANGUAGE_LABELS] = Labels::getLabel('LBL_Language_Labels', $langId);
                    //$arr[static::TYPE_POLICY_POINTS] = Labels::getLabel('LBL_Policy_Points', $langId);
                    $arr[static::TYPE_PRODUCTS] = Labels::getLabel('LBL_My_Products', $langId);
                    if (FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
                        $arr[static::TYPE_CUSTOM_FIELDS] = Labels::getLabel('LBL_Custom_Fields', $langId);
                    } 
                } else {
                    unset($arr[static::TYPE_PRODUCTS]);
                    $arr[static::TYPE_SELLER_PRODUCTS] = Labels::getLabel('LBL_My_Products', $langId);
                    if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 1) {
                        $arr[static::TYPE_ADDONS] = Labels::getLabel('LBL_RENTAL_SERVICES(FOR_RENTAL_PRODUCTS)', $langId);
                    }
                }
                break;
        }

        return $arr;
    }

    public static function getOptionContentTypeArr($langId)
    {
        $arr = array(
            static::LABEL_OPTIONS => Labels::getLabel('LBL_Options', $langId),
            static::LABEL_OPTIONS_VALUES => Labels::getLabel('LBL_Option_Values', $langId),
        );
        return $arr;
    }

    public static function getProductCatalogContentTypeArr($langId)
    {
        $arr = array(
            static::PRODUCT_CATALOG => Labels::getLabel('LBL_Product_Catalog', $langId),
            static::PRODUCT_OPTION => Labels::getLabel('LBL_Product_Options', $langId),
            //static::PRODUCT_TAG => Labels::getLabel('LBL_Product_Tags', $langId),
            static::PRODUCT_SPECIFICATION => Labels::getLabel('LBL_Product_Specifications', $langId),
            static::PRODUCT_CUSTOM_FIELDS => Labels::getLabel('LBL_Product_Custom_Fields_Data', $langId),
                // static::PRODUCT_SHIPPING => Labels::getLabel('LBL_Product_Shipping', $langId),
        );
        return $arr;
    }

    public static function getSellerProductContentTypeArr($langId)
    {
        $arr = array(
            static::SELLER_PROD_GENERAL_DATA => Labels::getLabel('LBL_General_Data', $langId),
            static::SELLER_PROD_OPTION => Labels::getLabel('LBL_INVENTORY_OPTIONS', $langId),
            static::SELLER_PROD_SEO => Labels::getLabel('LBL_SEO_Data', $langId),
            static::SELLER_PROD_RELATED_PRODUCT => Labels::getLabel('LBL_Related_products', $langId),
                //static::SELLER_PROD_POLICY => Labels::getLabel('LBL_Seller_Product_Policy', $langId),
        );

        if (ALLOW_SALE > 0) {
            $arr[static::SELLER_PROD_BUY_TOGTHER] = Labels::getLabel('LBL_Buy_togther', $langId);
            $arr[static::SELLER_PROD_VOLUME_DISCOUNT] = Labels::getLabel('LBL_Volume_Discount', $langId);
        }
        
        $arr[static::SELLER_PROD_SPECIAL_PRICE] = Labels::getLabel('LBL_Special_Price', $langId);

        if (ALLOW_RENT > 0) {
            $arr[static::SELLER_PROD_DURATION_DISCOUNT] = Labels::getLabel('LBL_Duration_Discounts', $langId);
            $arr[static::SELLER_PROD_UNAVAILABLE_DATES] = Labels::getLabel('LBL_Unavailable_Dates', $langId);
            $arr[static::SELLER_PROD_ADDON_PRODUCTS] = Labels::getLabel('LBL_Rental_Addons', $langId);
        }
        return $arr;
    }

    public static function getDataRangeArr($langId)
    {
        $arr = array(
            static::BY_ID_RANGE => Labels::getLabel('LBL_By_id_range', $langId),
            static::BY_BATCHES => Labels::getLabel('LBL_By_batches', $langId),
        );
        return $arr;
    }

    public function getCsvFilePointer($fileTempName)
    {
        return fopen($fileTempName, 'r');
    }

    public function getFileRow($csvFilePointer)
    {
        return fgetcsv($csvFilePointer);
    }

    public function getCell($arr = array(), $index, $defaultValue = '')
    {
        if (array_key_exists($index, $arr) && trim($arr[$index]) != '') {
            return $str = str_replace("\xc2\xa0", '', trim($arr[$index]));
            /*  return str_replace("\xa0", '', $str); */
        }
        return $defaultValue;
    }

    public function parseContentForExport($colValue)
    {
        $encoding = mb_detect_encoding($colValue, "auto");
        return mb_convert_encoding($colValue, 'utf-8', $encoding);
        /* $colValue = html_entity_decode($colValue, ENT_QUOTES, 'utf-8')
          return mb_convert_encoding($colValue, 'UTF-16LE', 'UTF-8'); */
        //html_entity_decode($colValue, ENT_QUOTES, 'utf-8');
    }

    private function validateCSVHeaders($csvFilePointer, $coloumArr, $langId)
    {
        $headingRow = $this->getFileRow($csvFilePointer);
        $i = 0;
        array_walk(
                $headingRow, function (&$string) use (&$i) {
            if (0 == $i) {
                $string = str_replace('"', '', preg_replace('/[^\x{0600}-\x{06FF}A-Za-z !@#$%^&*()]/u', '', $string));
            }
            $i++;
        }
        );

        if (!$this->isValidColumns($headingRow, $coloumArr)) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Coloum_CSV_File", $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->headingIndexArr = array_flip($headingRow);
    }

    public function export($type, $langId, $sheetType, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = 0, $sellerDashboard = false)
    {
        $all = !isset($offset) && !isset($noOfRows) && !isset($minId) && !isset($maxId);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $sheetData = array();
        $sheetName = '';

        if (isset($offset) && isset($noOfRows)) {
            $sheetName .= '_' . $offset;
        }

        if (isset($minId) && isset($maxId)) {
            $sheetName .= '_' . $minId . '-' . $maxId;
        }

        $default = false;
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportBrands($langId, $userId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCategories($langId, $userId);
                break;
            case Importexport::TYPE_PRODUCTS:
                $this->actionType = self::ACTION_ADMIN_PRODUCTS;
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = (!$sellerDashboard ? Labels::getLabel('LBL_My_Products', $langId) : Labels::getLabel('LBL_Marketplace_Products', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductsCatalog($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = (!$sellerDashboard ? Labels::getLabel('LBL_My_Product_Options', $langId) : Labels::getLabel('LBL_Marketplace_Product_Options', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductOptions($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_TAG:
                        $sheetName = Labels::getLabel('LBL_Marketplace_Product_Tags', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductTags($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SPECIFICATION:
                        $sheetName = (!$sellerDashboard ? Labels::getLabel('LBL_My_Product_Specifications', $langId) : Labels::getLabel('LBL_Marketplace_Product_Specifications', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductSpecification($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SHIPPING:
                        $sheetName = Labels::getLabel('LBL_Marketplace_Product_Shipping', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductShipping($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_CUSTOM_FIELDS:
                        $sheetName = Labels::getLabel('LBL_Catalog_Custom_Fields', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductCustomFields($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->actionType = self::ACTION_SELLER_PRODUCTS;
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = ((0 < $userId) ? Labels::getLabel('LBL_My_Products', $langId) : Labels::getLabel('LBL_Seller_Products', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductsCatalog($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = ((0 < $userId) ? Labels::getLabel('LBL_My_Product_Options', $langId) : Labels::getLabel('LBL_Seller_Product_Options', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductOptions($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_TAG:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Tags', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductTags($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SPECIFICATION:
                        $sheetName = ((0 < $userId) ? Labels::getLabel('LBL_My_Product_Specifications', $langId) : Labels::getLabel('LBL_Seller_Product_Specifications', $langId)) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductSpecification($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_SHIPPING:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Shipping', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductShipping($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::PRODUCT_CUSTOM_FIELDS:
                        $sheetName = Labels::getLabel('LBL_Catalog_Custom_Fields', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportProductCustomFields($langId, $offset, $noOfRows, $minId, $maxId, $userId, true);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_INVENTORIES:
                switch ($sheetType) {
                    case Importexport::SELLER_PROD_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_SELLER_INVENTORY_GENERAL', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdGeneralData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_OPTION:
                        $sheetName = Labels::getLabel('LBL_SELLER_INVENTORY_OPTIONS', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdOptionData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SEO:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Seo', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdSeoData($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SPECIAL_PRICE:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Special_price', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdSpecialPrice($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_VOLUME_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Volume_Discount', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdVolumeDiscount($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_BUY_TOGTHER:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Buy_Together', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdBuyTogther($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_RELATED_PRODUCT:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Related_Prod', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdRelatedProd($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_POLICY:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Policy', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdPolicy($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_DURATION_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Duration_Discount', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdDurationDiscount($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_UNAVAILABLE_DATES:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Unavialable_Rental_Dates', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdUnavailableRentalDates($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;
                    case Importexport::SELLER_PROD_ADDON_PRODUCTS:
                        $sheetName = Labels::getLabel('LBL_Seller_Prod_Addon_Prod', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdAddonProd($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                        break;

                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_OPTIONS:
                switch ($sheetType) {
                    case Importexport::LABEL_OPTIONS:
                        $sheetName = Labels::getLabel('LBL_Options', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptions($langId, $userId);
                        break;
                    case Importexport::LABEL_OPTIONS_VALUES:
                        $sheetName = Labels::getLabel('LBL_Option_Values', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptionValues($langId, $userId);
                        break;
                    default:
                        $sheetName = Labels::getLabel('LBL_Options', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportOptions($langId, $userId);
                        break;
                }
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $sheetName = Labels::getLabel('LBL_Option_Values', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportOptionValues($langId, $userId);
                break;
            case Importexport::TYPE_TAG:
                $sheetName = Labels::getLabel('LBL_Tags', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportTags($langId, $userId);
                break;
            case Importexport::TYPE_COUNTRY:
                $sheetName = Labels::getLabel('LBL_Country', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCountries($langId, $userId);
                break;
            case Importexport::TYPE_STATE:
                $sheetName = Labels::getLabel('LBL_State', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportStates($langId, $userId);
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $sheetName = Labels::getLabel('LBL_Policy_points', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportPolicyPoints($langId, $userId);
                break;
            case Importexport::TYPE_USERS:
                $sheetName = Labels::getLabel('LBL_Users', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportUsers($langId, $offset, $noOfRows, $minId, $maxId);
                break;
            case Importexport::TYPE_TAX_CATEGORY:
                $sheetName = Labels::getLabel('LBL_Tax_Category', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportTaxCategory($langId, $userId);
                break;
            case Importexport::TYPE_CUSTOM_FIELDS:
                $sheetName = Labels::getLabel('LBL_CUSTOM_FIELDS', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCustomFields($langId);
                break;
            case Importexport::TYPE_ADDONS:
                switch ($sheetType) {
                    case Importexport::ADDON_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_RENTAL_ADDONS_GENERAL', $langId) . $sheetName;
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                        $this->exportSellerProdGeneralData($langId, $offset, $noOfRows, $minId, $maxId, $userId, SellerProduct::PRODUCT_TYPE_ADDON);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;

            default:
                $default = true;
                break;
        }

        if ($default) {
            Message::addMessage(Labels::getLabel('MSG_Invalid_Access', $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function exportMedia($type, $langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = 0)
    {
        $all = !isset($offset) && !isset($noOfRows) && !isset($minId) && !isset($maxId);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $sheetData = array();
        $sheetName = '';
        if (isset($offset) && isset($noOfRows)) {
            $sheetName .= '_' . $offset;
        }

        if (isset($minId) && isset($maxId)) {
            $sheetName .= '_' . $minId . '-' . $maxId;
        }
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportBrandMedia($langId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportCategoryMedia($langId);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $catMediaId = $type == Importexport::TYPE_PRODUCTS ? 0 : $userId;
                $sheetName = Labels::getLabel('LBL_Product_Media', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportProductMedia($langId, $offset, $noOfRows, $minId, $maxId, $catMediaId, $type);
                break;
            case Importexport::TYPE_INVENTORIES:
                $sheetName = Labels::getLabel('LBL_Seller_Product_Digital_File', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportSellerProductMedia($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                break;
            case Importexport::TYPE_ADDONS:
                $sheetName = Labels::getLabel('LBL_RENTAL_ADDONS_MEDIA', $langId) . $sheetName;
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId);
                $this->exportAddonMedia($langId, $offset, $noOfRows, $minId, $maxId, $userId);
                break;
        }
    }

    public function import($type, $langId, $sheetType = '', $userId = 0)
    {
        $post = FatApp::getPostedData();
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        $csvFilePointer = $this->getCsvFilePointer($_FILES['import_file']['tmp_name']);
        $default = false;
        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importBrands($csvFilePointer, $post, $langId, $userId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Categories_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCategories($csvFilePointer, $post, $langId, $userId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $this->actionType = ($type == self::TYPE_PRODUCTS) ? self::ACTION_ADMIN_PRODUCTS : self::ACTION_SELLER_PRODUCTS;
                switch ($sheetType) {
                    case Importexport::PRODUCT_CATALOG:
                        $sheetName = Labels::getLabel('LBL_Products_catalog_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductsCatalog($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::PRODUCT_OPTION:
                        $sheetName = Labels::getLabel('LBL_Product_Options_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductOptions($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_TAG:
                        $sheetName = Labels::getLabel('LBL_Product_Tags_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductTags($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_SPECIFICATION:
                        $sheetName = Labels::getLabel('LBL_Product_Specifications_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductSpecifications($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_SHIPPING:
                        $sheetName = Labels::getLabel('LBL_Product_Shipping_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductShipping($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::PRODUCT_CUSTOM_FIELDS:
                        $sheetName = Labels::getLabel('LBL_Product_Custom_Fields_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importProductCustomFields($csvFilePointer, $langId, $userId);
                        break;

                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_INVENTORIES:
                switch ($sheetType) {
                    case Importexport::SELLER_PROD_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_General_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdGeneralData($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_OPTION:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Option_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdOptionData($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_SEO:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Seo_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdSeoData($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_SPECIAL_PRICE:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Special_Price_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdSpecialPrice($csvFilePointer, $post, $langId, $userId);
                        Product::updateMinPrices();
                        break;
                    case Importexport::SELLER_PROD_VOLUME_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Volume_Discount_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdVolumeDiscount($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_BUY_TOGTHER:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Buy_Togther_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdBuyTogther($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_RELATED_PRODUCT:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Related_Product_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdRelatedProd($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_POLICY:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Policy_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdPolicy($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_DURATION_DISCOUNT:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Duration_Discount_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdDurationDiscount($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_UNAVAILABLE_DATES:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Rental_Unavialable_Dates_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdRentalUnavialableDates($csvFilePointer, $post, $langId, $userId);
                        break;
                    case Importexport::SELLER_PROD_ADDON_PRODUCTS:
                        $sheetName = Labels::getLabel('LBL_Seller_Product_Rental_Addons_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importSellerProdAddonProd($csvFilePointer, $post, $langId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            case Importexport::TYPE_OPTIONS:
                $sheetName = Labels::getLabel('LBL_Options_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importOptions($csvFilePointer, $post, $langId);
                break;
                break;
            case Importexport::TYPE_OPTION_VALUES:
                $sheetName = Labels::getLabel('LBL_Option_Values_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importOptionValues($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_TAG:
                $sheetName = Labels::getLabel('LBL_Tags_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importTags($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_COUNTRY:
                $sheetName = Labels::getLabel('LBL_Countries_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCountries($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_STATE:
                $sheetName = Labels::getLabel('LBL_States_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importStates($csvFilePointer, $post, $langId);
                Product::updateMinPrices();
                break;
            case Importexport::TYPE_POLICY_POINTS:
                $sheetName = Labels::getLabel('LBL_Policy_Points_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importPolicyPoints($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_CUSTOM_FIELDS:
                $sheetName = Labels::getLabel('LBL_CUSTOM_FIELDS_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCustomFields($csvFilePointer, $langId);
                break;
            case Importexport::TYPE_ADDONS:
                switch ($sheetType) {
                    case Importexport::ADDON_GENERAL_DATA:
                        $sheetName = Labels::getLabel('LBL_Rental_Addons_General_Data_Error', $langId);
                        $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                        $this->importAddonsGeneralData($csvFilePointer, $post, $langId, $userId);
                        break;
                    default:
                        $default = true;
                        break;
                }
                break;
            default:
                $default = true;
                break;
        }

        if ($default) {
            Message::addMessage(Labels::getLabel('MSG_Invalid_Access', $langId));
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function importMedia($type, $post, $langId, $userId = 0)
    {
        $csvFilePointer = $this->getCsvFilePointer($_FILES['import_file']['tmp_name']);
        $userId = FatUtility::int($userId);
        $this->settings = $this->getSettings($userId);

        switch ($type) {
            case Importexport::TYPE_BRANDS:
                $sheetName = Labels::getLabel('LBL_Brands_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importBrandsMedia($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_CATEGORIES:
                $sheetName = Labels::getLabel('LBL_Category_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importCategoryMedia($csvFilePointer, $post, $langId);
                break;
            case Importexport::TYPE_PRODUCTS:
            case Importexport::TYPE_SELLER_PRODUCTS:
                $sheetName = Labels::getLabel('LBL_Product_Catalog_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importProductCatalogMedia($csvFilePointer, $post, $langId, $userId);
                break;
            case Importexport::TYPE_ADDONS:
                $sheetName = Labels::getLabel('LBL_Rental_Addons_Media_Error', $langId);
                $this->CSVfileObj = $this->openCSVfileToWrite($sheetName, $langId, true);
                $this->importAddonMedia($csvFilePointer, $post, $langId, $userId);
                break;
        }
    }

    public function exportCategories($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);

        if (!$userId) {
            $urlKeywords = $this->getAllRewriteUrls(ProductCategory::REWRITE_URL_PREFIX);
        }

        $useCategoryId = false;
        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $useCategoryId = true;
        } else {
            $categoriesIdentifiers = $this->getAllCategoryIdentifiers();
        }

        $srch = ProductCategory::getSearchObject(false, $langId, false);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->addMultipleFields(array('prodcat_id', 'prodcat_identifier', 'prodcat_parent', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'prodcat_description', 'prodcat_featured', 'prodcat_active', 'prodcat_status', 'prodcat_deleted', 'prodcat_display_order'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('prodcat_id', 'asc');
        if ($userId) {
            $srch->addCondition('prodcat_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        }

        $rs = $srch->getResultSet();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getCategoryColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array('prodcat_featured', 'prodcat_active', 'prodcat_status', 'prodcat_deleted')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('urlrewrite_custom' == $columnKey) {
                    $colValue = isset($urlKeywords[ProductCategory::REWRITE_URL_PREFIX . $row['prodcat_id']]) ? $urlKeywords[ProductCategory::REWRITE_URL_PREFIX . $row['prodcat_id']] : '';
                }

                if ('prodcat_parent_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['prodcat_parent'], $categoriesIdentifiers) ? $categoriesIdentifiers[$row['prodcat_parent']] : '';
                }
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportCategoryMedia($langId)
    {
        $srch = ProductCategory::getSearchObject(false, false, false);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'prodcat_id = afile_record_id and ( afile_type = ' . AttachedFile::FILETYPE_CATEGORY_ICON . ' or afile_type = ' . AttachedFile::FILETYPE_CATEGORY_BANNER . ')');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id', 'prodcat_identifier', 'afile_record_id', 'afile_record_subid', 'afile_type', 'afile_lang_id', 'afile_screen', 'afile_physical_path', 'afile_name', 'afile_display_order'));
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getCategoryMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);
        $fileTypeArr = AttachedFile::getFileTypeArray($langId);
        $displayArr = applicationConstants::getDisplaysArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['afile_lang_id']];
                }

                if ('afile_type' == $columnKey) {
                    $colValue = array_key_exists($row['afile_type'], $fileTypeArr) ? $fileTypeArr[$row['afile_type']] : '';
                }

                if ('afile_screen' == $columnKey) {
                    $colValue = array_key_exists($row['afile_screen'], $displayArr) ? $displayArr[$row['afile_screen']] : '';
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importCategories($csvFilePointer, $post, $langId, $userId = null)
    {
        $coloumArr = $this->getCategoryColoumArr($langId, $userId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $rowIndex = 1;
        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodCatDataArr = $prodCatlangDataArr = array();
            $errorInRow = $seoUrl = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = ProductCategory::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array('prodcat_featured', 'prodcat_active', 'prodcat_status', 'prodcat_deleted'))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }

                    if ('prodcat_display_order' == $columnKey) {
                        $colValue = FatUtility::int($colValue);
                    }

                    if ('prodcat_parent_identifier' == $columnKey) {
                        $columnKey = 'prodcat_parent';
                    }

                    if (in_array($columnKey, array('prodcat_name', 'prodcat_description'))) {
                        $prodCatlangDataArr[$columnKey] = $colValue;
                    } elseif ('urlrewrite_custom' == $columnKey) {
                        $seoUrl = $colValue;
                    } else {
                        $prodCatDataArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodCatDataArr)) {
                if ($this->isDefaultSheetData($langId)) {
                    if ($this->settings['CONF_USE_CATEGORY_ID']) {
                        $parentId = $prodCatDataArr['prodcat_parent'];
                    } else {
                        $identifier = mb_strtolower($prodCatDataArr['prodcat_parent']);
                        $categoriesIdentifiers = $this->array_change_key_case_unicode($this->getAllCategoryIdentifiers(false), CASE_LOWER);
                        $parentId = isset($categoriesIdentifiers[$identifier]) ? $categoriesIdentifiers[$identifier] : 0;
                    }
                    if ($parentId) {
                        $parentCategoryData = ProductCategory::getAttributesById($parentId, 'prodcat_id');
                        if (empty($parentCategoryData) || $parentCategoryData == false) {
                            $parentId = 0;
                        }
                        $prodCatDataArr['prodcat_parent'] = $parentId;
                    }
                }

                $categoryId = 0;
                if ($this->settings['CONF_USE_CATEGORY_ID']) {
                    $categoryId = $prodCatDataArr['prodcat_id'];
                    $categoryData = ProductCategory::getAttributesById($categoryId, array('prodcat_id'));
                } else {
                    $identifier = $prodCatDataArr['prodcat_identifier'];
                    $categoryData = ProductCategory::getAttributesByIdentifier($identifier, array('prodcat_id'));
                    if ($categoryData != false) {
                        $categoryId = $categoryData['prodcat_id'];
                    }
                }

                if (!$this->isDefaultSheetData($langId)) {
                    unset($prodCatDataArr['prodcat_parent']);
                    unset($prodCatDataArr['prodcat_identifier']);
                    unset($prodCatDataArr['prodcat_display_order']);
                } else {
                    if ($categoryId == $prodCatDataArr['prodcat_parent']) {
                        $prodCatDataArr['prodcat_parent'] = 0;
                    }
                    $prodCatDataArr['prodcat_status'] = 1; 
                }

                if (!empty($categoryData) && $categoryData['prodcat_id']) {
                    $where = array('smt' => 'prodcat_id = ?', 'vals' => array($categoryId));
                    $this->db->updateFromArray(ProductCategory::DB_TBL, $prodCatDataArr, $where);
                } elseif (false === $errInSheet) {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(ProductCategory::DB_TBL, $prodCatDataArr);
                        $categoryId = $this->db->getInsertId();
                    }
                }

                if (0 < $categoryId) {
                    /* Lang Data [ */
                    $langData = array(
                        'prodcatlang_prodcat_id' => $categoryId,
                        'prodcatlang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $prodCatlangDataArr);

                    $this->db->insertFromArray(ProductCategory::DB_TBL_LANG, $langData, false, array(), $langData);

                    /* ] */

                    /* Update cat code[ */
                    $category = new ProductCategory($categoryId);
                    $category->updateCatCode();
                    /* ] */

                    /* Url rewriting [ */
                    if ($this->isDefaultSheetData($langId)) {
                        if (!$seoUrl) {
                            $seoUrl = $identifier;
                        }
                        $prodcatData = ProductCategory::getAttributesById($categoryId, array('prodcat_parent'));
                        $category->rewriteUrl($seoUrl, true, $prodcatData['prodcat_parent']);
                    }
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }

        $ProductCategory = new ProductCategory();
        $ProductCategory->updateCatCode();
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }

        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function importCategoryMedia($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $fileTypeArr = AttachedFile::getFileTypeArray($langId);
        $fileTypeIdArr = array_flip($fileTypeArr);

        $displayArr = applicationConstants::getDisplaysArr($langId);
        $displayIdArr = array_flip($displayArr);

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $useCategoryId = false;
        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $useCategoryId = true;
        } else {
            $categoriesIdentifiers = $this->getAllCategoryIdentifiers();
            $categoriesIds = array_flip($categoriesIdentifiers);
        }

        $coloumArr = $this->getCategoryMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $screenInputRequired = false;
            $rowIndex++;

            $categoryMediaArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = ProductCategory::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('afile_type' == $columnKey) {
                        $colValue = array_key_exists($colValue, $fileTypeIdArr) ? $fileTypeIdArr[$colValue] : 0;
                        $screenIndex = isset($this->headingIndexArr[$coloumArr['afile_screen']]) ? $this->headingIndexArr[$coloumArr['afile_screen']] : '';
                        $screen = !empty($screenIndex) ? $this->getCell($row, $screenIndex, '') : '';
                        $screenInputRequired = AttachedFile::FILETYPE_CATEGORY_BANNER == $colValue ? true : false;
                        if (AttachedFile::FILETYPE_CATEGORY_BANNER == $colValue && (empty($screen) || !in_array($screen, $displayArr))) {
                            $errorInRow = true;
                            $errMsg = Labels::getLabel('LBL_INVALID_SCREEN_VALUE', $langId);
                            $err = array($rowIndex, ($screenIndex + 1), $errMsg);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                            continue;
                        }
                    }

                    if ('afile_screen' == $columnKey) {
                        $colValue = array_key_exists($colValue, $displayIdArr) ? $displayIdArr[$colValue] : 0;
                    }

                    if ('prodcat_id' == $columnKey) {
                        $columnKey = 'afile_record_id';
                    }
                    if ('prodcat_identifier' == $columnKey) {
                        $columnKey = 'afile_record_id';
                        $colValue = array_key_exists($colValue, $categoriesIds) ? $categoriesIds[$colValue] : 0;
                    }

                    if ('afile_lang_code' == $columnKey) {
                        $columnKey = 'afile_lang_id';
                        $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                    }

                    $categoryMediaArr[$columnKey] = $colValue;
                    if (false === $screenInputRequired) {
                        unset($categoryMediaArr['afile_screen']);
                    }
                }
            }
            if (false === $errorInRow && count($categoryMediaArr)) {
                $categoryMediaArr['afile_record_subid'] = 0;

                $saveToTempTable = false;
                $isUrlArr = parse_url($categoryMediaArr['afile_physical_path']);

                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                $screen = isset($categoryMediaArr['afile_screen']) ? $categoryMediaArr['afile_screen'] : 0;

                if ($saveToTempTable) {
                    $categoryMediaArr['afile_downloaded'] = applicationConstants::NO;
                    $categoryMediaArr['afile_unique'] = applicationConstants::YES;
                    $this->db->deleteRecords(
                            AttachedFile::DB_TBL_TEMP, array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ? AND afile_screen = ?',
                        'vals' => array($categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_lang_id'], $screen)
                            )
                    );
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $categoryMediaArr, false, array(), $categoryMediaArr);
                } else {
                    $this->db->deleteRecords(
                            AttachedFile::DB_TBL, array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ? AND afile_screen = ?',
                        'vals' => array($categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_lang_id'], $screen)
                            )
                    );

                    $physical_path = explode('/', $categoryMediaArr['afile_physical_path']);
                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH == $physical_path[0] . '/') {
                        $afileObj = new AttachedFile();
                        $moved = $afileObj->moveAttachment($categoryMediaArr['afile_physical_path'], $categoryMediaArr['afile_type'], $categoryMediaArr['afile_record_id'], 0, $categoryMediaArr['afile_name'], $categoryMediaArr['afile_display_order'], true, $categoryMediaArr['afile_lang_id'], $screen);

                        if (false === $moved) {
                            $errMsg = Labels::getLabel("MSG_Invalid_File.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, 'N/A', $errMsg));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $categoryMediaArr, false, array(), $categoryMediaArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportBrands($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        if (!$userId) {
            /* Fetch all seo keyword [ */
            $urlKeywords = $this->getAllRewriteUrls(Brand::REWRITE_URL_PREFIX);
            /* ] */
        }

        $srch = Brand::getSearchObject($langId, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('brand_id', 'brand_identifier', 'iFNULL(brand_name,brand_identifier) as brand_name', 'brand_short_description', 'brand_featured', 'brand_active', 'brand_deleted'));
        $srch->addCondition('brand_status', '=', applicationConstants::ACTIVE);
        if ($userId) {
            $srch->addCondition('brand_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('brand_deleted', '=', applicationConstants::NO);
            $srch->addOrder('brand_id');
        }
        $rs = $srch->getResultSet();

        $sheetData = array();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getBrandColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */
        // $data = $this->db->fetchAll($rs);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey) {
                    case 'brand_featured':
                    case 'brand_active':
                    case 'brand_deleted':
                        if (!$this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                        }
                        break;
                    case 'urlrewrite_custom':
                        $colValue = isset($urlKeywords[Brand::REWRITE_URL_PREFIX . $row['brand_id']]) ? $urlKeywords[Brand::REWRITE_URL_PREFIX . $row['brand_id']] : '';
                        break;
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importBrands($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;

        $coloumArr = $this->getBrandColoumArr($langId, $userId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $brandDataArr = $brandlangDataArr = array();
            $errorInRow = $seoUrl = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Brand::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array('brand_featured', 'brand_active', 'brand_deleted'))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }

                    if (in_array($columnKey, array('brand_name', 'brand_short_description'))) {
                        $brandlangDataArr[$columnKey] = $colValue;
                    } elseif ('urlrewrite_custom' == $columnKey) {
                        $seoUrl = $colValue;
                    } else {
                        $brandDataArr['brand_status'] = applicationConstants::ACTIVE;
                        $brandDataArr[$columnKey] = $colValue;
                    }
                }
            }
            $brandId = 0;
            if (false === $errorInRow && count($brandDataArr)) {
                if ($this->settings['CONF_USE_BRAND_ID']) {
                    $brandId = $brandDataArr['brand_id'];
                    $brandData = Brand::getAttributesById($brandId, array('brand_id'));
                } else {
                    $identifier = $brandDataArr['brand_identifier'];
                    $brandData = Brand::getAttributesByIdentifier($identifier, array('brand_id'));
                    if ($brandData !== false) {
                        $brandId = $brandData['brand_id'];
                    }
                }

                if (!empty($brandData) && $brandId > 0) {
                    $where = array('smt' => 'brand_id = ?', 'vals' => array($brandId));
                    $this->db->updateFromArray(Brand::DB_TBL, $brandDataArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Brand::DB_TBL, $brandDataArr);
                        $brandId = $this->db->getInsertId();
                    }
                }

                if ($brandId) {
                    /* Lang Data [ */
                    $langData = array(
                        'brandlang_brand_id' => $brandId,
                        'brandlang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $brandlangDataArr);

                    $this->db->insertFromArray(Brand::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */

                    /* Url rewriting [ */
                    if ($this->isDefaultSheetData($langId)) {
                        if (!$seoUrl) {
                            $seoUrl = $brandDataArr['brand_identifier'];
                        }
                        $brand = new Brand($brandId);
                        $brand->rewriteUrl($seoUrl);
                    }
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }

        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);



        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportBrandMedia($langId)
    {
        $srch = Brand::getSearchObject();
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'brand_id = afile_record_id AND ( afile_type = ' . AttachedFile::FILETYPE_BRAND_LOGO . ' OR afile_type = ' . AttachedFile::FILETYPE_BRAND_IMAGE . ')');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('brand_id', 'brand_identifier', 'afile_record_id', 'afile_record_subid', 'afile_lang_id', 'afile_screen', 'afile_physical_path', 'afile_name', 'afile_display_order', 'afile_type'));
        $srch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getBrandMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);
        $displayArr = applicationConstants::getDisplaysArr($langId);
        $ratioArr = AttachedFile::getRatioTypeArray($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey) {
                    case 'afile_lang_code':
                        $colValue = $languageCodes[$row['afile_lang_id']];
                        break;

                    case 'afile_lang_code':
                        $colValue = $languageCodes[$row['afile_lang_id']];
                        break;

                    case 'afile_type':
                        $colValue = 'logo';
                        if ($row['afile_type'] == AttachedFile::FILETYPE_BRAND_IMAGE) {
                            $colValue = 'image';
                        }
                        break;
                    case 'afile_screen':
                        $colValue = '';
                        if ($row['afile_type'] == AttachedFile::FILETYPE_BRAND_IMAGE) {
                            $colValue = array_key_exists($row['afile_screen'], $displayArr) ? $displayArr[$row['afile_screen']] : '';
                            }
                        break;
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importBrandsMedia($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $brandIdentifiers = Brand::getAllIdentifierAssoc();
        $brandIds = array_flip($brandIdentifiers);

        $displayArr = applicationConstants::getDisplaysArr($langId);
        $displayIdArr = array_flip($displayArr);

        $coloumArr = $this->getBrandMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $screenInputRequired = $ratioInputRequired = false;
            $rowIndex++;

            $brandsMediaArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Brand::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'brand_id':
                            $columnKey = 'afile_record_id';
                            break;

                        case 'brand_identifier':
                            $columnKey = 'afile_record_id';
                            $colValue = $brandIds[$colValue];
                            break;
                        case 'afile_lang_code':
                            $columnKey = 'afile_lang_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;

                        case 'afile_type':
                            $fileType = AttachedFile::FILETYPE_BRAND_LOGO;
                            if ('image' == mb_strtolower($colValue)) {
                                $fileType = AttachedFile::FILETYPE_BRAND_IMAGE;
                            }
                            $colValue = $fileType;

                            $screenIndex = isset($this->headingIndexArr[$coloumArr['afile_screen']]) ? $this->headingIndexArr[$coloumArr['afile_screen']] : '';
                            $screen = !empty($screenIndex) ? $this->getCell($row, $screenIndex, '') : '';
                            $screenInputRequired = AttachedFile::FILETYPE_BRAND_IMAGE == $colValue ? true : false;
                            if (AttachedFile::FILETYPE_BRAND_IMAGE == $colValue && (empty($screen) || !in_array($screen, $displayArr))) {
                                $errorInRow = true;
                                $errMsg = Labels::getLabel('LBL_INVALID_SCREEN_VALUE', $langId);
                                $err = array($rowIndex, ($screenIndex + 1), $errMsg);
                                CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                                continue 2;
                            }
                            break;
                        case 'afile_screen':
                            $colValue = array_key_exists($colValue, $displayIdArr) ? $displayIdArr[$colValue] : 0;
                            break;
                    }

                    $brandsMediaArr[$columnKey] = $colValue;
                    if (false === $screenInputRequired) {
                        unset($brandsMediaArr['afile_screen']);
                    }
                }
            }

            if (false === $errorInRow && count($brandsMediaArr)) {
                $dataToSaveArr = array(
                    'afile_record_subid' => 0,
                );
                $dataToSaveArr = array_merge($dataToSaveArr, $brandsMediaArr);

                $saveToTempTable = false;
                $isUrlArr = parse_url($brandsMediaArr['afile_physical_path']);

                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                $screen = isset($brandsMediaArr['afile_screen']) ? $brandsMediaArr['afile_screen'] : 0;

                if ($saveToTempTable) {
                    $dataToSaveArr['afile_downloaded'] = applicationConstants::NO;
                    $dataToSaveArr['afile_unique'] = applicationConstants::YES;
                    $this->db->deleteRecords(
                            AttachedFile::DB_TBL_TEMP, array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ? AND afile_screen = ?',
                        'vals' => array($fileType, $dataToSaveArr['afile_record_id'], 0, $dataToSaveArr['afile_lang_id'], $screen)
                            )
                    );
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $dataToSaveArr, false, array(), $dataToSaveArr);
                } else {
                    $this->db->deleteRecords(
                            AttachedFile::DB_TBL, array(
                        'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ? AND afile_screen = ?',
                        'vals' => array($fileType, $dataToSaveArr['afile_record_id'], 0, $dataToSaveArr['afile_lang_id'], $screen)
                            )
                    );

                    $physical_path = explode('/', $brandsMediaArr['afile_physical_path']);
                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH == $physical_path[0] . '/') {
                        $afileObj = new AttachedFile();
                        $moved = $afileObj->moveAttachment($brandsMediaArr['afile_physical_path'], $fileType, $dataToSaveArr['afile_record_id'], 0, $brandsMediaArr['afile_name'], $brandsMediaArr['afile_display_order'], true, $brandsMediaArr['afile_lang_id'], $screen);

                        if (false === $moved) {
                            $errMsg = Labels::getLabel("MSG_Invalid_File.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, 'N/A', $errMsg));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $dataToSaveArr, false, array(), $dataToSaveArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function exportProductsCatalog($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $useProductId = false;

        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $useProductId = true;
        }

        if (!$this->settings['CONF_USE_PRODUCT_TYPE_ID']) {
            $ProdTypeIdentifierById = Product::getProductTypes($langId);
        }

        if (!$this->settings['CONF_USE_TAX_CATEOGRY_ID']) {
            $taxCategoryIdentifierById = $this->getTaxCategoriesArr();
        }

        /* if (!$this->settings['CONF_USE_DIMENSION_UNIT_ID']) {
          $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
          } */

        if (!$this->settings['CONF_USE_SHIPPING_PROFILE_ID']) {
            $shippingProfiles = $this->getShippingProfileArr(true, false, 0, $langId);
        }

        if (!$this->settings['CONF_USE_SHIPPING_PACKAGE_ID']) {
            $shippingPackages = $this->getShippingPackageArr();
        }

        if (!$this->settings['CONF_USE_WEIGHT_UNIT_ID']) {
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
        }

        $shipProfileSrch = ShippingProfileProduct::getUserSearchObject($userId);
        $shipProfileSubQuery = $shipProfileSrch->getQuery();

        $srch = Product::getSearchObject($langId, false, true);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = tp.product_seller_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = tp.product_seller_id', 'uc');
        $srch->joinTable(Brand::DB_TBL, 'LEFT OUTER JOIN', 'b.brand_id = tp.product_brand_id', 'b');

        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $cond = ' and ps.ps_user_id = 0';
        } else {
            if ($userId) {
                $cond = ' and ps.ps_user_id = ' . $userId;
            } else {
                $cond = ' and ps.ps_user_id = tp.product_seller_id';
            }
        }

        $srch->joinTable(Product::DB_TBL_PRODUCT_SHIPPING, 'LEFT OUTER JOIN', 'ps.ps_product_id = tp.product_id ' . $cond, 'ps');
        $srch->joinTable('(' . $shipProfileSubQuery . ')', 'LEFT OUTER JOIN', 'tp.product_id = sppro.shippro_product_id', 'sppro');
        /* $srch->joinTable(ShippingProfile::DB_TBL, 'LEFT OUTER JOIN', 'sppro.shippro_shipprofile_id = shp.shipprofile_id', 'shp');
          $srch->joinTable(ShippingPackage::DB_TBL, 'LEFT OUTER JOIN', 'spp.shippack_id = tp.product_ship_package', 'spp'); */
        //$srch->joinTable(Countries::DB_TBL,'LEFT OUTER JOIN','c.country_id = tp.product_ship_country','c');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(['tp.*', 'tp_l.*', 'user_id', 'credential_username', 'brand_id', 'brand_identifier', 'product_warranty', 'sppro.shippro_shipprofile_id']);

        switch ($this->actionType) {
            case self::ACTION_ADMIN_PRODUCTS:
                $srch->addCondition('tp.product_added_by_admin_id', '>', 0);
                break;
            case self::ACTION_SELLER_PRODUCTS:
                if ($userId) {
                    $srch->addCondition('tp.product_seller_id', '=', $userId);
                } else {
                    $srch->addCondition('tp.product_seller_id', '>', 0);
                }
                break;
            default:
                if ($userId) {
                    $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
                    $cnd->attachCondition('tp.product_seller_id', '=', 0);
                }
                break;
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }

        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductsCatalogColoumArr($langId, $userId, $this->actionType);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $taxData = $this->getTaxCategoryByProductId($row['product_id']);

            if (!empty($taxData)) {
                $row = $row + $taxData;
            }
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array('brand_featured', 'brand_active')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('product_fulfillment_type' == $columnKey) {
                    switch ($colValue) {
                        case Shipping::FULFILMENT_SHIP:
                            $colValue = Labels::getLabel('LBL_SHIPPED_ONLY', $langId);
                            break;
                        case Shipping::FULFILMENT_PICKUP:
                            $colValue = Labels::getLabel('LBL_PICKUP_ONLY', $langId);
                            break;
                        case Shipping::FULFILMENT_ALL:
                            $colValue = Labels::getLabel('LBL_SHIPPED_AND_PICKUP', $langId);
                            break;
                        default:
                            $colValue = Labels::getLabel('LBL_SHIPPED_ONLY', $langId);
                            break;
                    }
                }

                if (in_array($columnKey, array('category_Id', 'category_indentifier'))) {
                    if ('category_Id' == $columnKey) {
                        $productCategories = $this->getProductCategoriesByProductId($row['product_id'], false);
                    } else {
                        $productCategories = $this->getProductCategoriesByProductId($row['product_id']);
                    }

                    $colValue = ($productCategories) ? implode(',', $productCategories) : '';
                }

                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($row[$columnKey]) ? $row['credential_username'] : Labels::getLabel('LBL_Admin', $langId));
                }

                /* if ('product_type_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_type']) && array_key_exists($row['product_type'], $ProdTypeIdentifierById) ? $ProdTypeIdentifierById[$row['product_type']] : 0);
                } */

                if ('tax_category_id' == $columnKey) {
                    $colValue = (array_key_exists('ptt_taxcat_id', $row) ? $row['ptt_taxcat_id'] : 0);
                }
                if ('tax_category_identifier' == $columnKey) {
                    $colValue = (!empty($row['ptt_taxcat_id']) && array_key_exists($row['ptt_taxcat_id'], $taxCategoryIdentifierById) ? $taxCategoryIdentifierById[$row['ptt_taxcat_id']] : 0);
                }
                
                if ('tax_category_id_rent' == $columnKey) {
                    $colValue = (array_key_exists('ptt_taxcat_id', $row) ? $row['ptt_taxcat_id'] : 0);
                }
                if ('tax_category_identifier_rent' == $columnKey) {
                    $colValue = (!empty($row['ptt_taxcat_id_rent']) && array_key_exists($row['ptt_taxcat_id_rent'], $taxCategoryIdentifierById) ? $taxCategoryIdentifierById[$row['ptt_taxcat_id_rent']] : 0);
                }
                

                if ('product_ship_package_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_ship_package']) && array_key_exists($row['product_ship_package'], $shippingPackages) ? $shippingPackages[$row['product_ship_package']] : '');
                }

                if ('shipping_profile_identifier' == $columnKey) {
                    $colValue = (!empty($row['shippro_shipprofile_id']) && array_key_exists($row['shippro_shipprofile_id'], $shippingProfiles) ? $shippingProfiles[$row['shippro_shipprofile_id']] : '');
                }

                if ('shipping_profile_id' == $columnKey) {
                    $colValue = (!empty($row['shippro_shipprofile_id']) ? $row['shippro_shipprofile_id'] : '');
                }

                /* if ('product_dimension_unit_identifier' == $columnKey) {
                  $colValue = (!empty($row['product_dimension_unit']) && array_key_exists($row['product_dimension_unit'], $lengthUnitsArr) ? $lengthUnitsArr[$row['product_dimension_unit']] : '');
                  } */

                if ('product_weight_unit_identifier' == $columnKey) {
                    $colValue = (!empty($row['product_weight_unit']) && array_key_exists($row['product_weight_unit'], $weightUnitsArr) ? $weightUnitsArr[$row['product_weight_unit']] : '');
                }

                if (in_array($columnKey, array('ps_free', 'product_cod_enabled', 'product_featured', 'product_approved', 'product_active', 'product_deleted')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductsCatalog($csvFilePointer, $post, $langId, $sellerId = null)
    {
        $sellerId = FatUtility::int($sellerId);

        $rowIndex = 1;
        $usernameArr = array();
        $categoryIdentifierArr = array();
        $brandIdentifierArr = array();
        $taxCategoryArr = array();
        $countryArr = array();
        $userProdUploadLimit = $usersCrossedUploadLimit = array();
        $userId = 0;
        if (!$this->settings['CONF_USE_PRODUCT_TYPE_ID']) {
            $prodTypeIdentifierArr = Product::getProductTypes($langId);
            $prodTypeIdentifierArr = $this->array_change_key_case_unicode(array_flip($prodTypeIdentifierArr), CASE_LOWER);
        }

        $shippingProfiles = $this->getShippingProfileArr($this->settings['CONF_USE_SHIPPING_PROFILE_ID'], false, 0, $langId);

        if (!$this->settings['CONF_USE_SHIPPING_PACKAGE_ID']) {
            $shippingPackages = $this->getShippingPackageArr();
            $shippingPackages = array_flip($shippingPackages);
        }

        if (!$this->settings['CONF_USE_DIMENSION_UNIT_ID']) {
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
            $lengthUnitsArr = array_flip($lengthUnitsArr);
        }

        if (!$this->settings['CONF_USE_WEIGHT_UNIT_ID']) {
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
            $weightUnitsArr = array_flip($weightUnitsArr);
        }

        $shippingProfileArr = ShippingProfile::getProfileArr($langId, 0, true, true, true);
        $adminDefaultShipProfileId = array_key_first($shippingProfileArr);
        $coloumArr = $this->getProductsCatalogColoumArr($langId, $sellerId, $this->actionType);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $prodType = Product::PRODUCT_TYPE_PHYSICAL;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $prodDataArr = $prodlangDataArr = $categoryIds = $prodShippingArr = array();
            $errorInRow = $taxCatId = $taxCatIdRent = false;
            $productId = 0;
            $breakForeach = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $invalid = $errMsg = false;

                if ($this->isDefaultSheetData($langId) && $this->actionType != Importexport::ACTION_ADMIN_PRODUCTS && in_array($columnKey, array('product_seller_id', 'credential_username'))) {
                    if ($this->settings['CONF_USE_USER_ID']) {
                        $colTitle = ('product_seller_id' != $columnKey) ? $coloumArr['product_seller_id'] : $columnTitle;
                        $colInd = $this->headingIndexArr[$colTitle];
                        $userId = $this->getCell($row, $colInd, '');
                    } else {
                        $colTitle = ('credential_username' != $columnKey) ? $coloumArr['credential_username'] : $columnTitle;

                        if ('credential_username' == $columnKey) {
                            $columnKey = 'product_seller_id';
                        }

                        $colInd = $this->headingIndexArr[$colTitle];
                        $userName = $this->getCell($row, $colInd, '');

                        if (0 < $sellerId && empty($userName)) {
                            $userObj = new User($sellerId);
                            $userInfo = $userObj->getUserInfo(array('credential_username'));
                            $userName = $userInfo['credential_username'];
                        } else {
                            $userName = ($userName == Labels::getLabel('LBL_Admin', $langId) ? '' : $userName);
                        }
                        $userName = mb_strtolower($userName);
                        if (!empty($userName) && !array_key_exists($userName, $usernameArr)) {
                            $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $userName), CASE_LOWER);
                            if (!$res) {
                                $colIndex = $colInd;
                                $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                            } else {
                                $usernameArr = $usernameArr + $res;
                            }
                        }
                        $userId = array_key_exists($userName, $usernameArr) ? FatUtility::int($usernameArr[$userName]) : 0;
                        if ('credential_username' == $columnKey) {
                            $colValue = $userId;
                        }
                    }
                    if (0 < $sellerId && ($sellerId != $userId || 1 > $userId)) {
                        $colIndex = $colInd;
                        $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                        $breakForeach = true;
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && in_array($columnKey, array('credential_username', 'product_seller_id')) && 0 < $userId) {
                        if (!array_key_exists($userId, $userProdUploadLimit)) {
                            $userProdUploadLimit[$userId] = SellerPackages::getAllowedLimit($userId, $langId, 'ossubs_products_allowed');
                        }
                    }
                }

                if (false === $errMsg) {
                    $errMsg = Product::validateFields($columnKey, $columnTitle, $colValue, $langId, $prodType);
                }

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    if ($breakForeach) {
                        break;
                    }
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                            if ($this->settings['CONF_USE_PRODUCT_ID']) {
                                if (0 < $sellerId) {
                                    $userTempIdData = $this->getProductIdByTempId($colValue, $userId);
                                    if (!empty($userTempIdData) && $userTempIdData['pti_product_temp_id'] == $colValue) {
                                        $colValue = $userTempIdData['pti_product_id'];
                                    }
                                }

                                $productId = $prodDataArr['product_id'] = $colValue;

                                $prodData = Product::getAttributesById($colValue, array('product_id', 'product_seller_id', 'product_featured', 'product_approved'));
                            }
                            break;
                        case 'product_identifier':
                            $prodData = Product::getAttributesByIdentifier($colValue, array('product_id', 'product_seller_id', 'product_featured', 'product_approved'));
                            if ($sellerId && is_array($prodData) && !empty($prodData) && $prodData['product_seller_id'] != $sellerId) {
                                $invalid = true;
                            }
                            $productId = false == $prodData ? 0 : $prodData['product_id'];
                            break;
                        case 'product_seller_id':
                            $colValue = 0;
                            if ($userId > 0) {
                                $colValue = $userId;
                            }
                            break;
                        case 'product_cod_enabled':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }

                            break;
                        case 'product_featured':
                        case 'product_approved':
                        case 'product_active':
                        case 'product_deleted':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'product_added_on':
                            if ($sellerId) {
                                $colValue = date('Y-m-d H:i:s');
                            }
                            break;
                        case 'category_Id':
                            $categoryIds = $colValue;
                            break;
                        case 'category_indentifier':
                            $catArr = array();
                            $catIdentifiers = explode('|', $colValue);
                            if (!empty($catIdentifiers)) {
                                foreach ($catIdentifiers as $val) {
                                    $val = mb_strtolower($val);
                                    if (!array_key_exists($val, $categoryIdentifierArr)) {
                                        $res = $this->array_change_key_case_unicode($this->getAllCategoryIdentifiers(false, $val), CASE_LOWER);
                                        if (!$res) {
                                            continue;
                                        } else {
                                            $categoryIdentifierArr = $categoryIdentifierArr + $res;
                                        }
                                    }
                                    if (isset($categoryIdentifierArr[$val])) {
                                        $catArr[] = $categoryIdentifierArr[$val];
                                    }
                                }
                            }
                            $categoryIds = implode(',', $catArr);
                            break;
                        case 'brand_identifier':
                            $columnKey = 'product_brand_id';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $brandIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllBrandsArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $brandIdentifierArr = $brandIdentifierArr + $res;
                                }
                            }
                            $colValue = isset($brandIdentifierArr[$colValue]) ? $brandIdentifierArr[$colValue] : 0;
                            break;
                        case 'product_type_identifier':
                            $columnKey = 'product_type';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $prodTypeIdentifierArr)) {
                                $invalid = true;
                            } else {
                                $colValue = $prodTypeIdentifierArr[$colValue];
                            }
                            $prodType = $colValue;
                            break;
                        case 'tax_category_id':
                            $taxCatId = $colValue;
                            break;
                        case 'tax_category_identifier':
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $taxCategoryArr)) {
                                $res = $this->array_change_key_case_unicode($this->getTaxCategoriesArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $taxCategoryArr = $taxCategoryArr + $res;
                                }
                            }
                            $taxCatId = isset($taxCategoryArr[$colValue]) ? $taxCategoryArr[$colValue] : 0;
                            break;
                            
                        case 'tax_category_id_rent':
                            $taxCatIdRent = $colValue;
                            break;
                        case 'tax_category_identifier_rent':
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $taxCategoryArr)) {
                                $res = $this->array_change_key_case_unicode($this->getTaxCategoriesArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $taxCategoryArr = $taxCategoryArr + $res;
                                }
                            }
                            $taxCatIdRent = isset($taxCategoryArr[$colValue]) ? $taxCategoryArr[$colValue] : 0;
                            break;    
                            
                            
                        case 'product_ship_package_id':
                            $columnKey = 'product_ship_package';
                            break;
                        case 'product_ship_package_identifier':
                            $columnKey = 'product_ship_package';
                            
                            if (!array_key_exists($colValue, $shippingPackages)) {
                                $invalid = true;
                            } else {
                                $colValue = $shippingPackages[$colValue];
                            }
                            
                            break;
                        case 'shipping_profile_id':
                            $columnKey = 'shippro_shipprofile_id';
                            if (!array_key_exists($colValue, $shippingProfiles)) {
                                $invalid = true;
                            }
                            break;
                        case 'shipping_profile_identifier':
                            $columnKey = 'shippro_shipprofile_id';
                            $shipBy = $userId;
                            if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                                    $shipBy = 0;
                                }

                                if (!array_key_exists($colValue, $shippingProfiles)) {
                                    $invalid = true;
                                } else {
                                    $colValue = isset($shippingProfiles[$colValue][$shipBy]) ? $shippingProfiles[$colValue][$shipBy] : 0;
                                }
                            
                            break;
                        case 'product_dimension_unit_identifier':
                            $columnKey = 'product_dimension_unit';
                            if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == Product::PRODUCT_TYPE_PHYSICAL) {
                                if (!array_key_exists($colValue, $lengthUnitsArr)) {
                                    $invalid = true;
                                } else {
                                    $colValue = $lengthUnitsArr[$colValue];
                                }
                            } else {
                                $colValue = '';
                            }

                            break;
                        case 'product_weight_unit_identifier':
                            $columnKey = 'product_weight_unit';
                            
                                if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == Product::PRODUCT_TYPE_PHYSICAL) {
                                    if (!array_key_exists($colValue, $weightUnitsArr)) {
                                        $invalid = true;
                                    } else {
                                        $colValue = $weightUnitsArr[$colValue];
                                    }
                                } else {
                                    $colValue = '';
                                }
                            
                            break;
                        case 'product_warranty':
                            $colValue = FatUtility::int($colValue);
                            if (0 > $colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'product_fulfillment_type':
                            $colValue = str_replace(' ', '_', mb_strtolower($colValue));
                            switch ($colValue) {
                                case 'shipped_only':
                                    $colValue = Shipping::FULFILMENT_SHIP;
                                    break;
                                case 'pickup_only':
                                    $colValue = Shipping::FULFILMENT_PICKUP;
                                    break;
                                case 'shipped_and_pickup':
                                    $colValue = Shipping::FULFILMENT_ALL;
                                    break;
                                default:
                                    $colValue = Shipping::FULFILMENT_SHIP;
                                    break;
                            }
                            $productId = FatUtility::int($productId);
                            $colValue = Product::setProductFulfillmentType($productId, $sellerId, $colValue);
                            break;
                    }


                    if (true == $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if (in_array($columnKey, array('product_name', 'product_description', 'product_youtube_video'))) {
                            $prodlangDataArr[$columnKey] = $colValue;
                        } elseif ('product_warranty' == $columnKey) {
                            $prodSepc = [
                                $columnKey => $colValue
                            ];
                        } else {
                            if (in_array($columnKey, array('tax_category_id', 'tax_category_identifier', 'tax_category_identifier_rent', 'tax_category_id_rent'))) {
                                continue;
                            }
                            if (in_array($columnKey, array('category_Id', 'category_indentifier'))) {
                                continue;
                            }

                            $prodDataArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($prodDataArr)) {
                $prodDataArr['product_type'] = Product::PRODUCT_TYPE_PHYSICAL;
                $prodDataArr['product_added_on'] = date('Y-m-d H:i:s');
                $prodDataArr['product_added_by_admin_id'] = (1 > $userId) ? applicationConstants::YES : applicationConstants::NO;
                $shippro_shipprofile_id = 0;
                if (array_key_exists('shippro_shipprofile_id', $prodDataArr)) {
                    $shippro_shipprofile_id = $prodDataArr['shippro_shipprofile_id'];
                    unset($prodDataArr['shippro_shipprofile_id']);
                }
                $newRecord = false;
                if (!empty($prodData) && $prodData['product_id'] && (!$sellerId || ($sellerId && $prodData['product_seller_id'] == $sellerId))) {
                    unset($prodData['product_seller_id']);
                    $productId = $prodData['product_id'];

                    if ($sellerId) {
                        $prodDataArr['product_approved'] = $prodData['product_approved'];
                        unset($prodDataArr['product_added_on']);
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && Product::getActiveCount($userId, $productId) >= $userProdUploadLimit[$userId]) {
                        $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        continue;
                    }
                    $where = array('smt' => 'product_id = ?', 'vals' => array($productId));
                    $this->db->updateFromArray(Product::DB_TBL, $prodDataArr, $where);

                    $shippedByUserId = (0 < $userId) ? $userId : 0;
                    if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                        $shippedByUserId = 0;
                    }

                    if ($sellerId && $this->isDefaultSheetData($langId)) {
                        $tempData = array(
                            'pti_product_id' => $productId,
                            'pti_product_temp_id' => $productId,
                            'pti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_PRODUCT_IDS, array('smt' => 'pti_product_id = ? and pti_user_id = ?', 'vals' => array($productId, $userId)));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_PRODUCT_IDS, $tempData, false, array(), $tempData);
                    }
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        unset($prodDataArr['product_id']);
                        if ($sellerId) {
                            unset($prodDataArr['product_featured']);
                            if (FatApp::getConfig("CONF_CUSTOM_PRODUCT_REQUIRE_ADMIN_APPROVAL", FatUtility::VAR_INT, 1)) {
                                $prodDataArr['product_approved'] = applicationConstants::NO;
                            }
                        }

                        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && Product::getActiveCount($userId) >= $userProdUploadLimit[$userId]) {
                            $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        }

                        $shippedByUserId = (0 < $userId) ? $userId : 0;
                        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                            $shippedByUserId = 0;
                        }

                        if (!$this->db->insertFromArray(Product::DB_TBL, $prodDataArr, false, array(), $prodDataArr)) {
                            FatUtility::dieJsonError($this->db->getError());
                        }

                        $newRecord = true;
                        // echo $this->db->getError();
                        $productId = $this->db->getInsertId();

                        if ($sellerId) {
                            $tempData = array(
                                'pti_product_id' => $productId,
                                'pti_product_temp_id' => $productId,
                                'pti_user_id' => $userId,
                            );
                            $this->db->deleteRecords(Importexport::DB_TBL_TEMP_PRODUCT_IDS, array('smt' => 'pti_product_id = ? and pti_user_id = ?', 'vals' => array($productId, $userId)));
                            $this->db->insertFromArray(Importexport::DB_TBL_TEMP_PRODUCT_IDS, $tempData, false, array(), $tempData);
                        }
                    }
                }

                if (!empty($productId)) {
                    $prodSepc['ps_product_id'] = $productId;
                    $productSpecificsObj = new ProductSpecifics($productId);
                    $productSpecificsObj->assignValues($prodSepc);
                    $prodSepcData = $productSpecificsObj->getFlds();
                    if (!$productSpecificsObj->addNew(array(), $prodSepcData)) {
                        FatUtility::dieJsonError($productSpecificsObj->getError());
                    }

                    if ($this->isDefaultSheetData($langId)) {
                        $shipProProdData = [];
                        $productSellerShiping = array(
                            'ps_product_id' => $productId,
                            'ps_user_id' => $shippedByUserId,
                        );

                        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && $newRecord == true && 0 < $sellerId) {
                            if (!ShippingProfileProduct::isShippingProfileLinked($productId)) {
                                $shipProProdData = array(
                                    'shippro_shipprofile_id' => $adminDefaultShipProfileId,
                                    'shippro_product_id' => $productId,
                                    'shippro_user_id' => 0
                                );
                            }
                        }
                        if ($shippro_shipprofile_id > 0) {
                            $shipProProdData = array(
                                'shippro_shipprofile_id' => $shippro_shipprofile_id,
                                'shippro_product_id' => $productId,
                                'shippro_user_id' => $shippedByUserId
                            );

                            $productSellerShiping = array_merge($productSellerShiping, $prodShippingArr);
                            FatApp::getDb()->insertFromArray(Product::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping);
                        }

                        if (!empty($shipProProdData)) {
                            $spObj = new ShippingProfileProduct();
                            $spObj->addProduct($shipProProdData);
                        }
                    }

                    /* Lang Data [ */
                    $langData = array(
                        'productlang_product_id' => $productId,
                        'productlang_lang_id' => $langId,
                    );

                    $langData = $langData + $prodlangDataArr;

                    $this->db->insertFromArray(Product::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */

                    if ($this->isDefaultSheetData($langId)) {
                        /* Product Categories [ */
                        $this->db->deleteRecords(Product::DB_TBL_PRODUCT_TO_CATEGORY, array('smt' => Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id = ?', 'vals' => array($productId)));

                        $categoryIdsArr = explode(',', $categoryIds);
                        if (!empty($categoryIdsArr)) {
                            foreach ($categoryIdsArr as $catId) {
                                $catData = array(
                                    'ptc_product_id' => $productId,
                                    'ptc_prodcat_id' => $catId
                                );
                                $this->db->insertFromArray(Product::DB_TBL_PRODUCT_TO_CATEGORY, $catData);
                            }
                        }
                        /* ] */

                        /* Tax Category [ */
                        $this->db->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, array('smt' => 'ptt_product_id = ? and ptt_seller_user_id = ? AND ptt_type = ', 'vals' => array($productId, $userId, SellerProduct::PRODUCT_TYPE_PRODUCT)));
                        if ($taxCatId && $taxCatIdRent) {
                            $this->db->insertFromArray(Tax::DB_TBL_PRODUCT_TO_TAX, array('ptt_product_id' => $productId, 'ptt_taxcat_id' => $taxCatId, 'ptt_seller_user_id' => $userId, 'ptt_taxcat_id_rent' => $taxCatIdRent, 'ptt_type' => SellerProduct::PRODUCT_TYPE_PRODUCT));
                        }
                        /* ] */
                    }
                }
            } else {
                $errInSheet = true;
            }
        }

        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }

        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }

        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function exportProductOptions($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', Product::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', Option::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id');
        $srch->addMultipleFields(array('option_id', 'option_identifier', 'product_id', 'product_identifier'));
        $srch->doNotCalculateRecords();
        switch ($this->actionType) {
            case self::ACTION_ADMIN_PRODUCTS:
                $srch->addCondition('tp.product_added_by_admin_id', '>', 0);
                break;
            case self::ACTION_SELLER_PRODUCTS:
                if ($userId) {
                    $srch->addCondition('tp.product_seller_id', '=', $userId);
                } else {
                    $srch->addCondition('tp.product_seller_id', '>', 0);
                }
                break;
            default:
                if ($userId) {
                    $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
                    $cnd->attachCondition('tp.product_seller_id', '=', 0);
                }
                break;
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductOptionColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductOptions($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $optionIdentifierArr = array();
        $prodArr = array();

        $coloumArr = $this->getProductOptionColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $optionsArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateProdOptionFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array('product_identifier', 'option_identifier'))) {
                        $colValue = mb_strtolower($colValue);
                        if ('product_identifier' == $columnKey) {
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = $prodIndetifierArr + $res;
                                }
                            }
                            $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                        } else {
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllOptions(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = $optionIdentifierArr + $res;
                                }
                            }
                            $colValue = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array('product_id', 'product_identifier'))) {
                        $columnKey = 'prodoption_product_id';

                        if ($userId) {
                            $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                        }

                        $productId = $colValue;
                    }

                    if (in_array($columnKey, array('option_id', 'option_identifier'))) {
                        $columnKey = 'prodoption_option_id';
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $optionsArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($optionsArr)) {
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_OPTION, array('smt' => 'prodoption_product_id = ? ', 'vals' => array($productId)));
                }

                $this->db->insertFromArray(Product::DB_PRODUCT_TO_OPTION, $optionsArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }

        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function exportProductTags($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_TAG, 'INNER JOIN', Product::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_TAG_PREFIX . 'product_id');
        $srch->joinTable(Tag::DB_TBL, 'INNER JOIN', Tag::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id');
        $srch->addMultipleFields(array('tag_id', 'tag_identifier', 'product_id', 'product_identifier'));
        $srch->doNotCalculateRecords();
        switch ($this->actionType) {
            case self::ACTION_ADMIN_PRODUCTS:
                $srch->addCondition('tp.product_added_by_admin_id', '>', 0);
                break;
            case self::ACTION_SELLER_PRODUCTS:
                if ($userId) {
                    $srch->addCondition('tp.product_seller_id', '=', $userId);
                } else {
                    $srch->addCondition('tp.product_seller_id', '>', 0);
                }
                break;
            default:
                if ($userId) {
                    $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
                    $cnd->attachCondition('tp.product_seller_id', '=', 0);
                }
                break;
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductTagColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductTags($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);

        $rowIndex = 1;
        $prodIndetifierArr = array();
        $tagIndetifierArr = array();
        $prodArr = array();

        $coloumArr = $this->getProductTagColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $tagsArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Tag::validateProdTagsFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if (in_array($columnKey, array('product_identifier', 'tag_identifier'))) {
                        $colValue = mb_strtolower($colValue);
                        if ('product_identifier' == $columnKey) {
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = ($prodIndetifierArr + $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                        } else {
                            if (!array_key_exists($colValue, $tagIndetifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllTags(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $tagIndetifierArr = ($tagIndetifierArr + $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $tagIndetifierArr) ? $tagIndetifierArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array('product_id', 'product_identifier'))) {
                        $columnKey = 'ptt_product_id';

                        if ($userId) {
                            $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                        }

                        $productId = $colValue;
                    }

                    if (in_array($columnKey, array('tag_id', 'tag_identifier'))) {
                        $columnKey = 'ptt_tag_id';
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $tagsArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($tagsArr)) {
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_TAG, array('smt' => 'ptt_product_id = ? ', 'vals' => array($productId)));
                }

                $this->db->insertFromArray(Product::DB_PRODUCT_TO_TAG, $tagsArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function exportProductSpecification($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'INNER JOIN', Product::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id');
        $srch->addCondition('prodspec_is_file', '=', 0);
        $srch->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT OUTER JOIN', Product::DB_PRODUCT_SPECIFICATION_PREFIX . 'id = ' . Product::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'prodspec_id');
        $srch->addMultipleFields(array('prodspec_id', 'prodspeclang_lang_id', 'prodspec_name', 'prodspec_value', 'prodspec_group', 'product_id', 'product_identifier', 'prodspec_identifier'));
        $srch->joinTable(Language::DB_TBL, 'INNER JOIN', 'language_id = prodspeclang_lang_id');
        $srch->doNotCalculateRecords();
        switch ($this->actionType) {
            case self::ACTION_ADMIN_PRODUCTS:
                $srch->addCondition('tp.product_added_by_admin_id', '>', 0);
                break;
            case self::ACTION_SELLER_PRODUCTS:
                if ($userId) {
                    $srch->addCondition('tp.product_seller_id', '=', $userId);
                } else {
                    $srch->addCondition('tp.product_seller_id', '>', 0);
                }
                break;
            default:
                if ($userId) {
                    $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'OR');
                    $cnd->attachCondition('tp.product_seller_id', '=', 0);
                }
                break;
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addCondition('language_active', '=', applicationConstants::ACTIVE);

        $srch->addOrder('product_id');
        $srch->addOrder('prodspec_id');

        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductSpecificationColoumArr($langId);

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */
        $languageCodes = Language::getAllCodesAssoc();

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('prodspeclang_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['prodspeclang_lang_id']];
                }
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductSpecifications($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $prodArr = array();
        $langArr = array();
        $languageCodes = Language::getAllCodesAssoc();
        $languageCodes = array_flip($languageCodes);

        $prodspec_id = 0;

        $coloumArr = $this->getProductSpecificationColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodSpecArr = $prodSpecLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = ProdSpecification::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_identifier' == $columnKey) {
                                $colValue = mb_strtolower($colValue);
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue), CASE_LOWER);

                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = ($prodIndetifierArr + $res);
                                    }
                                }
                                $productId = $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            } else {
                                $productId = $colValue;
                            }

                            if ($userId) {
                                $productId = $colValue = $this->getCheckAndSetProductIdByTempId($colValue, $userId);
                            }
                            break;
                        case 'prodspeclang_lang_id':
                            $languageId = $colValue;
                            break;
                        case 'prodspeclang_lang_code':
                            $columnKey = 'prodspeclang_lang_id';
                            $colValue = array_key_exists($colValue, $languageCodes) ? $languageCodes[$colValue] : 0;
                            if (0 >= $colValue) {
                                $invalid = true;
                            }
                            $languageId = $colValue;
                            break;
                    }


                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if (in_array($columnKey, array('prodspeclang_lang_id', 'prodspec_name', 'prodspec_value', 'prodspec_group'))) {
                            $prodSpecLangArr[$columnKey] = $colValue;
                        } else {
                            $prodSpecArr[$columnKey] = $colValue;
                        }
                    }
                }
            }
            
            if (false === $errorInRow && count($prodSpecArr)) {
                $prodSpecArr['prodspec_is_file'] = 0;
                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;

                    $srch = new SearchBase(Product::DB_PRODUCT_SPECIFICATION);
                    $srch->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT OUTER JOIN', 'prodspeclang_prodspec_id = prodspec_id AND prodspeclang_lang_id = '. FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1));
                    $srch->addCondition(Product::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id', '=', $productId);
                    $srch->addCondition('prodspec_is_file', '=', 0);
                    $rs = $srch->getResultSet();
                    $res = FatApp::getDb()->fetchAll($rs);
                    foreach ($res as $val) {
                        $this->db->deleteRecords(Product::DB_PRODUCT_LANG_SPECIFICATION, array('smt' => 'prodspeclang_prodspec_id = ? ', 'vals' => array($val['prodspec_id'])));
                    }
                    $this->db->deleteRecords(Product::DB_PRODUCT_SPECIFICATION, array('smt' => 'prodspec_product_id = ? AND prodspec_is_file =? ', 'vals' => array($productId, 0)));
                }

                if (!in_array($languageId, $langArr)) {
                    $langArr[] = $languageId;
                    if (!$prodspec_id) {
                        $this->db->insertFromArray(Product::DB_PRODUCT_SPECIFICATION, array('prodspec_product_id' => $productId, 'prodspec_identifier' => $prodSpecArr['prodspec_identifier']));
                        $prodspec_id = $this->db->getInsertId();
                    }
                } else {
                    // continue lang loop
                    $langArr = array();
                    $langArr[] = $languageId;
                    $this->db->insertFromArray(Product::DB_PRODUCT_SPECIFICATION, array('prodspec_product_id' => $productId, 'prodspec_identifier' => $prodSpecArr['prodspec_identifier']));
                    $prodspec_id = $this->db->getInsertId();
                }

                $langData = array(
                    'prodspeclang_prodspec_id' => $prodspec_id
                );
                $langData = array_merge($langData, $prodSpecLangArr);

                $this->db->insertFromArray(Product::DB_PRODUCT_LANG_SPECIFICATION, $langData);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function exportProductShipping($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(Product::DB_PRODUCT_TO_SHIP, 'INNER JOIN', Product::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_SHIP_PREFIX . 'prod_id', 'tpsr');
        $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT OUTER JOIN', ShippingCompanies::DB_TBL_PREFIX . 'id = tpsr.pship_company', 'tsc');
        $srch->joinTable(ShippingDurations::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_duration=tsd.sduration_id', 'tsd');
        $srch->joinTable(ShippingMethods::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_method = tsm.shippingapi_id', 'tsm');
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_country = c.country_id', 'c');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'tpsr.pship_user_id = u.user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'tpsr.pship_user_id = uc.credential_user_id', 'uc');
        $srch->addMultipleFields(array('product_id', 'product_identifier', 'scompany_id', 'scompany_identifier', 'shippingapi_id', 'shippingapi_identifier', 'sduration_id', 'sduration_identifier', 'user_id', 'credential_username', 'country_id', 'country_code', 'pship_charges', 'pship_additional_charges'));
        $srch->doNotCalculateRecords();
        switch ($this->actionType) {
            case self::ACTION_ADMIN_PRODUCTS:
                $srch->addDirectCondition("( tp.product_seller_id = 0 and tpsr.pship_user_id = 0)");
                break;
            case self::ACTION_SELLER_PRODUCTS:
                if ($userId) {
                    $srch->addDirectCondition("( ( tp.product_seller_id = '" . $userId . "' and (tpsr.pship_user_id = '" . $userId . "' or tpsr.pship_user_id = 0)) )");
                } else {
                    $srch->addDirectCondition("( ( tp.product_seller_id > 0 and (tpsr.pship_user_id > 0 or tpsr.pship_user_id = 0)))");
                }
                break;
            default:
                if ($userId) {
                    $srch->addDirectCondition("( ( tp.product_seller_id = '" . $userId . "' and (tpsr.pship_user_id = '" . $userId . "' or tpsr.pship_user_id = 0)) or (tp.product_seller_id = 0 and (tpsr.pship_user_id = '" . $userId . "' or tpsr.pship_user_id = 0)))");
                }
                break;
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductShippingColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('user_id' == $columnKey) {
                    $colValue = $colValue == '' ? 0 : $colValue;
                }
                if ('credential_username' == $columnKey) {
                    $colValue = !empty($row['credential_username']) ? $row['credential_username'] : Labels::getLabel('LBL_Admin', $langId);
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductShipping($csvFilePointer, $post, $langId, $userId = null)
    {
        $sellerId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $prodArr = array();
        $usernameArr = array();
        $scompanyIdentifierArr = array();
        $durationIdentifierArr = array();
        $countryCodeArr = array();

        $coloumArr = $this->getProductShippingColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodShipArr = array();
            $errorInRow = false;
            $breakForeach = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Product::validateShippingFields($columnKey, $columnTitle, $colValue, $langId);

                if ($errMsg) {
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_identifier' == $columnKey) {
                                $colValue = mb_strtolower($colValue);
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = ($prodIndetifierArr + $res);
                                    }
                                }
                                $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            }
                            $productId = $colValue;

                            /* Product Ship By Seller [ */
                            $srch = new ProductSearch($langId);
                            $srch->joinProductShippedBySeller($sellerId);
                            $srch->addCondition('psbs_user_id', '=', $sellerId);
                            $srch->addCondition('product_id', '=', $productId);
                            $srch->addFld('psbs_user_id');
                            $rs = $srch->getResultSet();
                            $shipBySeller = FatApp::getDb()->fetch($rs);
                            /* ] */

                            if (empty($shipBySeller) && 0 < $sellerId) {
                                $colValue = $productId = $this->getCheckAndSetProductIdByTempId($productId, $sellerId);
                            }

                            if (1 > $productId) {
                                $invalid = true;
                            }
                            $columnKey = 'pship_prod_id';
                            break;
                        case 'user_id':
                        case 'credential_username':
                            if ($this->settings['CONF_USE_USER_ID']) {
                                $userId = $colValue;
                            } else {
                                $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);
                                $colValue = mb_strtolower($colValue);
                                if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                                    $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $usernameArr = ($usernameArr + $res);
                                    }
                                }
                                $userId = $colValue = array_key_exists($colValue, $usernameArr) ? FatUtility::int($usernameArr[$colValue]) : 0;
                            }

                            if (0 < $sellerId && ($sellerId != $userId || 1 > $userId)) {
                                $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                                $breakForeach = true;
                            }

                            $columnKey = 'pship_user_id';
                            break;
                        case 'country_code':
                        case 'country_id':
                            $colValue = mb_strtolower($colValue);
                            if ('country_code' == $columnKey && !array_key_exists($colValue, $countryCodeArr)) {
                                $res = $this->array_change_key_case_unicode($this->getCountriesArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $countryCodeArr = ($countryCodeArr + $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $countryCodeArr) ? $countryCodeArr[$colValue] : -1;
                            $columnKey = 'pship_country';
                            break;
                        case 'scompany_id':
                            $columnKey = 'pship_company';
                            break;
                        case 'scompany_identifier':
                            $columnKey = 'pship_company';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $scompanyIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllShippingCompany(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $scompanyIdentifierArr = ($scompanyIdentifierArr + $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $scompanyIdentifierArr) ? $scompanyIdentifierArr[$colValue] : 0;
                            break;
                        case 'sduration_id':
                            $columnKey = 'pship_duration';
                            break;
                        case 'sduration_identifier':
                            $columnKey = 'pship_duration';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $durationIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllShippingDurations(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $durationIdentifierArr = ($durationIdentifierArr + $res);
                                }
                            }
                            $colValue = array_key_exists($colValue, $durationIdentifierArr) ? $durationIdentifierArr[$colValue] : 0;
                            if (0 >= $colValue) {
                                $invalid = true;
                            }
                            break;
                    }

                    if (true === $invalid) {
                        if ('' == $errMsg) {
                            $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_{column-name}_is_invalid", $langId));
                        }
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $prodShipArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodShipArr)) {
                $data = array(
                    'pship_method' => ShippingCompanies::MANUAL_SHIPPING,
                );
                $data = array_merge($prodShipArr, $data);

                if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $where = array('smt' => 'pship_prod_id = ? ', 'vals' => array($productId));
                    if ($sellerId) {
                        $where = array('smt' => 'pship_prod_id = ? and pship_user_id = ?', 'vals' => array($productId, $sellerId));
                    }
                    $this->db->deleteRecords(Product::DB_PRODUCT_TO_SHIP, $where);
                }
                $this->db->insertFromArray(Product::DB_PRODUCT_TO_SHIP, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductMedia($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null, $type = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = Product::getSearchObject();
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'product_id = afile_record_id and ( afile_type = ' . AttachedFile::FILETYPE_PRODUCT_IMAGE . ')');
        $srch->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'ov.optionvalue_id = afile_record_subid', 'ov');
        $srch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
        $srch->doNotCalculateRecords();
        if ($userId) {
            $srch->addCondition('tp.product_seller_id', '=', $userId);
        } else {
            $opr = $type == Importexport::TYPE_SELLER_PRODUCTS ? '>' : '=';
            $srch->addCondition('tp.product_seller_id', $opr, 0);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }

        $srch->addMultipleFields(array('product_id', 'product_identifier', 'afile_record_id', 'afile_record_subid', 'afile_type', 'afile_lang_id', 'afile_screen', 'afile_physical_path', 'afile_name', 'afile_display_order', 'optionvalue_identifier', 'option_identifier', 'optionvalue_id', 'option_id'));
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getProductMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['afile_lang_id']];
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importProductCatalogMedia($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $optionValueIndetifierArr = array();
        $optionIdentifierArr = array();
        $prodTempArr = array();
        $prodArr = array();
        $selProdValidOptionArr = array();

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $coloumArr = $this->getProductMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $breakForeach = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodCatalogMediaArr = array();
            $errorInRow = false;
            $productId = $optionId = 0;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Product::validateMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'product_id':
                        case 'product_identifier':
                            if ('product_id' == $columnKey) {
                                $productId = $colValue;
                            }
                            if ('product_identifier' == $columnKey) {
                                $colValue = mb_strtolower($colValue);
                                if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                    $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $prodIndetifierArr = ($prodIndetifierArr + $res);
                                    }
                                }
                                $colValue = $productId = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            }
                            $columnKey = 'afile_record_id';

                            if (!empty($userId)) {
                                $colValue = $productId = $this->getCheckAndSetProductIdByTempId($productId, $userId);
                            }

                            if (1 > $colValue) {
                                $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                                $invalid = true;
                                $breakForeach = true;
                            }

                            break;
                        case 'option_id':
                        case 'option_identifier':
                            if ('option_id' == $columnKey) {
                                $optionId = $colValue;
                            }
                            if ('option_identifier' == $columnKey) {
                                $optionId = 0;
                                $colValue = mb_strtolower($colValue);
                                if (!empty($colValue) && !array_key_exists($colValue, $optionIdentifierArr)) {
                                    $res = $this->array_change_key_case_unicode($this->getAllOptions(false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    }
                                    $optionIdentifierArr = ($optionIdentifierArr + $res);
                                }
                                $colValue = $optionId = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                            }

                            if (!array_key_exists($productId, $selProdValidOptionArr)) {
                                $selProdValidOptionArr[$productId] = array();
                                $optionSrch = Product::getSearchObject();
                                $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', 'tp.product_id = po.prodoption_product_id', 'po');
                                $optionSrch->addCondition('product_id', '=', $productId);
                                $optionSrch->addMultipleFields(array('prodoption_option_id'));
                                $optionSrch->doNotCalculateRecords();
                                $optionSrch->doNotLimitRecords();
                                $rs = $optionSrch->getResultSet();
                                $db = FatApp::getDb();
                                while ($rowOptions = $db->fetch($rs)) {
                                    $selProdValidOptionArr[$productId][] = $rowOptions['prodoption_option_id'];
                                }
                                if ($optionId && !in_array($optionId, $selProdValidOptionArr[$productId])) {
                                    $invalid = true;
                                }
                            }
                            break;
                        case 'optionvalue_id':
                        case 'optionvalue_identifier':
                            if ('optionvalue_id' == $columnKey) {
                                $columnKey = 'afile_record_subid';
                                $optionValueId = $colValue;
                            }
                            if ('optionvalue_identifier' == $columnKey) {
                                $columnKey = 'afile_record_subid';
                                $optionValueId = 0;
                                $optionValueIndetifierArr[$optionId] = array_key_exists($optionId, $optionValueIndetifierArr) ? $optionValueIndetifierArr[$optionId] : array();
                                $colValue = mb_strtolower($colValue);
                                if (!empty($colValue) && !array_key_exists($colValue, $optionValueIndetifierArr[$optionId])) {
                                    $res = $this->array_change_key_case_unicode($this->getAllOptionValues($optionId, false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    }
                                    $optionValueIndetifierArr[$optionId] = ($optionValueIndetifierArr[$optionId] + $res);
                                }
                                $colValue = $optionValueId = isset($optionValueIndetifierArr[$optionId][$colValue]) ? $optionValueIndetifierArr[$optionId][$colValue] : 0;
                            }
                            break;
                        case 'afile_lang_code':
                            $columnKey = 'afile_lang_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = !empty($errMsg) ? $errMsg : str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        if ($breakForeach) {
                            break;
                        }
                    } else {
                        $prodCatalogMediaArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodCatalogMediaArr)) {
                unset($prodCatalogMediaArr['option_identifier']);
                unset($prodCatalogMediaArr['option_id']);
                $fileType = AttachedFile::FILETYPE_PRODUCT_IMAGE;

                $prodCatalogMediaArr['afile_type'] = $fileType;

                $saveToTempTable = false;
                $isUrlArr = parse_url($prodCatalogMediaArr['afile_physical_path']);
                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $prodCatalogMediaArr['afile_downloaded'] = applicationConstants::NO;
                    $prodCatalogMediaArr['afile_unique'] = applicationConstants::NO;
                    if (!in_array($productId, $prodTempArr)) {
                        $prodTempArr[] = $productId;
                        $this->db->deleteRecords(
                                AttachedFile::DB_TBL_TEMP, array(
                            'smt' => 'afile_type = ? AND afile_record_id = ?',
                            'vals' => array($fileType, $productId)
                                )
                        );
                    }
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                } else {
                    if (!in_array($productId, $prodArr)) {
                        $prodArr[] = $productId;
                        $this->db->deleteRecords(
                                AttachedFile::DB_TBL, array(
                            'smt' => 'afile_type = ? AND afile_record_id = ?',
                            'vals' => array($fileType, $productId)
                                )
                        );
                    }

                    $physical_path = explode('/', $prodCatalogMediaArr['afile_physical_path']);

                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH == $physical_path[0] . '/') {
                        $afileObj = new AttachedFile();

                        $moved = $afileObj->moveAttachment($prodCatalogMediaArr['afile_physical_path'], $fileType, $productId, $prodCatalogMediaArr['afile_record_subid'], $prodCatalogMediaArr['afile_name'], $prodCatalogMediaArr['afile_display_order'], false, $prodCatalogMediaArr['afile_lang_id']);

                        if (false === $moved) {
                            $errMsg = str_replace('{filepath}', $prodCatalogMediaArr['afile_physical_path'], Labels::getLabel("MSG_Invalid_File_{filepath}.", $langId));
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, 'N/A', $errMsg));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProductMedia($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'pa.afile_record_id = sp.selprod_id and afile_type = ' . AttachedFile::FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD, 'pa');
        if ($userId) {
            $srch->addCondition('u.user_id', '=', $userId);
            $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.*', 'sp_l.*', 'pa.*', 'user_id', 'credential_username', 'product_id', 'product_identifier'));

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['afile_lang_id']];
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportSellerProdGeneralData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $userId = FatUtility::int($userId);
        $shipProfileSrch = new SearchBase(ShippingProfileProduct::DB_TBL, 'sppro');
        $shipProfileSrch->doNotCalculateRecords();
        $shipProfileSrch->joinTable(ShippingProfile::DB_TBL, 'INNER JOIN', 'sppro.shippro_shipprofile_id = sprofile.shipprofile_id', 'sprofile');
        $shipProfileSubQuery = $shipProfileSrch->getQuery();
        
        $srch = SellerProduct::getSearchObject($langId, true);
        $srch->joinTable(SellerProductSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'spsrent.sps_selprod_id = sp.selprod_id AND spsrent.selprod_specific_type='. applicationConstants::PRODUCT_FOR_RENT, 'spsrent' );
        
        if ($type == SellerProduct::PRODUCT_TYPE_PRODUCT) {
            $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
            $srch->addMultipleFields(array('sp.*', 'sp_l.*', 'user_id', 'credential_username', 'product_id', 'product_identifier', 'sps.selprod_return_age', 'sps.selprod_cancellation_age', 'spd.*', 'spsrent.selprod_cancellation_age as rental_cancellation_age', 'sppro.shipprofile_identifier'));
            $srch->joinTable('(' . $shipProfileSubQuery . ')', 'LEFT OUTER JOIN', 'sp.selprod_product_id = sppro.shippro_product_id AND sp.selprod_user_id = sppro.shippro_user_id', 'sppro');
        } else {
            $srch->addMultipleFields(array('sp.*', 'sp_l.*', 'user_id', 'credential_username', 'sps.selprod_return_age', 'sps.selprod_cancellation_age'));
        }
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        if ($userId) {
            $srch->addCondition('u.user_id', '=', $userId);
            /* $srch->addCondition('selprod_deleted', '=', applicationConstants::NO); */
        }
       
        $srch->addCondition('selprod_type', '=', $type);
        $srch->doNotCalculateRecords();

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $taxCategoryIdentifierById = [];
        if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
            if (!$this->settings['CONF_USE_TAX_CATEOGRY_ID']) {
                $taxCategoryIdentifierById = $this->getTaxCategoriesArr();
            }
            $headingsArr = $this->getAdddonsGeneralColoumArr($langId, $userId);
        } else {
            $headingsArr = $this->getSelProdGeneralColoumArr($langId, $userId);
        }

        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $conditionArr = Product::getConditionArr($langId);

        while ($row = $this->db->fetch($rs)) {
            /* UPDATE FOR ADDONS EXPORT */
            if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
                $taxData = $this->getTaxCategoryByProductId($row['selprod_id'], $type);
                if (!empty($taxData)) {
                    $row = array_merge($row, $taxData);
                }
            }
            /* ] */
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }
                if ('selprod_condition_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['selprod_condition'], $conditionArr) ? $conditionArr[$row['selprod_condition']] : '';
                }
                if ('sprodata_rental_condition_identifier' == $columnKey) {
                    $colValue = array_key_exists($row['sprodata_rental_condition'], $conditionArr) ? $conditionArr[$row['sprodata_rental_condition']] : '';
                }
                
                if (in_array($columnKey, array('selprod_added_on', 'selprod_available_from', 'sprodata_rental_available_from'))) {
                    $colValue = $this->displayDateTime($colValue);
                }
                if (in_array($columnKey, array('selprod_subtract_stock', 'selprod_track_inventory', 'selprod_active', 'selprod_cod_enabled', 'selprod_deleted', 'sprodata_is_for_sell', 'sprodata_is_for_rent', 'sprodata_rental_active', 'selprod_is_eligible_refund', 'selprod_is_eligible_cancel')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('selprod_fulfillment_type' == $columnKey || 'sprodata_fullfillment_type' == $columnKey) {
                    switch ($colValue) {
                        case Shipping::FULFILMENT_SHIP:
                            $colValue = Labels::getLabel('LBL_SHIPPED_ONLY', $langId);
                            break;
                        case Shipping::FULFILMENT_PICKUP:
                            $colValue = Labels::getLabel('LBL_PICKUP_ONLY', $langId);
                            break;
                        case Shipping::FULFILMENT_ALL:
                            $colValue = Labels::getLabel('LBL_SHIPPED_AND_PICKUP', $langId);
                            break;
                        default:
                            $colValue = Labels::getLabel('LBL_SHIPPED_ONLY', $langId);
                            break;
                    }
                }
                
                if ('shipping_profile' == $columnKey) {
                    $colValue = $row['shipprofile_identifier'];
                }
                
                /* [ UPDATE FOR ADDONS */
                if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
                    if ('tax_category_id' == $columnKey) {
                        $colValue = (array_key_exists('ptt_taxcat_id', $row) ? $row['ptt_taxcat_id'] : 0);
                    }
                    if ('tax_category_identifier' == $columnKey) {
                        $colValue = (!empty($row['ptt_taxcat_id']) && array_key_exists($row['ptt_taxcat_id'], $taxCategoryIdentifierById) ? $taxCategoryIdentifierById[$row['ptt_taxcat_id']] : 0);
                    }
                }
                /* ] */
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdGeneralData($csvFilePointer, $post, $langId, $sellerId = null)
    {
        $sellerId = FatUtility::int($sellerId);

        $rowIndex = 1;
        $usernameArr = array();
        $prodIndetifierArr = array();
        $prodTypeArr = array();
        $userProdUploadLimit = array();

        $prodConditionArr = Product::getConditionArr($langId);
        $prodConditionArr = array_flip($prodConditionArr);
        $userId = $sellerId;

        $coloumArr = $this->getSelProdGeneralColoumArr($langId, $sellerId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $selProdGenArr = $selProdGenLangArr = $selProductData = array();
            $errorInRow = false;
            $shippingProfile = '';

            //if(array_key_exists($row['selprod_product_id'], $prodTypeArr))
            $selProdSepc = [];
            $selProdSepcRent = [];
            $productId = 0;
            
            foreach ($coloumArr as $columnKey => $columnTitle) {
                if ($sellerId == 0) {
                    if ($this->settings['CONF_USE_USER_ID']) {
                        $colIndex = $this->headingIndexArr[Labels::getLabel('LBL_User_ID', $langId)];
                        $userSellerId = $this->getCell($row, $colIndex, '');
                    } else {
                        $colIndex = $this->headingIndexArr[Labels::getLabel('LBL_Username', $langId)];
                        $userName = $this->getCell($row, $colIndex, '');
                        $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $userName), CASE_LOWER);
                        $userSellerId = $res[strtolower($userName)];
                    }
                } else {
                    $userSellerId = $sellerId;
                }
                
                
                $checkOption = false;
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateGenDataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'selprod_id':
                            $selprodId = $sellerTempId = $colValue;
                            if ($sellerId) {
                                $userId = $sellerId;
                                $userTempIdData = $this->getTempSelProdIdByTempId($sellerTempId, $sellerId);
                                if (!empty($userTempIdData) && $userTempIdData['spti_selprod_temp_id'] == $sellerTempId) {
                                    $selprodId = $colValue = $userTempIdData['spti_selprod_id'];
                                }
                            }
                            break;
                        case 'selprod_product_id':
                            $productId = $colValue;
                            $checkOption = true;
                            break;
                        case 'product_identifier':
                            $columnKey = 'selprod_product_id';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $prodIndetifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllProductsIdentifiers(false, $colValue, $userSellerId), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $prodIndetifierArr = $prodIndetifierArr + $res;
                                }
                            }

                            $productId = $colValue = array_key_exists($colValue, $prodIndetifierArr) ? $prodIndetifierArr[$colValue] : 0;
                            $checkOption = true;
                            break;
                        case 'shipping_profile':
                            $shippingProfile = $colValue;
                            break;
                            
                        case 'selprod_user_id':
                            $userId = $colValue;
                            break;
                        case 'credential_username':
                            $columnKey = 'selprod_user_id';
                            $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);
                            $colValue = mb_strtolower($colValue);
                            if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $usernameArr = $usernameArr + $res;
                                }
                            }
                            $userId = $colValue = array_key_exists($colValue, $usernameArr) ? $usernameArr[$colValue] : 0;
                            break;
                        case 'sprodata_is_for_sell':
                            if (!$this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'sprodata_is_for_rent':
                            if (!$this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'sprodata_rental_condition_identifier':
                        case 'selprod_condition_identifier':
                            $colValue = mb_strtolower($colValue);
                            $prodConditions = $this->array_change_key_case_unicode($prodConditionArr, CASE_LOWER);
                            $colValue = array_key_exists($colValue, $prodConditions) ? $prodConditions[$colValue] : 0;
                            $productType = Product::getAttributesById($productId, 'product_type');

                            if (0 < $productId && Product::PRODUCT_TYPE_PHYSICAL == $productType && 1 > $colValue) {
                                $invalid = true;
                            }
                            if ($columnKey == 'selprod_condition_identifier') {
                                $columnKey = 'selprod_condition';
                            } else {
                                $columnKey = 'sprodata_rental_condition';
                            }
                            
                            break;
                        case 'selprod_available_from':
                        case 'sprodata_rental_available_from':
                            $colValue = $this->getDateTime($colValue);
                            break;
                        case 'selprod_url_keyword':
                            $urlKeyword = $colValue;
                            break;
                        case 'sprodata_rental_active':
                        case 'selprod_active':
                        case 'selprod_cod_enabled':
                        case 'selprod_deleted':
                            if (!$this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'selprod_return_age':
                        case 'selprod_cancellation_age':
                        case 'rental_cancellation_age':
                            $colValue = FatUtility::int($colValue);
                            if (0 > $colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'sprodata_fullfillment_type':
                        case 'selprod_fulfillment_type':
                            $colValue = str_replace(' ', '_', mb_strtolower($colValue));
                            switch ($colValue) {
                                case 'shipped_only':
                                    $colValue = Shipping::FULFILMENT_SHIP;
                                    break;
                                case 'pickup_only':
                                    $colValue = Shipping::FULFILMENT_PICKUP;
                                    break;
                                case 'shipped_and_pickup':
                                    $colValue = Shipping::FULFILMENT_ALL;
                                    break;
                                default:
                                    $colValue = Shipping::FULFILMENT_SHIP;
                                    break;
                            }
                            $colValue = SellerProduct::setSellerProdFulfillmentType($colValue);
                            break;
                    }
                    /* Check if inventory already added for the product without option [ */
                    if (0 < $productId && true === $checkOption) {
                        $srch = Product::getSearchObject();
                        $srch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'LEFT JOIN', 'product_id = prodoption_product_id', 'tpo');
                        $srch->joinTable(SellerProduct::DB_TBL, 'LEFT JOIN', 'product_id = selprod_product_id', 'sp');
                        $srch->addCondition('selprod_product_id', '=', $productId);
                        $srch->addCondition('selprod_user_id', '=', $userSellerId);
                        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
                        $srch->addMultipleFields(array('selprod_product_id', 'selprod_id', 'prodoption_option_id'));
                        $rs = $srch->getResultSet();
                        $sellerProduct = FatApp::getDb()->fetch($rs);

                        if (!empty($sellerProduct['selprod_id']) && $sellerProduct['selprod_id'] != $selprodId && empty($sellerProduct['prodoption_option_id'])) {
                            $errMsg = Labels::getLabel("LBL_INVENTORY_ALREADY_ADDED", $langId);
                            $invalid = true;
                        }
                    }
                    /* ] */

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId) {
                        if (!array_key_exists($userId, $userProdUploadLimit)) {
                            $userProdUploadLimit[$userId] = SellerPackages::getAllowedLimit($userId, $langId, 'ossubs_inventory_allowed');
                        }
                    }

                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = !empty($errMsg) ? $errMsg : str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        $err = array($rowIndex, ($colIndex + 1), $errMsg);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    } else {
                        if (in_array($columnKey, array('selprod_title', 'selprod_comments', 'selprod_rental_terms'))) {
                            $selProdGenLangArr[$columnKey] = $colValue;
                        } elseif (in_array($columnKey, array('selprod_return_age', 'selprod_cancellation_age'))) {
                            if ('' != $colValue) {
                                $selProdSepc[$columnKey] = $colValue;
                                $selProdSepc['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_SALE;
                            }
                        } elseif(in_array($columnKey, array('rental_cancellation_age'))) {
                            $selProdSepcRent['selprod_cancellation_age'] = $colValue;
                            $selProdSepcRent['selprod_specific_type'] = applicationConstants::PRODUCT_FOR_RENT;
                        } elseif (in_array($columnKey, array('selprod_subtract_stock', 'selprod_track_inventory')) && !$this->settings['CONF_USE_O_OR_1']) {
                            $colValue = ('yes' == strtolower($colValue)) ? 1 : 0;
                            $selProdGenArr[$columnKey] = $colValue;
                        } elseif (in_array($columnKey, array('sprodata_rental_price','sprodata_rental_security','sprodata_rental_stock','sprodata_rental_buffer_days','sprodata_minimum_rental_duration','sprodata_duration_type','sprodata_rental_active','sprodata_rental_available_from','sprodata_rental_condition','sprodata_minimum_rental_quantity', 'sprodata_fullfillment_type'))) {
                            $selProductData[$columnKey] = $colValue; 
                        } else {
                            $selProdGenArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            $userId = (!$sellerId) ? $userId : $sellerId;
            $selProdGenArr['selprod_user_id'] = $userId;
            if (false === $errorInRow && count($selProdGenArr)) {
                $prodData = Product::getAttributesById($productId, array('product_min_selling_price'));
                if (array_key_exists('selprod_price', $selProdGenArr)) {
                    if (is_array($prodData) && array_key_exists('product_min_selling_price', $prodData) && $selProdGenArr['selprod_price'] < $prodData['product_min_selling_price']) {
                        $selProdGenArr['selprod_price'] = $prodData['product_min_selling_price'];
                    }

                    $srch = new SearchBase(SellerProductSpecialPrice::DB_TBL);
                    $srch->addCondition('splprice_selprod_id', '=', $selprodId);
                    $srch->addCondition('splprice_price', '>=', $selProdGenArr['selprod_price']);
                    $srch->addCondition('splprice_end_date', '>=', date('Y-m-d H:i:s'));
                    $srch->addFld('splprice_price');
                    $srch->addOrder('splprice_price', 'DESC');
                    $srch->doNotCalculateRecords();
                    $db = FatApp::getDb();
                    $rs = $srch->getResultSet();
                    $result = $db->fetch($rs);
                    if (is_array($result) && !empty($result)) {
                        $price = CommonHelper::displayMoneyFormat($result['splprice_price']);
                        $errMsg = Labels::getLabel('MSG_SELLING_PRICE_MUST_BE_GREATER_THAN_SPECIAL_PRICE_{SPECIAL-PRICE}', $langId);
                        $errMsg = CommonHelper::replaceStringData($errMsg, ['{SPECIAL-PRICE}' => $price]);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        $errInSheet = true;
                        continue;
                    }
                }

                $selProdGenArr['selprod_added_on'] = date('Y-m-d H:i:s');
                $selProdData = SellerProduct::getAttributesById($selprodId, array('selprod_id', 'selprod_sold_count', 'selprod_user_id'));

                if (!empty($selProdData) && $selProdData['selprod_id'] && ($sellerId  == 0 ||  ( $sellerId > 0 &&  $selProdData['selprod_user_id'] == $sellerId))) {
                    $where = array('smt' => 'selprod_id = ?', 'vals' => array($selprodId));
                    $selProdGenArr['selprod_sold_count'] = $selProdData['selprod_sold_count'];

                    if ($sellerId) {
                        unset($selProdGenArr['selprod_added_on']);
                    }

                    if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId, $selprodId) >= $userProdUploadLimit[$userId]) {
                        $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        $errInSheet = true;
                        continue;
                    }
                    unset($selProdGenArr['shipping_profile']);
                    $this->db->updateFromArray(SellerProduct::DB_TBL, $selProdGenArr, $where);

                    if ($sellerId && $this->isDefaultSheetData($langId)) {
                        $tempData = array(
                            'spti_selprod_id' => $selprodId,
                            'spti_selprod_temp_id' => $sellerTempId,
                            'spti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt' => 'spti_selprod_id = ? and spti_user_id = ?', 'vals' => array($selprodId, $userId)));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                } else {
                    $selProdGenArr['selprod_code'] = $productId . '_';
                    unset($selProdGenArr['selprod_id']);
                    if ($sellerId) {
                        unset($selProdGenArr['selprod_sold_count']);
                    }
                    if ($this->isDefaultSheetData($langId)) {
                        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId) >= $userProdUploadLimit[$userId]) {
                            $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            $errInSheet = true;
                            continue;
                        }
                        unset($selProdGenArr['shipping_profile']);
                        $this->db->insertFromArray(SellerProduct::DB_TBL, $selProdGenArr);
                        $selprodId = $this->db->getInsertId();

                        $tempData = array(
                            'spti_selprod_id' => $selprodId,
                            'spti_selprod_temp_id' => $sellerTempId,
                            'spti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt' => 'spti_selprod_id = ? and spti_user_id = ?', 'vals' => array($selprodId, $userId)));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                }

                if ($selprodId) {
                    $ShippingProfileId = ShippingProfile::getShipProfileIdIdentifier($shippingProfile, $userId);
                    if ($ShippingProfileId > 0) {
                        $shipProProdData = array(
                            'shippro_shipprofile_id' => $ShippingProfileId,
                            'shippro_product_id' => $productId,
                            'shippro_user_id' => $userId
                        );
                        $spObj = new ShippingProfileProduct();
                        $spObj->addProduct($shipProProdData);
                    }
                    $rpsrch = ProductRental::getSearchObject();
                    $rpsrch->addCondition('sprodata_selprod_id', '=', $selprodId);
                    $rprs = $rpsrch->getResultSet();
                    $productData = $this->db->fetch($rprs);
                    $selProductData['sprodata_selprod_id'] = $selprodId;
                    $selProductData['sprodata_is_for_rent'] = 1;
                    $selProductData['sprodata_is_for_sell'] = 1;
                    
                    if (!empty($productData)) {
                        $where = array('smt' => 'sprodata_selprod_id = ?', 'vals' => array($selprodId));
                        $this->db->updateFromArray(ProductRental::DB_TBL, $selProductData, $where);
                    } else {
                        $this->db->insertFromArray(ProductRental::DB_TBL, $selProductData, false, array(), $selProductData);
                    }

                    if (!empty($selProdSepc) && ALLOW_SALE) {
                        $selProdSpecificsObj = new SellerProductSpecifics($selprodId);
                        $selProdSepc['sps_selprod_id'] = $selprodId;
                        $selProdSpecificsObj->assignValues($selProdSepc);
                        if (!$selProdSpecificsObj->addNew(array(), $selProdSepc)) {
                            $errMsg = $selProdSpecificsObj->getError();
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            $errInSheet = true;
                            continue;
                        }
                    }
                    
                    if (!empty($selProdSepcRent)) {
                        $selProdSpecificsObj = new SellerProductSpecifics($selprodId);
                        $selProdSepcRent['sps_selprod_id'] = $selprodId;
                        $selProdSpecificsObj->assignValues($selProdSepcRent);
                        if (!$selProdSpecificsObj->addNew(array(), $selProdSepcRent)) {
                            $errMsg = $selProdSpecificsObj->getError();
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            $errInSheet = true;
                            continue;
                        }
                    }
                    
                    /* Lang Data [ */
                    $langData = array(
                        'selprodlang_selprod_id' => $selprodId,
                        'selprodlang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $selProdGenLangArr);
                    $this->db->insertFromArray(SellerProduct::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */

                    /* Url rewriting [ */
                    if ($this->isDefaultSheetData($langId)) {
                        if (trim($urlKeyword) != '') {
                            $sellerProdObj = new SellerProduct($selprodId);
                            $sellerProdObj->rewriteUrlProduct($urlKeyword);
                            $sellerProdObj->rewriteUrlReviews($urlKeyword);
                            $sellerProdObj->rewriteUrlMoreSellers($urlKeyword);
                        }
                    }
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdOptionData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'spo');
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spo.selprodoption_selprod_id', 'sp');
        $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'spo.selprodoption_optionvalue_id = ov.optionvalue_id', 'ov');
        $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ov_lang.optionvaluelang_optionvalue_id = ov.optionvalue_id AND ov_lang.optionvaluelang_lang_id = ' . $langId, 'ov_lang');
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'o.option_id = ov.optionvalue_option_id', 'o');
        $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'o.option_id = o_lang.optionlang_option_id AND o_lang.optionlang_lang_id = ' . $langId, 'o_lang');
        $srch->addMultipleFields(array('selprodoption_selprod_id', 'o.option_id', 'ov.optionvalue_id', 'option_identifier', 'optionvalue_identifier'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprodoption_selprod_id', '>=', $minId);
            $srch->addCondition('selprodoption_selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprodoption_selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdOptionsColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdOptionData($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionValueIndetifierArr = array();
        $selProdArr = array();
        $selProdOptionsArr = array();
        $selProdValidOptionArr = array();

        $coloumArr = $this->getSelProdOptionsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $errorInRow = false;
            $selprodId = $optionId = 0;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateOptionDataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprodoption_selprod_id' == $columnKey) {
                        $selprodId = $colValue;
                        if ($userId) {
                            $selprodId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                    }

                    if (in_array($columnKey, array('option_id', 'option_identifier'))) {
                        $optionId = $colValue;
                        if ('option_identifier' == $columnKey) {
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllOptions(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = ($optionIdentifierArr + $res);
                                }
                            }
                            $colValue = $optionId = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                            if (1 > $optionId) {
                                $invalid = true;
                            }
                        }

                        if (!array_key_exists($selprodId, $selProdValidOptionArr)) {
                            $selProdValidOptionArr[$selprodId] = array();
                            $optionSrch = SellerProduct::getSearchObject();
                            $optionSrch->joinTable(Product::DB_PRODUCT_TO_OPTION, 'INNER JOIN', 'sp.selprod_product_id = po.prodoption_product_id', 'po');
                            $optionSrch->addCondition('selprod_id', '=', $selprodId);
                            $optionSrch->addMultipleFields(array('prodoption_option_id'));
                            $optionSrch->doNotCalculateRecords();
                            $optionSrch->doNotLimitRecords();

                            $rs = $optionSrch->getResultSet();
                            $db = FatApp::getDb();
                            while ($spRow = $db->fetch($rs)) {
                                $selProdValidOptionArr[$selprodId][] = $spRow['prodoption_option_id'];
                            }

                            if (!in_array($optionId, $selProdValidOptionArr[$selprodId])) {
                                $invalid = true;
                            }
                        }
                    }

                    if (in_array($columnKey, array('optionvalue_id', 'optionvalue_identifier'))) {
                        $optionValueId = $colValue;
                        if ($optionId) {
                            if ('optionvalue_identifier' == $columnKey) {
                                $optionValueId = 0;
                                $optionValueIndetifierArr[$optionId] = array_key_exists($optionId, $optionValueIndetifierArr) ? $optionValueIndetifierArr[$optionId] : array();
                                $colValue = mb_strtolower($colValue);
                                if (!array_key_exists($colValue, $optionValueIndetifierArr[$optionId])) {
                                    $res = $this->array_change_key_case_unicode($this->getAllOptionValues($optionId, false, $colValue), CASE_LOWER);
                                    if (!$res) {
                                        $invalid = true;
                                    } else {
                                        $optionValueIndetifierArr[$optionId] = ($optionValueIndetifierArr[$optionId] + $res);
                                    }
                                }
                                $optionValueId = array_key_exists($colValue, $optionValueIndetifierArr[$optionId]) ? $optionValueIndetifierArr[$optionId][$colValue] : 0;
                            }
                        }
                        $colValue = $optionValueId;
                        if (1 > $colValue) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    }
                }
            }

            if (false === $errorInRow) {
                if (!in_array($selprodId, $selProdArr)) {
                    $selProdArr[] = $selprodId;
                    $where = array('smt' => 'selprodoption_selprod_id = ?', 'vals' => array($selprodId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, $where);
                }
                $selProdOptionsArr[$selprodId]['optionValueIds'][] = $optionValueId;
                $selProdOptionsArr[$selprodId]['row'] = $rowIndex;

                $data = array(
                    'selprodoption_selprod_id' => $selprodId,
                    'selprodoption_option_id' => $optionId,
                    'selprodoption_optionvalue_id' => $optionValueId,
                );

                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, $data, false, array(), $data);
            } else {
                $errInSheet = true;
            }
        }

        if ($selProdOptionsArr) {
            $options = array();
            foreach ($selProdOptionsArr as $k => $v) {
                $productRow = SellerProduct::getAttributesById($k, array('selprod_product_id'));
                if (!$productRow) {
                    $errMsg = Labels::getLabel("MSG_Product_not_found.", $langId);
                    $err = array($v['row'], 'N/A', $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    continue;
                }
                $options['selprod_code'] = $productRow['selprod_product_id'] . '_' . implode('_', $v['optionValueIds']);
                $sellerProdObj = new SellerProduct($k);
                $sellerProdObj->assignValues($options);
                if (!$sellerProdObj->save()) {
                    $errMsg = Labels::getLabel("MSG_Product_not_saved.", $langId);
                    $err = array($v['row'], 'N/A', $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    continue;
                }
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdSeoData($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $metaTabArr = MetaTag::getTabsArr($langId);

        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(MetaTag::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = m.meta_record_id', 'm');
        $srch->joinTable(MetaTag::DB_TBL_LANG, 'LEFT OUTER JOIN', 'm_l.metalang_meta_id = m.meta_id and m_l.metalang_lang_id = ' . $langId, 'm_l');
        /* $srch->addCondition('meta_identifier', '!=', ''); */
        $srch->addCondition('meta_controller', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller']);
        $srch->addCondition('meta_action', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action']);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.selprod_id', 'm.*', 'm_l.*'));

        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdSeoColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdSeoData($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $metaTabArr = MetaTag::getTabsArr($langId);
        $metaSrch = MetaTag::getSearchObject();

        $coloumArr = $this->getSelProdSeoColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdSeoArr = $selProdSeoLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = SellerProduct::validateSEODataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                    }

                    if (in_array($columnKey, array('meta_title', 'meta_keywords', 'meta_description', 'meta_other_meta_tags'))) {
                        $selProdSeoLangArr[$columnKey] = $colValue;
                    } else {
                        $selProdSeoArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($selProdSeoArr)) {
                $data = array(
                    'meta_controller' => $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller'],
                    'meta_action' => $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action'],
                    'meta_record_id' => $selProdId,
                );
                $data = array_merge($data, $selProdSeoArr);

                $srch = clone $metaSrch;
                $srch->addCondition('meta_controller', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller']);
                $srch->addCondition('meta_action', '=', $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action']);
                $srch->addCondition('meta_record_id', '=', $selProdId);
                $srch->addMultipleFields(array('meta_id', 'meta_record_id'));
                $srch->doNotCalculateRecords();
                $srch->setPageSize(1);
                $rs = $srch->getResultSet();
                $row = $this->db->fetch($rs);
                if ($row && $row['meta_record_id'] == $selProdId) {
                    $metaId = $row['meta_id'];
                    $where = array('smt' => 'meta_controller = ? AND meta_action = ? AND meta_record_id = ?', 'vals' => array($metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['controller'], $metaTabArr[MetaTag::META_GROUP_PRODUCT_DETAIL]['action'], $selProdId));
                    $this->db->updateFromArray(MetaTag::DB_TBL, $data, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        unset($data['selprod_id']);
                        $resp = $this->db->insertFromArray(MetaTag::DB_TBL, $data);
                        $metaId = $this->db->getInsertId();
                    }
                }

                if (isset($metaId)) {
                    /* Lang Data [ */
                    $langData = array(
                        'metalang_meta_id' => $metaId,
                        'metalang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $selProdSeoLangArr);
                    $this->db->insertFromArray(MetaTag::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdSpecialPrice($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'INNER JOIN', 'sp.selprod_id = spsp.splprice_selprod_id', 'spsp');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spsp.*', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdSpecialPriceColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */


        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if (in_array($columnKey, array('splprice_start_date', 'splprice_end_date'))) {
                    $colValue = $this->displayDate($colValue);
                }
                if ($columnKey == 'splprice_type') {
                    $colValue = ($colValue == applicationConstants::PRODUCT_FOR_SALE) ? Labels::getLabel('LBL_Sale', $langId)  : Labels::getLabel('LBL_Rent', $langId);
                }
                
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdSpecialPrice($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $persentOrFlatTypeArr = applicationConstants::getPercentageFlatArr($langId);
        $persentOrFlatTypeArr = array_flip($persentOrFlatTypeArr);
        $selProdArr = array();

        $coloumArr = $this->getSelProdSpecialPriceColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $productArr = [];
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $selProdId = 0;
            $rowIndex++;

            $sellerProdSplPriceArr = array();
            $errorInRow = false;
            
            $priceTypeColumnIndex = $this->headingIndexArr[$coloumArr['splprice_type']];
            
            $priceType = $row[$priceTypeColumnIndex];
            $priceType = (strtolower(trim($priceType)) == strtolower(Labels::getLabel('LBL_Sale', $langId))) ? applicationConstants::PRODUCT_FOR_SALE : applicationConstants::PRODUCT_FOR_RENT;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;

                $errMsg = SellerProduct::validateSplPriceFields($columnKey, $columnTitle, $colValue, $langId);
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }

                        if (0 < $selProdId && !array_key_exists($selProdId, $productArr)) {
                            $prodSrch = new ProductSearch($langId);
                            $prodSrch->joinSellerProducts($userId, '', array(), false);
                            $prodSrch->addCondition('selprod_id', '=', $selProdId);
                            $prodSrch->addMultipleFields(array('product_min_selling_price', 'selprod_price', 'selprod_available_from'));
                            $prodSrch->setPageSize(1);
                            $rs = $prodSrch->getResultSet();
                            $productArr[$selProdId] = FatApp::getDb()->fetch($rs);
                        }
                    } else if ('splprice_price' == $columnKey && $priceType == applicationConstants::PRODUCT_FOR_SALE) {
                        if ($colValue < $productArr[$selProdId]['product_min_selling_price'] || $colValue >= $productArr[$selProdId]['selprod_price']) {
                            $str = Labels::getLabel('MSG_Price_must_between_min_selling_price_{minsellingprice}_and_selling_price_{sellingprice}', $langId);
                            $minSellingPrice = CommonHelper::displayMoneyFormat($productArr[$selProdId]['product_min_selling_price'], false, true, true);
                            $sellingPrice = CommonHelper::displayMoneyFormat($productArr[$selProdId]['selprod_price'], false, true, true);

                            $errMsg = CommonHelper::replaceStringData($str, array('{minsellingprice}' => $minSellingPrice, '{sellingprice}' => $sellingPrice));
                            $invalid = true;
                        }
                    } else if ('splprice_start_date' == $columnKey && $priceType == applicationConstants::PRODUCT_FOR_SALE) {
                        if (strtotime($colValue) < strtotime($productArr[$selProdId]['selprod_available_from'])) {
                            $str = Labels::getLabel('MSG_Special_Price_Date_Must_Be_Greater_Or_Than_Equal_To_{availablefrom}', $langId);
                            $errMsg = CommonHelper::replaceStringData($str, array('{availablefrom}' => date('Y-m-d', strtotime($productArr[$selProdId]['selprod_available_from']))));
                            $invalid = true;
                        }
                    }
                    
                    if ($columnKey == 'splprice_type') {
                        $colValue = (strtolower(trim($colValue)) == strtolower(Labels::getLabel('LBL_Sale', $langId))) ? applicationConstants::PRODUCT_FOR_SALE : applicationConstants::PRODUCT_FOR_RENT;
                    }
                    

                    if (in_array($columnKey, array('splprice_start_date', 'splprice_end_date'))) {
                        $colValue = $this->getDateTime($colValue, false);
                    }
                    if (true === $invalid) {
                        $errMsg = !empty($errMsg) ? $errMsg : str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $sellerProdSplPriceArr[$columnKey] = $colValue;
                    }
                }
            }

            unset($sellerProdSplPriceArr['selprod_id']);
            if (false === $errorInRow && count($sellerProdSplPriceArr)) {
                $data = array(
                    'splprice_selprod_id' => $selProdId,
                );
                $data = array_merge($data, $sellerProdSplPriceArr);

                $res = SellerProduct::getSellerProductSpecialPrices($selProdId);
                if (!empty($res)) {
                    if (!in_array($selProdId, $selProdArr)) {
                        $selProdArr[] = $selProdId;
                        $where = array('smt' => 'splprice_selprod_id = ?', 'vals' => array($selProdId));
                        $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, $where);
                    }
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdVolumeDiscount($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProductVolumeDiscount::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spvd.voldiscount_selprod_id', 'spvd');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spvd.voldiscount_min_qty', 'spvd.voldiscount_percentage', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdVolumeDiscountColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdVolumeDiscount($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();

        $coloumArr = $this->getSelProdVolumeDiscountColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdVolDisArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateVolDiscountFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $selProdVolDisArr[$columnKey] = $colValue;
                    }
                }
            }
            unset($selProdVolDisArr['selprod_id']);
            if (false === $errorInRow && count($selProdVolDisArr)) {
                $data = array(
                    'voldiscount_selprod_id' => $selProdId,
                );
                $data = array_merge($data, $selProdVolDisArr);

                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'voldiscount_selprod_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProductVolumeDiscount::DB_TBL, $where);
                }
                $this->db->insertFromArray(SellerProductVolumeDiscount::DB_TBL, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdBuyTogther($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_UPSELL_PRODUCTS, 'INNER JOIN', 'sp.selprod_id = spu.upsell_sellerproduct_id', 'spu');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spu.upsell_sellerproduct_id', 'spu.upsell_recommend_sellerproduct_id', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdBuyTogetherColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdBuyTogther($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();
        $selProdUserArr = array();

        $coloumArr = $this->getSelProdBuyTogetherColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $errorInRow = false;
            $selProdId = 0;
            $selProdBuyTogetherArr = array();

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateBuyTogetherFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'upsell_sellerproduct_id';
                        $selProdId = $colValue;
                        if (0 < $userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }

                        if (!array_key_exists($selProdId, $selProdUserArr)) {
                            $res = SellerProduct::getAttributesById($selProdId, array('selprod_id', 'selprod_user_id'));
                            if (empty($res)) {
                                $invalid = true;
                            } else {
                                $selProdUserArr[$res['selprod_id']] = $res['selprod_user_id'];
                            }
                        }
                    }

                    if ('upsell_recommend_sellerproduct_id' == $columnKey) {
                        $upselProdId = $colValue;
                        if (0 < $userId) {
                            $upselProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($upselProdId, $userId);
                        }

                        if (1 > $upselProdId) {
                            $invalid = true;
                        }

                        if (!array_key_exists($upselProdId, $selProdUserArr)) {
                            $res = SellerProduct::getAttributesById($upselProdId, array('selprod_id', 'selprod_user_id'));
                            if (empty($res)) {
                                $invalid = true;
                            } else {
                                $selProdUserArr[$res['selprod_id']] = $res['selprod_user_id'];
                            }
                        }

                        if ((array_key_exists($selProdId, $selProdUserArr) && array_key_exists($upselProdId, $selProdUserArr) && $selProdUserArr[$selProdId] != $selProdUserArr[$upselProdId]) || !array_key_exists($selProdId, $selProdUserArr) || !array_key_exists($upselProdId, $selProdUserArr)) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $selProdBuyTogetherArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($selProdBuyTogetherArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'upsell_sellerproduct_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_UPSELL_PRODUCTS, $where);
                }

                $this->db->insertFromArray(SellerProduct::DB_TBL_UPSELL_PRODUCTS, $selProdBuyTogetherArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdRelatedProd($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_RELATED_PRODUCTS, 'INNER JOIN', 'sp.selprod_id = spr.related_sellerproduct_id', 'spr');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spr.related_sellerproduct_id', 'spr.related_recommend_sellerproduct_id', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdRelatedProductColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdRelatedProd($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();

        $coloumArr = $this->getSelProdRelatedProductColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdSplPriceArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;

                $errMsg = SellerProduct::validateRelatedProdFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'related_sellerproduct_id';
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    } elseif ('related_recommend_sellerproduct_id' == $columnKey) {
                        $relSelProdId = $colValue;
                        if (0 < $userId) {
                            $relSelProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($relSelProdId, $userId);
                        }

                        if (1 > $relSelProdId) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    }
                    $sellerProdSplPriceArr[$columnKey] = $colValue;
                }
            }

            if (false === $errorInRow && count($sellerProdSplPriceArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'related_sellerproduct_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_RELATED_PRODUCTS, $where);
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_RELATED_PRODUCTS, $sellerProdSplPriceArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdPolicy($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_POLICY, 'INNER JOIN', 'sp.selprod_id = spp.sppolicy_selprod_id', 'spp');
        $srch->joinTable(PolicyPoint::DB_TBL, 'INNER JOIN', 'spp.sppolicy_ppoint_id = pp.ppoint_id', 'pp');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('pp.ppoint_identifier', 'sp.selprod_id', 'spp.sppolicy_ppoint_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdPolicyColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdPolicy($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $policyPonitIdentifierArr = array();
        $policyPonitIdArr = array();
        $selProdArr = array();

        $coloumArr = $this->getSelProdPolicyColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdPolicyArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateProdPolicyFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'sppolicy_selprod_id';
                        $selProdId = $colValue;
                        if (0 < $userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (1 > $selProdId) {
                            $invalid = true;
                        }
                    }

                    if (in_array($columnKey, array('sppolicy_ppoint_id', 'ppoint_identifier'))) {
                        if ('sppolicy_ppoint_id' == $columnKey) {
                            $colValue = $policyPointId = FatUtility::int($colValue);

                            if (!array_key_exists($policyPointId, $policyPonitIdArr)) {
                                $res = $this->getAllPrivacyPoints(true, $policyPointId);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $policyPonitIdArr = ($policyPonitIdArr + $res);
                                }
                            }
                        }

                        if ('ppoint_identifier' == $columnKey) {
                            $columnKey = 'sppolicy_ppoint_id';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $policyPonitIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllPrivacyPoints(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $policyPonitIdentifierArr = ($policyPonitIdentifierArr + $res);
                                }
                            }
                            $colValue = $policyPointId = $policyPonitIdentifierArr[$colValue];
                        }

                        if (1 > $policyPointId) {
                            $invalid = true;
                        }
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $sellerProdPolicyArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($sellerProdPolicyArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'sppolicy_selprod_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $where);
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_POLICY, $sellerProdPolicyArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportOptions($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = Option::getSearchObject($langId, false);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = o.option_seller_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = o.option_seller_id', 'uc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('option_id', 'option_identifier', 'option_seller_id', 'option_type', 'option_deleted', 'option_is_separate_images', 'option_is_color', 'option_display_in_filter', 'IFNULL(option_name,option_identifier)option_name', 'credential_username', 'option_attach_sizechart'));
        $srch->addOrder('option_id', 'ASC');
        if ($userId) {
            $srch->addCondition('option_deleted', '=', applicationConstants::NO);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getOptionsColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        /* $optionTypeArr = Option::getOptionTypes($langId); */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }

                if (in_array($columnKey, array('option_is_separate_images', 'option_is_color', 'option_display_in_filter', 'option_deleted', 'option_attach_sizechart')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importOptions($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionIdArr = array();
        $userArr = array();

        $coloumArr = $this->getOptionsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $optionsArr = $optionsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateOptionFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('credential_username' == $columnKey) {
                        $columnKey = 'option_seller_id';
                        $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);

                        if (!empty($colValue)) {
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $userArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $userArr = ($userArr + $res);
                                }
                            }
                            $colValue = $userId = array_key_exists($colValue, $userArr) ? $userArr[$colValue] : 0;
                        }
                    }

                    if (in_array($columnKey, array('option_is_separate_images', 'option_is_color', 'option_display_in_filter', 'option_deleted', 'option_attach_sizechart'))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = FatUtility::int($colValue);
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if ('option_name' == $columnKey) {
                            $optionsLangArr[$columnKey] = $colValue;
                        } else {
                            $optionsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }
            if (false === $errorInRow && count($optionsArr)) {
                $data = array('option_type' => Option::OPTION_TYPE_SELECT);

                $data = array_merge($data, $optionsArr);

                if ($this->settings['CONF_USE_OPTION_ID']) {
                    $optionData = Option::getAttributesById($data['option_id'], array('option_id'));
                } else {
                    $brandId = 0;
                    $optionData = Option::getAttributesByIdentifier($data['option_identifier'], array('option_id'));
                }


                if (!empty($optionData) && $optionData['option_id']) {
                    $optionId = $optionData['option_id'];
                    $where = array('smt' => 'option_id = ?', 'vals' => array($optionId));
                    $this->db->updateFromArray(Option::DB_TBL, $data, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Option::DB_TBL, $data);
                        $optionId = $this->db->getInsertId();
                    }
                }

                if ($optionId) {
                    /* Lang Data [ */
                    $langData = array(
                        'optionlang_option_id' => $optionId,
                        'optionlang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $optionsLangArr);
                    $this->db->insertFromArray(Option::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportOptionValues($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = OptionValue::getSearchObject();
        $srch->joinTable(
                OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'ovl.optionvaluelang_optionvalue_id = ov.optionvalue_id
		AND ovl.optionvaluelang_lang_id = ' . $langId, 'ovl'
        );
        $srch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'ov.optionvalue_option_id = o.option_id', 'o');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('optionvalue_id', 'optionvalue_option_id', 'optionvalue_identifier', 'optionvalue_color_code', 'optionvalue_display_order', 'IFNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name', 'option_identifier'));
        $srch->addOrder('optionvalue_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getOptionsValueColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importOptionValues($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $optionIdentifierArr = array();
        $optionIdArr = array();

        $optionValueObj = new OptionValue();
        $srchObj = OptionValue::getSearchObject();

        $coloumArr = $this->getOptionsValueColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $sellerProdPolicyArr = $sellerProdPolicyLangArr = array();
            $errorInRow = false;
            $optionvalue_identifier = '';

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Option::validateOptionValFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('optionvalue_display_order' == $columnKey) {
                        $colValue = FatUtility::int($colValue);
                    }
                    if (in_array($columnKey, array('optionvalue_id', 'optionvalue_identifier'))) {
                        if ('optionvalue_id' == $columnKey) {
                            $optionValueData = OptionValue::getAttributesById($colValue, array('optionvalue_id'));
                        } else {
                            $optionvalue_identifier = $colValue;
                        }
                    }

                    if (in_array($columnKey, array('optionvalue_option_id', 'option_identifier'))) {
                        $optionId = 0;
                        if ('optionvalue_option_id' == $columnKey && !array_key_exists($colValue, $optionIdArr)) {
                            $optionId = $colValue;
                            $res = $this->getAllOptions(true, $optionId);
                            if (!$res) {
                                $invalid = true;
                            } else {
                                $optionIdArr = ($optionIdArr + $res);
                            }
                        }

                        if ('option_identifier' == $columnKey) {
                            $columnKey = 'optionvalue_option_id';
                            $colValue = mb_strtolower($colValue);
                            if (!array_key_exists($colValue, $optionIdentifierArr)) {
                                $res = $this->array_change_key_case_unicode($this->getAllOptions(false, $colValue), CASE_LOWER);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $optionIdentifierArr = ($optionIdentifierArr + $res);
                                }
                            }

                            $optionId = $colValue = array_key_exists($colValue, $optionIdentifierArr) ? $optionIdentifierArr[$colValue] : 0;
                        }

                        if (1 > $optionId) {
                            $invalid = true;
                        } else {
                            $optionValueData = $optionValueObj->getAtttibutesByIdentifierAndOptionId($optionId, $optionvalue_identifier, array('optionvalue_id'));
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if ('optionvalue_name' == $columnKey) {
                            $sellerProdPolicyLangArr[$columnKey] = $colValue;
                        } else {
                            $sellerProdPolicyArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($sellerProdPolicyArr)) {
                if (!empty($optionValueData) && $optionValueData['optionvalue_id']) {
                    $optionValueId = $optionValueData['optionvalue_id'];
                    $where = array('smt' => 'optionvalue_id = ?', 'vals' => array($optionValueId));
                    $this->db->updateFromArray(OptionValue::DB_TBL, $sellerProdPolicyArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(OptionValue::DB_TBL, $sellerProdPolicyArr);
                        $optionValueId = $this->db->getInsertId();
                    }
                }

                if ($optionValueId) {
                    /* Lang Data [ */
                    $langData = array(
                        'optionvaluelang_optionvalue_id' => $optionValueId,
                        'optionvaluelang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $sellerProdPolicyLangArr);

                    $this->db->insertFromArray(OptionValue::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportTags($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = Tag::getSearchObject($langId);
        $srch->addMultipleFields(array('tag_id', 'tag_identifier', 'tag_user_id', 'tag_admin_id', 'tag_name', 'credential_username'));
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = t.tag_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getTagColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('credential_username' == $columnKey) {
                    $colValue = (!empty($colValue) ? $colValue : Labels::getLabel('LBL_Admin', $langId));
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importTags($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;
        $usernameArr = array();
        $useTagId = false;
        if ($this->settings['CONF_USE_TAG_ID']) {
            $useTagId = true;
        }

        $coloumArr = $this->getTagColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $tagsArr = $tagsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = Tag::validateTagsFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('tag_user_id' == $columnKey) {
                        $userId = $colValue;
                    }
                    if ('credential_username' == $columnKey) {
                        $columnKey = 'tag_user_id';
                        $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);
                        $colValue = mb_strtolower($colValue);
                        if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                            $res = $this->array_change_key_case_unicode($this->getAllUserArr(false, $colValue), CASE_LOWER);
                            if (!$res) {
                                $invalid = true;
                            } else {
                                $usernameArr = ($usernameArr + $res);
                            }
                        }
                        $userId = $colValue = array_key_exists($colValue, $usernameArr) ? $usernameArr[$colValue] : 0;
                    }

                    if (in_array($columnKey, array('tag_identifier', 'tag_name')) && empty($colValue)) {
                        if ('tag_id' == $columnKey) {
                            $tagData = Tag::getAttributesById($colValue, array('tag_id'));
                        }

                        if ('tag_identifier' == $columnKey) {
                            $tagData = Tag::getAttributesByIdentifier($colValue, array('tag_id'));
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if ('tag_name' == $columnKey) {
                            $tagsLangArr[$columnKey] = $colValue;
                        } else {
                            if (isset($userId)) {
                                $tagsArr['tag_admin_id'] = 0;
                            }

                            $tagsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($tagsArr)) {
                if (!empty($tagData) && $tagData['tag_id']) {
                    $tagId = $tagData['tag_id'];
                    $where = array('smt' => 'tag_id = ?', 'vals' => array($tagId));
                    $this->db->updateFromArray(Tag::DB_TBL, $tagsArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(Tag::DB_TBL, $tagsArr);
                        $tagId = $this->db->getInsertId();
                    }
                }

                if ($tagId) {
                    /* Lang Data [ */
                    $langData = array(
                        'taglang_tag_id' => $tagId,
                        'taglang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $tagsLangArr);

                    $this->db->insertFromArray(Tag::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */

                    /* update product tags association and tag string in products lang table[ */
                    Tag::updateTagStrings($tagId);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportCountries($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);

        $srch = Countries::getSearchObject(false, $langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('country_active', '=', applicationConstants::ACTIVE);
        }
        $rs = $srch->getResultSet();

        $languageCodes = Language::getAllCodesAssoc(true);
        $currencyCodes = Currency::getCurrencyAssoc(true);

        $useCountryId = false;
        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $useCountryId = true;
        }

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getCountryColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : 'a';

                if ('country_currency_code' == $columnKey) {
                    $colValue = array_key_exists($row['country_currency_id'], $currencyCodes) ? $currencyCodes[$row['country_currency_id']] : 0;
                }

                if ('country_language_code' == $columnKey) {
                    $colValue = array_key_exists($row['country_language_id'], $languageCodes) ? $languageCodes[$row['country_language_id']] : 0;
                }

                if ('country_active' == $columnKey) {
                    if (!$this->settings['CONF_USE_O_OR_1']) {
                        $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                    }
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importCountries($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $useCountryId = false;
        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $useCountryId = true;
        }

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $currencyCodes = Currency::getCurrencyAssoc(true);
        $currencyIds = array_flip($currencyCodes);

        $coloumArr = $this->getCountryColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $countryArr = $countryLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');

                $errMsg = Countries::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'country_currency_id':
                            $currencyId = FatUtility::int($colValue);
                            $colValue = array_key_exists($currencyId, $currencyCodes) ? $currencyId : 0;
                            break;
                        case 'country_currency_code':
                            $columnKey = 'country_currency_id';
                            $colValue = array_key_exists($colValue, $currencyIds) ? $currencyIds[$colValue] : 0;
                            break;
                        case 'country_language_id':
                            $currencyLangId = FatUtility::int($colValue);
                            $colValue = array_key_exists($currencyLangId, $languageCodes) ? $currencyLangId : 0;
                            break;
                        case 'country_language_code':
                            $columnKey = 'country_language_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;
                        case 'country_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = FatUtility::int($colValue);
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'country_id':
                            $countryData = Countries::getAttributesById($colValue, array('country_id'));
                            break;
                        case 'country_code':
                            $countryData = Countries::getCountryByCode($colValue, array('country_id'));
                            break;
                        case 'country_name':
                            $countryLangArr[$columnKey] = $colValue;
                            break;
                    }

                    $countryArr[$columnKey] = $colValue;
                    unset($countryArr['country_name']);
                }
            }

            if (false === $errorInRow && count($countryArr)) {
                if (!empty($countryData) && $countryData['country_id']) {
                    $countryId = $countryData['country_id'];
                    $where = array('smt' => 'country_id = ?', 'vals' => array($countryId));
                    $this->db->updateFromArray(Countries::DB_TBL, $countryArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        unset($countryArr['country_language_code']);
                        $this->db->insertFromArray(Countries::DB_TBL, $countryArr);
                        $countryId = $this->db->getInsertId();
                    }
                }

                if ($countryId) {
                    /* Lang Data [ */
                    $langData = array(
                        'countrylang_country_id' => $countryId,
                        'countrylang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $countryLangArr);
                    $this->db->insertFromArray(Countries::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportStates($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $useStateId = false;
        if ($this->settings['CONF_USE_STATE_ID']) {
            $useStateId = true;
        }

        $srch = States::getSearchObject(false, $langId);
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'st.state_country_id = c.country_id', 'c');
        $srch->addMultipleFields(array('state_id', 'state_code', 'state_country_id', 'state_identifier', 'state_active', 'country_id', 'country_code', 'state_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('state_active', '=', applicationConstants::ACTIVE);
        }

        if ($useStateId) {
            $srch->addOrder('state_country_id', 'ASC');
            $srch->addOrder('state_id', 'ASC');
        } else {
            $srch->addOrder('country_code', 'ASC');
            $srch->addOrder('state_identifier', 'ASC');
        }

        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getStatesColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('state_active' == $columnKey && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importStates($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $countryCodes = $this->getCountriesArr(true);
        } else {
            $countryIds = $this->getCountriesArr(false);
        }

        $coloumArr = $this->getStatesColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $statesArr = $statesLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = States::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'state_country_id':
                            $countryId = FatUtility::int($colValue);
                            $colValue = array_key_exists($countryId, $countryCodes) ? $countryId : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'country_code':
                            $columnKey = 'state_country_id';
                            $colValue = array_key_exists($colValue, $countryIds) ? $countryIds[$colValue] : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'state_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'state_name':
                            if (false === $invalid) {
                                $statesLangArr[$columnKey] = $colValue;
                            }
                            break;
                    }

                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $statesArr[$columnKey] = $colValue;
                        unset($statesArr['state_name']);
                    }
                }
            }

            if (false === $errorInRow && count($statesArr)) {
                if ($this->settings['CONF_USE_STATE_ID']) {
                    $stateData = States::getAttributesById($statesArr['state_id'], array('state_id'));
                } else {
                    $stateData = States::getAttributesByIdentifierAndCountry($statesArr['state_identifier'], $statesArr['state_country_id'], array('state_id'));
                }

                if (!empty($stateData) && $stateData['state_id']) {
                    $stateId = $stateData['state_id'];
                    $where = array('smt' => 'state_id = ?', 'vals' => array($stateId));
                    $this->db->updateFromArray(States::DB_TBL, $statesArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(States::DB_TBL, $statesArr);
                        $stateId = $this->db->getInsertId();
                    }
                }
                if ($stateId) {
                    /* Lang Data [ */
                    $langData = array(
                        'statelang_state_id' => $stateId,
                        'statelang_lang_id' => $langId,
                    );

                    $langData = array_merge($langData, $statesLangArr);

                    $this->db->insertFromArray(States::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);

        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportPolicyPoints($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $srch = PolicyPoint::getSearchObject($langId, false, false);
        $srch->addMultipleFields(array('ppoint_id', 'ppoint_identifier', 'ppoint_type', 'ppoint_display_order', 'ppoint_active', 'ppoint_deleted', 'ppoint_title'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('ppoint_active', '=', applicationConstants::ACTIVE);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getPolicyPointsColoumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $usePolicyPointId = false;
        if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
            $usePolicyPointId = true;
        }

        $policyPointTypeArr = PolicyPoint::getPolicyPointTypesArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                switch ($columnKey) {
                    case 'ppoint_active':
                    case 'ppoint_deleted':
                        if (!$this->settings['CONF_USE_O_OR_1']) {
                            $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                        }
                        break;
                    case 'ppoint_type_identifier':
                        $colValue = isset($policyPointTypeArr[$row['ppoint_type']]) ? $policyPointTypeArr[$row['ppoint_type']] : '';
                        break;
                }
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importPolicyPoints($csvFilePointer, $post, $langId)
    {
        $rowIndex = 1;

        $policyPointTypeArr = PolicyPoint::getPolicyPointTypesArr($langId);
        $policyPointTypeKeys = array_flip($policyPointTypeArr);

        $coloumArr = $this->getPolicyPointsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $policyPointsArr = $policyPointsLangArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = PolicyPoint::validateFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('ppoint_type' == $columnKey) {
                        $policyPointTypeId = FatUtility::int($colValue);
                        $colValue = $policyPointTypeId = array_key_exists($policyPointTypeId, $policyPointTypeArr) ? $policyPointTypeId : 0;
                    } elseif ('ppoint_type_identifier' == $columnKey) {
                        $columnKey = 'ppoint_type';
                        $colValue = $policyPointTypeId = array_key_exists($colValue, $policyPointTypeKeys) ? $policyPointTypeKeys[$colValue] : 0;
                        if (1 > $colValue) {
                            $errInSheet = $invalid = true;
                        }
                    }

                    if (in_array($columnKey, array('ppoint_active', 'ppoint_deleted'))) {
                        if ($this->settings['CONF_USE_O_OR_1']) {
                            $colValue = FatUtility::int($colValue);
                        } else {
                            $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        if ('ppoint_title' == $columnKey) {
                            $policyPointsLangArr[$columnKey] = $colValue;
                        } else {
                            $policyPointsArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            if (false === $errorInRow && count($policyPointsArr)) {
                if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
                    $policyData = PolicyPoint::getAttributesById($policyPointsArr['ppoint_id'], array('ppoint_id'));
                } else {
                    $policyData = PolicyPoint::getAttributesByIdentifier($policyPointsArr['ppoint_identifier'], array('ppoint_id'));
                }

                if (!empty($policyData) && $policyData['ppoint_id']) {
                    $policyPointId = $policyData['ppoint_id'];
                    $where = array('smt' => 'ppoint_id = ?', 'vals' => array($policyPointId));
                    $this->db->updateFromArray(PolicyPoint::DB_TBL, $policyPointsArr, $where);
                } else {
                    if ($this->isDefaultSheetData($langId)) {
                        $this->db->insertFromArray(PolicyPoint::DB_TBL, $policyPointsArr);
                        $policyPointId = $this->db->getInsertId();
                    }
                }

                if ($policyPointId) {
                    /* Lang Data [ */
                    $langData = array(
                        'ppointlang_ppoint_id' => $policyPointId,
                        'ppointlang_lang_id' => $langId,
                    );

                    $langData = array_merge($langData, $policyPointsLangArr);

                    $this->db->insertFromArray(PolicyPoint::DB_TBL_LANG, $langData, false, array(), $langData);
                    /* ] */
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportUsers($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null)
    {
        $userObj = new User();
        $srch = $userObj->getUserSearchObj();
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::NO);
        $srch->doNotCalculateRecords();
        $srch->addFld(array('user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate', 'user_registered_initially_for'));
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('user_id', '>=', $minId);
            $srch->addCondition('user_id', '<=', $maxId);
        }

        $srch->addOrder('user_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getUsersColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $userTypeArr = User::getUserTypesArr($langId);

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array('user_is_buyer', 'user_is_supplier', 'user_is_advertiser', 'user_is_affiliate')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                /* if ('urlrewrite_custom' == $columnKey) {
                  $colValue = isset($urlKeywords[ProductCategory::REWRITE_URL_PREFIX . $row['prodcat_id']]) ? $urlKeywords[ProductCategory::REWRITE_URL_PREFIX . $row['prodcat_id']] : '';
                  }

                  if ('prodcat_parent_identifier' == $columnKey) {
                  $colValue = array_key_exists($row['prodcat_parent'], $categoriesIdentifiers) ? $categoriesIdentifiers[$row['prodcat_parent']] : '';
                  } */

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function exportTaxCategory($langId, $userId = 0)
    {
        $userId = FatUtility::int($userId);
        $taxObj = new Tax();
        $srch = $taxObj->getSearchObject($langId, false);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($userId) {
            $srch->addCondition('taxcat_active', '=', applicationConstants::ACTIVE);
            $srch->addCondition('taxcat_deleted', '=', applicationConstants::NO);
        }
        $rs = $srch->getResultSet();

        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSalesTaxColumArr($langId, $userId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if (in_array($columnKey, array('taxcat_active', 'taxcat_deleted')) && !$this->settings['CONF_USE_O_OR_1']) {
                    $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                }

                if ('taxcat_last_updated' == $columnKey) {
                    $colValue = $this->displayDateTime($colValue);
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function array_change_key_case_unicode($arr, $c = MB_CASE_LOWER)
    {
        $c = ($c == CASE_LOWER) ? MB_CASE_LOWER : MB_CASE_UPPER;
        $ret = [];
        foreach ($arr as $k => $v) {
            $ret[mb_convert_case($k, $c, "UTF-8")] = $v;
        }
        return $ret;
    }

    public function exportCustomFields(int $langId)
    {
        /* Sheet Heading Row [ */
        $headingsArr = $this->getCustomFieldsColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */
        $fldTypes = AttrGroupAttribute::getAllAttributeTypeArr($langId);
        $srch = AttrGroupAttribute::getSearchObject();
        $srch->joinTable(ProductCategory::DB_TBL, 'LEFT JOIN', 'attr_prodcat_id = ' . ProductCategory::DB_TBL_PREFIX . 'id');
        $srch->joinTable(ProductCategory::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'catLang.prodcatlang_prodcat_id = ' . ProductCategory::DB_TBL_PREFIX . 'id AND prodcatlang_lang_id = ' . $langId, 'catLang');
        $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = ' . AttrGroupAttribute::DB_TBL_PREFIX . 'id AND attrlang_lang_id = ' . $langId, 'lang');
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT JOIN', 'attr_attrgrp_id = ' . AttributeGroup::DB_TBL_PREFIX . 'id');
        $srch->joinTable(AttributeGroup::DB_TBL . '_lang', 'LEFT JOIN', AttributeGroup::DB_TBL_PREFIX . 'id= attrgrplang_attrgrp_id AND attrgrplang_lang_id = ' . $langId);
        $srch->addMultipleFields(array('attrgrp.*', 'attrgrp_name', 'IFNULL(attr_name, attr_identifier) as attr_name', 'attr_postfix', 'attrgrp_identifier', 'attr_options', 'IFNULL(prodcat_name, prodcat_identifier) as attr_prodcat_name', 'attr_identifier'));
        $rs = $srch->getResultSet();
        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if ($columnKey == 'attr_type') {
                    if (!$this->settings['CONF_USE_ATTRIBUTE_TYPE_ID']) {
                        $colValue = (isset($fldTypes[$row['attr_type']])) ? $fldTypes[$row['attr_type']] : '';
                    }
                }
                if ($columnKey == 'attr_display_in_filter') {
                    if (!$this->settings['CONF_USE_O_OR_1']) {
                        $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                    }
                }
                if ($columnKey == 'attr_active') {
                    if (!$this->settings['CONF_USE_O_OR_1']) {
                        $colValue = (FatUtility::int($colValue) == 1) ? 'YES' : 'NO';
                    }
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importCustomFields($csvFilePointer, int $langId)
    {
        $rowIndex = 1;
        $coloumArr = $this->getCustomFieldsColoumArr($langId);
        $categoriesArr = $this->getCategoriesArr(true);
        $attrGrpArr = $this->getAttrGrpArr();
        
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $fldTypes = AttrGroupAttribute::getAllAttributeTypeArr($langId);
        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $customFldsArr = $customFldsLangArr = $groupLangData = array();
            $errorInRow = false;
            $attrGrpId = 0;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = AttrGroupAttribute::validateFields($columnKey, $columnTitle, $colValue, $langId);
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'attr_id':
                            if (!$this->isDefaultSheetData($langId)) {
                                $attrId = FatUtility::int($colValue);
                                $attrData = AttrGroupAttribute::getAttributesById($attrId, array('attr_fld_name', 'attr_identifier', 'attr_type', 'attr_attrgrp_id'));
                                if (empty($attrData)) {
                                    $errorInRow = true;
                                    $errMsg = Labels::getLabel("MSG_Invalid_Field", $langId);
                                    CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                                }
                            }
                            break;
                        case 'attr_prodcat_id':
                            $productCatId = FatUtility::int($colValue);
                            $colValue = array_key_exists($productCatId, $categoriesArr) ? $productCatId : 0;
                            if (!$colValue) {
                                $invalid = true;
                            }
                            break;
                        case 'attrgrp_identifier':
                            $attrName = trim($colValue);
                            $colValue = strtolower($colValue);
                            if ($colValue != '') {
                                if (array_key_exists($colValue, $attrGrpArr)) {
                                    $attrGrpId = $attrGrpArr[$colValue];
                                } else {
                                    $attrGrpObj = new AttributeGroup();
                                    if (!$attrGrpObj->setup(array($langId => $colValue), $langId)) {
                                        $errorInRow = true;
                                        $errMsg = Labels::getLabel("MSG_Insert_group_for_default_language_first", $langId);
                                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                                    } else {
                                        $attrGrpId = $attrGrpObj->getMainTableRecordId();
                                        $attrGrpObj = new AttributeGroup($attrGrpId);
                                        $attrGrpObj->setupLangData(array($langId => $attrName));
                                        $attrGrpArr[$colValue] = $attrGrpId;
                                    }
                                }
                                $colValue = $attrGrpId;
                                $columnKey = 'attr_attrgrp_id';
                            }
                            break;
                        case 'attrgrp_name':
                            $groupLangData[$langId] = $colValue;
                            break;
                        case 'attr_display_in_filter':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'attr_active':
                            if ($this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (FatUtility::int($colValue) == 1) ? applicationConstants::YES : applicationConstants::NO;
                            } else {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                        case 'attr_postfix':
                            $customFldsLangArr[$columnKey] = $colValue;
                            break;
                        case 'attr_type':
                            if (!$this->settings['CONF_USE_ATTRIBUTE_TYPE_ID']) {
                                $colValue = array_search($colValue, $fldTypes);
                            }
                            break;
                        case 'attr_name':
                            $customFldsLangArr['attr_name'] = $colValue;
                            if ($this->isDefaultSheetData($langId)) {
                                $customFldsArr['attr_identifier'] = $colValue;
                            }
                            break;
                        case 'attr_options' :
                            $customFldsLangArr['attr_options'] = $colValue;
                            break;
                    }
                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $customFldsArr[$columnKey] = $colValue;
                        /* unset($customFldsArr['attr_identifier']); */
                    }
                }
            }
            
            if (false === $errorInRow && count($customFldsArr)) {
                $attrId = intval($customFldsArr['attr_id']);
                $flagToDeletOldEntry = 0;
                $attrData = [];
                $recheckFldName = false;
                if ($attrId > 0) {
                    $attrData = AttrGroupAttribute::getAttributesById($attrId, array('attr_id', 'attr_fld_name', 'attr_identifier', 'attr_type', 'attr_attrgrp_id'));
                    if ($this->isDefaultSheetData($langId)) {
                        if (!empty($attrData) && $attrData['attr_type'] != $customFldsArr['attr_type']) {
                            $errorInRow = true; 
                            $errMsg = Labels::getLabel("MSG_You_Can_not_Change_Attribute_Type", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        }
                        $attrId = (!empty($attrData)) ? $attrData['attr_id'] : 0;
                        $attrFldName = (!empty($attrData)) ? $attrData['attr_fld_name'] : "";
                        if (!empty($attrData) && ($attrData['attr_attrgrp_id'] != $attrGrpId || $attrData['attr_prodcat_id'] != $customFldsArr['attr_prodcat_id']) ) {
                            $recheckFldName = true;
                        }
                    } else {
                        if (empty($attrData)) {
                            $errorInRow = true; 
                            $errMsg = Labels::getLabel("MSG_Invalid_Filed", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        }
                        if ($attrData['attr_attrgrp_id'] > 0 && !empty($groupLangData)) {
                            $attrGrpObj = new AttributeGroup($attrData['attr_attrgrp_id']);
                            $attrGrpObj->setupLangData($groupLangData);
                        }
                    }
                }
                
                if ((0 == $attrId || $recheckFldName) && $this->isDefaultSheetData($langId)) {
                    $numAttributes = AttrGroupAttribute::getNumericTypeArr($langId);
                    $attrGrpObj = new AttributeGroup($attrGrpId);
                    $lastNumAndTxtAttr = $attrGrpObj->getLastNumAndTxtAttr($customFldsArr['attr_prodcat_id']);
                    $nextNumAttr = intval($lastNumAndTxtAttr['num_attributes']) + 1;
                    $nextTextAttr = intval($lastNumAndTxtAttr['text_attributes']) + 1;
                    if ($customFldsArr['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                        if ($nextTextAttr > 30) {
                            $errInSheet = true;
                            $errMsg = Labels::getLabel('MSG_Text_Field_Limit_Error', $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        }
                        $attrFldName = 'prodtxtattr_text_' . $nextTextAttr;
                        $flagToDeletOldEntry = 1;
                    } else {
                        if ($nextNumAttr > 30) {
                            $errInSheet = true;
                            $errMsg = Labels::getLabel('MSG_Numeric_Field_Limit_Error', $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        }
                        $attrFldName = 'prodnumattr_num_' . $nextNumAttr;
                        $flagToDeletOldEntry = 1;
                    }
                }
                if ($flagToDeletOldEntry == 1 && $attrId > 0) {
                    $attrGrpAttrObj = new AttrGroupAttribute($attrId);
                    if (!$attrGrpAttrObj->resetLangData()) {
                        $errInSheet = true;
                        $errMsg = Labels::getLabel("MSG_issue_to_save_group_language_data", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        continue;
                    }
                }
                
                $langData = array(
                    'attrlang_attr_id' => $attrId,
                    'attrlang_lang_id' => $langId,
                    'attr_name' => $customFldsArr['attr_name'],
                    'attr_prefix' => '',
                    'attr_postfix' => $customFldsArr['attr_postfix'],
                    'attr_options' => $customFldsArr['attr_options'],
                );
                
                if ($this->isDefaultSheetData($langId)) {
                    unset($customFldsArr['attr_postfix']);
                    unset($customFldsArr['attr_options']);
                    unset($customFldsArr['attr_name']);
                    unset($customFldsArr['attr_id']);
                    $customFldsArr['attr_fld_name'] = $attrFldName;
                    $customFldsArr['attr_active'] = applicationConstants::YES;
                
                    $record = new AttrGroupAttribute($attrId);
                    $record->assignValues($customFldsArr);
                    if (!$record->save()) {
                        //FatUtility::dieWithError($record->getError(). " - rowIndex". $rowIndex);
                        $errInSheet = true;
                        $errMsg = $record->getError();
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        continue;
                    }
                    $attrId = $record->getMainTableRecordId();
                    $langData['attrlang_attr_id'] = $attrId;
                }
                
                $attrGrpAttrObj = new AttrGroupAttribute($attrId);
                if (!$attrGrpAttrObj->updateLangData($langId, $langData)) {
                    Message::addErrorMessage($attrGrpAttrObj->getError());
                    FatUtility::dieJsonError(Message::getHtml());
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportProductCustomFields(int $langId, int $offset = null, int $noOfRows = null, int $minId = null, int $maxId = null, int $userId = null, $isSellerProduct = false)
    {
        $srch = Product::getSearchObject();
        $srch->addMultipleFields(array('product_id', 'product_identifier'));
        $srch->doNotCalculateRecords();

        if ($userId) {
            $cnd = $srch->addCondition('tp.product_seller_id', '=', $userId, 'AND');
            if (!$isSellerProduct) {
                $cnd->attachCondition('tp.product_seller_id', '=', 0, 'OR');
            }
        }
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }
        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('product_id', '>=', $minId);
            $srch->addCondition('product_id', '<=', $maxId);
        }
        $srch->addOrder('product_id');
        $rs = $srch->getResultSet();
        $products = $this->db->fetchAll($rs);
        if (!empty($products)) {
            $prod = new Product();
            $headingsArr = $this->getProductCustomFieldsColoumArr($langId);
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
            foreach ($products as $product) {
                $prodCat = 0;
                $productId = $product['product_id'];
                $productCategories = $prod->getProductCategories($productId);
                if (!empty($productCategories)) {
                    $selectedCat = array_keys($productCategories);
                    $prodCat = $selectedCat[0];
                }
                $numericAttributes = Product::getProductNumericAttributes($productId, true);
                $textualAttributes = Product::getProductTextualAttributes($productId, $langId, true);
                $prodCatObj = new ProductCategory($prodCat);
                $attributes = $prodCatObj->getAttrDetail($langId, 0, 'attr_type');
                if (!empty($attributes)) {
                    $attributes = $this->formatAttributesData($attributes, $numericAttributes, $textualAttributes);
                    foreach ($attributes as $attribute) {
                        $sheetData = array();
                        foreach ($headingsArr as $columnKey => $heading) {
                            $colValue = array_key_exists($columnKey, $attribute) ? $attribute[$columnKey] : '';
                            if ('attr_identifier' == $columnKey) {
                                $colValue = ($attribute['attr_name'] != '') ? $attribute['attr_name'] : $attribute[$columnKey];
                            }
                            if ('product_identifier' == $columnKey) {
                                $colValue = $product['product_identifier'];
                            }
                            if ('product_id' == $columnKey) {
                                $colValue = $product['product_id'];
                            }
                            $sheetData[] = $this->parseContentForExport($colValue);
                        }
                        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
                    }
                }
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
        }
    }

    public function importProductCustomFields($csvFilePointer, int $langId, int $userId = 0)
    {
        $rowIndex = 1;
        $errInSheet = false;
        $coloumArr = $this->getProductCustomFieldsColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $fldTypes = AttrGroupAttribute::getAllAttributeTypeArr($langId);
        $prod = new Product();
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $customFldsArr = $customFldsLangArr = array();
            $errorInRow = false;
            $attributeData = [];
            $productCategories = [];
            $valFldIndex = '';
            $attributeLangData = [];
            $productData = [];
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = AttrGroupAttribute::validateProductCustomFieldsColumns($columnKey, $columnTitle, $colValue, $langId);
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'attr_id':
                            if (!$colValue) {
                                $invalid = true;
                            }
                            /* [ check for custom field and field category */
                            $attributeData = AttrGroupAttribute::getAttributesById($colValue, array('attr_prodcat_id', 'attr_type', 'attr_fld_name', 'attr_attrgrp_id'));
                            if (!empty($attributeData['attr_type']) && ($attributeData['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX || $attributeData['attr_type'] == AttrGroupAttribute::ATTRTYPE_CHECKBOXES)) {
                                $attributeLangData = AttrGroupAttribute::getAttributesByLangId($langId, $colValue);
                            }
                            /* ] */
                            break;
                        case 'product_id':
                            if (!$colValue) {
                                $invalid = true;
                            } else {
                                $productData = Product::getAttributesById($colValue);
                                if (empty($productData)) {
                                    $invalid = true;
                                }
                                $productCategories = $prod->getProductCategories($colValue);
                                if (!empty($productCategories)) {
                                    $productCategories = array_keys($productCategories);
                                }
                            }
                            break;
                        case 'field_value':
                            if (!$colValue) {
                                $invalid = true;
                            }
                            $valFldIndex = $colIndex + 1;
                            break;
                    }
                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $customFldsArr[$columnKey] = $colValue;
                        /* unset($customFldsArr['attr_identifier']); */
                    }
                }
            }
            $attributeCategoryId = (empty($attributeData)) ? 0 : $attributeData['attr_prodcat_id'];
            if (empty($productCategories) || (!in_array($attributeCategoryId, $productCategories))) {
                $errorInRow = true;
                $errMsg = Labels::getLabel("LBL_Invalid_Custom_Field", $langId);
                CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($valFldIndex), $errMsg));
            }
            if (($attributeData['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX || $attributeData['attr_type'] == AttrGroupAttribute::ATTRTYPE_CHECKBOXES) && false === $errorInRow) {
                if (empty($attributeLangData['attr_options']) && $customFldsArr['field_value'] != '') {
                    $errorInRow = true;
                    $errMsg = Labels::getLabel("LBL_Invalid_field_value", $langId);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($valFldIndex), $errMsg));
                }
                $attrOptions = explode("\n", $attributeLangData['attr_options']);
                $attrOptions = array_map(
                        function ($item) {
                    return trim(strtolower($item));
                }, $attrOptions
                );
                $fldVal = trim(strtolower($customFldsArr['field_value']));
                $optionKey = array_search($fldVal, $attrOptions);

                if (!is_numeric($optionKey)) {
                    $errorInRow = true;
                    $errMsg = Labels::getLabel("LBL_Invalid_field_value", $langId);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($valFldIndex), $errMsg));
                }
                $customFldsArr['field_value'] = $optionKey;
            }
            if (0 < $userId) {
                if (false === $errorInRow && $productData['product_seller_id'] != $userId) {
                    $errorInRow = true;
                    $errMsg = Labels::getLabel("LBL_You_can_import_custom_fields_with_your_catalog_only", $langId);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($valFldIndex), $errMsg));
                }
            }
            if (false === $errorInRow && count($customFldsArr)) {
                $prodObj = new Product($customFldsArr['product_id']);
                if ($attributeData['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                    $dataToUpdate = array(
                        'prodtxtattr_product_id' => $customFldsArr['product_id'],
                        'prodtxtattr_attrgrp_id' => $attributeData['attr_attrgrp_id'],
                        'prodtxtattr_lang_id' => $langId,
                        $attributeData['attr_fld_name'] => $customFldsArr['field_value'],
                    );
                    if (!$prodObj->addUpdateTextualAttributes($dataToUpdate)) {
                        Message::addErrorMessage($prodObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                } else {
                    $dataToUpdate = array(
                        'prodnumattr_product_id' => $customFldsArr['product_id'],
                        'prodnumattr_attrgrp_id' => $attributeData['attr_attrgrp_id'],
                        $attributeData['attr_fld_name'] => $customFldsArr['field_value'],
                    );
                    if (!$prodObj->addUpdateNumericAttributes($dataToUpdate)) {
                        Message::addErrorMessage($prodObj->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
                unset($prodObj);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    private function formatAttributesData(array $prodCatAttr, array $numericAttributes, array $textualAttributes): array
    {
        $response = array();
        foreach ($prodCatAttr as $attr) {
            $attributeVal = "";
            if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                if (!empty($textualAttributes) && !empty($attr)) {
                    $fldName = $attr['attr_fld_name'];
                    $attributeVal = (isset($textualAttributes[$attr['attr_attrgrp_id']][$fldName])) ? $textualAttributes[$attr['attr_attrgrp_id']][$fldName] : "";
                }
            } else {
                if (!empty($numericAttributes) && !empty($attr)) {
                    $fldName = $attr['attr_fld_name'];
                    $attributeVal = (isset($numericAttributes[$attr['attr_attrgrp_id']][$fldName])) ? $numericAttributes[$attr['attr_attrgrp_id']][$fldName] : "";
                    if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX || $attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_CHECKBOXES) {
                        $options = explode("\n", $attr['attr_options']);
                        $attributeVal = intval($attributeVal);
                        if (array_key_exists($attributeVal, $options)) {
                            $attributeVal = $options[$attributeVal];
                        } else {
                            $attributeVal = '';
                        }
                    }
                }
            }
            $attr += array('field_value' => $attributeVal);
            $response[] = $attr;
        }
        return $response;
    }

    public static function getAddonsContentTypeArr($langId)
    {
        $arr = array(
            static::ADDON_GENERAL_DATA => Labels::getLabel('LBL_General_Data', $langId),
        );
        return $arr;
    }

    public function importAddonsGeneralData($csvFilePointer, array $post, int $langId, int $sellerId = 0)
    {
        $rowIndex = 1;
        $usernameArr = array();
        $prodIndetifierArr = array();
        $prodTypeArr = array();
        $userId = $sellerId;
        $userProdUploadLimit = array();
        $categoryIdentifierArr = array();
        $taxCategoryArr = array();

        $coloumArr = $this->getAdddonsGeneralColoumArr($langId, $sellerId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $selProdGenArr = $selProdGenLangArr = array();
            $errorInRow = false;

            //if(array_key_exists($row['selprod_product_id'], $prodTypeArr))
            $selProdSepc = [];
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateAddonsGenDataFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'selprod_id':
                            $selprodId = $sellerTempId = $colValue;
                            if ($sellerId) {
                                $userId = $sellerId;
                                $userTempIdData = $this->getTempSelProdIdByTempId($sellerTempId, $sellerId);
                                if (!empty($userTempIdData) && $userTempIdData['spti_selprod_temp_id'] == $sellerTempId) {
                                    $selprodId = $colValue = $userTempIdData['spti_selprod_id'];
                                }
                            }
                            break;
                        case 'selprod_user_id':
                            $userId = $colValue;
                            break;
                        case 'credential_username':
                            $columnKey = 'selprod_user_id';
                            $colValue = ($colValue == Labels::getLabel('LBL_Admin', $langId) ? '' : $colValue);
                            if (!empty($colValue) && !array_key_exists($colValue, $usernameArr)) {
                                $res = $this->getAllUserArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $usernameArr = array_merge($usernameArr, $res);
                                }
                            }
                            $userId = $colValue = array_key_exists($colValue, $usernameArr) ? $usernameArr[$colValue] : 0;
                            break;

                        case 'tax_category_id':
                            $taxCatId = $colValue;
                            break;
                        case 'tax_category_identifier':
                            if (!array_key_exists($colValue, $taxCategoryArr)) {
                                $res = $this->getTaxCategoriesArr(false, $colValue);
                                if (!$res) {
                                    $invalid = true;
                                } else {
                                    $taxCategoryArr = array_merge($taxCategoryArr, $res);
                                }
                            }
                            $taxCatId = isset($taxCategoryArr[$colValue]) ? $taxCategoryArr[$colValue] : 0;
                            break;

                        case 'selprod_active':
                        case 'selprod_cod_enabled':
                        case 'selprod_deleted':
                        case 'selprod_is_eligible_cancel':
                        case 'selprod_is_eligible_refund':
                            if (!$this->settings['CONF_USE_O_OR_1']) {
                                $colValue = (strtoupper($colValue) == 'YES') ? applicationConstants::YES : applicationConstants::NO;
                            }
                            break;
                    }

                    /* if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId) {
                        if (!array_key_exists($userId, $userProdUploadLimit)) {
                            $userProdUploadLimit[$userId] = SellerPackages::getAllowedLimit($userId, $langId, 'spackage_inventory_allowed');
                        }
                    } */

                    if (true === $invalid) {
                        $errorInRow = true;
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        $err = array($rowIndex, ($colIndex + 1), $errMsg);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                    } else {
                        if ($columnKey == 'selprod_title' && $this->isDefaultSheetData($langId)) {
                            $selProdGenArr['selprod_identifier'] = $colValue;
                        }
                    
                        if (in_array($columnKey, array('selprod_title', 'selprod_features', 'selprod_rental_terms'))) {
                            $selProdGenLangArr[$columnKey] = $colValue;
                        } else {
                            $selProdGenArr[$columnKey] = $colValue;
                        }
                    }
                }
            }

            $userId = (!$sellerId) ? $userId : $sellerId;
            $selProdGenArr['selprod_user_id'] = $userId;
            if ($this->isDefaultSheetData($langId)) {
                $urlKeyword = $selProdGenLangArr['selprod_title'];
            }
            unset($selProdGenArr['selprod_rental_terms']);
            unset($selProdGenArr['category_indentifier']);
            unset($selProdGenArr['tax_category_identifier']);

            if (false === $errorInRow && count($selProdGenArr)) {
                $selProdGenArr['selprod_added_on'] = date('Y-m-d H:i:s');
                $selProdGenArr['selprod_available_from'] = date('Y-m-d');
                $selProdData = SellerProduct::getAttributesById($selprodId);
                
                if (!empty($selProdData) && $selProdData['selprod_user_id'] != $userId) {
                    $errInSheet = true;
                    $errMsg = Labels::getLabel("MSG_invalid_addon_id", $langId);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, (1), Labels::getLabel("MSG_invalid_addon_id", $langId)));
                    continue;
                }
                
                if (!empty($selProdData) && $selProdData['selprod_id'] && $selProdData['selprod_user_id'] == $userId && $selProdData['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                    $where = array('smt' => 'selprod_id = ?', 'vals' => array($selprodId));
                    unset($selProdGenArr['selprod_added_on']);
                    unset($selProdGenArr['selprod_available_from']);
                    /* if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId, $selprodId) >= $userProdUploadLimit[$userId]) {
                        $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        continue;
                    } */

                    $this->db->updateFromArray(SellerProduct::DB_TBL, $selProdGenArr, $where);
                    if ($sellerId && $this->isDefaultSheetData($langId)) {
                        $tempData = array(
                            'spti_selprod_id' => $selprodId,
                            'spti_selprod_temp_id' => $sellerTempId,
                            'spti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt' => 'spti_selprod_id = ? and spti_user_id = ?', 'vals' => array($selprodId, $userId)));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                } else {
                    $selProdGenArr['selprod_type'] = SellerProduct::PRODUCT_TYPE_ADDON;
                    $selProdGenArr['selprod_code'] = '';
                    /* if ($sellerId) { */
                        unset($selProdGenArr['selprod_id']);
                    /* } */

                    if ($this->isDefaultSheetData($langId)) {
                        /* if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0) && 0 < $userId && SellerProduct::getActiveCount($userId) >= $userProdUploadLimit[$userId]) {
                            $errMsg = Labels::getLabel("MSG_You_have_crossed_your_package_limit.", $langId);
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                            continue;
                        } */
                        if (!$this->db->insertFromArray(SellerProduct::DB_TBL, $selProdGenArr)) {
                            continue;
                        }
                        $selprodId = $this->db->getInsertId();
                        $tempData = array(
                            'spti_selprod_id' => $selprodId,
                            'spti_selprod_temp_id' => $sellerTempId,
                            'spti_user_id' => $userId,
                        );
                        $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt' => 'spti_selprod_id = ? and spti_user_id = ?', 'vals' => array($selprodId, $userId)));
                        $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
                    }
                }

                if ($selprodId) {
                    /* Lang Data [ */
                    $langData = array(
                        'selprodlang_selprod_id' => $selprodId,
                        'selprodlang_lang_id' => $langId,
                    );
                    $langData = array_merge($langData, $selProdGenLangArr);
                    $this->db->insertFromArray(SellerProduct::DB_TBL_LANG, $langData, false, array(), $langData);

                    /* ] */

                    if ($this->isDefaultSheetData($langId)) {
                        /* Tax Category [ */
                        $this->db->deleteRecords(Tax::DB_TBL_PRODUCT_TO_TAX, array('smt' => 'ptt_product_id = ? and ptt_seller_user_id = ? and ptt_type = ?', 'vals' => array($selprodId, $userId, SellerProduct::PRODUCT_TYPE_ADDON)));
                        if ($taxCatId) {
                            $this->db->insertFromArray(Tax::DB_TBL_PRODUCT_TO_TAX, array('ptt_product_id' => $selprodId, 'ptt_taxcat_id' => $taxCatId, 'ptt_taxcat_id_rent' => $taxCatId, 'ptt_seller_user_id' => $userId, 'ptt_type' => SellerProduct::PRODUCT_TYPE_ADDON));
                        }
                        /* ] */
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportAddonMedia($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.user_id = sp.selprod_user_id', 'u');
        $srch->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'uc.credential_user_id = u.user_id', 'uc');
        $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'pa.afile_record_id = sp.selprod_id and afile_type = ' . AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, 'pa');
        if ($userId) {
            $srch->addCondition('u.user_id', '=', $userId);
            $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        }
        $srch->addCondition('selprod_type', '=', SellerProduct::PRODUCT_TYPE_ADDON);
        $srch->doNotCalculateRecords();
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('sp.*', 'sp_l.*', 'pa.*', 'user_id', 'credential_username'));

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();

        $sheetData = array();

        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelAddonMediaColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        $languageCodes = Language::getAllCodesAssoc(true);
        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';

                if ('afile_lang_code' == $columnKey) {
                    $colValue = $languageCodes[$row['afile_lang_id']];
                }

                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importAddonMedia($csvFilePointer, $post, $langId, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $rowIndex = 1;
        $prodIndetifierArr = array();
        $optionValueIndetifierArr = array();
        $optionIdentifierArr = array();
        $prodTempArr = array();
        $prodArr = array();
        $selProdValidOptionArr = array();

        $languageCodes = Language::getAllCodesAssoc(true);
        $languageIds = array_flip($languageCodes);

        $coloumArr = $this->getSelAddonMediaColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        $breakForeach = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;

            $prodCatalogMediaArr = array();
            $errorInRow = false;
            $productId = $optionId = 0;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;

                $errMsg = SellerProduct::validateAddonMediaFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    switch ($columnKey) {
                        case 'selprod_id':
                            $productId = $colValue;
                            $columnKey = 'afile_record_id';

                            if (!empty($userId)) {
                                $colValue = $productId = $this->getCheckAndSetProductIdByTempId($productId, $userId, SellerProduct::PRODUCT_TYPE_ADDON);
                            }

                            if (1 > $colValue) {
                                $errMsg = Labels::getLabel("MSG_Sorry_you_are_not_authorized_to_update_this_product.", $langId);
                                $invalid = true;
                                $breakForeach = true;
                            }

                            break;
                        case 'afile_lang_code':
                            $columnKey = 'afile_lang_id';
                            $colValue = array_key_exists($colValue, $languageIds) ? $languageIds[$colValue] : 0;
                            break;
                    }

                    if (true === $invalid) {
                        $errMsg = !empty($errMsg) ? $errMsg : str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                        if ($breakForeach) {
                            break;
                        }
                    } else {
                        $prodCatalogMediaArr[$columnKey] = $colValue;
                    }
                }
            }

            if (false === $errorInRow && count($prodCatalogMediaArr)) {
                $fileType = AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE;
                $prodCatalogMediaArr['afile_type'] = $fileType;
                unset($prodCatalogMediaArr['selprod_title']);
                $saveToTempTable = false;
                $isUrlArr = parse_url($prodCatalogMediaArr['afile_physical_path']);
                if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
                    $saveToTempTable = true;
                }

                if ($saveToTempTable) {
                    $prodCatalogMediaArr['afile_downloaded'] = applicationConstants::NO;
                    $prodCatalogMediaArr['afile_unique'] = applicationConstants::YES;
                    if (!in_array($productId, $prodTempArr)) {
                        $prodTempArr[] = $productId;
                        $this->db->deleteRecords(
                                AttachedFile::DB_TBL_TEMP, array(
                            'smt' => 'afile_type = ? AND afile_record_id = ?',
                            'vals' => array($fileType, $productId)
                                )
                        );
                    }
                    $this->db->insertFromArray(AttachedFile::DB_TBL_TEMP, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                } else {
                    //if (!in_array($productId, $prodArr)) {
                    $prodArr[] = $productId;
                    $this->db->deleteRecords(
                            AttachedFile::DB_TBL, array(
                        'smt' => 'afile_type = ? AND afile_record_id = ?',
                        'vals' => array($fileType, $productId)
                            )
                    );
                    //}

                    $physical_path = explode('/', $prodCatalogMediaArr['afile_physical_path']);

                    if (AttachedFile::FILETYPE_BULK_IMAGES_PATH == $physical_path[0] . '/') {
                        $afileObj = new AttachedFile();
                        $moved = $afileObj->moveAttachment($prodCatalogMediaArr['afile_physical_path'], $fileType, $productId, $prodCatalogMediaArr['afile_record_subid'], $prodCatalogMediaArr['afile_name'], $prodCatalogMediaArr['afile_display_order'], false, $prodCatalogMediaArr['afile_lang_id']);

                        if (false === $moved) {
                            $errMsg = str_replace('{filepath}', $prodCatalogMediaArr['afile_physical_path'], Labels::getLabel("MSG_Invalid_File_{filepath}.", $langId));
                            CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, 'N/A', $errMsg));
                        }
                    } else {
                        $this->db->insertFromArray(AttachedFile::DB_TBL, $prodCatalogMediaArr, false, array(), $prodCatalogMediaArr);
                    }
                }
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdDurationDiscount($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $durationTypes = ProductRental::durationTypeArr($langId);
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        /* $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'INNER JOIN', 'produr_selprod_id = sprodata_selprod_id', 'spd'); */
        $srch->joinTable(SellerProductDurationDiscount::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spdd.produr_selprod_id', 'spdd');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spdd.*', 'sp.selprod_id', 'spd.sprodata_duration_type as produr_duration_type'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }
        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }
        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdDurationDiscountColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */
        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if (in_array($columnKey, array('produr_duration_type'))) {
                    $colValue = (isset($durationTypes[$colValue])) ? $durationTypes[$colValue] : "";
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdDurationDiscount($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();
        $coloumArr = $this->getSelProdDurationDiscountColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $errInSheet = false;

        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $selProdDurationDisArr = array();
            $errorInRow = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = ProductRental::validateDurationDiscountFields($columnKey, $columnTitle, $colValue, $langId);
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }

                    if ('produr_discount_percent' == $columnKey && $colValue > 100) {
                        $invalid = true;
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $selProdDurationDisArr[$columnKey] = $colValue;
                    }
                }
            }
            unset($selProdDurationDisArr['selprod_id']);
            if (false === $errorInRow && count($selProdDurationDisArr)) {
                $data = array(
                    'produr_selprod_id' => $selProdId,
                );
                $data = array_merge($data, $selProdDurationDisArr);
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'produr_selprod_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProductDurationDiscount::DB_TBL, $where);
                }
                $this->db->insertFromArray(SellerProductDurationDiscount::DB_TBL, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdUnavailableRentalDates($langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerRentalProductUnavailableDate::DB_TBL, 'INNER JOIN', 'sp.selprod_id = spud.pu_selprod_id', 'spud');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spud.*', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }
        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }
        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }
        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdUnavialableDatesColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr);
        /* ] */
        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                if (in_array($columnKey, array('pu_start_date', 'pu_end_date'))) {
                    $colValue = $this->displayDate($colValue);
                }
                $sheetData[] = $colValue;
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdRentalUnavialableDates($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();
        $coloumArr = $this->getSelProdUnavialableDatesColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);
        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $selProdUnavilDatesArr = array();
            $errorInRow = false;
            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = false;
                $errMsg = ProductRental::validateUnavialableDatesFields($columnKey, $columnTitle, $colValue, $langId);
                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $selProdId = $colValue;
                        if ($userId) {
                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    }
                    if (in_array($columnKey, array('pu_start_date', 'pu_end_date'))) {
                        $colValue = $this->getDateTime($colValue, false);
                    }
                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    } else {
                        $selProdUnavilDatesArr[$columnKey] = $colValue;
                    }
                }
            }
            unset($selProdUnavilDatesArr['selprod_id']);
            if (false === $errorInRow && count($selProdUnavilDatesArr)) {
                $data = array(
                    'pu_selprod_id' => $selProdId,
                );
                $data = array_merge($data, $selProdUnavilDatesArr);
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'pu_selprod_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerRentalProductUnavailableDate::DB_TBL, $where);
                }
                $this->db->insertFromArray(SellerRentalProductUnavailableDate::DB_TBL, $data);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);
        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

    public function exportSellerProdAddonProd(int $langId, $offset = null, $noOfRows = null, $minId = null, $maxId = null, $userId = null)
    {
        $userId = FatUtility::int($userId);
        $srch = SellerProduct::getSearchObject($langId);
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'INNER JOIN', 'sp.selprod_id = spa.spa_seller_prod_id', 'spa');
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('spa.spa_seller_prod_id', 'spa.spa_addon_product_id', 'sp.selprod_id'));
        if ($userId) {
            $srch->addCondition('sp.selprod_user_id', '=', $userId);
        }

        if (isset($offset) && isset($noOfRows)) {
            $srch->setPageNumber($offset);
            $srch->setPageSize($noOfRows);
        } else {
            $srch->setPageSize(static::MAX_LIMIT);
        }

        if (isset($minId) && isset($maxId)) {
            $srch->addCondition('selprod_id', '>=', $minId);
            $srch->addCondition('selprod_id', '<=', $maxId);
        }

        $srch->addOrder('selprod_id', 'ASC');
        $rs = $srch->getResultSet();
        $sheetData = array();
        /* Sheet Heading Row [ */
        $headingsArr = $this->getSelProdAddonProductColoumArr($langId);
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, $headingsArr, false, '', true);
        /* ] */

        while ($row = $this->db->fetch($rs)) {
            $sheetData = array();
            foreach ($headingsArr as $columnKey => $heading) {
                $colValue = array_key_exists($columnKey, $row) ? $row[$columnKey] : '';
                $sheetData[] = $this->parseContentForExport($colValue);
            }
            CommonHelper::writeExportDataToCSV($this->CSVfileObj, $sheetData);
        }
        CommonHelper::writeExportDataToCSV($this->CSVfileObj, array(), true, $this->CSVfileName);
    }

    public function importSellerProdAddonProd($csvFilePointer, $post, $langId, $userId = null)
    {
        $rowIndex = 1;
        $selProdArr = array();

        $coloumArr = $this->getSelProdAddonProductColoumArr($langId);
        $this->validateCSVHeaders($csvFilePointer, $coloumArr, $langId);

        $errInSheet = false;
        while (($row = $this->getFileRow($csvFilePointer)) !== false) {
            $rowIndex++;
            $sellerProdAddonArr = array();
            $errorInRow = false;

            foreach ($coloumArr as $columnKey => $columnTitle) {
                $colIndex = $this->headingIndexArr[$columnTitle];
                $colValue = $this->getCell($row, $colIndex, '');
                $invalid = $errMsg = false;
                $errMsg = SellerProduct::validateAddonProdFields($columnKey, $columnTitle, $colValue, $langId);

                if (false !== $errMsg) {
                    $errorInRow = true;
                    $err = array($rowIndex, ($colIndex + 1), $errMsg);
                    CommonHelper::writeToCSVFile($this->CSVfileObj, $err);
                } else {
                    if ('selprod_id' == $columnKey) {
                        $columnKey = 'spa_seller_prod_id';
                        $selProdId = $colValue;
                        $selprodData = SellerProduct::getAttributesById($selProdId, ['selprod_user_id', 'selprod_type']);

                        if (isset($selprodData['selprod_type']) && $selprodData['selprod_type'] != SellerProduct::PRODUCT_TYPE_PRODUCT) {
                            $invalid = true;
                        }

                        if ($userId) {
                            if (isset($selprodData['selprod_user_id']) && $selprodData['selprod_user_id'] != $userId) {
                                $invalid = true;
                            }

                            $selProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($colValue, $userId);
                        }
                        if (!$selProdId) {
                            $invalid = true;
                        }
                    } elseif ('spa_addon_product_id' == $columnKey) {
                        $relAddonProdId = $colValue;
                        $selprodData = SellerProduct::getAttributesById($relAddonProdId, ['selprod_user_id', 'selprod_type']);

                        if (isset($selprodData['selprod_type']) && $selprodData['selprod_type'] != SellerProduct::PRODUCT_TYPE_ADDON) {
                            $invalid = true;
                        }

                        if (0 < $userId) {
                            $relAddonProdId = $colValue = $this->getCheckAndSetSelProdIdByTempId($relAddonProdId, $userId);
                        }

                        if (1 > $relAddonProdId) {
                            $invalid = true;
                        }
                    }

                    if (true === $invalid) {
                        $errMsg = str_replace('{column-name}', $columnTitle, Labels::getLabel("MSG_Invalid_{column-name}.", $langId));
                        CommonHelper::writeToCSVFile($this->CSVfileObj, array($rowIndex, ($colIndex + 1), $errMsg));
                    }
                    $sellerProdAddonArr[$columnKey] = $colValue;
                }
            }

            if (false === $errorInRow && count($sellerProdAddonArr)) {
                if (!in_array($selProdId, $selProdArr)) {
                    $selProdArr[] = $selProdId;
                    $where = array('smt' => 'spa_seller_prod_id = ?', 'vals' => array($selProdId));
                    $this->db->deleteRecords(SellerProduct::DB_TBL_SELLER_PROD_ADDON, $where);
                }
                $this->db->insertFromArray(SellerProduct::DB_TBL_SELLER_PROD_ADDON, $sellerProdAddonArr);
            } else {
                $errInSheet = true;
            }
        }
        // Close File
        CommonHelper::writeToCSVFile($this->CSVfileObj, array(), true);


        if (CommonHelper::checkCSVFile($this->CSVfileName)) {
            $success['CSVfileUrl'] = UrlHelper::generateFullUrl('custom', 'downloadLogFile', array($this->CSVfileName), CONF_WEBROOT_FRONTEND);
        }
        if ($errInSheet) {
            $success['msg'] = Labels::getLabel('LBL_Error!_Please_check_error_log_sheet.', $langId);
            FatUtility::dieJsonError($success);
        }
        $success['msg'] = Labels::getLabel('LBL_data_imported/updated_Successfully.', $langId);
        FatUtility::dieJsonSuccess($success);
    }

}
