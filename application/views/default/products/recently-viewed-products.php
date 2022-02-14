<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$displayProductNotAvailableLable = false;
if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
    $displayProductNotAvailableLable = true;
}
if ($recentViewedProducts) {
?>
    <div class="container">
        <div class="d-flex justify-content-between">
            <div class="section__title">
                <h2><?php echo Labels::getLabel('LBL_Recently_Viewed', $siteLangId); ?></h2>
            </div>
            <div class="recent-prod slide-arrow">
                <button class="arrow-prev slick-arrow slick-disabled" aria-disabled="true" style=""></button>
                <button class="arrow-next slick-arrow" style="" aria-disabled="false"></button>
            </div>
        </div>
        <div class="slider-wrapper js-carousel" data-slides="4,2,1,1,1" data-infinite="false" data-arrows="false" data-slickdots="flase" >
            <?php
            foreach ($recentViewedProducts as $product) {
                //$productUrl = UrlHelper::generateUrl('Products', 'View', array($rProduct['selprod_id']));

                include(CONF_DEFAULT_THEME_PATH . '_partial/collection/product-listing-tile-layout-2.php');
                //include('product-listing-tile-layout-2.php');
            } ?>
        </div>
    </div>
<?php
}
?>