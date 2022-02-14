<div class="row">
    <?php
    $i = 0;
    foreach ($collection['shops'] as $shop) {
        ?>    
        <div class="col-lg-4 col-sm-4">
            <div class="shop__portlet">
                    <div class="shop--img">
                        <a href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>">
                            <img loading='lazy'
                            src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'shopLogo', array($shop['shopData']['shop_id'], $siteLangId, "THUMB3", 0, false), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                            alt="<?php echo $shop['shopData']['shop_name']; ?>">
                        </a>
                    </div>
                    <div class="shop--detail">
                        <a href="<?php echo (!isset($shop['shopData']['promotion_id']) ? UrlHelper::generateUrl('shops', 'view', array($shop['shopData']['shop_id'])) : UrlHelper::generateUrl('shops', 'track', array($shop['shopData']['promotion_record_id'], Promotion::REDIRECT_SHOP, $shop['shopData']['promotion_record_id']))); ?>">
                            <h5><?php echo $shop['shopData']['shop_name']; ?></h5>
                        </a>
                        <p>
                            <span>
                                <i class="icn icn-sm-location">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#sm-location" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#sm-location"></use>
                                    </svg>
                                </i>
                            </span>
                            <?php echo $shop['shopData']['country_name']; ?>
                        </p>
                    </div>
            </div>
        </div>

        <?php
        $i++;
        isset($shop['shopData']['promotion_id']) ? Promotion::updateImpressionData($shop['shopData']['promotion_id']) : '';
        
    }
    ?>
</div>