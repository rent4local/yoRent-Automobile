<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-lg-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'setupCounterOffer(this); return(false);');


if((isset($isSeller) || $isSeller) && $rfqData['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP){
    $rowLength = 3;

}else{
    $rowLength = 4;
}

$priceFld = $frm->getField('counter_offer_total_cost');
$priceFld->addFieldTagAttribute('class', 'change-price--js');
$priceFld->setWrapperAttribute('class', 'col-lg-'.$rowLength);
$priceFld->developerTags['col'] = $rowLength;

$lbl =  Labels::getLabel('LBL_Excluded_Tax_Charges', $siteLangId);
if (!empty($rfqData['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP)) { 
    $lbl =  Labels::getLabel('LBL_Excluded_Shipping_and_Tax_Charges', $siteLangId);
}    
                                                        

$priceFld->htmlAfterField = '<small class="note">(' . $lbl . ')</small>';

if($rfqData['rfq_request_type'] != applicationConstants::PRODUCT_FOR_SALE){
    $priceFld = $frm->getField('counter_offer_rental_security');
    $priceFld->setWrapperAttribute('class', 'col-lg-'.$rowLength);
    $priceFld->developerTags['col'] = $rowLength;
    $priceFld->addFieldTagAttribute('class', 'change-price--js');
}

if($rfqData['rfq_fulfilment_type'] != Shipping::FULFILMENT_PICKUP){
    $priceFld = $frm->getField('counter_offer_shipping_cost');
    $priceFld->setWrapperAttribute('class', 'col-lg-'.$rowLength);
    $priceFld->developerTags['col'] = $rowLength;
    $priceFld->addFieldTagAttribute('class', 'change-price--js');
}

$totalFld = $frm->getField('total_price');
$totalFld->setWrapperAttribute('class', 'col-lg-'.$rowLength);
$totalFld->developerTags['col'] = $rowLength;

$btnFld = $frm->getField('btn_submit');
$btnFld->setFieldTagAttribute('class', 'btn btn-brand');


$totalPriceFld = $frm->getField('total_price');
$totalPriceFld->htmlAfterField = '<small class="note">(' . Labels::getLabel('LBL_Excluded_Tax_Charges', $siteLangId) . ')</small>';
?>
<?php echo $frm->getFormHTML(); ?>
<script>
    $(document).ready(function () {
        $('.change-price--js').on('change', function () {
            console.log("test");
            var productPrice = $('input[name="counter_offer_total_cost"]').val();
            var shippingCost = $('input[name="counter_offer_shipping_cost"]').val();
            shippingCost = (shippingCost) ? shippingCost : 0;

            var rentalSecurityAmt = $('input[name="counter_offer_rental_security"]').val();
            rentalSecurityAmt = (rentalSecurityAmt) ? rentalSecurityAmt : 0;
            // var rentalSecurityAmt = 0;
            $('.total_price--js').html(parseFloat(productPrice) + parseFloat(shippingCost) + parseFloat(rentalSecurityAmt));
        });
    });
</script>