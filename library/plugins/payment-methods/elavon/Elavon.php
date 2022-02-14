<?php
use Curl\Curl;
class Elavon extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;
    public const PRODUCTION_URL = 'https://api.convergepay.com/hosted-payments/transaction_token';    
    public const SANDBOX_URL = 'https://api.demo.convergepay.com/hosted-payments/transaction_token';
	public $requiredKeys = [];
    private $sslMerchantId = '';
    private $sslUserId = '';
    private $sslPin = '';
    private $env = '';
        
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
			'ssl_merchant_id',
            'ssl_user_id',
            'ssl_pin',
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
        $this->sslMerchantId = $this->settings['ssl_merchant_id'];
        $this->sslUserId = $this->settings['ssl_user_id'];
        $this->sslPin = $this->settings['ssl_pin'];
        $this->env = $this->settings['env'];
        return true;
    }
    
    /**
     * getEnvironment
     *
     * @return int
    */
    public function getEnvironment(): int
    {
        return (int)$this->env;
    }
    
    /**
     * createTransactionToken
     *
     * @param  string $userName
     * @param  string $paymentAmount 
     * @return string
    */
    public function createTransactionToken(string $userName, string $paymentAmount): string
    {
        $url = (Plugin::ENV_PRODUCTION == $this->env) ? self::PRODUCTION_URL : self::SANDBOX_URL;
        $requestParam = [
            'ssl_merchant_id' => $this->sslMerchantId,
            'ssl_user_id' => $this->sslUserId,
            'ssl_pin' => $this->sslPin,
            'ssl_transaction_type' => 'ccsale',
            'ssl_first_name' => $userName,
            'ssl_amount' => $paymentAmount,
        ];
        
        return $this->doRequest($url, $requestParam);       
    }
    
    /**
     * doRequest
     *
     * @param  string $url
     * @param  array $requestParam
     * @return string
     */
    private function doRequest(string $url, array $requestParam): string
    {   
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_VERBOSE, true);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);        
        $curl->post($url, http_build_query($requestParam));  
        return $curl->getResponse(); 
    }

}