<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<script>
events.viewContent();
</script>
<div id="body" class="body" role="main">
    <!-- Section Search Start -->
    <section class="page_search main-search-bar" data-close-on-click-outside="main-search-bar">
        <div class="page_search-wrapper">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Search any product</label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input placeholder="Search any product here..." data-field-caption="Name"
                            data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                    </div>
                </div>
            </div>
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Location</label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input placeholder="Choose Location..." data-field-caption="Name"
                            data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                    </div>
                </div>
            </div>
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Pickup</label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input placeholder="Add dates" data-field-caption="Name"
                            data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                    </div>
                </div>
            </div>
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label">Return</label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input placeholder="Add dates" data-field-caption="Name"
                            data-fatreq="{&quot;required&quot;:true}" type="text" name="user_name" value="">
                    </div>
                </div>
            </div>
            <a href="#" class="search-button"> <span>
                    <i class="icn icn-page_search">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#page_search"></use>
                        </svg>
                    </i>
                </span> <span class="btn-txt">Search</span> </a>
        </div>
    </section>
    <!-- Section Search End -->
    <!-- Listing Start -->
    <section class="section">
        <div class="container">
            <div class="collection-listing filter-left">
                <sidebar class="collection-sidebar" id="collection-sidebar"
                    data-close-on-click-outside="collection-sidebar">
                    <div class="filters  productFilters-js">
                        <div class="filters__ele">
                            <div class="filters_body" id="filters_body--js">
                                <!--Category Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#category" aria-expanded="true"
                                        aria-controls="category"> Catagories </div>
                                    <div class="sidebar-widget__body collapse show" id="category">
                                        <div id="accordian" class="cat-accordion toggle-target">
                                            <ul>
                                                <li> <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span> <a class="filter_categories"
                                                        data-id="113" href="#">Dresses</a> </li>
                                                <li> <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span> <a class="filter_categories"
                                                        data-id="109" href="#">Tops</a> </li>
                                                <li> <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span> <a class="filter_categories"
                                                        data-id="112" href="#">Sweaters + Sweatshirts</a> </li>
                                                <li> <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span> <a class="filter_categories"
                                                        data-id="112" href="#">Sausages, Bacon & Salami</a> </li>
                                                <li> <span class="acc-trigger" ripple="ripple"
                                                        ripple-color="#000"></span> <a class="filter_categories"
                                                        data-id="112" href="#">Fish & Seafood</a> </li>
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
                                                <label class="checkbox brand" id="brand_95"> <i
                                                        class="input-helper"></i><span class="lb-txt">Peppermint</span>
                                                    <input name="brands" data-id="brand_95" value="95"
                                                        data-title="Apple" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox brand" id="brand_117"> <i
                                                        class="input-helper"></i><span class="lb-txt">Allen Solly
                                                        Woman</span>
                                                    <input name="brands" data-id="brand_117" value="117"
                                                        data-title="Avast" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox brand" id="brand_130"> <i
                                                        class="input-helper"></i><span class="lb-txt">Vero Moda</span>
                                                    <input name="brands" data-id="brand_130" value="130"
                                                        data-title="Beats" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox brand" id="brand_116"> <i
                                                        class="input-helper"></i><span class="lb-txt">American
                                                        Eye</span>
                                                    <input name="brands" data-id="brand_116" value="116"
                                                        data-title="Candle" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox brand" id="brand_125"> <i
                                                        class="input-helper"></i><span class="lb-txt">Bollywood
                                                        Vogue</span>
                                                    <input name="brands" data-id="brand_125" value="125"
                                                        data-title="Consoles" type="checkbox"> </label>
                                            </li>
                                        </ul>
                                        <div class="text-right"> <a href="javascript:void(0)" onclick="brandFilters()"
                                                class="link-plus">View More </a> </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!--Price Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#price" aria-expanded="true"
                                        aria-controls="price"> Selling Price ($) </div>
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
                                                                class="input-group-text">$</span></div>
                                                        <input class="input-filter form-control" value="9"
                                                            data-defaultvalue="9.99" name="priceFilterMinValue"
                                                            type="text" id="priceFilterMinValue">
                                                    </div>
                                                </div> <span class="dash"> - </span>
                                                <div class="price-input">
                                                    <div class="price-text-box input-group">
                                                        <div class="input-group-prepend"><span
                                                                class="input-group-text">$</span></div>
                                                        <input class="input-filter form-control" value="1865"
                                                            data-defaultvalue="1865.00" name="priceFilterMaxValue"
                                                            type="text" id="priceFilterMaxValue">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ] -->
                                <!-- Condition Filters[ -->
                                <div class="sidebar-widget">
                                    <div class="sidebar-widget__head" data-target="#condition" aria-expanded="true">
                                        Color </div>
                                    <div class="sidebar-widget__body collapse show" id="condition">
                                        <ul class="list-vertical">
                                            <li>
                                                <label class="checkbox condition" id="condition_1"> <i
                                                        class="input-helper"></i> <span class="lb-txt">Black</span>
                                                    <input value="1" name="conditions" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox condition" id="condition_1"> <i
                                                        class="input-helper"></i> <span class="lb-txt">Blue</span>
                                                    <input value="1" name="conditions" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox condition" id="condition_1"> <i
                                                        class="input-helper"></i> <span class="lb-txt">Navy Blue</span>
                                                    <input value="1" name="conditions" type="checkbox"> </label>
                                            </li>
                                            <li>
                                                <label class="checkbox condition" id="condition_1"> <i
                                                        class="input-helper"></i> <span class="lb-txt">Green</span>
                                                    <input value="1" name="conditions" type="checkbox"> </label>
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
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"
                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#filter"></use>
                            </svg>
                        </i> </button>
                    <div class="row align-items-center justify-content-between flex-column flex-md-row page-sort-wrap">
                        <div class="col mb-3 mb-md-0">
                            <div class="total-products">
                                <h4>
                                    <span class="hide_on_no_product">
                                        <small class="text-show">
                                            <span id="total_records">Showing 1 - 18 of 34 result</small>
                                    </span>
                                </h4>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div id="top-filters" class="page-sort hide_on_no_product">
                                <ul>
                                    <!-- <li class="list__item">
                                        <a href="javascript:void(0)" onclick="saveProductSearch()"
                                            class="btn btn-outline-bottom btn-sm btn--filters-control saveSearch-js"> <i
                                                class="icn fas fa-file-download d-md-none"></i><span class="txt">Save
                                                Search</span></a>
                                    </li> -->
                                    <li>
                                        <select id="sortBy" class="custom-select sorting-select" data-field-caption=""
                                            data-fatreq="{&quot;required&quot;:false}" name="sortBy">
                                            <option value="price_asc">Price (Low To High)</option>
                                            <option value="price_desc">Price (High To Low)</option>
                                            <option value="popularity_desc" selected="selected">Default</option>
                                            <option value="discounted">Most Discounted</option>
                                            <option value="rating_desc">Sort By Rating</option>
                                        </select>
                                    </li>
                                    <li>
                                        <select id="pageSize" class="custom-select sorting-select" data-field-caption=""
                                            data-fatreq="{&quot;required&quot;:false}" name="pageSize">
                                            <option value="12" selected="selected">Sort by Popularity</option>
                                            <option value="24">24 Items</option>
                                            <option value="48">48 Items</option>
                                        </select>
                                    </li>
                                    <li class="d-none d-md-block">
                                        <div class="listing-grid-toggle switch--link-js">
                                            <div class="icon"> <span>
                                                    <a href="#">
                                                        <i class="icn icn-show_grid">
                                                            <svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_grid"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_grid">
                                                                </use>
                                                            </svg>
                                                        </i>
                                                    </a>
                                                </span> <span>
                                                    <a href="#">
                                                        <i class="icn icn-show_list">
                                                            <svg class="svg">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list"
                                                                    href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#show_list">
                                                                </use>
                                                            </svg>
                                                        </i>
                                                    </a>
                                                </span> </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="listing-products -listing-products">
                        <div id="productsList" class="listing-products--grid">
                            <div class="product-listing" data-view="4">
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_1.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_2.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_3.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_1.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_2.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_3.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_1.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_2.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_3.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_1.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_2.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="items">
                                    <div class="product_card">
                                        <div class="product_card-body">
                                            <div class="product_media">
                                                <a href="#"><img src="/images/product_3.png"></a>
                                            </div>
                                        </div>
                                        <div class="product_card-footer">
                                            <p class="product_name">Rust Sweater Dress</p> <a href="#"
                                                class="product_title">Love, Orange by Whitney Port</a>
                                            <h4 class="product_price">$199 <span>/ day</span></h4>
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
    <!-- Listing End -->
</div>