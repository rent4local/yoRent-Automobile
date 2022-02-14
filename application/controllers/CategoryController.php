<?php

class CategoryController extends MyAppController
{

    public function __construct($action)
    {
        parent::__construct($action);
    }

    public function index()
    {
        $categoriesArr = ProductCategory::getTreeArr($this->siteLangId, 0, true, false, true);
        $this->set('categoriesArr', $categoriesArr);
        $this->_template->render();
    }

    public function search()
    {
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $pageSize = FatApp::getConfig('CONF_CATEGORIES_PER_PAGE_ON_CATEGORY_PAGE', FatUtility::VAR_INT, 50);
        $productCategory = ProductCategory::getSearchObject(false, $this->siteLangId, true);
        $productCategory->addOrder('m.prodcat_active', 'DESC');
        $productCategory->addCondition('prodcat_parent', '=', 'mysql_func_0', 'AND', true);
        $productCategory->addCondition('prodcat_deleted', '=', 'mysql_func_0', 'AND', true);
        $productCategory->addOrder('prodcat_ordercode');
        $productCategory->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name'));
        $productCategory->setPageNumber($page);
        $productCategory->setPageSize($pageSize);
        $rs = $productCategory->getResultSet();
        $categoriesArr = FatApp::getDb()->fetchAll($rs);
        $this->set('categoriesArr', $categoriesArr);
        $this->set('page', $page);
        $this->set('recordCount', $productCategory->recordCount());
        $this->set('pageCount', $productCategory->pages());
        $this->set('postedData', FatApp::getPostedData());
        $this->_template->render(false, false);
    }

    public function view($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);
        ProductCategory::recordCategoryWeightage($categoryId);

        $db = FatApp::getDb();
        $frm = $this->getProductSearchForm();
        if (true === MOBILE_APP_API_CALL) {
            $get = FatApp::getPostedData();
        } else {
            $get = Product::convertArrToSrchFiltersAssocArr(FatApp::getParameters());
        }

        $get['category'] = $categoryId;
        $get['join_price'] = 1;
        if (empty($get['producttype'])) {
            if (ALLOW_RENT) {
                $get['producttype'] = array(Product::PRODUCT_FOR_RENT);
            } else if (ALLOW_SALE) {
                $get['producttype'] = array(Product::PRODUCT_FOR_SALE);
            }
        }
        $get['vtype']  = $get['vtype'] ?? 'grid';
        $get['selectedFulfillmentType'] = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;
        $buyerAddress = $userAddress = Address::getYkGeoData();
        $countryId = ((isset($buyerAddress['ykGeoCountryId'])) && $buyerAddress['ykGeoCountryId']) ? $buyerAddress['ykGeoCountryId'] : 0;


        $frm->fill($get);

        $productCategorySearch = new ProductCategorySearch($this->siteLangId, true, true, false, false);
        $productCategorySearch->addCondition('prodcat_id', '=', 'mysql_func_'. $categoryId, 'AND', true);

        /* to show searched category data[ */
        $productCategorySearch->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_description', 'prodcat_code'));
        $productCategorySearch->setPageSize(1);
        $productCategorySearchRs = $productCategorySearch->getResultSet();
        $category = $db->fetch($productCategorySearchRs);

        if (false == $category) {
            if (true === MOBILE_APP_API_CALL) {
                $message = Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId);
                FatUtility::dieJsonError($message);
            }
            FatUtility::exitWithErrorCode(404);
        }
        $bannerDetail = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $categoryId);
        $category['banner'] = empty($bannerDetail) ? (object) array() : $bannerDetail;
        /* ] */

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
            } elseif (array_key_exists('selectedFulfillmentType', $get) && $get['selectedFulfillmentType'] == Shipping::FULFILMENT_SHIP && $countryId > 0) {
                $key = 'availableForShip';
            }

            if (!empty($sellerProductData)) {
                foreach ($sellerProductData as $sellerProduct) {
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
            $compProdCount = count($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products']);
            $comparedProdSpecCatId = $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['attr_grp_cat_id'];
        }
        $prodCatAttributes = [];
        $prodCustomFldsData = [];
        if (!empty($products) && applicationConstants::getActiveTheme() == applicationConstants::THEME_AUTOMOBILE) {
            $productCatArr = array($categoryId);
            $productIdsArr = array_unique(array_column($products, 'product_id'));
            $prodCatObj = new ProductCategory();
            $prodCatAttributes = $prodCatObj->getAttrDetail($this->siteLangId, 0, 'attr_attrgrp_id', $productCatArr, true);
            $prodCatAttributes = Product::formatArrByCatId($prodCatAttributes);
            $prodCustomFldsData = Product::getCustomFields($productIdsArr, $this->siteLangId);
        }
        $moreSellersArr = [];
        if ($get['vtype'] == 'map') {
            if (0 < count($products)) {
                $selprodCodes = array_column($products, 'selprod_code');
                $moreSellersArr = Product::getMoreSeller($selprodCodes, $this->siteLangId);
            }
        }
        $pageSizeArr = FilterHelper::getPageSizeArr($this->siteLangId);

        $get['pageSize'] = $pageSize;
        $data = array(
            'frmProductSearch' => $frm,
            'category' => $category,
            'products' => $products,
            'moreSellersProductsArr' => $moreSellersArr,
            'pageRecordCount' => count($products),
            'prodCatAttributes' => $prodCatAttributes,
            'prodCustomFldsData' => $prodCustomFldsData,
            'page' => $page,
            'pageSizeArr' => $pageSizeArr,
            'pageSize' => $pageSize,
            'categoryId' => $categoryId,
            'pageCount' => $srch->pages(),
            'postedData' => $get,
            'recordCount' => $srch->recordCount(),
            'pageTitle' => $category['prodcat_name'],
            'canonicalUrl' => UrlHelper::generateFullUrl('Category', 'view', array($categoryId)),
            'productSearchPageType' => SavedSearchProduct::PAGE_CATEGORY,
            'recordId' => $categoryId,
            'bannerListigUrl' => UrlHelper::generateFullUrl('Banner', 'categories'),
            'siteLangId' => $this->siteLangId,
            'showBreadcrumb' => true,
            'compProdCount' => $compProdCount,
            'comparedProdSpecCatId' => $comparedProdSpecCatId
        );

        if (FatUtility::isAjaxCall()) {
            $this->set('products', $products);
            $this->set('moreSellersProductsArr', $data['moreSellersProductsArr']);
            $this->set('prodCatAttributes', $prodCatAttributes);
            $this->set('prodCustomFldsData', $prodCustomFldsData);
            $this->set('page', $page);
            $this->set('pageSize', $data['pageSize']);
            $this->set('pageSizeArr', $data['pageSizeArr']);
            $this->set('pageCount', $srch->pages());
            $this->set('postedData', $get);
            $this->set('recordCount', $srch->recordCount());
            $this->set('siteLangId', $this->siteLangId);
            $this->set('compProdCount', $compProdCount);
            $this->set('comparedProdSpecCatId', $comparedProdSpecCatId);
            echo $this->_template->render(false, false, 'products/products-list.php', true);
            exit;
        }

        $srchForm = Common::getSiteSearchForm();
        $srchForm->fill($get);
        $data['searchForm'] = $srchForm;

        $this->set('data', $data);
        if (false === MOBILE_APP_API_CALL) {
            $this->includeProductPageJsCss();
            $this->_template->addJs('js/slick.min.js');
        }

        $this->_template->render();
    }

    public function image($catId, $langId = 0, $sizeType = '', $afileId = 0)
    {
        $catId = FatUtility::int($catId);
        $langId = FatUtility::int($langId);
        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_CATEGORY_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_IMAGE, $catId, 0, $langId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'COLLECTION_PAGE':
                $w = 45;
                $h = 41;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function icon(int $catId, int $langId = 0, $sizeType = '')
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_ICON, $catId, 0, $langId, true);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = 'category-icon-default.png';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 246;
                $h = 185;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION_PAGE':
                $w = 48;
                $h = 48;
                if (applicationConstants::getActiveTheme() == applicationConstants::THEME_FASHION) {
                    $w = 32;
                    $h = 32;
                }
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LARGE':
                $w = 522;
                $h = 441;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'HOME':
                $w = 380;
                $h = 285;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function sellerBanner($shopId, $prodCatId, $langId = 0, $sizeType = '')
    {
        $shopId = FatUtility::int($shopId);
        $prodCatId = FatUtility::int($prodCatId);
        $langId = FatUtility::int($langId);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shopId, $prodCatId, $langId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 250;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'WIDE':
                $w = 1320;
                $h = 320;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function banner($prodCatId, $langId = 0, $sizeType = '', $screen = 0, $displayUniversalImage = true, $afileId = 0)
    {
        $default_image = 'category_banner.png';
        $prodCatId = FatUtility::int($prodCatId);
        $langId = FatUtility::int($langId);
        $file_row = [];
        if ((isset($afileId)) && $afileId > 0) {
            $res = AttachedFile::getAttributesById($afileId);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_CATEGORY_BANNER) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_BANNER, $prodCatId, 0, $langId, $displayUniversalImage, $screen);
        }


        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 250;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 600;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MOBILE':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TABLET':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'DESKTOP':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function getBreadcrumbNodes($action)
    {
        $nodes = array();
        $parameters = FatApp::getParameters();
        switch ($action) {
            case 'view':
                if (isset($parameters[0]) && $parameters[0] > 0) {
                    $parent = FatUtility::int($parameters[0]);
                    if ($parent > 0) {
                        $cntInc = 1;
                        $prodCateObj = new ProductCategory();
                        $category_structure = $prodCateObj->getCategoryStructure($parent, '', $this->siteLangId);
                        $category_structure = array_reverse($category_structure);
                        foreach ($category_structure as $catKey => $catVal) {
                            if ($cntInc < count($category_structure)) {
                                $nodes[] = array('title' => $catVal["prodcat_name"], 'href' => Urlhelper::generateUrl('category', 'view', array($catVal['prodcat_id'])));
                            } else {
                                $nodes[] = array('title' => $catVal["prodcat_name"]);
                            }
                            $cntInc++;
                        }
                    }
                }
                break;

            case 'form':
                break;
        }
        return $nodes;
    }

    private function resetKeyValues($arr, $langId)
    {
        $langId = FatUtility::int($langId);
        $result = array();
        foreach ($arr as $key => $val) {
            if (!array_key_exists('prodcat_id', $val)) {
                continue;
            }
            $result[$key] = $val;
            $isLastChildCategory = ProductCategory::isLastChildCategory($val['prodcat_id']);
            $result[$key]['isLastChildCategory'] = $isLastChildCategory ? 1 : 0;
            $result[$key]['icon'] = UrlHelper::generateFullUrl('Category', 'icon', array($val['prodcat_id'], $langId, 'COLLECTION_PAGE'));
            $result[$key]['image'] = UrlHelper::generateFullUrl('Category', 'banner', array($val['prodcat_id'], $langId, 'MOBILE', applicationConstants::SCREEN_MOBILE));
            $childernArr = array();
            if (!empty($val['children'])) {
                $array = array_values($val['children']);
                $childernArr = $this->resetKeyValues($array, $langId);
            }
            $result[$key]['children'] = $childernArr;
        }
        return array_values($result);
    }

    public function structure()
    {
        $productCategory = new productCategory();

        $prodSrchObj = (true === MOBILE_APP_API_CALL ? false : new ProductCategorySearch($this->siteLangId));
        $parentId = FatApp::getPostedData('parentId', FatUtility::VAR_INT, 0);
        $includeChild = true;
        if (true === MOBILE_APP_API_CALL && 0 == $parentId) {
            $includeChild = false;
        }

        $categoriesArr = ProductCategory::getProdCatParentChildWiseArr($this->siteLangId, $parentId, $includeChild, false, false, $prodSrchObj, true);

        if (false === MOBILE_APP_API_CALL) {
            $categoriesArr = $productCategory->getCategoryTreeArr($this->siteLangId, $categoriesArr, array('prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier ) as prodcat_name', 'substr(GETCATCODE(prodcat_id),1,6) AS prodrootcat_code', 'prodcat_content_block', 'prodcat_active', 'prodcat_parent', 'GETCATCODE(prodcat_id) as prodcat_code'));
        }

        $categoriesArr = $this->resetKeyValues(array_values($categoriesArr), $this->siteLangId);

        if (empty($categoriesArr)) {
            $categoriesArr = array();
        }

        $this->set('categoriesData', $categoriesArr);
        $this->_template->render();
    }

    public function checkUniqueCategoryName()
    {
        $post = FatApp::getPostedData();

        $langId = FatUtility::int($post['langId']);

        $categoryName = $post['categoryName'];
        $categoryId = FatUtility::int($post['categoryId']);
        if (1 > $langId) {
            trigger_error(Labels::getLabel('LBL_Lang_Id_not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
        }
        if (1 > $categoryId) {
            trigger_error(Labels::getLabel('LBL_Brand_Id_not_Specified', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = productCategory::getSearchObject($langId);
        $srch->addOrder('m.prodcat_active', 'DESC');
        $srch->addCondition('prodcat_name', '=', $categoryName);
        if ($categoryId) {
            $srch->addCondition('prodcat_id', '!=', 'mysql_func_'. $categoryId, 'AND', true);
        }
        $rs = $srch->getResultSet();
        $records = $srch->recordCount();
        if ($records > 0) {
            FatUtility::dieJsonError(sprintf(Labels::getLabel('LBL_%s_not_available', $this->siteLangId), $categoryName));
        }
        FatUtility::dieJsonSuccess(array());
    }
}
