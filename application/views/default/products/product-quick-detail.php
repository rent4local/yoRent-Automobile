<?php
$rentalAvailableDate = date('Y-m-d');
$selectedFullfillmentType = (isset($_COOKIE['locationCheckoutType'])) ? FatUtility::int($_COOKIE['locationCheckoutType']) : Shipping::FULFILMENT_SHIP;
if ( $selectedFullfillmentType == Shipping::FULFILMENT_SHIP && ($fulfillmentType == Shipping::FULFILMENT_ALL || $fulfillmentType == Shipping::FULFILMENT_SHIP)) {
   $rentalAvailableDate = date('Y-m-d', strtotime('+ ' . FatUtility::int($minShipDuration) . ' days', strtotime($rentalAvailableDate)));
}

if (strtotime($product['sprodata_rental_available_from']) > strtotime($rentalAvailableDate)) {
    $rentalAvailableDate = $product['sprodata_rental_available_from'];
}

?>

<div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"> <span class="primary-color"><?php echo $product['selprod_title']; ?></span></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php
        $frmBuyProduct->setFormTagAttribute('class', 'form form--horizontal');
        $qtyField = $frmBuyProduct->getField('quantity');
        $qtyField->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
        $qtyField->addFieldTagAttribute('data-page', 'product-view');
        $qtyField->addFieldTagAttribute('data-min-qty', $product['selprod_min_order_qty']);
        $qtyFieldName = $qtyField->getCaption();
        $qtyField->value = $product['sprodata_minimum_rental_quantity'];

        $rentalStartDateFld = $frmBuyProduct->getField('rental_dates');
        $rentalStartDateFld->addFieldTagAttribute('class', 'rental_datescalendar--js field--calender');

        echo $frmBuyProduct->getFormTag(); ?>



        <div class="modal-body">
            <div class="product-detail">
                <div class="detail--js">
                    <div class="detial-content scroll scroll-y detial-content--js" style="overflow-y : auto"></div>
                </div>


                <div class="product-details--js">
                    <div class="product-detail_options">
                        <?php if (!empty($optionsFinalArr)) { ?>
                            <div class="product-detail_options_variations">
                                <label class="label d-flex justify-content-between">
                                    <?php echo Labels::getLabel('LBL_Options', $siteLangId); ?>
                                    <?php if (!empty($product['size_chart'])) {
                                        $chartFileRow = $product['size_chart'];
                                        $sizeChartUrl = CommonHelper::generateUrl('image', 'productSizeChart', array($chartFileRow['afile_record_id'], "ORIGINAL", $chartFileRow['afile_id'])); ?>
                                        <a href="<?php echo $sizeChartUrl; ?>" class="size-chart popup-image--js">
                                            <i class="icn">
                                                <svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/fashion/retina/sprite-front.svg#size-chart">
                                                    </use>
                                                </svg>
                                            </i>
                                            <?php echo Labels::getLabel('LBL_SIZE_CHART', $siteLangId); ?>
                                        </a>
                                    <?php } ?>
                                </label>
                                <div class="dropdown dropdown-options">
                                    <button class="btn btn-outline-gray dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false"> <span><span class="colors" style="background-color:<?php echo $optionsFinalArr[$product['selprod_id']]['color_code']; ?>;"></span> <?php echo $optionsFinalArr[$product['selprod_id']]['value']; ?> </span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-anim">
                                        <ul class="nav nav-block scroll scroll-y no-url--js">
                                            <?php foreach ($optionsFinalArr as $optId => $optRow) { ?>
                                                <li class="nav__item  <?php echo ($optId == $product['selprod_id']) ? " is-active " : " "; ?>">
                                                    <a title="<?php echo $optRow['value']; ?>" class="dropdown-item nav__link " href="javascript:void(0);" onClick="quickDetail(<?php echo $optId; ?>);">
                                                        <span class="color-dot" style="background-color: <?php echo $optRow['color_code']; ?>;"></span><?php echo $optRow['value']; ?>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="row ">
                        <div class="col-md-6">
                            <div class="product-detail_options_quantity">
                                <div class="qty-wrapper">
                                    <label class="label"><?php echo $qtyFieldName; ?></label>
                                    <div class="quantity quantity-theme" data-stock="<?php echo $product['selprod_stock']; ?>">
                                        <span class="decrease decrease-js not-allowed">
                                            <i class="fas fa-minus"></i>
                                        </span>
                                        <div class="qty-input-wrapper qty-wrapper--js">
                                            <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                        </div>
                                        <span class=" increase increase-js">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4 field--calender-daterange--js date-selector">
                            <div class="caption-wraper">
                                <label class="label">
                                    <?php echo $frmBuyProduct->getField('rental_dates')->getCaption(); ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frmBuyProduct->getFieldHtml('rental_dates'); ?>
                                    <?php echo $frmBuyProduct->getFieldHtml('rental_start_date'); ?>
                                    <?php echo $frmBuyProduct->getFieldHtml('rental_end_date'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="py-5">
                        <?php if (!empty($addonProducts)) { ?>

                            <h6><?php echo Labels::getLabel('LBL_Addon\'s_to_go_with_your_rental_order!', $siteLangId) ?>
                            </h6>
                            <div class="rental-addons rental-cart-tbl-js">
                                <ul class="list-cart">
                                    <?php foreach ($addonProducts as $rentalAddon) { ?>
                                        <li class="">
                                            <label class="rental-addons_list" for="">
                                                <div class="cell cell_checkbox">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="check_addons" id="check_addons" data-attrname="rental_addons[<?php echo $rentalAddon['selprod_id'] ?>]" data-rentalqty="1">
                                                    </div>
                                                </div>

                                                <div class="cell cell_product">
                                                    <div class="product-profile">
                                                        <div class="product-profile__thumbnail">
                                                            <img src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('Image', 'addonProduct', array($rentalAddon['selprod_id'], 'MINI', 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $rentalAddon['selprod_title']; ?>">
                                                        </div>
                                                        <div class="product-profile__data">
                                                            <div class="title">
                                                                <?php echo html_entity_decode($rentalAddon['selprod_title']); ?>
                                                            </div>
                                                            <a href="javascript:void(0);" onClick="viewServiceDetails(<?php echo $rentalAddon['selprod_id']; ?>)" class="link">
                                                                <?php echo Labels::getLabel('LBL_Details', $siteLangId); ?>
                                                            </a>

                                                            <div class="service-details--js" id="service_detail_<?php echo $rentalAddon['selprod_id']; ?>" style="display:none;">
                                                                <h6><?php echo html_entity_decode($rentalAddon['selprod_title']); ?>
                                                                </h6>
                                                                <?php echo html_entity_decode(nl2br($rentalAddon['selprod_rental_terms'])); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="cell cell_price">
                                                    <div class="price">
                                                        <strong><?php echo CommonHelper::displayMoneyFormat($rentalAddon['selprod_price']); ?></strong>
                                                    </div>
                                                </div>
                                            </label>

                                        </li>

                                    <?php } ?>
                                </ul>
                            </div>

                        <?php } ?>
                    </div>
                    <!-- ] -->


                </div>
            </div>
        </div>
        <div class="modal-footer">
            <div class="addon-actions--js">
                <?php
                $btnAddToCartFld = $frmBuyProduct->getField('btnAddToCart');
                $btnAddToCartFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide quickView');
                echo $frmBuyProduct->getFieldHtml('selprod_id');
                echo $frmBuyProduct->getFieldHtml('fulfillmentType');
                echo $frmBuyProduct->getFieldHtml('product_for');
                echo $frmBuyProduct->getFieldHtml('extend_order');
                echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                ?>
            </div>
            <a href="javascript:void(0);" class="btn btn-brand close-detail--js" style="display: none;"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>

            </form>
            <?php echo $frmBuyProduct->getExternalJs(); ?>

        </div>


    </div>
</div>
<script>
    var mainSelprodId = '<?php echo $product['selprod_id']; ?>';
    var disableDates = <?php echo json_encode($unavailableDates); ?>;
    var datepickerOption = {
        autoClose: true,
        startDate: '<?php echo $rentalAvailableDate; ?>',
        <?php if ($product['sprodata_duration_type'] == applicationConstants::RENT_TYPE_WEEK) { ?>
        batchMode: 'week-range',
        minDays: <?php echo $product['sprodata_minimum_rental_duration'] * 7;?>,
        <?php } else if($product['sprodata_duration_type'] == applicationConstants::RENT_TYPE_MONTH) {  ?>
        batchMode: 'month-range',
        minDays: <?php echo $product['sprodata_minimum_rental_duration'] * 30;?>,
        <?php } else { ?>
        minDays: <?php echo $product['sprodata_minimum_rental_duration'];?>,
        <?php } ?>
        showShortcuts: false,
        customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
        customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
        stickyMonths: true,
        inline: true,
        container: '.field--calender-daterange--js',
        beforeShowDay: function(t) {
            var _class = '';
            var valid = false;
            var _tooltip =
                '<?php echo Labels::getLabel('LBL_Product_is_not_available_for_this_date', $siteLangId);?>';
            var string = moment(t).format('YYYY-MM-DD');
            if (disableDates.indexOf(string) == -1) {
                var valid = true;
                var _tooltip = '';
            }
            return [valid, _class, _tooltip];
        },
    }
    $('.rental_datescalendar--js').dateRangePicker(datepickerOption).bind('datepicker-change', function(event, obj) {
        $('.continue-rental-cart--js').data('skipservices', '0');
        $('.service-link--js').data('displaybox', '1');
        var selectedDates = obj.value;
        var datesArr = selectedDates.split(" to ");
        $('input[name="rental_start_date"]').val(datesArr[0]);
        $('input[name="rental_end_date"]').val(datesArr[1]);
        getRentalDetails();
    });
</script>
<style>
    .product-profile__thumbnail {
        width: 50px;
        height: 50px;
    }
</style>
<script>
    function viewServiceDetails(addonId) {
        var details = $('#service_detail_' + addonId).html();
        $('.detial-content--js').html(details);
        $('.detail--js').css('display', 'block');
        $('.product-details--js').hide();
        $('.addon-actions--js').hide();
        $('.close-detail--js').show();
    }

    $(document).ready(function() {
        $('.close-detail--js').on('click', function(e) {
            e.preventDefault();
            $('.detial-content--js').html(' ');
            $('.close-detail--js').hide();
            $('.product-details--js').show();
            $('.addon-actions--js').show();
            $('.detail--js').css('display', 'none');
        });
    });
</script>