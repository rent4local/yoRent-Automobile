<?php if (isset($collection['cbs']) && count($collection['cbs']) > 0) { ?>
    <section class="section" id="cbs_layout_<?php echo $collection['collection_id'];?>">
    <div class="container">
        <div class="section__heading">
            <h2><?php echo $collection['collection_name']; ?></h2>
			<h5><?php echo $collection['collection_description']; ?></h5>
		</div>
        <div class="row">
		<?php foreach ($collection['cbs'] as $block) { ?>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img class="svg" src="<?php echo UrlHelper::generateFullUrl('Image', 'contentBlockIcon', array($block['cbs_id'], 'THUMB', 0, $siteLangId)); ?>" />
                        </div>
                    </div>
                    <div class="service__body">
                        <h5><?php echo $block['cbs_name']; ?></h5>
                        <p><?php echo html_entity_decode($block['cbslang_description']); ?></p>
                    </div>
                </div>
            </div>
		<?php } ?>	
        </div>
    </div>
</section>
<?php } ?>