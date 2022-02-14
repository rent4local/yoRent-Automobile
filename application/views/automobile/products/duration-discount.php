<?php if (!empty($durationDiscountRows)) { ?>
    <div class="price-seller">
        <div class="block-title">
            <?php echo Labels::getLabel('LBL_Duration_Discounts_for_rent', $siteLangId); ?>:
        </div>
        <ul class="list-bullet list-bullet-tick ">
            <?php foreach ($durationDiscountRows as $durationDiscountRow) { ?>
                <li id="dur_<?php echo $durationDiscountRow['produr_id'];?>" class="duration-list--js">
                    <?php echo ($durationDiscountRow['produr_rental_duration']) . ' ' . $rentalTypeArr[$durationDiscountRow['produr_duration_type']]; ?>
                    <?php echo Labels::getLabel('LBL_Or_more', $siteLangId); ?>
                    <span class="item__price">(<?php echo $durationDiscountRow['produr_discount_percent'] . '%'; ?>) </span>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>