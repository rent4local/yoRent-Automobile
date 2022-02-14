<?php
$session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];
?>
<div class="product">
    <div class="product__head">
        <?php if (array_key_exists('availableForPickup', $product) && 0 == $product['availableForPickup']) { ?> 
            <div class="not-pick-ship">
                <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"></use>
                </svg> 
                <?php echo Labels::getLabel('LBL_NOT_PICKABLE', $siteLangId); ?>
            </div>
        <?php } ?>
        <?php if (array_key_exists('availableForShip', $product) && 0 == $product['availableForShip']) { ?> 
            <div class="not-pick-ship">
                <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info"></use>
                </svg> 
                <?php echo Labels::getLabel('LBL_NOT_SHIPABLE', $siteLangId); ?>
            </div>
        <?php } ?>
        <?php
        if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0 && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) {
            $prodInCompList = 0;
            if (array_key_exists($product['selprod_id'], $session)) {
                $prodInCompList = 1;
            }
            include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/compare-label-ui.php');
        }
        ?>
        <div class="product-media">
            <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
            <a title="<?php echo html_entity_decode($product['selprod_title']); ?>" href="<?php echo!isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>">
                <img loading='lazy' data-ratio="4:3" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i == 1)) ? $prodImgSize : "AUTOCLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['prodcat_name']; ?>">
            </a>
        </div>
        <?php if ($product['special_price_found'] > 0) { ?>
            <div class="off-price"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></div>
        <?php } ?>
    </div>
    <?php
    $selprod_condition = true;
    include('product-listing-footer-section.php');
    ?>
</div>