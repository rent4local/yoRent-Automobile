<?php

class ImportexportCommon extends FatModel
{

    protected $db;
    public $CSVfileName;

    public const IMPORT_ERROR_LOG_PATH = CONF_UPLOADS_PATH . 'import-error-log/';

    public const VALIDATE_POSITIVE_INT = 'positiveInt';
    public const VALIDATE_INT = 'int';
    public const VALIDATE_FLOAT = 'float';
    public const VALIDATE_NOT_NULL = 'notNull';
    
    public function __construct($id = 0)
    {
        //$this->defaultLangId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG',FatUtility::VAR_INT,CommonHelper::getLangId());
        $this->defaultLangId = CommonHelper::getLangId();
        $this->db = FatApp::getDb();
        $this->settings = $this->getSettingsArr();
    }

    public function CSVFileName($fileName = '', $langId = 0)
    {
        $langId = FatUtility::int($langId);
        if (0 >= $langId) {
            $langId = CommonHelper::getLangId();
        }

        $langData = Language::getAttributesById($langId, array('language_code'));

        $fileName = empty($fileName) ? 'CSV_FILE' : $fileName;

        return $fileName . '_' . $langData['language_code'] . '_' . date("d-M-Y-His") . mt_rand() . '.csv';
    }

    public function openCSVfileToWrite($fileName, $langId = 0, $errorLog = false, $headingsArr = array())
    {
        if (empty($fileName)) {
            return false;
        }
        $this->CSVfileName = $this->CSVFileName($fileName, $langId);

        if (true === $errorLog) {
            if (!file_exists(self::IMPORT_ERROR_LOG_PATH)) {
                mkdir(self::IMPORT_ERROR_LOG_PATH, 0777);
            }
            $file = self::IMPORT_ERROR_LOG_PATH . $this->CSVfileName;
            $headingsArr = array(
                Labels::getLabel('LBL_Row', $langId),
                Labels::getLabel('LBL_Column', $langId),
                Labels::getLabel('LBL_Description', $langId)
            );
            $handle = fopen($file, "w");
        }

        if (false === $errorLog) {
            $handle = fopen('php://memory', 'w');
        }

        $langId = FatUtility::int($langId);
        if (0 >= $langId) {
            $langId = CommonHelper::getLangId();
        }

        CommonHelper::writeToCSVFile($handle, $headingsArr, false, true);
        return $handle;
    }

    public function getCsvFileName()
    {
        return $this->CSVfileName;
    }

    public static function deleteErrorLogFiles($hoursBefore = '4')
    {
        if (empty($hoursBefore)) {
            return false;
        }
        $importErrorLogFilesDir = ImportexportCommon::IMPORT_ERROR_LOG_PATH;
        $errorLogFiles = array_diff(scandir($importErrorLogFilesDir), array('..', '.'));
        foreach ($errorLogFiles as $fileName) {
            $file = $importErrorLogFilesDir . $fileName;
            $modifiedOn = filemtime($file);
            if ($modifiedOn <= strtotime('-' . $hoursBefore . ' hour')) {
                unlink($file);
            }
        }
        return true;
    }

    public function isValidColumns($headingsArr, $coloumArr)
    {
        $arr = array_diff($headingsArr, $coloumArr);
        if (count($arr) || count($headingsArr) != count($coloumArr)) {
            return false;
        }
        return true;
    }

    public static function validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId)
    {
        $errMsg = false;

        foreach ($requiredFields as $type => $fieldsArr) {
            if (!in_array($columnIndex, $fieldsArr)) {
                continue;
            }

            switch ($type) {
                case static::VALIDATE_POSITIVE_INT:
                    $errMsg = (0 >= FatUtility::int($columnValue)) ? Labels::getLabel("MSG_{column-name}_should_be_greater_than_0.", $langId) : false;
                    break;
                case static::VALIDATE_NOT_NULL:
                    $errMsg = ('' == $columnValue) ? Labels::getLabel("MSG_{column-name}_is_mandatory.", $langId) : false;
                    break;
                case static::VALIDATE_INT:
                    $errMsg = (0 > FatUtility::int($columnValue)) ? Labels::getLabel("MSG_{column-name}_should_be_greater_than_equal_to_0.", $langId) : false;
                    break;
                case static::VALIDATE_FLOAT:
                    $errMsg = (0 > FatUtility::float($columnValue)) ? Labels::getLabel("MSG_{column-name}_should_be_greater_than_0.", $langId) : false;
                    break;
            }

            $errMsg = (false !== $errMsg) ? mb_strtolower($errMsg) : $errMsg;
            return (false !== $errMsg) ? mb_convert_case(str_replace('{column-name}', $columnTitle, $errMsg), MB_CASE_TITLE, "UTF-8") : $errMsg;
        }
        return $errMsg;
    }

    public function isUploadedFileValidMimes($files)
    {
        $csvValidMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );
        return (isset($files['name']) && $files['error'] == 0 && in_array(trim($files['type']), $csvValidMimes) && $files['size'] > 0);
    }

    public function isDefaultSheetData($langId)
    {
        if ($langId == $this->defaultLangId) {
            return true;
        }
        return false;
    }

    public function displayDate($date)
    {
        return $this->displayDateTime($date, false);
    }

    public function displayDateTime($dt, $time = true)
    {
        try {
            if (trim($dt) == '' || $dt == '0000-00-00' || $dt == '0000-00-00 00:00:00') {
                return;
            }
            if ($time == false) {
                return date("m/d/Y", strtotime($dt));
            }
            return date('m/d/Y H:i:s', strtotime($dt));
        } catch (Exception $e) {
            return false;
        }
    }

    public function getDateTime($dt, $time = true)
    {
        $emptyDateArr = array('0000-00-00', '0000-00-00 00:00:00', '0000/00/00', '0000/00/00 00:00:00', '00/00/0000', '00/00/0000 00:00:00', '00/00/00', '00/00/00 00:00:00');
        if (trim($dt) == '' || in_array($dt, $emptyDateArr)) {
            return '0000-00-00';
        }

        try {
            $date = new DateTime($dt);
            $timeStamp = $date->getTimestamp();
            if ($time == false) {
                return date("Y-m-d", $timeStamp);
            }
            return date("Y-m-d H:i:s", $timeStamp);
        } catch (Exception $e) {
            return '0000-00-00';
        }
    }

    public function getCategoryColoumArr($langId, $userId = 0)
    {
        $arr = array();

        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $arr['prodcat_id'] = Labels::getLabel('LBL_Category_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['prodcat_identifier'] = Labels::getLabel('LBL_Category_Identifier', $langId);
            }
        } else {
            $arr['prodcat_identifier'] = Labels::getLabel('LBL_Category_Identifier', $langId);
        }

        if ($this->isDefaultSheetData($langId)) {
            if ($this->settings['CONF_USE_CATEGORY_ID']) {
                $arr['prodcat_parent'] = Labels::getLabel('LBL_Parent_Id', $langId);
            } else {
                $arr['prodcat_parent_identifier'] = Labels::getLabel('LBL_Parent_Identifier', $langId);
            }
        }

        $arr['prodcat_name'] = Labels::getLabel('LBL_Name', $langId);
        if (!$userId) {
            /* $arr['prodcat_description'] = Labels::getLabel('LBL_Description', $langId); */
            /* $arr[] = Labels::getLabel('LBL_Content_block', $langId); */

            if ($this->isDefaultSheetData($langId)) {
                $arr['urlrewrite_custom'] = Labels::getLabel('LBL_Seo_friendly_url', $langId);
                /* $arr['prodcat_featured'] = Labels::getLabel('LBL_Featured', $langId); */
                $arr['prodcat_active'] = Labels::getLabel('LBL_Active', $langId);
                /* $arr['prodcat_status'] = Labels::getLabel('LBL_STATUS', $langId); */  // Not Required. It is being used in category request from seller.
                $arr['prodcat_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
                $arr['prodcat_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
            }
        }
        
		return $this->formatColumnRow($arr);
    }

    public function getCategoryMediaColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_CATEGORY_ID']) {
            $arr['prodcat_id'] = Labels::getLabel('LBL_Category_Id', $langId);
        } else {
            $arr['prodcat_identifier'] = Labels::getLabel('LBL_Category_Identifier', $langId);
        }

        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['afile_lang_id'] = Labels::getLabel('LBL_lang_id', $langId);
        } else {
            $arr['afile_lang_code'] = Labels::getLabel('LBL_lang_code', $langId);
        }

        $arr['afile_type'] = Labels::getLabel('LBL_Image_Type', $langId);
        $arr['afile_screen'] = Labels::getLabel('LBL_DISPLAY_SCREEN', $langId);
        $arr['afile_physical_path'] = Labels::getLabel('LBL_File_Path', $langId);
        $arr['afile_name'] = Labels::getLabel('LBL_File_Name', $langId);
        $arr['afile_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getBrandColoumArr($langId, $userId = 0)
    {
        $arr = array();

        if ($this->settings['CONF_USE_BRAND_ID']) {
            $arr['brand_id'] = Labels::getLabel('LBL_Brand_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['brand_identifier'] = Labels::getLabel('LBL_Brand_Identifier', $langId);
            }
        } else {
            $arr['brand_identifier'] = Labels::getLabel('LBL_Brand_Identifier', $langId);
        }
        $arr['brand_name'] = Labels::getLabel('LBL_Name', $langId);

        if (!$userId) {
            /* $arr['brand_short_description'] = Labels::getLabel('LBL_Description', $langId); */

            if ($this->isDefaultSheetData($langId)) {
                $arr['urlrewrite_custom'] = Labels::getLabel('LBL_Seo_friendly_url', $langId);
                /* $arr['brand_featured'] = Labels::getLabel('LBL_Featured', $langId); */
                $arr['brand_active'] = Labels::getLabel('LBL_Active', $langId);
                $arr['brand_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getBrandMediaColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_BRAND_ID']) {
            $arr['brand_id'] = Labels::getLabel('LBL_Brand_Id', $langId);
        } else {
            $arr['brand_identifier'] = Labels::getLabel('LBL_Brand_Identifier', $langId);
        }

        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['afile_lang_id'] = Labels::getLabel('LBL_lang_id', $langId);
        } else {
            $arr['afile_lang_code'] = Labels::getLabel('LBL_lang_code', $langId);
        }

        $arr['afile_type'] = Labels::getLabel('LBL_File_Type', $langId);
        $arr['afile_screen'] = Labels::getLabel('LBL_DISPLAY_SCREEN', $langId);
        $arr['afile_physical_path'] = Labels::getLabel('LBL_File_Path', $langId);
        $arr['afile_name'] = Labels::getLabel('LBL_File_Name', $langId);
        $arr['afile_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getProductsCatalogColoumArr($langId, $userId = 0, $actionType = null)
    {
        $arr = array();

        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_PRODUCT_ID', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['product_identifier'] = Labels::getLabel('LBL_Product_identifier', $langId);
            }
        } else {
            if ($this->isDefaultSheetData($langId)) {
                $arr['product_id'] = Labels::getLabel('LBL_PRODUCT_ID', $langId);
            }
            $arr['product_identifier'] = Labels::getLabel('LBL_Product_identifier', $langId);
        }

        if ($this->isDefaultSheetData($langId) && $actionType != Importexport::ACTION_ADMIN_PRODUCTS) {
            if ($this->settings['CONF_USE_USER_ID']) {
                $arr['product_seller_id'] = Labels::getLabel('LBL_User_ID', $langId);
            } else {
                $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
            }
        }

        $arr['product_name'] = Labels::getLabel('LBL_PRODUCT_NAME', $langId);
        /* $arr['product_short_description'] = Labels::getLabel('LBL_Short_Description', $langId); */
        $arr['product_description'] = Labels::getLabel('LBL_Description', $langId);
        $arr['product_youtube_video'] = Labels::getLabel('LBL_Youtube_Video', $langId);

        if ($this->isDefaultSheetData($langId)) {
            if ($this->settings['CONF_USE_CATEGORY_ID']) {
                $arr['category_Id'] = Labels::getLabel('LBL_Category_Id', $langId);
            } else {
                $arr['category_indentifier'] = Labels::getLabel('LBL_Category_Identifier', $langId);
            }

            if ($this->settings['CONF_USE_BRAND_ID']) {
                $arr['product_brand_id'] = Labels::getLabel('LBL_Brand_Id', $langId);
            } else {
                $arr['brand_identifier'] = Labels::getLabel('LBL_Brand_Identifier', $langId);
            }

            /* if ($this->settings['CONF_USE_PRODUCT_TYPE_ID']) {
              $arr['product_type'] = Labels::getLabel('LBL_Product_Type_Id', $langId);
              } else {
              $arr['product_type_identifier'] = Labels::getLabel('LBL_Product_Type_Identifier', $langId);
              } */

            $arr['product_model'] = Labels::getLabel('LBL_Model', $langId);

            $allowSale = FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0);
            if($allowSale) {
                $arr['product_min_selling_price'] = Labels::getLabel('LBL_Min_Selling_price', $langId);
            }

            if ($this->settings['CONF_USE_TAX_CATEOGRY_ID']) {
                if($allowSale) {
                    $arr['tax_category_id'] = Labels::getLabel('LBL_Tax_Category_Id[Sale]', $langId);
                }
                $arr['tax_category_id_rent'] = Labels::getLabel('LBL_Tax_Category_Id[Rent]', $langId);
            } else {
                if($allowSale) {
                    $arr['tax_category_identifier'] = Labels::getLabel('LBL_Tax_Category_Identifier[Sale]', $langId);
                }
                $arr['tax_category_identifier_rent'] = Labels::getLabel('LBL_Tax_Category_Identifier[Rent]', $langId);
            }

            $shippedBy = FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0);
            if ((0 == $userId && $shippedBy) || !$shippedBy) {
                if ($this->settings['CONF_USE_SHIPPING_PROFILE_ID']) {
                    $arr['shipping_profile_id'] = Labels::getLabel('LBL_SHIPPING_PROFILE_ID', $langId);
                } else {
                    $arr['shipping_profile_identifier'] = Labels::getLabel('LBL_SHIPPING_PROFILE_IDENTIFIER', $langId);
                }
            }


            if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
                if ($this->settings['CONF_USE_SHIPPING_PACKAGE_ID']) {
                    $arr['product_ship_package_id'] = Labels::getLabel('LBL_SHIPPING_PACKAGE_ID', $langId);
                } else {
                    $arr['product_ship_package_identifier'] = Labels::getLabel('LBL_SHIPPING_PACKAGE_IDENTIFIER', $langId);
                }
                
                $arr['product_weight'] = Labels::getLabel('LBL_Weight', $langId);
                if ($this->settings['CONF_USE_WEIGHT_UNIT_ID']) {
                    $arr['product_weight_unit'] = Labels::getLabel('LBL_Weight_unit_id', $langId);
                } else {
                    $arr['product_weight_unit_identifier'] = Labels::getLabel('LBL_Weight_unit_identifier', $langId);
                }
            }

            /*  $arr['product_length'] = Labels::getLabel('LBL_Length', $langId);
              $arr['product_width'] = Labels::getLabel('LBL_Width', $langId);
              $arr['product_height'] = Labels::getLabel('LBL_Height', $langId); */

            /* if ($this->settings['CONF_USE_DIMENSION_UNIT_ID']) {
              $arr['product_dimension_unit'] = Labels::getLabel('LBL_Dimension_Unit_Id', $langId);
              } else {
              $arr['product_dimension_unit_identifier'] = Labels::getLabel('LBL_Dimension_Unit_Identifier', $langId);
              } */

            if($allowSale) {
                $arr['product_warranty'] = Labels::getLabel('LBL_PRODUCT_WARRANTY_(DAYS)', $langId);
            }
            $arr['product_upc'] = Labels::getLabel('LBL_EAN/UPC/GTIN_code', $langId);

            if (0 == $userId) {
                $arr['product_fulfillment_type'] = Labels::getLabel('LBL_FULFILLMENT_TYPE', $langId);
            }

            if ((0 == $userId && $shippedBy) || !$shippedBy) {
                // $arr['ps_free'] = Labels::getLabel('LBL_Free_Shipping', $langId);
                $arr['product_cod_enabled'] = Labels::getLabel('LBL_COD_available', $langId);
            }

            $arr['product_featured'] = Labels::getLabel('LBL_Featured', $langId);
            $arr['product_approved'] = Labels::getLabel('LBL_Approved', $langId);
            $arr['product_active'] = Labels::getLabel('LBL_Active', $langId);
            $arr['product_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
        }

        return $this->formatColumnRow($arr);
    }

    public function getProductOptionColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_PRODUCT_ID', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_PRODUCT_IDENTIFIER', $langId);
        }

        if ($this->settings['CONF_USE_OPTION_ID']) {
            $arr['option_id'] = Labels::getLabel('LBL_Option_ID', $langId);
        } else {
            $arr['option_identifier'] = Labels::getLabel('LBL_Option_Identifier', $langId);
        }

        return $this->formatColumnRow($arr);
    }

    public function getProductTagColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_PRODUCT_ID', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_PRODUCT_IDENTIFIER', $langId);
        }

        if ($this->settings['CONF_USE_TAG_ID']) {
            $arr['tag_id'] = Labels::getLabel('LBL_TAG_ID', $langId);
        } else {
            $arr['tag_identifier'] = Labels::getLabel('LBL_TAG_Identifier', $langId);
        }

        return $this->formatColumnRow($arr);
    }

    public function getProductSpecificationColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_product_id', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_product_identifier', $langId);
        }

        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['prodspeclang_lang_id'] = Labels::getLabel('LBL_Lang_id', $langId);
        } else {
            $arr['prodspeclang_lang_code'] = Labels::getLabel('LBL_Lang_code', $langId);
        }

        $arr['prodspec_identifier'] = Labels::getLabel('LBL_specification_Identifier', $langId);
        $arr['prodspec_name'] = Labels::getLabel('LBL_specification_Name', $langId);
        $arr['prodspec_value'] = Labels::getLabel('LBL_specification_Value', $langId);
        $arr['prodspec_group'] = Labels::getLabel('LBL_specification_Group', $langId);

        return $this->formatColumnRow($arr);
    }

    public function getProductShippingColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_product_id', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_product_identifier', $langId);
        }

        if ($this->settings['CONF_USE_USER_ID']) {
            $arr['user_id'] = Labels::getLabel('LBL_User_id', $langId);
        } else {
            $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
        }

        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $arr['country_id'] = Labels::getLabel('LBL_Shipping_country_id', $langId);
        } else {
            $arr['country_code'] = Labels::getLabel('LBL_Shipping_country_code', $langId);
        }

        if ($this->settings['CONF_USE_SHIPPING_COMPANY_ID']) {
            $arr['scompany_id'] = Labels::getLabel('LBL_Shipping_company_id', $langId);
        } else {
            $arr['scompany_identifier'] = Labels::getLabel('LBL_Shipping_company_identifier', $langId);
        }

        if ($this->settings['CONF_USE_SHIPPING_DURATION_ID']) {
            $arr['sduration_id'] = Labels::getLabel('LBL_Shipping_duration_id', $langId);
        } else {
            $arr['sduration_identifier'] = Labels::getLabel('LBL_Shipping_duration_identifier', $langId);
        }

        $arr['pship_charges'] = Labels::getLabel('LBL_Cost', $langId);
        $arr['pship_additional_charges'] = Labels::getLabel('LBL_Additional_item_cost', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getProductMediaColoumArr($langId)
    {
        $arr = array();
        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['product_id'] = Labels::getLabel('LBL_Product_Id', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_Product_Identifier', $langId);
        }

        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['afile_lang_id'] = Labels::getLabel('LBL_lang_id', $langId);
        } else {
            $arr['afile_lang_code'] = Labels::getLabel('LBL_lang_code', $langId);
        }

        if ($this->settings['CONF_USE_OPTION_ID']) {
            $arr['option_id'] = Labels::getLabel('LBL_Option_id', $langId);
        } else {
            $arr['option_identifier'] = Labels::getLabel('LBL_Option_identifer', $langId);
        }

        if ($this->settings['CONF_OPTION_VALUE_ID']) {
            $arr['optionvalue_id'] = Labels::getLabel('LBL_Option_value_id', $langId);
        } else {
            $arr['optionvalue_identifier'] = Labels::getLabel('LBL_OPTION_VALUE_IDENTIFIER', $langId);
        }

        $arr['afile_physical_path'] = Labels::getLabel('LBL_File_Path', $langId);
        $arr['afile_name'] = Labels::getLabel('LBL_File_Name', $langId);
        $arr['afile_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdGeneralColoumArr($langId, $userId = 0)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);

        if ($this->settings['CONF_USE_PRODUCT_ID']) {
            $arr['selprod_product_id'] = Labels::getLabel('LBL_Product_Id', $langId);
        } else {
            $arr['product_identifier'] = Labels::getLabel('LBL_Product_Identifier', $langId);
        }

        if (!$userId) {
            if ($this->settings['CONF_USE_USER_ID']) {
                $arr['selprod_user_id'] = Labels::getLabel('LBL_User_ID', $langId);
            } else {
                $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
            }
        }

        if ($this->isDefaultSheetData($langId)) {
            $arr['selprod_sku'] = Labels::getLabel('LBL_SKU', $langId);
            $arr['selprod_cost'] = Labels::getLabel('LBL_Cost_Price', $langId);

            /* $arr['sprodata_is_for_rent'] = Labels::getLabel('LBL_For_Rent', $langId); */
            if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) {
                $rentalPricelbl = Labels::getLabel('LBL_Rental_Price', $langId);
                if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                    $rentalPricelbl = Labels::getLabel('LBL_Rental_Price_(Including_Tax)', $langId);
                }
                $arr['sprodata_rental_price'] = $rentalPricelbl;
                $arr['sprodata_rental_security'] = Labels::getLabel('LBL_Rental_Security', $langId);
                $arr['sprodata_rental_buffer_days'] = Labels::getLabel('LBL_Rental_Buffer_Days', $langId);
            }
            
            $arr['sprodata_rental_stock'] = Labels::getLabel('LBL_Rental_Stock_Quantity', $langId);
            $arr['sprodata_minimum_rental_duration'] = Labels::getLabel('LBL_Rental_Minimum_Duration', $langId);
            $arr['sprodata_duration_type'] = Labels::getLabel('LBL_Rental_Minimum_Duration_Type', $langId);
            $arr['sprodata_rental_active'] = Labels::getLabel('LBL_Product_Active[Rent]', $langId);
            $arr['sprodata_rental_available_from'] = Labels::getLabel('LBL_Rental_Available_From', $langId);
            $arr['sprodata_minimum_rental_quantity'] = Labels::getLabel('LBL_Minimum_Rental_Quantity', $langId);

            if ($this->settings['CONF_USE_PROD_CONDITION_ID']) {
                $arr['sprodata_rental_condition'] = Labels::getLabel('LBL_Rental_Condition_id', $langId);
                if (ALLOW_SALE) {
                    $arr['selprod_condition'] = Labels::getLabel('LBL_Condition_id', $langId);
                }
            } else {
                $arr['sprodata_rental_condition_identifier'] = Labels::getLabel('LBL_Rental_Condition_Identifier', $langId);
                if (ALLOW_SALE) {
                    $arr['selprod_condition_identifier'] = Labels::getLabel('LBL_Condition_Identifier', $langId);
                }
            }
            
            if (ALLOW_SALE) {
                /* $arr['sprodata_is_for_sell'] = Labels::getLabel('LBL_For_Sale', $langId); */
                $salePricelbl = Labels::getLabel('LBL_Selling_Price(Sale)', $langId);
                if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                    $salePricelbl = Labels::getLabel('LBL_Selling_Price(Sale_-Including_Tax)', $langId);
                }
                $arr['selprod_price'] = $salePricelbl;
                $arr['selprod_stock'] = Labels::getLabel('LBL_Stock(Sale)', $langId);
                $arr['selprod_min_order_qty'] = Labels::getLabel('LBL_Min_Order_Quantity(Sale)', $langId);
                $arr['selprod_subtract_stock'] = Labels::getLabel('LBL_Subtract_stock(Sale)', $langId);
                $arr['selprod_track_inventory'] = Labels::getLabel('LBL_Track_Inventory(Sale)', $langId);
                $arr['selprod_threshold_stock_level'] = Labels::getLabel('LBL_Threshold_stock_level(Sale)', $langId);
            }
        }

        $arr['selprod_title'] = Labels::getLabel('LBL_Title', $langId);
        $arr['selprod_comments'] = Labels::getLabel('LBL_Comments', $langId);
        /* $arr['rental_cancellation_age'] = Labels::getLabel('LBL_CANCELLATION_AGE(RENT)', $langId); */
        
        if ($this->isDefaultSheetData($langId)) {
            if (ALLOW_SALE) {
                $arr['selprod_cancellation_age'] = Labels::getLabel('LBL_CANCELLATION_AGE(SALE)', $langId);
                $arr['selprod_return_age'] = Labels::getLabel('LBL_RETURN_AGE(SALE)', $langId);
                $arr['selprod_active'] = Labels::getLabel('LBL_Active(Sale)', $langId);
                $arr['selprod_available_from'] = Labels::getLabel('LBL_Available_from(Sale)', $langId);
                $arr['selprod_fulfillment_type'] = Labels::getLabel('LBL_FULFILLMENT_TYPE', $langId);
            }
            $arr['selprod_url_keyword'] = Labels::getLabel('LBL_Url_keyword', $langId);
            $arr['selprod_cod_enabled'] = Labels::getLabel('LBL_COD_Available', $langId);
            $arr['sprodata_fullfillment_type'] = Labels::getLabel('LBL_Rental_FULFILLMENT_TYPE', $langId);
            $arr['shipping_profile'] = Labels::getLabel('LBL_Shipping_Profile_Identifier', $langId);
            $arr['selprod_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
            if (!$userId && ALLOW_SALE) {
                $arr['selprod_sold_count'] = Labels::getLabel('LBL_Sold_Count(Sale)', $langId);
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getSelProdMediaColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['afile_lang_id'] = Labels::getLabel('LBL_lang_id', $langId);
        } else {
            $arr['afile_lang_code'] = Labels::getLabel('LBL_lang_code', $langId);
        }
        $arr['afile_physical_path'] = Labels::getLabel('LBL_File_Path', $langId);
        $arr['afile_name'] = Labels::getLabel('LBL_File_Name', $langId);
        $arr['afile_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdOptionsColoumArr($langId)
    {
        $arr = array();
        $arr['selprodoption_selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);

        if ($this->settings['CONF_USE_OPTION_ID']) {
            $arr['option_id'] = Labels::getLabel('LBL_Option_id', $langId);
        } else {
            $arr['option_identifier'] = Labels::getLabel('LBL_Option_identifier', $langId);
        }

        if ($this->settings['CONF_OPTION_VALUE_ID']) {
            $arr['optionvalue_id'] = Labels::getLabel('LBL_Option_Value_ID', $langId);
        } else {
            $arr['optionvalue_identifier'] = Labels::getLabel('LBL_Option_Value_Identifier', $langId);
        }
        return $this->formatColumnRow($arr);
    }

    public function getSelProdSeoColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);

        /* if ($this->isDefaultSheetData($langId)) {
          $arr['meta_identifier'] = Labels::getLabel('LBL_meta_identifier', $langId);
          } */
        $arr['meta_title'] = Labels::getLabel('LBL_meta_title', $langId);
        $arr['meta_keywords'] = Labels::getLabel('LBL_meta_keywords', $langId);
        $arr['meta_description'] = Labels::getLabel('LBL_meta_description', $langId);
        $arr['meta_other_meta_tags'] = Labels::getLabel('LBL_other_meta_tags', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdSpecialPriceColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        $arr['splprice_start_date'] = Labels::getLabel('LBL_Start_date', $langId);
        $arr['splprice_end_date'] = Labels::getLabel('LBL_End_date', $langId);
        $arr['splprice_price'] = Labels::getLabel('LBL_Price', $langId);
        if (ALLOW_SALE) {
            $arr['splprice_type'] = Labels::getLabel('LBL_Type', $langId);    
        }
       
        /* if($this->settings['CONF_USE_PERSENT_OR_FLAT_CONDITION_ID']){
          $arr[] = Labels::getLabel('LBL_display_price_type_id', $langId);
          }else{
          $arr[] = Labels::getLabel('LBL_display_price_type', $langId);
          }

          $arr[] = Labels::getLabel('LBL_display_discount_value', $langId);
          $arr[] = Labels::getLabel('LBL_display_list_price', $langId); */
        return $this->formatColumnRow($arr);
    }

    public function getSelProdVolumeDiscountColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        $arr['voldiscount_min_qty'] = Labels::getLabel('LBL_Min_quantity', $langId);
        $arr['voldiscount_percentage'] = Labels::getLabel('LBL_discount_percentage', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdBuyTogetherColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        $arr['upsell_recommend_sellerproduct_id'] = Labels::getLabel('LBL_Buy_together_seller_product_id', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdRelatedProductColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        $arr['related_recommend_sellerproduct_id'] = Labels::getLabel('LBL_Related_seller_product_id', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdPolicyColoumArr($langId)
    {
        $arr = array();
        $arr['selprod_id'] = Labels::getLabel('LBL_seller_product_id', $langId);
        if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
            $arr['sppolicy_ppoint_id'] = Labels::getLabel('LBL_Policy_point_id', $langId);
        } else {
            $arr['ppoint_identifier'] = Labels::getLabel('LBL_Policy_point_identifier', $langId);
        }
        return $this->formatColumnRow($arr);
    }

    public function getOptionsColoumArr($langId, $userId = 0)
    {
        $arr = array();

        if ($this->settings['CONF_USE_OPTION_ID']) {
            $arr['option_id'] = Labels::getLabel('LBL_Option_id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['option_identifier'] = Labels::getLabel('LBL_Option_identifier', $langId);
            }
        } else {
            $arr['option_identifier'] = Labels::getLabel('LBL_Option_identifier', $langId);
        }

        $arr['option_name'] = Labels::getLabel('LBL_Option_name', $langId);

        if (!$userId) {
            if ($this->isDefaultSheetData($langId)) {
                if ($this->settings['CONF_USE_USER_ID']) {
                    $arr['option_seller_id'] = Labels::getLabel('LBL_User_ID', $langId);
                } else {
                    $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
                }

                /* if($this->settings['CONF_USE_OPTION_TYPE_ID']){
                  $arr[] = Labels::getLabel('LBL_Option_Type_ID', $langId);
                  }else{
                  $arr[] = Labels::getLabel('LBL_Option_Type', $langId);
                  } */

                $arr['option_is_separate_images'] = Labels::getLabel('LBL_Has_Separate_Image', $langId);
                $arr['option_is_color'] = Labels::getLabel('LBL_Option_is_Color', $langId);
                $arr['option_display_in_filter'] = Labels::getLabel('LBL_Display_in_filters', $langId);
                $arr['option_attach_sizechart'] = Labels::getLabel('LBL_Attach_Sizechart', $langId);
                if (!$userId) {
                    $arr['option_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
                }
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getOptionsValueColoumArr($langId, $userId = 0)
    {
        $arr = array();

        if ($this->settings['CONF_OPTION_VALUE_ID']) {
            $arr['optionvalue_id'] = Labels::getLabel('LBL_Option_Value_ID', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['optionvalue_identifier'] = Labels::getLabel('LBL_Option_Value_Identifier', $langId);
            }
        } else {
            $arr['optionvalue_identifier'] = Labels::getLabel('LBL_Option_Value_Identifier', $langId);
        }

        if ($this->settings['CONF_USE_OPTION_ID']) {
            $arr['optionvalue_option_id'] = Labels::getLabel('LBL_Option_id', $langId);
        } else {
            $arr['option_identifier'] = Labels::getLabel('LBL_Option_identifier', $langId);
        }

        $arr['optionvalue_name'] = Labels::getLabel('LBL_OPTION_VALUE_NAME', $langId);

        if ($this->isDefaultSheetData($langId)) {
            $arr['optionvalue_color_code'] = Labels::getLabel('LBL_Color_Code', $langId);
            if (!$userId) {
                $arr['optionvalue_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
            }
        }

        return $this->formatColumnRow($arr);
    }

    public function getTagColoumArr($langId, $userId = 0)
    {
        $arr = array();
        if ($this->settings['CONF_USE_TAG_ID']) {
            $arr['tag_id'] = Labels::getLabel('LBL_Tag_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['tag_identifier'] = Labels::getLabel('LBL_Tag_Identifier', $langId);
            }
        } else {
            $arr['tag_identifier'] = Labels::getLabel('LBL_Tag_Identifier', $langId);
        }

        if (!$userId) {
            if ($this->isDefaultSheetData($langId)) {
                if ($this->settings['CONF_USE_USER_ID']) {
                    $arr['tag_user_id'] = Labels::getLabel('LBL_User_ID', $langId);
                } else {
                    $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
                }
            }
        }

        $arr['tag_name'] = Labels::getLabel('LBL_Tag_Name', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getCountryColoumArr($langId, $userId = 0)
    {
        $arr = array();
        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $arr['country_id'] = Labels::getLabel('LBL_Country_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['country_code'] = Labels::getLabel('LBL_Country_code', $langId);
            }
        } else {
            $arr['country_code'] = Labels::getLabel('LBL_Country_code', $langId);
        }

        $arr['country_name'] = Labels::getLabel('LBL_Country_Name', $langId);

        if (!$userId) {
            if ($this->isDefaultSheetData($langId)) {
                if ($this->settings['CONF_USE_CURRENCY_ID']) {
                    $arr['country_currency_id'] = Labels::getLabel('LBL_Currency_ID', $langId);
                } else {
                    $arr['country_currency_code'] = Labels::getLabel('LBL_Currency_code', $langId);
                }

                if ($this->settings['CONF_USE_LANG_ID']) {
                    $arr['country_language_id'] = Labels::getLabel('LBL_Lang_ID', $langId);
                } else {
                    $arr['country_language_code'] = Labels::getLabel('LBL_Lang_code', $langId);
                }

                $arr['country_active'] = Labels::getLabel('LBL_Active', $langId);
            }
        }

        return $this->formatColumnRow($arr);
    }

    public function getStatesColoumArr($langId, $userId = 0)
    {
        $arr = array();
        if ($this->settings['CONF_USE_STATE_ID']) {
            $arr['state_id'] = Labels::getLabel('LBL_State_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['state_identifier'] = Labels::getLabel('LBL_State_Identifier', $langId);
            }
        } else {
            $arr['state_identifier'] = Labels::getLabel('LBL_State_Identifier', $langId);
        }

        if ($this->settings['CONF_USE_COUNTRY_ID']) {
            $arr['state_country_id'] = Labels::getLabel('LBL_Country_Id', $langId);
        } else {
            $arr['country_code'] = Labels::getLabel('LBL_Country_code', $langId);
        }

        $arr['state_name'] = Labels::getLabel('LBL_State_Name', $langId);

        if ($this->isDefaultSheetData($langId)) {
            $arr['state_code'] = Labels::getLabel('LBL_State_Code', $langId);
            if (!$userId) {
                $arr['state_active'] = Labels::getLabel('LBL_Active', $langId);
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getPolicyPointsColoumArr($langId, $userId = 0)
    {
        $arr = array();
        if ($this->settings['CONF_USE_POLICY_POINT_ID']) {
            $arr['ppoint_id'] = Labels::getLabel('LBL_Policy_Point_Id', $langId);
            if ($this->isDefaultSheetData($langId)) {
                $arr['ppoint_identifier'] = Labels::getLabel('LBL_Policy_Point_Identifier', $langId);
            }
        } else {
            $arr['ppoint_identifier'] = Labels::getLabel('LBL_Policy_Point_Identifier', $langId);
        }
        $arr['ppoint_title'] = Labels::getLabel('LBL_Policy_Point_Title', $langId);

        if ($this->isDefaultSheetData($langId)) {
            if ($this->settings['CONF_USE_POLICY_POINT_TYPE_ID']) {
                $arr['ppoint_type'] = Labels::getLabel('LBL_Policy_Point_Type_Id', $langId);
            } else {
                $arr['ppoint_type_identifier'] = Labels::getLabel('LBL_Policy_Point_Type_Identifier', $langId);
            }

            if (!$userId) {
                $arr['ppoint_display_order'] = Labels::getLabel('LBL_Display_order', $langId);
                $arr['ppoint_active'] = Labels::getLabel('LBL_Active', $langId);
                $arr['ppoint_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getUsersColoumArr($langId)
    {
        $arr = array();
        $arr['user_id'] = Labels::getLabel('LBL_User_id', $langId);
        $arr['user_name'] = Labels::getLabel('LBL_Name', $langId);
        $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
        $arr['user_phone'] = Labels::getLabel('LBL_phone', $langId);
        $arr['user_is_buyer'] = Labels::getLabel('LBL_Is_buyer', $langId);
        $arr['user_is_supplier'] = Labels::getLabel('LBL_Is_supplier', $langId);
        $arr['user_is_advertiser'] = Labels::getLabel('LBL_Is_Advertiser', $langId);
        $arr['user_is_affiliate'] = Labels::getLabel('LBL_Is_Affiliate', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSalesTaxColumArr($langId, $userId = 0)
    {
        $arr = array();
        $arr['taxcat_id'] = Labels::getLabel('LBL_Tax_Category_Id', $langId);
        $arr['taxcat_identifier'] = Labels::getLabel('LBL_Tax_Category_Identifier', $langId);
        $arr['taxcat_name'] = Labels::getLabel('LBL_Tax_Category_Name', $langId);
        if (!$userId) {
            if ($this->isDefaultSheetData($langId)) {
                $arr['taxcat_last_updated'] = Labels::getLabel('LBL_Last_Updated', $langId);
                $arr['taxcat_active'] = Labels::getLabel('LBL_Active', $langId);
                $arr['taxcat_deleted'] = Labels::getLabel('LBL_Deleted', $langId);
            }
        }
        return $this->formatColumnRow($arr);
    }

    public function getAllRewriteUrls($startingWith)
    {
        $keywordSrch = UrlRewrite::getSearchObject();
        $keywordSrch->doNotCalculateRecords();
        $keywordSrch->doNotLimitRecords();
        $keywordSrch->addMultipleFields(array('urlrewrite_original', 'urlrewrite_custom'));
        $keywordSrch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'original', 'like', $startingWith . '%');
        $keywordRs = $keywordSrch->getResultSet();
        $urlKeywords = $this->db->fetchAllAssoc($keywordRs, 'brand_identifier');
        return $urlKeywords;
    }

    public function getSettingsArr($siteConfiguration = false)
    {
        return array(
            'CONF_USE_BRAND_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_BRAND_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_CATEGORY_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_CATEGORY_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_PRODUCT_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_PRODUCT_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_USER_ID' => false,
            'CONF_USE_OPTION_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_OPTION_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_OPTION_VALUE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_OPTION_VALUE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_TAG_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_TAG_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_TAX_CATEOGRY_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_TAX_CATEOGRY_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_PRODUCT_TYPE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_PRODUCT_TYPE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_DIMENSION_UNIT_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_DIMENSION_UNIT_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_WEIGHT_UNIT_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_WEIGHT_UNIT_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_LANG_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_LANG_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_CURRENCY_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_CURRENCY_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_PROD_CONDITION_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_PROD_CONDITION_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_PERSENT_OR_FLAT_CONDITION_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_PERSENT_OR_FLAT_CONDITION_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_STATE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_STATE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_COUNTRY_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_COUNTRY_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_POLICY_POINT_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_POLICY_POINT_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_POLICY_POINT_TYPE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_POLICY_POINT_TYPE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_SHIPPING_COMPANY_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_SHIPPING_COMPANY_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_SHIPPING_DURATION_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_SHIPPING_DURATION_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_SHIPPING_PROFILE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_SHIPPING_PROFILE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_SHIPPING_PACKAGE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_SHIPPING_PACKAGE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_O_OR_1' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_O_OR_1', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_ATTRIBUTE_TYPE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_ATTRIBUTE_TYPE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_ATTRIBUTE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_ATTRIBUTE_ID', FatUtility::VAR_INT, 0) : false,
            'CONF_USE_DURATION_DISCOUNT_TYPE_ID' => ($siteConfiguration) ? FatApp::getConfig('CONF_USE_DURATION_DISCOUNT_TYPE_ID', FatUtility::VAR_INT, 0) : false,
        );
    }

    public function getSettings($userId = 0)
    {
        $userId = FatUtility::int($userId);
        $res = $this->getSettingsArr(true);
        if (!$userId) {
            return $res;
        }

        $srch = new SearchBase(Importexport::DB_TBL_SETTINGS, 's');
        $srch->addCondition('impexp_setting_user_id', '=', $userId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('impexp_setting_key', 'impexp_setting_value'));
        $rs = $srch->getResultSet();
        $row = $this->db->fetchAllAssoc($rs);
        if (!$row) {
            return $res;
        }
        $row['CONF_USE_USER_ID'] = false;
        return $row;
    }

    public function getAllCategoryIdentifiers($byId = true, $catIdOrIdentifier = false)
    {
        $srch = ProductCategory::getSearchObject(false, false, false);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->doNotCalculateRecords();
        if ($catIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('prodcat_id', 'prodcat_identifier'));
            if ($catIdOrIdentifier) {
                $srch->addCondition('prodcat_id', '=', $catIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('prodcat_identifier', 'prodcat_id'));
            if ($catIdOrIdentifier) {
                $srch->addCondition('prodcat_identifier', '=', $catIdOrIdentifier);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllProductsIdentifiers($byId = true, $productIdOrIdentifier = false, $userId = 0)
    {
        $srch = Product::getSearchObject();
        $srch->doNotCalculateRecords();

        if ($productIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('product_id', 'product_identifier'));
            if ($productIdOrIdentifier) {
                $srch->addCondition('product_id', '=', $productIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('product_identifier', 'product_id'));
            if ($productIdOrIdentifier) {
                $srch->addCondition('product_identifier', '=', $productIdOrIdentifier);
            }
        }
        if ($userId > 0) {
            $cnd = $srch->addCondition('product_seller_id', '=', $userId);
            $cnd->attachCondition('product_seller_id', '=', 0);
        }
        
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllUserArr($byId = true, $userIdOrUsername = false)
    {
        $srch = User::getSearchObject(true);
        $srch->doNotCalculateRecords();

        if ($userIdOrUsername) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('user_id', 'credential_username'));
            if ($userIdOrUsername) {
                $srch->addCondition('user_id', '=', $userIdOrUsername);
            }
        } else {
            $srch->addMultipleFields(array('credential_username', 'user_id'));
            if ($userIdOrUsername) {
                $srch->addCondition('credential_username', '=', $userIdOrUsername);
            }
        }

        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getTaxCategoryArr($byId = true, $taxCatIdOrIdentifier = false)
    {
        $srch = Tax::getSearchObject(false, false);
        $srch->doNotCalculateRecords();

        if ($taxCatIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('taxcat_id', 'taxcat_identifier'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('taxcat_id', '=', $taxCatIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('taxcat_identifier', 'taxcat_id'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('taxcat_identifier', '=', $taxCatIdOrIdentifier);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getTaxCategoryByProductId($productId, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $taxData = array();
        $taxObj = Tax::getTaxCatObjByProductId($productId, 0, $type);
        $taxObj->addMultipleFields(array('ptt_taxcat_id', 'ptt_taxcat_id_rent'));
        $taxObj->doNotCalculateRecords();
        $taxObj->setPageSize(1);
        $taxObj->addOrder('ptt_seller_user_id', 'ASC');
        $rs = $taxObj->getResultSet();
        return $taxData = FatApp::getDb()->fetch($rs);
    }

    public function getAllBrandsArr($byId = true, $brandIdOrIdentifier = false)
    {
        $srch = Brand::getSearchObject(false, false, false);
        $srch->doNotCalculateRecords();

        if ($brandIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('brand_id', 'brand_identifier'));
            if ($brandIdOrIdentifier) {
                $srch->addCondition('brand_id', '=', $brandIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('brand_identifier', 'brand_id'));
            if ($brandIdOrIdentifier) {
                $srch->addCondition('brand_identifier', '=', $brandIdOrIdentifier);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getShippingPackageArr($byId = true, $taxCatIdOrIdentifier = false)
    {
        $srch = ShippingPackage::getSearchObject();
        $srch->doNotCalculateRecords();

        if ($taxCatIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('shippack_id', 'shippack_name'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('shippack_id', '=', $taxCatIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('shippack_name', 'shippack_id'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('shippack_name', '=', $taxCatIdOrIdentifier);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getShippingProfileArr($byId = true, $taxCatIdOrIdentifier = false, $userId = 0, $langId = 0)
    {
        $srch = ShippingProfile::getSearchObject($langId,false);
        $srch->doNotCalculateRecords();

        if ($taxCatIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('shipprofile_id', 'shipprofile_name'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('shipprofile_id', '=', $taxCatIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('shipprofile_name', 'shipprofile_id', 'shipprofile_user_id'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('shipprofile_name', '=', $taxCatIdOrIdentifier);
            }
        }

        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $srch->addCondition('shipprofile_user_id', '=', 0);
        } else {
            if (0 < $userId) {
                $cnd = $srch->addCondition('shipprofile_user_id', '=', 0);
                $cnd->attachCondition('shipprofile_user_id', '=', $userId);
            }
        }

        $rs = $srch->getResultSet();

        $res = [];
        if ($byId) {
            $res = $this->db->fetchAllAssoc($rs);
        } else {
            while ($row = $this->db->fetch($rs)) {
                $res[$row['shipprofile_name']][$row['shipprofile_user_id']] = $row['shipprofile_id'];
            }
        }

        return $res;
    }

    public function getTaxCategoriesArr($byId = true, $taxCatIdOrIdentifier = false)
    {
        $srch = Tax::getSearchObject(false, false);
        $srch->doNotCalculateRecords();

        if ($taxCatIdOrIdentifier) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('taxcat_id', 'taxcat_identifier'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('taxcat_id', '=', $taxCatIdOrIdentifier);
            }
        } else {
            $srch->addMultipleFields(array('taxcat_identifier', 'taxcat_id'));
            if ($taxCatIdOrIdentifier) {
                $srch->addCondition('taxcat_identifier', '=', $taxCatIdOrIdentifier);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getCountriesArr($byId = true, $countryIdOrCode = false)
    {
        $srch = Countries::getSearchObject(false, false);
        $srch->doNotCalculateRecords();

        if ($countryIdOrCode) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('country_id', 'country_code'));
            if ($countryIdOrCode) {
                $srch->addCondition('country_id', '=', $countryIdOrCode);
            }
        } else {
            $srch->addMultipleFields(array('country_code', 'country_id'));
            if ($countryIdOrCode) {
                $srch->addCondition('country_code', '=', $countryIdOrCode);
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getProductCategoriesByProductId($productId, $byId = true)
    {
        $srch = new SearchBase(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'ptc');
        $srch->addCondition(Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id', '=', $productId);

        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX . 'id = ptc.' . Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id', 'cat');
        if ($byId) {
            $srch->addMultipleFields(array(ProductCategory::DB_TBL_PREFIX . 'id', ProductCategory::DB_TBL_PREFIX . 'identifier'));
        } else {
            $srch->addMultipleFields(array(ProductCategory::DB_TBL_PREFIX . 'identifier', ProductCategory::DB_TBL_PREFIX . 'id'));
        }
        $rs = $srch->getResultSet();
        $records = $this->db->fetchAllAssoc($rs);
        if (!$records) {
            return false;
        }
        return $records;
    }

    public function getAllOptions($byId = true, $optionIdOrIdentifier = false)
    {
        $srch = Option::getSearchObject(false, false);
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('option_id', 'option_identifier'));
            if ($optionIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('option_id', '=', $optionIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('option_identifier', 'option_id'));
            if ($optionIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('option_identifier', '=', $optionIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllOptionValues($optionId, $byId = true, $optionValueIdOrIdentifier = false)
    {
        $optionId = FatUtility::convertToType($optionId, FatUtility::VAR_INT);
        $srch = OptionValue::getSearchObject();
        $srch->addCondition('ov.optionvalue_option_id', '=', $optionId);
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('optionvalue_id', 'optionvalue_identifier'));
            if ($optionValueIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('optionvalue_id', '=', $optionValueIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('optionvalue_identifier', 'optionvalue_id'));
            if ($optionValueIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('optionvalue_identifier', '=', $optionValueIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllTags($byId = true, $tagIdOrIdentifier = false)
    {
        $srch = Tag::getSearchObject();
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('tag_id', 'tag_identifier'));
            if ($tagIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('tag_id', '=', $tagIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('tag_identifier', 'tag_id'));
            if ($tagIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('tag_identifier', '=', $tagIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllShippingCompany($byId = true, $scompanyIdOrIdentifier = false)
    {
        $srch = ShippingCompanies::getSearchObject(false);
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('scompany_id', 'scompany_identifier'));
            if ($scompanyIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('scompany_id', '=', $scompanyIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('scompany_identifier', 'scompany_id'));
            if ($scompanyIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('scompany_identifier', '=', $scompanyIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllShippingDurations($byId = true, $durationIdOrIdentifier = false)
    {
        $srch = ShippingDurations::getSearchObject(false, false);
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('sduration_id', 'sduration_identifier'));
            if ($durationIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('sduration_id', '=', $durationIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('sduration_identifier', 'sduration_id'));
            if ($durationIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('sduration_identifier', '=', $durationIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getAllPrivacyPoints($byId = true, $policyPointIdOrIdentifier = false)
    {
        $srch = PolicyPoint::getSearchObject(false, false);
        $srch->doNotCalculateRecords();
        if ($byId) {
            $srch->addMultipleFields(array('ppoint_id', 'ppoint_identifier'));
            if ($policyPointIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('ppoint_id', '=', $policyPointIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        } else {
            $srch->addMultipleFields(array('ppoint_identifier', 'ppoint_id'));
            if ($policyPointIdOrIdentifier) {
                $srch->setPageSize(1);
                $srch->addCondition('ppoint_identifier', '=', $policyPointIdOrIdentifier);
            } else {
                $srch->doNotLimitRecords();
            }
        }
        $rs = $srch->getResultSet();
        return $row = $this->db->fetchAllAssoc($rs);
    }

    public function getProductIdByTempId($tempId, $userId = 0)
    {
        $srch = new SearchBase(Importexport::DB_TBL_TEMP_PRODUCT_IDS, 't');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition('pti_product_temp_id', '=', $tempId);
        $srch->addCondition('pti_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public function getCheckAndSetProductIdByTempId($sellerTempId, $userId, $type = SellerProduct::PRODUCT_TYPE_PRODUCT)
    {
        $productId = 0;
        $userTempIdData = $this->getProductIdByTempId($sellerTempId, $userId);

        if (!empty($userTempIdData) && $userTempIdData['pti_product_temp_id'] == $sellerTempId) {
            $productId = $userTempIdData['pti_product_id'];
        } else {
            if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
                $row = SellerProduct::getAttributesById($sellerTempId, array('selprod_id as product_id', 'selprod_user_id as product_seller_id'));
            } else {
                $row = Product::getAttributesById($sellerTempId, array('product_id', 'product_seller_id'));
            }

            if (!empty($row) && $row['product_seller_id'] == $userId) {
                $productId = $row['product_id'];
                $tempData = array(
                    'pti_product_id' => $productId,
                    'pti_product_temp_id' => $sellerTempId,
                    'pti_user_id' => $userId,
                );
                $this->db->deleteRecords(Importexport::DB_TBL_TEMP_PRODUCT_IDS, array('smt' => 'pti_product_id = ? and pti_user_id = ?', 'vals' => array($productId, $userId)));
                $this->db->insertFromArray(Importexport::DB_TBL_TEMP_PRODUCT_IDS, $tempData, false, array(), $tempData);
            }
        }
        return $productId;
    }

    public function getTempSelProdIdByTempId($tempId, $userId = 0)
    {
        $srch = new SearchBase(Importexport::DB_TBL_TEMP_SELPROD_IDS, 't');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition('spti_selprod_temp_id', '=', $tempId);
        $srch->addCondition('spti_user_id', '=', $userId);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public function getCheckAndSetSelProdIdByTempId($sellerTempId, $userId)
    {
        $selprodId = 0;
        $userTempIdData = $this->getTempSelProdIdByTempId($sellerTempId, $userId);
        if (!empty($userTempIdData) && $userTempIdData['spti_selprod_temp_id'] == $sellerTempId) {
            $selprodId = $userTempIdData['spti_selprod_id'];
        } else {
            $row = SellerProduct::getAttributesById($sellerTempId, array('selprod_id', 'selprod_user_id'));
            if (!empty($row) && $row['selprod_user_id'] == $userId) {
                $selprodId = $row['selprod_id'];
                $tempData = array(
                    'spti_selprod_id' => $selprodId,
                    'spti_selprod_temp_id' => $sellerTempId,
                    'spti_user_id' => $userId,
                );
                $this->db->deleteRecords(Importexport::DB_TBL_TEMP_SELPROD_IDS, array('smt' => 'spti_selprod_id = ? and spti_user_id = ?', 'vals' => array($selprodId, $userId)));
                $this->db->insertFromArray(Importexport::DB_TBL_TEMP_SELPROD_IDS, $tempData, false, array(), $tempData);
            }
        }
        return $selprodId;
    }

    public function getProductInventoryColumnArr(int $langId): array
    {
        return array(
            Labels::getLabel("LBL_Seller_Product_Id", $langId),
            Labels::getLabel("LBL_SKU", $langId),
            Labels::getLabel("LBL_Product", $langId),
            Labels::getLabel("LBL_Cost_Price", $langId),
            Labels::getLabel("LBL_Price", $langId),
            Labels::getLabel("LBL_Stock/Quantity", $langId)
        );
    }

    public function getCustomFieldsColoumArr(int $langId): array
    {
        $arr = [
            'attr_id' => Labels::getLabel('LBL_CUSTOM_FIELD_ID', $langId),
            'attrgrp_identifier' => Labels::getLabel('LBL_FIELD_GROUP_IDENTIFIER', $langId),
            'attrgrp_name' => Labels::getLabel('LBL_FIELD_GROUP_NAME', $langId),
            'attr_identifier' => Labels::getLabel('LBL_FIELD_IDENTIFIER', $langId),
            'attr_name' => Labels::getLabel('LBL_FIELD_NAME', $langId),
            'attr_type' => Labels::getLabel('LBL_FIELD_TYPE', $langId),
            'attr_postfix' => Labels::getLabel('LBL_POSTFIX', $langId),
            'attr_options' => Labels::getLabel('LBL_ATTRIBUTE_OPTIONS', $langId),
        ];
        if ($this->isDefaultSheetData($langId)) {
            unset($arr['attr_identifier']);
            unset($arr['attrgrp_name']);
            $arr['attr_display_in_filter'] = Labels::getLabel('LBL_DISPLAY_IN_FILTER', $langId);
            $arr['attr_prodcat_id'] = Labels::getLabel('LBL_CATEGORY_ID', $langId);
            $arr['attr_active'] = Labels::getLabel('LBL_Active', $langId);
        } else {
            unset($arr['attrgrp_identifier']);
            unset($arr['attr_type']);
        }
        
        return $this->formatColumnRow($arr);
    }

    public function getCategoriesArr(bool $byId = true, bool $categoryIdOrCode = false): array
    {
        $srch = ProductCategory::getSearchObject();
        $srch->doNotCalculateRecords();
        if ($categoryIdOrCode) {
            $srch->setPageSize(1);
        } else {
            $srch->doNotLimitRecords();
        }

        if ($byId) {
            $srch->addMultipleFields(array('prodcat_id', 'prodcat_identifier'));
            if ($categoryIdOrCode) {
                $srch->addCondition('prodcat_id', '=', $categoryIdOrCode);
            }
        } else {
            $srch->addMultipleFields(array('lower(prodcat_identifier) as prodcat_identifier', 'prodcat_id'));
            if ($categoryIdOrCode) {
                $srch->addCondition('prodcat_identifier', '=', $categoryIdOrCode);
            }
        }
        $rs = $srch->getResultSet();
        return $this->db->fetchAllAssoc($rs);
    }

    public function getProductCustomFieldsColoumArr(int $langId): array
    {
        $arr = [
            'product_id' => Labels::getLabel('LBL_PRODUCT_ID', $langId),
            'product_identifier' => Labels::getLabel('LBL_PRODUCT_NAME', $langId),
            'attr_id' => Labels::getLabel('LBL_Field_id', $langId),
            'attr_identifier' => Labels::getLabel('LBL_ATTRIBUTE_NAME', $langId),
            'field_value' => Labels::getLabel('LBL_VALUE', $langId),
        ];
		return $this->formatColumnRow($arr);
    }

    public function getAdddonsGeneralColoumArr(int $langId, int $userId = 0): array
    {
        $arr = ['selprod_id' => Labels::getLabel('LBL_Rental_Addon_Id', $langId)];
        if (!$userId) {
            if ($this->settings['CONF_USE_USER_ID']) {
                $arr['selprod_user_id'] = Labels::getLabel('LBL_User_ID', $langId);
            } else {
                $arr['credential_username'] = Labels::getLabel('LBL_Username', $langId);
            }
        }

        $arr['selprod_title'] = Labels::getLabel('LBL_Title', $langId);
        $arr['selprod_rental_terms'] = Labels::getLabel('LBL_Discription', $langId);
        
        if ($this->isDefaultSheetData($langId)) {
            $arr['selprod_price'] = Labels::getLabel('LBL_Selling_Price', $langId);
        
            if ($this->settings['CONF_USE_CATEGORY_ID']) {
                $arr['tax_category_id'] = Labels::getLabel('LBL_Addon_Tax_Category_Id', $langId);
            } else {
                $arr['tax_category_identifier'] = Labels::getLabel('LBL_Tax_Category_Identifier', $langId);
            }
            
            $arr['selprod_is_eligible_cancel'] = Labels::getLabel('LBL_Refund_Amount_On_Cancellation', $langId);
            $arr['selprod_is_eligible_refund'] = Labels::getLabel('LBL_Refund_Amount_On_Return', $langId);
            
            /* $arr['selprod_available_from'] = Labels::getLabel('LBL_Available_from', $langId); */
            $arr['selprod_active'] = Labels::getLabel('LBL_Active', $langId);
            /* $arr['selprod_deleted'] = Labels::getLabel('LBL_Deleted', $langId); */
        }
        return $this->formatColumnRow($arr);
    }

    public function getSelAddonMediaColoumArr(int $langId): array
    {
        $arr = [
            'selprod_id' => Labels::getLabel('LBL_Rental_Addon_Id', $langId),
            'selprod_title' => Labels::getLabel('LBL_Rental_Addon_Name', $langId),
        ];

        if ($this->settings['CONF_USE_LANG_ID']) {
            $arr['afile_lang_id'] = Labels::getLabel('LBL_lang_id', $langId);
        } else {
            $arr['afile_lang_code'] = Labels::getLabel('LBL_lang_code', $langId);
        }
        $arr['afile_physical_path'] = Labels::getLabel('LBL_File_Path', $langId);
        $arr['afile_name'] = Labels::getLabel('LBL_File_Name', $langId);
        $arr['afile_display_order'] = Labels::getLabel('LBL_Display_Order', $langId);
        return $this->formatColumnRow($arr);
    }

    public function getSelProdDurationDiscountColoumArr(int $langId): array
    {
        $arr = [
            'selprod_id' => Labels::getLabel('LBL_seller_product_id', $langId),
            'produr_rental_duration' => Labels::getLabel('LBL_Min_Duration', $langId),
            'produr_duration_type' => Labels::getLabel('LBL_Duration_Type', $langId),
            'produr_discount_percent' => Labels::getLabel('LBL_discount_percentage', $langId),
        ];
		
		return $this->formatColumnRow($arr);
    }

    public function getSelProdUnavialableDatesColoumArr(int $langId): array
    {
        $arr = [
            'selprod_id' => Labels::getLabel('LBL_seller_product_id', $langId),
            'pu_start_date' => Labels::getLabel('LBL_Start_date', $langId),
            'pu_end_date' => Labels::getLabel('LBL_End_date', $langId),
            'pu_quantity' => Labels::getLabel('LBL_Quantity', $langId),
        ];
		return $this->formatColumnRow($arr);
    }

    public function getSelProdAddonProductColoumArr(int $langId): array
    {
        $arr = [
            'selprod_id' => Labels::getLabel('LBL_seller_product_id', $langId),
            'spa_addon_product_id' => Labels::getLabel('LBL_Rental_Addon_Id', $langId),
        ];
		return $this->formatColumnRow($arr);
    }

    public function getAttrGrpArr() : array
    {
        $srch = AttributeGroup::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('lower(attrgrp_identifier) as attrgrp_identifier', 'attrgrp_id'));
        $rs = $srch->getResultSet();
        return $this->db->fetchAllAssoc($rs);
    }
	
	public function formatColumnRow(array $coloumArr)
    {
        $ii = 0;
		array_walk($coloumArr, function (&$string) use (&$ii) {
            if (0 == $ii) {
                $string = str_replace('"', '', preg_replace('/[^\x{0600}-\x{06FF}A-Za-z !@#$%^&*()]/u', '', $string));
            }
            $ii++;
        });
		
		return $coloumArr;
    }
}
