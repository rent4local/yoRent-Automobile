<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$zoneIds = [];
$countryStatesArr = [];
$zoneCountries = [];
if (!empty($zoneLocations)) {
    $zoneIds = array_column($zoneLocations, 'shiploc_zone_id');
    $zoneIds = array_unique(array_map('intval', $zoneIds));
    foreach ($zoneLocations as $location) {
        $selectedCountryId = $location['shiploc_country_id'];
        $selectedStateId = $location['shiploc_state_id'];
        $selectedZoneId = $location['shiploc_zone_id'];
        $zoneCountries[$selectedZoneId][] = $selectedCountryId;
        $countryStatesArr[$selectedCountryId][] = $selectedStateId;
    }
}
$excludeCountryStates = [];
$exZoneIds = [];

if (!empty($excludeLocations)) {
    $exZoneIds = array_column($excludeLocations, 'shiploc_zone_id');
    $exZoneIds = array_unique(array_map('intval', $exZoneIds));
    foreach ($excludeLocations as $exLocation) {
        $disableCountryId = $exLocation['shiploc_country_id'];
        $disableStateId = $exLocation['shiploc_state_id'];
        $excludeCountryStates[$disableCountryId][] = $disableStateId;
    }
}
$selectedCountryArr = [];

?>
<div class="modal-dialog modal-dialog-centered" role="document" id="zone-form-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Zone_Setup:', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form onsubmit="setupZone(this); return(false);" method="post" class="form" id="shippingZoneFrm">
                            <input type="hidden" name="shipprozone_id" value="<?php echo (!empty($zone_data)) ? $zone_data['shipprozone_id'] : 0; ?>">
                            <input type="hidden" name="shipzone_user_id" value="<?php echo $userId; ?>">
                            <input type="hidden" name="shipzone_id" value="<?php echo $zone_id; ?>">
                            <input type="hidden" name="shipzone_profile_id" value="<?php echo $profile_id; ?>">
                            <!--<input type="hidden" name="selected_ship_zone"> -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group zone-main-field--js">
                                        <input type="text" placeholder="<?php echo Labels::getLabel("LBL_Zone_Name", $siteLangId); ?>" name="shipzone_name" class="form-control shipzone_name" value="<?php echo (!empty($zone_data)) ? $zone_data['shipzone_name'] : ''; ?>" required>
                                        <span class="form-text text-muted"><?php echo Labels::getLabel("LBL_Customers_will_not_see_this.", $siteLangId); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="form-group mb-20 zone-main-field--js">
                                        <input type="text" placeholder="<?php echo Labels::getLabel("LBL_Search_Country_By_Name", $siteLangId); ?>" class="filterCountryJs form-control shipzone_name" />
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="checkbox_container--js">
                                        <ul class="list-country-zone">
                                            <li class="zoneLiJs">
                                                <div class="row no-gutters zone-row">
                                                    <div class="col">
                                                        <div class="field-wraper">
                                                            <div class="field_cover">
                                                                <label>
                                                                    <span class="checkbox" data-zoneid="-1">
                                                                        <input type="checkbox" name="rest_of_the_world" value="-1" class="checkbox_zone_-1" <?php echo (in_array(-1, $zoneIds)) ? 'checked' : ''; ?> <?php echo (in_array(-1, $exZoneIds)) ? 'disabled' : ''; ?>><?php echo Labels::getLabel("LBL_REST_OF_THE_WORLD", $siteLangId); ?>
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                            <?php if (!empty($zones)) {
                                                foreach ($zones as $zone) {
                                                    $countCounties = 0;
                                                    if (!empty($zoneCountries)) {
                                                        $cZoneCountries = (isset($zoneCountries[$zone['zone_id']])) ? $zoneCountries[$zone['zone_id']] : array();
                                                        $countCounties = count(array_unique($cZoneCountries));
                                                    }

                                                    $countries = (isset($zone['countries'])) ? $zone['countries'] : array();
                                                    $totalCountries = count($countries); ?>
                                                    <li class="zoneLiJs">
                                                        <div class="row no-gutters zone-row">
                                                            <div class="col">
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <label>
                                                                            <span class="checkbox zone--js" data-zoneid="<?php echo $zone['zone_id']; ?>"><input type="checkbox" name="shiploc_zone_ids[]" value="<?php echo $zone['zone_id']; ?>" class="checkbox_zone_<?php echo $zone['zone_id']; ?>" <?php echo ($countCounties == $totalCountries && $countCounties != 0) ? 'checked' : ''; ?>><?php echo $zone['zone_name']; ?></span>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php if (!empty($countries)) { ?>
                                                                <div class="col-auto">
                                                                    <a class="btn btn-sm containCountries-js-<?php echo $zone['zone_id']; ?>" data-toggle="collapse" href="#countries_list_<?php echo $zone['zone_id']; ?>" aria-expanded="false" aria-controls="countries_list_<?php echo $zone['zone_id']; ?>"><span class="fa fa-angle-down" aria-hidden="true"></span> </a>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <?php 
                                                        if (!empty($countries)) { ?>
                                                            <ul class="child-checkbox-ul zone_<?php echo $zone['zone_id']; ?>">
                                                                <?php foreach ($countries as $country) {
                                                                    $statesCount = $country['state_count'];
                                                                    $countryId = $country['country_id'];
                                                                    $disabled = '';
                                                                    $checked = '';
                                                                    $countryStates = [];
                                                                    $selectedStatesCount = 0;
                                                                    if (!empty($countryStatesArr) && isset($countryStatesArr[$countryId])) {
                                                                        $countryStates = $countryStatesArr[$countryId];
                                                                        $selectedStatesCount = count($countryStates);
                                                                        if (!in_array('-1', $countryStates)) {
                                                                            $selectedCountryArr[] = $countryId;
                                                                        }
                                                                    }
                                                                    if (!empty($countryStates) && in_array('-1', $countryStates)) {
                                                                        $checked = 'checked';
                                                                        $selectedStatesCount = $statesCount;
                                                                    }
                                                                    if (!empty($excludeCountryStates) && isset($excludeCountryStates[$countryId])) {
                                                                        $disabled = 'disabled';
                                                                    } ?>
                                                                    <li class="collapse multi-collapse <?php echo (in_array($zone['zone_id'], $zoneIds)) ? "show" : ""; ?>" id="countries_list_<?php echo $zone['zone_id']; ?>">
                                                                        <div class="row no-gutters">
                                                                            <div class="col">
                                                                                <div class="field-wraper">
                                                                                    <div class="field_cover">
                                                                                        <label>
                                                                                            <span class="checkbox country--js " data-countryid="<?php echo $countryId; ?>" data-statecount="<?php echo $statesCount; ?>">
                                                                                                <input type="checkbox" name="shiploc_country_ids[]" value="<?php echo $zone['zone_id']; ?>-<?php echo $countryId; ?>" class="checkbox_country_<?php echo $countryId; ?>" <?php echo $checked; ?>> <?php echo $country['country_identifier']; ?></span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-auto">
                                                                                <?php if ($statesCount > 0) { ?>
                                                                                    <a class="btn  btn-sm link_<?php echo $countryId; ?> containChild-js" data-toggle="collapse" href="#state_list_<?php echo $countryId; ?>" aria-expanded="false" aria-controls="state_list_<?php echo $countryId; ?>" data-zone="<?php echo $zone['zone_id']; ?>" data-countryid="<?php echo $countryId; ?>" data-loadedstates="0" onclick="getStates(<?php echo $countryId . ',' . $zone['zone_id'] . ',' . $profile_id; ?>);"><span class="statecount--js selectedStateCount--js_<?php echo $countryId; ?> " data-totalcount="<?php echo $statesCount; ?>"><?php echo $selectedStatesCount; ?></span>
                                                                                        <?php echo Labels::getLabel("LBL_of", $siteLangId); ?>
                                                                                        <span class="totalStates "><?php echo $statesCount; ?></span>
                                                                                        <span class="fa fa-angle-down" aria-hidden="true"></span>
                                                                                    </a>
                                                                                <?php } ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="collapse box--scroller" id="state_list_<?php echo $countryId; ?>">
                                                                        </div>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        <?php } ?>
                                                    </li>
                                            <?php }
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 justify-content-between">
                                <div class="col">
                                    <input type="hidden" value="<?php echo implode(',' , $selectedCountryArr);?>" name="selected_countries">
                                    <input class="btn btn-brand" type="submit" name="btn_submit" value="<?php echo Labels::getLabel("LBL_Add_Zone", $siteLangId); ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php if (0 < $zone_id) { ?>
    <script>
        /* $(".containChild-js").each(function() {
            var dropStateElement = $(this);
            dropStateElement.click();
            var countryId = $(this).data("countryid");
            var zoneId = $(this).data("zone");
            $("#state_list_" + countryId).addClass('d-none');
            setTimeout(function() {
                if (0 < $("#state_list_" + countryId + " .state--js:checked").length) {
                    $("#state_list_" + countryId).removeClass('d-none');
                    $(".containCountries-js-" + zoneId).click();
                } else {
                    $("#state_list_" + countryId).removeClass('d-none show');
                }
            }, 500);
        }); */
    </script>
<?php } ?>
<script>
    /*  $(document).on('keyup', "input[name='shipzone_name']", function() {
        var currObj = $(this);
        var parentForm = currObj.closest('form').attr('id');
        $("#" + parentForm + " input[name='shipzone_id']").val(0);
        $('.country--js input[type="checkbox"]').prop('checked', false);
        $('.zone--js input[type="checkbox"]').prop('checked', false);
        if ('' != currObj.val()) {
            currObj.siblings('ul.dropdown-menu').remove();
            currObj.autocomplete({
                'source': function(request, response) {
                    $.ajax({
                        url: fcom.makeUrl('ShippingZones', 'autoCompleteZone'),
                        data: {
                            fIsAjax: 1,
                            keyword: currObj.val()
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function(json) {
                            response($.map(json, function(item) {
                                return {
                                    label: item['name'],
                                    value: item['name'],
                                    id: item['id']
                                };
                            }));
                        },
                    });
                },
                appendTo: '.zone-main-field--js',
                select: function(event, ui) {
                    $("#" + parentForm + " input[name='shipzone_id']").val(ui.item.id);
                    getZoneLocation(ui.item.id);
                }
            });
        } else {
            $("#" + parentForm + " input[name='shipzone_id']").val(0);
        }
    }); */
</script>
<script>
$(document).on('keyup', '.filterCountryJs', function() {
    var searchTxt = $(this).val(); 
    var filter = $(this).val().trim();
    var activeZoneIds = [];
    $('.checkbox_container--js .country--js').each(function(index, el) {
        var val = $(el).find('input[type="checkbox"]').attr('value');
        var idsArr = val.split('-');
        var zoneId = parseInt(idsArr[0]);
        if ($(el).text().trim().search(new RegExp(filter, "i")) >= 0) {
            if (jQuery.inArray(zoneId, activeZoneIds) === -1) {
                activeZoneIds.push(zoneId);
            }
            $(el).parents('.collapse').show();
        } else {
            if ($(el).find('input[type="checkbox"]').prop('checked') == false) {
                $(el).parents('.collapse').hide();
            }
        } 
    });
    $('.checkbox_container--js .zone--js').each(function(index, el) {
        var zoneId = $(el).data('zoneid');
        if (jQuery.inArray(zoneId, activeZoneIds) === -1) {
           $(el).parents('li.zoneLiJs').hide();
        } else {
            $(el).parents('li.zoneLiJs').show();
            $('.zone_'+ zoneId + ' .collapse').addClass('show');
        }
    });
});  
</script>