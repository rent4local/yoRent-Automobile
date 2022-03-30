<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'pickupAddressFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setPickupAddress(this); return(false);');

$addrLabelFld = $frm->getField('addr_title');
$addrLabelFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $siteLangId));

$countryFld = $frm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . $stateId . ',\'#addr_state_id\')');

$stateFld = $frm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');

$cityFld = $frm->getField('addr_city');
$cityFld->setFieldTagAttribute('id', 'addr_city');

$zipFld = $frm->getField('addr_zip');
$zipFld->setFieldTagAttribute('id', 'addr_zip');

if ($allowSale) {
    $slotTypeFld = $frm->getField('tslot_availability');
    $slotTypeFld->setOptionListTagAttribute('class', 'list-inline');
    $slotTypeFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
    $slotTypeFld->developerTags['rdHtmlAfterRadio'] = '';
    $slotTypeFld->setFieldTagAttribute('onClick', 'displaySlotTimings(this);');
    $slotTypeFld->setFieldTagAttribute('class', 'availabilityType-js');

    $fromAllFld = $frm->getField('tslot_from_all');
    $fromAllFld->setFieldTagAttribute('onChange', 'validateTimeFields()');
    $fromAllFld->setFieldTagAttribute('class', 'selectAllFromTime-js');

    $toAllFld = $frm->getField('tslot_to_all');
    $toAllFld->setFieldTagAttribute('onChange', 'validateTimeFields()');
    $toAllFld->setFieldTagAttribute('class', 'selectAllToTime-js');
}
$cancelFld = $frm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand');
$cancelFld->developerTags['col'] = 2;
$cancelFld->developerTags['noCaptionTag'] = true;

$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
$btnSubmit->developerTags['col'] = 2;
$btnSubmit->developerTags['noCaptionTag'] = true;

$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Pickup_Addresses', $siteLangId); ?></h5>
        <div class="btn-group">
            <a href="javascript:void(0)" onClick="pickupAddress()" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo $frm->getFormTag(); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_title');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_title'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_name');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_name'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_address1');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_address1'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_address2');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_address2'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_country_id');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_country_id'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_state_id');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_state_id'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_city');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_city'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_zip');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_zip'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                    <?php $fld = $frm->getField('addr_phone');
                                    echo $fld->getCaption();
                                    ?>
                                </label>
                                <span class="spn_must_field">*</span>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('addr_phone'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
                    echo $frm->getFieldHtml('addr_lat');
                    echo $frm->getFieldHtml('addr_lng');
                } else { ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $frm->getField('addr_lat');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                    <span class="spn_must_field">*</span>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('addr_lat'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $frm->getField('addr_lng');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                    <span class="spn_must_field">*</span>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('addr_lng'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') { ?>
                    <div class="gap"> </div>
                    <b><?php echo Labels::getLabel('LBL_Note:_Map_works_according_to_country_and_state_only', $siteLangId); ?></b>
                    <div class="gap"> </div>
                    <div class="col-lg-12 col-md-12" id="pickupMap" style="width:1500px; height:500px"></div>
                <?php } ?>

                <?php if ($allowSale) { ?>
                    <div class="gap"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label >
                                        <?php $fld = $frm->getField('tslot_availability');
                                        echo $fld->getCaption() . " <span > (" . Labels::getLabel('LBL_Note:_Time_slots_only_applicable_for_sale', $siteLangId) . " )</span>";
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('tslot_availability'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="js-slot-individual <?php echo $availability == TimeSlot::DAY_ALL_DAYS ? 'd-none' : ''; ?>">
                        <?php
                        $daysArr = TimeSlot::getDaysArr($siteLangId);
                        $row = 0;
                        for ($i = 0; $i < count($daysArr); $i++) {
                            $dayFld = $frm->getField('tslot_day[' . $i . ']');
                            $dayFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                            $dayFld->developerTags['cbHtmlAfterCheckbox'] = '';
                            $dayFld->setFieldTagAttribute('onChange', 'displayFields(' . $i . ', this)');
                            $dayFld->setFieldTagAttribute('class', 'slotDays-js');

                            if (!empty($slotData) && isset($slotData['tslot_day'][$i])) {
                                $dayFld->setFieldTagAttribute('checked', 'true');
                                foreach ($slotData['tslot_from_time'][$i] as $key => $time) {
                                    $fromTime = date('H:i', strtotime($time));
                                    $toTime = date('H:i', strtotime($slotData['tslot_to_time'][$i][$key]));

                                    $fromFld = $frm->getField('tslot_from_time[' . $i . '][]');
                                    $fromFld->setFieldTagAttribute('class', 'js-slot-from-' . $i . ' fromTime-js');
                                    $fromFld->setFieldTagAttribute('data-row', $row);
                                    $fromFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
                                    $fromFld->value = $fromTime;

                                    $toFld = $frm->getField('tslot_to_time[' . $i . '][]');
                                    $toFld->setFieldTagAttribute('class', 'js-slot-to-' . $i);
                                    $toFld->setFieldTagAttribute('data-row', $row);
                                    $toFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
                                    $toFld->value = $toTime;
                        ?>
                                    <div class="row row-<?php echo $row;
                                                        echo ($key > 0) ? ' js-added-rows-' . $i : '' ?>">
                                        <div class="col-md-2">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php
                                                        if ($key == 0) {
                                                            echo $frm->getFieldHtml('tslot_day[' . $i . ']');
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 js-from_time_<?php echo $i; ?>">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php $fld = $frm->getField('tslot_from_time[' . $i . '][]');
                                                        echo $fld->getCaption();
                                                        ?>
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $frm->getFieldHtml('tslot_from_time[' . $i . '][]'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 js-to_time_<?php echo $i; ?>">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php $fld = $frm->getField('tslot_to_time[' . $i . '][]');
                                                        echo $fld->getCaption();
                                                        ?>
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $frm->getFieldHtml('tslot_to_time[' . $i . '][]'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 addRowBtnBlock<?php echo $i; ?>-js">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php if ($key != 0) {  ?>
                                                            <button class="btn btn-outline-brand btn-sm" type="button" name="btn_remove_row" data-day="<?php echo $i; ?>"><i class="icn">
                                                                    <svg class="svg" width="16px" height="16px">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus">
                                                                        </use>
                                                                    </svg>
                                                                </i></button>
                                                        <?php }
                                                        if (count($slotData['tslot_from_time'][$i]) - 1 == $key) { ?>
                                                            <button type="button" name="btn_add_row[<?php echo $i; ?>]" onClick="addTimeSlotRow(<?php echo $i; ?>)" class="btn btn-brand btn-sm js-slot-add-<?php echo $i; ?>"><i class="icn">
                                                                    <svg class="svg" width="16px" height="16px">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus">
                                                                        </use>
                                                                    </svg>
                                                                </i></button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    $row++;
                                }
                            } else {
                                $fromFld = $frm->getField('tslot_from_time[' . $i . '][]');
                                $fromFld->setFieldTagAttribute('disabled', 'true');
                                $fromFld->setFieldTagAttribute('data-row', $row);
                                $fromFld->setFieldTagAttribute('class', 'js-slot-from-' . $i . ' fromTime-js');
                                $fromFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');

                                $toFld = $frm->getField('tslot_to_time[' . $i . '][]');
                                $toFld->setFieldTagAttribute('disabled', 'true');
                                $toFld->setFieldTagAttribute('data-row', $row);
                                $toFld->setFieldTagAttribute('class', 'js-slot-to-' . $i);
                                $toFld->setFieldTagAttribute('onChange', 'displayAddRowField(' . $i . ', this)');
                                ?>
                                <div class="row row-<?php echo $row; ?>">
                                    <div class="col-md-2">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('tslot_day[' . $i . ']'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 js-from_time_<?php echo $i; ?>">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php $fld = $frm->getField('tslot_from_time[' . $i . '][]');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('tslot_from_time[' . $i . '][]'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 js-to_time_<?php echo $i; ?>">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php $fld = $frm->getField('tslot_to_time[' . $i . '][]');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('tslot_to_time[' . $i . '][]'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 addRowBtnBlock<?php echo $i; ?>-js">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php //echo $frm->getFieldHtml('btn_add_row['.$i.']'); 
                                                    ?>
                                                    <button type="button" name="btn_add_row[<?php echo $i; ?>]" onClick="addTimeSlotRow(<?php echo $i; ?>)" class="d-none btn btn-brand js-slot-add-<?php echo $i; ?>"><i class="icn">
                                                            <svg class="svg" width="16px" height="16px">
                                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus">
                                                                </use>
                                                            </svg>
                                                        </i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                                $row++;
                            }
                        }
                        ?>
                    </div>
                    <div class="row js-slot-all  <?php echo $availability == TimeSlot::DAY_INDIVIDUAL_DAYS ? 'd-none' : ''; ?>">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $frm->getField('tslot_from_all');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('tslot_from_all'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label">
                                        <?php $fld = $frm->getField('tslot_to_all');
                                        echo $fld->getCaption();
                                        ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('tslot_to_all'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label">
                                </label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    echo $frm->getFieldHtml('addr_id');
                                    echo $frm->getFieldHtml('btn_submit');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
                <?php echo $frm->getExternalJS(); ?>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
$(document).ready(function(){
    stylePhoneNumberFld("input[name='addr_phone']", false, 'addr_dial_code', 'addr_country_iso');
});
</script>
<?php
if (isset($countryIso) && !empty($countryIso)) { ?>
    <script>
        langLbl.defaultCountryCode = '<?php echo $countryIso; ?>';
    </script>
<?php } ?>
<script language="javascript">
    $(document).ready(function() {
        /* getCountryStates($("#addr_country_id").val(), <?php echo ($stateId) ? $stateId : 0; ?>, '#addr_state_id'); */

        addTimeSlotRow = function(day) {
            var fromTimeHtml = $(".js-from_time_" + day).html();
            var toTimeHtml = $(".js-to_time_" + day).html();
            var count = $('.js-slot-individual .row').length;
            var toTime = $(".js-slot-to-" + day + ":last").val();
            var rowElement = ".js-slot-individual .row-" + count;

            var addRowBtn = $('.js-slot-add-' + day);
            if (0 < addRowBtn.closest('.field-set').length) {
                addRowBtn.remove();
                addRowBtn.closest('.field-set').remove();
            }

            if (0 < $('.addRowBtn' + day + '-js').length) {
                $('.addRowBtn' + day + '-js').remove();
            }

            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand btn-sm js-slot-add-' + day + ' addRowBtn' + day + '-js d-none"><i class="icn"><svg class="svg" width="16px" height="16px"> <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus"></use></svg></i></button>';

            var html = "<div class='row row-" + count + " js-added-rows-" + day + "'><div class='col-md-2'></div><div class='col-md-4 js-from_time_" + day + "'>" + fromTimeHtml + "</div><div class='col-md-4 js-to_time_" + day + "'>" + toTimeHtml + "</div><div class='col-md-2'><div class='field-set'><div class='caption-wraper'><label class='field_label'></label></div><div class='field-wraper'><div class='field_cover btn-group'><button class='btn btn-outline-brand btn-sm' type='button' name='btn_remove_row' data-day='" + day + "'><i class='fas fa-minus'></i></button>" + addRowBtnHtml + "</div></div></div></div></div>";

            $(".js-from_time_" + day).last().parent().after(html);
            $(rowElement + " select").val('').attr('data-row', (count));
            var frmElement = rowElement + " .js-slot-from-" + day;

            $(frmElement + " option").removeClass('d-none');
            $(frmElement + " option").each(function() {
                var toVal = $(this).val();
                if (toVal != '' && toVal <= toTime) {
                    $(this).addClass('d-none');
                }
            });
        }

        displayFields = function(day, ele) {
            if ($(ele).prop("checked") == true) {
                $(".js-slot-from-" + day).removeAttr('disabled');
                $(".js-slot-to-" + day).removeAttr('disabled');
                displayAddRowField(day, ele);
            } else {
                $(".js-slot-from-" + day).attr('disabled', 'true');
                $(".js-slot-to-" + day).attr('disabled', 'true');
                $(".js-slot-add-" + day).addClass('d-none');
                $(".js-added-rows-" + day).remove();
            }
        }

        displayAddRowField = function(day, ele) {
            var index = $(ele).data('row');
            var rowElement = ".js-slot-individual .row-" + index;
            var frmElement = rowElement + " .js-slot-from-" + day;
            var toElement = rowElement + " .js-slot-to-" + day;

            var fromTime = $(frmElement + " option:selected").val();
            var toTime = $(toElement + " option:selected").val();

            var toElementIndex = $(rowElement).index();
            var nextRowElement = ".js-slot-individual .row:eq(" + (toElementIndex + 1) + ")";
            var nextFrmElement = nextRowElement + " .js-slot-from-" + day;
            if (0 < $(nextFrmElement).length) {
                $(nextFrmElement + " option").removeClass('d-none');
                var nxtFrmSelectedVal = $(nextFrmElement + ' option:selected').val();
                if (nxtFrmSelectedVal <= toTime) {
                    $(".js-slot-from-" + day).each(function() {
                        if (index < $(this).data('row') && $(this).val() <= toTime) {
                            var nxtRow = $(this).data('row');
                            $(this).val("");
                            $(".js-slot-individual .row-" + nxtRow + " .js-slot-to-" + day).val("");
                            $("option", this).each(function() {
                                var optVal = $(this).val();
                                if (optVal != '' && optVal <= toTime) {
                                    $(this).addClass('d-none');
                                }
                            });
                        }
                    });
                }
                $(nextFrmElement + " option").each(function() {
                    var nxtFrmVal = $(this).val();
                    if (nxtFrmVal != '' && nxtFrmVal <= toTime) {
                        $(this).addClass('d-none');
                    }
                });
            }

            if (fromTime == '' && toTime != '') {
                $(toElement).val("");
                $.mbsmessage(langLbl.invalidFromTime, true, 'alert--danger');
                return false;
            }

            if (toTime != '' && toTime <= fromTime) {
                $(toElement).val('').addClass('error');
                var toTime = $(toElement).children("option:selected").val();
            } else {
                $(toElement).removeClass('error');
            }

            $(toElement + " option").removeClass('d-none');
            $(toElement + " option").each(function() {
                var toVal = $(this).val();
                if (toVal != '' && toVal <= fromTime) {
                    $(this).addClass('d-none');
                }
            });

            var toTimeLastOpt = $(toElement + " option:last").val();

            if (fromTime != '' && toTime != '' && toTime < toTimeLastOpt) {
                $(rowElement + " .js-slot-add-" + day).removeClass('d-none');
            } else {
                $(rowElement + " .js-slot-add-" + day).addClass('d-none');
            }

        }

        displaySlotTimings = function(ele) {
            var selectedVal = $(ele).val();
            if (selectedVal == 2) {
                $('.js-slot-individual').addClass('d-none');
                $('.js-slot-all').removeClass('d-none');
            } else {
                $('.js-slot-all').addClass('d-none');
                $('.js-slot-individual').removeClass('d-none');
            }
        }

        validateTimeFields = function() {
            var from_time = $("[name='tslot_from_all']").children("option:selected").val();
            var to_time = $("[name='tslot_to_all']").children("option:selected").val();

            $("[name='tslot_to_all'] option").removeClass('d-none');
            $("[name='tslot_to_all'] option").each(function() {
                var toVal = $(this).val();
                if (toVal != '' && toVal <= from_time) {
                    $(this).addClass('d-none');
                }
            });
            if (to_time != '' && to_time <= from_time) {
                $("[name='tslot_to_all']").val('').addClass('error');
            } else {
                $("[name='tslot_to_all']").removeClass('error');
            }
        }
    });

    $(document).on("click", "[name='btn_remove_row']", function() {
        var day = $(this).data('day');
        $(this).parentsUntil('.row').parent().remove();

        if (0 < $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').length) {
            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand js-slot-add-' + day + ' addRowBtn' + day + '-js"><i class="icn"> <svg class="svg" width="16px" height="16px"> <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus"></use></svg></i></button>';

            if (1 > $('.js-added-rows-' + day + ':last .addRowBtn' + day + '-js').length) {
                $('.js-added-rows-' + day + ':last [name="btn_remove_row"]').after(addRowBtnHtml);
            }

        } else if (0 < $('.addRowBtnBlock' + day + '-js').length) {
            var addRowBtnHtml = '<button type="button" name="btn_add_row[' + day + ']" onclick="addTimeSlotRow(' + day + ')" class="btn btn-brand js-slot-add-' + day + ' addRowBtn' + day + '-js mt-4"><i class="icn"><svg class="svg" width="16px" height="16px"> <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus"></use> </svg></i></button>';
            $('.addRowBtnBlock' + day + '-js').html(addRowBtnHtml);
        }
    })
</script>

<script>
    var map;
    var marker;
    var geocoder;
    var infowindow;
    /* // Initialize the map. */
    function initPickupMap(lat = 40.72, lng = -73.96, elementId = "pickupMap") {
        var lat = parseFloat(lat);
        var lng = parseFloat(lng);
        var latlng = {
            lat: lat,
            lng: lng
        };
        var address = "";
        if (1 > $("#" + elementId).length) {
            return;
        }
        map = new google.maps.Map(document.getElementById(elementId), {
            zoom: 12,
            center: latlng,
        });
        geocoder = new google.maps.Geocoder();
        infowindow = new google.maps.InfoWindow();

        geocodePickupAddress(geocoder, map, infowindow, {
            location: latlng
        });

        document.getElementById("addr_state_id").addEventListener("change", function() {
            var sel = document.getElementById("addr_country_id");
            var country = sel.options[sel.selectedIndex].text;

            var sel = document.getElementById("addr_state_id");
            var state = sel.options[sel.selectedIndex].text;

            address = country + " " + state;

            geocodePickupAddress(geocoder, map, infowindow, {
                address: address
            });
        });

        document.getElementById("addr_country_id").addEventListener("change", function() {
            var sel = document.getElementById("addr_country_id");
            var country = sel.options[sel.selectedIndex].text;
            geocodePickupAddress(geocoder, map, infowindow, {
                address: country
            });
        });

        document.getElementById("addr_zip").addEventListener("blur", function () {
            var sel = document.getElementById("addr_country_id");
            var country = sel.options[sel.selectedIndex].text;
        
            var sel = document.getElementById("addr_state_id");
            var state = sel.options[sel.selectedIndex].text;
        
            address = document.getElementById("addr_zip").value;
            address = country + " " + state + " " + address;
            geocodePickupAddress(geocoder, map, infowindow, { address: address }, true);
        });
        
        
    }

    function geocodePickupAddress(geocoder, resultsMap, infowindow, address, isPostcodeChange = false) {
        geocoder.geocode(address, function(results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                resultsMap.setCenter(results[0].geometry.location);
                if (marker && marker.setMap) {
                    marker.setMap(null);
                }
                marker = new google.maps.Marker({
                    map: resultsMap,
                    position: results[0].geometry.location,
                    draggable: true,
                });
                geocodePickupSetData(results, isPostcodeChange);
                google.maps.event.addListener(marker, "dragend", function() {
                    geocoder.geocode({
                            latLng: marker.getPosition()
                        },
                        function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                geocodePickupSetData(results, isPostcodeChange);
                            }
                        }
                    );
                });
            } else {
                console.log(
                    "Geocode was not successful for the following reason: " + status
                );
            }
        });
    }

    function geocodePickupSetData(results, isPostcodeChange = false) {
        document.getElementById("lat").value = marker.getPosition().lat();
        document.getElementById("lng").value = marker.getPosition().lng();
        if (results[0]) {
            infowindow.setContent(results[0].formatted_address);
            infowindow.open(map, marker);
            var address_components = results[0].address_components;
            var data = {};
            /* data['lat'] = pos.lat();
                 data['lng'] = pos.lng(); */
            data["formatted_address"] = results[0].formatted_address;
            if (0 < address_components.length) {
                var addressComponents = address_components;
                for (var i = 0; i < addressComponents.length; i++) {
                    var key = address_components[i].types[0];
                    var value = address_components[i].long_name;
                    data[key] = value;
                    if ("country" == key) {
                        data["country_code"] = address_components[i].short_name;
                        data["country"] = value;
                    } else if ("administrative_area_level_1" == key) {
                        data["state_id"] = address_components[i].short_name;
                        data["state"] = value;
                    } else if ("administrative_area_level_2" == key) {
                        data["city"] = value;
                    }
                }
            }
    
            if (isPostcodeChange) {
                $("#addr_zip").val(data.postal_code);
            }
            
            $("#addr_country_id option").each(function() {
                if (this.text == data.country) {
                    $("#addr_country_id").val(this.value);
                    var state = 0;
                    $("#addr_state_id option").each(function() {
                        if (
                            this.value == data.state_id ||
                            this.text == data.state ||
                            this.text == data.locality
                        ) {
                            return (state = this.value);
                        }
                    });
                    getCountryStates(this.value, state, "#addr_state_id", "state_id");

                    return false;
                }
            });
        }
    }
</script>

<?php if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') { ?>
    <script>
        var lat = (!$('#lat').val()) ? 0 : $('#lat').val();
        var lng = (!$('#lng').val()) ? 0 : $('#lng').val();
        initPickupMap(lat, lng);
    </script>
<?php } ?>