<li class=" <?php echo isset($product['key']) ? md5($product['key']) : ''; ?> <?php echo (!$product['in_stock']) ? 'disabled' : ''; ?>">
	<div class="cell cell_product">
		<div class="product-profile">
			<div class="product-profile__thumbnail"> 
                <a href="<?php echo $productUrl; ?>">
                    <img class="img-fluid" data-ratio="3:4" src="<?php echo $imageUrl; ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                </a> 
            </div>
			<div class="product-profile__data">
				<div class="title">
					<a class="" href="<?php echo $productUrl; ?>">
						<?php echo $productTitle; ?>
					</a>
					<?php if ($product['uwlp_product_type'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
					<label class="badge badge-success">
						<?php echo Labels::getLabel('LBL_For_Rent', $siteLangId); ?>
					</label>
					<?php } else { ?>
						<label class="badge badge-info">
							<?php echo Labels::getLabel('LBL_For_Sale', $siteLangId); ?>
						</label>
					<?php } ?>
					
					
				</div>
				<?php if (isset($product['options']) && count($product['options'])) { ?>
				<div class="options">
					<p class="">
						<?php
							foreach ($product['options'] as $key => $option) {
								echo  (0 < $key) ? ' | ' : "" ;
								echo $option['option_name'] . ':';
							?> 
							<span class="text--dark"><?php echo $option['optionvalue_name']; ?></span>
							<?php
						}
						?>
					</p>
				</div>
				<?php  }?>
						
                        <?php
                        $moveToBagAction = 'moveToCart(' . $product['selprod_id'] . ', ' . $product['uwlp_uwlist_id'] . ', event, ' . Shipping::FULFILMENT_PICKUP . ')';
                        if ($product['uwlp_product_type'] == applicationConstants::PRODUCT_FOR_RENT) {
                            $moveToBagAction = 'quickDetail(' . $product['selprod_id'] . ', ' . $product['uwlp_uwlist_id'] . ', ' . Shipping::FULFILMENT_PICKUP . ');';
                        }
                        ?>
                        <button class="btn btn-outline-brand btn-sm product-profile__btn" type="button" onclick="<?php echo $moveToBagAction; ?>">
                            <?php echo Labels::getLabel('LBL_Move_To_Bag', $siteLangId); ?>
                        </button>
			</div>
		</div>
	</div>
	<div class="cell cell_price">
		<div class="product-price">
			<?php if ($product['uwlp_product_type'] == applicationConstants::PRODUCT_FOR_RENT) {
                echo CommonHelper::displayMoneyFormat($product['rent_price']);
            } else {
                echo CommonHelper::displayMoneyFormat($product['theprice']);
            } ?>
		</div>
	</div>
	<div class="cell cell_action">
		<ul class="actions">
			<li> 
                <a href="javascript:void(0)" onclick="removeFromWishlist(<?php echo $product['selprod_id']; ?>, <?php echo $product['uwlp_uwlist_id']; ?>, event)">
                    <svg class="svg" width="24px" height="24px" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?>">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove"></use>
                    </svg>
                </a> 
            </li>
		</ul>
	</div>
</li>