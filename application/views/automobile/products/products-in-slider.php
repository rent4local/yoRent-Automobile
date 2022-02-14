<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($products)) {
    ?>
    <div class="container">
        <div class="section__heading">
            <h2><?php echo $heading; ?></h2>
            <?php if (isset($subheading) && trim($subheading) != '') { ?>
                <h5><?php echo $subheading; ?></h5> 
            <?php } ?>
        </div>
        <div class="product-wrapper js-carousel"  data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true" data-slickdots="true" data-theme="automobile">
            <?php
            foreach ($products as $rproduct) {
                $dataToSend = [
                    'product' => $rproduct,
                    'siteLangId' => $siteLangId,
                    'compProdCount' => (isset($compProdCount)) ? $compProdCount : 0,
                    'prodInCompList' => (isset($prodInCompList)) ? $prodInCompList : 0,
                    'comparedProdSpecCatId' => (isset($comparedProdSpecCatId)) ? $comparedProdSpecCatId : 0,
                ];
                echo $this->includeTemplate('_partial/collection/product-layout-1-list.php', $dataToSend);
            }
            ?>
        </div>
    </div>
<?php }
?>