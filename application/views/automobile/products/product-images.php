<!-- Page Slider Start -->
<section class="section py-0">
    <div class="detail-gallery">
        <div class="js-carousel" data-slides="2,1,1,1,1" data-arrows="true">
            <?php if ($productImagesArr) { 
			foreach ($productImagesArr as $afile_id => $image) {
				$originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'DETAIL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
				$mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array($product['product_id'], 'DETAIL', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg');
				
			?>
			<div class="slide-item">
                <picture>
                    <source media="(min-width:767px)" srcset="<?php echo $originalImgUrl;?>"/>
                    <source media="(min-width:1024px)" srcset="<?php echo $originalImgUrl;?>"/>
                    <source srcset="<?php echo $originalImgUrl;?>"/>
                    <img src="<?php echo $mainImgUrl;?>" alt="slide"/>
                </picture>
            </div><!--item-->
			<?php } ?>
			<?php } else {  
			$mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'DETAIL', 0)), CONF_IMG_CACHE_TIME, '.jpg');
			$originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'product', array(0, 'DETAIL', 0)), CONF_IMG_CACHE_TIME, '.jpg');
			
			?>
            <div class="slide-item">
                <picture>
					<source media="(min-width:767px)" srcset="<?php echo $originalImgUrl;?>"/>
                    <source media="(min-width:1024px)" srcset="<?php echo $originalImgUrl;?>"/>
                    <source srcset="<?php echo $originalImgUrl;?>"/>
                    <img src="<?php echo $mainImgUrl;?>" alt="slide"/>
                </picture>
            </div><!--item-->
			<?php } ?> 
        </div>
    </div>      
</section>