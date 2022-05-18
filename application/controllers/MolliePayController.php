<?php

class MolliePayController extends PaymentController
{
    public const KEY_NAME = "Mollie";
    
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
        return ['EUR', 'USD'];
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

        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $frm = $this->getPaymentForm($orderId);
        $postOrderId = FatApp::getPostedData('orderId', FatUtility::VAR_STRING, '');
        $processRequest = false;
        
        $actionUrl = UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', [$orderId]);
        if (!empty($postOrderId) && $orderId = $postOrderId) {
            $frm = $this->getPaymentForm($orderId, true);
            if (!$this->plugin->createPaymentAndActionUrl($orderId)) {
                $msg = $this->plugin->getError();
                $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
            }
            $actionUrl  = $this->plugin->getActionUrl();
            $processRequest = true;
        }
		
        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('actionUrl', $actionUrl);
        $this->set('processRequest', $processRequest);
        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'mollie-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }
    
    /**
     * callback - Used for webhook
     *
     * @param  string $orderId
     * @return void
     */
    public function callback(string $orderId)
    {
		$post = FatApp::getPostedData();
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->logFailure($orderId, $msg);
            return false;
        }

		if(strlen(trim($post['id'])) <= 0 ){
			$msg = Labels::getLabel('MSG_Invalid_Callback_Response', $this->siteLangId);
            $this->logFailure($orderId, $msg);
            return false;
		}
		
        if ($this->plugin->validatePaymentResponse($post) === false) {
            $msg = Labels::getLabel('MSG_Invalid_Payment_Response', $this->siteLangId);
            $this->logFailure($orderId, $msg);
            return false;
        }
		
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if (false === $orderPaymentObj->addOrderPayment(self::KEY_NAME, $post['id'], $paymentAmount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), json_encode($post))) {
            $msg = $orderPaymentObj->getError();
            $this->logFailure($orderId, $msg);
            return false;
        }
		return true;
	}

    /**
     * getPaymentForm
     *
     * @param  string $orderId
     * @param  bool $processRequest
     * @return object
     */
    private function getPaymentForm(string $orderId, bool $processRequest = false): object
    {
        $frm = new Form('frmPaymentForm', array('class' => "form form--normal"));
        if (false === $processRequest) {	
            $frm->addHiddenField('', 'orderId');
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        }
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
        $response = !empty($response) ? $response : $_REQUEST;
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($response));
        if (empty($msg)) {
            $msg = Labels::getLabel("MSG_PAYMENT_FAILED._{MSG}", $this->siteLangId);
            $msg = CommonHelper::replaceStringData($msg, ['{MSG}' => $this->plugin->getError()]);
        }
        
        $orderPaymentObj = new OrderPayment($orderId);
        $orderPaymentObj->addOrderPaymentComments($msg);
        exit;
    }
    
  


}