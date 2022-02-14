<?php

class InvoiceRequest extends MyAppModel
{
    public const DB_TBL = 'tbl_invoice_requests';
    public const DB_TBL_PREFIX = 'inreq_';
    
    public const INVOICE_REQUEST_PENDING = 0;
    public const INVOICE_REQUEST_COMPLETE = 1;
    
    public function __construct($invReqId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'order_id', $invReqId);
    }
    
    public static function getSearchObj()
    {
        $srch = new SearchBase(static::DB_TBL, 'inreq');
        return $srch;
    }
   
    public function saveInvoiceRequest(array $data) : bool
    {
        if (empty($data)) {
            $this->error = Labels::getLabel('MSG_Invalid_Request', CommonHelper::getLangId());
        }
        $db = FatApp::getDB();
        if (!$db->insertFromArray(static::DB_TBL, $data, false, array(), $data )) {
            $this->error = $db->getError();
            return false;
        }
        return true;
    }
    
    public static function getRequestStatusArr(int $langId) {
        if (1 > $langId) {
            $langId = CommonHelper::getLangId();
        }
        return [
            static::INVOICE_REQUEST_PENDING => Labels::getLabel('MSG_Pending', $langId),
            static::INVOICE_REQUEST_COMPLETE => Labels::getLabel('MSG_Complete', $langId),
        ];
    }

}
