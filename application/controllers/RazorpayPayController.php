<?php

class RazorpayPayController extends PaymentController
{
    public const KEY_NAME = "Razorpay";

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return [
            'AED', 'ALL', 'AMD', 'ARS', 'AUD', 'AWG', 'BBD', 'BDT', 'BMD', 'BND', 'BOB', 'BSD', 'BWP', 'BZD', 'CAD', 'CHF', 'CNY', 'COP', 'CRC', 'CUP', 'CZK', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB', 'EUR', 'FJD', 'GBP', 'GIP', 'GMD', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'JMD', 'KES', 'KGS', 'KHR', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MKD', 'MMK', 'MNT', 'MOP', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PEN', 'PGK', 'PHP', 'PKR', 'QAR', 'RUB', 'SAR', 'SCR', 'SEK', 'SGD', 'SLL', 'SOS', 'SSP', 'SVC', 'SZL', 'THB', 'TTD', 'TZS', 'USD', 'UYU', 'UZS', 'YER', 'ZAR'
        ];
    }
    
    private function init(): void
    {
        if (false === $this->plugin->validateSettings($this->siteLangId)) {
            $this->setErrorAndRedirect($this->plugin->getError());
        }

        $this->settings = $this->plugin->getSettings();
    }

    public function charge($orderId)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (!empty($orderInfo) && $orderInfo["order_payment_status"] != Orders::ORDER_PAYMENT_PENDING) {
            $msg = Labels::getLabel('MSG_INVALID_ORDER_PAID_CANCELLED', $this->siteLangId);
            $this->setErrorAndRedirect($msg, FatUtility::isAjaxCall());
        }

        $frm = $this->getPaymentForm($orderId);
        $this->set('frm', $frm);

        $cancelBtnUrl = CommonHelper::getPaymentCancelPageUrl();
        if ($orderInfo['order_type'] == Orders::ORDER_WALLET_RECHARGE) {
            $cancelBtnUrl = CommonHelper::getPaymentFailurePageUrl();
        }

        $this->set('cancelBtnUrl', $cancelBtnUrl);

        $this->set('paymentAmount', $paymentAmount);
        $this->set('orderInfo', $orderInfo);
        $this->set('paymentSettings', $this->settings);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'razorpay-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $post = FatApp::getPostedData();
       
        $razorpay_payment_id = $post['razorpay_payment_id'];
        $merchant_order_id = (isset($post['merchant_order_id'])) ? $post['merchant_order_id'] : 0;
        $orderPaymentObj = new OrderPayment($merchant_order_id, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payment_gateway_charge_in_paisa = $paymentGatewayCharge * 100;
        if ($paymentGatewayCharge > 0) {
            $success = false;
            $error = "";
            try {
                $url = 'https://api.razorpay.com/v1/payments/' . $razorpay_payment_id . '/capture';
                $fields_string = "amount=$payment_gateway_charge_in_paisa";
                //cURL Request
                $ch = curl_init();
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_USERPWD, $this->settings['merchant_key_id'] . ":" . $this->settings['merchant_key_secret']);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                //execute post
                $result = curl_exec($ch);
                $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($result === false) {
                    $success = false;
                    $error = 'Curl error: ' . curl_error($ch);
                } else {
                    $response_array = json_decode($result, true);
                    //Check success response
                    if ($http_status === 200 and isset($response_array['error']) === false) {
                        $success = true;
                    } else {
                        $success = false;
                        if (!empty($response_array['error']['code'])) {
                            $error = $response_array['error']['code'] . ":" . $response_array['error']['description'];
                        } else {
                            $error = "RAZORPAY_ERROR:Invalid Response <br/>" . $result;
                        }
                    }
                }
                //close connection
                curl_close($ch);
            } catch (Exception $e) {
                $success = false;
                $error = "ERROR:Request to Razorpay Failed";
            }
            if ($success === true) {
                $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $razorpay_payment_id, $paymentGatewayCharge, Labels::getLabel("L_Received_Payment", $this->siteLangId), $result);
                FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($merchant_order_id)));
            } else {
                $orderPaymentObj->addOrderPaymentComments($error . ' Payment Failed! Check Razorpay dashboard for details of Payment Id:' . $razorpay_payment_id);
                TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $merchant_order_id, $result);
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        } else {
            FatUtility::exitWithErrorCode(404);
        }
    }

    private function getPaymentForm(string $orderId)
    {
        $frm = new Form('razorpay-form', array('id' => 'razorpay-form', 'action' => UrlHelper::generateFullUrl('RazorpayPay', 'callback'), 'class' => "form form--normal"));

        $frm->addHiddenField('', 'razorpay_payment_id', '', array('id' => 'razorpay_payment_id'));
        $frm->addHiddenField('', 'merchant_order_id', $orderId, array('id' => 'merchant_order_id'));
        $frm->addButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        return $frm;
    }

    
    public function getExternalLibraries()
    {
        $json['libraries'] = [
            'https://checkout.razorpay.com/v1/checkout.js',
        ];
        FatUtility::dieJsonSuccess($json);
    }
}
