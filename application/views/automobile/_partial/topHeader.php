<div class="wrapper">
    <!--header start here-->
    <header id="header" class="header no-print" role="site-header">
        <?php if ((FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl())) {
            $this->includeTemplate('restore-system/top-header.php');
        } ?>
        <div class="header__main">
            <div class="container container--fluid d-flex flex-wrap align-items-center justify-content-between">
                <?php if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
                    $logoUrl = UrlHelper::generateUrl('home', 'index');
                } else {
                    $logoUrl = UrlHelper::generateUrl();
                } ?>

                <?php
                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
                $sizeType = 'CUSTOM';
                $logoClass = "logo--custom";
                if ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_RECTANGULAR) {
                    $sizeType = '16X9';
                    $logoClass = "";
                } elseif ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_SQUARE) {
                    $sizeType = '1X1';
                    $logoClass = "";
                }

                $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
                $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId, $sizeType), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                ?>
                <?php /* <a href="#HDR-OFFSET" data-tgl="header-offset" class="header-offset-trigger d-xl-none order-2 order-md-1 order-xl-0">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </a> */ ?>
                <a class="order-2 js--navigation-trigger ft-navigation__target ft-target --button" href="javascript:void(0);"></a>
                <div class="ft-navigation__mobile bg-gray" data-add="#MAIN_NAVIGATION">
                    <a class="js--navigation-close ft-navigation__close ft-target --button" href="javascript:void(0);"><?php echo Labels::getLabel('Lbl_Close', $siteLangId); ?></a>
                </div>

                <div class="logo <?php echo $logoClass; ?> order-3 order-md-2 order-xl-1">
                    <a href="<?php echo $logoUrl; ?>"><img src="<?php echo $siteLogo ?>" title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>" alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>"></a>
                </div>
                <?php if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) { ?>
                    <div class="location  order-md-3 order-1">
                        <?php
                        $geoAddress = '';
                        if ((!isset($_COOKIE['_ykGeoLat']) || !isset($_COOKIE['_ykGeoLng']) || !isset($_COOKIE['_ykGeoCountryCode'])) && FatApp::getConfig('CONF_DEFAULT_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
                            $geoAddress = FatApp::getConfig('CONF_GEO_DEFAULT_ADDR', FatUtility::VAR_STRING, '');
                            if (empty($address)) {
                                $address = FatApp::getConfig('CONF_GEO_DEFAULT_ZIPCODE', FatUtility::VAR_INT, 0) . '-' . FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_STRING, '');
                            }
                        }
                        if (empty($geoAddress)) {
                            $geoAddress = Labels::getLabel("Lbl_Select_Location", $siteLangId);
                        }


                        $defaultCheckoutType =  isset($_COOKIE["locationCheckoutType"]) ? $_COOKIE["locationCheckoutType"] : FatApp::getConfig('CONF_DEFAULT_LOCATION_CHECKOUT_TYPE', FatUtility::VAR_STRING, Shipping::FULFILMENT_PICKUP);

                        if ($defaultCheckoutType == Shipping::FULFILMENT_PICKUP) {
                            $fullfillmentType = Shipping::FULFILMENT_PICKUP;
                            $fullfillmentTypeLbl = Labels::getLabel('Lbl_Pickup_At', $siteLangId);
                        } else {
                            $fullfillmentType = Shipping::FULFILMENT_SHIP;
                            $fullfillmentTypeLbl = Labels::getLabel('Lbl_Shipping_to', $siteLangId);
                        }
                        ?>
                        <div class="location_inner">
                            <div class="select-by" id="fullfillment-type-js">
                                <span id="fullfillment-label-js"><?php echo $fullfillmentTypeLbl; ?></span>
                                <input type="hidden" name="fullfillment_type" value="<?php echo $fullfillmentType; ?>" />
                            </div>
                            <div class="dropdown">
                                <button class="location_trigger" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icn icn-location">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#location">
                                            </use>
                                        </svg>
                                    </i>
                                    <span class="location-selected"><?php echo isset($_COOKIE["_ykGeoAddress"]) ? $_COOKIE["_ykGeoAddress"] : $geoAddress; ?></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-fit dropdown-menu-right dropdown-menu-anim  location_dropdown-menu" aria-labelledby="location-dropdown">
                                    <div class="location_body">
                                        <button onclick="loadGeoLocation()" class="btn btn-brand btn-block btn-detect">
                                            <i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite-header.svg#location-detect">
                                                    </use>
                                                </svg></i>
                                            <span><?php echo Labels::getLabel('Lbl_Detect_my_current_location', $siteLangId); ?>
                                            </span></button>

                                        <div class="or">
                                            <span><?php echo Labels::getLabel('Lbl_OR', $siteLangId); ?></span>
                                        </div>

                                        <input class="location_input form-control" type="text" name="location" placeholder="<?php echo Labels::getLabel("LBL_Search_for_...", $siteLangId); ?>" id="ga-autoComplete" autocomplete="off" value="<?php echo (isset($_COOKIE['_ykGeoAddress'])) ? $_COOKIE['_ykGeoAddress'] : ""; ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="header-right-actions order-last">
                    <div class="search">
                        <a href="#HDR-SEARCH" title="<?php echo Labels::getLabel('Lbl_Search', $siteLangId); ?>" class="item-circle" data-tgl="open-search">
                            <i class="icn icn-maginifier">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#maginifier">
                                    </use>
                                </svg>
                            </i>
                        </a>
                    </div>
                    <div class="user">
                        <?php $this->includeTemplate('_partial/headerUserArea.php'); ?>
                    </div>
                    <?php if ($controllerName != 'Cart') { ?>
                        <div class="header__cart">
                            <div class="cart" id="cartSummary">
                                <?php $this->includeTemplate('_partial/headerWishListAndCartSummary.php'); ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php $postedData = Product::convertArrToSrchFiltersAssocArr(FatApp::getParameters());
            $this->includeTemplate('_partial/header/site-search-form.php', ['searchForm' => Common::getSiteSearchForm(), 'siteLangId' => $siteLangId, 'postedData' => $postedData, 'headerSearch' => true], false); ?>
        </div>
        <div class="header__bottom">
            <div class="container container--fluid">
                <div class="ft-navigation" id="MAIN_NAVIGATION">
                    <nav class="ft-menu ft-nav --inline">
                        <?php Navigation::headerMegaNavigation(); ?>
                        <?php Navigation::headerNavigation(); ?>
                    </nav>
                </div>
            </div>
        </div>
        <div class="common-overlay"></div>
    </header>
    <!--header end here-->