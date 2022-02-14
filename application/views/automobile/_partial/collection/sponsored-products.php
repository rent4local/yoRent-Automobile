<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (isset($collection['products']) && count($collection['products']) > 0) { ?>
<section class="section collection--product">
    <div class="container">
        <div class="section__heading">
			<h2><?php echo ($collection['collection_name'] != '') ? $collection['collection_name'] : ''; ?></h2>
			<h5><?php echo ($collection['collection_description'] != '') ? $collection['collection_description'] : ''; ?></h5>
		</div>
        <div class="product-wrapper js-carousel"  data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true" data-slickdots="flase">
		<?php 
		foreach ($collection['products'] as $product) { 
			include('product-layout-1-list.php'); 
		} ?>
		</div>
    </div>
</section>
<?php } ?>