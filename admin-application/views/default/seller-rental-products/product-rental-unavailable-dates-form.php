<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$dateFld = $frm->getField('pu_start_date');
$dateFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Start_Date', $adminLangId));
$dateFld->setFieldTagAttribute('class', 'start-date--js');

$dateFld = $frm->getField('pu_end_date');
$dateFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_End_Date', $adminLangId));
$dateFld->setFieldTagAttribute('class', 'end-date--js');

$qtyFld = $frm->getField('pu_quantity');
$qtyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Unavailable_Quantity', $adminLangId));


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
                            echo SellerProduct::getProductDisplayTitle($selprod_id, $adminLangId, true);
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('pu_start_date'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('pu_end_date'); ?>
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
$(document).ready(function () { 
    var startDate = new Date();
    if ($('.start-date--js').val() != '' && $('.start-date--js').val() != undefined && $('.start-date--js').val() != null) {
        var startDate = new Date($('input[name="pu_start_date"]').val());
    }
    
    $('.start-date--js').datepicker('option', { minDate: startDate });
    $('.end-date--js').datepicker('option', {
       minDate: new Date(startDate)
    });
});
</script>

