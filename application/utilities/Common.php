<?php

class Common
{

    public static function headerWishListAndCartSummary($template)
    {
        $cartObj = new Cart();
        $cartObj->invalidateCheckoutType();
        $siteLangId = CommonHelper::getLangId();
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wislistPSrchObj->addGroupBy('uwlp_selprod_id');
        $wislistPSrchObj->addMultipleFields(array('uwlp_uwlist_id'));
        $rs = $wislistPSrchObj->getResultSet();
        $totalWishListItems = $wislistPSrchObj->recordCount();
        if (FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0)) {
            $cartObj->excludeTax();
        }
        $productsArr = $cartObj->getCartProductsDataAccToAddons($siteLangId);
        $cartSummary = $cartObj->getCartFinancialSummary($siteLangId);

        $template->set('siteLangId', $siteLangId);
        $template->set('products', $productsArr);
        $template->set('cartSummary', $cartSummary);
        $template->set('totalWishListItems', $totalWishListItems);
        $template->set('totalCartItems', $cartObj->countProducts());
    }

    public static function countWishList()
    {
        $loggedUserId = 0;
        if (UserAuthentication::isUserLogged()) {
            $loggedUserId = UserAuthentication::getLoggedUserId();
        }

        $wislistPSrchObj = new UserWishListProductSearch();
        $wislistPSrchObj->joinSellerProducts();
        $wislistPSrchObj->joinProducts();
        $wislistPSrchObj->joinSellers();
        $wislistPSrchObj->joinShops();
        $wislistPSrchObj->joinProductToCategory();
        $wislistPSrchObj->joinSellerSubscription();
        $wislistPSrchObj->addSubscriptionValidCondition();
        $wislistPSrchObj->joinWishLists();
        $wislistPSrchObj->doNotLimitRecords();
        $wislistPSrchObj->addCondition('uwlist_user_id', '=', $loggedUserId);
        $wislistPSrchObj->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $wislistPSrchObj->addCondition('selprod_active', '=', applicationConstants::YES);
        $wislistPSrchObj->addGroupBy('uwlp_selprod_id');
        $wislistPSrchObj->addMultipleFields(array('uwlp_uwlist_id'));
        $rs = $wislistPSrchObj->getResultSet();
        $totalWishListItems = $wislistPSrchObj->recordCount();

        return $totalWishListItems;
    }

    public static function setHeaderBreadCrumb($template)
    {
        $controllerName = FatApp::getController();
        $action = FatApp::getAction();

        $controller = new $controllerName('');
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('nodes', $controller->getBreadcrumbNodes($action));
    }

    public static function headerUserArea($template)
    {
        $template->set('siteLangId', CommonHelper::getLangId());
        $isUserLogged = UserAuthentication::isUserLogged();
        $template->set('isUserLogged', $isUserLogged);
        if ($isUserLogged) {
            $userId = UserAuthentication::getLoggedUserId();
            $userImgUpdatedOn = User::getAttributesById($userId, 'user_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
            $profileImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Account', 'userProfileImage', array($userId, 'THUMB', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $template->set('userName', ucfirst(CommonHelper::getUserFirstName(UserAuthentication::getLoggedUserAttribute('user_name'))));
            $template->set('userEmail', UserAuthentication::getLoggedUserAttribute('user_email'));
            $template->set('profilePicUrl', $profileImage);
        }
    }

    public static function headerSearchFormArea($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $headerSrchFrm = static::getSiteSearchForm();
        $headerSrchFrm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this, ' . FatApp::getConfig('CONF_ITEMS_PER_PAGE_CATALOG', FatUtility::VAR_INT, 10) . '); return(false);');

        /* to fill the posted data to form[ */
        $paramsArr = FatApp::getParameters();
        $paramsAssocArr = CommonHelper::arrayToAssocArray($paramsArr);
        $headerSrchFrm->fill($paramsAssocArr);
        /* ] */

        $headerRootCatArr = FatCache::get('headerRootCatArr' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerRootCatArr) {
            $categoriesArr = unserialize($headerRootCatArr);
        } else {
            /* SubQuery, Category have products[ */
            $prodCatCache = FatCache::get('prodCatCache' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($prodCatCache) {
                $productRows = unserialize($prodCatCache);
            } else {
                $prodSrchObj = new ProductSearch();
                $prodSrchObj->setDefinedCriteria(0, 0, array('doNotJoinSpecialPrice' => true));
                $prodSrchObj->joinProductToCategory($siteLangId);
                $prodSrchObj->doNotCalculateRecords();
                $prodSrchObj->doNotLimitRecords();
                $prodSrchObj->joinSellerSubscription($siteLangId, true);
                $prodSrchObj->addSubscriptionValidCondition();
                $prodSrchObj->addGroupBy('prodcat_id');
                $prodSrchObj->addMultipleFields(array('prodcat_code AS prodrootcat_code', 'count(selprod_id) as productCounts', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_parent'));
                $prodSrchObj->addOrder('prodcat_display_order', 'asc');
                $rs = $prodSrchObj->getResultSet();
                $productRows = FatApp::getDb()->fetchAll($rs);
                FatCache::set('prodCatCache' . $siteLangId, serialize($productRows), '.txt');
            }
            $mainRootCategories = FatUtility::int(array_column($productRows, 'prodrootcat_code'));

            $categoriesMainRootArr = array();

            if ($productRows) {
                $categoriesMainRootArr = array_unique($mainRootCategories);
                array_flip($categoriesMainRootArr);
            }
            /* ] */

            $catSrch = ProductCategory::getSearchObject(false, $siteLangId);
            $catSrch->addOrder('m.prodcat_active', 'DESC');
            $catSrch->addMultipleFields(array('prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as category_name'));
            $catSrch->addOrder('category_name');
            $catSrch->doNotCalculateRecords();
            $catSrch->addCondition('prodcat_active', '=', applicationConstants::YES);
            $catSrch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
            if ($categoriesMainRootArr) {
                $catSrch->addCondition('prodcat_id', 'in', $categoriesMainRootArr);
            }
            $catSrch->setPageSize(25);
            $catRs = $catSrch->getResultSet();
            $categoriesArr = [];
            while ($row = FatApp::getDb()->fetch($catRs)) {
                $categoriesArr[$row['prodcat_id']] = strip_tags($row['category_name']);
            }
        }

        FatCache::set('headerRootCatArr' . $siteLangId, serialize($categoriesArr), '.txt');

        $template->set('categoriesArr', $categoriesArr);
        $template->set('headerSrchFrm', $headerSrchFrm);
        $template->set('siteLangId', $siteLangId);
        //$productRootCategoriesArr = $prodCatObj->getCategoriesForSelectBox($siteLangId, 0, true); */
        //ProductCategory::getRootProdCatAssocArr($siteLangId, 0);
        /* ob_end_clean();
          CommonHelper::printArray($data);
          die(); */
        //$template->set( 'productRootCategoriesArr', $productRootCategoriesArr );
    }

    public static function getSiteSearchForm($isHome = false)
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = new Form('frmSiteSearch');
        $frm->setFormTagAttribute('class', 'main-search-form');
        $frm->setFormTagAttribute('autocomplete', 'off');
        /* $frm->addSelectBox('', 'category', $categoriesArr, '', array(), Labels::getLabel('LBL_All', CommonHelper::getLangId()) ); */
        $frm->addTextBox(Labels::getLabel('LBL_Search_any_product', CommonHelper::getLangId()), 'keyword');
        $frm->addTextBox(Labels::getLabel('LBL_LOCATION', CommonHelper::getLangId()), 'location');
        $frm->addTextBox(Labels::getLabel('LBL_Rental_Dates', CommonHelper::getLangId()), 'rentaldates');
        $frm->addHiddenField('', 'rentalstart');
        $frm->addHiddenField('', 'rentalend');
        $frm->addHiddenField('', 'category');
        //$frm->addSubmitButton('', 'btnSiteSrchSubmit', Labels::getLabel('LBL_Search', CommonHelper::getLangId()));
        if ($isHome) {
            $frm->addHTML(null, 'searchButton', '<button name="submit" type="submit" class="btn btn-brand btn-search btn-wide-full"> <i class="icn icn-maginifier"><svg class="svg"><use xlink:href="' . CONF_WEBROOT_URL .'images/' . ACTIVE_THEME . '/retina/sprite-front.svg#maginifier"></use></svg></i>' . Labels::getLabel('LBL_Search', CommonHelper::getLangId()) . '</button>');
        } else {
            $frm->addHTML(null, 'searchButton', '<button name="submit" type="submit" class="search-button"><i class="icn"><svg class="svg"><use xlink:href="'.  CONF_WEBROOT_URL . 'images/'. ACTIVE_THEME .'/retina/sprite-front.svg#maginifier"></use></svg></i><span class="btn-txt">' . Labels::getLabel('LBL_Search', CommonHelper::getLangId()) . '</span></button>');
        }
        return $frm;
    }

    public static function headerLanguageArea($template)
    {
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('siteCurrencyId', CommonHelper::getCurrencyId());
        $template->set('languages', Language::getAllNames(false));
        $template->set('currencies', Currency::getCurrencyAssoc(CommonHelper::getLangId()));
    }

    public static function footerNewsLetterForm($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $frm = static::getNewsLetterForm($siteLangId);
        $template->set('frm', $frm);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTopBrands($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $brandSrch = Brand::getSearchObject($siteLangId);
        $brandSrch->joinTable(Product::DB_TBL, 'INNER JOIN', 'brand_id = p.product_brand_id', 'p');
        $brandSrch->joinTable(SellerProduct::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = p.product_id', 'sp');
        $brandSrch->doNotCalculateRecords();
        $brandSrch->addMultipleFields(array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'SUM(IFNULL(selprod_sold_count, 0)) as totSoldQty'));
        $brandSrch->addCondition('brand_status', '=', Brand::BRAND_REQUEST_APPROVED);
        $brandSrch->addCondition('brand_active', '=', applicationConstants::YES);
        $brandSrch->addGroupBy('brand_id');
        $brandSrch->addHaving('totSoldQty', '>', 0);
        $brandSrch->addOrder('totSoldQty', 'DESC');
        $brandSrch->addOrder('brand_name');
        $brandSrch->setPageSize(25);

        $brandRs = $brandSrch->getResultSet();
        $topBrands = FatApp::getDb()->fetchAll($brandRs);
        $template->set('topBrands', $topBrands);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTopCategories($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $catSrch = new ProductCategorySearch($siteLangId, true, true, false, false);
        $catSrch->joinTable(Product::DB_TBL_PRODUCT_TO_CATEGORY, 'LEFT OUTER JOIN', 'c.prodcat_id = ptc.ptc_prodcat_id', 'ptc');
        $catSrch->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_product_id = ptc.ptc_product_id', 'sp');
        $catSrch->doNotCalculateRecords();
        $catSrch->addMultipleFields(array('c.prodcat_id', 'IFNULL(c_l.prodcat_name, c.prodcat_identifier) as prodcat_name', 'SUM(IFNULL(selprod_sold_count, 0)) as totSoldQty'));
        $catSrch->addCondition('prodcat_active', '=', applicationConstants::YES);
        $catSrch->addCondition('prodcat_deleted', '=', applicationConstants::NO);
        $catSrch->addGroupBy('prodcat_id');
        $catSrch->addHaving('totSoldQty', '>', 0);
        $catSrch->addOrder('totSoldQty', 'DESC');
        $catSrch->addOrder('prodcat_name');
        $catSrch->setPageSize(25);

        $catRs = $catSrch->getResultSet();
        $topCategories = FatApp::getDb()->fetchAll($catRs);
        $template->set('topCategories', $topCategories);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerTrustBanners($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $obj = new Extrapage();
        $footerData = $obj->getContentByPageType(Extrapage::FOOTER_TRUST_BANNERS, $siteLangId);
        $template->set('footerData', $footerData);
    }

    public static function getNewsLetterForm($langId)
    {
        $frm = new Form('frmNewsLetter');
        $frm->setRequiredStarWith('');
        $fld1 = $frm->addEmailField('', 'email');
        $fld1->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_Enter_Your_email_Address', $langId));
        $fld2 = $frm->addSubmitButton('', 'btnSubmit', Labels::getLabel('LBL_Subscribe', $langId));
        $fld1->attachField($fld2);
        $frm->setJsErrorDiv('customErrorJs');
        $frm->setJsErrorDisplay(Form::FORM_ERROR_TYPE_SUMMARY);
        return $frm;
    }

    /* public static function brandFilters($template)
      {
      $brandSrch = clone $prodSrchObj;
      $brandSrch->addGroupBy('brand_id');
      $brandSrch->addOrder('brand_name');
      $brandSrch->addMultipleFields(array('brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name'));
      /* if needs to show product counts under brands[ */

    //$brandSrch->addFld('count(selprod_id) as totalProducts');
    /* ] *//*
      //echo $brandSrch->getQuery(); die();
      $brandRs = $brandSrch->getResultSet();
      $brandsArr = FatApp::getDb()->fetchAll($brandRs);
      $template->set('brandsArr', $brandsArr);
      } */

    public static function userMessages($template)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $srch = new MessageSearch();
        $srch->joinThreadMessage();
        $srch->joinMessagePostedFromUser();
        $srch->joinMessagePostedToUser();
        $srch->addMultipleFields(array('tth.*', 'ttm.message_id', 'ttm.message_text', 'ttm.message_date', 'ttm.message_is_unread'));
        $srch->addCondition('ttm.message_deleted', '=', 0);
        //$cnd = $srch->addCondition('ttm.message_from','=',$userId);
        $srch->addCondition('ttm.message_to', '=', $userId);
        $srch->addOrder('message_id', 'DESC');
        $srch->setPageSize(3);
        $rs = $srch->getResultSet();
        $messages = FatApp::getDb()->fetchAll($rs);
        $template->set('messages', $messages);
        $template->set('siteLangId', CommonHelper::getLangId());
    }

    public static function footerSocialMedia($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $srch = SocialPlatform::getSearchObject($siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', 0);
        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);

        $template->set('rows', $rows);
        $template->set('siteLangId', $siteLangId);
    }

    public static function footerUserLangArea($template)
    {
        $template->set('siteLangId', CommonHelper::getLangId());
        $template->set('siteCurrencyId', CommonHelper::getCurrencyId());
        $template->set('languages', Language::getAllNames(false));
        $template->set('currencies', Currency::getCurrencyAssoc(CommonHelper::getLangId()));
    }

    public static function homePageBelowSlider($template)
    {
        $siteLangId = CommonHelper::getLangId();
    }

    public static function productDetailPageBanner($template)
    {
        $siteLangId = CommonHelper::getLangId();
    }

    public static function blogSidePanelArea($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $blogSrchFrm = static::getBlogSearchForm();
        $blogSrchFrm->setFormTagAttribute('action', UrlHelper::generateUrl('Blog'));

        /* to fill the posted data into form[ */
        $postedData = FatApp::getPostedData();
        $blogSrchFrm->fill($postedData);
        /* ] */

        /* Right Side Categories Data[ */
        $categoriesArr = BlogPostCategory::getBlogPostCatParentChildWiseArr($siteLangId);
        $template->set('categoriesArr', $categoriesArr);
        /* ] */

        $template->set('blogSrchFrm', $blogSrchFrm);
        $template->set('siteLangId', $siteLangId);
    }

    public static function blogTopFeaturedCategories($template)
    {
        $siteLangId = CommonHelper::getLangId();

        $bpCatObj = new BlogPostCategory();
        $arrCategories = $bpCatObj->getFeaturedCategories($siteLangId);
        $categories = $bpCatObj->makeAssociativeArray($arrCategories);
        $template->set('featuredBlogCategories', $categories);
        $template->set('siteLangId', $siteLangId);
    }

    public static function getBlogSearchForm()
    {
        $frm = new Form('frmBlogSearch');
        $frm->setFormTagAttribute('autocomplete', 'off');
        $frm->addTextBox('', 'keyword', '');
        $frm->addHiddenField('', 'page', 1);
        $frm->addSubmitButton('', 'btn_submit', '');
        return $frm;
    }

    /* GET DURATION BETWEEN DATES */

    public static function daysBetweenDates($startDate, $endDate)
    {
        $days = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24)) + 1;
        return $days > 0 ? ceil($days) : 1;
    }

    public static function hoursBetweenDates($startDate, $endDate)
    {
        $hours = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60));
        return $hours > 0 ? ceil($hours) : 1;
    }

    public static function weeksBetweenDates($startDate, $endDate)
    {
        $weeks = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24 * 7));
        return $weeks > 0 ? ceil($weeks) : 1;
    }

    public static function monthsBetweenDates($startDate, $endDate)
    {
        /* $months = abs((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24 * 30));
          return $months > 0 ? ceil($months) : 1; */
        $date1 = new DateTime($startDate);
        $date2 = new DateTime($endDate);

        $diff = $date1->diff($date2);
        $years = $diff->y;
        $months = $diff->m + ($years * 12);
        $totalDays = $diff->d;
        $hours = $diff->h;
        $minutes = $diff->i;
        if ($totalDays > 0 || $hours > 0 || $minutes > 0) {
            $months ++;
        }
        return $months;
    }

}
