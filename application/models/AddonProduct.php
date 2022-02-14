<?php

class AddonProduct extends MyAppModel
{

    public const DB_TBL = 'tbl_addon_products';
    public const DB_TBL_LANG = 'tbl_addon_products_lang';
    public const DB_TBL_PREFIX = 'addonprod_';
    public const DB_TBL_LANG_PREFIX = 'addonprodlang_';
    
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function requiredGenDataFields(): array
    {
        $arr = array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'selprod_id',
                'selprod_price',
                'selprod_stock',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'selprod_title',
                'credential_username'
            ),
        );

        return $arr;
    }

    public static function validateGenDataFields(string $columnIndex, string $columnTitle, string $columnValue, int $langId)
    {
        $requiredFields = static::requiredGenDataFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public function getAttachedProductWithAddon(): array
    {
        $srch = new SearchBase(SellerProduct::DB_TBL_SELLER_PROD_ADDON, 'spa');
        $srch->addCondition('spa_addon_product_id', '=', $this->mainTableRecordId);
        $srch->addFld('spa_seller_prod_id');
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetchAll($rs);
        if (!empty($data)) {
            return array_column($data, 'spa_seller_prod_id');
        }
        return [];
    }

}
