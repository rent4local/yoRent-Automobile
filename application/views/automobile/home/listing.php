<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="body" class="body" role="main">
    <section class="bg-shop">
        <picture class="shop-banner">
            <source data-aspect-ratio="4:3" srcset="/images/automobile/listing-banner.png" media="(max-width: 767px)">
            <source data-aspect-ratio="4:3" srcset="/images/automobile/listing-banner.png" media="(max-width: 1024px)">
            <source data-aspect-ratio="4:1" srcset="/images/automobile/listing-banner.png">
            <img data-aspect-ratio="4:1" src="/images/automobile/listing-banner.png">
        </picture>
    </section>

    <!----- LISTING START ----->
    <section class="section">
        <div class="container">
            <div class="collection-listing filter-left">
                <sidebar class="collection-sidebar" id="collection-sidebar"
                    data-close-on-click-outside="collection-sidebar">
                    <div class="filters">
                        <h2>FILTERS</h2>
                        <div class="filters__ele productFilters-js">
                            <div class="filters_body" id="filters_body--js">
                                <!--Category Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#category" aria-expanded="true"
                                        aria-controls="category">
                                        Catagories
                                    </div>
                                    <div class="sidebar-widget__body collapse show" id="category">
                                        <div id="accordian" class="cat-accordion toggle-target">
                                            <ul>
                                                <li>
                                                    <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span>
                                                    <a class="filter_categories" data-id="113" href="#">Agricultural &
                                                        Farming</a>
                                                </li>
                                                <li>
                                                    <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span>
                                                    <a class="filter_categories" data-id="109" href="#">Welders &
                                                        Accessories</a>
                                                </li>
                                                <li>
                                                    <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span>
                                                    <a class="filter_categories" data-id="112" href="#">Compaction</a>
                                                </li>
                                                <li>
                                                    <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span>
                                                    <a class="filter_categories" data-id="112" href="#">Light Towers &
                                                        Generators</a>
                                                </li>
                                                <li>
                                                    <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span>
                                                    <a class="filter_categories" data-id="112" href="#">Power & HVAC</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Brand Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#brand" aria-expanded="true">Brand
                                    </div>
                                    <div class="sidebar-widget__body collapse show" id="brand">
                                        <ul class="list-vertical brandFilter-js">
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="lb-txt">Maserati</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="lb-txt">BMW</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="lb-txt">Mercedes</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="lb-txt">Toyota</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="lb-txt">Renault</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                        </ul>
                                        <div class="link--more">
                                            <a href="#">+More Brands</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Price Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#price" aria-expanded="true"
                                        aria-controls="price">RENT PRICE </div>
                                    <div class="sidebar-widget__body collapse show" id="price">
                                        <div class="filter-content toggle-target">
                                            <div class="prices" id="perform_price">
                                                <div id="rangeSlider"
                                                    class="noUi-target noUi-ltr noUi-horizontal noUi-txt-dir-ltr">
                                                    <div class="noUi-base">
                                                        <div class="noUi-connects">
                                                            <div class="noUi-connect"
                                                                style="transform: translate(0%, 0px) scale(1, 1);">
                                                            </div>
                                                        </div>
                                                        <div class="noUi-origin"
                                                            style="transform: translate(-1000%, 0px); z-index: 5;">
                                                            <div class="noUi-handle noUi-handle-lower" data-handle="0"
                                                                tabindex="0" role="slider" aria-orientation="horizontal"
                                                                aria-valuemin="9.0" aria-valuemax="1865.0"
                                                                aria-valuenow="9.0" aria-valuetext="9.00">
                                                                <div class="noUi-touch-area"></div>
                                                                <div class="noUi-tooltip">9.00</div>
                                                            </div>
                                                        </div>
                                                        <div class="noUi-origin"
                                                            style="transform: translate(0%, 0px); z-index: 4;">
                                                            <div class="noUi-handle noUi-handle-upper" data-handle="1"
                                                                tabindex="0" role="slider" aria-orientation="horizontal"
                                                                aria-valuemin="9.0" aria-valuemax="1865.0"
                                                                aria-valuenow="1865.0" aria-valuetext="1865.00">
                                                                <div class="noUi-touch-area"></div>
                                                                <div class="noUi-tooltip">1865.00</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="noUi-pips noUi-pips-horizontal">
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-large"
                                                            style="left: 0%;"></div>
                                                        <div class="noUi-value noUi-value-horizontal noUi-value-large"
                                                            data-value="9" style="left: 0%;">9</div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 4.16667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 8.33333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 12.5%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 16.6667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 20.8333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 25%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 29.1667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-large"
                                                            style="left: 33.3333%;"></div>
                                                        <div class="noUi-value noUi-value-horizontal noUi-value-large"
                                                            data-value="627.6666666666666" style="left: 33.3333%;">628
                                                        </div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 37.5%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 41.6667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 45.8333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 50%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 54.1667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 58.3333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 62.5%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-large"
                                                            style="left: 66.6667%;"></div>
                                                        <div class="noUi-value noUi-value-horizontal noUi-value-large"
                                                            data-value="1246.3333333333333" style="left: 66.6667%;">1246
                                                        </div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 70.8333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 75%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 79.1667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 83.3333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 87.5%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 91.6667%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-normal"
                                                            style="left: 95.8333%;"></div>
                                                        <div class="noUi-marker noUi-marker-horizontal noUi-marker-large"
                                                            style="left: 100%;"></div>
                                                        <div class="noUi-value noUi-value-horizontal noUi-value-large"
                                                            data-value="1865" style="left: 100%;">1865</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                            <div class="slide__fields">
                                                <div class="price-input">
                                                    <div class="price-text-box input-group">
                                                        <div class="input-group-prepend"><span
                                                                class="input-group-text">min price</span></div>
                                                        <input class="input-filter form-control" value="Min Price"
                                                            data-defaultvalue="9.99" name="priceFilterMinValue"
                                                            type="text" id="priceFilterMinValue">

                                                    </div>
                                                </div>
                                                <span class="dash"></span>
                                                <div class="price-input">
                                                    <div class="price-text-box input-group">
                                                        <div class="input-group-prepend"><span
                                                                class="input-group-text">max price</span></div>
                                                        <input class="input-filter form-control" value="Max Price"
                                                            data-defaultvalue="1865.00" name="priceFilterMaxValue"
                                                            type="text" id="priceFilterMaxValue">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Color Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#brand" aria-expanded="true">Color
                                    </div>
                                    <div class="sidebar-widget__body collapse show" id="brand">
                                        <ul class="list-vertical brandFilter-js">
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span data-colorhex="white" class="color-lable"
                                                        style="background-color: #FFF;"></span>
                                                    <span class="lb-txt">White (2)</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="color-lable" style="background-color: #FF0020;"></span>
                                                    <span class="lb-txt">Red (12)</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="color-lable" style="background-color: #294342;"></span>
                                                    <span class="lb-txt">Olive
                                                        (12345)</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="color-lable" style="background-color: #9B9B9B;"></span>
                                                    <span class="lb-txt">Grey (12345)</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <span class="color-lable" style="background-color: #1C1C1C;"></span>
                                                    <span class="lb-txt">Black
                                                        (12345)</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Condition Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#brand" aria-expanded="true">PRODUCT
                                        CONDITION</div>
                                    <div class="sidebar-widget__body collapse show">
                                        <div class="filter-condition">
                                            <div class="filter-condition__type selected">
                                                <a href="#">
                                                    <i class="icn">
                                                        <svg class="svg" width="28" height="28">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#new"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#new">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    New
                                                </a>
                                            </div>
                                            <span class="dash"></span>
                                            <div class="filter-condition__type">
                                                <a href="#">
                                                    <i class="icn">
                                                        <svg class="svg" width="28" height="28">
                                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#used"
                                                                href="<?php echo CONF_WEBROOT_URL; ?>images/automobile/retina/sprite.svg#used">
                                                            </use>
                                                        </svg>
                                                    </i>
                                                    Used
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Review Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#brand" aria-expanded="true">AVG.
                                        CUSTOMER REVIEW</div>
                                    <div class="sidebar-widget__body collapse show" id="review">
                                        <ul class="list-vertical brandFilter-js">
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <ul class="review">
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <span class="rating-above">& above</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <ul class="review">
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <span class="rating-above">& above</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <ul class="review">
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <span class="rating-above">& above</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                            <li>
                                                <label class="checkbox" id="brand_95">
                                                    <ul class="review">
                                                        <li>
                                                            <a href="#" tabindex="-1">
                                                                <i class="icn">
                                                                    <svg class="svg" width="16" height="16">
                                                                        <use xlink:href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star"
                                                                            href="/images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#star">
                                                                        </use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <span class="rating-above">& above</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox">
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Availability Filters[ -->
                            </div>
                        </div>
                    </div>
                </sidebar>
                <main class="collection-content">
                    <button class="btn btn-float link__filter btn--filters-control" data-trigger="collection-sidebar">
                        <i class="icn">
                            <svg class="svg">
                                <use xlink:href="/images/retina/sprite.svg#filter"
                                    href="/images/retina/sprite.svg#filter"></use>
                            </svg>
                        </i>
                    </button>
                    <div class="row justify-content-between d-md-column page-sort-wrap">
                        <div class="col m-tb-3">
                            <div class="filtered">
                                <div class="filtered__item">
                                    Chevrolet
                                    <span></span>
                                </div>
                                <div class="filtered__item">
                                    Sedan
                                    <span></span>
                                </div>
                                <div class="filtered__item">
                                    New
                                    <span></span>
                                </div>
                                <div class="filtered__item">
                                    <a href="javascript:void(0)" class="resetAll link" id="resetAll"
                                        onclick="resetListingFilter()" style="display: block;">Clear All</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto m-tb-3">
                            <div id="top-filters" class="page-sort hide_on_no_product">
                                <ul>
                                    <!-- <li class="list__item">
                                        <a href="javascript:void(0)" onclick="saveProductSearch()"
                                            class="btn btn-brand btn--filters-control saveSearch-js">
                                            <i class="icn fas fa-file-download d-md-none"></i><span class="txt">Save
                                                Search</span></a>
                                    </li> -->
                                    <li>
                                        <select id="sortBy" class="custom-select sorting-select" data-field-caption=""
                                            data-fatreq="{&quot;required&quot;:false}" name="sortBy">
                                            <option value="price_asc">Price (Low To High)</option>
                                            <option value="price_desc">Price (High To Low)</option>
                                            <option value="popularity_desc" selected="selected">Sort By Popularity
                                            </option>
                                            <option value="discounted">Most Discounted</option>
                                            <option value="rating_desc">Sort By Rating</option>
                                        </select>
                                    </li>
                                    <li>
                                        <select id="pageSize" class="custom-select sorting-select" data-field-caption=""
                                            data-fatreq="{&quot;required&quot;:false}" name="pageSize">
                                            <option value="12" selected="selected">12 Items</option>
                                            <option value="24">24 Items</option>
                                            <option value="48">48 Items</option>
                                        </select>
                                    </li>
                                    <li class="d-none d-md-block">
                                        <div class="list-grid-toggle switch--link-js">
                                            <div class="icon">
                                                <div class="icon-bar"></div>
                                                <div class="icon-bar"></div>
                                                <div class="icon-bar"></div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="listing-products -listing-products">
                        <div id="productsList" class="listing-products--grid">
                            <div class="product-listing" data-view="3">
                                <div class="items">
                                    <div class="product">
                                        <div class="product__head">
                                            <div class=product-media>
                                                <img src="/images/automobile/caravan1.png">
                                            </div>
                                            <div class="off-price">10% off</div>
                                        </div>
                                        <div class="product__body">
                                            <div class="product__body--head">
                                                <a href="#" class="product-name">Ford Thor Miramar 35.2 Ford Thor
                                                    Miramar 35.2</a>
                                                <div class="product-description">
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
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
                                                    13 seatbelts <span class="slash">|</span> Sleeps 4 <span
                                                        class="slash">|</span> 24 ft <span class="slash">|</span> 2015
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
                                                            class="line-through">$500</span> <span
                                                            class="slash-diagonal">/</span> night</span></div>
                                                <button class="btn btn-white btn-round">RENT CAR</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <ul class="pagination pagination--center">
                                <li class="pervious"><a href="javascript:void(0);"
                                        onclick="goToProductListingSearchPage(2);"><i class="fa fa-angle-left"></i><i
                                            class="fa fa-angle-left"></i></a></li>
                                <li class="selected"><a href="javascript:void(0);">1</a></li>
                                <li><a href="javascript:void(0);">2</a></li>
                                <li><a href="javascript:void(0);">3</a></li>
                                <li><a href="javascript:void(0);">4</a></li>
                                <li class="forward"><a href="javascript:void(0);"
                                        onclick="goToProductListingSearchPage(3);"><i class="fa fa-angle-right"></i><i
                                            class="fa fa-angle-right"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
    <!----- LISTING END ----->


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