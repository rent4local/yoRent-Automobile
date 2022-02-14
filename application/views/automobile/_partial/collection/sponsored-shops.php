<?php if (isset($collection['shops']) && count($collection['shops']) > 0) { ?>
    <section class="section collection-shop" id="shop_layout2_<?php echo $collection['collection_id']; ?>">
        <div class="container container--narrow">
            <div class="section__heading">
                <h2><?php echo $collection['collection_name'] ?></h2>    
                <h5><?php echo $collection['collection_description'] ?></h5>    
            </div>
            
			<div class="row <?php echo (count($collection['shops'])) > 3 ? " js-carousel" : ""; ?> " data-slides="4,3,3,1,1" data-infinite="false" data-arrows="false" data-slickdots="false">
                <?php foreach ($collection['shops'] as $shopId => $shop) { ?>
                    <div class="col-md-3">
                        <div class="shop">
                            <div class="shop__card">
                                <div class="shop__card--body">
                                    <div class="shop-media">
                                        <a href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>">
                                            <picture class="product-img" data-ratio="1:1">
                                            <source type="image/webp" srcset="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shopData']['shop_id'], $siteLangId, "SHOP_LAYOUT_2", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                                            <img data-aspect-ratio="1:1" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shopData']['shop_id'], $siteLangId, "SHOP_LAYOUT_2", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>">
                                            </picture>
                                        </a>
                                    </div>
                                </div>
                                <div class="shop__card--foot">
                                    <div class="shop-description">
                                        <a href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>">
                                            <h4 class="shop-name"><?php echo $shop['shopData']['shop_name']; ?></h4>
                                        </a>
                                        <p class="shop-location">
                                            <i class="icn icn-coll-location">
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#collection-location"></use>
                                                </svg>
                                            </i>
                                            <span><?php echo $shop['shopData']['state_name']; ?><?php echo ($shop['shopData']['country_name'] && $shop['shopData']['state_name']) ? ', ' : ''; ?><?php echo $shop['shopData']['country_name']; ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
				</div>
        </div>
    </section>
<?php } ?>