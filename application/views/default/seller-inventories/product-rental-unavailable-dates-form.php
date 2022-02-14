<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

/* $dateFld = $frm->getField('pu_start_date');
$dateFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Start_Date', $siteLangId));
$dateFld->setFieldTagAttribute('class', 'field--calender start-date--js'); */

$dateFld = $frm->getField('dates');
$dateFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Select_Dates', $siteLangId));
$dateFld->setFieldTagAttribute('class', 'unavaildates--js field--calender'); 

$qtyFld = $frm->getField('pu_quantity');
$qtyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Unavailable_Quantity', $siteLangId));


$frm->setFormTagAttribute('class', 'form form--horizontal durationDiscountForm--js');
$frm->setFormTagAttribute('onsubmit', 'setUpRentalUnavailableDates(this); return(false);');

$btnFld = $frm->getField('btn_submit');
$btnFld->setFieldTagAttribute('class', 'btn btn-brand');
?>
<div class="card-body">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('pu_selprod_id');
        echo $frm->getFieldHtml('pu_id');
        ?>
        <div class="row">
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php
                        if (0 >= $selprod_id) {
                            echo $frm->getFieldHtml('product_name');
                        } else {
                            echo SellerProduct::getProductDisplayTitle($selprod_id, $siteLangId, true);
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php /* <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('pu_start_date'); ?>
                    </div>
                </div>
            </div> */ ?>
            <div class="col-lg-4 col-md-4">
                <div class="field-set">
                    <div class="field-wraper rent-calender date-selector">
                        <?php echo $frm->getFieldHtml('dates'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('pu_quantity'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $frm->getFieldHtml('pu_start_date'); ?>
                            <?php echo $frm->getFieldHtml('pu_end_date'); ?>
                            <?php echo $frm->getFieldHtml('btn_submit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $frm->getExternalJs(); ?>
    </div>
</div>
<div class="divider m-0"></div>

<script>
var startDate = new Date();
if ($('input[name="pu_start_date"]').val() != '' && $('input[name="pu_start_date"]').val() != undefined && $('input[name="pu_start_date"]').val() != null) {
    var startDate = new Date($('input[name="pu_start_date"]').val());
}
    
var datepickerOption = {
    autoClose: true,
    startDate: startDate,
    customArrowPrevSymbol: '<i class="fa fa-arrow-circle-left"></i>',
    customArrowNextSymbol: '<i class="fa fa-arrow-circle-right"></i>',
    stickyMonths: true, 
    inline: true,
    container: '.rent-calender',
}

$('.unavaildates--js').dateRangePicker(datepickerOption).bind('datepicker-change', function(event, obj) {
    var selectedDates = obj.value;
    var datesArr = selectedDates.split(" to ");
    $('input[name="pu_start_date"]').val(datesArr[0]);
    $('input[name="pu_end_date"]').val(datesArr[1]);
});
</script>
