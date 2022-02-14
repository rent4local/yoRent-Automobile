<?php if (isset($collection['cbs']) && count($collection['cbs']) > 0) { ?>
<section class="section collection--experience" style="background-color:rgba(var(--brand-color-alpha),0.2)" >
    <div class="container">
        <div class="row">
		<?php foreach ($collection['cbs'] as $block) { ?>
            <div class="col-md-3 col-6">
                <div class="experience">
                    <div class="experience__icon flex-center">
                        <i class="icn">
                            <img class="svg" src="<?php echo UrlHelper::generateFullUrl('Image', 'contentBlockIcon', array($block['cbs_id'], 'THUMB', 0, $siteLangId)); ?>" />
						</i>                        
                    </div>
                    <div class="experience__content">
                        <h5><?php echo $block['cbs_name']; ?></h5>
                        <span><?php echo html_entity_decode($block['cbslang_description']); ?></span>
                    </div>
                </div>
            </div>
		<?php } ?>
        </div>
    </div>
</section>
<?php } ?>