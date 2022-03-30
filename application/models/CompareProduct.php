<?php
class CompareProduct extends FatModel
{
    const COMPARE_SESSION_ELEMENT_NAME = 'yorentCompareSession';
    const COMPARE_SESSION_URL_ELEMENT_NAME = 'yorentCompareSessionUrl';
    const COMPARE_PRODUCTS_LIMIT = 4;
    const COMPARE_PRODUCTS_APP_LIMIT = 3;

    public function getProdAttrGrpCatId($selProdId)
    {
        $srch = new SearchBase(SellerProduct::DB_TBL);
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'selprod_product_id = product_id');
        $srch->addCondition('selprod_id', '=', $selProdId);
        $srch->addMultipleFields(array('product_spec_cat_id'));
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetch($rs);
    }

    public function searchProducts($keyword,int $catId,int $langId = 1,$selprodArr=[])
    {
        if (0 > $catId || 0 > $langId) {
            return false;
        }

        $srch = new ProductSearch($langId);
        $srch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'INNER JOIN', 'tptc.ptc_product_id =  product_id', 'tptc');
        $srch->setDefinedCriteria(1);
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();
        $srch->addMultipleFields(array('product_id', 'selprod_id', 'selprod_price', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title','ifNULL(shop_name,shop_identifier)as shop_name'));
        $srch->addCondition('ptc_prodcat_id', '=', $catId);
        if (!empty($selprodArr) && 0 < count($selprodArr)) {
            $srch->addCondition('selprod_id', 'NOT IN', $selprodArr);
        }
        $srch->addDirectCondition('(selprod_title like "%' . urldecode($keyword) . '%" OR product_name like "%' . urldecode($keyword) . '%")');
        /* $srch->addGroupBy('product_id'); */
        $srch->addGroupBy('selprod_id');

        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs,'selprod_id');
        return $records;
    }

    public function productsDetail($productIds, $langId)
    {
        $langId = intval($langId);
        if (empty($productIds) || 1 > $langId) {
            return false;
        }

        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');

        $prodSrch = new ProductSearch($langId);
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinProductToCategory();
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinProductSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->doNotCalculateRecords();
        

        $prodSrch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN', 'sale_special_price.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN sale_special_price.splprice_start_date AND sale_special_price.splprice_end_date AND sale_special_price.splprice_type = ' . Product::PRODUCT_FOR_SALE, 'sale_special_price'
        );

        $prodSrch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'rent_special_price.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN rent_special_price.splprice_start_date AND rent_special_price.splprice_end_date AND rent_special_price.splprice_type = ' . Product::PRODUCT_FOR_RENT, 'rent_special_price'
        );
        
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'prodcat_comparison', 'COALESCE(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id', 'COALESCE(shop_name, shop_identifier) as shop_name',  'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled', 'selprod_available_from', 'selprod_min_order_qty', 'product_updated_on', 'product_warranty', 'selprod_return_age', 'selprod_cancellation_age', 'shop_return_age', 'shop_cancellation_age', 'selprod_fulfillment_type', 'shop_fulfillment_type', 'product_fulfillment_type', 'ptc_prodcat_id', 'sprodata_rental_security ', 'sprodata_rental_terms', 'sprodata_rental_stock', 'sprodata_rental_buffer_days', 'sprodata_minimum_rental_duration', 'selprod_product_id', 'sprodata_duration_type', 'selprod_cost', 'sprodata_minimum_rental_quantity', 'selprod_active', 'sprodata_rental_available_from', 'sprodata_rental_active', 'selprod_enable_rfq', 'prodcat_comparison', 'sprodata_fullfillment_type', 'sprodata_rental_price', 'COALESCE(sale_special_price.splprice_price, selprod_price) as theprice', 'COALESCE(rent_special_price.splprice_price, sprodata_rental_price) as rent_price','sprodata_is_for_rent','selprodRentalTerms','sprodata_is_for_sell', 'user_name', 'user_phone', 'user_country_id', 'user_state_id', 'user_city', 'selprod_avg_rating as prod_rating', 'selprod_review_count as totalReview', 'user_dial_code'
            )
        );

        $prodSrch->addCondition('selprod_id', 'IN', $productIds);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->doNotLimitRecords();
        $prodSrch->addGroupBy('selprod_id');


        $productRs = $prodSrch->getResultSet();
        return FatApp::getDb()->fetchAll($productRs, 'selprod_id');
    }

    public function attachedOptions($prodArr, $langId)
    {
        if (empty($prodArr)) {
            return false;
        }
        $optionSrch = new ProductSearch($langId);
        $optionSrch->setDefinedCriteria();
        $optionSrch->doNotCalculateRecords();
        $optionSrch->doNotLimitRecords();
        $optionSrch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrch->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        $optionSrch->addCondition('product_id', 'IN', $prodArr);
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $langId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'ifNULL(option_name, option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', 'IN', array_keys($prodArr));
        $optionSrch->addGroupBy('option_id');
        $optionRs = $optionSrch->getResultSet();
        return FatApp::getDb()->fetchAll($optionRs, 'option_id');
    }

    public function optionsValues($catalogIds, $optIdArr, $langId)
    {
        $optionValueSrch = new ProductSearch($langId);
        $optionValueSrch->setDefinedCriteria();
        $optionValueSrch->doNotCalculateRecords();
        $optionValueSrch->doNotLimitRecords();
        $optionValueSrch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionValueSrch->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionValueSrch->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        $optionValueSrch->addCondition('product_id', 'IN', $catalogIds);

        $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $langId, 'opval_l');
        $optionValueSrch->addCondition('product_id', 'IN', $catalogIds);
        $optionValueSrch->addCondition('option_id', 'IN', $optIdArr);
        $optionValueSrch->addMultipleFields(array('IFNULL(product_name, product_identifier) as product_name', 'product_id', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'ifNULL(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
        $optionValueSrch->addGroupBy('optionvalue_id, product_id');
        $optionValueRs = $optionValueSrch->getResultSet();
        return FatApp::getDb()->fetchAll($optionValueRs);
    }

    public function moreSellersProd($selProdArr, $selProdCodeArr, $langId)
    {
        $moreSellerSrch = new ProductSearch($langId);
        $moreSellerSrch->addMoreSellerCriteria($selProdCodeArr);
        $moreSellerSrch->addMultipleFields(array(
            'selprod_id', 'selprod_user_id', 'selprod_price', 'selprod_code', 'special_price_found', 'theprice', 'shop_id', 'shop_name', 'product_seller_id', 'product_id',
            'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city', 'selprod_cod_enabled', 'product_cod_enabled', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_min_order_qty', 'selprod_available_from','sprodata_rental_active','selprod_active','sprodata_rental_available_from'
        ));

        $moreSellerSrch->addCondition('selprod_id', 'NOT IN', $selProdArr);
        $moreSellerSrch->addOrder('theprice');
        $moreSellerSrch->addHaving('in_stock', '>', 0);
        $moreSellerSrch->addGroupBy('selprod_id');
        $moreSellerRs = $moreSellerSrch->getResultSet();
        $moreSellersArr = FatApp::getDb()->fetchAll($moreSellerRs);
        return $moreSellersArr;
    }

    public function getCatAttributes($attrCatId, $langId,$active=false)
    {
        $srch = AttrGroupAttribute::getSearchObject();
        $srch->joinTable(AttrGroupAttribute::DB_TBL . '_lang', 'LEFT JOIN', 'lang.attrlang_attr_id = ' . AttrGroupAttribute::DB_TBL_PREFIX . 'id AND attrlang_lang_id = ' . $langId, 'lang');
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT JOIN', 'attr_attrgrp_id = attrgrp_id', 'ag');
        $srch->joinTable(
            AttributeGroup::DB_LANG_TBL,
            'LEFT OUTER JOIN',
            'ag_l.' . AttributeGroup::DB_TBL_LANG_PREFIX . 'attrgrp_id = ag.' . AttributeGroup::tblFld('id') . ' and
			ag_l.' . AttributeGroup::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
            'ag_l'
        );
        $srch->addCondition('attr_prodcat_id', '=', $attrCatId);
        if($active) {
            $srch->addCondition('attr_active', '=', applicationConstants::ACTIVE);
        }
        $srch->addOrder('attr_display_order', 'ASC');
        return $srch;
    }

    public function getCompareProductsSpecifications($product_ids, $langId)
    {
        $langId = FatUtility::int($langId);
        if (empty($product_ids)) {
            return [];
        }
        $specSrchObj = new ProductSearch($langId);
        $specSrchObj->setDefinedCriteria();
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = ' . $langId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id', 'prodspec_name', 'prodspec_value', 'prodspec_is_file', 'prodspec_group', 'prodspec_product_id'));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addCondition('prodspec_product_id', 'IN', $product_ids);
        $specSrchObjRs = $specSrchObj->getResultSet();
        return FatApp::getDb()->fetchAll($specSrchObjRs);
    }
}
