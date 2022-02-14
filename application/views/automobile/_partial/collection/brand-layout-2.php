<?php if (isset($collection['brands']) && count($collection['brands']) > 0) { ?>
    <section class="section collection--brands">
        <div class="container">
            <div class="section__heading">
                <?php echo ($collection['collection_name'] != '') ? ' <h2>' . $collection['collection_name'] . '</h2>' : ''; ?>
                <?php echo ($collection['collection_description'] != '') ? ' <h5>' . $collection['collection_description'] . '</h5>' : ''; ?>
            </div>
            <div class="row justify-content-center">
                <?php foreach ($collection['brands'] as $brand) {  ?>
                    <div class="col">
                        <div class="brand-media flex-center">
                            <?php
                            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $brand['brand_id'], 0, 0, false);
                            ?>
                            <a href="<?php echo UrlHelper::generateUrl('brands', 'view', [$brand['brand_id']]); ?>">
                                <img data-aspect-ratio="3:4" alt="<?php echo $brand['brand_name']; ?>" title="<?php echo $brand['brand_name']; ?>" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'brandFeaturedImage', array($brand['brand_id'], $siteLangId, 'AUTOLOGO')), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if ($collection['totBrands'] > $recordLimit) { ?>
                <div class="flex-center">
                    <a href="<?php echo UrlHelper::generateUrl('brands'); ?>" class="btn btn-outline-brand btn-round arrow-right"><?php echo Labels::getLabel('LBL_View_All', $siteLangId) ?></a>
                </div>
            <?php } ?>
        </div>
    </section>
<?php } ?>