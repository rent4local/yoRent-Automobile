<?php if (isset($collection['brands']) && count($collection['brands']) > 0) { ?>
    <section class="section collection-brand " id="brand_layout2_<?php echo $collection['collection_id']; ?>">
        <div class="container">
            <div class="section__title  text-center">
                <h5><?php echo $collection['collection_description']; ?></h5>
                <h2><?php echo $collection['collection_name']; ?></h2>
            </div>
        </div>
        <div class="container">
            <div class="js-carousel"  data-slides="5,3,3,2,2"  data-arrows="true" data-slickdots="true"  id="slider_<?php echo $collection['collection_id']; ?>" data-slickinfinite="false">
                <?php foreach ($collection['brands'] as $brand) { ?>
                    <div>
                        <div class="brand">
                            <div class="brand__card">
                                <div class="brand__card--body">
                                    <div class="brand-media">
                                        <a href="<?php echo UrlHelper::generateUrl('brands', 'View', array($brand['brand_id'])); ?>">
                                        <picture class="product-img" data-ratio="3:4">
                                        <source type="image/webp" srcset="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'brandFeaturedImage', array($brand['brand_id'], $siteLangId, 'DESKTOP')), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                                        <img data-aspect-ratio="3:4"  alt="<?php echo $brand['brand_name']; ?>" title="<?php echo $brand['brand_name']; ?>" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'brandFeaturedImage', array($brand['brand_id'], $siteLangId, 'DESKTOP')), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                                        </picture>
                                        </a>
                                    </div>
                                </div>
                                <div class="brand__card--foot">
                                    <div class="brand-logo-media">
                                        <a href="<?php echo UrlHelper::generateUrl('brands', 'View', array($brand['brand_id'])); ?>">
                                            <img data-aspect-ratio="1:1"  src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'brand', array($brand['brand_id'], $siteLangId, 'BRAND_LAYOUT_2')), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $brand['brand_name']; ?>" title="<?php echo $brand['brand_name']; ?>">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>


            <!-- <div id="sliderControls_<?php echo $collection['collection_id']; ?>" class="slider-controls d-flex justify-content-center align-items-center" data-href="#slider_<?php echo $collection['collection_id']; ?>">
                <a href="javascript:void(0);" data-href="#slider_<?php echo $collection['collection_id']; ?>" class="slider-btn prev-arrow order-4">Prev</a>
                <a href="javascript:void(0);" data-href="#slider_<?php echo $collection['collection_id']; ?>" class="slider-btn next-arrow order-5">Next</a>                            
            </div>   -->

        </div>
    </section>
<?php } ?>