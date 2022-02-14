<?php
if (FatApp::getConfig("CONF_ACTIVE_THEME_ID", FatUtility::VAR_INT, 0) == applicationConstants::THEME_AUTOMOBILE) {
    include(CONF_THEME_PATH_WITH_THEME_NAME .'_partial/headerWishListAndCartSummary.php');
}else{
    include(CONF_DEFAULT_THEME_PATH .'_partial/headerWishListAndCartSummary.php');
}


?>