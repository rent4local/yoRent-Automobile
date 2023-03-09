<?php 
if (isset($collection['testimonials']) && count($collection['testimonials']) > 0) { ?>
    <section class="section collection--testimonial" style="background-color:rgba(var(--brand-color-alpha),0.2)">
    <div class="container">
        <div class="section__heading">
            <h2><?php echo $collection['collection_name']; ?></h2>
            <h5><?php echo $collection['collection_description']; ?></h5>
		</div>
        <div class="testimonial-wrapper js--single">
		<?php foreach ($collection['testimonials'] as $testimonial) {?>
            <div class="testimonial">
                <div class="testimonial__quotes">
                    <img src="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/quotes-test.svg">
                </div>
                <div class="testimonial__body">
					<?php 
					echo $testimonial['testimonial_title'] .'<br />';
					echo CommonHelper::truncateCharacters($testimonial['testimonial_text'], 250, '', '', true);  
					echo (strlen($testimonial['testimonial_text']) > 150) ? '...' : "";
					?>
                </div>
                <div class="testimonial__foot">
                    <div class="user">
                        <div class="user__media">
                            <img src="<?php echo CommonHelper::generateUrl('image', 'testimonial', array($testimonial['testimonial_id'], $siteLangId, 'THUMB')); ?>" />
                        </div>
                        <div class="user__detail">
                            <h5><?php echo $testimonial['testimonial_user_name']; ?></h5>
                            <span><?php echo $testimonial['testimonial_author_city']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
        </div>
        <div class="flex-center mt-btn">
            <a href="<?php echo UrlHelper::generateUrl('testimonials');?>"class="btn btn-outline-brand btn-round arrow-right"><?php echo Labels::getLabel('LBL_VIEW_ALL', $siteLangId); ?></a>
        </div>
    </div>
</section>
<?php } ?>
<script>
$('.js--single').slick({
  dots: true,
  arrows: false,
  infinite: false,
  speed: 300,
  slidesToShow: 2,
  slidesToScroll: 2,
  centerMode: false,
        responsive: [
            {
            breakpoint: 1200,
            settings: {
            slidesToShow: 2,
          }
     },
     {
            breakpoint: 800,
            settings: {
            slidesToShow: 1,
          }
     },
    ]
});
</script>