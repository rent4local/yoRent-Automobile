<div class="product-prices">
<?php $rentalTypeArr = applicationConstants::rentalTypeArr($siteLangId); ?>
    <?php if (ALLOW_RENT > 0 && $product['is_rent'] > 0 && $product['rent_price'] > 0) { ?>
        <div class="product-prices-per-day">
            <?php echo CommonHelper::displayMoneyFormat($product['rent_price']); ?>
            <span class="slash">/ <?php echo $rentalTypeArr[$product['sprodata_duration_type']]; ?></span>
        </div>
    <?php
    }
    ?>
    <?php if (ALLOW_SALE > 0 && $product['is_sell'] > 0) { ?>
        <div class="product-prices-per-day">
            <?php echo CommonHelper::displayMoneyFormat($product['theprice']); ?>
            <span class="slash"> <?php echo Labels::getLabel('LBL_Retail', $siteLangId); ?>
            </span>
            <?php if ($product['special_price_found']) { ?>
                <del class="product-prices-old"> <?php echo CommonHelper::displayMoneyFormat($product['selprod_price']); ?></del>

            <?php } ?>
            <?php /* if($product['selprod_sold_count']>0){?>
          <span class="products__price_sold"><?php echo $product['selprod_sold_count'];?> <?php echo Labels::getLabel('LBL_Sold',$siteLangId);?></span>
          <?php } */ ?>
            </h4>
            <?php if ($product['special_price_found']) { ?>
                <span class="product-prices-off"><?php echo CommonHelper::showProductDiscountedText($product, $siteLangId); ?></span>
            <?php } ?>
			 <span class="slash">/ <?php echo Labels::getLabel('LBL_To_buy',$siteLangId);?></span>
        </div>
    <?php } ?>
</div>