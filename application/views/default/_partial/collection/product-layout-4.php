<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (isset($collection['products']) && count($collection['products']) > 0) { ?>
    <section class="section collection-product" id="product_layout_4_<?php echo $collection['collection_id']; ?>">
        <div class="container container--narrow">
            <div class="section__title  text-center">
                <?php if (!empty($collection['collection_description'])) { ?>
                    <h5><?php echo $collection['collection_description']; ?></h5>
                <?php } ?>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>

            <div class="slider-wrapper js-carousel" data-slides="4,2,2,1,1" data-infinite="false" data-arrows="false" data-slickdots="false">
                <?php
                $displayProductNotAvailableLable = false;
                if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
                    $displayProductNotAvailableLable = true;
                }
                $extraClsss = " ";
                $footerExtraClasses = "product_slide_foot border-radius-all";
                foreach ($collection['products'] as $product) {
                    include('product-listing-tile-layout-3.php');
                }
                ?>
            </div>
        </div>
    </section>
<?php
}
