<?php if (isset($collection['testimonials']) && count($collection['testimonials']) > 0) { ?>
    <section class="section collection-testimonial" id="testimonial_layout_2_<?php echo $collection['collection_id']; ?>">
        <div class="container">
            <div class="section__title text-center">
                <h5><?php echo $collection['collection_name']; ?></h5>
                <h2><?php echo $collection['collection_description']; ?></h2>
            </div>
            <div class="section__body">
                <div class="testimonial-wrapper">
                    <div class="testimonial-wrapper__left">
                        <div class="testimonail__media">
                            <img src="<?php echo UrlHelper::generateFullUrl('Image', 'collectionReal', array($collection['collection_id'], $siteLangId, 'testimonial')); ?>">
                        </div>
                    </div>

                    <div class="testimonial-wrapper__right">
                        <div class="testimonial-arrows js--testimonial-arrows">
                            <a href="javascript:void(0);" class="testimonial-arrow arrow-left"></a>
                            <a href="javascript:void(0);" class="testimonial-arrow arrow-right"></a>
                        </div>
                        <div class="testimonial js--testimonail">
                            <!--item-->
                            <?php foreach ($collection['testimonials'] as $testimonial) { ?>
                                <div class="testimonial__item">
                                    <div class="testimonail-content">                                      
                                        <?php echo CommonHelper::truncateCharacters($testimonial['testimonial_text'], 250, '', '', true); ?>
                                        <?php
                                        if (strlen($testimonial['testimonial_text']) > 150) {
                                            echo '...';
                                        }
                                        ?>
                                    </div>
                                    <div class="testimonail-avtar">
                                        <div class="testimonail-avtar__img">
                                            <img alt="<?php echo $testimonial['testimonial_user_name']; ?>" src="<?php echo UrlHelper::generateUrl('Image', 'testimonial', array($testimonial['testimonial_id'], $siteLangId, 'THUMB')) . '?t=' . time(); ?>">
                                        </div>
                                        <div class="testimonail-avtar__detail">
                                            <h5><?php echo $testimonial['testimonial_user_name']; ?></h5>
                                            <span><?php echo $testimonial['testimonial_author_city']; ?></span>
                                            <!-- <span>California</span> -->
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>


<script>
$('.js--testimonail').slick({
    dots: false,
    arrows: true,
    infinite: false,
    slidesToShow: 1,
    slidesToScroll: 1,
    appendArrows: '.testimonial-arrows',
    prevArrow: $('.arrow-left'),
    nextArrow: $('.arrow-right'),
    responsive: [
        {
        breakpoint: 1199,
        settings: {
            arrows: false,
            dots: true,
        }
        }
    ]
});
</script>

