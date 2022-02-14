<?php
class CustomRouter
{
    public static function setRoute(&$controller, &$action, &$queryString)
    {
        $userType = null;
        define('LANG_CODES_ARR', Language::getAllCodesAssoc());
        
        if ('mobile-app-api' == $controller) {
            define('MOBILE_APP_API_CALL', true);
            define('MOBILE_APP_API_VERSION', '1.0');
            define('SYSTEM_LANG_ID', CommonHelper::getLangId());
        } elseif ('app-api' == $controller) {
            define('MOBILE_APP_API_CALL', true);
            define('MOBILE_APP_API_VERSION', str_replace('v', '', $action));
            define('SYSTEM_LANG_ID', CommonHelper::getLangId());

            if (MOBILE_APP_API_VERSION <= '1.2') {
                $controller = 'mobile-app-api';
                if (!array_key_exists(0, $queryString)) {
                    $queryString[0] = '';
                }
                if (!array_key_exists(1, $queryString)) {
                    $queryString[1] = '';
                }
            } else {
                if (!array_key_exists(0, $queryString)) {
                    $arr = array('status' => -1, 'msg' => "Invalid Request");
                    die(json_encode($arr));
                }

                $controller = $queryString[0];
                array_shift($queryString);

                if (!array_key_exists(0, $queryString)) {
                    $queryString[0] = '';
                }
            }

            $action = $queryString[0];
            if ($controller != '' && $action == '') {
                $action = 'index';
            }

            array_shift($queryString);

            $token = null;

            if (array_key_exists('HTTP_X_USER_TYPE', $_SERVER)) {
                $userType = intval($_SERVER['HTTP_X_USER_TYPE']);
            }
        } else {
            /* [ Handled lang code in url */
            if (FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && in_array(strtoupper($controller), LANG_CODES_ARR)) {
                $langId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
                $langCodes = array_flip(LANG_CODES_ARR);
                if (in_array(strtoupper($controller), LANG_CODES_ARR)) {
                    $langId = $langCodes[strtoupper($controller)];
                }
                $langId = ($langId > 0) ? $langId : CommonHelper::getLangId();
                define('SYSTEM_LANG_ID', $langId);
                setcookie('defaultSiteLang', SYSTEM_LANG_ID, time() + 3600 * 24 * 10, CONF_WEBROOT_URL);

                $controller = ($action == 'index') ? 'Home' : $action;
                if (!array_key_exists(0, $queryString)) {
                    $action = 'index';
                } else {
                    $action = $queryString[0];
                    array_shift($queryString);
                }
            } else {
                define('SYSTEM_LANG_ID', CommonHelper::getLangId());
            }
            /* ] */
            
            define('MOBILE_APP_API_CALL', false);
            define('MOBILE_APP_API_VERSION', '');
        }
        define('MOBILE_APP_USER_TYPE', $userType);
       
        /* Handled CDN url for static contents and 404 for other requests. Specially when mapped on same root directory[*/
        if (CDN_DOMAIN_URL != '' && (strpos(CDN_DOMAIN_URL, $_SERVER['SERVER_NAME']) !== false)) {
            if (!UrlHelper::staticContentProvider($controller, $action)) {
                $action = 'error404';
                return;
            }
        }
        /* ]*/

        if (defined('SYSTEM_FRONT') && SYSTEM_FRONT === true/*  && !FatUtility::isAjaxCall() */) {
            $url = urldecode($_SERVER['REQUEST_URI']);
                        
            if (strpos($url, "index.php?url=") !== false || UrlHelper::staticContentProvider($controller, $action) == true) {
                return ;
            }

            if (strpos($url, "?") !== false && strpos($url, "/?") === false) {
                $url = str_replace('?', '/?', $url);
            }
          
            $customUrl = substr($url, strlen(CONF_WEBROOT_URL));
            $customUrl = rtrim($customUrl, '/');
            $customUrl = explode('/?', $customUrl);

            /* [ Handled lang code in url */
            if (FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0)) {
                $langCustomUrl = explode('/', $customUrl[0]);
                if (isset($langCustomUrl[0]) && $langCustomUrl[0] != '') {
                    if (in_array(strtoupper($langCustomUrl[0]), LANG_CODES_ARR)) {
                        $customUrl[0] = substr($customUrl[0], 3);
                        $customUrl[0] = ltrim($customUrl[0], '/');
                    }
                }
            }
            /* ] */
                
            /* [ Check url rewritten by the system or system url with query parameter*/
            $row = false;
            if (!empty($customUrl[0])) {
                $srch = UrlRewrite::getSearchObject();
                $srch->doNotCalculateRecords();
                $srch->addMultipleFields(array('urlrewrite_custom','urlrewrite_original'));
                $srch->setPageSize(1);
                $srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'custom', '=', $customUrl[0]);
                //$srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'lang_id', '=', SYSTEM_LANG_ID);
                $rs = $srch->getResultSet();
                $row = FatApp::getDb()->fetch($rs);

                if (!$row && FatApp::getConfig('CONF_ENABLE_301', FatUtility::VAR_INT, 0) && !FatUtility::isAjaxCall()) {
                    $srch = UrlRewrite::getSearchObject();
                    $srch->doNotCalculateRecords();
                    $srch->addMultipleFields(array('urlrewrite_custom','urlrewrite_original'));
                    $srch->setPageSize(1);
                    $srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'original', '=', $customUrl[0]);
                    $rs = $srch->getResultSet();
                    $res = FatApp::getDb()->fetch($rs);
                    if (!empty($res) && $res['urlrewrite_custom'] != '') {
                        $redirectQueryString = (isset($customUrl[1]) && $customUrl[1] != '') ?  '?' . $customUrl[1] : '';
                        header("HTTP/1.1 301 Moved Permanently");
                        header("Location: " . UrlHelper::generateFullUrl(CONF_WEBROOT_URL) . '/' . $res['urlrewrite_custom'] . $redirectQueryString);
                        header("Connection: close");
                    }
                }
            }
            if (!$row && (!isset($customUrl[1]) || (isset($customUrl[1]) && strpos($customUrl[1], 'pagesize') === false))) {
                return;
            }
            /*]*/
           
            $url = (!empty($row['urlrewrite_original'])) ? $row['urlrewrite_original'] : '';
            if (!$row && isset($customUrl[1])) {
                $url = $customUrl[0];
            }
           
            $arr = explode('/', $url);

            $controller = (isset($arr[0])) ? $arr[0] : '';
            array_shift($arr);

            $action = (isset($arr[0])) ? $arr[0] : '';
            array_shift($arr);

            $queryString = $arr;
            /* [ used in case of filters when passed through url*/
            //array_shift($customUrl);
            if (isset($customUrl[1]) && !empty($customUrl[1])) {
                $customUrl = explode('&', $customUrl[1]);
                $queryString = array_merge($queryString, $customUrl);
            }
            
            /* ]*/

            if ($controller != '' && $action == '') {
                $action = 'index';
            }

            if ($controller == '') {
                $controller = 'Content';
            }
            
            if ($action == '') {
                $action = 'error404';
            }
        }
    }
}
