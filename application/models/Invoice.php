<?php

class Invoice extends MyAppModel
{
    public const DB_TBL = 'tbl_invoices';
    public const DB_TBL_PREFIX = 'invoice_';
    private $orderId;
    
    public const INVOICE_IS_PENDING = 0;
    public const INVOICE_IS_SHARED_WITH_BUYER = 2;
    public const INVOICE_IS_PAID = 1;
   
    public function __construct($invoiceId = 0, $orderId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $invoiceId);
        $this->orderId = $orderId;
    }
    
    public static function getSearchObj()
    {
        $srch = new SearchBase(static::DB_TBL, 'invoice');
        return $srch;
        
    }
    
    public static function getInvoiceDetailsByOrderId(string $orderId)
    {
        $srch = static::getSearchObj();
        $srch->addCondition('invoice_order_id', '=', $orderId);
		$srch->doNotLimitRecords();
		$srch->doNotCalculateRecords();
		$rs = $srch->getResultSet();
        return FatApp::getDB()->fetch($rs);
    }


}