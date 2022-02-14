<div class="product__body">
    <div class="product__body--head">
        <div class="product-name--wrapper">
            <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])); ?>" class="product-name"><?php echo html_entity_decode($product['selprod_title']); ?></a>

        </div>

        <div class="product-description"> <?php echo html_entity_decode($product['prodcat_name']); ?> <span class="slash">|</span> <?php echo html_entity_decode($product['brand_name']); ?> <span class="slash">|</span><?php echo html_entity_decode($product['product_model']); ?></div>

        <?php if (isset($product['isListingPage']) && $product['isListingPage']) {
        ?>
            <div class="product-rate-info">

                <?php
                /* [ RATING DIV GOES HERE */
                if (isset($product['prod_rating'])) { ?>
                    <div class="product-rating product-rating-inline">
                        <ul>
                            <li class="active"></li>
                        </ul>
                        <span class="rating-count"><?php echo round($product['prod_rating'], 1); ?></span>
                    </div>
                <?php }
                /* ] */
                /* NEED TO UPDATE UI */
                if (isset($product['distance']) && $product['availableForPickup']) { ?>
                    <div class="product-distance">
                        <span class="icn">
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#location"></use>
                            </svg>
                        </span> <span class="distance-count"><?php echo round($product['distance'], 0) . '</span> ' . Labels::getLabel('LBL_KM_Away', $siteLangId); ?>
                    </div>
                <?php

                }
                ?>
            </div>
        <?php
        } ?>
    </div>
    <div class="product__body--body"><?php include('product-custom-fields.php'); ?></div>
    <?php include('product-price.php'); ?>
</div>