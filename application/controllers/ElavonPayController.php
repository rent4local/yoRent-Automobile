<?php

class ElavonPayController extends PaymentController
{
    public const KEY_NAME = "Elavon";
    
    /**
     * __construct
     *
     * @param  string $action
     * @return void
     */
    public function __construct(string $action)
    {
        parent::__construct($action);
        $this->init();
    }

    /**
     * allowedCurrenciesArr
     *
     * @return array
     */
    protected function allowedCurrenciesArr(): array
    {
        return ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];
    }

    /**
     * init
     *
     * @return void
     */
    private function init(): void
    {
        if (false === $this->plugin->init()) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }
    }

    /**
     * charge
     *
     * @param  string $orderId
     * @return void
     */
    public function charge($orderId)
    {
        if (empty($orderId)) {
            $msg = Labels::getLabel('MSG_Invalid_Access', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $orderPayment = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPayment->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPayment->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $transactionToken = $this->plugin->createTransactionToken($orderInfo['customer_name'], $paymentAmount);
        $frm = $this->getPaymentForm();        
        $frm->fill(['ssl_invoice_number' => $orderId, 'token' => $transactionToken, 'first_name' => $orderInfo['customer_name']]);

        $this->set('frm', $frm);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('env', $this->plugin->getEnvironment());

        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'elavon-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }
    
    /**
     * paymentApproved
     *
     * @param  string $orderId
     * @return void
     */
    public function paymentApproved($orderId)
    {   
        $orderPayment = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPayment->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }
        
        $response = FatApp::getPostedData('response', FatUtility::VAR_STRING, '');
        $rsp = !empty($response) ? json_decode($response, true) : [];
		if(empty($rsp) || strlen(trim($rsp['ssl_txn_id'])) <= 0  || strtoupper($rsp['ssl_result_message']) != 'APPROVAL'){
			$msg = Labels::getLabel('MSG_Invalid_Response', $this->siteLangId);
            $this->logFailure($orderId, $msg, $rsp);
		}
		
        $paymentAmount = $orderPayment->getOrderPaymentGatewayAmount();
        if (false === $orderPayment->addOrderPayment(self::KEY_NAME, $rsp['ssl_txn_id'], $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($rsp))) {
            $msg = $orderPayment->getError();
            $this->logFailure($orderId, $msg, $rsp);
        }
        
        $this->_template->render(false, false, 'json-success.php');
    }
    
    /**
     * getPaymentForm
     *
     * @return object
     */
    private function getPaymentForm(): object
    {
        $frm = new Form('frmPaymentForm');
        $frm->addRequiredField(Labels::getLabel('LBL_Card_Number', $this->siteLangId), 'card_number');
        $data['months'] = applicationConstants::getMonthsArr($this->siteLangId);
        $today = getdate();
        $data['year_expire'] = array();
        for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
            $data['year_expire'][strftime('%y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
        }
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_MONTH', $this->siteLangId), 'exp_month', $data['months'], '', array(), '');
        $frm->addSelectBox(Labels::getLabel('LBL_EXPIRY_YEAR', $this->siteLangId), 'exp_year', $data['year_expire'], '', array(), '');
        $frm->addPasswordField(Labels::getLabel('LBL_Cvv', $this->siteLangId), 'cvv')->requirements()->setRequired();
        $frm->addHiddenField('', 'token');
        $frm->addHiddenField('', 'first_name');
        $frm->addHiddenField('', 'ssl_invoice_number');
        $frm->addSubmitButton('', 'btn_pay', Labels::getLabel('LBL_Pay_Now', $this->siteLangId));
        return $frm;
    }
    
      /**
     * logFailure
     *
     * @param  string $orderId
     * @return void
     */
    private function logFailure(string $orderId, string $msg = '', array $response = [])
    {
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
        if (empty($msg)) {
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);
        }
        
        $orderPayment = new OrderPayment($orderId);
        $orderPayment->addOrderPaymentComments($msg);
        $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
    }

}