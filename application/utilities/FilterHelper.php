<?php

class FilterHelper extends FatUtility
{

    public const LAYOUT_DEFAULT = 1;
    public const LAYOUT_TOP = 2;
    
    public static function getLayouts(int $langId)
    {
        return [
            self::LAYOUT_DEFAULT => Labels::getLabel('LBL_Default', $langId),
            self::LAYOUT_TOP => Labels::getLabel('LBL_TOP', $langId)
        ];
    }

    public static function getSearchObj($langId, $headerFormParamsAssocArr)
    {
        $langId = FatUtility::int($langId);
        $post = FatApp::getPostedData();

        $prodSrchObj = new ProductSearch($langId);
        $prodSrchObj->joinSellerProductWithData($headerFormParamsAssocArr);
        $prodSrchObj->joinSellers();
        $prodSrchObj->joinShops($langId);
        $prodSrchObj->setGeoAddress();
        $prodSrchObj->joinBasedOnPriceConditionInnerQry('', $headerFormParamsAssocArr, true, true);
        $prodSrchObj->unsetDefaultLangForJoins();
        $prodSrchObj->joinShopCountry();
        $prodSrchObj->joinShopState();
        $prodSrchObj->joinBrands($langId);
        $prodSrchObj->joinProductToCategory($langId);
        $prodSrchObj->joinSellerSubscription(0, false, true);
        $prodSrchObj->addSubscriptionValidCondition();
        /* $prodSrchObj->validateAndJoinDeliveryLocation(); */
       
        /* $prodSrchObj->joinSellerProducts(0, '', $headerFormParamsAssocArr, true);
        $prodSrchObj->unsetDefaultLangForJoins();
        $prodSrchObj->joinSellers();
        $prodSrchObj->setGeoAddress();
        $prodSrchObj->joinShops($langId);
        $prodSrchObj->joinShopCountry();
        $prodSrchObj->joinShopState();
        $prodSrchObj->joinBrands($langId);
        $prodSrchObj->joinProductToCategory($langId);
        $prodSrchObj->joinSellerSubscription(0, false, true);
        $prodSrchObj->addSubscriptionValidCondition();
        $prodSrchObj->validateAndJoinDeliveryLocation(); */

        if (array_key_exists('category', $post)) {
            $prodSrchObj->addCategoryCondition($post['category']);
        }

        $shopId = FatApp::getPostedData('shop_id', FatUtility::VAR_INT, 0);
        if (0 < $shopId) {
            $prodSrchObj->addShopIdCondition($shopId);
        }

        $topProducts = FatApp::getPostedData('top_products', FatUtility::VAR_INT, 0);
        if (0 < $topProducts) {
            $prodSrchObj->joinProductRating();
            $prodSrchObj->addCondition('prod_rating', '>=', 'mysql_func_3', 'AND', true);
        }

        $brandId = FatApp::getPostedData('brand_id', FatUtility::VAR_INT, 0);
        if (0 < $brandId) {
            $prodSrchObj->addBrandCondition($brandId);
        }

        $featured = FatApp::getPostedData('featured', FatUtility::VAR_INT, 0);
        if (0 < $featured) {
            $prodSrchObj->addCondition('product_featured', '=', 'mysql_func_'. applicationConstants::YES, 'AND', true);
        }

        $keyword = '';
        if (array_key_exists('keyword', $headerFormParamsAssocArr) && !empty($headerFormParamsAssocArr['keyword'])) {
            $keyword = $headerFormParamsAssocArr['keyword'];
            $prodSrchObj->addKeywordSearch($keyword, false, false);
        }
        return $prodSrchObj;
    }

    public static function getParamsAssocArr()
    {
        $post = FatApp::getPostedData();

        $get = FatApp::getParameters();
        $headerFormParamsAssocArr = Product::convertArrToSrchFiltersAssocArr($get);
        return array_merge($headerFormParamsAssocArr, $post);
    }

    public static function getCacheKey($langId, $post)
    {
        $cacheKey = $langId;

        if (array_key_exists('category', $post)) {
            $cacheKey .= '-' . FatUtility::int($post['category']);
        }

        if (array_key_exists('shop_id', $post)) {
            $cacheKey .= '-' . $post['shop_id'];
        }

        if (array_key_exists('top_products', $post)) {
            $cacheKey .= '-tp';
        }

        if (array_key_exists('brand_id', $post)) {
            $cacheKey .= '-' . $post['brand_id'];
        }

        if (array_key_exists('featured', $post)) {
            $cacheKey .= '-f';
        }

        if (array_key_exists('keyword', $post) && !empty($post['keyword'])) {
            $cacheKey .= '-' . urlencode($post['keyword']);
        }

        return $cacheKey;
    }

    public static function selectedBrands($post)
    {
        if (array_key_exists('brand', $post)) {
            if (true === MOBILE_APP_API_CALL) {
                $post['brand'] = json_decode($post['brand'], true);
            }

            if (is_array($post['brand'])) {
                return $post['brand'];
            }

            return explode(',', $post['brand']);
        }
        return array();
    }

    public static function brands($prodSrchObj, $langId, $post, $doNotLimitRecord = false, $includePriority = false)
    {
        $brandId = 0;
        if (array_key_exists('brand_id', $post)) {
            $brandId = FatUtility::int($post['brand_id']);
        }

        $brandsCheckedArr = array();
        if (true == $includePriority) {
            $brandsCheckedArr = static::selectedBrands($post);
        }

        if (FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_FULL_TEXT_SEARCH, FatUtility::VAR_INT, 0)) {
            $pageSize = max(count($brandsCheckedArr), 10);

            $srch = FullTextSearch::getListingObj($post, $langId);
            $srch->setFields(array('brand.brand_id', 'brand.brand_name'));
            $srch->setPageNumber(0);
            $srch->setPageSize($pageSize);
            $srch->setSortFields(array('brand.brand_name.keyword' => array('order' => 'asc')));
            $srch->setGroupByField('brand.brand_name');
            return $srch->convertToSystemData($srch->fetch(), 'brand');
        }

        $brandSrch = clone $prodSrchObj;
        if (true == $doNotLimitRecord) {
            $brandSrch->doNotLimitRecords();
        } else {
            $pageSize = max(count($brandsCheckedArr), 10);
            $brandSrch->setPageSize($pageSize);
        }

        $brandSrch->joinBrandsLang($langId);
        $brandSrch->removGroupBy('product_id');
        $brandSrch->addGroupBy('brand.brand_id');
        $brandSrch->addFld(array('brand.brand_id', 'COALESCE(tb_l.brand_name,brand.brand_identifier) as brand_name'));
        if ($brandId) {
            $brandSrch->addCondition('brand_id', '=', 'mysql_func_'. $brandId, 'AND', true);
            $brandsCheckedArr = array($brandId);
        }

        if (!empty($brandsCheckedArr) && true == $includePriority) {
            $brandSrch->addFld('IF(FIND_IN_SET(brand.brand_id, "' . implode(',', $brandsCheckedArr) . '"), 1, 0) as priority');
            $brandSrch->addOrder('priority', 'desc');
        } else {
            $brandSrch->addFld('0 as priority');
        }
        $brandSrch->addOrder('tb_l.brand_name');
        $brandSrch->addCondition('brand_id', 'IS NOT', 'mysql_func_null', 'AND', true);
        /* if needs to show product counts under brands[ */
        //$brandSrch->addFld('count(selprod_id) as totalProducts');
        /* ] */
        $brandRs = $brandSrch->getResultSet();
        $brands = FatApp::getDb()->fetchAll($brandRs);
        
        if (count($brands) > 0 && !FatApp::getConfig('CONF_PRODUCT_BRAND_MANDATORY', FatUtility::VAR_INT, 1) && in_array(null, array_column($brands, 'brand_id'))) {
            array_push($brands, array(
                'brand_id' => '-1',
                'brand_name' => Labels::getLabel('LBL_Unbranded', CommonHelper::getLangId()),
                'priority' => 9999
            ));
            $brands = array_map('array_filter', $brands);
            $brands = array_values(array_filter($brands));
        }
        return $brands;
    }

    public static function getPrice(array $post, int $langId, array $productTypeCheckedArr)
    {
        if (FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_FULL_TEXT_SEARCH, FatUtility::VAR_INT, 0)) {
            $srch = FullTextSearch::getListingObj($post, $langId);
            $srch->setFields(array('aggregations'));
            $srch->setPageNumber(0);
            $srch->setPageSize(9999);
            $result = $srch->fetch(true);
            $priceArr = [
                'minPrice' => 0,
                'maxPrice' => 0
            ];

            if (is_array($result) && array_key_exists('aggregations', $result)) {
                $priceArr = [
                    'minPrice' => $result['aggregations']['min_price']['value'],
                    'maxPrice' => $result['aggregations']['max_price']['value']
                ];
            }
            return $priceArr;
        }

        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $post) && !empty($post['keyword'])) {
            $langIdForKeywordSeach = $langId;
        }

        unset($post['doNotJoinSpecialPrice']);
        $priceSrch = static::getSearchObj($langIdForKeywordSeach, $post);
        $priceKey = 'spd.sprodata_rental_price';
        if (in_array(Product::PRODUCT_FOR_SALE, $productTypeCheckedArr)) {
            $priceKey = 'sp.selprod_price';
        }
        $priceSrch->removGroupBy('product_id');
        $priceSrch->addFld(['MIN(COALESCE(tsp.splprice_price, '. $priceKey .')) AS minPrice', 'MAX(COALESCE(tsp.splprice_price, '. $priceKey .')) AS maxPrice']);
        $priceSrch->doNotLimitRecords();
        $priceSrch->doNotCalculateRecords();
        
        $priceSrch->addHaving('minPrice', 'IS NOT', 'mysql_func_null', 'and', true);
        $priceSrch->addHaving('maxPrice', 'IS NOT', 'mysql_func_null', 'and', true);
        $rs = $priceSrch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public static function getCategories($langId, $categoryId, $prodSrchObj, $cacheKey)
    {
        $cacheKey .= (true === MOBILE_APP_API_CALL) ? $cacheKey . '-m' : $cacheKey;
        $catFilter = FatCache::get('catFilter' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$catFilter) {
            $catSrch = clone $prodSrchObj;
            $catSrch->doNotLimitRecords();
            $catSrch->joinProductToCategoryLang($langId);
            $catSrch->removGroupBy('product_id');
            $catSrch->addGroupBy('c.prodcat_id');
            $excludeCatHavingNoProducts = true;
            if (!empty($keyword)) {
                $excludeCatHavingNoProducts = false;
            }
            $categoriesArr = ProductCategory::getTreeArr($langId, $categoryId, false, $catSrch, $excludeCatHavingNoProducts);
            $categoriesArr = (true === MOBILE_APP_API_CALL) ? array_values($categoriesArr) : $categoriesArr;
            FatCache::set('catFilter' . $cacheKey, serialize($categoriesArr), '.txt');
            return $categoriesArr;
        }
        return unserialize($catFilter);
    }

    public static function getOptions($langId, $categoryId, $prodSrchObj)
    {
        $options = FatCache::get('options' . $categoryId . '-' . $langId, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$options) {
            $options = array();
            if ($categoryId && ProductCategory::isLastChildCategory($categoryId)) {
                $selProdCodeSrch = clone $prodSrchObj;
                $selProdCodeSrch->removGroupBy('product_id');
                $selProdCodeSrch->doNotLimitRecords();
                /* Removed Group by as taking time for huge data. handled in fetch all second param */
                //$selProdCodeSrch->addGroupBy('selprod_code');
                $selProdCodeSrch->addMultipleFields(array('product_id', 'selprod_code'));
                $selProdCodeRs = $selProdCodeSrch->getResultSet();
                $selProdCodeArr = FatApp::getDb()->fetchAll($selProdCodeRs, 'selprod_code');

                if (!empty($selProdCodeArr)) {
                    foreach ($selProdCodeArr as $val) {
                        $optionsVal = SellerProduct::getSellerProductOptionsBySelProdCode($val['selprod_code'], $langId, true);
                        $options = $options + $optionsVal;
                    }
                }
            }

            usort(
                    $options, function ($a, $b) {
                if ($a['optionvalue_id'] == $b['optionvalue_id']) {
                    return 0;
                }
                return ($a['optionvalue_id'] < $b['optionvalue_id']) ? -1 : 1;
            }
            );
            FatCache::set('options ' . $categoryId . '-' . $langId, serialize($options), '.txt');
            return $options;
        }
        return unserialize($options);
    }

    public static function getPageSizeArr($langId)
    {
        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);

        $itemsTxt = Labels::getLabel('LBL_Items', $langId);

        $pageSizeArr[$pageSize] = Labels::getLabel('LBL_Default', $langId);
        $pageSizeArr[12] = 12 . ' ' . $itemsTxt;
        $pageSizeArr[24] = 24 . ' ' . $itemsTxt;
        $pageSizeArr[48] = 48 . ' ' . $itemsTxt;
        return $pageSizeArr;
    }

    public static function selectedAttributes(array $post): array
    {
        $attrCheckedArr = array();
        if (array_key_exists('attributes', $post)) {
            foreach ($post['attributes'] as $group => $attributes) {
                $attributesData = unserialize($attributes);
                foreach ($attributesData as $key => $attrValueArr) {
                    $keyArr = explode('_', $key);
                    foreach ($attrValueArr as $attrValue) {
                        $attrCheckedArr[] = $keyArr[1] . '_' . $keyArr[2] . '_' . $group . '_' . $attrValue;
                    }
                }
            }
        }
        return $attrCheckedArr;
    }

    public static function getprodTypes($langId, $prodSrchObj, $cacheKey)
    {
        $cacheKey .= (true === MOBILE_APP_API_CALL) ? $cacheKey . '-m' : $cacheKey;
        $prodTypeSrch = clone $prodSrchObj;
        $prodSrchObj->doNotLimitRecords();
        $prodTypeSrch->addMultipleFields(array('SUM(IF(sprodata_is_for_sell = 1, 1, 0)) AS sellProductCount', 'SUM(IF(sprodata_is_for_rent = 1, 1, 0)) AS rentProductCount'));
        $prodTypeSrch = $prodTypeSrch->getResultSet();

        $prodTypeArr = FatApp::getDb()->fetch($prodTypeSrch);
        $prodTypeArr = (true === MOBILE_APP_API_CALL) ? array_values($prodTypeArr) : $prodTypeArr;
        FatCache::set('productTypeFilter' . $cacheKey, serialize($prodTypeArr), '.txt');
        return $prodTypeArr;
    }

}
