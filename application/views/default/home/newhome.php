<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<script>
events.viewContent();
</script>

<div id="body" class="body" role="main"> 

<!--Slider start here-->

<section class="section collection--hero p-0">
    <div class="hero-slider js--hero-slider">
        <div class="hero-slider__item">
            <a href="" class="hero_media">
                <picture>
                    <source media="(min-width:767px)" srcset="/images/slide_1.png">
                    <source media="(min-width:1024px)" srcset="/images/slide_1.png">
                    <source srcset="/images/slide_1.png">
                    <img src="/images/slide_1.png" alt="slide">
                </picture>
            </a>
        </div>
        <div class="hero-slider__item">
            <a href="" class="hero_media">
                <picture>
                    <source media="(min-width:767px)" srcset="/images/slide_1.png">
                    <source media="(min-width:1024px)" srcset="/images/slide_1.png">
                    <source srcset="/images/slide_1.png">
                    <img src="/images/slide_1.png" alt="slide">
                </picture>
            </a>
        </div>
    </div>       
    <div class="search__form">
            <div class="search__form-wrapper bg-white">
                <div class="search-head">
                    <h1 class="search__form-heading">Looking to save more on your <span>Rental Dress?</span></h1>
                    <p class="pb-3">Unlock your dream closet, and never look back.</p>
                </div>
                <div class="search-body">
                    
                <form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">Search any product</label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <input placeholder="Bodycon Dress" data-field-caption="Name" data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">LOCATION</label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <input placeholder="India" data-field-caption="Name" data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set d-flex">
                                <div class="field-left">
                                    <div class="caption-wraper">
                                        <label class="field_label">Pickup</label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input placeholder="Add Date" data-field-caption="Name" data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="field-right">
                                    <div class="caption-wraper">
                                        <label class="field_label">Return</label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <input placeholder="Add Date" data-field-caption="Name" data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-action">
                        <button type="button" class="btn btn-brand btn-search btn-wide-full">
                            <i class="icn icn-maginifier">
                                <svg class="svg"> 
                                    <use xlink:href="'.CONF_WEBROOT_URL.'images/retina/sprite.svg#maginifier" href="'.CONF_WEBROOT_URL.'images/retina/sprite.svg#maginifier"></use>
                                </svg>
                            </i>    
                        Search</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
</section>

<!--Slider end here-->




<!-- Section flow start -->

<section class="section collection-cms">
    <div class="container">
        <div class="section__head">
            <div class="section__title  text-center">
                <h5>Follow steps to rent</h5>
                <h2>Our Rent Flow</h2>
            </div>
        </div>
        <div class="section__body">
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="flow-card">
                        <div class="flow-card__head">
                            <div class="flow-icon">
                                <i class="icn icn-location_flow">
                                    <svg class="svg"> 
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#location_flow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#location_flow"></use>
                                    </svg>
                                </i>
                            </div>
                        </div>
                        <div class="flow-card__body">
                            <div class="title">
                                <h5>Choose Location</h5>
                                <p>Find out anything you required and choose location</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="flow-card">
                        <div class="flow-card__head">
                            <div class="flow-icon">
                                <i class="icn icn-calendar_flow"> 
                                    <svg class="svg"> 
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#calendar_flow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#calendar_flow"></use>
                                    </svg>
                                </i>
                            </div>
                        </div>
                        <div class="flow-card__body">
                            <div class="title">
                                <h5>Pick Dates</h5>
                                <p>Pick the departure and return dates for your journey.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="flow-card">
                        <div class="flow-card__head">
                            <div class="flow-icon">
                                <i class="icn icn-search_flow">
                                    <svg class="svg"> 
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#search_flow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#search_flow"></use>
                                    </svg>
                                </i>
                            </div>
                        </div>
                        <div class="flow-card__body">
                            <div class="title">
                                <h5>Find out Dress</h5>
                                <p>Find out anything which suits your style & required</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="flow-card">
                        <div class="flow-card__head">
                            <div class="flow-icon">
                                <i class="icn icn-pay_flow">
                                    <svg class="svg"> 
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pay_flow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#pay_flow"></use>
                                    </svg>
                                </i>
                            </div>
                        </div>
                        <div class="flow-card__body">
                            <div class="title">
                                <h5>Payment</h5>
                                <p>Finish payment step to confirm your order</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section flow end -->


<!-- Section Latest Start -->

<section class="section scoop">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xl-3 col-lg-12 col-md-12">
                <div class="text__lg--center">
                    <h5>Best services from top rated</h5>
                    <h2>The Latest Scoop</h2>
                    <p class="title-detail">Etiam a ex ut ipsum suscipit scelerisque quis ac lorem. Interdum et malesuada fames ac ante ipsum primis in faucibus. Suspendisse consectetur non leo at eleifend.</p>
                    <div class="btn-layout"><button class="btn btn-brand ">View More</button></div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-12 col-md-12">
                <div class="latest_links">
                    <ul class="js-tabs">
                        <li>
                            <a href="#tab1" class="current"><span>New Arrivals</span></a>
                        </li>
                        <li>
                            <a href="#tab2"><span>Best Sellers</span></a>
                        </li>
                        <li>
                            <a href="#tab3"><span>Most Popular</span></a>
                        </li>
                    </ul>
                </div>
                <div class="tabs-container">
                    <div class="tab_content visible" id="tab1">
                        <div class="product">
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_1.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Love, Orange by Whitney Port</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_2.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Navy Blue Printed Fit and Fla...</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_3.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Black Solid Basic</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab_content" id="tab2">
                        <div class="product">
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_2.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Love, Orange by Whitney Port</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_2.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Navy Blue Printed Fit and Fla...</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_2.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Black Solid Basic</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab_content" id="tab3">
                        <div class="product">
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_3.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Love, Orange by Whitney Port</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_3.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Navy Blue Printed Fit and Fla...</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                            <div class="product_card card-with-hover">
                                <div class="product_card-body">
                                    <div class="product_media">
                                        <a href="#"><img src="/images/product_3.png"></a>
                                    </div>
                                </div>
                                <div class="product_card-footer">
                                    <p class="product_name">Rust Sweater Dress</p>
                                    <a href="#" class="product_title">Women Black Solid Basic</a>                                    
                                    <h4 class="product_price">$199 <span>/ day</span></h4>
                                    <div class="card--action btn-layout">
                                        <button class="btn btn-brand " tabindex="0">Rent Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Latest End -->


<!-- Section big_ad start -->

<section class="section">
    <div class="container">
        <div class="ads-wrapper">
            <div class="ads-wrapper__banner">
                <div class="banner-img">
                    <img src="/images/big-ad.png">
                </div>
            </div>
            <div class="ads-wrapper__content">
                <div class="content-wrapp">
                    <h2>Quality is our Habit</h2>
                    <p>Every outfit we onboard goes through a rigorous screening process to make sure that you get nothing but the best craftsmanship from the most innovative</p>
                    <div class="btn-layout"><button class="btn btn-outline-white btn-theme ">Rent Now</button></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section big_ad end -->



<!-- Section Seller Slider Start -->

<section class="section seller-slider">
    <div class="container container--narrow">
        <div class="section__title  text-center">
            <h5>Best services from top rated</h5>
            <h2>Best Seller this week</h2>
        </div>
        <div class="product product-slide product-slider-js with-arrows">
            <div>
                <div class="product_card card_slide">
                    <div class="product_card-body">
                        <div class="product_media">
                            <a href="#"><img src="/images/product_slide_1.png"></a>
                        </div>
                    </div>
                    <div class="product_card-footer product_slide_foot border-radius-all">
                        <p class="product_name">Rust Sweater Dress</p>
                        <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                        <h4 class="product_price">$199 <span>/ day</span></h4>
                    </div>
                </div>
            </div>            
            <div>
                <div class="product_card card_slide">
                    <div class="product_card-body">
                        <div class="product_media">
                            <a href="#"><img src="/images/product_slide_2.png"></a>
                        </div>
                    </div>
                    <div class="product_card-footer product_slide_foot border-radius-all">
                        <p class="product_name">Rust Sweater Dress</p>
                        <a href="#" class="product_title">Pink Wrapper Dress Maxi</a>                     
                        <h4 class="product_price">$199 <span>/ day</span></h4>
                    </div>
                </div>
            </div>
            <div>
                <div class="product_card card_slide">
                    <div class="product_card-body">
                        <div class="product_media">
                            <a href="#"><img src="/images/product_slide_3.png"></a>
                        </div>
                    </div>
                    <div class="product_card-footer product_slide_foot border-radius-all">
                        <p class="product_name">Rust Sweater Dress</p>
                        <a href="#" class="product_title">Black Mock Mini Dress</a>                        
                        <h4 class="product_price">$199 <span>/ day</span></h4>
                    </div>
                </div>
            </div>
            <div>
                <div class="product_card card_slide">
                    <div class="product_card-body">
                        <div class="product_media">
                            <a href="#"><img src="/images/product_slide_2.png"></a>
                        </div>
                    </div>
                    <div class="product_card-footer product_slide_foot border-radius-all">
                        <p class="product_name">Rust Sweater Dress</p>
                        <a href="#" class="product_title">Pink Wrapper Dress Maxi</a>                        
                        <h4 class="product_price">$199 <span>/ day</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Seller Slider End -->

<!-- Section gallery start -->

<section class="section">
    <div class="container container--fluid">
        <div class="section__title  text-center">
            <h5>Be any version of yourself</h5>
            <h2>Derisk You Style Experiment</h2>
        </div>
        <div class="gallery_wrapper">
            <div class="grid-post grid-wide">
                <div class="grid-wide-media">
                    <a href="#"><img src="/images/grid-wide-1.png"></a> 
                </div>
                <div class="grid-content">
                    Sunset to sunrise
                </div>
            </div>
            <div class="grid-post grid-sm">
                <div class="grid-sm-media">
                <a href="#"><img src="/images/grid-sm-1.png"></a>
                </div>
                <div class="grid-content">
                Fashion on the rocks
                </div>
            </div>
            <div class="grid-post grid-height">
                <div class="grid-lg-media">
                <a href="#"><img src="/images/grid-lg.png"></a>
                </div>
                <div class="grid-content">
                Fashion on the rocks
                </div>
            </div>
            <div class="grid-post grid-sm-left">
                <div class="grid-sm-media">
                <a href="#"><img src="/images/grid-sm-1.png"></a>
                </div>
                <div class="grid-content">
                    Sunset to sunrise
                </div>
            </div>
            <div class="grid-post grid-wide-center">
                <div class="grid-wide-media">
                <a href="#"><img src="/images/grid-wide-2.png"></a>
                </div>
                <div class="grid-content">
                Wedding bells
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section gallery End -->


<!-- Section Explore Start -->

<section class="section section--explore">
    <div class="container">
        <div class="section__title  text-center">
            <h5>Best services from top rated</h5>
            <h2>Explore our top Deals</h2>
        </div>
        <div class="explore--category">
            <ul class="js-tabs explore-links">
                <li>
                    <a href="#tab-1"  class="current">
                        <i class="icn icn-work">
                            <svg class="svg"> 
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#work" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#work"></use>
                            </svg>
                        </i>
                        <p>Work</p>
                    </a>
                </li>
                <li>
                    <a href="#tab-2">
                        <i class="icn icn-ring">
                            <svg class="svg"> 
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#ring" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#ring"></use>
                            </svg>
                        </i>
                        <p>Wedding</p>
                    </a>
                </li>
                <li>
                    <a href="#tab-3">
                        <i class="icn icn-heels">
                            <svg class="svg"> 
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heels" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#heels"></use>
                            </svg>
                        </i>
                        <p>Night out</p>
                    </a>
                </li>
                <li>
                    <a href="#tab-4">
                        <i class="icn icn-wine">
                            <svg class="svg"> 
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wine" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wine"></use>
                            </svg>
                        </i>
                        <p>Party</p>
                    </a>
                </li>
                <li>
                    <a href="#tab-5">
                        <i class="icn icn-wine">
                            <svg class="svg"> 
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wine" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#wine"></use>
                            </svg>
                        </i>
                        <p>Traveling</p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="container">
          <div id="tab-1" class="tab_content visible">
            <div  class="product explore--slider-js">
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_2.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Women Navy Blue Printed Fit and Fla...</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_3.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Women Black Solid Basic</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Women Black Solid Basic</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_2.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Women Black Solid Basic</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="tab-2" class="tab_content">
            <div  class="product explore--slider-js">
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="tab-3" class="tab_content">
            <div  class="product explore--slider-js">
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>              
            </div>
        </div>
        <div id="tab-4" class="tab_content">
            <div  class="product explore--slider-js">
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>              
            </div>
        </div>
        <div id="tab-5" class="tab_content">
            <div  class="product explore--slider-js">
                <div>
                    <div class="product_card card-with-hover">
                        <div class="product_card-body">
                            <div class="product_media">
                                <a href="#"><img src="/images/product_1.png"></a>
                            </div>
                        </div>
                        <div class="product_card-footer">
                        <p class="product_name">Rust Sweater Dress</p>
                            <a href="#" class="product_title">Love, Orange by Whitney Port</a>                        
                            <h4 class="product_price">$199 <span>/ day</span></h4>
                            <div class="card--action btn-layout">
                                <button class="btn btn-brand ">Rent Now</button>
                            </div>
                        </div>
                    </div>
                </div>              
            </div>
        </div>
    </div>
</section>

<!-- Section Explore End -->

<!-- Section Membership Start -->

<section class="section section--membership">
    <div class="container">
        <div class="section__title  text-center">
            <h5>Try your first month for just $49</h5>
            <h2>Membership Perks</h2>
        </div>
        <div class="row membership_wrapper justify-content-between">
            <div class="col-xl-4 col-lg-4 col-md-12">
                <label class="membership_label">
                <input type="checkbox" name="member" class="member-check">
                    <div class="membership_card">
                        <div class="membership_card-head">
                            <h5>7 Item per month</h5>
                            <h2>$59/Month</h2>
                        </div>
                        <div class="membership_card-body">
                            <ul>
                                <li>Rent 7 items per case</li>
                                <li>1 case per month</li>
                                <li>Free shipping both ways</li>
                                <li>$1,000+ of clothing a month</li>
                                <li>$1,750+ of clothing a month</li>
                            </ul>
                            <div class="membership_price">
                                <div>
                                    <h3>$59 trial month</h3>
                                    <p>4 items / Per 30 Days</p>
                                </div>
                                <i class="checkbox-custom-fill"></i>  
                            </div>
                            <div class="btn-layout">
                                <button class="btn btn-grey">Try It Now</button>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12">
                <label class="membership_label">
                <input type="checkbox" name="member-check" class="member-check" checked>
                    <div class="membership_card">
                        <div class="membership_card-head">
                            <h5>4 Item per month</h5>
                            <h2>$40/Month</h2>
                        </div>
                        <div class="membership_card-body">
                            <ul>
                                <li>Rent 7 items per case</li>
                                <li>1 case per month</li>
                                <li>Free shipping both ways</li>
                                <li>$1,000+ of clothing a month</li>
                                <li>$1,750+ of clothing a month</li>
                            </ul>
                            <div class="membership_price">
                                <div>
                                    <h3>$49 trial month</h3>
                                    <p>4 items / Per 30 Days</p>
                                </div>
                                <i class="checkbox-custom-fill"></i>  
                            </div>
                            <div class="btn-layout">
                                <button class="btn btn-grey">Try It Now</button>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12">
                <label class="membership_label">
                <input type="checkbox" name="-check" class="member-check">
                    <div class="membership_card">
                        <div class="membership_card-head">
                            <h5>Unlimited</h5>
                            <h2>$99/ First 2 Months</h2>
                        </div>
                        <div class="membership_card-body">
                            <ul>
                                <li>Rent 7 items per case</li>
                                <li>1 case per month</li>
                                <li>Free shipping both ways</li>
                                <li>$1,000+ of clothing a month</li>
                                <li>$1,750+ of clothing a month</li>
                            </ul>
                            <div class="membership_price">
                                <div>
                                    <h3>$249/month after</h3>
                                    <p>4 items / Per 30 Days</p>
                                </div>
                                <i class="checkbox-custom-fill"></i>  
                            </div>
                            <div class="btn-layout">
                                <button class="btn btn-grey">Try It Now</button>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
</section>

<!-- Section Membership End -->


<!-- Section Testimonial Start -->

<section class="section section--testimonial">
    <div class="container">
        <div class="section__title text-center">
            <h5>Our Testimonial</h5>
            <h2>People Say The Nicest Things</h2>
        </div>
        <div class="section__body">
            <div class="testimonial-wrapper">
                    <div class="testimonial-wrapper__left">
                        <div class="testimonail__media">
                            <img src="/images/testimonial.png">
                        </div>
                    </div>
                    <div class="testimonial-wrapper__right">
                        <div class="testimonial-arrows js--testimonial-arrows">
                            <a href="#" class="testimonial-arrow arrow-left"></a>
                            <a href="#" class="testimonial-arrow arrow-right"></a>
                        </div>
                        <div class="testimonial js--testimonail">
                            <!--item-->   
                            <div class="testimonial__item">
                                <div class="testimonail-content">                                      
                                    I love this dress. It is much classier than I expected. The beauty is in the detail and the quality of fabric. And it is so comfortable! Really a great winter dress. I would buy it if it were less expensive. Also it was a tad loose in the waist but not unflattering.
                                </div>
                                <div class="testimonail-avtar">
                                    <div class="testimonail-avtar__img">
                                        <img src="/images/testimonial-avtar.png">
                                    </div>
                                    <div class="testimonail-avtar__detail">
                                        <h5>Ronny Sharma</h5>
                                        <span>California</span>
                                    </div>
                                </div>
                           </div>
                           <!--item-->
                           <div class="testimonial__item">
                                <div class="testimonail-content">                                      
                                    I love this dress. It is much classier than I expected. The beauty is in the detail and the quality of fabric. And it is so comfortable! Really a great winter dress. I would buy it if it were less expensive. Also it was a tad loose in the waist but not unflattering.
                                </div>
                                <div class="testimonail-avtar">
                                    <div class="testimonail-avtar__img">
                                        <img src="/images/testimonial-avtar.png">
                                    </div>
                                    <div class="testimonail-avtar__detail">
                                        <h5>Ronny Sharma</h5>
                                        <span>California</span>
                                    </div>
                                </div>
                           </div>
                           <!--item-->   
                           <div class="testimonial__item">
                                <div class="testimonail-content">                                      
                                    I love this dress. It is much classier than I expected. The beauty is in the detail and the quality of fabric. And it is so comfortable! Really a great winter dress. I would buy it if it were less expensive. Also it was a tad loose in the waist but not unflattering.
                                </div>
                                <div class="testimonail-avtar">
                                    <div class="testimonail-avtar__img">
                                        <img src="/images/testimonial-avtar.png">
                                    </div>
                                    <div class="testimonail-avtar__detail">
                                        <h5>Ronny Sharma</h5>
                                        <span>California</span>
                                    </div>
                                </div>
                           </div>
                           <!--item-->
                        </div>
                    </div>
            </div>
        </div>
    </div>
</section>



<!-- Section Testimonial End -->


<!-- Section FAQ Start -->

<section class="section section-faq">
    <div class="container container--narrow">
        <div class="section__title  text-center">
            <h5>Ask what you need</h5>
            <h2>Have any Questions?</h2>
        </div>
        <div class="faqTabs--flat-js">
        <ul class="tabs--faq">
                <li class="is-active"><a href="#tb-1">General</a></li>
                <li><a href="#tb-2">Service</a></li>
                <li><a href="#tb-3">Payment</a></li>
                <li><a href="#tb-4">Shop</a></li>
                <li><a href="#tb-5">REFUND</a></li>
            </ul>
        </div>
        <div id="tb-1" class="faq-wrapper tabs-content-home--js">
            <div class="faq-component js-group is-active">
                <h4 class="faq-title js-group-head">Are your garment bags cleaned after each use?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">What’s the best way to shop for maternity clothes on RTR?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Does Rent the Runway rent wedding gowns?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Why might my review be rejected?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
        </div>
        <div id="tb-2" class="faq-wrapper tabs-content-home--js">
            <div class="faq-component js-group is-active">
                <h4 class="faq-title js-group-head">Are your garment bags cleaned after each use?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">What’s the best way to shop for maternity clothes on RTR?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Does Rent the Runway rent wedding gowns?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Why might my review be rejected?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
        </div>
        <div id="tb-3" class="faq-wrapper tabs-content-home--js">
            <div class="faq-component js-group is-active">
                <h4 class="faq-title js-group-head">Are your garment bags cleaned after each use?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">What’s the best way to shop for maternity clothes on RTR?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Does Rent the Runway rent wedding gowns?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Why might my review be rejected?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
        </div>
        <div id="tb-4" class="faq-wrapper tabs-content-home--js">
            <div class="faq-component js-group is-active">
                <h4 class="faq-title js-group-head">Are your garment bags cleaned after each use?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">What’s the best way to shop for maternity clothes on RTR?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Does Rent the Runway rent wedding gowns?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Why might my review be rejected?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
        </div>
        <div id="tb-5" class="faq-wrapper tabs-content-home--js">
            <div class="faq-component js-group is-active">
                <h4 class="faq-title js-group-head">Are your garment bags cleaned after each use?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">What’s the best way to shop for maternity clothes on RTR?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Does Rent the Runway rent wedding gowns?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
            <div class="faq-component js-group">
                <h4 class="faq-title js-group-head">Why might my review be rejected?</h4>
                <div class="faq-component-wrapp faq-content js-group-body">
                    <p>Of course! we totally understand that you often have 2 occasions in one weekend, or that you might be getting multiple items to go on holiday. You can also rent items for different delivery dates in one order. You will have to pay the £3 rental fee for each delivery date. Please note, that due to demand, unless it is your first order, we are only able to offer rental credit on one unworn item per order. If it is your first order you are eligible for a refund up to 2 items.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section FAQ End -->

<!-- Section ad_2 start -->

<section class="section section--ads">
    <div class="container">
        <div class="ad__media">
            <div class="ad__media--left">
                <a href="#"><img src="/images/ad_left.png"></a>
            </div>
            <div class="ad__media--right">
            <a href="#"><img src="/images/ad_right.png"></a>
            </div>
        </div>
    </div>
</section>

<!-- Section ad_2 end -->

<!-- Section blog Post Start -->

<section class="section  section--blog">
    <div class="container">
        <div class="section__title  text-center">
            <h5>Stay punchy with these latest arrivals.</h5>
            <h2>Our Latest Blog posts</h2>
        </div>
    </div>
    <div class="container container--narrow">
        <div class="row blog-wrapper">
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="blog__card">
                    <div class="blog__card--body">
                        <span class="date-vertical">
                            January 22, 2021
                        </span>
                        <div class="blog-media">
                            <a href="#"><img src="/images/blog_1.png"></a>
                        </div>
                    </div>
                    <div class="blog__card--footer">
                        <a href="#"><h3 class="blog-title">Suit Up This Christmas With VienSo</h3></a>
                        <p class="blog-detail">Sometimes (all the time) we like to wear the pants. Luckily for you and I we have partnered with VienSo.</p>
                        <a href="#" class="link-more mt-3">Read more</a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="blog__card">
                    <div class="blog__card--body">
                        <span class="date-vertical">
                            January 23, 2021
                        </span>
                        <div class="blog-media">
                            <a href="#"><img src="/images/blog_2.png"></a>
                        </div>
                    </div>
                    <div class="blog__card--footer">
                        <a href="#"><h3 class="blog-title">17 pairs of the best designer rings</h3></a>
                        <p class="blog-detail">Sometimes (all the time) we like to wear the pants. Luckily for you and I we have partnered with VienSo.</p>
                        <a href="#" class="link-more mt-3">Read more</a>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="blog__card">
                    <div class="blog__card--body">
                        <span class="date-vertical">
                            January 24, 2021
                        </span>
                        <div class="blog-media">
                            <a href="#"><img src="/images/blog_1.png"></a>
                        </div>
                    </div>
                    <div class="blog__card--footer">
                        <a href="#"><h3 class="blog-title">Suit Up This Christmas With VienSo</h3></a>
                        <p class="blog-detail">Sometimes (all the time) we like to wear the pants. Luckily for you and I we have partnered with VienSo.</p>
                        <a href="#" class="link-more mt-3">Read more</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section blog Post End -->


<!-- Section Brand start -->

<section class="section section--brand">
    <div class="container">
        <div class="section__title  text-center">
            <h5>Best services from top rated</h5>
            <h2>Explore by Brands</h2>
        </div>
    </div>
    <div class="container">
        <div class="product explore--slider-js">
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_1.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-1.png"></a>
                    </div>
                </div>
            </div>
            </div>
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_2.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-4.png"></a>
                    </div>
                </div>
            </div>
            </div>
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_3.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-3.png"></a>
                    </div>
                </div>
            </div>
            </div>
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_4.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-4.png"></a>
                    </div>
                </div>
            </div>
            </div>
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_5.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-1.png"></a>
                    </div>
                </div>
            </div>
            </div>
            <div>
            <div class="product_card card-bg-grey">
                <div class="product_card-body">
                    <div class="product_media">
                    <a href="#"><img src="/images/brand_1.png"></a>
                    </div>
                </div>
                <div class="product_card-footer-brand">
                    <div class="brand-media">
                    <a href="#"><img src="/images/brand-name-1.png"></a>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Brand end -->

<!-- Section Shop shop Start -->

<section class="section shop--shop">
    <div class="container container--narrow">
        <div class="section__title  text-center">
            <h5>Best services from top rated</h5>
            <h2>Shop shops</h2>
        </div>
        <div class="row shop-wrapper product-slider-js">
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="shop__card">
                    <div class="shop__card--body">
                        <div class="shop-media">
                            <a href="#"><img src="/images/shop_1.png"></a>
                        </div>
                    </div>
                    <div class="shop__card--foot">
                        <div class="shop-description">
                            <a href="#"><h4 class="shop-name">Balle Vintage</h4></a>
                            <p class="shop-location">
                                <i class="icn icn-coll-location">
                                <svg class="svg"> 
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location"></use>
                                </svg>
                                </i>
                                <span>California, United States</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
                
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="shop__card">
                    <div class="shop__card--body">
                        <div class="shop-media">
                            <a href="#"><img src="/images/shop_2.png"></a>
                        </div>
                    </div>
                    <div class="shop__card--foot">
                        <div class="shop-description">
                            <a href="#"><h4 class="shop-name">Fashion Gyrl</h4></a>
                            <p class="shop-location">
                                <i class="icn icn-coll-location">
                                <svg class="svg"> 
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location"></use>
                                </svg>
                                </i>
                                <span>California, United States</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
                
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="shop__card">
                    <div class="shop__card--body">
                        <div class="shop-media">
                        <a href="#"><img src="/images/shop_3.png"></a>
                        </div>
                    </div>
                    <div class="shop__card--foot">
                        <div class="shop-description">
                            <a href="#"><h4 class="shop-name">Emma Simons</h4></a>
                            <p class="shop-location">
                                <i class="icn icn-coll-location">
                                <svg class="svg"> 
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location"></use>
                                </svg>
                                </i>
                                <span>California, United States</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>  
            <div class="col-xl-4 col-lg-4 col-md-4">
                <div class="shop__card">
                    <div class="shop__card--body">
                        <div class="shop-media">
                            <a href="#"><img src="/images/shop_2.png"></a>
                        </div>
                    </div>
                    <div class="shop__card--foot">
                        <div class="shop-description">
                            <a href="#"><h4 class="shop-name">Fashion Gyrl</h4></a>
                            <p class="shop-location">
                                <i class="icn icn-coll-location">
                                <svg class="svg"> 
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#shop-location"></use>
                                </svg>
                                </i>
                                <span>California, United States</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
</section>

<!-- Section Shop shop End -->


<!-- Tabs function Start -->
<script>
var _tab = $('.js-tabs');
    _tab.each(function(){
  var _this = $(this),
      _tabTrigger = _this.find('a'),
      _tabTarget = [];
      _tabTrigger.each(function(){
        var _this = $(this),
        _target = $(_this.attr('href'));
        _tabTarget.push(_target);
        _this.on('click', function(e){
            e.preventDefault();
            _tabTrigger.removeClass('current');
            $.each(_tabTarget, function(index, _thisTarget){
              _thisTarget.removeClass('visible');
            });
            _this.addClass('current');
            _target.addClass('visible');
        });
    });
});
</script>
<!-- Tabs function End -->

 <script>
    $(".tabs-content-home--js").hide();
    $(".faqTabs--flat-js li:first").addClass("is-active").show();
    $(".tabs-content-home--js:first").show();
    $(".faqTabs--flat-js li").click(function() {
        $(".faqTabs--flat-js li").removeClass("is-active");
        $(this).addClass("is-active");
        $(".tabs-content-home--js").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
    });
  
    $(".js-group-body").hide();
        $(".js-group-body:first").show();
        $(".js-group-head").click(function() {
            if ($(this).parents('.js-group').hasClass('is-active')) {
                $(this).siblings('.js-group-body').slideUp();
                $('.js-group').removeClass('is-active');
            } else {
                $('.js-group').removeClass('is-active');
                $(this).parents('.js-group').addClass('is-active');
                $('.js-group-body').slideUp();
                $(this).siblings('.js-group-body').slideDown();
            }
        });
</script>

<script>
$('.product-slider-js').slick({
  dots: true,
  arrows: false,
  infinite: false,
  speed: 300,
  slidesToShow: 3,
  slidesToScroll: 1,
  responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    }
  ]
});
</script> 

<script>
$('.explore--slider-js').slick({
  dots: false,
  arrows: false,
  infinite: false,
  speed: 300,
  slidesToShow: 4,
  slidesToScroll: 1,
  responsive: [
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    }
  ]
});
</script>

<script>
$('.js--hero-slider').slick({
    dots: false,
    arrows: false,
    infinite: true,
    speed: 500,
    fade: true,
    cssEase: 'linear'
});
</script>

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
        {breakpoint: 767,
            settings: {
                dots: true,
            }
        }
    ]
});
</script>

</div>
</div>
