<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="body" class="body" role="main">

    <!-- Section Gallery Start -->
    <section class="section py-0">
        <div class="detail-gallery">
            <div class="js-carousel" data-slides="1,1,1,1,1" data-arrows="true">
                <div class="slide-item">
                    <picture>
                        <source media="(min-width:767px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source media="(min-width:1024px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <img src="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" alt="slide" />
                    </picture>
                </div>
                <!--item-->
                <div class="slide-item">
                    <picture>
                        <source media="(min-width:767px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source media="(min-width:1024px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <img src="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" alt="slide" />
                    </picture>
                </div>
                <!--item-->
                <div class="slide-item">
                    <picture>
                        <source media="(min-width:767px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source media="(min-width:1024px)" srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <source srcset="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" />
                        <img src="/images/<?php echo ACTIVE_THEME; ?>/gallery-img-1.png" alt="slide" />
                    </picture>
                </div>
                <!--item-->

            </div>
        </div>
    </section>
    <!-- Section Banner End -->

    <!-- Section detail Start -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-xl-5 order-xl-2">
                    <div class="block">
                        <div class="block__header">
                            <div class="tabs js-tabs tabs--rent-buy">
                                <ul>
                                    <li><a href="#tb-1" class="current">Rent</a></li>
                                    <li><a href="#tb-2">Buy</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="block__body">
                            <div id="tb-1" class="tabs-content visible">
                                <form class="form form--buy-rent">

                                    <div class="row">
                                        <div class="col-xl-12 col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">Select city<span
                                                            class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <select>
                                                            <option>Kuala Lumpur, Malaysia</option>
                                                            <option>Kuala Lumpur, Malaysia</option>
                                                            <option>Kuala Lumpur, Malaysia</option>
                                                            <option>Kuala Lumpur, Malaysia</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">Journey starts on<span
                                                            class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <input type="text" value="25 Mar / 10:00 AM"
                                                            class="date-picker-field" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">Journey ends on<span
                                                            class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <input type="text" placeholder="Select date & time"
                                                            class="date-picker-field" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="dashed-separater"></div>

                                    <div class="row">
                                        <div class="col-xl-12 col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">Select variant<span
                                                            class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <select>
                                                            <option>530i M Sport / Diesel / Automatic (CVT)</option>
                                                            <option>530i M Sport / Diesel / Automatic (CVT)</option>
                                                            <option>530i M Sport / Diesel / Automatic (CVT)</option>
                                                            <option>530i M Sport / Diesel / Automatic (CVT)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="dashed-separater"></div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">Choose plan*<span
                                                            class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">

                                                        <div class="rental-plan">
                                                            <div class="choose-option">
                                                                <input type="radio" name="choose-plan">
                                                            </div>
                                                            <div class="rental-detail">
                                                                <h5 class="plan-title">ONE TIME RENTAL</h5>
                                                                <h6>Each trip costs</h6>
                                                                <p><span>+$1200</span> security charges (refundable)</p>
                                                            </div>
                                                            <div class="plan-cost">$20<span>/hr</span></div>
                                                        </div>

                                                        <div class="rental-plan membership">
                                                            <div class="choose-option">
                                                                <input type="radio" name="choose-plan">
                                                            </div>
                                                            <div class="rental-detail">
                                                                <h5 class="plan-title">YO!RENT MEMBERSHIP</h5>
                                                                <h6>Basic Plan</h6>
                                                                <p>includes <span>4 trips a month</span> <i>(applicable
                                                                        on same car only)</i></p>
                                                                <p><a href="#">Explore plans</a> to save more!
                                                            </div>
                                                            <div class="plan-cost">$15<span>/hr</span></div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="dashed-separater"></div>

                                    <div class="aditional-services">
                                        <h3>Aditional Service</h3>
                                        <div class="aditional-services-wrap">
                                            <ul>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Bike/Gear Rack</span>
                                                        <p>Hitch-mounted rack that holds up to 4 bikes or other large
                                                            gear (coolers, etc)</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Empty Gas Tank Fee</span>
                                                        <p>We'll start you off with a full tank of gas - return the RV
                                                            with a full tank or we'll charge this fee.</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Little Adventurer Gear</span>
                                                        <p>Carseat (we have both infant and toddler seats available),
                                                            jogging stroller, clip-to-table high chair, hiking backpack
                                                            carrier, pack and play, water toys</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Bike/Gear Rack</span>
                                                        <p>Hitch-mounted rack that holds up to 4 bikes or other large
                                                            gear (coolers, etc)</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Empty Gas Tank Fee</span>
                                                        <p>We'll start you off with a full tank of gas - return the RV
                                                            with a full tank or we'll charge this fee.</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                                <li>
                                                    <span class="checkbox"><input type="checkbox"></span>
                                                    <div class="services-text">
                                                        <span>Little Adventurer Gear</span>
                                                        <p>Carseat (we have both infant and toddler seats available),
                                                            jogging stroller, clip-to-table high chair, hiking backpack
                                                            carrier, pack and play, water toys</p>
                                                    </div>
                                                    <span>$30</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="btn-wraper">
                                        <button class="btn btn-brand btn-round btn--lg arrow--right" tabindex="0">Rent
                                            Now</button>
                                    </div>

                                </form>
                            </div>
                            <div id="tb-2" class="tabs-content">
                                Buy form here
                            </div>
                        </div>
                    </div>
                    <div class="sold-by mt-md-5 mt-3">
                        <a href="#" class="text-link">More Sellers</a>
                        <h5 class="section-title">SELLERS INFORMATION</h5>
                        <h5><a href="" class="shop--title">BMW, Industarial Area, Chandigarh</a></h5>
                        <div class="rating" style="--rating-fg: #FFBD3A;">
                            <div class="rating-view" data-rating="4" style="--size:1rem">
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                </svg>
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                </svg>
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                </svg>
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                </svg>
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                </svg>
                            </div>
                            <span class="rate pl-2">1323 reviews</span>
                        </div>
                        <div class="sold-by__actions">
                            <a href="#">
                                <svg class="icon" width="24px" height="24px">
                                    <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#ask-question"></use>
                                </svg>
                                Ask a question
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7 order-xl-1">
                    <div class="detail pr-xl-3 pt-4 pt-xl-0">
                        <div class="detail__head">
                            <h1>Jayco GreyHawk TSi 2030M</h1>
                            <ul class="higlight-features">
                                <li>Class C</li>
                                <li>8 seatbelts</li>
                                <li>Sleeps 8</li>
                                <li>2021</li>
                            </ul>
                            <div class="d-md-flex align-items-center justify-content-between pt-3">
                                <div class="product-review">
                                    <span class="review">
                                        <i class="icon">
                                            <svg class="svg">
                                                <use xlink:href="/images/retina/sprite.svg#rating"
                                                    href="/images/retina/sprite.svg#rating"></use>
                                            </svg>
                                        </i>1234 reviews</span>
                                    <span>678 answered questions</span>
                                </div>
                                <div class="product-action">
                                    <ul>
                                        <li>
                                            <a href="#" class="btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24">
                                                    <g transform="translate(6 2)">
                                                        <path fill="none" stroke="currentColor" stroke-width="1.6px"
                                                            d="M17754,698v15" transform="translate(-17748 -698)" />
                                                        <path fill="none" stroke="currentColor" stroke-width="1.6px"
                                                            d="M17748,713.5l6,6,6-6"
                                                            transform="translate(-17748 -704.5)" />
                                                        <path fill="none" stroke="currentColor" stroke-width="1.6px"
                                                            d="M17748,719h12" transform="translate(-17748 -700)" />
                                                    </g>
                                                </svg>
                                                <span>Brochure</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <!-- use  "marked-favorite" class is for marke favorite-->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24">
                                                    <path fill="none" stroke="currentColor" stroke-width="1.6px"
                                                        d="M18.058,3.444a5.342,5.342,0,0,0-7.289.531L10,4.768l-.77-.793a5.341,5.341,0,0,0-7.289-.531,5.609,5.609,0,0,0-.387,8.121l7.558,7.8a1.225,1.225,0,0,0,1.769,0l7.558-7.8a5.605,5.605,0,0,0-.383-8.121Z"
                                                        transform="translate(2.001 1.252)" />
                                                </svg>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24">
                                                    <g transform="translate(4 3)">
                                                        <path fill="none" stroke="currentColor" stroke-width="1.6px"
                                                            d="M20859.109-974,20849-968l10.113,6"
                                                            transform="translate(-20846.496 977)" />
                                                        <circle fill="#fff" stroke="currentColor" stroke-width="1.6px"
                                                            cx="3" cy="3" r="3" transform="translate(9.614)" />
                                                        <circle fill="#fff" stroke="currentColor" stroke-width="1.6px"
                                                            cx="3" cy="3" r="3" transform="translate(9.614 12)" />
                                                        <circle fill="#fff" stroke="currentColor" stroke-width="1.6px"
                                                            cx="3" cy="3" r="3" transform="translate(0 6)" />
                                                    </g>
                                                </svg>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="detail__body">
                            <div class="detail-block cms">
                                <h4>OVERVIEW</h4>
                                <p>2021 Class C Jayco Greyhawk BunkhouseBeautiful new RV with everything you need to
                                    enjoy a trip with family or friends. Sleeps 8 comfortably. Easy to drive.</p>

                                <ul class="list list--overview mt-3">
                                    <li>
                                        <span>Sleeps</span>
                                        8 guests
                                    </li>
                                    <li>
                                        <span>Seats</span>
                                        8 seatbelts
                                    </li>
                                    <li>
                                        <span>Fresh water tank</span>
                                        42 gal
                                    </li>
                                    <li>
                                        <span>Propane capacity</span>
                                        4 lbs
                                    </li>
                                    <li>
                                        <span>Transmission</span>
                                        Automatic
                                    </li>
                                    <li>
                                        <span>Fuel / Capacity</span>
                                        Gas / 57 gal
                                    </li>
                                </ul>

                            </div>
                            <div class="detail-block cms">
                                <h2>Meet your host</h2>
                                <div class="host">
                                    <div class="host__img">
                                        <img src="/images/<?php echo ACTIVE_THEME; ?>/avtar-img.png" />
                                    </div>
                                    <div class="host__detail">
                                        <h5>Katelyn</h5>
                                        <p>We love getting outdoors with our two daughters (ages 6 and 1) and seeing
                                            their faces light up at discovering new things in nature. Our RV is our
                                            ticket to making these outdoor adventures as comfortable, safe and epic as
                                            possible. We'd love to share with you about our past travels and our
                                            ever-expanding wish-list of future destinations!</p>
                                    </div>
                                </div>
                            </div>
                            <div class="detail-block cms">
                                <h2>Amenities</h2>
                                <ul class="list list--amenities">
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/washroom.svg" />
                                        </span>
                                        <span>Washroom</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/shower.svg" />
                                        </span>
                                        <span>Inside Shower</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/ac.svg" />
                                        </span>
                                        <span>Air Conditioner</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/washroom.svg" />
                                        </span>
                                        <span>Radio</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/fan.svg" />
                                        </span>
                                        <span>Ceiling Fan</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/mircowave.svg" />
                                        </span>
                                        <span>Microwave</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/stove.svg" />
                                        </span>
                                        <span>Stove</span>
                                    </li>

                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/generator.svg" />
                                        </span>
                                        <span>Generator</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/refrigerator.svg" />
                                        </span>
                                        <span>Refrigerator</span>
                                    </li>
                                    <li>
                                        <span class="icon">
                                            <img src="/images/<?php echo ACTIVE_THEME; ?>/retina/dinning-table.svg" />
                                        </span>
                                        <span>Dining Table</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="detail-block cms">
                                <h2>Owner rules and policies</h2>
                                <ul class="list list--rules">
                                    <li>Pets not allowed</li>
                                    <li>No music festivals</li>
                                    <li>Tailgating not allowed</li>
                                    <li>No smoking</li>
                                </ul>
                            </div>
                            <div class="detail-block cms">
                                <h2>Policies</h2>
                                <h5>Cancellation policy</h5>
                                <p>- Free cancellation for 48 hours after booking, as long as you cancel more than 14
                                    days before departure. </p>
                                <p>- If canceled more than 5 days prior to departure:</p>
                                <ul class="list list--symble">
                                    <li>- Insurance is refunded.</li>
                                    <li>- 100% of the booking total is refunded.</li>
                                    <li>- The service fee is not refunded.</li>
                                </ul>
                                <p>- If canceled within 5 days prior to departure:</p>
                                <ul class="list list--symble">
                                    <li>- Insurance is refunded.</li>
                                    <li>- 75% of the booking total is refunded.</li>
                                    <li>- The service fee is not refunded.</li>
                                </ul>
                                <br>
                                <h5>Mileage</h5>
                                <p>100 miles free per day
                                <p>
                                <p>You will be charged $1.00 for every mile over per day.</p>
                                <br>
                                <h5>Generator usage</h5>
                                <p>4 free generator hours per day</p>
                                <p> If you exceed the included hours you will be charged $3.00 per hour.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Section detail End -->

    <section class="section">
        <div class="container">
            <div class="section__head pb-md-5 pb-3 text-center">
                <h2><strong>Ratings &amp; Reviews</strong></h2>
            </div>
            <div class="section__body">
                <div class="row">
                    <div class="col-xl-5 col-lg-4">
                        <div class="overall-product-rating">
                            <div class="overall-product-rating__left">
                                <div class="products__rating d-block">
                                    <div class="rate">4.3 <span>/ 5</span></div>
                                    <div class="rating" style="--rating-fg: #FFBD3A;">
                                        <div class="rating-view" data-rating="4" style="--size:1.25rem">
                                            <svg class="icon" width="24px" height="24px">
                                                <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                            </svg>
                                            <svg class="icon" width="24px" height="24px">
                                                <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                            </svg>
                                            <svg class="icon" width="24px" height="24px">
                                                <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                            </svg>
                                            <svg class="icon" width="24px" height="24px">
                                                <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                            </svg>
                                            <svg class="icon" width="24px" height="24px">
                                                <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating"></use>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="rating-based-on">Based on <span>1234 Ratings</span></div>
                            </div>
                            <div class="listing--progress-wrapper ">
                                <ul class="listing--progress">
                                    <li>
                                        <span class="progress_count">5 <img
                                                src="/images/<?php echo ACTIVE_THEME; ?>/retina/rating-star.svg"></span>
                                        <div class="progress__bar">
                                            <div title="50% Number Of Reviews Have 5 Stars"
                                                style="width: 60%; clip: rect(0px, 96px, 160px, 0px);"
                                                role="progressbar" class="progress__fill"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">4 <img
                                                src="/images/<?php echo ACTIVE_THEME; ?>/retina/rating-star.svg"></span>
                                        <div class="progress__bar">
                                            <div title="40% Number Of Reviews Have 4 Stars"
                                                style="width: 40%; clip: rect(0px, 96px, 160px, 0px);"
                                                role="progressbar" class="progress__fill"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">3 <img
                                                src="/images/<?php echo ACTIVE_THEME; ?>/retina/rating-star.svg"></span>
                                        <div class="progress__bar">
                                            <div title="30% Number Of Reviews Have 3 Stars"
                                                style="width: 40%; clip: rect(0px, 96px, 160px, 0px);"
                                                role="progressbar" class="progress__fill"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">2 <img
                                                src="/images/<?php echo ACTIVE_THEME; ?>/retina/rating-star.svg"></span>
                                        <div class="progress__bar">
                                            <div title="0% Number Of Reviews Have 2 Stars"
                                                style="width: 0%; clip: rect(0px, 96px, 160px, 0px);" role="progressbar"
                                                class="progress__fill"></div>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="progress_count">1 <img
                                                src="/images/<?php echo ACTIVE_THEME; ?>/retina/rating-star.svg"></span>
                                        <div class="progress__bar">
                                            <div title="0% Number Of Reviews Have 1 Stars"
                                                style="width: 0%; clip: rect(0px, 96px, 160px, 0px);" role="progressbar"
                                                class="progress__fill"></div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-8">
                        <div class="row justify-content-between">
                            <div class="col-auto">
                                <a onclick="rateAndReviewProduct(5)" href="javascript:void(0)"
                                    class="btn btn-brand btn-round btn--edit">
                                    <span class="btn--icon">
                                        <svg class="icon" width="20" height="20">
                                            <use xmlns:xlink="http://www.w3.org/1999/xlink"
                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#icon__pencil"></use>
                                        </svg>
                                    </span>
                                    Add Review
                                </a>
                            </div>
                            <div class="col-auto">
                                <div class="dropdown">
                                    <button class="btn btn--rating-filter dropdown-toggle" type="button"
                                        data-toggle="dropdown" data-display="static" aria-haspopup="true"
                                        aria-expanded="false"> <span>Most Recent</span> </button>
                                    <div class="dropdown-menu dropdown-menu-anim">
                                        <ul class="drop nav nav-block">
                                            <li class="nav__item selected"><a class="dropdown-item nav__link"
                                                    href="javascript:void(0);" data-sort="most_recent"
                                                    onclick="getSortedReviews(this);return false;">Most Recent</a></li>
                                            <li class="nav__item selected"><a class="dropdown-item nav__link"
                                                    href="javascript:void(0);" data-sort="most_helpful"
                                                    onclick="getSortedReviews(this);return false;">Most Helpful</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="listing__all">
                            <div class="total-review">Customer Reviews (1452)</div>
                            <ul class="reviews-list">
                                <li>
                                    <div class="reviews-desc">
                                        <div class="cms">
                                            <h5>Not flattering and not as expected.</h5>
                                            <div class="products__rating">
                                                <div class="rating" style="--rating-fg: #FFBD3A;">
                                                    <div class="rating-view" data-rating="4" style="--size:1rem">
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>
                                                <span class="lessText">I got it in the mail today and tried it on asap!
                                                    Love love this dress! The material is soft, the army green is so
                                                    pretty. Fyi ladies I am 5'6. 190. the girl's are 38dd and I'm curvy.
                                                    Xl I have lot's of room. The dress flows. I could of gotten a lg. If
                                                    you're going to use a shaper make sure it's a leotard type or a
                                                    waist clincher because the splits on the side are high enough for
                                                    anything else to be shown. I'm definitely buying another.</span>
                                                <span class="moreText hidden">Improved front and rear cameras -- now
                                                    with optical image stabilization -- deliver much improved photos,
                                                    especially in low light. Water resistant. A faster processor, plus
                                                    slightly better</span>
                                                <a class="readMore btn-link" href="javascript:void(0);">Show More </a>
                                            </p>
                                            <ul class="thumb-list has-more">
                                                <li>
                                                    <a href="#"><img src="/images/automobile/review-thumb.png"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img src="/images/automobile/review-thumb.png"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img src="/images/automobile/review-thumb.png"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img src="/images/automobile/review-thumb.png"></a>
                                                </li>
                                                <li class="more-media">
                                                    <a data-count="45+" href="#"><img
                                                            src="/images/automobile/review-thumb.png"></a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="row pt-3 justify-content-between align-items-center">
                                            <div class="col-md-auto">
                                                <div class="profile-avatar">
                                                    <div class="profile__dp">
                                                        <img src="/images/automobile/avtar-img.png" alt="">
                                                    </div>
                                                    <div class="review-postedby">By Jenny <span class="dated">On Date
                                                            25/07/2017</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="yes-no">
                                                    <span class="yes-no__lbl">Was This Review Helpful?</span>
                                                    <ul>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(2,1);return false;"
                                                                class="yes">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_liked">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                1234
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(&quot;2&quot;,0);return false;"
                                                                class="no">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_dislike">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                12
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="reviews-desc">
                                        <div class="cms">
                                            <h5>Not flattering and not as expected.</h5>
                                            <div class="products__rating">
                                                <div class="rating" style="--rating-fg: #FFBD3A;">
                                                    <div class="rating-view" data-rating="4" style="--size:1rem">
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>
                                                <span class="lessText">I got it in the mail today and tried it on asap!
                                                    Love love this dress! The material is soft, the army green is so
                                                    pretty. Fyi ladies I am 5'6. 190. the girl's are 38dd and I'm curvy.
                                                    Xl I have lot's of room. The dress flows. I could of gotten a lg. If
                                                    you're going to use a shaper make sure it's a leotard type or a
                                                    waist clincher because the splits on the side are high enough for
                                                    anything else to be shown. I'm definitely buying another.</span>
                                                <span class="moreText hidden">Improved front and rear cameras -- now
                                                    with optical image stabilization -- deliver much improved photos,
                                                    especially in low light. Water resistant. A faster processor, plus
                                                    slightly better</span>
                                                <a class="readMore btn-link" href="javascript:void(0);">Show More </a>
                                            </p>
                                        </div>
                                        <div class="row pt-3 justify-content-between align-items-center">
                                            <div class="col-md-auto">
                                                <div class="profile-avatar">
                                                    <div class="profile__dp">
                                                        <img src="/images/automobile/avtar-img.png" alt="">
                                                    </div>
                                                    <div class="review-postedby">By Jenny <span class="dated">On Date
                                                            25/07/2017</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="yes-no">
                                                    <span class="yes-no__lbl">Was This Review Helpful?</span>
                                                    <ul>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(2,1);return false;"
                                                                class="yes">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_liked">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                1234
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(&quot;2&quot;,0);return false;"
                                                                class="no">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_dislike">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                12
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="reviews-desc">
                                        <div class="cms">
                                            <h5>Not flattering and not as expected.</h5>
                                            <div class="products__rating">
                                                <div class="rating" style="--rating-fg: #FFBD3A;">
                                                    <div class="rating-view" data-rating="4" style="--size:1rem">
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>
                                                <span class="lessText">I got it in the mail today and tried it on asap!
                                                    Love love this dress! The material is soft, the army green is so
                                                    pretty. Fyi ladies I am 5'6. 190. the girl's are 38dd and I'm curvy.
                                                    Xl I have lot's of room. The dress flows. I could of gotten a lg. If
                                                    you're going to use a shaper make sure it's a leotard type or a
                                                    waist clincher because the splits on the side are high enough for
                                                    anything else to be shown. I'm definitely buying another.</span>
                                                <span class="moreText hidden">Improved front and rear cameras -- now
                                                    with optical image stabilization -- deliver much improved photos,
                                                    especially in low light. Water resistant. A faster processor, plus
                                                    slightly better</span>
                                                <a class="readMore btn-link" href="javascript:void(0);">Show More </a>
                                            </p>
                                        </div>
                                        <div class="row pt-3 justify-content-between align-items-center">
                                            <div class="col-md-auto">
                                                <div class="profile-avatar">
                                                    <div class="profile__dp">
                                                        <img src="/images/automobile/avtar-img.png" alt="">
                                                    </div>
                                                    <div class="review-postedby">By Jenny <span class="dated">On Date
                                                            25/07/2017</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="yes-no">
                                                    <span class="yes-no__lbl">Was This Review Helpful?</span>
                                                    <ul>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(2,1);return false;"
                                                                class="yes">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_liked">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                1234
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(&quot;2&quot;,0);return false;"
                                                                class="no">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_dislike">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                12
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="reviews-desc">
                                        <div class="cms">
                                            <h5>Not flattering and not as expected.</h5>
                                            <div class="products__rating">
                                                <div class="rating" style="--rating-fg: #FFBD3A;">
                                                    <div class="rating-view" data-rating="4" style="--size:1rem">
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                        <svg class="icon" width="24px" height="24px">
                                                            <use
                                                                xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#rating">
                                                            </use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>
                                                <span class="lessText">I got it in the mail today and tried it on asap!
                                                    Love love this dress! The material is soft, the army green is so
                                                    pretty. Fyi ladies I am 5'6. 190. the girl's are 38dd and I'm curvy.
                                                    Xl I have lot's of room. The dress flows. I could of gotten a lg. If
                                                    you're going to use a shaper make sure it's a leotard type or a
                                                    waist clincher because the splits on the side are high enough for
                                                    anything else to be shown. I'm definitely buying another.</span>
                                                <span class="moreText hidden">Improved front and rear cameras -- now
                                                    with optical image stabilization -- deliver much improved photos,
                                                    especially in low light. Water resistant. A faster processor, plus
                                                    slightly better</span>
                                                <a class="readMore btn-link" href="javascript:void(0);">Show More </a>
                                            </p>
                                        </div>
                                        <div class="row pt-3 justify-content-between align-items-center">
                                            <div class="col-md-auto">
                                                <div class="profile-avatar">
                                                    <div class="profile__dp">
                                                        <img src="/images/automobile/avtar-img.png" alt="">
                                                    </div>
                                                    <div class="review-postedby">By Jenny <span class="dated">On Date
                                                            25/07/2017</span></div>
                                                </div>
                                            </div>
                                            <div class="col-md-auto">
                                                <div class="yes-no">
                                                    <span class="yes-no__lbl">Was This Review Helpful?</span>
                                                    <ul>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(2,1);return false;"
                                                                class="yes">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_liked">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                1234
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:undefined;"
                                                                onclick="markReviewHelpful(&quot;2&quot;,0);return false;"
                                                                class="no">
                                                                <span>
                                                                    <svg class="icon" width="18px" height="18px">
                                                                        <use
                                                                            xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#review_dislike">
                                                                        </use>
                                                                    </svg>
                                                                </span>
                                                                12
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div class="all-review-btn text-center">
                                <a href="" class="btn arrow-right btn-outline-brand btn-round">
                                    Read all
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Section Product Slide Start -->
    <section class="section collection--product">
        <div class="container">
            <div class="section__heading">
                <h2>Similar Caravans</h2>
                <h5>Select from our best, we have a wide range of caravan from road to off-road</h5>
            </div>
            <div class="product-wrapper js-carousel" data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true"
                data-slickdots="flase">
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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

    <!-- Section Product Slide Start -->
    <section class="section collection--product">
        <div class="container">
            <div class="section__heading">
                <h2>Recently Viewed</h2>
                <h5>Select from our best, we have a wide range of caravan from road to off-road</h5>
            </div>
            <div class="product-wrapper js-carousel" data-slides="3,2,2,1,1" data-infinite="false" data-arrows="true"
                data-slickdots="flase">
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
                                13 seatbelts <span class="slash">|</span> Sleeps 4 <span class="slash">|</span> 24 ft
                                <span class="slash">|</span> 2015
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
                            <div class="product--price"> <span class="bold">$450</span> <span> <span
                                        class="line-through">$500</span> <span class="slash-diagonal">/</span>
                                    night</span></div>
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
var _tab = $('.js-tabs');
_tab.each(function() {
    var _this = $(this),
        _tabTrigger = _this.find('a'),
        _tabTarget = [];
    _tabTrigger.each(function() {
        var _this = $(this),
            _target = $(_this.attr('href'));
        _tabTarget.push(_target);
        _this.on('click', function(e) {
            e.preventDefault();
            _tabTrigger.removeClass('current');
            $.each(_tabTarget, function(index, _thisTarget) {
                _thisTarget.removeClass('visible');
            });
            _this.addClass('current');
            _target.addClass('visible');
        });
    });
});
</script>