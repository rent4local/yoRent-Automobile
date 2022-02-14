<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($zones)) { ?>
    <ul class="list-group list-shipping">
        <?php
        foreach ($zones as $zone) {
            $zoneId = $zone['shipzone_id'];
            $locationData = (isset($zoneLocations[$zoneId])) ? $zoneLocations[$zoneId] : array();
            $statesArr = $zoneLocations[$zoneId]['statesArr'];
            unset($locationData['statesArr']);
            
            $tempArr = array_unique(array_column($locationData, 'country_id'));
            $locationData = array_intersect_key($locationData, $tempArr);
            
            /* $countryNames = array_column($locationData, 'country_name');
            $countryNames = array_unique($countryNames); */
            $zoneIds = array_column($locationData, 'shiploc_zone_id');
            /*if (in_array(-1, $zoneIds)) {
                 $countryNames = array(Labels::getLabel("LBL_REST_OF_THE_WORLD", $siteLangId)); 
                    $locationData = array(
                    'shiploc_country_id' => -1,
                    'country_name' => Labels::getLabel("LBL_REST_OF_THE_WORLD", $siteLangId),
                    'country_id' => -1
                ); 
            } */
            $shipProZoneId = $zone['shipprozone_id'];
            $shipRates = (isset($shipRatesData[$shipProZoneId])) ? $shipRatesData[$shipProZoneId] : array();
            ?>
            <li class="list-group-item zoneRates-js">
                <div class="row justify-content-between">
                    <div class="col">
                        <div class="shipping-states">
                            <span class="box-icon"><i class="fa fa-globe"> </i></span>
                            <div class="detail">
                                <h6><?php echo $zone['shipzone_name'] ?></h6>
                                <div><?php /*  <span> echo implode(', ', $countryNames); </span> */ ?>
                                <?php 
                                $totalCounties = count($locationData);
                                $index = 1;
                                foreach($locationData as $location) { 
                                    $countryId = FatUtility::int($location['country_id']);
                                    $attachedStates = [];
                                    $countryName = Labels::getLabel("LBL_REST_OF_THE_WORLD", $siteLangId);
                                    if ($countryId > 0) {
                                        $attachedStates = $statesArr[$countryId];
                                        $countryName = $location['country_name'];
                                    }
                                    ?>
                                    <small <?php if (count($attachedStates) > 0) { ?> data-toggle="tooltip" data-placement="top" title="<?php echo implode(', ', $attachedStates);?>" <?php } ?> ><?php echo $countryName;?></small><?php echo ($index < $totalCounties) ? ', ' : " "; ?>
                                    
                                <?php 
                                    $index++;
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row no-gutters"><div class="col"></div></div>
                    </div>
                    <div class="col-auto">
                        <?php if ($canEdit) { ?>
                            <ul class="actions">
                                <li>
                                    <a href="javascript:void(0);" onClick="zoneForm(<?php echo $profile_id; ?>, <?php echo $zone['shipzone_id'] ?>)" title="<?php echo Labels::getLabel("LBL_Edit_Zone", $siteLangId); ?>"><i class="fa fa-edit"></i></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" onClick="deleteZone(<?php echo $shipProZoneId ?>)" title="<?php echo Labels::getLabel("LBL_Delete_Zone", $siteLangId); ?>"><i class="fa fa-trash"></i></a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" title="<?php echo Labels::getLabel("LBL_Add_Rates", $siteLangId); ?>" onclick="addEditShipRates(<?php echo $shipProZoneId; ?>, 0);"><i class="fa fa-plus-square"></i></a>
                                </li>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
                <?php if (!empty($shipRates)) { ?>
                    <div class="scroll scroll-x js-scrollable table-wrap">
                        <table class="table table-justified table-rates mt-3">
                            <thead>
                                <tr>
                                    <th><?php echo Labels::getLabel("LBL_Rate_Name", $siteLangId); ?></th>
                                    <?php /* <th><?php echo Labels::getLabel("LBL_Conditions", $siteLangId); ?></th> */ ?>
                                    <th><?php echo Labels::getLabel("LBL_Duration(Days)", $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel("LBL_Cost", $siteLangId); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipRates as $rate) { ?>
                                    <tr>
                                        <td><?php echo $rate['shiprate_rate_name']; ?>
                                        </td>
                                        <?php /* <td>
                                            <?php
                                            if ($rate['shiprate_condition_type'] > 0) {
                                                if ($rate['shiprate_condition_type'] == ShippingRate::CONDITION_TYPE_PRICE) {
                                                    echo CommonHelper::displayMoneyFormat($rate['shiprate_min_val']) . ' - ' . CommonHelper::displayMoneyFormat($rate['shiprate_max_val']);
                                                } else {
                                                    echo $rate['shiprate_min_val'] . ' - ' . $rate['shiprate_max_val'];
                                                }
                                            } else {
                                                echo '—';
                                            }
                                            ?>
                                        </td> */ ?>
                                        <td><?php echo $rate['shiprate_min_duration'] . ' (' . Labels::getLabel("LBL_Days", $siteLangId) . ')'; ?></td>
                                        <td><?php echo CommonHelper::displayMoneyFormat($rate['shiprate_cost']); ?></td>
                                        <td>
                                            <?php if ($canEdit) { ?>
                                                <ul class="actions">
                                                    <li>
                                                        <a href="javascript:void(0);" onclick="addEditShipRates(<?php echo $rate['shiprate_shipprozone_id'] ?>, <?php echo $rate['shiprate_id'] ?>);" title="<?php echo Labels::getLabel("LBL_Edit", $siteLangId); ?>"><i class="fa fa-edit"></i></a>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0);" onClick="deleteRate(<?php echo $rate['shiprate_id'] ?>)" title="<?php echo Labels::getLabel("LBL_Delete", $siteLangId); ?>"><i class="fa fa-trash"></i></a>
                                                    </li>
                                                </ul>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                } else {
                    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
                    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
                }
                ?>
            </li>
        <?php } ?>
    </ul>
    <?php
} else {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}
