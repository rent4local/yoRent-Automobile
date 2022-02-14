<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>


<div id="body" class="body" role="main"> 
    
<!-- Section Banner Start -->
<section class="section pt-0 home">
    <div class="home__slider">
        <div class="home__slider--item">
            <picture>
                <source media="(min-width:767px)" srcset="/images/automobile/banner.png">
                <source media="(min-width:1024px)" srcset="/images/automobile/banner.png">
                <source srcset="/images/automobile/banner.png">
                <img src="/images/automobile/banner.png" alt="slide">
            </picture>
        </div>
    </div>
    <div class="homesearch">
        <div class="homesearch__search">
            <div class="field-icon">
                <i class="icn">
                    <svg class="svg" width="20" height="20">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#location" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#location"></use>
                    </svg>
                </i> 
            </div>
            <div class="field-set">
              <div class="caption-wraper">
                    <label class="field_label">Select city<span class="spn_must_field">*</span></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <div class="dropdown dropdown--location show">
                            <a class="dropdown-toggle no-after" data-toggle="dropdown" data-display="static" href="javascript:void(0)" aria-expanded="true">
                                <span class="hide-sm">Rio De Janeiro</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
        <div class="homesearch__date">
            <div class="field-icon">
                <i class="icn">
                    <svg class="svg" width="20" height="20">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#calender" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#calender"></use>
                    </svg>
                </i> 
            </div>
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Journey starts on<span class="spn_must_field">*</span></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                    <input type="text" placeholder="25 March / 10:00 AM">
                    </div>
                </div>
            </div>
        </div>
        <div class="homesearch__date">
            <div class="field-icon d--block">
                <i class="icn">
                    <svg class="svg" width="20" height="20">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#calender" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#calender"></use>
                    </svg>
                </i> 
            </div>
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Journey ends on<span class="spn_must_field">*</span></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                    <input type="text" placeholder="Select Date / Time">
                    </div>
                </div>
            </div>            
        </div>
        <div class="homesearch__btn">
            <button class="btn btn-brand btn-round">SEARCH CARS</button>
        </div>
    </div>
</section>
<!-- Section Banner End -->

<!-- Section Benefits Start -->
<section class="section">
    <div class="container">
        <div class="section__heading">
            <h2>The Benefits Of Yo!rent</h2>
            <h5>We simplified car rentals, so you can focus on what's important to you.</h5>
        </div>
        <div class="row">
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/fuel.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>FUEL COST INCLUDED</h5>
                        <p>Don't worry about mileage! All fuel costs are included. If you refill fuel, we'll pay you back!</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/hidden_charges.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>NO HIDDEN CHARGES</h5>
                        <p>Our prices include taxes and insurance. What you see is what you really pay!</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/flexi-package.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>FLEXI PRICING PACKAGES</h5>
                        <p>One size never fits all! Choose a balance of time and kilometers that works best for you.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/go_anywhere.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>GO ANYWHERE</h5>
                        <p>Our cars have all-India permits. Just remember to pay state tolls and entry taxes.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/road_assistance.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>24X7 ROADSIDE ASSISTANCE</h5>
                        <p>We have round-the-clock, pan India partners. Help is never far away from you.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="service">
                    <div class="service__media flex-center">
                        <div class="media-icon flex-center">
                            <img src="/images/automobile/damage_insurance.png">
                        </div>
                    </div>
                    <div class="service__body">
                        <h5>DAMAGE INSURANCE</h5>
                        <p>All your bookings include damage insurance! Drive safe, but don’t worry!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section Benefits End -->

<!-- Section How It Work Start -->
<section class="section">
    <div class="container">
        <div class="section__heading">
            <h2>How Yo!rent Works</h2>
            <h5>Drive yourself to an adventure and back in 5 simple steps.</h5>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="work-media">
                    <img src="/images/automobile/how_it_works.png">
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="work-list">
                    <ul>
                        <li>
                            <h4>BOOK</h4>
                            <p>Search for a car as per your need and book on our site!</p>
                        </li>
                        <li>
                            <h4>UPLOAD LICENSE</h4>
                            <p>Upload your driver’s license, and pay a small security deposit.</p>
                        </li>
                        <li>
                            <h4>UNLOCK</h4>
                            <p>We SMS your car details 20 minutes before pickup. Unlock it via the Yo!rent app.</p>
                        </li>
                        <li>
                            <h4>LET'S ROLL</h4>
                            <p>Fill the start checklist in the Zoomcar app. Grab the keys from the glove-box and drive.</p>
                        </li>
                        <li>
                            <h4>RETURN</h4>
                            <p>Return the car to the same location and fill the end checklist to end your trip.</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section How It Work End-->

<!-- Section Product Slide Start -->
<section class="section collection--product">
    <div class="container">
        <div class="section__heading">
            <h2>Yo!rent's Caravan Fleet</h2>
            <h5>Select from our best, we have a wide range of caravan from road to off-road</h5>
        </div>
        <div class="product-wrapper js-carousel"  data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true" data-slickdots="flase">
            <div class="product">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/caravan1.png">
                    </div>
                    <div class="off-price">10% off</div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Ford Thor Miramar 35.2</a>
                        <div class="product-description">
                        13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft <span class="slash">|</span> 2015
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Fuel Type</span>
                                    <h5>Petrol</h5>
                                </li>
                                <li>
                                    <span>Mileage</span>
                                    <h5>20.35-25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Available features</span>
                                    <h5>AC, TV, Refrigerator...</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span></div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/caravan1.png">
                    </div>
                    <div class="off-price">10% off</div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Ford Thor Miramar 35.2</a>
                        <div class="product-description">
                        13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft <span class="slash">|</span> 2015
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Fuel Type</span>
                                    <h5>Petrol</h5>
                                </li>
                                <li>
                                    <span>Mileage</span>
                                    <h5>20.35-25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Available features</span>
                                    <h5>AC, TV, Refrigerator...</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span></div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/caravan1.png">
                    </div>
                    <div class="off-price">10% off</div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Ford Thor Miramar 35.2</a>
                        <div class="product-description">
                        13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft <span class="slash">|</span> 2015
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Fuel Type</span>
                                    <h5>Petrol</h5>
                                </li>
                                <li>
                                    <span>Mileage</span>
                                    <h5>20.35-25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Available features</span>
                                    <h5>AC, TV, Refrigerator...</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span></div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/caravan1.png">
                    </div>
                    <div class="off-price">10% off</div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Ford Thor Miramar 35.2</a>
                        <div class="product-description">
                        13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft <span class="slash">|</span> 2015
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Fuel Type</span>
                                    <h5>Petrol</h5>
                                </li>
                                <li>
                                    <span>Mileage</span>
                                    <h5>20.35-25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Available features</span>
                                    <h5>AC, TV, Refrigerator...</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span></div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-center mt-btn">
            <a class="btn btn-outline-brand btn-round arrow-right">View all</a>
        </div>
    </div>
</section>
<!-- Section Product Slide End--> 

<!-- Section Slider Top Arrow Start -->
<section class="section collection--top-arrows">
    <div class="container">
        <div class="section__heading">
            <h2>A Vehicle For Your Every Need</h2>
            <h5>Select from our wide range of cars collection</h5>
        </div>

        <div class="vehicle-category">
            <ul class="category-list">
                <li>
                    <h5>Popular car in category</h5>
                    <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC &amp; many more...</span>
                </li>
                <li>
                    <h5>Price Starting at</h5>
                    <span>₹80/hr (Fuel Free)</span>
                </li>
                <li>
                    <h5>Occupancy</h5>
                    <span>5</span>
                </li>
                <li>
                    <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                </li>
            </ul>
        </div>

        <div class="arrow-top js-carousel" data-slides="1,1,1,1,1" data-infinite="false" data-arrows="true" data-slickdots="true">
            <div class="vehicle">
                <div class="vehicle__media">
                    <img src="/images/automobile/caravan.png">
                </div>
            </div>
            <div class="vehicle">
                <div class="vehicle__media">
                    <img src="/images/automobile/caravan.png">
                </div>
            </div>
            <div class="vehicle">
                <div class="vehicle__media">
                    <img src="/images/automobile/caravan.png">
                </div>
            </div>  
        </div>
    </div>
</section>
<!-- Section Slider Top Arrow End -->

<!-- Section Categoies Start -->
<section class="section collection--category">
    <div class="container">
        <div class="section__heading">
            <h2>Caravan by Categories</h2>
            <h5>Select caravan of your choice from our well organized categories</h5>
        </div>
        <div class="d-grid d-lg-down-flex" data-view="3">
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_family.png">
                </div>
                <div class="category__content">
                <h5>For friends</h5>
                </div>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_friends.png">
                </div>
                <div class="category__content">
                        <h5>For friends</h5>
                </div>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_pet_friendly.png">
                </div>
                <div class="category__content">
                    <h5>Pet Friendly</h5>
                </div>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_with_amenities.png">
                </div>
                <div class="category__content">
                    <h5>With amenities</h5>
                </div>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_short_trips.png">
                </div>
                <div class="category__content">
                    <h5>For short trips</h5>
                </div>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/cc_inexpensive.png">
                </div>
                <div class="category__content">
                    <h5>Under $150</h5>
                </div>
                <a href="#"></a>
            </div>
        </div>
    </div>
</section>
<!-- Section Categoies End -->

<!-- Section Product Slide Start -->
<section class="section collection--product-tile-2">
    <div class="container">
        <div class="section__heading">
            <h2>Yo!rent's Sport Car Fleet</h2>
            <h5>Select from our best, we have a wide range of sports cars</h5>
        </div>
        <div class="js-carousel product-wrapper"  data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true" data-slickdots="flase">
            <div class="product tile-2">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/red.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Volkswagen Polo</a>
                        <div class="product-description">
                         Hatchback <span class="slash">|</span> Sport <span class="slash">|</span> 2017 
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Capacity</span>
                                    <h5>5 passengers</h5>
                                </li>
                                <li>
                                    <span>Fuel Type / Mileage</span>
                                    <h5>Petrol / 25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Boot Space</span>
                                    <h5>250 Liters</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span> <span class="off-price">10% off</span> </div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product tile-2">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/red-2.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Volkswagen Polo</a>
                        <div class="product-description">
                         Hatchback <span class="slash">|</span> Sport <span class="slash">|</span> 2017 
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Capacity</span>
                                    <h5>5 passengers</h5>
                                </li>
                                <li>
                                    <span>Fuel Type / Mileage</span>
                                    <h5>Petrol / 25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Boot Space</span>
                                    <h5>250 Liters</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span> <span class="off-price">10% off</span> </div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product tile-2">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/red.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Volkswagen Polo</a>
                        <div class="product-description">
                         Hatchback <span class="slash">|</span> Sport <span class="slash">|</span> 2017 
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Capacity</span>
                                    <h5>5 passengers</h5>
                                </li>
                                <li>
                                    <span>Fuel Type / Mileage</span>
                                    <h5>Petrol / 25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Boot Space</span>
                                    <h5>250 Liters</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span> <span class="off-price">10% off</span> </div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
            <div class="product tile-2">
                <div class="product__head">
                    <div class=product-media>
                        <img src="/images/automobile/red-2.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <a href="#" class="product-name">Volkswagen Polo</a>
                        <div class="product-description">
                         Hatchback <span class="slash">|</span> Sport <span class="slash">|</span> 2017 
                        </div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <span>Capacity</span>
                                    <h5>5 passengers</h5>
                                </li>
                                <li>
                                    <span>Fuel Type / Mileage</span>
                                    <h5>Petrol / 25.2 Kmpl</h5>
                                </li>
                                <li>
                                    <span>Transmission</span>
                                    <h5>Manual</h5>
                                </li>
                                <li>
                                    <span>Boot Space</span>
                                    <h5>250 Liters</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="product__body--foot">
                        <div class="product--price"> <span class="bold">$450</span> <span> <span class="line-through">$500</span> <span class="slash-diagonal">/</span> night</span> <span class="off-price">10% off</span> </div>
                        <button class="btn btn-white btn-round">RENT CAR</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-center mt-btn">
            <a class="btn btn-outline-brand btn-round arrow-right">View all</a>
        </div>
    </div>
</section>
<!-- Section Product Slide End--> 

<!-- Section Brands Start -->
<section class="section collection--brands">
    <div class="container">
        <div class="section__heading">
            <h2>Top Brands</h2>
            <h5>Select from our wide range of garage</h5>
        </div>
        <div class="row justify-content-center">
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_citreon.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_honda.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center active">
                    <img src="/images/automobile/logo_bmw.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_jaguar.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_chevrolet.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_skoda.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_land_rover.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_vol.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_toyota.png">
                </div>
            </div>
            <div class="col">
                <div class="brand-media flex-center">
                    <img src="/images/automobile/logo_hyundai.png">
                </div>
            </div>
        </div>
        <div class="flex-center">
            <a class="btn btn-outline-brand btn-round arrow-right">View all</a>
        </div>
    </div>
</section>
<!-- Section Brands End -->

<!-- Section Tabs With Slider End -->
<section class="section bg-brand-alpha">
    <div class="container">
        <div class="section__heading">
            <h2>A Vehicle For Your Every Need</h2>
        </div>
        <div class="automobile-tabs">
            <ul class="js--tabs">
                <li >
                    <a href="#tab--1" class="is-active">HATCHBACK</a>
                </li>
                <li>
                    <a href="#tab--2">SEDAN</a>
                </li>
                <li>
                    <a href="#tab--3">MUV</a>
                </li>
                <li>
                    <a href="#tab--4">SUV</a>
                </li>
                <li>
                    <a href="#tab--5">OFF ROAD JEEP</a>
                </li>
                <li>
                    <a href="#tab--6">CARAVAN</a>
                </li>
            </ul>
        </div>
        <div id="tab--1" class="tab_content visible">
            <div class="bottom-dots js-carousel" data-slides="1,1,1,1,1" data-infinite="false" data-arrows="true" data-slickdots="true">
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>  
            </div>
            <div class="vehicle-category">
                <ul class="category-list">
                    <li>
                        <h5>Popular car in category</h5>
                        <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC &amp; many more...</span>
                    </li>
                    <li>
                        <h5>Price Starting at</h5>
                        <span>₹80/hr (Fuel Free)</span>
                    </li>
                    <li>
                        <h5>Occupancy</h5>
                        <span>5</span>
                    </li>
                    <li>
                        <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                    </li>
                </ul>
            </div>
        </div>
        <div id="tab--2" class="tab_content">
            <div class="bottom-dots js-carousel" data-slides="1,1,1,1,1" data-infinite="false" data-arrows="true" data-slickdots="true">
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>
                <div class="vehicle">
                    <div class="vehicle__text">CARAVAN</div>
                    <div class="vehicle__media">
                        <img src="/images/automobile/caravan.png">
                    </div>
                </div>  
            </div>
            <div class="vehicle-category">
                <ul class="category-list">
                    <li>
                        <h5>Popular car in category</h5>
                        <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC &amp; many more...</span>
                    </li>
                    <li>
                        <h5>Price Starting at</h5>
                        <span>₹80/hr (Fuel Free)</span>
                    </li>
                    <li>
                        <h5>Occupancy</h5>
                        <span>5</span>
                    </li>
                    <li>
                        <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- Section Tabs With Slider End -->

<!-- Section Product Start -->
<section class="section collection--product">
    <div class="container">
        <div class="section__heading">
            <h2>Affordable Cars In Demand</h2>
            <h5>Select from our best, we have a wide range of cars from road to off-road</h5>
        </div>
        <div class="d-grid d-lg-down-flex product-wrapper" data-view="3">
            <div class="product tile-3">
                <div class="product__head">
                    <div class="product-media">
                        <img src="/images/automobile/car_jeep.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <div>
                            <a href="#" class="product-name">Hummer</a>
                            <div class="product-description">
                            2019 <span class="slash">|</span> Jeep <span class="slash">|</span> Off-road
                            </div>
                        </div>
                        <div class="product--price">~$20 <span class="slash-diagonal">/</span> <span>hr</span></div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        5 passengers
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Automatic
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Petrol <br> 20.35-25.2 Kmpl
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        311 Liters
                                    </h5>
                                </li>
                            <ul>
                        </div>
                        <div class="action">
                        <button class="btn btn-brand btn-round">RENT CAR</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product tile-3">
                <div class="product__head">
                    <div class="product-media">
                        <img src="/images/automobile/car_bmw2.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <div>
                            <a href="#" class="product-name">BMW 520d</a>
                            <div class="product-description">
                            2019 <span class="slash">|</span> Jeep <span class="slash">|</span> Off-road
                            </div>
                        </div>
                        <div class="product--price">~$20 <span class="slash-diagonal">/</span> <span>hr</span></div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        5 passengers
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Automatic
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Petrol <br> 20.35-25.2 Kmpl
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        311 Liters
                                    </h5>
                                </li>
                            <ul>
                        </div>
                        <div class="action">
                        <button class="btn btn-brand btn-round">RENT CAR</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product tile-3">
                <div class="product__head">
                    <div class="product-media">
                        <img src="/images/automobile/car_minicooper.png">
                    </div>
                </div>
                <div class="product__body">
                    <div class="product__body--head">
                        <div>
                            <a href="#" class="product-name">Mini Cooper</a>
                            <div class="product-description">
                            2019 <span class="slash">|</span> Jeep <span class="slash">|</span> Off-road
                            </div>
                        </div>
                        <div class="product--price">~$20 <span class="slash-diagonal">/</span> <span>hr</span></div>
                    </div>
                    <div class="product__body--body">
                        <div class="product-detail">
                            <ul>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#passenger"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        5 passengers
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#transmission"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Automatic
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#petrol"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        Petrol <br> 20.35-25.2 Kmpl
                                    </h5>
                                </li>
                                <li>
                                    <h5> 
                                        <span>
                                        <i class="icn">
                                            <svg class="svg" width="22" height="22">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#capacity"></use>
                                            </svg>
                                        </i>    
                                        </span>
                                        311 Liters
                                    </h5>
                                </li>
                            <ul>
                        </div>
                        <div class="action">
                        <button class="btn btn-brand btn-round">RENT CAR</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section Product End--> 

<!-- Section Experience Start -->
<section class="section bg-brand-alpha collection--experience">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="experience">
                    <div class="experience__icon flex-center">
                        <i class="icn">
                            <svg class="svg" width="48" height="48">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#rids" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#rides"></use>
                            </svg>
                        </i>                        
                    </div>
                    <div class="experience__content">
                        <h5>3,000+</h5>
                        <span>Rides Daily</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="experience">
                    <div class="experience__icon flex-center">
                        <i class="icn">
                            <svg class="svg" width="48" height="48">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#happy" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#happy"></use>
                            </svg>
                        </i>                        
                    </div>
                    <div class="experience__content">
                        <h5>48,00,000+</h5>
                        <span>Happy users</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="experience">
                    <div class="experience__icon flex-center">
                        <i class="icn">
                            <svg class="svg" width="48" height="48">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#rated" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#rated"></use>
                            </svg>
                        </i>                        
                    </div>
                    <div class="experience__content">
                        <h5>Rated 4.7/5.0</h5>
                        <span>Rated by 3,00,000+ customers</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="experience">
                    <div class="experience__icon flex-center">
                        <i class="icn">
                            <svg class="svg" width="48" height="48">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#cars" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#cars"></use>
                            </svg>
                        </i>                        
                    </div>
                    <div class="experience__content">
                        <h5>6,500+</h5>
                        <span>Number of Yo!cars</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section Experience End -->

<!-- Section Page Banner Start -->
<section class="section">
    <div class="container">
        <div class="explore-banner">
            <img src="/images/automobile/page-banner.png">
        </div>
    </div>
</section>
<!-- Section Page Banner End -->

<!-- Section TABS START -->
<section class="section collection--tabs">
    <div class="container">
        <div class="section__heading">
            <h2>A Car For Your Every Need</h2>
        </div>
        <div class="automobile-tabs">
            <ul class="js--tabs">
                <li>
                    <a href="#tab-1" class="is-active">HATCHBACK</a>
                </li>
                <li>
                    <a href="#tab-2" class="">SEDAN</a>
                </li>
                <li>
                    <a href="#tab-3">MUV</a>
                </li>
                <li>
                    <a href="#tab-4">SUV</a>
                </li>
                <li>
                    <a href="#tab-5">OFF ROAD JEEP</a>
                </li>
                <li>
                    <a href="#tab-6">CARAVAN</a>
                </li>
            </ul>
        </div>
        <div id="tab-1" class="tab_content visible">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="car-category-media">
                        <img src="/images/automobile/sedan.png">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="car-category">
                        <h2>SEDAN</h2>
                        <ul class="category-list">
                            <li>
                                <h5>Price Starting at</h5>
                                <span>₹80/hr (Fuel Free)</span>
                            </li>
                            <li>
                                <h5>Occupancy</h5>
                                <span>5</span>
                            </li>
                            <li>
                                <h5>Popular car in category</h5>
                                <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC & many more...</span>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="tab-2" class="tab_content">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="car-category-media">
                        <img src="/images/automobile/sedan.png">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="car-category">
                        <h2>SEDAN</h2>
                        <ul class="category-list">
                            <li>
                                <h5>Price Starting at</h5>
                                <span>₹80/hr (Fuel Free)</span>
                            </li>
                            <li>
                                <h5>Occupancy</h5>
                                <span>5</span>
                            </li>
                            <li>
                                <h5>Popular car in category</h5>
                                <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC & many more...</span>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="tab-3" class="tab_content">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="car-category-media">
                        <img src="/images/automobile/sedan.png">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="car-category">
                        <h2>SEDAN</h2>
                        <ul class="category-list">
                            <li>
                                <h5>Price Starting at</h5>
                                <span>₹80/hr (Fuel Free)</span>
                            </li>
                            <li>
                                <h5>Occupancy</h5>
                                <span>5</span>
                            </li>
                            <li>
                                <h5>Popular car in category</h5>
                                <span>Ford Figo, Maruti Swift, Mahindra Kuv 100, Hyundai I20 Elite, Jazz Smt 1.5 IDTEC & many more...</span>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-brand btn-round arrow-right">EXPLORE CARS</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Section TABS End -->

<!-- Section Category-2 Start -->
<section class="section collection--category">
    <div class="container">
        
    <div class="section__heading">
            <h2>Popular Destinations Near New York</h2>
            <h5>Take a break from busy schedule, travel to places nearby</h5>
        </div>
        <div class="d-grid d-lg-down-flex" data-view="3">
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/nearby-city_delaware.png">
                </div>
                <div class="category__content">
                    <h5>Delaware</h5>
                    <span>245 miles</span>
                </div>
                <button class="btn btn-brand btn-round">RENT CAR</button>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/nearby-city_pittsburg.png">
                </div>
                <div class="category__content">
                       <h5>Pittsburg</h5>
                       <span>245 miles</span>
                </div>
                <button class="btn btn-brand btn-round">RENT CAR</button>
                <a href="#"></a>
            </div>
            <div class="category">
                <div class="category__media">
                    <img src="/images/automobile/nearby-city_baltimore.png">
                </div>
                <div class="category__content">
                    <h5>Baltimore</h5>
                    <span>245 miles</span>
                </div>
                <button class="btn btn-brand btn-round">RENT CAR</button>
                <a href="#"></a>
            </div>
        </div>
    </div>
</section>
<!-- Section Category-2 End -->

<!-- SECTOIN FAQ  Start-->
<section class="section collection--faq">
    <div class="container">
        <div class="section__heading">
            <h2>Frequently Asked Questions</h2>
            <h5>For any general problems, choose the categories and find solutions in any easy way</h5>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="d-grid" data-view="3">
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>Car bookings</h5>
                            <span>24 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>Journey & Trips</h5>
                            <span>15 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>Payments</h5>
                            <span>24 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>Repair & Service</h5>
                            <span>24 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>Cancellation & refunds</h5>
                            <span>24 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                    <div class="faq-card">
                        <div class="faq-media flex-center">
                            <i class="icn">
                                <svg class="svg" width="40" height="40">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#car-booking"></use>
                                </svg>
                            </i>  
                        </div>
                        <div class="faq-content">
                            <h5>All questions</h5>
                            <span>24 questions</span>
                        </div>
                        <span class="arrow-right"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- SECTOIN FAQ  End-->

<!-- SECTOIN TESTIMONIAL START -->
<section class="section bg-brand-alpha collection--testimonial">
    <div class="container">
        <div class="section__heading">
            <h2>Testimonials</h2>
            <h5>Listen to our travelers, what they are saying</h5>
        </div>
        <div class="testimonial-wrapper js--single">
            <div class="testimonial">
                <div class="testimonial__quotes">
                    <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/quotes-test.svg">
                </div>
                <div class="testimonial__body">
                The personal benefits include on-demand transportation, mobility, independence, and convenience.The societal benefits include economic benefits, such as job and wealth creation from the automotive industry, transportation provision, societal well-being from leisure and travel opportunities, and revenue generation from the taxes. People's ability to move flexibly from place to place has far-reaching implications for the nature of societies.There are around 1 billion cars in use worldwide
                </div>
                <div class="testimonial__foot">
                    <div class="user">
                        <div class="user__media">
                            <img src="/images/automobile/user1.png">
                        </div>
                        <div class="user__detail">
                            <h5>Nedra Brekke</h5>
                            <span>New York City, New York</span>
                            <ul class="review">
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial__quotes">
                    <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/quotes-test.svg">
                </div>
                <div class="testimonial__body">
                The personal benefits include on-demand transportation, mobility, independence, and convenience.The societal benefits include economic benefits, such as job and wealth creation from the automotive industry, transportation provision, societal well-being from leisure and travel opportunities, and revenue generation from the taxes. People's ability to move flexibly from place to place has far-reaching implications for the nature of societies.There are around 1 billion cars in use worldwide
                </div>
                <div class="testimonial__foot">
                    <div class="user">
                        <div class="user__media">
                            <img src="/images/automobile/user1.png">
                        </div>
                        <div class="user__detail">
                            <h5>Nedra Brekke</h5>
                            <span>New York City, New York</span>
                            <ul class="review">
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial__quotes">
                    <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/quotes-test.svg">
                </div>
                <div class="testimonial__body">
                The personal benefits include on-demand transportation, mobility, independence, and convenience.The societal benefits include economic benefits, such as job and wealth creation from the automotive industry, transportation provision, societal well-being from leisure and travel opportunities, and revenue generation from the taxes. People's ability to move flexibly from place to place has far-reaching implications for the nature of societies.There are around 1 billion cars in use worldwide
                </div>
                <div class="testimonial__foot">
                    <div class="user">
                        <div class="user__media">
                            <img src="/images/automobile/user1.png">
                        </div>
                        <div class="user__detail">
                            <h5>Nedra Brekke</h5>
                            <span>New York City, New York</span>
                            <ul class="review">
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="icn">
                                            <svg class="svg" width="16" height="16">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star" href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#star"></use>
                                            </svg>
                                        </i> 
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-center mt-btn">
            <a class="btn btn-outline-brand btn-round arrow-right">View all</a>
        </div>
    </div>
</section>
<!-- SECTOIN TESTIMONIAL END -->

<!-- SECTOIN BLOG START -->
<section class="section collection--blog">
    <div class="container">
        <div class="section__heading">
            <h2>Our Blogs</h2>
            <h5>Sharing something important from our experts journey</h5>
        </div>
        <div class="d-grid" data-view="4">
            <div class="blog-item">
                <div class="blog">
                    <div class="blog__media">
                        <img src="/images/automobile/blog2.png">
                    </div>
                    <div class="blog__content">
                        <div class="blog-detail">
                            <div class="blog-date">10th March 2021 <span class="slash">|</span> Adriana Grande</div>
                            <a href="#" class="blog-title">Adventurous activities you can plan on a road trip</a>
                            <p>Born and raised in Nashua, New Hmpshi, Triple H began his professional wrestling career in 1992 with the International Wrestling Federation (IWF) under... the ring name Terra Ryzing. He joined World Championship Wrestling (WCW) in 1994 and was repackaged as a French-Canadian aristocrat named Jean-Paul Lévesque, and was later repackaged in 1995 when he signed with the World Wrestling Federation (WWF, now WWE), where he became Hunter Hearst Helmsley, and later, Triple H.In WWF, Triple H gained industry fame after co-founding the influential D-Generation X stable, which became a major element of the "Attitude Era" in the 1990s. After winning his first WWF Championship in 1999, he became a fixture of the company's main event scene, and was widely regarded as the best wrestler in North America by the turn of the millennium. Triple H has headlined several major WWE pay-per-view events, closing the company's flagship annual event, WrestleMania, on seven occasions.Triple H has also won a number of championships in his career, being a five-time Intercontinental Champion, a three-time world tag team champion (two World Tag Team Championship reigns, and one Unified WWE Tag Team Championship reign), a two-time European Champion, and a fourteen-time world champion, making him the company's seventh Triple Crown Champion and second Grand Slam Champion. He is also a two-time Royal Rumble match winner, and a King of the Ring tournament winner. Later in his career, Triple H gained notability for his behind-the-scenes work at WWE, creating the developmental branch NXT, and gaining praise for his business acumen in professional wrestling.Outside of wrestling, Triple H has been a figure of substantial media attention due to his marriage to Stephanie McMahon, daughter of Vince and Linda McMahon, who are majority owners of WWE. In 2019, he was inducted into the WWE Hall of Fame as part of the D-Generation X stable.</p>
                        </div>
                        <a href="#" class="action">
                            <span class="arrow-right">Continue Reading</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="blog-item">
                <div class="blog">
                    <div class="blog__media">
                        <img src="/images/automobile/blog3.png">
                    </div>
                    <div class="blog__content">
                        <div class="blog-detail">
                            <div class="blog-date">12th March 2021 <span class="slash">|</span> Adriana Grande</div>
                            <a href="#" class="blog-title">Travel to places with relaxing views</a>
                            <p>Born and raised in Nashua, New Hmpshi, Triple H began his professional wrestling career in 1992 with the International Wrestling Federation (IWF) under... the ring name Terra Ryzing. He joined World Championship Wrestling (WCW) in 1994 and was repackaged as a French-Canadian aristocrat named Jean-Paul Lévesque, and was later repackaged in 1995 when he signed with the World Wrestling Federation (WWF, now WWE), where he became Hunter Hearst Helmsley, and later, Triple H.In WWF, Triple H gained industry fame after co-founding the influential D-Generation X stable, which became a major element of the "Attitude Era" in the 1990s. After winning his first WWF Championship in 1999, he became a fixture of the company's main event scene, and was widely regarded as the best wrestler in North America by the turn of the millennium. Triple H has headlined several major WWE pay-per-view events, closing the company's flagship annual event, WrestleMania, on seven occasions.Triple H has also won a number of championships in his career, being a five-time Intercontinental Champion, a three-time world tag team champion (two World Tag Team Championship reigns, and one Unified WWE Tag Team Championship reign), a two-time European Champion, and a fourteen-time world champion, making him the company's seventh Triple Crown Champion and second Grand Slam Champion. He is also a two-time Royal Rumble match winner, and a King of the Ring tournament winner. Later in his career, Triple H gained notability for his behind-the-scenes work at WWE, creating the developmental branch NXT, and gaining praise for his business acumen in professional wrestling.Outside of wrestling, Triple H has been a figure of substantial media attention due to his marriage to Stephanie McMahon, daughter of Vince and Linda McMahon, who are majority owners of WWE. In 2019, he was inducted into the WWE Hall of Fame as part of the D-Generation X stable.</p>
                        </div>
                        <a href="#" class="action">
                            <span class="arrow-right">Continue Reading</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="blog-item">
                <div class="blog">
                    <div class="blog__media">
                        <img src="/images/automobile/blog1.png">
                    </div>
                    <div class="blog__content">
                        <div class="blog-detail">
                            <div class="blog-date">10th March 2021 <span class="slash">|</span> Adriana Grande</div>
                            <a href="#" class="blog-title" >Adventurous activities you can plan on a road trip</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex-center mt-btn">
            <a class="btn btn-outline-brand btn-round arrow-right">View all</a>
        </div>
    </div>
</section>
<!-- SECTOIN BLOG END -->

<!-- Section Newsletter Start -->
<section class="section bg-brand-alpha">
    <div class="container">
        <div class="section__heading">
            <h2>Stay Tuned With Us</h2>
            <h5>Subscribe to our newsletter to get alert, notifications & offers.</h5>
        </div>
        <form>
            <div class="newsletter-form">
                <input type="text" placeholder="Enter your email address">
                <input type="submit">
            </div>
        </form>
    </div>
</section>
<!-- Section Newsletter End -->


</div>

<script>
var _tab = $('.js--tabs');
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
            _tabTrigger.removeClass('is-active');
            $.each(_tabTarget, function(index, _thisTarget){
              _thisTarget.removeClass('visible');
            });
            _this.addClass('is-active');
            _target.addClass('visible');
        });
    });
});
</script>


<script>
$('.js--single').slick({
  dots: false,
  arrows: false,
  infinite: false,
  speed: 300,
  slidesToShow: 1,
  centerMode: true,
        responsive: [{
            breakpoint: 1200,
            settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            centerMode: false,
        }
    }
    ]
});
</script>