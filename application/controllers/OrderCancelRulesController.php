<?php

class OrderCancelRulesController extends SellerBaseController
{

    public function __construct($action)
    {
        if (!FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) {
            die(Labels::getLabel('MSG_Order_Cancel_Penalty_module_is_not_enabled', CommonHelper::getLangId()));
        }
        parent::__construct($action);
    }

    public function index()
    {
        $data = OrderCancelRule::getSellerDefaultCancleRules($this->userParentId);
        if (empty($data)) {
            $dataToSaveArr[0] = array(
                'ocrule_duration_min' => 0,
                'ocrule_duration_max' => 24,
                'ocrule_refund_amount' => 0,
                'ocrule_user_id' => $this->userParentId,
                'ocrule_active' => applicationConstants::INACTIVE,
                'ocrule_is_default' => OrderCancelRule::MIN_VALUE
            );
            $dataToSaveArr[1] = array(
                'ocrule_duration_min' => 24,
                'ocrule_duration_max' => -1,
                'ocrule_refund_amount' => 0,
                'ocrule_user_id' => $this->userParentId,
                'ocrule_active' => applicationConstants::INACTIVE,
                'ocrule_is_default' => OrderCancelRule::MAX_VALUE
            );
            foreach ($dataToSaveArr as $dataToSave) {
                if (!FatApp::getDb()->insertFromArray(OrderCancelRule::DB_TBL, $dataToSave)) {
                    Message::addErrorMessage(FatApp::getDb()->getError());
                }
            }
        }
        /*Check Available slotes [*/
        $warningMsg = $this->checkAvailableSlots();
        /* ]*/
        $this->set('data', $data);
        $this->set('warningMsg', $warningMsg);
        $this->set('frm', $this->getForm());
        $this->_template->render();
    }

    public function search()
    {
        $srch = OrderCancelRule::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', $this->userParentId);
        $srch->addOrder('ocrule_duration_min', 'ASC');
        $rs = $srch->getResultSet();
        $arr_listing = FatApp::getDb()->fetchAll($rs);
        $defaultIsActive = true;
        foreach ($arr_listing as $row) {
            if ($row['ocrule_active'] == applicationConstants::INACTIVE && ($row['ocrule_is_default'] == OrderCancelRule::MIN_VALUE || $row['ocrule_is_default'] == OrderCancelRule::MAX_VALUE)) {
                $defaultIsActive = false;
                break;
            }
        }
        $this->set("arr_listing", $arr_listing);
        $this->set("defaultIsActive", $defaultIsActive);
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function form(int $ruleId)
    {
        $data = ['ocrule_id' => $ruleId];
        if (0 < $ruleId) {
            $data = OrderCancelRule::getAttributesById($ruleId);
            if ($data === false || (!empty($data) && $data['ocrule_user_id'] != $this->userParentId)) {
                FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            }
        }

        $minFldShow = true;
        $maxFldShow = true;
        if (!empty($data) && $data['ocrule_id'] > 0) {
            if ($data['ocrule_is_default'] == OrderCancelRule::MIN_VALUE) {
                $minFldShow = false;
            } elseif ($data['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
                $maxFldShow = false;
            }
        }

        $isInfinty = 0;
        if (isset($data['ocrule_duration_max']) && $data['ocrule_duration_max'] < 0) {
            $isInfinty = 1;
        }

        $frm = $this->getForm($minFldShow, $maxFldShow, $isInfinty);
        $frm->fill($data);
        $this->set('ocrule_id', $ruleId);
        $this->set('isInfinty', $isInfinty);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $post = FatApp::getPostedData();
        $isInfinty = 0;
        if (isset($post['infinity_field'])) {
            $isInfinty = 1;
        }

        $minFldShow = true;
        $maxFldShow = true;
        if ($post['ocrule_is_default'] == OrderCancelRule::MIN_VALUE) {
            $minFldShow = false;
        } elseif ($post['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
            $post['ocrule_duration_max'] = -1;
            $maxFldShow = false;
        }

        $frm = $this->getForm($minFldShow, $maxFldShow, $isInfinty);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $activeRules = OrderCancelRule::getSellerDefaultCancleRules($this->userParentId, true);

        if (count($activeRules) < 2) {
            Message::addErrorMessage(Labels::getLabel('MSG_ACTIVATE_BOTH_THE_DEFAULT_RULES_FIRST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post['ocrule_duration_min'] = (isset($post['ocrule_duration_min'])) ? $post['ocrule_duration_min'] : 0;
        $post['ocrule_duration_max'] = (isset($post['ocrule_duration_max'])) ? $post['ocrule_duration_max'] : -1;
        if ($post['ocrule_duration_max'] <= $post['ocrule_duration_min'] && !$post['ocrule_is_default']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Duration_must_be_Greater_Then_Minimum_Duration', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $ruleId = FatApp::getPostedData('ocrule_id', FatUtility::VAR_INT, 0);
        unset($post['ocrule_id']);


        $dataToCheck = ['user_id' => $this->userParentId, 'min_duration' => $post['ocrule_duration_min'], 'max_duration' => $post['ocrule_duration_max'], 'rule_id' => $ruleId, 'is_default' => $post['ocrule_is_default']];
        if (!OrderCancelRule::checkDurationRangeIsValid($dataToCheck)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->siteLangId));
        }


        /* $flag = false;
        if( $ruleId > 0 && ($post['ocrule_is_default'] == OrderCancelRule::MIN_VALUE || $post['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) ){
            if($post['ocrule_is_default'] == OrderCancelRule::MIN_VALUE && $data[0]['ocrule_duration_min'] >= $post['ocrule_duration_max']){
                $flag = true;
            }elseif($post['ocrule_is_default'] == OrderCancelRule::MAX_VALUE && $data[count($data) - 1]['ocrule_duration_max'] <= $post['ocrule_duration_min']){
                $flag = true;
            }
        }else {
            if($data[0]['ocrule_duration_max'] > $post['ocrule_duration_min']) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->siteLangId));
            }elseif($data[count($data) - 1]['ocrule_duration_min'] < $post['ocrule_duration_max']){
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->siteLangId));
            }else{
                for ($i = 1; $i < count($data); $i++) { 
                    if($data[$i]['ocrule_duration_min'] >= $post['ocrule_duration_max'] && $data[$i-1]['ocrule_duration_max'] <= $post['ocrule_duration_min']){
                        $flag = true;
                        break;
                    }
                }
            }
            
        } 

        if ($flag) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->siteLangId));
        } */


        $record = new OrderCancelRule($ruleId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Rule_Setup_Successfully', $this->siteLangId));
        $this->set('ruleId', $record->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewAdminRules()
    {
        $srch = OrderCancelRule::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', 0);
        $srch->addOrder('ocrule_duration_min', 'ASC');
        $srch->addMultipleFields(array('ocrule_duration_min', 'ocrule_duration_max', 'ocrule_refund_amount'));
        $rs = $srch->getResultSet();
        $this->set("arr_listing", FatApp::getDb()->fetchAll($rs));
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    private function getForm($minFldShow = true, $maxFldShow = true, $isInfinty = 0)
    {
        $frm = new Form('frmOrderCancelRules');
        $frm->addHiddenField('', 'ocrule_id');
        $frm->addHiddenField('', 'ocrule_user_id', $this->userParentId);
        $frm->addHiddenField('', 'ocrule_is_default', 0);

        /* if ($minFldShow) { */
        $minFldExtraAtt = ['placeholder' => Labels::getLabel('LBL_Min_hours_for_cancellation', $this->siteLangId)];
        if (!$minFldShow) {
            $minFldExtraAtt['disabled'] = 'disabled';
        }

        $frm->addIntegerField(Labels::getLabel('LBL_Cancellation_Min(Hours)', $this->siteLangId), 'ocrule_duration_min', '', $minFldExtraAtt)->requirements()->setIntPositive();

        /*  } */
        if ($isInfinty == 0) {
            $maxFldExtraAtt = ['placeholder' => Labels::getLabel('LBL_Max_hours_for_cancellation', $this->siteLangId)];
            if (!$maxFldShow) {
                $maxFldExtraAtt['disabled'] = 'disabled';
            }
            $fld = $frm->addIntegerField(Labels::getLabel('LBL_Cancellation_Max(Hours)', $this->siteLangId), 'ocrule_duration_max', '', $maxFldExtraAtt);
            if ($maxFldShow) {
                $fld->requirements()->setFloatPositive();
            }
        }

        if ($isInfinty) {
            $frm->addHiddenField(Labels::getLabel('LBL_Cancellation_Max(Hours)', $this->siteLangId), 'ocrule_duration_max', '');
            $frm->addTextbox(Labels::getLabel('LBL_Cancellation_Max(Hours)', $this->siteLangId), 'infinity_field', Labels::getLabel('LBL_Infinity', $this->siteLangId), array('disabled' => 'disabled'));
        }

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Refund_Amount(Percentage)', $this->siteLangId), 'ocrule_refund_amount', '', array('placeholder' => Labels::getLabel('LBL_Refund_Amount(Percentage)',  $this->siteLangId)));
        $fld->requirements()->setFloatPositive();
        $fld->requirements()->setRange(0, 100);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $ruleId = FatApp::getPostedData('ruleId', FatUtility::VAR_INT, 0);
        $this->markAsDeleted($ruleId);
        FatUtility::dieJsonSuccess(Labels::getLabel('MSG_Record_Deleted', $this->siteLangId));
    }

    public function deleteSelected()
    {

        $ocruleIdsArr = FatUtility::int(FatApp::getPostedData('ocrule_ids'));
        if (empty($ocruleIdsArr)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        foreach ($ocruleIdsArr as $ruleId) {
            $data = OrderCancelRule::getAttributesById($ruleId);
            if ($data['ocrule_is_default'] == OrderCancelRule::MIN_VALUE || $data['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
                FatUtility::dieWithError(Labels::getLabel('MSG_CAN_NOT_DELETE_DEFAULTS', $this->siteLangId));
            }
        }

        foreach ($ocruleIdsArr as $ruleId) {
            if (1 > $ruleId) {
                continue;
            }
            $this->markAsDeleted($ruleId);
        }
        $this->set('msg', Labels::getLabel('MSG_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted(int $ruleId)
    {
        $ruleUserId = OrderCancelRule::getAttributesById($ruleId, 'ocrule_user_id');
        if ($ruleUserId != $this->userParentId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new OrderCancelRule($ruleId);
        if (!$obj->deleteRecord()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    public function changeCancleRuleStatus()
    {
        $ruleId = FatApp::getPostedData('ocruleId', FatUtility::VAR_INT, 0);
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        $orderCancelData = OrderCancelRule::getAttributesById($ruleId);

        if (!$orderCancelData || (!empty($orderCancelData) && $orderCancelData['ocrule_user_id'] != $this->userParentId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $ruleId = $orderCancelData['ocrule_id'];

        if ($status == applicationConstants::ACTIVE) {
            $dataToCheck = ['user_id' => $this->userParentId, 'min_duration' => $orderCancelData['ocrule_duration_min'], 'max_duration' => $orderCancelData['ocrule_duration_max'], 'rule_id' => $ruleId, 'is_default' => $orderCancelData['ocrule_is_default']];

            if (!OrderCancelRule::checkDurationRangeIsValid($dataToCheck)) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->siteLangId));
            }
        }

        $obj = new OrderCancelRule($ruleId);
        if (!$obj->changeStatus($status)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeAllRulesStatus()
    {
        $status = FatApp::getPostedData('status', FatUtility::VAR_INT, 0);
        $data = array('ocrule_active' => $status);
        $where = array('smt' => 'ocrule_user_id = ?', 'vals' => array($this->userParentId));
        if(!FatApp::getDb()->updateFromArray(OrderCancelRule::DB_TBL, $data, $where)){
            Message::addErrorMessage(FatApp::getDb()->getError());
        }
        
        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function checkAvailableSlots()
    {
        $srch = OrderCancelRule::getSearchObject();
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', $this->userParentId);
        $srch->addOrder('ocrule_duration_min', 'ASC');
        $srch->addCondition('ocrule_active', '=', applicationConstants::ACTIVE);

        $rs = $srch->getResultSet();
        $cancleRuleData = FatApp::getdb()->fetchAll($rs);
        $previousMaxHr = 0;
        $warningMsg = array();
        for ($i = 1; $i < count($cancleRuleData); $i++) {
            $previousMaxHr = $cancleRuleData[$i - 1]['ocrule_duration_max'];
            if ($previousMaxHr != $cancleRuleData[$i]['ocrule_duration_min']) {
                $msg = $previousMaxHr . ' - ' . $cancleRuleData[$i]['ocrule_duration_min'];
                array_push($warningMsg, $msg);
            }
        }
        return $warningMsg;
    }
}
