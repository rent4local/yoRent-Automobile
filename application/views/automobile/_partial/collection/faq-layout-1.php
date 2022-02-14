<?php
if (isset($collection['faqs']) && count($collection['faqs']) > 0) {
    $faqCategories = array();
    foreach ($collection['faqs'] as $faq) {
        $faqCategories[$faq['faqcat_id']]['faqcat_name'] = $faq['faqcat_name'];
        $faqCategories[$faq['faqcat_id']]['faqs'][$faq['faq_id']] = $faq;
    }
?>
    <section class="section collection--faq">
        <div class="container">
            <div class="section__heading">
                <h2><?php echo $collection['collection_name']; ?></h2>
                <h5><?php echo $collection['collection_description']; ?></h5>
            </div>
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="d-grid" data-view="3">
                        <?php foreach ($faqCategories as $faqCatId => $faqCat) { ?>
                            <a href="<?php echo UrlHelper::generateUrl('Custom', 'faq', array($faqCatId)); ?>" > 
                                <div class="faq-card">
                                    <div class="faq-media flex-center">
                                        <i class="icn">
                                            <svg class="svg" width="40" height="40">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#car-booking"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="faq-content">
                                        <h5><?php echo $faqCat['faqcat_name']; ?></h5>
                                        <span><?php echo count($faqCat['faqs']); ?> <?php echo Labels::getLabel('LBL_Questions', $siteLangId); ?></span>
                                    </div>
                                    <span class="arrow-right"></span>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>