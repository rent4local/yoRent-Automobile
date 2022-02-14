<?php

class AttrGroupAttribute extends MyAppModel
{

    public const DB_TBL = 'tbl_attribute_group_attributes';
    public const DB_TBL_PREFIX = 'attr_';
    public const DB_TBL_LANG = 'tbl_attribute_group_attributes_lang';
    public const DB_TBL_LANG_PREFIX = 'attrlang_';
    public const ATTRTYPE_NUMBER = 1;
    public const ATTRTYPE_DECIMAL = 2;
    public const ATTRTYPE_SELECT_BOX = 3;
    public const ATTRTYPE_TEXT = 4;
    public const ATTRTYPE_CHECKBOXES = 5;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject(): object
    {
        $srch = new SearchBase(static::DB_TBL, 'attrgrp');
        return $srch;
    }

    public static function getNumericTypeArr(int $langId): array
    {
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        return array(
            static::ATTRTYPE_NUMBER => Labels::getLabel('LBL_Number', $langId),
            static::ATTRTYPE_DECIMAL => Labels::getLabel('LBL_Decimal', $langId),
            static::ATTRTYPE_SELECT_BOX => Labels::getLabel('LBL_(Drop_Down)_Select_Box', $langId),
            static::ATTRTYPE_CHECKBOXES => Labels::getLabel('LBL_Checkboxes', $langId),
        );
    }

    public static function getTextualTypeArr(int $langId): array
    {
        if ($langId == 0) {
            trigger_error(Labels::getLabel('MSG_Language_Id_not_specified.', $langId), E_USER_ERROR);
        }
        return array(
            static::ATTRTYPE_TEXT => Labels::getLabel('LBL_Text', $langId),
        );
    }

    public static function getAllAttributeTypeArr(int $langId): array
    {
        return SELF::getNumericTypeArr($langId) + SELF::getTextualTypeArr($langId);
    }

    public function getDetail(): array
    {
        $srch = self::getSearchObject();
        $srch->addMultipleFields(array('attr_id', 'attr_name', 'attr_attrgrp_id', 'attr_prodcat_id', 'attr_type', 'attrgrp_name', 'attr_postfix', 'attrlang_lang_id', 'attr_options', 'attr_display_in_filter', 'attr_display_in_listing'));
        $srch->joinTable(static::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attrgrp.attr_id = attrlang_attr_id');
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'attrgrp.attr_attrgrp_id = attrgrp_id');
        $srch->joinTable(AttributeGroup::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attrgrp_id = attrgrplang_attrgrp_id AND attrgrplang_lang_id = attrlang_lang_id');
        $srch->addCondition(self::DB_TBL_PREFIX . 'id', '=', $this->mainTableRecordId);
        $rs = $srch->getResultSet();
        return $this->db->fetchAll($rs, 'attrlang_lang_id');
    }

    public function resetLangData(): bool
    {
        if (!$this->db->updateFromArray(SELF::DB_TBL . '_lang', array('attr_options' => ''), array('smt' => 'attrlang_attr_id=?', 'vals' => array($this->mainTableRecordId)))) {
            return false;
        }
        return true;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'attr_id',
            ),
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                /* 'attr_attrgrp_id', */
                'attr_prodcat_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'attr_identifier',
                'attr_type',
                'attr_prodcat_name',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function validateProductCustomFieldsColumns(string $columnIndex, string $columnTitle, string $columnValue, int $langId)
    {
        $requiredFields = static::requiredProductCustomFieldColumns();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

    public static function requiredProductCustomFieldColumns(): array
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'product_id',
                'attr_id',
            )
        );
    }

}
