<?php $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId); ?>
<?php if (isset($searchProductType) && $searchProductType == applicationConstants::PRODUCT_FOR_SALE && $product['is_sell'] && ALLOW_SALE) { ?>
    <div class="product-prices">
        <div class="product-prices-per-day">
            <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
            <?php if ($product['special_price_found'] && $product['selprod_price'] > $product['theprice']) { ?>
                <span class="slash">|</span>
                <del class="product-prices-old"> <?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></del>
            
                <span class="slash">|</span>
                <span class="product-prices-off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
            <?php } ?>
        </div>
    </div>
<?php } elseif($product['is_rent']) { ?>
    <div class="product-prices">
        <div class="product-prices-per-day">
            <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?> 
            <span>/ <?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?></span>
    
            <?php if ($product['special_price_found'] && $product['rent_price'] > $product['theprice']) { ?>
                <span class="slash">|</span>
                <del class="product-prices-old"> <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?></del>
            
                <span class="slash">|</span>
                <span class="product-prices-off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
            <?php } ?>
        </div>
    </div>
<?php } ?>