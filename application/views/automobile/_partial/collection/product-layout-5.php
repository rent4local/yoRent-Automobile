<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php 
    $session = (isset($_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'])) ? $_SESSION[CompareProduct::COMPARE_SESSION_ELEMENT_NAME]['products'] : [];
    if (isset($collection['records']) && count($collection['records']) > 0) { ?>
    <?php $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId); ?>
    <section class="section collection--tabs">
        <div class="container">
            <div class="section__heading">
                <h2><?php echo $collection['collection_name']; ?></h2>
                <h5><?php echo $collection['collection_description']; ?></h5>
            </div>
            <div class="automobile-tabs">
                <ul class="js--tabs">
                    <?php
                    $i = 1;
                    foreach ($collection['records'] as $subColId => $subCollection) {
                        ?>
                        <li>
                            <a href="#tab_<?php echo $subColId; ?>" class="<?php echo ($i == 1) ? "is-active" : ""; ?>"><?php echo $subCollection['collection_name']; ?></a>
                        </li>
                        <?php
                        $i++;
                    }
                    ?>
                </ul>
            </div>
            <?php
            $j = 1;
            foreach ($collection['records'] as $subColId => $subCollection) {
                ?>
                <div id="tab_<?php echo $subColId; ?>" class="tab_content <?php echo ($j == 1) ? "visible" : ""; ?> ">
                    <?php
                    $productCount = 0;
                    foreach ($subCollection['products'] as $product) {
                        if ($productCount >= $recordLimit) {
                            break;
                        }
                        ?>
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
                                <div class="car-category-media">
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
                                    } ?>
                                    
                                    
                                    <a title="<?php echo html_entity_decode($product['selprod_title']); ?>" href="<?php echo!isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>">
                                        <img data-aspect-ratio="4:3" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($product['product_id'], 'AUTOCLAYOUT5', $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo html_entity_decode($product['prodcat_name']); ?>">
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="car-category">
                                    <h2><?php echo html_entity_decode($product['selprod_title']); ?></h2>
                                    <ul class="category-list">
                                        <li>
                                            <h5><?php echo Labels::getLabel('LBL_Price_Starting_at', $siteLangId); ?></h5>
                                            <span><?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?>/<?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?></span>
                                        </li>
                                        <?php
                                        $attributesArr = (isset($prodCatAttributes[$product['prodcat_id']])) ? $prodCatAttributes[$product['prodcat_id']] : [];
                                        $attributeValues = (isset($prodCustomFldsData[$product['product_id']])) ? $prodCustomFldsData[$product['product_id']] : [];
                                        if (!empty($attributesArr) && !empty($attributeValues)) {
                                            ?>
                                            <?php
                                            foreach ($attributesArr as $groupId => $attributeData) {
                                                foreach ($attributeData as $attributeVal) {
                                                    if (isset($attributeValues[$groupId][$attributeVal['attr_fld_name']]) && !empty($attributeValues[$groupId][$attributeVal['attr_fld_name']])) {
                                                        ?>
                                                        <li> 
                                                            <h5><?php echo (trim($attributeVal['attr_name']) == '') ? $attributeVal['attr_identifier'] : $attributeVal['attr_name']; ?></h5>
                                                            <span>
                                                                <?php
                                                                if ($attributeVal['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX) {
                                                                    $attrOpt = explode("\n", $attributeVal['attr_options']);
                                                                    $selectedOptions = $attributeValues[$groupId][$attributeVal['attr_fld_name']];
                                                                    $selectedOptions = explode(',', $selectedOptions);
                                                                    $i = 1;
                                                                    $itemCount = 0;
                                                                    if (!empty($selectedOptions)) {
                                                                        foreach ($selectedOptions as $option) {
                                                                            if (!isset($attrOpt[$option])) {
                                                                                continue;
                                                                            }
                                                                            echo $attrOpt[$option] . ' ' . $attributeVal['attr_postfix'];
                                                                            if ($i < count($selectedOptions)) {
                                                                                echo ', ';
                                                                            }
                                                                            $i++;
                                                                            $itemCount++;
                                                                        }
                                                                    } else {
                                                                        echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                                    }

                                                                    if ($itemCount == 0) {
                                                                        echo Labels::getLabel('LBL_N/A', $siteLangId);
                                                                    }
                                                                } else {
                                                                    echo $attributeValues[$groupId][$attributeVal['attr_fld_name']];
                                                                    echo $attributeVal['attr_postfix'];
                                                                }
                                                                ?>

                                                            </span>
                                                        </li>
                                                        <?php
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </ul>
                                    <a title="<?php echo html_entity_decode($product['selprod_title']); ?>" href="<?php echo!isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>" class="btn btn-brand btn-round arrow-right"><?php echo Labels::getLabel('LBL_RENT_NOW', $siteLangId); ?> </a>
                                </div>
                            </div>
                        </div>
                        <?php
                        $productCount++;
                    }
                    ?>
                </div>
                <?php
                $j++;
            }
            ?>
        </div>
    </section>
<?php } ?>  