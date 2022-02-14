<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<ul class="list-group list-shipping">
    <?php if (!empty($locationsData)) { 
        foreach($locationsData as $countryId => $locations) { 
        $countryLocs = $locations['locations'];
        $statesArr = array_column($countryLocs, 'state_name');
        ?>
        <li class="list-group-item zoneRates-js">
            <div class="row justify-content-between">
                <div class="col">
                    <div class="shipping-states"><span class="box-icon"><i class="fa fa-globe"> </i></span>
                        <div class="detail">
                            <h6><?php echo ($countryId == -1) ? Labels::getLabel('LBL_Shipable_to_All_Locations', $siteLangId) :$locations['country_name']; ?></h6>
                            <?php if ($countryId != -1) { ?>
                                <div><small><?php echo implode(', ', $statesArr); ?></small></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    <?php } 
    } else { ?>
        <li class="list-group-item zoneRates-js"> 
            <div class="row justify-content-between">
                <div class="col">
                    <div class="shipping-states"><span class="box-icon"><i class="fa fa-globe"> </i></span>
                        <div class="detail">
                            <h6><?php echo Labels::getLabel('LBL_Not_Shipable', $siteLangId); ?></h6>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    <?php } ?>
</ul>