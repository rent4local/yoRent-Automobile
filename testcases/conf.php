<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define('HTTP_YORENT_PUBLIC', $protocol . $_SERVER['HTTP_HOST'] . rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\') . '/');
define('HTTP_YORENT', preg_replace('~/[^/]*/([^/]*)$~', '/\1', HTTP_YORENT_PUBLIC));

$root = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

require_once $root . '/conf/conf.php';

require_once $root . '/public/application-top.php';

FatApp::unregisterGlobals();

if (file_exists(CONF_APPLICATION_PATH . 'utilities/prehook.php')) {
    require_once CONF_APPLICATION_PATH . 'utilities/prehook.php';
}
// FatApplication::getInstance()->callHook();