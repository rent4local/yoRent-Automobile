<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

/* $rentStartDate = $orderDetails['opd_rental_end_date']; */
$rentStartDate = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($orderDetails['opd_rental_end_date'])));

$rentalPriceSection = $frm->getField('rental_price_section');
/* $rentalPriceSection->value = "Rental Price section goes here!!!!!"; */
$addTimeToDate = 60 * 60 * 1000; ?>


<div class="modal-dialog modal-dialog-centered" role="document" id="extend-order-form-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Extend_Order_Form', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>
                <?php echo Labels::getLabel('LBL_Qty_to_extend', $siteLangId) . ' ' . $qty; ?> 
            </p><hr />
            <?php echo $frm->getFormHtml(); ?>
        </div>
    </div>
</div>

<script>
    var disableDates = <?php echo json_encode($unavailableDates); ?>;
    var extendOrder = 1;
    var availableDate = new Date('<?php echo $rentStartDate; ?>');
    var rentalMinEndDate = new Date(availableDate.getTime());
    /* $('.rental_start_datetime').datepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:00',
        stepMinute: 60,
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
            $(".rental_end_datetime").datepicker(
                "option", {
                    minDate: new Date(endDate),
                    minDateTime: new Date(endDate),
                    defaultDate: new Date(endDate),
                });
        }
    }); */

    $('.rental_end_datetime').datepicker({
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:00',
        stepMinute: 60,
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
                $(".rental_start_datetime").datepicker(
                    "option", {
                        maxDate: new Date(startDate),
                        maxDateTime: new Date(startDate),
                    }
                );
            }
        }
    });
</script>