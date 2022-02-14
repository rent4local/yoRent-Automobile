<?php
$markers = [];
if (!empty($allShops)) {
?>
<div class="interactive-stores__list stores">
    <div class="d-flex align-items-center pb-3">
        <h4 class="block-heading">STORES</h4>
        <div class="stores-count ml-auto">
            <?php echo Labels::getLabel('LBL_SHOWING_RESULTS', $siteLangId); ?> &nbsp;<?php echo $recordCount; ?>
        </div>
    </div>
    <div class="stores-body scroll scroll-y">
        <ul id="mapShops--js">
            <?php
            foreach ($allShops as $shop) {
                $markers[$shop['shop_id']] = [
                    'lat' => $shop['shop_lat'],
                    'lng' => $shop['shop_lng'],
                    'content' => '<a href="'.UrlHelper::generateUrl('shops', 'view', array($shop['shop_id']), '', null, false, false, true, true).'">'.$shop['shop_name'].'</a>',
                ];
            ?>
            <li data-shopId="<?php echo $shop['shop_id']; ?>">
                <a class="store"
                    href="<?php echo UrlHelper::generateUrl('shops', 'view', array($shop['shop_id']), '', null, false, false, true, true); ?>">
                    <div class="store__img">
                        <?php
                            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_LOGO, $shop['shop_id'], 0, 0, false);
                            $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
                            ?>
                        <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?>
                            data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?>
                            src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shop_id'], $siteLangId, "THUMB", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                            alt="<?php echo $shop['shop_name']; ?>">
                    </div>
                    <div class="store__detail">
                        <h6><?php echo $shop['shop_name']; ?></h6>
                        <p class="location">
                            <?php echo $shop['state_name']; ?><?php echo ($shop['country_name'] && $shop['state_name']) ? ', ' : ''; ?><?php echo $shop['country_name']; ?>
                        </p>
                        <div class="store__detail-foot">
                            <?php if (0 < FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0) && round($shop['shopRating']) > 0) { ?>
                            <div class="product-rating product-rating-inline">
                                <ul>
                                    <?php for ($ii = 0; $ii < 5; $ii++) {
                                        $liClass = '';
                                        if ($ii < round($shop['shopRating'], 1)) {
                                            $liClass = 'active';
                                        }
                                    ?>
                                    <li class="<?php echo $liClass; ?>"></li>
                                    <?php } ?>
                                </ul>
                                <p><?php echo  round($shop['shopRating'], 1); ?></p>
                            </div>
                            <?php /* <div class="products__rating">
                                <i class="icn"><svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                            href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                        </use>
                                    </svg></i> <span
                                    class="rate"><?php echo  round($shop['shopRating'], 1); ?><span></span></span>
                            </div> */ ?>
                            <?php } ?>
                        </div>
                    </div>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>
<?php
} else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false);
}

$postedData['page'] = (isset($page)) ? $page : 1;
$postedData['recordDisplayCount'] = $recordCount;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchShopsPaging', 'id' => 'frmSearchShopsPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToShopSearchPage', 'siteLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);

?>
<script>
var markers = <?php echo json_encode($markers); ?>;
</script>