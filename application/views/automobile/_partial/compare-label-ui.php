<?php
$rand = rand(00, 10000);
$id = 'compfor_' . $rand;
?>
<label class="add-compare" for="<?php echo $id;?>">
    <input id="<?php echo $id;?>" onclick="compare_products(this,<?php echo $product['selprod_id']; ?>)" class="checkbox-input compare_product_js_<?php echo $product['selprod_id']; ?> comp_product_cat_<?php echo $product['prodcat_id']; ?> compProductsJs" data-catid=<?php echo $product['prodcat_id']; ?> title="Compare Product" name="compare" value="1" type="checkbox" <?php if ($prodInCompList == 1) { echo 'checked="checked"'; } ?>>
    <svg class="svg add">
        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#add-compare">
        </use>
    </svg>
    <svg class="svg tick">
        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#added-compare">
        </use>
    </svg>
    <?php echo Labels::getLabel('LBL_Compare', $siteLangId); ?>
</label>