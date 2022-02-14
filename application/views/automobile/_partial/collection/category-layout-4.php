<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($collection['categories']) && count($collection['categories']) > 0) { ?>
<section class="section collection--category" id="prod_category_lay4_<?php echo $collection['collection_id']; ?>">
    <div class="container">
        <div class="section__heading">
            <h2><?php echo $collection['collection_name']; ?></h2> 
			<h5><?php echo $collection['collection_description']; ?></h5>
        </div>
        <div class="d-grid d-lg-down-flex" data-view="3">
		<?php 
			$index = 1;
			foreach ($collection['categories'] as $key => $category) {
                if ($index > $recordLimit) {
                    break;
                } 
				?>
			<div class="category">
                <div class="category__media">                   
					<a href="<?php echo UrlHelper::generateFullUrl('Category', 'view', array($key)); ?>">
						<img data-aspect-ratio="4:3" src="<?php echo UrlHelper::generateFullUrl('Image', 'CollectionCatTmage', array($collection['collection_id'], $index, 'ORIGINAL', 0, $siteLangId)); ?>">
					</a>
                </div>
                <div class="category__content">
                    <h5><?php echo $category['prodcat_name']; ?></h5> 
                </div>
                <a href="<?php echo UrlHelper::generateFullUrl('Category', 'view', array($key)); ?>"></a>
            </div>
			<?php 
            $index++;
        } ?>
		</div>
    </div>
</section>
<!-- Section Quality End -->
<?php } ?>