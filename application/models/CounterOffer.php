<?php

class CounterOffer extends MyAppModel
{
    public const DB_TBL = 'tbl_counter_offers';
    public const DB_TBL_PREFIX = 'counter_offer_';
    private $rfqId;
    
    public function __construct($counterOfferId = 0, $rfqId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $counterOfferId);
        $this->rfqId = $rfqId;
    }

    public static function getSearchObject(int $rfqId)
    {
        $srch = new SearchBase(static::DB_TBL, 'co');
        $srch->addCondition('co.counter_offer_rfq_id', '=', $rfqId);
        return $srch;
    }

    public function getDetailByStatus(int $status): array
    {
        $srch = SELF::getSearchObject($this->rfqId);
        $srch->joinTable(RequestForQuote::DB_TBL, 'INNER JOIN', 'co.counter_offer_rfq_id = rfq.rfq_id', 'rfq');
        $srch->addCondition('co.counter_offer_status', '=', $status);
        $srch->addMultipleFields(array('co.*','rfq.rfq_quote_validity'));
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);
        if (!empty($data)) {
            return $data;
        }
        return array();
    }

    public function getFinalOfferByRfqId(bool $forDeliveyDate = false) : array
    {
        $srch = SELF::getSearchObject($this->rfqId);
        $order = 'DESC';
        if ($forDeliveyDate) {
            $order = 'ASC';
        }
        $srch->addOrder('co.counter_offer_id', $order);
        $srchRs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($srchRs);
        if (!empty($data)) {
            return $data;
        }
        return array();
    }

}