<?php 
defined('SYSTEM_INIT') or die('Invalid Usage.');

if (isset($includeEditor) && $includeEditor == true) {
    $extendEditorJs = 'true';
} else {
    $extendEditorJs = 'false';
}

if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
    $themeActive = 'true';
} else {
    $themeActive = 'false';
}

array_walk($jsVariables, function (&$item1, $key) {
    $item1 = html_entity_decode($item1, ENT_QUOTES, 'UTF-8');
});
$commonHeadData = array(
    'siteLangId' => $siteLangId,
    'siteLangCode' => $siteLangCode,
    'controllerName' => $controllerName,
    'action' => $action,
    'jsVariables' => $jsVariables,
    'extendEditorJs' => $extendEditorJs,
    'themeDetail' => $themeDetail,
    'themeActive' => $themeActive,
    'currencySymbolLeft' => $currencySymbolLeft,
    'currencySymbolRight' => $currencySymbolRight,
    'isUserDashboard' => $isUserDashboard,
    'canonicalUrl' => isset($canonicalUrl) ? $canonicalUrl: '',
);
if (isset($layoutTemplate) && $layoutTemplate != '') {
    $commonHeadData['layoutTemplate'] = $layoutTemplate;
    $commonHeadData['layoutRecordId'] = $layoutRecordId;
}
if (isset($socialShareContent) && $socialShareContent != '') {
    $commonHeadData['socialShareContent'] = $socialShareContent;
}
if (isset($includeEditor) && $includeEditor == true) {
    $commonHeadData['includeEditor'] = $includeEditor;
}



$this->includeTemplate('_partial/header/commonHeadTop.php', $commonHeadData, false);
/* This is not included in common head, because, commonhead file not able to access the $this->Controller and $this->action[ */
echo $this->writeMetaTags();
/* ] */
$this->includeTemplate('_partial/header/commonHeadMiddle.php', $commonHeadData, false);

/* This is not included in common head, because, if we are adding any css/js from any controller then that file is not included[ */
echo $this->getJsCssIncludeHtml(!CONF_DEVELOPMENT_MODE);
/* ] */

$this->includeTemplate('_partial/header/commonHeadBottom.php', $commonHeadData, false);

if (isset($isUserDashboard) && $isUserDashboard) {
    $this->includeTemplate('_partial/topHeaderDashboard.php', $commonHeadData, false);
    $exculdeMainHeaderDiv = true;
}

if (!isset($exculdeMainHeaderDiv)) {
    $this->includeTemplate('_partial/topHeader.php', array('siteLangId'=>$siteLangId, 'controllerName'=>$controllerName), false);
}

if (!$isAppUser) {
    $controllerName = strtolower($controllerName);
    switch ($controllerName) {
        case 'rfqcheckout':
        case 'checkout':
        case 'walletpay':
        case 'subscriptioncheckout':
            $this->includeTemplate('_partial/header/checkout-header.php', array('siteLangId'=>$siteLangId,'headerData'=>$headerData,'controllerName'=>$controllerName), false);
            break;
    }
}