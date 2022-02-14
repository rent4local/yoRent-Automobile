<?php if (!empty($volumeDiscountRows)) { ?>
    <div class="price-seller">
        <div class="block-title">
            <?php echo Labels::getLabel('LBL_Wholesale_Price_(Piece)', $siteLangId); ?>:
        </div>
        <ul class="list-bullet list-bullet-tick">
            <?php
            foreach ($volumeDiscountRows as $volumeDiscountRow) {
                $volumeDiscount = $product['theprice'] * ($volumeDiscountRow['voldiscount_percentage'] / 100);
                $price = ($product['theprice'] - $volumeDiscount);
            ?>
                <li id="volumne_<?php echo $volumeDiscountRow['voldiscount_min_qty'];?>" class="duration-list--js" data-qty = "<?php echo $volumeDiscountRow['voldiscount_min_qty'];?>">
                    <?php echo ($volumeDiscountRow['voldiscount_min_qty']); ?>
                    <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                    (<?php echo $volumeDiscountRow['voldiscount_percentage'] . '%'; ?>) <span class="item__price"><?php echo CommonHelper::displayMoneyFormat($price); ?>
                        /
                        <?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>