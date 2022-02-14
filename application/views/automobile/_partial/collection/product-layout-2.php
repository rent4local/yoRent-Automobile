<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($collection['products']) && count($collection['products']) > 0) {
    ?>
    <section class="section collection--product-tile-2">
        <div class="container">
            <div class="section__heading">
                <h2><?php echo $collection['collection_name']; ?></h2>
                <h5><?php echo $collection['collection_description']; ?></h5>
            </div>
            <div class="js-carousel product-wrapper"  data-slides="3,3,2,1,1" data-infinite="false" data-arrows="false" data-slickdots="true">
                <?php
                foreach ($collection['products'] as $product) {
                    $layoutClass = 'products--layout';
                    include('product-listing-tile-layout-2.php');
                }
                ?>
            </div>
        </div>
    </section>
<?php }
?>
