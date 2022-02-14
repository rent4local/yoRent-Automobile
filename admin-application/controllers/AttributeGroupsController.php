<?php

class AttributeGroupsController extends AdminBaseController
{
    private $canEdit;

    public function __construct($action)
    {
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->objPrivilege->canViewAttributes();
        $this->canEdit = $this->objPrivilege->canEditAttributes($this->admin_id, true);
        $this->set("canEdit", $this->canEdit);
    }

    public function autocomplete()
    {
        $post = FatApp::getPostedData();
        $srch = AttributeGroup::getSearchObject();
        $srch->addOrder('attrgrp_identifier');
        $srch->addMultipleFields(array('attrgrp_id, attrgrp_name, attrgrp_identifier, attrgrplang_lang_id'));
        $srch->joinTable(AttributeGroup::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attrgrp_id = attrgrplang_attrgrp_id');
        
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('attrgrp_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('attrgrp_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $attributeGroups = $db->fetchAll($rs);
        $json = array();
        $otherLangData = array();
        foreach ($attributeGroups as $key => $attrGroup) {
            if ($attrGroup['attrgrplang_lang_id'] == $this->adminLangId) {
                $name = ($attrGroup['attrgrp_name'] != '') ? $attrGroup['attrgrp_name'] : $attrGroup['attrgrp_identifier'];

                $json[] = array(
                    'id' => $attrGroup['attrgrp_id'],
                    'lang_id' => $attrGroup['attrgrplang_lang_id'],
                    'name' => strip_tags(html_entity_decode($name, ENT_QUOTES, 'UTF-8')),
                    'attrgrp_identifier' => strip_tags(html_entity_decode($attrGroup['attrgrp_identifier'], ENT_QUOTES, 'UTF-8')),
                    'otherLangData' => array(),
                );
            } else {
                $otherLangData[$attrGroup['attrgrp_id']][$attrGroup['attrgrplang_lang_id']] = $attrGroup['attrgrp_name'];
            }
        }

        if (!empty($otherLangData) && !empty($json)) {
            foreach ($json as $key => $jsondata) {
                if (array_key_exists($jsondata['id'], $otherLangData)) {
                    $json[$key]['otherLangData'] = $otherLangData[$jsondata['id']];
                }
            }
        }
        die(json_encode($json));
    }

}
