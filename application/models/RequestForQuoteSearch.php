<?php

class RequestForQuoteSearch extends SearchBase
{
    public function __construct()
    {
        parent::__construct(RequestForQuote::DB_TBL, 'rfq');
    }
    
    public function joinWithSellerProduct(int $langId = 0)
    {
        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'selprod_id = rfq_selprod_id');
        $this->joinTable(SellerProduct::DB_TBL_SELLER_PROD_DATA, 'LEFT OUTER JOIN', 'sprodata_selprod_id = rfq_selprod_id');
        if ($langId > 0) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'selprodlang_selprod_id = selprod_id and sp_l.selprodlang_lang_id = ' . $langId, 'sp_l');
        }
    }
    
    public function joinUsers($joinCredentials = false)
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'rfq_user_id = user.user_id', 'user');
        if ($joinCredentials == true) {
            $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'user.user_id = user_cred.credential_user_id', 'user_cred');
        }
    }
    
    public function joinForSeller($joinCredentials = false)
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = seller.user_id', 'seller');
        if ($joinCredentials == true) {
            $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller.user_id = seller_cred.credential_user_id', 'seller_cred');
        }
    }
    
    public function joinForShop(int $langId = 0)
    {
        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'selprod_user_id = shop.shop_user_id', 'shop');
        if ($langId > 0) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop.shop_id = shoplang_shop_id AND shoplang_lang_id = '.$langId);
        }
    }
    
    public function joinWithProduct($langId = 0)
    {
        $this->joinTable(Product::DB_TBL, 'LEFT JOIN', Product::DB_TBL_PREFIX.'id = selprod_product_id','p');
        if ($langId > 0) {
            $this->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $langId, 'p_l');
        } 
    }
    
    public function joinWithOrder()
    {
        $this->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', 'order_rfq_id = rfq_id', 'ord');
    }
    
    public function joinWithInvoice()
    {
        $this->joinTable(Invoice::DB_TBL, 'LEFT OUTER JOIN', 'invoice_order_id = order_id', 'invoice');
    }
}
