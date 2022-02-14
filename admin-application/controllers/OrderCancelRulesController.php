<?php

class OrderCancelRulesController extends AdminBaseController
{

    private $canEdit;

    public function __construct($action)
    {
        $ajaxCallArray = array('deleteRecord', 'form', 'langForm', 'search', 'setup', 'langSetup');
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        if (!FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) {
            die(Labels::getLabel('LBL_Order_Cancel_Penalty_module_is_not_enabled', $this->adminLangId));
        }

        parent::__construct($action);
        $this->objPrivilege->canViewRentalOrderCancelRules();
        $this->canEdit = $this->objPrivilege->canEditRentalOrderCancelRules(AdminAuthentication::getLoggedAdminId(), true);
        $this->set("canEdit", $this->canEdit);
    }

    public function index()
    {
        /*Check Available slotes [*/
        $warningMsg = $this->checkAvailableSlots();
        /* ]*/
        $this->set('warningMsg', $warningMsg);
        $this->_template->render();
    }

    public function search()
    {
        $srch = OrderCancelRule::getSearchObject($this->adminLangId);
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', 0);
        $srch->addOrder('ocrule_duration_min', 'ASC');
        $rs = $srch->getResultSet();
        $this->set("arr_listing", FatApp::getDb()->fetchAll($rs));
        $this->set('recordCount', $srch->recordCount());
        $this->_template->render(false, false);
    }

    public function form(int $ruleId)
    {
        
        $data = ['ocrule_id' => $ruleId];
        if (0 < $ruleId) {
            $data = OrderCancelRule::getAttributesById($ruleId);
            if ($data === false) {
                FatUtility::dieJsonError($this->str_invalid_request);
            }
        }

        $minFldShow = true;
        $maxFldShow = true;
        if(!empty($data) && $data['ocrule_id'] > 0){
            if($data['ocrule_is_default'] == OrderCancelRule::MIN_VALUE){
                $minFldShow = false;
            }elseif($data['ocrule_is_default'] == OrderCancelRule::MAX_VALUE){
                $maxFldShow = false;
            }
        }

        $frm = $this->getForm($minFldShow, $maxFldShow);
        $frm->fill($data);
        $this->set('ocrule_id', $ruleId);
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setup()
    {
        $this->objPrivilege->canEditOrderCancelReasons();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $minFldShow = true;
        $maxFldShow = true;
        if ($post['ocrule_is_default'] == OrderCancelRule::MIN_VALUE) {
            $minFldShow = false;
        } elseif($post['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
            $maxFldShow = false;
        }

        $frm = $this->getForm($minFldShow, $maxFldShow);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $post['ocrule_duration_min'] = (isset($post['ocrule_duration_min'])) ? $post['ocrule_duration_min'] : 0;
        $post['ocrule_duration_max'] = (isset($post['ocrule_duration_max'])) ? $post['ocrule_duration_max'] : -1;

        if ($post['ocrule_duration_max'] <= $post['ocrule_duration_min'] && !$post['ocrule_is_default']) {
            Message::addErrorMessage(Labels::getLabel('MSG_Maximum_Duration_must_be_Greater_Then_Minimum_Duration', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $ruleId = FatApp::getPostedData('ocrule_id', FatUtility::VAR_INT, 0);
        unset($post['ocrule_id']);

        $dataToCheck = ['user_id' => 0, 'min_duration' => $post['ocrule_duration_min'], 'max_duration' => $post['ocrule_duration_max'], 'rule_id' => $ruleId, 'is_default' => $post['ocrule_is_default']];
        
        if (!OrderCancelRule::checkDurationRangeIsValid($dataToCheck)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_TIME_DURATION', $this->adminLangId));
        }
        
        $record = new OrderCancelRule($ruleId);
        $record->assignValues($post);
        if (!$record->save()) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', $this->str_setup_successful);
        $this->set('ruleId', $record->getMainTableRecordId());
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getForm($minFldShow = true, $maxFldShow = true)
    {
        $frm = new Form('frmOrderCancelRules');
        $frm->addHiddenField('', 'ocrule_id');
        $frm->addHiddenField('', 'ocrule_user_id', 0);

        $frm->addHiddenField('', 'ocrule_is_default',0);
        if ($minFldShow) {
            $frm->addIntegerField(Labels::getLabel('LBL_Cancellation_Min(Hours)', $this->adminLangId), 'ocrule_duration_min')->requirements()->setIntPositive();
        }
        if ($maxFldShow) {
            $frm->addIntegerField(Labels::getLabel('LBL_Cancellation_Max(Hours)', $this->adminLangId), 'ocrule_duration_max')->requirements()->setIntPositive();
        }
        
        $fld = $frm->addFloatField(Labels::getLabel('LBL_Refund_Amount(Percentage)', $this->adminLangId), 'ocrule_refund_amount');
        $fld->requirements()->setFloatPositive();
        $fld->requirements()->setRange(0, 100);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    public function deleteRecord()
    {
        $ruleId = FatApp::getPostedData('ruleId', FatUtility::VAR_INT, 0);
        $this->objPrivilege->canEditRentalOrderCancelRules();
        $this->markAsDeleted($ruleId);
        FatUtility::dieJsonSuccess($this->str_delete_record);
    }

    public function deleteSelected()
    {
        $this->objPrivilege->canEditOrderCancelReasons();
        $ocruleIdsArr = FatUtility::int(FatApp::getPostedData('ocrule_ids'));

        if (empty($ocruleIdsArr)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->adminLangId));
        }

        foreach ($ocruleIdsArr as $ruleId) {
            $data = OrderCancelRule::getAttributesById($ruleId);
            if ($data['ocrule_is_default'] == OrderCancelRule::MIN_VALUE || $data['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_CAN_NOT_DELETE_DEFAULTS', $this->adminLangId));
            }
        }

        foreach ($ocruleIdsArr as $ruleId) {
            if (1 > $ruleId) {
                continue;
            }
            $this->markAsDeleted($ruleId);
        }
        $this->set('msg', $this->str_delete_record);
        $this->_template->render(false, false, 'json-success.php');
    }

    private function markAsDeleted(int $ruleId)
    {
        $obj = new OrderCancelRule($ruleId);
        if (!$obj->deleteRecord()) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
    }

    private function checkAvailableSlots(){
        $srch = OrderCancelRule::getSearchObject();
        $srch->addMultipleFields(array('ocrule.*'));
        $srch->addCondition('ocrule_user_id', '=', 0);
        $srch->addOrder('ocrule_duration_min', 'ASC');

        $rs = $srch->getResultSet();
        $cancleRuleData = FatApp::getdb()->fetchAll($rs); 
        $previousMaxHr = 0;
        $warningMsg = array();
        for($i = 1; $i < count($cancleRuleData); $i++){
            $previousMaxHr = $cancleRuleData[$i-1]['ocrule_duration_max'];
            if($previousMaxHr != $cancleRuleData[$i]['ocrule_duration_min']){
                $msg = $previousMaxHr.' - '. $cancleRuleData[$i]['ocrule_duration_min'];
                array_push($warningMsg, $msg);
            }
        }
        return $warningMsg;
    }

}
