<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$colMdVal = isset($colMdVal) ? $colMdVal : 3;
$displayProductNotAvailableLable = false;
if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
    $displayProductNotAvailableLable = true;
}

$session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];
$rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId);
?>
<div class="listing-products -listing-products">
    <div id="productsList" class="listing-products--grid">
        <?php
        if ($products) { ?>
        <div class="product-listing" data-view="<?php echo $colMdVal; ?>">
            <?php
                $showActionBtns = !empty($showActionBtns) ? $showActionBtns : false;
                $isWishList = isset($isWishList) ? $isWishList : 0;
                ?>
            <?php
                foreach ($products as $product) {
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                    $extraClsss = '';
                ?>
            <div class="product">
                <div class="product__head">
                    <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0 && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) {
                                $prodInCompList = 0;
                                if (array_key_exists($product['selprod_id'], $session)) {
                                    $prodInCompList = 1;
                                }
                                include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/compare-label-ui.php');
                            } ?>
                    <a href="javascript:void(0)" onclick="removeFavProduct(<?php echo $product['selprod_id']; ?>)"
                        class="close-layer"></a>
                    <div class="product-media">
                        <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
                        <a title="<?php echo $product['selprod_title']; ?>"
                            href="<?php echo !isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>">

                            <img loading='lazy' data-ratio="4:3"
                                src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i == 1)) ? $prodImgSize : "AUTOCLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                alt="<?php echo $product['prodcat_name']; ?>">
                        </a>
                    </div>
                    <?php if ($product['special_price_found'] > 0) { ?>
                    <div class="off-price"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?>
                    </div>
                    <?php } ?>
                </div>
                <?php
                        $selprod_condition = true;
                        ?>
                <div class="product__body">
                    <div class="product__category"><a
                            href="<?php echo UrlHelper::generateUrl('Category', 'View', array($product['prodcat_id'])); ?>"><?php echo $product['prodcat_name']; ?>
                        </a></div>
                    <div class="product__title"><a title="<?php echo $product['selprod_title']; ?>"
                            href="<?php echo !isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>"><?php echo (mb_strlen($product['selprod_title']) > 50) ? mb_substr($product['selprod_title'], 0, 50) . "..." : $product['selprod_title']; ?>
                        </a></div>
                    <div class="product--price">
                        <span class="bold">
                            <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?>
                        </span>
                        <span>
                            <span class="slash-diagonal">/</span>
                            <?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?>
                        </span>
                    </div>
                    <a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]); ?>"
                        class="btn btn-brand btn-round"><?php echo Labels::getLabel('LBL_RENT_NOW', $siteLangId); ?></a>
                </div>
            </div>

            <?php } ?>
        </div>


        <?php
            $searchFunction = 'goToProductListingSearchPage';
            if (isset($pagingFunc)) {
                $searchFunction = $pagingFunc;
            }

            $postedData['page'] = (isset($page)) ? $page : 1;
            $postedData['recordDisplayCount'] = $recordCount;
            echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
            $pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction, 'removePageCentClass' => 1, 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true); ?>
        <div class="collection-pager">
            <?php
                $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
                if (!isset($removePageSize)) {
                ?>
            <select name="pageSizeSelect" id="pageSizeSelect" class="custom-select sorting-select">
                <?php foreach ($pageSizeArr as $key => $val) { ?>
                <option value="<?php echo $key; ?>" <?php echo ($key == $pageSize) ? 'selected' : ''; ?>>
                    <?php echo $val; ?>
                </option>
                <?php } ?>
            </select>
            <?php } ?>
        </div>

        <?php } else { ?>
        <?php
            $arr['recordDisplayCount'] = $recordCount;
            echo FatUtility::createHiddenFormFromData($arr, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
            $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
            $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
            ?>
        <?php }
        ?>
    </div>
    <script>
    $(document).ready(function() {
        var e = document.getElementById("pageSizeSelect");
        if (e != undefined && e != null) {
            var pageSize = e.options[e.selectedIndex].value;
            $('#pageSize').val(pageSize);
        }
    })
    </script>
    <?php
    if (isset($postedData['vtype']) && $postedData['vtype'] == 'map') {
        foreach ($productsByShop as &$marker) {
            $contentString = '<ul>';
            foreach ($marker['products'] as $product) {
                $contentString .= '<li>
            <figure class="product-profile">
            <div class="product-profile__thumbnail"><img class="product-img" src="pic_trulli.jpg" alt="Trulli"></div>
  <figcaption class="product-name"><a href="' . $product['url'] . '">' . html_entity_decode($product['name']) . '</a></figcaption>
</figure>
            </li>';
            }
            $contentString .= '</ul>';
            unset($marker['products']);
            $marker['content'] = $contentString;
        }
    ?>
    <script>
    var markers = <?php echo json_encode($productsByShop); ?>;
    $(document).ready(function() {
        if (typeof map == 'undefined') {
            initMutipleMapMarker(markers, 'productMap--js', getCookie('_ykGeoLat'), getCookie('_ykGeoLng'),
                dragCallback);
        } else {
            clearMarkers();
            createMarkers(markers);
        }
    });
    </script>
    <?php } ?>