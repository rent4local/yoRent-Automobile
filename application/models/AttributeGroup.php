<?php

class AttributeGroup extends MyAppModel
{
    public const DB_TBL = 'tbl_attribute_groups';
    public const DB_TBL_PREFIX = 'attrgrp_';
    public const DB_LANG_TBL = 'tbl_attribute_groups_lang';
    public const DB_TBL_LANG_PREFIX = 'attrgrplang_';

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject(int $langId = 0): object
    {
        $srch = new SearchBase(static::DB_TBL, 'ag');
        if ($langId > 0) {
            $srch->joinTable(static::DB_LANG_TBL, 'LEFT OUTER JOIN',
                    'ag_l.' . static::DB_TBL_LANG_PREFIX . 'attrgrp_id = ag.' . static::tblFld('id') . ' and
			ag_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'ag_l');
        }
        return $srch;
    }

    public static function displayTitle(int $grpId, int $langId) : string
    {
        $srch = static::getSearchObject($langId);
        $srch->addCondition('attrgrp_id', '=', $grpId);
        $srch->addFld('IFNULL(attrgrp_name, attrgrp_identifier) as attrgrp_name');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        return (!empty($row)) ? $row['attrgrp_name'] : "";
    }

    public function setup(array $data, int $langId)
    {
        $grpData = array('attrgrp_identifier' => $data[$langId]);
        $this->assignValues($grpData);
        if (!$this->save()) {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }

    public function setupLangData(array $data)
    {
        $languages = Language::getAllNames();
        foreach ($languages as $key => $language) {
            if (empty($data[$key])) {
                continue;
            }

            $dataToUpdate = array(
                'attrgrplang_attrgrp_id' => $this->mainTableRecordId,
                'attrgrplang_lang_id' => $key,
                'attrgrp_name' => $data[$key],
            );

            if (!$this->updateLangData($key, $dataToUpdate)) {
                Message::addErrorMessage($this->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
    }

    public function getAttrGrpByName(string $attrName, int $langId): array
    {
        if ($attrName == '') {
            return array();
        }
        $srch = SELF::getSearchObject($langId);
        $srch->addMultipleFields(array('attrgrp_id, attrgrp_name, attrgrp_identifier'));
        $srch->addCondition('mysql_func_lower(attrgrp_name)', '=', strtolower(trim($attrName)), '', true);
        $srch->addCondition('mysql_func_lower(attrgrp_identifier)', '=', strtolower(trim($attrName)), 'OR', true);

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $record = $db->fetch($rs);
        if (empty($record)) {
            return array();
        }
        return $record;
    }

    public function getLastNumAndTxtAttr(int $prodCatId): array
    {
        $lastTxtAttr = $lastNumAttr = 0;

        $srch = AttrGroupAttribute::getSearchObject();
        $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'prodcat_id', '=', $prodCatId);
        $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'attrgrp_id', '=', $this->mainTableRecordId);
        $srch->addMultipleFields(array('attr_fld_name', 'attr_identifier', 'attr_type'));
        $srch->addOrder(AttrGroupAttribute::DB_TBL_PREFIX . 'display_order');
        $rs = $srch->getResultSet();
        $attributes = FatApp::getDb()->fetchAll($rs);

        if (!empty($attributes)) {
            foreach($attributes as $attribute) {
                $attrFldName = explode('_', $attribute['attr_fld_name']);
                if ($attribute['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                    if ($attrFldName[2] > $lastTxtAttr) {
                        $lastTxtAttr = $attrFldName[2];
                    }
                } else {
                    if ($attrFldName[2] > $lastNumAttr) {
                        $lastNumAttr = $attrFldName[2];
                    }
                }
            }
        }

        return array('text_attributes' => $lastTxtAttr, 'num_attributes' => $lastNumAttr);
    }
}
