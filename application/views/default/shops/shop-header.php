<?php
defined('SYSTEM_INIT') or die('Invalid Usage');
$catBannerArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BANNER, $shop['shop_id'], '', $siteLangId);
$desktop_url = '';
$tablet_url = '';
$mobile_url = '';
$defaultImgUrl = '';
foreach ($catBannerArr as $slideScreen) {
    $uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
    switch ($slideScreen['afile_screen']) {
        case applicationConstants::SCREEN_MOBILE:
            $mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg') . ",";
            break;
        case applicationConstants::SCREEN_IPAD:
            $tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'TABLET', 0, applicationConstants::SCREEN_IPAD)) . $uploadedTime) . ",";
            break;
        case applicationConstants::SCREEN_DESKTOP:
            $defaultImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopBanner', array($shop['shop_id'], $siteLangId, 'DESKTOP', 0, applicationConstants::SCREEN_DESKTOP)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $desktop_url = $defaultImgUrl . ",";
            break;
    }
} ?>

<?php
if (empty($desktop_url)) {
    $desktop_url = 'images/defaults/' . applicationConstants::getActiveTheme() . '/shop_banner.png';
}
?>

<section class="bg-shop">

    <picture class="shop-banner">
        <source data-aspect-ratio="4:3" srcset="<?php echo $mobile_url; ?>" media="(max-width: 767px)">
        <source data-aspect-ratio="4:3" srcset="<?php echo $tablet_url; ?>" media="(max-width: 1024px)">
        <source data-aspect-ratio="4:1" srcset="<?php echo $desktop_url; ?>">
        <img data-aspect-ratio="4:1" src="<?php echo $desktop_url; ?>" alt="">
    </picture>

</section>


<section class="section pt-0">
    <div class="container">
        <div class="shop-wrapper">
            <div class="shop-information">
                <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) { ?>
                <div class="products__rating">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"></use>
                        </svg>
                    </i>
                    <span class="rate">
                        <?php echo round($shopRating, 1); //, ' ', Labels::getLabel('Lbl_Out_of', $siteLangId), ' ', '5';
                            if ($shopTotalReviews) { ?>
                        <!-- - <a href="<?php echo UrlHelper::generateUrl('Reviews', 'shop', array($shop['shop_id'])); ?>">
                                    <?php echo $shopTotalReviews, ' ', Labels::getLabel('Lbl_Reviews', $siteLangId); ?></a> -->
                        <?php } ?>
                    </span>
                </div>
                <?php } ?>
                <div class="shop-logo">
                    <?php
                    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_LOGO, $shop['shop_id'], 0, 0, false);
                    $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
                    ?>
                    <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?>
                        data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?>
                        src="<?php echo CommonHelper::generateUrl('image', 'shopLogo', array($shop['shop_id'], $siteLangId, 'SMALL')); ?>"
                        alt="<?php echo $shop['shop_name']; ?>">
                </div>

                <div class="shop-info">
                    <div class="shop-name">
                        <h5>
                            <?php echo $shop['shop_name']; ?>
                        </h5>
                        <span class="location">
                            <i class="icn icn-location">
                                <svg class="svg" height="16" width="12">
                                    <use xlink:href="/images/retina/sprite.svg#location"
                                        href="/images/retina/sprite.svg#location"></use>
                                </svg>
                            </i>
                            <?php echo $shop['shop_state_name'] . ', ' . $shop['shop_country_name']; ?>
                        </span>
                        <span class="blk-txt"><?php echo Labels::getLabel('LBL_Shop_Opened_On', $siteLangId); ?>
                            <strong> <?php $date = new DateTime($shop['shop_created_on']);
                                        echo $date->format('M d, Y'); ?>
                            </strong>
                        </span>
                    </div>

                    <div class="shop-btn-group">

                        <!-- Button trigger modal -->

                        <a href="javascript:void(0);" class="no-after share-icon" data-toggle="modal"
                            data-target="#shareModal">
                            <i class="icn icn-share">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#share">
                                    </use>
                                </svg>
                            </i>
                        </a>

                        <!-- Modal -->
                        <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModal"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">
                                            <?php echo Labels::getLabel('LBL_Share', $siteLangId); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="social-sharing">
                                            <li class="social-facebook">
                                                <a class="st-custom-button" data-network="facebook"
                                                    data-url="<?php echo UrlHelper::generateFullUrl('Products', 'view', array($shop['shop_id'])); ?>/">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#fb">
                                                            </use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-twitter">
                                                <a class="st-custom-button" data-network="twitter">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#tw">
                                                            </use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-pintrest">
                                                <a class="st-custom-button" data-network="pinterest">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pt">
                                                            </use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                            <li class="social-email">
                                                <a class="st-custom-button" data-network="email">
                                                    <i class="icn"><svg class="svg">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#envelope">
                                                            </use>
                                                        </svg></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $showAddToFavorite = true;
                        if (UserAuthentication::isUserLogged() && (!User::isBuyer())) {
                            $showAddToFavorite = false;
                        }
                        ?>
                        <?php if ($showAddToFavorite) { ?>
                        <a href="javascript:void(0)"
                            title="<?php echo ($shop['is_favorite']) ? Labels::getLabel('Lbl_Unfavorite_Shop', $siteLangId) : Labels::getLabel('Lbl_Favorite_Shop', $siteLangId); ?>"
                            onclick="toggleShopFavorite(<?php echo $shop['shop_id']; ?>);"
                            class="btn btn-brand <?php echo ($shop['is_favorite']) ? 'is-active' : ''; ?>"
                            id="shop_<?php echo $shop['shop_id']; ?>">
                            <i class="icn icn-fav-heart">
                                <svg class="svg icon-unchecked--js" style="<?php echo ($shop['is_favorite']) ? 'display:none' : ''; ?>" >
                                    <use
                                        xlink:href="/images/retina/sprite.svg#fav-heart">
                                    </use>
                                </svg>
                                <svg class="svg icon-checked--js"  style="<?php echo !($shop['is_favorite']) ? 'display:none' : ''; ?>"
                                    >
                                    <use
                                        xlink:href="/images/retina/sprite.svg#fav-heart-fill">
                                    </use>
                                </svg>
                            </i>
                        </a>
                        <?php } ?>
                        <?php $showMoreButtons = true;
                        if (isset($userParentId) && $userParentId == $shop['shop_user_id']) {
                            $showMoreButtons = false;
                        } ?>
                        <?php if ($showMoreButtons) {
                            $shopRepData = ShopReport::getReportDetail($shop['shop_id'], UserAuthentication::getLoggedUserId(true), 'sreport_id');
                            if (false === UserAuthentication::isUserLogged() || empty($shopRepData)) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('Shops', 'ReportSpam', array($shop['shop_id'])); ?>"
                            title="<?php echo Labels::getLabel('Lbl_Report_Spam', $siteLangId); ?>"
                            class="btn btn-brand"><i class="icn"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report"
                                        href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#report"></use>
                                </svg></i></a>
                        <?php } ?>
                        <?php if (!UserAuthentication::isUserLogged() || (UserAuthentication::isUserLogged() && ((User::isBuyer()) || (User::isSeller())))) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('shops', 'sendMessage', array($shop['shop_id'])); ?>"
                            title="<?php echo Labels::getLabel('Lbl_Send_Message', $siteLangId); ?>"
                            class="btn btn-brand"><i class="icn"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icon-msg"
                                        href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icon-msg"></use>
                                </svg></i></a>
                        <?php } ?>
                        <?php } ?>
                    </div>
                    <?php if (isset($socialPlatforms) && !empty($socialPlatforms)) { ?>
                    <div class="social-profiles">
                        <p><strong><?php echo Labels::getLabel('LBL_Follow_Us', $siteLangId); ?></strong> </p>
                        <ul class="social-icons">
                            <?php foreach ($socialPlatforms as $row) { ?>
                            <li>
                                <a <?php if ($row['splatform_url'] != '') { ?> target="_blank" <?php } ?>
                                    href="<?php echo ($row['splatform_url'] != '') ? $row['splatform_url'] : 'javascript:void(0)'; ?>"><i
                                        class="fab fa-<?php echo $row['splatform_icon_class']; ?>"></i>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="shop-nav">
                <?php
                $variables = array('template_id' => $template_id, 'shop_id' => $shop['shop_id'], 'shop_user_id' => $shop['shop_user_id'], 'collectionData' => $collectionData, 'action' => $action, 'siteLangId' => $siteLangId);
                $this->includeTemplate('shops/shop-layout-navigation.php', $variables, false); ?>
            </div>
        </div>
    </div>
</section>