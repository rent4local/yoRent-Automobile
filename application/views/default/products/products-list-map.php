<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$colMdVal = isset($colMdVal) ? $colMdVal : 4;
$displayProductNotAvailableLable = false;
if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
    $displayProductNotAvailableLable = true;
}
?>
<div id="productsList">
    <?php
    $productsByShop = [];
    if ($products) {
        ?>
    <div class="interactive-stores__list stores">
        <div class="stores-body scroll scroll-y">
            <ul id="mapProducts--js">
                <?php
                    foreach ($products as $product) {
                        $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']);
                        $productUrl = !isset($product['promotion_id']) ? UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateFullUrl('Products', 'track', array($product['promotion_record_id']));
                        $img = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "PRODUCT_LAYOUT_1", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        $productsByShop[$product['shop_id']]['lat'] = $product['shop_lat'];
                        $productsByShop[$product['shop_id']]['lng'] = $product['shop_lng'];
                        $productsByShop[$product['shop_id']]['products'][] = ['url' => $productUrl, 'name' => ((mb_strlen($product['selprod_title']) > 30) ? mb_substr($product['selprod_title'], 0, 50) . "..." : $product['selprod_title']), 'img' => $img];
                        $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id']);
                        ?>

                <li data-shopId="<?php echo $product['shop_id']; ?>">
                    <a class="store" href="<?php echo $productUrl; ?>">
                        <div class="store__img">
                            <img loading='lazy' data-ratio="1:1"
                                src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "PRODUCT_LAYOUT_1", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $product['prodcat_name']; ?>"
                                title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $product['prodcat_name']; ?>">
                        </div>
                        <div class="store__detail">
                            <h6><?php echo (mb_strlen($product['selprod_title']) > 50) ? mb_substr($product['selprod_title'], 0, 50) . "..." : $product['selprod_title']; ?>
                            </h6>
                            <p class="location">
                                <?php echo $product['prodcat_name']; ?>
                            </p>
                            <div class="store__detail-foot">
                                <?php
                                        if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
                                            $rating = round($product['prod_rating'], 1);
                                            if (round($product['totReviews']) > 0) {
                                                ?>
                                <div class="products__rating">
                                    <?php for ($ii = 0; $ii < $rating; $ii++) { ?>
                                    <i class="icn">
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow"
                                                href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#star-yellow">
                                            </use>
                                        </svg>
                                    </i>
                                    <?php } ?>
                                    <span class="rate">(<?php echo round($product['totReviews'], 1); ?>
                                        <?php echo Labels::getLabel('LBL_Customer_Reviews', $siteLangId); ?>)</span>
                                </div>
                                <?php
                                            }
                                        }
                                        include(CONF_DEFAULT_THEME_PATH . '_partial/collection/product-price.php');
                                        ?>
                            </div>
                        </div>
                    </a>
                </li>
                <?php } ?>

            </ul>
        </div>
    </div>


    <?php
        $searchFunction = 'goToProductListingSearchPage';
        if (isset($pagingFunc)) {
            $searchFunction = $pagingFunc;
        }

        $postedData['page'] = (isset($page)) ? $page : 1;
        $postedData['recordDisplayCount'] = $recordCount;
        echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
        $pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction, 'siteLangId' => $siteLangId);
        $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
        ?>
    <?php
    } else {
        $arr['recordDisplayCount'] = $recordCount;
        echo FatUtility::createHiddenFormFromData($arr, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
foreach ($productsByShop as &$marker) {
    $contentString = '<ul>';
    foreach ($marker['products'] as $product) {
        $contentString .= '<li>
            <figure class="product-profile">
                <div class="product-profile__thumbnail"><img class="product-img" src="' . $product['img'] . '" alt="Trulli"></div>
                <figcaption class="product-name"><a href="' . $product['url'] . '">' . html_entity_decode($product['name']) . '</a></figcaption>
            </figure>
            </li>';
    }
    $contentString .= '</ul>';
    unset($marker['products']);
    $marker['content'] = $contentString;
}

$userAddress = Address::getYkGeoData();
$lat = ($userAddress['ykGeoLat'] == '') ? FatApp::getConfig('CONF_GEO_DEFAULT_LAT', FatUtility::VAR_STRING, '') : $userAddress['ykGeoLat'];
$lng = ($userAddress['ykGeoLng'] == '') ? FatApp::getConfig('CONF_GEO_DEFAULT_LNG', FatUtility::VAR_STRING, '') : $userAddress['ykGeoLng'];

?>
<script>
var markers = <?php echo json_encode($productsByShop); ?>;
$(document).ready(function() {
    if (typeof map == 'undefined') {
        initMutipleMapMarker(markers, 'productMap--js', "<?php echo $lat; ?>", "<?php echo $lng; ?>",
            dragCallback);
    } else {
        clearMarkers();
        createMarkers(markers);
    }
});
</script>