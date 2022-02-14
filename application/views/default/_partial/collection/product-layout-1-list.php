<!--product tile-->
<div class="products <?php echo (isset($layoutClass)) ? $layoutClass : ''; ?> <?php if ($product['selprod_stock'] <= 0) { ?> item--sold  <?php } ?>">
    <?php if ((ALLOW_SALE && $product['is_sell'] && $product['selprod_stock'] <= 0) && (ALLOW_RENT && $product['is_rent'] && $product['sprodata_rental_stock'] <= 0)) { ?>
        <span class="tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId); ?></span>
    <?php } ?>
    <div class="products__quickview">
        <a onClick='quickDetail(<?php echo $product['selprod_id']; ?>)' class="modaal-inline-content">
            <span class="svg-icon">
                <svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#quick-view">
                </use>
                </svg>
            </span><?php echo Labels::getLabel('LBL_Quick_View', $siteLangId); ?>
        </a>
        <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0 && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) { ?>
                <a class="modaal-inline-content compare_product_js_<?php echo $product['selprod_id']; ?> comp_product_cat_<?php echo $product['prodcat_id'];?> compProductsJs" href="javascript:void(0)" data-catid=<?php echo $product['prodcat_id'];?> style="margin-top:5px" onclick='addToCompareList(<?php echo $product["selprod_id"];?>, 1)'><?php echo Labels::getLabel('LBL_Add_To_Compare', $siteLangId);?></a>
            <?php } ?>
    </div>
    <div class="products__body">
        <?php if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) { ?>
            <div class="not-available"><svg class="svg">
                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                </use>
                </svg> <?php echo Labels::getLabel('LBL_NOT_AVAILABLE', $siteLangId); ?></div>
        <?php } ?>
        <?php include(CONF_DEFAULT_THEME_PATH . '_partial/collection-ui.php'); ?>
        <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
        <div class="products__img">
            <a title="<?php echo $product['selprod_title']; ?>" href="<?php echo!isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>"><img loading='lazy' data-ratio="1:1 (500x500)" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i == 1)) ? $prodImgSize : "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['prodcat_name']; ?>">
            </a>
        </div>
    </div>
    <?php
    $selprod_condition = true;
    include(CONF_DEFAULT_THEME_PATH . '_partial/product-listing-footer-section.php');
    ?>
</div>
<!--/product tile-->