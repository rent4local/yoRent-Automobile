<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($relatedProductsRs) {
?>
    <div class="container">
        <div class="d-flex justify-content-between">
            <div class="section__title">
                <h2><?php echo Labels::getLabel('LBL_Similar_Products', $siteLangId); ?></h2>
            </div>
            <div class="related_prod slide-arrow">
                <button class="arrow-prev slick-arrow slick-disabled" aria-disabled="true" style=""></button>
                <button class="arrow-next slick-arrow" style="" aria-disabled="false"></button>
            </div>
        </div>

        <div class="slider-wrapper <?php echo (count($relatedProductsRs) > 3) ? "js-carousel" : ""; ?>" data-slides="4,2,1,1,1" data-infinite="false" data-arrows="false" data-slickdots="flase" dir="<?php echo CommonHelper::getLayoutDirection(); ?>">
            <?php
            foreach ($relatedProductsRs as $rproduct) {
                $dataToSend = [
                    'product' => $rproduct,
                    'siteLangId' => $siteLangId,
                    'compProdCount' => (isset($compProdCount)) ? $compProdCount  : 0,
                    'prodInCompList' => (isset($prodInCompList)) ? $prodInCompList  : 0,
                    'comparedProdSpecCatId' => (isset($comparedProdSpecCatId)) ? $comparedProdSpecCatId  : 0,
                ];

                echo $this->includeTemplate('_partial/collection/product-listing-tile-layout-2.php', $dataToSend);
            } ?>
        </div>

    </div>
<?php }
?>