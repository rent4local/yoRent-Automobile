<?php
/**
 * MpesaPayController - M-Pesa services in 10 countries: Albania, the Democratic Republic of Congo, Egypt, Ghana, India, Kenya, Lesotho, Mozambique, Romania and Tanzania. 
 */
class MpesaPayController extends PaymentController
{
    public const KEY_NAME = "Mpesa";
    private $userId = 0;

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
        return [
            "ALL", "CDF", "EGP", "GHS", "INR", "KES", "LSL", "ZAR", "MZN", "RON", "TZS"
        ];
    }

    /**
     * init
     *
     * @return void
     */
    private function init(): void
    {
        $this->userId = UserAuthentication::getLoggedUserId(true);
        if (false === $this->plugin->init($this->userId)) {
            $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
        }

        $this->settings = $this->plugin->getSettings();
        $this->clientId = 0 < $this->settings['env'] ? $this->settings['live_shortcode'] : $this->settings['shortcode'];
        $this->secretKey = 0 < $this->settings['env'] ? $this->settings['live_passkey'] : $this->settings['passkey'];
    }

    /**
     * charge
     *
     * @param  string $orderId
     * @return void
     */
    public function charge($orderId)
    {
        if ($orderId == '') {
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
        
        $customerPhone =  '';
        if (0 < $this->userId) {
            $userObj = new User($this->userId);
            $userData = $userObj->getUserInfo(['user_dial_code', 'user_phone']);
            $dialCode = !empty($userData["user_dial_code"]) ? FatUtility::int($userData["user_dial_code"]) : '';
            $phone = !empty($userData["user_phone"]) ? FatUtility::int($userData["user_phone"]) : '';
            $customerPhone = $dialCode . $phone;
        }

        $frm = $this->getPaymentForm($orderId);
        if (!empty($customerPhone)) {
            $frm->fill(['customerPhone' => $customerPhone]);
        }
        $this->set('frm', $frm);

        $phoneNumber = FatApp::getPostedData('customerPhone', FatUtility::VAR_INT, 0);
        if (0 < $phoneNumber) {
            if (false === $this->plugin->STKPushSimulation($orderId, $paymentAmount, $phoneNumber, $orderId)) {
                $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
            }

            $response = $this->plugin->getResponse();
            if (array_key_exists('errorMessage', $response)) {
                $this->setErrorAndRedirect($response['errorMessage'], FatUtility::isAjaxCall());
            }

            if (array_key_exists('ResponseCode', $response)) {
                if (0 < $response['ResponseCode']) {
                    $this->setErrorAndRedirect($response['ResponseDescription'], FatUtility::isAjaxCall());
                }

                $msg = Labels::getLabel('MSG_WAITING_FOR_CONFIRMATION', $this->siteLangId);
                $json['msg'] = $response['ResponseDescription'] . ' ' . $msg;
                $json['redirect'] = UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId));
            } else {
                $msg = Labels::getLabel('LBL_SOMETHING_WENT_WRONG', $this->siteLangId);
                $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
            }
        }

        $this->set('exculdeMainHeaderDiv', true);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'mpesa-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }
    
    /**
     * callback
     *
     * @param  string $orderId
     * @return void
     */
    public function callback(string $orderId)
    {
        $json = file_get_contents('php://input');
        $post = json_decode($json, true);
        $orderPaymentObj = new OrderPayment($orderId);
        if (json_last_error() == JSON_ERROR_NONE) {
            $error = false;
            /**
             * 0 Success (for C2B).
             * 00000000	Success (For APIs that are not C2B).
             * 1 or any other number	Rejecting the transaction.
             */
            $stkCallback = isset($post['Body']['stkCallback']) ? $post['Body']['stkCallback'] : [];
            $checkoutRequestID = isset($stkCallback['CheckoutRequestID']) ? $stkCallback['CheckoutRequestID'] : '';
            $error = empty($checkoutRequestID);
            if (false === $error && isset($stkCallback['ResultCode']) && 0 == $stkCallback['ResultCode']) {
                if (false === $this->plugin->STKPushQuery($checkoutRequestID)) {
                    $this->setErrorAndRedirect($this->plugin->getError(), FatUtility::isAjaxCall());
                }
    
                $response = $this->plugin->getResponse();
                if (array_key_exists('ResponseCode', $response) && 0 < $response['ResponseCode']) {
                    $error = ($response['ResultCode'] != $stkCallback['ResultCode']);
                    $json = false == $error ? $json : json_encode(array_merge(['callback_response' => $post], ['verification_response' => $response]));
                } else {
                    $error = true;
                    $json = json_encode(array_merge(['callback_response' => $post], ['verification_response' => $response]));
                }

                if (false === $error) {
                    $callbackMetadata = $stkCallback['CallbackMetadata'];
                    $payment_amount = 0;
                    $txnId = '';
                    foreach ($callbackMetadata['Item'] as $orderTxn) {
                        if ('amount' == strtolower($orderTxn['Name'])) {
                            $payment_amount = $orderTxn['Value'];
                        }

                        if ('mpesareceiptnumber' == strtolower($orderTxn['Name'])) {
                            $txnId = $orderTxn['Value'];
                        }

                        if (!empty($payment_amount) && !empty($txnId)) {
                            break;
                        }
                    }
                    $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $txnId, $payment_amount, Labels::getLabel("MSG_RECEIVED_PAYMENT", $this->siteLangId), $json);
                    return;
                }
            }
        }
        TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, $json);
        $msg = Labels::getLabel("MSG_PAYMENT_FAILED", $this->siteLangId);
        $orderPaymentObj->addOrderPaymentComments($msg);
        return;
    }

    /**
     * getPaymentForm
     *
     * @param  mixed $orderId
     * @return object
     */
    private function getPaymentForm($orderId): object
    {
        $frm = new Form('frmPaymentForm', array('id' => 'frmPaymentForm', 'action' => UrlHelper::generateUrl('MpesaPay', 'charge', array($orderId)), 'class' => "form form--normal"));
        $frm->addRequiredField(Labels::getLabel('LBL_PHONE_NUMBER', $this->siteLangId), 'customerPhone');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_REQUEST', $this->siteLangId));

        return $frm;
    }
}
