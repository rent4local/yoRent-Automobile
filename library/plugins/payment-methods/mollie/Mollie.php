<?php
use Mollie\Api\Exceptions\ApiException;
class Mollie extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;
    private $privateKey = '';
    private $actionUrl = '';
	public $requiredKeys = [];
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
		$this->requiredKeys = [
			'privateKey',
		];
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

        $this->privateKey = $this->settings['privateKey'];
        return true;
    }
    
    /**
     * createPayment
     *
     * @param  string $orderId
     * @return void
     */
    public function createPaymentAndActionUrl($orderId)
    {   
        $orderPaymentObj = new OrderPayment($orderId, $this->langId);
        $paymentAmount = $orderPaymentObj->getOrderPaymentGatewayAmount();
        $payableAmount = $this->formatPayableAmount($paymentAmount);
        
        $mollie = $this->prepareRequestObject();
		try {
			$payment = $mollie->payments->create([
				"amount" => [
					"currency" => $this->systemCurrencyCode,
					"value" => $payableAmount
				],
				"description" => Labels::getLabel('LBL_Order',$this->langId)." #".$orderId,
				"redirectUrl" => CommonHelper::generateFullUrl('Custom','paymentSuccess',[$orderId]),
				"webhookUrl" =>  CommonHelper::generateFullUrl('MolliePay','callback', [$orderId]),
				"metadata" => [
					"order_id" => $orderId, 
				],
			]);	
            $this->actionUrl = $payment->getCheckoutUrl();
            return true;
		} catch (ApiException $e) {
            $this->actionUrl = '';
			$this->error = htmlspecialchars($e->getMessage());            
            return false;
		}
    }
    
    /**
     * validatePaymentResponse
     *
     * @param  array $response
     * @return bool
     */
    public function validatePaymentResponse(array $response): bool
    {   
        $mollie = $this->prepareRequestObject();
        try {
			$payment = $mollie->payments->get($response['id']);
            if(!isset($payment->metadata->order_id) || $payment->metadata->order_id == ''){
                return false;
            }
            if (!$payment->isPaid()) {
                return false;
            }
            return true;
		} catch (ApiException $e) {
            return false;
		}
    }
    
    /**
     * initiateRequest
     *
     * @return object
     */
    private function prepareRequestObject()
    {
        $mollie = new \Mollie\Api\MollieApiClient();
		$mollie->setApiKey($this->privateKey);
        return $mollie;
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
     * formatPayableAmount
     *
     * @param  string $amount
     * @return string
     */
    private function formatPayableAmount($amount)
	{ 
		return number_format((float)$amount, 2, '.', '');
	}
    

}