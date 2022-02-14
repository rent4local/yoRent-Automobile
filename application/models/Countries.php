<?php

class Countries extends MyAppModel
{
    public const DB_TBL = 'tbl_countries';
    public const DB_TBL_PREFIX = 'country_';

    public const DB_TBL_LANG = 'tbl_countries_lang';
    public const DB_TBL_LANG_PREFIX = 'countrylang_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'c');

        if ($isActive == true) {
            $srch->addCondition('c.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'c_l.' . static::DB_TBL_LANG_PREFIX . 'country_id = c.' . static::tblFld('id') . ' and
			c_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'c_l'
            );
        }

        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'country_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'country_code',
                'country_name',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function getCountriesArr($langId, $isActive = true, $idCol = 'country_id')
    {
        $langId = FatUtility::int($langId);

        $srch = static::getSearchObject($isActive, $langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('country_name', 'ASC');
        $srch->addMultipleFields(
            array(
                $idCol,
                'COALESCE(country_name, country_code) as country_name'
            )
        );

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);

        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public static function getCountryByCode($countryCode, $attr = null)
    {
        if (!$countryCode) {
            return false;
        }

        $srch = static::getSearchObject();
        $srch->addCondition('country_code', '=', strtoupper($countryCode));

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }
    public static function getCountryById($countryId, $langId, $attr = null)
    {
        if (!$countryId) {
            return false;
        }

        $srch = static::getSearchObject(true, $langId, $attr);
        $srch->addCondition('country_id', '=', $countryId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }
    
    public function getCountriesAssocArr($langId, $isActive = true, $idCol = 'country_id')
    {
        $langId = FatUtility::int($langId);

        $srch = $this->searchCountriesObj($langId, $isActive);
        $srch->addMultipleFields(
            array(
                $idCol,
                'COALESCE(country_name, country_code) as country_name'
            )
        );

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);

        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    private function searchCountriesObj(int $langId, bool $isActive = true)
    {
        $srch = static::getSearchObject($isActive, $langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('country_name', 'ASC');
        return $srch;
    }
}
