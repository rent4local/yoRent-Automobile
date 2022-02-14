<?php

class Brand extends MyAppModel
{
    public const DB_TBL = 'tbl_brands';
    public const DB_TBL_LANG = 'tbl_brands_lang';
    public const DB_TBL_PREFIX = 'brand_';
    public const DB_TBL_LANG_PREFIX = 'brandlang_';

    public const BRAND_REQUEST_PENDING = 0;
    public const BRAND_REQUEST_APPROVED = 1;
    public const BRAND_REQUEST_CANCELLED = 2;

    public const REWRITE_URL_PREFIX = 'brands/view/';
    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId = 0, $isDeleted = true, $isActive = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'b');

        if ($isDeleted == true) {
            $srch->addCondition('b.' . static::DB_TBL_PREFIX . 'deleted', '=', 0);
        }
        if ($isActive == true) {
            $srch->addCondition('b.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'b_l.' . static::DB_TBL_LANG_PREFIX . 'brand_id = b.' . static::tblFld('id') . ' and
			b_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'b_l'
            );
        }

        $srch->addOrder('b.' . static::DB_TBL_PREFIX . 'active', 'DESC');
        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'brand_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'brand_identifier',
                'brand_name',
            )
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredMediaFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'brand_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'brand_identifier',
                'afile_physical_path',
                'afile_name',
                'afile_type',
            )
        );
    }

    public static function validateMediaFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredMediaFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function getListingObj($langId, $attr = null, $isActive = false)
    {
        $srch = self::getSearchObject($langId, true, $isActive);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
                'IFNULL(b_l.brand_name,b.brand_identifier) as brand_name'
            )
        );

        return $srch;
    }

    public static function getAllIdentifierAssoc(int $langId = 0, bool $isDeleted = true, bool $isActive = false): array
    {
        $langId = FatUtility::int($langId);
        $srch = self::getSearchObject($langId, true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array(static::tblFld('id'), static::tblFld('identifier')));
        return FatApp::getDb()->fetchAllAssoc($srch->getResultSet());
    }

    public function canRecordMarkDelete($id)
    {
        $srch = $this->getSearchObject();
        $srch->addCondition('b.' . static::DB_TBL_PREFIX . 'id', '=', $id);
        $srch->addFld('b.' . static::DB_TBL_PREFIX . 'id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX . 'id'] == $id) {
            return true;
        }
        return false;
    }

    /* public function canRecordUpdateStatus($id)
    {
        $srch = $this->getSearchObject();
        $srch->addCondition('b.' . static::DB_TBL_PREFIX . 'id', '=', $id);
        $srch->addFld('b.' . static::DB_TBL_PREFIX . 'id', 'b.' . static::DB_TBL_PREFIX . 'active');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row[static::DB_TBL_PREFIX . 'id'] == $id) {
            return $row;
        }
        return false;
    }
    */
    public function rewriteUrl(string $keyword)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $originalUrl = Brand::REWRITE_URL_PREFIX . $this->mainTableRecordId;

        $seoUrl = CommonHelper::seoUrl($keyword);

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);

        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public static function recordBrandWeightage($brandId)
    {
        /* $brandId =  FatUtility::int($brandId);

        if(1 > $brandId){ return false;}

        $obj = new SmartUserActivityBrowsing();
        return $obj->addUpdate($brandId,SmartUserActivityBrowsing::TYPE_BRAND); */
    }

    public static function getBrandReqStatusArr(int $langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $arr = array(
            static::BRAND_REQUEST_PENDING => Labels::getLabel('LBL_Pending', $langId),
            static::BRAND_REQUEST_APPROVED => Labels::getLabel('LBL_Approved', $langId),
            static::BRAND_REQUEST_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId)
        );
        return $arr;
    }

    public static function getBrandReqStatusClassArr()
    {
        return array(
            static::BRAND_REQUEST_PENDING => applicationConstants::CLASS_INFO,
            static::BRAND_REQUEST_APPROVED => applicationConstants::CLASS_SUCCESS,
            static::BRAND_REQUEST_CANCELLED => applicationConstants::CLASS_DANGER
        );
    }

    public static function getBrandName(int $brandId, int $langId, bool $isActive = true)
    {
        $srch = static::getListingObj($langId, null, $isActive);
        $srch->addCondition('b.' . static::DB_TBL_PREFIX . 'id', '=', $brandId);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            return $row['brand_name'];
        } else {
            return false;
        }
    }
    
    public static function getRequestCount(array $usersIds) : int
    {
        if (empty($usersIds)) {
            return 0;
        }
        $srch = self::getSearchObject();
        $srch->addCondition('brand_seller_id', 'in', $usersIds);
        $srch->addCondition('brand_deleted', '=', applicationConstants::NO);
        $srch->addFld('count(brand_id) as brandCount');
        $rs = $srch->getResultSet($srch);
        $row = FatApp::getDb()->fetch($rs);
        return $row['brandCount'];
    }
    
}
