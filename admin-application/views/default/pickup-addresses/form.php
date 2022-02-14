<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form  layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
//$frm->developerTags['colClassPrefix'] = 'col-md-';
//$frm->developerTags['fld_default_col'] = 6;

$langFld = $frm->getField('lang_id');
$langFld->setFieldTagAttribute('onChange', "addAddressForm(" . $addressId . ", this.value);");

$addrLabelFld = $frm->getField('addr_title');
$addrLabelFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_E.g:_My_Office_Address', $langId));

$countryFld = $frm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id', 'addr_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . $stateId . ',\'#shop_state\',' . $langId . ')');

$stateFld = $frm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id', 'addr_state_id');

if ($allowSale) {

    $slotTypeFld = $frm->getField('tslot_availability');
    $slotTypeFld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
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
?>

<div class="sectionbody space">
    <div class="row">
        <div class="col-sm-12">
            <div class="tabs_nav_container responsive flat">
                <div class="tabs_panel_wrap">
                    <div class="tabs_panel">
                        <?php echo $frm->getFormTag(); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">
                                            <?php $fld = $frm->getField('lang_id');
                                            echo $fld->getCaption();
                                            ?>
                                        </label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('lang_id'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <b><?php echo Labels::getLabel('LBL_Note:_Map_works_according_to_country_and_state_only', $adminLangId); ?></b>
                            <div class="gap"> </div>
                            <div class="col-lg-12 col-md-12" id="pickupMap" style="width:1500px; height:500px"></div>
                        <?php } ?>

                        <?php if ($allowSale) { ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php $fld = $frm->getField('tslot_availability');
                                                echo $fld->getCaption();
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
                                $daysArr = TimeSlot::getDaysArr($langId);
                                $row = 0;
                                for ($i = 0; $i < count($daysArr); $i++) {

                                    $dayFld = $frm->getField('tslot_day[' . $i . ']');
                                    $dayFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                                    $dayFld->developerTags['cbHtmlAfterCheckbox'] = '';
                                    $dayFld->setFieldTagAttribute('onChange', 'displayFields(' . $i . ', this)');
                                    $dayFld->setFieldTagAttribute('class', 'slotDays-js');

                                    $addRowFld = $frm->getField('btn_add_row[' . $i . ']');
                                    $addRowFld->setFieldTagAttribute('onClick', 'addTimeSlotRow(' . $i . ')');
                                    $addRowFld->setFieldTagAttribute('class', 'js-slot-add-' . $i);

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
                                                            <label class="field_label"> </label>
                                                        </div>
                                                        <div class="field-wraper">
                                                            <div class="field_cover">
                                                                <?php if ($key == 0) {
                                                                    echo $frm->getFieldHtml('tslot_day[' . $i . ']');
                                                                } ?>
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
                                                                    <input type='button' name='btn_remove_row' value='x' data-day="<?php echo $i; ?>">
                                                                <?php }
                                                                if (count($slotData['tslot_from_time'][$i]) - 1 == $key) {
                                                                    echo $frm->getFieldHtml('btn_add_row[' . $i . ']');
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                            $row++;
                                        }
                                    } else {
                                        $addRowFld->setFieldTagAttribute('class', 'd-none js-slot-add-' . $i);

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
                                                            <?php echo $frm->getFieldHtml('btn_add_row[' . $i . ']'); ?>
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
                            <div class="row js-slot-all <?php echo $availability == TimeSlot::DAY_INDIVIDUAL_DAYS ? 'd-none' : ''; ?>">
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
    </div>
</div>


<script language="javascript">
    <?php /*   if ($addressId > 0) { ?>
        $(document).ready(function() {
            getCountryStates($("#addr_country_id").val(), <?php echo ($stateId) ? $stateId : 0; ?>, '#addr_state_id', <?php echo $langId; ?>);
        });
    <?php } */ ?>
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

        /* var sel = document.getElementById('addr_country_id');
           var country = sel.options[sel.selectedIndex].text;
           
           address = document.getElementById('postal_code').value;
           address = country + ' ' + address;
           
           geocodePickupAddress(geocoder, map, infowindow, { 'address': address }); */

        /* document.getElementById("addr_zip").addEventListener("blur", function() {
            var sel = document.getElementById("addr_country_id");
            var country = sel.options[sel.selectedIndex].text;

            var sel = document.getElementById("addr_state_id");
            var state = sel.options[sel.selectedIndex].text;

            address = document.getElementById("addr_zip").value;
            address = country + " " + state + " " + address;
            geocodePickupAddress(geocoder, map, infowindow, {
                address: address
            });
        }); */

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

        /* for (i = 0; i < document.getElementsByClassName('addressSelection-js').length; i++) {
           document.getElementsByClassName('addressSelection-js')[i].addEventListener("change", function(e) {
           address = e.target.options[e.target.selectedIndex].text;
           geocodePickupAddress(geocoder, map, infowindow, {'address': address});
           });
           } */
    }

    function geocodePickupAddress(geocoder, resultsMap, infowindow, address) {
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
                geocodePickupSetData(results);
                google.maps.event.addListener(marker, "dragend", function() {
                    geocoder.geocode({
                            latLng: marker.getPosition()
                        },
                        function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                geocodePickupSetData(results);
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

    function geocodePickupSetData(results) {
        document.getElementById("lat").value = marker.getPosition().lat();
        document.getElementById("lng").value = marker.getPosition().lng();
        if (results[0]) {
            infowindow.setContent(results[0].formatted_address);
            infowindow.open(map, marker);
            var address_components = results[0].address_components;
            var data = {};
            /*  console.log(address_components); */
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


            $("#addr_zip").val(data.postal_code);
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