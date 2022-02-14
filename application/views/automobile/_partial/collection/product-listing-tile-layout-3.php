<?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); 
$inStock = 1;
if(($product['sprodata_rental_active'] && $product['sprodata_rental_stock'] <=0) && ($product['selprod_active'] && $product['selprod_stock'] <=0)) {
	$inStock = 0;
}
$session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];

?>
<div class="items">
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
        
            <?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && $product['prodcat_comparison'] > 0 && ($compProdCount < 1 || $product['prodcat_id'] == $comparedProdSpecCatId)) {
                $prodInCompList = 0;
                if (array_key_exists($product['selprod_id'], $session)) {
                    $prodInCompList = 1;
                }
                include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/compare-label-ui.php');
            } ?>
        
            <div class="slide-media"> 
				<a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]);?>" tabindex="0">
					<img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], 'PRODUCT_LAYOUT_5', $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>">
				</a> 
			</div>
		</div>
		<div class="product__body"> 
			<a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]);?>" tabindex="0">
				<h5><?php echo html_entity_decode($product['selprod_title']); ?></h5>
			</a> 
			<span><a href="<?php echo UrlHelper::generateUrl('category', 'view', [$product['prodcat_id']]);?>"><?php echo html_entity_decode($product['prodcat_name']); ?></a></span>
			<?php include(CONF_THEME_PATH_WITH_THEME_NAME . '_partial/collection/product-price.php'); ?>
		</div>
	</div>
</div>