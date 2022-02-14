<?php

class Paytm extends PaymentMethodBase
{
    public const KEY_NAME = __CLASS__;

    public $requiredKeys = [
        'merchant_id',
        'merchant_key',
        'merchant_website',
        'merchant_channel_id',
        'merchant_industry_type',
    ];

    /**
     * __construct
     *
     * @param  int $langId
     * @return void
     */
    public function __construct(int $langId)
    {
        $this->langId = 0 < $langId ? $langId : CommonHelper::getLangId();
    }
}
