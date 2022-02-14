<?php

require_once CONF_INSTALLATION_PATH . 'library/payment-plugins/paytm/PaytmKit/lib/encdec_paytm.php';
class PaytmPayController extends PaymentController
{
    public const KEY_NAME = "Paytm";
    private $testEnvironmentUrl = 'https://securegw-stage.paytm.in/order';
    private $liveEnvironmentUrl = 'https://securegw.paytm.in/order';

    public function __construct($action)
    {
        parent::__construct($action);
        $this->init();
    }

    protected function allowedCurrenciesArr()
    {
        return ['INR'];
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
        if (empty(trim($orderId))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
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
        if (!empty($postOrderId) && $orderId = $postOrderId) {
            $frm = $this->getPaymentForm($orderId, true);
            $processRequest = true;
        }

        $frm->fill(['orderId' => $orderId]);
        $this->set('frm', $frm);
        $this->set('processRequest', $processRequest);

        $this->set('orderInfo', $orderInfo);
        $this->set('paymentAmount', $paymentAmount);
        $this->set('exculdeMainHeaderDiv', true);
        if (FatUtility::isAjaxCall()) {
            $json['html'] = $this->_template->render(false, false, 'paytm-pay/charge-ajax.php', true, false);
            FatUtility::dieJsonSuccess($json);
        }
        $this->_template->render(true, false);
    }

    public function callback()
    {
        $post = FatApp::getPostedData();

        $request = '';
        foreach ($post as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }

        $isValidChecksum = false;
        $paytmChecksum = isset($post["CHECKSUMHASH"]) ? $post["CHECKSUMHASH"] : ""; //Sent by Paytm pg
        $isValidChecksum = verifychecksum_e($post, $this->settings['merchant_key'], $paytmChecksum); //will return TRUE or FALSE string.
        $arrOrder = explode("_", $post['ORDERID']);
        $orderId = (!empty($arrOrder[1])) ? $arrOrder[1] : 0;
        $txnInfo = $this->PaytmTransactionStatus($post['ORDERID']);

        $orderPaymentObj = new OrderPayment($orderId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        if ($paymentGatewayCharge > 0) {
            if ($isValidChecksum) {
                $paid_amount = (float) $txnInfo['TXNAMOUNT'];
                $totalPaidMatch = ($paid_amount == $paymentGatewayCharge);
                if (!$totalPaidMatch) {
                    $request .= "\n\n Paytm :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
                }

                if ($txnInfo['STATUS'] == "TXN_SUCCESS" && $totalPaidMatch) {
                    $orderPaymentObj->addOrderPayment($this->settings["plugin_code"], $post['TXNID'], $paymentGatewayCharge, Labels::getLabel("MSG_Received_Payment", $this->siteLangId), json_encode($post));
                    FatApp::redirectUser(UrlHelper::generateUrl('custom', 'paymentSuccess', array($orderId)));
                } else {
                    TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($post));
                    $orderPaymentObj->addOrderPaymentComments($request);
                    if (isset($post['PAYMENTMODE'])) {
                        FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
                    } else {
                        FatApp::redirectUser(CommonHelper::getPaymentCancelPageUrl());
                    }
                }
            } else {
                TransactionFailureLog::set(TransactionFailureLog::LOG_TYPE_CHECKOUT, $orderId, json_encode($post));
                FatApp::redirectUser(CommonHelper::getPaymentFailurePageUrl());
            }
        } else {
            FatUtility::exitWithErrorCode(404);
        }
    }

    public function PaytmTransactionStatus($orderId)
    {
        header("Pragma: no-cache");
        header("Cache-Control: no-cache");
        header("Expires: 0");
        $checkSum = "";
        $data = array(
            "MID" => $this->settings["merchant_id"],
            "ORDER_ID" => $orderId,
        );

        $key = $this->settings['merchant_key'];
        $checkSum = getChecksumFromArray($data, $key);

        $request = array("MID" => $this->settings["merchant_id"], "ORDERID" => $orderId, "CHECKSUMHASH" => $checkSum);

        $JsonData = json_encode($request);
        $postData = 'JsonData=' . urlencode($JsonData);
        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $url = $this->liveEnvironmentUrl . '/status';
        } else {
            $url = $this->testEnvironmentUrl . '/status';
        }
        $HEADER[] = "Content-Type: application/json";
        $HEADER[] = "Accept: application/json";

        $args['HEADER'] = $HEADER;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $args['HEADER']);
        $server_output = curl_exec($ch);
        return json_decode($server_output, true);
    }

    private function getPaymentForm(string $orderId, bool $processRequest = false)
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->siteLangId);
        $paymentGatewayCharge = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();

        if (FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_BOOLEAN, false) == true) {
            $action_url = $this->liveEnvironmentUrl . "/process";
        } else {
            $action_url = $this->testEnvironmentUrl . "/process";
        }

        $action_url = false === $processRequest ? UrlHelper::generateUrl(self::KEY_NAME . 'Pay', 'charge', array($orderId)) : $action_url;

        $orderPaymentGatewayDescription = sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $this->siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']);

        $frm = new Form('frmPaytm', array('id' => 'frmPaytm', 'action' => $action_url, 'class' => "form form--normal"));
        $frm->addHiddenField('', 'orderId');

        if (false === $processRequest) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_CONFIRM', $this->siteLangId));
        } else {
            $parameters = array(
                "MID" => $this->settings["merchant_id"],
                "ORDER_ID" => date("ymdhis") . "_" . $orderId,
                "CUST_ID" => $orderInfo['customer_id'],
                "TXN_AMOUNT" => $paymentGatewayCharge,
                "CHANNEL_ID" => $this->settings['merchant_channel_id'],
                "INDUSTRY_TYPE_ID" => $this->settings['merchant_industry_type'],
                "WEBSITE" => $this->settings['merchant_website'],
                "MOBILE_NO" => $orderInfo['customer_phone'],
                "EMAIL" => $orderInfo['customer_email'],
                "CALLBACK_URL" => UrlHelper::generateFullUrl('PaytmPay', 'callback'),
                "ORDER_DETAILS" => $orderPaymentGatewayDescription,
            );

            $checkSumHash = getChecksumFromArray($parameters, $this->settings['merchant_key']);

            $frm->addHiddenField('', 'CHECKSUMHASH', $checkSumHash);
            foreach ($parameters as $paramkey => $paramval) {
                $frm->addHiddenField('', $paramkey, $paramval);
            }
        }
        return $frm;
    }
}
