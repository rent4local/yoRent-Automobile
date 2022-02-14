<?php

class ProductCategory extends MyAppModel
{

    public const DB_TBL = 'tbl_product_categories';
    public const DB_TBL_PREFIX = 'prodcat_';
    public const DB_TBL_LANG = 'tbl_product_categories_lang';
    public const DB_TBL_LANG_PREFIX = 'prodcatlang_';

    public const DB_TBL_PROD_CAT_RELATIONS = 'tbl_product_category_relations';
    public const DB_TBL_PROD_CAT_REL_PREFIX = 'pcr_';

    public const REWRITE_URL_PREFIX = 'category/view/';
    public const REMOVED_OLD_IMAGE_TIME = 4;
    private $categoryTreeArr = array();

    public const REQUEST_PENDING = 0;
    public const REQUEST_APPROVED = 1;
    public const REQUEST_CANCELLED = 2;
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getStatusArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            trigger_error(Labels::getLabel('ERR_Language_Id_not_specified.', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $arr = array(
            static::REQUEST_PENDING => Labels::getLabel('LBL_Pending', $langId),
            static::REQUEST_APPROVED => Labels::getLabel('LBL_Approved', $langId),
            static::REQUEST_CANCELLED => Labels::getLabel('LBL_Cancelled', $langId)
        );
        return $arr;
    }

    public static function getStatusClassArr()
    {
        return array(
            static::REQUEST_PENDING => applicationConstants::CLASS_INFO,
            static::REQUEST_APPROVED => applicationConstants::CLASS_SUCCESS,
            static::REQUEST_CANCELLED => applicationConstants::CLASS_DANGER
        );
    }

    public static function getSearchObject($includeChildCount = false, $langId = 0, $prodcatActive = true, $prodcatStatus = 1)
    {
        $langId = FatUtility::int($langId);
        $prodcatStatus = FatUtility::int($prodcatStatus);
        $srch = new SearchBase(static::DB_TBL, 'm');

        if ($includeChildCount) {
            $childSrchbase = new SearchBase(static::DB_TBL);
            $childSrchbase->addCondition('prodcat_deleted', '=', 0);
            $childSrchbase->doNotCalculateRecords();
            $childSrchbase->doNotLimitRecords();
            $srch->joinTable('(' . $childSrchbase->getQuery() . ')', 'LEFT OUTER JOIN', 's.prodcat_parent = m.prodcat_id', 's');
            $srch->addGroupBy('m.prodcat_id');
            $srch->addFld('COUNT(s.prodcat_id) AS child_count');
        }

        if ($langId > 0) {
            $srch->joinTable(
                    static::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pc_l.' . static::DB_TBL_LANG_PREFIX . 'prodcat_id = m.' . static::tblFld('id') . ' and
			pc_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'pc_l'
            );
        }

        if (-1 != $prodcatStatus) {
            $srch->addCondition('m.prodcat_status', '=', $prodcatStatus);
        }

        if ($prodcatActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }

        return $srch;
    }

    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'prodcat_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'prodcat_identifier',
                'prodcat_name',
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
                'prodcat_id'
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'prodcat_identifier',
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

    public function updateCatCode()
    {
        $categoryId = $this->mainTableRecordId;
        if (1 > $categoryId) {
            return false;
        }

        $categoryArray = array($categoryId);
        $parentCatData = ProductCategory::getAttributesById($categoryId, array('prodcat_parent'));
        if (array_key_exists('prodcat_parent', $parentCatData) && $parentCatData['prodcat_parent'] > 0) {
            array_push($categoryArray, $parentCatData['prodcat_parent']);
        }

        foreach ($categoryArray as $categoryId) {
            $srch = ProductCategory::getSearchObject(false, 0, false, -1);
            $srch->addOrder('m.prodcat_active', 'DESC');
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $srch->addMultipleFields(array('prodcat_id', 'GETCATCODE(`prodcat_id`) as prodcat_code', 'GETCATORDERCODE(`prodcat_id`) as prodcat_ordercode'));
            $srch->addCondition('GETCATCODE(`prodcat_id`)', 'LIKE', '%' . str_pad($categoryId, 6, '0', STR_PAD_LEFT) . '%', 'AND', true);
            $rs = $srch->getResultSet();
            $catCode = FatApp::getDb()->fetchAll($rs);
            foreach ($catCode as $row) {
                $record = new ProductCategory($row['prodcat_id']);
                $data = array('prodcat_code' => $row['prodcat_code'], 'prodcat_ordercode' => $row['prodcat_ordercode']);
                $record->assignValues($data);
                if (!$record->save()) {
                    Message::addErrorMessage($record->getError());
                    return false;
                }

                /* Updating Category Relations */
                self::updateCategoryRelations($row['prodcat_id'], $row['prodcat_code']);
                /* Updating Category Relations */
            }
        }
        return true;
    }

    public static function updateCatOrderCode($prodCatId = 0)
    {
        $prodCatId = FatUtility::int($prodCatId);

        $srch = ProductCategory::getSearchObject(false, 0, false);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id', 'GETCATORDERCODE(`prodcat_id`) as prodcat_ordercode'));
        if ($prodCatId) {
            $srch->addCondition('prodcat_id', '=', $prodCatId);
        }

        $rs = $srch->getResultSet();
        $orderCode = FatApp::getDb()->fetchAll($rs);
        foreach ($orderCode as $row) {
            $record = new ProductCategory($row['prodcat_id']);
            $data = array('prodcat_ordercode' => $row['prodcat_ordercode']);
            $record->assignValues($data);
            if (!$record->save()) {
                Message::addErrorMessage($record->getError());
                return false;
            }
        }
    }

    public function getMaxOrder($parent = 0)
    {
        $srch = new SearchBase(static::DB_TBL);
        $srch->addFld("MAX(" . static::DB_TBL_PREFIX . "display_order) as max_order");
        if ($parent > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'parent', '=', $parent);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        if (!empty($record)) {
            return $record['max_order'] + 1;
        }
        return 1;
    }

    public static function getTreeArr($langId, $parentId = 0, $sortByName = false, $prodCatSrchObj = false, $excludeCatHavingNoProducts = false, $keywords = false)
    {
        $parentId = FatUtility::int($parentId);
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error("Language not specified", E_USER_ERROR);
        }

        if (is_object($prodCatSrchObj)) {
            $prodCatSrch = clone $prodCatSrchObj;
        } else {
            $prodCatSrch = new ProductCategorySearch($langId, true, true, false);
        }

        if (!empty($keywords)) {
            $cnd = $prodCatSrch->addCondition('prodcat_identifier', 'like', '%' . $keywords . '%');
            $cnd->attachCondition('prodcat_name', 'like', '%' . $keywords . '%');
        }

        $prodCatSrch->doNotCalculateRecords();
        $prodCatSrch->doNotLimitRecords();
        $prodCatSrch->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block', 'prodcat_active', 'prodcat_parent', 'prodcat_code', 'prodcat_ordercode'));

        if (0 < $parentId) {
            $catCode = static::getAttributesById($parentId, 'prodcat_code');
            $prodCatSrch->addCondition('prodcat_code', 'like', $catCode . '%');
        }

        if ($excludeCatHavingNoProducts) {
            $prodSrchObj = new ProductSearch();
            $prodSrchObj->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice' => true));
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->doNotLimitRecords();
            $prodSrchObj->joinProductToCategory();
            $prodSrchObj->joinSellerSubscription($langId, true);
            $prodSrchObj->addSubscriptionValidCondition();

            $prodSrchObj->addGroupBy('c.prodcat_id');
            $prodSrchObj->addMultipleFields(array('count(selprod_id) as productCounts', 'c.prodcat_id as qryProducts_prodcat_id'));
            //$prodSrchObj->addMultipleFields( array('count(selprod_id) as productCounts', 'c.prodcat_code as qryProducts_prodcat_code') );
            $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $prodSrchObj->addHaving('productCounts', '>', 0);
            $prodCatSrch->joinTable('(' . $prodSrchObj->getQuery() . ')', 'INNER JOIN', 'qryProducts.qryProducts_prodcat_id = c.prodcat_id', 'qryProducts');
            //$prodCatSrch->joinTable( '('.$prodSrchObj->getQuery().')', 'LEFT OUTER JOIN', 'qryProducts.qryProducts_prodcat_code like CONCAT(c.prodcat_code, "%")', 'qryProducts' );
        }

        if ($sortByName) {
            $prodCatSrch->addOrder('prodcat_name');
            $prodCatSrch->addOrder('prodcat_identifier');
        } else {
            //$prodCatSrch->addOrder('prodrootcat_code');
            $prodCatSrch->addOrder('prodcat_ordercode');
        }
        // echo $prodCatSrch->getQuery();exit;
        $rs = $prodCatSrch->getResultSet();
        $categoriesArr = FatApp::getDb()->fetchAll($rs, 'prodcat_id');
        static::addMissingParentDetails($categoriesArr, $langId);
        $categoriesArr = static::parseTree($categoriesArr, $parentId);

        return $categoriesArr;
    }

    public static function addMissingParentDetails(&$categoriesArr, $langId)
    {
        foreach ($categoriesArr as $category) {
            if (!$category['prodcat_parent'] || array_key_exists($category['prodcat_parent'], $categoriesArr)) {
                continue;
            }

            $catCode = explode('_', rtrim($category['prodcat_code'], '_'));
            foreach ($catCode as $code) {
                $catId = ltrim($code, 0);

                if (!$catId || array_key_exists($catId, $categoriesArr)) {
                    continue;
                }

                $srch = new ProductCategorySearch($langId, true, true, false);
                $srch->addCondition('prodcat_id', '=', $catId);
                $srch->setPageSize(1);
                $srch->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block', 'prodcat_active', 'prodcat_parent', 'prodcat_code', 'prodcat_ordercode'));
                $rs = $srch->getResultSet();
                $data = FatApp::getDb()->fetch($rs);
                $categoriesArr[$catId] = $data;

                if (empty($data)) {
                    unset($categoriesArr[$catId]);
                }
            }
        }
    }

    public static function parseTree($tree, $root = 0)
    {
        $return = array();
        foreach ($tree as $categoryId => $category) {
            $parent = $category['prodcat_parent'];
            if ($parent == $root) {
                unset($tree[$categoryId]);
                $return[$categoryId] = $category;
                $child = static::parseTree($tree, $categoryId);
                $return[$categoryId]['isLastChildCategory'] = (0 < count($child)) ? 0 : 1;
                $return[$categoryId]['children'] = (true === MOBILE_APP_API_CALL) ? array_values($child) : $child;
            }
        }
        return empty($return) ? array() : $return;
    }

    public function getCategoryStructure($prodcat_id, $category_tree_array = '', $langId = 0)
    {
        if (!is_array($category_tree_array)) {
            $category_tree_array = array();
        }
        $langId = FatUtility::int($langId);

        $srch = static::getSearchObject();
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');

        if ($langId > 0) {
            $srch->joinTable(static::DB_TBL_LANG, 'LEFT OUTER JOIN', static::DB_TBL_LANG_PREFIX . 'prodcat_id = ' . static::tblFld('id') . ' and ' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId);
            $srch->addFld(array('COALESCE(prodcat_name,prodcat_identifier) as prodcat_name'));
        } else {
            $srch->addFld(array('prodcat_identifier as prodcat_name'));
        }

        $srch->addMultipleFields(array('prodcat_id', 'prodcat_identifier', 'prodcat_parent'));
        $rs = $srch->getResultSet();
        while ($categories = FatApp::getDb()->fetch($rs)) {
            $category_tree_array[] = $categories;
            $category_tree_array = self::getCategoryStructure($categories['prodcat_parent'], $category_tree_array, $langId);
        }

        return $category_tree_array;
    }

    /* public function getProdCat($prodcat_id,$lang_id=0){
      $srch =$this->getSearchObject();
      $srch->addCondition('m.prodcat_id','=',$prodcat_id);
      if($lang_id>0){
      $srch->joinTable(static::DB_TBL_LANG, 'LEFT JOIN', 'plang.prodcatlang_prodcat_id = m.prodcat_id', 'plang');
      $srch->addFld('plang.*');
      }
      $srch->addFld('m.*');
      $record = FatApp::getDb()->fetch($srch->getResultSet());
      //var_dump($record); exit;
      $lang_record=array();
      return  array_merge($record,$lang_record);

      } */

    public function addUpdateProdCatLang($data, $lang_id, $prodcat_id)
    {
        $tbl = new TableRecord(static::DB_TBL_LANG);
        $data['prodcatlang_prodcat_id'] = FatUtility::int($prodcat_id);
        $tbl->assignValues($data);
        if ($this->isExistProdCatLang($lang_id, $prodcat_id)) {
            if (!$tbl->update(array('smt' => 'prodcatlang_prodcat_id = ? and prodcatlang_lang_id = ? ', 'vals' => array($prodcat_id, $lang_id)))) {
                $this->error = $tbl->getError();
                return false;
            }
            return $prodcat_id;
        }
        if (!$tbl->addNew()) {
            $this->error = $tbl->getError();
            return false;
        }
        return true;
    }

    public function isExistProdCatLang($lang_id, $prodcat_id)
    {
        $srch = new SearchBase(static::DB_TBL_LANG);
        $srch->addCondition('prodcatlang_prodcat_id', '=', $prodcat_id);
        $srch->addCondition('prodcatlang_lang_id', '=', $lang_id);
        $row = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    public function getParentTreeStructure($prodCat_id = 0, $level = 0, $name_suffix = '', $langId = 0, $active = true, $status = 1)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, $active, $status);
        $srch->addFld('m.prodcat_id,COALESCE(prodcat_name,m.prodcat_identifier) as prodcat_identifier,m.prodcat_parent');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodCat_id', '=', FatUtility::int($prodCat_id));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        $name = '';
        $seprator = '';
        if ($level > 0) {
            $seprator = ' &nbsp;&nbsp;&raquo;&raquo;&nbsp;&nbsp;';
        }

        if ($records) {
            $name = strip_tags($records['prodcat_identifier']) . $seprator . $name_suffix;
            if ($records['prodcat_parent'] > 0) {
                $name = self::getParentTreeStructure($records['prodcat_parent'], $level + 1, $name, $langId);
            }
        }
        return $name;
    }

    public static function isLastChildCategory($prodCat_id = 0)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('prodcat_parent', '=', $prodCat_id);
        $srch->addCondition('prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(array('prodcat_id'));
        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetch($rs);
        if (empty($records)) {
            return true;
        }
        return false;
    }

    public function getProdCatAutoSuggest($keywords = '', $limit = 10, $langId = 0, $collectionId = 0)
    {
        $srch = static::getSearchObject(false, $langId);
        $srch->addFld('m.prodcat_id,m.prodcat_identifier,m.prodcat_parent');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        if (!empty($keywords)) {
            $srch->addCondition('m.prodcat_identifier', 'like', '%' . $keywords . '%');
        }

        $alreadyAdded = Collections::getRecords($collectionId);
        if (!empty($alreadyAdded) && 0 < count($alreadyAdded)) {
            $srch->addCondition('prodcat_id', 'NOT IN', array_keys($alreadyAdded));
        }

        $srch->addOrder('m.prodcat_parent', 'asc');
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $return = array();
        foreach ($records as $row) {
            if (count($return) >= $limit) {
                break;
            }
            if ($row['prodcat_parent'] > 0) {
                $return[$row['prodcat_id']] = self::getParentTreeStructure($row['prodcat_id'], 0, '', $langId);
            } else {
                $return[$row['prodcat_id']] = $row['prodcat_identifier'];
            }
        }
        return $return;
    }

    public function getNestedArray($langId)
    {
        $arr = $this->getCategoriesForSelectBox($langId);
        $out = array();
        foreach ($arr as $id => $cat) {
            $tree = str_split($cat['prodcat_code'], 6);
            array_pop($tree);
            $parent = &$out;
            foreach ($tree as $parentId) {
                $parentId = intval($parentId);
                $parent = &$parent['children'][$parentId];
            }
            $parent['children'][$id]['name'] = $cat['prodcat_name'];
        }
        return $out;
    }

    public function makeAssociativeArray($arr, $prefix = ' » ')
    {
        $out = array();
        $tempArr = array();
        foreach ($arr as $key => $value) {
            $tempArr[] = $key;
            $name = $value['prodcat_name'];
            $code = str_replace('_', '', $value['prodcat_code']);
            $hierarchyArr = str_split($code, 6);

            $this_deleted = 0;
            foreach ($hierarchyArr as $node) {
                $node = FatUtility::int($node);
                if (!in_array($node, $tempArr)) {
                    $this_deleted = 1;
                    break;
                }
            }
            if ($this_deleted == 0) {
                $level = strlen($code) / 6;
                for ($i = 1; $i < $level; $i++) {
                    $name = $prefix . $name;
                }
                $out[$key] = $name;
            }
        }
        return $out;
    }

    public function getCategoriesForSelectBox($langId, $ignoreCategoryId = 0, $prefCategoryid = array())
    {
        /* $srch = new SearchBase(static::DB_TBL); */
        $srch = static::getSearchObject();
        $srch->joinTable(static::DB_TBL_LANG, 'LEFT OUTER JOIN', 'prodcatlang_prodcat_id = prodcat_id
			AND prodcatlang_lang_id = ' . $langId);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', 0);
        $srch->addMultipleFields(array(
            'prodcat_id',
            'COALESCE(prodcat_name, prodcat_identifier) AS prodcat_name',
            'prodcat_code'
        ));

        //$srch->addOrder('GETCATORDERCODE(prodcat_id)');
        $srch->addOrder('prodcat_ordercode');

        if (count($prefCategoryid) > 0) {
            foreach ($prefCategoryid as $prefCategoryids) {
                $srch->addHaving('prodcat_code', 'LIKE', '%' . $prefCategoryids . '%', 'OR');
            }
        }

        if ($ignoreCategoryId > 0) {
            $srch->addHaving('prodcat_code', 'NOT LIKE', '%' . str_pad($ignoreCategoryId, 6, '0', STR_PAD_LEFT) . '%');
        }

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        /* echo $srch->getQuery(); die; */
        $rs = $srch->getResultSet();

        return FatApp::getDb()->fetchAll($rs, 'prodcat_id');
    }

    public function getProdCatTreeStructure($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }

        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('m.prodcat_parent', '=', FatUtility::int($parent_id));

        if (!empty($keywords)) {
            $srch->addCondition('prodcat_name', 'like', '%' . $keywords . '%');
        }

        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('prodcat_name', 'asc');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $return = array();
        $seprator = '';
        if ($level > 0) {
            if ($isForCsv) {
                $seprator = '->-> ';
            } else {
                $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
            }
            $seprator = CommonHelper::renderHtml($seprator);
        }
        foreach ($records as $prodcat_id => $prodcat_identifier) {
            $name = $name_prefix . $seprator . $prodcat_identifier;
            $return[$prodcat_id] = $name;
            $return += self::getProdCatTreeStructure($prodcat_id, $langId, $keywords, $level + 1, $name, $isActive, $isDeleted, $isForCsv);
        }
        return $return;
    }

    public function getProdCatTreeStructureSearch($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }

        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addCondition('m.prodcat_parent', '=', FatUtility::int($parent_id));

        if (!empty($keywords)) {
            //$srch->addCondition('prodcat_name','like','%'.$keywords.'%');
        }
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('prodcat_name', 'asc');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $return = array();
        $seprator = '';
        if ($level > 0) {
            if ($isForCsv) {
                $seprator = '->-> ';
            } else {
                $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
            }
            $seprator = CommonHelper::renderHtml($seprator);
        }
        //print_r($records); die;
        foreach ($records as $prodcat_id => $prodcat_identifier) {
            $name = $name_prefix . $seprator . $prodcat_identifier;
            //echo $name."<br>";
            $flag = 0;
            if ($keywords) {
                if (stripos($name, $keywords) !== false) {
                    $return[$prodcat_id] = $name;
                }
            } else {
                $return[$prodcat_id] = $name;
            }
            $return += self::getProdCatTreeStructureSearch($prodcat_id, $langId, $keywords, $level + 1, $name, $isActive, $isDeleted, $isForCsv);
            //print_r($return); die;
        }
        return $return;
    }

    public function getAutoCompleteProdCatTreeStructure($parent_id = 0, $langId = 0, $keywords = '', $level = 0, $name_prefix = '', $isActive = true, $isDeleted = true, $isForCsv = false)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject(false, $langId, false);
        //$srch->addOrder('catOrder','asc');
        //$srch->addOrder('m.prodcat_display_order','asc');
        $srch->addOrder('prodcat_id', 'asc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('prodcat_id', 'prodcat_active', 'prodcat_deleted', 'prodcat_name', 'prodcat_code'));
        $rs = $srch->getResultSet();
        $catRecords = FatApp::getDb()->fetchAll($rs, 'prodcat_id');


        $srch = static::getSearchObject(false, $langId, $isActive);
        if ($langId) {
            $srch->addFld('m.prodcat_id, COALESCE(pc_l.prodcat_name, m.prodcat_identifier) as prodcat_name');
        } else {
            $srch->addFld('m.prodcat_id, m.prodcat_identifier as prodcat_name');
        }
        //$srch->addFld('GETCATORDERCODE(prodcat_id) as catOrder');
        $srch->addFld('prodcat_ordercode as catOrder');
        if ($isDeleted) {
            $srch->addCondition('m.prodcat_deleted', '=', 0);
        }

        if ($isActive) {
            $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        }
        if ($parent_id > 0) {
            $srch->addCondition('m.prodcat_id', '=', FatUtility::int($parent_id));
        }

        if (!empty($keywords)) {
            $srch->addCondition('prodcat_name', 'like', '%' . $keywords . '%');
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('catOrder', 'asc');
        $srch->addOrder('prodcat_name', 'asc');
        //echo $srch->getQuery();
        $rs = $srch->getResultSet();
        $return = array();

        $records = FatApp::getDb()->fetchAll($rs);
        foreach ($records as $prodCats) {
            $level = 0;
            $seprator = '';
            $name_prefix = '';
            $categoryCode = substr($catRecords[$prodCats['prodcat_id']]['prodcat_code'], 0, -1);
            $prodCat = explode("_", $categoryCode);
            foreach ($prodCat as $key => $prodcatParent) {
                // var_dump($catRecords[FatUtility::int($prodcatParent)]);
                if ($catRecords[FatUtility::int($prodcatParent)]['prodcat_deleted'] != applicationConstants::NO || $catRecords[FatUtility::int($prodcatParent)]['prodcat_active'] != applicationConstants::ACTIVE) {
                    break;
                }
                if ($level > 0) {
                    if ($isForCsv) {
                        $seprator = '->-> ';
                    } else {
                        $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                    }
                    $seprator = CommonHelper::renderHtml($seprator);
                }
                $productCatName = $catRecords[FatUtility::int($prodcatParent)]['prodcat_name'];

                $name_prefix = $name_prefix . $seprator . $productCatName;

                $return[$prodCats['prodcat_id']] = $name_prefix;
                $level++;
            }
        }
        return $return;
    }

    public static function getProdCatParentChildWiseArr(int $langId = 0, int $parentId = 0, bool $includeChildCat = true, bool $forSelectBox = false, bool $sortByName = false, $prodCatSrchObj = false, bool $excludeCategoriesHavingNoProducts = false, $joinRelationTable = true)
    {
        if (!$langId) {
            trigger_error("Language not specified", E_USER_ERROR);
        }

        if (is_object($prodCatSrchObj)) {
            $prodCatSrch = clone $prodCatSrchObj;
        } else {
            $prodCatSrch = new ProductCategorySearch($langId);
            $prodCatSrch->setParent($parentId);
        }
        $prodCatSrch->doNotCalculateRecords();
        $prodCatSrch->doNotLimitRecords();

        $prodCatSrch->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block', 'prodcat_active', 'prodcat_parent', 'prodcat_code as prodcat_code'));

        if ($excludeCategoriesHavingNoProducts) {
            $prodSrchObj = new ProductSearch();
            $prodSrchObj->setDefinedCriteria();
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->doNotLimitRecords();
            $prodSrchObj->joinSellerSubscription(0, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->addMultipleFields(array('product_id'));
            $prodSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
            $prodSrchObj->addGroupBy('product_id');

            if ($joinRelationTable) {
                $prodCatSrch->joinProductCategoryRelations(); 
            }
            
            $prodCatSrch->addFld('COALESCE(COUNT(ptc.ptc_product_id), 0) as productCounts');
            $prodCatSrch->joinTable('(' . $prodSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'qryProducts.product_id = ptc.ptc_product_id', 'qryProducts');

            $prodCatSrch->addHaving('productCounts', '>', 0);
        }

        if ($sortByName) {
            $prodCatSrch->addOrder('prodcat_name');
            $prodCatSrch->addOrder('prodcat_identifier');
        } else {
            $prodCatSrch->addOrder('prodcat_ordercode');
        }

        $rs = $prodCatSrch->getResultSet();
        if ($forSelectBox) {
            $categoriesArr = FatApp::getDb()->fetchAllAssoc($rs);
        } else {
            $categoriesArr = FatApp::getDb()->fetchAll($rs);
        }

        if (true === $includeChildCat && $categoriesArr) {
            foreach ($categoriesArr as $key => $cat) {
                $categoriesArr[$key]['icon'] = UrlHelper::generateFullUrl('Category', 'icon', array($cat['prodcat_id'], $langId, 'COLLECTION_PAGE'));
                $categoriesArr[$key]['children'] = self::getProdCatParentChildWiseArr($langId, $cat['prodcat_id']);
            }
        }
        return $categoriesArr;
    }

    public static function getRootProdCatArr($langId)
    {
        $langId = FatUtility::int($langId);
        if (!$langId) {
            trigger_error(Labels::getLabel('ERR_Language_Not_Specified', $langId), E_USER_ERROR);
        }
        return static::getProdCatParentChildWiseArr($langId, 0, false, true);
    }

    public function canRecordMarkDelete($prodcat_id)
    {
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addFld('m.prodcat_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['prodcat_id'] == $prodcat_id) {
            return true;
        }
        return false;
    }

    public function canRecordUpdateStatus($prodcat_id)
    {
        $srch = static::getSearchObject();
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $prodcat_id);
        $srch->addFld('m.prodcat_id,m.prodcat_active');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row) && $row['prodcat_id'] == $prodcat_id) {
            return $row;
        }
        return false;
    }

    /* function getSubCategory(){
      $srch = new SearchBase(static::DB_TBL, 'prodSubCate');
      $srch->addCondition('prodSubCate.prodcat_deleted', '=',0);
      $srch->doNotCalculateRecords();
      $srch->doNotLimitRecords();
      $srch->addGroupBy('prodSubCate.prodcat_parent');
      $srch->addMultipleFields(array('prodSubCate.prodcat_parent',"COUNT(prodSubCate.prodcat_id) AS total_sub_cats"));
      return $srch;
      } */

    public static function recordCategoryWeightage($categoryId)
    {
        /* $categoryId =  FatUtility::int($categoryId);
          if(1 > $categoryId){ return false;}
          $obj = new SmartUserActivityBrowsing();
          return $obj->addUpdate($categoryId,SmartUserActivityBrowsing::TYPE_CATEGORY); */
    }

    public static function getDeletedProductCategoryByIdentifier($identifier = '')
    {
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::YES);
        $srch->addCondition('m.prodcat_identifier', '=', $identifier);

        $srch->addFld('m.prodcat_id');
        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            return $row['prodcat_id'];
        } else {
            return false;
        }
    }

    /* public static function getCatName($id,$categoryArr) {
      if (!array_key_exists($id, $categoryArr)) {
      $categoryArr[$id] = productCategory::getAttributesByLangId($id, 'prodcat_name');
      }
      return $categoryArr[$id];
      } */

    public static function getProductCategoryName($id, $langId)
    {
        $srch = static::getSearchObject(false, $langId);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('m.prodcat_deleted', '=', 0);
        $srch->addCondition('m.prodcat_id', '=', $id);
        $srch->addFld('COALESCE(prodcat_name,prodcat_identifier) as prodcat_name');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if ($row) {
            return $row['prodcat_name'];
        } else {
            return false;
        }
    }

    public function getCategoryTreeForSearch($siteLangId, $categories, &$globalCatTree = array(), $attr = array())
    {
        if ($categories) {
            $remainingCatCods = $categories;
            $catId = $categories[0];
            unset($remainingCatCods[0]);
            $remainingCatCods = array_values($remainingCatCods);
            $catId = FatUtility::int($catId);
            if (!empty($attr) && is_array($attr)) {
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(prodcat_code,1,6) AS prodrootcat_code', 'prodcat_content_block', 'prodcat_active', 'prodcat_parent', 'prodcat_code as prodcat_code'));
                $prodCatSrch->addCondition('prodcat_id', '=', $catId);
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);
                if (!empty($rows)) {
                    foreach ($rows as $key => $val) {
                        $globalCatTree[$catId][$key] = $val;
                    }
                }
            } else {
                /* $globalCatTree[$catId]['prodcat_name'] = productCategory::getAttributesByLangId($siteLangId,$catId,'prodcat_name'); */

                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addFld('COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name');
                $prodCatSrch->addCondition('prodcat_id', '=', $catId);
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);
                if (!empty($rows)) {
                    $globalCatTree[$catId]['prodcat_name'] = $rows['prodcat_name'];
                    $globalCatTree[$catId]['prodcat_id'] = $catId;
                }
                
            }
            //$globalCatTree[$catId]['prodcat_id']['children'] = '';
            if (count($remainingCatCods) > 0) {
                $this->getCategoryTreeForSearch($siteLangId, $remainingCatCods, $globalCatTree[$catId]['children'], $attr);
            }
        }
    }

    public function getCategoryTreeArr($siteLangId, $categoriesDataArr, $attr = array())
    {
        foreach ($categoriesDataArr as $categoriesData) {
            $categoryCode = substr($categoriesData['prodcat_code'], 0, -1);
            $prodCats = explode("_", $categoryCode);
            $remaingCategories = $prodCats;
            unset($remaingCategories[0]);
            $remaingCategories = array_values($remaingCategories);

            $parentId = FatUtility::int($prodCats[0]);
            if (!array_key_exists($parentId, $this->categoryTreeArr)) {
                $this->categoryTreeArr[$parentId] = array();
            }
            if (!empty($attr) && is_array($attr)) {
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addMultipleFields($attr);
                $prodCatSrch->addCondition('prodcat_id', '=', FatUtility::int($prodCats[0]));
                $rs = $prodCatSrch->getResultSet();
                $rows = FatApp::getDb()->fetch($rs);
                foreach ($rows as $key => $val) {
                    $this->categoryTreeArr[$parentId][$key] = $val;
                }
            } else {
                /* $this->categoryTreeArr [$parentId]['prodcat_name'] = productCategory::getAttributesByLangId($siteLangId,FatUtility::int($prodCats[0]),'prodcat_name'); */
                $prodCatSrch = new ProductCategorySearch($siteLangId);
                $prodCatSrch->addFld('COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name');
                $prodCatSrch->addCondition('prodcat_id', '=', FatUtility::int($prodCats[0]));
                $rs = $prodCatSrch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                $this->categoryTreeArr[$parentId]['prodcat_name'] = $row['prodcat_name'];
                $this->categoryTreeArr[$parentId]['prodcat_id'] = FatUtility::int($prodCats[0]);
            }

            if (!isset($this->categoryTreeArr[$parentId]['children'])) {
                $this->categoryTreeArr[$parentId]['children'] = array();
            }
            $this->getCategoryTreeForSearch($siteLangId, $remaingCategories, $this->categoryTreeArr[$parentId]['children'], $attr);
        }
        return $this->categoryTreeArr;
    }

    public function getProdRootCategoriesWithKeyword($langId = 0, $keywords = '', $returnWithChildArr = false, $prodcatCode = false, $inludeChildCount = false)
    {
        $srch = static::getSearchObject($inludeChildCount, $langId);
        $srch->addFld('m.prodcat_id,COALESCE(pc_l.prodcat_name,m.prodcat_identifier) as prodcat_name,m.prodcat_parent,substr(m.prodcat_code,1,6) AS prodrootcat_code');
        $srch->addCondition('m.prodcat_deleted', '=', applicationConstants::NO);
        $srch->addCondition('m.prodcat_active', '=', applicationConstants::ACTIVE);
        if (!empty($keywords)) {
            $cnd = $srch->addCondition('m.prodcat_identifier', 'like', '%' . $keywords . '%');
            $cnd->attachCondition('pc_l.prodcat_name', 'like', '%' . $keywords . '%');
        }
        $srch->addOrder('m.prodcat_parent', 'asc');
        $srch->addOrder('m.prodcat_display_order', 'asc');
        $srch->addOrder('m.prodcat_identifier', 'asc');
        if ($returnWithChildArr == false) {
            $srch->addFld('count(m.prodcat_id) as totalRecord');
            $srch->addGroupBy('prodrootcat_code');
        }

        if ($prodcatCode) {
            $srch->addHaving('prodrootcat_code', '=', $prodcatCode);
        }

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $return = array();
        if ($returnWithChildArr) {
            foreach ($records as $row) {
                if ($row['prodcat_parent'] > 0) {
                    $return[$row['prodrootcat_code']][$row['prodcat_id']]['structure'] = self::getParentTreeStructure($row['prodcat_id'], 0, '', $langId);
                    $return[$row['prodrootcat_code']][$row['prodcat_id']]['prodcat_name'] = $row['prodcat_name'];
                }
            }
        } else {
            $return = $records;
        }
        return $return;
    }

    public function haveProducts()
    {
        $prodSrchObj = new ProductSearch();
        $prodSrchObj->setDefinedCriteria();
        $prodSrchObj->joinProductToCategory();
        $prodSrchObj->doNotCalculateRecords();
        $prodSrchObj->setPageSize(1);

        $prodSrchObj->addMultipleFields(array('count(selprod_id) as productCounts', 'prodcat_id'));
        /* $prodSrchObj->addMultipleFields(array('substr(prodcat_code,1,6) AS prodrootcat_code','count(selprod_id) as productCounts', 'prodcat_id')); */

        $cnd = $prodSrchObj->addCondition('c.prodcat_id', '=', $this->mainTableRecordId);
        $cnd->attachCondition('c.prodcat_code', 'like', '%' . str_pad($this->mainTableRecordId, 6, '0', STR_PAD_LEFT) . '%');

        /*  if (0 < $this->mainTableRecordId) {
            $prodSrchObj->addHaving('prodrootcat_code', 'LIKE', '%' . str_pad($this->mainTableRecordId, 6, '0', STR_PAD_LEFT) . '%', 'AND', true);
        } */

        $prodSrchObj->addHaving('productCounts', '>', 0);

        $rs = $prodSrchObj->getResultSet();
        $productRows = FatApp::getDb()->fetch($rs);
        if (!empty($productRows) && $productRows['productCounts'] > 0) {
            return true;
        }
        return false;
    }

    public function rewriteUrl($keyword, $suffixWithId = true, $parentId = 0)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }

        $parentId = FatUtility::int($parentId);
        /* $parentUrl = '';
          if (0 < $parentId) {
          $parentUrlRewriteData = UrlRewrite::getDataByOriginalUrl(ProductCategory::REWRITE_URL_PREFIX.$parentId);
          if (!empty($parentUrlRewriteData)) {
          $parentUrl = preg_replace('/-'.$parentId.'$/', '', $parentUrlRewriteData['urlrewrite_custom']);
          }
          } */

        $originalUrl = ProductCategory::REWRITE_URL_PREFIX . $this->mainTableRecordId;

        $keyword = preg_replace('/-' . $this->mainTableRecordId . '$/', '', $keyword);
        $seoUrl = CommonHelper::seoUrl($keyword);
        if ($suffixWithId) {
            $seoUrl = $seoUrl . '-' . $this->mainTableRecordId;
        }

        /* $seoUrl = str_replace($parentUrl, '', $seoUrl);
          $seoUrl = $parentUrl.'-'.$seoUrl; */

        $customUrl = UrlRewrite::getValidSeoUrl($seoUrl, $originalUrl, $this->mainTableRecordId);
        return UrlRewrite::update($originalUrl, $customUrl);
    }

    public static function setImageUpdatedOn($userId, $date = '')
    {
        $date = empty($date) ? date('Y-m-d  H:i:s') : $date;
        $where = array('smt' => 'prodcat_id = ?', 'vals' => array($userId));
        FatApp::getDb()->updateFromArray(static::DB_TBL, array('prodcat_img_updated_on' => date('Y-m-d  H:i:s')), $where);
    }

    public function saveCategoryData($post)
    {
        $parentCatId = FatUtility::int($post['prodcat_parent']);
        $prodCatId = FatUtility::int($post['prodcat_id']);
        unset($post['prodcat_id']);
        $autoUpdateOtherLangsData = 0;
        if (isset($post['auto_update_other_langs_data'])) {
            $autoUpdateOtherLangsData = FatUtility::int($post['auto_update_other_langs_data']);
        }
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        if ($this->mainTableRecordId == 0) {
            $post['prodcat_display_order'] = $this->getMaxOrder($parentCatId);
        }

        if ($post['prodcat_parent'] == $this->mainTableRecordId) {
            $post['prodcat_parent'] = 0;
        }

        $this->assignValues($post);
        if ($this->save()) {
            $this->updateCatCode();
            $this->rewriteUrl($post['prodcat_identifier'], true, $parentCatId);
            Product::updateMinPrices();
        } else {
            $categoryId = self::getDeletedProductCategoryByIdentifier($post['prodcat_identifier']);
            if (!$categoryId) {
                $this->error = $this->getError();
                return false;
            }

            $record = new ProductCategory($categoryId);
            $data = $post;
            $data['prodcat_deleted'] = applicationConstants::NO;
            $record->assignValues($data);
            if (!$record->save()) {
                $this->error = $record->getError();
                return false;
            }
            $this->mainTableRecordId = $record->getMainTableRecordId();
            $this->updateCatCode();
        }

        $this->saveLangData($siteDefaultLangId, $post['prodcat_name'][$siteDefaultLangId]); // For site default language
        $catNameArr = $post['prodcat_name'];
        unset($catNameArr[$siteDefaultLangId]);
        foreach ($catNameArr as $langId => $catName) {
            if (empty($catName) && $autoUpdateOtherLangsData > 0) {
                $this->saveTranslatedLangData($langId);
            } elseif (!empty($catName)) {
                $this->saveLangData($langId, $catName);
            }
        }

        if ($prodCatId == 0 && isset($post['cat_icon_image_id']) && isset($post['cat_banner_image_id'])) {
            $this->updateMedia($post['cat_icon_image_id']);
            $this->updateMedia($post['cat_banner_image_id']);
        }
        return true;
    }

    public function saveLangData($langId, $prodCatName)
    {
        $langId = FatUtility::int($langId);
        if ($this->mainTableRecordId < 1 || $langId < 1) {
            $this->error = Labels::getLabel('ERR_Invalid_Request', $this->commonLangId);
            return false;
        }

        $data = array(
            'prodcatlang_prodcat_id' => $this->mainTableRecordId,
            'prodcatlang_lang_id' => $langId,
            'prodcat_name' => $prodCatName,
        );
        if (!$this->updateLangData($langId, $data)) {
            $this->error = $this->getError();
            return false;
        }
        return true;
    }

    public function saveTranslatedLangData($langId)
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

    public function updateMedia($ImageIds)
    {
        if (count($ImageIds) == 0) {
            return false;
        }
        foreach ($ImageIds as $imageId) {
            if ($imageId > 0) {
                $data = array('afile_record_id' => $this->mainTableRecordId);
                $where = array('smt' => 'afile_id = ?', 'vals' => array($imageId));
                FatApp::getDb()->updateFromArray(AttachedFile::DB_TBL, $data, $where);
            }
        }
        return true;
    }

    public function getTranslatedCategoryData($data, $toLangId)
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

    public function getCategories($includeProductCount = true, $includeSubCategoriesCount = true)
    {
        $srch = static::getSearchObject(false, $this->commonLangId, false);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', 0);
        $srch->addCondition(static::DB_TBL_PREFIX . 'parent', '=', $this->mainTableRecordId);
        if ($includeProductCount === true) {
            $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT JOIN', static::DB_TBL_PREFIX . 'id = ' . Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'prodcat_id', 'ptc');
            $srch->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX . 'id = ' . Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id', 'p');
            $cnd = $srch->addDirectCondition(Product::DB_TBL_PREFIX . 'deleted IS NULL');
            $cnd->attachCondition(Product::DB_TBL_PREFIX . 'deleted', '=', 0);
            $srch->addMultipleFields(array('COUNT(' . Product::DB_TBL_PRODUCT_TO_CATEGORY_PREFIX . 'product_id) as category_products'));
            $srch->addGroupBy(static::DB_TBL_PREFIX . 'id');
        }
        $srch->addOrder(static::DB_TBL_PREFIX . 'display_order', 'asc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('m.*', 'COALESCE(prodcat_name,prodcat_identifier ) as prodcat_name'));
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        if ($includeSubCategoriesCount === true) {
            foreach ($records as $key => $data) {
                $records[$key]['subcategory_count'] = $this->getSubCategoriesCount($data[static::DB_TBL_PREFIX . 'id']);
            }
        }
        return $records;
    }

    public function getSubCategoriesCount($prodCatId)
    {
        $prodCatId = FatUtility::int($prodCatId);
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', 0);
        $srch->addCondition(static::DB_TBL_PREFIX . 'parent', '=', $prodCatId);
        $srch->addMultipleFields(array('COUNT(' . static::DB_TBL_PREFIX . 'id) as subcategory_count'));
        $rs = $srch->getResultSet();
        $record = FatApp::getDb()->fetch($rs);
        return $record['subcategory_count'];
    }

    public static function getActiveInactiveCategoriesCount($active)
    {
        $srch = static::getSearchObject(false, 0, false);
        $srch->addCondition(static::DB_TBL_PREFIX . 'deleted', '=', 0);
        $srch->addCondition(static::DB_TBL_PREFIX . 'active', '=', $active);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addFld('COUNT(' . static::DB_TBL_PREFIX . 'id) as categories_count');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public static function deleteImagesWithOutCategoryId($fileType)
    {
        $allowedFileTypes = [AttachedFile::FILETYPE_CATEGORY_ICON, AttachedFile::FILETYPE_CATEGORY_BANNER];
        if (empty($fileType) || !in_array($fileType, $allowedFileTypes)) {
            return false;
        }

        $currentDate = date('Y-m-d  H:i:s');
        $prevDate = strtotime('-' . static::REMOVED_OLD_IMAGE_TIME . ' hour', strtotime($currentDate));
        $prevDate = date('Y-m-d  H:i:s', $prevDate);
        $where = array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_updated_at <= ?', 'vals' => array($fileType, 0, $prevDate));
        if (!FatApp::getDb()->deleteRecords(AttachedFile::DB_TBL, $where)) {
            return false;
        }
        return true;
    }

    public function updateCatParent($parentCatId)
    {
        if ($this->mainTableRecordId < 1) {
            return false;
        }
        $parentCatId = FatUtility::int($parentCatId);
        FatApp::getDb()->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX . 'parent' => $parentCatId), array('smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => array($this->mainTableRecordId)));
        return true;
    }

    /**
     * updateCategoryRelations
     *
     * @param  int $recordId
     * @param  string $prodcatCode
     * @return bool
     */
    public static function updateCategoryRelations(int $recordId = 0, string $prodcatCode = ''): bool
    {
        $db = FatApp::getDb();
        if (!empty($prodcatCode)) {
            $prodCatCodeArr = [$recordId => $prodcatCode];
        } else {
            $prodCatSrch = new ProductCategorySearch();
            if (0 < $recordId) {
                $prodCatSrch->addCondition('prodcat_id', '=', $recordId);
            }
            $prodCatSrch->addMultipleFields(['prodcat_id', 'prodcat_code']);
            $rs = $prodCatSrch->getResultSet();
            $prodCatCodeArr = $db->fetchAllAssoc($rs);
        }

        if (!empty($prodCatCodeArr)) {
            foreach ($prodCatCodeArr as $prodCatId => $prodCatCode) {
                $catCodeArr = explode('_', rtrim($prodCatCode, '_'));
                $deleted = false;
                foreach (array_reverse($catCodeArr) as $level => $code) {
                    $catId = ltrim($code, 0);

                    if (!$catId) {
                        continue;
                    }

                    if (false === $deleted) {
                        $db->deleteRecords(self::DB_TBL_PROD_CAT_RELATIONS, ['smt' => self::DB_TBL_PROD_CAT_REL_PREFIX . 'prodcat_id = ?', 'vals' => [$prodCatId]]);
                        $deleted = true;
                    }

                    $dataToSave = [
                        'pcr_prodcat_id' => $prodCatId,
                        'pcr_parent_id' => $catId,
                        'pcr_level' => ($catId == $prodCatId) ? 0 : ($level + 1),
                    ];
                    $db->insertFromArray(self::DB_TBL_PROD_CAT_RELATIONS, $dataToSave, false, array(), $dataToSave);
                }
            }
        }
        return true;
    }

    public function getAttributes(int $langId): array
    {
        $srch = AttrGroupAttribute::getSearchObject();
        $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = ' . AttrGroupAttribute::DB_TBL_PREFIX . 'id AND attrlang_lang_id = ' . $langId, 'lang');
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT JOIN', 'attr_attrgrp_id = ' . AttributeGroup::DB_TBL_PREFIX . 'id');
        $srch->joinTable(AttributeGroup::DB_TBL . '_lang', 'LEFT JOIN', AttributeGroup::DB_TBL_PREFIX . 'id= attrgrplang_attrgrp_id AND attrgrplang_lang_id = ' . $langId);
        $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'prodcat_id', '=', $this->mainTableRecordId);
        $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        $srch->addOrder(AttrGroupAttribute::DB_TBL_PREFIX . 'display_order');
        $srch->addMultipleFields(array('attrgrp.*', 'IFNULL(attr_name, attr_identifier) as attr_name', 'attr_postfix', 'IFNULL(attrgrp_name, attrgrp_identifier) as attrgrp_name', 'attr_options'));
        $rs = $srch->getResultSet();

        return FatApp::getDb()->fetchAll($rs);
    }

    public function getAttrDetail(int $langId = 0, $attrType = 0, string $addOrder = '', array $catIds = [], bool $forListingOnly = false): array
    {
        $srch = AttrGroupAttribute::getSearchObject();
        if (0 < $langId) {
            $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attrgrp.attr_id = attrlang_attr_id AND attrLang_lang_id = ' . $langId);
            $srch->joinTable(AttributeGroup::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attr_attrgrp_id = attrgrplang_attrgrp_id AND attrgrplang_lang_id = ' . $langId);
        } else {
            $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'attrgrp.attr_id = attrlang_attr_id');
        }
		if (!empty($catIds)) {
			$srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'prodcat_id', 'IN', $catIds);
		} else {
			$srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'prodcat_id', '=', $this->mainTableRecordId);
		}
		if ($forListingOnly) {
			$srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'display_in_listing', '=', applicationConstants::YES);
		}
		
        
        $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'active', '=', applicationConstants::YES);

        if (is_array($attrType) && !empty($attrType)) {
             $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'type', 'IN', $attrType);
        } elseif(is_int($attrType) && $attrType > 0) {
            $srch->addCondition(AttrGroupAttribute::DB_TBL_PREFIX . 'type', '=', $attrType);
        }
        
        if ($addOrder != '') {
            $srch->addOrder($addOrder, 'ASC');
        }
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public function isCategoryHasCustomFields($langId) : bool
    {
        if (empty($this->getAttributes($langId))) {
            return false;
        }
        return true;
    }
    
    public static function getRequestCount(array $usersIds) : int
    {
        if (empty($usersIds)) {
            return 0;
        }
        $srch = self::getSearchObject(false, 0, false, -1);
        $srch->addCondition('prodcat_seller_id', 'in', $usersIds);
        $srch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $srch->addFld('count(prodcat_id) as categoriesCount');
        $rs = $srch->getResultSet($srch);
        $row = FatApp::getDb()->fetch($rs);
        return $row['categoriesCount'];
    }
    
    
}