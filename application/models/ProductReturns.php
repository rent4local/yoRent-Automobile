<?php

class ProductReturns extends SearchBase
{

    const UPCOMING_RETURN_TYPE = 1;
    const OVERDUE_RETURN_TYPE = 2;

    public function __construct()
    {
        
    }

    public function getTotalPages()
    {
        return $this->total_pages;
    }

    public function getReturnProducts(int $shopId, string $startDate, string $endDate,  int $langId = 0)
    {
        if (1 > $shopId) {
            return false;
        }
        $srch = new SearchBase(OrderProduct::DB_TBL);
        $srch->joinTable(Orders::DB_TBL, 'LEFT JOIN', 'op_order_id=order_id');
        $srch->joinTable(OrderProductData::DB_TBL, 'INNER JOIN', 'opd_op_id=op_id');
        if ($langId > 0) {
            $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_LANG, 'LEFT OUTER JOIN', 'oplang_op_id = op_id AND oplang_lang_id = ' . $langId);
        }
        $srch->addCondition('op_shop_id', '=', $shopId);
        if (!empty($startDate)) {
            $srch->addCondition('opd_rental_end_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $srch->addCondition('opd_rental_end_date', '<=', $endDate);
        }
        $srch->addCondition('opd_sold_or_rented', '=', applicationConstants::ORDER_TYPE_RENT);
        $srch->addCondition('op_status_id', '=', OrderStatus::ORDER_DELIVERED);
        $srch->addMultipleFields(array("op_id, op_order_id, op_selprod_id, op_selprod_title as opr_name, op_qty,  opd_rental_start_date, opd_rental_end_date, op_invoice_number"));
        return $srch;
        /* $rs = $srch->getResultSet();
        $this->total_pages = $srch->pages();
        return FatApp::getDb()->fetchAll($rs); */
    }

    public function overdueProductNotification(string $orderID, int $oprID, int $langId = 0): bool
    {
        $srch = new SearchBase(Orders::DB_TBL);
        $srch->joinTable(OrderProduct::DB_TBL, 'JOIN', 'op_order_id=order_id');
        $srch->joinTable(OrderProductData::DB_TBL, 'JOIN', 'opd_order_id=order_id');
        if ($langId > 0) {
            $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_LANG, 'LEFT OUTER JOIN', 'oplang_op_id = op_id AND oplang_lang_id = ' . $langId);
        }
        $srch->joinTable(User::DB_TBL, 'JOIN', 'order_user_id=user_id');
        $srch->joinTable(User::DB_TBL_CRED, 'JOIN', 'order_user_id=credential_user_id');
        $srch->addCondition('order_id', '=', $orderID);
        $srch->addCondition('op_id', '=', $oprID);
        $srch->addMultipleFields(array("op_status_id, user_name, op_selprod_title, opd_rental_end_date, credential_email"));
        $rs = $srch->getResultSet();
        $result = FatApp::getDb()->fetch($rs);
        if (empty($result)) {
            return Labels::getLabel('L_Something_went_wrong_with_order_,Please_contact_with_administrator', $langId);
        }

        if ($result['op_status_id'] != OrderStatus::ORDER_DELIVERED) {
            return Labels::getLabel('L_Product_already_Returned_,Please_refresh_your_page', $langId);
        }

        $arr_replacements = [
            '{name}' => $result['user_name'],
            '{product_name}' => $result['op_selprod_title'],
            '{rental_end_date}' => date('Y-m-d', strtotime($result['opd_rental_end_date'])),
        ];
        $tpl_name = 'overdue_product_return';
        return EmailHandler::sendMailTpl($result['credential_email'], $tpl_name, $langId, $arr_replacements);
    }

}
