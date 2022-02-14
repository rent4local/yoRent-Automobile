<?php
$rentalAvailableDate = date('Y-m-d H:i:s', strtotime('+ 1 days', strtotime(date('Y-m-d'))));

if ($fulfillmentType == Shipping::FULFILMENT_ALL || $fulfillmentType == Shipping::FULFILMENT_PICKUP) {
    $timeSlotHours = FatApp::getConfig("CONF_TIME_SLOT_ADDITION", FatUtility::VAR_INT, 2);
    $rentalAvailableDate = date('Y-m-d H:i:s', strtotime('+ ' . $timeSlotHours . ' hours', strtotime(date('Y-m-d H:i:s'))));
} else {
    $rentalAvailableDate = date('Y-m-d H:i:s', strtotime('+ ' . $minShipDuration . ' days', strtotime(date('Y-m-d'))));
}
$rentalAvailableDate = date("Y-m-d H:i:s", ceil(strtotime($rentalAvailableDate) / (60 * 30)) * (60 * 30));
$addTimeToDate = 0;

if (strtotime($product['selprod_available_from']) > strtotime($rentalAvailableDate)) {
    $rentalAvailableDate = $product['selprod_available_from'];
}
?>
<div class="modal-dialog modal-dialog-centered" role="document" id="rent-product-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                <a title="<?php echo $product['selprod_title']; ?>" href="<?php echo !isset($product['promotion_id']) ? UrlHelper::generateUrl('Products', 'View', array($product['selprod_id'])) : UrlHelper::generateUrl('Products', 'track', array($product['promotion_record_id'])) ?>"><?php echo $product['selprod_title']; ?>
                </a>

            </h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php
            if ($product['in_stock']) {
                if (true == $displayProductNotAvailableLable && array_key_exists('availableInLocation', $product) && 0 == $product['availableInLocation']) {
            ?>

                    <div class="not-available m-5">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                            </use>
                        </svg><?php echo Labels::getLabel('LBL_NOT_AVAILABLE_FOR_YOUR_LOCATION', $siteLangId); ?>
                    </div>
                <?php
                } else {
                    $frmBuyProduct->setFormTagAttribute('class', 'form');
                    echo $frmBuyProduct->getFormTag();
                    $qtyField = $frmBuyProduct->getField('quantity');
                    $qtyField->addFieldTagAttribute('class', 'qty-input cartQtyTextBox productQty-js');
                    $qtyField->addFieldTagAttribute('data-page', 'product-view');
                    $qtyField->value = ($product['is_sell'] > 0 && ALLOW_SALE) ? $product['selprod_min_order_qty'] : 1;
                    $qtyField->addFieldTagAttribute('data-min-qty', $product['selprod_min_order_qty']);
                    $fld = $frmBuyProduct->getField('btnAddToCart');
                    $fld->addFieldTagAttribute('class', 'btn btn-brand btn-block');
                    $qtyFieldName = $qtyField->getCaption();
                ?>
                    <?php if ($product['is_rent'] > 0 && ALLOW_RENT > 0) { ?>
                        <div class="row align-items-end rental-fields--js">
                            <?php if ($product['sprodata_rental_stock'] == 0) { ?>
                                <div class="col-md-12 mb-3">
                                    <div class="bg-gray p-4 rounded">
                                        <h6 class="m-0">
                                            <?php echo Labels::getLabel('LBL_SOLD_OUT', $siteLangId); ?>
                                        </h6>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="stats-list bg-gray rounded mb-4  p-4">
                        <?php if ($product['sprodata_rental_stock'] > 0) { ?>
                            
                                <ul>
                                    <li>
                                        <span class="label"><?php echo Labels::getLabel('LBL_Rental_Price', $siteLangId); ?></span> <span class="value"><?php echo CommonHelper::displayMoneyFormat($product['rent_price']) . ' / ' .  $rentalTypeArr[$product['sprodata_duration_type']]; ?></span>
                                    </li>
                                    <li>
                                        <span class="label"><?php echo Labels::getLabel('LBL_Rental_Security', $siteLangId); ?></span> <span class="value"><?php echo CommonHelper::displayMoneyFormat($product['sprodata_rental_security']); ?></span>
                                    </li>
                                    <?php if (!empty($shippingRateRow)) { ?>
                                        <li>
                                            <span class="label"><?php echo Labels::getLabel('LBL_Estimated_Shipping_Charges', $siteLangId); ?></span> <span class="value"><?php echo CommonHelper::displayMoneyFormat($shippingRateRow['shiprate_cost']); ?></span>
                                        </li>

                                    <?php } ?>
                                </ul>
                             
                        <?php } ?>
                    </div>

                    <?php
                    if ($product['is_rent'] > 0 && ALLOW_RENT > 0) {
                        $rentalStartDateFld = $frmBuyProduct->getField('rental_start_date');
                        $rentalEndDateFld = $frmBuyProduct->getField('rental_end_date');
                        $rentalStartDateName = $rentalStartDateFld->getCaption();
                        $rentalEndDateName = $rentalEndDateFld->getCaption();
                        $rentalStartDateFld->addFieldTagAttribute('class', 'rental_start_datetime');
                        $rentalEndDateFld->addFieldTagAttribute('class', 'rental_end_datetime');
                    ?>
                        <div class="rental-wrapper rental-fields--js">
                            <div class="row">
                                <div class="col-md-6" <?php echo ($product['sprodata_rental_stock'] == 0) ? 'style="display:none;"' : ''; ?>>
                                    <div class="field-set">
                                        <label class="field_label"><?php echo $rentalStartDateName; ?></label>
                                        <?php echo $frmBuyProduct->getFieldHtml('rental_start_date'); ?>
                                    </div>
                                </div>

                                <div class="col-md-6" <?php echo ($product['sprodata_rental_stock'] == 0) ? 'style="display:none;"' : ''; ?>>
                                    <div class="field-set">
                                        <label class="field_label"><?php echo $rentalEndDateName; ?></label>
                                        <?php echo $frmBuyProduct->getFieldHtml('rental_end_date'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="<?php echo (($product['is_rent'] && ALLOW_RENT) && ($product['sprodata_rental_stock'] == 0)) ? 'sale-products--js hide-sell-section' : ''; ?>">
                        <label class="field_label"><?php echo $qtyFieldName; ?></label>
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="qty-wrapper">
                                    <div class="quantity" data-stock="<?php echo $product['selprod_stock']; ?>">
                                        <span class="decrease decrease-js not-allowed"><i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus">
                                                    </use>
                                                </svg>
                                            </i></span>
                                        <div class="qty-input-wrapper" data-stock="<?php echo $product['selprod_stock']; ?>">
                                            <?php echo $frmBuyProduct->getFieldHtml('quantity'); ?>
                                        </div>
                                        <span class="increase increase-js"><i class="icn">
                                                <svg class="svg" width="16px" height="16px">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus">
                                                    </use>
                                                </svg>
                                            </i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="buy-group">
                                    <?php
                                    if (strtotime($product['selprod_available_from']) <= strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) {
                                        // echo $frmBuyProduct->getFieldHtml('btnProductBuy');
                                        echo $frmBuyProduct->getFieldHtml('btnAddToCart');
                                    }
                                    echo $frmBuyProduct->getFieldHtml('selprod_id');
                                    echo $frmBuyProduct->getFieldHtml('product_for');
                                    echo $frmBuyProduct->getFieldHtml('extend_order');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>


                    </form>
                <?php
                    echo $frmBuyProduct->getExternalJs();
                }
            } else {
                ?>
                <div class="tag--soldout tag--soldout-full">
                    <h3 class=""><?php echo Labels::getLabel('LBL_Sold_Out', $siteLangId); ?></h3>
                    <p class=""><?php echo Labels::getLabel('LBL_This_item_is_currently_out_of_stock', $siteLangId); ?></p>
                </div>
            <?php } ?>
            <?php if (strtotime($product['selprod_available_from']) > strtotime(FatDate::nowInTimezone(FatApp::getConfig('CONF_TIMEZONE'), 'Y-m-d'))) { ?>
                <div class="tag--soldout tag--soldout-full">
                    <h3 class=""><?php echo Labels::getLabel('LBL_Not_Available', $siteLangId); ?></h3>
                    <p class=""><?php echo str_replace('{available-date}', FatDate::Format($product['selprod_available_from']), Labels::getLabel('LBL_This_item_will_be_available_from_{available-date}', $siteLangId)); ?></p>
                </div>
            <?php } ?>
            <!-- ] -->


        </div>
    </div>


    <script>
        var disableDates = <?php echo json_encode($unavailableDates); ?>;
        var extendOrder = <?php echo (empty($extendedOrderData) ? 0 : 1) ?>;
        var availableDate = new Date('<?php echo $rentalAvailableDate; ?>');
        var rentalMinEndDate = new Date(availableDate.getTime() + <?php echo $addTimeToDate; ?>);
        $('.rental_start_datetime').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm',
            stepMinute: 30,
            defaultDate: availableDate,
            minDate: availableDate,
            beforeShowDay: function(date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                if (disableDates.indexOf(string) == -1) {
                    return [disableDates.indexOf(string) == -1, ''];
                } else {
                    return [disableDates.indexOf(string) == -1, 'rental-unavailable-date'];
                }
            },
            onSelect: function(select_date) {
                getRentalDetails();
                var selectedDate = new Date(select_date);
                var msecsInAHour = 60 * 60 * 1000;
                var endDate = new Date(selectedDate.getTime() + msecsInAHour);
                console.log(endDate);
                var event = new Date(endDate);
                var time = event.toLocaleTimeString('it-IT');
                $(".rental_end_datetime").datetimepicker(
                    "option", {
                        minDate: new Date(endDate),
                        minDateTime: new Date(endDate),
                        defaultDate: new Date(endDate),
                    });
            }
        });

        $('.rental_end_datetime').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm',
            stepMinute: 30,
            minDate: rentalMinEndDate,
            defaultDate: rentalMinEndDate,
            beforeShowDay: function(date) {
                var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                if (disableDates.indexOf(string) == -1) {
                    return [disableDates.indexOf(string) == -1, ''];
                } else {
                    return [disableDates.indexOf(string) == -1, 'rental-unavailable-date'];
                }
            },
            onSelect: function(select_date) {
                getRentalDetails();
                var selectedDate = new Date(select_date);
                var msecsInAHour = 60 * 60 * 1000; // Miliseconds in hours
                var startDate = new Date(selectedDate.getTime() - msecsInAHour);
                if (extendOrder < 1) {
                    $(".rental_start_datetime").datetimepicker(
                        "option", {
                            maxDate: new Date(startDate),
                            maxDateTime: new Date(startDate),
                        }
                    );
                }
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            var layoutDirection = '<?php echo CommonHelper::getLayoutDirection(); ?>';
            if (layoutDirection == 'rtl') {
                $('.js-product-gallery').slick({
                    dots: true,
                    arrows: false,
                    autoplay: false,
                    pauseOnHover: false,
                    slidesToShow: 1,
                    draggable: true,
                    rtl: true,
                });
            } else {
                $('.js-product-gallery').slick({
                    dots: true,
                    arrows: false,
                    autoplay: false,
                    pauseOnHover: false,
                    slidesToShow: 1,
                    draggable: true,
                });
            }

            $('#close-quick-js').click(function() {
                if ($('html').removeClass('quick-view--open')) {
                    $('.quick-view').removeClass('quick-view--open');
                }
            });

            /* $('#close-quick-js').click(function () {
             if ($('html').removeClass('quick-view--open')) {
             $(document).trigger('close.facebox');
             $('.quick-view').removeClass('quick-view--open');
             }
             }); */
            /* $('#quickView-slider-for').slick( getSlickGallerySettings(false,'<?php echo CommonHelper::getLayoutDirection(); ?>') );
             $('#quickView-slider-nav').slick( getSlickGallerySettings(true,'<?php echo CommonHelper::getLayoutDirection(); ?>') ); */

            function DropDown(el) {
                this.dd = el;
                this.placeholder = this.dd.children('span');
                this.opts = this.dd.find('ul.drop li');
                this.val = '';
                this.index = -1;
                this.initEvents();
            }

            DropDown.prototype = {
                initEvents: function() {
                    var obj = this;
                    obj.dd.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).toggleClass('active');
                    });
                    obj.opts.on('click', function() {
                        var opt = $(this);
                        obj.val = opt.text();
                        obj.index = opt.index();
                        obj.placeholder.text(obj.val);
                        opt.siblings().removeClass('is-active');
                        opt.filter(':contains("' + obj.val + '")').addClass('is-active');
                        var link = opt.filter(':contains("' + obj.val + '")').find('a').attr('href');
                        window.location.replace(link);
                    }).change();
                },
                getValue: function() {
                    return this.val;
                },
                getIndex: function() {
                    return this.index;
                }
            };

            $(function() {
                // create new variable for each menu
                $(document).click(function() {
                    // close menu on document click
                    $('.wrap-drop').removeClass('active');
                });

                $('.js-wrap-drop-quick').click(function() {
                    $(this).parent().siblings().children('.js-wrap-drop-quick').removeClass('active');
                });
            });

            $(".js-wrap-drop-quick").each(function(index, element) {
                var div = '#js-wrap-drop-quick' + index;
                new DropDown($(div));
            });

        });
    </script>