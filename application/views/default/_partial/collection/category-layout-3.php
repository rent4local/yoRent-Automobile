<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($collection['categories']) && count($collection['categories']) > 0) {
?>
    <section class="section collection-category-product-2" id="categories_lay3_<?php echo $collection['collection_id']; ?>">
        <div class="container">
            <div class="section__title  text-center">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>
            <div class="explore--category faqTabs--flat-js">
                <ul class="js-tabs explore-links">
                    <?php
                    $index = 0;
                    foreach ($collection['categories'] as $key => $category) {
                    ?>
                        <li>
                            <a href="#tbcat_<?php echo $collection['collection_id'] . '_' . $key; ?>">
                                <i class="icn icn-work">
                                    <img data-aspect-ratio="1:1" src="<?php echo UrlHelper::generateFileUrl('category', 'icon', [$key, $siteLangId, 'COLLECTION_PAGE']); ?>" />
                                </i>
                                <p><?php echo $category['catData']['prodcat_name']; ?></p>
                            </a>
                        </li>
                    <?php
                        $index++;
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="container">
                <?php
                $extraClsss = "";
				$index = 0;
                $durationTypes = $collection['durationTypes'];
                foreach ($collection['categories'] as $key => $category) {
                ?>
                    <div class="tabs-content tabs-content-home--js" id="tbcat_<?php echo $collection['collection_id'] . '_' . $key; ?>">
                    <div class="slider-wrapper js-carousel" data-slides="4,3,3,2,2" data-infinite="false" data-arrows="false" data-slickdots="flase" data-mode="false" data-vertical="false" id="slider_<?php echo $collection['collection_id'] . '_' . $key; ?>">
                    <?php
                        foreach ($category['products'] as $prodKey => $product) {
                            $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']);
                            include('product-listing-tile-layout-2.php');
                        ?>
                        <?php } ?>
                    </div>     
                    </div>
                <?php 
				$index++;
				} ?>
         </div>
    </section>
<?php } ?>