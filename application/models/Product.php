<?php

class Product extends MyAppModel
{

    public const DB_TBL = 'tbl_products';
    public const DB_TBL_LANG = 'tbl_products_lang';
    public const DB_TBL_PREFIX = 'product_';
    public const DB_TBL_LANG_PREFIX = 'productlang_';
    public const DB_NUMERIC_ATTRIBUTES_TBL = 'tbl_product_numeric_attributes';
    public const DB_NUMERIC_ATTRIBUTES_PREFIX = 'prodnumattr_';
    public const DB_TEXT_ATTRIBUTES_TBL = 'tbl_product_text_attributes';
    public const DB_TEXT_ATTRIBUTES_PREFIX = 'prodtxtattr_';
    public const DB_TBL_PRODUCT_TO_CATEGORY = 'tbl_product_to_category';
    public const DB_TBL_PRODUCT_TO_CATEGORY_PREFIX = 'ptc_';
    public const DB_PRODUCT_TO_OPTION = 'tbl_product_to_options';
    public const DB_PRODUCT_TO_OPTION_PREFIX = 'prodoption_';
    public const DB_PRODUCT_TO_SHIP = 'tbl_product_shipping_rates';
    public const DB_PRODUCT_TO_SHIP_PREFIX = 'pship_';
    public const DB_PRODUCT_TO_TAG = 'tbl_product_to_tags';
    public const DB_PRODUCT_TO_TAG_PREFIX = 'ptt_';
    public const DB_TBL_PRODUCT_FAVORITE = 'tbl_user_favourite_products';
    public const DB_PRODUCT_SPECIFICATION = 'tbl_product_specifications';
    public const DB_PRODUCT_SPECIFICATION_PREFIX = 'prodspec_';
    public const DB_PRODUCT_LANG_SPECIFICATION = 'tbl_product_specifications_lang';
    public const DB_PRODUCT_LANG_SPECIFICATION_PREFIX = 'prodspeclang_';
    public const DB_TBL_PRODUCT_SHIPPING = 'tbl_products_shipping';
    public const DB_TBL_PRODUCT_SHIPPING_PREFIX = 'ps_';
    public const DB_PRODUCT_SHIPPED_BY_SELLER = 'tbl_products_shipped_by_seller';
    public const DB_PRODUCT_SHIPPED_BY_SELLER_PREFIX = 'psbs_';
    public const DB_PRODUCT_MIN_PRICE = 'tbl_products_min_price';
    public const DB_PRODUCT_MIN_PRICE_PREFIX = 'pmp_';
    public const DB_PRODUCT_EXTERNAL_RELATIONS = 'tbl_product_external_relations';
    public const DB_PRODUCT_EXTERNAL_RELATIONS_PREFIX = 'perel_';
    public const PRODUCT_TYPE_PHYSICAL = 1;
    public const PRODUCT_TYPE_DIGITAL = 2;
    public const APPROVED = 1;
    public const UNAPPROVED = 0;
    public const INVENTORY_TRACK = 1;
    public const INVENTORY_NOT_TRACK = 0;
    public const CONDITION_NEW = 1;
    public const CONDITION_USED = 2;
    public const CONDITION_REFURBISH = 3;
    public const PRODUCT_VIEW_ORGINAL_URL = 'products/view/';
    public const PRODUCT_REVIEWS_ORGINAL_URL = 'reviews/product/';
    public const PRODUCT_MORE_SELLERS_ORGINAL_URL = 'products/sellers/';
    public const PRODUCT_FOR_SALE = 1;
    public const PRODUCT_FOR_RENT = 2;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject($langId = 0, $isDeleted = true, $joinSpecifics = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'tp');

        if ($langId > 0) {
            $srch->joinTable(
                    static::DB_TBL_LANG,
                    'LEFT OUTER JOIN',
                    'productlang_product_id = tp.product_id	AND productlang_lang_id = ' . $langId,
                    'tp_l'
            );
        }

        if ($isDeleted) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        }

        if (true === $joinSpecifics) {
            $srch->joinTable(
                    ProductSpecifics::DB_TBL,
                    'LEFT OUTER JOIN',
                    'psp.' . ProductSpecifics::DB_TBL_PREFIX . 'product_id = tp.' . static::tblFld('id'),
                    'psp'
            );
        }

        $srch->addOrder(static::DB_TBL_PREFIX . 'active', 'DESC');
        return $srch;
    }

    public static function requiredFields($prodType = Product::PRODUCT_TYPE_PHYSICAL)
    {
        $arr = array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
                'category_Id',
                'tax_category_id',
            ),
            ImportexportCommon::VALIDATE_FLOAT => array(
                'product_min_selling_price',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_name',
                'product_identifier',
                'credential_username',
                'category_indentifier',
                'product_type_identifier',
                'tax_category_identifier'
            ),
            ImportexportCommon::VALIDATE_INT => array(
                'product_seller_id',
                'product_type',
                'product_ship_free',
            ),
        );

        if (FatApp::getConfig('CONF_PRODUCT_BRAND_MANDATORY', FatUtility::VAR_INT, 1)) {
            $arr[ImportexportCommon::VALIDATE_POSITIVE_INT][] = 'product_brand_id';
            $arr[ImportexportCommon::VALIDATE_NOT_NULL][] = 'brand_identifier';
        }

        if (FatApp::getConfig('CONF_PRODUCT_DIMENSIONS_ENABLE', FatUtility::VAR_INT, 0) && $prodType == Product::PRODUCT_TYPE_PHYSICAL) {
            $physical = array(
                'product_dimension_unit_identifier',
                'product_weight_unit_identifier',
                'product_length',
                'product_width',
                'product_height',
                'product_weight',
            );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        if (FatApp::getConfig('CONF_PRODUCT_MODEL_MANDATORY', FatUtility::VAR_INT, 0)) {
            $physical = array(
                'product_model',
            );
            $arr[ImportexportCommon::VALIDATE_NOT_NULL] = array_merge($arr[ImportexportCommon::VALIDATE_NOT_NULL], $physical);
        }

        return $arr;
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId, $prodType = Product::PRODUCT_TYPE_PHYSICAL)
    {
        $requiredFields = static::requiredFields($prodType);
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredMediaFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'afile_physical_path',
                'afile_name',
            ),
        );
    }

    public static function validateMediaFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredMediaFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredShippingFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
                'country_id',
                'scompany_id',
                'sduration_id',
                'pship_charges',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'product_identifier',
                'credential_username',
                'scompany_identifier',
                'sduration_identifier',
                'user_id',
            ),
        );
    }

    public static function validateShippingFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredShippingFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getApproveUnApproveArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
            static::UNAPPROVED => Labels::getLabel('LBL_Un-Approved', $langId),
            static::APPROVED => Labels::getLabel('LBL_Approved', $langId),
        );
    }

    public static function getStatusClassArr()
    {
        return array(
            static::APPROVED => applicationConstants::CLASS_SUCCESS,
            static::UNAPPROVED => applicationConstants::CLASS_DANGER
        );
    }

    public static function getInventoryTrackArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
            static::INVENTORY_TRACK => Labels::getLabel('LBL_Track', $langId),
            static::INVENTORY_NOT_TRACK => Labels::getLabel('LBL_Do_not_track', $langId)
        );
    }

    public static function getConditionArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        return array(
            static::CONDITION_NEW => Labels::getLabel('LBL_New', $langId),
            static::CONDITION_USED => Labels::getLabel('LBL_Used', $langId),
            static::CONDITION_REFURBISH => Labels::getLabel('LBL_Refurbished', $langId)
        );
    }

    public static function getProductTypes($langId = 0)
    {
        $langId = FatUtility::convertToType($langId, FatUtility::VAR_INT);
        if (!$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $langId), E_USER_ERROR);
            return false;
        }
        return array(
            self::PRODUCT_TYPE_PHYSICAL => Labels::getLabel('LBL_Physical', $langId),
                /* self::PRODUCT_TYPE_DIGITAL => Labels::getLabel('LBL_Digital', $langId) */
        );
    }

    public static function getAttributesById($recordId, $attr = null, $joinSpecifics = false)
    {
        $recordId = FatUtility::int($recordId);

        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL, 'p');
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(static::tblFld('id'), '=', $recordId);

        if (true === $joinSpecifics) {
            $srch->joinTable(
                    ProductSpecifics::DB_TBL,
                    'LEFT OUTER JOIN',
                    'ps.' . ProductSpecifics::DB_TBL_PREFIX . 'product_id = p.' . static::tblFld('id'),
                    'ps'
            );
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

        /* get Numeric attributes data[ */
        if (!$attr) {
            $num_attr_row = static::getProductNumericAttributes($recordId);
            if (!empty($num_attr_row)) {
                $row = array_merge($row, $num_attr_row);
            }
        }
        /* ] */

        if (is_string($attr)) {
            return $row[$attr];
        }
        return $row;
    }

    public static function getProductDataById($langId = 0, $productId = 0, $attr = array())
    {
        $srch = self::getSearchObject($langId);
        $srch->addCondition('product_id', '=', $productId);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }
        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);
        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public function deleteProductImage(int $product_id, int $image_id, int $isSizeChart = 0): bool
    {
        if ($product_id < 1 || $image_id < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $fileHandlerObj = new AttachedFile();
        $type = AttachedFile::FILETYPE_PRODUCT_IMAGE;
        if ($isSizeChart > 0) {
            $type = AttachedFile::FILETYPE_PRODUCT_SIZE_CHART;
        }

        if (!$fileHandlerObj->deleteFile($type, $product_id, $image_id)) {
            $this->error = $fileHandlerObj->getError();
            return false;
        }
        return true;
    }

    public function updateProdImagesOrder($product_id, $order)
    {
        $product_id = FatUtility::int($product_id);
        if (is_array($order) && sizeof($order) > 0) {
            foreach ($order as $i => $id) {
                if (FatUtility::int($id) < 1) {
                    continue;
                }
                FatApp::getDb()->updateFromArray('tbl_attached_files', array('afile_display_order' => $i), array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_id = ?', 'vals' => array(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, $id)));
            }
            return true;
        }
        return false;
    }

    public function addUpdateProductCategories($product_id, $categories = array())
    {
        if (!$product_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_CATEGORY, array('smt' => static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id = ?', 'vals' => array($product_id)));
        if (empty($categories)) {
            return true;
        }

        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_CATEGORY);
        foreach ($categories as $category_id) {
            $to_save_arr = array();
            $to_save_arr['ptc_product_id'] = $product_id;
            $to_save_arr['ptc_prodcat_id'] = $category_id;
            $record->assignValues($to_save_arr);
            if (!$record->addNew(array(), $to_save_arr)) {
                $this->error = $record->getError();
                return false;
            }
        }
        return true;
    }

    public function addUpdateProductOption($option_id)
    {
        $option_id = FatUtility::int($option_id);
        if (!$this->mainTableRecordId || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_PRODUCT_TO_OPTION);
        $to_save_arr = array();
        $to_save_arr[static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id'] = $this->mainTableRecordId;
        $to_save_arr[static::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id'] = $option_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        $this->logUpdatedRecord();
        return true;
    }

    public function removeProductOption($option_id)
    {
        $db = FatApp::getDb();
        $option_id = FatUtility::int($option_id);
        if (!$this->mainTableRecordId || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_PRODUCT_TO_OPTION, array('smt' => static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id = ? AND ' . static::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id = ?', 'vals' => array($this->mainTableRecordId, $option_id)))) {
            $this->error = $db->getError();
            return false;
        }
        $this->logUpdatedRecord();
        return true;
    }

    public function addUpdateProductTag($tag_id)
    {
        $tag_id = FatUtility::int($tag_id);
        if (!$this->mainTableRecordId || !$tag_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_PRODUCT_TO_TAG);
        $to_save_arr = array();
        $to_save_arr[static::DB_PRODUCT_TO_TAG_PREFIX . 'product_id'] = $this->mainTableRecordId;
        $to_save_arr[static::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id'] = $tag_id;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }
        $this->logUpdatedRecord();
        return true;
    }

    public function addUpdateProductTags($tags = array())
    {
        if (!$this->mainTableRecordId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_PRODUCT_TO_TAG, array('smt' => static::DB_PRODUCT_TO_TAG_PREFIX . 'product_id = ?', 'vals' => array($this->mainTableRecordId)));
        if (empty($tags)) {
            return true;
        }

        foreach ($tags as $tag_id) {
            if (!$this->addUpdateProductTag($tag_id)) {
                return false;
            }
        }
        return true;
    }

    public function removeProductTag($tag_id)
    {
        $db = FatApp::getDb();
        $tag_id = FatUtility::int($tag_id);
        if (!$this->mainTableRecordId || !$tag_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_PRODUCT_TO_TAG, array('smt' => static::DB_PRODUCT_TO_TAG_PREFIX . 'product_id = ? AND ' . static::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id = ?', 'vals' => array($this->mainTableRecordId, $tag_id)))) {
            $this->error = $db->getError();
            return false;
        }
        $this->logUpdatedRecord();
        return true;
    }

    public static function getProductShippingRates($product_id, $lang_id, $country_id = 0, $sellerId = 0, $limit = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            //trigger_error(Labels::getLabel("ERR_Arguments_not_specified.",$this->commonLangId), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_PRODUCT_TO_SHIP, 'tpsr');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'country=tc.' . Countries::DB_TBL_LANG_PREFIX . 'country_id and tc.' . Countries::DB_TBL_LANG_PREFIX . 'lang_id=' . $lang_id, 'tc');
        $srch->joinTable(ShippingCompanies::DB_TBL, 'LEFT JOIN', 'tpsr.pship_company=sc.scompany_id ', 'sc');
        $srch->joinTable(ShippingCompanies::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.pship_company=tsc.scompanylang_scompany_id and tsc.' . ShippingCompanies::DB_TBL_LANG_PREFIX . 'lang_id=' . $lang_id, 'tsc');
        $srch->joinTable(ShippingDurations::DB_TBL_LANG, 'LEFT JOIN', 'tpsr.pship_duration=tsd.sdurationlang_sduration_id  and tsd.' . ShippingDurations::DB_TBL_PREFIX_LANG . 'lang_id=' . $lang_id, 'tsd');
        $srch->joinTable(ShippingDurations::DB_TBL, 'LEFT JOIN', 'tpsr.pship_duration=ts.sduration_id and sduration_deleted =0 ', 'ts');
        $srch->addCondition('tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'prod_id', '=', intval($product_id));
        if ($country_id > 0) {
            $srch->addDirectCondition('( tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'country =' . intval($country_id) . ' OR ' . 'tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'country =-1 )');
        }
        $srch->addCondition('tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'user_id', '=', $sellerId);

        $srch->addOrder('(tpsr.' . static::DB_PRODUCT_TO_SHIP_PREFIX . 'country = -1),country_name');
        $srch->addMultipleFields(
                array(
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'id',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'country',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'user_id',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'company',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'duration',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'charges',
                    static::DB_PRODUCT_TO_SHIP_PREFIX . 'additional_charges',
                    'IFNULL(' . Countries::DB_TBL_PREFIX . 'name', '\'' . Labels::getLabel('LBL_Everywhere_Else', $lang_id) . '\') as country_name',
                    'ifNull(' . ShippingCompanies::DB_TBL_PREFIX . 'name', ShippingCompanies::DB_TBL_PREFIX . 'identifier) as ' . ShippingCompanies::DB_TBL_PREFIX . 'name',
                    ShippingCompanies::DB_TBL_PREFIX . 'id',
                    ShippingCompanies::DB_TBL_LANG_PREFIX . 'scompany_id',
                    ShippingDurations::DB_TBL_PREFIX . 'name',
                    ShippingDurations::DB_TBL_PREFIX . 'id',
                    ShippingDurations::DB_TBL_PREFIX . 'from',
                    ShippingDurations::DB_TBL_PREFIX . 'identifier ',
                    ShippingDurations::DB_TBL_PREFIX . 'to',
                    ShippingDurations::DB_TBL_PREFIX . 'days_or_weeks',
                )
        );

        if ($limit > 0) {
            $srch->setPageSize($limit);
        } else {
            $srch->doNotLimitRecords(true);
            $srch->doNotCalculateRecords(true);
        }
        $rs = $srch->getResultSet();
        /* echo $srch->getQuery();die; */
        $row = FatApp::getDb()->fetchAll($rs);

        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public static function getProductFreeShippingAvailabilty($product_id, $lang_id, $country_id = 0, $sellerId = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        $sellerId = FatUtility::convertToType($sellerId, FatUtility::VAR_INT);
        //if (!$product_id || !$lang_id || !$sellerId) {
        if (!$product_id || !$lang_id) {
            //trigger_error(Labels::getLabel("ERR_Arguments_not_specified.",$this->commonLangId), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING, 'tps');
        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT JOIN', 'tps.' . static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'from_country_id=tc.' . Countries::DB_TBL_LANG_PREFIX . 'country_id and tc.' . Countries::DB_TBL_LANG_PREFIX . 'lang_id=' . $lang_id, 'tc');
        $srch->addCondition('tps.' . static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'product_id', '=', intval($product_id));

        $srch->addCondition('tps.' . static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'user_id', '=', $sellerId);
        $srch->addFld(
                array(
                    static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'free'
                )
        );

        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if ($row) {
            return $row[static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'free'];
        }
        return 0;
    }

    public static function getProductShippingDetails($productId, $langId, $userId = 0)
    {
        $productId = FatUtility::convertToType($productId, FatUtility::VAR_INT);
        if (!$productId || !$langId) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING);
        $srch->addCondition(static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'product_id', '=', $productId);
        $srch->addCondition(static::DB_TBL_PRODUCT_SHIPPING_PREFIX . 'user_id', '=', $userId);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $row = $db->fetch($rs);
        return $row;
    }

    public static function getProductOptions($product_id, $lang_id, $includeOptionValues = false, $option_is_separate_images = 0)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', Option::DB_TBL_PREFIX . 'id = ' . static::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id');

        $srch->joinTable(Option::DB_TBL . '_lang', 'LEFT JOIN', 'lang.optionlang_option_id = ' . Option::DB_TBL_PREFIX . 'id AND optionlang_lang_id = ' . $lang_id, 'lang');

        $srch->addMultipleFields(array('option_id', 'option_name', 'option_identifier'));

        if ($option_is_separate_images) {
            $srch->addCondition('option_is_separate_images', '=', applicationConstants::YES);
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $data = array();
        while ($row = $db->fetch($rs)) {
            if ($includeOptionValues) {
                $row['optionValues'] = static::getOptionValues($row['option_id'], $lang_id);
            }
            $data[] = $row;
        }
        return $data;
    }

    public static function getSeparateImageOptions($product_id, $lang_id)
    {
        $imgTypesArr = array(0 => Labels::getLabel('LBL_For_All_Options', $lang_id));
        $productOptions = Product::getProductOptions($product_id, $lang_id, true, 1);

        foreach ($productOptions as $val) {
            if (!empty($val['optionValues'])) {
                foreach ($val['optionValues'] as $k => $v) {
                    $imgTypesArr[$k] = $v;
                }
            }
        }
        return $imgTypesArr;
    }

    public static function getProductSpecifications($product_id, $lang_id)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id || !$lang_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $data = array();
        $languages = Language::getAllNames();

        foreach ($languages as $langId => $langName) {
            $srch = new SearchBase(static::DB_PRODUCT_SPECIFICATION);
            $srch->addCondition(static::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id', '=', $product_id);
            $srch->joinTable(static::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT JOIN', static::DB_PRODUCT_SPECIFICATION_PREFIX . 'id = ' . static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'prodspec_id and ' . static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'lang_id =' . $langId);
            $srch->addMultipleFields(
                    array(
                        static::DB_PRODUCT_SPECIFICATION_PREFIX . 'id',
                        static::DB_PRODUCT_SPECIFICATION_PREFIX . 'name',
                        static::DB_PRODUCT_SPECIFICATION_PREFIX . 'value'
                    )
            );
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetchAll($rs);
            foreach ($row as $resRow) {
                $data[$resRow[static::DB_PRODUCT_SPECIFICATION_PREFIX . 'id']][$langId] = $resRow;
            }
        }

        return $data;
    }

    public static function getProductTags($product_id, $lang_id = 0, $assoc = false)
    {
        $product_id = FatUtility::convertToType($product_id, FatUtility::VAR_INT);
        $lang_id = FatUtility::convertToType($lang_id, FatUtility::VAR_INT);
        if (!$product_id) {
            trigger_error(Labels::getLabel("ERR_Arguments_not_specified.", $lang_id), E_USER_ERROR);
            return false;
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_TAG);
        $srch->addCondition(static::DB_PRODUCT_TO_TAG_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(Tag::DB_TBL, 'INNER JOIN', Tag::DB_TBL_PREFIX . 'id = ' . static::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id');

        if ($lang_id) {
            $srch->joinTable(Tag::DB_TBL . '_lang', 'LEFT JOIN', 'lang.taglang_tag_id = ' . Tag::DB_TBL_PREFIX . 'id AND taglang_lang_id = ' . $lang_id, 'lang');

            if (true == $assoc) {
                $fields = array('tag_id', 'COALESCE(tag_name, tag_identifier) as tag_name');
            } else {
                $fields = array('tag_id', 'tag_identifier', 'COALESCE(tag_name, tag_identifier) as tag_name');
            }
            $srch->addMultipleFields($fields);
        } else {
            $srch->addMultipleFields(array('tag_id', 'tag_identifier'));
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        if (true == $assoc) {
            return $db->fetchAllAssoc($rs);
        }

        $data = array();
        while ($row = $db->fetch($rs)) {
            $data[] = $row;
        }
        return $data;
    }

    public static function getProductIdsByTagId($tagId)
    {
        $tagId = FatUtility::int($tagId);
        if (!$tagId) {
            return array();
        }

        $srch = new SearchBase(static::DB_PRODUCT_TO_TAG);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition(static::DB_PRODUCT_TO_TAG_PREFIX . 'tag_id', '=', $tagId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function getOptionValues($option_id, $lang_id)
    {
        $option_id = FatUtility::int($option_id);
        $lang_id = FatUtility::int($lang_id);
        if (!$option_id || !$lang_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', $lang_id), E_USER_ERROR);
        }
        $srch = new SearchBase(OptionValue::DB_TBL);
        $srch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT JOIN', 'lang.optionvaluelang_optionvalue_id = ' . OptionValue::DB_TBL_PREFIX . 'id AND optionvaluelang_lang_id = ' . $lang_id, 'lang');
        $srch->addCondition(OptionValue::DB_TBL_PREFIX . 'option_id', '=', $option_id);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('optionvalue_display_order');
        $srch->addOrder('optionvalue_option_id');
        $srch->addMultipleFields(array('optionvalue_id', 'optionvalue_name'));
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        return $db->fetchAllAssoc($rs);
    }

    public function getProductCategories($product_id)
    {
        $srch = new SearchBase(static::DB_TBL_PRODUCT_TO_CATEGORY, 'ptc');
        $srch->addCondition(static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id', '=', $product_id);
        $srch->joinTable(ProductCategory::DB_TBL, 'INNER JOIN', ProductCategory::DB_TBL_PREFIX . 'id = ptc.' . static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id', 'cat');
        $srch->addMultipleFields(array('prodcat_id'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs, 'prodcat_id');
        if (!$records) {
            return false;
        }
        return $records;
    }

    public function addUpdateNumericAttributes($data)
    {
        $record = new TableRecord(self::DB_NUMERIC_ATTRIBUTES_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function addUpdateTextualAttributes($data)
    {
        $record = new TableRecord(self::DB_TEXT_ATTRIBUTES_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    /* public static function getProductNumericAttributes(int $prodId): array
      {
      if (empty($prodId)) {
      trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
      }

      $srch = new SearchBase(static::DB_NUMERIC_ATTRIBUTES_TBL);
      $srch->addCondition(static::DB_NUMERIC_ATTRIBUTES_PREFIX . 'product_id', '=', $prodId);
      $rs = $srch->getResultSet();
      return FatApp::getDb()->fetchAll($rs, 'prodnumattr_attrgrp_id');
      }

      public static function getProductTextualAttributes(int $prodId, int $langId = 0, bool $keyByGroupId = false): array
      {
      if (empty($prodId)) {
      trigger_error(Labels::getLabel('ERR_Invalid_Arguments', $this->commonLangId), E_USER_ERROR);
      }

      $srch = new SearchBase(static::DB_TEXT_ATTRIBUTES_TBL);
      $srch->addCondition(static::DB_TEXT_ATTRIBUTES_PREFIX . 'product_id', '=', $prodId);
      if (0 < $langId) {
      $srch->addCondition(static::DB_TEXT_ATTRIBUTES_PREFIX . 'lang_id', '=', $langId);
      }
      $rs = $srch->getResultSet();
      if ($keyByGroupId) {
      $records = FatApp::getDb()->fetchAll($rs, 'prodtxtattr_attrgrp_id');
      } else {
      $records = FatApp::getDb()->fetchAll($rs);
      }
      return $records;
      } */

    public static function getProductNumericAttributes($prodId, bool $keyByGroupId = false)
    {
        if (empty($prodId)) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = new SearchBase(static::DB_NUMERIC_ATTRIBUTES_TBL);
        if (!is_array($prodId)) {
            $srch->addCondition(static::DB_NUMERIC_ATTRIBUTES_PREFIX . 'product_id', '=', $prodId);
        } else {
            $srch->addCondition(static::DB_NUMERIC_ATTRIBUTES_PREFIX . 'product_id', 'IN', $prodId);
        }

        $rs = $srch->getResultSet();
        if ($keyByGroupId) {
            $records = FatApp::getDb()->fetchAll($rs, 'prodnumattr_attrgrp_id');
        } else {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        return $records;
    }

    public static function getProductTextualAttributes($prodId, int $langId = 0, bool $keyByGroupId = false): array
    {
        if (empty($prodId)) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', $this->commonLangId), E_USER_ERROR);
        }
        $srch = new SearchBase(static::DB_TEXT_ATTRIBUTES_TBL);
        if (!is_array($prodId)) {
            $srch->addCondition(static::DB_TEXT_ATTRIBUTES_PREFIX . 'product_id', '=', $prodId);
        } else {
            $srch->addCondition(static::DB_TEXT_ATTRIBUTES_PREFIX . 'product_id', 'IN', $prodId);
        }
        if (0 < $langId) {
            $srch->addCondition(static::DB_TEXT_ATTRIBUTES_PREFIX . 'lang_id', '=', $langId);
        }
        $rs = $srch->getResultSet();
        if ($keyByGroupId) {
            $records = FatApp::getDb()->fetchAll($rs, 'prodtxtattr_attrgrp_id');
        } else {
            $records = FatApp::getDb()->fetchAll($rs);
        }
        return $records;
    }

    public static function generateProductOptionsUrl($selprod_id, $selectedOptions, $option_id, $optionvalue_id, $product_id, $returnId = false)
    {
        $selectedOptions[$option_id] = $optionvalue_id;
        sort($selectedOptions);

        $selprod_code = $product_id . '_' . implode('_', $selectedOptions);

        $prodSrchObj = new ProductSearch();
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->addCondition('selprod_id', '!=', $selprod_id);
        $prodSrchObj->addMultipleFields(array('product_id', 'selprod_id', 'theprice'));
        $prodSrchObj->addCondition('product_id', '=', $product_id);

        $prodSrch = clone $prodSrchObj;

        $prodSrch->addCondition('selprod_code', '=', $selprod_code);
        $prodSrch->doNotLimitRecords();
        $prodSrch->addOrder('theprice', 'ASC');
        $productRs = $prodSrch->getResultSet();
        //echo $prodSrch->getQuery();
        $product = FatApp::getDb()->fetch($productRs);
        if ($product) {
            if ($returnId) {
                return $product['selprod_id'];
            }
            return UrlHelper::generateUrl('Products', 'view', array($product['selprod_id']));
        } else {
            $prodSrch2 = new ProductSearch(CommonHelper::getLangId());
            $prodSrch2->doNotCalculateRecords();
            $prodSrch2->setDefinedCriteria();
            $prodSrch2->addCondition('selprod_id', '!=', $selprod_id);
            $prodSrch2->addCondition('product_id', '=', $product_id);
            $prodSrch2->addCondition('selprod_code', 'LIKE', '%_' . $optionvalue_id . '%');
            $prodSrch2->addMultipleFields(array('selprod_id', 'special_price_found', 'theprice'));
            $prodSrch2->setPageSize(1);
            $prodSrch2->addOrder('theprice', 'ASC');
            $productRs = $prodSrch2->getResultSet();
            $product = FatApp::getDb()->fetch($productRs);

            if ($product) {
                if ($returnId) {
                    return $product['selprod_id'];
                }
                return UrlHelper::generateUrl('Products', 'view', array($product['selprod_id'])) . "::";
            } else {
                return false;
            }
            return false;
        }
    }

    public static function uniqueProductAction($selprodCode, $weightageKey)
    {
        /* $ipAddress = $_SERVER['REMOTE_ADDR'];
          list($product_id) = explode('_',$selprodCode);
          $product_id = FatUtility::int($product_id);

          $srch = new SearchBase('tbl_smart_log_actions');

          $date = date('Y-m-d H:i:s');
          $currentDate = strtotime($date);
          $futureDate = $currentDate - (60*5);
          $formatDate = date("Y-m-d H:i:s", $futureDate);

          $srch->addDirectCondition("slog_ip = '".$ipAddress."' and '".$formatDate."' < slog_datetime and      slog_swsetting_key = '".$weightageKey."' and slog_record_code = '".$selprodCode."' and slog_record_id = '".$product_id."' and slog_type = '".SmartUserActivityBrowsing::TYPE_PRODUCT."'");
          $srch->doNotCalculateRecords();
          $srch->doNotLimitRecords();
          $srch->addMultipleFields(array('slog_ip'));
          $rs = $srch->getResultSet();
          $row =  FatApp::getDb()->fetch($rs);
          return ($row == false)?true:false; */
    }

    public static function recordProductWeightage($selprodCode, $weightageKey, $eventWeightage = 0)
    {
        list($productId) = explode('_', $selprodCode);
        $productId = FatUtility::int($productId);

        if (1 > $productId) {
            return false;
        }

        if ($eventWeightage == 0) {
            $weightageArr = SmartWeightageSettings::getWeightageAssoc();
            $eventWeightage = !empty($weightageArr[$weightageKey]) ? $weightageArr[$weightageKey] : 0;
        }

        if (!UserAuthentication::isUserLogged()) {
            $userId = CommonHelper::getUserIdFromCookies();
        } else {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $record = new TableRecord('tbl_recommendation_activity_browsing');

        $assignFields = array();
        $assignFields['rab_session_id'] = session_id();
        $assignFields['rab_user_id'] = $userId;
        $assignFields['rab_record_id'] = $productId;
        $assignFields['rab_record_type'] = SmartUserActivityBrowsing::TYPE_PRODUCT;
        $assignFields['rab_weightage_key'] = $weightageKey;
        $assignFields['rab_weightage'] = $eventWeightage;
        $assignFields['rab_last_action_datetime'] = date('Y-m-d H:i:s');

        $onDuplicateKeyUpdate = array_merge($assignFields, array('rab_weightage' => 'mysql_func_rab_weightage + ' . $eventWeightage));

        FatApp::getDb()->insertFromArray('tbl_recommendation_activity_browsing', $assignFields, true, array(), $onDuplicateKeyUpdate);
    }

    public static function addUpdateProductBrowsingHistory($selprodCode, $weightageKey, $weightageVal = 1)
    {
        /* list($productId) = explode('_',$selprodCode);
          $productId = FatUtility::int($productId);
          $weightageVal = FatUtility::int($weightageVal);

          $weightageKey = FatUtility::int($weightageKey);
          $weightageKey = 1 ;

          if(1 > $weightageKey || 1 > $weightageVal) { return false;}

          if(!static::uniqueProductAction($selprodCode,$weightageKey)){ return false ;}

          if (!UserAuthentication::isUserLogged()) {
          $userId = CommonHelper::getUserIdFromCookies();
          }else{
          $userId = UserAuthentication::getLoggedUserId();
          }

          $record = new TableRecord('tbl_products_browsing_history');

          $assignFields = array();
          $assignFields['pbhistory_sessionid'] = session_id();
          $assignFields['pbhistory_selprod_code'] = $selprodCode;
          $assignFields['pbhistory_swsetting_key'] = $weightageKey;
          $assignFields['pbhistory_user_id'] = $userId;
          $assignFields['pbhistory_product_id'] = $productId;
          $assignFields['pbhistory_count'] = $weightageVal;
          $assignFields['pbhistory_datetime'] = date('Y-m-d H:i:s');

          $onDuplicateKeyUpdate = array_merge($assignFields,array('pbhistory_count'=>'mysql_func_pbhistory_count + '.$weightageVal));

          FatApp::getDb()->insertFromArray('tbl_products_browsing_history',$assignFields,true,array(),$onDuplicateKeyUpdate); */
    }

    public static function tempHoldStockCount($selprod_id = 0, $userId = 0, $pshold_prodgroup_id = 0, $useProductGroup = false)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $pshold_prodgroup_id = FatUtility::int($pshold_prodgroup_id);
        $intervalInMinutes = FatApp::getConfig('cart_stock_hold_minutes', FatUtility::VAR_INT, 15);

        $srch = new SearchBase('tbl_product_stock_hold');
        $srch->doNotCalculateRecords();
        $srch->addOrder('pshold_id', 'ASC');
        $srch->addCondition('pshold_added_on', '>=', 'mysql_func_DATE_SUB( NOW(), INTERVAL ' . $intervalInMinutes . ' MINUTE )', 'AND', true);
        $srch->addCondition('pshold_selprod_id', '=', $selprod_id);

        if ($useProductGroup == true) {
            $srch->addCondition('pshold_prodgroup_id', '=', $pshold_prodgroup_id);
        }

        if ($userId > 0) {
            $srch->addCondition('pshold_user_id', '=', $userId);
        }
        $srch->addMultipleFields(array('sum(pshold_selprod_stock) as stockHold'));
        $srch->setPageNumber(1);
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $stockHoldRow = FatApp::getDb()->fetch($rs);
        if ($stockHoldRow == false) {
            return 0;
        }
        return $stockHoldRow['stockHold'];
    }

    public function addUpdateUserFavoriteProduct(int $user_id, int $selProdId)
    {
        $data_to_save = array('ufp_user_id' => $user_id, 'ufp_selprod_id' => $selProdId);
        $data_to_save_on_duplicate = array('ufp_selprod_id' => $selProdId);
        if (!FatApp::getDb()->insertFromArray(static::DB_TBL_PRODUCT_FAVORITE, $data_to_save, false, array(), $data_to_save_on_duplicate)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getUserFavouriteProducts($user_id, $langId)
    {
        $user_id = FatUtility::int($user_id);
        $srch = new UserFavoriteProductSearch();
        $srch->setDefinedCriteria($langId);
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription($langId, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('ufp_user_id', '=', $user_id);
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock'));
        $srch->setPageNumber(1);
        $srch->setPageSize(4);
        $srch->addGroupBy('selprod_id');

        /* die($srch->getQuery());  */
        $rs = $srch->getResultSet();
        $result['uwlist_id'] = 0;
        $result['uwlist_title'] = Labels::getLabel('LBL_FAVORITE_LIST', $langId);
        $result['uwlist_type'] = UserWishList::TYPE_FAVOURITE;

        $result['totalProducts'] = $srch->recordCount();
        $result['products'] = FatApp::getDb()->fetchAll($rs);
        return $result;
    }

    public static function getProductMetaData($selProductId = 0)
    {
        if ($selProductId <= 0) {
            return false;
        }
        $srch = MetaTag::getSearchObject();
        $srch->addCondition(MetaTag::DB_TBL_PREFIX . 'record_id', '=', $selProductId);
        $srch->addCondition(MetaTag::DB_TBL_PREFIX . 'controller', '=', 'Products');
        $srch->addCondition(MetaTag::DB_TBL_PREFIX . 'action', '=', 'view');
        $srch->addMultipleFields(array('meta_id'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        return $records;
    }

    public static function isProductShippedBySeller($productId, $productAddedBySellerId, $selProdSellerId)
    {
        $productId = FatUtility::int($productId);
        $productAddedBySellerId = FatUtility::int($productAddedBySellerId);
        $selProdSellerId = FatUtility::int($selProdSellerId);
        if ($productAddedBySellerId && $productAddedBySellerId == $selProdSellerId) {
            return true;
        }
        $srch = new SearchBase(static::DB_PRODUCT_SHIPPED_BY_SELLER, 'psbs');
        $srch->addCondition('psbs_product_id', '=', $productId);
        $srch->addCondition('psbs_user_id', '=', $selProdSellerId);
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['psbs_user_id'] == $selProdSellerId) {
            return true;
        }
        return false;
    }

    public function getTotalProductsAddedByUser($user_id)
    {
        $srch = SellerProduct::getSearchObject(CommonHelper::getLangId());
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . CommonHelper::getLangId(), 'p_l');
        $srch->addOrder('product_name');
        $srch->addCondition('selprod_user_id', '=', $user_id);
        /* $srch->addCondition('selprod_deleted', '=', 0); */
        $srch->addMultipleFields(
                array(
                    'count(selprod_id) as totProducts'
                )
        );
        $srch->addOrder('selprod_active', 'DESC');

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $produtcCountList = $db->fetch($rs);
        $totalProduct = $produtcCountList['totProducts'];
        return $totalProduct;
    }

    public static function getProductShippingTitle($langId, $shippingDetails = array())
    {
        $langId = FatUtility::int($langId);
        if (1 > $langId) {
            return;
        }

        if (empty($shippingDetails)) {
            return;
        } else {
            return FatUtility::decodeHtmlEntities('<em><strong>' . $shippingDetails['country_name'] . '</em></strong> ' . Labels::getLabel('LBL_by', $langId) . ' <strong>' . $shippingDetails['scompany_name'] . '</strong> ' . Labels::getLabel('LBL_in', $langId) . ' ' . ShippingDurations::getShippingDurationTitle($shippingDetails, $langId));
        }
    }

    public static function isSellProdAvailableForUser($selProdCode, $langId, $userId = 0, $selprod_id = 0)
    {
        $userId = FatUtility::int($userId);
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }

        if (1 > $userId) {
            return false;
        }

        $srch = SellerProduct::getSearchObject($langId);
        $srch->addCondition('selprod_code', '=', $selProdCode);
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addOrder('selprod_id', 'DESC');
        /* $srch->addCondition('selprod_deleted','=',applicationConstants::NO); */
        if ($selprod_id) {
            $srch->addCondition('selprod_id', '!=', $selprod_id);
        }
        $db = FatApp::getDb();

        $srch->addMultipleFields(array('selprod_id', 'selprod_deleted'));
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);

        if ($row == false) {
            return array();
        }

        return $row;
    }

    public static function availableForAddToStore($productId, $userId)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);

        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        // $srch->addCondition('tspo.selprodoption_optionvalue_id', 'is', 'mysql_func_null', 'and', true);
        $srch->addFld('count(DISTINCT selprod_code) as count');
        $rs = $srch->getResultSet();
        $alreadyAdded = FatApp::getDb()->fetch($rs);
        if ($alreadyAdded == false) {
            return true;
        }
        $alreadyAddedOptions = $alreadyAdded['count'];

        $srch = new SearchBase(static::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(static::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $productId);
        $srch->joinTable(OptionValue::DB_TBL, 'LEFT JOIN', 'prodoption_option_id = opval.optionvalue_option_id', 'opval');
        $srch->addFld('count(DISTINCT optionvalue_id) as count');
        $srch->addGroupBy('prodoption_option_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $totalOptionCombination = 1;

        while ($row = FatApp::getDb()->fetch($rs)) {
            $totalOptionCombination *= $row['count'];
        }

        return ($totalOptionCombination - $alreadyAddedOptions) > 0 ? true : false;
    }

    public static function hasInventory($productId, $userId)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);
        if (!$productId || !$userId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', CommonHelper::getLangId()));
        }
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addFld('selprodoption_optionvalue_id');
        $rs = $srch->getResultSet();
        $alreadyAdded = FatApp::getDb()->fetchAll($rs, 'selprodoption_optionvalue_id');
        if (empty($alreadyAdded)) {
            return false;
        }
        return true;
    }

    public static function addUpdateProductSellerShipping($product_id, $data_to_be_save, $userId)
    {
        $productSellerShiping = array();
        $productSellerShiping['ps_product_id'] = $product_id;
        $productSellerShiping['ps_user_id'] = $userId;
        $productSellerShiping['ps_from_country_id'] = $data_to_be_save['ps_from_country_id'];
        $productSellerShiping['ps_free'] = $data_to_be_save['ps_free'];
        if (!FatApp::getDb()->insertFromArray(Product::DB_TBL_PRODUCT_SHIPPING, $productSellerShiping, false, array(), $productSellerShiping)) {
            return false;
        }
        return true;
    }

    public static function addUpdateProductShippingRates($product_id, $data, $userId = 0)
    {
        static::removeProductShippingRates($product_id, $userId);

        if (empty($data) || count($data) == 0) {
            // $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId);
            return false;
        }

        foreach ($data as $key => $val) {
            if (isset($val["country_id"]) && ($val["country_id"] > 0 || $val["country_id"] == -1) && $val["company_id"] > 0 && $val["processing_time_id"] > 0) {
                $prodShipData = array(
                    'pship_prod_id' => $product_id,
                    'pship_country' => (isset($val["country_id"]) && FatUtility::int($val["country_id"])) ? FatUtility::int($val["country_id"]) : 0,
                    'pship_user_id' => $userId,
                    'pship_company' => (isset($val["company_id"]) && FatUtility::int($val["company_id"])) ? FatUtility::int($val["company_id"]) : 0,
                    'pship_duration' => (isset($val["processing_time_id"]) && FatUtility::int($val["processing_time_id"])) ? FatUtility::int($val["processing_time_id"]) : 0,
                    'pship_charges' => (1 > FatUtility::float($val["cost"]) ? 0 : FatUtility::float($val["cost"])),
                    'pship_additional_charges' => FatUtility::float($val["additional_cost"]),
                );

                if (!FatApp::getDb()->insertFromArray(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, $prodShipData, false, array(), $prodShipData)) {
                    // $this->error = FatApp::getDb()->getError();
                    return false;
                }
            }
        }

        return true;
    }

    public static function removeProductShippingRates($product_id, $userId)
    {
        $db = FatApp::getDb();
        $product_id = FatUtility::int($product_id);
        $userId = FatUtility::int($userId);

        if (!$db->deleteRecords(ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES, array('smt' => ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX . 'prod_id = ? and ' . ShippingApi::DB_TBL_PRODUCT_SHIPPING_RATES_PREFIX . 'user_id = ?', 'vals' => array($product_id, $userId)))) {
            // $this->error = $db->getError();
            return false;
        }
        return true;
    }

    public function removeProductCategory($option_id)
    {
        $db = FatApp::getDb();
        $option_id = FatUtility::int($option_id);
        if (!$this->mainTableRecordId || !$option_id) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        if (!$db->deleteRecords(static::DB_TBL_PRODUCT_TO_CATEGORY, array('smt' => static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id = ? AND ' . static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id = ?', 'vals' => array($this->mainTableRecordId, $option_id)))) {
            $this->error = $db->getError();
            return false;
        }

        if (!$this->updateModifiedTime()) {
            return false;
        }

        return true;
    }

    public function addUpdateProductCategory($prodCatId)
    {
        $prodCatId = FatUtility::int($prodCatId);
        if (!$this->mainTableRecordId || !$prodCatId) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_CATEGORY);
        $to_save_arr = array();
        $to_save_arr[static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id'] = $this->mainTableRecordId;
        $to_save_arr[static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id'] = $prodCatId;
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            $this->error = $record->getError();
            return false;
        }

        if (!$this->updateModifiedTime()) {
            return false;
        }
        return true;
    }

    public function deleteProduct()
    {
        $productId = FatUtility::int($this->mainTableRecordId);
        if (0 >= $productId) {
            Message::addErrorMessage($this->str_invalid_request_id);
            FatUtility::dieWithError(Message::getHtml());
        }

        $product = new Product($productId);
        if (!$product->deleteRecord()) {
            $this->error = $product->getError();
            return false;
        }
        return true;
    }

    public static function verifyProductIsValid($selprod_id)
    {
        $prodSrch = new ProductSearch();
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->addMultipleFields(array('selprod_id', 'product_id'));
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);

        if ($product == false) {
            return false;
        }
        return true;
    }

    public static function convertArrToSrchFiltersAssocArr($arr)
    {
        return SearchItem::convertArrToSrchFiltersAssocArr($arr);
    }

    public static function getListingObj($criteria, $langId = 0, $userId = 0, $mainQuery = true)
    {
        $srch = new ProductSearch($langId);
        $keyword = '';
        if (array_key_exists('keyword', $criteria)) {
            $keyword = $criteria['keyword'];
        }

        if (true === MOBILE_APP_API_CALL) {
            $criteria['optionvalue'] = !empty($criteria['optionvalue']) ? json_decode($criteria['optionvalue'], true) : '';
        }
        
        $productType = applicationConstants::PRODUCT_FOR_RENT;
        if (!empty($criteria['producttype']) && in_array(Product::PRODUCT_FOR_SALE, $criteria['producttype'])) {
            $productType = applicationConstants::PRODUCT_FOR_SALE;
        }
        
        $shop_id = 0;
        if (array_key_exists('shop_id', $criteria)) {
            $shop_id = FatUtility::int($criteria['shop_id']);
        }

        $criteria['max_price'] = true;
        $srch->joinSellerProductWithData($criteria);
        $srch->joinSellers();
        $srch->joinShops($langId, true, true, $shop_id);
        $srch->setGeoAddress();
        $srch->joinBasedOnPriceConditionInnerQry('', $criteria, true, $mainQuery);
        $srch->unsetDefaultLangForJoins();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinBrands($langId);
        $srch->joinProductToCategory($langId);
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();
        
        $includeRating = false;
        if (true === MOBILE_APP_API_CALL) {
            $includeRating = true;
        }

        if (array_key_exists('top_products', $criteria)) {
            $includeRating = true;
        }
        if ($shop_id > 0 && array_key_exists('shop_featured', $criteria) && $criteria['shop_featured'] > 0) {
            $srch->addCondition('product_featured', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        }
        
        
        $ratingJoined = false;
        if (array_key_exists('sortBy', $criteria)) {
            $sortBy = $criteria['sortBy'];
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            if ($sortBy == 'rating') {
                $includeRating = true;
            }
        }
        
        if ((isset($criteria['vtype']) && $criteria['vtype'] == 'map') || true === $includeRating) {
            /* $selProdReviewObj = new SelProdReviewSearch();
            $selProdReviewObj->joinProducts();
            $selProdReviewObj->joinSellerProducts();
            $selProdReviewObj->joinSelProdRating();
            $selProdReviewObj->joinUser();
            $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
            $selProdReviewObj->doNotCalculateRecords();
            $selProdReviewObj->doNotLimitRecords();
            $selProdReviewObj->addGroupBy('spr.spreview_product_id');
            $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
            $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spr.spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
            $selProdRviewSubQuery = $selProdReviewObj->getQuery();
            $srch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');
            $srch->addFld(['COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'COALESCE(sq_sprating.totReviews,0) totReviews']); */
            
            $srch->addFld(['selprod_avg_rating as prod_rating', 'selprod_review_count as totReviews']);
            if (array_key_exists('top_products', $criteria)) {
                if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                    $srch->addCondition('selprod_sold_count', '>', 0);
                } else {
                    $srch->addCondition('selprod_rent_count', '>', 0);
                }
            }
            
        }
        /* to check current product is in wish list or not[ */
        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if ($favVar == applicationConstants::NO) {
            $srch->joinFavouriteProducts($userId);
            $srch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $srch->joinUserWishListProducts($userId);
            $srch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        /* substring_index(group_concat(IFNULL(prodcat_name, prodcat_identifier) ORDER BY IFNULL(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name */
        if ($mainQuery) {
            $srch->addMultipleFields(
                array(
                    'prodcat_code', 'prodcat_comparison', 'product_id', 'prodcat_id', 'COALESCE(product_name, product_identifier) as product_name', 'product_model', 'product_updated_on', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'user_name', 'selprod_available_from', 'sprodata_rental_available_from'
                )
            );
        } else {
            $srch->addMultipleFields(
                array(
                    'product_id', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'IFNULL(selprod_title, IFNULL(product_name, product_identifier)) as selprod_title', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'splprice_start_date', 'splprice_end_date', 'splprice_type', 'user_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sold_count' , 'selprod_rent_count', 'selprod_return_policy', 'selprod_min_order_qty', 'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'sprodata_rental_stock', 'sprodata_rental_price as rent_price', 'sprodata_minimum_rental_duration', 'selprod_active', 'sprodata_rental_active', 'sprodata_duration_type', 'shop.shop_id', 'shop.shop_lat', 'shop.shop_lng', 'selprod_code', 'COALESCE(shop_name, shop_identifier) as shop_name', 'IFNULL(splprice_id, 0) as special_price_found', 'selprod_available_from', 'sprodata_rental_available_from'
                )
            );
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        }
        
        if (array_key_exists('category', $criteria)) {
            $srch->addCategoryCondition($criteria['category']);
            if (array_key_exists('attributes', $criteria)) {
                $srch->joinAttributes($criteria['attributes']);
            }
        }

        if (array_key_exists('prodcat', $criteria)) {
            if (true === MOBILE_APP_API_CALL) {
                $criteria['prodcat'] = json_decode($criteria['prodcat'], true);
            }
            $srch->addCategoryCondition($criteria['prodcat']);
        }

        if (0 < $shop_id) {
            $srch->addShopIdCondition($shop_id);
        }


        if (array_key_exists('collection_id', $criteria)) {
            $collection_id = FatUtility::int($criteria['collection_id']);
            if (0 < $collection_id) {
                $srch->addCollectionIdCondition($collection_id);
            }
        }

        if (!empty($keyword)) {
            $priceKey = 'spd.sprodata_rental_price';
            if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                $priceKey = 'sp.selprod_price';
            }
            $srch->addKeywordSearch($keyword);
            $srch->addFld('if(selprod_title LIKE ' . FatApp::getDb()->quoteVariable('%' . $keyword . '%') . ',  1,   0  ) as keywordmatched');
            $srch->addFld('if(selprod_title LIKE ' . FatApp::getDb()->quoteVariable('%' . $keyword . '%') . ',  IFNULL(splprice_price, '. $priceKey .'), (COALESCE(splprice_price, '. $priceKey .'))) as theprice');
            $srch->addFld(
                    'if(selprod_title LIKE ' . FatApp::getDb()->quoteVariable('%' . $keyword . '%') . ',  CASE WHEN splprice_selprod_id IS NULL THEN 0 ELSE 1 END, (IF(splprice_selprod_id IS NULL, 0, 1))) as special_price_found'
            );
            $sortBy = 'keyword_relevancy';
        } else {
            //$srch->addFld('theprice');
            //$srch->addFld('special_price_found');
            $sortBy = 'popularity';
        }

        if ($productType == applicationConstants::PRODUCT_FOR_RENT && isset($criteria['rentalstart'])) {
            $rentavailable =  $srch->addCondition('sprodata_rental_available_from', '<=',  date('Y-m-d', strtotime($criteria['rentalstart'])));
            $rentavailable->attachCondition('sprodata_rental_available_from', '<=',  date('Y-m-d', strtotime($criteria['rentalend'])), 'OR');
            $srch->addHaving('availableQty', 'IS NOT', 'mysql_func_null', 'and', true);
        }

        if (array_key_exists('brand', $criteria)) {
            if (!empty($criteria['brand'])) {
                if (true === MOBILE_APP_API_CALL && !is_array($criteria['brand'])) {
                    $criteria['brand'] = json_decode($criteria['brand'], true);
                }
                $srch->addBrandCondition($criteria['brand']);
            }
        }

        if (array_key_exists('optionvalue', $criteria)) {
            if (!empty($criteria['optionvalue'])) {
                $srch->addOptionCondition($criteria['optionvalue']);
            }
        }

        if (array_key_exists('condition', $criteria)) {
            if (true === MOBILE_APP_API_CALL) {
                $criteria['condition'] = json_decode($criteria['condition'], true);
            }
            $condition = is_array($criteria['condition']) ? array_filter($criteria['condition']) : $criteria['condition'];
            $srch->addConditionCondition($condition, false, $productType);
        }

        if (array_key_exists('out_of_stock', $criteria)) {
            if (!empty($criteria['out_of_stock']) && $criteria['out_of_stock'] == 1) {
                $srch->excludeOutOfStockProducts();
            }
        }

        $minPriceRange = '';
        if (array_key_exists('price-min-range', $criteria)) {
            $minPriceRange = floor($criteria['price-min-range']);
        } elseif (array_key_exists('min_price_range', $criteria)) {
            $minPriceRange = floor($criteria['min_price_range']);
        }
        //currency_id
        if (!empty($minPriceRange)) {
            $currCurrencyId = isset($criteria['currency_id']) ? $criteria['currency_id'] : FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
            
            $min_price_range_default_currency = CommonHelper::convertExistingToOtherCurrency($currCurrencyId, $minPriceRange, FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1), false);
            
            $srch->addHaving('theprice', '>=', $min_price_range_default_currency);
            
        }

        $maxPriceRange = '';
        if (array_key_exists('price-max-range', $criteria)) {
            $maxPriceRange = ceil($criteria['price-max-range']);
        } elseif (array_key_exists('max_price_range', $criteria)) {
            $maxPriceRange = ceil($criteria['max_price_range']);
        }

        if (!empty($maxPriceRange)) {
            $currCurrencyId = isset($criteria['currency_id']) ? $criteria['currency_id'] : FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
            $max_price_range_default_currency = CommonHelper::convertExistingToOtherCurrency($currCurrencyId, $maxPriceRange, FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1), false);
            $srch->addHaving('theprice', '<=', $max_price_range_default_currency);
        }

        if (array_key_exists('featured', $criteria)) {
            $featured = FatUtility::int($criteria['featured']);
            if (0 < $featured) {
                $srch->addCondition('product_featured', '=', $featured);
            }
        }
        if (array_key_exists('sortBy', $criteria)) {
            $sortBy = $criteria['sortBy'];
        }

        $sortOrder = 'asc';
        if (array_key_exists('sortOrder', $criteria)) {
            $sortOrder = $criteria['sortOrder'];
        }

        if (!empty($sortBy)) {
            $sortByArr = explode("_", $sortBy);
            $sortBy = isset($sortByArr[0]) ? $sortByArr[0] : $sortBy;
            $sortOrder = isset($sortByArr[1]) ? $sortByArr[1] : $sortOrder;

            if (!in_array($sortOrder, array('asc', 'desc'))) {
                $sortOrder = 'asc';
            }

            if (!in_array($sortBy, array('keyword', 'price', 'popularity', 'rating', 'discounted'))) {
                $sortOrder = 'keyword_relevancy';
            }

            switch ($sortBy) {
                case 'keyword':
                    /* if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
                      $srch->addOrder('availableInLocation', 'DESC');
                      } */
                    $srch->addOrder('keyword_relevancy', 'DESC');
                    break;
                case 'price':
                    $srch->addOrder('theprice', $sortOrder);
                    break;
                case 'popularity':
                    $soldKey = 'sp.selprod_rent_count';
                    if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                        $soldKey = 'sp.selprod_sold_count';
                    }
                    $srch->addOrder($soldKey, $sortOrder); 
                    break;
                case 'discounted':
                    $priceKey = 'spd.sprodata_rental_price';
                    if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
                        $priceKey = 'sp.selprod_price';
                    }
                    $srch->addFld('ROUND(((selprod_price - (COALESCE(tsp.splprice_price, '. $priceKey .')))*100)/'. $priceKey .') as discountedValue');
                    
                    $srch->addOrder('discountedValue', 'DESC');
                    break;
                case 'rating':
                    /* $srch->addOrder('prod_rating', $sortOrder); */
                    $srch->addOrder('selprod_avg_rating', $sortOrder);
                    break;
                default:
                    $srch->addOrder('keyword_relevancy', 'DESC');
                    break;
            }
        }

        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        
        if (!empty($criteria['producttype']) && in_array(Product::PRODUCT_FOR_RENT, $criteria['producttype'])) {
            $srch->addCondition('sprodata_rental_stock', '>', 0);
        }

        if ($mainQuery) {
            $srch->addGroupBy('product_id');
            if (!empty($keyword)) {
                /* $srch->addGroupBy('keywordmatched');  */
                $srch->addOrder('keywordmatched', 'desc');
            }
        }
        return $srch;
    }

    public static function getActiveCount($sellerId, $prodId = 0)
    {
        if (0 > FatUtility::int($sellerId)) {
            // $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $prodId = FatUtility::int($prodId);

        $srch = new SearchBase(static::DB_TBL);

        $srch->addCondition(static::DB_TBL_PREFIX . 'seller_id', '=', $sellerId);

        $srch->addMultipleFields(array(static::DB_TBL_PREFIX . 'id'));
        $srch->addCondition(static::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', applicationConstants::NO);
        $srch->addCondition(static::DB_TBL_PREFIX . 'approved', '=', applicationConstants::YES);
        if ($prodId) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'id', '!=', $prodId);
        }
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        return $srch->recordCount();
    }

    public static function isShippedBySeller($selprodUserId = 0, $productSellerId = 0, $shippedBySellerId = false)
    {
        $productSellerId = FatUtility::int($productSellerId);
        $selprodUserId = FatUtility::int($selprodUserId);
        if(FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY',FatUtility::VAR_INT,0)){
          return false;
        }

        if ($productSellerId > 0 && $selprodUserId == $productSellerId) {
            /* Catalog-Product Added By Seller so also shipped by seller */
            return $selprodUserId;
        } else {
            $shippedBySellerId = FatUtility::int($shippedBySellerId);
            if ($shippedBySellerId > 0 && $selprodUserId == $shippedBySellerId) {
                return $shippedBySellerId;
            }
        }
        return false;
    }

    public static function updateMinPrices($productId = 0, $shopId = 0, $brandId = 0)
    {
        $criteria = array();
        $shopId = FatUtility::int($shopId);
        $brandId = FatUtility::int($brandId);
        $productId = FatUtility::int($productId);

        if (0 < $shopId) {
            $criteria = array('shop_id' => $shopId);
        }/* else {
          $shop = Shop::getAttributesByUserId($sellerId);
          if (!empty($shop) && array_key_exists('shop_id', $shop)) {
          $criteria = array('shop_id'=>$shop['shop_id'] );
          }
          } */

        if (0 < $brandId) {
            $criteria = array('brand_id' => $brandId);
        }

        $criteria = array('max_price' => true);

        $srch = new ProductSearch();
        $srch->setDefinedCriteria(1, 0, $criteria, true, false);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_available_from', '<=', FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'));
        $srch->addMultipleFields(array('product_id', 'selprod_id', 'theprice', 'IFNULL(splprice_id, 0) as splprice_id', applicationConstants::PRODUCT_FOR_SALE . ' as priceType'));
        $srch->doNotLimitRecords();
        $srch->addCondition('selprod_active', '=', applicationConstants::ACTIVE);
        $srch->doNotCalculateRecords();
        $srch->addGroupBy('product_id');
        if (!empty($shop) && array_key_exists('shop_id', $shop)) {
            $srch->addCondition('shop_id', '=', $shop['shop_id']);
        }

        if (0 < $productId) {
            $srch->addCondition('product_id', '=', $productId);
        }

        $tmpQry = $srch->getQuery();

        $qry = "INSERT INTO " . static::DB_PRODUCT_MIN_PRICE . " (pmp_product_id, pmp_selprod_id, pmp_min_price, pmp_splprice_id, pmp_price_type) SELECT * FROM (" . $tmpQry . ") AS t ON DUPLICATE KEY UPDATE pmp_selprod_id = t.selprod_id, pmp_min_price = t.theprice, pmp_splprice_id = t.splprice_id, pmp_price_type = t.priceType";

        FatApp::getDb()->query($qry);
        $query = "DELETE m FROM " . static::DB_PRODUCT_MIN_PRICE . " m LEFT OUTER JOIN (" . $tmpQry . ") ON pmp_product_id = selprod_product_id AND pmp_price_type= " . applicationConstants::PRODUCT_FOR_SALE . " WHERE m.pmp_product_id IS NULL";
        FatApp::getDb()->query($query);

        /* [ UPDATES FOR RENTAL PRICE */
        $srch = new ProductSearch();
        $srch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'msellprod.selprod_product_id = p.product_id and selprod_deleted = ' . applicationConstants::NO, 'msellprod');
        $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'msellprod.selprod_id = spd.sprodata_selprod_id ', 'spd');
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinBrands();
        $srch->joinShippingPackages();
        $srch->addCondition('spd.sprodata_rental_stock', '>', 0);
        $srch->addCondition('spd.sprodata_rental_active', '=', applicationConstants::ACTIVE);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription(0, false, true);
        $srch->addSubscriptionValidCondition();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('sprodata_rental_available_from', '<=', FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'));

        $priceSrch = ProductSearch::getSeachObjForRentMinPrice();
        $priceSrch->addFld(['MIN(sprodata_rental_price) as minPrice']);
        $priceSrch->addGroupBy('product_id');
        if (0 < $productId) {
            $priceSrch->addCondition('product_id', '=', $productId);
        }
        if (!empty($shop) && array_key_exists('shop_id', $shop)) {
            $priceSrch->addCondition('shop_id', '=', $shop['shop_id']);
        }
        $priceSrch->addDirectCondition('rmsellprod.selprod_product_id = msellprod.selprod_product_id');
        $priceQuery = $priceSrch->getQuery();
        $srch->addMultipleFields(array('product_id', 'sprodata_selprod_id as selprod_id', 'sprodata_rental_price as theprice', '0 as splprice_id', applicationConstants::PRODUCT_FOR_RENT . ' as priceType', '(' . $priceQuery . ') as minPrice'));
        $cnd = $srch->addHaving('theprice', '!=', '', 'AND');
        $cnd->setDirectString('theprice = minPrice');
        /* $priceSrch->addDirectCondition('theprice = ('. $priceQuery .')');  */

        $srch->doNotLimitRecords();
        $srch->addOrder('theprice', 'ASC');
        $srch->doNotCalculateRecords();

        if (!empty($shop) && array_key_exists('shop_id', $shop)) {
            $srch->addCondition('shop_id', '=', $shop['shop_id']);
        }

        if (0 < $productId) {
            $srch->addCondition('product_id', '=', $productId);
        }

        $tmpQry = $srch->getQuery();
        $qry = "INSERT INTO " . static::DB_PRODUCT_MIN_PRICE . " (pmp_product_id, pmp_selprod_id, pmp_min_price, pmp_splprice_id, pmp_price_type, pmp_max_price) SELECT * FROM (" . $tmpQry . ") AS t ON DUPLICATE KEY UPDATE pmp_selprod_id = t.selprod_id, pmp_min_price = t.theprice, pmp_splprice_id = t.splprice_id, pmp_price_type = t.priceType, pmp_max_price = t.minPrice";

        FatApp::getDb()->query($qry);
        $query = "DELETE m FROM " . static::DB_PRODUCT_MIN_PRICE . " m LEFT OUTER JOIN (" . $tmpQry . ") ON pmp_product_id = selprod_product_id AND pmp_price_type= " . applicationConstants::PRODUCT_FOR_RENT . "  WHERE m.pmp_product_id IS NULL";
        FatApp::getDb()->query($query);
        /* ] */
    }

    public static function getProductsCount()
    {
        $srch = static::getSearchObject();
        $srch->addFld('COUNT(' . static::DB_TBL_PREFIX . 'id) as total_products');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public function saveProductData($data)
    {
        if (empty($data)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        unset($data['product_id']);
        if ($this->mainTableRecordId < 1) {
            $data['product_added_on'] = 'mysql_func_now()';
            $data['product_added_by_admin_id'] = isset($data['product_added_by_admin_id']) ? $data['product_added_by_admin_id'] : applicationConstants::YES;
        }
        $this->assignValues($data, true);
        if (!$this->save()) {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }

    public function saveProductLangData($langData)
    {
        if ($this->mainTableRecordId < 1 || empty($langData)) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $autoUpdateOtherLangsData = isset($langData['auto_update_other_langs_data']) ? FatUtility::int($langData['auto_update_other_langs_data']) : 0;
        foreach ($langData['product_name'] as $langId => $prodName) {
            if (empty($prodName) && $autoUpdateOtherLangsData > 0) {
                $this->saveTranslatedProductLangData($langId);
            } elseif (!empty($prodName)) {
                $data = array(
                    static::DB_TBL_LANG_PREFIX . 'product_id' => $this->mainTableRecordId,
                    static::DB_TBL_LANG_PREFIX . 'lang_id' => $langId,
                    'product_name' => $prodName,
                    'product_description' => $langData['product_description_' . $langId],
                    'product_youtube_video' => $langData['product_youtube_video'][$langId],
                );
                if (!$this->updateLangData($langId, $data)) {
                    $this->error = $this->getError();
                    return false;
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

    public function getTranslatedProductData($data, $toLangId)
    {
        $toLangId = FatUtility::int($toLangId);
        if (empty($data) || $toLangId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $translateLangobj = new TranslateLangData(static::DB_TBL_LANG);
        $translatedData = $translateLangobj->directTranslate($data, $toLangId);
        if (false === $translatedData) {
            $this->error = $translateLangobj->getError();
            return false;
        }
        return $translatedData;
    }

    public function saveProductCategory($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);
        if ($this->mainTableRecordId < 1 || $categoryId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        FatApp::getDb()->deleteRecords(static::DB_TBL_PRODUCT_TO_CATEGORY, array('smt' => static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id = ?', 'vals' => array($this->mainTableRecordId)));

        $record = new TableRecord(static::DB_TBL_PRODUCT_TO_CATEGORY);
        $data = array(
            static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id' => $this->mainTableRecordId,
            static::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id' => $categoryId
        );
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            $this->error = $record->getError();
            return false;
        }
        return true;
    }

    public function saveProductTax($taxId, $userId = 0, $type = SellerProduct::PRODUCT_TYPE_PRODUCT, $taxCatIdRent = 0)
    {
        if ($this->mainTableRecordId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        if(!empty($taxId)) {
            $taxId = FatUtility::int($taxId);
            if ($taxId < 1) {
                $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
                return false;
            }
        }else {
            $taxId = 0;
        }

        if (0 >= $type) {
            $type = SellerProduct::PRODUCT_TYPE_PRODUCT;
        }

        $data = array(
            'ptt_product_id' => $this->mainTableRecordId,
            'ptt_taxcat_id' => $taxId,
            'ptt_taxcat_id_rent' => $taxCatIdRent,
            'ptt_seller_user_id' => $userId,
            'ptt_type' => $type,
        );
        $tax = new Tax();
        if ($userId > 0) {
            $tax->removeTaxSetByAdmin($this->mainTableRecordId);
        }
        if (!$tax->addUpdateProductTaxCat($data)) {
            $this->error = $tax->getError();
            return false;
        }
        return true;
    }

    public static function getCatalogProductCount($productId)
    {
        $productId = FatUtility::int($productId);
        $srch = SellerProduct::getSearchObject();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->addCondition('selprod_deleted', '=', 0);
        $srch->addCondition('selprod_product_id', '=', $productId);
        $srch->addFld('selprod_id');
        $rs = $srch->getResultSet();
        return $srch->recordCount();
    }

    public function saveProductSpecifications($prodSpecId, $langId, $prodSpecName, $prodSpecValue, $prodSpecGroup, int $isFile = 0, bool $fileUpload = false, $isAutoCompleteData = 0, array $post = [])
    {
        $prodSpecId = FatUtility::int($prodSpecId);
        $langId = FatUtility::int($langId);
        if ($langId < 1 || empty($prodSpecName) || (empty($prodSpecValue) && $fileUpload == false) || ($prodSpecId < 1 && $this->mainTableRecordId < 1)) {
            $this->error = Labels::getLabel('ERR_Please_fill_product_speicification_text_and_value', $this->commonLangId);
            return false;
        }

        $prodSpec = new ProdSpecification($prodSpecId);
        if ($prodSpecId < 1) {
            $data['prodspec_product_id'] = $this->mainTableRecordId;
        }

        $data['prodspec_identifier'] = $post['prodspec_identifier'];
        $data['prodspec_is_file'] = $isFile;
        $prodSpec->assignValues($data);
        if (!$prodSpec->save()) {
            $this->error = $prodSpec->getError();
            return false;
        }
        $prodSpecId = $prodSpec->getMainTableRecordId();

        $prodSpec = new ProdSpecification($prodSpecId);
        $langData = array(
            'prodspeclang_prodspec_id' => $prodSpecId,
            'prodspeclang_lang_id' => $langId,
            'prodspec_name' => $prodSpecName,
            'prodspec_value' => $prodSpecValue,
            'prodspec_group' => $prodSpecGroup,
        );
        if (!$prodSpec->updateLangData($langId, $langData)) {
            $this->error = $prodSpec->getError();
            return false;
        }

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        /* if ($isAutoCompleteData && $langId == $siteLangId) { */
        $prodspecNames = (!empty($post) && isset($post['prodspec_name'])) ? $post['prodspec_name'] : [];
        $prodspecValues = (!empty($post) && isset($post['prodspec_value'])) ? $post['prodspec_value'] : [];
        $languages = Language::getAllNames();
        unset($languages[$siteLangId]);
        if (!empty($prodspecNames)) {
            foreach ($languages as $langToTranId => $langName) {
                if (isset($prodspecNames[$langToTranId]) && trim($prodspecNames[$langToTranId]) != '') {
                    $langData = array(
                        'prodspeclang_prodspec_id' => $prodSpecId,
                        'prodspeclang_lang_id' => $langToTranId,
                        'prodspec_name' => $prodspecNames[$langToTranId],
                        'prodspec_value' => (isset($prodspecValues[$langToTranId])) ? $prodspecValues[$langToTranId] : "",
                        'prodspec_group' => $prodSpecGroup,
                    );
                    if (!$prodSpec->updateLangData($langToTranId, $langData)) {
                        $this->error = $prodSpec->getError();
                        return false;
                    }
                } else if ($isAutoCompleteData) {
                    if (!$prodSpec->saveTranslateProdSpecLangData($langToTranId)) {
                        $this->error = $prodSpec->getError();
                        return false;
                    }
                }
            }
        }
        /* } */

        if ($fileUpload == true) {
            return $prodSpecId;
        }

        return true;
    }

    public function getProdSpecificationsByLangId($langId, $isFile = false)
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $srch = new SearchBase(static::DB_PRODUCT_SPECIFICATION);
        $srch->joinTable(static::DB_PRODUCT_LANG_SPECIFICATION, 'LEFT OUTER JOIN', static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'prodspec_id = ' . static::DB_PRODUCT_SPECIFICATION_PREFIX . 'id AND ' . static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'lang_id = ' . $langId);
        $srch->addCondition(static::DB_PRODUCT_SPECIFICATION_PREFIX . 'product_id', '=', $this->mainTableRecordId);
        /*$srch->addCondition(static::DB_PRODUCT_LANG_SPECIFICATION_PREFIX . 'lang_id', '=', $langId);*/
        $srch->addMultipleFields(
                array(
                    static::DB_PRODUCT_SPECIFICATION_PREFIX . 'id',
                    'IFNULL(' . static::DB_PRODUCT_SPECIFICATION_PREFIX . 'name, ' . static::DB_PRODUCT_SPECIFICATION_PREFIX . 'identifier) as prodspec_name',
                    /*static::DB_PRODUCT_SPECIFICATION_PREFIX . 'name',*/
                    static::DB_PRODUCT_SPECIFICATION_PREFIX . 'value',
                    static::DB_PRODUCT_SPECIFICATION_PREFIX . 'group'
                )
        );
        if ($isFile == false) {
            $cnd = $srch->addCondition(static::DB_PRODUCT_SPECIFICATION_PREFIX . 'is_file', '=', 0);
        } else {
            $cnd = $srch->addCondition(static::DB_PRODUCT_SPECIFICATION_PREFIX . 'is_file', '=', 1);
        }

        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function saveProductSellerShipping($prodSellerId, $psFree, $psCountryId)
    {
        if ($this->mainTableRecordId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }
        $prodSellerShip = array(
            'ps_product_id' => $this->mainTableRecordId,
            'ps_user_id' => $prodSellerId,
            'ps_free' => $psFree,
            'ps_from_country_id' => $psCountryId
        );
        if (!FatApp::getDb()->insertFromArray(Product::DB_TBL_PRODUCT_SHIPPING, $prodSellerShip, false, array(), $prodSellerShip)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getProductSpecificsDetails($productId)
    {
        $productId = FatUtility::int($productId);
        if ($productId < 1) {
            return false;
        }
        $srch = new SearchBase(ProductSpecifics::DB_TBL);
        $srch->addCondition(ProductSpecifics::DB_TBL_PREFIX . 'product_id', '=', $productId);
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public static function isShipFromConfigured($productId, $userId = 0)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);

        $srch = new SearchBase(static::DB_TBL_PRODUCT_SHIPPING, 'ps');
        $srch->addCondition('ps_product_id', '=', $productId);
        $srch->addCondition('ps_user_id', '=', $userId);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $res = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($res)) {
            return true;
        }
        return false;
    }

    public function updateUpdatedOn()
    {
        $productId = FatUtility::int($this->mainTableRecordId);
        FatApp::getDb()->updateFromArray('tbl_products', array('product_updated_on' => date('Y-m-d H:i:s')), array('smt' => 'product_id = ?', 'vals' => array($productId)));
    }

    /**
     * setProductFulfillmentType - Need to enhance later.
     *
     * @param  int $productId
     * @param  int $loggedUserId
     * @param  int $fulfillmentType
     * @return int
     */
    public static function setProductFulfillmentType(int $productId, int $loggedUserId, int $fulfillmentType): int
    {
        return $fulfillmentType;
    }

    public function formatAttributesData($prodCatAttr)
    {
        $response = array();
        foreach ($prodCatAttr as $attr) {
            $response[$attr['attr_id']][$attr['attrlang_lang_id']] = $attr;
        }
        return $response;
    }

    public function getProdCatCustomFieldsForm(array $attributes, int $langId, bool $isCustomRequestForm = false, array $attrList = [])
    {
        $languages = Language::getAllNames();
        $frm = new Form('prodCatCustomFieldForm', array('id' => 'prodCatCustomFieldForm'));

        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                if (!isset($attr[$langId])) {
                    $attr[$langId] = current($attr);
                    $attr[$langId]['attrlang_lang_id'] = $langId;
                }
                $caption = ($attr[$langId]['attr_name'] != '') ? $attr[$langId]['attr_name'] : $attr[$langId]['attr_identifier'];
                switch ($attr[$langId]['attr_type']) {
                    case AttrGroupAttribute::ATTRTYPE_NUMBER:
                        $selectedValue = '';
                        if (!empty($attrList) && isset($attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']])) {
                            $selectedValue = $attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']];
                        }
                        $selectedValue = (is_array($selectedValue)) ? "" : $selectedValue;
                        $fld = $frm->addIntegerField($caption, 'num_attributes[' . $attr[$langId]['attr_attrgrp_id'] . '][' . $attr[$langId]['attr_fld_name'] . ']', $selectedValue);
                        $fld->requirements()->setRange(0, 99999999999);
                        /* $fld->requirements()->setFloat(); */
                        break;
                    case AttrGroupAttribute::ATTRTYPE_DECIMAL:
                        $selectedValue = '';
                        if (!empty($attrList) && isset($attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']])) {
                            $selectedValue = $attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']];
                        }
                        $selectedValue = (is_array($selectedValue)) ? "" : $selectedValue;
                        $fld = $frm->addFloatField($caption, 'num_attributes[' . $attr[$langId]['attr_attrgrp_id'] . '][' . $attr[$langId]['attr_fld_name'] . ']', $selectedValue);
                        $fld->requirements()->setRange(0, 99999999999);
                        /* $fld->requirements()->setFloat(); */
                        break;
                    case AttrGroupAttribute::ATTRTYPE_SELECT_BOX:
                        $arr_options = array();

                        $selectedoptions = [];
                        if (!empty($attrList) && isset($attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']])) {
                            $selectedValue = $attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']];
                            if (is_array($selectedValue)) {
                                $selectedoptions = $selectedValue;
                            } else {
                                if (trim($selectedValue) != '') {
                                    $selectedoptions = explode(',', $selectedValue);
                                }
                            }
                        }

                        if ($attr[$langId]['attr_options'] != '') {
                            $arr_options = explode("\n", $attr[$langId]['attr_options']);
                            if (is_array($arr_options)) {
                                $arr_options = array_map('trim', $arr_options);
                            }
                        }

                        $arr_options = array_filter($arr_options);
                        $fld = $frm->addSelectBox($caption, 'num_attributes[' . $attr[$langId]['attr_attrgrp_id'] . '][' . $attr[$langId]['attr_fld_name'] . ']', $arr_options, $selectedoptions, array(), '');
                        break;
                    case AttrGroupAttribute::ATTRTYPE_CHECKBOXES:
                        $arr_options = array();

                        $selectedoptions = [];
                        if (!empty($attrList) && isset($attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']])) {
                            $selectedValue = $attrList['num_attributes'][$attr[$langId]['attr_attrgrp_id']][$attr[$langId]['attr_fld_name']];
                            if (is_array($selectedValue)) {
                                $selectedoptions = $selectedValue;
                            } else {
                                if (trim($selectedValue) != '') {
                                    $selectedoptions = explode(',', $selectedValue);
                                }
                            }
                        }

                        if ($attr[$langId]['attr_options'] != '') {
                            $arr_options = explode("\n", $attr[$langId]['attr_options']);
                            if (is_array($arr_options)) {
                                $arr_options = array_map('trim', $arr_options);
                            }
                        }

                        $arr_options = array_filter($arr_options);
                        $fld = $frm->addCheckBoxes($caption, 'num_attributes[' . $attr[$langId]['attr_attrgrp_id'] . '][' . $attr[$langId]['attr_fld_name'] . ']', $arr_options, $selectedoptions, array('class' => 'list-inline list-inline-fluid'));
                        break;


                    case AttrGroupAttribute::ATTRTYPE_TEXT:
                        foreach ($languages as $key => $language) {
                            $selectedValue = '';
                            if (!empty($attrList) && isset($attrList['text_attributes'][$attr[$langId]['attr_attrgrp_id']][$key][$attr[$langId]['attr_fld_name']])) {
                                $selectedValue = $attrList['text_attributes'][$attr[$langId]['attr_attrgrp_id']][$key][$attr[$langId]['attr_fld_name']];
                            }
                            $selectedValue = (is_array($selectedValue)) ? "" : $selectedValue;
                            $fld = $frm->addTextBox($caption, 'text_attributes[' . $attr[$langId]['attr_attrgrp_id'] . '][' . $key . '][' . $attr[$langId]['attr_fld_name'] . ']', $selectedValue);

                            //$postfix_hint = Labels::getLabel('LBL_Enter_N.A._if_value_not_required.',$key);
                            $postfix_hint = '';
                            if (isset($attr[$key]['attr_postfix']) && $attr[$key]['attr_postfix'] != '') {
                                $postfix_hint .= '<small>(' . sprintf(Labels::getLabel('LBL_Will_Display_as_:_%s_value_', $key), $caption) . $attr[$key]['attr_postfix'] . ')</small>';
                            }

                            $fld->htmlAfterField = $postfix_hint;
                        }
                        break;
                }
                if ($attr[$langId]['attr_type'] != AttrGroupAttribute::ATTRTYPE_TEXT) {
                    if ($attr[$langId]['attr_postfix'] != '') {
                        //$fld->htmlAfterField = '<small>' . $attr[$langId]['attr_postfix'] . ')</small>';
                        $fld->htmlAfterField = '<small>(' . sprintf(Labels::getLabel('LBL_Will_Display_as_:_%s_value_', $langId), $caption) . $attr[$langId]['attr_postfix'] . ')</small>';
                    }
                }
            }
        }
        $frm->addHiddenField('', 'product_id', $attrList['product_id']);
        $frm->addHiddenField('', 'preq_id', $attrList['product_id']);
        $frm->addButton('', 'btn_back', Labels::getLabel('LBL_Back', $langId));
        $label = Labels::getLabel('LBL_Save_And_Next', $langId);
        if ($isCustomRequestForm) {
            $label = Labels::getLabel('LBL_Save_Changes', $langId);
        }
        $frm->addSubmitButton('', 'btn_submit', $label);
        return $frm;
    }

    public function checkOptionWithSizeChart(): bool
    {
        $srch = new SearchBase(Product::DB_PRODUCT_TO_OPTION);
        $srch->addCondition(Product::DB_PRODUCT_TO_OPTION_PREFIX . 'product_id', '=', $this->getMainTableRecordId());
        $srch->joinTable(Option::DB_TBL, 'INNER JOIN', Option::DB_TBL_PREFIX . 'id = ' . Product::DB_PRODUCT_TO_OPTION_PREFIX . 'option_id');
        $srch->addCondition(Option::DB_TBL_PREFIX . 'attach_sizechart', '=', applicationConstants::YES);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (empty($row)) {
            return false;
        }
        return true;
    }

    public function deleteProductSpecFile(int $fileType, int $productId, int $fileId, bool $saveToTemp = false): bool
    {
        if ($productId < 1 || $fileId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        if ($saveToTemp) {
            if (!FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL_TEMP, ['smt' => 'afile_id = ?', 'vals' => array($fileId)])) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        } else {
            $fileHandlerObj = new AttachedFile();
            if (!$fileHandlerObj->deleteFile($fileType, $productId, $fileId)) {
                $this->error = $fileHandlerObj->getError();
                return false;
            }
        }
        return true;
    }

    public static function getProductType($productId)
    {
        $productId = FatUtility::int($productId);
        $srch = self::getSearchObject();
        $srch->addCondition('tp.product_id', '=', $productId);
        $srch->addFld('product_type');
        $rs = $srch->getResultSet();
        $productData = FatApp::getDb()->fetch($rs);
        if (empty($productData)) {
            return false;
        }
        return $productData['product_type'];
    }

    public static function getProductShippingRatesByAddress(int $selprodId, array $buyerAddress = []): array
    {
        $srch = new ProductSearch();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->setDefinedCriteria(0, 0, array(), false);
        $srch->joinProductShippedBy();
        $srch->joinShippingProfileProducts(applicationConstants::PRODUCT_FOR_RENT);
        $srch->joinShippingProfile();
        $srch->joinShippingProfileZones();
        $srch->joinShippingZones();
        $srch->joinShippingRates();
        
        if (empty($buyerAddress)) {
            $buyerAddress = $userAddress = Address::getYkGeoData();
        }
        $defaultCountryCode = FatApp::getConfig('CONF_GEO_DEFAULT_COUNTRY', FatUtility::VAR_STRING, '');
        $defaultStateCode = FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_STRING, '');
        $defaultCountryId = -1;
        $defaultStateId = -1;
        if (trim($defaultCountryCode) != '') {
            $defaultCountryId = Countries::getCountryByCode($defaultCountryCode, 'country_id');
        }
        
        if (trim($defaultStateCode) != '' && $defaultCountryId > 0) {
            $stateDetail = States::getStateByCountryAndCode($defaultCountryId, $defaultStateCode);
            if (!empty($stateDetail)) {
                $defaultStateId = $stateDetail['state_id'];
            }
        }
        
        $countryId = ((isset($buyerAddress['ykGeoCountryId'])) && $buyerAddress['ykGeoCountryId']) ? $buyerAddress['ykGeoCountryId'] : $defaultCountryId;
        $stateId = (isset($buyerAddress['ykGeoStateId']) && $buyerAddress['ykGeoStateId']) ? $buyerAddress['ykGeoStateId'] : $defaultStateId;
        
        $countryId = ($countryId == 0) ? -1 : $countryId;
        $stateId = ($stateId == 0) ? -1 : $stateId;
        
        $srch->joinShippingLocations($countryId, $stateId, 0);
        $srch->addCondition('selprod_id', '=', $selprodId);
        $srch->addMultipleFields(array('IFNULL(shiprate_min_duration, 0) as minimum_shipping_duration'));
        $srch->addCondition('shiprate_id', '!=', 'null');
        $srch->addOrder('shiploc_country_id', 'DESC');
        $srch->addOrder('shiploc_state_id', 'DESC');
        $srch->addOrder('shiprate_min_duration', 'ASC');
        $prodSrchRs = $srch->getResultSet();
        $row = (array) FatApp::getDb()->fetch($prodSrchRs);
        
        return $row;
    }

    public static function getCustomFields(array $catalogIds, int $langId): array
    {
        $numericAttributes = Product::getProductNumericAttributes($catalogIds);
        $textualAttributes = Product::getProductTextualAttributes($catalogIds, $langId);
        $groupedArr = [];

        if (empty($infoAttributes) && empty($textualAttributes)) {
            $groupedArr = [];
        }
        $infoAttributes = [];
        if (!empty($numericAttributes)) {
            foreach ($numericAttributes as $numericAttribute) {
                $catalogId = $numericAttribute['prodnumattr_product_id'];
                $key = $numericAttribute['prodnumattr_attrgrp_id'];
                $infoAttributes[$catalogId][$key] = $numericAttribute;
            }
        }
        if (!empty($textualAttributes)) {
            foreach ($textualAttributes as $textualAttribute) {
                $catalogId = $textualAttribute['prodtxtattr_product_id'];
                $key = $textualAttribute['prodtxtattr_attrgrp_id'];
                if (!empty($infoAttributes[$catalogId][$key])) {
                    $groupedArr[$catalogId][$key] = $infoAttributes[$catalogId][$key] + $textualAttribute;
                    unset($infoAttributes[$catalogId][$key]);
                    if (empty($infoAttributes[$catalogId])) {
                        unset($infoAttributes[$catalogId]);
                    }
                } else {
                    $groupedArr[$catalogId][$key] = $textualAttribute;
                }
            }
        }
        $groupedArr = $infoAttributes + $groupedArr;
        return $groupedArr;
    }

    public static function formatArrByCatId(array $prodCatAttributes): array
    {
        if (empty($prodCatAttributes)) {
            return [];
        }
        $groupedArr = [];
        foreach ($prodCatAttributes as $attribute) {
            $groupedArr[$attribute['attr_prodcat_id']][$attribute['attr_attrgrp_id']][] = $attribute;
        }
        return $groupedArr;
    }

    public static function getMoreSeller($selprodCode, int $langId, int $userId = 0)
    {
        $moreSellerSrch = new ProductSearch($langId);
        $moreSellerSrch->setGeoAddress();
        $moreSellerSrch->addMoreSellerCriteria($selprodCode, $userId);
        $moreSellerSrch->validateAndJoinDeliveryLocation();
        
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');
        $moreSellerSrch->addMultipleFields(
                array('selprod_id', 'selprod_user_id', 'selprod_price', 'theprice', 'shop_id', 'shop_name', 'product_seller_id',
                    'product_id', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city', 'selprod_cod_enabled',
                    'product_cod_enabled', 'IF((selprod_stock > 0 OR sprodata_rental_stock > 0), 1, 0) AS in_stock', 'selprod_min_order_qty', 'selprod_available_from',
                    'sprodata_rental_price', 'sprodata_duration_type', 'sprodata_rental_available_from', 'sprodata_rental_active', 'sprodata_rental_stock', 'selprod_active',
                    '0 as rating', '0 as total_reviews', 'product_updated_on', 'selprod_code', 'shop_lat', 'shop_lng', 'COALESCE(selprod_title  ,COALESCE(product_name, product_identifier)) as selprod_title',
                    'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'sprodata_rental_price as rent_price', 'selprod_stock'
                )
        );
        
        //echo $moreSellerSrch->getQuery(); die();
        
       
        $moreSellerSrch->addHaving('in_stock', '>', 0);
        $moreSellerSrch->addOrder('theprice');
        $moreSellerSrch->addGroupBy('selprod_id');
        $moreSellerRs = $moreSellerSrch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($moreSellerRs);

        
        if (empty($rows)) {
            return [];
        }
        $sellerIds = array_column($rows, 'selprod_user_id');
        $ratingArr = SelProdRating::getSellerRatingByIds($sellerIds);
        if (!empty($ratingArr)) {
            foreach ($rows as $index => $row) {
                $rows[$index]['rating'] = (isset($ratingArr[$row['selprod_user_id']])) ? round($ratingArr[$row['selprod_user_id']]['avg_rating'], 1) : 0;

                $rows[$index]['total_reviews'] = (isset($ratingArr[$row['selprod_user_id']])) ? $ratingArr[$row['selprod_user_id']]['total_reviews'] : 0;
            }
        }

        return $rows;
    }

}
