<div class="wrapper">
    <!--header start here-->
    <header id="header" class="header no-print" role="site-header">
        <div class="header__top">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="header__top-left">

                        <div class="dropdown location">
                            <?php if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') { ?>
                            <a class="dropdown-toggle no-after" onClick="accessLocation(true)"
                                href="javascript:void(0)">
                                <i class="icn icn-location">
                                    <svg class="svg">
                                        <use
                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#location">
                                        </use>
                                    </svg>
                                </i>

                                <!-- <span class="hide-sm">
                                                <?php echo Labels::getLabel("LBL_Your_Location", $siteLangId); ?>-</span> -->


                                <span class="curent-zip-code" id="js-curent-zip-code">
                                    <?php echo isset($_COOKIE["_ykGeoAddress"]) ? $_COOKIE["_ykGeoAddress"] : Labels::getLabel("LBL_Location", $siteLangId); ?>
                                </span>

                            </a>
                            <?php } ?>
                        </div>

                    </div>
                    <?php if (FatApp::getConfig('CONF_ENABLE_TEXT_IN_TOP_HEADER', FatUtility::VAR_INT, 0)) { ?>
                    <div class="header__top-center">
                        <div class="slogan">
                            <span><?php echo nl2br(html_entity_decode(FatApp::getConfig('CONF_TEXT_IN_TOP_HEADER_' . $siteLangId, FatUtility::VAR_STRING, ""))); ?></span>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="header__top-right">
                        <div class="short-links">
                            <ul>
                                <li>
                                    <i class="icn icn-phone">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#phone"
                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#phone">
                                            </use>
                                        </svg>
                                    </i>
                                    <?php echo FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, ""); ?>
                                </li>
                                <?php /* [  HEADER LANGUAGE AND CURRENCY UPDATE SECTION */ ?>
                                <?php $this->includeTemplate('_partial/headerLanguageArea.php'); ?>
                                <?php /* ] */ ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header__main">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <a class="navs_toggle" href="javascript:void(0)"><span></span></a>
                    <div class="header__main-left">
                        <?php
                        if (CommonHelper::isThemePreview() && isset($_SESSION['preview_theme'])) {
                            $logoUrl = UrlHelper::generateUrl('home', 'index');
                        } else {
                            $logoUrl = UrlHelper::generateUrl();
                        }
                        ?>

                        <div class="logo_wrapper">
                            <?php
                            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, 0, 0, $siteLangId, false);
                            $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId, true);
                            $sizeType = 'CUSTOM';
                            if ($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_RECTANGULAR) {
                                $sizeType = '16X9';
                            } elseif($fileData['afile_aspect_ratio'] == AttachedFile::RATIO_TYPE_SQUARE) {
                                $sizeType = '1X1';
                            }
                            
                            $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
                            $siteLogo = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'siteLogo', array($siteLangId, $sizeType), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                            ?>
                            <a href="<?php echo $logoUrl; ?>">
                                <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?>
                                    data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>"
                                    <?php } ?> src="<?php echo $siteLogo; ?>"
                                    alt="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>"
                                    title="<?php echo FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, '') ?>">
                            </a>
                        </div>
                       <?php Navigation::headerNavigation();//$this->includeTemplate('_partial/headerNavigation.php'); ?>
                    </div>
                    <div class="header__main-right">
                        <div class="short-links">
                            <ul>
                                <li>
                                    <a href="javascript:void(0);" data-trigger="main-search-bar" class="">
                                        <i class="icn icn-maginifier">
                                            <svg class="svg">
                                                <use
                                                    xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#search_flow">
                                                </use>
                                            </svg>
                                        </i>
                                    </a>
                                </li>
                                <?php $this->includeTemplate('_partial/headerUserArea.php'); ?>
                                <?php $this->includeTemplate('_partial/headerWishListAndCartSummary.php'); ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->includeTemplate('_partial/header/site-search-form.php', ['searchForm' => Common::getSiteSearchForm(), 'siteLangId' => $siteLangId], false); ?>

    </header>
    <div class="search-overlay"></div>


    <!--header end here-->