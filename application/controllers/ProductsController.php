<?php

class ProductsController extends MyAppController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        //$this->productsData(__FUNCTION__);
        $this->featured();
    }

    public function search()
    {
        $post = (MOBILE_APP_API_CALL) ? FatApp::getPostedData() : [];
        $this->productsData(__FUNCTION__, true, $post);
    }
    
    public function featured()
    {
        $this->productsData(__FUNCTION__);
    }

    private function productsData($method, $validateBrand = false, $post = [])
    {
        $get = (!empty($post)) ? $post : Product::convertArrToSrchFiltersAssocArr(FatApp::getParameters());
        $postBrands = [];
        if (MOBILE_APP_API_CALL) {
            $postBrands = FatApp::getPostedData('brand', FatUtility::VAR_STRING, '[]');
            $postBrands = json_decode($postBrands, true);
        }

        $includeKeywordRelevancy = false;
        $keyword = '';
        if (array_key_exists('keyword', $get)) {
            $includeKeywordRelevancy = true;
            $keyword = trim($get['keyword']);
        }

        if ($validateBrand && array_key_exists('keyword', $get)) {
            $prodSrchObj = new ProductSearch(0);
            $prodSrchObj->addMultipleFields(array('brand_id', 'COALESCE(tb_l.brand_name, brand.brand_identifier) as brand_name'));
            $prodSrchObj->joinSellerProducts(0, '', ['doNotJoinSpecialPrice' => true], true);
            $prodSrchObj->joinSellers();
            $prodSrchObj->setGeoAddress();
            $prodSrchObj->joinShops();
            $prodSrchObj->validateAndJoinDeliveryLocation();
            $prodSrchObj->joinBrands();
            $prodSrchObj->joinBrandsLang($this->siteLangId, $keyword);
            $prodSrchObj->joinProductToCategory();
            $prodSrchObj->joinSellerSubscription(0, false, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->setPageSize(1);
            $prodSrchObj->doNotCalculateRecords();
            $prodSrchObj->addHaving('brand_name', 'like', $keyword);
            $brandRs = $prodSrchObj->getResultSet();
            $brandArr = FatApp::getDb()->fetchAllAssoc($brandRs);
            if (!empty($brandArr)) {
                $brands = array_keys($brandArr);
                $get['brand'] = !empty($postBrands) ? array_merge($brands, $postBrands) : $brands;
            }
        }
        $frm = $this->getProductSearchForm($includeKeywordRelevancy);

        $get['join_price'] = 1;

        $arr = array();

        switch ($method) {
            case 'index':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_All_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'index'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_PRODUCT_INDEX,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'allProducts'),
                );
                break;
            case 'search':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_Search_results_for', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'search'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_PRODUCT,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'searchListing'),
                    'keyword' => $keyword,
                );
                break;
            case 'featured':
                $arr = array(
                    'pageTitle' => Labels::getLabel('LBL_FEATURED_PRODUCTS', $this->siteLangId),
                    'canonicalUrl' => UrlHelper::generateFullUrl('Products', 'featured'),
                    'productSearchPageType' => SavedSearchProduct::PAGE_FEATURED_PRODUCT,
                    'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'searchListing'),
                );
                $get['featured'] = 1;
                break;
        }

        if (empty($get['producttype'])) {
            if (ALLOW_RENT) {
                $get['producttype'] = array(Product::PRODUCT_FOR_RENT);
            } else if (ALLOW_SALE) {
                $get['producttype'] = array(Product::PRODUCT_FOR_SALE);
            }
        }
        $get['vtype']  = (isset($get['vtype'])) ? $get['vtype'] : 'grid';

        $frm->fill($get);
        $data = $this->getListingData($get);

        if (array_key_exists('keyword', $get) && count($data['products'])) {
            $searchItemObj = new SearchItem();
            $searchData = array('keyword' => $get['keyword']);
            $searchItemObj->addSearchResult($searchData);
        }

        $compProdCount = 0;
        $comparedProdSpecCatId = 0;
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
            $comparedProdSpecCatId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
            $compProdCount = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
        }

        $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);
        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        if (array_key_exists('pageSize', $get)) {
            $pageSize = $get['pageSize'];
        }
        $common = [];
        if (false === MOBILE_APP_API_CALL) {
            $common = array(
                'frmProductSearch' => $frm,
                'recordId' => 0,
                'showBreadcrumb' => false,
                'compProdCount' => $compProdCount,
                'comparedProdSpecCatId' => $comparedProdSpecCatId,
                'pageSizeArr' => $pageSizeArr,
                'pageSize' => $pageSize
            );
        }

        $data = array_merge($data, $common, $arr);
        $banner = Banner::getBannerByLocationId(BannerLocation::PRODUCT_LISTING_PAGE_BANNER_LOCATION, $this->siteLangId);

        $displayProductNotAvailableLable = false;
        if (isset($_COOKIE['locationCheckoutType']) && FatUtility::int($_COOKIE['locationCheckoutType']) == Shipping::FULFILMENT_SHIP) {
            $displayProductNotAvailableLable = true;
        }

        if (FatUtility::isAjaxCall()) {
            $this->set('products', $data['products']);
            $this->set('moreSellersProductsArr', $data['moreSellersProductsArr']);
            $this->set('prodCatAttributes', $data['prodCatAttributes']);
            $this->set('prodCustomFldsData', $data['prodCustomFldsData']);
            $this->set('page', $data['page']);
            $this->set('pageSize', $data['pageSize']);
            $this->set('pageSizeArr', $data['pageSizeArr']);
            $this->set('pageCount', $data['pageCount']);
            $this->set('postedData', $get);
            $this->set('pageRecordCount', $data['pageRecordCount']);
            $this->set('recordCount', $data['recordCount']);
            $this->set('siteLangId', $this->siteLangId);
            $this->set('compProdCount', $compProdCount);
            $this->set('comparedProdSpecCatId', $comparedProdSpecCatId);
            $this->set('banner', $banner);
            echo $this->_template->render(false, false, 'products/products-list.php', true);
            exit;
        }
        $srchForm = Common::getSiteSearchForm();
        $srchForm->fill($get);
        $data['searchForm'] = $srchForm;
        $data['banner'] = $banner;
        $data['displayProductNotAvailableLable'] = $displayProductNotAvailableLable;
        $this->set('data', $data);
        $this->includeProductPageJsCss();
        $this->_template->addJs(['js/slick.min.js']);
        $this->_template->render(true, true, 'products/index.php');
    }

    private function getFilterSearchObj($langId, $headerFormParamsAssocArr)
    {
        return FilterHelper::getSearchObj($langId, $headerFormParamsAssocArr);
    }

    public function brandFilters()
    {
        $db = FatApp::getDb();
        $post = FilterHelper::getParamsAssocArr();

        $categoryId = 0;
        if (array_key_exists('category', $post)) {
            $categoryId = FatUtility::int($post['category']);
        }

        $keyword = '';
        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $post) && !empty($post['keyword'])) {
            $keyword = $post['keyword'];
            $langIdForKeywordSeach = $this->siteLangId;
        }

        $post['doNotJoinSpecialPrice'] = true;
        $prodSrchObj = $this->getFilterSearchObj($langIdForKeywordSeach, $post);
        $prodSrchObj->doNotCalculateRecords();

        $brandsCheckedArr = FilterHelper::selectedBrands($post);
        //$prodSrchObj->addFld('count(selprod_id) as totalProducts');
        $cacheKey = FilterHelper::getCacheKey($this->siteLangId, $post);

        $brandFilter = FatCache::get('brandFilter' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$brandFilter) {
            $brandsArr = FilterHelper::brands($prodSrchObj, $this->siteLangId, $post, true);
            FatCache::set('brandFilter' . $cacheKey, serialize($brandsArr), '.txt');
        } else {
            $brandsArr = unserialize($brandFilter);
        }

        $this->set('brandsArr', $brandsArr);
        $this->set('brandsCheckedArr', $brandsCheckedArr);

        echo $this->_template->render(false, false, 'products/brand-filters.php', true);
        exit;
    }

    public function filters()
    {
        $db = FatApp::getDb();
        $headerFormParamsAssocArr = FilterHelper::getParamsAssocArr();
        $headerFormParamsAssocArr['vtype']  = $headerFormParamsAssocArr['vtype'] ?? 'grid';
        $headerFormParamsAssocArr['selectedFulfillmentType'] = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;

        $categoryId = 0;
        if (array_key_exists('category', $headerFormParamsAssocArr)) {
            $categoryId = FatUtility::int($headerFormParamsAssocArr['category']);
        }
        if (!isset($headerFormParamsAssocArr['producttype'])) {
            $headerFormParamsAssocArr['producttype'] = (ALLOW_RENT) ? [Product::PRODUCT_FOR_RENT] : [Product::PRODUCT_FOR_SALE];
        }

        $keyword = '';
        $langIdForKeywordSeach = 0;
        if (array_key_exists('keyword', $headerFormParamsAssocArr) && !empty($headerFormParamsAssocArr['keyword'])) {
            $keyword = $headerFormParamsAssocArr['keyword'];
            $langIdForKeywordSeach = $this->siteLangId;
        }

        $cacheKey = FilterHelper::getCacheKey($this->siteLangId, $headerFormParamsAssocArr);

        $headerFormParamsAssocArr['doNotJoinSpecialPrice'] = true;
        $prodSrchObj = $this->getFilterSearchObj($langIdForKeywordSeach, $headerFormParamsAssocArr);
        $prodSrchObj->doNotCalculateRecords();

        /* Categories Data[ */
        $categoriesArr = array();
        $attrList = array();
        $attrCheckedArr = array();
        if (empty($keyword)) {
            $categoriesArr = FilterHelper::getCategories($this->siteLangId, $categoryId, $prodSrchObj, $cacheKey);
            if (0 < $categoryId) {
                $attrCheckedArr = FilterHelper::selectedAttributes($headerFormParamsAssocArr);
                $prodCatObj = new ProductCategory($categoryId);
                $attrList = $prodCatObj->getAttrDetail($this->siteLangId, [AttrGroupAttribute::ATTRTYPE_SELECT_BOX, AttrGroupAttribute::ATTRTYPE_CHECKBOXES]);
            }
        }
        $this->set('attrList', $attrList);
        $this->set('attrCheckedArr', $attrCheckedArr);
        /* ] */
        /* PRODUCT TYPE FILTERS [ */
        $productTypeCheckedArr = array();
        if (array_key_exists('producttype', $headerFormParamsAssocArr)) {
            if (true === MOBILE_APP_API_CALL) {
                $headerFormParamsAssocArr['producttype'] = json_decode($headerFormParamsAssocArr['producttype'], true);
            }
            $productTypeCheckedArr = $headerFormParamsAssocArr['producttype'];
        }

        if (empty($productTypeCheckedArr)) {
            if (ALLOW_RENT) {
                $productTypeCheckedArr = array(Product::PRODUCT_FOR_RENT);
            } else if (ALLOW_SALE) {
                $productTypeCheckedArr = array(Product::PRODUCT_FOR_SALE);
            }
        }

        /* ] */

        /* Brand Filters Data[ */
        $brandsCheckedArr = FilterHelper::selectedBrands($headerFormParamsAssocArr);
        $brandsArr = FilterHelper::brands($prodSrchObj, $this->siteLangId, $headerFormParamsAssocArr, false, true);
        /* ] */

        /* {Can modify the logic fetch data directly from query . will implement later}
          Option Filters Data[ */
        $options = FilterHelper::getOptions($this->siteLangId, $categoryId, $prodSrchObj);
        /* ] */


        /* Condition filters data[ */
        $conditionsArr = array();
        $conditions = FatCache::get('conditions' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$conditions) {
            $conditionArr = Product::getConditionArr($this->siteLangId);
            $conditions = array();
            foreach ($conditionArr as $key => $val) {
                $conditionSrch = clone $prodSrchObj;
                $conditionSrch->setPageSize(1);
                $conditionSrch->addMultipleFields(array('selprod_condition'));
                $conditionSrch->addCondition('selprod_condition', '=', $key);
                $conditionSrch->addCondition('selprod_condition', '!=', 0);
                $conditionRs = $conditionSrch->getResultSet();
                $conditionRow = $db->fetch($conditionRs);
                if (!empty($conditionRow)) {
                    $conditionsArr[] = $conditionRow;
                }
            }
            FatCache::set('conditions' . $cacheKey, serialize($conditionsArr), '.txt');
        } else {
            $conditionsArr = unserialize($conditions);
        }
        /* ] */

        /* Price Filters[ */
        $priceArr = FilterHelper::getPrice($headerFormParamsAssocArr, $this->siteLangId, $productTypeCheckedArr);
        $filterDefaultMinValue = 0;
        $filterDefaultMaxValue = 0;
        $priceInFilter = false;
        if (!empty($priceArr)) {
            $filterDefaultMinValue = (isset($priceArr['minPrice'])) ? $priceArr['minPrice'] : 0;
            $filterDefaultMaxValue = (isset($priceArr['maxPrice'])) ? $priceArr['maxPrice'] : 0;

            if ($this->siteCurrencyId != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1) || (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId)) {
                $filterDefaultMinValue = CommonHelper::displayMoneyFormat($priceArr['minPrice'], false, false, false);
                $filterDefaultMaxValue = CommonHelper::displayMoneyFormat($priceArr['maxPrice'], false, false, false);
                $priceArr['minPrice'] = $filterDefaultMinValue;
                $priceArr['maxPrice'] = $filterDefaultMaxValue;
            }

            if (array_key_exists('price-min-range', $headerFormParamsAssocArr) && array_key_exists('price-max-range', $headerFormParamsAssocArr)) {
                $priceArr['minPrice'] = $headerFormParamsAssocArr['price-min-range'];
                $priceArr['maxPrice'] = $headerFormParamsAssocArr['price-max-range'];
                $priceInFilter = true;
            }

            if (array_key_exists('currency_id', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['currency_id'] != $this->siteCurrencyId && array_key_exists('price-min-range', $headerFormParamsAssocArr) && array_key_exists('price-max-range', $headerFormParamsAssocArr)) {
                $priceArr['minPrice'] = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-min-range'], $this->siteCurrencyId, false);
                $priceArr['maxPrice'] = CommonHelper::convertExistingToOtherCurrency($headerFormParamsAssocArr['currency_id'], $headerFormParamsAssocArr['price-max-range'], $this->siteCurrencyId, false);
            }
        }

        /* ] */

        /* Availability Filters[ */
        $availabilities = FatCache::get('availabilities' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$availabilities) {
            $availabilitySrch = clone $prodSrchObj;
            $availabilitySrch->setPageSize(1);
            //$availabilitySrch->addGroupBy('in_stock');
            $availabilitySrch->addHaving('in_stock', '>', 0);
            $availabilitySrch->addMultipleFields(array('if(selprod_stock > 0,1,0) as in_stock'));
            $availabilityRs = $availabilitySrch->getResultSet();
            $availabilityArr = $db->fetchAll($availabilityRs, 'in_stock');
            FatCache::set('availabilities' . $cacheKey, serialize($availabilityArr), '.txt');
        } else {
            $availabilityArr = unserialize($availabilities);
        }
        /* ] */

        $optionValueCheckedArr = array();
        if (array_key_exists('optionvalue', $headerFormParamsAssocArr)) {
            $optionValueCheckedArr = $headerFormParamsAssocArr['optionvalue'];
        }

        $conditionsCheckedArr = array();
        if (array_key_exists('condition', $headerFormParamsAssocArr)) {
            $conditionsCheckedArr = $headerFormParamsAssocArr['condition'];
        }

        $availability = 0;
        if (array_key_exists('out_of_stock', $headerFormParamsAssocArr)) {
            $availability = $headerFormParamsAssocArr['out_of_stock'];
        }

        $productFiltersArr = array('count_for_view_more' => FatApp::getConfig('CONF_COUNT_FOR_VIEW_MORE', FatUtility::VAR_INT, 5));

        $prodcatArr = array();
        if (array_key_exists('prodcat', $headerFormParamsAssocArr)) {
            $prodcatArr = $headerFormParamsAssocArr['prodcat'];
        }

        $shopCatFilters = false;
        if (array_key_exists('shop_id', $headerFormParamsAssocArr)) {
            $shop_id = FatUtility::int($headerFormParamsAssocArr['shop_id']);
            $searchFrm = Shop::getFilterSearchForm();
            $searchFrm->fill($headerFormParamsAssocArr);
            $this->set('searchFrm', $searchFrm);
            if (0 < $shop_id) {
                $shopCatFilters = true;
            }
        }

        $this->set('productFiltersArr', $productFiltersArr);
        $this->set('headerFormParamsAssocArr', $headerFormParamsAssocArr);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('shopCatFilters', $shopCatFilters);
        $this->set('prodcatArr', $prodcatArr);
        // $this->set('productCategories',$productCategories);
        $this->set('brandsArr', $brandsArr);
        $this->set('brandsCheckedArr', $brandsCheckedArr);
        $this->set('optionValueCheckedArr', $optionValueCheckedArr);
        $this->set('conditionsArr', $conditionsArr);
        $this->set('conditionsCheckedArr', $conditionsCheckedArr);
        $this->set('options', $options);
        $this->set('priceArr', $priceArr);
        $this->set('priceInFilter', $priceInFilter);
        $this->set('filterDefaultMinValue', $filterDefaultMinValue);
        $this->set('filterDefaultMaxValue', $filterDefaultMaxValue);
        $this->set('availability', $availability);
        $availabilityArr = (true === MOBILE_APP_API_CALL) ? array_values($availabilityArr) : $availabilityArr;
        $this->set('availabilityArr', $availabilityArr);
        $this->set('layoutDirection', CommonHelper::getLayoutDirection());
        $this->set('productTypeCheckedArr', $productTypeCheckedArr);
        $this->set('rentalStartDate', (isset($headerFormParamsAssocArr['rentalstart'])) ? $headerFormParamsAssocArr['rentalstart'] : "");
        $this->set('rentalEndDate', (isset($headerFormParamsAssocArr['rentalend'])) ? $headerFormParamsAssocArr['rentalend'] : "");
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $templateName = 'filters.php';
        if (array_key_exists('vtype', $headerFormParamsAssocArr) && $headerFormParamsAssocArr['vtype'] == 'map') {
            $templateName = 'filters-top.php';
        }

        echo $this->_template->render(false, false, 'products/' . $templateName, true);
        exit;
    }

    public function view(int $selprod_id = 0, $oprID = 0)
    {
        if (true === MOBILE_APP_API_CALL && 1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $loggedUserId = 0;
        $extendQuantity = 1;
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');

        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $oprID = FatUtility::int($oprID);
        if ($oprID > 0) {
            if (!UserAuthentication::isUserLogged()) {
                FatUtility::exitWithErrorCode(404);
            }
            $orderInfo = $this->getOrderDetailsForExtendRental($selprod_id, $oprID);
            if (empty($orderInfo)) {
                FatUtility::exitWithErrorCode(404);
            }
            $extendQuantity = $orderInfo['op_qty'];
        }

        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        /* $prodSrch->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice' => true), false); */
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinProductToCategory();
        $prodSrch->joinShopSpecifics();
        $prodSrch->joinProductSpecifics();
        $prodSrch->joinSellerProductSpecifics();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $prodSrch->doNotLimitRecords();

        $prodSrch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE,
            'LEFT OUTER JOIN', 'sale_special_price.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN sale_special_price.splprice_start_date AND sale_special_price.splprice_end_date AND sale_special_price.splprice_type = ' . Product::PRODUCT_FOR_SALE, 'sale_special_price'
        );

        $prodSrch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'rent_special_price.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN rent_special_price.splprice_start_date AND rent_special_price.splprice_end_date AND rent_special_price.splprice_type = ' . Product::PRODUCT_FOR_RENT, 'rent_special_price'
        );
        
        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;

        if ($favVar == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinProducts($this->siteLangId);
        $selProdReviewObj->joinSellerProducts($this->siteLangId);
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->joinUser();
        // $selProdReviewObj->joinSelProdReviewHelpful();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        // $selProdReviewObj->addGroupBy('sprh_spreview_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spr.spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $prodSrch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'prodcat_comparison', 'COALESCE(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id', 'COALESCE(shop_name, shop_identifier) as shop_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'COALESCE(sq_sprating.totReviews,0) totReviews', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled', 'selprod_available_from', 'selprod_min_order_qty', 'product_updated_on', 'product_warranty', 'selprod_return_age', 'selprod_cancellation_age', 'shop_return_age', 'shop_cancellation_age', 'selprod_fulfillment_type', 'shop_fulfillment_type', 'product_fulfillment_type', 'ptc_prodcat_id', 'sprodata_rental_security ', 'sprodata_rental_terms', 'sprodata_rental_stock', 'sprodata_rental_buffer_days', 'sprodata_minimum_rental_duration', 'selprod_product_id', 'sprodata_duration_type', 'selprod_cost', 'sprodata_minimum_rental_quantity', 'selprod_active', 'sprodata_rental_available_from', 'sprodata_rental_active', 'selprod_enable_rfq', 'prodcat_comparison', 'sprodata_fullfillment_type', 'sprodata_rental_price', 'COALESCE(sale_special_price.splprice_price, selprod_price) as theprice', 'COALESCE(rent_special_price.splprice_price, sprodata_rental_price) as rent_price'
            )
        );

        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
            FatUtility::exitWithErrorCode(404);
        }
        $cartObj = new Cart();
        $this->set('cartType', $cartObj->getCartType());

        /* [ CHECK AND GET SIZE CHART */
        $pObj = new Product($product['product_id']);
        $product['size_chart'] = [];
        if ($pObj->checkOptionWithSizeChart()) {
            $productSizeChart = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $product['product_id'], 0, $this->siteLangId, true, 0, 0, true);
            $product['size_chart'] = $productSizeChart;
        }
        /* ] */

        /* over all catalog product reviews */
        $selProdReviewObj->addCondition('spreview_product_id', '=', $product['product_id']);
        $selProdReviewObj->addMultipleFields(array('count(spreview_postedby_user_id) totReviews', 'sum(if(sprating_rating=1,1,0)) rated_1', 'sum(if(sprating_rating=2,1,0)) rated_2', 'sum(if(sprating_rating=3,1,0)) rated_3', 'sum(if(sprating_rating=4,1,0)) rated_4', 'sum(if(sprating_rating=5,1,0)) rated_5'));

        $reviews = FatApp::getDb()->fetch($selProdReviewObj->getResultSet());
        /* CommonHelper::printArray($reviews); die; */
        $this->set('reviews', $reviews);
        $subscription = false;
        $allowed_images = -1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $product['selprod_user_id'], array('ossubs_images_allowed'));
            $allowed_images = $currentPlanData['ossubs_images_allowed'];
            $subscription = true;
        }

        /* Current Product option Values[ */
        $options = SellerProduct::getSellerProductOptions($selprod_id, false);
        $productSelectedOptionValues = array();
        $productGroupImages = array();
        $productImagesArr = array();

        if (count($options) > 0) {
            foreach ($options as $op) {
                /* Product UPC code [ */
                $product['product_upc'] = UpcCode::getUpcCode($product['product_id'], $op['selprodoption_optionvalue_id']);
                /* ] */
                $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], $op['selprodoption_optionvalue_id'], $this->siteLangId, true, '', $allowed_images);
                if ($images) {
                    $productImagesArr += $images;
                }
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        if (count($productImagesArr) > 0) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        $product['selectedOptionValues'] = $productSelectedOptionValues;
        /* ] */

        if (isset($allowed_images) && $allowed_images > 0) {
            $universal_allowed_images_count = $allowed_images - count($productImagesArr);
        }

        $productUniversalImagesArr = array();
        if (empty($productGroupImages) || !$subscription || isset($universal_allowed_images_count)) {
            $universalImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true, '');
            /* CommonHelper::printArray($universalImages); die; */
            if ($universalImages) {
                if (isset($universal_allowed_images_count)) {
                    $images = array_slice($universalImages, 0, $universal_allowed_images_count);
                    $productUniversalImagesArr = $images;
                    $universal_allowed_images_count = $universal_allowed_images_count - count($productUniversalImagesArr);
                } elseif (!$subscription) {
                    $productUniversalImagesArr = $universalImages;
                }
            }
        }

        if ($productUniversalImagesArr) {
            foreach ($productUniversalImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        //abled and Get Shipping Rates [*/
        $codEnabled = false;
        if (Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id'])) {
            $walletBalance = User::getUserBalance($product['selprod_user_id']);
            if ($product['selprod_cod_enabled']) {
                $codEnabled = true;
            }
            $codMinWalletBalance = -1;
            $shop_cod_min_wallet_balance = Shop::getAttributesByUserId($product['selprod_user_id'], 'shop_cod_min_wallet_balance');
            if ($shop_cod_min_wallet_balance > -1) {
                $codMinWalletBalance = $shop_cod_min_wallet_balance;
            } elseif (FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1) > -1) {
                $codMinWalletBalance = FatApp::getConfig('CONF_COD_MIN_WALLET_BALANCE', FatUtility::VAR_FLOAT, -1);
            }
            if ($codMinWalletBalance > -1 && $codMinWalletBalance > $walletBalance) {
                $codEnabled = false;
            }
        } else {
            if ($product['product_cod_enabled']) {
                $codEnabled = true;
            }
        }

        $isProductShippedBySeller = Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id']);
        $fulfillmentType = $product['sprodata_fullfillment_type'];
        $fulfillmentTypeSale = $product['selprod_fulfillment_type'];
        if ($product['shop_fulfillment_type'] != Shipping::FULFILMENT_ALL) {
            $fulfillmentType = $product['shop_fulfillment_type'];
            $product['sprodata_fullfillment_type'] = $fulfillmentType;
            $fulfillmentTypeSale = $product['shop_fulfillment_type'];
            $product['selprod_fulfillment_type'] = $fulfillmentTypeSale;
        }
        if (!$isProductShippedBySeller) {
            $fulfillmentTypeSale = $product['product_fulfillment_type'];
        }
        
        $shippingRateRow = Product::getProductShippingRatesByAddress($product['selprod_id']);
        $minShipDuration = (!empty($shippingRateRow)) ? $shippingRateRow['minimum_shipping_duration'] : 1;
        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('fulfillmentTypeSale', $fulfillmentTypeSale);
        $this->set('codEnabled', $codEnabled);
        $this->set('shippingRateRow', $shippingRateRow);
        $this->set('minShipDuration', $minShipDuration);
        /* ] */

        $sellerId = (false === MOBILE_APP_API_CALL) ? $product['selprod_user_id'] : 0;
        $product['moreSellersArr'] = Product::getMoreSeller($product['selprod_code'], $this->siteLangId, $sellerId);
        $product['selprod_return_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId);
        $product['selprod_warranty_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);
        /* Form buy product[ */
        $productFor = applicationConstants::PRODUCT_FOR_SALE;
        $isRent = /* $product['is_rent'] */ 1; // NEED TO UPDATE
        $unavailableDates = array();
        $extendedOrderData = array();
        $rentalAvailableDate = '';
        if ($isRent > 0 && ALLOW_RENT > 0) {
            $productFor = applicationConstants::PRODUCT_FOR_RENT;
            if ($oprID > 0) {
                $extendedOrderData = OrderProductData::getOrderProductData($oprID);
                if (!empty($extendedOrderData)) {
                    $rentalAvailableDate = $extendedOrderData['opd_rental_end_date'];
                }
            }

            if (strtotime($product['selprod_available_from']) < time()) {
                $startDate = date('Y-m-d');
            } else {
                $startDate = date('Y-m-d', strtotime($product['selprod_available_from']));
            }
            $endDate = date('Y-m-d', strtotime('+6 month'));
            $productOrderData = OrderProductData::getProductOrders($selprod_id, $startDate, $endDate, $product['sprodata_rental_buffer_days']);
            if (!empty($productOrderData)) {
                $unavailableDates = ProductRental::prodDisableDates($productOrderData, $product['sprodata_rental_stock'], $product['sprodata_rental_buffer_days'], 0, $minShipDuration);
            }
        }
        if (1 > $oprID) {
            $extendQuantity =  min($product['selprod_min_order_qty'], $product['sprodata_minimum_rental_quantity']);
        }

        $frm = $this->getCartForm($this->siteLangId, $isRent, $extendQuantity);
        $frm->fill(array('selprod_id' => $selprod_id, 'product_for' => $productFor, 'extend_order' => $oprID, 'rental_start_date' => $rentalAvailableDate, 'fulfillmentType' => $fulfillmentType));
        $this->set('frmBuyProduct', $frm);
        $this->set('unavailableDates', $unavailableDates);
        $this->set('extendedOrderData', $extendedOrderData);
        $this->set('orderProdId', $oprID);
        /* ] */

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->doNotCalculateRecords();
        $optionSrchObj->doNotLimitRecords();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
            $optionSrchObj->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type=' . ORDERS::ORDER_SUBSCRIPTION . ' AND o.order_payment_status =1', 'o');
            $optionSrchObj->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
        }
        $optionSrchObj->addCondition('product_id', '=', $product['product_id']);

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selprod_id);
        $optionSrch->addGroupBy('option_id');

        //echo $optionSrch->getQuery(); die();
        $optionRs = $optionSrch->getResultSet();
        if (true === MOBILE_APP_API_CALL) {
            $optionRows = FatApp::getDb()->fetchAll($optionRs);
        } else {
            $optionRows = FatApp::getDb()->fetchAll($optionRs, 'option_id');
        }
        if (count($optionRows) > 0) {
            $optionArr = [];
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $product['product_id']);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                if (!FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                    $optionValueSrch->addGroupBy('optionvalue_id');
                }
                $optionValueSrch->addGroupBy('selprod_code');
                $optionValueRs = $optionValueSrch->getResultSet();
                if (true === MOBILE_APP_API_CALL || FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs);
                    $optionArr = array_merge($optionArr, $optionValueRows);
                } else {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs, 'optionvalue_id');
                }
                $option['values'] = $optionValueRows;
            }

            /* [ GROUP OPTION BY SELPROD ID */
            if (FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                $optionsFinalArr = [];
                foreach ($optionArr as $opRow) {
                    $sId = $opRow['selprod_id'];
                    if ($opRow['selprod_code'] == $product['selprod_code']) {
                        $sId = $product['selprod_id'];
                    }
                    if (trim($opRow['optionvalue_color_code']) != '' && substr($opRow['optionvalue_color_code'], 0, 1) != "#") {
                        $opRow['optionvalue_color_code'] = '#' . $opRow['optionvalue_color_code'];
                    }

                    if (isset($optionsFinalArr[$sId])) {
                        $value = $optionsFinalArr[$sId]['value'] . ' | ' . $opRow['optionvalue_name'];
                        $colorCode = ($optionsFinalArr[$sId]['color_code'] != '') ? $optionsFinalArr[$sId]['color_code'] : $opRow['optionvalue_color_code'];
                    } else {
                        $value = $opRow['optionvalue_name'];
                        $colorCode  = $opRow['optionvalue_color_code'];
                    }
                    /* $optionsFinalArr[$sId] = $value; */
                    $optionsFinalArr[$sId] = array('value' => $value, 'color_code' => $colorCode);
                }
                $this->set('optionsFinalArr', $optionsFinalArr);
            }
            /* ] */
        }

        $this->set('optionRows', $optionRows);
        $sellerProduct = new SellerProduct($selprod_id);
        $criteria = array('selprod.selprod_id as selprod_id');

        $upsellProducts = $sellerProduct->getUpsellProducts($product['selprod_id'], $this->siteLangId, $loggedUserId);
        $relatedProducts = $sellerProduct->getRelatedProducts($this->siteLangId, $product['selprod_id'], $criteria);
        $relatedProductsRs = $this->relatedProductsById(array_keys($relatedProducts));

        $srch = new ShopSearch($this->siteLangId);
        $srch->setDefinedCriteria($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(
            array('shop_id', 'shop_user_id', 'shop_ltemplate_id', 'shop_created_on', 'COALESCE(shop_name, shop_identifier) as shop_name', 'shop_description', 'shop_payment_policy', 'shop_delivery_policy', 'shop_refund_policy', 'COALESCE(shop_country_l.country_name,shop_country.country_code) as shop_country_name', 'COALESCE(shop_state_l.state_name,state_identifier) as shop_state_name', 'shop_city'/* , 'shop_free_ship_upto' */)
        );
        $srch->addCondition('shop_id', '=', $product['shop_id']);
        $shopRs = $srch->getResultSet();
        $shop = FatApp::getDb()->fetch($shopRs);

        $shop_rating = 0;
        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
            $shop_rating = SelProdRating::getSellerRating($shop['shop_user_id']);
        }

        /*   [ Promotional Banner   */
        $banners = BannerLocation::getPromotionalBanners(0, $this->siteLangId);
        /* End of Prmotional Banner  ] */

        /* Get Product Specifications */
        $allSpecifications = $this->getProductSpecifications($product['product_id'], $this->siteLangId);
        $this->set('productSpecifications', $allSpecifications['textSpecifications']);
        $this->set('productFileSpecifications', $allSpecifications['fileSpecifications']);
        /* End of Product Specifications */

        $canSubmitFeedback = false;
        if ($loggedUserId) {
            $canSubmitFeedback = true;
            $orderProduct = SelProdReview::getProductOrderId($product['product_id'], $loggedUserId);
            if (empty($orderProduct) || (isset($orderProduct['op_order_id']) && !Orders::canSubmitFeedback($loggedUserId, $orderProduct['op_order_id'], $selprod_id))) {
                $canSubmitFeedback = false;
            }
        }

        $displayProductNotAvailableLable = false;
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $displayProductNotAvailableLable = true;
        }

        /* Get Product Custom Fields */
        $prodCatAttributes = $prodCustomFldsData = array();
        if (0 < $product['ptc_prodcat_id']) {
            $prodCatObj = new ProductCategory($product['ptc_prodcat_id']);
            $prodCatAttr = $prodCatObj->getAttrDetail($this->siteLangId, 0, 'attr_attrgrp_id');
            $prodCatAttributes = $this->formatAttributes($prodCatAttr);
            $prodCustomFldsData = $this->getCustomFields($product['product_id']);
        }

        $compProdCount = 0;
        $prodInCompList = 0;
        $comparedProdSpecCatId = 0;
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
            $compProdCount = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
            $comparedProdSpecCatId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
            if (array_key_exists($selprod_id, $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
                $prodInCompList = 1;
            }
        }

        $ratingAspects = SelProdRating::getAvgSelProdReviewsRating($product['product_id'], $this->siteLangId);

        $this->set('ratingAspects', $ratingAspects);
        $this->set('compProdCount', $compProdCount);
        $this->set('comparedProdSpecCatId', $comparedProdSpecCatId);
        $this->set('prodInCompList', $prodInCompList);
        $this->set('attributes', $prodCatAttributes);
        $this->set('productCustomFields', $prodCustomFldsData);
        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('canSubmitFeedback', $canSubmitFeedback);
        $this->set('upsellProducts', !empty($upsellProducts) ? $upsellProducts : array());
        $this->set('relatedProductsRs', !empty($relatedProductsRs) ? $relatedProductsRs : array());
        $this->set('banners', $banners);
        $this->set('product', $product);
        $this->set('shop_rating', $shop_rating);
        $this->set('shop', $shop);
        $this->set('shopTotalReviews', SelProdReview::getSellerTotalReviews($shop['shop_user_id']));
        $this->set('productImagesArr', $productGroupImages);
        $frmReviewSearch = $this->getReviewSearchForm(5);
        $frmReviewSearch->fill(array('selprod_id' => $selprod_id));
        $this->set('frmReviewSearch', $frmReviewSearch);

        /* Get Product Volume Discount (if any)[ */
        $this->set('volumeDiscountRows', $sellerProduct->getVolumeDiscounts());
        /* ] */

        $rentProdobj = new ProductRental($selprod_id);
        $this->set('durationDiscountRows', $rentProdobj->getDurationDiscounts());

        if (!empty($product)) {
            $afile_id = !empty($productGroupImages) ? array_keys($productGroupImages)[0] : 0;
            $this->set('socialShareContent', $this->getOgTags($product, $afile_id));
        }

        /* Recommnended Products [ */
        /*$recommendedProducts = $this->getRecommendedProducts($selprod_id, $this->siteLangId, $loggedUserId);*/
        $this->set('recommendedProducts', array());
        /* ]  */

        if (FatApp::getConfig('CONF_DISPLAY_RECENT_VIEW_PRODUCTS', FatUtility::VAR_INT, 0)) {
            $this->setRecentlyViewedItem($selprod_id);
        }

        if (false === MOBILE_APP_API_CALL) {
            $this->_template->addJs(array('js/slick.js', 'js/modaal.js', 'js/product-detail.js', 'js/xzoom.js', 'js/magnific-popup.js'));
        } else if (FatApp::getConfig('CONF_DISPLAY_RECENT_VIEW_PRODUCTS', FatUtility::VAR_INT, 0)) { //@todo
            $recentlyViewed = FatApp::getPostedData('recentlyViewed');
            $recentlyViewed = is_array($recentlyViewed) && 0 < count($recentlyViewed) ? FatUtility::int($recentlyViewed) : array();
            if (in_array($selprod_id, $recentlyViewed)) {
                unset($recentlyViewed[$selprod_id]);
            }
            $recentlyViewed = $this->getRecentlyViewedProductsDetail($recentlyViewed);
            $this->set('recentlyViewed', $recentlyViewed);
        }

        $this->set('rentalTypeArr', ProductRental::durationTypeArr($this->siteLangId));
        /* $this->set('addonProducts', $sellerProduct->getAddonProducts($this->siteLangId)); */

        $addonProducts = [];
        if (FatApp::getConfig('CONF_ALLOW_RENTAL_SERVICES', FatUtility::VAR_INT, 0) == 1) {
            $addonProducts = $sellerProduct->getAddonProducts($this->siteLangId);
        }
        $this->set('addonProducts', $addonProducts);
        $this->set('searchForm', Common::getSiteSearchForm());
        if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) {
            $this->set('orderCancelPenaltyRules', OrderCancelRule::getOrderCancelRules($product['selprod_user_id'], true));
        }
        $this->_template->render();
    }

    private function getProductSpecifications($product_id, $langId)
    {
        $product_id = FatUtility::int($product_id);
        $langId = FatUtility::int($langId);
        if (1 > $product_id) {
            return array();
        }
        $specSrchObj = new ProductSearch($langId);
        $specSrchObj->setDefinedCriteria();
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = ' . $langId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id', 'prodspec_name', 'prodspec_value', 'prodspec_is_file '));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addCondition('prodspec_product_id', '=', $product_id);
        $specSrchObjRs = $specSrchObj->getResultSet();
        $rows = FatApp::getDb()->fetchAll($specSrchObjRs);
        if (empty($rows)) {
            return  ['fileSpecifications' => [], 'textSpecifications' => []];
        }
        $specicationGroupArr = [
            'fileSpecifications' => [],
            'textSpecifications' => []
        ];

        foreach ($rows as $key => $row) {
            if ($row['prodspec_is_file'] == 1) {
                $specicationGroupArr['fileSpecifications'][] = $row;
            } else {
                $specicationGroupArr['textSpecifications'][] = $row;
            }
        }
        return $specicationGroupArr;
    }

    private function setRecentlyViewedItem($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            return;
        }

        $recentProductsArr = array();
        if (!isset($_COOKIE['recentViewedProducts'])) {
            setcookie('recentViewedProducts', $selprod_id . '_', time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        } else {
            $recentProducts = $_COOKIE['recentViewedProducts'];
            $recentProductsArr = explode('_', $recentProducts);
        }

        $products = array();

        if (is_array($recentProductsArr) && !in_array($selprod_id, $recentProductsArr)) {
            if (count($recentProductsArr) >= 10) {
                $recentProductsArr = array_reverse($recentProductsArr);
                array_pop($recentProductsArr);
                $recentProductsArr = array_reverse($recentProductsArr);
            }

            foreach ($recentProductsArr as $val) {
                if ($val == '') {
                    continue;
                }
                array_push($products, $val);
            }
            array_push($products, $selprod_id);
            setcookie('recentViewedProducts', implode('_', $products), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        }
    }

    private function getRecommendedProducts($selprod_id, $langId, $userId = 0)
    {
        $selprod_id = FatUtility::int($selprod_id);
        if (1 > $selprod_id) {
            return;
        }
        $productId = SellerProduct::getAttributesById($selprod_id, 'selprod_product_id', false);

        $srch = new ProductSearch($langId);
        $join_price = 1;
        $srch->setGeoAddress();
        $srch->setDefinedCriteria($join_price);
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription();
        $srch->addSubscriptionValidCondition();
        $srch->validateAndJoinDeliveryLocation();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addMultipleFields(
            array(
                'product_id', 'prodcat_id', 'prodcat_comparison', 'substring_index(group_concat(COALESCE(prodcat_name, prodcat_identifier) ORDER BY COALESCE(prodcat_name, prodcat_identifier) ASC SEPARATOR "," ) , ",", 1) as prodcat_name', 'COALESCE(product_name, product_identifier) as product_name', 'product_model', 'product_short_description', 'product_updated_on',
                'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_stock', 'selprod_condition', 'selprod_price', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                'theprice', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name',
                'IF(selprod_stock > 0, 1, 0) AS in_stock', 'selprod_sold_count', 'selprod_return_policy',
                'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'sprodata_rental_price as rent_price', 'sprodata_rental_stock', 'sprodata_rental_active', 'selprod_active', 'sprodata_duration_type', 'IFNULL(brand_name, brand_identifier) as brand_name', 'product_model'
            )
        );

        $dateToEquate = date('Y-m-d');

        $subSrch1 = new SearchBase('tbl_product_product_recommendation', 'ppr');
        $subSrch1->addMultipleFields(array('ppr_recommended_product_id as rec_product_id', 'ppr_weightage as weightage'));
        $subSrch1->addCondition('ppr_viewing_product_id', '=', $productId);
        $subSrch1->addOrder('weightage', 'desc');
        $subSrch1->doNotCalculateRecords();
        $subSrch1->setPageSize(5);

        $subSrch2 = new SearchBase(Product::DB_PRODUCT_TO_TAG, 'ptt');
        $subSrch2->joinTable('tbl_tag_product_recommendation', 'INNER JOIN', 'tpr.tpr_tag_id = ptt.ptt_tag_id', 'tpr');
        $subSrch2->addMultipleFields(array('tpr_product_id  as rec_product_id', 'if(tpr_custom_weightage_valid_till <= ' . $dateToEquate . ', tpr_custom_weightage+tpr_weightage , tpr_weightage) as weightage'));
        $subSrch2->addOrder('weightage', 'desc');
        $subSrch2->doNotCalculateRecords();
        $subSrch2->setPageSize(5);

        $recommendedProductsQuery = '(' . $subSrch1->getQuery() . ') union (' . $subSrch2->getQuery() . ')';
        if (0 < $userId) {
            $subSrch3 = new SearchBase('tbl_user_product_recommendation', 'upr');
            $subSrch3->addMultipleFields(array('upr_product_id as rec_product_id', 'upr_weightage as weightage'));
            $subSrch3->addOrder('weightage', 'desc');
            $subSrch3->addCondition('upr_user_id', '=', $userId);
            $subSrch3->doNotCalculateRecords();
            $subSrch3->setPageSize(5);
            $recommendedProductsQuery .= ' union (' . $subSrch3->getQuery() . ')';
        }

        $rs = FatApp::getDb()->query('select rec_product_id , weightage from (' . $recommendedProductsQuery . ') as temp order by weightage desc');
        $recommendedProds = FatApp::getDb()->fetchAllAssoc($rs);
        if (empty($recommendedProds)) {
            return array();
        }

        $srch->addGroupBy('product_id');
        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if ($favVar == applicationConstants::NO) {
            $srch->joinFavouriteProducts($userId);
            $srch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $srch->joinUserWishListProducts($userId);
            $srch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $srch->addCondition('selprod_id', '!=', $selprod_id);
        $srch->addCondition('product_id', 'in', array_keys($recommendedProds));
        $srch->setPageSize(5);
        $srch->doNotCalculateRecords();

        $recommendedProducts = FatApp::getDb()->fetchAll($srch->getResultSet());
        return $recommendedProducts;
    }

    private function getOgTags($product = array(), $afile_id = 0)
    {
        if (empty($product)) {
            return array();
        }
        $afile_id = FatUtility::int($afile_id);
        $title = $product['product_name'];

        if ($product['selprod_title']) {
            $title = $product['selprod_title'];
        }

        $product_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($product["product_description"], true)), 500));
        $product_description .= ' - ' . Labels::getLabel('LBL_See_more_at', $this->siteLangId) . ": " . UrlHelper::getCurrUrl();

        $productImageUrl = '';
        /* $productImageUrl = UrlHelper::generateFullUrl('Image','product', array($product['product_id'],'', $product['selprod_id'],0,$this->siteLangId )); */
        if (0 < $afile_id) {
            $productImageUrl = UrlHelper::generateFullUrl('Image', 'product', array($product['product_id'], 'FB_RECOMMEND', 0, $afile_id));
        }
        $socialShareContent = array(
            'type' => 'Product',
            'title' => $title,
            'description' => $product_description,
            'image' => $productImageUrl,
        );
        return $socialShareContent;
    }

    public function getProductShippingRates()
    {
        $post = FatApp::getPostedData();
        $productId = $post['productId'];
        $sellerId = $post['sellerId'];
    }

    private function getRecentlyViewedProductsDetail($cookiesProductsArr = array())
    {
        if (1 > count($cookiesProductsArr)) {
            return $cookiesProductsArr;
        }

        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation();
        $prodSrch->joinProductToCategory();
        $prodSrch->doNotCalculateRecords();
        $prodSrch->doNotLimitRecords();
        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if ($favVar == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        $prodSrch->addCondition('selprod_id', 'IN', $cookiesProductsArr);
        $prodSrch->addMultipleFields(
            array(
                'product_id', 'COALESCE(product_name, product_identifier) as product_name', 'prodcat_id', 'prodcat_comparison', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_updated_on',
                'selprod_id', 'selprod_condition', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'theprice',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_sold_count', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'selprod_price',
                'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'sprodata_rental_price as rent_price', 'sprodata_rental_stock', 'sprodata_rental_active', 'selprod_active', 'sprodata_duration_type', 'IFNULL(brand_name, brand_identifier) as brand_name', 'product_model'
            )
        );
        $productRs = $prodSrch->getResultSet();
        $recentViewedProducts = FatApp::getDb()->fetchAll($productRs, 'selprod_id');
        uksort(
            $recentViewedProducts,
            function ($key1, $key2) use ($cookiesProductsArr) {
                return (array_search($key1, $cookiesProductsArr) > array_search($key2, $cookiesProductsArr));
            }
        );
        return $recentViewedProducts;
    }

    public function recentlyViewedProducts($productId = 0)
    {
        $productId = FatUtility::int($productId);
        $recentViewedProducts = array();
        $cookieProducts = isset($_COOKIE['recentViewedProducts']) ? $_COOKIE['recentViewedProducts'] : false;
        if ($cookieProducts != false) {
            $cookiesProductsArr = explode("_", $cookieProducts);
            if (!isset($cookiesProductsArr) || !is_array($cookiesProductsArr) || count($cookiesProductsArr) <= 0) {
                return '';
            }
            if ($productId && in_array($productId, $cookiesProductsArr)) {
                $pos = array_search($productId, $cookiesProductsArr);
                unset($cookiesProductsArr[$pos]);
            }

            if (isset($cookiesProductsArr) && is_array($cookiesProductsArr) && count($cookiesProductsArr)) {
                $cookiesProductsArr = array_map('intval', $cookiesProductsArr);
                $cookiesProductsArr = array_reverse($cookiesProductsArr);

                $recentViewedProducts = $this->getRecentlyViewedProductsDetail($cookiesProductsArr);
            }
        }
        $compProdCount = 0;
        $comparedProdSpecCatId = 0;
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
            $comparedProdSpecCatId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
            $compProdCount = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
        }

        $this->set('comparedProdSpecCatId', $comparedProdSpecCatId);
        $this->set('compProdCount', $compProdCount);
        $this->_template->addJs('common-js/yr-function.js');
        $this->set('recentViewedProducts', $recentViewedProducts);
        $this->_template->render(false, false);
    }

    public function relatedProductsById($ids = array())
    {
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        if (isset($ids) && is_array($ids) && count($ids)) {
            $prodSrch = new ProductSearch($this->siteLangId);
            $prodSrch->setDefinedCriteria();
            $prodSrch->joinProductToCategory();
            $prodSrch->doNotCalculateRecords();

            if (true === MOBILE_APP_API_CALL) {
                $prodSrch->joinTable(SelProdReview::DB_TBL, 'LEFT OUTER JOIN', 'spr.spreview_selprod_id = selprod_id AND spr.spreview_product_id = product_id', 'spr');
                $prodSrch->joinTable(SelProdRating::DB_TBL, 'LEFT OUTER JOIN', 'sprating.sprating_spreview_id = spr.spreview_id', 'sprating');
                $prodSrch->addFld(array('COALESCE(ROUND(AVG(sprating_rating),2),0) as prod_rating'));
                $prodSrch->addGroupBy('selprod_id');
            }

            $prodSrch->doNotLimitRecords();
            $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
            $favVar = 0;
            if ($favVar == applicationConstants::NO) {
                $prodSrch->joinFavouriteProducts($loggedUserId);
                $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
            } else {
                $prodSrch->joinUserWishListProducts($loggedUserId);
                $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
            }
            // $prodSrch->joinProductRating();
            $prodSrch->addCondition('selprod_id', 'IN', $ids);
            $prodSrch->addMultipleFields(
                array(
                    'product_id', 'COALESCE(product_name, product_identifier) as product_name', 'prodcat_id', 'prodcat_comparison', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_updated_on', 'COALESCE(selprod_title,product_name, product_identifier) as selprod_title',
                    'selprod_id', 'selprod_condition', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'theprice',
                    'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type', 'selprod_sold_count', 'selprod_price',
                    'IFNULL(sprodata_is_for_sell, 0) as is_sell', 'IFNULL(sprodata_is_for_rent, 0) as is_rent', 'sprodata_rental_price as rent_price', 'sprodata_rental_stock', 'sprodata_rental_active', 'selprod_active', 'sprodata_duration_type', 'IFNULL(brand_name, brand_identifier) as brand_name', 'product_model'
                )
            );

            $productRs = $prodSrch->getResultSet();
            $products = FatApp::getDb()->fetchAll($productRs, 'selprod_id');

            uksort(
                $products,
                function ($key1, $key2) use ($ids) {
                    return (array_search($key1, $ids) > array_search($key2, $ids));
                }
            );
            return $products;
        }
    }

    public function clearSearchKeywords()
    {
        $keyword = FatApp::getPostedData("keyword");
        if (!empty($keyword)) {
            $recentSearchArr = [];
            if (isset($_COOKIE['recentSearchKeywords_' . $this->siteLangId])) {
                $recentSearchArr = unserialize($_COOKIE['recentSearchKeywords_' . $this->siteLangId]);
            }

            if (($key = array_search($keyword, $recentSearchArr)) !== false) {
                unset($recentSearchArr[$key]);
                var_dump($recentSearchArr);
                setcookie('recentSearchKeywords_' . $this->siteLangId, serialize($recentSearchArr), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
            }
        } else {
            setcookie('recentSearchKeywords_' . $this->siteLangId, '', time() + 60 * 60 * 72, CONF_WEBROOT_URL);
        }
    }

    public function searchProducttagsAutocomplete()
    {
        $keyword = FatApp::getPostedData("keyword");

        $recentSearchArr = [];
        if (isset($_COOKIE['recentSearchKeywords_' . $this->siteLangId])) {
            $recentSearchArr = unserialize($_COOKIE['recentSearchKeywords_' . $this->siteLangId]);
        }

        if (empty($keyword) || mb_strlen($keyword) < 3) {
            if (true === MOBILE_APP_API_CALL) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_PLEASE_ENTER_ATLEAST_3_CHARACTERS', $this->siteLangId));
            }

            $this->set('keyword', $keyword);
            $this->set('recentSearchArr', $recentSearchArr);
            $html = '';
            if (!empty($recentSearchArr)) {
                $html = $this->_template->render(false, false, 'products/search-producttags-autocomplete.php', true, false);
            }
            $this->set('html', $html);
            $this->_template->render(false, false, 'json-success.php', false, false);
        }
        $cacheKey = $this->siteLangId . '-' . urlencode($keyword);


        $autoSuggetionsCache = FatCache::get('autoSuggetionsCache' . $cacheKey, CONF_FILTER_CACHE_TIME, '.txt');
        if (!$autoSuggetionsCache) {
            $criteria = [
                'keyword' => $keyword,
                'doNotJoinSpecialPrice' => true
            ];
            $prodSrchObj = new ProductSearch($this->siteLangId);
            $prodSrchObj->joinSellerProducts(0, '', $criteria, true);
            $prodSrchObj->unsetDefaultLangForJoins();
            $prodSrchObj->joinSellers();
            $prodSrchObj->setGeoAddress();
            $prodSrchObj->joinShops();
            $prodSrchObj->validateAndJoinDeliveryLocation();
            $prodSrchObj->joinBrands($this->siteLangId);
            $prodSrchObj->joinProductToCategory($this->siteLangId);
            $prodSrchObj->joinSellerSubscription(0, false, true);
            $prodSrchObj->addSubscriptionValidCondition();
            $prodSrchObj->doNotCalculateRecords();

            $brandSrch = clone $prodSrchObj;
            $brandSrch->addMultipleFields(array('brand_id', 'COALESCE(tb_l.brand_name, brand.brand_identifier) as brand_name', 'if(LOCATE("' . $keyword . '", COALESCE(tb_l.brand_name, brand.brand_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(tb_l.brand_name, brand.brand_identifier)), 99) as level'));
            //$brandSrch->addKeywordSearch($keyword, false, false);
            $brandSrch->addCondition('brand_name', 'LIKE', '%' . $keyword . '%');
            $brandSrch->addOrder('level');
            $brandSrch->addGroupBy('brand_id');
            $brandSrch->setPageSize(5);
            $brandRs = $brandSrch->getResultSet();
            $brandArr = [];
            while ($row = FatApp::getDb()->fetch($brandRs)) {
                $brandArr[$row['brand_id']] = $row['brand_name'];
            }

            $catListingCount = 10 - count($brandArr);
            $catSrch = clone $prodSrchObj;
            $catSrch->setPageSize($catListingCount);
            $catSrch->addMultipleFields(array('prodcat_id', 'prodcat_comparison', 'COALESCE(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'if(LOCATE("' . $keyword . '", COALESCE(c_l.prodcat_name, c.prodcat_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(c_l.prodcat_name, c.prodcat_identifier)), 99) as level'));
            //$catSrch->addKeywordSearch($keyword, false, false);
            $catSrch->addCondition('prodcat_name', 'LIKE', '%' . $keyword . '%');
            $catSrch->addOrder('level');
            $catSrch->addGroupBy('prodcat_id');
            $catRs = $catSrch->getResultSet();
            // $catArr = FatApp::getDb()->fetchAll($catRs);
            $catArr = [];
            while ($row = FatApp::getDb()->fetch($catRs)) {
                $catArr[$row['prodcat_id']] = $row['prodcat_name'];
            }

            $srch = Tag::getSearchObject($this->siteLangId);
            $srch->doNotCalculateRecords();
            $srch->setPageSize(10);
            $srch->addMultipleFields(array('tag_id', 'COALESCE(tag_name, tag_identifier) as tag_name', 'if(LOCATE("' . $keyword . '", COALESCE(tag_name, tag_identifier)) > 0 , LOCATE("' . $keyword . '", COALESCE(tag_name, tag_identifier)), 99) as level'));
            $srch->addOrder('level');
            $srch->addGroupby('tag_id');
            $srch->addHaving('tag_name', 'LIKE', '%' . urldecode($keyword) . '%');
            $rs = $srch->getResultSet();
            $tags = FatApp::getDb()->fetchAll($rs);
            $prodArr = [];
            if (empty($tags)) {
                $prodSrchObj->setPageSize(10);
                $prodSrchObj->addMultipleFields(array('selprod_id', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'COALESCE(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'prodcat_comparison', 'if(LOCATE("' . $keyword . '", COALESCE(selprod_title, product_name, product_identifier)) > 0, LOCATE("' . $keyword . '", COALESCE(selprod_title, product_name, product_identifier)), 99) as level'));
                $prodSrchObj->addKeywordSearch($keyword, false, false);
                $prodSrchObj->addOrder('level');
                $prodSrchObj->addGroupBy('selprod_title');
                $prodRs = $prodSrchObj->getResultSet();
                $prodArr = FatApp::getDb()->fetchAll($prodRs);
            }

            $suggestions = [
                'tags' => $tags,
                'brands' => $brandArr,
                'categories' => $catArr,
                'products' => $prodArr
            ];
            if (!empty($tags) || !empty($brandArr) || !empty($catArr) || !empty($prodArr)) {
                array_unshift($recentSearchArr, $keyword);
            }
            $recentSearchArr = array_unique($recentSearchArr);
            $recentSearchArr = array_slice($recentSearchArr, 0, 5);
            setcookie('recentSearchKeywords_' . $this->siteLangId, serialize($recentSearchArr), time() + 60 * 60 * 72, CONF_WEBROOT_URL);
            FatCache::set('autoSuggetionsCache' . $cacheKey, serialize($suggestions), '.txt');
        } else {
            $suggestions = unserialize($autoSuggetionsCache);
        }

        $this->set('suggestions', $suggestions);
        $this->set('recentSearchArr', $recentSearchArr);
        $this->set('keyword', $keyword);
        if (true === MOBILE_APP_API_CALL) {
            $this->_template->render();
        }

        $html = $this->_template->render(false, false, 'products/search-producttags-autocomplete.php', true, false);
        $this->set('html', $html);
        $this->_template->render(false, false, 'json-success.php', false, false);
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'view':
                if (isset($parameters[0]) && FatUtility::int($parameters[0]) > 0) {
                    $selprod_id = FatUtility::int($parameters[0]);
                    if ($selprod_id) {
                        $srch = new ProductSearch($this->siteLangId);
                        $srch->joinSellerProducts();
                        $srch->joinProductToCategory();
                        $srch->doNotCalculateRecords();
                        $srch->doNotLimitRecords();
                        $srch->addMultipleFields(array('COALESCE(selprod_title, product_name, product_identifier) as selprod_title', 'COALESCE(product_name, product_identifier)as product_name', 'prodcat_code'));
                        $srch->addCondition('selprod_id', '=', $selprod_id);
                        $rs = $srch->getResultSet();
                        $row = FatApp::getDb()->fetch($rs);
                        if ($row) {
                            $productCatCode = $row['prodcat_code'];
                            $productCatCode = explode("_", $productCatCode);
                            $productCatCode = array_filter($productCatCode, 'strlen');
                            $productCatObj = new ProductCategory();
                            $prodCategories = $productCatObj->getCategoriesForSelectBox($this->siteLangId, '', $productCatCode);

                            foreach ($productCatCode as $code) {
                                $code = FatUtility::int($code);
                                if (isset($prodCategories[$code]['prodcat_name'])) {
                                    $prodCategories[$code]['prodcat_name'];
                                    $nodes[] = array('title' => $prodCategories[$code]['prodcat_name'], 'href' => UrlHelper::generateUrl('category', 'view', array($code)));
                                }
                            }
                            $nodes[] = array('title' => ($row['selprod_title']) ? $row['selprod_title'] : $row['product_name']);
                        }
                    }
                }
                break;
            default:
                $nodes[] = array('title' => Labels::getLabel('LBL_' . FatUtility::camel2dashed($action), $this->siteLangId));
                break;
        }
        return $nodes;
    }

    public function logWeightage()
    {
        $post = FatApp::getPostedData();
        $selprod_code = (isset($post['selprod_code']) && $post['selprod_code'] != '') ? $post['selprod_code'] : '';

        if ($selprod_code == '') {
            return false;
        }

        $weightageKey = SmartWeightageSettings::PRODUCT_VIEW;
        if (isset($post['timeSpend']) && $post['timeSpend'] == true) {
            $weightageKey = SmartWeightageSettings::PRODUCT_TIME_SPENT;
        }

        $weightageSettings = SmartWeightageSettings::getWeightageAssoc();
        Product::recordProductWeightage($selprod_code, $weightageKey, $weightageSettings[$weightageKey]);
    }

    private function getCartForm($formLangId, $isRent = 0, $quantity = 1, $extraData = [])
    {
        $frm = new Form('frmBuyProduct', array('id' => 'frmBuyProduct'));
        /* [ Rental Product Fields */
        $forSale = applicationConstants::PRODUCT_FOR_SALE;
        $forRent = applicationConstants::PRODUCT_FOR_RENT;

        $productForFld = $frm->addHiddenField('', 'product_for');
        $extend_order = $frm->addHiddenField('', 'extend_order');
        if ($isRent > 0 && ALLOW_RENT > 0) {
            $frm->addTextBox(Labels::getLabel('LBL_Rental_Dates', $formLangId), 'rental_dates', '', array('placeholder' => Labels::getLabel('LBL_Rental_Dates', $formLangId), 'readonly' => 'readonly'));

            $startDateUnReqFld = new FormFieldRequirement('rental_dates', Labels::getLabel('LBL_Rental_Dates', $formLangId));
            $startDateUnReqFld->setRequired(false);

            $startDateReqFld = new FormFieldRequirement('rental_dates', Labels::getLabel('LBL_Rental_Dates', $formLangId));
            $startDateReqFld->setRequired(true);

            $productForFld->requirements()->addOnChangerequirementUpdate($forSale, 'eq', 'rental_dates', $startDateUnReqFld);
            $productForFld->requirements()->addOnChangerequirementUpdate($forRent, 'eq', 'rental_dates', $startDateReqFld);

            $frm->addHiddenField('', 'rental_start_date');
            $frm->addHiddenField('', 'rental_end_date');
        }
        /* Rental Product Fields ] */
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Select_Quantity', $formLangId), 'quantity', $quantity, array('maxlength' => '3'));

        $fld->requirements()->setIntPositive();
        $wishlistId = (isset($extraData['wishlistId'])) ? $extraData['wishlistId'] : 0;
        $fullfillment = (isset($extraData['fullfillment'])) ? $extraData['fullfillment'] : 0;

        $frm->addSubmitButton(Labels::getLabel('LBL_Rent_Now', $formLangId), 'btnAddToCart', Labels::getLabel('LBL_Rent_Now', $formLangId), array('class' => 'add-to-cart add-to-cart--js', 'data-producttype' => 2, 'data-wishlistid' => $wishlistId, 'data-fullfillment' => $fullfillment, 'data-orderdetail' => 0));

        $frm->addSubmitButton(Labels::getLabel('LBL_Add_to_Cart', $formLangId), 'btnAddToCartSale', Labels::getLabel('LBL_Add_to_Cart', $formLangId), array('class' => 'add-to-cart add-to-cart--js', 'data-producttype' => 1, 'data-wishlistid' => $wishlistId, 'data-fullfillment' => $fullfillment, 'data-orderdetail' => 0));

        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'fulfillmentType');
        return $frm;
    }

    private function getReviewSearchForm($pageSize = 10)
    {
        $frm = new Form('frmReviewSearch');
        $frm->addHiddenField('', 'selprod_id');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'pageSize', $pageSize);
        $frm->addHiddenField('', 'orderBy', 'most_helpful');
        return $frm;
    }

    private function getReviewAbuseForm($reviewId)
    {
        $frm = new Form('frmReviewAbuse');
        $frm->addHiddenField('', 'spra_spreview_id', $reviewId);
        $frm->addTextarea(Labels::getLabel('Lbl_Comments', $this->siteLangId), 'spra_comments');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('Lbl_Report_Abuse', $this->siteLangId));
        return $frm;
    }

    public function fatActionCatchAll($action)
    {
        FatUtility::exitWithErrorCode(404);
    }

    public function track($productId = 0)
    {
        $bannerId = FatUtility::int($productId);
        if (1 > $productId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl(''));
        }
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }
        /* Track Click */
        $prodObj = new PromotionSearch($this->siteLangId, true);
        $prodObj->joinProducts();
        $prodObj->joinActiveUser();
        $prodObj->joinShops();
        $prodObj->addPromotionTypeCondition(Promotion::TYPE_PRODUCT);
        $prodObj->addShopActiveExpiredCondition();
        $prodObj->joinUserWallet();
        $prodObj->joinBudget();
        $prodObj->addBudgetCondition();
        $prodObj->doNotCalculateRecords();
        $prodObj->addMultipleFields(array('selprod_id as proSelProdId', 'promotion_id'));
        $prodObj->addCondition('promotion_record_id', '=', $productId);
        $sponsoredProducts = array();
        $productSrchObj = new ProductSearch($this->siteLangId);
        $productSrchObj->joinProductToCategory($this->siteLangId);
        $productSrchObj->doNotCalculateRecords();
        $productSrchObj->setPageSize(10);
        $productSrchObj->setDefinedCriteria();

        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if ($favVar == applicationConstants::NO) {
            $productSrchObj->joinFavouriteProducts($loggedUserId);
            $productSrchObj->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $productSrchObj->joinUserWishListProducts($loggedUserId);
            $productSrchObj->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }
        $productSrchObj->joinProductRating();
        $productSrchObj->addMultipleFields(
            array(
                'product_id', 'selprod_id', 'COALESCE(product_name, product_identifier) as product_name', 'COALESCE(selprod_title, product_name, product_identifier) as selprod_title',
                'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                'theprice', 'selprod_price', 'selprod_stock', 'selprod_condition', 'prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'selprod_sold_count'
            )
        );

        $productCatSrchObj = ProductCategory::getSearchObject(false, $this->siteLangId);
        $productCatSrchObj->addOrder('m.prodcat_active', 'DESC');
        $productCatSrchObj->doNotCalculateRecords();
        /* $productCatSrchObj->setPageSize(4); */
        $productCatSrchObj->addMultipleFields(array('prodcat_id', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description'));

        $productSrchObj->joinTable('(' . $prodObj->getQuery() . ') ', 'INNER JOIN', 'selprod_id = ppr.proSelProdId ', 'ppr');
        $productSrchObj->addFld(array('promotion_id'));
        $productSrchObj->joinSellerSubscription();
        $productSrchObj->addSubscriptionValidCondition();
        $productSrchObj->addGroupBy('selprod_id');

        $rs = $productSrchObj->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        $url = UrlHelper::generateFullUrl('products', 'view', array($productId));
        if ($row == false) {
            if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
                FatApp::redirectUser($url);
            }
        }

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (Promotion::isUserClickCountable($userId, $row['promotion_id'], $_SERVER['REMOTE_ADDR'], session_id())) {
            $promotionClickData = array(
                'pclick_promotion_id' => $row['promotion_id'],
                'pclick_user_id' => $userId,
                'pclick_datetime' => date('Y-m-d H:i:s'),
                'pclick_ip' => $_SERVER['REMOTE_ADDR'],
                'pclick_cost' => Promotion::getPromotionCostPerClick(Promotion::TYPE_PRODUCT),
                'pclick_session_id' => session_id(),
            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_CLICKS, $promotionClickData, false, '', $promotionClickData);
            $clickId = FatApp::getDb()->getInsertId();

            $promotionClickChargesData = array(
                'picharge_pclick_id' => $clickId,
                'picharge_datetime' => date('Y-m-d H:i:s'),
                'picharge_cost' => Promotion::getPromotionCostPerClick(Promotion::TYPE_PRODUCT),
            );

            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_ITEM_CHARGES, $promotionClickChargesData, false);

            $promotionLogData = array(
                'plog_promotion_id' => $row['promotion_id'],
                'plog_date' => date('Y-m-d'),
                'plog_clicks' => 1,
            );

            $onDuplicatePromotionLogData = array_merge($promotionLogData, array('plog_clicks' => 'mysql_func_plog_clicks+1'));
            FatApp::getDb()->insertFromArray(Promotion::DB_TBL_LOGS, $promotionLogData, true, array(), $onDuplicatePromotionLogData);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
            FatApp::redirectUser($url);
        }

        FatApp::redirectUser(UrlHelper::generateUrl(''));
    }

    public function setUrlString()
    {
        $urlString = FatApp::getPostedData('urlString', FatUtility::VAR_STRING, '');
        if ($urlString != '') {
            $_SESSION['referer_page_url'] = rtrim($urlString, '/') . '/';
        }
    }

    public function sellers($selprod_id)
    {
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $selProdReviewObj = new SelProdReviewSearch();
        $selProdReviewObj->joinSelProdRating();
        $selProdReviewObj->addCondition('sprating_rating_type', '=', SelProdRating::TYPE_PRODUCT);
        $selProdReviewObj->doNotCalculateRecords();
        $selProdReviewObj->doNotLimitRecords();
        $selProdReviewObj->addGroupBy('spr.spreview_product_id');
        $selProdReviewObj->addCondition('spr.spreview_status', '=', SelProdReview::STATUS_APPROVED);
        $selProdReviewObj->addMultipleFields(array('spr.spreview_selprod_id', 'spr.spreview_product_id', "ROUND(AVG(sprating_rating),2) as prod_rating", "count(spreview_id) as totReviews"));
        $selProdRviewSubQuery = $selProdReviewObj->getQuery();
        $prodSrch->joinTable('(' . $selProdRviewSubQuery . ')', 'LEFT OUTER JOIN', 'sq_sprating.spreview_product_id = product_id', 'sq_sprating');

        $prodSrch->addMultipleFields(
            array(
                'product_id', 'COALESCE(product_name,product_identifier ) as product_name', 'product_seller_id', 'product_model', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'product_upc', 'product_isbn', 'product_short_description', 'product_description', 'selprod_id', 'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found', 'splprice_start_date', 'splprice_end_date', 'COALESCE(selprod_title,product_name,product_identifier) as selprod_title', 'selprod_warranty', 'selprod_return_policy', 'selprodComments', 'theprice', 'selprod_stock', 'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id', 'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id', 'shop_name', 'COALESCE(sq_sprating.prod_rating,0) prod_rating ', 'COALESCE(sq_sprating.totReviews,0) totReviews', 'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price', 'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled'
            )
        );

        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }
        /* more sellers[ */
        $product['moreSellersArr'] = Product::getMoreSeller($product['selprod_code'], $this->siteLangId);

        foreach ($product['moreSellersArr'] as $seller) {
            if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                $product['rating'][$seller['selprod_user_id']] = SelProdRating::getSellerRating($seller['selprod_user_id']);
            } else {
                $product['rating'][$seller['selprod_user_id']] = 0;
            }

            /* [ Check COD enabled */
            $codEnabled = false;
            if (Product::isProductShippedBySeller($seller['product_id'], $seller['product_seller_id'], $seller['selprod_user_id'])) {
                if ($product['selprod_cod_enabled']) {
                    $codEnabled = true;
                }
            } else {
                if ($product['product_cod_enabled']) {
                    $codEnabled = true;
                }
            }
            $product['cod'][$seller['selprod_user_id']] = $codEnabled;
            /* ] */
        }
        /* ] */

        $displayProductNotAvailableLable = false;
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $displayProductNotAvailableLable = true;
        }
        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('product', $product);
        $this->_template->render();
    }

    public function productQuickDetail($selprod_id = 0)
    {
        $productImagesArr = array();
        $selprod_id = FatUtility::int($selprod_id);
        $prodSrchObj = new ProductSearch($this->siteLangId);

        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->setLocationBasedInnerJoin(false);
        $prodSrch->setGeoAddress();
        $prodSrch->setDefinedCriteria(false, false, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->validateAndJoinDeliveryLocation(false);
        $prodSrch->doNotCalculateRecords();
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->doNotLimitRecords();

        /* sub query to find out that logged user have marked current product as in wishlist or not[ */
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
        $favVar = 0;
        if ($favVar == applicationConstants::NO) {
            $prodSrch->joinFavouriteProducts($loggedUserId);
            $prodSrch->addFld('IFNULL(ufp_id, 0) as ufp_id');
        } else {
            $prodSrch->joinUserWishListProducts($loggedUserId);
            $prodSrch->addFld('COALESCE(uwlp.uwlp_selprod_id, 0) as is_in_any_wishlist');
        }

        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'COALESCE(product_name,product_identifier) as product_name',
                'product_seller_id', 'product_model', 'product_type', 'prodcat_id', 'prodcat_comparison',
                'COALESCE(prodcat_name,prodcat_identifier) as prodcat_name', 'product_upc',
                'product_isbn', 'product_short_description', 'product_description', 'selprod_id',
                'selprod_user_id', 'selprod_code', 'selprod_condition', 'selprod_price', 'special_price_found',
                'splprice_start_date',
                'splprice_end_date', 'COALESCE(selprod_title,product_name, product_identifier) as selprod_title',
                'selprod_warranty', 'selprod_return_policy', 'selprodComments', 'theprice', 'selprod_stock',
                'selprod_threshold_stock_level', 'IF(selprod_stock > 0, 1, 0) AS in_stock', 'brand_id',
                'COALESCE(brand_name, brand_identifier) as brand_name', 'brand_short_description', 'user_name', 'shop_id',
                'shop_name', 'splprice_display_dis_type', 'splprice_display_dis_val', 'splprice_display_list_price',
                'product_attrgrp_id', 'product_youtube_video', 'product_cod_enabled', 'selprod_cod_enabled',
                'selprod_available_from', 'selprod_min_order_qty',
                'sprodata_is_for_sell as is_sell', 'sprodata_is_for_rent as is_rent',
                'sprodata_rental_price as rent_price', 'sprodata_rental_security ',
                'sprodata_rental_terms', 'sprodata_rental_stock', 'sprodata_rental_buffer_days',
                'sprodata_minimum_rental_duration', 'selprod_fulfillment_type', 'shop_fulfillment_type', 'sprodata_duration_type', 'sprodata_minimum_rental_quantity', 'sprodata_rental_available_from'
            )
        );
        /* echo $selprod_id; die; */
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        if (!$product) {
            FatUtility::exitWithErrorCode(404);
        }

        $subscription = false;
        $allowed_images = -1;
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $product['selprod_user_id'], array('ossubs_images_allowed'));
            $allowed_images = $currentPlanData['ossubs_images_allowed'];
            $subscription = true;
        }

        /* Current Product option Values[ */
        $options = SellerProduct::getSellerProductOptions($selprod_id, false);
        /* CommonHelper::printArray($options);die(); */
        $productSelectedOptionValues = array();
        $productGroupImages = array();
        if ($options) {
            foreach ($options as $op) {
                $images = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], $op['selprodoption_optionvalue_id'], $this->siteLangId, true, '', $allowed_images);
                if ($images) {
                    $productImagesArr += $images;
                }
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        if ($productImagesArr) {
            foreach ($productImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }
        $product['selectedOptionValues'] = $productSelectedOptionValues;
        /* ] */

        if (isset($allowed_images) && $allowed_images > 0) {
            $universal_allowed_images_count = $allowed_images - count($productImagesArr);
        }

        $productUniversalImagesArr = array();
        if (empty($productGroupImages) || !$subscription || isset($universal_allowed_images_count)) {
            $universalImages = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id'], -1, $this->siteLangId, true, '');
            /* CommonHelper::printArray($universalImages); die; */
            if ($universalImages) {
                if (isset($universal_allowed_images_count)) {
                    $images = array_slice($universalImages, 0, $universal_allowed_images_count);
                    $productUniversalImagesArr = $images;
                    $universal_allowed_images_count = $universal_allowed_images_count - count($productUniversalImagesArr);
                } elseif (!$subscription) {
                    $productUniversalImagesArr = $universalImages;
                }
            }
        }

        if ($productUniversalImagesArr) {
            foreach ($productUniversalImagesArr as $image) {
                $afileId = $image['afile_id'];
                if (!array_key_exists($afileId, $productGroupImages)) {
                    $productGroupImages[$afileId] = array();
                }
                $productGroupImages[$afileId] = $image;
            }
        }

        /* [ Product shipping cost */
        $shippingCost = 0;
        /* ] */

        /* more sellers[ */
        $product['moreSellersArr'] = Product::getMoreSeller($product['selprod_code'], $this->siteLangId, $product['selprod_user_id']);
        /* ] */

        $product['selprod_return_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_RETURN, $this->siteLangId);
        $product['selprod_warranty_policies'] = SellerProduct::getSelprodPolicies($product['selprod_id'], PolicyPoint::PPOINT_TYPE_WARRANTY, $this->siteLangId);

        /* Form buy product[ */
        $productFor = applicationConstants::PRODUCT_FOR_SALE;
        $isRent = $product['is_rent'];
        $unavailableDates = array();
        $rentalAvailableDate = '';

        if ($isRent > 0 && ALLOW_RENT > 0) {
            $productFor = applicationConstants::PRODUCT_FOR_RENT;
            if (strtotime($product['selprod_available_from']) < time()) {
                $startDate = date('Y-m-d');
            } else {
                $startDate = date('Y-m-d', strtotime($product['selprod_available_from']));
            }
            $endDate = date('Y-m-d', strtotime('+6 month'));
            $productOrderData = OrderProductData::getProductOrders($selprod_id, $startDate, $endDate, $product['sprodata_rental_buffer_days']);

            if (!empty($productOrderData)) {
                $unavailableDates = ProductRental::prodDisableDates($productOrderData, $product['sprodata_rental_stock'], $product['sprodata_rental_buffer_days']);
            }
        }
        /* ] */

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->doNotCalculateRecords();
        $optionSrchObj->doNotLimitRecords();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            $validDateCondition = " and oss.ossubs_till_date >= '" . date('Y-m-d') . "'";
            $optionSrchObj->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_user_id=seller_user.user_id AND o.order_type=' . ORDERS::ORDER_SUBSCRIPTION . ' AND o.order_payment_status =1', 'o');
            $optionSrchObj->joinTable(OrderSubscription::DB_TBL, 'INNER JOIN', 'o.order_id = oss.ossubs_order_id and oss.ossubs_status_id=' . FatApp::getConfig('CONF_DEFAULT_SUBSCRIPTION_PAID_ORDER_STATUS') . $validDateCondition, 'oss');
        }
        $optionSrchObj->addCondition('product_id', '=', $product['product_id']);

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selprod_id);
        $optionSrch->addGroupBy('option_id');
        /* echo $optionSrch->getQuery();die; */
        $optionRs = $optionSrch->getResultSet();
        $optionRows = FatApp::getDb()->fetchAll($optionRs, 'option_id');
        /* CommonHelper::printArray($optionRows);die; */
        if (count($optionRows) > 0) {
            $optionArr = [];
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $product['product_id']);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                if (!FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                    $optionValueSrch->addGroupBy('optionvalue_id');
                }
                $optionValueSrch->addGroupBy('selprod_code');
                $optionValueRs = $optionValueSrch->getResultSet();
                if (true === MOBILE_APP_API_CALL || FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs);
                    $optionArr = array_merge($optionArr, $optionValueRows);
                } else {
                    $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs, 'optionvalue_id');
                }
                $option['values'] = $optionValueRows;
            }

            /* [ GROUP OPTION BY SELPROD ID */
            if (FatApp::getConfig('CONF_DISPLAY_SINGLE_SELECT_FOR_PRODUCT_OPTIONS', FatUtility::VAR_INT, 0)) {
                $optionsFinalArr = [];
                foreach ($optionArr as $opRow) {
                    $sId = $opRow['selprod_id'];
                    if ($opRow['selprod_code'] == $product['selprod_code']) {
                        $sId = $product['selprod_id'];
                    }

                    if (trim($opRow['optionvalue_color_code']) != '' && substr($opRow['optionvalue_color_code'], 0, 1) != "#") {
                        $opRow['optionvalue_color_code'] = '#' . $opRow['optionvalue_color_code'];
                    }

                    if (isset($optionsFinalArr[$sId])) {
                        $value = $optionsFinalArr[$sId]['value'] . ' | ' . $opRow['optionvalue_name'];
                        $colorCode = ($optionsFinalArr[$sId]['color_code'] != '') ? $optionsFinalArr[$sId]['color_code'] : $opRow['optionvalue_color_code'];
                    } else {
                        $value = $opRow['optionvalue_name'];
                        $colorCode  = $opRow['optionvalue_color_code'];
                    }
                    /* $optionsFinalArr[$sId] = $value; */
                    $optionsFinalArr[$sId] = array('value' => $value, 'color_code' => $colorCode);
                }
                $this->set('optionsFinalArr', $optionsFinalArr);
            }
            /* ] */
        }
        $this->set('optionRows', $optionRows);

        /* Get Product Specifications */
        $allSpecifications = $this->getProductSpecifications($product['product_id'], $this->siteLangId);
        $this->set('productSpecifications', $allSpecifications['textSpecifications']);
        $this->set('productFileSpecifications', $allSpecifications['fileSpecifications']);
        /* End of Product Specifications */

        if ($product) {
            $title = $product['product_name'];

            if ($product['selprod_title']) {
                $title = $product['selprod_title'];
            }

            $product_description = trim(CommonHelper::subStringByWords(strip_tags(CommonHelper::renderHtml($product["product_description"], true)), 500));
            $product_description .= ' - ' . Labels::getLabel('LBL_See_more_at', $this->siteLangId) . ": " . UrlHelper::getCurrUrl();

            $productImageUrl = '';
            /* $productImageUrl = UrlHelper::generateFullUrl('Image','product', array($product['product_id'],'', $product['selprod_id'],0,$this->siteLangId )); */
            if ($productImagesArr) {
                $afile_id = array_keys($productImagesArr)[0];
                $productImageUrl = UrlHelper::generateFullUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $afile_id));
            }
        }

        $displayProductNotAvailableLable = false;
        //availableInLocation
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $displayProductNotAvailableLable = true;
        }

        $isProductShippedBySeller = Product::isProductShippedBySeller($product['product_id'], $product['product_seller_id'], $product['selprod_user_id']);
        $fulfillmentType = $product['selprod_fulfillment_type'];
        if (true == $isProductShippedBySeller) {
            if ($product['shop_fulfillment_type'] != Shipping::FULFILMENT_ALL) {
                $fulfillmentType = $product['shop_fulfillment_type'];
                $product['selprod_fulfillment_type'] = $fulfillmentType;
            }
        } else {
            $fulfillmentType = isset($product['product_fulfillment_type']) ? $product['product_fulfillment_type'] : Shipping::FULFILMENT_SHIP;
            $product['selprod_fulfillment_type'] = $fulfillmentType;
            if (FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1) != Shipping::FULFILMENT_ALL) {
                $fulfillmentType = FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1);
                $product['selprod_fulfillment_type'] = $fulfillmentType;
            }
        }

        $shippingRateRow = Product::getProductShippingRatesByAddress($product['selprod_id']);
        $minShipDuration = (!empty($shippingRateRow)) ? $shippingRateRow['minimum_shipping_duration'] : 1;
        $this->set('shippingRateRow', $shippingRateRow);
        $this->set('minShipDuration', $minShipDuration);

        $sellerProduct = new SellerProduct($selprod_id);
        $this->set('addonProducts', $sellerProduct->getAddonProducts($this->siteLangId));

        $frm = $this->getCartForm($this->siteLangId, $isRent, 1, FatApp::getPostedData());
        $frm->fill(array('selprod_id' => $selprod_id, 'product_for' => $productFor, 'rental_start_date' => $rentalAvailableDate, 'fulfillmentType' => $fulfillmentType));
        $this->set('frmBuyProduct', $frm);

        $this->set('fulfillmentType', $fulfillmentType);
        $this->set('displayProductNotAvailableLable', $displayProductNotAvailableLable);
        $this->set('product', $product);
        $this->set('unavailableDates', $unavailableDates);
        $this->set('productImagesArr', $productGroupImages);
        $this->set('postedData', FatApp::getPostedData());
        $this->set('rentalTypeArr', applicationConstants::rentalTypeArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function linksAutocomplete()
    {
        $prodCatObj = new ProductCategory();
        $post = FatApp::getPostedData();
        $arr_options = $prodCatObj->getProdCatTreeStructureSearch(0, $this->siteLangId, $post['keyword']);
        $json = array();
        foreach ($arr_options as $key => $product) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($product, ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    private function getListingData($get)
    {
        $db = FatApp::getDb();

        $userId = 0;
        if (UserAuthentication::isUserLogged()) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $page = 1;
        if (array_key_exists('page', $get)) {
            $page = FatUtility::int($get['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
        if (array_key_exists('pageSize', $get)) {
            $pageSize = FatUtility::int($get['pageSize']);
            $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);
            if (0 >= $pageSize || !array_key_exists($pageSize, $pageSizeArr)) {
                $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
            }
        }

        $get['selectedFulfillmentType'] = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;
        $buyerAddress = $userAddress = Address::getYkGeoData();
        $countryId = ((isset($buyerAddress['ykGeoCountryId'])) && $buyerAddress['ykGeoCountryId']) ? $buyerAddress['ykGeoCountryId'] : 0;
        
        //Currenty normal search is in working @todo
        if (FatApp::getConfig('CONF_DEFAULT_PLUGIN_' . Plugin::TYPE_FULL_TEXT_SEARCH, FatUtility::VAR_INT, 0) && 0) {
            $srch = FullTextSearch::getListingObj($get, $this->siteLangId, $userId);
            $page = ($page - 1) * $pageSize;
            $srch->setPageNumber($page);
            $srch->setPageSize($pageSize);
            $srch->setFields(array('brand', 'categories', 'general'));
            $records = $srch->fetch();
            $products = [];
            $favVar = FatApp::getConfig('CONF_ADD_FAVORITES_TO_WISHLIST', FatUtility::VAR_INT, 1);
            $favVar = 0;
            if (isset($records['hits']) && count($records['hits']) > 0) {
                foreach ($records['hits'] as $record) {
                    if ($favVar == applicationConstants::NO) {
                        $arr = array('ufp_id' => 0);
                        $favSrch = new UserFavoriteProductSearch();
                        $favSrch->addCondition('ufp_user_id', '=', $userId);
                        $favSrch->addCondition('ufp_selprod_id', '=', $record['_source']['general']['selprod_id']);
                        $favSrch->doNotCalculateRecords();
                        $favSrch->setPageSize(1);
                        $favSrch->addGroupBy('selprod_id');
                        $rs = $favSrch->getResultSet();
                        $wishListProd = $db->fetch($rs);
                        if (!empty($wishListProd) && $wishListProd['ufp_id']) {
                            $arr = array('ufp_id' => $wishListProd['ufp_id']);
                        }
                    } else {
                        $arr = array('is_in_any_wishlist' => 0);
                        $wislistPSrchObj = new UserWishListProductSearch();
                        $wislistPSrchObj->joinWishLists();
                        $wislistPSrchObj->doNotCalculateRecords();
                        $wislistPSrchObj->setPageSize(1);
                        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $userId);
                        $wislistPSrchObj->addMultipleFields(array('uwlp_selprod_id', 'uwlp_uwlist_id'));
                        $wislistPSrchObj->addCondition('uwlp_selprod_id', '=', $record['_source']['general']['selprod_id']);
                        $rs = $wislistPSrchObj->getResultSet();
                        $wishListProd = $db->fetch($rs);
                        if (!empty($wishListProd) && $wishListProd['uwlp_uwlist_id']) {
                            $arr = array('is_in_any_wishlist' => 1);
                        }
                    }

                    $products[] = array_merge($record['_source']['general'], $record['_source']['brand'], current($record['_source']['categories']), $arr);
                }
            }
        } else {
            $srch = Product::getListingObj($get, $this->siteLangId, $userId, true);
            $srch->setPageNumber($page);
            if ($pageSize) {
                $srch->setPageSize($pageSize);
            }
            $rs = $srch->getResultSet();
            $db = FatApp::getDb();
            $productData = $db->fetchAll($rs, 'product_id'); 
            
            $products = [];
            if (!empty($productData)) {
                $catalogIds = array_column($productData, 'product_id');
                $srchNew = Product::getListingObj($get, $this->siteLangId, $userId, false);
                $srchNew->addCondition('product_id', 'IN', $catalogIds);
                $sellerProductData = FatApp::getDb()->fetchAll($srchNew->getResultSet());
                $key = 'theprice';
                if (array_key_exists('selectedFulfillmentType', $get) && $get['selectedFulfillmentType'] == Shipping::FULFILMENT_PICKUP) {
                    $key = 'distance';
                } elseif(array_key_exists('selectedFulfillmentType', $get) && $get['selectedFulfillmentType'] == Shipping::FULFILMENT_SHIP && $countryId > 0) {
                    $key = 'availableForShip';
                }
                
                if (!empty($sellerProductData)) {
                    foreach($sellerProductData as $sellerProduct) {
                        if (isset($products[$sellerProduct['product_id']])) {
                            continue;
                        }
                    
                        if ((isset($productData[$sellerProduct['product_id']])) && $sellerProduct[$key] == $productData[$sellerProduct['product_id']][$key]) {
                            $productDetails = array_merge($productData[$sellerProduct['product_id']], $sellerProduct);
                            $products[$sellerProduct['product_id']] = $productDetails;
                        }
                    }
                }
            }
        }

        /* to show searched category data[ */
        $categoryId = null;
        $category = array();
        if (array_key_exists('category', $get)) {
            $categoryId = FatUtility::int($get['category']);
            if ($categoryId) {
                $productCategorySearch = new ProductCategorySearch($this->siteLangId);
                $productCategorySearch->addCondition('prodcat_id', '=', $categoryId);
                $productCategorySearch->addMultipleFields(array('prodcat_id', 'prodcat_comparison', 'COALESCE(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description', 'prodcat_code'));
                $productCategorySearchRs = $productCategorySearch->getResultSet();
                $category = $db->fetch($productCategorySearchRs);
                $category['banner'] = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $categoryId);
            }
        }
        /* ] */
        $prodCatAttributes = [];
        $prodCustomFldsData = [];
        if (!empty($products) && applicationConstants::getActiveTheme() == applicationConstants::THEME_AUTOMOBILE) {
            $productCatArr = array_unique(array_column($products, 'prodcat_id'));
            $productIdsArr = array_unique(array_column($products, 'product_id'));
            $prodCatObj = new ProductCategory();
            $prodCatAttributes = $prodCatObj->getAttrDetail($this->siteLangId, 0, 'attr_attrgrp_id', $productCatArr, true);
            $prodCatAttributes = Product::formatArrByCatId($prodCatAttributes);
            $prodCustomFldsData = Product::getCustomFields($productIdsArr, $this->siteLangId);
        }
        $get['pageSize'] = $pageSize;
        $moreSellersArr = [];
        if ($get['vtype'] == 'map') {
            if (0 < count($products)) {
                $selprodCodes = array_column($products, 'selprod_code');
                $moreSellersArr = Product::getMoreSeller($selprodCodes, $this->siteLangId);
            }
        }
        $data = array(
            'products' => $products,
            'moreSellersProductsArr' => $moreSellersArr, /* seller products which is related to same options*/
            'prodCatAttributes' => $prodCatAttributes,
            'prodCustomFldsData' => $prodCustomFldsData,
            'category' => $category,
            'categoryId' => $categoryId,
            'postedData' => $get,
            'page' => $page,
            'pageCount' => $srch->pages(),
            'pageSize' => $pageSize,
            'recordCount' => $srch->recordCount(),
            'pageRecordCount' => count($products),
            'siteLangId' => $this->siteLangId
        );
        return $data;
    }

    public function getFilteredProducts()
    {
        $post = FatApp::getPostedData();
        $userId = UserAuthentication::getLoggedUserId(true);
        $post['join_price'] = 1;
        $page = 1;
        if (array_key_exists('page', $post)) {
            $page = FatUtility::int($post['page']);
            if ($page < 2) {
                $page = 1;
            }
        }

        $pageSize = !empty($post['pageSize']) ? FatUtility::int($post['pageSize']) : FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);

        $srch = Product::getListingObj($post, $this->siteLangId, $userId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $products = $db->fetchAll($rs);

        $data = array(
            'products' => $products,
            'page' => $page,
            'pageCount' => $srch->pages(),
            'pageSize' => $pageSize,
            'recordCount' => $srch->recordCount()
        );
        $this->set('data', $data);
        $this->_template->render();
    }

    public function getOptions($selProdId)
    {
        $selProdId = FatUtility::int($selProdId);
        if (1 > $selProdId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $productId = SellerProduct::getAttributesById($selProdId, 'selprod_product_id', false);

        $options = SellerProduct::getSellerProductOptions($selProdId, false);
        $productSelectedOptionValues = array();
        if (is_array($options) && 0 < count($options)) {
            foreach ($options as $op) {
                $productSelectedOptionValues[$op['selprodoption_option_id']] = $op['selprodoption_optionvalue_id'];
            }
        }

        $prodSrchObj = new ProductSearch($this->siteLangId);

        $optionSrchObj = clone $prodSrchObj;
        $optionSrchObj->setDefinedCriteria();
        $optionSrchObj->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'LEFT OUTER JOIN', 'selprod_id = tspo.selprodoption_selprod_id', 'tspo');
        $optionSrchObj->joinTable(OptionValue::DB_TBL, 'LEFT OUTER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
        $optionSrchObj->joinTable(Option::DB_TBL, 'LEFT OUTER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');

        $optionSrch = clone $optionSrchObj;
        $optionSrch->joinTable(Option::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'op.option_id = op_l.optionlang_option_id AND op_l.optionlang_lang_id = ' . $this->siteLangId, 'op_l');
        $optionSrch->addMultipleFields(array('option_id', 'option_is_color', 'COALESCE(option_name,option_identifier) as option_name'));
        $optionSrch->addCondition('option_id', '!=', 'NULL');
        $optionSrch->addCondition('selprodoption_selprod_id', '=', $selProdId);
        $optionSrch->addGroupBy('option_id');

        $optionRs = $optionSrch->getResultSet();
        $optionRows = FatApp::getDb()->fetchAll($optionRs);

        if ($optionRows) {
            foreach ($optionRows as &$option) {
                $optionValueSrch = clone $optionSrchObj;
                $optionValueSrch->joinTable(OptionValue::DB_TBL . '_lang', 'LEFT OUTER JOIN', 'opval.optionvalue_id = opval_l.optionvaluelang_optionvalue_id AND opval_l.optionvaluelang_lang_id = ' . $this->siteLangId, 'opval_l');
                $optionValueSrch->addCondition('product_id', '=', $productId);
                $optionValueSrch->addCondition('option_id', '=', $option['option_id']);
                $optionValueSrch->addMultipleFields(array('COALESCE(product_name, product_identifier) as product_name', 'selprod_id', 'selprod_user_id', 'selprod_code', 'option_id', 'COALESCE(optionvalue_name,optionvalue_identifier) as optionvalue_name ', 'theprice', 'optionvalue_id', 'optionvalue_color_code'));
                $optionValueSrch->addGroupBy('optionvalue_id');
                $optionValueRs = $optionValueSrch->getResultSet();
                $optionValueRows = FatApp::getDb()->fetchAll($optionValueRs);

                foreach ($optionValueRows as $index => $opVal) {
                    $optionValueRows[$index]['isAvailable'] = 1;
                    if (is_array($productSelectedOptionValues) && !in_array($opVal['optionvalue_id'], $productSelectedOptionValues)) {
                        $optionUrl = Product::generateProductOptionsUrl($selProdId, $productSelectedOptionValues, $option['option_id'], $opVal['optionvalue_id'], $productId);
                        $optionUrlArr = explode("::", $optionUrl);
                        if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                            $optionValueRows[$index]['isAvailable'] = 0;
                        }
                    }
                }

                $option['values'] = $optionValueRows;
            }
        }
        $this->set('options', $optionRows);
        $this->_template->render();
    }

    public function autoCompleteTaxCategories()
    {
        $pagesize = 10;
        $post = FatApp::getPostedData();
        $srch = Tax::getSearchObject($this->siteLangId, true);
        $srch->addCondition('taxcat_deleted', '=', 0);
        $activatedTaxServiceId = Tax::getActivatedServiceId();

        $srch->addFld('taxcat_id');
        if ($activatedTaxServiceId) {
            $srch->addFld('concat(IFNULL(taxcat_name,taxcat_identifier), " (",taxcat_code,")")as taxcat_name');
        } else {
            $srch->addFld('IFNULL(taxcat_name,taxcat_identifier)as taxcat_name');
        }
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        if (!empty($post['keyword'])) {
            $srch->addCondition('taxcat_name', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_identifier', 'LIKE', '%' . $post['keyword'] . '%')
                ->attachCondition('taxcat_code', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->setPageSize($pagesize);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $taxCategories = $db->fetchAll($rs, 'taxcat_id');
        $json = array();
        $defaultStringLength = applicationConstants::DEFAULT_STRING_LENGTH;
        foreach ($taxCategories as $key => $taxCategory) {
            $taxCatName = strip_tags(html_entity_decode($taxCategory['taxcat_name'], ENT_QUOTES, 'UTF-8'));
            $taxCatName1 = substr($taxCatName, 0, $defaultStringLength);
            if ($defaultStringLength < strlen($taxCatName)) {
                $taxCatName1 .= '...';
            }
            $json[] = array(
                'id' => $key,
                'name' => $taxCatName1
            );
        }
        die(json_encode($json));
    }

    private function formatAttributes(array $prodCatAttr): array
    {
        $response = array();
        foreach ($prodCatAttr as $attr) {
            $response[$attr['attr_attrgrp_id']]['attr_group_name'] = $attr['attrgrp_name'];
            $response[$attr['attr_attrgrp_id']]['attributes'][] = $attr;
        }

        $formattedArr = $response;
        if(isset($response[0]) && !empty($response[0])) {
            $othersArr[] = $response[0];
            unset($response[0]);
            $formattedArr = $response + $othersArr;
        }
        return $formattedArr;
    }

    private function getCustomFields(int $catalogId): array
    {
        $infoAttributes = Product::getProductNumericAttributes($catalogId, true);
        $textualAttributes = Product::getProductTextualAttributes($catalogId, $this->siteLangId);

        if (empty($infoAttributes)) {
            $infoAttributes = $textualAttributes;
        } else {
            foreach ($textualAttributes as $textualAttribute) {
                $key = $textualAttribute['prodtxtattr_attrgrp_id'];
                if (!empty($infoAttributes[$key])) {
                    $infoAttributes[$key] = $infoAttributes[$key] + $textualAttribute;
                } else {
                    $infoAttributes[$key] = $textualAttribute;
                }
            }
        }
        return $infoAttributes;
    }

    private function getOrderDetailsForExtendRental(int $selprodId, int $oprID): array
    {
        $srch = new SearchBase(OrderProduct::DB_TBL, 'op');
        $srch->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_id = op.op_order_id', 'o');
        $srch->addCondition('o.order_user_id', '=', UserAuthentication::getLoggedUserId());
        $srch->addCondition('op.op_selprod_id', '=', $selprodId);
        $srch->addCondition('op.op_id', '=', $oprID);
        $rs = $srch->getResultSet();
        $orderInfo = FatApp::getDb()->fetch($rs);
        if ($orderInfo === false) {
            return [];
        }
        return $orderInfo;
    }

    public function getRentalDetails()
    {
        $post = FatApp::getPostedData();
        if (empty($post)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $selprod_id = FatUtility::int($post['selprod_id']);
        $sellerId = SellerProduct::getAttributesById($selprod_id, 'selprod_user_id');
        $fulfillmentType = FatApp::getPostedData('fulfillmentType', FatUtility::VAR_INT, Shipping::FULFILMENT_SHIP);

        $rentalStartDate = $post['rental_start_date'];
        $rentalEndDate = $post['rental_end_date'];
        $quantity = FatUtility::int($post['quantity']);
        $extendOrderId = FatUtility::int($post['extendOrderId']);


        if ($extendOrderId < 1 && strtotime($rentalEndDate) < strtotime($rentalStartDate)) {
            $message = Labels::getLabel('LBL_Rental_End_Date_Must_Be_Greater_Then_Current_Date', $this->siteLangId);
            FatUtility::dieJsonError($message);
        }

        if ($selprod_id < 1) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $splPriceForDate = FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d');

        $srch = new ProductSearch();
        /* $srch->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice' => true)); */
        $srch->setDefinedCriteria();
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->joinTable(
            SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE, 'LEFT OUTER JOIN', 'rent_special_price.splprice_selprod_id = selprod_id AND \'' . $splPriceForDate . '\' BETWEEN rent_special_price.splprice_start_date AND rent_special_price.splprice_end_date AND rent_special_price.splprice_type = ' . Product::PRODUCT_FOR_RENT, 'rent_special_price'
        );

        $srch->addMultipleFields(array('selprod_id', 'sprodata_rental_stock', 'sprodata_rental_buffer_days', 'sprodata_minimum_rental_duration', 'COALESCE(rent_special_price.splprice_price, sprodata_rental_price) as sprodata_rental_price', 'sprodata_rental_security', 'sprodata_duration_type', 'product_id', 'product_seller_id', 'selprod_user_id'));

        $srch->addCondition('selprod_id', '=', $selprod_id);
        $srch->addCondition('sprodata_is_for_rent', '=', 1);
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();

        $sellerProductRow = $db->fetch($rs);

        if (empty($sellerProductRow)) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $isProductShippedBySeller = Product::isProductShippedBySeller($sellerProductRow['product_id'], $sellerProductRow['product_seller_id'], $sellerProductRow['selprod_user_id']);
        $shippingRateRow = Product::getProductShippingRatesByAddress($sellerProductRow['selprod_id']);
        $minShipDuration = (!empty($shippingRateRow)) ? $shippingRateRow['minimum_shipping_duration'] : 1;

        $rentalAvailableDate = date('Y-m-d');
        $selectedFullfillmentType = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : FatApp::getConfig('CONF_DEFAULT_LOCATION_CHECKOUT_TYPE', FatUtility::VAR_INT, 1);
        if ($selectedFullfillmentType == Shipping::FULFILMENT_SHIP && ($fulfillmentType == Shipping::FULFILMENT_ALL || $fulfillmentType == Shipping::FULFILMENT_SHIP)) {
            $rentalAvailableDate = date('Y-m-d', strtotime('+ ' . FatUtility::int($minShipDuration) . ' days', strtotime($rentalAvailableDate)));
        }

        if (($extendOrderId < 1) && (strtotime($rentalEndDate) < strtotime($rentalAvailableDate) || strtotime($rentalStartDate) < strtotime($rentalAvailableDate))) {
            $message = Labels::getLabel('LBL_Rental_Start_And_End_Date_Must_Be_Greater_Then_', $this->siteLangId) . ' ' . $rentalAvailableDate;
            FatUtility::dieJsonError($message);
        }

        $rentProd = new ProductRental($selprod_id);
        $alreadyBookedQty = $rentProd->getRentalProductQuantity($rentalStartDate, $rentalEndDate, $sellerProductRow['sprodata_rental_buffer_days'], $extendOrderId, $minShipDuration);

        $availableQty = $sellerProductRow['sprodata_rental_stock'] - $alreadyBookedQty;
        if (1 > $availableQty) {
            $data = array(
                'availableQuantity' => $availableQty,
                'msg' => Labels::getLabel('LBL_This_product_is_out_of_stock_now', $this->siteLangId)
            );
            FatUtility::dieJsonError($data);
        } else if ($availableQty < $quantity) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Max_available_quantity_is', $this->siteLangId) . ' ' . $availableQty);
        }

        $duration = CommonHelper::getDifferenceBetweenDates($rentalStartDate, $rentalEndDate, $sellerId, $sellerProductRow['sprodata_duration_type']);
        $priceArr = CommonHelper::getRentalPricesArr($sellerProductRow);
        $rentalPrice = CommonHelper::getProductRentalPrice($duration, $priceArr);

        $rentalSecurity = CommonHelper::displayMoneyFormat($sellerProductRow['sprodata_rental_security'] * $quantity);
        $rentalSecurityAmount = $sellerProductRow['sprodata_rental_security'];

        $srch = new SellerProductDurationDiscountSearch();
        $srch->doNotCalculateRecords();
        $srch->addCondition('produr_selprod_id', '=', $sellerProductRow['selprod_id'], 'AND');
        $srch->addCondition('produr_rental_duration', '<=', $duration);
        $srch->addOrder('produr_rental_duration', 'DESC');
        $srch->addMultipleFields(array('produr_id'));
        $rs = $srch->getResultSet();
        $durationDiscountRow = FatApp::getDb()->fetch($rs);

        if ($extendOrderId > 0) {
            $durationDiscountRow = [];
            $rentalSecurity = Labels::getLabel('LBL_NA', $this->siteLangId);
            $rentalSecurityAmount = 0;
        }

        $totalPayableAmount = ($rentalPrice + $rentalSecurityAmount) * $quantity;
        $data = array(
            'availableQuantity' => $availableQty,
            'rentalPrice' => CommonHelper::displayMoneyFormat($rentalPrice * $quantity),
            'rentalSecurity' => $rentalSecurity,
            'totalPayableAmount' => CommonHelper::displayMoneyFormat($totalPayableAmount),
            'durationDiscount' => (isset($durationDiscountRow['produr_id'])) ? $durationDiscountRow['produr_id'] : 0
        );
        FatUtility::dieJsonSuccess($data);
    }
    
    public function getFullfillmentData() 
    {
        $fullfillment = FatApp::getPostedData('fullfillmentType', FatUtility::VAR_INT, 1);
        $productType = FatApp::getPostedData('productType', FatUtility::VAR_INT, 2);
        $selprodId = FatApp::getPostedData('sellerProductId', FatUtility::VAR_INT, 0);
        $sellprodData = SellerProduct::getAttributesById($selprodId, ['selprod_user_id', 'selprod_product_id']);
        
        if (1 > $fullfillment || 1 > $productType || 1 > $selprodId || empty($sellprodData)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        
        if ($fullfillment == Shipping::FULFILMENT_SHIP) {
            $countryObj = new Countries();
            $this->set('countriesArr', $countryObj->getCountriesArr($this->siteLangId));
            $this->set('productType', $productType);
            $this->set('selprodId', $selprodId);
            $this->_template->render(false, false, 'products/shipping-locations-form.php');
        } else {
            $sellerId = $sellprodData['selprod_user_id'];
            if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && $productType == applicationConstants::PRODUCT_FOR_SALE) {
                $sellerId = 0; 
            } elseif($productType == applicationConstants::PRODUCT_FOR_SALE) {
                $srch = new SearchBase(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'psbs');
                $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'product_id = psbs.psbs_product_id');
                $srch->doNotCalculateRecords();
                $srch->addCondition('psbs.psbs_product_id', '=', $sellprodData['selprod_product_id']);
                $cnd = $srch->addCondition('psbs.psbs_user_id', '=', $sellprodData['selprod_user_id']);
                $cnd->attachCondition('product_seller_id', '=', $sellprodData['selprod_user_id']);
                $srch->addFld('psbs_user_id');
                $rs = $srch->getResultSet();
                $shippedByRow = (array) FatApp::getDb()->fetch($rs);
                $sellerId = (empty($shippedByRow)) ? 0 : $sellprodData['selprod_user_id'];
            }
            
            $addObj = new Address(0, $this->siteLangId);
            if ($productType == applicationConstants::PRODUCT_FOR_RENT) {
                $addresses = $addObj->getPickupData(Address::TYPE_SHOP_PICKUP, $selprodId, 0, false);
                if (empty($addresses)) {
                    $shopDetails = Shop::getAttributesByUserId($sellerId, null, false);
                    $addresses = $addObj->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);
                }
            } else {
                if ($sellerId > 0) { /* GET SELLER PICKUP ADDRESS */
                    $shopDetails = Shop::getAttributesByUserId($sellerId, null, false);
                    $addresses = $addObj->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);
                } else { /* GET ADMIN PICKUP ADDRESS */
                    $addresses = $addObj->getData(Address::TYPE_ADMIN_PICKUP, 0);
                }
            }
            
            $this->set('addresses', $addresses);
            $this->set('productType', $productType);
            $this->set('selprodId', $selprodId);
            $this->_template->render(false, false, 'products/pickup-locations.php');
        }
    }
    
    public function getShippingLocations() 
    {
        $countryId = FatApp::getPostedData('country_id', FatUtility::VAR_INT, -1);
        $stateId = FatApp::getPostedData('state_id', FatUtility::VAR_INT, -1);
        $selprodId = FatApp::getPostedData('selprodId', FatUtility::VAR_INT, 0);
        $productType = FatApp::getPostedData('productType', FatUtility::VAR_INT, applicationConstants::PRODUCT_FOR_RENT);
        $sellprodData = SellerProduct::getAttributesById($selprodId, ['selprod_user_id', 'selprod_product_id']);
        
        if (1 > $countryId || 1 > $selprodId || empty($sellprodData)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        
        $sellerId = $sellprodData['selprod_user_id'];
        if ($productType == applicationConstants::PRODUCT_FOR_SALE) {
            if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && $productType == applicationConstants::PRODUCT_FOR_SALE) {
                $sellerId = 0; 
            } elseif($productType == applicationConstants::PRODUCT_FOR_SALE) {
                $srch = new SearchBase(Product::DB_PRODUCT_SHIPPED_BY_SELLER, 'psbs');
                $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'product_id = psbs.psbs_product_id');
                $srch->doNotCalculateRecords();
                $srch->addCondition('psbs.psbs_product_id', '=', $sellprodData['selprod_product_id']);
                $cnd = $srch->addCondition('psbs.psbs_user_id', '=', $sellprodData['selprod_user_id']);
                $cnd->attachCondition('product_seller_id', '=', $sellprodData['selprod_user_id']);
                $srch->addFld('psbs_user_id');
                $rs = $srch->getResultSet();
                $shippedByRow = (array) FatApp::getDb()->fetch($rs);
                $sellerId = (empty($shippedByRow)) ? 0 : $sellprodData['selprod_user_id'];
            }
        }
        
        $srch = ShippingZone::getZoneLocationSearchObject($this->siteLangId, true);
        $srch->joinTable(ShippingProfileZone::DB_TBL, 'INNER JOIN', 'shipprozone_shipzone_id = shiploc_shipzone_id', 'pzone');
        $srch->joinTable(ShippingProfileProduct::DB_TBL, 'INNER JOIN', 'shipprozone_shipprofile_id = shippro_shipprofile_id AND shippro_user_id = '. $sellerId .' AND shippro_product_id = '. $sellprodData['selprod_product_id']);
        
        $srchStr = '';
        if ($stateId != 0) {
           $srchStr =  "AND(shiploc_state_id = '-1' OR shiploc_state_id = '". $stateId ."')";
        }
        
        $srch->addDirectCondition("shiploc_country_id = '-1' OR(shiploc_country_id = '". $countryId ."' ". $srchStr ." )");
        
        $rs = $srch->getResultSet();
        $locations = FatApp::getDb()->fetchAll($rs);
        $locationGrpData = [];
        
        if (!empty($locations)) {
            $locationCount = count($locations);
            foreach($locations as $location) {
                if ($locationCount > 1 && $location['shiploc_zone_id'] == -1) {
                    continue;
                }
                if ($location['shiploc_state_id'] == -1) {
                    $location['state_name'] = Labels::getLabel('LBL_Shipable_to_All_states', $this->siteLangId);
                }
                
                $locationGrpData[$location['shiploc_country_id']]['locations'][] = $location;
                if (!isset($locationGrpData[$location['shiploc_country_id']]['country_name'])) {
                    $locationGrpData[$location['shiploc_country_id']]['country_name'] = $location['country_name'];
                }
            }
        }
        
        $this->set('locationsData', $locationGrpData);
        $this->_template->render(false, false, 'products/shipping-locations.php');
    }
    
}