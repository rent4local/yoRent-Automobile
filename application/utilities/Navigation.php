<?php

class Navigation
{

    public static function headerTopNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $activeTheme = applicationConstants::getActiveTheme();
        $headerTopNavigationCache = FatCache::get('headerTopNavigation_'. $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');

        if ($headerTopNavigationCache) {
            $headerTopNavigation = unserialize($headerTopNavigationCache);
        } else {
            $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
            FatCache::set('headerTopNavigation_'. $activeTheme . '_' . $siteLangId, serialize($headerTopNavigation), '.txt');
        }
        $template->set('top_header_navigation', $headerTopNavigation);
    }

    public static function headerNavigation($template = '')
    {
        if (!$template) {
            $template = new FatTemplate('', '');
        }

        $siteLangId = CommonHelper::getLangId();
        $template->set('siteLangId', $siteLangId);
        $activeTheme = applicationConstants::getActiveTheme();
        $layout = /* FatApp::getConfig('CONF_LAYOUT_MEGA_MENU', FatUtility::VAR_INT, 1) */ 1;
        $headerNavigationCacheNew = FatCache::get('headerNavigation_Cache_'. $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavigationCacheNew) {
            echo $headerNavigationCacheNew = unserialize($headerNavigationCacheNew);
        } else {
            $headerNavigationCache = FatCache::get('headerNavigation_'. $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($headerNavigationCache) {
                $headerNavigation = unserialize($headerNavigationCache);
            } else {
                $headerNavigation = self::getNavigation(Navigations::NAVTYPE_HEADER, true);
                FatCache::set('headerNavigation_'. $activeTheme . '_' . $siteLangId, serialize($headerNavigation), '.txt');
            }

            $isUserLogged = UserAuthentication::isUserLogged();
            if ($isUserLogged) {
                $template->set('userName', ucfirst(CommonHelper::getUserFirstName(UserAuthentication::getLoggedUserAttribute('user_name'))));
            }

            $headerTopNavigationCache = FatCache::get('headerTopNavigation_' . $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($headerTopNavigationCache) {
                $headerTopNavigation = unserialize($headerTopNavigationCache);
            } else {
                $headerTopNavigation = self::getNavigation(Navigations::NAVTYPE_TOP_HEADER);
                FatCache::set('headerTopNavigation_' . $activeTheme . '_' . $siteLangId, serialize($headerTopNavigation), '.txt');
            }
            
            $template->set('top_header_navigation', $headerTopNavigation);
            $template->set('isUserLogged', $isUserLogged);
            $template->set('headerNavigation', $headerNavigation);
            echo $headerNavigationffff = $template->render(false, false, '_partial/headerNavigation.php', true, true);
            FatCache::set('headerNavigation_Cache_'. $activeTheme . '_' . $siteLangId, serialize($headerNavigationffff), '.txt');
        }
    }
	
	public static function headerMegaNavigation($template = '')
    {
        $excludeCatHavingNoProds = FatApp::getConfig('CONF_EXCLUDE_CATEGORIES_WITHOUT_PRODUCTS', FatUtility::VAR_INT, 1);
        if (!$template) {
            $template = new FatTemplate('', '');
        }

        $siteLangId = CommonHelper::getLangId();
        $template->set('siteLangId', $siteLangId);
        $activeTheme = applicationConstants::getActiveTheme();
        $headerMegaMenuCacheNew = FatCache::get('headerMegaMenu_Cache_'. $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        $isUserLogged = UserAuthentication::isUserLogged();
        if ($headerMegaMenuCacheNew) {
            echo unserialize($headerMegaMenuCacheNew);
        } else {
            $headerCategoriesCache = FatCache::get('headerCategories_'. $activeTheme . '_' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($headerCategoriesCache) {
                $headerCategories = unserialize($headerCategoriesCache);
            } else {
                $headerCategories = ProductCategory::getTreeArr($siteLangId, 0, false, false, $excludeCatHavingNoProds);
                FatCache::set('headerCategories_'. $activeTheme . '_' . $siteLangId, serialize($headerCategories), '.txt');
            }
            $template->set('headerCategories', $headerCategories);
            $template->set('isUserLogged', $isUserLogged);
            echo $headerNavigationhtml = $template->render(false, false, '_partial/headerMegaMenu.php', true, true);
            FatCache::set('headerMegaMenu_Cache_'. $activeTheme . '_' . $siteLangId, serialize($headerNavigationhtml), '.txt');
        }
    }
	
	public static function buyerDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [ */
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /* ] */
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function topHeaderDashboard($template)
    {
        /* $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
          $userId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId(); */
        $userId = UserAuthentication::getLoggedUserId();
        /* Unread Message Count [ */
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /* ] */
        /* [ UNREAD NOTIFICATION COUNT (RFQ MODULE) */

        $nObj = new Notifications();
        $totalUnreadNotificationCount = $nObj->getUnreadNotificationCount($userId, Notifications::getRfqModuleNotificationTypes());

        /* ] */


        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);
        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $controller = str_replace('Controller', '', FatApp::getController());
        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $shop = new Shop(0, $userId);
        $isShopActive = $shop->isActive();

        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('activeTab', $activeTab);
        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', $isShopActive);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
        $template->set('unreadNotificationCount', $totalUnreadNotificationCount);
    }

    public static function advertiserDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();
        $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
        $userParentId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId();
        $template->set('userParentId', $userParentId);
        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function sellerDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $userData = User::getAttributesById(UserAuthentication::getLoggedUserId());
        $userId = (0 < $userData['user_parent']) ? $userData['user_parent'] : UserAuthentication::getLoggedUserId();
        /* Unread Message Count [ */
        $threadObj = new Thread();
        $todayUnreadMessageCount = $threadObj->getMessageCount(UserAuthentication::getLoggedUserId(), Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
        /* ] */
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $shop = new Shop(0, $userId);
        $isShopActive = $shop->isActive();
        
        /* [ USER REQUESTS COUNT CHECK */
        $isRequsetCount = true;
        if (FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0) == 0 && FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0) == 0 && FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0) == 0) {
            $userArr = User::getAuthenticUserIds(UserAuthentication::getLoggedUserId(), $userId);
            $reqCount = Brand::getRequestCount($userArr);
            if ($reqCount == 0) {
                $reqCount = ProductCategory::getRequestCount($userArr);
                $reqCount = ($reqCount == 0) ?  ProductRequest::getRequestCount($userArr) : $reqCount;
                $isRequsetCount = ($reqCount == 0) ? false : true;
            }  
        }
        /* ] */
        
        $template->set('isRequsetCount', $isRequsetCount);
        $template->set('userParentId', $userId);
        $template->set('userPrivilege', UserPrivilege::getInstance());
        $template->set('shop_id', $shop_id);
        $template->set('isShopActive', $isShopActive);
        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
        $template->set('todayUnreadMessageCount', $todayUnreadMessageCount);
    }

    public static function affiliateDashboardNavigation($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());
        $action = FatApp::getAction();

        $template->set('siteLangId', $siteLangId);
        $template->set('controller', $controller);
        $template->set('action', $action);
    }

    public static function dashboardTop($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $controller = str_replace('Controller', '', FatApp::getController());

        $activeTab = 'B';
        $sellerActiveTabControllers = array('Seller');
        $buyerActiveTabControllers = array('Buyer');

        if (in_array($controller, $sellerActiveTabControllers)) {
            $activeTab = 'S';
        } elseif (in_array($controller, $buyerActiveTabControllers)) {
            $activeTab = 'B';
        } elseif (isset($_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'])) {
            $activeTab = $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'];
        }

        $jsVariables = array(
            'confirmDelete' => Labels::getLabel('LBL_Do_you_want_to_delete', $siteLangId),
            'confirmDefault' => Labels::getLabel('LBL_Do_you_want_to_set_default', $siteLangId),
        );

        $template->set('jsVariables', $jsVariables);
        $template->set('siteLangId', $siteLangId);
        $template->set('activeTab', $activeTab);
    }

    public static function customPageLeft($template)
    {
        $siteLangId = CommonHelper::getLangId();
        $contentBlockUrlArr = array(Extrapage::CONTACT_US_CONTENT_BLOCK => UrlHelper::generateUrl('Custom', 'ContactUs'));

        $srch = Extrapage::getSearchObject($siteLangId);
        $srch->addCondition('epage_default', '=', 1);
        $srch->addMultipleFields(
                array('epage_id as id', 'epage_type as pageType', 'IFNULL(epage_label,epage_identifier) as pageTitle ')
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $pagesArr = FatApp::getDb()->fetchAll($rs);

        $srch = ContentPage::getSearchObject($siteLangId);
        $srch->addCondition('cpagelang_cpage_id', 'is not', 'mysql_func_null', 'and', true);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $cpagesArr = FatApp::getDb()->fetchAll($rs);

        $template->set('pagesArr', $pagesArr);
        $template->set('cpagesArr', $cpagesArr);
        $template->set('contentBlockUrlArr', $contentBlockUrlArr);
        $template->set('siteLangId', $siteLangId);
    }

    public static function getNavigation($type = 0, $includeCategories = true)
    {
        $siteLangId = CommonHelper::getLangId();
        $activeTheme = applicationConstants::getActiveTheme();
        $headerNavCache = FatCache::get('headerNavCache'. $activeTheme . '_' . $siteLangId . '-' . $type, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($headerNavCache) {
            return unserialize($headerNavCache);
        }

        $excludeCatHavingNoProds = FatApp::getConfig('CONF_EXCLUDE_CATEGORIES_WITHOUT_PRODUCTS', FatUtility::VAR_INT, 1);
        if ($includeCategories) {
            /* SubQuery, Category have products[ */
            if ($excludeCatHavingNoProds) {
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
            } else {
                $prodSrchObj = new ProductCategorySearch($siteLangId);
                $prodSrchObj->addOrder('prodcat_display_order', 'asc');
                $prodSrchObj->addMultipleFields(array('prodcat_code AS prodrootcat_code', '0 as productCounts', 'prodcat_id', 'IFNULL(prodcat_name, prodcat_identifier) as prodcat_name', 'prodcat_parent'));
                $prodSrchObj->doNotCalculateRecords();
                $prodSrchObj->doNotLimitRecords();
            }
            
            $navigationCatCache = FatCache::get('navigationCatCache' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($navigationCatCache) {
                $categoriesMainRootArr = unserialize($navigationCatCache);
            } else {
                $prodCatCache = FatCache::get('prodCatCache' . $siteLangId, CONF_HOME_PAGE_CACHE_TIME, '.txt');
                if ($prodCatCache) {
                    $productRows = unserialize($prodCatCache);
                } else {
                    $rs = $prodSrchObj->getResultSet();
                    $productRows = FatApp::getDb()->fetchAll($rs);
                    FatCache::set('prodCatCache' . $siteLangId, serialize($productRows), '.txt');
                }

                $categoriesMainRootArr = array_column($productRows, 'prodrootcat_code');
                array_walk(
                        $categoriesMainRootArr,
                        function (&$n) {
                    $n = FatUtility::int(substr($n, 0, 6));
                }
                );
                $categoriesMainRootArr = array_unique($categoriesMainRootArr);
                array_flip($categoriesMainRootArr);
                FatCache::set('navigationCatCache' . $siteLangId, serialize($categoriesMainRootArr), '.txt');
            }

            $catWithProductConditoon = '';
            if ($categoriesMainRootArr) {
                $catWithProductConditoon = " and nlink_category_id in(" . implode(",", $categoriesMainRootArr) . ")";
            }

            /* ] */
        }

        $srch = new NavigationLinkSearch($siteLangId);
        if ($includeCategories) {
            $srch->joinTable('(' . $prodSrchObj->getQuery() . ')', 'LEFT OUTER JOIN', 'qryProducts.prodcat_id = nlink_category_id', 'qryProducts');
            $srch->joinProductCategory();
            $srch->addMultipleFields(array(
                'nav_id', 'IFNULL( nav_name, nav_identifier ) as nav_name',
                'IFNULL( nlink_caption, nlink_identifier ) as nlink_caption', 'nlink_parent', 'nlink_id', 'nlink_type', 'nlink_cpage_id', 'nlink_category_id', 'IFNULL( prodcat_active, ' . applicationConstants::ACTIVE . ' ) as filtered_prodcat_active', 'IFNULL(prodcat_deleted, ' . applicationConstants::NO . ') as filtered_prodcat_deleted', 'IFNULL( cpage_deleted, ' . applicationConstants::NO . ' ) as filtered_cpage_deleted', 'nlink_target', 'nlink_url', 'nlink_login_protected'
            ));
            if ($excludeCatHavingNoProds) {
                $srch->addFld('(qryProducts.productCounts) as totProductCounts');
            } else {
                $srch->addFld('0 as totProductCounts');
            }
            
            $srch->addDirectCondition("((nlink_type = " . NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE . " AND nlink_category_id > 0 $catWithProductConditoon ) OR (nlink_type = " . NavigationLinks::NAVLINK_TYPE_CMS . " AND nlink_cpage_id > 0 ) OR  ( nlink_type = " . NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE . " ))");
            $srch->addHaving('filtered_prodcat_active', '=', applicationConstants::ACTIVE);
            $srch->addHaving('filtered_prodcat_deleted', '=', applicationConstants::NO);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
        
        } else {
            $srch->addDirectCondition("((nlink_type != " . NavigationLinks::NAVLINK_TYPE_CATEGORY_PAGE . "  ) OR (nlink_type = " . NavigationLinks::NAVLINK_TYPE_CMS . " AND nlink_cpage_id > 0 ) OR  ( nlink_type = " . NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE . " ))");
            $srch->addMultipleFields(array(
                'nav_id', 'IFNULL( nav_name, nav_identifier ) as nav_name',
                'IFNULL( nlink_caption, nlink_identifier ) as nlink_caption', 'nlink_parent', 'nlink_id', 'nlink_type', 'nlink_cpage_id', 'nlink_category_id', 'IFNULL( cpage_deleted, ' . applicationConstants::NO . ' ) as filtered_cpage_deleted', 'nlink_target', 'nlink_url', 'nlink_login_protected'
            ));
            $srch->setPageSize(10);
        }

        
        
        $srch->joinNavigation();
        $srch->joinContentPages();

        $srch->addOrder('nav_id');
        $srch->addOrder('nlink_display_order');

        $srch->addCondition('nav_type', '=', $type);
        $srch->addCondition('nlink_deleted', '=', applicationConstants::NO);
        $srch->addCondition('nav_active', '=', applicationConstants::ACTIVE);

        $srch->addHaving('filtered_cpage_deleted', '=', applicationConstants::NO);

        $isUserLogged = UserAuthentication::isUserLogged();
        if ($isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_YES, 'OR');
        }
        if (!$isUserLogged) {
            $cnd = $srch->addCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_BOTH);
            $cnd->attachCondition('nlink_login_protected', '=', NavigationLinks::NAVLINK_LOGIN_NO, 'OR');
        }
        
        $srch->addGroupBy('nav_id');
        $srch->addGroupBy('nlink_id');
        $rs = $srch->getResultSet();
        $rows = FatApp::getDb()->fetchAll($rs);
        $navigation = array();
        $previous_nav_id = 0;
        $productCategory = new ProductCategory();
        $normalPages = [];
        
        if ($rows) {
            foreach ($rows as $key => $row) {
                if ($key == 0 || $previous_nav_id != $row['nav_id']) {
                    $previous_nav_id = $row['nav_id'];
                }
                $navigation[$previous_nav_id]['parent'] = $row['nav_name'];
                if (($type == Navigations::NAVTYPE_HEADER && $row['nlink_category_id'] > 0) || ($type != Navigations::NAVTYPE_HEADER)) {
                    $navigation[$previous_nav_id]['pages'][$key] = $row;
                }

                $childrenCats = array();
                if ($includeCategories && $row['nlink_category_id'] > 0) {
                    $catObj = clone $prodSrchObj; /* To DO */
                    if ($excludeCatHavingNoProds) {
                        $catObj->addCategoryCondition($row['nlink_category_id']); 
                    } else {
                        $catCode = ProductCategory::getAttributesById($row['nlink_category_id'], 'prodcat_code');
                        $catObj->addCondition('c.prodcat_code', 'LIKE', $catCode . '%', 'AND', true);
                    }
                    
                    $categoriesDataArr = ProductCategory::getProdCatParentChildWiseArr($siteLangId, $row['nlink_category_id'], false, false, false, $catObj, $excludeCatHavingNoProds, false);
                    $childrenCats = $productCategory->getCategoryTreeArr($siteLangId, $categoriesDataArr);
                    $childrenCats = ($childrenCats) ? $childrenCats[$row['nlink_category_id']]['children'] : array();
                    $navigation[$previous_nav_id]['pages'][$key]['children'] = $childrenCats;
                } else {
                    if ($type == Navigations::NAVTYPE_HEADER) {
                        $normalPages[] = $row;
                    } else {
                        $navigation[$previous_nav_id]['pages'][$key]['children'] = $childrenCats;
                    }
                }
            }
            if ($type == Navigations::NAVTYPE_HEADER) {
                $groupedLinks = CommonHelper::groupLinksByKey($normalPages);
                if (isset($navigation[$previous_nav_id]['pages'])) {
                    $catPages = $navigation[$previous_nav_id]['pages'];
                } else {
                    $catPages = [];
                }
                $allPages = array_merge($catPages, $groupedLinks);
                $navigation[$previous_nav_id]['pages'] = $allPages;
            }
        }
        FatCache::set('headerNavCache'. $activeTheme . '_' . $siteLangId . '-' . $type, serialize($navigation), '.txt');
        return $navigation;
    }

    public static function footerNavigation($template = '',$navType = Navigations::NAVTYPE_FOOTER)
    {
        if (!$template) {
            $template = new FatTemplate('', '');
        }
        $siteLangId = CommonHelper::getLangId();
        $activeTheme = applicationConstants::getActiveTheme();

        $footerNavigationCacheNew = FatCache::get('footerNavigation_Cache_'. $activeTheme . '_' . $siteLangId . '_' . $navType, CONF_HOME_PAGE_CACHE_TIME, '.txt');
        if ($footerNavigationCacheNew) {
            echo $footerNavigationCacheNew = unserialize($footerNavigationCacheNew);
        } else {
            $footerNavigationCache = FatCache::get('footerNavigation'. $activeTheme . '_' . $siteLangId . '_' . $navType, CONF_HOME_PAGE_CACHE_TIME, '.txt');
            if ($footerNavigationCache) {
                $footerNavigation = unserialize($footerNavigationCache);
            } else {
                $footerNavigation = self::getNavigation($navType);
                FatCache::set('footerNavigation'. $activeTheme . '_' . $siteLangId. '_' . $navType, serialize($footerNavigation), '.txt');
            }

            $template->set('footer_navigation', $footerNavigation);
            if($navType == Navigations::NAVTYPE_FOOTER) {
                echo $footerNavigationCacheNew = $template->render(false, false, '_partial/footerNavigation.php', true, true);
            }else{
                echo $footerNavigationCacheNew = $template->render(false, false, '_partial/footerNavigationBottom.php', true, true);
            }
            
            FatCache::set('footerNavigation_Cache_'. $activeTheme . '_' . $siteLangId . '_' . $navType, serialize($footerNavigationCacheNew), '.txt');
        }
    }

    public static function sellerNavigationLeft($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $seller_navigation_left = self::getNavigation(Navigations::NAVTYPE_SELLER_LEFT);
        $template->set('seller_navigation_left', $seller_navigation_left);
    }

    public static function sellerNavigationRight($template)
    {
        $db = FatApp::getDb();
        $siteLangId = CommonHelper::getLangId();
        $seller_navigation_right = self::getNavigation(Navigations::NAVTYPE_SELLER_RIGHT);
        $template->set('seller_navigation_right', $seller_navigation_right);
    }

    public static function blogNavigation()
    {
        $siteLangId = CommonHelper::getLangId();
        $blog = new BlogController();
        $srchFrm = $blog->getBlogSearchForm();
        $categoriesArr = BlogPostCategory::getRootBlogPostCatArr($siteLangId);
        $data = array(
            'srchFrm' => $srchFrm,
            'categoriesArr' => $categoriesArr,
            'siteLangId' => $siteLangId,
        );
        return $data;
    }

}
