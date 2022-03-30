<?php

class PickupAddressesController extends AdminBaseController
{
    public function __construct($action)
    {
        /* FatApp::redirectUser(UrlHelper::generateUrl('home'));
        FatUtility::dieJsonError(Labels::getLabel('LBL_INVALID_REQUEST', $this->admin_id)); */
        parent::__construct($action);
        $this->objPrivilege->canViewPickupAddresses();
    }

    public function index()
    {
        $this->set('canEdit', $this->objPrivilege->canEditPickupAddresses($this->admin_id, true));
		$this->_template->addJs(['js/intlTelInput.min.js']);
        $this->_template->addCss(['css/intlTelInput.css']);
        $this->_template->render();
    }

    public function search()
    {
        $address = new Address(0, $this->adminLangId);
        $addresses = $address->getData(Address::TYPE_ADMIN_PICKUP, 0);
        $this->set('arr_listing', $addresses);
        $this->set('canEdit', $this->objPrivilege->canEditPickupAddresses($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function form($addressId = 0, $langId = 0)
    {
        $this->objPrivilege->canEditPickupAddresses();
        $stateId = 0;
        $slotData = [];
        $allowSale = FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0);
        $langId = FatUtility::int($langId);
        if ($langId == 0) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1);
        }
        $addressId = FatUtility::int($addressId);

        $frm = $this->getForm($addressId, $langId);
        $availability = TimeSlot::DAY_INDIVIDUAL_DAYS;
        if (0 < $addressId) {
            $address = new Address($addressId, $langId);
            $data = $address->getData(Address::TYPE_ADMIN_PICKUP, 0);
            if ($data === false) {
                FatUtility::dieWithError($this->str_invalid_request);
            }
			
			$this->set('countryIso', $data['addr_country_iso']);

            $countryId = $data['addr_country_id'];
            $stateId = $data['addr_state_id'];
            $timeSlots = [];
            if ($allowSale) {
                $timeSlot = new TimeSlot();
                $timeSlots = $timeSlot->timeSlotsByAddrId($addressId);
                $timeSlotsRow = current($timeSlots);
                $availability = isset($timeSlotsRow['tslot_availability']) ? $timeSlotsRow['tslot_availability'] : 0;

                if ($availability == TimeSlot::DAY_ALL_DAYS) {
                    $data['tslot_from_all'] = date('H:i', strtotime($timeSlotsRow['tslot_from_time']));
                    $data['tslot_to_all'] = date('H:i', strtotime($timeSlotsRow['tslot_to_time']));
                }

                $data['tslot_availability'] = $availability;
            }


            /* country and states */
            $countryId = (isset($shopDetails['addr_country_id'])) ? $shopDetails['addr_country_id'] : FatApp::getConfig('CONF_COUNTRY',     FatUtility::VAR_INT, 223);
            $shopDetails['shop_country_code'] = Countries::getCountryById($countryId, $langId, 'country_id');
            $stateObj = new States();
            $statesArr = $stateObj->getStatesByCountryId($countryId, $langId, true, 'state_id');

            $frm->getField('addr_state_id')->options = $statesArr;
            $data['addr_state_id'] = $stateId;
            /* ---  */

            $frm->fill($data);

            if (!empty($timeSlots)) {
                foreach ($timeSlots as $key => $slot) {
                    $slotData['tslot_day'][$slot['tslot_day']] = $slot['tslot_day'];
                    $slotData['tslot_from_time'][$slot['tslot_day']][] = $slot['tslot_from_time'];
                    $slotData['tslot_to_time'][$slot['tslot_day']][] = $slot['tslot_to_time'];
                }
            }
        }

        $this->set('availability', $availability);
        $this->set('addressId', $addressId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('langId', $langId);
        $this->set('allowSale', $allowSale);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->set('slotData', $slotData);
        $this->_template->render(false, false);
    }

    private function getForm($addressId = 0, $langId)
    {
        $addressId = FatUtility::int($addressId);
        $frm = new Form('frmAddress');
        $frm->addHiddenField('', 'addr_id', $addressId);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $langId), 'lang_id', Language::getAllNames(), $langId, array(), '');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $langId), 'addr_title');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $langId), 'addr_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line_1', $langId), 'addr_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line_2', $langId), 'addr_address2');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Country', $langId), 'addr_country_id', $countriesArr, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId))->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $langId), 'addr_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId))->requirement->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_City', $langId), 'addr_city');

        $zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $langId), 'addr_zip');
        /* $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $langId)); */

        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $langId), 'addr_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $langId));

        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $frm->addHiddenField(Labels::getLabel('LBL_Latitude', $this->adminLangId), 'addr_lat', '', array('id' => 'lat'));
            $frm->addHiddenField(Labels::getLabel('LBL_Longitude', $this->adminLangId), 'addr_lng', '', array('id' => 'lng'));
        } else {
            $frm->addRequiredField(Labels::getLabel('LBL_Latitude', $this->adminLangId), 'addr_lat', '', array('id' => 'lat'));
            $frm->addRequiredField(Labels::getLabel('LBL_Longitude', $this->adminLangId), 'addr_lng', '', array('id' => 'lng'));
        }

        $slotTimingsTypeArr = TimeSlot::getSlotTypeArr($this->adminLangId);
        $frm->addRadioButtons(Labels::getLabel('LBL_Slot_Timings', $this->adminLangId), 'tslot_availability', $slotTimingsTypeArr, TimeSlot::DAY_INDIVIDUAL_DAYS);

        $daysArr = TimeSlot::getDaysArr($this->adminLangId);
        for ($i = 0; $i < count($daysArr); $i++) {
            $frm->addCheckBox($daysArr[$i], 'tslot_day[' . $i . ']', $i, array(), false);
            $frm->addSelectBox(Labels::getLabel('LBL_From', $this->adminLangId), 'tslot_from_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
            $frm->addSelectBox(Labels::getLabel('LBL_To', $this->adminLangId), 'tslot_to_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
            $frm->addButton('', 'btn_add_row[' . $i . ']', '+');
        }

        $frm->addSelectBox(Labels::getLabel('LBL_From', $this->adminLangId), 'tslot_from_all', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));
        $frm->addSelectBox(Labels::getLabel('LBL_To', $this->adminLangId), 'tslot_to_all', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    public function setup()
    {
        $this->objPrivilege->canEditPickupAddresses();
        $post = FatApp::getPostedData();
        $allowSale = FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0);
        $availability = FatApp::getPostedData('tslot_availability', FatUtility::VAR_INT, 1);
        $post['addr_phone'] = !empty($post['addr_phone']) ? ValidateElement::convertPhone($post['addr_phone']) : '';
        $addrStateId = FatUtility::int($post['addr_state_id']);

		$isoCode = FatApp::getPostedData('addr_country_iso', FatUtility::VAR_STRING, "");
		$dialCode = FatApp::getPostedData('addr_dial_code', FatUtility::VAR_STRING, "");

        $slotFromAll = '';
        $slotToAll = '';
        $slotDays = [];
        if ($allowSale) {
            if ($availability == TimeSlot::DAY_ALL_DAYS) {
                $slotFromAll = $post['tslot_from_all'];
                $slotToAll = $post['tslot_to_all'];
            } else {
                $slotDays = isset($post['tslot_day']) ? $post['tslot_day'] : array();
                $slotFromTime = $post['tslot_from_time'];
                $slotToTime = $post['tslot_to_time'];
            }
        }

        $frm = $this->getForm($post['addr_id'], $post['lang_id']);
        $postedData = $frm->getFormDataFromArray($post);
        if (false === $postedData) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $addressId = $post['addr_id'];
        unset($post['addr_id']);
		
        $address = new Address($addressId);
        $data = $post;
		$data['addr_country_iso'] = $isoCode;
		$data['addr_dial_code'] = $dialCode;
        $data['addr_state_id'] = $addrStateId;
        $data['addr_lang_id'] = $post['lang_id'];
        $data['addr_type'] = Address::TYPE_ADMIN_PICKUP;
        $address->assignValues($data);
        if (!$address->save()) {
            Message::addErrorMessage($address->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($allowSale) {
            $updatedAddressId = $address->getMainTableRecordId();
            if (!FatApp::getDb()->deleteRecords(TimeSlot::DB_TBL, array('smt' => 'tslot_type = ? and tslot_record_id = ?', 'vals' => array(Address::TYPE_ADMIN_PICKUP, $updatedAddressId)))) {
                Message::addErrorMessage(FatApp::getDb()->getError());
                FatUtility::dieWithError(Message::getHtml());
            }

            if (!empty($slotDays) && $availability == TimeSlot::DAY_INDIVIDUAL_DAYS) {
                foreach ($slotDays as $day) {
                    foreach ($slotFromTime[$day] as $key => $fromTime) {
                        if (!empty($fromTime) && !empty($slotToTime[$day][$key])) {
                            $slotData['tslot_type'] = Address::TYPE_ADMIN_PICKUP;
                            $slotData['tslot_availability'] = $availability;
                            $slotData['tslot_record_id'] = $updatedAddressId;
                            $slotData['tslot_day'] = $day;
                            $slotData['tslot_from_time'] = $fromTime;
                            $slotData['tslot_to_time'] = $post['tslot_to_time'][$day][$key];
                            $timeSlot = new TimeSlot();
                            $timeSlot->assignValues($slotData);
                            if (!$timeSlot->save()) {
                                Message::addErrorMessage($timeSlot->getError());
                                FatUtility::dieJsonError(Message::getHtml());
                            }
                        }
                    }
                }
            }

            if ($availability == TimeSlot::DAY_ALL_DAYS && !empty($slotFromAll) && !empty($slotToAll)) {
                $daysArr = TimeSlot::getDaysArr($this->adminLangId);
                for ($i = 0; $i < count($daysArr); $i++) {
                    $slotData['tslot_type'] = Address::TYPE_ADMIN_PICKUP;
                    $slotData['tslot_availability'] = $availability;
                    $slotData['tslot_record_id'] = $updatedAddressId;
                    $slotData['tslot_day'] = $i;
                    $slotData['tslot_from_time'] = $slotFromAll;
                    $slotData['tslot_to_time'] = $slotToAll;
                    $timeSlot = new TimeSlot();
                    $timeSlot->assignValues($slotData);
                    if (!$timeSlot->save()) {
                        Message::addErrorMessage($timeSlot->getError());
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
            }
        }
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRecord()
    {
        $this->objPrivilege->canEditPickupAddresses();
        $addressId = FatApp::getPostedData('id', FatUtility::VAR_INT, 0);
        if ($addressId < 1) {
            FatUtility::dieJsonError($this->str_invalid_request_id);
        }
        if (!FatApp::getDb()->deleteRecords(Address::DB_TBL, array('smt' => 'addr_type = ? AND addr_id = ?', 'vals' => array(Address::TYPE_ADMIN_PICKUP, $addressId)))) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }
}
