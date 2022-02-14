<?php
class ShippingZonesController extends SellerBaseController
{
    public function __construct($action)
    {
        parent::__construct($action);
        $this->userPrivilege->canViewShippingProfiles(UserAuthentication::getLoggedUserId());
    }

    public function search($profileId)
    {
        $srch = ShippingProfileZone::getSearchObject();
        $srch->addCondition("shipprozone_shipprofile_id", "=", $profileId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $zones = FatApp::getDb()->fetchAll($rs);
        $zoneLocations = [];
        $shipRates = [];
        if (!empty($zones)) {
            $zoneIds = array_column($zones, 'shipzone_id');
            $zoneIds = array_map('intval', $zoneIds);
            $zoneLocations = $this->getLocations($zoneIds, false, true);
            $shipProZoneIds = array_column($zones, 'shipprozone_id');
            $shipProZoneIds = array_map('intval', $shipProZoneIds);
            $shipRates = $this->getRates($shipProZoneIds);
        }

        $this->set("zones", $zones);
        $this->set("zoneLocations", $zoneLocations);
        $this->set("shipRatesData", $shipRates);
        $this->set("profile_id", $profileId);
        $this->set('canEdit', $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    public function autoCompleteZone()
    {
        $userId = UserAuthentication::getLoggedUserId();
        $post = FatApp::getPostedData();
        $srch = ShippingZone::getSearchObject();
        $srch->addOrder('shipzone_name');
        $srch->addCondition('shipzone_user_id', '=', $userId); //== only admin added zones
        if (!empty($post['keyword'])) {
            $srch->addCondition('shipzone_name', 'LIKE', '%' . $post['keyword'] . '%');
        }
        $srch->addMultipleFields(array('shipzone_id as id', 'shipzone_name'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $zones = $db->fetchAll($rs, 'id');
        $json = array();
        foreach ($zones as $key => $option) {
            $json[] = array(
                'id' => $key,
                'name' => strip_tags(html_entity_decode($option['shipzone_name'], ENT_QUOTES, 'UTF-8'))
            );
        }
        die(json_encode($json));
    }

    public function form($profileId, $zoneId = 0)
    {
        $userId = UserAuthentication::getLoggedUserId();
        $profileId = FatUtility::int($profileId);
        $zoneId = FatUtility::int($zoneId);
        $data = array();
        $zoneLocations = array();
        if (0 < $zoneId) {
            //$data = ShippingProfileZone::getAttributesById($zoneId);
            $srch = ShippingProfileZone::getSearchObject();
            $srch->addCondition("shipprozone_shipprofile_id", "=", $profileId);
            $srch->addCondition("shipprozone_shipzone_id", "=", $zoneId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $data = FatApp::getDb()->fetch($rs);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
            $zoneLocations = $this->getLocations($zoneId);
        }
        $zones = Zone::getZoneWithCountries($this->siteLangId, true);
        $excludeLocations = $this->getExcludeLocations($profileId, $zoneId);

        $this->set('profile_id', $profileId);
        $this->set('zone_id', $zoneId);
        $this->set('zones', $zones);
        $this->set('zone_data', $data);
        $this->set('zoneLocations', $zoneLocations);
        $this->set('excludeLocations', $excludeLocations);
        $this->set('userId', $userId);
        $this->_template->render(false, false);
    }

    public function getExcludeLocations($profileId, $zoneId)
    {
        $srch = ShippingProfileZone::getSearchObject();
        $srch->joinTable(ShippingZone::DB_SHIP_LOC_TBL, 'LEFT OUTER JOIN', 'zoneLoc.shiploc_shipzone_id = spzone.shipprozone_shipzone_id', 'zoneLoc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $cnd = $srch->addCondition('shipprozone_shipprofile_id', '=', $profileId);
        $cnd->attachCondition('shipprozone_shipzone_id', '!=', $zoneId, 'AND', false);
        $rs = $srch->getResultSet();
        $zoneLocations = FatApp::getDb()->fetchAll($rs);
        return $zoneLocations;
    }

    public function searchStates($countryId, $zoneId, $shipZoneId, $profileId, $selected = 0)
    {
        $stateObj = new States();
        $states = $stateObj->getStatesByCountryId($countryId, $this->siteLangId, true);
        $zoneLocations = $this->getLocations($shipZoneId);
        $excludeLocations = $this->getExcludeLocations($profileId, $shipZoneId);

        $this->set("states", $states);
        $this->set("countryId", $countryId);
        $this->set("zoneId", $zoneId);
        $this->set("shipZoneId", $shipZoneId);
        $this->set("selected", $selected);
        $this->set("zoneLocations", $zoneLocations);
        $this->set("excludeLocations", $excludeLocations);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        
        if (isset($post['shipzone_name']) && empty(trim($post['shipzone_name']))) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shipZoneId = (isset($post['shipzone_id'])) ? $post['shipzone_id'] : 0;
        $msg = 0 < $shipZoneId ? Labels::getLabel('LBL_UPDATED_SUCCESSFULLY', $this->siteLangId) : Labels::getLabel('LBL_ADDED_SUCCESSFULLY', $this->siteLangId);

        if (!$this->checkForLocations($post['shipzone_profile_id'], $shipZoneId, $post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Locations_already_added_in_other_zone_of_same_profile', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        unset($post['shipzone_id']);
        $sObj = new ShippingZone($shipZoneId);
        $sObj->assignValues($post);
        if (!$sObj->save()) {
            Message::addErrorMessage($sObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shipZoneId = $sObj->getMainTableRecordId();
        
        $db = FatApp::getDb();
        $db->startTransaction();
        $shipProZoneId = (isset($post['shipprozone_id'])) ? $post['shipprozone_id'] : 0;
        $data = array(
            'shipprozone_shipprofile_id' => $post['shipzone_profile_id'],
            'shipprozone_shipzone_id' => $shipZoneId
        );

        $spObj = new ShippingProfileZone($shipProZoneId);
        $spObj->assignValues($data);
        if (!$spObj->save($data)) {
            Message::addErrorMessage($spObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shipProZoneId = $spObj->getMainTableRecordId();
        ShippingProfile::setDefaultRates($shipProZoneId, $post['shipzone_profile_id']);
        
        if ($shipZoneId > 0) {
            if (!$this->eligibleForUpdateLocations($shipZoneId, $post)) {
                $db->rollbackTransaction();
                Message::addErrorMessage(Labels::getLabel('LBL_This_zone_is_Also_used_with_another_profile._Please_change_the_zone_name_to_update_it.', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
            if (!$this->setupLocations($post, $shipZoneId)) {
                Message::addErrorMessage(Labels::getLabel('LBL_Unable_to_update_locations', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        $db->commitTransaction();
        $this->set('msg', $msg);
        $this->set('zoneId', $shipZoneId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteZone($shipprozoneId)
    {
        $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId());
        $shipprozoneId = FatUtility::int($shipprozoneId);
        $shippingProfData = ShippingProfileZone::getAttributesById($shipprozoneId);

        if (false == $shippingProfData) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shippingProfileId = $shippingProfData['shipprozone_shipprofile_id'];
        $allZones = ShippingProfileZone::getAttributesByProfileId($shippingProfileId, null, true);
        if (is_array($allZones) && 1 == count($allZones)) {
            $msg = Labels::getLabel('MSG_PLEASE_MAINTAIN_ATLEASE_ONE_SHIPPING_ZONE', $this->siteLangId);
            Message::addErrorMessage($msg);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $sObj = new ShippingProfileZone($shipprozoneId);
        if (!$sObj->deleteRecord()) {
            Message::addErrorMessage($sObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* [ DELETE ZONE LOCATIONS IF ZONE IS NOT ATTACHED WITH OTHER PROFILES */
        $srch = ShippingProfileZone::getSearchObject();
        $srch->addCondition('shipprozone_shipzone_id', '=', $shipprozoneId);
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $zoneCount = $srch->recordCount();
        
        if (1 >= $srch->recordCount()) {
            $sZoneObj = new ShippingZone();
            if (!$sZoneObj->deleteLocations($shipprozoneId)) {
                Message::addErrorMessage($sZoneObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }
        /* ] */
        
        
        /* delete zone attached data[rates] */
        $sObj = new ShippingZone($shippingProfData['shipprozone_shipzone_id']);
        if (!$sObj->deleteRates($shipprozoneId)) {
            Message::addErrorMessage($sObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$sObj->deleteRecord()) {
            Message::addErrorMessage($sObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Zone_Deleted_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function getLocations($zoneIds, $isAjax = false, $includeStates = false)
    {
        if (empty($zoneIds)) {
            return array();
        }
        $locSrch = ShippingZone::getZoneLocationSearchObject($this->siteLangId, $includeStates);
        if (is_array($zoneIds)) {
            $locSrch->addCondition('shiploc_shipzone_id', 'IN', $zoneIds);
        } else {
            $locSrch->addCondition('shiploc_shipzone_id', '=', $zoneIds);
        }
        $locSrch->doNotCalculateRecords();
        $locSrch->doNotLimitRecords();
        $locRs = $locSrch->getResultSet();
        $zoneLocations = FatApp::getDb()->fetchAll($locRs);
        $zoneLocationsData = $zoneLocations;
        if ($isAjax) {
            die(json_encode($zoneLocations));
        } else {
            $zoneLocationData = [];
            $zoneStateArr = [];
            
            if (!empty($zoneLocations) && is_array($zoneIds)) {
                foreach ($zoneLocations as $location) {
                    $zoneId = $location['shiploc_shipzone_id'];
                    $countryId = $location['shiploc_country_id'];
                    $zoneLocationData[$zoneId][] = $location;
                    if ($includeStates) {
                        $zoneLocationData[$zoneId]['statesArr'][$countryId][] = ($location['shiploc_state_id']) == -1 ? Labels::getLabel("LBL_ALL", $this->siteLangId) : $location['state_name'];
                    }
                }
                $zoneLocationsData = $zoneLocationData;
            }
        }
        return $zoneLocationsData;
    }

    private function checkForLocations($profileId, $shipZoneId, $data)
    {
        $excludeLocations = $this->getExcludeLocations($data['shipzone_profile_id'], $shipZoneId);

        if (!empty($excludeLocations)) {
            $isRestOfWorld = (isset($data['rest_of_the_world'])) ? $data['rest_of_the_world'] : 0;
            $postedCountries = (isset($data['shiploc_country_ids'])) ? $data['shiploc_country_ids'] : array();
            $postedStates = (isset($data['shiploc_state_ids'])) ? $data['shiploc_state_ids'] : array();
            $countryIds = array();
            $stateIds = array();

            if (!empty($postedCountries)) {
                foreach ($postedCountries as $countryData) {
                    $arr = explode('-', $countryData);
                    $countryIds[] = $arr[1];
                }
            }

            if (!empty($postedStates)) {
                foreach ($postedStates as $statesData) {
                    $arr = explode('-', $statesData);
                    $stateIds[] = $arr[2];
                }
            }

            $oldZone = array_filter(array_column($excludeLocations, 'shiploc_zone_id'));
            $oldCountries = array_filter(array_column($excludeLocations, 'shiploc_country_id'));
            $oldStates = array_filter(array_column($excludeLocations, 'shiploc_state_id'));
            if ((in_array($isRestOfWorld, $oldZone)) || array_intersect($countryIds, $oldCountries) || array_intersect($stateIds, $oldStates)) {
                return false;
            }
            return true;
        }
        return true;
    }

    private function eligibleForUpdateLocations($zoneId, $data)
    {
        $profileId = $data['shipzone_profile_id'];
        /*[ check if zone if also attached to another profile */
        $srch = ShippingProfileZone::getSearchObject();
        $srch->addCondition('shipprozone_shipzone_id', '=', $zoneId);
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $zoneCount = $srch->recordCount();
        /* ] */
        if ($zoneCount > 1) {
            $zoneLocationData = $this->getLocationsToCompare($zoneId);
            $countries = (isset($zoneLocationData['countries'])) ? $zoneLocationData['countries'] : array();

            $isRestOfWorld = $zoneLocationData['isRestOfWorld'];

            $states = (isset($zoneLocationData['states'])) ? $zoneLocationData['states'] : array();

            $countriesList = array();
            if (isset($data['shiploc_country_ids'])) {
                $countryData = $data['shiploc_country_ids'];
                foreach ($countryData as $data) {
                    $arr = explode('-', $data);
                    $countriesList[] = $arr[1];
                }
                sort($countriesList);
            }
            $statesList = array();
            if (isset($data['shiploc_state_ids'])) {
                $statesData = $data['shiploc_state_ids'];
                foreach ($statesData as $data) {
                    $arr = explode('-', $data);
                    $statesList[] = $arr[2];
                }
                sort($statesList);
            }

            $restOfTheWorld = 0;
            $newRestOfTheWorld = 0;
            if (!empty($isRestOfWorld)) {
                $restOfTheWorld = $isRestOfWorld['shiploc_zone_id'];
            }
            if (isset($data['rest_of_the_world'])) {
                $newRestOfTheWorld = $data['rest_of_the_world'];
            }

            if ((!empty($countries) || !empty($states) || !empty($isRestOfWorld)) && ($countries != $countriesList || $states != $statesList || $restOfTheWorld != $newRestOfTheWorld)) {
                return false;
            }
        }
        return true;
    }

    private function getLocationsToCompare($zoneId)
    {
        $locSrch = new SearchBase(ShippingZone::DB_SHIP_LOC_TBL, 'szone');
        $locSrch->addCondition('shiploc_shipzone_id', '=', $zoneId);

        $locSrch->doNotCalculateRecords();
        $locSrch->doNotLimitRecords();
        $stateSrch = clone $locSrch;
        $zoneSrch = clone $locSrch;

        $zoneSrch->addCondition('shiploc_zone_id', '=', '-1');
        $zoneSrch->addFld('shiploc_zone_id');
        $zoneRs = $zoneSrch->getResultSet();
        $isRestOfWorld = FatApp::getDb()->fetch($zoneRs);

        $locSrch->addCondition('shiploc_state_id', '=', '-1');
        $locSrch->addCondition('shiploc_zone_id', '!=', '-1');
        $locSrch->addFld('shiploc_country_id');
        $locRs = $locSrch->getResultSet();
        $countries = FatApp::getDb()->fetchAll($locRs);
        $countriesList = [];

        if (!empty($countries)) {
            $countriesList = array_column($countries, 'shiploc_country_id');
            sort($countriesList);
        }

        $stateSrch->addCondition('shiploc_state_id', '>', '-1');
        $stateSrch->addCondition('shiploc_zone_id', '!=', '-1');
        $stateSrch->addMultipleFields(array('shiploc_state_id'));
        $stateRs = $stateSrch->getResultSet();
        $states = FatApp::getDb()->fetchAll($stateRs);

        $statesList = [];
        if (!empty($states)) {
            $statesList = array_column($states, 'shiploc_state_id');
            sort($statesList);
        }

        return array('countries' => $countriesList, 'states' => $statesList, 'isRestOfWorld' => $isRestOfWorld);
    }

    private function getRates($zoneIds)
    {
        if (empty($zoneIds)) {
            return array();
        }
        $rateSrch = ShippingRate::getSearchObject($this->siteLangId);
        $rateSrch->addCondition('shiprate_shipprozone_id', 'IN', $zoneIds);
        $rateSrch->addMultipleFields(array('srate.*', 'if(ratelang.shiprate_name is null, shiprate_identifier, ratelang.shiprate_name) as shiprate_rate_name'));
        $rateSrch->doNotCalculateRecords();
        $rateSrch->doNotLimitRecords();
        $rateRs = $rateSrch->getResultSet();

        $shipRates = FatApp::getDb()->fetchAll($rateRs);
        $shipRatesData = [];
        if (!empty($shipRates)) {
            foreach ($shipRates as $rate) {
                $zoneId = $rate['shiprate_shipprozone_id'];
                $shipRatesData[$zoneId][] = $rate;
            }
        }
        return $shipRatesData;
    }

    private function setupLocations($data, $shipZoneId)
    {
        $excludeCountries = (isset($data['selected_countries']) && trim($data['selected_countries']) != "") ? explode(',', $data['selected_countries'])  : [];
    
        $sZoneObj = new ShippingZone();
        if (!$sZoneObj->deleteLocations($shipZoneId, $excludeCountries)) {
            return false;
        }

        if (isset($data['rest_of_the_world'])) {
            $dataToAdd = array(
                //'shiploc_shipprofile_id' => $data['shipzone_profile_id'],
                'shiploc_zone_id' => -1,
                'shiploc_country_id' => -1,
                'shiploc_state_id' => -1,
                'shiploc_shipzone_id' => $shipZoneId
            );
            if (!$sZoneObj->updateLocations($dataToAdd)) {
                //Message::addErrorMessage($sZoneObj->getError());
                //FatUtility::dieJsonError(Message::getHtml());
                return false;
            }
        } elseif (isset($data['shiploc_country_ids'])) {
            foreach ($data['shiploc_country_ids'] as $countryData) {
                $arr = explode('-', $countryData);
                $zoneId = $arr[0];
                $countryId = $arr[1];
                $dataToAdd = array(
                    //'shiploc_shipprofile_id' => $data['shipzone_profile_id'],
                    'shiploc_zone_id' => $zoneId,
                    'shiploc_country_id' => $countryId,
                    'shiploc_state_id' => -1,
                    'shiploc_shipzone_id' => $shipZoneId
                );

                if (!$sZoneObj->updateLocations($dataToAdd)) {
                    //Message::addErrorMessage($sZoneObj->getError());
                    //FatUtility::dieJsonError(Message::getHtml());
                    return false;
                }
            }
        }
        if (isset($data['shiploc_state_ids'])) {
            $countryIds = array();
            if (isset($data['shiploc_country_ids'])) {
                foreach ($data['shiploc_country_ids'] as $countryData) {
                    $arr = explode('-', $countryData);
                    $countryIds[] = $arr[1];
                }
            }
            foreach ($data['shiploc_state_ids'] as $stateData) {
                $arr = explode('-', $stateData);
                $zoneId = $arr[0];
                $countryId = $arr[1];
                $stateId = $arr[2];
                if (!in_array($countryId, $countryIds)) { // == already added data for country
                    $dataToAdd = array(
                        //'shiploc_shipprofile_id' => $data['shipzone_profile_id'],
                        'shiploc_zone_id' => $zoneId,
                        'shiploc_country_id' => $countryId,
                        'shiploc_state_id' => $stateId,
                        'shiploc_shipzone_id' => $shipZoneId
                    );
                    if (!$sZoneObj->updateLocations($dataToAdd)) {
                        //Message::addErrorMessage($sZoneObj->getError());
                        //FatUtility::dieJsonError(Message::getHtml());
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
