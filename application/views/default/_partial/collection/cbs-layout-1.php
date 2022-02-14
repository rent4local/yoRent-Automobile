<?php if (isset($collection['cbs']) && count($collection['cbs']) > 0) { ?>
    <section class="section collection-cms">
        <div class="container">
            <div class="section__head">
                <div class="section__title  text-center">
                    <h5><?php echo $collection['collection_description']; ?></h5>
                    <h2><?php echo $collection['collection_name']; ?></h2>
                </div>
            </div>
            <div class="section__body">
                <div class="row">
                    <?php foreach ($collection['cbs'] as $block) { ?>
                        <div class="col-lg-3 col-6">
                            <div class="flow-card">
                                <div class="flow-card__head">

                                    <div class="flow-icon">
                                        <i class="icn icn-location_flow">
                                            <img class="svg" alt="<?php echo $block['cbs_name'];?>" src="<?php echo UrlHelper::generateFullUrl('Image', 'contentBlockIcon', array($block['cbs_id'], 'THUMB', 0, $siteLangId)); ?>" />
                                        </i>
                                    </div>
                                </div>
                                <div class="flow-card__body">
                                    <div class="title">
                                        <h5><?php echo $block['cbs_name']; ?></h5>
                                        <p class="description-limit"><?php echo html_entity_decode($block['cbslang_description']); ?> </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
<?php } ?>