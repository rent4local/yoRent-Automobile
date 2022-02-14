<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmBuyProduct->setFormTagAttribute('class', 'form');
$buyQuantity = $frmBuyProduct->getField('quantity');
$buyQuantity->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
$buyQuantity->addFieldTagAttribute('data-page', 'product-view');

$rentalAvailableDate = date('Y-m-d');
$selectedFullfillmentType = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : FatApp::getConfig('CONF_DEFAULT_LOCATION_CHECKOUT_TYPE', FatUtility::VAR_INT, 1);
if ( $selectedFullfillmentType == Shipping::FULFILMENT_SHIP && ($fulfillmentType == Shipping::FULFILMENT_ALL || $fulfillmentType == Shipping::FULFILMENT_SHIP)) {
   $rentalAvailableDate = date('Y-m-d', strtotime('+ ' . FatUtility::int($minShipDuration) . ' days', strtotime($rentalAvailableDate)));
}

if (strtotime($product['sprodata_rental_available_from']) > strtotime($rentalAvailableDate)) {
    $rentalAvailableDate = $product['sprodata_rental_available_from'];
}

if (!empty($extendedOrderData)) {
    $rentalAvailableDate = $extendedOrderData['opd_rental_end_date'];
}

$availableForSale = false;
if ($product['selprod_active'] == applicationConstants::ACTIVE) {
    $availableForSale = true;
}

$availableForRent = false;
if ($product['sprodata_rental_active'] == applicationConstants::ACTIVE) {
    $availableForRent = true;
}

?>

<section class="product-details--js">
    <div class="breadcrumbs-bar">
        <div class="container">
            <div class="breadcrumbs">
                <?php $this->includeTemplate('_partial/custom/header-breadcrumb.php');  ?>
            </div>
        </div>
    </div>
</section>

<!-- [ PRODUCT IMAGES SECTION START -->
<?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/product-images.php'); ?>
<!-- ] --->

<!-- [ PRODUCT DETAILS SECTION START HERE -->
<?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/product-details.php'); ?>
<!-- ] --->

<?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/shipping-rates.php'); ?>

<?php if (!empty($upsellProducts) && ALLOW_SALE) {
?>
    <section class="section">
        <?php
        $dataToSend['products'] = $upsellProducts;
        $dataToSend['sectionId'] = 1;
        $dataToSend['siteLangId'] = $siteLangId;
        $dataToSend['compProdCount'] = (isset($compProdCount)) ? $compProdCount  : 0;
        $dataToSend['prodInCompList'] = (isset($prodInCompList)) ? $prodInCompList  : [];
        $dataToSend['comparedProdSpecCatId'] = (isset($comparedProdSpecCatId)) ? $comparedProdSpecCatId  : 0;
        $dataToSend['heading'] = Labels::getLabel('LBL_Buy_Together_Products', $siteLangId);
        echo $this->includeTemplate('products/products-in-slider.php', $dataToSend);
        ?>
    </section>
<?php } ?>

<!-- [ REVIEWS SECTION GOES HERE.... -->
<?php
if (FatApp::getConfig("CONF_ALLOW_REVIEWS", FatUtility::VAR_INT, 0)) {
    echo $frmReviewSearch->getFormHtml();
    $this->includeTemplate('_partial/product-reviews.php', array('reviews' => $reviews, 'siteLangId' => $siteLangId, 'product_id' => $product['product_id'], 'canSubmitFeedback' => $canSubmitFeedback,'ratingAspects' => $ratingAspects), false);
}
?>
<!-- ] -->

<?php if ($relatedProductsRs) { ?>
    <section class="section collection--product">
        <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/related-products.php'); ?>
    </section>
<?php } ?>

<?php if ($recommendedProducts) { ?>
    <section class="section collection--product">
        <?php include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/recommended-products.php'); ?>
    </section>
<?php } ?>
<section class="section collection--product" id="recentlyViewedProductsDiv"></section>



<!-- compare products lists starts here-->
<div id="compare_product_list_js"></div>
<!-- compare products lists ends here-->
<?php if (FatApp::getConfig("CONF_ENABLE_PRODUCT_COMPARISON", FatUtility::VAR_INT, 1) && 0 < $compProdCount) {
?>
    <script type="text/javascript">
        var data = 'detail_page=1';
        fcom.ajax(fcom.makeUrl('CompareProducts', 'listing'), data, function(res) {
            $("#compare_product_list_js").html(res);
            $('body').addClass('is-compare-visible');
        });
    </script>
<?php }
?>

<script>
    var disableDates = <?php echo json_encode($unavailableDates); ?>;
    var datepickerOption = {
        autoClose: true,
        startDate: '<?php echo $rentalAvailableDate; ?>',
        <?php if ($product['sprodata_duration_type'] == applicationConstants::RENT_TYPE_WEEK) { ?>
            batchMode: 'week-range',
            minDays: <?php echo $product['sprodata_minimum_rental_duration'] * 7; ?>,
        <?php } else if ($product['sprodata_duration_type'] == applicationConstants::RENT_TYPE_MONTH) {  ?>
            batchMode: 'month-range',
            minDays: <?php echo $product['sprodata_minimum_rental_duration'] * 30; ?>,
        <?php } else { ?>
            minDays: <?php echo $product['sprodata_minimum_rental_duration']; ?>,
        <?php } ?>
        showShortcuts: false,
        customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
        customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
        stickyMonths: true,
        inline: true,
        container: '.rent-calender',
        beforeShowDay: function(t) {
            var _class = '';
            var valid = false;
            var _tooltip =
                '<?php echo Labels::getLabel('LBL_Product_is_not_available_for_this_date', $siteLangId); ?>';
            var string = moment(t).format('YYYY-MM-DD');
            if (disableDates.indexOf(string) == -1) {
                var valid = true;
                var _tooltip = '';
            }
            return [valid, _class, _tooltip];
        },
    }
    $('input[name=rental_dates]').dateRangePicker(datepickerOption).bind('datepicker-change', function(event, obj) {
        $('.continue-rental-cart--js').data('skipservices', '0');
        $('.service-link--js').data('displaybox', '1');
        var selectedDates = obj.value;
        var datesArr = selectedDates.split(" to ");
        $('input[name="rental_start_date"]').val(datesArr[0]);
        $('input[name="rental_end_date"]').val(datesArr[1]);
        getRentalDetails();
    });
</script>



<script type="text/javascript">
    var mainSelprodId = <?php echo $product['selprod_id']; ?>;
    var layout = '<?php echo CommonHelper::getLayoutDirection(); ?>';

    $("document").ready(function() {
        recentlyViewedProducts(<?php echo $product['selprod_id']; ?>);
        /*zheight = $(window).height() - 180; */
        zwidth = $(window).width() / 3 - 15;

        if (layout == 'rtl') {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                position: 'left'
            });
        } else {
            $('.xzoom, .xzoom-gallery').xzoom({
                zoomWidth: zwidth,
                /*zoomHeight: zheight,*/
                title: true,
                tint: '#333',
                Xoffset: 2
            });
        }

        window.setInterval(function() {
            var scrollPos = $(window).scrollTop();
            if (scrollPos > 0) {
                setProductWeightage('<?php echo $product['selprod_code']; ?>');
            }
        }, 5000);

    });
</script>
<script>
    $(document).ready(function() {
        $("#btnAddToCart").addClass("quickView");
        $('#slider-for').slick(getSlickGallerySettings(false));
        $('#slider-nav').slick(getSlickGallerySettings(true, '<?php echo CommonHelper::getLayoutDirection(); ?>'));

        /* for toggling of tab/list view[ */
        $('.list-js').hide();
        $('.view--link-js').on('click', function(e) {
            $('.view--link-js').removeClass("btn--active");
            $(this).addClass("btn--active");
            if ($(this).hasClass('list')) {
                $('.tab-js').hide();
                $('.list-js').show();
            } else if ($(this).hasClass('tab')) {
                $('.list-js').hide();
                $('.tab-js').show();
            }
        });
        /* ] */

        $(".nav-scroll-js").click(function(event) {
            event.preventDefault();
            var full_url = this.href;
            var parts = full_url.split("#");
            var trgt = parts[1];
            /* var target_offset = $("#" + trgt).offset();
             
             var target_top = target_offset.top - $('#header').height();
             $('html, body').animate({
             scrollTop: target_top
             }, 800); */
            $('html, body').animate({
                scrollTop: parseInt($("#" + trgt).position().top) + parseInt($("#scrollUpTo-js")
                    .position().top)
            }, 800);

        });
        $('.nav-detail-js li a').click(function() {
            $('.nav-detail-js li a').removeClass('is-active');
            $(this).addClass('is-active');
        });

        var headerHeight = $("#header").height();
        $(".nav-detail-js").css('top', headerHeight);

    });
</script>
<!-- Product Schema Code -->
<script>
    $('.spcification-image--js').magnificPopup({
        type: 'image'
    });
</script>
<script>
    $('.sizechart-image--js').magnificPopup({
        type: 'image'
    });
</script>
<?php
if (FatApp::getConfig("CONF_DEFAULT_SCHEMA_CODES_SCRIPT", FatUtility::VAR_STRING, '')) {
    $image = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product['product_id']);
?>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Product",
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "<?php echo round(FatUtility::convertToType($reviews['prod_rating'], FatUtility::VAR_FLOAT), 1); ?>",
                "reviewCount": "<?php echo FatUtility::int($reviews['totReviews']); ?>"
            },
            "description": "<?php echo CommonHelper::renderHtml($product['product_description']); ?>",
            "name": "<?php echo html_entity_decode($product['selprod_title']); ?>",
            "image": "<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'product', array($product['product_id'], 'THUMB', 0, $image['afile_id'])), CONF_IMG_CACHE_TIME, '.jpg'); ?>",
            "offers": {
                "@type": "Offer",
                "availability": "http://schema.org/InStock",
                "price": "<?php echo $product['theprice']; ?>",
                "priceCurrency": "<?php echo CommonHelper::getCurrencyCode(); ?>"
            }
        }
    </script>
<?php } ?>
<!-- End Product Schema Code -->

<!--Here is the facebook OG for this product  -->
<?php echo $this->includeTemplate('_partial/shareThisScript.php'); ?>
<script>
    $('.readmore--js').on('click', function() {
        $(this).parents('.description').find('.product-details__text').toggleClass('detail-expand--js');
        if ($(this).parents('.description').find('.product-details__text').hasClass('detail-expand--js') == true) {
            $(this).text('<?php echo Labels::getLabel('LBL_Read_Less', $siteLangId) ?>');
        } else {
            $(this).text('<?php echo Labels::getLabel('LBL_Read_More', $siteLangId) ?>');
        }
    });
</script>
<script>
    $(document).on('click', '.tabs-product--js', function() {
        var productType = $(this).data('producttype');
        $('.tabs-product--js').removeClass("current");
        $(this).addClass("current");
        if (productType == <?php echo applicationConstants::PRODUCT_FOR_RENT; ?>) {
            $('.rental-fields--js').show();
            $('.sale-fields--js').hide();
        } else {
            $('.rental-fields--js').hide();
            $('.sale-fields--js').show();
        }
    });
    $('#aditional-services-js').insertAfter('#recentlyViewedProductsDiv');
    $('#penaltyModal').insertAfter('#recentlyViewedProductsDiv');
</script>