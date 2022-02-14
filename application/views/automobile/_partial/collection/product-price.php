<?php $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId); ?>
<div class="product__body--foot">
    <?php if (isset($searchProductType) && $searchProductType == applicationConstants::PRODUCT_FOR_SALE && $product['is_sell'] && ALLOW_SALE) { ?>
        <div class="product--price"> 
            <span class="bold">
                <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
            </span> 
            <?php if ($product['special_price_found'] && $product['selprod_price'] > $product['theprice']) { ?>
                <span class="slash">|</span>
                <del class="product-prices-old"> <?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></del>
            <?php } ?>
        </div>
        <a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]); ?>" class="btn btn-white btn-round"><?php echo Labels::getLabel('LBL_BUY_NOW', $siteLangId); ?></a>
    <?php } else { ?>
        <div class="product--price"> 
            <span class="bold">
                <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?> 
            </span> 
            <span> 
                <span class="slash-diagonal">/</span> 
                <?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?>
            </span>
            <?php if ($product['special_price_found'] && $product['rent_price'] > $product['theprice']) { ?>
                <span class="slash">|</span>
                <del class="product-prices-old"> <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?></del>
            <?php } ?>
            <?php if (isset($product['availableForPickup']) && 0) { ?>
                <p>Distance : <?php echo round($product['distance'], 2) . ' Miles Approx'; ?></p> 
            <?php } ?>
        </div>
        <a href="<?php echo UrlHelper::generateUrl('products', 'view', [$product['selprod_id']]); ?>" class="btn btn-white btn-round"><?php echo Labels::getLabel('LBL_RENT_NOW', $siteLangId); ?></a>
    <?php } ?>    
</div>
