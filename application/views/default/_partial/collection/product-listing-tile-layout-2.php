<?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); 
$session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];
?>
<div
    class="product product-tile-2 <?php echo  isset($extraClsss) ? $extraClsss : ""; ?> <?php echo (array_key_exists($product['selprod_id'], $session)) ? "compared" : ""; ?>">
    <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0 && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) {
            $prodInCompList = 0;

            if (array_key_exists($product['selprod_id'], $session)) {
                $prodInCompList = 1;
            }

            include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/compare-label-ui.php');
        } ?>
    <div class="product-body">

        <a title="<?php echo $product['selprod_title']; ?>"
            href="<?php echo !isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>">
            <picture class="product-img" data-ratio="3:4">
                <source type="image/webp"
                    srcset="<?php echo UrlHelper::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], 'PRODUCT_LAYOUT_4', $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.webp'); ?>">
                <img data-aspect-ratio="3:4"
                    src="<?php echo UrlHelper::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($product['product_id'], 'PRODUCT_LAYOUT_4', $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                    alt="<?php echo $product['prodcat_name']; ?>">
            </picture>
        </a>
    </div>
    <div class="product-foot">
        <h5 class="product-category">
            <a href="<?php echo UrlHelper::generateUrl('Category', 'View', array($product['prodcat_id'])); ?>">
                <?php echo html_entity_decode($product['prodcat_name']); ?>
            </a>
        </h5>
        <h3 class="product-name">
            <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>" class=""
                title="<?php echo html_entity_decode($product['selprod_title']); ?>"><?php echo html_entity_decode($product['selprod_title']); ?></a>
        </h3>
        <?php include(CONF_DEFAULT_THEME_PATH . '_partial/collection/product-price.php'); ?>
    </div>
</div>