<?php if ($recommendedProducts) { ?>
    <div class="container">
        <div class="d-flex justify-content-between">
            <div class="section__title">
                <h2><?php echo Labels::getLabel('LBL_Recommended_Products', $siteLangId); ?></h2>
            </div>
            <div class="slide-arrow recommended-prod">
                <button class="arrow-prev slick-arrow slick-disabled" aria-disabled="true" style=""></button>
                <button class="arrow-next slick-arrow"  aria-disabled="false"></button>
            </div>
        </div>
        <div class="slider-wrapper js-carousel"  data-slides="4,2,1,1,1" data-infinite="false" data-arrows="false" data-slickdots="flase">
            <?php
            foreach ($recommendedProducts as $product) {
                // $productUrl = UrlHelper::generateUrl('Products', 'View', array($rProduct['selprod_id']));

                include(CONF_DEFAULT_THEME_PATH . '_partial/collection/product-listing-tile-layout-2.php');
            } ?>
        </div>
    </div>
<?php
}
