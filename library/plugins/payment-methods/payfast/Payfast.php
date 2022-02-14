<?php

/**
 * Payfast - Services in South Africa
 * API's reference https://developers.payfast.co.za/docs
 */
class Payfast extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;
    public const PRODUCTION_HOST = 'https://payfast.co.za';
    public const PRODUCTION_URL = self::PRODUCTION_HOST . '/eng/process';
    
    public const SANDBOX_HOST = 'https://sandbox.payfast.co.za';
    public const SANDBOX_URL = self::SANDBOX_HOST . '/eng/process';

    public $requiredKeys = [
        'passphrase',
        'merchant_id',
        'merchant_key',
    ];
    private $env = Plugin::ENV_SANDBOX;
    private $passphrase = '';
    private $signature = '';
    private $merchantId = '';
    private $merchantKey = '';
    private $actionUrl = '';
    private $requestBody = [];
    private $paymentHost = '';

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
        $this->requiredKeys();
    }

    /**
     * requiredKeys
     *
     * @return void
     */
    public function requiredKeys()
    {
        $this->env = FatUtility::int($this->getKey('env'));
        if (0 < $this->env) {
            $this->requiredKeys = [
                'passphrase',
                'live_merchant_id',
                'live_merchant_key',
            ];
        }
    }

    /**
     * init
     *
     * @return bool
     */
    public function init(): bool
    {
        if (false == $this->validateSettings()) {
            return false;
        }

        if (false === $this->loadBaseCurrencyCode()) {
            return false;
        }

        $this->passphrase = $this->settings['passphrase'];
        $this->merchantId = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_merchant_id'] : $this->settings['merchant_id'];
        $this->merchantKey = Plugin::ENV_PRODUCTION == $this->settings['env'] ? $this->settings['live_merchant_key'] : $this->settings['merchant_key'];
        $this->actionUrl = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::PRODUCTION_URL : self::SANDBOX_URL;
        $this->paymentHost = Plugin::ENV_PRODUCTION == $this->settings['env'] ? self::PRODUCTION_HOST : self::SANDBOX_HOST;

        return true;
    }

    /**
     * getPassphrase
     *
     * @return string
     */
    public function getPassphrase(): string
    {
        return (string)$this->passphrase;
    }

    /**
     * getmerchantId
     *
     * @return string
     */
    public function getmerchantId(): string
    {
        return (string)$this->merchantId;
    }

    /**
     * getMerchantKey
     *
     * @return string
     */
    public function getMerchantKey(): string
    {
        return (string)$this->merchantKey;
    }

    /**
     * getActionUrl
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        return (string)$this->actionUrl;
    }

    /**
     * getSignature
     *
     * @return string
     */
    public function getSignature(): string
    {
        return (false === $this->loadSignature() ? "" : (string)$this->signature);
    }

    /**
     * loadSignature
     *
     * @return bool
     */
    private function loadSignature(): bool
    {
        /*if (!empty($this->settings['signature'])) {
            $this->signature = $this->settings['signature'];
            return true;
        }*/		
		

		$signature = md5(http_build_query($this->getRequestBody()));

       /*  if (false === $this->updateSettings($this->settings["plugin_id"], ['signature' => $signature], $this->error)) {
            return false;
        } */
		if(empty($signature)){
			return false;
		}
		
        $this->signature = $signature;
        return true;
    }

    /**
     * buildRequestBody
     *
     * @param  string $orderId
     * @return bool
     */
    public function buildRequestBody(string $orderId): bool
    {
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $orderInfo = $orderPaymentObj->getOrderPrimaryinfo();
        if (false === $this->loadLoggedUserInfo($orderInfo['customer_id'])) {
            return false;
        }

        $customerEmail = !isset($orderInfo['customer_email']) || empty($orderInfo['customer_email']) ? $this->userData['credential_email'] : $orderInfo['customer_email'];

        $this->requestBody = array(
            // Merchant details
            'merchant_id' => $this->getmerchantId(),
            'merchant_key' => $this->getMerchantKey(),
            'return_url' => CommonHelper::generateFullUrl('Custom', 'paymentSuccess', [$orderId]),
			'cancel_url' => CommonHelper::generateFullUrl('Custom', 'paymentFailed'),
            'notify_url' => CommonHelper::generateFullUrl(self::KEY_NAME . 'Pay', 'callback', [$orderId]),
            // Buyer details
            'name_first' => $this->userData['user_name'],
            'email_address' => $customerEmail,
            // Transaction details
            'm_payment_id' => $orderId,
            'amount' => number_format(sprintf('%.2f', $paymentAmount), 2, '.', ''),
            'item_name' => "Order #" . $orderId,
            'passphrase' => $this->getPassphrase()
        );

        if (false === $this->loadSignature()) {
            return false;
        }
        $this->requestBody['signature'] = $this->getSignature();	
        return true;
    }
    
    /**
     * validateResponseSignature
     *
     * @param  array $response
     * @return bool
     */
    public function validateResponseSignature(array $response): bool
    {		
		$responseSignature = $response['signature'];
		unset($response['signature']);
		if(!empty($this->passphrase)) {
			$response['passphrase'] = $this->passphrase;
        }
        $this->requestBody = $response;
        return ($responseSignature === $this->getSignature());		
    }

    /**
     * getRequestBody
     *
     * @return array
     */
    public function getRequestBody(): array
    {
        return $this->requestBody;
    }

    /**
     * validServerConfirmation
     * @param string $paramString parameters used to create signature
     * @param string $proxy proxy on or not
     * @return boolean
     */
    public function validServerConfirmation($paramString, $proxy = null)
    {
        if (in_array('curl', get_loaded_extensions(), true)) {
            $validateUrl = $this->paymentHost . '/eng/query/validate';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, null);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            curl_setopt($ch, CURLOPT_URL, $validateUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramString);
            if (!empty($proxy)) {
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
            }

            $response = curl_exec($ch);
            curl_close($ch);
            if (strtoupper($response) === 'VALID') {
                return true;
            }
        }
        return false;
    }
}
