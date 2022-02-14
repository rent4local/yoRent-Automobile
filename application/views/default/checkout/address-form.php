<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php

$addressFrm->setFormTagAttribute('class', 'form');
$addressFrm->setFormTagAttribute('onsubmit', 'setUpAddress(this, ' . $addressType . '); return(false);');
$addressFrm->developerTags['colClassPrefix'] = 'col-md-';
$addressFrm->developerTags['fld_default_col'] = 6;

$countryFld = $addressFrm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value, 0 ,\'#addr_state_id\')');

$stateFld = $addressFrm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');

$cancelFld = $addressFrm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-wide');
$cancelFld->setFieldTagAttribute('onclick', 'resetAddress(' . $addressType . ')');
$cancelFld->developerTags['col'] = 12;
$cancelFld->htmlBeforeField = '<div class="checkout-actions mt-0">';
$submitFld = $addressFrm->getField('btn_submit');
$cancelFld->attachField($submitFld);

$submitFld->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
$submitFld->htmlAfterField = '</div>'; 

?>
<div class="step">
    <div class="step_section">
        <div class="step_head">
            <h5 class="step_title"><?php echo Labels::getLabel('LBL_ADDRESS_DETAILS', $siteLangId); ?>
            </h5>
        </div>
        <div class="step_">
            <?php echo $addressFrm->getFormHtml(); ?>
        </div>
    </div>

</div>
<script language="javascript">
$(document).ready(function() {
    getCountryStates($("#addr_country_id").val(), <?php echo $stateId; ?>, '#addr_state_id');
});
</script>