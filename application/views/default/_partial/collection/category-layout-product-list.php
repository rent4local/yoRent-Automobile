<div id="tabUl" class="tabs tabs--flat-js justify-content-md-center">
    <ul>
        <?php
        $collection_id = $collection['collection_id'];
        $index = 0;
        foreach ($collection['categories'] as $key => $category) {
            ?>
            <li class="<?php echo ($index == 0) ? "is-active" : ""; ?>">
                <a href="#tb-<?php echo $collection_id . '-' . $key; ?>"><?php echo $category['catData']['prodcat_name']; ?></a>
            </li>
            <?php
            $index++;
        }
        ?>
        <li class="no-tabs--js">
            <a href="<?php echo UrlHelper::generateFileUrl('collections', 'view', array($collection_id)); ?>"><?php echo Labels::getLabel('LBL_VIEW_ALL', $siteLangId); ?></a>
        </li>
    </ul>
</div>
<?php
$index = 0;
foreach ($collection['categories'] as $key => $category) {
    ?>
    <div id="tb-<?php echo $collection_id . '-' . $key; ?>" class="tabs-content tabs-content-js <?php echo ($index == 0) ? "active-tab--js" : ""; ?>" style="display: block;">
        <div class="ft-pro-wrapper">
            <?php
            $i = 1;
            foreach ($category['products'] as $key => $product) {
                ?>
                <div class="ft-pro ft-pro-<?php echo $i; ?>">
                    <?php $prodImgSize = 'MEDIUM'; ?>

                    <!--product tile-->
                    <div class="products <?php echo (isset($layoutClass)) ? $layoutClass : ''; ?> <?php if ($product['selprod_stock'] <= 0) { ?> item--sold  <?php } ?>">
                        <?php if ((ALLOW_SALE && $product['is_sell'] && $product['selprod_stock'] <= 0) && (ALLOW_RENT && $product['is_rent'] && $product['sprodata_rental_stock'] <= 0)) { ?>
                            <span class="tag--soldout"><?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId); ?></span>
                        <?php } ?>

                        <div class="products__body">
                            <?php if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) { ?>
                                <div class="not-available"><svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                                    </use>
                                    </svg> <?php echo Labels::getLabel('LBL_NOT_AVAILABLE', $siteLangId); ?></div>
                            <?php } ?>
                            <?php include(CONF_THEME_PATH . 'default/_partial/collection-ui.php'); ?>
                            <?php $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']); ?>
                            <div class="products__img">
                                <a title="<?php echo $product['selprod_title']; ?>" href="<?php echo!isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])); ?>">
                                    <?php $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id']); ?>
                                    <img loading='lazy' data-ratio="1:1 (500x500)" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], (isset($prodImgSize) && isset($i) && ($i == 1)) ? $prodImgSize : "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $product['prodcat_name']; ?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $product['prodcat_name']; ?>">
                                </a>
                            </div>
                        </div>
                        <div class="content-overlay"></div>
                        <div class="content-details">
                            <div class="">
                                <div class="products__title"><a title="<?php echo $product['selprod_title']; ?>" href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>"><?php echo $product['selprod_title']; ?>
                                    </a></div>
                                <?php include(CONF_THEME_PATH . 'default/_partial/collection/product-price.php'); ?>
                            </div>

                        </div>
                    </div>
                    <!--/product tile-->
                </div>
                <?php
                $i++;
            }
            ?>
        </div>
    </div>
    <?php
    $index++;
}