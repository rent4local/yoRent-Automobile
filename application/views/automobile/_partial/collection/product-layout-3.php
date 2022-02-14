<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
    $session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];
    if (isset($collection['products']) && count($collection['products']) > 0) { ?>
    <?php $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId); ?>
    <section class="section collection--product">
        <div class="container">
            <div class="section__heading">
                <h2><?php echo ($collection['collection_name'] != '') ? $collection['collection_name'] : ''; ?></h2>
                <h5><?php echo ($collection['collection_description'] != '') ? $collection['collection_description'] : ''; ?></h5>
            </div>
            <div class="d-grid d-lg-down-flex product-wrapper" data-view="3">
                <?php
                $productCount = 1;
                foreach ($collection['products'] as $product) {
                    if ($productCount > $recordLimit) {
                        break;
                    }
                    ?>
                    <div class="product tile-3">
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
                            
                            <div class="product-media">
                                <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
                                <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>"><img loading='lazy' data-ratio="4:3 (380X285)" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i == 1)) ? $prodImgSize : "AUTOCLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['prodcat_name']; ?>" /></a>
                            </div>
                        </div>
                        <div class="product__body">
                            <div class="product__body--head">
                                <div class="max-60">
                                    <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>" class="product-name"><?php echo html_entity_decode($product['selprod_title']); ?></a>
                                    <div class="product-description"> <?php echo html_entity_decode($product['prodcat_name']); ?> <span class="slash">|</span> <?php echo html_entity_decode($product['brand_name']); ?> <span class="slash">|</span><?php echo html_entity_decode($product['product_model']); ?> <!--<span class="slash">|</span> 2015 --> </div>

                                </div>
                                <div class="product--price"><?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?> <span class="slash-diagonal">/</span> <span><?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?></span></div>
                            </div>
                            <div class="product__body--body">
                                <?php include('product-custom-fields.php'); ?>
                            </div>
                            <div class="product__body--foot">
                                <div class="action">
                                    <a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]); ?>" class="btn btn-brand btn-round"><?php echo Labels::getLabel('LBL_RENT_NOW', $siteLangId); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $productCount++;
                }
                ?>    
            </div>
        </div>
    </section>
<?php } ?>
