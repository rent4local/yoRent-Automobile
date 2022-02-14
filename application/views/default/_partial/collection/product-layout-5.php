<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (isset($collection['records']) && count($collection['records']) > 0) { ?>
    <section class="section collection-category-product" id="product_layout5_<?php echo $collection['collection_id']; ?>">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-3 col-lg-12 col-md-12">
                    <div>
                        <div class="section__title">
                            <h5><?php echo $collection['collection_description']; ?></h5>
                            <h2><?php echo $collection['collection_name']; ?></h2>
                        </div>
                        <p class="title-detail"><?php echo $collection['collection_text']; ?></p>
                        <?php /* <div class="btn-layout mt-4"><a href="<?php echo UrlHelper::generateUrl('collections', 'view', [$collection['collection_id']]); ?>" class="btn btn-brand btn-theme"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?></a></div> */ ?>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-12 col-md-12">
                    <div class="latest_links faqTabs--flat-js">
                        <ul class="js-tabs">
                            <?php
                            $i = 0;
                            foreach ($collection['records'] as $subColId => $subCollection) {
                                ?>
                                <li class="<?php echo ($i == 0) ? "is-active" : ""; ?>">
                                    <a href="#tab_layout5_<?php echo $collection['collection_id'] ?>_<?php echo $subColId; ?>"><?php echo $subCollection['collection_name']; ?></a>
                                </li>
                                <?php
                                $i++;
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="tabs-container">
                        <!--TABS SECTION START-->
                        <?php
                        $extraClsss = "";
                        foreach ($collection['records'] as $subColId => $subCollection) {
                            ?>
                            <div class="tabs-content tabs-content-home--js" id="tab_layout5_<?php echo $collection['collection_id'] ?>_<?php echo $subColId; ?>">
                                <div class="product-listing" data-view="4"> 
                                    <?php
                                    $productCount = 0;
                                    foreach ($subCollection['products'] as $product) {
                                        if ($productCount >= $recordLimit) {
                                            break;
                                        }

                                        include('product-listing-tile-layout.php');
                                        $productCount++;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                        <!--- TABS SECTION END --->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}
