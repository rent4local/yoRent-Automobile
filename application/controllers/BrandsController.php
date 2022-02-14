<?php

class BrandsController extends MyAppController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $brandSrch = Brand::getListingObj($this->siteLangId, array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->doNotCalculateRecords();
        $brandSrch->doNotLimitRecords();
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brandsArr = FatApp::getDb()->fetchAll($brandRs);
        if (true === MOBILE_APP_API_CALL) {
            $db = FatApp::getDb();
            $totalProdCountToDisplay = 4;
            $productCustomSrchObj = new ProductSearch($this->siteLangId);
            $productCustomSrchObj->joinProductToCategory($this->siteLangId);
            $productCustomSrchObj->setDefinedCriteria();
            $productCustomSrchObj->joinSellerSubscription($this->siteLangId, true);
            $productCustomSrchObj->addSubscriptionValidCondition();

            if (UserAuthentication::isUserLogged()) {
                $productCustomSrchObj->joinFavouriteProducts(UserAuthentication::getLoggedUserId());
            }

            $productCustomSrchObj->joinProductRating();
            $productCustomSrchObj->addCondition('selprod_deleted', '=', 'mysql_func_'. applicationConstants::NO, 'AND', true);
            $productCustomSrchObj->addGroupBy('selprod_id');

            $productCustomSrchObj->addMultipleFields(
                    array('product_id', 'selprod_id', 'IFNULL(product_name, product_identifier) as product_name', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title',
                        'special_price_found', 'splprice_display_list_price', 'splprice_display_dis_val', 'splprice_display_dis_type',
                        'theprice', 'selprod_price', 'selprod_stock', 'selprod_condition', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'ifnull(sq_sprating.prod_rating,0) prod_rating ', 'ifnull(sq_sprating.totReviews,0) totReviews', 'selprod_sold_count', 'selprod_min_order_qty')
            );
            if (UserAuthentication::isUserLogged()) {
                $productCustomSrchObj->addFld(array('IF(ufp_id > 0, 1, 0) as isfavorite', 'IFNULL(ufp_id, 0) as ufp_id'));
            } else {
                $productCustomSrchObj->addFld(array('0 as isfavorite', '0 as ufp_id'));
            }

            $productCustomSrchObj->setPageSize($totalProdCountToDisplay);
            $cnt = 0;
            foreach ($brandsArr as $val) {
                $prodSrch = clone $productCustomSrchObj;
                $prodSrch->addBrandCondition($val['brand_id']);
                $prodSrch->addGroupBy('selprod_id');
                $prodRs = $prodSrch->getResultSet();
                $brandsArr[$cnt] = $val;
                $brandProducts = $db->fetchAll($prodRs);

                foreach ($brandProducts as &$brandProduct) {
                    $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($brandProduct['product_id'], "MEDIUM", $brandProduct['selprod_id'], 0, $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                    $brandProduct['discounted_text'] = CommonHelper::showProductDiscountedText($brandProduct, $this->siteLangId);
                    $brandProduct['product_image'] = $mainImgUrl;
                    $brandProduct['currency_selprod_price'] = CommonHelper::displayMoneyFormat($brandProduct['selprod_price'], true, false, false);
                    $brandProduct['currency_theprice'] = CommonHelper::displayMoneyFormat($brandProduct['theprice'], true, false, false);
                }
                $brandsArr[$cnt]['brand_image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'brand', array($val['brand_id'], $this->siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                $brandsArr[$cnt]['products'] = $brandProducts;
                $brandsArr[$cnt]['totalProducts'] = $prodSrch->recordCount();
                $cnt++;
            }
        }
        $this->set('layoutDirection', Language::getLayoutDirection($this->siteLangId));
        $this->set('allBrands', $brandsArr);
        $this->_template->render();
    }

    public function all()
    {
        FatApp::redirectUser(UrlHelper::generateUrl('Brands'));
    }

    public function view($brandId)
    {
        $brandId = FatUtility::int($brandId);
        Brand::recordBrandWeightage($brandId);

        $db = FatApp::getDb();

        $brandSrch = Brand::getListingObj($this->siteLangId, array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'), true);
        $brandSrch->addCondition('brand_id', '=', 'mysql_func_'.$brandId, 'AND', true);
        $brandSrch->addOrder('brand_name', 'asc');
        $brandRs = $brandSrch->getResultSet();
        $brand = FatApp::getDb()->fetch($brandRs);

        if (empty($brand)) {
            FatUtility::exitWithErrorCode(404);
        }

        $frm = $this->getProductSearchForm();

        $get = FatApp::getParameters();
        $get = Product::convertArrToSrchFiltersAssocArr($get);

        $get['join_price'] = 1;
        $get['brand_id'] = $brandId;
        $get['brand'] = array($brandId); /* For filters */
        $get['vtype']  = $get['vtype'] ?? 'grid';
        $get['selectedFulfillmentType'] = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;
        $buyerAddress = $userAddress = Address::getYkGeoData();
        $countryId = ((isset($buyerAddress['ykGeoCountryId'])) && $buyerAddress['ykGeoCountryId']) ? $buyerAddress['ykGeoCountryId'] : 0;
        
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) =='' && $get['vtype'] == 'map') {
            $get['vtype'] = 'grid';
        }
        if (empty($get['producttype'])) {
            if (ALLOW_RENT) {
                $get['producttype'] = array(Product::PRODUCT_FOR_RENT);
            } else if (ALLOW_SALE) {
                $get['producttype'] = array(Product::PRODUCT_FOR_SALE);
            }
        }

        $frm->fill($get);


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
            if (0 >= $pageSize) {
                $pageSize = FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10);
            }
        }

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
        
        

        $compProdCount = 0;
        $comparedProdSpecCatId = 0;
        if (!empty($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) {
            $comparedProdSpecCatId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
            $compProdCount = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
        }
		
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
        $srchForm = Common::getSiteSearchForm();
        $srchForm->fill($get);
        $get['pageSize'] = $pageSize;
        $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);
        
        $moreSellersArr = [];
        if($get['vtype'] == 'map'){            
            if(0 < count($products)){           
                $selprodCodes = array_column($products, 'selprod_code');               
                $moreSellersArr = Product::getMoreSeller($selprodCodes, $this->siteLangId);
            }
        }
        $data = array(
            'frmProductSearch' => $frm,
            'products' => $products,
            'moreSellersProductsArr' => $moreSellersArr,
            'pageRecordCount' => count($products),
            'prodCatAttributes' => $prodCatAttributes,
            'prodCustomFldsData' => $prodCustomFldsData,
            'page' => $page,
            'pageSize' => $pageSize,
            'pageSizeArr' => $pageSizeArr,
            'pageCount' => $srch->pages(),
            'postedData' => $get,
            'recordCount' => $srch->recordCount(),
            'pageTitle' => $brand['brand_name'],
            'canonicalUrl' => UrlHelper::generateFullUrl('Brands', 'view', array($brandId)),
            'productSearchPageType' => SavedSearchProduct::PAGE_BRAND,
            'recordId' => $brandId,
            'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'brands'),
            'siteLangId' => $this->siteLangId,
            'showBreadcrumb' => true,
            'searchForm' => $srchForm
        );

        if (FatUtility::isAjaxCall()) {
            $this->set('products', $products);
            $this->set('moreSellersProductsArr', $data['moreSellersProductsArr']);
            $this->set('prodCatAttributes', $prodCatAttributes);
            $this->set('prodCustomFldsData', $prodCustomFldsData);
            $this->set('page', $page);
            $this->set('pageSize', $pageSize);
            $this->set('pageSizeArr', $pageSizeArr);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $get);
            $this->set('recordCount', $srch->recordCount());
            $this->set('siteLangId', $this->siteLangId);
            $this->set('compProdCount', $compProdCount);
            $this->set('comparedProdSpecCatId', $comparedProdSpecCatId);
            echo $this->_template->render(false, false, 'products/products-list.php', true);
            exit;
        }

        $this->set('data', $data);
        $this->includeProductPageJsCss();
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render();
    }

    public function autoComplete()
    {
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE');
        $post = FatApp::getPostedData();
        $fetchAllRecords = FatApp::getPostedData('fetchAllRecords', FatUtility::VAR_INT, 0);
        $brandObj = new Brand();
        $srch = $brandObj->getSearchObject($this->siteLangId, true, true);

        $srch->addMultipleFields(array('brand_id, IFNULL(brand_name, brand_identifier) as brand_name'));

        if (!empty($post['keyword'])) {
            $cond = $srch->addCondition('brand_name', 'LIKE', '%' . $post['keyword'] . '%');
            $cond->attachCondition('brand_identifier', 'LIKE', '%' . $post['keyword'] . '%', 'OR');
        }
        $srch->addCondition('brand_status', '=', 'mysql_func_'. Brand::BRAND_REQUEST_APPROVED, 'AND', true);

        //$srch->setPageSize($pagesize);
        if ($fetchAllRecords == 1) {
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        } else {
            $srch->setPageSize($pagesize);
        }
        $rs = $srch->getResultSet();
        $db = FatApp::getDb();
        $brands = $db->fetchAll($rs, 'brand_id');
        $json = array();
        foreach ($brands as $key => $brand) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($brand['brand_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
        /* $this->set('brands', $db->fetchAll($rs,'brand_id') );
          $this->_template->render(false,false); */
    }

    public function checkUniqueBrandName()
    {
        $post = FatApp::getPostedData();

        $langId = FatUtility::int($post['langId']);

        $brandName = $post['brandName'];
        $brandId = FatUtility::int($post['brandId']);
        if (1 > $langId) {
            trigger_error(Labels::getLabel('LBL_Lang_Id_not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (1 > $brandId) {
            trigger_error(Labels::getLabel('LBL_Brand_Id_not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = Brand::getSearchObject($langId);
        $srch->addCondition('brand_name', '=', $brandName);
        if ($brandId) {
            $srch->addCondition('brand_id', '!=', 'mysql_func_'. $brandId, 'AND', true);
        }
        $rs = $srch->getResultSet();
        $records = $srch->recordCount();
        if ($records > 0) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_%s_not_available', $this->siteLangId), $brandName));
        }
        FatUtility::dieJsonSuccess(array());
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'view':
                $nodes[] = array('title' => Labels::getLabel('LBL_Brands', $this->siteLangId), 'href' => UrlHelper::generateUrl('brands'));
                if (isset($parameters[0]) && $parameters[0] > 0) {
                    $brandId = FatUtility::int($parameters[0]);
                    if ($brandId > 0) {
                        $brandSrch = Brand::getListingObj($this->siteLangId, array('IFNULL(brand_name, brand_identifier) as brand_name',));
                        $brandSrch->doNotCalculateRecords();
                        $brandSrch->doNotLimitRecords();
                        $brandSrch->addCondition('brand_id', '=', 'mysql_func_'.$brandId, 'AND', true);
                        $brandRs = $brandSrch->getResultSet();
                        $brandsArr = FatApp::getDb()->fetch($brandRs);
                        $nodes[] = array('title' => $brandsArr['brand_name']);
                    }
                }

                break;

            case 'index':
                $nodes[] = array('title' => Labels::getLabel('LBL_Brands', $this->siteLangId));

                break;
        }
        return $nodes;
    }

}
