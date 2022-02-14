<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php if (!empty($productsListing)) { ?>
    <ul class="list-tags">
        <?php
        foreach ($productsListing as $product) {
            $productItems = (isset($product['items'])) ? $product['items'] : [];
            ?>
            <?php if ($product['id'] > 0) { ?>
                <li><span><?php echo html_entity_decode($product['text']); ?> <i class="remove_buyTogether remove_param fa fa-times" onclick="deleteAttachedProduct(<?php echo $product['id']; ?>, <?php echo $addonProductId;?>);"></i></span>

                </li>
            <?php } ?> 
            <?php
            if (!empty($productItems)) {
                foreach ($productItems as $item) {
                    ?>
                    <li>
                        <span><?php echo html_entity_decode($product['text']); ?> - <?php echo html_entity_decode($item['text']); ?> <i class="remove_buyTogether remove_param fa fa-times" onclick="deleteAttachedProduct(<?php echo $item['id']; ?>);"></i></span> 
                    </li>

                <?php }
                ?>

        <?php } ?>   


    <?php } ?>
    </ul>        
<?php } ?>