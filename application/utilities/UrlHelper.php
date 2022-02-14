<?php
class UrlHelper extends FatUtility
{
    public static function getCurrUrl()
    {
        return self::getUrlScheme() . $_SERVER["REQUEST_URI"];
    }

    public static function getUrlScheme()
    {
        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
        return $pageURL;
    }

    public static function generateUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false, $getOriginalUrl = false, $useLangCode = true)
    {
        $langId = SYSTEM_LANG_ID;
        if (CommonHelper::isThemePreview()) {
            array_push($queryData, '?theme-preview');
        }

        $useRootUrl = $use_root_url;
        if (true == $useLangCode && FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && count(LANG_CODES_ARR) > 1 && $langId  != FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1)) {
            $use_root_url = rtrim($use_root_url, '/') . '/' . strtolower(LANG_CODES_ARR[$langId]) . '/';
        }
        $url = FatUtility::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting);

        if (rtrim($use_root_url, '/') === $url) {
            $url = $use_root_url;
        }

        if (!$use_root_url) {
            $use_root_url = CONF_WEBROOT_URL;
        }

        if ($getOriginalUrl) {
            return $url;
        }

        $urlForString = FatUtility::generateUrl($controller, $action, $queryData, $useRootUrl, $url_rewriting);
        $urlString = trim(ltrim($urlForString, CONF_WEBROOT_FRONTEND), '/');
        $srch = UrlRewrite::getSearchObject();
        $srch->addFld('urlrewrite_custom');
        if (true == $useLangCode && FatApp::getConfig('CONF_LANG_SPECIFIC_URL', FatUtility::VAR_INT, 0) && count(LANG_CODES_ARR) > 1 && $langId  != FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1)) {
            $srch->joinTable(Language::DB_TBL, 'LEFT OUTER JOIN', 'urlrewrite_lang_id = language_id');
            $srch->addMultipleFields(array('if(urlrewrite_lang_id = ' . $langId  . ', 99 , 1) as priority'));
            $srch->addOrder('priority', 'desc');
        }

        $srch->doNotCalculateRecords();
        $srch->setPageSize(1);
        $srch->addCondition(UrlRewrite::DB_TBL_PREFIX . 'original', 'LIKE', $urlString);
        $rs = $srch->getResultSet();
        if ($row = FatApp::getDb()->fetch($rs)) {
            $url = $use_root_url;
            if ($encodeUrl) {
                $url .=  urlencode($row['urlrewrite_custom']);
            } else {
                $url .=  $row['urlrewrite_custom'];
            }
        }
        return $url;
    }

    public static function generateFullUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false, $getOriginalUrl = false, $useLangCode = true)
    {
        $url = self::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting, false, $getOriginalUrl, $useLangCode);
        $protocol = (FatApp::getConfig('CONF_USE_SSL') == 1) ? 'https://' : 'http://';
        if ($encodeUrl) {
            $url = urlencode($url);
        }
        return $protocol . $_SERVER['SERVER_NAME'] . $url;
    }

    public static function generateNoAuthUrl($model = '', $action = '', $queryData = array(), $use_root_url = '')
    {
        $url = self::generateUrl($model, $action, $queryData, $use_root_url, false);
        $url = str_replace('index.php?', 'index_noauth.php?', $url);
        $protocol = (FatApp::getConfig('CONF_USE_SSL') == 1) ? 'https://' : 'http://';
        return $protocol . $_SERVER['SERVER_NAME'] . $url;
    }

    public static function getCachedUrl(string $key, int $expiry = null, string $extension = '')
    {
        $url = FatCache::getCachedUrl($key, $expiry, $extension);

        if (CDN_DOMAIN_URL != '') {
            if (strpos($url, CDN_DOMAIN_URL) !== false) {
                return $url;
            }
            return rtrim(CDN_DOMAIN_URL, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public static function getAsFileUrl(string $key, int $expiry = null, string $extension = '')
    {
        return FatCache::getAsFileUrl($key, $expiry, $extension);
    }

    public static function generateFileUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false, $getOriginalUrl = false)
    {
        $url = UrlHelper::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting, $encodeUrl, $getOriginalUrl, false);

        if (CDN_DOMAIN_URL != '') {
            return rtrim(CDN_DOMAIN_URL, '/') . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public static function generateFullFileUrl($controller = '', $action = '', $queryData = array(), $use_root_url = '', $url_rewriting = null, $encodeUrl = false)
    {
        $url = UrlHelper::generateUrl($controller, $action, $queryData, $use_root_url, $url_rewriting, $encodeUrl, false, false);
        if ($encodeUrl) {
            $url = urlencode($url);
        }

        if (CDN_DOMAIN_URL != '') {
            return rtrim(CDN_DOMAIN_URL, '/') . '/' . ltrim($url, '/');
        }

        $protocol = (FatApp::getConfig('CONF_USE_SSL') == 1) ? 'https://' : 'http://';
        return $protocol . $_SERVER['SERVER_NAME'] . $url;
    }

    public static function parseYouTubeurl($url)
    {
        $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
        $pattern .= '(?:www\.)?';         #  Optional www subdomain.
        $pattern .= '(?:';                #  Group host alternatives:
        $pattern .=   'youtu\.be/';       #    Either youtu.be,
        $pattern .=   '|youtube\.com';    #    or youtube.com
        $pattern .=   '(?:';              #    Group path alternatives:
        $pattern .=     '/embed/';        #      Either /embed/,
        $pattern .=     '|/v/';           #      or /v/,
        $pattern .=     '|/watch\?v=';    #      or /watch?v=,
        $pattern .=     '|/watch\?.+&v='; #      or /watch?other_param&v=
        $pattern .=   ')';                #    End path alternatives.
        $pattern .= ')';                  #  End host alternatives.
        $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
        $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.

        preg_match($pattern, $url, $matches);


        // preg_match("/(?:[\/]|v=)([a-zA-Z0-9-_]{11})/", $url, $matches);

        return (isset($matches[1])) ? $matches[1] : false;
    }

    public static function staticContentProvider($controller, $action)
    {
        if (in_array($controller, array('js-css', 'image', 'fonts', 'images', 'js', 'img', 'innovas', 'assetmanager', 'assets'))) {
            return true;
        }

        $arr = [
            'banner' => [
                'home-page-banner-top-layout',
                'home-page-banner-middle-layout',
                'home-page-banner-bottom-layout',
                'product-detail-page-banner',
                'thumb',
                'show-banner',
                'show-original-banner',
                'product-page',
                'blog',
                'brand-page',
            ],
            'category' => [
                'banner',
                'seller-banner',
                'image',
                'icon',
                'banner'
            ],
            'custom' => [
                'update-screen-resolution'
            ],
            'Account' => [
                'user-profile-image'
            ],
            'home' => [
                'pwa-manifest',
                'get-url-segments-detail',
                'splash-screen-data',
                'get-image',
                'get-all-sponsored-products'
            ]
        ];

        if (array_key_exists($controller, $arr) && in_array($action, $arr[$controller])) {
            return true;
        }

        return false;
    }
}
