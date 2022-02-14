<?php
class LateChargesProfileProduct extends MyAppModel
{
    const DB_TBL = 'tbl_late_charges_profile_to_product';
    const DB_TBL_PREFIX = 'lcptp_';

    public function __construct()
    {
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'lcpprod');
        $srch->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', 'pro.product_id = lcpprod.lcptp_product_id', 'pro');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pro.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . CommonHelper::getLangId(), 'p_l');
        $srch->addMultipleFields(array('product_id', 'IFNULL(product_name, product_identifier) as product_name', 'lcptp_lcp_id as profile_id'));
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        return $srch;
    }

    public function addProduct(array $data)
    {
        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

}
