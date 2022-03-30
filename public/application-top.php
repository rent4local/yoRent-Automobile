<?php

if (!array_key_exists('screenWidth', $_COOKIE)) {
    setcookie('screenWidth', 769, 0, CONF_WEBROOT_URL);
}

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

ini_set('display_errors', (CONF_DEVELOPMENT_MODE) ? 1 : 0);

error_reporting((CONF_DEVELOPMENT_MODE) ? E_ALL : E_ALL & ~E_NOTICE & ~E_WARNING);

require_once CONF_INSTALLATION_PATH . 'library/autoloader.php';

/* We must set it before initiating db connection. So that connection timezone is in sync with php */
if (CommonHelper::demoUrl()) {
    date_default_timezone_set('Asia/Kolkata');
} else {
    date_default_timezone_set('America/New_York');
}
date_default_timezone_set(FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get()));

/* setting Time Zone of Mysql Server with same as of PHP[ */
$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
/* FatApp::getDb()->query("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");
FatApp::getDb()->query("SET time_zone = '" . $offset . "'"); */
/* ] */

ini_set('session.cookie_httponly', true);
ini_set('session.cookie_path', CONF_WEBROOT_FRONT_URL);
session_start();
FatApp::getDb()->query("SET NAMES utf8mb4");
//FatApp::getDb()->clearQueryLog();
//FatApp::getDb()->logQueries(true,CONF_UPLOADS_PATH.'logQuery.txt');

/* --- Redirect SSL --- */
if (true == USE_X_FORWARDED_PROTO) {
    /* USE when $_SERVER['HTTPS'] will not provided by server . Generally in AWS server when load balance used for SSL. */
    if ((!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https')  && (FatApp::getConfig('CONF_USE_SSL', FatUtility::VAR_INT, 0) == 1)) {
        $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        FatApp::redirectUser($redirect);
    }
} else {
    if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')  && (FatApp::getConfig('CONF_USE_SSL', FatUtility::VAR_INT, 0) == 1)) {
        $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        FatApp::redirectUser($redirect);
    }
}
/* --- Redirect SSL --- */
$_SESSION['WYSIWYGFileManagerRequirements'] = CONF_INSTALLATION_PATH . 'public/WYSIWYGFileManagerRequirements.php';
if (strpos(CONF_UPLOADS_PATH, 's3://') !== false) {
    require_once CONF_INSTALLATION_PATH . 'library/aws/aws-autoloader.php';
    AttachedFile::registerS3ClientStream();
}
define('SYSTEM_INIT', true);
define('CONF_WEB_APP_VERSION', 'RV-3.0');
/* define('CONF_WEB_APP_VERSION', 'TV-3.5.0.20220323'); */
define('CONF_BASECOPY_WEB_APP_VERSION', 'RV-9.3.0');
define('ALLOW_RENT', FatApp::getConfig('CONF_ALLOW_RENT'));
define('ALLOW_SALE', FatApp::getConfig('CONF_ALLOW_SALE'));