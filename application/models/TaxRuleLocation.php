<?php

class TaxRuleLocation extends MyAppModel
{

    const DB_TBL = 'tbl_tax_rule_locations';
    const DB_TBL_PREFIX = 'taxruleloc_';

    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    /**
     * getSearchObject
     *
     * @return object
     */
    public static function getSearchObject(): object
    {
        $srch = new SearchBase(static::DB_TBL, 'taxRuleLoc');
        return $srch;
    }

    /**
     * 
     * @param int $taxCatId
     * @param bool $joinCountryState
     * @param type $langId
     * @return array
     */
    public static function getLocationsByCatId(int $taxCatId = 0, bool $joinCountryState = false, $langId = 0): array
    {
        $srch = TaxRuleLocation::getSearchObject();
        $srch->addCondition('taxruleloc_taxcat_id', '=', $taxCatId);
        if ($joinCountryState) {
            $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_from_state_id = from_st.state_id', 'from_st');
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'from_st.state_id = from_st_l.statelang_state_id AND from_st_l.statelang_lang_id = ' . $langId, 'from_st_l');

            $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_to_country_id = from_c.country_id', 'from_c');
            $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'from_c.country_id = from_c_l.countrylang_country_id AND from_c_l.countrylang_lang_id = ' . $langId, 'from_c_l');

            $srch->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_to_state_id = to_st.state_id', 'to_st');
            $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'to_st.state_id = to_st_l.statelang_state_id AND to_st_l.statelang_lang_id = ' . $langId, 'to_st_l');

            $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'taxruleloc_to_country_id = to_c.country_id', 'to_c');
            $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'to_c.country_id = to_c_l.countrylang_country_id AND to_c_l.countrylang_lang_id = ' . $langId, 'to_c_l');

            $srch->addMultipleFields(array('taxruleloc_taxcat_id', 'taxruleloc_taxrule_id', 'taxruleloc_to_country_id', 'taxruleloc_to_state_id', 'taxruleloc_type','IFNULL(from_c_l.country_name, from_c.country_code) as from_country_name', 'IFNULL(to_c_l.country_name, to_c.country_code) as to_country_name', 'IFNULL(from_st_l.state_name, from_st.state_identifier) as from_state_name', 'IFNULL(to_st_l.state_name, to_st.state_identifier) as to_state_name'));
        }
        return FatApp::getDb()->fetchAll($srch->getResultSet());       
    }

    /**
     * updateLocations
     *
     * @param  array $data
     * @return bool
     */
    public function updateLocations(array $data): bool
    {
        if (0 >= FatUtility::int($data['taxruleloc_taxcat_id']) || 0 >= FatUtility::int($data['taxruleloc_taxrule_id'])) {
            return false;
        }
        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    /**
     * deleteLocations
     *
     * @param  int $taxCatId
     * @return bool
     */
    public function deleteLocations(int $taxRuleId): bool
    {
        if (!FatApp::getDb()->deleteRecords(
                        self::DB_TBL,
                        array(
                            'smt' => self::DB_TBL_PREFIX . 'taxrule_id=? ',
                            'vals' => array($taxRuleId)
                        )
                )) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

}
