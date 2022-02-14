<?php if (isset($collection['cbs']) && count($collection['cbs']) > 0) { ?>
<section class="section">
    <div class="container">
        <div class="section__heading">
            <h2><?php echo $collection['collection_name']; ?></h2>
			<h5><?php echo $collection['collection_description']; ?></h5>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="work-media">
                    <img data-aspect-ratio="4:3" src="<?php echo UrlHelper::generateFullUrl('Image', 'collectionReal', array($collection['collection_id'], $siteLangId, 'ORIGINAL')); ?>">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="work-list">
                    <ul>
					<?php foreach ($collection['cbs'] as $block) { ?>
                        <li>
                            <h4><?php echo $block['cbs_name']; ?></h4>
                            <p><?php echo html_entity_decode($block['cbslang_description']); ?></p>
                        </li>
					<?php } ?>	
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<?php } ?>